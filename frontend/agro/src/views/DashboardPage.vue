<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import { useRouter } from 'vue-router'
import NavBar from '@/components/NavBar.vue'
import mqtt from 'mqtt/dist/mqtt.min.js'

const router = useRouter()

// ---------- DONNÉES UTILISATEUR ----------
const userName = ref('Agriculteur')
const userVille = ref('')

// ---------- MÉTÉO ----------
const meteo = ref({ temperature: null, description: '', icone: '☀️' })
const isLoadingMeteo = ref(false)

function getWeatherIcon(code) {
  if (code === 0) return '☀️'
  if (code === 1 || code === 2) return '🌤️'
  if (code === 3) return '☁️'
  if (code >= 45 && code <= 48) return '🌫️'
  if (code >= 51 && code <= 67) return '🌧️'
  if (code >= 71 && code <= 77) return '❄️'
  if (code >= 80 && code <= 99) return '⛈️'
  return '🌡️'
}

function getDescriptionFromCode(code) {
  if (code === 0) return 'Ensoleillé'
  if (code === 1 || code === 2) return 'Peu nuageux'
  if (code === 3) return 'Nuageux'
  if (code >= 51 && code <= 67) return 'Pluie'
  if (code >= 71 && code <= 77) return 'Neige'
  if (code >= 80 && code <= 99) return 'Orage'
  return 'Clair'
}

async function geocodeVille(nomVille) {
  const url = `https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(nomVille)}&format=json&limit=1`
  const response = await fetch(url, { headers: { 'User-Agent': 'Fellah-App/1.0' } })
  const data = await response.json()
  if (data && data.length > 0) {
    return { lat: parseFloat(data[0].lat), lon: parseFloat(data[0].lon) }
  }
  throw new Error('Ville non trouvée')
}

async function fetchWeatherForCity(nomVille) {
  if (!nomVille) return
  isLoadingMeteo.value = true
  try {
    const coords = await geocodeVille(nomVille)
    const url = `https://api.open-meteo.com/v1/forecast?latitude=${coords.lat}&longitude=${coords.lon}&current_weather=true&timezone=auto`
    const response = await fetch(url)
    const data = await response.json()
    if (data.current_weather) {
      const temp = Math.round(data.current_weather.temperature)
      const code = data.current_weather.weathercode
      meteo.value = { temperature: temp, description: getDescriptionFromCode(code), icone: getWeatherIcon(code) }
    } else throw new Error()
  } catch {
    meteo.value = { temperature: '?', description: 'Indisponible', icone: '⚠️' }
  } finally {
    isLoadingMeteo.value = false
  }
}

// ---------- DATE, SAISON ET STADE ----------
const currentDate = ref('')
const currentSaison = ref('')
const currentStade = ref('Floraison')

function getSaison(date) {
  const month = date.getMonth()
  const day = date.getDate()
  if ((month === 2 && day >= 20) || month === 3 || month === 4 || (month === 5 && day <= 20)) return 'Printemps'
  if ((month === 5 && day >= 21) || month === 6 || month === 7 || (month === 8 && day <= 22)) return 'Été'
  if ((month === 8 && day >= 23) || month === 9 || month === 10 || (month === 11 && day <= 21)) return 'Automne'
  return 'Hiver'
}

function updateDateAndSaison() {
  const now = new Date()
  const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }
  currentDate.value = now.toLocaleDateString('fr-FR', options).charAt(0).toUpperCase() + now.toLocaleDateString('fr-FR', options).slice(1)
  currentSaison.value = getSaison(now)
}

// ---------- DONNÉES AGRICOLES ----------
const parcelles = ref([])
const capteurs = ref({})
const weatherByParcelle = ref({})

// Statistiques
const superficieActive = computed(() => {
  return parcelles.value.reduce((total, p) => total + (parseFloat(p.superficie) || 0), 0).toFixed(1)
})
const rendementPrevu = ref(3.2)
const humiditeSolMoy = computed(() => {
  const values = Object.values(capteurs.value).map(c => c.humidite).filter(v => v != null)
  return values.length ? Math.round(values.reduce((a,b)=>a+b,0)/values.length) : 68
})

// ========== PRÉVISIONS PLUIE (API) ==========
const pluiePrevue = ref([]) // [{ date, total }]

async function fetchPluiePrevue(nomVille) {
  if (!nomVille) return
  try {
    const coords = await geocodeVille(nomVille)
    const url = `https://api.open-meteo.com/v1/forecast?latitude=${coords.lat}&longitude=${coords.lon}&daily=precipitation_sum&timezone=auto&forecast_days=4`
    const response = await fetch(url)
    const data = await response.json()
    if (data.daily && data.daily.precipitation_sum) {
      const today = new Date().toISOString().split('T')[0]
      const futures = data.daily.time.map((date, idx) => ({
        date: date,
        total: data.daily.precipitation_sum[idx]
      })).filter(d => d.date > today && d.total > 0)
      pluiePrevue.value = futures
    }
  } catch (error) {
    console.error('Erreur récupération pluie', error)
    pluiePrevue.value = []
  }
}
const alertesTrendMessage = computed(() => {
  if (alertesActives.value.length === 0) return '✓ Aucune alerte'
  // Prend la première alerte (la plus récente ou la plus critique)
  const first = alertesActives.value[0]
  // Exemple : "↓ Humidité critique — Parcelle A1"
  return `↓ ${first.titre}`
})
// ========== ALERTES ACTIVES (intégrant toutes les conditions) ==========
const alertesActives = computed(() => {
  const alerts = []
  
  // 1. Alertes capteurs (pH, humidité, température sol, conductivité)
  for (const [id, c] of Object.entries(capteurs.value)) {
    const parcelle = parcelles.value.find(p => p.id == id)
    const nomParcelle = parcelle?.nom || `Parcelle ${id}`
    
    if (c.pH !== undefined && c.pH !== null) {
      if (c.pH < 6.0) alerts.push({ type: '🧪', titre: `pH trop acide — ${nomParcelle}`, message: `pH = ${c.pH} (seuil min 6.0) · Ajouter de la chaux dolomitique` })
      else if (c.pH > 7.5) alerts.push({ type: '🧪', titre: `pH trop alcalin — ${nomParcelle}`, message: `pH = ${c.pH} (seuil max 7.5) · Incorporer du soufre` })
    }
    
    if (c.humidite !== undefined && c.humidite !== null) {
      if (c.humidite < 50) alerts.push({ type: '💧', titre: `Stress hydrique — ${nomParcelle}`, message: `Humidité = ${c.humidite}% · Irriguer immédiatement` })
      else if (c.humidite > 70) alerts.push({ type: '💧', titre: `Asphyxie racinaire — ${nomParcelle}`, message: `Humidité = ${c.humidite}% · Stopper irrigation, aérer` })
    }
    
    if (c.temperature !== undefined && c.temperature !== null) {
      if (c.temperature > 28) alerts.push({ type: '🌡️', titre: `Stress thermique — ${nomParcelle}`, message: `Température sol = ${c.temperature}°C · Arroser tôt matin / ombrage` })
    }
    
    if (c.conductivite !== undefined && c.conductivite !== null && c.conductivite > 0.8) {
      alerts.push({ type: '⚡', titre: `Salinité excessive — ${nomParcelle}`, message: `Conductivité = ${c.conductivite} µS/cm · Lessiver le sol` })
    }
  }
  
  // 2. Alerte température air élevée
  const maxTempAir = Math.max(...Object.values(weatherByParcelle.value).map(w => w.airTemp || 0), meteo.value.temperature || 0)
  if (maxTempAir > 35) {
    alerts.push({ type: '🌡️', titre: 'Pic de température prévu', message: `${Math.round(maxTempAir)}°C · Risque stress hydrique` })
  }
  
  // 3. Alerte pluie réelle (API)
  const pluieProche = pluiePrevue.value.find(p => {
    const diff = (new Date(p.date) - new Date()) / (1000 * 60 * 60 * 24)
    return diff <= 3 && diff > 0
  })
  if (pluieProche && pluieProche.total > 5) {
    alerts.push({ type: '🌧️', titre: 'Pluie prévue prochainement', message: `${pluieProche.total.toFixed(1)} mm estimés dans les 3 jours · Reporter irrigation` })
  } else if (pluieProche && pluieProche.total > 0 && pluieProche.total <= 5) {
    alerts.push({ type: '🌧️', titre: 'Légère pluie annoncée', message: `${pluieProche.total.toFixed(1)} mm · Surveillance possible` })
  }
  
  // Suppression des doublons
  const unique = []
  const seen = new Set()
  for (const a of alerts) {
    const key = `${a.titre}-${a.message}`
    if (!seen.has(key)) {
      seen.add(key)
      unique.push(a)
    }
  }
  return unique.slice(0, 4)
})

const alertesCount = computed(() => alertesActives.value.length)

// Graphique rendement
const rendementMensuel = ref([2.8, 2.9, 3.1, 3.0, 3.2, 3.4])
const moisLabels = ['Oct', 'Nov', 'Déc', 'Jan', 'Fév', 'Mar']

// ---------- MQTT ----------
let mqttClient = null

function initMQTT() {
  mqttClient = mqtt.connect('ws://broker.hivemq.com:8000/mqtt')
  mqttClient.on('connect', () => {
    console.log('✅ MQTT Dashboard connecté')
    mqttClient.subscribe('agriculture/parcelle1/sensors')
    mqttClient.subscribe('agriculture/parcelle2/sensors')
    mqttClient.subscribe('agriculture/parcelle3/sensors')
  })
  mqttClient.on('message', (topic, message) => {
    try {
      const payload = JSON.parse(message.toString())
      let parcelleId = null
      if (topic === 'agriculture/parcelle1/sensors') parcelleId = 1
      else if (topic === 'agriculture/parcelle2/sensors') parcelleId = 2
      else if (topic === 'agriculture/parcelle3/sensors') parcelleId = 3
      if (parcelleId && payload) {
        capteurs.value[parcelleId] = {
          humidite: payload.humidite,
          temperature: payload.temperature,
          conductivite: payload.conductivite,
          pH: payload.pH,
          N: payload.N,
          P: payload.P,
          K: payload.K
        }
      }
    } catch (err) { console.error(err) }
  })
  mqttClient.on('error', (err) => { console.error(err); startFallbackSimulation() })
}

let fallbackInterval = null
function startFallbackSimulation() {
  if (fallbackInterval) clearInterval(fallbackInterval)
  const update = () => {
    for (let p of parcelles.value) {
      capteurs.value[p.id] = {
        humidite: Math.floor(Math.random() * 40 + 40),
        temperature: Math.floor(Math.random() * 15 + 18),
        conductivite: Math.random() * 1.5 + 0.2,
        pH: (Math.random() * 2 + 5.5).toFixed(1),
        N: Math.floor(Math.random() * 50 + 20),
        P: Math.floor(Math.random() * 50 + 20),
        K: Math.floor(Math.random() * 50 + 20)
      }
    }
  }
  update()
  fallbackInterval = setInterval(update, 15000)
}

watch(humiditeSolMoy, (newVal) => {
  if (newVal > 60) rendementPrevu.value = 3.5
  else if (newVal > 40) rendementPrevu.value = 3.2
  else rendementPrevu.value = 2.8
}, { immediate: true })

// ---------- CHARGEMENT PARCELLES ----------
const fetchParcelles = async () => {
  try {
    const token = localStorage.getItem('token')
    const response = await fetch('http://localhost:8000/api/parcelles', { headers: { 'Authorization': `Bearer ${token}` } })
    const data = await response.json()
    parcelles.value = data.map(p => ({ ...p, lat: p.localisation?.split(',')[0], lng: p.localisation?.split(',')[1] }))
  } catch {
    parcelles.value = [
      { id: 1, nom: 'Parcelle A1', superficie: 2.5, culture: 'Blé' },
      { id: 2, nom: 'Parcelle B2', superficie: 1.5, culture: 'Maïs' }
    ]
  }
}

const fetchWeatherForParcelles = async () => {
  for (let p of parcelles.value) {
    if (p.lat && p.lng) {
      try {
        const url = `https://api.open-meteo.com/v1/forecast?latitude=${p.lat}&longitude=${p.lng}&current=temperature_2m&timezone=auto`
        const res = await fetch(url)
        const data = await res.json()
        if (data.current) weatherByParcelle.value[p.id] = { airTemp: data.current.temperature_2m }
      } catch {}
    }
  }
}

// ---------- TÂCHES DEPUIS CALENDRIER ----------
const taches = ref([])
const tasksCompleted = ref({})

const fetchTachesFromCalendar = async () => {
  try {
    const token = localStorage.getItem('token')
    const response = await fetch('http://localhost:8000/api/calendar-events', { headers: { 'Authorization': `Bearer ${token}` } })
    if (!response.ok) throw new Error()
    const events = await response.json()
    const today = new Date()
    const tomorrow = new Date()
    tomorrow.setDate(today.getDate() + 1)
    const formatDate = (d) => d.toISOString().split('T')[0]
    const todayStr = formatDate(today)
    const tomorrowStr = formatDate(tomorrow)
    const filtered = events.filter(ev => {
      if (!ev.start_date) return false;
      const datePart = ev.start_date.split('T')[0].split(' ')[0];
      return datePart === todayStr || datePart === tomorrowStr;
    })
    const saved = localStorage.getItem('tasks_completed')
    tasksCompleted.value = saved ? JSON.parse(saved) : {}
    taches.value = filtered.map(ev => {
      const datePart = ev.start_date.split('T')[0].split(' ')[0];
      return {
        id: ev.id,
        titre: ev.title,
        priorite: datePart === todayStr ? 'urgent' : 'normal',
        fait: tasksCompleted.value[ev.id] || false,
        date: datePart
      }
    })
  } catch (error) {
    console.error(error)
    taches.value = []
  }
}

const toggleTaskCompletion = (task) => {
  task.fait = !task.fait
  tasksCompleted.value[task.id] = task.fait
  localStorage.setItem('tasks_completed', JSON.stringify(tasksCompleted.value))
}

// ---------- AUTH ET INIT ----------
onMounted(async () => {
  updateDateAndSaison()
  const urlParams = new URLSearchParams(window.location.search)
  const tokenFromUrl = urlParams.get('token')
  const userFromUrl = urlParams.get('user')
  if (tokenFromUrl && userFromUrl) {
    localStorage.setItem('token', tokenFromUrl)
    localStorage.setItem('user', userFromUrl)
    await router.replace('/dashboard')
    return
  }
  const token = localStorage.getItem('token')
  const userStr = localStorage.getItem('user')
  if (!token) {
    router.push('/login')
    return
  }
  if (userStr) {
    try {
      const user = JSON.parse(userStr)
      userName.value = user.name || user.nom || 'Agriculteur'
      userVille.value = user.ville || ''
    } catch { router.push('/login') }
  }
  if (userVille.value) await fetchWeatherForCity(userVille.value)
  else {
    userVille.value = 'El Jadida'
    await fetchWeatherForCity('El Jadida')
  }
  await fetchParcelles()
  await fetchWeatherForParcelles()
  await fetchPluiePrevue(userVille.value)   // Récupère les prévisions pluie
  initMQTT()
  await fetchTachesFromCalendar()
})
</script>



<template>
  <div class="dashboard-container">
    <NavBar />
    <main class="dashboard-content">
      <!-- En-tête -->
      <div class="header">
        <h2>Tableau de bord</h2>
        <div class="weather-area">
          <div class="weather-info" v-if="!isLoadingMeteo && meteo.temperature !== null">
            <span class="weather-icon">{{ meteo.icone }}</span>
            <span class="weather-text">{{ userVille }} · {{ meteo.temperature }}°C · {{ meteo.description }}</span>
          </div>
          <div v-else-if="isLoadingMeteo" class="weather-info">Chargement météo...</div>
          <div v-else class="weather-info">Météo indisponible</div>
        </div>
      </div>

      <!-- Bienvenue -->
      <div class="welcome-section">
        <p class="bnj">Bonjour, {{ userName }} 👋</p>
        <p class="date-saison-stade">{{ currentDate }} · Saison : {{ currentSaison }} · Stade : {{ currentStade }}</p>
      </div>

      <!-- 4 cartes statistiques -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon">🌿</div>
          <div class="stat-content">
            <div class="stat-value">{{ superficieActive }} ha</div>
            <div class="stat-label">SUPERFICIE ACTIVE</div>
            <div class="stat-trend up">↑ +0.5 ha cette année</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon">📊</div>
          <div class="stat-content">
            <div class="stat-value">{{ rendementPrevu }} T/ha</div>
            <div class="stat-label">RENDEMENT PRÉVU</div>
            <div class="stat-trend up">↑ +12% vs 2025</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon">💧</div>
          <div class="stat-content">
            <div class="stat-value">{{ humiditeSolMoy }} %</div>
            <div class="stat-label">HUMIDITÉ SOL MOY.</div>
            <div class="stat-trend good">✓ Niveau optimal</div>
          </div>
        </div>
        <div class="stat-card">
  <div class="stat-icon">⚠️</div>
  <div class="stat-content">
    <div class="stat-value">{{ alertesCount }}</div>
    <div class="stat-label">ALERTES ACTIVES</div>
    <div class="stat-trend down">{{ alertesTrendMessage }}</div>
  </div>
</div>
      </div>

      <!-- Section principale 2 colonnes -->
      <div class="main-grid">
        <!-- Graphique rendement -->
        <div class="card graph-card">
          <div class="card-header">
            <h3>📈 Rendement — 6 derniers mois</h3>
            <button class="btn-link">Rapport →</button>
          </div>
          <div class="bar-chart">
            <div v-for="(val, idx) in rendementMensuel" :key="idx" class="bar-item">
              <div class="bar-label">{{ moisLabels[idx] }}</div>
              <div class="bar-container">
                <div class="bar-fill" :style="{ height: (val / 5 * 100) + '%' }"></div>
              </div>
              <div class="bar-value">{{ val.toFixed(1) }}</div>
            </div>
          </div>
        </div>

        <!-- Capteurs sol (affichage hum, pH, temp, cond) -->
        <div class="card sensors-card">
          <div class="card-header">
            <h3>🌡️ Capteurs sol</h3>
            <!-- Bouton pour forcer l'actualisation manuelle (optionnel) -->
            <button class="btn-link" @click="initMQTT">Actualiser</button>
          </div>
          <div class="sensors-grid">
            <div class="sensor-item">
              <div class="sensor-icon">💧</div>
              <div class="sensor-detail">
                <label>Humidité</label>
                <span class="sensor-value">{{ humiditeSolMoy }} %</span>
              </div>
            </div>
            <div class="sensor-item">
              <div class="sensor-icon">🧪</div>
              <div class="sensor-detail">
                <label>pH sol</label>
                <span class="sensor-value">{{ capteurs[1]?.pH || capteurs[3]?.pH || '6.8' }} pH</span>
              </div>
            </div>
            <div class="sensor-item">
              <div class="sensor-icon">🌡️</div>
              <div class="sensor-detail">
                <label>Température sol</label>
                <span class="sensor-value">{{ capteurs[1]?.temperature || capteurs[3]?.temperature || '--' }} °C</span>
              </div>
            </div>
            <div class="sensor-item">
              <div class="sensor-icon">⚡</div>
              <div class="sensor-detail">
                <label>Conductivité</label>
                <span class="sensor-value">{{ capteurs[1]?.conductivite || capteurs[3]?.conductivite || '--' }} µS/cm</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Tâches du jour -->
      <!-- Tâches du jour -->
<div class="card tasks-card">
  <div class="card-header">
    <h3>✅ Tâches du jour</h3>
    <button class="btn-link" @click="fetchTachesFromCalendar">⟳ Actualiser</button>
  </div>
  <div class="tasks-list">
    <div v-for="tache in taches" :key="tache.id" class="task-item" :class="tache.priorite">
      <label class="task-checkbox">
        <input type="checkbox" v-model="tache.fait" @change="toggleTaskCompletion(tache)" />
        <span class="task-title" :class="{ 'task-done': tache.fait }">{{ tache.titre }}</span>
      </label>
      <span class="task-priority">{{ tache.priorite }}</span>
    </div>
    <div v-if="taches.length === 0" class="empty-tasks">Aucune tâche planifiée aujourd'hui ou demain</div>
  </div>
</div>

        <!-- Alertes actives -->
        <div class="card alerts-card">
          <div class="card-header">
            <h3>🔔 Alertes actives</h3>
            <router-link to="/alertes" class="btn-link">Tout voir →</router-link>
          </div>
          <div class="alerts-list">
            <div v-for="(alert, idx) in alertesActives" :key="idx" class="alert-item">
              <div class="alert-icon">{{ alert.type }}</div>
              <div class="alert-content">
                <div class="alert-title">{{ alert.titre }}</div>
                <div class="alert-message">{{ alert.message }}</div>
              </div>
            </div>
            <div v-if="alertesActives.length === 0" class="empty-alerts">Aucune alerte</div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<style scoped>
/* (tous vos styles existants, inchangés) */
.dashboard-container {
  display: flex;
  height: 100vh;
  width: 100%;
}
.dashboard-content {
  flex: 1;
  overflow-y: auto;
  padding: 0 1.5rem 1.5rem;
}
.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 0;
  border-bottom: 1px solid #e8ddd0;
  background: transparent;
}
.weather-info {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #f0f4ea;
  padding: 5px 12px;
  border-radius: 30px;
  font-size: 0.85rem;
  font-weight: 500;
}
.welcome-section {
  margin-top: -10px;
}
.bnj {
  font-size: 1.6rem;
  font-weight: bold;
  margin-bottom: 0.2rem;
  color: #1a4d2e;
}
.date-saison-stade {
  font-size: 0.9rem;
  color: #5b7a4e;
}
.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1.2rem;
  margin: 1.5rem 0;
}
.stat-card {
  background: #fbf9f8;
  border-radius: 10px;
  padding: 1rem 1.2rem;
  display: flex;
  align-items: center;
  gap: 0.8rem;
  box-shadow: 2px 2px 2px 2px rgba(30, 113, 9, 0.04);
  transition: transform 0.2s;
}
.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(0,0,0,0.06);
}
.stat-icon {
  font-size: 2rem;
}
.stat-content {
  flex: 1;
}
.stat-value {
  font-size: 1.6rem;
  font-weight: 800;
  color: #2c5f2d;
  line-height: 1.2;
}
.stat-label {
  font-size: 0.7rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: #8b7a6b;
  margin: 4px 0;
}
.stat-trend {
  font-size: 0.7rem;
  margin-top: 2px;
}
.stat-trend.up { color: #2e7d32; }
.stat-trend.good { color: #2e7d32; }
.stat-trend.down { color: #d94f3d; }
.main-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1.5rem;
}
.card {
  background: white;
  border-radius: 24px;
  padding: 1.2rem;
  box-shadow: 0 2px 12px rgba(0,0,0,0.04);
}
.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}
.card-header h3 {
  font-size: 1.1rem;
  font-weight: 600;
  color: #2c5f2d;
  margin: 0;
}
.btn-link {
  background: none;
  border: none;
  color: #c9a87b;
  cursor: pointer;
  font-size: 0.8rem;
  font-weight: 500;
}
.btn-link:hover {
  text-decoration: underline;
}
.bar-chart {
  display: flex;
  justify-content: space-around;
  align-items: flex-end;
  gap: 0.8rem;
}
.bar-item {
  flex: 1;
  text-align: center;
}
.bar-container {
  height: 100px;
  display: flex;
  align-items: flex-end;
  justify-content: center;
  margin: 0.5rem 0;
}
.bar-fill {
  width: 30px;
  background: #c9a87b;
  border-radius: 8px 8px 0 0;
  transition: height 0.3s;
}
.bar-label {
  font-size: 0.7rem;
  color: #6b5744;
}
.bar-value {
  font-size: 0.7rem;
  font-weight: 600;
}
.sensors-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}
.sensor-item {
  display: flex;
  align-items: center;
  gap: 0.8rem;
  background: #faf7f0;
  padding: 0.6rem;
  border-radius: 16px;
}
.sensor-icon {
  font-size: 1.6rem;
}
.sensor-detail {
  flex: 1;
}
.sensor-detail label {
  font-size: 0.7rem;
  color: #8b7a6b;
  display: block;
}
.sensor-value {
  font-weight: 700;
  font-size: 1rem;
}
.tasks-list {
  display: flex;
  flex-direction: column;
  gap: 0.8rem;
}
.task-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #faf7f0;
  padding: 0.6rem 1rem;
  border-radius: 14px;
}
.task-checkbox {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  cursor: pointer;
}
.task-title {
  font-size: 0.85rem;
}
.task-priority {
  font-size: 0.7rem;
  text-transform: uppercase;
  font-weight: 600;
  padding: 0.2rem 0.6rem;
  border-radius: 20px;
}
.task-item.urgent .task-priority { background: #ffebee; color: #c62828; }
.task-item.moyen .task-priority { background: #fff4e5; color: #ed6c02; }
.task-item.normal .task-priority { background: #e8f5e9; color: #2e7d32; }
.alerts-list {
  display: flex;
  flex-direction: column;
  gap: 0.8rem;
}
.alert-item {
  display: flex;
  gap: 0.8rem;
  background: #fef5e8;
  padding: 0.8rem;
  border-radius: 16px;
  border-left: 4px solid #f5a623;
}
.alert-icon {
  font-size: 1.4rem;
}
.alert-title {
  font-weight: 700;
  font-size: 0.85rem;
}
.alert-message {
  font-size: 0.7rem;
  color: #6b5744;
}
.empty-alerts {
  text-align: center;
  padding: 1rem;
  color: #aa9a88;
}
@media (max-width: 1000px) {
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  .main-grid {
    grid-template-columns: 1fr;
  }
}
@media (max-width: 600px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }
  .header {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
  }
}
.task-done {
  text-decoration: line-through;
  opacity: 0.7;
}
</style>