<template>
  <q-dialog v-model="isOpen" maximized transition-show="slide-up" transition-hide="slide-down">
    <q-card class="bg-gray-900 text-white flex flex-col print:bg-white print:text-black">
      
      <!-- Toolbar -->
      <q-bar class="bg-gray-800 p-4 print:hidden border-b border-gray-700">
        <div class="text-h6 flex items-center gap-2">
          🎓 Certificate Generator
        </div>
        <q-space />
        <q-btn dense flat icon="close" v-close-popup />
      </q-bar>

      <div class="flex-1 flex flex-col lg:flex-row overflow-hidden print:block print:overflow-visible">
        
        <!-- Controls Panel (Left Side on Desktop) -->
        <div class="w-full lg:w-96 bg-gray-800 p-6 overflow-y-auto border-r border-gray-700 flex flex-col gap-6 print:hidden shadow-xl z-20">
          
          <!-- Template Selection -->
          <div>
            <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Select Template</div>
            <div class="grid grid-cols-2 gap-3">
              <div 
                v-for="t in templates" 
                :key="t.id"
                class="cursor-pointer rounded-lg overflow-hidden transition-all duration-300 relative group"
                :class="selectedTemplate.id === t.id ? 'ring-2 ring-primary shadow-lg scale-[1.02]' : 'opacity-70 hover:opacity-100 hover:scale-[1.02]'"
                @click="selectedTemplate = t"
              >
                <img :src="t.image" class="w-full h-24 object-cover" />
                <div class="absolute inset-0 bg-black/20 group-hover:bg-transparent transition-colors"></div>
                <div v-if="selectedTemplate.id === t.id" class="absolute bottom-1 right-1 bg-primary text-white text-xs px-1.5 py-0.5 rounded-full shadow-sm">
                  <q-icon name="check" size="xs" />
                </div>
              </div>
            </div>
          </div>

          <q-separator dark />

          <!-- Form Inputs -->
          <div class="space-y-4">
             <div class="flex items-center justify-between">
                 <div class="text-xs font-bold text-gray-400 uppercase tracking-wider">Certificate Details</div>
                 <q-toggle v-model="isEditMode" :label="$t('rewardSys.certificate.editLayout')" dense color="secondary" class="text-xs" />
             </div>
             
             <!-- Edit Mode Controls -->
             <div v-if="isEditMode" class="bg-gray-700 p-3 rounded-lg space-y-3">
                 <div class="text-xs text-yellow-500 font-bold mb-1">
                    <q-icon name="warning" class="mr-1"/> {{ $t('rewardSys.certificate.editModeActive') }}
                 </div>
                 <div class="text-xs text-gray-300 mb-2">{{ $t('rewardSys.certificate.dragInstruction') }}</div>
                 
                 <!-- Logo Size Slider -->
                 <div>
                    <div class="text-xs text-gray-400 mb-1">{{ $t('rewardSys.certificate.logoHeight') }}</div>
                    <q-slider v-model="currentStyles.logo.height" :min="40" :max="200" label :label-value="currentStyles.logo.height + 'px'" color="secondary" />
                 </div>

                 <!-- Reset Button -->
                 <q-btn 
                    outline 
                    dense 
                    color="warning" 
                    icon="restore" 
                    :label="$t('rewardSys.certificate.resetLayout')" 
                    size="sm" 
                    class="w-full"
                    @click="resetSettings"
                 />
             </div>

             <q-input v-model="certificateData.academicYear" label="Academic Year" dark outlined dense bg-color="gray-900" color="white" label-color="grey-4">
               <template v-slot:prepend><q-icon name="event" /></template>
             </q-input>
             
             <div class="grid grid-cols-2 gap-3">
                <q-input v-model="certificateData.week" label="Week" dark outlined dense bg-color="gray-900" color="white" label-color="grey-4" />
                <q-input v-model="certificateData.date" label="Date" dark outlined dense bg-color="gray-900" color="white" label-color="grey-4" />
             </div>

             <q-input 
               v-model="certificateData.message" 
               label="Custom Message" 
               dark outlined dense 
               autogrow 
               bg-color="gray-900" color="white" label-color="grey-4"
               hint="Short appreciation text"
             />
          </div>

          <q-space class="hidden lg:block" />

          <!-- Actions -->
          <div class="grid grid-cols-1 gap-3 mt-auto">
            <q-btn 
              color="primary" 
              icon="download" 
              label="Download PDF" 
              class="py-3 font-bold shadow-lg"
              @click="generatePDF" 
              :loading="generating" 
            />
            <q-btn 
              color="white" 
              text-color="black"
              icon="print" 
              label="Print Certificate" 
              class="py-2"
              @click="printCertificate" 
            />
          </div>
        </div>

        <!-- Preview Area (Right/Center) -->
        <div class="flex-1 bg-gray-900 relative p-4 lg:p-8 overflow-auto print:p-0 print:block flex flex-col items-center">
          
          <!-- Bulk Mode Navigation -->
          <div v-if="isBulkMode" class="w-full max-w-4xl mb-4 print:hidden">
            <div class="bg-gray-800 rounded-lg p-3 flex items-center justify-between">
              <q-btn 
                flat 
                dense 
                icon="chevron_left" 
                color="yellow-500"
                @click="currentStudentIndex = Math.max(0, currentStudentIndex - 1)"
                :disable="currentStudentIndex === 0"
              />
              <div class="text-yellow-400 font-bold">
                Student {{ currentStudentIndex + 1 }} of {{ students.length }}
                <span class="text-white ml-2">{{ currentStudent?.firstName }} {{ currentStudent?.lastName }}</span>
              </div>
              <q-btn 
                flat 
                dense 
                icon="chevron_right" 
                color="yellow-500"
                @click="currentStudentIndex = Math.min(students.length - 1, currentStudentIndex + 1)"
                :disable="currentStudentIndex === students.length - 1"
              />
            </div>
          </div>

          <div class="text-center absolute top-2 left-0 right-0 text-gray-500 text-sm italic lg:hidden print:hidden z-10 pointer-events-none">
            Scroll to view full certificate
          </div>

          <!-- Certificate Container -->
          <div 
             ref="certificateRef" 
             class="certificate-container relative shadow-2xl overflow-hidden bg-white print:shadow-none print:m-0 shrink-0"
             :style="{ 
                width: '1123px', 
                height: '794px',
                transform: `scale(${previewScale})`,
                transformOrigin: 'top center'
             }"
          >
            <!-- Background Image -->
            <img :src="selectedTemplate.image" class="absolute inset-0 w-full h-full object-cover select-none pointer-events-none" />
            
            <!-- School Logo -->
            <img 
              v-if="schoolLogo" 
              :src="schoolLogo" 
              class="absolute object-contain z-30"
              :class="{'cursor-move hover:outline-dashed hover:outline-2 hover:outline-blue-500': isEditMode}"
              :style="{
                  ...currentStyles.logo,
                  height: typeof currentStyles.logo.height === 'number' ? currentStyles.logo.height + 'px' : currentStyles.logo.height
              }"
              @mousedown="startDrag($event, 'logo')"
            />

            <!-- Overlay Content -->
            <div class="absolute inset-0 text-black font-serif z-20 pointer-events-none">
              
              <!-- Student Name -->
              <div 
                class="absolute pointer-events-auto" 
                :class="{'cursor-move hover:outline-dashed hover:outline-2 hover:outline-blue-500': isEditMode}"
                :style="currentStyles.name"
                @mousedown="startDrag($event, 'name')"
              >
                <h1 class="font-bold capitalize leading-none select-none" 
                    :style="{ 
                      fontFamily: `'Pinyon Script', cursive, serif`, 
                      color: currentStyles.name.color || '#111827',
                      fontSize: '5rem'
                    }">
                  {{ currentStudent?.firstName }} {{ currentStudent?.lastName }}
                </h1>
              </div>

              <!-- Message -->
              <div 
                class="absolute font-medium italic pointer-events-auto select-none" 
                :class="{'cursor-move hover:outline-dashed hover:outline-2 hover:outline-blue-500': isEditMode}"
                :style="currentStyles.message"
                @mousedown="startDrag($event, 'message')"
              >
                "{{ certificateData.message }}"
              </div>

              <!-- Details -->
              <div 
                class="absolute font-bold pointer-events-auto select-none" 
                :class="{'cursor-move hover:outline-dashed hover:outline-2 hover:outline-blue-500': isEditMode}"
                :style="currentStyles.year"
                @mousedown="startDrag($event, 'year')"
              >
                {{ certificateData.academicYear }}
              </div>

              <div 
                class="absolute font-bold pointer-events-auto select-none" 
                :class="{'cursor-move hover:outline-dashed hover:outline-2 hover:outline-blue-500': isEditMode}"
                :style="currentStyles.date"
                @mousedown="startDrag($event, 'date')"
              >
                {{ certificateData.date }}
              </div>

              <div 
                class="absolute font-bold pointer-events-auto select-none" 
                :class="{'cursor-move hover:outline-dashed hover:outline-2 hover:outline-blue-500': isEditMode}"
                :style="currentStyles.week"
                 @mousedown="startDrag($event, 'week')"
              >
                Week: {{ certificateData.week }}
              </div>

            </div>
          </div>
        </div>
      </div>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue'
import html2canvas from 'html2canvas'
import jsPDF from 'jspdf'
import { useQuasar } from 'quasar'

const $q = useQuasar()

const props = defineProps({
  modelValue: Boolean,
  student: Object,
  students: { type: Array, default: () => [] },
  defaultDate: String,
  schoolLogo: String
})

const emit = defineEmits(['update:modelValue'])

const isOpen = computed({
  get: () => props.modelValue,
  set: (val) => emit('update:modelValue', val)
})

// Determine if in bulk mode
const isBulkMode = computed(() => props.students && props.students.length > 0)

// Current student for preview (single mode uses prop, bulk mode uses index)
const currentStudentIndex = ref(0)
const currentStudent = computed(() => {
  if (isBulkMode.value) {
    return props.students[currentStudentIndex.value] || props.students[0]
  }
  return props.student
})

const certificateData = ref({
  academicYear: '2024-2025',
  week: '1',
  date: props.defaultDate || new Date().toLocaleDateString(),
  message: 'For your outstanding performance and excellent behavior.'
})

// Default Templates
const defaultTemplates = [
  {
    id: 1,
    name: 'Classic Gold',
    image: '/images/certificate1.png',
    styles: {
      name: { top: '301px', left: '0', right: '0', width: '100%', textAlign: 'center', color: '#111827' },
      message: { top: '436px', left: '112px', right: '112px', width: '899px', textAlign: 'center', color: '#4b5563', fontSize: '1.5rem' },
      year: { top: '635px', left: '168px', fontSize: '1.5rem', color: '#374151' },
      date: { top: '635px', left: '800px', fontSize: '1.5rem', color: '#374151' },
      week: { top: '198px', left: '800px', fontSize: '1.5rem', color: '#374151' },
      logo: { top: '63px', left: '460px', height: '80px', opacity: '0.9' }
    }
  },
  {
    id: 2,
    name: 'Modern Blue',
    image: '/images/certificate2.png',
    styles: {
      name: { top: '333px', left: '0', width: '80%', textAlign: 'center', color: '#1e3a8a' },
      message: { top: '476px', left: '112px', width: '65%', textAlign: 'center', color: '#1e3a8a', fontSize: '1.25rem' },
      year: { top: '680px', left: '471px', color: '#1e3a8a', fontSize: '1.25rem' }, 
      date: { top: '680px', left: '112px', color: '#1e3a8a', fontSize: '1.25rem' }, 
      week: { top: '238px', left: '101px', color: '#1e3a8a', fontSize: '1.25rem' },
      logo: { top: '40px', left: '56px', height: '90px' }
    }
  }
]

const templates = ref(defaultTemplates)
const selectedTemplate = ref(templates.value[0])
const currentStyles = ref(JSON.parse(JSON.stringify(templates.value[0].styles)))
const isEditMode = ref(false)

// Watch for template change to load styles
watch(selectedTemplate, (newTemplate) => {
    loadStyles(newTemplate.id)
})

function loadStyles(templateId) {
    const saved = localStorage.getItem(`cert_styles_${templateId}`)
    if (saved) {
        currentStyles.value = JSON.parse(saved)
    } else {
        // Find default styles
        const t = defaultTemplates.find(t => t.id === templateId)
        if (t) currentStyles.value = JSON.parse(JSON.stringify(t.styles))
    }
}

function saveSettings() {
    localStorage.setItem(`cert_styles_${selectedTemplate.value.id}`, JSON.stringify(currentStyles.value))
    $q.notify({ message: 'Layout saved!', color: 'positive', position: 'top', timeout: 1000 })
}

function resetSettings() {
    const t = defaultTemplates.find(t => t.id === selectedTemplate.value.id)
    if (t) {
        currentStyles.value = JSON.parse(JSON.stringify(t.styles))
        saveSettings()
    }
}

// Drag Logic
const dragState = ref({
    active: false,
    element: null,
    startX: 0,
    startY: 0,
    initialTop: 0,
    initialLeft: 0
})

function startDrag(event, elementKey) {
    if (!isEditMode.value) return
    event.preventDefault()
    
    // Check if clicking resize handles (if we add them later), otherwise drag
    dragState.value.active = true
    dragState.value.element = elementKey
    dragState.value.startX = event.clientX
    dragState.value.startY = event.clientY
    
    const style = currentStyles.value[elementKey]
    // Parse 'px' if present, otherwise assume 0
    dragState.value.initialTop = parseInt(style.top) || 0
    dragState.value.initialLeft = parseInt(style.left) || 0
    
    window.addEventListener('mousemove', onDrag)
    window.addEventListener('mouseup', stopDrag)
}

function onDrag(event) {
    if (!dragState.value.active) return
    
    // Scale delta by inverse of preview scale
    const deltaX = (event.clientX - dragState.value.startX) / previewScale.value
    const deltaY = (event.clientY - dragState.value.startY) / previewScale.value
    
    const key = dragState.value.element
    if (currentStyles.value[key]) {
        currentStyles.value[key].top = `${dragState.value.initialTop + deltaY}px`
        currentStyles.value[key].left = `${dragState.value.initialLeft + deltaX}px`
        // Clear right/bottom if set to avoid conflicts
        delete currentStyles.value[key].right
        delete currentStyles.value[key].bottom
    }
}

function stopDrag() {
    dragState.value.active = false
    window.removeEventListener('mousemove', onDrag)
    window.removeEventListener('mouseup', stopDrag)
    saveSettings() // Auto-save on drop
}


const generating = ref(false)
const certificateRef = ref(null)

// Preview Scale (Fixed for now, or user adjustable in future)
const previewScale = ref(1.0)
// Removed responsive scaling logic as requested by user

onMounted(() => {
    // Initial load
    loadStyles(selectedTemplate.value.id)
})

onUnmounted(() => {
    window.removeEventListener('mousemove', onDrag)
    window.removeEventListener('mouseup', stopDrag)
})

async function generatePDF() {
  generating.value = true
  isEditMode.value = false // Ensure edit mode is off
  
  try {
    const element = certificateRef.value
    if (!element) throw new Error('Certificate element not found')

    const pdf = new jsPDF({
      orientation: 'landscape',
      unit: 'px',
      format: [1123, 794]
    })

    const studentsToProcess = isBulkMode.value ? props.students : [props.student]
    
    for (let i = 0; i < studentsToProcess.length; i++) {
      // Update progress notification
      if (isBulkMode.value) {
        $q.notify({ 
          message: `Generating certificate ${i + 1} of ${studentsToProcess.length}...`, 
          color: 'info',
          position: 'top',
          timeout: 500
        })
      }

      // Set current student for preview
      currentStudentIndex.value = i
      
      // Wait for DOM update
      await nextTick()
      await new Promise(resolve => setTimeout(resolve, 100))

      // Temporarily reset scale
      const originalTransform = element.style.transform
      element.style.transform = 'none'
      
      const canvas = await html2canvas(element, {
        scale: 2,
        useCORS: true,
        logging: false,
        allowTaint: true 
      })

      element.style.transform = originalTransform

      const imgData = canvas.toDataURL('image/png')
      
      // Add page (except for first iteration)
      if (i > 0) {
        pdf.addPage()
      }
      
      pdf.addImage(imgData, 'PNG', 0, 0, 1123, 794)
    }

    // Save PDF
    const filename = isBulkMode.value 
      ? `Certificates_${studentsToProcess.length}_Students.pdf`
      : `${props.student.firstName}_Certificate.pdf`
    
    pdf.save(filename)
    
    $q.notify({ 
      message: isBulkMode.value 
        ? `${studentsToProcess.length} certificates downloaded!` 
        : 'Certificate downloaded!', 
      color: 'positive' 
    })
  } catch (e) {
    console.error(e)
    $q.notify({ message: 'Failed to generate PDF', color: 'negative' })
  } finally {
    generating.value = false
    currentStudentIndex.value = 0
  }
}

function printCertificate() {
  isEditMode.value = false
  window.print()
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Pinyon+Script&display=swap');

.certificate-container {
  width: 1123px; /* A4 Landscape width in pixels at 96 DPI */
  height: 794px; /* A4 Landscape height */
  transform-origin: center center;
  transition: transform 0.3s ease;
}

/* Custom scrollbar for controls */
::-webkit-scrollbar {
  width: 6px;
}
::-webkit-scrollbar-track {
  background: #1f2937; 
}
::-webkit-scrollbar-thumb {
  background: #4b5563; 
  border-radius: 3px;
}
::-webkit-scrollbar-thumb:hover {
  background: #6b7280; 
}

@media print {
  @page {
    size: A4 landscape;
    margin: 0;
  }
  
  body * {
    visibility: hidden;
  }
  
  .q-dialog, .q-dialog__inner, .q-card {
    position: static !important;
    background: none !important;
    box-shadow: none !important;
    border: none !important;
    width: 100% !important;
    height: 100% !important;
    overflow: visible !important;
    display: block !important;
  }

  .certificate-container {
    visibility: visible !important;
    position: fixed;
    left: 0;
    top: 0;
    width: 100% !important;
    height: 100% !important;
    margin: 0;
    padding: 0;
    transform: none !important;
    z-index: 9999;
  }
  
  /* Hide specific elements explicitly */
  .certificate-container * {
    visibility: visible !important;
  }
}
</style>
