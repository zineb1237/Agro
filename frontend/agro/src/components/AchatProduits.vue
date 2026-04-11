<script setup>
import { ref } from 'vue'
import { store } from '../store'

const isCartOpen = ref(false)
const showConfirmation = ref(false)

const produits = [
  { id: 1, nom: 'Engrais Bio NPK', prix: 45.90, icon: '🌱', description: 'Favorise la croissance racinaire' },
  { id: 2, nom: 'Semences Blé Hiver', prix: 120.00, icon: '🌾', description: 'Haute résistance aux maladies' },
  { id: 3, nom: 'Fongicide Naturel', prix: 35.50, icon: '🛡️', description: 'Protection contre l\'oïdium' },
  { id: 4, nom: 'Herbicide Sélectif', prix: 58.00, icon: '🌿', description: 'Efficace sur adventices' }
]

const addToCart = (p) => {
  store.addToCart(p)
  // Animation feedback (facultatif)
}

const validerCommande = () => {
  if (store.cart.length === 0) return
  
  showConfirmation.value = true
  store.clearCart()
  isCartOpen.value = false
  
  setTimeout(() => {
    showConfirmation.value = false
  }, 4000)
}

const formatPrice = (p) => p.toLocaleString('fr-FR', { style: 'currency', currency: 'EUR' })
</script>

<template>
  <div class="achat-container">
    <div class="header-section">
      <div class="title-area">
        <i class="fas fa-shopping-basket icon"></i>
        <h2>Boutique Agricole</h2>
        <p class="subtitle">Équipez-vous avec les meilleurs produits pour vos cultures.</p>
      </div>
      
      <button class="cart-toggle" @click="isCartOpen = true">
        <i class="fas fa-shopping-cart"></i>
        <span class="cart-badge" v-if="store.cartCount > 0">{{ store.cartCount }}</span>
        <span>Mon Panier</span>
      </button>
    </div>

    <div v-if="showConfirmation" class="toast-success animate-slide-down">
      <i class="fas fa-check-circle"></i>
      <span>Commande validée avec succès ! Un email de confirmation vous a été envoyé.</span>
    </div>

    <div class="products-grid">
      <div v-for="p in produits" :key="p.id" class="product-card">
        <div class="product-icon">{{ p.icon }}</div>
        <div class="product-info">
          <h3>{{ p.nom }}</h3>
          <p>{{ p.description }}</p>
          <div class="price">{{ formatPrice(p.prix) }}</div>
        </div>
        <button @click="addToCart(p)" class="btn-secondary">
          <i class="fas fa-plus"></i>
          Ajouter au panier
        </button>
      </div>
    </div>

    <!-- Panier Latéral (Overlay) -->
    <div class="cart-overlay" :class="{ 'active': isCartOpen }" @click.self="isCartOpen = false">
      <div class="cart-sidebar" :class="{ 'active': isCartOpen }">
        <div class="cart-header">
          <h3>Votre Panier</h3>
          <button @click="isCartOpen = false" class="btn-close">
            <i class="fas fa-times"></i>
          </button>
        </div>

        <div class="cart-items" v-if="store.cart.length > 0">
          <div v-for="item in store.cart" :key="item.id" class="cart-item">
            <div class="item-icon">{{ item.icon }}</div>
            <div class="item-details">
              <h4>{{ item.nom }}</h4>
              <div class="item-meta">
                <span>{{ formatPrice(item.prix) }} x {{ item.quantity }}</span>
                <div class="quantity-controls">
                  <button @click="store.removeFromCart(item.id)">-</button>
                  <span>{{ item.quantity }}</span>
                  <button @click="store.addToCart(item)">+</button>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="cart-empty" v-else>
          <i class="fas fa-shopping-cart"></i>
          <p>Votre panier est vide</p>
        </div>

        <div class="cart-footer" v-if="store.cart.length > 0">
          <div class="total-line">
            <span>Total :</span>
            <strong>{{ formatPrice(store.cartTotal) }}</strong>
          </div>
          <button class="btn-primary full-width" @click="validerCommande">
            Valider la commande
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.achat-container {
  max-width: 1000px;
  margin: 0 auto;
}

.header-section {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 2rem;
}

.title-area h2 {
  margin: 0;
  color: var(--text-color);
}

.subtitle {
  color: var(--text-muted);
  margin-top: 0.5rem;
}

.icon {
  font-size: 1.5rem;
  color: var(--sun-color);
  margin-bottom: 0.5rem;
}

.cart-toggle {
  background: white;
  border: 1px solid var(--border-color);
  padding: 0.8rem 1.5rem;
  border-radius: 30px;
  display: flex;
  align-items: center;
  gap: 0.8rem;
  cursor: pointer;
  position: relative;
  transition: all 0.3s ease;
  font-weight: 600;
  color: var(--text-color);
}

.cart-toggle:hover {
  border-color: var(--sun-color);
  box-shadow: 0 4px 12px rgba(232, 160, 32, 0.15);
}

.cart-badge {
  position: absolute;
  top: -5px;
  right: -5px;
  background: var(--alert-color);
  color: white;
  width: 22px;
  height: 22px;
  border-radius: 50%;
  font-size: 0.75rem;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
}

.products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 1.5rem;
}

.product-card {
  background: var(--card-bg);
  border: 1px solid var(--border-color);
  border-radius: 16px;
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  transition: all 0.3s ease;
}

.product-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
  border-color: var(--wheat-color);
}

.product-icon {
  font-size: 3.5rem;
  margin-bottom: 1.5rem;
  text-align: center;
  background: var(--cream-color);
  border-radius: 12px;
  padding: 1rem;
}

.product-info h3 {
  margin: 0 0 0.5rem 0;
  font-size: 1.1rem;
}

.product-info p {
  color: var(--text-muted);
  font-size: 0.85rem;
  margin-bottom: 1rem;
  line-height: 1.4;
}

.price {
  font-size: 1.25rem;
  font-weight: 800;
  color: var(--soil-color);
  margin-bottom: 1.5rem;
}

.btn-secondary {
  background: var(--sun-color);
  color: white;
  border: none;
  padding: 0.8rem;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 0.5rem;
  margin-top: auto;
}

.btn-secondary:hover {
  background: #d49010;
  box-shadow: 0 4px 12px rgba(232, 160, 32, 0.2);
}

/* Sidebar Panier */
.cart-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.4);
  z-index: 1000;
  visibility: hidden;
  opacity: 0;
  transition: all 0.3s ease;
}

.cart-overlay.active {
  visibility: visible;
  opacity: 1;
}

.cart-sidebar {
  position: absolute;
  top: 0;
  right: -350px;
  width: 350px;
  height: 100%;
  background: white;
  transition: all 0.4s cubic-bezier(0.23, 1, 0.32, 1);
  display: flex;
  flex-direction: column;
}

.cart-sidebar.active {
  right: 0;
}

.cart-header {
  padding: 1.5rem;
  border-bottom: 1px solid var(--border-color);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.btn-close {
  background: none;
  border: none;
  font-size: 1.2rem;
  cursor: pointer;
  color: var(--text-muted);
}

.cart-items {
  flex: 1;
  overflow-y: auto;
  padding: 1rem;
}

.cart-item {
  display: flex;
  gap: 1rem;
  padding: 1rem 0;
  border-bottom: 1px solid var(--border-color);
}

.item-icon {
  font-size: 2rem;
  background: var(--cream-color);
  width: 50px;
  height: 50px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
}

.item-details h4 {
  margin: 0 0 0.5rem 0;
  font-size: 0.95rem;
}

.item-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
  width: 230px;
}

.quantity-controls {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background: var(--cream-color);
  padding: 0.2rem 0.5rem;
  border-radius: 4px;
}

.quantity-controls button {
  background: none;
  border: none;
  font-weight: bold;
  cursor: pointer;
  padding: 0 0.5rem;
}

.cart-empty {
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  color: var(--text-muted);
  gap: 1rem;
}

.cart-empty i {
  font-size: 3rem;
  opacity: 0.3;
}

.cart-footer {
  padding: 1.5rem;
  border-top: 1px solid var(--border-color);
  background: var(--cream-color);
}

.total-line {
  display: flex;
  justify-content: space-between;
  font-size: 1.2rem;
  margin-bottom: 1.5rem;
}

.total-line strong {
  color: var(--soil-color);
}

.btn-primary {
  background: var(--leaf-color);
  color: white;
  border: none;
  padding: 1rem;
  border-radius: 8px;
  font-weight: 700;
  cursor: pointer;
  width: 100%;
}

.toast-success {
  position: fixed;
  top: 20px;
  left: 50%;
  transform: translateX(-50%);
  background: #3D6B35;
  color: white;
  padding: 1rem 2rem;
  border-radius: 50px;
  display: flex;
  align-items: center;
  gap: 1rem;
  box-shadow: 0 8px 30px rgba(0,0,0,0.2);
  z-index: 2000;
}

@keyframes slideDown {
  from { top: -100px; opacity: 0; }
  to { top: 20px; opacity: 1; }
}

.animate-slide-down {
  animation: slideDown 0.5s cubic-bezier(0.23, 1, 0.32, 1);
}

@media (max-width: 600px) {
  .cart-sidebar {
    width: 100%;
    right: -100%;
  }
}
</style>
