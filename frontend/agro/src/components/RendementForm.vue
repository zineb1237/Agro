<script setup>
import { ref, reactive } from 'vue'

const formData = reactive({
  type: 'Maïs',
  variete: '',
  surface: 1,
  qualiteSol: 'Moyen'
})

const estimation = ref(null)
const isLoading = ref(false)

const typesProduit = ['Maïs', 'Blé', 'Tournesol', 'Orge']
const qualitesSol = ['Pauvre', 'Moyen', 'Bon', 'Excellent']

const coefficients = {
  'Maïs': 8.5,
  'Blé': 7.2,
  'Tournesol': 3.1,
  'Orge': 6.5
}

const solMultipliers = {
  'Pauvre': 0.6,
  'Moyen': 1.0,
  'Bon': 1.3,
  'Excellent': 1.6
}

const estimerRendement = () => {
  isLoading.value = true
  estimation.value = null
  
  // Simuler un appel API
  setTimeout(() => {
    const baseYield = coefficients[formData.type] || 5
    const multiplier = solMultipliers[formData.qualiteSol] || 1
    const yieldPerHa = baseYield * multiplier
    const totalYield = yieldPerHa * formData.surface
    
    estimation.value = {
      perHa: yieldPerHa.toFixed(2),
      total: totalYield.toFixed(2)
    }
    isLoading.value = false
    
    // Sauvegarder dans localStorage
    const history = JSON.parse(localStorage.getItem('falah_yield_history') || '[]')
    history.push({
      date: new Date().toISOString(),
      ...formData,
      result: estimation.value
    })
    localStorage.setItem('falah_yield_history', JSON.stringify(history))
  }, 1200)
}
</script>

<template>
  <div class="rendement-container">
    <div class="card">
      <div class="card-header">
        <i class="fas fa-chart-line icon"></i>
        <h2>Prédiction de Rendement</h2>
      </div>
      <p class="subtitle">Estimez la récolte de votre parcelle en quelques clics.</p>

      <form @submit.prevent="estimerRendement" class="form-grid">
        <div class="form-group">
          <label>Type de produit</label>
          <select v-model="formData.type">
            <option v-for="t in typesProduit" :key="t" :value="t">{{ t }}</option>
          </select>
        </div>

        <div class="form-group">
          <label>Variété / Nom</label>
          <input type="text" v-model="formData.variete" placeholder="ex: Pioneer P0937" required />
        </div>

        <div class="form-group">
          <label>Surface cultivée (ha)</label>
          <input type="number" v-model="formData.surface" step="0.1" min="0.1" required />
        </div>

        <div class="form-group">
          <label>Qualité du sol</label>
          <select v-model="formData.qualiteSol">
            <option v-for="q in qualitesSol" :key="q" :value="q">{{ q }}</option>
          </select>
        </div>

        <button type="submit" class="btn-primary" :disabled="isLoading">
          <i v-if="isLoading" class="fas fa-spinner fa-spin"></i>
          <span v-else>Estimer le rendement</span>
        </button>
      </form>

      <div v-if="estimation" class="result-area animate-fade-in">
        <div class="result-card">
          <div class="result-item">
            <span class="label">Rendement estimé</span>
            <span class="value">{{ estimation.perHa }} <small>tonnes / ha</small></span>
          </div>
          <div class="result-divider"></div>
          <div class="result-item">
            <span class="label">Production totale</span>
            <span class="value">{{ estimation.total }} <small>tonnes</small></span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.rendement-container {
  max-width: 800px;
  margin: 0 auto;
}

.card {
  background: var(--card-bg);
  border: 1px solid var(--border-color);
  border-radius: 16px;
  padding: 2rem;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
}

.card-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 0.5rem;
}

.icon {
  font-size: 1.5rem;
  color: var(--leaf-color);
}

h2 {
  margin: 0;
  color: var(--text-color);
  font-size: 1.5rem;
}

.subtitle {
  color: var(--text-muted);
  margin-bottom: 2rem;
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

label {
  font-weight: 600;
  color: var(--text-color);
  font-size: 0.9rem;
}

input, select {
  padding: 0.8rem 1rem;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  background: var(--cream-color);
  color: var(--text-color);
  font-size: 1rem;
  transition: all 0.3s ease;
}

input:focus, select:focus {
  outline: none;
  border-color: var(--leaf-color);
  box-shadow: 0 0 0 3px rgba(90, 158, 79, 0.1);
}

.btn-primary {
  grid-column: span 2;
  background: var(--leaf-color);
  color: white;
  border: none;
  padding: 1rem;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 0.5rem;
  margin-top: 1rem;
}

.btn-primary:hover {
  background: var(--soil-color);
  transform: translateY(-2px);
}

.btn-primary:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.result-area {
  margin-top: 2rem;
}

.result-card {
  background: var(--cream-color);
  border: 1px solid var(--wheat-color);
  border-radius: 12px;
  padding: 1.5rem;
  display: flex;
  justify-content: space-around;
  align-items: center;
}

.result-item {
  text-align: center;
}

.label {
  display: block;
  font-size: 0.85rem;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: 1px;
  margin-bottom: 0.5rem;
}

.value {
  display: block;
  font-size: 1.8rem;
  font-weight: 800;
  color: var(--leaf-color);
}

.value small {
  font-size: 0.9rem;
  font-weight: 400;
  color: var(--text-muted);
}

.result-divider {
  width: 1px;
  height: 40px;
  background: var(--wheat-color);
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

.animate-fade-in {
  animation: fadeIn 0.5s ease-out;
}

@media (max-width: 600px) {
  .form-grid {
    grid-template-columns: 1fr;
  }
  .btn-primary {
    grid-column: span 1;
  }
  .result-card {
    flex-direction: column;
    gap: 1.5rem;
  }
  .result-divider {
    width: 100%;
    height: 1px;
  }
}
</style>
