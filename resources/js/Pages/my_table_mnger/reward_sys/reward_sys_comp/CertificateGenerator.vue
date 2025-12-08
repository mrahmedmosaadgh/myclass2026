<template>
  <q-dialog v-model="isOpen" maximized transition-show="slide-up" transition-hide="slide-down">
    <q-card class="bg-gray-900 text-white flex flex-col print:bg-white print:text-black">
      <q-bar class="bg-gray-800 p-4 print:hidden">
        <div class="text-h6">🎓 Certificate Generator</div>
        <q-space />
        <q-btn dense flat icon="close" v-close-popup />
      </q-bar>

      <q-card-section class="flex-1 flex flex-col items-center justify-center p-4 overflow-auto print:p-0 print:overflow-visible">
        <!-- Controls -->
        <div class="flex gap-4 mb-6 w-full max-w-4xl justify-center print:hidden">
          <q-input v-model="certificateData.academicYear" label="Academic Year" dark outlined dense class="w-40" />
          <q-input v-model="certificateData.week" label="Week Number" dark outlined dense class="w-40" />
          <q-input v-model="certificateData.date" label="Date" dark outlined dense class="w-40" />
          <q-btn color="primary" icon="download" label="Download PDF" @click="generatePDF" :loading="generating" />
          <q-btn color="secondary" icon="print" label="Print" @click="printCertificate" />
        </div>

        <!-- Certificate Preview Area -->
        <div ref="certificateRef" class="certificate-container relative shadow-2xl border-4 border-gray-700 overflow-hidden print:shadow-none print:border-0" style="width: 100%; height: 100%; ">
          <!-- Background Image -->
          <img src="/images/certificate1.png" class="absolute inset-0 w-full h-full object-cover" />
          
          <!-- Overlay Content -->
          <div class="absolute inset-0 flex flex-col items-center justify-center text-black font-serif">
            
            <!-- Student Name -->
            <div class="absolute top-[42%] w-full text-center px-20">
              <h1 class="text-8xl font-bold text-gray-900 capitalize" style="font-family: 'Pinyon Script', cursive, serif;">
                {{ student?.firstName }} {{ student?.lastName }}
              </h1>
            </div>

            <!-- Details -->
            <div class="absolute bottom-[18%] left-[15%] text-2xl font-bold text-gray-700">
              {{ certificateData.academicYear }}
            </div>

            <div class="absolute bottom-[18%] right-[15%] text-2xl font-bold text-gray-700">
              {{ certificateData.date }}
            </div>

            <div class="absolute top-[25%] right-[15%] text-2xl font-bold text-gray-700">
              Week: {{ certificateData.week }}
            </div>

          </div>
        </div>
      </q-card-section>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { ref, computed } from 'vue'
import html2canvas from 'html2canvas'
import jsPDF from 'jspdf'
import { useQuasar } from 'quasar'

const $q = useQuasar()

const props = defineProps({
  modelValue: Boolean,
  student: Object,
  defaultDate: String
})

const emit = defineEmits(['update:modelValue'])

const isOpen = computed({
  get: () => props.modelValue,
  set: (val) => emit('update:modelValue', val)
})

const certificateData = ref({
  academicYear: '2024-2025',
  week: '1',
  date: props.defaultDate || new Date().toLocaleDateString()
})

const generating = ref(false)
const certificateRef = ref(null)

async function generatePDF() {
  generating.value = true
  try {
    const element = certificateRef.value
    
    if (!element) throw new Error('Certificate element not found')

    const canvas = await html2canvas(element, {
      scale: 2,
      useCORS: true,
      logging: false
    })

    const imgData = canvas.toDataURL('image/png')
    const pdf = new jsPDF({
      orientation: 'landscape',
      unit: 'px',
      format: [1123, 794]
    })

    pdf.addImage(imgData, 'PNG', 0, 0, 1123, 794)
    pdf.save(`${props.student.firstName}_Certificate.pdf`)
    
    $q.notify({ message: 'Certificate downloaded!', color: 'positive' })
  } catch (e) {
    console.error(e)
    $q.notify({ message: 'Failed to generate PDF', color: 'negative' })
  } finally {
    generating.value = false
  }
}

function printCertificate() {
  window.print()
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Pinyon+Script&display=swap');

/* Default scale for preview */
.certificate-container {
  transform: scale(0.8);
}

@media print {
  @page {
    size: A4 landscape;
    margin: 0;
  }
  
  body * {
    visibility: hidden;
  }
  
  .q-dialog, .q-dialog__inner, .q-card, .q-card__section {
    background: none !important;
    box-shadow: none !important;
    border: none !important;
    width: 100% !important;
    height: 100% !important;
    max-width: 100% !important;
    max-height: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
    position: absolute !important;
    top: 0 !important;
    left: 0 !important;
  }

  .certificate-container, .certificate-container * {
    visibility: visible;
  }

  .certificate-container {
    position: fixed;
    left: 0;
    top: 0;
    width: 100% !important;
    height: 100% !important;
    transform: none !important; /* Remove preview scale */
    margin: 0;
    padding: 0;
    border: none;
    box-shadow: none;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
  }
}
</style>
