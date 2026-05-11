<template>
  <div class="page-container">
    <NavBar />
    <div class="main-content">
      <header class="page-header">
        <h1>🛒 Marché</h1>
        <p>Achetez et vendez vos produits agricoles en direct.</p>
      </header>
      <div class="placeholder-card">
        <div class="icon">🚧</div>
        <h2>Espace en construction</h2>
        <p>Le marché Fellah sera bientôt disponible. Vous pourrez y trouver des semences, du matériel et vendre vos récoltes.</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import NavBar from '@/components/NavBar.vue'
</script>

<style scoped>
.page-container { display: flex; height: 100vh; }
.main-content { flex: 1; padding: 2rem; background: #fafcf8; }
.page-header { margin-bottom: 2rem; }
.placeholder-card {
  background: white;
  padding: 3rem;
  border-radius: 24px;
  text-align: center;
  box-shadow: 0 4px 20px rgba(0,0,0,0.05);
  max-width: 600px;
  margin: 0 auto;
}
.icon { font-size: 4rem; margin-bottom: 1rem; }
h1 { color: #1a4d2e; }
h2 { color: #2D7A4F; margin-bottom: 1rem; }
p { color: #6B6458; line-height: 1.6; }
</style>
