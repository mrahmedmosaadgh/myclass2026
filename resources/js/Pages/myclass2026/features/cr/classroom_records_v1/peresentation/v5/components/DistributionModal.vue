<template>
  <teleport to="body">
    <div v-if="uiStore.showDistributionModal" class="modal-overlay" @click="closeModal">
      <div class="modal-content" @click.stop>
        <!-- Header -->
        <div class="modal-header">
          <h2>📤 Distribution Settings</h2>
          <button @click="closeModal" class="close-btn">✕</button>
        </div>

        <!-- Content -->
        <div class="modal-body">
          <!-- Export Options -->
          <div class="section">
            <h3>Export Presentation</h3>
            <div class="export-options">
              <button @click="exportAsJSON" class="export-btn json">
                📄 Export as JSON
                <small>Full presentation data</small>
              </button>
              <button @click="copyShareLink" class="export-btn link">
                🔗 Copy Share Link
                <small>Create shareable link</small>
              </button>
              <button @click="downloadAsPackage" class="export-btn package">
                📦 Download Package
                <small>Offline-ready package</small>
              </button>
            </div>
          </div>

          <!-- Share Settings -->
          <div class="section">
            <h3>Share Settings</h3>
            <div class="share-settings">
              <div class="setting-item">
                <label>
                  <input type="checkbox" v-model="settings.allowComments" />
                  Allow comments
                </label>
              </div>
              <div class="setting-item">
                <label>
                  <input type="checkbox" v-model="settings.allowDownload" />
                  Allow download
                </label>
              </div>
              <div class="setting-item">
                <label>
                  <input type="checkbox" v-model="settings.requirePassword" />
                  Require password
                </label>
                <input 
                  v-if="settings.requirePassword" 
                  v-model="settings.password" 
                  type="password" 
                  placeholder="Enter password"
                  class="password-input"
                />
              </div>
              <div class="setting-item">
                <label>
                  Expiration
                </label>
                <select v-model="settings.expiration" class="expiration-select">
                  <option value="never">Never</option>
                  <option value="1day">1 day</option>
                  <option value="7days">7 days</option>
                  <option value="30days">30 days</option>
                </select>
              </div>
            </div>
          </div>

          <!-- Preview Link -->
          <div v-if="shareLink" class="section">
            <h3>Share Link</h3>
            <div class="share-link-container">
              <input :value="shareLink" readonly class="share-link-input" />
              <button @click="copyShareLink" class="copy-btn">📋 Copy</button>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="modal-footer">
          <button @click="closeModal" class="btn-secondary">Cancel</button>
          <button @click="saveSettings" class="btn-primary">Save Settings</button>
        </div>
      </div>
    </div>
  </teleport>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { usePresentationStore } from '../stores/presentationStore';
import { useUIStore } from '../stores/uiStore';

const presentation = usePresentationStore();
const uiStore = useUIStore();

const shareLink = ref('');
const settings = reactive({
  allowComments: false,
  allowDownload: true,
  requirePassword: false,
  password: '',
  expiration: 'never'
});

function closeModal() {
  uiStore.showDistributionModal = false;
}

async function exportAsJSON() {
  try {
    await presentation.exportCurrentPresentation();
    showNotification('Presentation exported successfully!', 'success');
  } catch (error) {
    showNotification('Export failed: ' + error.message, 'error');
  }
}

function copyShareLink() {
  // Generate share link (mock implementation)
  const presentationId = Date.now().toString(36);
  shareLink.value = `${window.location.origin}/presentation/shared/${presentationId}`;
  
  navigator.clipboard.writeText(shareLink.value).then(() => {
    showNotification('Share link copied to clipboard!', 'success');
  }).catch(() => {
    showNotification('Failed to copy link', 'error');
  });
}

function downloadAsPackage() {
  // Create offline package (mock implementation)
  const packageData = {
    title: presentation.title,
    slides: presentation.slides,
    version: '1.0',
    timestamp: new Date().toISOString()
  };
  
  const blob = new Blob([JSON.stringify(packageData, null, 2)], {
    type: 'application/json'
  });
  
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = `${presentation.title.replace(/\s+/g, '-').toLowerCase()}-package.json`;
  a.click();
  
  URL.revokeObjectURL(url);
  showNotification('Package downloaded successfully!', 'success');
}

function saveSettings() {
  // Save distribution settings (mock implementation)
  console.log('Saving distribution settings:', settings);
  showNotification('Distribution settings saved!', 'success');
  closeModal();
}

function showNotification(message, type = 'info') {
  // Simple notification system (could be enhanced)
  const notification = document.createElement('div');
  notification.className = `notification ${type}`;
  notification.textContent = message;
  notification.style.cssText = `
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 12px 20px;
    background: ${type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#3b82f6'};
    color: white;
    border-radius: 6px;
    z-index: 10000;
    animation: slideIn 0.3s ease-out;
  `;
  
  document.body.appendChild(notification);
  
  setTimeout(() => {
    notification.remove();
  }, 3000);
}
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10000;
  padding: 20px;
}

.modal-content {
  background: white;
  border-radius: 12px;
  max-width: 600px;
  width: 100%;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 24px;
  border-bottom: 1px solid #e5e7eb;
}

.modal-header h2 {
  margin: 0;
  font-size: 1.5rem;
  font-weight: 700;
  color: #111827;
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #6b7280;
  padding: 4px;
  border-radius: 4px;
  transition: all 0.2s;
}

.close-btn:hover {
  background: #f3f4f6;
  color: #111827;
}

.modal-body {
  padding: 24px;
}

.section {
  margin-bottom: 32px;
}

.section:last-child {
  margin-bottom: 0;
}

.section h3 {
  margin: 0 0 16px;
  font-size: 1.125rem;
  font-weight: 600;
  color: #111827;
}

.export-options {
  display: grid;
  gap: 12px;
}

.export-btn {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 4px;
  padding: 16px;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  background: white;
  cursor: pointer;
  transition: all 0.2s;
  text-align: left;
}

.export-btn:hover {
  border-color: #3b82f6;
  background: #f8fafc;
}

.export-btn.json {
  border-color: #10b981;
}

.export-btn.json:hover {
  background: #f0fdf4;
}

.export-btn.link {
  border-color: #3b82f6;
}

.export-btn.link:hover {
  background: #eff6ff;
}

.export-btn.package {
  border-color: #8b5cf6;
}

.export-btn.package:hover {
  background: #faf5ff;
}

.export-btn small {
  color: #6b7280;
  font-size: 0.875rem;
}

.share-settings {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.setting-item {
  display: flex;
  align-items: center;
  gap: 12px;
}

.setting-item label {
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 500;
  color: #374151;
  min-width: 120px;
}

.setting-item input[type="checkbox"] {
  width: 18px;
  height: 18px;
}

.password-input {
  padding: 8px 12px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 0.875rem;
  outline: none;
}

.password-input:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.expiration-select {
  padding: 8px 12px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 0.875rem;
  outline: none;
  background: white;
}

.share-link-container {
  display: flex;
  gap: 8px;
}

.share-link-input {
  flex: 1;
  padding: 12px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 0.875rem;
  background: #f9fafb;
  font-family: monospace;
}

.copy-btn {
  padding: 12px 16px;
  background: #3b82f6;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.2s;
}

.copy-btn:hover {
  background: #2563eb;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  padding: 24px;
  border-top: 1px solid #e5e7eb;
}

.btn-secondary {
  padding: 10px 20px;
  background: #f3f4f6;
  color: #374151;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.2s;
}

.btn-secondary:hover {
  background: #e5e7eb;
}

.btn-primary {
  padding: 10px 20px;
  background: #3b82f6;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.2s;
}

.btn-primary:hover {
  background: #2563eb;
}

@keyframes slideIn {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}
</style>
