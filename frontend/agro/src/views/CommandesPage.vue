<template>
  <div class="page-container">
    <NavBar />
    <div class="main-content">
      <header class="page-header">
        <h1>📦 Mes Commandes</h1>
        <p>Suivez vos achats de fournitures et vos ventes.</p>
      </header>
      <div class="placeholder-card">
        <div class="icon">🚚</div>
        <h2>Gestion des Flux</h2>
        <p>Retrouvez ici l'historique de vos commandes et le statut de vos livraisons en cours.</p>
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
