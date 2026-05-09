import { Buffer } from 'buffer'
window.Buffer = Buffer
import process from 'process'
window.process = process

import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import 'leaflet/dist/leaflet.css'

// Optionnel : fix des icônes Leaflet (si les marqueurs n’apparaissent pas)
import L from 'leaflet'
delete L.Icon.Default.prototype._getIconUrl
L.Icon.Default.mergeOptions({
  iconRetinaUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon-2x.png',
  iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon.png',
  shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
})

import axios from 'axios'

// Configurer l'URL de base pour Axios
axios.defaults.baseURL = 'http://localhost:8000'

createApp(App).use(router).mount('#app')