<template>
  <Teleport to="body">
    <Transition name="fullscreen">
      <div v-if="open" class="fullscreen-overlay" @click.self="handleOverlayClick">
        <div class="fullscreen-viewer">
          <div class="viewer-header">
            <div class="header-left">
              <h3 class="viewer-title">Presentation Viewer</h3>
              <div v-if="url" class="url-display">{{ displayUrl }}</div>
            </div>
            <div class="header-actions">
              <button 
                v-if="url" 
                @click="openInNewTab" 
                class="action-btn"
                title="Open in new tab"
              >
                ↗
              </button>
              <button @click="$emit('close')" class="close-btn" title="Close">✕</button>
            </div>
          </div>

          <div class="viewer-content">
            <div v-if="!url" class="no-content">
              <div class="no-content-icon">📄</div>
              <h4>No Presentation URL</h4>
              <p>No presentation link has been provided for this weekly plan entry.</p>
            </div>
            
            <div v-else-if="isLoading" class="loading">
              <div class="loading-spinner"></div>
              <p>Loading presentation...</p>
            </div>
            
            <div v-else-if="hasError" class="error">
              <div class="error-icon">⚠️</div>
              <h4>Failed to Load Presentation</h4>
              <p>{{ errorMessage }}</p>
              <div class="error-actions">
                <button @click="retryLoad" class="retry-btn">Retry</button>
                <button @click="openInNewTab" class="external-btn">Open in Browser</button>
              </div>
            </div>
            
            <iframe
              v-else
              :src="iframeSrc"
              class="presentation-frame"
              :title="'Presentation: ' + displayUrl"
              @load="handleFrameLoad"
              @error="handleFrameError"
              referrerpolicy="no-referrer-when-downgrade"
              allowfullscreen
            ></iframe>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch } from 'vue';

const props = defineProps({
  open: Boolean,
  url: String
});

defineEmits(['close']);

const isLoading = ref(false);
const hasError = ref(false);
const errorMessage = ref('');

const displayUrl = computed(() => {
  if (!props.url) return '';
  // Try to extract a cleaner display URL
  try {
    const urlObj = new URL(props.url);
    return urlObj.hostname + urlObj.pathname;
  } catch {
    return props.url;
  }
});

const iframeSrc = computed(() => {
  if (!props.url) return '';
  
  // For some presentation services, we might need to add embed parameters
  try {
    const urlObj = new URL(props.url);
    
    // Google Slides embed
    if (urlObj.hostname.includes('docs.google.com')) {
      if (urlObj.pathname.includes('/presentation/')) {
        // Convert to embed format
        const embedUrl = props.url.replace('/presentation/', '/embed/');
        return embedUrl;
      }
    }
    
    // Microsoft PowerPoint Online
    if (urlObj.hostname.includes('office.com') || urlObj.hostname.includes('live.com')) {
      // Add embed parameters if needed
      const separator = urlObj.search ? '&' : '?';
      return props.url + separator + 'embed=1';
    }
    
    return props.url;
  } catch {
    return props.url;
  }
});

const handleOverlayClick = (event) => {
  // Only close if clicking the overlay, not the content
  if (event.target === event.currentTarget) {
    emit('close');
  }
};

const openInNewTab = () => {
  if (props.url) {
    window.open(props.url, '_blank', 'noopener,noreferrer');
  }
};

const retryLoad = () => {
  hasError.value = false;
  errorMessage.value = '';
  isLoading.value = true;
  
  // Force iframe reload by changing src
  const iframe = document.querySelector('.presentation-frame');
  if (iframe) {
    iframe.src = iframeSrc.value;
  }
};

const handleFrameLoad = () => {
  isLoading.value = false;
  hasError.value = false;
  errorMessage.value = '';
};

const handleFrameError = () => {
  isLoading.value = false;
  hasError.value = true;
  errorMessage.value = 'Unable to load the presentation. The URL may be invalid or blocked.';
};

// Watch for URL changes
watch(() => props.url, (newUrl) => {
  if (newUrl && props.open) {
    isLoading.value = true;
    hasError.value = false;
    errorMessage.value = '';
  } else {
    isLoading.value = false;
    hasError.value = false;
    errorMessage.value = '';
  }
});

// Initialize loading state when opening
watch(() => props.open, (isOpen) => {
  if (isOpen && props.url) {
    isLoading.value = true;
    hasError.value = false;
    errorMessage.value = '';
  } else {
    isLoading.value = false;
    hasError.value = false;
    errorMessage.value = '';
  }
});
</script>

<style scoped>
.fullscreen-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.9);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2000;
}

.fullscreen-viewer {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  background: #000;
}

.viewer-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.5rem;
  background: rgba(0, 0, 0, 0.8);
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  color: white;
}

.header-left {
  flex: 1;
  min-width: 0;
}

.viewer-title {
  margin: 0 0 0.25rem 0;
  font-size: 1.125rem;
  font-weight: 600;
}

.url-display {
  font-size: 0.875rem;
  color: #ccc;
  font-family: monospace;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.header-actions {
  display: flex;
  gap: 0.5rem;
  align-items: center;
}

.action-btn, .close-btn {
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: white;
  padding: 0.5rem;
  border-radius: 6px;
  cursor: pointer;
  font-size: 1rem;
  line-height: 1;
  transition: background-color 0.2s;
}

.action-btn:hover, .close-btn:hover {
  background: rgba(255, 255, 255, 0.2);
}

.close-btn {
  font-size: 1.25rem;
  padding: 0.375rem 0.5rem;
}

.viewer-content {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  overflow: hidden;
}

.presentation-frame {
  width: 100%;
  height: 100%;
  border: none;
  background: white;
}

.no-content, .loading, .error {
  text-align: center;
  color: white;
  padding: 2rem;
}

.no-content-icon, .error-icon {
  font-size: 4rem;
  margin-bottom: 1rem;
}

.no-content h4, .error h4 {
  margin: 0 0 0.5rem 0;
  font-size: 1.25rem;
  font-weight: 600;
}

.no-content p, .error p, .loading p {
  margin: 0;
  font-size: 1rem;
  color: #ccc;
}

.loading-spinner {
  width: 40px;
  height: 40px;
  border: 3px solid rgba(255, 255, 255, 0.2);
  border-top: 3px solid white;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 1rem auto;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.error-actions {
  margin-top: 1.5rem;
  display: flex;
  gap: 1rem;
  justify-content: center;
}

.retry-btn, .external-btn {
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: white;
  padding: 0.625rem 1.25rem;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.875rem;
  transition: background-color 0.2s;
}

.retry-btn:hover, .external-btn:hover {
  background: rgba(255, 255, 255, 0.2);
}

.external-btn {
  background: rgba(0, 123, 255, 0.3);
  border-color: rgba(0, 123, 255, 0.5);
}

.external-btn:hover {
  background: rgba(0, 123, 255, 0.5);
}

/* Transitions */
.fullscreen-enter-active,
.fullscreen-leave-active {
  transition: opacity 0.3s ease;
}

.fullscreen-enter-from,
.fullscreen-leave-to {
  opacity: 0;
}

/* Responsive */
@media (max-width: 768px) {
  .viewer-header {
    padding: 0.75rem 1rem;
  }
  
  .viewer-title {
    font-size: 1rem;
  }
  
  .url-display {
    font-size: 0.75rem;
  }
  
  .no-content-icon, .error-icon {
    font-size: 3rem;
  }
  
  .error-actions {
    flex-direction: column;
    gap: 0.75rem;
  }
  
  .retry-btn, .external-btn {
    width: 100%;
  }
}
</style>
