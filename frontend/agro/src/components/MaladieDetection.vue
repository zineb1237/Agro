<script setup>
import { ref } from 'vue'

const imagePreview = ref(null)
const isLoading = ref(false)
const detectionResult = ref(null)
const isDragging = ref(false)

const maladies = [
  { 
    nom: 'Rouille brune', 
    recommandation: 'Appliquer un fongicide spécifique à base de triazoles et surveiller l\'humidité.',
    severite: 'Moyenne'
  },
  { 
    nom: 'Oïdium', 
    recommandation: 'Utiliser un traitement au soufre et assurer une meilleure aération des cultures.',
    severite: 'Faible'
  },
  { 
    nom: 'Fusariose', 
    recommandation: 'Rotation des cultures recommandée et traitement des semences immédiat.',
    severite: 'Élevée'
  },
  { 
    nom: 'Tache septorienne', 
    recommandation: 'Éliminer les débris de culture et appliquer un fongicide protecteur.',
    severite: 'Moyenne'
  }
]

const handleFileUpload = (event) => {
  const file = event.target.files[0]
  if (file) {
    loadImage(file)
  }
}

const onDrop = (event) => {
  isDragging.value = false
  const file = event.dataTransfer.files[0]
  if (file && file.type.startsWith('image/')) {
    loadImage(file)
  }
}

const loadImage = (file) => {
  const reader = new FileReader()
  reader.onload = (e) => {
    imagePreview.value = e.target.result
    detectionResult.value = null
  }
  reader.readAsDataURL(file)
}

const analyserImage = () => {
  if (!imagePreview.value) return
  
  isLoading.value = true
  detectionResult.value = null
  
  setTimeout(() => {
    const randomIndex = Math.floor(Math.random() * maladies.length)
    detectionResult.value = maladies[randomIndex]
    isLoading.value = false
    
    // Sauvegarder dans localStorage
    const history = JSON.parse(localStorage.getItem('falah_disease_history') || '[]')
    history.push({
      date: new Date().toISOString(),
      maladie: detectionResult.value.nom,
      image: imagePreview.value.substring(0, 100) + '...' // On ne stocke qu'un extrait pour l'exemple
    })
    localStorage.setItem('falah_disease_history', JSON.stringify(history))
  }, 2000)
}

const reset = () => {
  imagePreview.value = null
  detectionResult.value = null
}
</script>

<template>
  <div class="maladie-container">
    <div class="card">
      <div class="card-header">
        <i class="fas fa-microscope icon"></i>
        <h2>Détection de Maladies</h2>
      </div>
      <p class="subtitle">Analysez l'état de santé de vos plantes via une simple photo.</p>

      <div 
        v-if="!imagePreview"
        class="drop-zone"
        :class="{ 'is-dragging': isDragging }"
        @dragover.prevent="isDragging = true"
        @dragleave.prevent="isDragging = false"
        @drop.prevent="onDrop"
        @click="$refs.fileInput.click()"
      >
        <i class="fas fa-cloud-upload-alt upload-icon"></i>
        <p>Glissez votre photo ici ou <span>parcourez vos fichiers</span></p>
        <small>Formats acceptés : JPG, PNG (Max 5Mo)</small>
        <input 
          type="file" 
          ref="fileInput" 
          @change="handleFileUpload" 
          accept="image/*" 
          hidden 
        />
      </div>

      <div v-else class="preview-area animate-fade-in">
        <div class="image-wrapper">
          <img :src="imagePreview" alt="Aperçu" class="preview-img" />
          <button @click="reset" class="btn-icon-close" title="Supprimer">
            <i class="fas fa-times"></i>
          </button>
        </div>

        <div class="actions">
          <button 
            @click="analyserImage" 
            class="btn-primary" 
            :disabled="isLoading || detectionResult"
          >
            <i v-if="isLoading" class="fas fa-spinner fa-spin"></i>
            <span v-else>Lancer l'analyse IA</span>
          </button>
        </div>
      </div>

      <div v-if="detectionResult" class="result-area animate-slide-up">
        <div class="alert-card">
          <div class="alert-header">
            <i class="fas fa-exclamation-triangle alert-icon"></i>
            <h3>Résultat de l'analyse</h3>
          </div>
          <div class="alert-body">
            <div class="disease-name">
              <span>Maladie détectée :</span>
              <strong>{{ detectionResult.nom }}</strong>
            </div>
            <div class="disease-recommendation">
              <span>Recommandation :</span>
              <p>{{ detectionResult.recommandation }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.maladie-container {
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
}

.subtitle {
  color: var(--text-muted);
  margin-bottom: 2rem;
}

.drop-zone {
  border: 2px dashed var(--border-color);
  border-radius: 12px;
  padding: 3rem 1rem;
  text-align: center;
  cursor: pointer;
  transition: all 0.3s ease;
  background: var(--cream-color);
}

.drop-zone:hover, .drop-zone.is-dragging {
  border-color: var(--leaf-color);
  background: white;
}

.upload-icon {
  font-size: 3rem;
  color: var(--leaf-light-color);
  margin-bottom: 1rem;
}

.drop-zone p {
  color: var(--text-color);
  font-weight: 500;
  margin-bottom: 0.5rem;
}

.drop-zone span {
  color: var(--leaf-color);
  text-decoration: underline;
}

.drop-zone small {
  color: var(--text-muted);
}

.preview-area {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1.5rem;
}

.image-wrapper {
  position: relative;
  max-width: 100%;
}

.preview-img {
  max-width: 100%;
  max-height: 400px;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  display: block;
}

.btn-icon-close {
  position: absolute;
  top: -10px;
  right: -10px;
  background: var(--alert-color);
  color: white;
  border: none;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  cursor: pointer;
  box-shadow: 0 2px 6px rgba(0,0,0,0.2);
  display: flex;
  align-items: center;
  justify-content: center;
  transition: transform 0.2s ease;
}

.btn-icon-close:hover {
  transform: scale(1.1);
}

.btn-primary {
  background: var(--leaf-color);
  color: white;
  border: none;
  padding: 0.8rem 2.5rem;
  border-radius: 30px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.btn-primary:hover:not(:disabled) {
  background: var(--soil-color);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.result-area {
  margin-top: 2rem;
}

.alert-card {
  background: white;
  border: 1px solid var(--alert-color);
  border-left: 6px solid var(--alert-color);
  border-radius: 8px;
  padding: 1.5rem;
}

.alert-header {
  display: flex;
  align-items: center;
  gap: 0.8rem;
  margin-bottom: 1rem;
}

.alert-icon {
  color: var(--alert-color);
  font-size: 1.2rem;
}

h3 {
  margin: 0;
  color: var(--alert-color);
  font-size: 1.1rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.alert-body {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.disease-name strong {
  font-size: 1.2rem;
  color: var(--text-color);
  margin-left: 0.5rem;
}

.disease-recommendation p {
  margin: 0.5rem 0 0 0;
  color: var(--text-muted);
  line-height: 1.5;
}

@keyframes slideUp {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

.animate-slide-up {
  animation: slideUp 0.6s cubic-bezier(0.23, 1, 0.32, 1);
}

.animate-fade-in {
  animation: fadeIn 0.4s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}
</style>
