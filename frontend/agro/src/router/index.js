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
import ParametresPage from '../views/ParametresPage.vue'

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
  { path: '/parametres', name: 'Parametres', component: ParametresPage },
  // ✅ AJOUTER CETTE LIGNE :
  { path: '/predictionForm', name: 'PredictionForm', component: PredictionForm }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router