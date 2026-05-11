<script setup>
import { ref } from 'vue'

const form = ref({
  nom: '',
  prix: '',
  description: '',
  type: 'Semence',
  stock: 10,
  promotion: false,
  certificationBio: false
})

const types = ['Semence', 'Engrais', 'Materiel', 'Pesticide']

const submitProduit = async () => {
  try {
    const response = await fetch('http://localhost:8000/api_vendeur_add.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ ...form.value, vendeur_id: 1 })
    })
    const result = await response.json()
    if (result.success) {
      alert('Produit ajouté avec succès !')
      // Reset form
      form.value = { nom: '', prix: '', description: '', type: 'Semence', stock: 10, promotion: false, certificationBio: false }
    }
  } catch (error) {
    console.error('Erreur:', error)
  }
}
</script>

<template>
  <div class="add-produit-container">
    <h2>Ajouter un nouveau produit</h2>
    <form @submit.prevent="submitProduit" class="add-form">
      <div class="form-grid">
        <div class="form-group">
          <label>Nom du produit</label>
          <input v-model="form.nom" type="text" required placeholder="Ex: Blé dur">
        </div>
        
        <div class="form-group">
          <label>Catégorie</label>
          <select v-model="form.type">
            <option v-for="t in types" :key="t" :value="t">{{ t }}</option>
          </select>
        </div>

        <div class="form-group">
          <label>Prix Unitaire (€)</label>
          <input v-model="form.prix" type="number" step="0.01" required>
        </div>

        <div class="form-group">
          <label>Quantité en Stock</label>
          <input v-model="form.stock" type="number" required>
        </div>

        <div class="form-group full-width">
          <label>Description</label>
          <textarea v-model="form.description" rows="4"></textarea>
        </div>

        <div class="checkbox-group">
          <label class="checkbox-item">
            <input v-model="form.promotion" type="checkbox">
            En Promotion
          </label>
          <label class="checkbox-item">
            <input v-model="form.certificationBio" type="checkbox">
            Certifié BIO
          </label>
        </div>
      </div>

      <button type="submit" class="btn-submit">
        <i class="fas fa-plus"></i> Enregistrer le produit
      </button>
    </form>
  </div>
</template>

<style scoped>
.add-produit-container {
  max-width: 800px;
}

.add-form {
  background: white;
  padding: 2rem;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.05);
  margin-top: 1.5rem;
}

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.5rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.full-width {
  grid-column: 1 / -1;
}

label {
  font-weight: 700;
  font-size: 0.9rem;
  color: #333;
}

input, select, textarea {
  padding: 0.8rem;
  border: 1px solid #eee;
  border-radius: 8px;
  outline: none;
  font-family: inherit;
}

input:focus, select:focus, textarea:focus {
  border-color: #f5a623;
}

.checkbox-group {
  grid-column: 1 / -1;
  display: flex;
  gap: 2rem;
  padding: 1rem 0;
}

.checkbox-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
}

.btn-submit {
  background: #f5a623;
  color: white;
  border: none;
  padding: 1rem 2rem;
  border-radius: 8px;
  font-weight: 700;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 10px;
  margin-top: 1rem;
  transition: transform 0.2s;
}

.btn-submit:hover { transform: scale(1.02); }
</style>
