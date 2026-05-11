<template>
  <div class="auth-container">
    <!-- Decorative field elements -->
    <div class="field-decoration field-left"></div>
    <div class="field-decoration field-right"></div>
    
    <div class="auth-card">
      <!-- Icône retour accueil en haut à droite -->
      <button class="close-home-btn" @click="goHome" aria-label="Retour à l'accueil">
        ✕
      </button>
<div v-if="errorMessage" class="error-message">
  {{ errorMessage }}
</div>
      <!-- Creative header with agricultural motif -->
      <div class="card-header">
        <div class="leaf-decoration leaf-1"></div>
        <div class="leaf-decoration leaf-2"></div>
        <h1><span>فلاح</span></h1>
          
      </div>
      
      <form @submit.prevent="handleLogin" class="auth-form">
        <div class="form-group">
          <label>
            Email :
          </label>
          <div class="input-wrapper">
            <input 
              type="email" 
              v-model="email" 
              required 
              placeholder="votre@exploitation.agri"
              autocomplete="email"
            />
          </div>
        </div>
        
        <div class="form-group">
          <label>
            Mot de passe :
          </label>
          <div class="input-wrapper">
            <input 
              type="password" 
              v-model="password" 
              required 
              placeholder="••••••••"
              autocomplete="current-password"
            />
          </div>
        </div>
        
        <div class="form-options">
          <label class="checkbox-label">
            <input type="checkbox" v-model="rememberMe" />
            <span>Se souvenir de moi</span>
          </label>
          <router-link to="/forgot-password" class="forgot-link">Mot de passe oublié ?</router-link>
        </div>
        
        <button type="submit" class="btn-primary" :disabled="isLoading">
          <span v-if="!isLoading"> Se connecter</span>
          
          <span v-else>Connexion en cours...</span>
        </button>
      </form>
      <div class="social-login">
  <hr>
  
<!--
  <div class="social-buttons">
    <a href="http://localhost:8000/auth/google/redirect" class="btn-social google">
      <span> Continuer avec Google</span>
    </a>
    <a href="http://localhost:8000/auth/facebook/redirect" class="btn-social facebook">
      <span> Continuer avec Facebook</span>
    </a>
  </div>
-->
</div>

      <div class="auth-footer">
        <p class="auth-link">
          Pas encore de compte agricole ? 
          <router-link to="/register">Créer mon espace</router-link>
        </p>
        <!-- Le lien classique a été retiré car l'icône le remplace, mais on garde la navigation secondaire -->
      </div>
    </div>
  </div>
</template>
<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()
const email = ref('')
const password = ref('')
const rememberMe = ref(false)
const isLoading = ref(false)
const errorMessage = ref('')

// Vérifier si on revient d'une erreur de connexion sociale
const urlParams = new URLSearchParams(window.location.search)
const socialError = urlParams.get('error')
if (socialError) {
  errorMessage.value = socialError
}

const handleLogin = async () => {
  errorMessage.value = ''
  if (!email.value || !password.value) {
    errorMessage.value = 'Veuillez remplir tous les champs'
    return
  }

  isLoading.value = true

  try {
    // Utilisation de l'URL absolue pour éviter les problèmes de baseURL
    const response = await axios.post('/api/login', {
      email: email.value,
      password: password.value,
    })

    localStorage.setItem('token', response.data.token)
    localStorage.setItem('user', JSON.stringify(response.data.user))

    router.push('/dashboard')
  } catch (error) {
    if (error.response && error.response.data.message) {
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
.social-login {
  margin-top: 20px;
}

.social-buttons {
  display: flex;
  gap: 15px;
  justify-content: center;
}

.btn-social {
  flex: 1;
  text-align: center;
  padding: 10px;
  border-radius: 60px;
  color: white;
  text-decoration: none;
  font-family: Georgia, serif;
  transition: all 0.25s;
}

.btn-social.google {
  background: #DB4437;
}

.btn-social.facebook {
  background: #4267B2;
}
/* Garde l'intégralité du style précédent, avec ajout de l'icône close-home-btn */
:global(body) {

  height: 100vh;
  margin: 0;
  padding: 0;
}

.auth-container {
   height: 100vh; /* Au lieu de min-height */
  overflow: hidden; /* Bloque tout scroll interne si jamais */
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #1a2f1a 0%, #2c4a2c 50%, #1f3b1a 100%);
  padding: 16px; /* Réduit le padding pour éviter le débordement */
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
  background: repeating-linear-gradient(
    45deg,
    transparent,
    transparent 15px,
    #e0b354 15px,
    #e0b354 18px
  );
  width: 250px;
  height: 100px;
  opacity: 0.1;
}

.auth-card {
  background: #FDFBF7;
  border-radius: 32px;
  max-width: 460px;
  width: 100%;
  padding: 40px 36px 36px;
  box-shadow: 0 25px 45px -12px rgba(0, 0, 0, 0.4), inset 0 1px 0 rgba(255,255,255,0.6);
  position: relative;
  transition: transform 0.3s ease;
  border: 1px solid rgba(200, 146, 42, 0.2);
}

.auth-card:hover {
  transform: translateY(-4px);
}

/* --- Icône retour accueil (fermer) en haut à droite --- */
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

.close-home-btn:active {
  transform: scale(0.95);
}

/* Reste du style inchangé */
.card-header {
  text-align: center;
  margin-bottom: 32px;
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
  font-family: 'IBM Plex Mono', monospace;
  font-size: 11px;
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
  margin-top: 12px;
}

.form-group {
  margin-bottom: 28px;
}

.form-group label {
  display: flex;
  align-items: center;
  gap: 6px;
  margin-bottom: 10px;
  font-family: Georgia, 'Times New Roman', Times, serif;
  font-size: 15px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 1px;
  color: #4A5B3A;
}

.label-icon {
  font-size: 14px;
}

.input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.input-icon {
  position: absolute;
  left: 16px;
  font-size: 18px;
  color: #B3925C;
  pointer-events: none;
  transition: color 0.2s;
}

.input-wrapper input {
  width: 100%;
  padding: 14px 16px 14px 48px;
  border: 1.5px solid #EBE3D4;
  border-radius: 60px;
  font-size: 15px;
   font-family: Georgia, 'Times New Roman', Times, serif;
  background: #FFFFFF;
  transition: all 0.2s;
  color: #1E2A10;
}

.input-wrapper input:focus {
  outline: none;
  border-color: #C8922A;
  box-shadow: 0 0 0 3px rgba(200, 146, 42, 0.2);
}

.input-wrapper input:focus + .input-icon,
.input-wrapper input:not(:placeholder-shown) + .input-icon {
  color: #C8922A;
}

.form-options {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 32px;
   font-family: Georgia, 'Times New Roman', Times, serif;
  font-size: 14px;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  color: #4B5E3D;
}

.checkbox-label input {
  width: 16px;
  height: 16px;
  accent-color: #C8922A;
  cursor: pointer;
}

.forgot-link {
  color: #7E6B46;
  text-decoration: none;
  border-bottom: 1px dashed #DCC99E;
  transition: color 0.2s;
}

.forgot-link:hover {
  color: #C8922A;
  border-bottom-color: #C8922A;
}

.btn-primary {
  width: 100%;
  padding: 14px 20px;
  background: linear-gradient(97deg, #C8922A 0%, #B07E29 100%);
  color: #1A2A0E;
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
  background: linear-gradient(97deg, #DFA73A 0%, #C8922A 100%);
  transform: scale(0.98);
  box-shadow: 0 8px 20px rgba(200, 146, 42, 0.25);
}

.btn-primary:disabled {
  opacity: 0.7;
  cursor: not-allowed;
  transform: none;
}

.auth-footer {
  margin-top: 32px;
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

.home-link {
  margin-top: 8px;
  font-size: 11px;
}

.home-link a {
  color: #7B6B48;
}

.harvest-quote {
  text-align: center;
  margin-top: 32px;
  padding-top: 20px;
  border-top: 1px solid #EFE8DC;
  font-family: 'Cormorant Garamond', serif;
  font-style: italic;
  font-size: 13px;
  color: #6F8259;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
}

.grain-icon {
  font-size: 14px;
  opacity: 0.7;
}

@media (max-width: 540px) {
  .auth-card {
    padding: 32px 20px 28px;
  }
  
  .card-header h1 {
    font-size: 32px;
  }
  
  .form-options {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
  }
  
  .input-icon {
    left: 14px;
  }
  
  .input-wrapper input {
    padding: 12px 16px 12px 44px;
  }
  
  .close-home-btn {
    top: 16px;
    right: 16px;
    width: 32px;
    height: 32px;
    font-size: 18px;
  }
}

.auth-card:hover .leaf-decoration {
  opacity: 0.5;
  transition: opacity 0.4s;
}
</style>