<template>
  <Head :title="exam.title" />
  <div class="print-container q-pa-lg">
    <!-- Student/School Info Header -->
    <div class="row items-center justify-between q-mb-lg border-bottom-2 q-pb-md">
       <div class="text-left">
          <div class="text-h6 text-weight-bold">{{ student_info?.school_name }}</div>
       </div>
       <div class="text-right">
          <div class="text-subtitle1">Name: <span class="text-weight-bold border-bottom-dotted">{{ student_info?.name }}</span></div>
          <div class="text-subtitle1">Class: <span class="text-weight-bold border-bottom-dotted">{{ student_info?.classroom_name }}</span></div>
          <div class="text-subtitle1">Date: <span class="text-weight-bold border-bottom-dotted">&nbsp;</span></div>
       </div>
    </div>

    <!-- Header -->
    <div class="text-center q-mb-xl">
      <h1 class="text-h4 text-weight-bold q-mb-sm">{{ exam.title }}</h1>
      <div class="text-subtitle1 text-grey-8">
        Subject: {{ exam.subject?.name }} | Duration: {{ exam.duration_minutes }} mins | Marks: {{ exam.total_marks }}
      </div>
      <div v-if="exam.description" class="text-body2 q-mt-md text-grey-7" style="white-space: pre-wrap;">
        {{ exam.description }}
      </div>
    </div>

    <!-- Questions -->
    <div class="questions-list">
      <div v-for="(question, index) in exam.questions" :key="question.id" class="question-item q-mb-xl">
        
        <!-- Question Text -->
        <div class="row no-wrap items-start">
          <div class="q-mr-md text-weight-bold text-h6">{{ index + 1 }}.</div>
          <div class="col">
            <div class="text-body1 q-mb-md" v-html="renderMath(question.question_text)"></div>
            
            <!-- Options (MCQ / True False) -->
            <div v-if="['mcq', 'true_false'].includes(question.question_type)" class="q-pl-sm">
              <div 
                v-for="(text, key) in question.options" 
                :key="key"
                class="row items-center q-mb-sm"
              >
                <div class="print-checkbox q-mr-sm"></div>
                <div class="text-body2">
                  <span class="text-weight-medium q-mr-xs">{{ key }}.</span>
                  <span v-html="renderMath(text)"></span>
                </div>
              </div>
            </div>

            <!-- Short Answer Space -->
            <div v-if="question.question_type === 'short_answer'" class="q-mt-lg">
              <div class="print-line"></div>
              <div class="print-line"></div>
            </div>
            
             <!-- Long Answer Space -->
             <div v-if="question.question_type === 'long_answer'" class="q-mt-lg">
              <div class="print-line" v-for="n in 6" :key="n"></div>
            </div>

          </div>
          
          <!-- Marks -->
          <div class="q-ml-md text-caption text-grey-7">
            [{{ question.marks }} marks]
          </div>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <div class="print-footer text-center q-mt-xl text-grey-5 text-caption">
      Generate by MyClass2026 - {{ formatDate(new Date()) }}
    </div>

    <!-- Print Controls (Corrected: No .no-print class here, usually handled by media query but simple v-show works for screen) -->
    <div class="fixed-bottom-right q-pa-md print-hide">
      <q-btn fab icon="print" color="primary" @click="printPage" />
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { renderMath } from '@/Utils/katex';
import PrintLayout from '@/Layouts/PrintLayout.vue';

// Use dedicated print layout
defineOptions({
  layout: PrintLayout
});

const props = defineProps({
  exam: Object,
  student_info: Object
});

const formatDate = (date) => {
  return new Intl.DateTimeFormat('en-US', {
    dateStyle: 'medium', 
    timeStyle: 'short'
  }).format(date);
};

const printPage = () => {
  window.print();
};

onMounted(() => {
  // Auto-print when opened? Maybe let user decide.
  // setTimeout(() => window.print(), 500);
});
</script>

<style scoped>
.print-container {
  max-width: 800px;
  margin: 0 auto;
  background: white;
  min-height: 100vh;
}

.print-checkbox {
  width: 16px;
  height: 16px;
  border: 1px solid #000;
  border-radius: 50%;
  display: inline-block;
}

.print-line {
  border-bottom: 1px solid #ccc;
  height: 30px;
  width: 100%;
}

@media print {
  @page {
    margin: 1cm;
    size: auto;
  }

  .print-hide {
    display: none !important;
  }
  
  /* Reset all potential layout interference */
  html, body, #app, .print-container {
    width: 100% !important;
    height: auto !important;
    min-height: 0 !important;
    margin: 0 !important;
    padding: 0 !important;
    overflow: visible !important;
    display: block !important;
    position: static !important;
    float: none !important;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
  }
  
  /* Specific container fix */
  .print-container {
    padding: 0 !important;
  }

  /* Typography fixes - ensure black text */
  .text-h4, 
  .text-subtitle1, 
  .text-body1, 
  .text-body2, 
  .text-caption,
  .text-h6,
  .text-grey-5,
  .text-grey-7, 
  .text-grey-8 {
    color: black !important;
  }

  .border-bottom-dotted {
    border-bottom: 2px dotted #000;
    min-width: 150px;
    display: inline-block;
    padding-left: 10px;
    padding-right: 10px;
  }
  
  .border-bottom-2 {
     border-bottom: 2px solid #000;
  }

  .text-h4 {
    font-size: 1.5rem !important;
    margin-top: 0 !important;
    line-height: normal !important;
  }

  /* Spacing fixes */
  .q-mb-xl {
    margin-bottom: 24px !important;
  }

  .print-footer {
    margin-top: 2rem !important;
    padding-bottom: 0 !important;
  }

  /* Page break management */
  h1, h2, h3, h4, 
  .question-item {
    break-inside: avoid;
    page-break-inside: avoid;
  }

  .print-footer {
    margin-top: 2rem !important; /* Reduce margin */
    padding-bottom: 0 !important;
  }
}
</style>
