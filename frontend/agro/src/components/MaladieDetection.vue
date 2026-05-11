<script setup>
import { ref } from 'vue'
import axios from 'axios'

const fileInput = ref(null)
const selectedFile = ref(null)
const imagePreview = ref(null)
const isLoading = ref(false)
const detectionResult = ref(null)
const isDragging = ref(false)
const errorMessage = ref(null)

const handleFileUpload = (event) => {
  const file = event.target.files[0]
  if (file) {
    prepareImage(file)
  }
}

const onDrop = (event) => {
  isDragging.value = false
  const file = event.dataTransfer.files[0]
  if (file && file.type.startsWith('image/')) {
    prepareImage(file)
  }
}

const prepareImage = (file) => {
  selectedFile.value = file
  errorMessage.value = null
  const reader = new FileReader()
  reader.onload = (e) => {
    imagePreview.value = e.target.result
    detectionResult.value = null
  }
  reader.readAsDataURL(file)
}

const analyserImage = async () => {
  if (!selectedFile.value) return
  
  isLoading.value = true
  detectionResult.value = null
  errorMessage.value = null

  const formData = new FormData()
  formData.append('image', selectedFile.value)

  try {
    const response = await axios.post('http://localhost:8000/api/detect-disease', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })

    if (response.data.success) {
      detectionResult.value = response.data.data
      saveToHistory(detectionResult.value)
    } else {
      errorMessage.value = "Une erreur est survenue lors de l'analyse."
    }
  } catch (error) {
    console.error('Erreur detection:', error)
    errorMessage.value = error.response?.data?.message || "Impossible de contacter le serveur d'analyse."
  } finally {
    isLoading.value = false
  }
}

const saveToHistory = (result) => {
  const history = JSON.parse(localStorage.getItem('falah_disease_history') || '[]')
  history.unshift({
    date: new Date().toLocaleString(),
    plante: result.plante,
    maladie: result.maladie,
    confiance: result.confiance,
    etat: result.etat,
    image: imagePreview.value.substring(0, 100) + '...'
  })
  localStorage.setItem('falah_disease_history', JSON.stringify(history.slice(0, 10)))
}

const reset = () => {
  selectedFile.value = null
  imagePreview.value = null
  detectionResult.value = null
  errorMessage.value = null
}
</script>

<template>
  <div class="maladie-container">
    <div class="card">
      <div class="card-header">
        <div class="header-icon">
          <i class="fas fa-microscope"></i>
        </div>
        <div class="header-text">
          <h2>Diagnostic IA des Plantes</h2>
          <p class="subtitle">Identifiez instantanément les maladies grâce à l'intelligence artificielle.</p>
        </div>
      </div>

      <div 
        v-if="!imagePreview"
        class="drop-zone"
        :class="{ 'is-dragging': isDragging }"
        @dragover.prevent="isDragging = true"
        @dragleave.prevent="isDragging = false"
        @drop.prevent="onDrop"
        @click="fileInput.click()"
      >
        <div class="upload-content">
          <i class="fas fa-cloud-upload-alt upload-icon"></i>
          <h3>Sélectionnez une photo</h3>
          <p>Glissez-déposez ou <span>cliquez pour parcourir</span></p>
          <small>Formats supportés : JPG, PNG (Max 5Mo)</small>
        </div>
        <input 
          type="file" 
          ref="fileInput" 
          @change="handleFileUpload" 
          accept="image/*" 
          hidden 
        />
      </div>

      <div v-else class="preview-container animate-fade-in">
        <div class="preview-card">
          <img :src="imagePreview" alt="Aperçu plante" class="preview-img" />
          <button @click="reset" class="btn-remove" title="Supprimer la photo">
            <i class="fas fa-times"></i>
          </button>
        </div>

        <div class="action-bar">
          <button 
            @click="analyserImage" 
            class="btn-analyze" 
            :disabled="isLoading"
          >
            <template v-if="isLoading">
              <i class="fas fa-circle-notch fa-spin"></i>
              <span>Analyse en cours...</span>
            </template>
            <template v-else>
              <i class="fas fa-magic"></i>
              <span>Lancer le diagnostic IA</span>
            </template>
          </button>
        </div>
      </div>

      <div v-if="errorMessage" class="error-toast animate-slide-up">
        <i class="fas fa-exclamation-circle"></i>
        <p>{{ errorMessage }}</p>
      </div>

      <div v-if="detectionResult" class="result-container animate-slide-up">
        <div class="result-card" :class="detectionResult.etat === 'Saine' ? 'healthy' : 'diseased'">
          <div class="result-header">
            <div class="status-badge">
              <i :class="detectionResult.etat === 'Saine' ? 'fas fa-check-circle' : 'fas fa-exclamation-triangle'"></i>
              {{ detectionResult.etat === 'Saine' ? 'Plante Saine' : 'Anomalie Détectée' }}
            </div>
            <div class="confidence-meter">
              Confiance : <strong>{{ detectionResult.confiance }}%</strong>
            </div>
          </div>

          <div class="result-body">
            <div class="info-row">
              <span class="label">Culture identifiée</span>
              <span class="value">{{ detectionResult.plante }}</span>
            </div>
            <div class="info-row" v-if="detectionResult.maladie !== 'Aucune'">
              <span class="label">Maladie suspectée</span>
              <span class="value disease-name">{{ detectionResult.maladie }}</span>
            </div>
          </div>

          <div class="result-footer" v-if="detectionResult.suggestion">
            <p class="suggestion"><strong>Note :</strong> {{ detectionResult.suggestion }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.maladie-container {
  max-width: 900px;
  margin: 0 auto;
  padding: 1rem;
}

.card {
  background: white;
  border-radius: 24px;
  padding: 2.5rem;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.04);
  border: 1px solid rgba(0, 0, 0, 0.05);
}

.card-header {
  display: flex;
  gap: 1.5rem;
  margin-bottom: 2.5rem;
  align-items: center;
}

.header-icon {
  width: 60px;
  height: 60px;
  background: rgba(61, 107, 53, 0.1);
  border-radius: 18px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.8rem;
  color: #3D6B35;
}

.header-text h2 {
  font-family: 'Fraunces', serif;
  font-size: 1.8rem;
  color: #2D1A0E;
  margin: 0;
}

.subtitle {
  color: #6B5744;
  margin: 0.2rem 0 0 0;
  font-size: 1rem;
}

.drop-zone {
  border: 2px dashed #E8DDD0;
  border-radius: 20px;
  padding: 4rem 2rem;
  text-align: center;
  cursor: pointer;
  transition: all 0.3s ease;
  background: #fdfbf7;
}

.drop-zone:hover, .drop-zone.is-dragging {
  border-color: #f5a623;
  background: #fff9f0;
}

.upload-icon {
  font-size: 3rem;
  color: #D4A853;
  margin-bottom: 1.5rem;
}

.upload-content h3 {
  font-size: 1.2rem;
  margin-bottom: 0.5rem;
  color: #2D1A0E;
}

.upload-content p span {
  color: #f5a623;
  font-weight: 700;
}

.upload-content small {
  display: block;
  margin-top: 1rem;
  color: #999;
}

.preview-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 2rem;
}

.preview-card {
  position: relative;
  width: 100%;
  max-width: 500px;
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 15px 30px rgba(0,0,0,0.1);
}

.preview-img {
  width: 100%;
  height: 350px;
  object-fit: cover;
  display: block;
}

.btn-remove {
  position: absolute;
  top: 15px;
  right: 15px;
  width: 35px;
  height: 35px;
  background: rgba(255, 255, 255, 0.9);
  border: none;
  border-radius: 50%;
  color: #e53935;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.btn-remove:hover {
  background: #e53935;
  color: white;
}

.btn-analyze {
  background: #2d4a1e;
  color: white;
  border: none;
  padding: 1.2rem 3rem;
  border-radius: 14px;
  font-size: 1.1rem;
  font-weight: 700;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 12px;
  transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
  box-shadow: 0 10px 20px rgba(45, 74, 30, 0.2);
}

.btn-analyze:hover:not(:disabled) {
  transform: translateY(-5px);
  box-shadow: 0 15px 30px rgba(45, 74, 30, 0.3);
}

.btn-analyze:disabled {
  background: #ccc;
  cursor: not-allowed;
  box-shadow: none;
}

.result-container {
  margin-top: 3rem;
}

.result-card {
  border-radius: 20px;
  padding: 2rem;
  border-left: 8px solid;
}

.result-card.healthy {
  background: #f1f8e9;
  border-left-color: #4caf50;
}

.result-card.diseased {
  background: #fff5f5;
  border-left-color: #e53935;
}

.result-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.status-badge {
  display: flex;
  align-items: center;
  gap: 10px;
  font-weight: 800;
  text-transform: uppercase;
  font-size: 0.9rem;
}

.healthy .status-badge { color: #2e7d32; }
.diseased .status-badge { color: #c62828; }

.result-body {
  display: grid;
  gap: 1.2rem;
}

.info-row {
  display: flex;
  justify-content: space-between;
  padding-bottom: 0.8rem;
  border-bottom: 1px solid rgba(0,0,0,0.05);
}

.info-row .label {
  color: #6B5744;
  font-weight: 500;
}

.info-row .value {
  font-weight: 800;
  color: #2D1A0E;
}

.disease-name {
  color: #e53935 !important;
}

.result-footer {
  margin-top: 1.5rem;
  padding-top: 1rem;
}

.suggestion {
  font-size: 0.95rem;
  color: #6B5744;
  line-height: 1.5;
}

.error-toast {
  margin-top: 2rem;
  background: #ffebee;
  color: #c62828;
  padding: 1rem 1.5rem;
  border-radius: 12px;
  display: flex;
  align-items: center;
  gap: 12px;
  font-weight: 600;
}

.animate-fade-in { animation: fadeIn 0.5s ease; }
.animate-slide-up { animation: slideUp 0.5s ease; }

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes slideUp {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
