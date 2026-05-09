<template>
  <div class="agrobot-container">
    <div class="chat-box" ref="chatBox">
      <div v-for="(msg, idx) in messages" :key="idx" :class="msg.role">
        <div class="bubble">
          <strong>{{ msg.role === 'user' ? '👨‍🌾 Vous' : '🌾 AgroBot' }}</strong>
          <p>{{ msg.text }}</p>
          <!-- Pas de lecteur audio -->
        </div>
      </div>
    </div>
    <div class="input-area">
      <button 
        @click="toggleRecording" 
        :disabled="isProcessing" 
        :class="['mic-btn', { recording: isRecording }]"
      >
        {{ isRecording ? '⏹️ Arrêter' : '🎤 Parler' }}
      </button>
      <input type="text" v-model="textInput" @keyup.enter="sendText" placeholder="Ou écrivez votre question..." />
      <button @click="sendText" class="send-btn" :disabled="isProcessing">Envoyer</button>
    </div>
    <div v-if="isProcessing" class="processing-indicator">🔄 Traitement en cours...</div>
  </div>
</template>

<script setup>
import { ref, nextTick, onMounted } from 'vue'
import axios from 'axios'

const messages = ref([])
const textInput = ref('')
const isRecording = ref(false)
const isProcessing = ref(false)
let mediaRecorder = null
let audioChunks = []

const addMessage = (role, text) => {
  messages.value.push({ role, text })
  nextTick(() => {
    const box = document.querySelector('.chat-box')
    if (box) box.scrollTop = box.scrollHeight
  })
}

const sendToBackend = async (question, history = '') => {
  isProcessing.value = true
  try {
    const response = await axios.post('/api/agrobot/ask', { question, history })
    const data = response.data
    // On ignore totalement l'audio
    addMessage('assistant', data.text)
    return data.text
  } catch (err) {
    console.error(err)
    addMessage('assistant', "❌ Désolé, le service est indisponible.")
  } finally {
    isProcessing.value = false
  }
}

const sendText = async () => {
  if (!textInput.value.trim()) return
  const question = textInput.value
  textInput.value = ''
  addMessage('user', question)
  const history = messages.value.slice(-5).map(m => `${m.role === 'user' ? 'Agriculteur' : 'AgroBot'}: ${m.text}`).join('\n')
  await sendToBackend(question, history)
}

const startRecording = async () => {
  audioChunks = []
  const stream = await navigator.mediaDevices.getUserMedia({ audio: true })
  mediaRecorder = new MediaRecorder(stream)
  mediaRecorder.ondataavailable = (event) => {
    audioChunks.push(event.data)
  }
  mediaRecorder.onstop = async () => {
    const audioBlob = new Blob(audioChunks, { type: 'audio/wav' })
    const formData = new FormData()
    formData.append('audio', audioBlob, 'question.wav')
    isProcessing.value = true
    try {
      const response = await axios.post('/api/agrobot/transcribe', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
      const question = response.data.text
      if (question) {
        addMessage('user', question)
        const history = messages.value.slice(-5).map(m => `${m.role === 'user' ? 'Agriculteur' : 'AgroBot'}: ${m.text}`).join('\n')
        await sendToBackend(question, history)
      } else {
        addMessage('assistant', "❌ Je n'ai pas compris. Réessayez.")
      }
    } catch (err) {
      console.error(err)
      addMessage('assistant', "❌ Erreur de transcription.")
    } finally {
      isProcessing.value = false
      // Arrêter les pistes du stream
      stream.getTracks().forEach(track => track.stop())
    }
  }
  mediaRecorder.start()
  isRecording.value = true
}

const stopRecording = () => {
  if (mediaRecorder && mediaRecorder.state === 'recording') {
    mediaRecorder.stop()
    isRecording.value = false
  }
}

const toggleRecording = async () => {
  if (isRecording.value) {
    stopRecording()
  } else {
    await startRecording()
  }
}

// Message de bienvenue au chargement
onMounted(() => {
  setTimeout(() => {
    // Envoyer "Bonjour" automatiquement
    sendToBackend("Bonjour", "")
  }, 500)
})
</script>

<style scoped>
.agrobot-container {
  display: flex;
  flex-direction: column;
  height: 80vh;
  max-width: 800px;
  margin: auto;
  background: #f7f9f5;
  border-radius: 24px;
  overflow: hidden;
  box-shadow: 0 8px 20px rgba(0,0,0,0.1);
}
.chat-box {
  flex: 1;
  overflow-y: auto;
  padding: 20px;
}
.user, .assistant {
  margin-bottom: 16px;
  display: flex;
}
.user {
  justify-content: flex-end;
}
.bubble {
  max-width: 70%;
  padding: 12px 16px;
  border-radius: 20px;
  background: white;
  box-shadow: 0 1px 2px rgba(0,0,0,0.1);
}
.user .bubble {
  background: #1a4d2e;
  color: white;
}
.assistant .bubble {
  background: #e9f0e6;
  color: #1e2a1c;
}
.input-area {
  display: flex;
  gap: 12px;
  padding: 16px;
  background: white;
  border-top: 1px solid #ddd;
}
.mic-btn, .send-btn {
  padding: 8px 16px;
  border-radius: 40px;
  border: none;
  background: #2D7A4F;
  color: white;
  cursor: pointer;
}
.mic-btn.recording {
  background: #c0392b;
}
.mic-btn:disabled, .send-btn:disabled {
  background: #aaa;
  cursor: not-allowed;
}
.input-area input {
  flex: 1;
  padding: 10px;
  border-radius: 40px;
  border: 1px solid #ccc;
}
.processing-indicator {
  text-align: center;
  padding: 8px;
  font-size: 0.9em;
  color: #555;
  background: #f0f0f0;
  border-top: 1px solid #ddd;
}
</style>