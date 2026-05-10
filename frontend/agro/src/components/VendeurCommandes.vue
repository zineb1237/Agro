<script setup>
import { ref, onMounted } from 'vue'

const commandes = ref([])

const fetchCommandes = async () => {
  try {
    const response = await fetch('http://localhost:8000/api_vendeur_commandes.php?vendeur_id=1')
    const data = await response.json()
    commandes.value = data
  } catch (error) {
    console.error('Erreur:', error)
  }
}

const formatPrice = (p) => p.toLocaleString('fr-FR', { style: 'currency', currency: 'EUR' })

const marquerLivre = async (id) => {
  try {
    const response = await fetch('http://localhost:8000/api_vendeur_livrer.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ commande_id: id })
    })
    const result = await response.json()
    if (result.success) {
      fetchCommandes() // Rafraîchir la liste
    }
  } catch (error) {
    console.error('Erreur lors de la livraison:', error)
  }
}

onMounted(fetchCommandes)
</script>

<template>
  <div class="vendeur-commandes">
    <h2>Commandes Reçues</h2>
    
    <div v-if="commandes.length > 0" class="commandes-list">
      <div v-for="c in commandes" :key="c.id" class="commande-card">
        <div class="card-header">
          <div class="order-id">Commande #{{ c.id }}</div>
          <div class="order-date">{{ c.date }}</div>
          <span class="status-badge" :class="c.status.toLowerCase().replace(' ', '-')">{{ c.status }}</span>
        </div>
        
        <div class="client-info">
          <i class="fas fa-user"></i>
          <span>{{ c.agriculteur_nom }}</span>
          <div class="address">
            <i class="fas fa-map-marker-alt"></i>
            {{ c.adresse || 'Adresse non fournie' }}
          </div>
        </div>

        <div class="items-list">
          <div v-for="item in c.produits" :key="item.id" class="item">
            <span>{{ item.nom }}</span>
            <span>x{{ item.quantite }}</span>
            <span class="item-price">{{ formatPrice(item.prixUnitaire) }}</span>
          </div>
        </div>

        <div class="card-footer">
          <div class="total">Total: {{ formatPrice(c.total) }}</div>
          <button 
            v-if="c.status === 'En attente'" 
            class="btn-deliver"
            @click="marquerLivre(c.id)"
          >
            <i class="fas fa-truck"></i> Marquer comme livré
          </button>
        </div>
      </div>
    </div>
    <div v-else class="empty-state">
      <i class="fas fa-box-open"></i>
      <p>Aucune commande reçue pour le moment.</p>
    </div>
  </div>
</template>

<style scoped>
.commandes-list {
  display: grid;
  gap: 1.5rem;
  margin-top: 2rem;
}

.commande-card {
  background: white;
  border-radius: 16px;
  padding: 1.5rem;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid #eee;
}

.order-id { font-weight: 800; color: #2D1A0E; }
.order-date { color: #999; font-size: 0.85rem; }

.status-badge {
  padding: 4px 10px;
  border-radius: 6px;
  font-size: 0.7rem;
  font-weight: 800;
  text-transform: uppercase;
}

.status-badge.en.attente { background: #fff3e0; color: #ef6c00; }
.status-badge.livré { background: #e8f5e9; color: #2e7d32; }

.client-info {
  margin-bottom: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.client-info span { font-weight: 700; }
.address { font-size: 0.9rem; color: #666; display: flex; gap: 8px; }

.items-list {
  background: #fdfbf7;
  padding: 1rem;
  border-radius: 8px;
  margin-bottom: 1.5rem;
}

.item {
  display: flex;
  justify-content: space-between;
  padding: 0.5rem 0;
  font-size: 0.9rem;
}

.item-price { font-weight: 700; }

.card-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.total { font-size: 1.2rem; font-weight: 800; color: #2d4a1e; }

.empty-state {
  text-align: center;
  padding: 4rem 2rem;
  color: #999;
}

.empty-state i {
  font-size: 3rem;
  margin-bottom: 1rem;
  opacity: 0.3;
}

.btn-deliver {
  background: #2d4a1e;
  color: white;
  border: none;
  padding: 0.6rem 1.2rem;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
}
</style>
