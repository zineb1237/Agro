<script setup>
import { ref, reactive, onMounted } from 'vue'

const profileData = reactive({
  nom: 'Jean Dupont',
  email: 'jean.dupont@agri.fr',
  localisation: 'Bourgogne-Franche-Comté, France',
  typeExploitation: 'Poly-culture et Élevage',
  surfaceTotale: 125.5,
  dateInscription: '12 Mars 2024'
})

const stats = reactive({
  totalYieldPredictions: 0,
  diseasesDetected: 0,
  ordersPlaced: 0
})

const isEditing = ref(false)
const history = ref([])

onMounted(() => {
  // Charger les stats depuis le localStorage
  const yieldHistory = JSON.parse(localStorage.getItem('falah_yield_history') || '[]')
  const diseaseHistory = JSON.parse(localStorage.getItem('falah_disease_history') || '[]')
  //const cartHistory = JSON.parse(localStorage.getItem('falah_cart_history') || '[]') // On pourrait ajouter un historique de commandes réel

  stats.totalYieldPredictions = yieldHistory.length
  stats.diseasesDetected = diseaseHistory.length
  
  // Fusionner l'historique pour l'affichage
  history.value = [
    ...yieldHistory.map(h => ({ ...h, type: 'yield', icon: 'fa-chart-line', color: 'var(--leaf-color)' })),
    ...diseaseHistory.map(h => ({ ...h, type: 'disease', icon: 'fa-microscope', color: 'var(--alert-color)' }))
  ].sort((a, b) => new Date(b.date) - new Date(a.date)).slice(0, 5)
})

const toggleEdit = () => {
  isEditing.value = !isEditing.value
  if (!isEditing.value) {
    // Sauvegarder les modifs (simulation)
    localStorage.setItem('falah_user_profile', JSON.stringify(profileData))
  }
}

const formatDate = (dateStr) => {
  return new Date(dateStr).toLocaleDateString('fr-FR', {
    day: 'numeric',
    month: 'short',
    hour: '2-digit',
    minute: '2-digit'
  })
}
</script>

<template>
  <div class="profile-container">
    <div class="profile-grid">
      <!-- Info Card -->
      <div class="card info-card">
        <div class="profile-header">
          <div class="avatar-large">
            <i class="fas fa-user-circle"></i>
            <div class="badge-online"></div>
          </div>
          <div class="header-text">
            <h2>{{ profileData.nom }}</h2>
            <p><i class="fas fa-map-marker-alt"></i> {{ profileData.localisation }}</p>
          </div>
          <button @click="toggleEdit" class="btn-edit" :class="{ 'is-active': isEditing }">
            <i class="fas" :class="isEditing ? 'fa-check' : 'fa-pen'"></i>
            {{ isEditing ? 'Enregistrer' : 'Modifier' }}
          </button>
        </div>

        <div class="profile-details">
          <div class="detail-item">
            <label>Email professionnel</label>
            <input v-if="isEditing" v-model="profileData.email" type="email" />
            <span v-else>{{ profileData.email }}</span>
          </div>
          <div class="detail-item">
            <label>Type d'exploitation</label>
            <input v-if="isEditing" v-model="profileData.typeExploitation" type="text" />
            <span v-else>{{ profileData.typeExploitation }}</span>
          </div>
          <div class="detail-item">
            <label>Surface totale (ha)</label>
            <input v-if="isEditing" v-model="profileData.surfaceTotale" type="number" step="0.1" />
            <span v-else>{{ profileData.surfaceTotale }} hectares</span>
          </div>
          <div class="detail-item">
            <label>Membre depuis</label>
            <span>{{ profileData.dateInscription }}</span>
          </div>
        </div>
      </div>

      <!-- Stats Cards -->
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
      </div>

      <!-- History Section -->
      <div class="card history-card">
        <h3><i class="fas fa-history"></i> Activité récente</h3>
        <div v-if="history.length > 0" class="history-list">
          <div v-for="(item, index) in history" :key="index" class="history-item">
            <div class="item-icon" :style="{ backgroundColor: item.color + '20', color: item.color }">
              <i class="fas" :class="item.icon"></i>
            </div>
            <div class="item-content">
              <p v-if="item.type === 'yield'">
                Estimation pour <strong>{{ item.type }}</strong> ({{ item.variete }}) : 
                <strong>{{ item.result.total }} tonnes</strong>
              </p>
              <p v-else-if="item.type === 'disease'">
                Analyse santé effectuée : <strong>{{ item.maladie }}</strong> détectée.
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
</template>

<style scoped>
.profile-container {
  max-width: 1000px;
  margin: 0 auto;
}

.profile-grid {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 2rem;
}

.card {
  background: var(--card-bg);
  border: 1px solid var(--border-color);
  border-radius: 16px;
  padding: 2rem;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
}

/* Info Card */
.profile-header {
  display: flex;
  align-items: center;
  gap: 1.5rem;
  margin-bottom: 2.5rem;
  position: relative;
}

.avatar-large {
  position: relative;
  font-size: 5rem;
  color: var(--leaf-color);
  background: var(--cream-color);
  width: 100px;
  height: 100px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
}

.badge-online {
  position: absolute;
  bottom: 5px;
  right: 5px;
  width: 18px;
  height: 18px;
  background: #4CAF50;
  border: 3px solid white;
  border-radius: 50%;
}

.header-text h2 {
  margin: 0;
  font-size: 1.8rem;
  color: var(--soil-color);
}

.header-text p {
  color: var(--text-muted);
  margin: 0.3rem 0 0 0;
}

.btn-edit {
  margin-left: auto;
  padding: 0.6rem 1.2rem;
  border-radius: 30px;
  border: 1px solid var(--border-color);
  background: white;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.6rem;
  font-weight: 600;
  transition: all 0.3s ease;
}

.btn-edit:hover {
  border-color: var(--leaf-color);
  color: var(--leaf-color);
}

.btn-edit.is-active {
  background: var(--leaf-color);
  color: white;
  border-color: var(--leaf-color);
}

.profile-details {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 2rem;
}

.detail-item {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.detail-item label {
  font-size: 0.85rem;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.detail-item span {
  font-weight: 600;
  font-size: 1.1rem;
}

.detail-item input {
  padding: 0.6rem;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  font-family: inherit;
  font-size: 1rem;
}

/* Stats */
.stats-grid {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.stat-card {
  background: white;
  border: 1px solid var(--border-color);
  border-radius: 16px;
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1.5rem;
  transition: transform 0.3s ease;
}

.stat-card:hover {
  transform: translateY(-5px);
  border-color: var(--leaf-color);
}

.stat-card i {
  font-size: 2.5rem;
  color: var(--leaf-color);
  background: var(--cream-color);
  width: 60px;
  height: 60px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 12px;
}

.stat-card.alert i {
  color: var(--alert-color);
  background: #FFF5F4;
}

.stat-info {
  display: flex;
  flex-direction: column;
}

.stat-value {
  font-size: 1.8rem;
  font-weight: 800;
  color: var(--soil-color);
}

.stat-label {
  color: var(--text-muted);
  font-size: 0.9rem;
}

/* History */
.history-card {
  grid-column: span 2;
}

.history-card h3 {
  margin-bottom: 1.5rem;
  display: flex;
  align-items: center;
  gap: 0.8rem;
}

.history-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.history-item {
  display: flex;
  gap: 1.2rem;
  padding: 1rem;
  border-radius: 12px;
  background: var(--cream-color);
  align-items: center;
}

.item-icon {
  width: 45px;
  height: 45px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
}

.item-content p {
  margin: 0;
  font-size: 0.95rem;
}

.item-date {
  font-size: 0.8rem;
  color: var(--text-muted);
}

.empty-state {
  text-align: center;
  padding: 3rem;
  color: var(--text-muted);
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
}
</style>
