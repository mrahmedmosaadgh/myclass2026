<template>
  <div class="pdf-viewer-wrapper">
    <!-- Elegant Loading Overlay -->
    <transition name="fade">
      <div v-if="isLoading" class="loading-overlay">
        <div class="spinner"></div>
        <p class="loading-text">جاري تحميل المستند...</p>
      </div>
    </transition>

    <!-- Error Message -->
    <transition name="fade">
      <div v-if="error" class="error-overlay">
        <div class="error-icon">⚠️</div>
        <p>فشل تحميل الملف</p>
        <small>{{ error }}</small>
        <button @click="retry" class="retry-btn">إعادة المحاولة</button>
      </div>
    </transition>

    <!-- No PDF Selected -->
    <div v-if="!pdfUrl && !isLoading && !error" class="empty-state">
      <div class="empty-icon">📄</div>
      <h3>لا يوجد ملف PDF</h3>
      <p>اختر ملفًا أو اسحب وأفلت هنا</p>
    </div>

    <!-- PDF Viewer -->
    <vue-pdf-embed
      v-if="pdfUrl && !error"
      :source="pdfUrl"
      @loaded="onLoaded"
      @load-failed="onLoadFailed"
      @render-failed="onRenderFailed"
      class="pdf-embed"
      :text-layer="true"
      :annotation-layer="true"
    />
  </div>
</template>

<script>
import VuePdfEmbed from 'vue-pdf-embed'

export default {
  name: 'PDFViewer',
  components: {
    VuePdfEmbed
  },
  props: {
    pdfUrl: {
      type: String,
      default: ''
    }
  },
  data() {
    return {
      isLoading: true,
      error: null
    }
  },
  watch: {
    pdfUrl(newUrl) {
      if (newUrl) {
        this.isLoading = true
        this.error = null
      }
    }
  },
  methods: {
    onLoaded() {
      this.isLoading = false
      this.error = null
      this.$emit('loaded')
    },
    onLoadFailed(err) {
      this.isLoading = false
      this.error = 'تعذر تحميل الملف. قد يكون تالفًا أو محميًا بكلمة مرور.'
      console.error('PDF Load Failed:', err)
      this.$emit('error', err)
    },
    onRenderFailed(err) {
      this.error = 'فشل عرض الصفحات'
      console.error('PDF Render Failed:', err)
    },
    retry() {
      if (this.pdfUrl) {
        this.isLoading = true
        this.error = null
        // Force reload
        setTimeout(() => {
          this.$forceUpdate()
        }, 100)
      }
    }
  },
  mounted() {
    if (!this.pdfUrl) {
      this.isLoading = false
    }
  }
}
</script>

<style scoped>
.pdf-viewer-wrapper {
  width: 100%;
  height: 100%;
  position: relative;
  background: #2d2d2d;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
  direction: ltr; /* PDF.js works best in LTR */
}

.pdf-embed {
  width: 100% !important;
  height: 100% !important;
  border: none;
}

/* Loading Overlay */
.loading-overlay {
  position: absolute;
  top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(20, 20, 30, 0.95);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: white;
  z-index: 100;
  backdrop-filter: blur(5px);
}

.spinner {
  width: 64px;
  height: 64px;
  border: 5px solid #444;
  border-top: 5px solid #4a9eff;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 20px;
}

.loading-text {
  font-size: 1.4rem;
  font-weight: 500;
  margin: 0;
}

/* Error State */
.error-overlay {
  position: absolute;
  top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(30, 10, 10, 0.95);
  color: #ff6b6b;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  padding: 2rem;
  z-index: 100;
}

.error-icon {
  font-size: 4rem;
  margin-bottom: 1rem;
}

.error-overlay p {
  font-size: 1.5rem;
  margin: 0.5rem 0;
}

.error-overlay small {
  color: #ff9999;
  margin: 1rem 0 1.5rem;
  max-width: 90%;
}

.retry-btn {
  background: #ff6b6b;
  color: white;
  border: none;
  padding: 12px 24px;
  border-radius: 8px;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.3s;
}

.retry-btn:hover {
  background: #ff5252;
  transform: translateY(-2px);
}

/* Empty State */
.empty-state {
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: #888;
  text-align: center;
  padding: 3rem;
}

.empty-icon {
  font-size: 5rem;
  margin-bottom: 1rem;
}

.empty-state h3 {
  font-size: 1.8rem;
  margin: 0 0 0.5rem;
  color: #bbb;
}

.empty-state p {
  font-size: 1.2rem;
  color: #999;
}

/* Animations */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.4s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Dark mode friendly */
@media (prefers-color-scheme: dark) {
  .pdf-viewer-wrapper {
    background: #1a1a1a;
  }
}
</style>