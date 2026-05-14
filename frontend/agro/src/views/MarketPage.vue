<script setup>
import { ref, onMounted } from 'vue'
import { store } from '../store'
import NavBar from '@/components/NavBar.vue'

const isCartOpen = ref(false)
const showConfirmation = ref(false)
const produits = ref([])
const adresse = ref('')
const isOrdering = ref(false)

const fetchProduits = async () => {
  try {
    const response = await fetch('http://localhost:8000/api/produits')
    const data = await response.json()
    produits.value = data
  } catch (error) {
    console.error('Erreur lors de la récupération des produits:', error)
  }
}

onMounted(() => {
  fetchProduits()
})

const addToCart = (p) => {
  store.addToCart(p)
}

const validerCommande = async () => {
  if (store.cart.length === 0) return
  if (!adresse.value) {
    alert('Veuillez renseigner votre adresse de livraison.')
    return
  }

  isOrdering.value = true
  try {
    const response = await fetch('http://localhost:8000/api/commander', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        cart: store.cart,
        adresse: adresse.value
      })
    })
    const result = await response.json()
    
    if (result.success) {
      showConfirmation.value = true
      store.clearCart()
      isCartOpen.value = false
      adresse.value = ''
      
      setTimeout(() => {
        showConfirmation.value = false
      }, 4000)
    }
  } catch (error) {
    alert('Erreur lors de la commande')
  } finally {
    isOrdering.value = false
  }
}

const formatPrice = (p) => {
  return new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'EUR',
    minimumFractionDigits: 2
  }).format(p)
}
</script>

<template>
  <div class="boutique-container">
    <NavBar />
    <div class="main-content">
      <div class="achat-container">
        <div class="header-section">
          <div class="title-area">
            <h2>Boutique Agricole</h2>
            <p class="subtitle">Équipez-vous avec les meilleurs produits pour vos cultures.</p>
          </div>
          
          <button class="cart-toggle-btn" @click="isCartOpen = true">
            <div class="cart-icon-wrapper">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="9" cy="21" r="1"></circle>
                <circle cx="20" cy="21" r="1"></circle>
                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
              </svg>
              <span class="cart-badge-count" v-if="store.cartCount > 0">{{ store.cartCount }}</span>
            </div>
            <span class="cart-text"> Mon Panier</span>
          </button>
        </div>

        <div v-if="showConfirmation" class="toast-success animate-slide-down">
          <i class="fas fa-check-circle"></i>
          <span>Commande validée avec succès </span>
        </div>

        <div class="products-grid">
          <div v-for="p in produits" :key="p.id" class="product-card">
            <div class="product-badges">
              <span v-if="p.promotion" class="badge promo">PROMO</span>
              <span v-if="p.certificationBio" class="badge bio">BIO</span>
            </div>
            <div class="product-image-area">
              <img v-if="p.images && p.images.length" :src="p.images[0]" class="product-img" alt="">
              <div v-else class="product-icon">{{ p.icon }}</div>
            </div>
            <div class="product-info">
              <h3>{{ p.nom }}</h3>
              <p class="description">{{ p.description }}</p>
              <div class="price-row">
                <div class="price">{{ formatPrice(p.prix) }}</div>
                <div class="stock-info" :class="{ 'low-stock': p.stock < 10 }">
                  <span class="stock-label">Stock:</span>
                  <span class="stock-value">{{ p.stock }}</span>
                </div>
              </div>
            </div>
            <button @click="addToCart(p)" class="btn-add-to-cart" :disabled="p.stock <= 0">
              <svg v-if="p.stock > 0" class="cart-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="9" cy="21" r="1"></circle>
                <circle cx="20" cy="21" r="1"></circle>
                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
              </svg>
              <span>{{ p.stock > 0 ? 'Ajouter au panier' : 'Rupture de stock' }}</span>
            </button>
          </div>
        </div>

        <!-- Panier Latéral -->
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
                <div class="item-image-wrapper">
                  <img v-if="item.images && item.images.length" :src="item.images[0]" class="item-img" alt="">
                  <div v-else class="item-icon">{{ item.icon }}</div>
                </div>
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
              <div class="address-input">
                <label>Adresse de livraison :</label>
                <input v-model="adresse" type="text" placeholder="Entrez votre adresse complète..." class="input-address">
              </div>
              <div class="total-line">
                <span>Total :</span>
                <strong>{{ formatPrice(store.cartTotal) }}</strong>
              </div>
              <button class="btn-primary full-width" @click="validerCommande" :disabled="isOrdering">
                {{ isOrdering ? 'Traitement...' : 'Valider la commande' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Structure avec NavBar fixe (identique aux autres pages) */
.boutique-container {
  display: flex;
  height: 100vh;
  width: 100%;
}

.main-content {
  flex: 1;
  overflow-y: auto;
  padding: 2rem;
  background: #fafcf8;
}

.achat-container {
  max-width: 1300px;
  margin: 0 auto;
 
}

.header-section {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  margin-bottom: 15px;
  margin-top: -10px;
  flex-wrap: wrap;
 padding: 0;
  
}

.title-area h2 {
  margin: 0;
  color: #2D1A0E;
  font-family: 'Fraunces', serif;
  font-size: 2.5rem;
  font-weight: 800;

}

.subtitle {
  color: #6b4c3a;
  margin-top: 0.5rem;
  font-size: 1.1rem;
}

.cart-toggle-btn {
  background: white;
  border: 1px solid rgba(0, 0, 0, 0.08);
  padding: 0.7rem 1.4rem;
  border-radius: 12px;
  margin-bottom: 40px;
  display: flex;
  align-items: center;
 
  cursor: pointer;
  transition: all 250ms ease;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
  
}

.cart-toggle-btn:hover {
  border-color: #f5a623;
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
}

.cart-icon-wrapper {
  position: relative;
  display: flex;
  align-items: center;
  color: #2D1A0E;
}

.cart-badge-count {
  position: absolute;
  top: -8px;
  right: -8px;
  background: #e53935;
  color: white;
  min-width: 18px;
  height: 18px;
  border-radius: 50%;
  font-size: 0.7rem;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 800;
  border: 2px solid white;
}

.cart-text {
  font-weight: 700;
  font-size: 0.95rem;
  color: #2D1A0E;
}

.products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(290px, 1fr));
  gap: 28px;
  margin: 0 auto;
  
}

.product-card {
  background: #ffffff;
  border: 1px solid rgba(0, 0, 0, 0.04);
  border-radius: 16px;
  padding: 1.2rem;
  display: flex;
  flex-direction: column;
  transition: all 250ms ease;
  position: relative;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02), 0 8px 16px rgba(0, 0, 0, 0.04);
  height:450px ;
}

.product-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.04), 0 16px 32px rgba(0, 0, 0, 0.08);
}

.product-badges {
  position: absolute;
  top: 16px;
  right: 16px;
  display: flex;
  flex-direction: column;
  gap: 6px;
  z-index: 2;
}

.badge {
  font-size: 0.7rem;
  font-weight: 700;
  padding: 4px 10px;
  border-radius: 4px;
  color: white;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.badge.promo { background: #e53935; }
.badge.bio { background: #4caf50; }

.product-image-area {
  background: radial-gradient(circle at center, #fdfbf7 0%, #f5f0e8 100%);
  border-radius: 12px;
  height: 150px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 1.2rem;
  padding: 2rem;
}

.product-icon {
  font-size: 4rem;
  filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.1));
}

.product-img {
  width: 200px;
  height:200px;
  object-fit: contain;
}

.product-info h3 {
  font-family: 'Fraunces', serif;
  font-size: 1.3rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
  color: #2D1A0E;
}

.product-info .description {
  font-size: 0.9rem;
  color: #8b7a6b;
  margin-bottom: 1.2rem;
  height: 2.8rem;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.price-row {
  display: flex;
  justify-content: space-between;
  align-items: baseline;
  margin-bottom: 1.2rem;
}

.price {
  font-size: 1.5rem;
  font-weight: 800;
  color: #2D1A0E;
}

.stock-info {
  font-size: 0.78rem;
  color: #8b7a6b;
  opacity: 0.55;
  display: flex;
  gap: 4px;
}

.stock-info.low-stock {
  opacity: 1;
  color: #f5a623;
  font-weight: 700;
}

.btn-add-to-cart {
  width: 100%;
  background: #f5a623;
  color: white;
  border: none;
  padding: 0.8rem;
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.95rem;
  letter-spacing: 0.03em;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  cursor: pointer;
  transition: all 250ms ease;
  box-shadow: 0 4px 12px rgba(245, 166, 35, 0.2);
}

.btn-add-to-cart:hover:not(:disabled) {
  transform: scale(1.02);
  box-shadow: 0 6px 16px rgba(245, 166, 35, 0.3);
  background: #f7b445;
}

.btn-add-to-cart:active:not(:disabled) {
  transform: scale(0.98);
}

.btn-add-to-cart:disabled {
  background: #ccc;
  cursor: not-allowed;
  box-shadow: none;
}

/* Panier latéral (identique à l'original) */
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
  border-bottom: 1px solid #eee;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.btn-close {
  background: none;
  border: none;
  font-size: 1.2rem;
  cursor: pointer;
  color: #8b7a6b;
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
  border-bottom: 1px solid #eee;
}

.item-icon, .item-img {
  width: 50px;
  height: 50px;
  object-fit: cover;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.item-icon {
  font-size: 2rem;
  background: #fdfbf7;
}

.item-image-wrapper {
  flex-shrink: 0;
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
  background: #fdfbf7;
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
  color: #8b7a6b;
  gap: 1rem;
}

.cart-empty i {
  font-size: 3rem;
  opacity: 0.3;
}

.address-input {
  margin-bottom: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.address-input label {
  font-size: 0.85rem;
  font-weight: 700;
  color: #666;
}

.input-address {
  width: 100%;
  padding: 0.8rem;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-size: 0.9rem;
}

.cart-footer {
  padding: 1.5rem;
  border-top: 1px solid #eee;
  background: #fdfbf7;
}

.total-line {
  display: flex;
  justify-content: space-between;
  font-size: 1.2rem;
  margin-bottom: 1.5rem;
}

.total-line strong {
  color: #2c5f2d;
}

.btn-primary {
  background: #2c5f2d;
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
  .main-content {
    padding: 1rem;
  }
  .products-grid {
    gap: 16px;
  }
}
</style>