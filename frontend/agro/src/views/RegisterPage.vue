<template>
  <div class="auth-container">
    <!-- Décors agricoles de fond -->
    <div class="field-decoration field-left"></div>
    <div class="field-decoration field-right"></div>
    
    <div class="auth-card">
      <button class="close-home-btn" @click="goHome" aria-label="Retour à l'accueil">✕</button>

      <div class="card-header">
        <div class="leaf-decoration leaf-1"></div>
        <div class="leaf-decoration leaf-2"></div>
        <h1><span>فلاح</span></h1>
        <div class="subtitle">Rejoignez la communauté agricole</div>
      </div>
      
      <form @submit.prevent="handleRegister" class="auth-form">
        <!-- Ligne 1 : Nom + Email -->
        <div class="form-row">
          <div class="form-group">
            <label>Nom complet :</label>
            <div class="input-wrapper">
              <input type="text" v-model="name" required autocomplete="name" />
            </div>
          </div>
          <div class="form-group">
            <label>Email :</label>
            <div class="input-wrapper">
              <input type="email" v-model="email" required autocomplete="email" />
            </div>
          </div>
        </div>

        <!-- Ligne 2 : Mot de passe + Confirmation -->
        <div class="form-row">
          <div class="form-group">
            <label>Mot de passe :</label>
            <div class="input-wrapper">
              <input type="password" v-model="password" required placeholder="••••••••" autocomplete="new-password" />
            </div>
          </div>
          <div class="form-group">
            <label>Confirmer le mot de passe :</label>
            <div class="input-wrapper">
              <input type="password" v-model="confirmPassword" required placeholder="••••••••" autocomplete="off" />
            </div>
          </div>
        </div>

        <!-- NOUVELLE LIGNE 3 : Ville + Téléphone -->
        <div class="form-row">
          <div class="form-group">
            <label>Ville :</label>
            <div class="input-wrapper">
              <input type="text" v-model="ville" placeholder="ex: El Jadida" autocomplete="address-level2" />
            </div>
          </div>
          <div class="form-group">
            <label>Téléphone :</label>
            <div class="input-wrapper">
              <input type="tel" v-model="telephone" placeholder="06 XX XX XX XX" autocomplete="tel" />
            </div>
          </div>
        </div>

        <div v-if="errorMessage" class="error-message">{{ errorMessage }}</div>

        <button type="submit" class="btn-primary" :disabled="isLoading">
          <span v-if="!isLoading">Créer mon espace</span>
          <span v-else>Inscription en cours...</span>
        </button>
      </form>
      
      <div class="auth-footer">
        <p class="auth-link">
          Déjà un compte agricole ? 
          <router-link to="/login">Se connecter</router-link>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()
const name = ref('')
const email = ref('')
const password = ref('')
const confirmPassword = ref('')
const ville = ref('')        // nouvelle donnée
const telephone = ref('')    // nouvelle donnée
const isLoading = ref(false)
const errorMessage = ref('')

axios.defaults.baseURL = 'http://localhost:8000'
axios.defaults.headers.common['Accept'] = 'application/json'
axios.defaults.headers.common['Content-Type'] = 'application/json'

const handleRegister = async () => {
  errorMessage.value = ''

  // Validation simple
  if (!name.value || !email.value || !password.value || !confirmPassword.value) {
    errorMessage.value = 'Veuillez remplir tous les champs obligatoires'
    return
  }
  if (password.value !== confirmPassword.value) {
    errorMessage.value = 'Les mots de passe ne correspondent pas'
    return
  }

  isLoading.value = true

  try {
    const response = await axios.post('/api/register', {
      name: name.value,
      email: email.value,
      password: password.value,
      password_confirmation: confirmPassword.value,
      ville: ville.value,           // envoyé au backend
      telephone: telephone.value    // envoyé au backend
    })

    localStorage.setItem('token', response.data.token)
    localStorage.setItem('user', JSON.stringify(response.data.user))
    router.push('/dashboard')
  } catch (error) {
    if (error.response && error.response.data.errors) {
      const errors = error.response.data.errors
      errorMessage.value = Object.values(errors).flat().join(' ')
    } else if (error.response && error.response.data.message) {
      errorMessage.value = error.response.data.message
    } else {
      errorMessage.value = 'Erreur de connexion au serveur'
    }
  } finally {
    isLoading.value = false
  }
}

const goHome = () => {
  router.push('/')
}
</script>

<style scoped>
/* Le style reste exactement le même que votre code existant */
.error-message {
  background: #f8d7da;
  color: #721c24;
  padding: 12px;
  border-radius: 12px;
  margin-top: 20px;
  font-family: monospace;
  font-size: 13px;
  text-align: center;
}
:global(body) {
  height: 100vh;
  margin: 0;
  padding: 0;
}
.auth-container {
  height: 100vh;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #1a2f1a 0%, #2c4a2c 50%, #1f3b1a 100%);
  padding: 16px;
  position: relative;
}
.field-decoration {
  position: absolute;
  bottom: 0;
  width: 200px;
  height: 120px;
  background-repeat: repeat-x;
  opacity: 0.15;
  pointer-events: none;
}
.field-left {
  left: 0;
  background: radial-gradient(circle at 20px 10px, #e8c87a 2px, transparent 2px);
  background-size: 30px 30px;
  transform: rotate(5deg);
}
.field-right {
  right: 0;
  bottom: 20px;
  background: repeating-linear-gradient(45deg, transparent, transparent 15px, #e0b354 15px, #e0b354 18px);
  width: 250px;
  height: 100px;
  opacity: 0.1;
}
.auth-card {
  background: #FDFBF7;
  border-radius: 32px;
  max-width: 640px;
  width: 100%;
  max-height: 97vh;
  overflow-y: auto;
  padding: 32px 28px;
  box-shadow: 0 25px 45px -12px rgba(0, 0, 0, 0.4), inset 0 1px 0 rgba(255,255,255,0.6);
  position: relative;
  transition: transform 0.3s ease;
  border: 1px solid rgba(200, 146, 42, 0.2);
}
.auth-card::-webkit-scrollbar {
  width: 4px;
}
.auth-card::-webkit-scrollbar-track {
  background: #EDE6D8;
  border-radius: 4px;
}
.auth-card::-webkit-scrollbar-thumb {
  background: #C8922A;
  border-radius: 4px;
}
.auth-card:hover {
  transform: translateY(-4px);
}
.close-home-btn {
  position: absolute;
  top: 20px;
  right: 24px;
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: #F5F0E8;
  border: 1px solid #EDE6D8;
  font-size: 20px;
  font-weight: 300;
  color: #7E6B46;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
  z-index: 10;
  font-family: monospace;
}
.close-home-btn:hover {
  background: #C8922A;
  color: white;
  border-color: #C8922A;
  transform: scale(1.05);
}
.card-header {
  text-align: center;
  margin-bottom: 24px;
  position: relative;
}
.card-header h1 {
  font-family: 'Cormorant Garamond', serif;
  font-size: 38px;
  font-weight: 700;
  color: #2C3E1F;
  letter-spacing: -0.5px;
  margin: 0;
  display: flex;
  align-items: baseline;
  justify-content: center;
  gap: 8px;
  flex-wrap: wrap;
}
.card-header h1 span {
  font-weight: 500;
  background: linear-gradient(135deg, #C8922A, #936E2C);
  background-clip: text;
  -webkit-background-clip: text;
  color: transparent;
  font-size: 32px;
}
.subtitle {
  font-family:Cambria, Cochin, Georgia, Times, 'Times New Roman', serif;
  font-size: 13px;
  letter-spacing: 2px;
  text-transform: uppercase;
  color: #7E6B46;
  margin-top: 8px;
  border-top: 1px solid #EDE6D8;
  display: inline-block;
  padding-top: 8px;
}
.leaf-decoration {
  position: absolute;
  width: 40px;
  height: 40px;
  background: radial-gradient(circle, #92a56e 1px, transparent 1px);
  background-size: 8px 8px;
  border-radius: 80% 0 80% 0;
  opacity: 0.3;
}
.leaf-1 {
  top: -15px;
  left: -10px;
  transform: rotate(45deg);
  background: #b9c49c;
  clip-path: polygon(0% 0%, 100% 20%, 80% 100%, 0% 80%);
}
.leaf-2 {
  bottom: -10px;
  right: -15px;
  transform: rotate(135deg);
  width: 50px;
  height: 50px;
  background: #dbb45c;
  clip-path: polygon(50% 0%, 100% 50%, 50% 100%, 0% 50%);
  opacity: 0.2;
}
.auth-form {
  margin-top: 4px;
}
.form-row {
  display: flex;
  gap: 20px;
  margin-bottom: 20px;
  flex-wrap: wrap;
}
.form-row .form-group {
  flex: 1;
  min-width: 0;
}
@media (max-width: 540px) {
  .form-row {
    flex-direction: column;
    gap: 16px;
  }
}
.form-group label {
  display: flex;
  align-items: center;
  gap: 6px;
  margin-bottom: 10px;
  font-family: Georgia, 'Times New Roman', Times, serif;
  font-size: 14px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 1px;
  color: #4A5B3A;
}
.input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}
.input-wrapper input {
  width: 100%;
  padding: 14px 16px;
  border: 1.5px solid #EBE3D4;
  border-radius: 60px;
  font-size: 15px;
  font-family: 'Inter', system-ui, sans-serif;
  background: #FFFFFF;
  transition: all 0.2s;
  color: #1E2A10;
}
.input-wrapper input:focus {
  outline: none;
  border-color: #C8922A;
  box-shadow: 0 0 0 3px rgba(200, 146, 42, 0.2);
}
.btn-primary {
  width: 100%;
  padding: 14px 20px;
  background: linear-gradient(97deg, #2D7A4F 0%, #1F5A3A 100%);
  color: #FDFBF7;
  border: none;
  border-radius: 60px;
  font-family: 'Syne', sans-serif;
  font-size: 14px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 2px;
  cursor: pointer;
  transition: all 0.25s;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}
.btn-primary:hover:not(:disabled) {
  background: linear-gradient(97deg, #3fae73 0%, #2D7A4F 100%);
  transform: scale(0.98);
  box-shadow: 0 8px 20px rgba(45, 122, 79, 0.3);
}
.btn-primary:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}
.auth-footer {
  margin-top: 28px;
  text-align: center;
}
.auth-link {
  font-family: Georgia, 'Times New Roman', Times, serif;
  font-size: 15px;
  margin: 12px 0;
}
.auth-link a {
  color: #2D7A4F;
  text-decoration: none;
  font-weight: 500;
  transition: color 0.2s;
}
.auth-link a:hover {
  color: #C8922A;
  text-decoration: underline;
}
@media (max-width: 540px) {
  .auth-card {
    padding: 28px 20px;
    max-width: 460px;
  }
  .card-header h1 {
    font-size: 32px;
  }
  .card-header h1 span {
    font-size: 28px;
  }
  .close-home-btn {
    top: 16px;
    right: 16px;
    width: 32px;
    height: 32px;
    font-size: 18px;
  }
}
</style>