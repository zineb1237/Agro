<template>
  <div class="page-container">
    <NavBar />
    <div class="main-content">
      <header class="page-header">
        <h1>🎙️ Assistant Vocal</h1>
        <span>Parlez à Fellah pour obtenir des conseils agricoles.</span>
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
h1{
  margin-top: 30px;
 font-size: 25px;
}

.page-header {   line-height: 20px;
  
  border-bottom: 2px solid #e8ddd0;
  background: transparent;margin-top: -40px;}
/* Styles du composant AgroBot sont déjà dans son scoped, pas besoin ici */
</style>