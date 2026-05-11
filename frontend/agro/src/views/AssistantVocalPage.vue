<template>
  <div class="page-container">
    <NavBar />
    <div class="main-content">
      <header class="page-header">
        <h1>🎙️ Assistant Vocal</h1>
        <p>Parlez à Fellah pour obtenir des conseils agricoles.</p>
      </header>
      <!-- Remplacer le placeholder par le chatbot vocal -->
      <AgroBot />
    </div>
  </div>
</template>

<script setup>
import NavBar from '@/components/NavBar.vue'
import AgroBot from '@/components/AgroBot.vue'
</script>

<style scoped>
.page-container { display: flex; height: 100vh; }
.main-content { flex: 1; padding: 2rem; background: #fafcf8; }
.page-header { margin-bottom: 2rem; }
/* Styles du composant AgroBot sont déjà dans son scoped, pas besoin ici */
</style>