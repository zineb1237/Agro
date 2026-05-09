<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import NavBar from '@/components/NavBar.vue'

const router = useRouter()
const userName = ref('Agriculteur')
const userVille = ref('')          // ville de l'utilisateur depuis DB

// ---------- MÉTÉO ----------
const meteo = ref({
  temperature: null,
  description: '',
  icone: '☀️'
})
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

// Géocodage ville → coordonnées
async function geocodeVille(nomVille) {
  const url = `https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(nomVille)}&format=json&limit=1`
  const response = await fetch(url, {
    headers: { 'User-Agent': 'Fellah-App/1.0' }
  })
  const data = await response.json()
  if (data && data.length > 0) {
    return {
      lat: parseFloat(data[0].lat),
      lon: parseFloat(data[0].lon),
      display: data[0].display_name.split(',')[0]
    }
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
      meteo.value = {
        temperature: temp,
        description: getDescriptionFromCode(code),
        icone: getWeatherIcon(code)
      }
    } else {
      throw new Error('Pas de données')
    }
  } catch (error) {
    console.error('Erreur météo', error)
    meteo.value = { temperature: '?', description: 'Indisponible', icone: '⚠️' }
  } finally {
    isLoadingMeteo.value = false
  }
}

// ---------- DATE ET SAISON ----------
const currentDate = ref('')
const currentSaison = ref('')

function getSaison(date) {
  const month = date.getMonth()
  const day = date.getDate()
  if ((month === 2 && day >= 20) || (month === 3) || (month === 4) || (month === 5 && day <= 20)) return 'Printemps'
  if ((month === 5 && day >= 21) || (month === 6) || (month === 7) || (month === 8 && day <= 22)) return 'Été'
  if ((month === 8 && day >= 23) || (month === 9) || (month === 10) || (month === 11 && day <= 21)) return 'Automne'
  return 'Hiver'
}

function updateDateAndSaison() {
  const now = new Date()
  const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }
  const formatted = now.toLocaleDateString('fr-FR', options)
  currentDate.value = formatted.charAt(0).toUpperCase() + formatted.slice(1)
  currentSaison.value = getSaison(now)
}

// ---------- AUTH ----------
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
      userVille.value = user.ville || ''   // ville depuis DB
    } catch (e) {
      console.error(e)
      router.push('/login')
    }
  }

  // Charger la météo avec la ville de l'utilisateur
  if (userVille.value) {
    await fetchWeatherForCity(userVille.value)
  } else {
    // Ville par défaut si l'utilisateur n'en a pas encore
    userVille.value = 'El Jadida'
    await fetchWeatherForCity('El Jadida')
  }
})
</script>

<template>
  <div class="dashboard-container">
    <NavBar />
    <main class="dashboard-content">
      <div class="header">
        <h2>Tableau de bord</h2>
        <!-- Affichage météo direct, sans champ de saisie -->
        <div class="weather-area">
          <div class="weather-info" v-if="!isLoadingMeteo && meteo.temperature !== null">
            <span class="weather-icon">{{ meteo.icone }}</span>
            <span class="weather-text">{{ userVille }} · {{ meteo.temperature }}°C · {{ meteo.description }}</span>
          </div>
          <div v-else-if="isLoadingMeteo" class="weather-info">Chargement météo...</div>
          <div v-else class="weather-info">Météo indisponible</div>
        </div>
      </div>

      <div class="welcome-section">
        <p class="bnj">Bonjour, {{ userName }} 👋</p>
        <p class="date-saison">{{ currentDate }} · Saison : {{ currentSaison }}</p>
      </div>

      <!-- Vos cartes, graphiques... -->
    </main>
  </div>
</template>

<style scoped>
/* styles inchangés, adaptez selon besoin */
.dashboard-container {
  display: flex;
  height: 100vh;
  width: 100%;
}
.dashboard-content {
  flex: 1;
  background: #fafcf8;
  overflow-y: auto;
}
.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 20px;
  border-bottom: 1px solid #e0e0e0;
  background: white;
}
.weather-area {
  display: flex;
  align-items: center;
  gap: 15px;
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
  color: #1f2e1c;
}
.weather-icon {
  font-size: 1.3rem;
}
.welcome-section {
  padding: 0px 20px 10px 20px;
}
.bnj {
  font-family: Georgia, 'Times New Roman', Times, serif;
  font-size: 1.5rem;
  font-weight: bold;
  margin-bottom: 2px;
  color: #1a4d2e;
}
.date-saison {
  font-size: 0.9rem;
  color: #5b7a4e;
  font-family: 'Inter', sans-serif;
}
</style>