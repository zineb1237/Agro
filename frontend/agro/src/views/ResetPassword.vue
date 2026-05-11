<template>
  <div class="auth-container">
    <div class="field-decoration field-left"></div>
    <div class="field-decoration field-right"></div>
    
    <div class="auth-card">
      <div class="card-header">
        <div class="leaf-decoration leaf-1"></div>
        <div class="leaf-decoration leaf-2"></div>
        <h1><span>فلاح</span></h1>
        <div class="subtitle">Nouveau mot de passe</div>
      </div>
      
      <p class="instruction-text">
        Sécurisez votre compte en choisissant un nouveau mot de passe robuste.
      </p>

      <form @submit.prevent="resetPassword" class="auth-form">
        <div class="form-group">
          <label>Email :</label>
          <div class="input-wrapper">
            <input 
              type="email" 
              v-model="email" 
              required 
              readonly
              autocomplete="email"
            />
          </div>
        </div>

        <div class="form-group">
          <label>Nouveau mot de passe :</label>
          <div class="input-wrapper">
            <input 
              type="password" 
              v-model="password" 
              required 
              placeholder="••••••••"
              autocomplete="new-password"
            />
          </div>
        </div>

        <div class="form-group">
          <label>Confirmer le mot de passe :</label>
          <div class="input-wrapper">
            <input 
              type="password" 
              v-model="password_confirmation" 
              required 
              placeholder="••••••••"
              autocomplete="new-password"
            />
          </div>
        </div>
        
        <div v-if="message" :class="['message-box', isError ? 'error' : 'success']">
          {{ message }}
        </div>
        
        <button type="submit" class="btn-primary" :disabled="loading">
          <span v-if="!loading">Réinitialiser le mot de passe</span>
          <span v-else>Traitement en cours...</span>
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'

const route = useRoute()
const router = useRouter()
const email = ref('')
const password = ref('')
const password_confirmation = ref('')
const message = ref('')
const isError = ref(false)
const loading = ref(false)

onMounted(() => {
  // Pré-remplir l'email s'il est présent dans l'URL
  if (route.query.email) {
    email.value = route.query.email
  }
})

const resetPassword = async () => {
  loading.value = true
  message.value = ''
  const token = route.query.token
  
  if (!token) {
    message.value = 'Token de réinitialisation manquant.'
    isError.value = true
    loading.value = false
    return
  }

  try {
    await axios.post('/api/reset-password', {
      email: email.value,
      password: password.value,
      password_confirmation: password_confirmation.value,
      token: token
    })
    
    message.value = 'Votre mot de passe a été réinitialisé avec succès. Redirection...'
    isError.value = false
    setTimeout(() => router.push('/login'), 2000)
  } catch (error) {
    message.value = error.response?.data?.message || 'Une erreur est survenue lors de la réinitialisation.'
    isError.value = true
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
/* Copié de ForgotPassword.vue pour la cohérence */
.auth-container {
  height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #1a2f1a 0%, #2c4a2c 50%, #1f3b1a 100%);
  position: relative;
  overflow: hidden;
}

.auth-card {
  background: #FDFBF7;
  border-radius: 32px;
  max-width: 460px;
  width: 100%;
  padding: 40px 36px;
  box-shadow: 0 25px 45px -12px rgba(0, 0, 0, 0.4);
  position: relative;
  border: 1px solid rgba(200, 146, 42, 0.2);
}

.card-header {
  text-align: center;
  margin-bottom: 32px;
}

.card-header h1 span {
  font-family: 'Cormorant Garamond', serif;
  font-weight: 500;
  background: linear-gradient(135deg, #C8922A, #936E2C);
  background-clip: text;
  -webkit-background-clip: text;
  color: transparent;
  font-size: 38px;
}

.subtitle {
  font-family: 'IBM Plex Mono', monospace;
  font-size: 11px;
  letter-spacing: 2px;
  text-transform: uppercase;
  color: #7E6B46;
  margin-top: 8px;
}

.instruction-text {
  text-align: center;
  color: #4A5B3A;
  font-family: Georgia, serif;
  font-size: 14px;
  line-height: 1.6;
  margin-bottom: 24px;
}

.form-group {
    margin-bottom: 16px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-family: Georgia, serif;
  font-weight: 600;
  color: #4A5B3A;
  text-transform: uppercase;
  font-size: 12px;
}

.input-wrapper input {
  width: 100%;
  padding: 14px 20px;
  border: 1.5px solid #EBE3D4;
  border-radius: 60px;
  background: #FFFFFF;
  transition: all 0.2s;
}

.input-wrapper input:focus {
  outline: none;
  border-color: #C8922A;
  box-shadow: 0 0 0 3px rgba(200, 146, 42, 0.2);
}

.input-wrapper input[readonly] {
    background-color: #f5f5f5;
    cursor: not-allowed;
}

.btn-primary {
  width: 100%;
  padding: 14px;
  background: linear-gradient(97deg, #C8922A 0%, #B07E29 100%);
  color: #1A2A0E;
  border: none;
  border-radius: 60px;
  font-family: 'Syne', sans-serif;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 2px;
  cursor: pointer;
  margin-top: 24px;
}

.message-box {
  padding: 12px;
  border-radius: 12px;
  font-size: 13px;
  margin: 16px 0;
  text-align: center;
}

.error { background: #f8d7da; color: #721c24; }
.success { background: #d4edda; color: #155724; }

.field-decoration {
  position: absolute;
  bottom: 0;
  width: 200px;
  height: 120px;
  opacity: 0.15;
}

.field-left { left: 0; background: radial-gradient(circle at 20px 10px, #e8c87a 2px, transparent 2px); background-size: 30px 30px; }
.field-right { right: 0; background: repeating-linear-gradient(45deg, transparent, transparent 15px, #e0b354 15px, #e0b354 18px); }

.leaf-decoration {
  position: absolute;
  width: 40px;
  height: 40px;
  opacity: 0.3;
}
.leaf-1 { top: -15px; left: -10px; background: #b9c49c; clip-path: polygon(0% 0%, 100% 20%, 80% 100%, 0% 80%); }
.leaf-2 { bottom: -10px; right: -15px; background: #dbb45c; clip-path: polygon(50% 0%, 100% 50%, 50% 100%, 0% 50%); }
</style>