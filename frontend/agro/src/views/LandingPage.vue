


<template>
     <div class="landing">
  <div>
    <!-- Custom cursor -->
    <div class="cursor" id="cur"></div>
    <div class="cursor-ring" id="ring"></div>

    <!-- Notification -->
    <div class="notif" id="notif"></div>

    <!-- ─── NAV ─── -->
    <nav>
      <div class="nav-logo">
        <span class="nav-logo-ar">فلاح</span>
        <span class="nav-logo-lat">Fellah</span>
      </div>
      <ul class="nav-links">
        <li><a href="#about">À propos</a></li>
        <li><a href="#features">Fonctionnalités</a></li>
        <li><a href="#process">Comment ça marche</a></li>
        <li><a href="#testimonials">Témoignages</a></li>
      </ul>
      <router-link to="/login" class="nav-cta">Commencer</router-link>
    </nav>

    <!-- ─── HERO ─── -->
    <section class="hero">
      <div class="hero-bg"></div>
      <div class="hero-grain"></div>

      <div class="hero-lines">
        <div class="hero-line"></div>
        <div class="hero-line"></div>
        <div class="hero-line"></div>
      </div>

      <div class="botanical botanical-1">🌿</div>
      <div class="botanical botanical-2">🌱</div>

      <div class="hero-number">2026</div>

      <div class="hero-content">
        <div class="hero-eyebrow">
          <div class="hero-eyebrow-line"></div>
          <div class="hero-eyebrow-text">Plateforme Agri-Intelligence · Maroc</div>
        </div>

        <h1 class="hero-h1">
          L'avenir de<br>
          <em>l'agriculture</em><br>
          marocaine
        </h1>
        <span class="hero-h1-ar">مستقبل الفلاحة المغربية</span>

        <p class="hero-desc">
          Diagnostics IA, capteurs IoT, traçabilité blockchain et assistant vocal en darija — tout ce dont l'agriculteur moderne a besoin, réuni en une seule plateforme.
        </p>

        <div class="hero-actions">
          <a href="#features" class="btn-primary">
            <span>Découvrir la plateforme</span>
            <span>→</span>
          </a>
          <a href="#process" class="btn-ghost">Voir comment ça marche</a>
        </div>
      </div>
    </section>

    <!-- ─── MARQUEE ─── -->
    <div class="marquee-strip">
      <div class="marquee-track" id="mq">
        <span class="marquee-item accent">Diagnostic IA</span>
        <span class="marquee-item">Capteurs IoT temps réel</span>
        <span class="marquee-item accent">Passeport Blockchain</span>
        <span class="marquee-item">Assistant Darija</span>
        <span class="marquee-item accent">Marketplace Agricole</span>
        <span class="marquee-item">Prédiction Rendement</span>
        <span class="marquee-item accent">Certification Bio</span>
        <span class="marquee-item">Planification Intelligente</span>
        <span class="marquee-item accent">Diagnostic IA</span>
        <span class="marquee-item">Capteurs IoT temps réel</span>
        <span class="marquee-item accent">Passeport Blockchain</span>
        <span class="marquee-item">Assistant Darija</span>
        <span class="marquee-item accent">Marketplace Agricole</span>
        <span class="marquee-item">Prédiction Rendement</span>
        <span class="marquee-item accent">Certification Bio</span>
        <span class="marquee-item">Planification Intelligente</span>
      </div>
    </div>

    <!-- ─── ABOUT ─── -->
    <section class="about" id="about">
      <div class="s-inner">
        <div class="about-grid">
          <div class="reveal">
            <div class="about-label">Notre mission</div>
            <h2 class="about-h2">
              Technologie de pointe<br>
              au service de la <em>terre</em>
            </h2>
            <p class="about-p">
              Fellah est née d'un constat simple : l'agriculteur marocain mérite les mêmes outils technologiques que les grandes exploitations mondiales — mais adaptés à sa réalité, sa langue, et ses besoins spécifiques.
            </p>
            <p class="about-p">
              De Fès-Meknès au Souss-Massa, notre plateforme connecte chaque parcelle aux données météo, aux modèles IA et à un écosystème de fournisseurs certifiés.
            </p>
          </div>

          <div class="about-right reveal reveal-delay-2">
            <div class="about-card-main">
              <div class="acm-num">68%</div>
              <div class="acm-label">d'économie d'eau<br>en irrigation de précision</div>
              <div class="acm-bar"></div>
              <div class="acm-sub">Données moyennes · Saison 2025</div>
            </div>
            <div class="about-card-float">
              <div class="acf-num">94%</div>
              <div class="acf-label">Précision diagnostic IA</div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ─── FEATURES ─── -->
    <section class="features" id="features">
      <div class="s-inner">
        <div class="features-head reveal">
          <div class="about-label">Ce que nous offrons</div>
          <h2 class="features-h2">
            Six outils qui<br>
            <em>transforment</em> votre exploitation
          </h2>
        </div>

        <div class="feat-grid">
          <div class="feat-card reveal">
            <div class="feat-num">01</div>
            <div class="feat-icon-wrap">🔬</div>
            <div class="feat-title">Diagnostic IA instantané</div>
            <p class="feat-desc">Photographiez votre plante. Notre modèle CNN identifie maladies, carences et nuisibles en moins de 2 secondes avec 94% de précision.</p>
            <a href="#" class="feat-link" @click.prevent="notify('🔬 Diagnostic IA — En savoir plus')">En savoir plus →</a>
          </div>

          <div class="feat-card reveal reveal-delay-1">
            <div class="feat-num">02</div>
            <div class="feat-icon-wrap">📡</div>
            <div class="feat-title">Capteurs IoT & alertes</div>
            <p class="feat-desc">Humidité, pH, température, azote — surveillez chaque paramètre de vos parcelles en temps réel depuis votre téléphone.</p>
            <a href="#" class="feat-link" @click.prevent="notify('📡 Capteurs IoT — En savoir plus')">En savoir plus →</a>
          </div>

          <div class="feat-card reveal reveal-delay-2">
            <div class="feat-num">03</div>
            <div class="feat-icon-wrap">🔗</div>
            <div class="feat-title">Passeport blockchain</div>
            <p class="feat-desc">Chaque plante reçoit un identifiant infalsifiable. Traçabilité complète graine → récolte. Idéal pour la certification bio et l'export.</p>
            <a href="#" class="feat-link" @click.prevent="notify('🔗 Blockchain — En savoir plus')">En savoir plus →</a>
          </div>

          <div class="feat-card reveal reveal-delay-1">
            <div class="feat-num">04</div>
            <div class="feat-icon-wrap">🎙️</div>
            <div class="feat-title">Assistant vocal Darija</div>
            <p class="feat-desc">"وقتاش نسمّد؟" — Posez vos questions en darija ou en français. L'assistant répond vocalement avec des recommandations personnalisées.</p>
            <a href="#" class="feat-link" @click.prevent="notify('🎙️ Assistant Vocal — En savoir plus')">En savoir plus →</a>
          </div>

          <div class="feat-card reveal reveal-delay-2">
            <div class="feat-num">05</div>
            <div class="feat-icon-wrap">🛒</div>
            <div class="feat-title">Marketplace agricole</div>
            <p class="feat-desc">Semences, engrais, équipements — achetez directement auprès de fournisseurs certifiés. Paiement sécurisé, livraison en 48h.</p>
            <a href="#" class="feat-link" @click.prevent="notify('🛒 Marketplace — En savoir plus')">En savoir plus →</a>
          </div>

          <div class="feat-card reveal reveal-delay-3">
            <div class="feat-num">06</div>
            <div class="feat-icon-wrap">📊</div>
            <div class="feat-title">Prédiction de rendement</div>
            <p class="feat-desc">Notre modèle XGBoost analyse vos données historiques, météo et sol pour estimer votre récolte et planifier intelligemment.</p>
            <a href="#" class="feat-link" @click.prevent="notify('📊 Prédiction — En savoir plus')">En savoir plus →</a>
          </div>
        </div>
      </div>
    </section>

    <!-- ─── PROCESS ─── -->
    <section class="process" id="process">
      <div class="s-inner">
        <div class="process-head reveal">
          <div class="about-label" style="color:#C8922A;margin-bottom:20px;display:flex;align-items:center;gap:12px;"><span style="width:28px;height:1px;background:#C8922A;display:block;"></span>Comment ça marche</div>
          <h2 class="about-h2" style="max-width:540px;">
            De l'inscription à la<br>
            <em>première récolte optimisée</em>
          </h2>
        </div>

        <div class="steps">
          <div class="step reveal">
            <div class="step-num">01</div>
            <div class="step-title">Créez votre compte</div>
            <p class="step-desc">Inscription en 3 minutes. Choisissez votre rôle — agriculteur ou fournisseur. Vérification sécurisée.</p>
          </div>
          <div class="step reveal reveal-delay-1">
            <div class="step-num">02</div>
            <div class="step-title">Ajoutez vos parcelles</div>
            <p class="step-desc">Dessinez vos champs sur la carte, indiquez vos cultures et connectez vos capteurs IoT en quelques clics.</p>
          </div>
          <div class="step reveal reveal-delay-2">
            <div class="step-num">03</div>
            <div class="step-title">Activez l'intelligence</div>
            <p class="step-desc">L'IA analyse vos données, génère des recommandations et vous alerte en temps réel via SMS ou notification.</p>
          </div>
          <div class="step reveal reveal-delay-3">
            <div class="step-num">04</div>
            <div class="step-title">Récoltez mieux</div>
            <p class="step-desc">Optimisez irrigation, fertilisation et traitements. Suivez l'évolution de votre rendement saison après saison.</p>
          </div>
        </div>
      </div>
    </section>


    <!-- ─── CTA ─── -->
    <section class="cta-section" id="cta">
      <div class="s-inner">
        <div class="about-label cta-label reveal" style="display:flex;align-items:center;justify-content:center;gap:12px;">
          <span style="width:28px;height:1px;background:#2D7A4F;display:block;"></span>
          Commencez aujourd'hui
          <span style="width:28px;height:1px;background:#2D7A4F;display:block;"></span>
        </div>
        <h2 class="cta-h2 reveal">
          Prêt à transformer<br>
          votre <em>exploitation</em> ?
        </h2>
        <div class="cta-actions reveal reveal-delay-1">
          <router-link to="/register" class="btn-primary-dark">
            <span>Créer mon compte gratuit</span>
            <span>→</span>
          </router-link>
          <a href="#" class="btn-ghost-dark" @click.prevent="notify('📞 Demande de démo envoyée')">Demander une démo</a>
        </div>
      </div>
    </section>

    <!-- ─── FOOTER ─── -->
    <footer>
      <div class="s-inner">
        <div class="footer-grid">
          <div>
            <div class="footer-brand-name">فلاح</div>
            <div class="footer-brand-ar">Fellah</div>
            <p class="footer-tagline">L'intelligence artificielle au service de l'agriculture marocaine. De Tanger à Dakhla.</p>
          </div>
          <div>
            <div class="footer-col-title">Plateforme</div>
            <ul class="footer-links">
              <li><router-link to="/dashboard">Tableau de bord</router-link></li>
              <li><a href="#">Diagnostic IA</a></li>
              <li><a href="#">Marketplace</a></li>
              <li><a href="#">Blockchain</a></li>
              <li><a href="#">Assistant vocal</a></li>
            </ul>
          </div>
          <div>
            <div class="footer-col-title">Ressources</div>
            <ul class="footer-links">
              <li><a href="#">Documentation</a></li>
              <li><a href="#">Blog agricole</a></li>
              <li><a href="#">Études de cas</a></li>
              <li><a href="#">Partenaires</a></li>
              <li><a href="#">API</a></li>
            </ul>
          </div>
          <div>
            <div class="footer-col-title">Contact</div>
            <ul class="footer-links">
              <li><a href="mailto:fellah@agri.ma">fellah@agri.ma</a></li>
              <li><a href="tel:+212535XXXXXX">+212 5 35 XX XX XX</a></li>
              <li><a href="#">Fès-Meknès, Maroc</a></li>
              <li><a href="#">LinkedIn</a></li>
              <li><a href="#">Twitter / X</a></li>
            </ul>
          </div>
        </div>

        <div class="footer-bottom">
          <div class="footer-copy">© 2026 Fellah Platform · Tous droits réservés · فلاح المغرب</div>
          <div class="footer-badge">Made in Morocco 🇲🇦</div>
        </div>
      </div>
    </footer>
  </div>
  </div>
</template>

<script setup>
import { onMounted, onUnmounted } from 'vue'

// Variables pour le curseur
let mx = 0, my = 0, rx = 0, ry = 0
let animationId = null
let mouseMoveHandler = null
let scrollHandler = null
let intersectionObserver = null
let notifyTimer = null

// Fonction notification
const notify = (msg) => {
  const n = document.getElementById('notif')
  if (!n) return
  n.textContent = msg
  n.classList.add('show')
  if (notifyTimer) clearTimeout(notifyTimer)
  notifyTimer = setTimeout(() => n.classList.remove('show'), 3000)
}

// Rendre la fonction globale pour les appels onclick (si besoin)
window.notify = notify

onMounted(() => {
  // Curseur personnalisé
  const cur = document.getElementById('cur')
  const ring = document.getElementById('ring')
  if (cur && ring) {
    mouseMoveHandler = (e) => {
      mx = e.clientX
      my = e.clientY
      cur.style.left = mx + 'px'
      cur.style.top = my + 'px'
    }
    document.addEventListener('mousemove', mouseMoveHandler)

    const lerpRing = () => {
      rx += (mx - rx) * 0.12
      ry += (my - ry) * 0.12
      ring.style.left = rx + 'px'
      ring.style.top = ry + 'px'
      animationId = requestAnimationFrame(lerpRing)
    }
    lerpRing()

    // Agrandissement du curseur au survol des éléments interactifs
    const interactiveEls = document.querySelectorAll('a, button, .feat-card, .testi-card, .step')
    const handleMouseEnter = () => {
      cur.style.width = '20px'
      cur.style.height = '20px'
      ring.style.width = '56px'
      ring.style.height = '56px'
    }
    const handleMouseLeave = () => {
      cur.style.width = '10px'
      cur.style.height = '10px'
      ring.style.width = '36px'
      ring.style.height = '36px'
    }
    interactiveEls.forEach(el => {
      el.addEventListener('mouseenter', handleMouseEnter)
      el.addEventListener('mouseleave', handleMouseLeave)
    })
    // Nettoyage ajouté plus tard
    window.cleanupCursor = () => {
      interactiveEls.forEach(el => {
        el.removeEventListener('mouseenter', handleMouseEnter)
        el.removeEventListener('mouseleave', handleMouseLeave)
      })
    }
  }

  // Scroll reveal (Intersection Observer)
  const revealElements = document.querySelectorAll('.reveal')
  if (revealElements.length) {
    intersectionObserver = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible')
        }
      })
    }, { threshold: 0.12 })
    revealElements.forEach(el => intersectionObserver.observe(el))
  }

  // Smooth scroll pour les ancres
  const anchorLinks = document.querySelectorAll('a[href^="#"]')
  anchorLinks.forEach(link => {
    link.addEventListener('click', (e) => {
      const hash = link.getAttribute('href')
      if (hash && hash !== '#') {
        const targetId = hash.slice(1)
        const targetEl = document.getElementById(targetId)
        if (targetEl) {
          e.preventDefault()
          targetEl.scrollIntoView({ behavior: 'smooth' })
        }
      }
    })
  })

  // Nav shrink on scroll
  const nav = document.querySelector('nav')
  if (nav) {
    scrollHandler = () => {
      if (window.scrollY > 60) {
        nav.style.padding = '14px 48px'
      } else {
        nav.style.padding = '22px 48px'
      }
    }
    window.addEventListener('scroll', scrollHandler)
  }
})

onUnmounted(() => {
  // Nettoyage des événements
  if (mouseMoveHandler) document.removeEventListener('mousemove', mouseMoveHandler)
  if (animationId) cancelAnimationFrame(animationId)
  if (scrollHandler) window.removeEventListener('scroll', scrollHandler)
  if (intersectionObserver) intersectionObserver.disconnect()
  if (window.cleanupCursor) window.cleanupCursor()
  if (notifyTimer) clearTimeout(notifyTimer)
  // Supprimer la fonction globale
  delete window.notify
})
</script>

<style scoped>
/* ─── RESET & VARS ─── */
.landing {
  font-family: var(--serif);
  background: var(--ink);
  color: var(--paper);
  overflow-x: hidden;
  cursor: none;
}
*, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

:root {
  --ink:     #0D0F0C;
  --paper:   #F5F0E8;
  --paper2:  #EDE8DC;
  --green:   #1A4D2E;
  --green2:  #2D7A4F;
  --green3:  #4AAE78;
  --gold:    #C8922A;
  --gold2:   #E8B84B;
  --rust:    #8B3A2A;
  --cream:   #FAF7F0;
  --muted:   #6B6458;
  --serif:   'Cormorant Garamond', Georgia, serif;
  --display: 'Syne', sans-serif;
  --mono:    'IBM Plex Mono', monospace;
}

html { scroll-behavior: smooth; }

body {
  font-family: var(--serif);
  background: var(--ink);
  color: var(--paper);
  overflow-x: hidden;
  cursor: none;
}

/* ─── CUSTOM CURSOR ─── */
.cursor {
  position: fixed;
  width: 10px; height: 10px;
  background: var(--gold2);
  border-radius: 50%;
  pointer-events: none;
  z-index: 9999;
  transform: translate(-50%, -50%);
  transition: transform 0.1s, width 0.2s, height 0.2s, background 0.2s;
  mix-blend-mode: difference;
}

.cursor-ring {
  position: fixed;
  width: 36px; height: 36px;
  border: 1px solid rgba(200,146,42,0.5);
  border-radius: 50%;
  pointer-events: none;
  z-index: 9998;
  transform: translate(-50%, -50%);
  transition: all 0.15s ease;
}

body:hover .cursor { opacity: 1; }

/* ─── SCROLLBAR ─── */
::-webkit-scrollbar { width: 3px; }
::-webkit-scrollbar-track { background: var(--ink); }
::-webkit-scrollbar-thumb { background: var(--green2); }

/* ─── NAV ─── */
nav {
  position: fixed;
  top: 0; left: 0; right: 0;
  z-index: 100;
  padding: 22px 48px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  mix-blend-mode: normal;
  padding-bottom: 20px;
}

nav::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(to bottom, rgba(13,15,12,0.9) 0%, transparent 100%);
  pointer-events: none;
}

.nav-logo {
  position: relative;
  z-index: 1;
  display: flex;
  align-items: baseline;
  gap: 12px;
}

.nav-logo-ar {
  font-family: var(--serif);
  font-size: 28px;
  font-weight: 300;
  color: #C8922A;
  letter-spacing: 2px;
}

.nav-logo-lat {
  font-family: var(--display);
  font-size: 13px;
  font-weight: 700;
  letter-spacing: 4px;
  text-transform: uppercase;
  color: rgba(245,240,232,0.5);
}

.nav-links {
  position: relative;
  z-index: 1;
  display: flex;
  align-items: center;
  gap: 40px;
  list-style: none;
  font-family: Georgia, 'Times New Roman', Times, serif;
}

.nav-links a {
  font-family: monospace;
  font-size: 11px;
  letter-spacing: 2px;
  text-transform: uppercase;
  color: rgba(245,240,232,0.55);
  text-decoration: none;
  transition: color 0.2s;
  position: relative;
  font-size: 12px;
  font-family: var(--display);
    font-weight: bold;
}

.nav-links a::after {
  content: '';
  position: absolute;
  bottom: -3px; left: 0;
  width: 0; height: 1px;
  background: #E8B84B;
  transition: width 0.3s;
 
}

.nav-links a:hover { color: #F5F0E8; }
.nav-links a:hover::after { width: 100%; }

.nav-cta {
  position: relative;
  z-index: 1;
  padding: 10px 24px;
  background: transparent;
  border: 1px solid rgba(200,146,42,0.4);
  border-radius: 2px;
  font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif, 'Times New Roman', Times, serif;
  font-size: 11px;
  letter-spacing: 2px;
  text-transform: uppercase;
  color: #E8B84B;
  cursor: none;
  transition: all 0.25s;
  text-decoration: none;
  
}

.nav-cta:hover {
  background: #C8922A;
  border-color: #C8922A;
  color: var(--ink);
}

/* ─── HERO ─── */
.hero {
  position: relative;
  min-height: 100vh;
  display: flex;
  align-items: flex-end;
  overflow: hidden;
}

/* Animated field background */
.hero-bg {
  position: absolute;
  inset: 0;
  background:
    radial-gradient(ellipse at 20% 50%, rgba(26,77,46,0.25) 0%, transparent 60%),
    radial-gradient(ellipse at 80% 30%, rgba(45,122,79,0.12) 0%, transparent 50%),
    linear-gradient(160deg, #0D0F0C 0%, #111A14 40%, #0A1A10 70%, #0D0F0C 100%);
}

/* Grain texture overlay */
.hero-grain {
  position: absolute;
  inset: 0;
  opacity: 0.06;
  background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 512 512' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.75' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
}

/* Decorative lines */
.hero-lines {
  position: absolute;
  inset: 0;
  overflow: hidden;
  pointer-events: none;
}

.hero-line {
  position: absolute;
  background: rgba(200,146,42,0.08);
  transform-origin: center;
}

.hero-line:nth-child(1) { width: 1px; height: 70%; top: 0; left: 15%; animation: lineGrow 2s 0.5s ease forwards; transform: scaleY(0); }
.hero-line:nth-child(2) { width: 1px; height: 50%; top: 20%; left: 85%; animation: lineGrow 2s 0.8s ease forwards; transform: scaleY(0); }
.hero-line:nth-child(3) { width: 40%; height: 1px; top: 35%; right: 0; animation: lineGrowH 2s 1s ease forwards; transform: scaleX(0); transform-origin: right; }

@keyframes lineGrow { to { transform: scaleY(1); } }
@keyframes lineGrowH { to { transform: scaleX(1); } }

/* Floating botanicals */
.botanical {
  position: absolute;
  opacity: 0;
  animation: floatIn 1.5s ease forwards;
}

.botanical-1 {
  top: 12%; right: 8%;
  font-size: 220px;
  animation-delay: 0.3s;
  filter: opacity(0.06) sepia(1) hue-rotate(60deg);
  user-select: none;
  transform: rotate(-15deg);
}

.botanical-2 {
  bottom: 20%; left: 5%;
  font-size: 90px;
  animation-delay: 0.8s;
  filter: opacity(0.07);
  user-select: none;
  transform: rotate(20deg);
}

@keyframes floatIn {
  from { opacity: 0; transform: translateY(20px) rotate(var(--r, 0deg)); }
  to   { opacity: 1; transform: translateY(0) rotate(var(--r, 0deg)); }
}

/* Large decorative number */
.hero-number {
  position: absolute;
  top: 50%; right: 8%;
  transform: translateY(-50%);
  font-family: var(--display);
  font-size: 380px;
  font-weight: 800;
  line-height: 1;
  color: transparent;
  -webkit-text-stroke: 1px rgba(200,146,42,0.07);
  user-select: none;
  animation: fadeNum 2s 0.2s ease forwards;
  opacity: 0;
}

@keyframes fadeNum { to { opacity: 1; } }

.hero-content {
  position: relative;
  z-index: 10;
  padding: 0 48px 52px;
  max-width: 900px;
}

.hero-eyebrow {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-bottom: 28px;
  opacity: 0;
  animation: slideUp 0.8s 0.3s ease forwards;
}

.hero-eyebrow-line {
  width: 48px; height: 1px;
  background: var(--gold);
}

.hero-eyebrow-text {
  font-family: var(--mono);
  font-size: 11px;
  letter-spacing: 3px;
  text-transform: uppercase;
  color: var(--gold2);
}

.hero-h1 {
font-family: 'Cormorant Garamond', 'Times New Roman', serif;
font-weight: 300;    /* light, très fin */
 /* ajoute une touche d’élégance supplémentaire */
  font-size: clamp(64px, 9vw, 128px);
  line-height: 0.92;
  letter-spacing: -2px;
  color: #F5F0E8;
  margin-bottom: 8px;
  opacity: 0;
  animation: slideUp 0.9s 0.5s ease forwards;
}

.hero-h1 em {
  font-style: italic;
  color: #E8B84B;
}

.hero-h1-ar {
  font-family: var(--serif);
  font-size: clamp(32px, 4.5vw, 64px);
  font-weight: 300;
  font-style: italic;
  color: rgba(200,146,42,0.4);
  letter-spacing: 4px;
  margin-bottom: 40px;
  display: block;
  opacity: 0;
  animation: slideUp 0.9s 0.65s ease forwards;
}

.hero-desc {
  font-family: var(--serif);
  font-size: 19px;
  font-weight: 300;
  line-height: 1.7;
  color: rgba(245,240,232,0.6);
  max-width: 520px;
  margin-bottom: 48px;
  opacity: 0;
  animation: slideUp 0.9s 0.8s ease forwards;
}

.hero-actions {
  display: flex;
  align-items: center;
  gap: 28px;
  opacity: 0;
  animation: slideUp 0.9s 1s ease forwards;
}

.btn-primary {
  padding: 16px 40px;
  background: #C8922A;
  color: #0D0F0C;
  font-family: var(--display);
  font-size: 13px;
  font-weight: 700;
  letter-spacing: 2px;
  text-transform: uppercase;
  text-decoration: none;
  border: none;
  border-radius: 2px;
  cursor: none;
  transition: all 0.25s;
  display: inline-flex; align-items: center; gap: 10px;
  position: relative;
  overflow: hidden;
}

.btn-primary::before {
  content: '';
  position: absolute;
  inset: 0;
  background: var(--gold2);
  transform: translateX(-101%);
  transition: transform 0.3s ease;
}

.btn-primary:hover::before { transform: translateX(0); }
.btn-primary span { position: relative; z-index: 1; }

.btn-ghost {
  font-family: var(--mono);
  font-size: 12px;
  letter-spacing: 2px;
  text-transform: uppercase;
  color: rgba(245,240,232,0.5);
  text-decoration: none;
  border-bottom: 1px solid rgba(245,240,232,0.2);
  padding-bottom: 2px;
  transition: all 0.2s;
  cursor: none;
}

.btn-ghost:hover { color: #F5F0E8; border-color: #F5F0E8; }

/* Hero stat strip */
.hero-stats {
  position: absolute;
  right: 48px; bottom: 72px;
  display: flex;
  flex-direction: column;
  gap: 24px;
  z-index: 10;
  opacity: 0;
  animation: slideUp 0.9s 1.2s ease forwards;
}

.stat-item {
  text-align: right;
  border-right: 1px solid rgba(200,146,42,0.3);
  padding-right: 20px;
}

.stat-num {
  font-family: var(--display);
  font-size: 32px;
  font-weight: 800;
  color: var(--gold2);
  line-height: 1;
}

.stat-label {
  font-family: var(--mono);
  font-size: 10px;
  letter-spacing: 1.5px;
  text-transform: uppercase;
  color: rgba(245,240,232,0.35);
  margin-top: 3px;
}

/* Hero scroll indicator */
.scroll-ind {
  position: absolute;
  bottom: 32px; left: 50%; transform: translateX(-50%);
  display: flex; flex-direction: column; align-items: center; gap: 8px;
  opacity: 0;
  animation: fadeIn 1s 1.5s ease forwards;
  z-index: 10;
}

.scroll-ind-text {
  font-family: var(--mono);
  font-size: 9px;
  letter-spacing: 3px;
  text-transform: uppercase;
  color: rgba(245,240,232,0.25);
}

.scroll-ind-line {
  width: 1px; height: 40px;
  background: linear-gradient(to bottom, rgba(200,146,42,0.5), transparent);
  animation: scrollLine 2s ease infinite;
}

@keyframes scrollLine {
  0% { transform: scaleY(0); transform-origin: top; }
  50% { transform: scaleY(1); transform-origin: top; }
  50.01% { transform-origin: bottom; }
  100% { transform: scaleY(0); transform-origin: bottom; }
}

@keyframes slideUp {
  from { opacity: 0; transform: translateY(30px); }
  to   { opacity: 1; transform: translateY(0); }
}

@keyframes fadeIn {
  to { opacity: 1; }
}

/* ─── MARQUEE STRIP ─── */
.marquee-strip {
  background: #1A4D2E;
  padding: 14px 0;
  overflow: hidden;
  border-top: 1px solid rgba(200,146,42,0.2);
  border-bottom: 1px solid rgba(200,146,42,0.2);
}

.marquee-track {
  display: flex;
  gap: 0;
  animation: marquee 30s linear infinite;
  white-space: nowrap;
  width: max-content;
}

.marquee-item {
  font-family: var(--mono);
  font-size: 13px;
  letter-spacing: 3px;
  text-transform: uppercase;
  color: rgba(245,240,232,0.6);
  padding: 0 40px;
  border-right: 1px solid rgba(245,240,232,0.15);
}

.marquee-item.accent { color: #C8922A; }

@keyframes marquee {
  from { transform: translateX(0); }
  to   { transform: translateX(-50%); }
}

/* ─── SECTIONS ─── */
section {
  position: relative;
}

.s-inner {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 48px;
}

/* ─── ABOUT SPLIT ─── */
.about {
  padding: 140px 0;
  background: #0D0F0C;
}

.about-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 80px;
  align-items: center;
}

.about-label {
  font-family: var(--mono);
  font-size: 10px;
  letter-spacing: 3px;
  text-transform: uppercase;
  color: #C8922A;
  margin-bottom: 20px;
  display: flex; align-items: center; gap: 12px;
}

.about-label::before {
  content: '';
  width: 28px; height: 1px;
  background: #C8922A;
}

.about-h2 {
  font-family: var(--serif);
  font-size: clamp(38px, 4vw, 60px);
  font-weight: 300;
  line-height: 1.1;
  letter-spacing: -1px;
  color: #F5F0E8;
  margin-bottom: 28px;
}

.about-h2 em { font-style: italic; color: #C8922A; }

.about-p {
  font-family: var(--serif);
  font-size: 17px;
  font-weight: 300;
  line-height: 1.85;
  color: rgba(245,240,232,0.55);
  margin-bottom: 16px;
}

.about-right {
  position: relative;
}

.about-card-main {
  background: #1A4D2E;
  border-radius: 4px;
  padding: 48px 40px;
  position: relative;
  overflow: hidden;
}

.about-card-main::before {
  content: '';
  position: absolute;
  top: -40px; right: -40px;
  width: 180px; height: 180px;
  border-radius: 50%;
  background: rgba(200,146,42,0.1);
}

.about-card-main::after {
  content: '🌾';
  position: absolute;
  bottom: -10px; right: 20px;
  font-size: 100px;
  opacity: 0.08;
}

.acm-num {
  font-family: var(--display);
  font-size: 80px;
  font-weight: 800;
  color: #E8B84B;
  line-height: 1;
  letter-spacing: -3px;
}

.acm-label {
  font-family: var(--serif);
  font-size: 20px;
  font-weight: 300;
  color: rgba(245,240,232,0.7);
  margin-top: 8px;
  margin-bottom: 24px;
}

.acm-bar {
  height: 2px;
  background: rgba(200,146,42,0.3);
  border-radius: 1px;
  margin-bottom: 28px;
  position: relative;
  overflow: hidden;
}

.acm-bar::after {
  content: '';
  position: absolute;
  left: 0; top: 0; bottom: 0;
  width: 68%;
  background: #C8922A(--gold);
  animation: barFill 2s 1s ease forwards;
  transform: scaleX(0);
  transform-origin: left;
}

@keyframes barFill { to { transform: scaleX(1); } }

.acm-sub {
  font-family: var(--mono);
  font-size: 10px;
  letter-spacing: 1.5px;
  color: rgba(245,240,232,0.3);
  text-transform: uppercase;
}

.about-card-float {
  position: absolute;
  bottom: -32px; left: -32px;
  background: #C8922A;
  padding: 20px 24px;
  border-radius: 3px;
  min-width: 160px;
  box-shadow: 0 20px 60px rgba(0,0,0,0.4);
}

.acf-num {
  font-family: var(--display);
  font-size: 36px;
  font-weight: 800;
  color: #0D0F0C;
  line-height: 1;
}

.acf-label {
  font-family: var(--mono);
  font-size: 9px;
  letter-spacing: 1.5px;
  text-transform: uppercase;
  color: rgba(13,15,12,0.55);
  margin-top: 4px;
}

/* ─── FEATURES ─── */
.features {
  padding: 140px 0;
  background: #F5F0E8;
}

.features-head {
  text-align: center;
  margin-bottom: 80px;
}

.features-head .about-label {
  justify-content: center;
}

.features-h2 {
  font-family: var(--serif);
  font-size: clamp(42px, 5vw, 72px);
  font-weight: 300;
  line-height: 1.05;
  letter-spacing: -2px;
  color: #0D0F0C(--ink);
  margin-top: 16px;
}

.features-h2 em { font-style: italic; color: #2D7A4F; }

.feat-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1px;
  background: rgba(13,15,12,0.08);
  border: 1px solid rgba(13,15,12,0.08);
}

.feat-card {
  background: #FAF7F0;
  padding: 48px 36px;
  position: relative;
  overflow: hidden;
  transition: background 0.3s;
  cursor: none;
}

.feat-card:hover { background:#088038; }
.feat-card:hover .feat-num { color: rgba(245,240,232,0.08); -webkit-text-stroke-color: rgba(245,240,232,0.1); }
.feat-card:hover .feat-icon-wrap { background: rgba(200,146,42,0.15); border-color: rgba(200,146,42,0.3); }
.feat-card:hover .feat-title { color: #F5F0E8; }
.feat-card:hover .feat-desc { color: rgba(245,240,232,0.55); }
.feat-card:hover .feat-link { color: #E8B84B; border-color: rgba(200,146,42,0.4); }

.feat-num {
  position: absolute;
  top: -20px; right: 20px;
  font-family: var(--display);
  font-size: 120px;
  font-weight: 800;
  color: transparent;
  -webkit-text-stroke: 1px rgba(13,15,12,0.05);
  line-height: 1;
  user-select: none;
  transition: all 0.3s;
}

.feat-icon-wrap {
  width: 52px; height: 52px;
  background: rgba(26,77,46,0.08);
  border: 1px solid rgba(26,77,46,0.15);
  border-radius: 4px;
  display: flex; align-items: center; justify-content: center;
  font-size: 22px;
  margin-bottom: 28px;
  transition: all 0.3s;
  position: relative;
  z-index: 1;
}

.feat-title {
  font-family: var(--serif);
  font-size: 24px;
  font-weight: 600;
  color: var(--ink);
  margin-bottom: 14px;
  transition: color 0.3s;
  line-height: 1.2;
  position: relative; z-index: 1;
}

.feat-desc {
  font-family: var(--serif);
  font-size: 15px;
  font-weight: 300;
  line-height: 1.75;
  color: var(--muted);
  margin-bottom: 28px;
  transition: color 0.3s;
  position: relative; z-index: 1;
}

.feat-link {
  font-family: var(--mono);
  font-size: 10px;
  letter-spacing: 2px;
  text-transform: uppercase;
  color: var(--green2);
  text-decoration: none;
  border-bottom: 1px solid rgba(45,122,79,0.3);
  padding-bottom: 2px;
  transition: all 0.3s;
  position: relative; z-index: 1;
  cursor: none;
}

/* ─── PROCESS ─── */
.process {
  padding: 140px 0;
  background: #0D0F0C;
  position: relative;
  overflow: hidden;
}

.process::before {
  content: 'الزراعة';
  position: absolute;
  top: 50%; left: 50%;
  transform: translate(-50%, -50%);
  font-family: var(--serif);
  font-size: 300px;
  font-weight: 300;
  color: transparent;
  -webkit-text-stroke: 1px rgba(26,77,46,0.08);
  user-select: none;
  white-space: nowrap;
  pointer-events: none;
}

.process-head { margin-bottom: 80px; }

.steps {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 40px;
  position: relative;
}

.steps::before {
  content: '';
  position: absolute;
  top: 28px; left: 28px; right: 28px;
  height: 1px;
  background: linear-gradient(to right, transparent, rgba(200,146,42,0.3) 10%, rgba(200,146,42,0.3) 90%, transparent);
  z-index: 0;
}

.step {
  position: relative;
  z-index: 1;
}

.step-num {
  width: 56px; height: 56px;
  border: 1px solid rgba(200,146,42,0.3);
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-family: var(--mono);
  font-size: 13px;
  color: #E8B84B;
  margin-bottom: 24px;
  background:#0D0F0C;
  position: relative;
  transition: all 0.3s;
}

.step:hover .step-num {
  background:#C8922A;
  border-color: #C8922A;
  color:#111A14;
}

.step-title {
  font-family: var(--serif);
  font-size: 20px;
  font-weight: 600;
  color: #F5F0E8;
  margin-bottom: 12px;
}

.step-desc {
  font-family: var(--serif);
  font-size: 14px;
  font-weight: 300;
  line-height: 1.75;
  color: rgba(245,240,232,0.4);
}

/* ─── TESTIMONIALS ─── */
.testi {
  padding: 140px 0;
  background: var(--green);
  position: relative;
  overflow: hidden;
}

.testi::after {
  content: '"';
  position: absolute;
  top: -40px; left: 20px;
  font-family: var(--serif);
  font-size: 400px;
  line-height: 1;
  color: rgba(245,240,232,0.04);
  user-select: none;
}

.testi-grid {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap: 28px;
}

.testi-card {
  background: rgba(245,240,232,0.05);
  border: 1px solid rgba(245,240,232,0.1);
  border-radius: 3px;
  padding: 36px 32px;
  transition: all 0.3s;
  cursor: none;
}

.testi-card:hover {
  background: rgba(245,240,232,0.1);
  border-color: rgba(200,146,42,0.3);
  transform: translateY(-4px);
}

.testi-stars {
  font-size: 14px;
  color: var(--gold2);
  margin-bottom: 20px;
  letter-spacing: 3px;
}

.testi-quote {
  font-family: var(--serif);
  font-size: 17px;
  font-style: italic;
  font-weight: 300;
  line-height: 1.75;
  color: rgba(245,240,232,0.75);
  margin-bottom: 24px;
}

.testi-author {
  display: flex;
  align-items: center;
  gap: 14px;
}

.testi-ava {
  width: 42px; height: 42px;
  border-radius: 50%;
  background: rgba(200,146,42,0.2);
  display: flex; align-items: center; justify-content: center;
  font-size: 16px;
}

.testi-name {
  font-family: var(--display);
  font-size: 14px;
  font-weight: 700;
  color: var(--paper);
}

.testi-loc {
  font-family: var(--mono);
  font-size: 10px;
  letter-spacing: 1px;
  color: rgba(245,240,232,0.35);
  margin-top: 2px;
}

/* ─── CTA SECTION ─── */
.cta-section {
  padding: 160px 0;
  background: #F5F0E8;
  text-align: center;
  position: relative;
  overflow: hidden;
}

.cta-section::before {
  content: '';
  position: absolute;
  inset: 0;
  background:
    radial-gradient(ellipse at 50% 50%, rgba(26,77,46,0.06) 0%, transparent 70%);
}

.cta-label { justify-content: center; color: #2D7A4F; }
.cta-label::before { background: #2D7A4F; }

.cta-h2 {
  font-family: var(--serif);
  font-size: clamp(48px, 6vw, 88px);
  font-weight: 300;
  line-height: 1.0;
  letter-spacing: -2px;
  color:#0D0F0C;
  margin: 20px 0 36px;
  position: relative;
}

.cta-h2 em { font-style: italic; color: #2D7A4F; }

.cta-actions {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 24px;
  position: relative;
}

.btn-primary-dark {
  padding: 18px 48px;
  background: #0D0F0C;
  color: #F5F0E8;
  font-family: var(--display);
  font-size: 13px;
  font-weight: 700;
  letter-spacing: 2px;
  text-transform: uppercase;
  text-decoration: none;
  border-radius: 2px;
  cursor: none;
  transition: all 0.25s;
  display: inline-flex; align-items: center; gap: 10px;
  position: relative;
  overflow: hidden;
}

.btn-primary-dark::before {
  content: '';
  position: absolute;
  inset: 0;
  background: #236941;
  transform: translateY(101%);
  transition: transform 0.3s ease;
}

.btn-primary-dark:hover::before { transform: translateY(0); }
.btn-primary-dark span { position: relative; z-index: 1; }

.btn-ghost-dark {
  font-family: var(--mono);
  font-size: 11px;
  letter-spacing: 2px;
  text-transform: uppercase;
  color:#6B6458;
  text-decoration: none;
  border-bottom: 1px solid rgba(107,100,88,0.3);
  padding-bottom: 2px;
  transition: all 0.2s;
  cursor: none;
}

.btn-ghost-dark:hover { color:#0D0F0C; border-color:#0D0F0C }

/* ─── FOOTER ─── */
footer {
  background:#0D0F0C;
  padding: 80px 0 40px;
  border-top: 1px solid rgba(200,146,42,0.1);
}

.footer-grid {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr 1fr;
  gap: 60px;
  margin-bottom: 60px;
}

.footer-brand-name {
  font-family: var(--serif);
  font-size: 38px;
  font-weight: 300;
  color:#E8B84B;
  margin-bottom: 4px;
}

.footer-brand-ar {
  font-family: var(--serif);
  font-size: 20px;
  font-weight: 300;
  font-style: italic;
  color: rgba(200,146,42,0.4);
  letter-spacing: 3px;
  margin-bottom: 16px;
}

.footer-tagline {
  font-family: var(--serif);
  font-size: 14px;
  font-weight: 300;
  line-height: 1.7;
  color: rgba(245,240,232,0.35);
  max-width: 260px;
}

.footer-col-title {
  font-family: var(--mono);
  font-size: 10px;
  letter-spacing: 2.5px;
  text-transform: uppercase;
  color: rgba(245,240,232,0.3);
  margin-bottom: 20px;
}

.footer-links { list-style: none; display: flex; flex-direction: column; gap: 10px; }

.footer-links a {
  font-family: var(--serif);
  font-size: 15px;
  font-weight: 300;
  color: rgba(245,240,232,0.45);
  text-decoration: none;
  transition: color 0.2s;
  cursor: none;
}

.footer-links a:hover { color:#E8B84B; }

.footer-bottom {
  border-top: 1px solid rgba(245,240,232,0.06);
  padding-top: 32px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.footer-copy {
  font-family: var(--mono);
  font-size: 10px;
  letter-spacing: 1px;
  color: rgba(245,240,232,0.2);
}

.footer-badge {
  font-family: var(--mono);
  font-size: 10px;
  letter-spacing: 1.5px;
  color: rgba(200,146,42,0.4);
  border: 1px solid rgba(200,146,42,0.15);
  padding: 5px 12px;
  border-radius: 20px;
}

/* ─── SCROLL REVEAL ─── */
.reveal {
  opacity: 0;
  transform: translateY(40px);
  transition: opacity 0.8s ease, transform 0.8s ease;
}

.reveal.visible {
  opacity: 1;
  transform: translateY(0);
}

.reveal-delay-1 { transition-delay: 0.1s; }
.reveal-delay-2 { transition-delay: 0.2s; }
.reveal-delay-3 { transition-delay: 0.3s; }
.reveal-delay-4 { transition-delay: 0.4s; }

/* ─── NOTIFICATION TOAST ─── */
.notif {
  position: fixed;
  bottom: 32px; left: 50%; transform: translateX(-50%) translateY(80px);
  background: var(--green);
  border: 1px solid rgba(200,146,42,0.3);
  color: var(--paper);
  padding: 14px 28px;
  border-radius: 2px;
  font-family: var(--mono);
  font-size: 11px;
  letter-spacing: 1.5px;
  opacity: 0;
  transition: all 0.4s cubic-bezier(0.34,1.56,0.64,1);
  z-index: 500;
  white-space: nowrap;
}

.notif.show {
  opacity: 1;
  transform: translateX(-50%) translateY(0);
}
</style>