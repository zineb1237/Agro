<template>
  <div class="prediction-layout">
    <NavBar />
    <div class="prediction-main">
      <div class="prediction-container">
        <!-- Header avec citation -->
        <div class="header-actions">
          <h1>Mes Parcelles</h1>
          <div class="citation">
            "La terre ne ment pas, elle repond a ce qu'on lui donne."
          </div>
        </div>

        <h2>Estimer le rendement d'une parcelle</h2>

        <form @submit.prevent="submitPrediction">
          <div class="form-grid-3cols">
            <div class="field">
              <label>Culture</label>
              <select v-model="form.culture" @change="updateVarietes">
                <option value="ble">Ble</option>
                <option value="pomme_de_terre">Pomme de terre</option>
                <option value="betterave">Betterave</option>
                <option value="olivier">Olivier</option>
              </select>
            </div>
            <div class="field">
              <label>Variete</label>
              <select v-model="form.variete">
                <option v-for="v in varietesDisponibles" :key="v">{{ v }}</option>
              </select>
            </div>
            <div class="field">
              <label>Region</label>
              <select v-model="form.region">
                <option>El Jadida</option>
                <option>Ouled Frej</option>
              </select>
            </div>
            <div class="field">
              <label>Type de sol</label>
              <select v-model="form.type_sol">
                <option v-for="sol in solTypes" :key="sol.nom">{{ sol.nom }}</option>
              </select>
            </div>
            <div class="field">
              <label>Irrigation</label>
              <select v-model="form.irrigation">
                <option :value="0">Bour (non irigue)</option>
                <option :value="1">Irigue</option>
              </select>
            </div>
            <div class="field">
              <label>Date de semis (jour)</label>
              <input type="number" v-model.number="form.semis_jour" min="1" max="365" />
              <span class="hint">320 = 15 nov</span>
            </div>
            <div class="field">
              <label>Azote N (kg/ha)</label>
              <input type="number" v-model.number="form.n_kg_ha" step="5" />
            </div>
            <div class="field">
              <label>Phosphore P2O5 (kg/ha)</label>
              <input type="number" v-model.number="form.p2o5_kg_ha" step="5" />
            </div>
            <div class="field">
              <label>Potassium K2O (kg/ha)</label>
              <input type="number" v-model.number="form.k2o_kg_ha" step="5" />
            </div>
          </div>

          <button type="submit" :disabled="loading">
            {{ loading ? 'Calcul en cours...' : 'Predire le rendement' }}
          </button>
        </form>

        <div v-if="error" class="error">{{ error }}</div>
      </div>
    </div>

    <!-- Modal popup -->
    <div v-if="showPopup" class="modal-overlay" @click.self="closePopup">
      <div class="modal-content">
        <button class="close-btn" @click="closePopup">&times;</button>
        <div class="modal-result">
          <p>Rendement estime</p>
          <div class="value">{{ popupResult }} qx/ha</div>
        </div>
        <div v-if="recommandations && recommandations.length" class="recos">
  <h4>Conseils personnalisés</h4>
  <ul>
    <li v-for="(rec, idx) in recommandations" :key="idx">{{ rec }}</li>
  </ul>
</div>
      </div>
    </div>
  </div>
</template>
<script>
import { ref, computed, watch } from 'vue'
import axios from 'axios'
import NavBar from '@/components/NavBar.vue'

export default {
  name: 'PredictionForm',
  components: { NavBar },
  setup() {
    const solTypes = [
      { nom: 'Argileux', clay: 45, sand: 20, ph: 7.0 },
      { nom: 'Limoneux', clay: 25, sand: 30, ph: 7.2 },
      { nom: 'Sableux', clay: 10, sand: 80, ph: 6.8 },
      { nom: 'Argilo-limoneux', clay: 35, sand: 25, ph: 7.1 },
      { nom: 'Sablo-limoneux', clay: 15, sand: 60, ph: 6.9 }
    ]

    const varietesParCulture = {
      ble: ['Achtar', 'Marzak', 'Karim'],
      pomme_de_terre: ['Spunta', 'Desiree', 'Almera'],
      betterave: ['Monodoro', 'Corbus', 'Montego'],
      olivier: ['Picholine', 'Arbequina', 'Haouzia']
    }

    const npkDefaults = {
      ble: { n: 80, p: 50, k: 80 },
      pomme_de_terre: { n: 100, p: 120, k: 180 },
      betterave: { n: 100, p: 120, k: 180 },
      olivier: { n: 60, p: 40, k: 70 }
    }

    const semisDefaults = {
      ble: 320,
      pomme_de_terre: 320,
      betterave: 289,
      olivier: 320
    }

    const getSolClaySandPh = (typeSol) => {
      const sol = solTypes.find(s => s.nom === typeSol)
      return sol || { clay: 35, sand: 25, ph: 7.1 }
    }

    const form = ref({
      culture: 'ble',
      variete: 'Marzak',
      region: 'El Jadida',
      type_sol: 'Argilo-limoneux',
      irrigation: 0,
      n_kg_ha: 80,
      p2o5_kg_ha: 50,
      k2o_kg_ha: 80,
      semis_jour: 320
    })

    const loading = ref(false)
    const error = ref(null)
    const showPopup = ref(false)
    const popupResult = ref(null)
    const recommandations = ref([])      // <--- AJOUT
    let timeoutId = null

    const varietesDisponibles = computed(() => varietesParCulture[form.value.culture] || [])

    const updateVarietes = () => {
      const newCulture = form.value.culture
      const vars = varietesParCulture[newCulture]
      if (vars && !vars.includes(form.value.variete)) {
        form.value.variete = vars[0]
      }
      const defaults = npkDefaults[newCulture]
      form.value.n_kg_ha = defaults.n
      form.value.p2o5_kg_ha = defaults.p
      form.value.k2o_kg_ha = defaults.k
      form.value.semis_jour = semisDefaults[newCulture]
    }

    watch(() => form.value.culture, updateVarietes)

    const closePopup = () => {
      if (timeoutId) clearTimeout(timeoutId)
      showPopup.value = false
      popupResult.value = null
      recommandations.value = []           // <--- nettoyer
    }

    const submitPrediction = async () => {
      loading.value = true
      error.value = null
      try {
        const sol = getSolClaySandPh(form.value.type_sol)
        const payload = {
          ...form.value,
          clay_pct: sol.clay,
          sand_pct: sol.sand,
          ph: sol.ph
        }

        const response = await axios.post('http://localhost:8000/api/predict', payload)
        const rendement = response.data.rendement_qx_ha
        recommandations.value = response.data.recommandations || []   // <--- AJOUT

        popupResult.value = rendement
        showPopup.value = true

        if (timeoutId) clearTimeout(timeoutId)
        timeoutId = setTimeout(() => {
          showPopup.value = false
          popupResult.value = null
          recommandations.value = []
        }, 40000)
      } catch (err) {
        console.error('Erreur:', err)
        error.value = "Erreur lors de la prediction. Verifiez votre connexion."
      } finally {
        loading.value = false
      }
    }

    return {
      form,
      solTypes,
      varietesDisponibles,
      loading,
      error,
      showPopup,
      popupResult,
      recommandations,    // <--- AJOUT
      closePopup,
      updateVarietes,
      submitPrediction
    }
  }
}
</script>
<style scoped>
/* ===== RESET & COULEURS ===== */
.prediction-layout {
  display: flex;
  height: 100vh;
  background: #F5F0E8;
}

.prediction-main {
  flex: 1;
  overflow-y: auto;
  padding: 2rem;
  background: #F5F0E8;
}

.prediction-container {
  max-width: 1190px;
  background: white;
  border-radius: 28px;
  box-shadow: 0 8px 24px rgba(0,0,0,0.05);
  padding: 2rem;
}

/* ===== HEADER ===== */
.header-actions {
  margin-bottom: 2px;
}
.header-actions h1 {
  font-size: 1.8rem;
  font-weight: 600;
  color: #1A4D2E;
  margin: 0 0 0.4rem 0;
  letter-spacing: -0.3px;
}
.citation {
  font-size: 0.9rem;
  font-style: italic;
  color: #6B6458;
  border-left: 3px solid #C8922A;
  padding-left: 1rem;
  margin-bottom: 1.5rem;
}
h2 {
  font-size: 1.4rem;
  font-weight: 500;
  color: #2D7A4F;
  margin-bottom: 1.8rem;
  padding-bottom: 0.5rem;
  border-bottom: 1px solid #EDE8DC;
}

/* ===== FORMULAIRE 3 COLONNES ===== */
.form-grid-3cols {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1.2rem 1.5rem;
  margin-bottom: 2rem;
}

.field {
  display: flex;
  flex-direction: column;
}

label {
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: #6B6458;
  margin-bottom: 0.4rem;
}

select, input {
  background: #FAF7F0;
  border: 1px solid #e2e0db;
  border-radius: 14px;
  padding: 0.7rem 1rem;
  font-size: 0.9rem;
  font-family: inherit;
  transition: 0.2s;
  color: #1e2a1c;
}

select:focus, input:focus {
  outline: none;
  border-color: #2D7A4F;
  box-shadow: 0 0 0 3px rgba(45,122,79,0.1);
}

.hint {
  font-size: 0.65rem;
  color: #6B6458;
  margin-top: 0.3rem;
}

/* Bouton */
button[type="submit"] {
  width: 100%;
  background: #2D7A4F;
  border: none;
  border-radius: 40px;
  padding: 0.9rem;
  font-size: 1rem;
  font-weight: 600;
  color: white;
  cursor: pointer;
  transition: background 0.2s, transform 0.1s;
  margin-bottom: 1.5rem;
}
button[type="submit"]:hover:not(:disabled) {
  background: #1A4D2E;
  transform: translateY(-2px);
}
button[type="submit"]:disabled {
  background: #b8cfc0;
  cursor: not-allowed;
}

/* Erreur */
.error {
  background: #fff2f0;
  border-left: 4px solid #8B3A2A;
  padding: 0.8rem;
  border-radius: 10px;
  color: #8B3A2A;
  font-size: 0.9rem;
  margin-top: 1rem;
}

/* ===== MODAL (POPUP) ===== */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
  backdrop-filter: blur(3px);
}

.modal-content {
  background: white;
  border-radius: 28px;
  padding: 2rem;
  min-width: 280px;
  max-width: 400px;
  text-align: center;
  position: relative;
  box-shadow: 0 20px 40px rgba(0,0,0,0.2);
  animation: fadeInUp 0.3s ease;
}

.close-btn {
  position: absolute;
  top: 12px;
  right: 16px;
  background: none;
  border: none;
  font-size: 1.8rem;
  cursor: pointer;
  color: #6B6458;
  padding: 0;
  margin: 0;
  width: auto;
  transition: color 0.2s;
}
.close-btn:hover {
  color: #8B3A2A;
  transform: none;
}

.modal-result p {
  font-size: 0.9rem;
  text-transform: uppercase;
  letter-spacing: 1px;
  color: #6B6458;
  margin-bottom: 0.5rem;
}
.modal-result .value {
  font-size: 2.8rem;
  font-weight: 800;
  color: #2D7A4F;
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

/* Responsive */
@media (max-width: 900px) {
  .form-grid-3cols {
    grid-template-columns: repeat(2, 1fr);
  }
}
@media (max-width: 600px) {
  .prediction-container {
    padding: 1.2rem;
  }
  .form-grid-3cols {
    grid-template-columns: 1fr;
  }
  .header-actions h1 {
    font-size: 1.5rem;
  }
}
</style>