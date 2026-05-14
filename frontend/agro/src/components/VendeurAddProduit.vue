<script setup>
import { ref, computed } from 'vue'
import NavBar from '@/components/NavBar.vue'

// Catégories principales
const categories = [
  'Semences',
  'Engrais',
  'Matériel agricole',
  'Irrigation',
  'Produits phytosanitaires',
  'Alimentation animale',
  'Pièces de rechange'
]

// Sous-catégories par catégorie
const subcategoriesMap = {
  'Semences': ['Blé', 'Maïs', 'Tomate', 'Carotte', 'Laitue', 'Haricot', 'Pomme de terre', 'Autre'],
  'Engrais': ['Organique', 'Minéral', 'Liquide', 'Granulé', 'Foliaire', 'Fumier', 'Compost'],
  'Matériel agricole': ['Tracteur', 'Pulvérisateur', 'Broyeur', 'Remorque', 'Charrue', 'Herse', 'Semoir'],
  'Irrigation': ['Goutte à goutte', 'Asperseur', 'Tuyau', 'Pompe', 'Raccord', 'Arroseur'],
  'Produits phytosanitaires': ['Fongicide', 'Insecticide', 'Herbicide', 'Acaricide', 'Bactéricide'],
  'Alimentation animale': ['Granulés', 'Farine', 'Complément minéral', 'Foin', 'Ensilage'],
  'Pièces de rechange': ['Filtre', 'Courroie', 'Lame', 'Pneu', 'Batterie', 'Électrovanne']
}

// Unités de vente
const unitesVente = ['kg', 'L', 'Sac', 'Pièce', 'Boîte', 'Palette', 'm²', 'hectare']

// Formulaire simplifié : plus de champs spécifiques
const form = ref({
  nom: '',
  categorie: 'Semences',
  sousCategorie: '',
  description: '',          // unique champ pour tout décrire
  prix: '',
  stock: '',
  uniteVente: 'kg',
  promotion: false,
  certificationBio: false
})

const images = ref([])
const imagePreviews = ref([])

const availableSubcategories = computed(() => {
  return subcategoriesMap[form.value.categorie] || []
})

const onCategoryChange = () => {
  form.value.sousCategorie = ''
}

// Upload images avec nouveau style
const handleImageUpload = (event) => {
  const files = Array.from(event.target.files)
  images.value.push(...files)
  files.forEach(file => {
    const reader = new FileReader()
    reader.onload = (e) => imagePreviews.value.push(e.target.result)
    reader.readAsDataURL(file)
  })
}

const removeImage = (index) => {
  images.value.splice(index, 1)
  imagePreviews.value.splice(index, 1)
}

const submitProduit = async () => {
  try {
    const formData = new FormData()
    
    // Mapping des champs pour le backend
    formData.append('nom', form.value.nom)
    formData.append('type', form.value.categorie)
    formData.append('sous_categorie', form.value.sousCategorie)
    formData.append('prix', form.value.prix)
    formData.append('stock', form.value.stock)
    formData.append('unite_vente', form.value.uniteVente)
    formData.append('description', form.value.description)
    formData.append('promotion', form.value.promotion)
    formData.append('certificationBio', form.value.certificationBio)

    images.value.forEach((file, idx) => {
      formData.append(`images[${idx}]`, file)
    })

    const token = localStorage.getItem('token')
    const response = await fetch('http://localhost:8000/api/vendeur/produits', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      },
      body: formData
    })
    
    const result = await response.json()
    if (response.ok && result.success) {
      alert('✅ Produit ajouté avec succès !')
      form.value = {
        nom: '', categorie: 'Semences', sousCategorie: '', description: '',
        prix: '', stock: '', uniteVente: 'kg', promotion: false, certificationBio: false
      }
      images.value = []
      imagePreviews.value = []
    } else {
      alert('❌ Erreur : ' + (result.message || result.error || 'Veuillez vérifier les champs'))
    }
  } catch (error) {
    console.error('Erreur:', error)
    alert('❌ Erreur réseau ou serveur')
  }
}
</script>

<template>
  <div class="vendeur-add-produit">
    <NavBar />
    <div class="main-content">
      <div class="add-produit-container">
        <h2>➕ Ajouter un produit</h2>
        <p class="subtitle">Renseignez les informations essentielles.</p>

        <form @submit.prevent="submitProduit" class="add-form">
          <!-- ========== IDENTITÉ : 3 colonnes ========== -->
          <div class="form-section">
            <h3>📦 Identité du produit</h3>
            <div class="form-grid-3">
              <div class="form-group">
                <label>Nom du produit *</label>
                <input v-model="form.nom" type="text" required placeholder="Ex: Semence Maïs Hybride DK777">
              </div>
              <div class="form-group">
                <label>Catégorie *</label>
                <select v-model="form.categorie" @change="onCategoryChange" required>
                  <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
                </select>
              </div>
              <div class="form-group">
                <label>Sous-catégorie</label>
                <select v-model="form.sousCategorie">
                  <option value="">Sélectionnez</option>
                  <option v-for="sub in availableSubcategories" :key="sub" :value="sub">{{ sub }}</option>
                </select>
              </div>
              <div class="form-group">
                <label>Prix unitaire (€ HT) *</label>
                <input v-model="form.prix" type="number" step="0.01" required placeholder="0.00">
              </div>
              <div class="form-group">
                <label>Quantité en stock *</label>
               <input v-model="form.stock" type="number" required placeholder="0">
              </div>
              <div class="form-group">
                <label>Unité de vente</label>
                <select v-model="form.uniteVente">
                  <option v-for="u in unitesVente" :key="u" :value="u">{{ u }}</option>
                </select>
              </div>
            </div>
          </div>

          <!-- ========== DESCRIPTION UNIQUE ========== -->
          <div class="form-section">
            <h3>📝 Description détaillée</h3>
            <div class="form-group full-width">
              <label>Description (utilisez ce champ pour toutes les caractéristiques : germination, NPK, marque, dimensions, etc.)</label>
              <textarea v-model="form.description" rows="8"></textarea>
            </div>
          </div>

          <!-- ========== IMAGES : nouveau style ========== -->
          <div class="form-section">
            <h3>🖼️ Images du produit</h3>
            <div class="image-upload-modern">
              <input type="file" multiple accept="image/*" @change="handleImageUpload" id="imageInput" hidden>
              <label for="imageInput" class="drop-zone">
                <div class="drop-zone-icon">
                  <i class="fas fa-cloud-upload-alt"></i>
                </div>
                <div class="drop-zone-text">
                 Cliquez ou glissez vos images
                </div>
                <div class="drop-zone-hint">
               JPG, PNG, GIF (max 5 Mo)
                </div>
              </label>
              <div class="image-previews" v-if="imagePreviews.length">
                <div v-for="(img, idx) in imagePreviews" :key="idx" class="preview-card">
                  <img :src="img" alt="Aperçu">
                  <button type="button" @click="removeImage(idx)" class="remove-btn">
                    <i class="fas fa-trash-alt"></i>
                  </button>
                  <div class="image-number">
                    <i class="fas fa-camera"></i> {{ idx+1 }}
                  </div>
                </div>
              </div>
              <p class="info-text" v-else>
                <i class="fas fa-exclamation-circle"></i> Aucune image sélectionnée
              </p>
            </div>
          </div>

          <!-- ========== OPTIONS ========== -->
          <div class="form-section">
            <h3>🏷️ Options commerciales</h3>
            <div class="checkbox-group">
              <label class="checkbox-item">
                <input v-model="form.promotion" type="checkbox">  En promotion
              </label>
              <label class="checkbox-item">
                <input v-model="form.certificationBio" type="checkbox"> Certifié BIO
              </label>
            </div>
          </div>

          <button type="submit" class="btn-submit">
             Enregistrer le produit
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Structure principale : pleine hauteur, comme dans l'exemple "Mes Parcelles" */
.vendeur-add-produit {
  display: flex;
  height: 100vh;           /* Force la hauteur totale de la fenêtre */
  width: 100%;
  background: #f8f6f2;
}

/* La NavBar prendra toute la hauteur automatiquement grâce au flex parent */

/* Zone de contenu : défile seule si nécessaire */
.main-content {
  flex: 1;
  overflow-y: auto;        /* permet le scrolling uniquement ici */
  padding: 2rem;
  background: #f8f6f2;
}

/* Le reste du style est inchangé */
.add-produit-container {
  max-width: 1100px;
  margin: 0 auto;
}

h2 {
  font-family: 'Fraunces', serif;
  font-size: 2rem;
  color: #2D1A0E;
  margin-bottom: 0.25rem;
  margin-top: -10px;
}
.subtitle {
  color: #6b4c3a;
  margin-bottom: 10px;
  font-size: 0.9rem;
}
.add-form {
  background: white;
  border-radius: 24px;
  box-shadow: 0 8px 30px rgba(0,0,0,0.08);
  overflow: hidden;
}
.form-section {
  padding: 1.5rem 2rem;
  border-bottom: 1px solid #eee;
}
.form-section h3 {
  font-size: 1.3rem;
  font-weight: 600;
  color: #2c5f2d;
  margin-bottom: 3px;
  margin-top: -10px;
}
.form-grid-3 {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1.2rem;
}
.full-width {
  grid-column: 1 / -1;
}
.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}
label {
  font-weight: 600;
  font-size: 0.85rem;
  color: #333;
}
input, select, textarea {
  padding: 0.7rem 1rem;
  border: 1px solid #ddd;
  border-radius: 12px;
  font-size: 0.9rem;
  transition: 0.2s;
  font-family: inherit;
}
input:focus, select:focus, textarea:focus {
  border-color: #f5a623;
  outline: none;
  box-shadow: 0 0 0 3px rgba(245,166,35,0.1);
}
textarea {
  resize: vertical;
}
.checkbox-group {
  display: flex;
  gap: 2rem;
  flex-wrap: wrap;
}
.checkbox-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: normal;
  cursor: pointer;
}

/* Upload images modernisé */
.image-upload-modern {
  margin-top: 0.5rem;
}
.drop-zone {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  background: #fafaf8;
  border: 2px dashed #ccc;
  border-radius: 20px;
  padding: 2rem;
  cursor: pointer;
  transition: 0.2s;
  text-align: center;
}
.drop-zone:hover {
  border-color: #675a46;
  background: #fff8f0;
}
.drop-zone-icon {
  font-size: 3rem;
  margin-bottom: 0.5rem;
}
.drop-zone-icon i {
  font-size: 3rem;
  color: #2f2d2b;
}
.drop-zone-text {
  font-weight: 500;
  color: #333;
}
.drop-zone-text i,
.drop-zone-hint i {
  margin-right: 6px;
}
.drop-zone-hint {
  font-size: 0.75rem;
  color: #888;
  margin-top: 0.25rem;
}
.image-previews {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  margin-top: 1.5rem;
}
.preview-card {
  position: relative;
  width: 120px;
  height: 120px;
  border-radius: 16px;
  overflow: hidden;
  border: 1px solid #eee;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
.preview-card img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.remove-btn {
  position: absolute;
  top: 6px;
  right: 6px;
  background: rgba(0,0,0,0.6);
  color: white;
  border: none;
  border-radius: 50%;
  width: 26px;
  height: 26px;
  font-size: 14px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: 0.2s;
}
.remove-btn:hover {
  background: #e74c3c;
}
.remove-btn i {
  pointer-events: none;
}
.image-number {
  position: absolute;
  bottom: 6px;
  left: 6px;
  background: rgba(0,0,0,0.6);
  color: white;
  font-size: 0.7rem;
  padding: 2px 6px;
  border-radius: 20px;
  display: flex;
  align-items: center;
  gap: 4px;
}
.info-text {
  color: #888;
  font-size: 0.8rem;
  margin-top: 1rem;
}
.btn-submit {
  background: #2c5f2d;
  color: white;
  border: none;
  width: 100%;
  padding: 1rem;
  font-size: 1.1rem;
  font-weight: 700;
  cursor: pointer;
  transition: 0.2s;
  font-family: inherit;
}
.btn-submit:hover {
  background: #138615;
  transform: translateY(-2px);
}

@media (max-width: 900px) {
  .form-grid-3 { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 600px) {
  .form-grid-3 { grid-template-columns: 1fr; }
  .form-section { padding: 1rem; }
  .main-content { padding: 1rem; }
}
</style>