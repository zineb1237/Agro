<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import RendementForm from './components/RendementForm.vue'
import MaladieDetection from './components/MaladieDetection.vue'
import AchatProduits from './components/AchatProduits.vue'
import UserProfile from './components/UserProfile.vue'
import VendeurProduits from './components/VendeurProduits.vue'
import VendeurAddProduit from './components/VendeurAddProduit.vue'
import VendeurCommandes from './components/VendeurCommandes.vue'
import VendeurDashboard from './components/VendeurDashboard.vue'

const currentTab = ref('rendement')
const userRole = ref('agriculteur') 
const hasScrolled = ref(false)
const isSidebarOpen = ref(false)

const agriculteurTabs = [
  { id: 'rendement', label: 'Tableau de bord', category: 'PRINCIPAL', icon: 'fas fa-th-large', emoji: '🏠' },
  { id: 'achat', label: 'Marché', category: 'PRINCIPAL', icon: 'fas fa-shopping-basket', emoji: '🛒' },
  { id: 'parcelles', label: 'Mes Parcelles', category: 'PRINCIPAL', icon: 'fas fa-map-marked-alt', emoji: '🗺️' },
  { id: 'maladies', label: 'Diagnostic Photo', category: 'OUTILS IA', icon: 'fas fa-camera-retro', emoji: '🔬' },
  { id: 'assistant', label: 'Assistant Vocal', category: 'OUTILS IA', icon: 'fas fa-microphone', emoji: '🎙️', badge: 3 },
  { id: 'profil', label: 'Mon Profil', category: 'COMPTE', icon: 'fas fa-user-circle', emoji: '👤' }
]

const vendeurTabs = [
  { id: 'v_dashboard', label: 'Tableau de bord', category: 'PRINCIPAL', icon: 'fas fa-chart-line', emoji: '📈' },
  { id: 'v_produits', label: 'Mes Produits', category: 'PRINCIPAL', icon: 'fas fa-box-open', emoji: '📦' },
  { id: 'v_add', label: 'Ajouter Produit', category: 'PRINCIPAL', icon: 'fas fa-plus-circle', emoji: '➕' },
  { id: 'v_commandes', label: 'Commandes', category: 'COMPTE', icon: 'fas fa-shopping-cart', emoji: '🛒' },
  { id: 'profil', label: 'Mon Profil', category: 'COMPTE', icon: 'fas fa-user-circle', emoji: '👤' }
]

const tabs = computed(() => userRole.value === 'agriculteur' ? agriculteurTabs : vendeurTabs)

const categories = computed(() => {
  const cats = [...new Set(tabs.value.map(t => t.category))]
  return cats
})

const switchRole = () => {
  userRole.value = userRole.value === 'agriculteur' ? 'vendeur' : 'agriculteur'
  currentTab.value = userRole.value === 'agriculteur' ? 'rendement' : 'v_dashboard'
}

const handleScroll = () => {
  hasScrolled.value = window.scrollY > 20
}

const toggleMenu = () => {
  isSidebarOpen.value = !isSidebarOpen.value
}

const selectTab = (id) => {
  currentTab.value = id
  isSidebarOpen.value = false
}

onMounted(() => {
  window.addEventListener('scroll', handleScroll)
})

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll)
})
</script>

<template>
  <div class="app-layout">
    <!-- Sidebar -->
    <aside class="sidebar" :class="{ 'is-open': isSidebarOpen }">
      <div class="sidebar-header">
        <div class="logo-area">
          <div class="arabic-logo">فلاح</div>
          <div class="logo-subtext">FELLAH · PLATFORME AGRICOLE</div>
        </div>
      </div>

      <div class="user-card" @click="switchRole" title="Changer de rôle">
        <div class="user-avatar">{{ userRole === 'agriculteur' ? 'ف' : 'V' }}</div>
        <div class="user-info">
          <div class="user-name">{{ userRole === 'agriculteur' ? 'فاطمة الشكداني' : 'Vendeur Pro' }}</div>
          <div class="user-role">
            <i class="fas fa-leaf role-icon"></i>
            {{ userRole === 'agriculteur' ? 'Agriculteur' : 'Vendeur' }}
          </div>
        </div>
      </div>

      <nav class="sidebar-nav">
        <div v-for="cat in categories" :key="cat" class="nav-group">
          <div class="group-title">{{ cat }}</div>
          <button 
            v-for="tab in tabs.filter(t => t.category === cat)" 
            :key="tab.id"
            @click="selectTab(tab.id)"
            :class="{ 'active': currentTab === tab.id }"
            class="nav-item"
          >
            <i :class="tab.icon" class="item-icon"></i>
            <span class="item-label">{{ tab.label }}</span>
            <span v-if="tab.badge" class="item-badge">{{ tab.badge }}</span>
          </button>
        </div>
      </nav>
    </aside>

    <!-- Main Content Area -->
    <div class="main-container">
      <header class="mobile-header">
        <button class="hamburger" @click="toggleMenu" :class="{ 'is-active': isSidebarOpen }">
          <span></span>
          <span></span>
          <span></span>
        </button>
        <div class="mobile-logo">FALAH</div>
      </header>

      <main class="content-area">
        <div class="page-wrapper">
          <Transition name="fade" mode="out-in">
            <div :key="currentTab">
              <!-- Agriculteur Components -->
              <RendementForm v-if="currentTab === 'rendement'" />
              <MaladieDetection v-else-if="currentTab === 'maladies'" />
              <AchatProduits v-else-if="currentTab === 'achat'" />
              <UserProfile v-else-if="currentTab === 'profil'" />
              
              <!-- Vendeur Components -->
              <VendeurDashboard v-else-if="currentTab === 'v_dashboard'" />
              <VendeurProduits v-else-if="currentTab === 'v_produits'" />
              <VendeurAddProduit v-else-if="currentTab === 'v_add'" />
              <VendeurCommandes v-else-if="currentTab === 'v_commandes'" />
              
              <div v-else class="placeholder-page">
                <h2>{{ tabs.find(t => t.id === currentTab)?.label }}</h2>
                <p>Contenu en cours de développement...</p>
              </div>
            </div>
          </Transition>
        </div>
      </main>
    </div>
  </div>
</template>

<style>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&family=Fraunces:ital,opsz,wght@0,9..144,100..900;1,9..144,100..900&display=swap');

:root {
  --soil-color: #2C1A0E; /* Vert foncé Navbar - note: user said #2d4a1e, but existing is #2C1A0E */
  --leaf-color: #3D6B35;
  --leaf-light-color: #5A9E4F;
  --sun-color: #f5a623; /* Boutons Orange/Jaune #f5a623 */
  --sun-light-color: #F5C842;
  --cream-color: #f5f0e8; /* Fond Beige Clair #f5f0e8 */
  --wheat-color: #D4A853;
  --sky-color: #7BBDD4;
  --alert-color: #e53935; /* Badge PROMO Rouge #e53935 */
  --bio-color: #4caf50; /* Badge BIO Vert #4caf50 */
  --text-color: #1C1008;
  --text-muted: #6B5744;
  --border-color: #E8DDD0;
  --card-bg: #FFFFFF;
}

* {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

body {
  font-family: 'DM Sans', sans-serif;
  background-color: var(--cream-color);
  color: var(--text-color);
  line-height: 1.6;
  -webkit-font-smoothing: antialiased;
}

.container {
  max-width: 1280px;
  margin: 0 auto;
  padding: 0 1.5rem;
}

.app-layout {
  display: flex;
  min-height: 100vh;
  background-color: #f8f9fa;
}

/* --- SIDEBAR STYLES --- */
.sidebar {
  width: 280px;
  background-color: #2D1A0E;
  color: #FFFFFF;
  display: flex;
  flex-direction: column;
  padding: 1.5rem;
  height: 100vh;
  position: sticky;
  top: 0;
  z-index: 1000;
  transition: all 0.3s ease;
}

.sidebar-header {
  padding-bottom: 2rem;
}

.arabic-logo {
  font-family: 'Fraunces', serif;
  font-size: 2.5rem;
  color: #D4A853;
  line-height: 1;
  margin-bottom: 0.2rem;
}

.logo-subtext {
  font-size: 0.65rem;
  letter-spacing: 0.1em;
  color: rgba(255, 255, 255, 0.4);
  font-weight: 600;
}

.user-card {
  background: rgba(255, 255, 255, 0.05);
  border-radius: 16px;
  padding: 1rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 2rem;
  cursor: pointer;
  transition: all 0.3s ease;
}

.user-card:hover {
  background: rgba(255, 255, 255, 0.1);
}

.user-avatar {
  width: 44px;
  height: 44px;
  background-color: #f5a623;
  color: #2D1A0E;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 800;
  font-size: 1.2rem;
}

.user-name {
  font-size: 0.95rem;
  font-weight: 700;
  margin-bottom: 0.2rem;
}

.user-role {
  font-size: 0.75rem;
  color: rgba(255, 255, 255, 0.5);
  display: flex;
  align-items: center;
  gap: 0.4rem;
}

.role-icon {
  font-size: 0.7rem;
  color: #4caf50;
}

.sidebar-nav {
  flex: 1;
  overflow-y: auto;
}

.nav-group {
  margin-bottom: 1.5rem;
}

.group-title {
  font-size: 0.7rem;
  font-weight: 800;
  color: rgba(255, 255, 255, 0.3);
  letter-spacing: 0.12em;
  margin-bottom: 0.8rem;
  padding-left: 0.8rem;
}

.nav-item {
  width: 100%;
  background: transparent;
  border: none;
  color: rgba(255, 255, 255, 0.7);
  padding: 0.8rem 1rem;
  border-radius: 12px;
  display: flex;
  align-items: center;
  gap: 1rem;
  cursor: pointer;
  transition: all 0.2s ease;
  margin-bottom: 0.2rem;
  font-size: 0.95rem;
  font-weight: 500;
}

.nav-item:hover {
  background: rgba(255, 255, 255, 0.05);
  color: #FFFFFF;
}

.nav-item.active {
  background-color: #2D4A1E;
  color: #FFFFFF;
  font-weight: 600;
}

.item-icon {
  font-size: 1rem;
  width: 20px;
  text-align: center;
  opacity: 0.8;
}

.item-badge {
  margin-left: auto;
  background-color: #e53935;
  color: white;
  font-size: 0.7rem;
  font-weight: 800;
  padding: 2px 8px;
  border-radius: 10px;
}

/* --- MAIN CONTAINER --- */
.main-container {
  flex: 1;
  display: flex;
  flex-direction: column;
  min-width: 0;
}

.mobile-header {
  display: none;
  background-color: #2D1A0E;
  padding: 1rem;
  align-items: center;
  justify-content: space-between;
  color: white;
}

.content-area {
  padding: 2rem;
  background-color: #f5f0e8;
  flex: 1;
}

.page-wrapper {
  background: #FFFFFF;
  border-radius: 24px;
  padding: 2.5rem;
  min-height: calc(100vh - 4rem);
  box-shadow: 0 4px 24px rgba(0, 0, 0, 0.03);
}

.placeholder-page {
  text-align: center;
  padding: 4rem 2rem;
  color: var(--text-muted);
}

/* --- RESPONSIVENESS --- */
@media (max-width: 1024px) {
  .sidebar {
    position: fixed;
    left: -280px;
    height: 100vh;
  }
  
  .sidebar.is-open {
    left: 0;
  }
  
  .mobile-header {
    display: flex;
  }
  
  .content-area {
    padding: 1rem;
  }
  
  .page-wrapper {
    padding: 1.5rem;
    min-height: calc(100vh - 2rem);
  }
}

/* Hamburger Styles for Sidebar */
.hamburger {
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  width: 24px;
  height: 18px;
  background: transparent;
  border: none;
  cursor: pointer;
  padding: 0;
}

.hamburger span {
  width: 100%;
  height: 2px;
  background: #FFFFFF;
  border-radius: 10px;
  transition: all 0.3s ease;
}

.hamburger.is-active span:nth-child(1) { transform: translateY(8px) rotate(45deg); }
.hamburger.is-active span:nth-child(2) { opacity: 0; }
.hamburger.is-active span:nth-child(3) { transform: translateY(-8px) rotate(-45deg); }

/* Transitions */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
