import { reactive, computed } from 'vue'

export const store = reactive({
  cart: JSON.parse(localStorage.getItem('falah_cart')) || [],
  
  addToCart(product) {
    const existing = this.cart.find(item => item.id === product.id)
    if (existing) {
      existing.quantity++
    } else {
      this.cart.push({ ...product, quantity: 1 })
    }
    this.saveCart()
  },
  
  removeFromCart(productId) {
    const index = this.cart.findIndex(item => item.id === productId)
    if (index !== -1) {
      if (this.cart[index].quantity > 1) {
        this.cart[index].quantity--
      } else {
        this.cart.splice(index, 1)
      }
    }
    this.saveCart()
  },
  
  clearCart() {
    this.cart = []
    this.saveCart()
  },
  
  saveCart() {
    localStorage.setItem('falah_cart', JSON.stringify(this.cart))
  },
  
  cartTotal: computed(() => {
    return store.cart.reduce((total, item) => total + (item.price * item.quantity), 0)
  }),
  
  cartCount: computed(() => {
    return store.cart.reduce((count, item) => count + item.quantity, 0)
  })
})
