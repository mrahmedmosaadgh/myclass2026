<template>
  <div class="menu-about">
    <h3 class="section-title">About</h3>
    <p class="section-desc">Schedule App V5 — offline-first, view-only main app, all settings from menu.</p>

    <div class="about-card">
      <div class="app-logo">📅</div>
      <h4 class="app-name">Schedule App V5</h4>
      <p class="app-tagline">Offline-First • View-Only • Menu-Driven</p>
      <div class="app-version">
        <span class="version-label">Version</span>
        <span class="version-number">5.0.0</span>
      </div>
    </div>

    <div class="features-section">
      <h4 class="sub-section-title">Features</h4>
      <ul class="features-list">
        <li>📱 Mobile-optimized interface</li>
        <li>🔄 Multiple view modes (Card, Table, List, School)</li>
        <li>⏰ Stage/day timing hierarchy</li>
        <li>📁 Full data import/export</li>
        <li>☁️ Cloud sync with local-wins rule</li>
        <li>📦 IndexedDB-only storage</li>
        <li>🔔 Period change notifications</li>
        <li>📲 PWA installable</li>
        <li>⚡ Full offline functionality</li>
      </ul>
    </div>

    <div class="tech-section">
      <h4 class="sub-section-title">Technology</h4>
      <div class="tech-grid">
        <div class="tech-item">
          <span class="tech-label">Frontend</span>
          <span class="tech-value">Vue 3 + Composition API</span>
        </div>
        <div class="tech-item">
          <span class="tech-label">Backend</span>
          <span class="tech-value">Laravel 10 + PHP 8.2</span>
        </div>
        <div class="tech-item">
          <span class="tech-label">Storage</span>
          <span class="tech-value">IndexedDB (browser)</span>
        </div>
        <div class="tech-item">
          <span class="tech-label">Sync</span>
          <span class="tech-value">REST API + local-wins</span>
        </div>
      </div>
    </div>

    <div class="links-section">
      <h4 class="sub-section-title">Links</h4>
      <div class="links-grid">
        <a href="/my-fly-schedule-app/v5" class="link-item" target="_blank">
          <span class="link-icon">🌐</span>
          <span class="link-label">Live App</span>
        </a>
        <a href="/api/v5/health" class="link-item" target="_blank">
          <span class="link-icon">💓</span>
          <span class="link-label">Health API</span>
        </a>
        <a href="/my-fly-schedule-app/v5/manifest.webmanifest" class="link-item" target="_blank">
          <span class="link-icon">📱</span>
          <span class="link-label">PWA Manifest</span>
        </a>
        <a href="/my-fly-schedule-app/v5-sw.js" class="link-item" target="_blank">
          <span class="link-icon">⚙️</span>
          <span class="link-label">Service Worker</span>
        </a>
      </div>
    </div>

    <div class="credits-section">
      <h4 class="sub-section-title">Credits</h4>
      <div class="credits-grid">
        <div class="credit-item">
          <span class="credit-role">Development</span>
          <span class="credit-name">Ahmed Mosaad</span>
        </div>
        <div class="credit-item">
          <span class="credit-role">Architecture</span>
          <span class="credit-name">Laravel + Vue.js + Inertia.js</span>
        </div>
        <div class="credit-item">
          <span class="credit-role">Deployment</span>
          <span class="credit-name">Hostinger + Rsync</span>
        </div>
      </div>
    </div>

    <div class="privacy-section">
      <h4 class="sub-section-title">Privacy & Data</h4>
      <div class="privacy-info">
        <p>• All data stored locally in IndexedDB</p>
        <p>• Cloud sync is optional and uses anonymous user IDs</p>
        <p>• No analytics or tracking</p>
        <p>• Export your data anytime</p>
      </div>
    </div>

    <div class="footer-info">
      <p class="footer-text">Schedule App V5 • Built with ❤️ for educators</p>
      <p class="footer-date">{{ buildDate }}</p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const buildDate = computed(() => {
  return new Date().toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
});
</script>

<style scoped>
.section-title {
  font-size: 0.8rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 1px;
  color: #64748b;
  margin: 0 0 0.25rem 0;
}

.section-desc {
  color: #475569;
  font-size: 0.8rem;
  margin: 0 0 1.5rem 0;
}

.sub-section-title {
  font-size: 0.75rem;
  font-weight: 600;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin: 0 0 0.75rem 0;
}

.about-card {
  background: rgba(255, 255, 255, 0.03);
  border-radius: 12px;
  padding: 1.5rem;
  text-align: center;
  margin-bottom: 2rem;
}

.app-logo {
  font-size: 3rem;
  margin-bottom: 0.5rem;
}

.app-name {
  font-size: 1.25rem;
  font-weight: 700;
  margin: 0 0 0.25rem 0;
  color: #f1f5f9;
}

.app-tagline {
  font-size: 0.8rem;
  color: #94a3b8;
  margin: 0 0 1rem 0;
}

.app-version {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.75rem;
}

.version-label {
  color: #64748b;
}

.version-number {
  background: rgba(59, 130, 246, 0.2);
  color: #60a5fa;
  padding: 0.15rem 0.4rem;
  border-radius: 6px;
  font-family: monospace;
}

.features-section,
.tech-section,
.links-section,
.credits-section,
.privacy-section {
  margin-bottom: 2rem;
}

.features-list {
  list-style: none;
  padding: 0;
  margin: 0;
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 0.5rem;
}

.features-list li {
  font-size: 0.8rem;
  color: #e2e8f0;
  padding: 0.25rem 0;
}

.tech-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
  gap: 0.75rem;
}

.tech-item {
  display: flex;
  flex-direction: column;
  gap: 0.15rem;
}

.tech-label {
  font-size: 0.7rem;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.tech-value {
  font-size: 0.75rem;
  color: #e2e8f0;
  font-family: monospace;
}

.links-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
  gap: 0.5rem;
}

.link-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem;
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.03);
  color: #e2e8f0;
  text-decoration: none;
  font-size: 0.75rem;
  transition: all 0.2s;
}

.link-item:hover {
  background: rgba(59, 130, 246, 0.15);
  color: #60a5fa;
}

.link-icon { font-size: 1rem; }
.link-label { font-weight: 600; }

.credits-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
  gap: 0.75rem;
}

.credit-item {
  display: flex;
  flex-direction: column;
  gap: 0.15rem;
}

.credit-role {
  font-size: 0.7rem;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.credit-name {
  font-size: 0.75rem;
  color: #e2e8f0;
}

.privacy-info {
  background: rgba(255, 255, 255, 0.03);
  border-radius: 8px;
  padding: 0.75rem;
}

.privacy-info p {
  margin: 0.25rem 0;
  font-size: 0.75rem;
  color: #94a3b8;
}

.footer-info {
  text-align: center;
  padding-top: 1rem;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
}

.footer-text {
  font-size: 0.75rem;
  color: #64748b;
  margin: 0 0 0.25rem 0;
}

.footer-date {
  font-size: 0.7rem;
  color: #475569;
  margin: 0;
}
</style>
