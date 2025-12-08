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
        class="text-center border-b-2 border-gray-200 pb-8 mb-12"
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
       :class="print_sild_only && index != sel_slideIndex ? 'no-print' : ''" 
        v-for="(slide, index) in slides" 
        :key="slide.id || index"
        class="slide-container relative break-inside-avoid border border-gray-200 rounded-lg p-8 print:border-0 print:p-0 print:rounded-none print:mb-0 group"
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

        <div 
         :class=" print_sild_only?'no-print':'print:hidden' "
        
        class="flex items-center justify-between mb-6 border-b border-gray-100 pb-2 ">
          <span class="text-sm font-medium text-gray-500 uppercase tracking-wider">Slide {{ index + 1 }}</span>
          <span class="text-xs text-gray-400 uppercase">{{ slide.slide_type }}</span>
        </div>

        <div class="slide-content">
          <!-- Text Slide -->
          <div v-if="slide.slide_type === 'text'" class="prose max-w-none" v-html="slide.slide_content?.text"></div>

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
          <div v-else-if="slide.slide_type === 'question'" class="space-y-6">
            <div v-for="(question, qIdx) in (slide.slide_content?.questions || [])" :key="qIdx" class="pl-4 border-l-4 border-blue-100 print:border-gray-300">
              <div class="font-medium text-gray-900 mb-3" v-html="question.text"></div>
              
              <div class="ml-4 space-y-2">
                <div v-if="question.type === 'true_false'" class="flex gap-4">
                  <div class="flex items-center">
                    <div class="w-4 h-4 border border-gray-300 rounded-full mr-2"></div> True
                  </div>
                  <div class="flex items-center">
                    <div class="w-4 h-4 border border-gray-300 rounded-full mr-2"></div> False
                  </div>
                </div>
                
                <div v-else-if="question.type === 'multiple_choice' || question.type === 'single_choice'" class="space-y-2">
                  <div v-for="option in question.options" :key="option.id" class="flex items-center">
                    <div class="w-4 h-4 border border-gray-300 mr-2" :class="question.type === 'single_choice' ? 'rounded-full' : 'rounded'"></div>
                    <span v-html="option.text"></span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Print Preview Modal -->
    <div 
      v-if="showPreviewModal" 
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4 no-print"
      @click.self="closePreview"
    >
      <div class="bg-white rounded-xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden flex flex-col">
        <!-- Modal Header -->
        <div class="flex items-center justify-between p-6 border-b border-gray-200">
          <div>
            <h2 class="text-2xl font-bold text-gray-900">Print Preview</h2>
            <p class="text-sm text-gray-500 mt-1">Slide {{ sel_slideIndex + 1 }} of {{ slides.length }}</p>
          </div>
          <button 
            @click="closePreview"
            class="text-gray-400 hover:text-gray-600 text-2xl w-8 h-8 flex items-center justify-center"
          >
            ×
          </button>
        </div>
        <!-- Modal Footer -->
        <div class="flex items-center justify-end gap-3 p-6 border-t border-gray-200 bg-gray-50">
          <button
            @click="closePreview"
            class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors font-medium"
          >
            Cancel
          </button>
          <button
            @click="confirmPrint"
            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium flex items-center gap-2"
          >
            <i class="fas fa-print"></i>
            Print This Slide
          </button>
        </div>
        <!-- Preview Content (scrollable) -->
        <div class="flex-1 overflow-y-auto p-8 bg-gray-50">
          <div class="bg-white rounded-lg shadow-sm p-8 max-w-3xl mx-auto">
            <!-- Slide Content Preview -->
            <div v-if="previewSlide">
              <!-- Text Slide -->
              <div v-if="previewSlide.slide_type === 'text'" class="prose max-w-none" v-html="previewSlide.slide_content?.text"></div>

              <!-- Media Slide -->
              <div v-else-if="['image', 'video', 'audio', 'pdf'].includes(previewSlide.slide_type)" class="flex flex-col items-center">
                <h3 v-if="previewSlide.slide_content?.title" class="text-lg font-medium mb-4">{{ previewSlide.slide_content.title }}</h3>
                
                <img 
                  v-if="previewSlide.slide_type === 'image' && previewSlide.slide_content?.url" 
                  :src="previewSlide.slide_content.url" 
                  class="max-w-full h-auto rounded-lg shadow-sm"
                />
                
                <div v-else-if="previewSlide.slide_type === 'video'" class="text-center p-8 bg-gray-50 rounded-lg w-full border border-gray-300">
                  <i class="fas fa-video text-4xl text-gray-400 mb-2"></i>
                  <p class="text-sm text-gray-600">Video: {{ previewSlide.slide_content?.url }}</p>
                </div>
                
                <div v-else-if="previewSlide.slide_type === 'audio'" class="text-center p-8 bg-gray-50 rounded-lg w-full border border-gray-300">
                  <i class="fas fa-volume-up text-4xl text-gray-400 mb-2"></i>
                  <p class="text-sm text-gray-600">Audio: {{ previewSlide.slide_content?.url }}</p>
                </div>

                <div v-else-if="previewSlide.slide_type === 'pdf'" class="text-center p-8 bg-gray-50 rounded-lg w-full border border-gray-300">
                  <i class="fas fa-file-pdf text-4xl text-gray-400 mb-2"></i>
                  <p class="text-sm text-gray-600">PDF Document: {{ previewSlide.slide_content?.url }}</p>
                </div>

                <p v-if="previewSlide.slide_content?.caption" class="mt-2 text-sm text-gray-500 italic">{{ previewSlide.slide_content.caption }}</p>
              </div>

              <!-- Question Slide -->
              <div v-else-if="previewSlide.slide_type === 'question'" class="space-y-6">
                <div v-for="(question, qIdx) in (previewSlide.slide_content?.questions || [])" :key="qIdx" class="pl-4 border-l-4 border-blue-100">
                  <div class="font-medium text-gray-900 mb-3" v-html="question.text"></div>
                  
                  <div class="ml-4 space-y-2">
                    <div v-if="question.type === 'true_false'" class="flex gap-4">
                      <div class="flex items-center">
                        <div class="w-4 h-4 border border-gray-300 rounded-full mr-2"></div> True
                      </div>
                      <div class="flex items-center">
                        <div class="w-4 h-4 border border-gray-300 rounded-full mr-2"></div> False
                      </div>
                    </div>
                    
                    <div v-else-if="question.type === 'multiple_choice' || question.type === 'single_choice'" class="space-y-2">
                      <div v-for="option in question.options" :key="option.id" class="flex items-center">
                        <div class="w-4 h-4 border border-gray-300 mr-2" :class="question.type === 'single_choice' ? 'rounded-full' : 'rounded'"></div>
                        <span v-html="option.text"></span>
                      </div>
                    </div>
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
import { ref, onMounted, nextTick, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
  presentationId: {
    type: [Number, String],
    required: true
  }
});

const presentation = ref({});
const slides = ref([]);
const sel_slideIndex = ref(0);
const showPreviewModal = ref(false);

const fetchLesson = async () => {
  try {
    const response = await axios.get(route('lesson-presentation.show', { id: props.presentationId }));
    presentation.value = response.data;
    slides.value = response.data.slides || [];
  } catch (error) {
    console.error('Failed to load lesson:', error);
  }
};

const print_all = () => {
  print_sild_only.value = false;
  window.print();
};

const print_sild_only = ref(false);

// Computed property for preview slide
const previewSlide = computed(() => {
  return slides.value[sel_slideIndex.value];
});

// Show print preview in new window
const showPrintPreview = (index) => {
  sel_slideIndex.value = index;
  const slide = slides.value[index];
  
  if (!slide) return;
  
  // Create a new window
  const printWindow = window.open('', '_blank', 'width=800,height=600');
  
  if (!printWindow) {
    alert('Please allow popups to preview the slide');
    return;
  }
  
  // Build the HTML content for the preview window
  let slideContent = '';
  
  if (slide.slide_type === 'text') {
    slideContent = `<div class="prose max-w-none">${slide.slide_content?.text || ''}</div>`;
  } else if (['image', 'video', 'audio', 'pdf'].includes(slide.slide_type)) {
    const title = slide.slide_content?.title ? `<h3 class="text-lg font-medium mb-4">${slide.slide_content.title}</h3>` : '';
    const caption = slide.slide_content?.caption ? `<p class="mt-2 text-sm text-gray-500 italic">${slide.slide_content.caption}</p>` : '';
    
    if (slide.slide_type === 'image' && slide.slide_content?.url) {
      slideContent = `
        <div class="flex flex-col items-center">
          ${title}
          <img src="${slide.slide_content.url}" class="max-w-full h-auto rounded-lg shadow-sm" />
          ${caption}
        </div>
      `;
    } else if (slide.slide_type === 'video') {
      slideContent = `
        <div class="flex flex-col items-center">
          ${title}
          <div class="text-center p-8 bg-gray-50 rounded-lg w-full border border-gray-300">
            <i class="fas fa-video text-4xl text-gray-400 mb-2"></i>
            <p class="text-sm text-gray-600">Video: ${slide.slide_content?.url || ''}</p>
          </div>
          ${caption}
        </div>
      `;
    } else if (slide.slide_type === 'audio') {
      slideContent = `
        <div class="flex flex-col items-center">
          ${title}
          <div class="text-center p-8 bg-gray-50 rounded-lg w-full border border-gray-300">
            <i class="fas fa-volume-up text-4xl text-gray-400 mb-2"></i>
            <p class="text-sm text-gray-600">Audio: ${slide.slide_content?.url || ''}</p>
          </div>
          ${caption}
        </div>
      `;
    } else if (slide.slide_type === 'pdf') {
      slideContent = `
        <div class="flex flex-col items-center">
          ${title}
          <div class="text-center p-8 bg-gray-50 rounded-lg w-full border border-gray-300">
            <i class="fas fa-file-pdf text-4xl text-gray-400 mb-2"></i>
            <p class="text-sm text-gray-600">PDF Document: ${slide.slide_content?.url || ''}</p>
          </div>
          ${caption}
        </div>
      `;
    }
  } else if (slide.slide_type === 'question') {
    const questions = slide.slide_content?.questions || [];
    slideContent = '<div class="space-y-6">';
    
    questions.forEach((question, qIdx) => {
      slideContent += `
        <div class="pl-4 border-l-4 border-blue-100">
          <div class="font-medium text-gray-900 mb-3">${question.text || ''}</div>
          <div class="ml-4 space-y-2">
      `;
      
      if (question.type === 'true_false') {
        slideContent += `
          <div class="flex gap-4">
            <div class="flex items-center">
              <div class="w-4 h-4 border border-gray-300 rounded-full mr-2"></div> True
            </div>
            <div class="flex items-center">
              <div class="w-4 h-4 border border-gray-300 rounded-full mr-2"></div> False
            </div>
          </div>
        `;
      } else if (question.type === 'multiple_choice' || question.type === 'single_choice') {
        slideContent += '<div class="space-y-2">';
        (question.options || []).forEach(option => {
          const shape = question.type === 'single_choice' ? 'rounded-full' : 'rounded';
          slideContent += `
            <div class="flex items-center">
              <div class="w-4 h-4 border border-gray-300 mr-2 ${shape}"></div>
              <span>${option.text || ''}</span>
            </div>
          `;
        });
        slideContent += '</div>';
      }
      
      slideContent += `
          </div>
        </div>
      `;
    });
    
    slideContent += '</div>';
  }
  
  // Write the complete HTML to the new window
  printWindow.document.write(`
    <!DOCTYPE html>
    <html>
    <head>
      <title>Print Preview - Slide ${index + 1}</title>
      <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
      <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
      <style>
        @media print {
          body {
            margin: 0;
            padding: 20px;
          }
          .no-print {
            display: none !important;
          }
        }
        .prose {
          max-width: none;
        }
      </style>
    </head>
    <body class="bg-white p-8">
      <div class="max-w-4xl mx-auto">
        <div class="no-print mb-6 flex justify-between items-center border-b pb-4">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">Print Preview</h1>
            <p class="text-sm text-gray-500">Slide ${index + 1} of ${slides.value.length} - ${presentation.value.name || ''}</p>
          </div>
          <button 
            onclick="window.print()" 
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2"
          >
            <i class="fas fa-print"></i>
            Print
          </button>
        </div>
        
        <div class="slide-content">
          ${slideContent}
        </div>
      </div>
    </body>
    </html>
  `);
  
  printWindow.document.close();
};

const printSlide = async (slideIndex) => {
  print_sild_only.value = true;
  
  // Wait for Vue to update the DOM
  await nextTick();
  
  // Hide all slides except the one to print
  const allSlides = document.querySelectorAll('.slide-container');
  allSlides.forEach((slide, index) => {
    if (index !== slideIndex) {
      slide.style.display = 'none';
    }
  });
  
  // Wait a bit more to ensure styles are applied
  await new Promise(resolve => setTimeout(resolve, 50));
  
  // Print
  window.print();
  
  // Restore all slides and reset flag after printing
  setTimeout(() => {
    allSlides.forEach(slide => {
      slide.style.display = '';
    });
    print_sild_only.value = false;
  }, 1000);
};

onMounted(() => {
  fetchLesson();
});
</script>

<style>
@media print {
  @page {
    margin: 2cm;
  }
  body {
    background: white;
  }
}

@media print {
    .no-print {
        display: none !important;
    }
}
</style>
