import { createRouter, createWebHistory } from 'vue-router'
import LandingPage from '../views/LandingPage.vue'
import Login from '../views/LoginPage.vue'
import Register from '../views/RegisterPage.vue'
import Dashboard from '../views/DashboardPage.vue'
import ForgotPassword from '../views/ForgotPassword.vue'
import ResetPassword from '../views/ResetPassword.vue'
import PredictionForm from '../views/PredictionForm.vue'  // ✅ Import existant
import ParcellePage from '../views/ParcellePage.vue'
import MarketPage from '../views/MarketPage.vue'
import DiagnosticPhotoPage from '../views/DiagnosticPhotoPage.vue'
import AssistantVocalPage from '../views/AssistantVocalPage.vue'
import PasseportPlantePage from '../views/PasseportPlantePage.vue'
import CommandesPage from '../views/CommandesPage.vue'
import VendeurLayout from '@/views/VendeurLayout.vue'
import { useAuthStore } from '../stores/auth'
import VendeurDashboard from '../components/VendeurDashboard.vue'
import VendeurProduits from '../components/VendeurProduits.vue'
import VendeurAddProduit from '../components/VendeurAddProduit.vue'
import VendeurCommandes from '../components/VendeurCommandes.vue'
import UserProfile from '../components/UserProfile.vue'

const routes = [
  { path: '/', name: 'Landing', component: LandingPage },
  { path: '/login', name: 'Login', component: Login },
  { path: '/register', name: 'Register', component: Register },
  { path: '/dashboard', name: 'Dashboard', component: Dashboard },
  { path: '/forgot-password', name: 'ForgotPassword', component: ForgotPassword },
  { path: '/reset-password', name: 'ResetPassword', component: ResetPassword },
  { path: '/parcelles', name: 'Parcelles', component: ParcellePage },
  { path: '/market', name: 'Market', component: MarketPage },
  { path: '/diagnostic-photo', name: 'DiagnosticPhoto', component: DiagnosticPhotoPage },
  { path: '/assistant-vocal', name: 'AssistantVocal', component: AssistantVocalPage },
  { path: '/passeport-plante', name: 'PasseportPlante', component: PasseportPlantePage },
  { path: '/commandes', name: 'Commandes', component: CommandesPage },
 {path: '/vendeur/dash', name: 'Vendeur', component: VendeurLayout},
  // ✅ AJOUTER CETTE LIGNE :
  { path: '/predictionForm', name: 'PredictionForm', component: PredictionForm },
  { path: '/vendeur/dashboard', name: 'VendeurDashboard', component: VendeurDashboard },
{ path: '/vendeur/produits', name: 'VendeurProduits', component: VendeurProduits },
{ path: '/vendeur/ajouter-produit', name: 'VendeurAddProduit', component: VendeurAddProduit },
{ path: '/vendeur/commandes', name: 'VendeurCommandes', component: VendeurCommandes },

// Route profil (commune)
{ path: '/profil', name: 'UserProfile', component: UserProfile }
]
// Routes Vendeur

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router