<script setup>
import { ref, onMounted } from 'vue'

const produits = ref([])
const search = ref('')
const loading = ref(true)
const showEditModal = ref(false)
const editingProduct = ref(null)

const fetchMyProduits = async () => {
  try {
    const response = await fetch('http://localhost:8000/api_vendeur_produits.php?vendeur_id=1')
    const data = await response.json()
    produits.value = data
    loading.value = false
  } catch (error) {
    console.error('Erreur:', error)
  }
}

const deleteProduit = async (id) => {
  if (confirm('Voulez-vous vraiment supprimer ce produit ?')) {
    try {
      const response = await fetch(`http://localhost:8000/api_vendeur_delete.php?id=${id}`, { method: 'DELETE' })
      const result = await response.json()
      if (result.success) {
        produits.value = produits.value.filter(p => p.id !== id)
      } else {
        alert(result.error)
      }
    } catch (error) {
      console.error('Erreur suppression:', error)
    }
  }
}

const openEditModal = (product) => {
  editingProduct.value = { ...product }
  showEditModal.value = true
}

const updateProduit = async () => {
  try {
    const response = await fetch('http://localhost:8000/api_vendeur_edit.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(editingProduct.value)
    })
    const result = await response.json()
    if (result.success) {
      showEditModal.value = false
      fetchMyProduits() // Rafraîchir la liste
    } else {
      alert(result.error)
    }
  } catch (error) {
    console.error('Erreur modification:', error)
  }
}

onMounted(fetchMyProduits)
</script>

<template>
  <div class="vendeur-produits">
    <div class="header-actions">
      <h2>Mes Produits</h2>
      <div class="search-bar">
        <i class="fas fa-search"></i>
        <input v-model="search" type="text" placeholder="Rechercher un produit...">
      </div>
    </div>

    <div v-if="loading" class="loading">Chargement...</div>
    
    <div v-else class="table-container">
      <table class="produits-table">
        <thead>
          <tr>
            <th>Produit</th>
            <th>Catégorie</th>
            <th>Prix</th>
            <th>Stock</th>
            <th>Statut</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="p in produits.filter(item => item.nom.toLowerCase().includes(search.toLowerCase()))" :key="p.id">
            <td>
              <div class="product-cell">
                <span class="icon">{{ p.icon }}</span>
                <span class="name">{{ p.nom }}</span>
              </div>
            </td>
            <td>{{ p.type }}</td>
            <td class="price">{{ p.prix }} €</td>
            <td>
              <span :class="{ 'low-stock': p.stock < 10 }">{{ p.stock }}</span>
            </td>
            <td>
              <span v-if="p.stock > 0" class="status-badge in-stock">En vente</span>
              <span v-else class="status-badge out-of-stock">Rupture</span>
            </td>
            <td class="actions">
              <button @click="openEditModal(p)" class="btn-icon edit" title="Modifier"><i class="fas fa-edit"></i></button>
              <button @click="deleteProduit(p.id)" class="btn-icon delete" title="Supprimer"><i class="fas fa-trash"></i></button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal de Modification -->
    <div v-if="showEditModal" class="modal-overlay">
      <div class="modal-content">
        <div class="modal-header">
          <h3>Modifier le produit</h3>
          <button @click="showEditModal = false" class="btn-close">&times;</button>
        </div>
        <form @submit.prevent="updateProduit" class="edit-form">
          <div class="form-grid">
            <div class="form-group">
              <label>Nom</label>
              <input v-model="editingProduct.nom" type="text" required>
            </div>
            <div class="form-group">
              <label>Prix (€)</label>
              <input v-model="editingProduct.prix" type="number" step="0.01" required>
            </div>
            <div class="form-group">
              <label>Stock</label>
              <input v-model="editingProduct.stock" type="number" required>
            </div>
            <div class="form-group">
              <label>Type</label>
              <select v-model="editingProduct.type">
                <option value="Semence">Semence</option>
                <option value="Engrais">Engrais</option>
                <option value="Materiel">Materiel</option>
                <option value="Pesticide">Pesticide</option>
              </select>
            </div>
            <div class="form-group full-width">
              <label>Description</label>
              <textarea v-model="editingProduct.description" rows="3"></textarea>
            </div>
            <div class="checkbox-group">
              <label><input v-model="editingProduct.promotion" type="checkbox"> Promo</label>
              <label><input v-model="editingProduct.certificationBio" type="checkbox"> Bio</label>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" @click="showEditModal = false" class="btn-cancel">Annuler</button>
            <button type="submit" class="btn-save">Enregistrer les modifications</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<style scoped>
.header-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.search-bar {
  position: relative;
  width: 300px;
}

.search-bar i {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #999;
}

.search-bar input {
  width: 100%;
  padding: 0.6rem 1rem 0.6rem 2.5rem;
  border: 1px solid #eee;
  border-radius: 8px;
  outline: none;
}

.table-container {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}

.produits-table {
  width: 100%;
  border-collapse: collapse;
}

.produits-table th {
  background: #fdfbf7;
  padding: 1rem;
  text-align: left;
  font-size: 0.85rem;
  text-transform: uppercase;
  color: #666;
  border-bottom: 1px solid #eee;
}

.produits-table td {
  padding: 1rem;
  border-bottom: 1px solid #eee;
}

.product-cell {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.product-cell .icon {
  font-size: 1.5rem;
}

.price {
  font-weight: 700;
}

.low-stock {
  color: #e53935;
  font-weight: 700;
}

.status-badge {
  padding: 4px 8px;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 700;
}

.in-stock { background: #e8f5e9; color: #2e7d32; }
.out-of-stock { background: #ffebee; color: #c62828; }

.actions {
  display: flex;
  gap: 0.5rem;
}

.btn-icon {
  background: none;
  border: none;
  cursor: pointer;
  padding: 8px;
  border-radius: 6px;
  transition: all 0.2s;
}

.btn-icon.edit { color: #2d4a1e; background: #e8f5e9; }
.btn-icon.delete { color: #e53935; background: #ffebee; }

.btn-icon:hover { transform: scale(1.1); }

/* --- Styles Modal --- */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2000;
  backdrop-filter: blur(4px);
}

.modal-content {
  background: white;
  width: 90%;
  max-width: 600px;
  border-radius: 20px;
  padding: 2rem;
  box-shadow: 0 20px 40px rgba(0,0,0,0.2);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.btn-close {
  background: none;
  border: none;
  font-size: 2rem;
  cursor: pointer;
  color: #999;
}

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
}

.form-group label {
  font-weight: 700;
  font-size: 0.85rem;
}

.form-group input, .form-group select, .form-group textarea {
  padding: 0.7rem;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-family: inherit;
}

.full-width { grid-column: 1 / -1; }

.checkbox-group {
  grid-column: 1 / -1;
  display: flex;
  gap: 1.5rem;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 2rem;
}

.btn-cancel {
  padding: 0.8rem 1.5rem;
  background: #eee;
  border: none;
  border-radius: 8px;
  cursor: pointer;
}

.btn-save {
  padding: 0.8rem 1.5rem;
  background: #f5a623;
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 700;
  cursor: pointer;
}
</style>
