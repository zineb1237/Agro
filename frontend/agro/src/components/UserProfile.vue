<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import NavBar from '@/components/NavBar.vue'

// ========== PROFIL UTILISATEUR (données dynamiques depuis API) ==========
const profileData = reactive({
  id: null,
  prenom: '',
  nom: '',
  email: '',
  telephone: '',
  adresse: '',
  ville: '',
  role: 'agriculteur',     // 'agriculteur' ou 'fournisseur'
  photo: null,             // URL ou base64
  // Pour agriculteur
  typeExploitation: '',
  surfaceTotale: null,
  // Pour fournisseur
  nomEntreprise: '',
  tva: '',
  categoriesProduits: []
})

const stats = reactive({
  totalYieldPredictions: 0,
  diseasesDetected: 0,
  ordersPlaced: 0
})

const isEditing = ref(false)
const history = ref([])
const photoPreview = ref(null)
const loading = ref(true)

// Toast State
const toast = reactive({
  visible: false,
  message: '',
  type: 'success'
})

const showToast = (message, type = 'success') => {
  toast.message = message
  toast.type = type
  toast.visible = true
  setTimeout(() => {
    toast.visible = false
  }, 3000)
}

// Options pour catégories (fournisseur)
const availableCategories = [
  'Semences', 'Engrais', 'Matériel agricole', 'Irrigation',
  'Produits phytosanitaires', 'Alimentation animale', 'Pièces de rechange'
]

// ========== APPELS API ==========
const fetchUserProfile = async () => {
  try {
    const token = localStorage.getItem('token')
    const response = await fetch('http://localhost:8000/api/user/profile', {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    })
    if (!response.ok) throw new Error('Erreur chargement profil')
    const data = await response.json()
    // Remplir les données reçues
    Object.assign(profileData, data)
    if (data.photo) photoPreview.value = data.photo
  } catch (error) {
    console.error('Erreur fetch profil:', error)
    // Fallback données mockées si API indisponible (pour test)
    profileData.prenom = 'Jean'
    profileData.nom = 'Dupont'
    profileData.email = 'jean.dupont@agri.fr'
    profileData.telephone = '+212 6 12 34 56 78'
    profileData.adresse = '123 Rue des Champs'
    profileData.ville = 'Dijon'
    profileData.role = 'agriculteur'
    profileData.typeExploitation = 'Polyculture et élevage'
    profileData.surfaceTotale = 125.5
  } finally {
    loading.value = false
  }
}

const updateUserProfile = async () => {
  try {
    const token = localStorage.getItem('token')
    // Envoyer toutes les données modifiées (sauf photo si gérée séparément)
    const payload = {
      prenom: profileData.prenom,
      nom: profileData.nom,
      email: profileData.email,
      telephone: profileData.telephone,
      adresse: profileData.adresse,
      ville: profileData.ville,
      role: profileData.role,
      typeExploitation: profileData.typeExploitation,
      surfaceTotale: profileData.surfaceTotale,
      nomEntreprise: profileData.nomEntreprise,
      tva: profileData.tva,
      categoriesProduits: profileData.categoriesProduits
    }
    const response = await fetch('http://localhost:8000/api/user/profile', {
      method: 'PUT',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify(payload)
    })
    if (!response.ok) throw new Error('Erreur mise à jour')
    const result = await response.json()
    if (result.success) {
      // Recharger le profil pour être à jour
      await fetchUserProfile()
      showToast('Les données sont modifiées')
    }
  } catch (error) {
    console.error('Erreur update profil:', error)
    alert('Erreur lors de la sauvegarde')
  }
}

// Gestion de la photo
const triggerPhotoUpload = () => {
  document.getElementById('hiddenPhotoInput').click()
}

const handlePhotoUpload = async (event) => {
  const file = event.target.files[0]
  if (!file) return
  
  // Option 1 : envoyer l'image directement au serveur (recommandé)
  const formData = new FormData()
  formData.append('photo', file)
  
  try {
    const token = localStorage.getItem('token')
    const response = await fetch('http://localhost:8000/api/user/photo', {
      method: 'POST',
      headers: { 'Authorization': `Bearer ${token}` },
      body: formData
    })
    const result = await response.json()
    if (result.success) {
      profileData.photo = result.photo_url
      photoPreview.value = result.photo_url
    } else {
      throw new Error('Upload échoué')
    }
  } catch (error) {
    console.error('Erreur upload photo:', error)
    // Fallback : stockage en base64 localement (si pas d'API upload)
    const reader = new FileReader()
    reader.onload = (e) => {
      profileData.photo = e.target.result
      photoPreview.value = e.target.result
    }
    reader.readAsDataURL(file)
  }
}

const removePhoto = async () => {
  try {
    const token = localStorage.getItem('token')
    await fetch('http://localhost:8000/api/user/photo', {
      method: 'DELETE',
      headers: { 'Authorization': `Bearer ${token}` }
    })
    profileData.photo = null
    photoPreview.value = null
  } catch (error) {
    // Fallback local
    profileData.photo = null
    photoPreview.value = null
  }
  const input = document.getElementById('hiddenPhotoInput')
  if (input) input.value = ''
}

// Sauvegarde des modifications texte
const toggleEdit = async () => {
  if (isEditing.value) {
    await updateUserProfile()
  }
  isEditing.value = !isEditing.value
}

// Chargement stats et historique (inchangé, peut aussi venir d'API)
const loadStatsAndHistory = () => {
  const yieldHistory = JSON.parse(localStorage.getItem('falah_yield_history') || '[]')
  const diseaseHistory = JSON.parse(localStorage.getItem('falah_disease_history') || '[]')
  stats.totalYieldPredictions = yieldHistory.length
  stats.diseasesDetected = diseaseHistory.length
  
  history.value = [
    ...yieldHistory.map(h => ({ ...h, type: 'yield', icon: 'fa-chart-line', color: '#3D6B35' })),
    ...diseaseHistory.map(h => ({ ...h, type: 'disease', icon: 'fa-microscope', color: '#e53935' }))
  ].sort((a, b) => new Date(b.date) - new Date(a.date)).slice(0, 5)
}

onMounted(async () => {
  loading.value = true
  await fetchUserProfile()
  loadStatsAndHistory()
  loading.value = false
})

const formatDate = (dateStr) => {
  return new Date(dateStr).toLocaleDateString('fr-FR', {
    day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit'
  })
}

const fullName = computed(() => `${profileData.prenom} ${profileData.nom}`)
const isAgriculteur = computed(() => profileData.role === 'agriculteur')
const isFournisseur = computed(() => profileData.role === 'fournisseur')
</script>

<template>
  <div class="profile-page">
    <NavBar />
    <div class="main-content">
      <!-- Toast Notification -->
      <div v-if="toast.visible" class="toast-notification" :class="toast.type">
        <i class="fas" :class="toast.type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'"></i>
        {{ toast.message }}
      </div>

      <div class="profile-container">
        <div v-if="loading" class="loading">Chargement de votre profil...</div>
        <div v-else class="profile-grid">
          <!-- Carte principale -->
          <div class="card info-card">
            <div class="profile-header">
              <!-- Avatar cliquable -->
              <div class="avatar-large" @click="triggerPhotoUpload">
                <img v-if="photoPreview" :src="photoPreview" class="avatar-img" />
                <i v-else class="fas fa-user-circle"></i>
              
                <div class="photo-overlay">
                  <i class="fas fa-camera"></i>
                </div>
               
              </div>
              <div class="header-text">
                <h2>{{ fullName }}</h2>
                <p><i class="fas fa-map-marker-alt"></i> {{ profileData.adresse }}{{ profileData.ville ? ', ' + profileData.ville : '' }}</p>
                <p class="role-badge" :class="profileData.role">
                  <i class="fas" :class="profileData.role === 'agriculteur' ? 'fa-tractor' : 'fa-truck'"></i>
                  {{ profileData.role === 'agriculteur' ? 'Agriculteur' : 'Fournisseur' }}
                </p>
              </div>
              <button @click="toggleEdit" class="btn-edit" :class="{ 'is-active': isEditing }">
                <i class="fas" :class="isEditing ? 'fa-check' : 'fa-pen'"></i>
                {{ isEditing ? 'Enregistrer' : 'Modifier' }}
              </button>
            </div>

            <!-- Input caché pour upload photo -->
            <input type="file" id="hiddenPhotoInput" accept="image/*" style="display: none" @change="handlePhotoUpload" />

            <div class="profile-details">
              <div class="detail-item">
                <label>Prénom</label>
                <input v-if="isEditing" v-model="profileData.prenom" type="text" />
                <span v-else>{{ profileData.prenom }}</span>
              </div>
              <div class="detail-item">
                <label>Nom</label>
                <input v-if="isEditing" v-model="profileData.nom" type="text" />
                <span v-else>{{ profileData.nom }}</span>
              </div>
              <div class="detail-item">
                <label>Email</label>
                <input v-if="isEditing" v-model="profileData.email" type="email" />
                <span v-else>{{ profileData.email }}</span>
              </div>
              <div class="detail-item">
                <label>Téléphone</label>
                <input v-if="isEditing" v-model="profileData.telephone" type="tel" />
                <span v-else>{{ profileData.telephone }}</span>
              </div>
              <div class="detail-item">
                <label>Adresse</label>
                <input v-if="isEditing" v-model="profileData.adresse" type="text" />
                <span v-else>{{ profileData.adresse }}</span>
              </div>
              <div class="detail-item">
                <label>Ville</label>
                <input v-if="isEditing" v-model="profileData.ville" type="text" />
                <span v-else>{{ profileData.ville }}</span>
              </div>
              <div class="detail-item">
                <label>Rôle</label>
                <select v-if="isEditing" v-model="profileData.role">
                  <option value="agriculteur">Agriculteur</option>
                  <option value="fournisseur">Fournisseur</option>
                </select>
                <span v-else>{{ profileData.role === 'agriculteur' ? 'Agriculteur' : 'Fournisseur' }}</span>
              </div>

              <!-- Champs spécifiques agriculteur -->
              <template v-if="isAgriculteur">
                <div class="detail-item">
                  <label>Type d'exploitation</label>
                  <input v-if="isEditing" v-model="profileData.typeExploitation" type="text" />
                  <span v-else>{{ profileData.typeExploitation || 'Non renseigné' }}</span>
                </div>
                <div class="detail-item">
                  <label>Surface totale (ha)</label>
                  <input v-if="isEditing" v-model="profileData.surfaceTotale" type="number" step="0.1" />
                  <span v-else>{{ profileData.surfaceTotale ? profileData.surfaceTotale + ' ha' : 'Non renseigné' }}</span>
                </div>
              </template>

              <!-- Champs spécifiques fournisseur -->
              <template v-if="isFournisseur">
                <div class="detail-item">
                  <label>Nom entreprise</label>
                  <input v-if="isEditing" v-model="profileData.nomEntreprise" type="text" />
                  <span v-else>{{ profileData.nomEntreprise || 'Non renseigné' }}</span>
                </div>
                <div class="detail-item">
                  <label>N° TVA</label>
                  <input v-if="isEditing" v-model="profileData.tva" type="text" />
                  <span v-else>{{ profileData.tva || 'Non renseigné' }}</span>
                </div>
                <div class="detail-item full-width">
                  <label>Catégories de produits</label>
                  <div v-if="isEditing" class="checkbox-group">
                    <label v-for="cat in availableCategories" :key="cat" class="checkbox-item">
                      <input type="checkbox" :value="cat" v-model="profileData.categoriesProduits" />
                      {{ cat }}
                    </label>
                  </div>
                  <div v-else>
                    <span class="categories-list">{{ profileData.categoriesProduits.length ? profileData.categoriesProduits.join(', ') : 'Aucune' }}</span>
                  </div>
                </div>
              </template>

              <div class="detail-item">
                <label>Membre depuis</label>
                <span>12 Mars 2024</span> <!-- À remplacer par date réelle si API fournit -->
              </div>
            </div>
          </div>

          <!-- Cartes statistiques (inchangées) -->
          <div class="stats-grid">
            <div class="stat-card">
              <i class="fas fa-seedling"></i>
              <div class="stat-info">
                <span class="stat-value">{{ stats.totalYieldPredictions }}</span>
                <span class="stat-label">Prédictions</span>
              </div>
            </div>
            <div class="stat-card alert">
              <i class="fas fa-shield-virus"></i>
              <div class="stat-info">
                <span class="stat-value">{{ stats.diseasesDetected }}</span>
                <span class="stat-label">Analyses santé</span>
              </div>
            </div>
            <div class="stat-card">
              <i class="fas fa-shopping-cart"></i>
              <div class="stat-info">
                <span class="stat-value">{{ stats.ordersPlaced }}</span>
                <span class="stat-label">Commandes</span>
              </div>
            </div>
          </div>

          <!-- Historique (inchangé) -->
          <div class="card history-card">
            <h3><i class="fas fa-history"></i> Activité récente</h3>
            <div v-if="history.length > 0" class="history-list">
              <div v-for="(item, index) in history" :key="index" class="history-item">
                <div class="item-icon" :style="{ backgroundColor: item.color + '20', color: item.color }">
                  <i class="fas" :class="item.icon"></i>
                </div>
                <div class="item-content">
                  <p v-if="item.type === 'yield'">
                    Estimation pour <strong>{{ item.variete }}</strong> : 
                    <strong>{{ item.result.total }} tonnes</strong>
                  </p>
                  <p v-else-if="item.type === 'disease'">
                    Analyse santé : <strong>{{ item.maladie }}</strong> détectée.
                  </p>
                  <span class="item-date">{{ formatDate(item.date) }}</span>
                </div>
              </div>
            </div>
            <div v-else class="empty-state">
              <p>Aucune activité récente pour le moment.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Les styles sont strictement identiques à la version précédente (je les conserve) */
.profile-page {
  display: flex;
  height: 100vh;
  width: 100%;
  background-color: #f5f0e8;
}
.main-content {
  flex: 1;
  overflow-y: auto;
  padding: 2rem;
}
.profile-container {
  max-width: 1200px;
  margin: 0 auto;
}
.profile-grid {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 2rem;
}
.card {
  background: white;
  border: 1px solid #e8ddd0;
  border-radius: 24px;
  padding: 2rem;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
}
.profile-header {
  display: flex;
  align-items: center;
  gap: 1.5rem;
  margin-bottom: 2.5rem;
  flex-wrap: wrap;
}
.avatar-large {
  position: relative;
  width: 100px;
  height: 100px;
  background: #f5f0e8;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 5rem;
  color: #3d6b35;
  overflow: hidden;
  cursor: pointer;
}
.avatar-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.photo-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: 0.2s;
  color: white;
  font-size: 1.8rem;
}
.avatar-large:hover .photo-overlay {
  opacity: 1;
}
.badge-online {
  position: absolute;
  bottom: 5px;
  right: 5px;
  width: 18px;
  height: 18px;
  background: #4caf50;
  border: 3px solid white;
  border-radius: 50%;
}
.btn-remove-photo {
  position: absolute;
  top: 0;
  right: 0;
  background: rgba(0,0,0,0.6);
  color: white;
  border: none;
  border-radius: 50%;
  width: 28px;
  height: 28px;
  cursor: pointer;
  font-size: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 5;
}
.btn-remove-photo:hover {
  background: #e53935;
}
.header-text h2 {
  margin: 0;
  font-size: 1.8rem;
  color: #2c1a0e;
}
.header-text p {
  margin: 0.2rem 0;
  color: #6b5744;
}
.role-badge {
  display: inline-block;
  padding: 0.2rem 0.8rem;
  border-radius: 30px;
  font-size: 0.7rem;
  font-weight: 600;
  background: #e8f5e9;
  color: #2e7d32;
}
.role-badge.fournisseur {
  background: #e3f2fd;
  color: #1565c0;
}
.btn-edit {
  margin-left: auto;
  padding: 0.6rem 1.2rem;
  border-radius: 40px;
  border: 1px solid #e8ddd0;
  background: white;
  cursor: pointer;
  font-weight: 600;
  transition: 0.2s;
}
.btn-edit:hover {
  border-color: #3d6b35;
  color: #3d6b35;
}
.btn-edit.is-active {
  background: #3d6b35;
  color: white;
  border-color: #3d6b35;
}
.profile-details {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.2rem;
}
.detail-item {
  display: flex;
  flex-direction: column;
  gap: 0.3rem;
}
.full-width {
  grid-column: span 2;
}
.detail-item label {
  font-size: 0.7rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: #8b7a6b;
}
.detail-item span {
  font-weight: 500;
  font-size: 0.95rem;
}
.detail-item input, .detail-item select {
  padding: 0.6rem 0.8rem;
  border: 1px solid #e8ddd0;
  border-radius: 12px;
  font-family: inherit;
}
.checkbox-group {
  display: flex;
  flex-wrap: wrap;
  gap: 0.8rem;
  margin-top: 0.3rem;
}
.checkbox-item {
  font-size: 0.8rem;
  display: flex;
  align-items: center;
  gap: 0.4rem;
}
.categories-list {
  background: #f5f0e8;
  padding: 0.2rem 0.6rem;
  border-radius: 20px;
  display: inline-block;
}
.stats-grid {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}
.stat-card {
  background: white;
  border: 1px solid #e8ddd0;
  border-radius: 20px;
  padding: 1.2rem;
  display: flex;
  align-items: center;
  gap: 1.2rem;
  transition: 0.2s;
}
.stat-card:hover {
  transform: translateY(-4px);
  border-color: #c9a87b;
}
.stat-card i {
  font-size: 2rem;
  color: #3d6b35;
  background: #f5f0e8;
  width: 55px;
  height: 55px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 16px;
}
.stat-card.alert i {
  color: #e53935;
  background: #fff4f4;
}
.stat-info {
  flex: 1;
}
.stat-value {
  font-size: 1.6rem;
  font-weight: 800;
  color: #2c1a0e;
}
.stat-label {
  font-size: 0.7rem;
  color: #8b7a6b;
}
.history-card {
  grid-column: span 2;
}
.history-list {
  display: flex;
  flex-direction: column;
  gap: 0.8rem;
}
.history-item {
  display: flex;
  gap: 1rem;
  padding: 0.8rem;
  background: #faf7f0;
  border-radius: 16px;
  align-items: center;
}
.item-icon {
  width: 42px;
  height: 42px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
}
.item-content p {
  margin: 0;
  font-size: 0.85rem;
}
.item-date {
  font-size: 0.7rem;
  color: #8b7a6b;
}
.empty-state {
  text-align: center;
  padding: 2rem;
  color: #aa9a88;
}
.loading {
  text-align: center;
  padding: 3rem;
  font-size: 1.1rem;
  color: #7c6b5a;
}
@media (max-width: 850px) {
  .profile-grid {
    grid-template-columns: 1fr;
  }
  .history-card {
    grid-column: span 1;
  }
  .profile-details {
    grid-template-columns: 1fr;
  }
  .full-width {
    grid-column: span 1;
  }
  .main-content {
    padding: 1rem;
  }
}
@media (max-width: 600px) {
  .profile-header {
    flex-direction: column;
    text-align: center;
  }
  .btn-edit {
    margin-left: 0;
  }
}

/* Toast Styles */
.toast-notification {
  position: fixed;
  top: 20px;
  right: 20px;
  padding: 1rem 1.5rem;
  border-radius: 12px;
  background: white;
  color: #333;
  display: flex;
  align-items: center;
  gap: 0.8rem;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
  z-index: 9999;
  font-weight: 600;
  animation: slideInRight 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
}

.toast-notification.success {
  border-left: 5px solid #4caf50;
}
.toast-notification.success i {
  color: #4caf50;
  font-size: 1.2rem;
}

.toast-notification.error {
  border-left: 5px solid #f44336;
}
.toast-notification.error i {
  color: #f44336;
  font-size: 1.2rem;
}

@keyframes slideInRight {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}
</style>