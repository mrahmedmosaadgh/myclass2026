<template>
  <div class="test-v3-container">
    <h1>🧪 Schedule App V3 Test</h1>
    
    <div class="test-section">
      <h2>Route Testing</h2>
      <div class="test-links">
        <a href="/my-fly-schedule-app/v3" target="_blank" class="test-link">
          📱 Main App V3
        </a>
        <a href="/my-fly-schedule-app/v3/manifest.webmanifest" target="_blank" class="test-link">
          📋 Manifest
        </a>
        <a href="/my-fly-schedule-app/v3/icon.svg" target="_blank" class="test-link">
          🎨 Icon
        </a>
        <a href="/my-fly-schedule-app/v3/sw.js" target="_blank" class="test-link">
          ⚙️ Service Worker
        </a>
      </div>
    </div>

    <div class="test-section">
      <h2>Component Testing</h2>
      <div class="component-test">
        <h3>StandaloneScheduleAppV3 Component</h3>
        <StandaloneScheduleAppV3 />
      </div>
    </div>

    <div class="test-section">
      <h2>PWA Features Test</h2>
      <div class="pwa-tests">
        <button @click="testServiceWorker" class="test-btn">
          🔄 Test Service Worker
        </button>
        <button @click="testManifest" class="test-btn">
          📋 Test Manifest
        </button>
        <button @click="testInstallation" class="test-btn">
          📱 Test Installation
        </button>
        <button @click="testOffline" class="test-btn">
          🔴 Test Offline Mode
        </button>
      </div>
    </div>

    <div class="test-results">
      <h2>Test Results</h2>
      <div v-for="result in testResults" :key="result.id" class="test-result" :class="result.status">
        <span class="result-icon">{{ getResultIcon(result.status) }}</span>
        <span class="result-text">{{ result.message }}</span>
        <span class="result-time">{{ result.timestamp }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import StandaloneScheduleAppV3 from './StandaloneScheduleAppV3.vue';

const testResults = ref([]);

const addTestResult = (message, status = 'success') => {
  testResults.value.unshift({
    id: Date.now(),
    message,
    status,
    timestamp: new Date().toLocaleTimeString()
  });
};

const getResultIcon = (status) => {
  const icons = {
    success: '✅',
    error: '❌',
    warning: '⚠️',
    info: 'ℹ️'
  };
  return icons[status] || '❓';
};

const testServiceWorker = async () => {
  try {
    if ('serviceWorker' in navigator) {
      const registration = await navigator.serviceWorker.getRegistration('/my-fly-schedule-app/v3/sw.js');
      if (registration) {
        addTestResult('Service Worker registered and active', 'success');
      } else {
        addTestResult('Service Worker not registered', 'warning');
      }
    } else {
      addTestResult('Service Worker not supported', 'error');
    }
  } catch (error) {
    addTestResult(`Service Worker test failed: ${error.message}`, 'error');
  }
};

const testManifest = async () => {
  try {
    const response = await fetch('/my-fly-schedule-app/v3/manifest.webmanifest');
    if (response.ok) {
      const manifest = await response.json();
      addTestResult(`Manifest loaded: ${manifest.name}`, 'success');
    } else {
      addTestResult(`Manifest failed: ${response.status}`, 'error');
    }
  } catch (error) {
    addTestResult(`Manifest test failed: ${error.message}`, 'error');
  }
};

const testInstallation = () => {
  if ('beforeinstallprompt' in window) {
    addTestResult('Installation prompt supported', 'success');
  } else {
    addTestResult('Installation prompt not supported', 'warning');
  }
  
  if (window.matchMedia('(display-mode: standalone)').matches) {
    addTestResult('App running in standalone mode', 'success');
  } else {
    addTestResult('App running in browser mode', 'info');
  }
};

const testOffline = () => {
  if (navigator.onLine) {
    addTestResult('Currently online - try disconnecting to test offline mode', 'info');
  } else {
    addTestResult('Currently offline - app should work normally', 'success');
  }
  
  // Test localStorage
  try {
    localStorage.setItem('test-v3', 'working');
    const value = localStorage.getItem('test-v3');
    localStorage.removeItem('test-v3');
    
    if (value === 'working') {
      addTestResult('LocalStorage working correctly', 'success');
    } else {
      addTestResult('LocalStorage not working', 'error');
    }
  } catch (error) {
    addTestResult(`LocalStorage test failed: ${error.message}`, 'error');
  }
};
</script>

<style scoped>
.test-v3-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.test-section {
  margin-bottom: 3rem;
  padding: 2rem;
  background: #f8fafc;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
}

.test-section h2 {
  margin: 0 0 1.5rem 0;
  color: #1e293b;
  font-size: 1.5rem;
  font-weight: 600;
}

.test-links {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
}

.test-link {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 1rem;
  background: white;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  text-decoration: none;
  color: #374151;
  font-weight: 500;
  transition: all 0.3s ease;
}

.test-link:hover {
  background: #3b82f6;
  color: white;
  border-color: #3b82f6;
  transform: translateY(-2px);
}

.component-test {
  background: white;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  padding: 1rem;
  min-height: 200px;
}

.pwa-tests {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
}

.test-btn {
  padding: 1rem;
  background: #3b82f6;
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.test-btn:hover {
  background: #2563eb;
  transform: translateY(-2px);
}

.test-results {
  background: white;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  padding: 1rem;
  max-height: 400px;
  overflow-y: auto;
}

.test-results h2 {
  margin: 0 0 1rem 0;
  color: #1e293b;
  font-size: 1.25rem;
  font-weight: 600;
}

.test-result {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem;
  border-radius: 6px;
  margin-bottom: 0.5rem;
  font-size: 0.875rem;
}

.test-result.success {
  background: #dcfce7;
  color: #166534;
}

.test-result.error {
  background: #fef2f2;
  color: #dc2626;
}

.test-result.warning {
  background: #fef3c7;
  color: #d97706;
}

.test-result.info {
  background: #eff6ff;
  color: #2563eb;
}

.result-icon {
  font-size: 1rem;
  flex-shrink: 0;
}

.result-text {
  flex: 1;
  font-weight: 500;
}

.result-time {
  font-size: 0.75rem;
  opacity: 0.7;
}

@media (max-width: 768px) {
  .test-v3-container {
    padding: 1rem;
  }
  
  .test-section {
    padding: 1rem;
  }
  
  .test-links,
  .pwa-tests {
    grid-template-columns: 1fr;
  }
}
</style>
