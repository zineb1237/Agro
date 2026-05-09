<template>
  <div class="parcelle-container">
    <NavBar />
    <div class="main-content">
      <!-- Toast notification -->
      <div v-if="toastVisible" class="toast-notification" :class="{ error: toastError }">
        {{ toastMessage }}
      </div>

      <div class="header-actions">
        <h1>Mes Parcelles</h1>
        <div class="search-bar">
          <div class="search-wrapper">
            <input
              type="text"
              v-model="searchQuery"
              @keyup.enter="searchLocation"
              placeholder="🔍 Chercher un douar"
              class="search-input"
            />
            <span 
              v-if="searchQuery.length > 0" 
              @click="clearSearchMarkers" 
              class="clear-icon"
            >✕</span>
          </div>
        </div>
       <div class="action-buttons">
  <button class="btn-add" @click="openAddModal">+ Nouvelle parcelle</button>
  <button class="btn-notif" @click="openNotifModal">
    🔔   <span v-if="unreadCount > 0" class="badge">{{ unreadCount }}</span>
  </button>
</div>
      </div>
<div v-if="showNotifModal" class="modal">
  <div class="modal-content notif-modal">
    <h3>📬 Mes rappels</h3>
    <div v-if="notifications.length === 0" class="empty-state">
      Aucune notification pour le moment.
    </div>
    <div v-else>
      <div v-for="notif in notifications" :key="notif.id" class="notif-item" :class="{ read: notif.read_at }">
        <div class="notif-title">{{ notif.title }}</div>
        <div class="notif-message">{{ notif.message }}</div>
        <small>{{ formatDate(notif.created_at) }}</small>
        <button v-if="!notif.read_at" @click="markAsRead(notif.id)" class="btn-read">✅ Marquer lue</button>
      </div>
    </div>
    <div class="modal-buttons">
      <button @click="showNotifModal = false" class="btn-cancel">Fermer</button>
    </div>
  </div>
</div>
      <!-- Carte satellite -->
      <div class="carte-section">
        <h3>Carte satellite – Région Doukkala</h3>
        <p class="region">El Jadida · Ouled Frej · Azemmour</p>
        <l-map
          ref="map"
          v-model:zoom="zoom"
          :center="center"
          style="height: 400px; width: 100%; border-radius: 16px;"
          @click="onMapClick"
        >
          <l-tile-layer
            url="https://tiles.stadiamaps.com/tiles/alidade_satellite/{z}/{x}/{y}.png"
            attribution='&copy; <a href="https://stadiamaps.com/">Stadia Maps</a>'
            :maxZoom="20"
          />
          <!-- Marqueurs des parcelles avec popups enrichies -->
          <l-marker
            v-for="p in parcelles"
            :key="p.id"
            :lat-lng="[p.lat, p.lng]"
          >
            <l-popup>
              <div class="popup-content">
                <strong>📍 {{ p.nom }} – {{ p.culture }}</strong><br />
                Superficie : {{ p.superficie }} ha<br />
                Stade : {{ p.stade }}<br />
                <hr />
                🌡️ Température sol : {{ getSoilTemp(p) }}°C<br />
                💧 Humidité sol : {{ getSoilMoisture(p) }}%<br />
                ⚡ Conductivité : {{ capteursParcelle1.conductivite || '__' }} µS/cm<br />
                🧪 pH : {{ capteursParcelle1.pH || '__' }}<br />
                🌱 NPK : N={{ capteursParcelle1.N || '__' }}, P={{ capteursParcelle1.P || '__' }}, K={{ capteursParcelle1.K || '__' }} mg/kg<br />
                📋 <strong>Action :</strong> {{ getAction(p) }}
              </div>
            </l-popup>
          </l-marker>

          <!-- Marqueurs temporaires des résultats de recherche -->
          <l-marker
            v-for="m in tempMarkers"
            :key="m.id"
            :lat-lng="[m.lat, m.lng]"
          >
            <l-popup>
              <div class="popup-content search-result">
                <strong>📍 {{ m.name }}</strong><br />
                <span v-html="m.fullInfo.replace(/\n/g, '<br>')"></span><br />
                <small>Coordonnées : {{ m.lat.toFixed(4) }}, {{ m.lng.toFixed(4) }}</small>
              </div>
            </l-popup>
          </l-marker>
        </l-map>
        <p class="carte-info">Cliquez sur un marqueur pour voir les détails • Cliquez sur la carte pour ajouter une parcelle</p>
      </div>

      <!-- Liste des parcelles -->
     
<div class="liste-section">
  <h3>Liste des parcelles :</h3>
  <div class="parcelle-grid">
    <div v-for="p in parcelles" :key="p.id" class="parcelle-card" :class="{ alert: p.alerte }">
      <div class="card-header" @click="center = [p.lat, p.lng]">
        <strong>{{ p.nom }}</strong> — {{ p.culture }}
      </div>
      <div class="card-details">
        <!-- pH + Humidité sol -->
        <div class="sensor-row">
          <div class="sensor-item">
             <div class="sensor-label">🧪 pH</div>
            <div class="sensor-value val-green">

               <span class="sensor-unit">   </span>{{ capteurs[p.id]?.pH ?? '--' }}
            </div>
            <div class="sensor-bar">
<div class="sensor-bar-fill" :style="{ width: getPercentage(capteurs[p.id]?.pH, 0, 14) + '%', background:'#2D7A4F' }"></div>
            </div>
           
          </div>
          <div class="sensor-item">
             <div class="sensor-label">💧Humidité sol</div>
            <div class="sensor-value val-blue">
              {{ capteurs[p.id]?.humidite ?? getSoilMoisture(p) }} <span class="sensor-unit">%</span>
            </div>
            <div class="sensor-bar">
              <div class="sensor-bar-fill" :style="{ width: (capteurs[p.id]?.humidite ?? getSoilMoisture(p)) + '%' }"></div>
            </div>
           
          </div>
        </div>

        <!-- Température sol + Conductivité -->
        <div class="sensor-row">
          <div class="sensor-item">
              <div class="sensor-label">🌡️ Température sol</div>
            <div class="sensor-value val-gold">
              {{ capteurs[p.id]?.temperature ?? getSoilTemp(p) }} <span class="sensor-unit">°C</span>
            </div>
            <div class="sensor-bar">
              <div class="sensor-bar-fill" :style="{ width: getTempPercent(capteurs[p.id]?.temperature ?? getSoilTemp(p)) + '%', background: '#e84703'  }"></div>
            </div>
          
          </div>
          <div class="sensor-item">
               <div class="sensor-label">⚡Conductivité</div>
            <div class="sensor-value val-orange">
              {{ capteurs[p.id]?.conductivite ?? '__' }}  <span class="sensor-unit">µS/cm  </span> 
            </div>
            <div class="sensor-bar">
              <div class="sensor-bar-fill" :style="{ width: getConductivityPercent(capteurs[p.id]?.conductivite) + '%', background :'#f59e0b' }"></div>
            </div>
         
          </div>
        </div>

        <!-- Air : température + humidité -->
        <div class="sensor-row">
          <div class="sensor-item"> <div class="sensor-label">Température air</div>
            <div class="sensor-value val-torabi">
              {{ weatherByParcelle[p.id]?.airTemp ?? '__' }} <span class="sensor-unit">°C</span>
            </div>
            <div class="sensor-bar">
              <div class="sensor-bar-fill" :style="{ width: getTempPercent(weatherByParcelle[p.id]?.airTemp) + '%', background:'#c79339' }"></div>
            </div>
           
          </div>
          <div class="sensor-item">
               <div class="sensor-label">Humidité air</div>
            <div class="sensor-value val-blue2">
              {{ weatherByParcelle[p.id]?.airHumidity ?? '__' }} <span class="sensor-unit">%</span>
            </div>
            <div class="sensor-bar">
              <div class="sensor-bar-fill" :style="{ width: (weatherByParcelle[p.id]?.airHumidity ?? 0) + '%',background: '#5BC0DE' }"></div>
            </div>
         
          </div>
        </div>

        <!-- NPK sur une seule ligne : trois indicateurs N, P, K -->
        <div class="sensor-row">
          <div class="sensor-item">
            <div class="sensor-value val-blue">
              {{ capteurs[p.id]?.N ?? '__' }} <span class="sensor-unit">N</span>
            </div>
            <div class="sensor-bar">
              <div class="sensor-bar-fill" :style="{ width: getNpkPercent(capteurs[p.id]?.N) + '%' }"></div>
            </div>
            <div class="sensor-label">Azote (N)</div>
          </div>
          <div class="sensor-item">
            <div class="sensor-value val-blue">
              {{ capteurs[p.id]?.P ?? '__' }} <span class="sensor-unit">P</span>
            </div>
            <div class="sensor-bar">
              <div class="sensor-bar-fill" :style="{ width: getNpkPercent(capteurs[p.id]?.P) + '%' }"></div>
            </div>
            <div class="sensor-label">Phosphore (P)</div>
          </div>
          <div class="sensor-item">
            <div class="sensor-value val-blue">
              {{ capteurs[p.id]?.K ?? '__' }} <span class="sensor-unit">K</span>
            </div>
            <div class="sensor-bar">
              <div class="sensor-bar-fill" :style="{ width: getNpkPercent(capteurs[p.id]?.K) + '%' }"></div>
            </div>
            <div class="sensor-label">Potassium (K)</div>
          </div>
        </div>

        <!-- Problèmes de santé du sol -->
        <div class="problems" v-if="capteurs[p.id] && !checkSoilHealth(capteurs[p.id]).isHealthy">
          <div v-for="(prob, idx) in checkSoilHealth(capteurs[p.id]).problems" :key="idx" class="problem-tag alert">
            ⚠️ {{ prob }}
          </div>
        </div>

        <!-- Recommandations -->
        <div class="action-reco" v-if="capteurs[p.id]">
          <strong>💡 Recommandations :</strong>
          <ul>
            <li v-for="(rec, idx) in checkSoilHealth(capteurs[p.id]).recommendations" :key="idx">{{ rec }}</li>
          </ul>
        </div>

        <!-- Action supplémentaire -->
        <div class="action-reco">{{ getAction(p) }}    <button class="btn-edit" @click.stop="editParcelle(p)">Modifier</button></div>

      
      </div>
    </div>
  </div>
</div>

      <!-- Calendrier agricole -->
      <div class="calendar-section">
        <h3>📅 Calendrier agricole</h3>
        <p class="region">Planification et alertes</p>
        <FullCalendar :options="calendarOptions" />
      </div>

      <!-- Modal d'édition / suppression d'événement -->
      <div v-if="showEditModal" class="modal note-modal">
        <div class="modal-content note-modal-content">
          <h3>✏️ Modifier la note</h3>
          <textarea 
            v-model="editNoteText" 
            rows="4"
            class="note-textarea"
          ></textarea>
          <div class="modal-buttons" style="justify-content: space-between;">
            <button @click="deleteEvent" class="btn-delete">🗑️ Supprimer</button>
            <div>
              <button @click="updateEvent" class="btn-save">Enregistrer</button>
              <button @click="showEditModal = false" class="btn-cancel">Annuler</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal pour ajouter une note dans le calendrier -->
      <div v-if="showNoteModal" class="modal note-modal">
        <div class="modal-content note-modal-content">
          <h3>📝 Ajouter une note</h3>
          <p class="note-date">Date : {{ noteDate }}</p>
          <textarea 
            v-model="noteText" 
            placeholder="Saisissez votre note ici..." 
            rows="4"
            class="note-textarea"
          ></textarea>
          <div class="modal-buttons">
            <button @click="saveNote" class="btn-save">Enregistrer</button>
            <button @click="showNoteModal = false" class="btn-cancel">Annuler</button>
          </div>
        </div>
      </div>
      <!-- Modale de choix : note ou action -->
<!-- Modale de choix : note ou action -->
<div v-if="showChoiceModal" class="modal choice-modal">
  <div class="modal-content choice-modal-content">
    <button class="close-modal-btn" @click="showChoiceModal = false">✕</button>
    <h3>Que voulez-vous ajouter ?</h3>
    <div class="choice-buttons">
      <button @click="openNoteFromChoice" class="btn-choice note">Note simple</button>
      <button @click="openActionFromChoice" class="btn-choice action"> Action agricole</button>
    </div>
  </div>
</div>
<!-- Modal pour enregistrer une action agricole -->
<div v-if="showActionModal" class="modal action-modal">
  <div class="modal-content action-modal-content">
    <h3>📝 Enregistrer une action</h3>
    <div class="form-field">
      <label>Type d'action</label>
      <select v-model="actionType">
        <option value="irrigation">💧 Irrigation</option>
        <option value="traitement">💊 Traitement</option>
        <option value="fertilisation">🌿 Fertilisation</option>
        <option value="stade">🌱 Stade cultural</option>
        <option value="probleme">👀 Problème</option>
      </select>
    </div>
    <div class="form-field" v-if="actionType === 'irrigation'">
      <label>Quantité d'eau (mm ou m³/ha)</label>
      <input type="number" step="1" v-model="actionQuantite" />
    </div>
    <div class="form-field" v-if="actionType === 'traitement'">
      <label>Produit + dose</label>
      <input type="text" v-model="actionDetail" placeholder="ex: Fongicide X – 1L/ha" />
    </div>
    <div class="form-field" v-if="actionType === 'fertilisation'">
      <label>Engrais + quantité</label>
      <input type="text" v-model="actionDetail" placeholder="ex: Urée – 50 kg/ha" />
    </div>
    <div class="form-field" v-if="actionType === 'stade'">
      <label>Stade observé</label>
      <input type="text" v-model="actionDetail" placeholder="ex: Floraison" />
    </div>
    <div class="form-field" v-if="actionType === 'probleme'">
      <label>Description du problème</label>
      <textarea v-model="actionDetail" rows="2" placeholder="ex: Mildiou sur feuilles"></textarea>
    </div>
    <div class="form-field">
      <label>Parcelle concernée</label>
      <select v-model="actionParcelleId">
        <option v-for="p in parcelles" :key="p.id" :value="p.id">{{ p.nom }} ({{ p.culture }})</option>
      </select>
    </div>
    <div class="modal-buttons">
      <button @click="saveActionEvent" class="btn-save">Enregistrer</button>
      <button @click="showActionModal = false" class="btn-cancel">Annuler</button>
    </div>
  </div>
</div>
      <!-- Modal d'ajout / édition -->
      <div v-if="showModal" class="modal">
        <div class="modal-content">
          <h3>{{ editingId ? 'Modifier' : 'Ajouter' }} une parcelle</h3>
          <form @submit.prevent="saveParcelle">
            <div class="form-grid">
              <div class="form-field">
                <label>Nom de la parcelle</label>
                <input v-model="form.nom" placeholder="ex: A1" required />
              </div>
              <div class="form-field">
                <label>Culture</label>
                <input v-model="form.culture" placeholder="ex: Blé dur" required />
              </div>
              <div class="form-field">
                <label>Superficie (ha)</label>
                <input type="number" step="0.1" v-model="form.superficie" placeholder="0.0" required />
              </div>
              <div class="form-field">
                <label>Stade</label>
                <input v-model="form.stade" placeholder="ex: Floraison" />
              </div>
              <div class="form-field">
                <label>Date de plantation</label>
                <input type="date" v-model="form.datePlantation" />
              </div>
              <div class="form-field">
                <label>Type de sol</label>
                <select v-model="form.typeSol">
                  <option value="">Non renseigné</option>
                  <option value="argileux">Argileux</option>
                  <option value="limoneux">Limoneux</option>
                  <option value="sableux">Sableux</option>
                  <option value="limono-argileux">Limono-argileux</option>
                  <option value="sablo-limoneux">Sablo-limoneux</option>
                </select>
              </div>
            </div>

            <!-- localisation sur une ligne large -->
            <div class="coords-row">
              <div class="form-field full-width">
                <label>Latitude (optionnel)</label>
                <input type="number" step="any" v-model="form.lat" placeholder="ex: 33.2316" />
              </div>
              <div class="form-field full-width">
                <label>Longitude (optionnel)</label>
                <input type="number" step="any" v-model="form.lng" placeholder="ex: -8.5007" />
              </div>
            </div>

            <div class="modal-buttons">
              <button type="submit">Enregistrer</button>
              <button type="button" @click="closeModal">Annuler</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import NavBar from '@/components/NavBar.vue'
import { ref, onMounted } from 'vue'
import { LMap, LTileLayer, LMarker, LPopup } from '@vue-leaflet/vue-leaflet'
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import interactionPlugin from '@fullcalendar/interaction'
import timeGridPlugin from '@fullcalendar/timegrid'
import mqtt from 'mqtt/dist/mqtt.min.js'
import axios from 'axios'
// Variables pour la modale d'action
const showActionModal = ref(false)
const actionType = ref('irrigation')
const actionQuantite = ref(null)
const actionDetail = ref('')
const actionParcelleId = ref(null)
const actionDate = ref('')
const showNotifModal = ref(false);
const notifications = ref([]);
const unreadCount = ref(0);

async function loadNotifications() {
  const res = await axios.get('/api/notifications');
  notifications.value = res.data;
  unreadCount.value = notifications.value.filter(n => !n.read_at).length;
}
async function markAsRead(id) {
  await axios.put(`/api/notifications/${id}/read`);
  await loadNotifications(); // recharge
}

function formatDate(dateStr) {
  return new Date(dateStr).toLocaleDateString('fr-FR');
}

// Ouvrir la modale et charger
function openNotifModal() {
  showNotifModal.value = true;
  loadNotifications();
}

// Ouvrir la modale pour une date donnée
function openActionModal(dateStr) {
  actionDate.value = dateStr
  actionType.value = 'irrigation'
  actionQuantite.value = null
  actionDetail.value = ''
  actionParcelleId.value = parcelles.value[0]?.id || null
  showActionModal.value = true
}

// Charger les événements du calendrier depuis le backend
const dbEvents = ref([]);

// Charger les événements du calendrier depuis le backend
async function loadCalendarEvents() {
  try {
    const response = await axios.get('/api/calendar-events');
    const events = response.data;

    dbEvents.value = events.map(ev => ({
      id: ev.id,
      title: ev.title,
      start: ev.start_date,
      end: ev.end_date,
      backgroundColor: ev.background_color || ev.backgroundColor, // Supporte les deux cas au cas où
      extendedProps: {
        parcelleId: ev.parcelle_id,
        type: ev.type,
        description: ev.description,
        notified: ev.notified_at !== null
      }
    }));

    updateCalendarDisplay();
  } catch (error) {
    console.error('Erreur chargement événements', error);
  }
}

// Sauvegarder un événement (création)
async function saveEventToBackend(eventData) {
  const response = await axios.post('/api/calendar-events', eventData);
  return response.data;
}

// Mettre à jour un événement
async function updateEventInBackend(id, eventData) {
  await axios.put(`/api/calendar-events/${id}`, eventData);
}

// Supprimer un événement
async function deleteEventFromBackend(id) {
  await axios.delete(`/api/calendar-events/${id}`);
}

// Vérifier les événements du jour pour notification
async function checkUpcomingEvents() {
  try {
    const response = await axios.get('/api/calendar-events/upcoming');
    const events = response.data;
    if (events.length) {
      const today = new Date().toISOString().split('T')[0];
      
      for (const ev of events) {
        const isToday = ev.start_date === today;
        const prefix = isToday ? "📅 Aujourd'hui" : "🔔 Demain";
        showToast(`${prefix} : ${ev.title}`);
        
        // Marquer comme notifié pour ne plus l'afficher
        await axios.patch(`/api/calendar-events/${ev.id}/mark-notified`);
      }
    }
  } catch (error) {
    console.error('Erreur check upcoming events', error);
  }
}
// Sauvegarder l'événement dans le calendrier
async function saveActionEvent() {
  const parcelle = parcelles.value.find(p => p.id === actionParcelleId.value)
  if (!parcelle) return

  let title = ''
  let backgroundColor = ''

  switch (actionType.value) {
    case 'irrigation':
      title = `💧 Irrigation ${parcelle.nom} : ${actionQuantite.value} mm`
      backgroundColor = '#3b82f6'
      break
    case 'traitement':
      title = `💊 Traitement ${parcelle.nom} : ${actionDetail.value}`
      backgroundColor = '#ef4444'
      break
    case 'fertilisation':
      title = `🌿 Fertilisation ${parcelle.nom} : ${actionDetail.value}`
      backgroundColor = '#10b981'
      break
    case 'stade':
      title = `🌱 Stade ${parcelle.nom} : ${actionDetail.value}`
      backgroundColor = '#f59e0b'
      break
    case 'probleme':
      title = `👀 Problème ${parcelle.nom} : ${actionDetail.value}`
      backgroundColor = '#d94f3d'
      break
  }

  const backendData = {
    title: title,
    start_date: actionDate.value,
    type: actionType.value,
    parcelle_id: actionParcelleId.value,
    background_color: backgroundColor,
    description: actionDetail.value || `Quantité: ${actionQuantite.value}`
  }

  try {
    const saved = await saveEventToBackend(backendData);
    
    dbEvents.value.push({
      id: saved.id,
      title: saved.title,
      start: saved.start_date,
      backgroundColor: saved.background_color,
      extendedProps: {
        type: saved.type,
        parcelleId: saved.parcelle_id,
        description: saved.description
      }
    });
    
    updateCalendarDisplay();
    showToast('Action enregistrée dans le calendrier')
    showActionModal.value = false
  } catch (error) {
    console.error('Erreur sauvegarde action', error);
    showToast('Erreur lors de la sauvegarde', true);
  }
}
// Références réactives pour stocker les données capteurs par parcelle
const capteursParcelle1 = ref({ humidite: null, temperature: null, conductivite: null, pH: null, N: null, P: null, K: null })

const capteurs = ref({}); 
const weatherByParcelle = ref({}) // clé = id de la parcelle, valeur = { airTemp, airHumidity }
// Connexion au broker MQTT
const mqttClient = mqtt.connect('ws://broker.hivemq.com:8000/mqtt');


mqttClient.on('connect', () => {
  console.log('✅ MQTT connecté au broker public');
  mqttClient.subscribe('agriculture/parcelle1/sensors');
  mqttClient.subscribe('agriculture/parcelle2/sensors');
  mqttClient.subscribe('agriculture/parcelle3/sensors');
});

mqttClient.on('message', (topic, message) => {
  const payload = JSON.parse(message.toString());
  console.log(`Reçu de ${topic} :`, payload);

  // Associer le topic à un id de parcelle
  let parcelleId = null;
  if (topic === 'agriculture/parcelle1/sensors') parcelleId = 1;
  else if (topic === 'agriculture/parcelle2/sensors') parcelleId = 2;
  else if (topic === 'agriculture/parcelle3/sensors') parcelleId = 3;

  if (parcelleId && payload) {
    capteurs.value[parcelleId] = payload;
    const diagnostic = checkSoilHealth(payload);
    const parcelle = parcelles.value.find(p => p.id === parcelleId);
    if (parcelle) {
      parcelle.alerte = !diagnostic.isHealthy;
    }
  }
});
// Configurer le token d'authentification (après login)
const token = localStorage.getItem('token')
if (token) {
  axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
}
// ---------- DIAGNOSTIC SOL ----------
const checkSoilHealth = (capteurs) => {
    const norms = {
        pH: { min: 6.0, max: 7.5, ideal: 6.5 },
        n: { min: 20, max: 60, ideal: 40 },
        p: { min: 10, max: 50, ideal: 30 },
        k: { min: 100, max: 300, ideal: 200 },
        humidity: { min: 50, max: 70, ideal: 60 },
        conductivity: { min: 0.3, max: 0.8, ideal: 0.5 },
        temperature: { min: 18, max: 28, ideal: 24 }
    };

    let problems = [];
    let recommendations = [];

    if (capteurs.pH < norms.pH.min) {
        problems.push("Sol trop acide");
        recommendations.push("Apportez de la chaux dolomitique (Calcaire) pour remonter le pH.");
    } else if (capteurs.pH > norms.pH.max) {
        problems.push("Sol trop alcalin (basique)");
        recommendations.push("Incorporez du soufre ou du compost bien décomposé pour baisser le pH.");
    }

    if (capteurs.N < norms.n.min) {
        problems.push("Carence en Azote (N)");
        recommendations.push("Appliquez un engrais riche en azote (Urée, fumier décomposé).");
    }

    if (capteurs.P < norms.p.min) {
        problems.push("Carence en Phosphore (P)");
        recommendations.push("Apportez du superphosphate ou du phosphore naturel pour booster les racines.");
    }

    if (capteurs.K < norms.k.min) {
        problems.push("Carence en Potassium (K)");
        recommendations.push("Ajoutez de la potasse ou de la cendre de bois.");
    }

    if (capteurs.humidite < norms.humidity.min) {
        problems.push("Stress hydrique (Sol trop sec)");
        recommendations.push("Arrosez immédiatement. Installez un paillage pour conserver l'humidité.");
    } else if (capteurs.humidite > norms.humidity.max) {
        problems.push("Asphyxie racinaire (Sol détrempé)");
        recommendations.push("Stop irrigation. Aérez le sol si possible.");
    }

    if (capteurs.conductivite > norms.conductivity.max) {
        problems.push("Salinité excessive (Trop de sels)");
        recommendations.push("Lessivez le sol avec un arrosage abondant (drainage). Réduisez les engrais chimiques.");
    }

    if (capteurs.temperature > norms.temperature.max) {
        problems.push("Stress thermique (Canicule)");
        recommendations.push("Arrosez tôt le matin ou tard le soir. Utilisez un voile d'ombrage.");
    }

    return {
        isHealthy: problems.length === 0,
        problems: problems,
        recommendations: recommendations,
        statusColor: problems.length === 0 ? '#2D7A4F' : (problems.length < 3 ? '#f59e0b' : '#d94f3d')
    };
};// Pour la modale de choix
const showChoiceModal = ref(false)
const selectedDateForEvent = ref('')

// Ouvrir la modale de choix quand on clique sur une date
function onDateClick(info) {
  selectedDateForEvent.value = info.dateStr
  showChoiceModal.value = true
}

// Appelée quand l'utilisateur choisit "Note simple"
function openNoteFromChoice() {
  showChoiceModal.value = false
  openNoteModal(selectedDateForEvent.value)
}

// Appelée quand l'utilisateur choisit "Action agricole"
function openActionFromChoice() {
  showChoiceModal.value = false
  openActionModal(selectedDateForEvent.value) // à créer
}
// ---------- ÉTAT ----------
const searchQuery = ref('')
const tempMarkers = ref([])
const parcelles = ref([])
const zoom = ref(13)
const center = ref([33.2316, -8.5007]) // El Jadida
const showModal = ref(false)
const editingId = ref(null)
const form = ref({
  nom: '',
  culture: '',
  superficie: null,
  stade: '',
  datePlantation: '',
  typeSol: '',
  lat: '',
  lng: ''
})

// Pour la modale de note
const showNoteModal = ref(false)
const noteDate = ref('')
const noteText = ref('')

// Modale d'édition d'événement
const showEditModal = ref(false)
const editingEvent = ref(null)
const editNoteText = ref('')
const editEventId = ref(null)

// ---------- TOAST ----------
const toastVisible = ref(false)
const toastMessage = ref('')
const toastError = ref(false)
let toastTimer = null

function showToast(message, isError = false) {
  if (toastTimer) clearTimeout(toastTimer)
  toastMessage.value = message
  toastError.value = isError
  toastVisible.value = true
  toastTimer = setTimeout(() => {
    toastVisible.value = false
  }, 3000)
}


// ---------- MÉTÉO (pour les simulations) ----------
const weather = ref({ temp: 26, humidity: 55, rainLastDays: 0 })

async function fetchWeather() {
  try {
    const url = `https://api.open-meteo.com/v1/forecast?latitude=${center.value[0]}&longitude=${center.value[1]}&current_weather=true&daily=precipitation_sum&timezone=auto`
    const res = await fetch(url)
    const data = await res.json()
    if (data.current_weather) {
      weather.value.temp = data.current_weather.temperature
      weather.value.humidity = Math.max(30, Math.min(80, 60 - (data.current_weather.temperature - 20) * 1.5))
      if (data.daily && data.daily.precipitation_sum && data.daily.precipitation_sum[0]) {
        weather.value.rainLastDays = data.daily.precipitation_sum[0]
      }
    }
  } catch (e) {
    console.warn("Météo non dispo, valeurs par défaut")
  }
}
async function fetchWeatherForParcelle(parcelle) {
  if (!parcelle.lat || !parcelle.lng) return
  const url = `https://api.open-meteo.com/v1/forecast?latitude=${parcelle.lat}&longitude=${parcelle.lng}&current=temperature_2m,relative_humidity_2m&timezone=auto`
  try {
    const response = await fetch(url)
    const data = await response.json()
    if (data.current) {
      weatherByParcelle.value[parcelle.id] = {
        airTemp: data.current.temperature_2m,
        airHumidity: data.current.relative_humidity_2m
      }
    }
  } catch (error) {
    console.warn(`Météo non dispo pour parcelle ${parcelle.id}`)
  }
}
 
    
    // Pourcentage pH (0-14)
    function  getPercentage(value, min, max) {
      if (value === undefined || value === null) return 0;
      let percent = ((value - min) / (max - min)) * 100;
      return Math.min(100, Math.max(0, percent));
    }
    // Pourcentage température (plage -10°C à 50°C)
    function  getTempPercent(temp) {
      if (temp === undefined || temp === null) return 0;
      let percent = ((temp + 10) / 60) * 100;
      return Math.min(100, Math.max(0, percent));
    }
    // Pourcentage conductivité (max 2000 µS/cm)
     function getConductivityPercent(cond) {
      if (cond === undefined || cond === null) return 0;
      let percent = (cond / 2000) * 100;
      return Math.min(100, Math.max(0, percent));
    }
    // Pourcentage NPK (max arbitraire 500 mg/kg)
   function getNpkPercent(value) {
      if (value === undefined || value === null) return 0;
      let percent = (value / 500) * 100;
      return Math.min(100, Math.max(0, percent));
    }
  
// ---------- FONCTIONS DE SIMULATION AGRICOLE ----------
function getSoilTemp(p) {
  let base = weather.value.temp
  if (p.culture === "Blé dur") base += 2
  else if (p.culture === "Orge") base += 1
  return Math.round(base)
}

function getSoilMoisture(p) {
  if (p.humidite) return p.humidite
  let moisture = 40 + weather.value.rainLastDays * 5 - (weather.value.temp - 20) * 1.5
  return Math.min(90, Math.max(15, Math.round(moisture)))
}

function getWaterStress(p) {
  // 1. Priorité aux données des capteurs
  const capteur = capteurs.value[p.id];
  if (capteur && capteur.humidite !== null) {
    if (capteur.humidite < 25) return "Critique";
    if (capteur.humidite < 40) return "Modéré";
    return "Normal";
  }
  
  // 2. Fallback : simulation
  const m = getSoilMoisture(p);
  if (m < 25) return "Critique";
  if (m < 40) return "Modéré";
  return "Normal";
}
// Fonction getIrrigationNeed supprimée car non utilisée

function getDiseaseRisk(p) {
  // Utiliser la météo spécifique à la parcelle
  const weatherData = weatherByParcelle.value[p.id];
  if (!weatherData) return "Faible";
  
  const airHumidity = weatherData.airHumidity;
  const airTemp = weatherData.airTemp;

  if (airHumidity > 80 && airTemp > 18 && airTemp < 28) return "Élevé (mildiou)";
  if (airHumidity > 60 && airTemp > 15) return "Modéré";
  return "Faible";
}
// ---------- ACTION RECOMMANDÉE ----------
function getAction(p) {
  // Stress hydrique (basé sur humidité du sol)
  const capteur = capteurs.value[p.id];
  const humiditeSol = capteur?.humidite ?? getSoilMoisture(p);
  let waterStress = "Normal";
  if (humiditeSol < 25) waterStress = "Critique";
  else if (humiditeSol < 40) waterStress = "Modéré";

  if (waterStress === "Critique") return "💧 Irriguer urgemment";
  if (waterStress === "Modéré") return "⏰ Prévoir irrigation dans 2 jours";

  // Maladies (basé sur météo air)
  const diseaseRisk = getDiseaseRisk(p);
  if (diseaseRisk.includes("Élevé")) return "🦠 Surveiller et traiter fongicide";
  if (diseaseRisk === "Modéré") return "🔍 Surveillance rapprochée";

  return "✅ Aucune action immédiate";
}

// ---------- API PARCELLES ----------
async function fetchParcelles() {
  try {
    const response = await axios.get('/api/parcelles')
    parcelles.value = response.data.map(p => {
      const coords = p.localisation ? p.localisation.split(',') : []
      const lat = coords[0] ? Number(coords[0]) : center.value[0]
      const lng = coords[1] ? Number(coords[1]) : center.value[1]
      return { ...p, lat, lng }
    })
    // Récupération météo pour chaque parcelle
    for (const p of parcelles.value) {
      await fetchWeatherForParcelle(p)
    }
    generateCalendarEvents()
  } catch (error) {
    console.error('Erreur chargement parcelles', error)
  }
}

async function saveParcelle() {
  if (!form.value.nom || !form.value.culture || !form.value.superficie) {
    showToast('Veuillez remplir le nom, la culture et la superficie', true)
    return
  }

  const lat = form.value.lat || center.value[0]
  const lng = form.value.lng || center.value[1]
  const localisation = `${lat},${lng}`

  const data = {
    nom: form.value.nom,
    culture: form.value.culture,
    superficie: form.value.superficie,
    stade: form.value.stade || null,
    datePlantation: form.value.datePlantation || null,
    typeSol: form.value.typeSol || null,
    localisation: localisation,
    statut: 'actif'
  }

  try {
    if (editingId.value) {
      await axios.put(`/api/parcelles/${editingId.value}`, data)
      showToast('Parcelle modifiée')
    } else {
      await axios.post('/api/parcelles', data)
      showToast('Parcelle ajoutée')
    }
    await fetchParcelles()
    closeModal()
  } catch (error) {
    console.error(error)
    showToast('Erreur lors de l’enregistrement', true)
  }
}

// ---------- RECHERCHE GÉOGRAPHIQUE (Doukkala) ----------
const doukkalaBbox = {
  minLat: 32.8,
  maxLat: 33.5,
  minLon: -8.8,
  maxLon: -8.3
}

async function searchLocation() {
  if (!searchQuery.value.trim()) return
  const query = encodeURIComponent(searchQuery.value)
  const viewbox = `${doukkalaBbox.minLon},${doukkalaBbox.minLat},${doukkalaBbox.maxLon},${doukkalaBbox.maxLat}`
  const url = `https://nominatim.openstreetmap.org/search?q=${query}&format=json&limit=5&accept-language=fr,ar&viewbox=${viewbox}&bounded=1&addressdetails=1`

  try {
    const response = await fetch(url, { headers: { 'User-Agent': 'Fellah-App/1.0' } })
    const data = await response.json()
    if (data && data.length > 0) {
      tempMarkers.value = []
      data.forEach(result => {
        const lat = parseFloat(result.lat)
        const lon = parseFloat(result.lon)
        let description = result.display_name
        if (result.address) {
          const parts = []
          if (result.address.village) parts.push(`Village : ${result.address.village}`)
          if (result.address.city) parts.push(`Ville : ${result.address.city}`)
          if (result.address.state) parts.push(`Région : ${result.address.state}`)
          if (parts.length) description += '\n' + parts.join(' · ')
        }
        tempMarkers.value.push({
          id: `search-${Date.now()}-${Math.random()}`,
          lat, lng: lon,
          name: result.display_name.split(',')[0],
          fullInfo: description
        })
      })
      const first = tempMarkers.value[0]
      center.value = [first.lat, first.lng]
      zoom.value = 14
      showToast(`${tempMarkers.value.length} lieu(x) trouvé(s) dans Doukkala.`)
    } else {
      showToast('Aucun lieu trouvé dans la région Doukkala.', true)
    }
  } catch (error) {
    console.error(error)
    showToast('Erreur lors de la recherche', true)
  }
}

function clearSearchMarkers() {
  tempMarkers.value = []
  searchQuery.value = ''
}

// ---------- GESTION DES PARCELLES ----------
function onMapClick(event) {
  if (confirm('Ajouter une parcelle à cet endroit ?')) {
    openAddModal()
    form.value.lat = event.latlng.lat.toFixed(6)
    form.value.lng = event.latlng.lng.toFixed(6)
  }
}

function openAddModal() {
  editingId.value = null
  form.value = {
    nom: '', culture: '', superficie: null, stade: '',
    datePlantation: '', typeSol: '', lat: '', lng: ''
  }
  showModal.value = true
}

function editParcelle(p) {
  editingId.value = p.id
  form.value = { ...p }
  if (form.value.datePlantation) {
    form.value.datePlantation = form.value.datePlantation.split('T')[0]
  }
  showModal.value = true
}

function closeModal() { 
  showModal.value = false 
}



// ---------- CALENDRIER ----------
const calendarOptions = ref({
  plugins: [dayGridPlugin, interactionPlugin, timeGridPlugin],
  initialView: 'dayGridMonth',
  locale: 'fr',
  editable: true,
  headerToolbar: {
    left: 'prev,next today',
    center: 'title',
    right: 'dayGridMonth,timeGridWeek'
  },
  events: [],
  eventClick: (info) => {
    openEditEventModal(info.event)
  },
  eventDrop: (info) => {
    showToast(`Événement "${info.event.title}" déplacé au ${info.event.start.toISOString().split('T')[0]}`)
  },
 dateClick: onDateClick
})

const generatedEvents = ref([]);

function generateCalendarEvents() {
  const events = []
  parcelles.value.forEach(p => {
    if (p.datePlantation) {
      const plantDate = new Date(p.datePlantation)
      if (!isNaN(plantDate)) {
        const harvestStart = new Date(plantDate)
        harvestStart.setDate(plantDate.getDate() + 90)
        const harvestEnd = new Date(harvestStart)
        harvestEnd.setDate(harvestStart.getDate() + 7)
        events.push({
          title: `🌾 Récolte ${p.nom} (${p.culture})`,
          start: harvestStart.toISOString().split('T')[0],
          end: harvestEnd.toISOString().split('T')[0],
          backgroundColor: '#2D7A4F',
          extendedProps: { parcelleId: p.id, type: 'recolte' }
        })
      }
    }
    const stress = getWaterStress(p)
    if (stress === 'Critique') {
      const tomorrow = new Date()
      tomorrow.setDate(tomorrow.getDate() + 1)
      events.push({
        title: `💧 Irriguer ${p.nom} (urgence)`,
        start: tomorrow.toISOString().split('T')[0],
        backgroundColor: '#d94f3d',
        extendedProps: { parcelleId: p.id, type: 'irrigation' }
      })
    }
  })
  generatedEvents.value = events;
  updateCalendarDisplay();
}

function updateCalendarDisplay() {
  calendarOptions.value.events = [...dbEvents.value, ...generatedEvents.value];
}

function openNoteModal(dateStr) {
  noteDate.value = dateStr
  noteText.value = ''
  showNoteModal.value = true
}

async function saveNote() {
  try {
    if (!noteText.value.trim()) return;
    const newEvent = {
      title: `📝 ${noteText.value}`,
      start_date: noteDate.value,
      type: 'note',
      background_color: '#facc15'
    };
    const saved = await saveEventToBackend(newEvent);
    dbEvents.value.push({
      id: saved.id,
      title: saved.title,
      start: saved.start_date,
      backgroundColor: saved.background_color,
      extendedProps: {
        type: saved.type,
        description: saved.description
      }
    });
    updateCalendarDisplay();
    
    noteText.value = '';
    showNoteModal.value = false;
    showToast('Note ajoutée');
  } catch (error) {
    console.error('Erreur saveNote', error);
    showToast('Erreur lors de l\'ajout de la note', true);
  }
}

function openEditEventModal(event) {
  editingEvent.value = event
  editNoteText.value = event.title.replace(/^📝 /, '')
  editEventId.value = event.id
  showEditModal.value = true
}

async function updateEvent() {
  if (!editingEvent.value) return;
  const newTitle = `📝 ${editNoteText.value}`;
  await updateEventInBackend(editEventId.value, { title: newTitle });
  editingEvent.value.setProp('title', newTitle);
  // Mettre à jour aussi dans dbEvents
  const index = dbEvents.value.findIndex(e => e.id == editEventId.value);
  if (index !== -1) {
    dbEvents.value[index].title = newTitle;
    updateCalendarDisplay();
  }
  showToast('Note modifiée');
  showEditModal.value = false;
}

async function deleteEvent() {
  if (!editEventId.value) return;
  try {
    const idToDelete = editEventId.value;
    // 1. Mise à jour de la source
    dbEvents.value = dbEvents.value.filter(e => e.id != idToDelete);
    // 2. Mise à jour du calendrier (fusionne dbEvents et generatedEvents)
    updateCalendarDisplay();
    
    showEditModal.value = false;
    showToast('Événement supprimé');

    // 3. Appel au backend (si erreur, recharge)
    await deleteEventFromBackend(idToDelete);
  } catch (error) {
    console.error(error);
    showToast('Erreur lors de la suppression', true);
    loadCalendarEvents(); // recharge les deux listes depuis le serveur
  }
}

// ---------- LIFECYCLE ----------
onMounted(() => {
  fetchWeather();
  fetchParcelles();
  loadCalendarEvents();
  checkUpcomingEvents(); // notification du jour et demain (J-1)
  loadNotifications(); // charger les notifications persistantes
});
</script>

<style scoped>
/* Reset layout principal */
.parcelle-container {
  display: flex;
  height: 100vh;
  width: 100%;
}
.main-content {
  flex: 1;
  overflow-y: auto;
  padding: 2rem;
  background: #fafcf8;
}

/* Toast notification */
.toast-notification {
  position: fixed;
  top: 20px;
  right: 20px;
  background: #2D7A4F;
  color: white;
  padding: 12px 24px;
  border-radius: 40px;
  font-size: 14px;
  font-weight: 500;
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  z-index: 2000;
  animation: slideIn 0.3s ease;
}
.toast-notification.error {
  background: #d94f3d;
}
@keyframes slideIn {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

/* En-tête et barre de recherche */
.header-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2px;
  margin-top: -20px;
padding: 5px;
  flex-wrap: wrap;
  gap: 1rem;

}
.search-bar {
  display: flex;
  gap: 10px;
  align-items: center;
}
.search-wrapper {
  position: relative;
  width: 300px;
}
.search-input {
  width: 100%;
  padding: 10px 40px 10px 20px;
  border-radius: 50px;
  border: 1px solid #e2e8f0;
  background: white;
  font-size: 14px;
  transition: all 0.2s ease;
  box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}
.search-input:focus {
  border-color: #2D7A4F;
  box-shadow: 0 0 0 3px rgba(45,122,79,0.1);
  outline: none;
}
.clear-icon {
  position: absolute;
  right: -40px;
  top: 50%;
  transform: translateY(-50%);
  cursor: pointer;
  color: #94a3b8;
  font-size: 18px;
  transition: color 0.2s;
}
.clear-icon:hover {
  color: #433303;
}
.btn-add, .btn-export {
  padding: 0.5rem 1rem;
  border-radius: 30px;
  border: none;
  cursor: pointer;
  font-weight: 600;
}
.btn-add {
  background: #2D7A4F;
  color: white;
}
.btn-export {
  background: #EDE8DC;
  color: #1a4d2e;
  margin-left: 0.5rem;
  border: 1px solid #433303;
}

/* Carte */
.carte-section {
  background: #fffbf1;
  border-radius: 24px;
 padding: 10px;
  margin-bottom: 2rem;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}
.region, .carte-info {
  margin-top: 8px;
  font-size: 0.8rem;
  color: #6B6458;
  text-align: center;
}
.popup-content {
  font-family: 'Inter', sans-serif;
  font-size: 13px;
  line-height: 1.4;
  min-width: 220px;
}
.popup-content.search-result {
  min-width: 200px;
  font-size: 12px;
  line-height: 1.5;
}
.popup-content small {
  color: #6B6458;
  display: block;
  margin-top: 6px;
}

/* Liste des parcelles - Grille 2 colonnes */
.parcelle-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
}

/* Style de la carte */
.parcelle-card {
  border: 1px solid #ddd;
  border-radius: 0.5rem;
  overflow: hidden;
  background: white;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.card-header {
  background: #f5f5f5;
  padding: 0.75rem;
  cursor: pointer;
  font-weight: bold;
  border-bottom: 1px solid #ddd;
}

.card-details {
  padding: 0.75rem;
}

/* Disposition des mesures : 2 par ligne (sauf NPK qui peut avoir 3) */
.sensor-row {
  display: flex;
  gap: 1rem;
  margin-bottom: 1rem;
  flex-wrap: wrap;
}

.sensor-item {
  flex: 1;
  min-width: 80px;
  border:1px solid #eee;
  padding: 4px 8px;
  border-radius: 5px; 
  background-color: #FAF7F0;/* légèrement réduit pour permettre 3 items */
}

.sensor-value {
  display: flex;
  align-items: baseline;
  gap: 0.2rem;
  font-size: 1.1rem;
  font-weight: bold;
  
}
.val-orange {
  color: #f59e0b;
}
.val-green {
  color: #2D7A4F;
}
.val-torabi{
  color:#c79339
}
.val-blue2
{
  color:#5BC0DE
}
.val-blue {
  color: #2c6e9e;
 
}
.val-gold
{
  color: #e84703;
}
.sensor-unit {
  font-size: 0.8rem;
  font-weight: normal;
  color: #666;
}

.sensor-bar {
  background: #e0e0e0;
  border-radius: 4px;
  height: 6px;
  width: 100%;
  margin-top: 4px;
  overflow: hidden;
}
.sensor-bar-fill {
  height: 100%;
  background: #3A7DBF;
  border-radius: 4px;
}

.sensor-label {
  font-size: 14px;
  color: #555;
  margin-top: 2px;
}
.action-reco{
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.problems, .action-reco {
  margin-top: 0.75rem;
  padding-top: 0.5rem;
  border-top: 1px solid #eee;
}
.problem-tag {
  background: #ffeeee;
  color: #b00020;
  padding: 0.2rem 0.5rem;
  border-radius: 4px;
  font-size: 0.8rem;
  display: inline-block;
  margin-right: 0.5rem;
}
.action-reco ul {
  margin: 0.25rem 0 0 1.25rem;
  padding: 0;
}
.btn-edit {
  margin-top: 0.5rem;
  background: #2D7A4F;
  color: white;
  border: none;
  padding: 0.25rem 0.75rem;
  border-radius: 4px;
  cursor: pointer;
}

/* Modal */
.modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(4px);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  background: #ffffff;
  border-radius: 32px;
  padding: 2rem;
  width: 700px;
  max-width: 90%;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  animation: fadeInUp 0.3s ease-out;
  line-height: 30px;
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.modal-content h3 {
  font-size: 1.5rem;
  font-weight: 600;
  margin-bottom: 1.5rem;
  color: #1a2e1a;
  border-left: 4px solid #2D7A4F;
  padding-left: 1rem;
}

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem 1.5rem;
  margin-bottom: 1rem;
}

.form-field {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.form-field label {
  font-size: 14px;
  font-family: Georgia, 'Times New Roman', Times, serif;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: #304220;
}

.form-field input,
.form-field select {
  padding: 10px 14px;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  font-size: 0.9rem;
  transition: all 0.2s;
  background: #fefefe;
}

.form-field input:focus,
.form-field select:focus {
  outline: none;
  border-color: #2D7A4F;
  box-shadow: 0 0 0 3px rgba(45, 122, 79, 0.1);
}

.coords-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
  margin: 0.5rem 0 1.5rem;
}

.full-width {
  width: 100%;
}

.modal-buttons {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 0.5rem;
}

.modal-buttons button {
  padding: 10px 20px;
  border-radius: 40px;
  font-weight: 600;
  font-size: 0.85rem;
  cursor: pointer;
  transition: all 0.2s;
  border: none;
}

.modal-buttons button[type="submit"] {
  background: #2D7A4F;
  color: white;
  box-shadow: 0 2px 6px rgba(45, 122, 79, 0.3);
}

.modal-buttons button[type="submit"]:hover {
  background: #1f5a3a;
  transform: translateY(-1px);
}

.modal-buttons button[type="button"] {
  background: #f1f5f9;
  color: #334155;
}

.modal-buttons button[type="button"]:hover {
  background: #e2e8f0;
}

@media (max-width: 640px) {
  .form-grid,
  .coords-row {
    grid-template-columns: 1fr;
    gap: 0.8rem;
  }
  .modal-content {
    padding: 1.5rem;
  }
}

/* Calendrier */
.calendar-section {
  background: white;
  border-radius: 24px;
  padding: 1.5rem;
  margin-top: 2rem;
  box-shadow: 0 2px 8px rgba(142, 59, 59, 0.05);
}
.calendar-section h3 {
  margin-bottom: 0.5rem;
  font-size: 1.3rem;
  color: #1a4d2e;
}
.calendar-section .region {
  margin-bottom: 1rem;
}

.fc {
  max-height: 600px;
  overflow-x: auto;
}

.fc-event {
  font-size: 0.8rem;
  cursor: pointer;
  border-radius: 8px;
}

/* Modal de note */
.note-modal .note-modal-content {
  width: 400px;
  max-width: 90%;
}
.note-date {
  font-size: 0.85rem;
  color: #4a5b3a;
  margin-bottom: 1rem;
}
.note-textarea {
  width: 100%;
  padding: 10px;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  font-family: inherit;
  font-size: 0.9rem;
  resize: vertical;
}
.note-textarea:focus {
  outline: none;
  border-color: #2D7A4F;
  box-shadow: 0 0 0 3px rgba(45,122,79,0.1);
}
.btn-save, .btn-cancel {
  padding: 8px 16px;
  border-radius: 30px;
  border: none;
  cursor: pointer;
  font-weight: 500;
}
.btn-save {
  background: #2D7A4F;
  color: white;
}
.btn-cancel {
  background: #e2e8f0;
  color: #334155;
}
.btn-delete {
  background: #d94f3d;
  color: white;
  padding: 8px 16px;
  border-radius: 30px;
  border: none;
  cursor: pointer;
  font-weight: 500;
}
.btn-delete:hover {
  background: #b91c1c;
}
.choice-modal-content {
  width: 350px;
  text-align: center;
}
.choice-buttons {
  display: flex;
  gap: 1rem;
  justify-content: center;
  margin: 1.5rem 0;
}
.btn-choice {
  padding: 8px 20px;
  border-radius: 40px;
  border: none;
  cursor: pointer;
  font-weight: bold;
}
.btn-choice.note {
  background: #ae9637;
  color: #1e293b;
}
.btn-choice.action {
  background: #356b4d;
  color: white;
}/* Croix de fermeture dans la modale de choix */
.close-modal-btn {
  position: absolute;
  top: 15px;
  right: 15px;
  background: transparent;
  border: none;
  font-size: 20px;
  font-weight: bold;
  cursor: pointer;
  color: #888;
  transition: color 0.2s;
}
.close-modal-btn:hover {
  color: #d94f3d;
}
.choice-modal-content {
  position: relative; /* pour que la croix soit positionnée par rapport à la modale */
  text-align: center;
}
.btn-notif {
 
  border: 1px solid #dfe4ea;
  padding: 8px 16px;
  margin-left: 10px;
  border-radius: 30px;
  position: relative;
}
.badge {
  background: #d94f3d;
  color: white;
  border-radius: 30px;
  padding: 2px 8px;
  font-size: 12px;
  margin-left: 8px;
}
.notif-item {
  border-bottom: 1px solid #e2e8f0;
  padding: 12px 0;
}
.notif-item.read {
  opacity: 0.6;
}
.notif-title {
  font-weight: bold;
}
.notif-message {
  margin: 4px 0;
  color: #4a5568;
}
.notif-modal {
  width: 500px;
}
.empty-state {
  text-align: center;
  padding: 2rem;
  color: #94a3b8;
}
.btn-read {
  margin-top: 8px;
  background: #f1f5f9;
  border: 1px solid #e2e8f0;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 12px;
  cursor: pointer;
  transition: all 0.2s;
}
.btn-read:hover {
  background: #e2e8f0;
}

</style>