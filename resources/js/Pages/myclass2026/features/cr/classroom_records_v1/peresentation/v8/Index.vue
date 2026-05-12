<script setup>
import { onMounted, onUnmounted, ref } from 'vue'
import { useQuasar } from 'quasar'
import { usePresentationStore } from './stores/presentationStore.js'
import { useUIStore } from './stores/uiStore.js'
import { useClipboardStore } from './stores/clipboardStore.js'
import { usePaste } from './composables/usePaste.js'
import EditorCanvas from './components/EditorCanvas.vue'
import SlideCanvasReadonly from './components/SlideCanvasReadonly.vue'
import PresentModeController from './components/PresentModeController.vue'
import Toolbar from './components/Toolbar.vue'
import SlideNavigationBar from './components/SlideNavigationBar.vue'
import ElementContextMenu from './components/ElementContextMenu.vue'

const $q = useQuasar()
const presentation = usePresentationStore()
const ui = useUIStore()
const clipboard = useClipboardStore()
const { handlePaste } = usePaste()

// Slide context menu state
const slideContextMenu = ref({
  show: false,
  x: 0,
  y: 0
})

function handlePageSelect(idx) {
  presentation.selectSlide(idx)
  ui.isPagesView = false
}

function handleSlideContextMenu(e) {
  if (!ui.isEditMode) return
  e.preventDefault()
  
  slideContextMenu.value = {
    show: true,
    x: e.clientX,
    y: e.clientY
  }
}

function closeSlideContextMenu() {
  slideContextMenu.value.show = false
}

function toggleFullscreen() {
  if (!document.fullscreenElement) {
    document.documentElement.requestFullscreen().catch(() => {})
  } else {
    document.exitFullscreen().catch(() => {})
  }
}

function promptCustomZoom() {
  $q.dialog({
    title: 'Set Zoom Level',
    message: 'Enter zoom percentage (25 - 200)',
    prompt: {
      model: String(ui.zoomLevel),
      type: 'number',
      isValid: (val) => {
        const n = Number(val)
        return Number.isFinite(n) && n >= 25 && n <= 200
      }
    },
    cancel: true,
    persistent: false,
    style: 'border-radius: 12px'
  }).onOk((val) => {
    const n = Number(val)
    if (Number.isFinite(n)) ui.setZoom(n)
  })
}

// Keyboard shortcuts
function handleKeydown(e) {
  if (['INPUT', 'TEXTAREA'].includes(e.target.tagName)) return

  const key = e.key.toLowerCase()

  // Copy/Cut/Paste shortcuts
  if ((e.ctrlKey || e.metaKey)) {
    switch (key) {
      case 'c':
        if (ui.selectedElementId) {
          const element = presentation.currentSlide.elements.find(el => el.id === ui.selectedElementId)
          if (element) {
            clipboard.copyElement(element, presentation.currentSlide.id)
          }
        }
        e.preventDefault()
        return
        
      case 'x':
        if (ui.selectedElementId) {
          const element = presentation.currentSlide.elements.find(el => el.id === ui.selectedElementId)
          if (element) {
            const deletedId = clipboard.cutElement(element, presentation.currentSlide.id)
            presentation.deleteElement(deletedId)
            ui.clearSelection()
          }
        }
        e.preventDefault()
        return
        
      case 'v':
        if (clipboard.hasClipboardContent() && !clipboard.isClipboardExpired()) {
          clipboard.pasteElement(presentation.currentSlide.id).then(pastedElement => {
            if (pastedElement) {
              presentation.addElement(pastedElement)
            }
          })
        }
        e.preventDefault()
        return
        
      case 'z':
        // Undo/Redo could be implemented here
        e.preventDefault()
        return
        
      case 'd':
        if (ui.selectedElementId) {
          presentation.duplicateElement(ui.selectedElementId)
        }
        e.preventDefault()
        return
        
      case '=':
        ui.zoomIn()
        e.preventDefault()
        return
        
      case '-':
        ui.zoomOut()
        e.preventDefault()
        return
        
      case '0':
        ui.resetZoom()
        e.preventDefault()
        return
    }
  }

  // Slide navigation (edit mode only — present mode handled by PresentModeController)
  if (ui.isEditMode) {
    if (e.key === 'ArrowRight' || e.key === 'ArrowDown' || e.key === 'PageDown') {
      presentation.selectSlide(presentation.currentSlideIndex + 1)
    } else if (e.key === 'ArrowLeft' || e.key === 'ArrowUp' || e.key === 'PageUp') {
      presentation.selectSlide(presentation.currentSlideIndex - 1)
    }
  }

  // Mode toggles
  if (key === 'e') {
    ui.toggleEditMode()
  }

  if (ui.isEditMode) {
    if (key === 'f') {
      ui.toggleFocusMode()
    }
    if (key === 's') {
      ui.toggleSlideNav()
    }
    if (key === 't') {
      ui.toggleEditTools()
    }
    if (key === 'p') {
      ui.togglePagesView()
    }
  }
}

// Print handling
let prevPrintState = null

function handleBeforePrint() {
  prevPrintState = {
    isEditMode: ui.isEditMode,
    presentModeLayout: ui.presentModeLayout,
    zoomLevel: ui.zoomLevel
  }

  ui.isEditMode = false
  ui.presentModeLayout = 'continuous'
  ui.resetZoom()
}

function handleAfterPrint() {
  if (!prevPrintState) return
  ui.isEditMode = prevPrintState.isEditMode
  ui.presentModeLayout = prevPrintState.presentModeLayout
  ui.zoomLevel = prevPrintState.zoomLevel
  prevPrintState = null
}

// PWA Service Worker Registration
function registerServiceWorker() {
  if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/sw.js')
      .then((registration) => {
        console.log('[SW] Service worker registered:', registration.scope)
        
        // Check for updates
        registration.addEventListener('updatefound', () => {
          const newWorker = registration.installing
          console.log('[SW] New service worker found')
          
          newWorker.addEventListener('statechange', () => {
            if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
              // New version available
              showUpdateNotification()
            }
          })
        })
      })
      .catch((error) => {
        console.error('[SW] Service worker registration failed:', error)
      })
  }
}

// PWA Install Prompt
let deferredPrompt = null

function handleBeforeInstallPrompt(e) {
  e.preventDefault()
  deferredPrompt = e
  showInstallButton()
}

function showInstallButton() {
  const installBtn = document.createElement('button')
  installBtn.textContent = 'Install App'
  installBtn.className = 'pwa-install-btn'
  installBtn.style.cssText = `
    position: fixed;
    bottom: 80px;
    right: 20px;
    background: #6366f1;
    color: white;
    border: none;
    padding: 12px 20px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    z-index: 10001;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
  `
  
  installBtn.onclick = () => {
    if (deferredPrompt) {
      deferredPrompt.prompt()
      deferredPrompt.userChoice.then((choiceResult) => {
        if (choiceResult.outcome === 'accepted') {
          console.log('[PWA] User accepted the install prompt')
        }
        deferredPrompt = null
        installBtn.remove()
      })
    }
  }
  
  document.body.appendChild(installBtn)
}

function showUpdateNotification() {
  const notification = document.createElement('div')
  notification.textContent = 'New version available! Click to refresh.'
  notification.className = 'pwa-update-notification'
  notification.style.cssText = `
    position: fixed;
    top: 20px;
    right: 20px;
    background: #10b981;
    color: white;
    padding: 12px 20px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    z-index: 10002;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
  `
  
  notification.onclick = () => {
    window.location.reload()
  }
  
  document.body.appendChild(notification)
  
  // Auto-hide after 10 seconds
  setTimeout(() => {
    if (notification.parentNode) {
      notification.remove()
    }
  }, 10000)
}

// Online/Offline Status
function updateOnlineStatus() {
  const isOnline = navigator.onLine
  const statusIndicator = document.querySelector('.online-status') || createOnlineStatusIndicator()
  
  statusIndicator.textContent = isOnline ? '🟢 Online' : '🔴 Offline'
  statusIndicator.className = `online-status ${isOnline ? 'online' : 'offline'}`
  
  if (isOnline) {
    // Trigger sync when back online
    if ('serviceWorker' in navigator && 'sync' in window.ServiceWorkerRegistration.prototype) {
      navigator.serviceWorker.ready.then((registration) => {
        return registration.sync.register('presentation-sync')
      })
    }
  }
}

function createOnlineStatusIndicator() {
  const indicator = document.createElement('div')
  indicator.className = 'online-status'
  indicator.style.cssText = `
    position: fixed;
    top: 20px;
    left: 20px;
    background: rgba(0,0,0,0.8);
    color: white;
    padding: 8px 12px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    z-index: 10003;
  `
  document.body.appendChild(indicator)
  return indicator
}

// Lifecycle
onMounted(() => {
  const timestamp = new Date().toISOString()
  console.log(`[${timestamp}] V8 Builder - onMounted started`)
  console.log(`[${timestamp}] Build version: 2026-05-09-v8-mobile-offline`)
  
  // Register service worker
  registerServiceWorker()
  
  // PWA install prompt
  window.addEventListener('beforeinstallprompt', handleBeforeInstallPrompt)
  
  // Online/offline status
  updateOnlineStatus()
  window.addEventListener('online', updateOnlineStatus)
  window.addEventListener('offline', updateOnlineStatus)
  
  // Event listeners
  document.addEventListener('paste', handlePaste)
  document.addEventListener('keydown', handleKeydown)
  document.addEventListener('click', closeSlideContextMenu)
  window.addEventListener('beforeprint', handleBeforePrint)
  window.addEventListener('afterprint', handleAfterPrint)
  
  console.log(`[${timestamp}] V8 Builder - event listeners added`)
})

onUnmounted(() => {
  document.removeEventListener('paste', handlePaste)
  document.removeEventListener('keydown', handleKeydown)
  document.removeEventListener('click', closeSlideContextMenu)
  window.removeEventListener('beforeprint', handleBeforePrint)
  window.removeEventListener('afterprint', handleAfterPrint)
})
</script>

<template>
  <div class="v8-container" :class="{ 
    'is-focus-mode': ui.isFocusMode, 
    'has-fixed-toolbar': ui.isEditMode && ui.areEditToolsVisible 
  }" @contextmenu="handleSlideContextMenu">
    
    <!-- Toolbar (Edit Mode) -->
    <Toolbar v-if="ui.isEditMode && ui.areEditToolsVisible" />

    <!-- Zoom Toolbar for Present Mode -->
    <div v-if="!ui.isEditMode" class="present-mode-zoom-toolbar">
      <button @click="ui.togglePresentModeLayout()" class="zoom-btn" :title="ui.presentModeLayout === 'continuous' ? 'Pages View (Continuous)' : 'Single Slide View'">
        <svg v-if="ui.presentModeLayout === 'continuous'" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
          <line x1="3" y1="9" x2="21" y2="9"></line>
          <line x1="9" y1="21" x2="9" y2="9"></line>
        </svg>
        <svg v-else xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
          <rect x="7" y="7" width="10" height="10" rx="1" ry="1"></rect>
        </svg>
        <span class="zoom-btn-text">{{ ui.presentModeLayout === 'continuous' ? 'Pages' : 'Slide' }}</span>
      </button>
      <button @click="ui.zoomIn()" class="zoom-btn" title="Zoom In (Ctrl +)">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="11" cy="11" r="8"></circle>
          <path d="m21 21-4.35-4.35"></path>
          <line x1="11" y1="8" x2="11" y2="14"></line>
          <line x1="8" y1="11" x2="14" y2="11"></line>
        </svg>
      </button>
      <div class="zoom-level" @click="promptCustomZoom" title="Click to set custom zoom">
        {{ Math.round(ui.zoomLevel) }}%
      </div>
      <div class="zoom-presets">
        <button class="zoom-preset" @click="ui.setZoom(75)" title="75%">75</button>
        <button class="zoom-preset" @click="ui.setZoom(100)" title="100%">100</button>
        <button class="zoom-preset" @click="ui.setZoom(125)" title="125%">125</button>
        <button class="zoom-preset" @click="ui.setZoom(150)" title="150%">150</button>
      </div>
      <button @click="ui.zoomOut()" class="zoom-btn" title="Zoom Out (Ctrl -)">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="11" cy="11" r="8"></circle>
          <path d="m21 21-4.35-4.35"></path>
          <line x1="8" y1="11" x2="14" y2="11"></line>
        </svg>
      </button>
      <button @click="ui.resetZoom()" class="zoom-btn" title="Reset Zoom (Ctrl 0)">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path>
          <path d="M3 3v5h5"></path>
        </svg>
      </button>
      <div class="zoom-divider" />
      <button @click="toggleFullscreen" class="zoom-btn" title="Full Screen">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M8 3H5a2 2 0 0 0-2 2v3"/><path d="M21 8V5a2 2 0 0 0-2-2h-3"/><path d="M3 16v3a2 2 0 0 0 2 2h3"/><path d="M16 21h3a2 2 0 0 0 2-2v-3"/>
        </svg>
      </button>
      <button @click="ui.toggleEditMode" class="zoom-btn" title="Exit Present Mode (E)">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
        </svg>
      </button>
    </div>

    <!-- Present Mode Controller Overlay -->
    <PresentModeController
      v-if="!ui.isEditMode"
      :current-index="presentation.currentSlideIndex"
      :total-slides="presentation.totalSlides"
      :slide-title="presentation.currentSlide.elements.find(e => e.type === 'text')?.content || 'Slide ' + (presentation.currentSlideIndex + 1)"
      :slide-description="presentation.description"
      :annotations-visible="ui.showAnnotations"
      @prev="presentation.selectSlide(presentation.currentSlideIndex - 1)"
      @next="presentation.selectSlide(presentation.currentSlideIndex + 1)"
      @go-to-slide="presentation.selectSlide($event)"
      @exit="ui.toggleEditMode()"
      @toggle-annotations="ui.toggleAnnotations()"
    />

    <div class="editor-layout" :class="{ 'slide-nav-hidden': !ui.isSlideNavVisible }">
      <!-- Sidebar Navigation -->
      <SlideNavigationBar v-if="ui.isSlideNavVisible && ui.isEditMode" />

      <button
        v-else-if="ui.isEditMode"
        class="slide-nav-toggle"
        @click="ui.toggleSlideNav()"
        title="Show slides"
      >
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <polyline points="9 18 15 12 9 6"></polyline>
        </svg>
      </button>

      <!-- Canvas Area -->
      <div class="canvas-area" :class="{ 'present-mode': !ui.isEditMode }">
        <div v-if="ui.isEditMode && ui.isPagesView" class="pages-view">
          <div
            v-for="(slide, idx) in presentation.slides"
            :key="slide.id"
            class="pages-view-slide"
            @click="handlePageSelect(idx)"
            title="Click to open this slide"
          >
            <div class="continuous-slide-label">Slide {{ idx + 1 }}</div>
            <SlideCanvasReadonly :slide="slide" />
          </div>
        </div>

        <EditorCanvas v-else />
      </div>
    </div>

    <!-- Focus Mode FAB -->
    <button
      v-if="ui.isEditMode"
      class="focus-fab"
      :class="{ active: ui.isFocusMode }"
      @click="ui.toggleFocusMode()"
      :title="ui.isFocusMode ? 'Exit Focus Mode' : 'Focus Mode (Clean Canvas)'"
    >
      <svg v-if="!ui.isFocusMode" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M8 3H5a2 2 0 0 0-2 2v3"/>
        <path d="M16 3h3a2 2 0 0 1 2 2v3"/>
        <path d="M8 21H5a2 2 0 0 1-2-2v-3"/>
        <path d="M16 21h3a2 2 0 0 0 2-2v-3"/>
      </svg>
      <svg v-else xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M9 18H5a2 2 0 0 1-2-2v-4"/>
        <path d="M15 6h4a2 2 0 0 1 2 2v4"/>
        <path d="M3 10V8a2 2 0 0 1 2-2h2"/>
        <path d="M21 14v2a2 2 0 0 1-2 2h-2"/>
      </svg>
      <span>{{ ui.isFocusMode ? 'Focus' : 'Focus' }}</span>
    </button>

    <!-- Focus Mode Mini Bar -->
    <div v-if="ui.isEditMode && ui.isFocusMode" class="focus-mini-bar">
      <button class="mini-btn" @click="ui.toggleEditTools()" :title="ui.areEditToolsVisible ? 'Hide Tools' : 'Show Tools'">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.8-3.8a2 2 0 0 0 0-2.8l-1.2-1.2a2 2 0 0 0-2.8 0Z"/>
          <path d="M6 20l2-2"/>
          <path d="M2 22l6-6"/>
          <path d="M14 10l-8 8"/>
        </svg>
      </button>
      <button class="mini-btn" @click="ui.toggleSlideNav()" :title="ui.isSlideNavVisible ? 'Hide Slides' : 'Show Slides'">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <rect x="3" y="3" width="18" height="14" rx="2"/>
          <path d="M7 21h10"/>
          <path d="M12 17v4"/>
        </svg>
      </button>
      <button class="mini-btn danger" @click="ui.toggleFocusMode()" title="Exit Focus Mode">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="18" y1="6" x2="6" y2="18"/>
          <line x1="6" y1="6" x2="18" y2="18"/>
        </svg>
      </button>
    </div>

    <!-- Context Menu -->
    <ElementContextMenu
      :show="ui.contextMenu.show"
      :x="ui.contextMenu.x"
      :y="ui.contextMenu.y"
      :element-id="ui.contextMenu.elementId"
    />
  </div>
</template>

<style scoped>
.v8-container {
  padding-top: 60px; /* Space for mobile header */
  padding-left: 1rem;
  padding-right: 1rem;
  padding-bottom: 1rem;
  min-height: 100vh;
  background-color: #f3f4f6;
  font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
  position: relative;
}

.v8-container.has-fixed-toolbar {
  padding-top: 140px;
}

/* Present mode: dark background for projection */
.v8-container:has(> .present-mode-zoom-toolbar) {
  background-color: #111827;
  padding: 0;
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
}

.v8-container.is-focus-mode {
  padding-left: 1rem;
  padding-right: 1rem;
}

.editor-layout {
  display: flex;
  gap: 10px;
  margin: 0 auto;
  align-items: flex-start;
  justify-content: center;
}

.editor-layout.slide-nav-hidden {
  justify-content: center;
}

.canvas-area {
  flex: 1;
  max-width: 1200px;
}

.canvas-area.present-mode {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0;
  max-width: none;
}

.canvas-area.present-mode :deep(.editor-canvas) {
  background: transparent;
  padding: 0;
}

.canvas-area.present-mode :deep(.canvas) {
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
}

.pages-view {
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 28px;
  padding: 12px 0;
}

.pages-view-slide {
  width: 100%;
  cursor: pointer;
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  padding: 20px;
  transition: all 0.2s;
}

.pages-view-slide:hover {
  outline: 2px solid rgba(99, 102, 241, 0.35);
  outline-offset: 6px;
  border-radius: 10px;
}

.continuous-slide-label {
  font-size: 12px;
  font-weight: 600;
  color: #6b7280;
  margin: 0 0 8px 6px;
}

.slide-nav-toggle {
  position: sticky;
  top: 90px;
  align-self: flex-start;
  width: 34px;
  height: 34px;
  border-radius: 10px;
  border: 1px solid #e5e7eb;
  background: white;
  color: #374151;
  box-shadow: 0 1px 3px rgba(0,0,0,0.12);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  flex-shrink: 0;
}

.slide-nav-toggle:hover {
  background: #f9fafb;
  border-color: #d1d5db;
}

/* Focus FAB */
.focus-fab {
  position: fixed;
  right: 24px;
  bottom: 26px;
  z-index: 10010;
  display: inline-flex;
  align-items: center;
  gap: 10px;
  padding: 12px 14px;
  border-radius: 999px;
  border: 1px solid #e2e8f0;
  background: rgba(255,255,255,0.95);
  color: #0f172a;
  font-weight: 900;
  box-shadow: 0 12px 25px rgba(0,0,0,0.12);
  cursor: pointer;
}

.focus-fab.active {
  border-color: #c7d2fe;
  background: rgba(238, 242, 255, 0.95);
}

.focus-mini-bar {
  position: fixed;
  right: 24px;
  bottom: 86px;
  z-index: 10010;
  display: flex;
  flex-direction: column;
  gap: 10px;
  padding: 10px;
  border-radius: 16px;
  border: 1px solid #e2e8f0;
  background: rgba(255,255,255,0.95);
  box-shadow: 0 12px 25px rgba(0,0,0,0.12);
}

.mini-btn {
  width: 44px;
  height: 44px;
  border-radius: 14px;
  border: 1px solid #e5e7eb;
  background: white;
  color: #0f172a;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.mini-btn:hover {
  background: #f8fafc;
}

.mini-btn.danger {
  background: #fef2f2;
  border-color: #fecaca;
  color: #991b1b;
}

/* Present Mode Zoom Toolbar */
.present-mode-zoom-toolbar {
  position: fixed;
  bottom: 20px;
  right: 20px;
  display: flex;
  align-items: center;
  gap: 8px;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(229, 231, 235, 0.8);
  border-radius: 12px;
  padding: 8px 12px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  z-index: 45;
}

.zoom-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border: none;
  border-radius: 6px;
  background: transparent;
  color: #374151;
  cursor: pointer;
  transition: all 0.2s;
}

.zoom-btn:hover {
  background: #f3f4f6;
  color: #111827;
}

.zoom-btn-text {
  margin-left: 6px;
  font-size: 12px;
  font-weight: 600;
}

.zoom-level {
  font-size: 0.75rem;
  font-weight: 600;
  color: #374151;
  min-width: 40px;
  text-align: center;
  cursor: pointer;
}

.zoom-presets {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 0 2px;
}

.zoom-preset {
  border: 1px solid rgba(0, 0, 0, 0.08);
  background: rgba(255, 255, 255, 0.9);
  color: #374151;
  border-radius: 8px;
  padding: 6px 8px;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
}

.zoom-preset:hover {
  background: white;
  border-color: rgba(99, 102, 241, 0.4);
  color: #4f46e5;
}

.zoom-divider {
  width: 1px;
  height: 20px;
  background: #d1d5db;
}

/* Mobile-first responsive design */
@media (min-width: 768px) {
  .v8-container {
    padding-top: 20px;
    padding-left: 2rem;
    padding-right: 2rem;
    padding-bottom: 2rem;
  }
}

@media (min-width: 1024px) {
  .editor-layout {
    flex-direction: row;
    align-items: flex-start;
  }
  
  .v8-container.has-fixed-toolbar {
    padding-top: 140px;
  }
}

@media (max-width: 767px) {
  .v8-container {
    padding-top: 50px; /* Smaller header on mobile */
  }
  
  .editor-layout {
    flex-direction: column-reverse;
    align-items: center;
    gap: 8px;
  }
  
  .canvas-area {
    width: 100%;
    max-width: none;
  }
  
  .focus-fab {
    bottom: 16px;
    right: 16px;
    padding: 10px 12px;
  }
  
  .focus-fab span {
    display: none; /* Hide text on mobile */
  }
  
  .focus-mini-bar {
    bottom: 70px;
    right: 16px;
  }
  
  .present-mode-zoom-toolbar {
    bottom: 16px;
    right: 16px;
    left: 16px;
    justify-content: space-between;
    flex-wrap: wrap;
  }
  
  .zoom-presets {
    order: -1;
    width: 100%;
    justify-content: center;
    margin-bottom: 8px;
  }
}

@media print {
  @page {
    margin: 10mm;
  }

  .v8-container {
    padding: 0 !important;
    background: white !important;
    min-height: auto !important;
  }

  .present-mode-zoom-toolbar,
  .focus-fab,
  .focus-mini-bar {
    display: none !important;
  }

  .pages-view {
    gap: 0 !important;
    padding: 0 !important;
  }

  .pages-view-slide {
    page-break-after: always;
    break-after: page;
    margin: 0 !important;
    padding: 0 !important;
    border: none !important;
  }

  .continuous-slide-label {
    display: none !important;
  }
}
</style>
