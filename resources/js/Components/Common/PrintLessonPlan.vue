<template>
  <q-dialog v-model="isOpen" persistent maximized>
    <q-card>
      <!-- Header -->
      <q-card-section class="bg-primary text-white">
        <div class="row items-center">
          <q-icon name="print" size="32px" class="q-mr-sm" />
          <div class="text-h5">Print Lesson Plan</div>
          <q-space />
          <q-btn flat round dense icon="close" @click="close" />
        </div>
      </q-card-section>

      <!-- Content -->
      <q-card-section class="q-pa-md">
        <div class="row q-col-gutter-md">
          <!-- Left: Slide Selection -->
          <div class="col-12 col-md-5">
            <q-card flat bordered>
              <q-card-section class="bg-grey-2">
                <div class="text-h6">Select Slides to Print</div>
                <div class="text-caption text-grey-7">Choose which slides to include in the lesson plan</div>
              </q-card-section>

              <q-card-section>
                <!-- Select All/None -->
                <div class="row q-mb-md">
                  <q-btn flat dense color="primary" label="Select All" @click="selectAll" class="q-mr-sm" />
                  <q-btn flat dense color="negative" label="Deselect All" @click="deselectAll" />
                </div>

                <!-- Sections with Slides -->
                <q-scroll-area style="height: calc(100vh - 350px);">
                  <div v-for="section in sections" :key="section.id" class="q-mb-md">
                    <div class="row items-center q-mb-sm">
                      <q-checkbox
                        :model-value="isSectionFullySelected(section.id)"
                        @update:model-value="toggleSection(section.id)"
                        :label="section.title"
                        color="primary"
                        class="text-weight-bold"
                      />
                      <q-space />
                      <q-chip dense :style="{ background: section.bg, color: section.textColor }">
                        {{ getSectionSlides(section.id).length }} slides
                      </q-chip>
                    </div>

                    <div class="q-pl-lg">
                      <q-checkbox
                        v-for="(slide, idx) in getSectionSlides(section.id)"
                        :key="idx"
                        v-model="selectedSlides"
                        :val="slide"
                        :label="`Slide ${idx + 1}: ${getSlideLabel(slide)}`"
                        color="primary"
                        dense
                        class="q-mb-xs"
                      />
                    </div>
                  </div>
                </q-scroll-area>
              </q-card-section>
            </q-card>
          </div>

          <!-- Right: Preview -->
          <div class="col-12 col-md-7">
            <q-card flat bordered>
              <q-card-section class="bg-grey-2">
                <div class="row items-center">
                  <div class="text-h6">Preview</div>
                  <q-space />
                  <q-chip color="primary" text-color="white">
                    {{ selectedSlides.length }} slides selected
                  </q-chip>
                </div>
              </q-card-section>

              <q-card-section>
                <q-scroll-area style="height: calc(100vh - 350px);">
                  <div id="printable-lesson-plan" class="printable-content">
                    <!-- Header -->
                    <div class="print-header">
                      <div class="school-logo">
                        <img v-if="schoolLogo" :src="schoolLogo" alt="School Logo" style="max-height: 60px;" />
                      </div>
                      <div class="lesson-title">
                        <h1>{{ presentation.name || 'Lesson Plan' }}</h1>
                        <p class="lesson-description">{{ presentation.description }}</p>
                      </div>
                    </div>

                    <!-- Lesson Info -->
                    <div class="lesson-info">
                      <div class="info-row">
                        <div class="info-item">
                          <strong>Teacher:</strong> {{ teacherName }}
                        </div>
                        <div class="info-item">
                          <strong>Subject:</strong> {{ subjectName }}
                        </div>
                      </div>
                      <div class="info-row">
                        <div class="info-item">
                          <strong>Grade:</strong> {{ gradeName }}
                        </div>
                        <div class="info-item">
                          <strong>Date:</strong> {{ currentDate }}
                        </div>
                      </div>
                      <div class="info-row">
                        <div class="info-item">
                          <strong>Duration:</strong> {{ estimatedDuration }} minutes
                        </div>
                        <div class="info-item">
                          <strong>Total Slides:</strong> {{ selectedSlides.length }}
                        </div>
                      </div>
                    </div>

                    <!-- Slides by Section -->
                    <div v-for="section in sections" :key="section.id" class="section-block">
                      <template v-if="getSectionSelectedSlides(section.id).length > 0">
                        <div class="section-header" :style="{ borderLeftColor: section.borderColor }">
                          <span class="section-icon">{{ section.icon }}</span>
                          <h2>{{ section.title }}</h2>
                        </div>

                        <div v-for="(slide, idx) in getSectionSelectedSlides(section.id)" :key="idx" class="slide-block">
                          <div class="slide-number">Slide {{ idx + 1 }}</div>
                          <div class="slide-content">
                            <!-- Text Slide -->
                            <div v-if="slide.slide_type === 'text'" v-html="slide.slide_content?.text || 'No content'"></div>

                            <!-- Question Slide -->
                            <div v-else-if="slide.slide_type === 'question'" class="question-content">
                              <div v-for="(q, qIdx) in slide.slide_content?.questions || []" :key="qIdx" class="question-item">
                                <div class="question-text">
                                  <strong>Q{{ qIdx + 1 }}:</strong> {{ q.text }}
                                </div>
                                <div v-if="q.options" class="question-options">
                                  <div v-for="(opt, optIdx) in q.options" :key="optIdx" class="option-item">
                                    <span :class="{ 'correct-answer': isCorrectOption(q, opt.id) }">
                                      {{ String.fromCharCode(65 + optIdx) }}. {{ opt.text }}
                                      <q-icon v-if="isCorrectOption(q, opt.id)" name="check_circle" color="positive" size="sm" />
                                    </span>
                                  </div>
                                </div>
                                <div v-else-if="q.type === 'true_false'" class="question-answer">
                                  <strong>Answer:</strong> {{ q.correct_answer ? 'True' : 'False' }}
                                </div>
                                <div v-if="q.explanation" class="question-explanation">
                                  <em>Explanation: {{ q.explanation }}</em>
                                </div>
                              </div>
                            </div>

                            <!-- Other Slide Types -->
                            <div v-else class="slide-placeholder">
                              <q-icon :name="getSlideIcon(slide.slide_type)" size="md" color="grey" />
                              <div>{{ slide.slide_type.toUpperCase() }} Slide</div>
                            </div>
                          </div>
                        </div>
                      </template>
                    </div>

                    <!-- Footer -->
                    <div class="print-footer">
                      <div>Prepared by {{ teacherName }}</div>
                      <div>Printed on {{ currentDate }}</div>
                    </div>
                  </div>
                </q-scroll-area>
              </q-card-section>
            </q-card>
          </div>
        </div>
      </q-card-section>

      <!-- Actions -->
      <q-card-actions align="right" class="q-pa-md">
        <q-btn flat label="Cancel" @click="close" />
        <q-btn 
          unelevated 
          color="primary" 
          icon="print" 
          label="Print" 
          @click="printLessonPlan"
          :disable="selectedSlides.length === 0"
        />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useQuasar } from 'quasar';

const $q = useQuasar();

const props = defineProps({
  presentation: { type: Object, required: true },
  sections: { type: Array, required: true },
  slides: { type: Array, required: true },
  teacherName: { type: String, default: 'Teacher Name' },
  subjectName: { type: String, default: 'Subject' },
  gradeName: { type: String, default: 'Grade' },
  schoolLogo: { type: String, default: '' }
});

const emit = defineEmits(['close']);

const isOpen = ref(false);
const selectedSlides = ref([]);

const currentDate = computed(() => {
  return new Date().toLocaleDateString('en-US', { 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric' 
  });
});

const estimatedDuration = computed(() => {
  // Estimate 2 minutes per slide
  return selectedSlides.value.length * 2;
});

const getSectionSlides = (sectionId) => {
  return props.slides.filter(s => s.section === sectionId);
};

const getSectionSelectedSlides = (sectionId) => {
  return selectedSlides.value.filter(s => s.section === sectionId);
};

const isSectionFullySelected = (sectionId) => {
  const sectionSlides = getSectionSlides(sectionId);
  if (sectionSlides.length === 0) return false;
  return sectionSlides.every(slide => selectedSlides.value.includes(slide));
};

const toggleSection = (sectionId) => {
  const sectionSlides = getSectionSlides(sectionId);
  const isFullySelected = isSectionFullySelected(sectionId);
  
  if (isFullySelected) {
    // Deselect all slides in this section
    selectedSlides.value = selectedSlides.value.filter(s => s.section !== sectionId);
  } else {
    // Select all slides in this section
    sectionSlides.forEach(slide => {
      if (!selectedSlides.value.includes(slide)) {
        selectedSlides.value.push(slide);
      }
    });
  }
};

const selectAll = () => {
  selectedSlides.value = [...props.slides];
};

const deselectAll = () => {
  selectedSlides.value = [];
};

const getSlideLabel = (slide) => {
  if (slide.slide_type === 'text') {
    const text = slide.slide_content?.text || '';
    const plainText = text.replace(/<[^>]*>/g, '').trim();
    return plainText.substring(0, 50) + (plainText.length > 50 ? '...' : '');
  } else if (slide.slide_type === 'question') {
    const qCount = slide.slide_content?.questions?.length || 0;
    return `${qCount} question${qCount !== 1 ? 's' : ''}`;
  }
  return slide.slide_type;
};

const getSlideIcon = (type) => {
  const icons = {
    'text': 'description',
    'image': 'image',
    'video': 'videocam',
    'audio': 'audiotrack',
    'pdf': 'picture_as_pdf',
    'question': 'quiz',
    'drawing': 'brush'
  };
  return icons[type] || 'article';
};

const isCorrectOption = (question, optionId) => {
  if (question.type === 'single_choice') {
    return question.correct_answer === optionId;
  } else if (question.type === 'multiple_choice') {
    return Array.isArray(question.correct_answer) && question.correct_answer.includes(optionId);
  }
  return false;
};

const printLessonPlan = () => {
  const printContent = document.getElementById('printable-lesson-plan').innerHTML;
  const printWindow = window.open('', '_blank');
  
  printWindow.document.write(`
    <!DOCTYPE html>
    <html>
    <head>
      <title>${props.presentation.name || 'Lesson Plan'}</title>
      <style>
        @media print {
          @page { margin: 1cm; }
          body { margin: 0; }
        }
        
        body {
          font-family: 'Arial', sans-serif;
          line-height: 1.6;
          color: #333;
          max-width: 210mm;
          margin: 0 auto;
          padding: 20px;
        }
        
        .print-header {
          text-align: center;
          border-bottom: 3px solid #1976d2;
          padding-bottom: 20px;
          margin-bottom: 30px;
        }
        
        .print-header h1 {
          margin: 10px 0;
          color: #1976d2;
          font-size: 28px;
        }
        
        .lesson-description {
          color: #666;
          font-size: 14px;
        }
        
        .lesson-info {
          background: #f5f5f5;
          padding: 15px;
          border-radius: 8px;
          margin-bottom: 30px;
        }
        
        .info-row {
          display: flex;
          justify-content: space-between;
          margin-bottom: 8px;
        }
        
        .info-item {
          flex: 1;
        }
        
        .section-block {
          page-break-inside: avoid;
          margin-bottom: 30px;
        }
        
        .section-header {
          background: #1976d2;
          color: white;
          padding: 12px 20px;
          border-left: 5px solid;
          margin-bottom: 15px;
          display: flex;
          align-items: center;
          gap: 10px;
        }
        
        .section-header h2 {
          margin: 0;
          font-size: 20px;
        }
        
        .section-icon {
          font-size: 24px;
        }
        
        .slide-block {
          margin-bottom: 20px;
          padding: 15px;
          border: 1px solid #ddd;
          border-radius: 8px;
          background: white;
          page-break-inside: avoid;
        }
        
        .slide-number {
          font-weight: bold;
          color: #1976d2;
          margin-bottom: 10px;
          font-size: 14px;
        }
        
        .slide-content {
          font-size: 14px;
        }
        
        .question-item {
          margin-bottom: 15px;
          padding: 10px;
          background: #f9f9f9;
          border-radius: 4px;
        }
        
        .question-text {
          font-weight: bold;
          margin-bottom: 8px;
        }
        
        .question-options {
          margin-left: 20px;
        }
        
        .option-item {
          margin: 5px 0;
        }
        
        .correct-answer {
          color: #4caf50;
          font-weight: bold;
        }
        
        .question-explanation {
          margin-top: 8px;
          padding: 8px;
          background: #e3f2fd;
          border-left: 3px solid #2196f3;
          font-size: 13px;
        }
        
        .slide-placeholder {
          text-align: center;
          padding: 20px;
          color: #999;
        }
        
        .print-footer {
          margin-top: 40px;
          padding-top: 20px;
          border-top: 2px solid #ddd;
          text-align: center;
          font-size: 12px;
          color: #666;
        }
      </style>
    </head>
    <body>
      ${printContent}
    </body>
    </html>
  `);
  
  printWindow.document.close();
  printWindow.focus();
  
  setTimeout(() => {
    printWindow.print();
  }, 500);
};

const open = () => {
  isOpen.value = true;
  // Select all slides by default
  selectedSlides.value = [...props.slides];
};

const close = () => {
  isOpen.value = false;
  emit('close');
};

defineExpose({ open });
</script>

<style scoped>
.printable-content {
  background: white;
  padding: 20px;
}

.print-header {
  text-align: center;
  border-bottom: 3px solid #1976d2;
  padding-bottom: 20px;
  margin-bottom: 30px;
}

.lesson-title h1 {
  margin: 10px 0;
  color: #1976d2;
}

.lesson-description {
  color: #666;
}

.lesson-info {
  background: #f5f5f5;
  padding: 15px;
  border-radius: 8px;
  margin-bottom: 30px;
}

.info-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 8px;
}

.section-header {
  background: #1976d2;
  color: white;
  padding: 12px 20px;
  border-left: 5px solid;
  margin-bottom: 15px;
  display: flex;
  align-items: center;
  gap: 10px;
}

.section-header h2 {
  margin: 0;
  font-size: 20px;
}

.slide-block {
  margin-bottom: 20px;
  padding: 15px;
  border: 1px solid #ddd;
  border-radius: 8px;
}

.slide-number {
  font-weight: bold;
  color: #1976d2;
  margin-bottom: 10px;
}

.question-item {
  margin-bottom: 15px;
  padding: 10px;
  background: #f9f9f9;
  border-radius: 4px;
}

.question-text {
  font-weight: bold;
  margin-bottom: 8px;
}

.question-options {
  margin-left: 20px;
}

.correct-answer {
  color: #4caf50;
  font-weight: bold;
}

.question-explanation {
  margin-top: 8px;
  padding: 8px;
  background: #e3f2fd;
  border-left: 3px solid #2196f3;
}

.slide-placeholder {
  text-align: center;
  padding: 20px;
  color: #999;
}

.print-footer {
  margin-top: 40px;
  padding-top: 20px;
  border-top: 2px solid #ddd;
  text-align: center;
  font-size: 12px;
  color: #666;
}
</style>
