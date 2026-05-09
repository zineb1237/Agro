<template>
  <aside class="navbar-vertical">
    <!-- Logo -->
    <div class="logo-area">
      <div class="arabic-logo">فلاح</div>
      <div class="brand-line">
        <span class="brand">FELLAH</span> · PLATEFORME AGRICOLE
      </div>
    </div>

    <!-- Profil utilisateur dynamique (comme demandé) -->
    <div class="user-profile">
      <div class="user-initials">{{ userInitials }}</div>
      <div class="user-info">
        <div class="user-name">{{ userName }}</div>
        <div class="user-role">🌿 {{ userRole }}</div>
      </div>
    </div>

    <!-- Section PRINCIPAL -->
    <div class="nav-section">
      <div class="section-title">Principal</div>
      <ul class="nav-list">
        <li>
          <router-link to="/dashboard" class="nav-item" active-class="active">
            <span class="nav-icon">🏡</span>
            Tableau de bord
          </router-link>
        </li>
        <li>
          <router-link to="/market" class="nav-item" active-class="active">
            <span class="nav-icon">🛒</span>
            Marché
          </router-link>
        </li>
        <li>
          <router-link to="/parcelles" class="nav-item" active-class="active">
            <span class="nav-icon">🗺️</span>
            Mes Parcelles
          </router-link>
        </li>
      </ul>
    </div>

    <!-- Section OUTILS IA -->
    <div class="nav-section">
      <div class="section-title">Outils IA</div>
      <ul class="nav-list">
        <li>
          <router-link to="/diagnostic-photo" class="nav-item" active-class="active">
            <span class="nav-icon">🔬</span>
            Diagnostic Photo
          </router-link>
        </li>
        <li>
          <router-link to="/assistant-vocal" class="nav-item" active-class="active">
            <span class="nav-icon">🎙️</span>
            Assistant Vocal
            <span class="nav-badge">3</span>
          </router-link>
        </li>
        <li>
          <router-link to="/predictionForm" class="nav-item" active-class="active">
            <span class="nav-icon">🔗</span>
            Prédiction de rendement
          </router-link>
        </li>
      </ul>
    </div>

    <!-- Section COMPTE -->
    <div class="nav-section">
      <div class="section-title">Compte</div>
      <ul class="nav-list">
        <li>
          <router-link to="/commandes" class="nav-item" active-class="active">
            <span class="nav-icon">📦</span>
            Commandes
            <span class="nav-badge" style="background:#C8922A;">2</span>
          </router-link>
        </li>
        <li>
          <router-link to="/parametres" class="nav-item" active-class="active">
            <span class="nav-icon">⚙️</span>
            Paramètres
          </router-link>
        </li>
      </ul>
    </div>

    <!-- Footer avec déconnexion -->
    <div class="nav-footer">
      <div class="logout-item" @click="logout">
        <span class="nav-icon">🚪</span>
        Déconnexion
      </div>
    </div>
  </aside>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

// État réactif pour l'utilisateur
const userName = ref('Ahmed Hassani')
const userRole = ref('Agriculteur')

// Calcul des initiales (ex: Ahmed Hassani → AH)
const userInitials = computed(() => {
  const name = userName.value
  if (!name) return '??'
  const parts = name.trim().split(/\s+/)
  if (parts.length === 1) return parts[0].substring(0, 2).toUpperCase()
  return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase()
})

// Chargement des infos depuis localStorage (si existantes)
const loadUserInfo = () => {
  const userStr = localStorage.getItem('user')
  if (userStr) {
    try {
      const user = JSON.parse(userStr)
      userName.value = user.name || user.nom || user.prenom || 'Ahmed Hassani'
      userRole.value = user.role || 'Agriculteur'
    } catch (e) {
      console.error('Erreur parsing user', e)
    }
  }
}

// Déconnexion
const logout = () => {
  localStorage.removeItem('token')
  localStorage.removeItem('user')
  router.push('/login')
}

onMounted(() => {
  loadUserInfo()
})
</script>

<style scoped>
/* ========== COULEURS EXACTES DU DESIGN FOURNI ========== */
.navbar-vertical {
  width: 220px;
  min-width: 220px;
  background-color: #2E1A0A;
  height: 100vh;
  display: flex;
  flex-direction: column;
  padding: 28px 16px 24px;
  position: relative;
  overflow: hidden;
  flex-shrink: 0;
  font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
}

/* Effet radial (glow) */
.navbar-vertical::after {
  content: '';
  position: absolute;
  bottom: -60px;
  left: 50%;
  transform: translateX(-50%);
  width: 260px;
  height: 260px;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(74,174,120,0.07) 0%, transparent 70%);
  pointer-events: none;
}

/* Logo */
.logo-area {
  margin-bottom: 22px;
  padding: 0 6px;
}

.arabic-logo {
  font-family: 'Noto Sans Arabic', serif;
  font-size: 32px;
  font-weight: 700;
  color: #E8B84B;
  line-height: 1.1;
  margin-bottom: 4px;
  letter-spacing: 1px;
}

.brand-line {
  font-size: 10px;
  letter-spacing: 1.5px;
  color: #7A6A58;
  line-height: 1.4;
}

.brand-line .brand {
  font-weight: 700;
  color: #9A8A78;
}

/* Profil utilisateur */
.user-profile {
  background-color: #3A2410;
  border-radius: 14px;
  padding: 11px 13px;
  margin-bottom: 26px;
  display: flex;
  align-items: center;
  gap: 11px;
}

.user-initials {
  background: linear-gradient(135deg, #C8922A, #E8B84B);
  color: #fff;
  font-weight: 700;
  font-size: 14px;
  width: 36px;
  height: 36px;
  min-width: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  letter-spacing: 0.5px;
}

.user-info {
  flex: 1;
  min-width: 0;
}

.user-name {
  font-size: 14px;
  font-weight: 600;
  color: #F0EAE0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  margin-bottom: 3px;
}

.user-role {
  font-size: 12px;
  color: #8A7A68;
  display: flex;
  align-items: center;
  gap: 4px;
}

/* Sections de navigation */
.nav-section {
  margin-bottom: 22px;
}

.section-title {
  font-size: 10px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 1.8px;
  color: #6A5A48;
  margin-bottom: 8px;
  padding: 0 8px;
}

.nav-list {
  list-style: none;
  display: flex;
  flex-direction: column;
  gap: 2px;
  padding: 0;
  margin: 0;
}

/* Élément de menu (router-link) */
.nav-item {
  display: flex;
  align-items: center;
  gap: 11px;
  padding: 10px 12px;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.18s ease;
  color: #C8BEB0;
  font-size: 14px;
  font-weight: 500;
  text-decoration: none;
  position: relative;
  user-select: none;
}

.nav-item:hover {
  background-color: rgba(74, 174, 120, 0.15);
  color: #E0F5EA;
  transform: translateX(3px);
}

/* État actif (grâce à active-class="active") */
.nav-item.active {
  background-color: #2D7A4F;
  color: #FFFFFF;
  font-weight: 600;
  box-shadow: 0 3px 12px rgba(45, 122, 79, 0.35);
}

.nav-item.active .nav-icon {
  filter: brightness(1.3);
}

/* Icône */
.nav-icon {
  font-size: 17px;
  width: 22px;
  text-align: center;
  flex-shrink: 0;
}

/* Badge */
.nav-badge {
  margin-left: auto;
  background: #D94F3D;
  color: white;
  font-size: 10px;
  font-weight: 700;
  min-width: 20px;
  height: 20px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0 5px;
  font-family: 'Inter', sans-serif;
}

/* Footer & déconnexion */
.nav-footer {
  margin-top: auto;
  padding-top: 16px;
  border-top: 1px solid rgba(255, 255, 255, 0.05);
}

.logout-item {
  display: flex;
  align-items: center;
  gap: 11px;
  padding: 9px 12px;
  border-radius: 10px;
  cursor: pointer;
  color: #6A5A48;
  font-size: 13px;
  font-weight: 500;
  transition: all 0.18s;
}

.logout-item:hover {
  background: rgba(217, 79, 61, 0.1);
  color: #D94F3D;
}
</style>