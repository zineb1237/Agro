<script setup>
import { ref, onMounted, computed } from 'vue'
import NavBar from '@/components/NavBar.vue'

// ========== ÉTATS ==========
const produits = ref([])
const search = ref('')
const loading = ref(true)
const showEditModal = ref(false)
const showDeleteModal = ref(false)
const editingProduct = ref(null)
const productToDelete = ref(null)
const editImageFile = ref(null)
const editImagePreview = ref('')

// Options de statut
const statutOptions = [
  { value: 'disponible', label: 'Disponible', color: '#2e7d32', bg: '#e8f5e9' },
  { value: 'en_attente', label: 'En attente', color: '#ed6c02', bg: '#fff4e5' },
  { value: 'vendu', label: 'Vendu', color: '#9c27b0', bg: '#f3e5f5' },
  { value: 'rupture', label: 'Rupture', color: '#d32f2f', bg: '#ffebee' }
]

// Toast
const toast = ref({ visible: false, message: '', error: false })
let toastTimeout = null

function showToast(message, isError = false) {
  if (toastTimeout) clearTimeout(toastTimeout)
  toast.value = { visible: true, message, error: isError }
  toastTimeout = setTimeout(() => {
    toast.value.visible = false
  }, 3000)
}

// Statistiques
const produitsActifs = computed(() => produits.value.filter(p => p.statut !== 'rupture' && p.stock > 0).length)
const alertesStock = computed(() => produits.value.filter(p => p.stock < 10 && p.stock > 0).length)
const totalVues = ref(284)

// Filtre recherche
const filteredProduits = computed(() => {
  let result = produits.value
  if (search.value.trim()) {
    const q = search.value.toLowerCase()
    result = result.filter(p => p.nom.toLowerCase().includes(q) || p.type?.toLowerCase().includes(q))
  }
  return result
})

// ========== API ==========
const fetchMyProduits = async () => {
  try {
    const token = localStorage.getItem('token')
    const response = await fetch('http://localhost:8000/api/vendeur/produits', {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    })
    const data = await response.json()
    produits.value = data
    loading.value = false
  } catch (error) {
    console.error('Erreur:', error)
    showToast('Erreur de chargement', true)
  }
}

// Suppression
const confirmDelete = (product) => {
  productToDelete.value = product
  showDeleteModal.value = true
}

const deleteProduit = async () => {
  try {
    const token = localStorage.getItem('token')
    const response = await fetch(`http://localhost:8000/api/vendeur/produits/${productToDelete.value.id}`, { 
      method: 'DELETE',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    })
    const result = await response.json()
    if (result.success) {
      produits.value = produits.value.filter(p => p.id !== productToDelete.value.id)
      showToast('Produit supprimé')
      showDeleteModal.value = false
      productToDelete.value = null
    } else {
      showToast(result.error || 'Erreur suppression', true)
    }
  } catch (error) {
    console.error('Erreur suppression:', error)
    showToast('Erreur réseau', true)
  }
}

// Modification
const openEditModal = (product) => {
  editingProduct.value = { ...product }
  editImageFile.value = null
  editImagePreview.value = product.images?.[0] || ''
  showEditModal.value = true
}

const handleImageChange = (event) => {
  const file = event.target.files[0]
  if (file) {
    editImageFile.value = file
    const reader = new FileReader()
    reader.onload = (e) => { editImagePreview.value = e.target.result }
    reader.readAsDataURL(file)
  }
}

const updateProduit = async () => {
  try {
    const token = localStorage.getItem('token')
    const formData = new FormData()
    formData.append('id', editingProduct.value.id)
    formData.append('nom', editingProduct.value.nom)
    formData.append('prix', editingProduct.value.prix)
    formData.append('stock', editingProduct.value.stock)
    formData.append('type', editingProduct.value.type)
    formData.append('description', editingProduct.value.description || '')
    formData.append('statut', editingProduct.value.statut || 'disponible')
    formData.append('promotion', editingProduct.value.promotion ? '1' : '0')
    formData.append('certificationBio', editingProduct.value.certificationBio ? '1' : '0')
    if (editImageFile.value) {
      formData.append('image', editImageFile.value)
    }
    const response = await fetch('http://localhost:8000/api/vendeur/produits/edit', {
      method: 'POST',
      headers: { 'Authorization': `Bearer ${token}` },
      body: formData
    })
    const result = await response.json()
    if (result.success) {
      showToast('Produit modifié')
      showEditModal.value = false
      fetchMyProduits()
    } else {
      showToast(result.error || 'Erreur modification', true)
    }
  } catch (error) {
    console.error('Erreur modification:', error)
    showToast('Erreur réseau', true)
  }
}

// Helpers
const getStatutStyle = (statut) => {
  const option = statutOptions.find(opt => opt.value === statut) || statutOptions[0]
  return { backgroundColor: option.bg, color: option.color }
}
const getStatutLabel = (statut) => statutOptions.find(opt => opt.value === statut)?.label || 'Disponible'

const stockDisplay = (stock) => {
  if (stock <= 0) return { text: 'Rupture', class: 'stock-danger' }
  if (stock < 10) return { text: `${stock} unités restantes`, class: 'stock-warning' }
  return { text: `${stock} unités`, class: 'stock-ok' }
}

onMounted(fetchMyProduits)
</script>

<template>
  <div class="produits-container">
    <NavBar />
    <div class="main-content">
      <!-- Toast -->
      <div v-if="toast.visible" class="toast" :class="{ error: toast.error }">{{ toast.message }}</div>

      <!-- En-tête -->
      <div class="page-header">
        <div>
          <h1>Mes produits</h1>
          <p class="stats-summary">{{ produitsActifs }} produits publiés · {{ alertesStock }} en alerte de stock</p>
        </div>
        <button class="btn-primary" @click="$router.push('/vendeur/ajouter-produit')">
          + Ajouter un produit
        </button>
      </div>

      <!-- Cartes stats -->
      <div class="stats-cards">
        <div class="stat-card">
          <div class="stat-icon green"><i class="fas fa-seedling"></i></div>
          <div class="stat-info">
            <span class="stat-value">{{ produitsActifs }}</span>
            <span class="stat-label">Produits actifs</span>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon orange"><i class="fas fa-eye"></i></div>
          <div class="stat-info">
            <span class="stat-value">{{ totalVues }}</span>
            <span class="stat-label">Vues ce mois</span>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon red"><i class="fas fa-exclamation-triangle"></i></div>
          <div class="stat-info">
            <span class="stat-value">{{ alertesStock }}</span>
            <span class="stat-label">Alertes stock</span>
          </div>
        </div>
      </div>

      <!-- Barre de recherche -->
      <div class="toolbar">
        <div class="search-wrapper">
          <i class="fas fa-search"></i>
          <input type="text" v-model="search" placeholder="Rechercher un produit..." />
        </div>
      </div>

      <!-- Tableau des produits -->
      <div class="table-wrapper">
        <table class="produits-table">
          <thead>
            <tr>
              <th>PRODUIT</th>
              <th>CATÉGORIE</th>
              <th>PRIX</th>
              <th>STOCK</th>
              <th>STATUT</th>
              <th>ACTIONS</th>
            </tr>
          </thead>
          <tbody>
            <!-- Lignes produits -->
            <tr v-for="p in filteredProduits" :key="p.id">
              <td class="product-cell">
                <div class="product-info">
                  <img v-if="p.images && p.images[0]" :src="p.images[0]" class="product-img" alt="produit" />
                  <div v-else class="product-img-placeholder">
                    <i class="fas fa-image"></i>
                  </div>
                  <span class="product-name">{{ p.nom }}</span>
                </div>
              </td>
              <td class="category">{{ p.type }}</td>
              <td class="price">{{ Number(p.prix).toFixed(2) }} MAD</td>
              <td class="stock-cell">
                <span :class="stockDisplay(p.stock).class">
                  <i v-if="p.stock > 0" class="fas fa-check-circle"></i>
                  <i v-else class="fas fa-times-circle"></i>
                  {{ stockDisplay(p.stock).text }}
                </span>
              </td>
              <td class="status-cell">
                <span class="status-badge" :style="getStatutStyle(p.statut)">
                  {{ getStatutLabel(p.statut) }}
                </span>
              </td>
             
              <td class="actions">
                <button @click="openEditModal(p)" class="icon-btn edit" title="Modifier">
                  <i class="fas fa-edit"></i>
                </button>
                <button @click="confirmDelete(p)" class="icon-btn delete" title="Supprimer">
                  <i class="fas fa-trash-alt"></i>
                </button>
              </td>
            </tr>
            <!-- Ligne vide si aucun résultat -->
            <tr v-if="filteredProduits.length === 0">
              <td colspan="7" class="empty-row">Aucun produit trouvé</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- MODAL MODIFICATION -->
      <div v-if="showEditModal" class="modal-overlay" @click.self="showEditModal = false">
        <div class="modal-content edit-modal">
          <div class="modal-header">
            <h3>✏️ Modifier le produit</h3>
            <button @click="showEditModal = false" class="close-btn">&times;</button>
          </div>
          <form @submit.prevent="updateProduit">
            <div class="form-grid-3">
              <div class="form-group">
                <label>Nom</label>
                <input v-model="editingProduct.nom" type="text" required />
              </div>
              <div class="form-group">
                <label>Prix (MAD)</label>
                <input v-model="editingProduct.prix" type="number" step="0.01" required />
              </div>
              <div class="form-group">
                <label>Stock</label>
                <input v-model="editingProduct.stock" type="number" required />
              </div>
              <div class="form-group">
                <label>Catégorie</label>
                <select v-model="editingProduct.type">
                  <option value="Semences">Semences</option>
                  <option value="Engrais">Engrais</option>
                  <option value="Matériel agricole">Matériel agricole</option>
                  <option value="Irrigation">Irrigation</option>
                  <option value="Produits phytosanitaires">Produits phytosanitaires</option>
                  <option value="Alimentation animale">Alimentation animale</option>
                  <option value="Pièces de rechange">Pièces de rechange</option>
                </select>
              </div>
              <div class="form-group">
                <label>Statut</label>
                <select v-model="editingProduct.statut">
                  <option v-for="opt in statutOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                </select>
              </div>
              <div class="form-group">
                <label>Image</label>
                <input type="file" @change="handleImageChange" accept="image/*" />
                <div class="image-preview" v-if="editImagePreview">
                  <img :src="editImagePreview" alt="aperçu" />
                </div>
              </div>
              <div class="form-group full-width">
                <label>Description</label>
                <textarea v-model="editingProduct.description" rows="3"></textarea>
              </div>
              <div class="checkbox-group full-width">
                <label><input v-model="editingProduct.promotion" type="checkbox" /> 🔥 En promotion</label>
                <label><input v-model="editingProduct.certificationBio" type="checkbox" /> 🌱 Certifié BIO</label>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" @click="showEditModal = false" class="btn-secondary">Annuler</button>
              <button type="submit" class="btn-primary">Enregistrer</button>
            </div>
          </form>
        </div>
      </div>

      <!-- MODAL SUPPRESSION -->
      <div v-if="showDeleteModal" class="modal-overlay" @click.self="showDeleteModal = false">
        <div class="modal-content delete-modal">
          <div class="modal-header">
            <h3>🗑️ Confirmer la suppression</h3>
            <button @click="showDeleteModal = false" class="close-btn">&times;</button>
          </div>
          <p>Voulez-vous vraiment supprimer <strong>{{ productToDelete?.nom }}</strong> ?</p>
          <p class="warning">Cette action est irréversible.</p>
          <div class="modal-footer">
            <button @click="showDeleteModal = false" class="btn-secondary">Annuler</button>
            <button @click="deleteProduit" class="btn-danger">Supprimer</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* (Styles inchangés – identiques à la version précédente) */
.produits-container {
  display: flex;
  height: 100vh;
  width: 100%;
}
.main-content {
  flex: 1;
  overflow-y: auto;
  padding: 1.8rem 2rem;
  background: #f8f6f2;
  position: relative;
}
.toast {
  position: fixed;
  top: 20px;
  right: 20px;
  background: #2c7a4d;
  color: white;
  padding: 12px 24px;
  border-radius: 40px;
  font-weight: 500;
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  z-index: 3000;
  animation: slideIn 0.3s ease;
}
.toast.error { background: #d94f3d; }
@keyframes slideIn {
  from { transform: translateX(100%); opacity: 0; }
  to { transform: translateX(0); opacity: 1; }
}
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  margin-bottom: 1.5rem;
  flex-wrap: wrap;
}
.page-header h1 {
  font-family: 'Fraunces', serif;
  font-size: 2rem;
  color: #2D1A0E;
  margin: 0;
}
.stats-summary {
  color: #6b4c3a;
  margin: 0;
  font-size: 0.9rem;
}
.btn-primary {
  background: #2c5f2d;
  border: none;
  padding: 0.6rem 1.5rem;
  border-radius: 40px;
  color: white;
  font-weight: 600;
  cursor: pointer;
  transition: 0.2s;
}
.btn-primary:hover {
  background: #1f4a1f;
  transform: translateY(-2px);
}
.stats-cards {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1.5rem;
  margin-bottom: 2rem;
}
.stat-card {
  background: white;
  border-radius: 20px;
  padding: 1.2rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  box-shadow: 0 2px 8px rgba(0,0,0,0.03);
}
.stat-icon {
  width: 48px;
  height: 48px;
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.4rem;
}
.stat-icon.green { background: #e8f5e9; color: #2e7d32; }
.stat-icon.orange { background: #fff4e5; color: #ed6c02; }
.stat-icon.red { background: #ffebee; color: #d32f2f; }
.stat-info { flex: 1; }
.stat-value {
  display: block;
  font-size: 1.8rem;
  font-weight: 800;
  color: #2D1A0E;
  line-height: 1.2;
}
.stat-label {
  font-size: 0.8rem;
  color: #8b7a6b;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}
.toolbar {
  display: flex;
  justify-content: flex-start;
  align-items: center;
  margin-bottom: 1.5rem;
}
.search-wrapper {
  position: relative;
  width: 900px;
}
.search-wrapper i {
  position: absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  color: #aaa;
}
.search-wrapper input {
  width: 100%;
  padding: 0.6rem 1rem 0.6rem 2.5rem;
  border: 1px solid #e2e8f0;
  border-radius: 40px;
  background: white;
  font-size: 0.9rem;
  transition: 0.2s;
}
.search-wrapper input:focus {
  border-color: #f5a623;
  outline: none;
  box-shadow: 0 0 0 3px rgba(245,166,35,0.1);
}
.table-wrapper {
  background: white;
  border-radius: 24px;
  overflow-x: auto;
  box-shadow: 0 4px 12px rgba(0,0,0,0.03);
}
.produits-table {
  width: 100%;
  border-collapse: collapse;
  min-width: 1000px;
}
.produits-table th {
  text-align: left;
  padding: 1rem 1.2rem;
  background: #fdfbf7;
  font-size: 0.7rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: #8b7a6b;
  border-bottom: 1px solid #eee;
}
.produits-table td {
  padding: 1rem 1.2rem;
  border-bottom: 1px solid #f0f0f0;
  vertical-align: middle;
}
.produits-table th:nth-child(1), .produits-table td:nth-child(1) { width: 28%; }
.produits-table th:nth-child(2), .produits-table td:nth-child(2) { width: 12%; }
.produits-table th:nth-child(3), .produits-table td:nth-child(3) { width: 10%; }
.produits-table th:nth-child(4), .produits-table td:nth-child(4) { width: 12%; }
.produits-table th:nth-child(5), .produits-table td:nth-child(5) { width: 10%; }
.produits-table th:nth-child(6), .produits-table td:nth-child(6) { width: 10%; }
.produits-table th:nth-child(7), .produits-table td:nth-child(7) { width: 12%; }
.product-info {
  display: flex;
  align-items: center;
  gap: 1rem;
}
.product-img {
 
  border-radius: 5px;
  height: 50px;
  width:50px;
  display: flex;
  align-items: center;
  justify-content: center;
  
}

.product-icon {
  font-size: 4rem;
  filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.1));
}
.product-name {
  font-weight: 600;
  color: #2D1A0E;
  margin-bottom: 4px;
}
.product-meta {
  font-size: 0.7rem;
  color: #8b7a6b;
}
.price {
  font-weight: 700;
  color: #2c5f2d;
}
.stock-cell span {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 0.85rem;
}
.stock-ok { color: #2e7d32; }
.stock-warning { color: #ed6c02; }
.stock-danger { color: #d32f2f; }
.status-badge {
  display: inline-block;
  padding: 4px 12px;
  border-radius: 30px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: capitalize;
}
.tags {
  display: flex;
  gap: 8px;
}
.tag {
  font-size: 0.7rem;
  padding: 2px 8px;
  border-radius: 20px;
  font-weight: 700;
}
.tag.bio { background: #e8f5e9; color: #2e7d32; }
.tag.promo { background: #fff4e5; color: #ed6c02; }
.actions {
  display: flex;
  gap: 0.5rem;
  white-space: nowrap;
}
.icon-btn {
  background: none;
  border: none;
  width: 32px;
  height: 32px;
  border-radius: 10px;
  cursor: pointer;
  transition: 0.2s;
  font-size: 1rem;
}
.icon-btn.edit { background: #e8f5e9; color: #2e7d32; }
.icon-btn.delete { background: #ffebee; color: #d32f2f; }
.icon-btn:hover { transform: scale(1.1); }
.empty-row {
  text-align: center;
  padding: 2rem;
  color: #aaa;
  font-style: italic;
}
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0,0,0,0.5);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2000;
}
.modal-content {
  background: white;
  border-radius: 28px;
  width: 90%;
  max-width: 850px;
  max-height: 90vh;
  overflow-y: auto;
  padding: 1.5rem 2rem 2rem;
  animation: fadeInUp 0.3s ease;
}
@keyframes fadeInUp {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}
.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}
.modal-header h3 {
  margin: 0;
  font-size: 1.4rem;
  color: #2c5f2d;
}
.close-btn {
  background: none;
  border: none;
  font-size: 2rem;
  cursor: pointer;
  color: #aaa;
  line-height: 1;
}
.close-btn:hover { color: #d94f3d; }
.form-grid-3 {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1.2rem;
}
.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
}
.form-group label {
  font-size: 0.75rem;
  text-transform: uppercase;
  font-weight: 600;
  color: #5b6e3e;
}
.form-group input, .form-group select, .form-group textarea {
  padding: 0.6rem 1rem;
  border: 1px solid #ddd;
  border-radius: 14px;
  font-family: inherit;
}
.full-width { grid-column: 1 / -1; }
.checkbox-group {
  display: flex;
  gap: 1.5rem;
  margin-top: 0.5rem;
}
.checkbox-group label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: normal;
  text-transform: none;
  color: #333;
  cursor: pointer;
}
.image-preview {
  margin-top: 8px;
}
.image-preview img {
  width: 70px;
  height: 70px;
  object-fit: cover;
  border-radius: 12px;
  border: 1px solid #eee;
}
.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 2rem;
  padding-top: 1rem;
  border-top: 1px solid #eee;
}
.btn-secondary {
  background: #f1f5f9;
  border: none;
  padding: 0.6rem 1.5rem;
  border-radius: 40px;
  font-weight: 600;
  cursor: pointer;
}
.btn-primary {
  background: #2c5f2d;
  color: white;
  border: none;
  padding: 0.6rem 1.5rem;
  border-radius: 40px;
  font-weight: 600;
  cursor: pointer;
}
.btn-danger {
  background: #d94f3d;
  color: white;
  border: none;
  padding: 0.6rem 1.5rem;
  border-radius: 40px;
  font-weight: 600;
  cursor: pointer;
}
.delete-modal {
  max-width: 450px;
  text-align: center;
}
.warning {
  color: #d94f3d;
  font-size: 0.8rem;
}
@media (max-width: 800px) {
  .main-content { padding: 1rem; }
  .stats-cards { grid-template-columns: 1fr; }
  .toolbar { flex-direction: column; align-items: stretch; }
  .search-wrapper { width: 100%; }
  .form-grid-3 { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 600px) {
  .form-grid-3 { grid-template-columns: 1fr; }
  .modal-content { padding: 1rem; }
}
</style>