<template>
  <div 
    :class="print_sild_only ? '' : 'min-h-screen p-8 print:p-0'" 
    class="bg-white"
  >
    <!-- Print Controls (Hidden when printing) -->
    <div 
      :class="print_sild_only ? 'no-print' : 'print:hidden'" 
      class="max-w-4xl mx-auto mb-8 flex justify-between items-center"
    >
      <h1 class="text-2xl font-bold text-gray-900">{{ presentation.name }}</h1>
      <button 
        @click="print_all"
        class="no-print inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
      >
        <i class="fas fa-print mr-2"></i> Print Lesson
      </button>
    </div>

    <!-- Printable Content -->
    <div 
      :class="print_sild_only ? '' : 'max-w-4xl mx-auto space-y-8 print:w-full print:max-w-none print:space-y-8'"
    >
      <!-- Cover Page / Header -->
      <div 
        :class="print_sild_only ? 'no-print' : ''"
        class="text-center border-b-2 border-gray-200 pb-8 mb-12 print:border-b print:pb-8 print:mb-12"
      >
        <h1 class="text-4xl font-bold text-gray-900 mb-4">{{ presentation.name }}</h1>
        <p class="text-xl text-gray-600">{{ presentation.description }}</p>
        <div class="mt-4 text-sm text-gray-500">
          <p>Teacher: {{ presentation.teacher?.name || 'N/A' }}</p>
          <p>Subject: {{ presentation.subject?.name || 'N/A' }}</p>
          <p>Date: {{ new Date().toLocaleDateString() }}</p>
        </div>
      </div>

      <!-- Slides -->
      <div 
        v-for="(slide, index) in slides" 
        :key="slide.id || index"
        :class="print_sild_only && index !== sel_slideIndex ? 'no-print' : ''"
        class="slide-container relative break-inside-avoid border border-gray-200 rounded-lg p-8 print:border-0 print:p-0 print:rounded-none print:mb-12 group"
      >
        <!-- Print This Slide Button (visible on hover) -->
        <button
          @click="showPrintPreview(index)"
          class="absolute no-print top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity duration-200 print:hidden bg-blue-600 hover:bg-blue-700 text-white rounded-lg px-3 py-2 shadow-lg flex items-center gap-2 text-sm font-medium z-10"
          title="Print this slide"
        >
          <i class="fas fa-print"></i>
          <span>Print Slide</span>
        </button>

        <div class="flex items-center justify-between mb-6 border-b border-gray-100 pb-2 print:border-gray-300">
          <span class="text-sm font-medium text-gray-500 uppercase tracking-wider">Slide {{ index + 1 }}</span>
          <span class="text-xs text-gray-400 uppercase">{{ slide.slide_type }}</span>
        </div>

        <div class="slide-content">
          <!-- Text Slide -->
          <div v-if="slide.slide_type === 'text'" class="prose max-w-none" v-html="safeHtml(slide.slide_content?.text)"></div>

          <!-- Media Slide -->
          <div v-else-if="['image', 'video', 'audio', 'pdf'].includes(slide.slide_type)" class="flex flex-col items-center">
            <h3 v-if="slide.slide_content?.title" class="text-lg font-medium mb-4">{{ slide.slide_content.title }}</h3>
            
            <img 
              v-if="slide.slide_type === 'image' && slide.slide_content?.url" 
              :src="slide.slide_content.url" 
              class="max-w-full h-auto rounded-lg shadow-sm print:shadow-none"
            />
            
            <div v-else-if="slide.slide_type === 'video'" class="text-center p-8 bg-gray-50 rounded-lg w-full print:border print:border-gray-300">
              <i class="fas fa-video text-4xl text-gray-400 mb-2"></i>
              <p class="text-sm text-gray-600">Video: {{ slide.slide_content?.url }}</p>
            </div>
            
            <div v-else-if="slide.slide_type === 'audio'" class="text-center p-8 bg-gray-50 rounded-lg w-full print:border print:border-gray-300">
              <i class="fas fa-volume-up text-4xl text-gray-400 mb-2"></i>
              <p class="text-sm text-gray-600">Audio: {{ slide.slide_content?.url }}</p>
            </div>

            <div v-else-if="slide.slide_type === 'pdf'" class="text-center p-8 bg-gray-50 rounded-lg w-full print:border print:border-gray-300">
              <i class="fas fa-file-pdf text-4xl text-gray-400 mb-2"></i>
              <p class="text-sm text-gray-600">PDF Document: {{ slide.slide_content?.url }}</p>
            </div>

            <p v-if="slide.slide_content?.caption" class="mt-2 text-sm text-gray-500 italic">{{ slide.slide_content.caption }}</p>
          </div>

          <!-- Question Slide -->
          <div v-else-if="slide.slide_type === 'question'" class="space-y-8">
            <div v-for="(question, qIdx) in (slide.slide_content?.questions || [])" :key="qIdx" class="pl-4 border-l-4 border-blue-100 print:border-gray-300">
              <div class="font-medium text-gray-900 mb-3" v-html="safeHtml(question.text)"></div>
              
              <div class="ml-6 space-y-3">
                <div v-if="question.type === 'true_false'" class="flex gap-8 text-lg">
                  <div class="flex items-center"><div class="w-5 h-5 border-2 border-gray-400 rounded-full mr-3"></div> True</div>
                  <div class="flex items-center"><div class="w-5 h-5 border-2 border-gray-400 rounded-full mr-3"></div> False</div>
                </div>
                
                <div v-else-if="question.type === 'multiple_choice' || question.type === 'single_choice'" class="space-y-3">
                  <div v-for="option in question.options" :key="option.id" class="flex items-start text-base">
                    <div class="w-5 h-5 border-2 border-gray-400 mr-3 mt-0.5 flex-shrink-0" 
                         :class="question.type === 'single_choice' ? 'rounded-full' : 'rounded'"></div>
                    <span v-html="safeHtml(option.text)"></span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick, computed } from 'vue'
import axios from 'axios'

const props = defineProps({
  presentationId: { type: [Number, String], required: true }
})

const presentation = ref({})
const slides = ref([])
const sel_slideIndex = ref(0)
const print_sild_only = ref(false)

// ──────────────────────────────────────────────────────────────
// FIX: Convert Unicode superscripts → <sup> tags BEFORE v-html
// This is the #1 fix that stops Chrome print duplication bug
// ──────────────────────────────────────────────────────────────
const safeHtml = (html = '') => {
  if (!html || typeof html !== 'string') return ''

  const superscriptMap = { '⁰':'0','¹':'1','²':'2','³':'3','⁴':'4','⁵':'5','⁶':'6','⁷':'7','⁸':'8','⁹':'9' }

  return html
    .replace(/\^(\d+|[n\-+])/g, (match, p) => {
      if (p === 'n') return '<sup>n</sup>'
      return p.split('').map(char => {
        if (/[0-9]/.test(char)) return `<sup>${char}</sup>`
        if ('-+'.includes(char)) return char
        return char
      }).join('')
    })
    // Convert any remaining Unicode superscripts (² ³ ⁴ etc.) → <sup>
    .replace(/[\u00B2\u00B3\u2070-\u2079]/g, match => {
      return `<sup>${superscriptMap[match] || match}</sup>`
    })
    // Extra safety: convert common patterns like 3⁴ → 3<sup>4</sup>
    .replace(/(\d)([\u2070-\u2079\u00B2\u00B3\u207B\u207A])/g, (match, digit, superscript) => {
      return digit + '<sup>' + (superscriptMap[superscript] || superscript) + '</sup>'
    })
}

const fetchLesson = async () => {
  try {
    const response = await axios.get(route('lesson-presentation.show', { id: props.presentationId }))
    presentation.value = response.data
    slides.value = response.data.slides || []
  } catch (error) {
    console.error('Failed to load lesson:', error)
  }
}

const print_all = () => {
  print_sild_only.value = false
  setTimeout(() => window.print(), 100)
}

const showPrintPreview = (index) => {
  sel_slideIndex.value = index
  printSlide(index)
}

const printSlide = async (slideIndex) => {
  print_sild_only.value = true
  sel_slideIndex.value = slideIndex

  await nextTick()

  // Force reflow
  document.body.offsetHeight

  setTimeout(() => {
    window.print()
    // Reset after print dialog closes
    setTimeout(() => {
      print_sild_only.value = false
    }, 800)
  }, 150)
}

onMounted(() => {
  fetchLesson()
})
</script>

<style>
/* ──────────────────────────────────────────────────────────────
   CRITICAL PRINT FIXES – Must be in <style> (not scoped!)
   These stop Chrome/Edge from duplicating or breaking <sup>
   ────────────────────────────────────────────────────────────── */
@media print {
  @page { margin: 1.5cm; }

  .no-print { display: none !important; }

  sup {
    font-feature-settings: "sups" 1 !important;
    -webkit-font-feature-settings: "sups" 1 !important;
    vertical-align: super !important;
    font-size: 0.65em !important;
    line-height: 0.8 !important;
    top: 0 !important;
  }

  /* Extra safety for Tailwind prose */
  .prose sup {
    font-size: 0.65em !important;
    vertical-align: super !important;
  }
}
</style>
