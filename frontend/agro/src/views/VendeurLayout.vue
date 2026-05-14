<script setup>
import { ref, onMounted, computed } from 'vue'
import NavBar from '@/components/NavBar.vue'

// ========== DONNÉES SIMULÉES (à remplacer par vos appels API) ==========
const stats = ref({
  commandes_total: 0,
  commandes_attente: 0,
  commandes_livrees: 0,
  chiffre_affaires: 0
})

const commandesRecentes = ref([])   // { id, client, total, statut, date }
const produitsRecents = ref([])     // { id, nom, prix, stock, image }

const loading = ref(true)

// ========== APPELS API (exemples) ==========
const fetchStats = async () => {
  try {
    const token = localStorage.getItem('token')
    // Exemple d'endpoint – adaptez selon votre backend
    const response = await fetch('http://localhost:8000/api/vendeur/stats', {
      headers: { 'Authorization': `Bearer ${token}` }
    })
    const data = await response.json()
    if (response.ok) {
      stats.value = {
        commandes_total: data.total_commandes || 0,
        commandes_attente: data.commandes_attente || 0,
        commandes_livrees: data.commandes_livrees || 0,
        chiffre_affaires: data.chiffre_affaires || 0
      }
    }
  } catch (error) {
    console.error('Erreur stats:', error)
    // Données de démonstration si l'API n'est pas prête
    stats.value = {
      commandes_total: 24,
      commandes_attente: 5,
      commandes_livrees: 19,
      chiffre_affaires: 12890
    }
  }
}

const fetchCommandesRecentes = async () => {
  try {
    const token = localStorage.getItem('token')
    const response = await fetch('http://localhost:8000/api/vendeur/commandes?limit=5', {
      headers: { 'Authorization': `Bearer ${token}` }
    })
    const data = await response.json()
    if (response.ok) {
      commandesRecentes.value = data.commandes || []
    }
  } catch (error) {
    // Données fictives
    commandesRecentes.value = [
      { id: 101, client: 'Ahmed Tazi', total: 3450, statut: 'livree', date: '2025-05-10' },
      { id: 102, client: 'Fatima Zahra', total: 1220, statut: 'attente', date: '2025-05-12' },
      { id: 103, client: 'Mohamed Benali', total: 890, statut: 'livree', date: '2025-05-09' },
      { id: 104, client: 'Khalid Mansouri', total: 2760, statut: 'attente', date: '2025-05-13' },
      { id: 105, client: 'Nadia Fadili', total: 540, statut: 'livree', date: '2025-05-08' }
    ]
  }
}

const fetchProduitsRecents = async () => {
  try {
    const token = localStorage.getItem('token')
    const response = await fetch('http://localhost:8000/api/vendeur/produits?limit=5', {
      headers: { 'Authorization': `Bearer ${token}` }
    })
    const data = await response.json()
    if (response.ok) {
      produitsRecents.value = data.produits || []
    }
  } catch (error) {
    produitsRecents.value = [
      { id: 1, nom: 'Semence Blé Dur', prix: 85, stock: 450, image: null },
      { id: 2, nom: 'Engrais NPK 20-20-20', prix: 320, stock: 200, image: null },
      { id: 3, nom: 'Kit Goutte-à-goutte 1Ha', prix: 2400, stock: 12, image: null },
      { id: 4, nom: 'Fongicide Cuivre Bio', prix: 140, stock: 78, image: null },
      { id: 5, nom: 'Semences Carottes F1', prix: 55, stock: 320, image: null }
    ]
  }
}

const formatPrice = (price) => {
  return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'MAD' }).format(price)
}

const getStatutBadge = (statut) => {
  if (statut === 'livree') return { class: 'badge-livree', text: 'Livrée' }
  if (statut === 'attente') return { class: 'badge-attente', text: 'En attente' }
  return { class: 'badge-annulee', text: 'Annulée' }
}

onMounted(async () => {
  loading.value = true
  await Promise.all([fetchStats(), fetchCommandesRecentes(), fetchProduitsRecents()])
  loading.value = false
})
</script>

<template>
  <div class="dashboard-vendeur">
    <NavBar />
    <div class="main-content">
      <div class="dashboard-header">
        <h2>Tableau de bord vendeur</h2>
        <p>Bienvenue dans votre espace de gestion – suivez vos ventes et commandes.</p>
      </div>

      <div v-if="loading" class="loading">Chargement des données...</div>

      <div v-else>
        <!-- Cartes stats -->
        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-shopping-cart"></i></div>
            <div class="stat-info">
              <span class="stat-label">Commandes totales</span>
              <span class="stat-value">{{ stats.commandes_total }}</span>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon pending"><i class="fas fa-clock"></i></div>
            <div class="stat-info">
              <span class="stat-label">En attente</span>
              <span class="stat-value">{{ stats.commandes_attente }}</span>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon delivered"><i class="fas fa-check-circle"></i></div>
            <div class="stat-info">
              <span class="stat-label">Livrées</span>
              <span class="stat-value">{{ stats.commandes_livrees }}</span>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon revenue"><i class="fas fa-tag"></i></div>
            <div class="stat-info">
              <span class="stat-label">Chiffre d'affaires</span>
              <span class="stat-value">{{ formatPrice(stats.chiffre_affaires) }}</span>
            </div>
          </div>
        </div>

        <!-- Deux colonnes : commandes récentes + produits récents -->
        <div class="two-col-grid">
          <!-- Commandes récentes -->
          <div class="card">
            <div class="card-header">
              <h3>📦 Commandes récentes</h3>
              <router-link to="/vendeur/commandes" class="link">Voir tout →</router-link>
            </div>
            <div class="table-responsive">
              <table class="commandes-table">
                <thead>
                  <tr>
                    <th>N° commande</th>
                    <th>Client</th>
                    <th>Total</th>
                    <th>Statut</th>
                    <th>Date</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="c in commandesRecentes" :key="c.id">
                    <td>#{{ c.id }}</td>
                    <td>{{ c.client }}</td>
                    <td>{{ formatPrice(c.total) }}</td>
                    <td><span :class="['badge', getStatutBadge(c.statut).class]">{{ getStatutBadge(c.statut).text }}</span></td>
                    <td>{{ new Date(c.date).toLocaleDateString('fr-FR') }}</td>
                  </tr>
                  <tr v-if="commandesRecentes.length === 0">
                    <td colspan="5" class="empty-row">Aucune commande récente</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Produits récents / plus vendus -->
          <div class="card">
            <div class="card-header">
              <h3>🌾 Produits récents</h3>
              <router-link to="/vendeur/produits" class="link">Gérer →</router-link>
            </div>
            <div class="produits-list">
              <div v-for="p in produitsRecents" :key="p.id" class="produit-item">
                <div class="produit-image">
                  <img v-if="p.image" :src="p.image" alt="">
                  <div v-else class="img-placeholder"><i class="fas fa-seedling"></i></div>
                </div>
                <div class="produit-info">
                  <div class="produit-nom">{{ p.nom }}</div>
                  <div class="produit-meta">{{ formatPrice(p.prix) }} · Stock {{ p.stock }}</div>
                </div>
              </div>
              <div v-if="produitsRecents.length === 0" class="empty-row">Aucun produit trouvé</div>
            </div>
          </div>
        </div>

        <!-- Mini graphique (optionnel) : ventes par mois (simulation) -->
        <div class="card graph-card">
          <h3>📈 Évolution des ventes (derniers mois)</h3>
          <div class="bar-chart">
            <div class="bar-item" v-for="(mois, idx) in ['Mai', 'Avr', 'Mar', 'Fév', 'Jan']" :key="idx">
              <div class="bar-label">{{ mois }}</div>
              <div class="bar-container">
                <div class="bar-fill" :style="{ height: (Math.random() * 60 + 20) + '%' }"></div>
              </div>
              <div class="bar-value">{{ Math.floor(Math.random() * 8000 + 2000) }} MAD</div>
            </div>
          </div>
          <p class="graph-note">* Simulation – adaptez avec vos données réelles</p>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* ---------- LAYOUT PRINCIPAL ---------- */
.dashboard-vendeur {
  display: flex;
  height: 100vh;
  width: 100%;
}

.main-content {
  flex: 1;
  overflow-y: auto;
  padding: 2rem;
  background: #f6f4ef;
}

.dashboard-header {
  margin-bottom: 2rem;
}
.dashboard-header h2 {
  font-family: 'Fraunces', serif;
  font-size: 2rem;
  color: #2b4b2b;
  margin-bottom: 0.25rem;
}
.dashboard-header p {
  color: #7c6b5a;
  font-size: 0.9rem;
}

/* Cartes stats */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1.5rem;
  margin-bottom: 2rem;
}
.stat-card {
  background: white;
  border-radius: 24px;
  padding: 1.2rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}
.stat-icon {
  width: 52px;
  height: 52px;
  background: #f0ebe2;
  border-radius: 18px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  color: #c9a87b;
}
.stat-icon.pending { background: #fff4e5; color: #ed6c02; }
.stat-icon.delivered { background: #e8f5e9; color: #2e7d32; }
.stat-icon.revenue { background: #e3f2fd; color: #1976d2; }
.stat-info {
  flex: 1;
}
.stat-label {
  font-size: 0.7rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: #8b7a6b;
}
.stat-value {
  font-size: 1.6rem;
  font-weight: 800;
  color: #2b4b2b;
  line-height: 1.2;
}

/* Grille 2 colonnes */
.two-col-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.5rem;
  margin-bottom: 2rem;
}

/* Cartes communes */
.card {
  background: white;
  border-radius: 24px;
  padding: 1.5rem;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}
.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}
.card-header h3 {
  font-size: 1.2rem;
  font-weight: 600;
  color: #2b4b2b;
  margin: 0;
}
.link {
  color: #c9a87b;
  text-decoration: none;
  font-size: 0.8rem;
  font-weight: 500;
}
.link:hover {
  text-decoration: underline;
}

/* Tableau commandes */
.commandes-table {
  width: 100%;
  border-collapse: collapse;
}
.commandes-table th {
  text-align: left;
  padding: 0.75rem 0;
  font-size: 0.7rem;
  text-transform: uppercase;
  color: #8b7a6b;
  border-bottom: 1px solid #ede6dc;
}
.commandes-table td {
  padding: 0.75rem 0;
  border-bottom: 1px solid #f5f0e8;
  font-size: 0.85rem;
}
.badge {
  display: inline-block;
  padding: 0.2rem 0.6rem;
  border-radius: 20px;
  font-size: 0.7rem;
  font-weight: 600;
}
.badge-livree { background: #e8f5e9; color: #2e7d32; }
.badge-attente { background: #fff4e5; color: #ed6c02; }
.empty-row {
  text-align: center;
  padding: 1rem;
  color: #aa9a88;
}

/* Liste produits récents */
.produits-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}
.produit-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.5rem 0;
  border-bottom: 1px solid #f5f0e8;
}
.produit-image {
  width: 48px;
  height: 48px;
  background: #f0ebe2;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}
.produit-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.img-placeholder {
  font-size: 1.5rem;
  color: #c9a87b;
}
.produit-nom {
  font-weight: 600;
  color: #2b4b2b;
  margin-bottom: 0.2rem;
}
.produit-meta {
  font-size: 0.7rem;
  color: #7c6b5a;
}

/* Graphique simplifié */
.graph-card {
  text-align: center;
}
.bar-chart {
  display: flex;
  justify-content: space-around;
  align-items: flex-end;
  gap: 1rem;
  margin: 1.5rem 0;
}
.bar-item {
  flex: 1;
  text-align: center;
}
.bar-container {
  height: 120px;
  display: flex;
  align-items: flex-end;
  justify-content: center;
  margin: 0.5rem 0;
}
.bar-fill {
  width: 40px;
  background: #c9a87b;
  border-radius: 8px 8px 0 0;
  transition: height 0.3s;
  min-height: 10px;
}
.bar-label {
  font-size: 0.7rem;
  font-weight: 600;
  color: #7c6b5a;
}
.bar-value {
  font-size: 0.7rem;
  color: #2b4b2b;
}
.graph-note {
  font-size: 0.7rem;
  color: #aa9a88;
  margin-top: 1rem;
}

.loading {
  text-align: center;
  padding: 3rem;
  color: #8b7a6b;
}

/* Responsive */
@media (max-width: 900px) {
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  .two-col-grid {
    grid-template-columns: 1fr;
  }
  .main-content {
    padding: 1rem;
  }
}
@media (max-width: 600px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }
}
</style>