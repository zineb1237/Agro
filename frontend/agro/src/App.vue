<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import RendementForm from './components/RendementForm.vue'
import MaladieDetection from './components/MaladieDetection.vue'
import AchatProduits from './components/AchatProduits.vue'
import UserProfile from './components/UserProfile.vue'

const currentTab = ref('rendement')
const isScrolled = ref(false)
const isMenuOpen = ref(false)

const tabs = [
  { id: 'rendement', label: 'Rendement', icon: 'fa-chart-line', emoji: '📊' },
  { id: 'maladies', label: 'Maladies', icon: 'fa-microscope', emoji: '🩺' },
  { id: 'achat', label: 'Achat', icon: 'fa-shopping-basket', emoji: '🛒' },
  { id: 'profil', label: 'Mon Profil', icon: 'fa-user-circle', emoji: '👤' }
]

const handleScroll = () => {
  isScrolled.value = window.scrollY > 20
}

const toggleMenu = () => {
  isMenuOpen.value = !isMenuOpen.value
}

const selectTab = (id) => {
  currentTab.value = id
  isMenuOpen.value = false
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
    <header 
      class="main-header" 
      :class="{ 'is-scrolled': isScrolled, 'is-menu-open': isMenuOpen }"
    >
      <div class="nav-container">
        <!-- Logo Area -->
        <div class="logo" @click="selectTab('rendement')">
          <div class="logo-wrapper">
            <i class="fas fa-leaf logo-icon"></i>
            <div class="wheat-grain"></div>
          </div>
          <h1>FALAH</h1>
        </div>

        <!-- Desktop Navigation -->
        <nav class="desktop-nav">
          <div class="nav-links">
            <template v-for="(tab, index) in tabs" :key="tab.id">
              <!-- On n'affiche pas Profil dans la barre desktop car il est à droite -->
              <button 
                v-if="tab.id !== 'profil'"
                @click="selectTab(tab.id)"
                :class="{ 'active': currentTab === tab.id }"
                class="nav-link"
              >
                <span class="nav-emoji">{{ tab.emoji }}</span>
                {{ tab.label }}
              </button>
              <div v-if="index < tabs.length - 2 && tab.id !== 'profil'" class="nav-separator"></div>
            </template>
          </div>
        </nav>

        <!-- Right Side: User & Hamburger -->
        <div class="nav-right">
          <div 
            class="user-profile" 
            :class="{ 'is-active': currentTab === 'profil' }"
            @click="selectTab('profil')"
            title="Mon profil"
          >
            <i class="fas fa-user-circle user-icon"></i>
            <div class="notification-badge"></div>
            <span class="tooltip">Mon profil</span>
          </div>

          <button class="hamburger" @click="toggleMenu" :class="{ 'is-active': isMenuOpen }">
            <span></span>
            <span></span>
            <span></span>
          </button>
        </div>
      </div>

      <!-- Mobile Menu Overlay -->
      <Transition name="slide-down">
        <nav v-if="isMenuOpen" class="mobile-menu">
          <div class="mobile-links">
            <button 
              v-for="tab in tabs" 
              :key="tab.id"
              @click="selectTab(tab.id)"
              :class="{ 'active': currentTab === tab.id }"
              class="mobile-link"
            >
              <span class="nav-emoji">{{ tab.emoji }}</span>
              {{ tab.label }}
            </button>
          </div>
        </nav>
      </Transition>
    </header>

    <main class="content-area">
      <div class="container">
        <Transition name="fade" mode="out-in">
          <div :key="currentTab">
            <RendementForm v-if="currentTab === 'rendement'" />
            <MaladieDetection v-else-if="currentTab === 'maladies'" />
            <AchatProduits v-else-if="currentTab === 'achat'" />
            <UserProfile v-else-if="currentTab === 'profil'" />
          </div>
        </Transition>
      </div>
    </main>

    <footer class="main-footer">
      <div class="container">
        <p>&copy; 2026 FALAH Platform. Tous droits réservés.</p>
        <div class="footer-links">
          <span>Aide</span>
          <span>Contact</span>
          <span>Mentions légales</span>
        </div>
      </div>
    </footer>
  </div>
</template>

<style>
:root {
  --soil-color: #2C1A0E;
  --leaf-color: #3D6B35;
  --leaf-light-color: #5A9E4F;
  --sun-color: #E8A020;
  --sun-light-color: #F5C842;
  --cream-color: #F9F4EC;
  --wheat-color: #D4A853;
  --sky-color: #7BBDD4;
  --alert-color: #D94F3D;
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
  font-family: 'Plus Jakarta Sans', sans-serif;
  background-color: var(--cream-color);
  color: var(--text-color);
  line-height: 1.6;
  -webkit-font-smoothing: antialiased;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1.5rem;
}

.app-layout {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

/* --- NAVBAR STYLES --- */
.main-header {
  position: sticky;
  top: 0;
  z-index: 1000;
  background: linear-gradient(135deg, var(--soil-color) 0%, var(--leaf-color) 100%);
  border-bottom: 2px solid var(--sun-color);
  padding: 1rem 2rem;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
}

.main-header.is-scrolled {
  padding: 0.6rem 2rem;
  background: linear-gradient(135deg, rgba(44, 26, 14, 0.95) 0%, rgba(61, 107, 53, 0.95) 100%);
}

.nav-container {
  max-width: 1400px;
  margin: 0 auto;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

/* Logo & Wheat Animation */
.logo {
  display: flex;
  align-items: center;
  gap: 0.8rem;
  cursor: pointer;
}

.logo-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.logo-icon {
  color: var(--cream-color);
  font-size: 1.8rem;
}

.wheat-grain {
  position: absolute;
  right: -5px;
  top: -5px;
  width: 8px;
  height: 12px;
  background: var(--wheat-color);
  border-radius: 50% 50% 50% 50% / 60% 60% 40% 40%;
  animation: wheat-bob 2s infinite ease-in-out;
}

@keyframes wheat-bob {
  0%, 100% { transform: translateY(0) rotate(0deg); }
  50% { transform: translateY(-4px) rotate(15deg); }
}

.logo h1 {
  color: var(--cream-color);
  font-size: 1.5rem;
  font-weight: 700;
  letter-spacing: -0.5px;
}

/* Desktop Navigation Links */
.nav-links {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.nav-link {
  background: transparent;
  border: none;
  color: var(--border-color);
  font-size: 1.1rem;
  font-weight: 500;
  padding: 0.5rem 1.2rem;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  border-radius: 50px;
}

.nav-link:hover {
  background: var(--leaf-light-color);
  color: #FFFFFF;
}

.nav-link.active {
  background: var(--sun-color);
  color: var(--soil-color);
  font-weight: 700;
}

.nav-separator {
  width: 1px;
  height: 20px;
  background: var(--wheat-color);
  opacity: 0.4;
}

/* Right Side Elements */
.nav-right {
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.user-profile {
  position: relative;
  cursor: pointer;
  display: flex;
  align-items: center;
  padding: 5px;
  border-radius: 50%;
  transition: all 0.3s ease;
}

.user-profile.is-active {
  background: var(--sun-color);
}

.user-profile.is-active .user-icon {
  color: var(--soil-color);
}

.user-icon {
  color: var(--sun-light-color);
  font-size: 1.8rem;
  transition: transform 0.3s ease;
}

.user-profile:hover .user-icon {
  transform: scale(1.1);
}

.notification-badge {
  position: absolute;
  top: 0;
  right: 0;
  width: 10px;
  height: 10px;
  background: var(--alert-color);
  border-radius: 50%;
  border: 2px solid var(--soil-color);
}

.tooltip {
  position: absolute;
  top: 100%;
  right: 0;
  background: var(--soil-color);
  color: white;
  padding: 0.4rem 0.8rem;
  border-radius: 4px;
  font-size: 0.8rem;
  white-space: nowrap;
  opacity: 0;
  visibility: hidden;
  transform: translateY(10px);
  transition: all 0.3s ease;
  margin-top: 10px;
}

.user-profile:hover .tooltip {
  opacity: 1;
  visibility: visible;
  transform: translateY(0);
}

/* Hamburger Menu */
.hamburger {
  display: none;
  flex-direction: column;
  justify-content: space-between;
  width: 24px;
  height: 18px;
  background: transparent;
  border: none;
  cursor: pointer;
  padding: 0;
  z-index: 10;
}

.hamburger span {
  width: 100%;
  height: 2px;
  background: var(--cream-color);
  border-radius: 10px;
  transition: all 0.3s ease;
}

.hamburger.is-active span:nth-child(1) {
  transform: translateY(8px) rotate(45deg);
}

.hamburger.is-active span:nth-child(2) {
  opacity: 0;
}

.hamburger.is-active span:nth-child(3) {
  transform: translateY(-8px) rotate(-45deg);
}

/* Mobile Menu */
.mobile-menu {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  background: var(--soil-color);
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
  border-bottom: 2px solid var(--sun-color);
  box-shadow: 0 10px 20px rgba(0,0,0,0.2);
}

.mobile-links {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.mobile-link {
  background: transparent;
  border: none;
  color: var(--border-color);
  font-size: 1.2rem;
  padding: 1rem;
  text-align: left;
  border-radius: 8px;
  display: flex;
  align-items: center;
  gap: 1rem;
}

.mobile-link.active {
  background: var(--sun-color);
  color: var(--soil-color);
  font-weight: 700;
}

/* Content & Layout */
.content-area {
  flex: 1;
  padding: 3rem 0;
}

.main-footer {
  padding: 2rem 0;
  color: var(--text-muted);
  font-size: 0.9rem;
  border-top: 1px solid var(--border-color);
}

.main-footer .container {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.footer-links {
  display: flex;
  gap: 1.5rem;
}

/* Transitions */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.slide-down-enter-active,
.slide-down-leave-active {
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.slide-down-enter-from,
.slide-down-leave-to {
  opacity: 0;
  transform: translateY(-20px);
}

/* Responsive */
@media (max-width: 992px) {
  .nav-link {
    padding: 0.5rem 0.8rem;
    font-size: 1rem;
  }
}

@media (max-width: 768px) {
  .desktop-nav {
    display: none;
  }
  .hamburger {
    display: flex;
  }
  .main-header {
    padding: 1rem;
  }
  .main-header.is-scrolled {
    padding: 0.8rem 1rem;
  }
  .logo h1 {
    font-size: 1.3rem;
  }
}
</style>
