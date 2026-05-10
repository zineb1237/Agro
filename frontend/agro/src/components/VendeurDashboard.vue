<script setup>
import { ref, onMounted } from 'vue'

const stats = ref([
  { label: 'Ventes Totales', value: '0 €', icon: 'fas fa-euro-sign', color: '#4caf50', key: 'total_ventes' },
  { label: 'Produits Actifs', value: '0', icon: 'fas fa-box', color: '#f5a623', key: 'produits_actifs' },
  { label: 'Livraisons Faites', value: '0', icon: 'fas fa-truck', color: '#2d4a1e', key: 'commandes_livrees' },
  { label: 'Clients Uniques', value: '0', icon: 'fas fa-users', color: '#2196f3', key: 'clients_uniques' }
])

const topProduits = ref([])

const fetchStats = async () => {
  try {
    const response = await fetch('http://localhost:8000/api_vendeur_stats.php?vendeur_id=1')
    const data = await response.json()
    
    stats.value.forEach(s => {
      if (s.key === 'total_ventes') s.value = new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(data.total_ventes || 0)
      if (s.key === 'produits_actifs') s.value = data.produits_actifs || 0
      if (s.key === 'commandes_livrees') s.value = data.commandes_livrees || 0
      if (s.key === 'clients_uniques') s.value = data.clients_uniques || 0
    })
    
    topProduits.value = data.top_produits || []
  } catch (error) {
    console.error('Erreur stats:', error)
  }
}

onMounted(fetchStats)
</script>

<template>
  <div class="vendeur-dashboard">
    <div class="dashboard-header">
      <h2>Tableau de Bord Vendeur</h2>
      <p>Bienvenue sur votre espace de gestion commerciale.</p>
    </div>

    <div class="stats-grid">
      <div v-for="s in stats" :key="s.label" class="stat-card">
        <div class="stat-icon" :style="{ backgroundColor: s.color + '15', color: s.color }">
          <i :class="s.icon"></i>
        </div>
        <div class="stat-info">
          <span class="stat-label">{{ s.label }}</span>
          <span class="stat-value">{{ s.value }}</span>
        </div>
      </div>
    </div>

    <div class="dashboard-sections">
      <div class="section-card">
        <h3>Top Produits les plus vendus</h3>
        <div class="top-products-list">
          <div v-for="p in topProduits" :key="p.nom" class="top-product-item">
            <div class="product-info-mini">
              <span class="product-icon-mini">📦</span>
              <span class="product-name-mini">{{ p.nom }}</span>
            </div>
            <div class="product-stats-mini">
              <span class="sold-count"><strong>{{ p.total_vendus }}</strong> vendus</span>
              <span class="revenue-mini">{{ new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(p.revenu) }}</span>
            </div>
          </div>
          <div v-if="topProduits.length === 0" class="empty-stats">
            Aucune vente enregistrée pour le moment.
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.dashboard-header {
  margin-bottom: 2.5rem;
}

.dashboard-header h2 {
  font-family: 'Fraunces', serif;
  font-size: 2rem;
  margin-bottom: 0.5rem;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 1.5rem;
  margin-bottom: 3rem;
}

.stat-card {
  background: white;
  padding: 1.5rem;
  border-radius: 16px;
  display: flex;
  align-items: center;
  gap: 1.2rem;
  box-shadow: 0 4px 12px rgba(0,0,0,0.03);
}

.stat-icon {
  width: 50px;
  height: 50px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
}

.stat-label {
  display: block;
  font-size: 0.85rem;
  color: #666;
  margin-bottom: 0.2rem;
}

.stat-value {
  display: block;
  font-size: 1.4rem;
  font-weight: 800;
  color: #2D1A0E;
}

.section-card {
  background: white;
  padding: 2rem;
  border-radius: 20px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.03);
}

.top-products-list {
  margin-top: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.top-product-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  background: #fdfbf7;
  border-radius: 12px;
  transition: transform 0.2s;
}

.top-product-item:hover {
  transform: translateX(8px);
}

.product-info-mini {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.product-name-mini {
  font-weight: 700;
  color: #2D1A0E;
}

.product-stats-mini {
  display: flex;
  align-items: center;
  gap: 2rem;
}

.sold-count {
  font-size: 0.9rem;
  color: #666;
}

.revenue-mini {
  font-weight: 800;
  color: #2d4a1e;
}

.empty-stats {
  text-align: center;
  padding: 2rem;
  color: #999;
  font-style: italic;
}
</style>
