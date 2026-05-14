<template>
  <aside class="navbar-vertical">
    <!-- Logo -->
    <div class="logo-area">
      <div class="arabic-logo">فلاح</div>
      <div class="brand-line">
        <span class="brand">FELLAH</span> · PLATEFORME AGRICOLE
      </div>
    </div>

    <!-- Profil utilisateur dynamique avec clic pour changer de rôle -->
    <div class="user-profile" @click="toggleRole">
      <div class="user-initials">{{ userInitials }}</div>
      <div class="user-info">
        <div class="user-name">{{ userName }}</div>
        <div class="user-role">
          <span class="role-icon">🌿</span> 
          {{ userRoleLabel }}
          <span class="switch-hint">⇄</span>
        </div>
      </div>
    </div>

    <!-- Section PRINCIPAL - Dynamique selon rôle -->
    <div class="nav-section">
      <div class="section-title">Principal</div>
      <ul class="nav-list">
        <li v-if="currentRole === 'agriculteur'">
          <router-link to="/dashboard" class="nav-item" active-class="active">
            <span class="nav-icon">🏡</span>
            Tableau de bord
          </router-link>
        </li>
        
        <li v-if="currentRole === 'vendeur'">
          <router-link to="/vendeur/dashboard" class="nav-item" active-class="active">
        
            
            Tableau de bord Vendeur
          </router-link>
        </li>

        <li v-if="currentRole === 'agriculteur'">
          <router-link to="/parcelles" class="nav-item" active-class="active">
            <span class="nav-icon">🗺️</span>
            Mes Parcelles
          </router-link>
        </li>
        <li v-if="currentRole === 'vendeur'">
          <router-link to="/vendeur/produits" class="nav-item" active-class="active">
           
            Mes Produits
          </router-link>
        </li>
        <li v-if="currentRole === 'vendeur'">
          <router-link to="/vendeur/commandes" class="nav-item" active-class="active">
           
            Commandes
          </router-link>
        </li>
      </ul>
    </div>

    <!-- Section OUTILS IA (visible seulement pour agriculteur) -->
    <div class="nav-section" v-if="currentRole === 'agriculteur'">
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
           
          </router-link>
        </li>
        <li>
          <router-link to="/predictionForm" class="nav-item" active-class="active">
            <span class="nav-icon">🔗</span>
            Prédiction  rendement
          </router-link>
        </li>
      </ul>
    </div>

    <!-- Section VENTE (visible seulement pour vendeur) -->
    <div class="nav-section" v-if="currentRole === 'vendeur'">
      <div class="section-title">Boutique</div>
      <ul class="nav-list">
        <li>
          <router-link to="/vendeur/ajouter-produit" class="nav-item" active-class="active">
           
            Ajouter un produit
          </router-link>
        </li>
   <li v-if="currentRole === 'vendeur'">
          <router-link to="/market" class="nav-item" active-class="active">
           
            Marché
          </router-link>
        </li>
      </ul>
    </div>

    <!-- Section COMPTE - identique pour les deux rôles -->
    <div class="nav-section">
      <div class="section-title">Compte</div>
      <ul class="nav-list">
        <li>
          <router-link to="/profil" class="nav-item" active-class="active">
         <span class="nav-icon">👤</span>
            Mon Profil
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
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

// États pour l'utilisateur
const userName = ref('Fatima Chegdani')

const currentRole = ref('agriculteur') // 'agriculteur' ou 'vendeur'

// Données vendeur
const vendeurData = ref({
  nom: 'Karimi',
  prenom: 'Youssef',
  entreprise: 'AgriPro Distribution',
  telephone: '06 12 34 56 78'
})

// Données agriculteur
const agriculteurData = ref({
  nom: 'Hassani',
  prenom: 'Ahmed',
  ferme: 'Domaine El Bassatine',
  telephone: '06 98 76 54 32'
})

// Computed pour afficher le nom complet selon le rôle
const displayName = computed(() => {
  return userName.value
})

// Computed pour le label du rôle
const userRoleLabel = computed(() => {
  return currentRole.value === 'agriculteur' ? 'Agriculteur' : 'Vendeur'
})

// Computed pour les initiales
const userInitials = computed(() => {
  const parts = userName.value.split(' ')
  const prenom = parts[0] || ''
  const nom = parts.length > 1 ? parts[1] : ''
  
  if (!prenom && !nom) return '??'
  if (!nom) return prenom[0].toUpperCase()
  return (prenom[0] + nom[0]).toUpperCase()
})

// Mise à jour du userName pour l'affichage
watch(displayName, (newName) => {
  userName.value = newName
}, { immediate: true })

// Bascule entre les rôles
const toggleRole = () => {
  // Sauvegarder les données du rôle actuel avant de changer
  if (currentRole.value === 'agriculteur') {
    // Sauvegarder les données agriculteur
    agriculteurData.value.prenom = userName.value.split(' ')[0]
    agriculteurData.value.nom = userName.value.split(' ')[1] || ''
    
    // Passer en mode vendeur
    currentRole.value = 'vendeur'
    
    // Optionnel: notification visuelle
    showToast('Mode Vendeur activé', 'info')
  } else {
    // Sauvegarder les données vendeur
    vendeurData.value.prenom = userName.value.split(' ')[0]
    vendeurData.value.nom = userName.value.split(' ')[1] || ''
    
    // Passer en mode agriculteur
    currentRole.value = 'agriculteur'
    
    // Optionnel: notification visuelle
    showToast('Mode Agriculteur activé', 'info')
  }
  
  // Sauvegarder la préférence dans localStorage
  localStorage.setItem('userRole', currentRole.value)
  localStorage.setItem('agriculteurData', JSON.stringify(agriculteurData.value))
  localStorage.setItem('vendeurData', JSON.stringify(vendeurData.value))
  
  // Rediriger vers le dashboard approprié
  if (currentRole.value === 'agriculteur') {
    router.push('/dashboard')
  } else {
    router.push('/vendeur/dashboard')
  }
}

// Pour afficher des notifications simples (optionnel)
const showToast = (message, type = 'info') => {
  // Vous pouvez implémenter un système de toast plus élaboré
  console.log(`[${type.toUpperCase()}] ${message}`)
  
  // Alternative simple: alert (décommentez si voulu)
  // alert(message)
}

// Chargement des données sauvegardées
const loadSavedData = async () => {
  const savedRole = localStorage.getItem('userRole')
  if (savedRole && (savedRole === 'agriculteur' || savedRole === 'vendeur')) {
    currentRole.value = savedRole
  }
  
  // Fetch real user data from backend
  try {
    const token = localStorage.getItem('token')
    if (token) {
      const response = await fetch('http://localhost:8000/api/user/profile', {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json'
        }
      })
      if (response.ok) {
        const data = await response.json()
        userName.value = `${data.prenom} ${data.nom}`.trim()
        if (data.role && (data.role === 'agriculteur' || data.role === 'fournisseur' || data.role === 'vendeur')) {
            // currentRole.value = data.role === 'fournisseur' ? 'vendeur' : 'agriculteur'
        }
      }
    }
  } catch (error) {
    console.error('Erreur lors de la récupération du profil:', error)
  }
  
  const savedAgriculteur = localStorage.getItem('agriculteurData')
  if (savedAgriculteur) {
    try {
      agriculteurData.value = JSON.parse(savedAgriculteur)
    } catch (e) {}
  }
  
  const savedVendeur = localStorage.getItem('vendeurData')
  if (savedVendeur) {
    try {
      vendeurData.value = JSON.parse(savedVendeur)
    } catch (e) {}
  }
}

// Déconnexion
const logout = () => {
  localStorage.removeItem('token')
  localStorage.removeItem('user')
  localStorage.removeItem('userRole')
  localStorage.removeItem('agriculteurData')
  localStorage.removeItem('vendeurData')
  router.push('/login')
}

onMounted(() => {
  loadSavedData()
})
</script>

<style scoped>
/* Gardez tous vos styles existants, ajoutez juste ceux-ci */

/* Animation et hint pour le switch de rôle */
.user-profile {
  cursor: pointer;
  transition: all 0.2s ease;
  position: relative;
}

.user-profile:hover {
  background-color: #4A3020;
  transform: translateY(-1px);
}

.user-profile:active {
  transform: translateY(1px);
}

.switch-hint {
  font-size: 10px;
  margin-left: 4px;
  opacity: 0;
  transition: opacity 0.2s ease;
  color: #E8B84B;
}

.user-profile:hover .switch-hint {
  opacity: 1;
}

.role-icon {
  font-size: 11px;
}

/* Tooltip optionnel */
.user-profile::before {
  content: 'Changer de rôle';
  position: absolute;
  bottom: 100%;
  left: 50%;
  transform: translateX(-50%) translateY(-8px);
  background: #1a1a1a;
  color: white;
  font-size: 11px;
  padding: 4px 10px;
  border-radius: 6px;
  white-space: nowrap;
  opacity: 0;
  pointer-events: none;
  transition: opacity 0.2s ease;
  z-index: 1000;
  font-weight: normal;
}

.user-profile:hover::before {
  opacity: 0.7;
}

/* Ajustement pour mobile */
@media (max-width: 768px) {
  .user-profile::before {
    display: none;
  }
}

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
  font-size: 16px;
  line-height: 24px;
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
 
  padding-top: 10px;
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
  font-size: 16px;
  font-weight: 500;
  transition: all 0.18s;
}

.logout-item:hover {
  background: rgba(217, 79, 61, 0.1);
  color: #D94F3D;
}
</style>