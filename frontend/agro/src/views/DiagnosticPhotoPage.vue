<template>
  <div class="page-container">
    <NavBar />
    <div class="main-content">
      <header class="page-header">
        <h1>🔬 Diagnostic Photo</h1>
        <p>Identifiez les maladies et ravageurs grâce à l'IA.</p>
      </header>
      <div class="placeholder-card">
        <div class="icon">📸</div>
        <h2>Analyse Intelligente</h2>
        <p>Prenez une photo de votre plante pour recevoir un diagnostic instantané et des conseils de traitement.</p>
        <button class="btn-dummy">Prendre une photo</button>
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
.btn-dummy {
  margin-top: 2rem;
  padding: 0.8rem 2rem;
  background: #2D7A4F;
  color: white;
  border: none;
  border-radius: 30px;
  font-weight: 600;
  cursor: pointer;
}
h1 { color: #1a4d2e; }
h2 { color: #2D7A4F; margin-bottom: 1rem; }
p { color: #6B6458; line-height: 1.6; }
</style>
