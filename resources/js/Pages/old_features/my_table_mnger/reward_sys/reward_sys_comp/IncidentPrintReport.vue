<template>
  <div class="print-report">
    <!-- Screen Actions and Filters -->
    <div class="no-print mb-4 space-y-4">
      <!-- Filters -->
      <q-card class="shadow-sm">
        <q-card-section class="bg-blue-50">
          <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-3 flex-1">
              <q-icon name="filter_list" size="sm" color="primary" />
              <span class="font-bold text-gray-700">{{ language === 'en' ? 'Filters:' : 'التصفية:' }}</span>
              
              <!-- Show Students Filter -->
              <q-select
                v-model="showStudentsFilter"
                :options="[
                  { label: language === 'en' ? 'All Students' : 'جميع الطلاب', value: 'all' },
                  { label: language === 'en' ? 'With Incidents Only' : 'الذين لديهم حوادث فقط', value: 'with-incidents' },
                  { label: language === 'en' ? 'Good Behavior Only' : 'السلوك الجيد فقط', value: 'no-incidents' }
                ]"
                option-value="value"
                option-label="label"
                outlined
                dense
                emit-value
                map-options
                class="w-64"
              >
                <template v-slot:prepend>
                  <q-icon name="people" />
                </template>
              </q-select>

              <!-- Severity Filter -->
              <q-select
                v-model="severityFilter"
                :options="[
                  { label: language === 'en' ? 'All Severities' : 'جميع المستويات', value: 'all' },
                  { label: language === 'en' ? 'Minor Only' : 'بسيط فقط', value: 'minor' },
                  { label: language === 'en' ? 'Major Only' : 'كبير فقط', value: 'major' }
                ]"
                option-value="value"
                option-label="label"
                outlined
                dense
                emit-value
                map-options
                class="w-64"
              >
                <template v-slot:prepend>
                  <q-icon name="warning" />
                </template>
              </q-select>
            </div>

            <div class="flex gap-2">
              <q-btn
                color="primary"
                icon="print"
                :label="language === 'en' ? 'Print' : 'طباعة'"
                @click="printReport"
              />
              <q-btn
                color="secondary"
                icon="open_in_new"
                :label="language === 'en' ? 'Open in New Window' : 'فتح في نافذة جديدة'"
                @click="openInNewWindow"
              />
            </div>
          </div>
        </q-card-section>
      </q-card>

      <!-- Filter Results Summary -->
      <div class="bg-gray-50 p-3 rounded-lg border border-gray-200">
        <div class="flex items-center gap-4 text-sm">
          <span class="font-semibold text-gray-700">
            {{ language === 'en' ? 'Showing:' : 'عرض:' }}
          </span>
          <span class="text-blue-600">
            {{ filteredStudents.length }} {{ language === 'en' ? 'students' : 'طالب' }}
          </span>
          <span class="text-gray-400">|</span>
          <span class="text-orange-600">
            {{ filteredTotalIncidents }} {{ language === 'en' ? 'incidents' : 'حادثة' }}
          </span>
        </div>
      </div>
    </div>

    <!-- Printable Content -->
    <div class="printable-content bg-white p-8" ref="printContent" id="printable-report">
      <!-- Header -->
      <div class="text-center mb-8 border-b-2 border-gray-300 pb-4">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">
          {{ language === 'en' ? 'Behavior Incident Report' : 'تقرير حوادث السلوك' }}
        </h1>
        <p class="text-gray-600">
          {{ language === 'en' ? 'Date' : 'التاريخ' }}: {{ formatDate(date) }}
        </p>
        <p class="text-gray-600">
          {{ language === 'en' ? 'Classroom ID' : 'رقم الفصل' }}: {{ classroomId }}
        </p>
      </div>

      <!-- Summary Statistics -->
      <div class="mb-8">
        <h2 class="text-xl font-bold text-gray-800 mb-4">
          {{ language === 'en' ? 'Summary' : 'ملخص' }}
        </h2>
        <div class="grid grid-cols-4 gap-4">
          <div class="border border-gray-300 p-4 text-center">
            <div class="text-3xl font-bold text-blue-600">{{ filteredTotalIncidents }}</div>
            <div class="text-sm text-gray-600">
              {{ language === 'en' ? 'Total Incidents' : 'إجمالي الحوادث' }}
            </div>
          </div>
          <div class="border border-gray-300 p-4 text-center">
            <div class="text-3xl font-bold text-yellow-600">{{ minorCount }}</div>
            <div class="text-sm text-gray-600">
              {{ language === 'en' ? 'Minor' : 'بسيط' }}
            </div>
          </div>
          <div class="border border-gray-300 p-4 text-center">
            <div class="text-3xl font-bold text-red-600">{{ majorCount }}</div>
            <div class="text-sm text-gray-600">
              {{ language === 'en' ? 'Major' : 'كبير' }}
            </div>
          </div>
          <div class="border border-gray-300 p-4 text-center">
            <div class="text-3xl font-bold text-purple-600">{{ totalPoints }}</div>
            <div class="text-sm text-gray-600">
              {{ language === 'en' ? 'Points Deducted' : 'النقاط المخصومة' }}
            </div>
          </div>
        </div>
      </div>

      <!-- Students Summary Table -->
      <div class="mb-8">
        <h2 class="text-xl font-bold text-gray-800 mb-4">
          {{ language === 'en' ? 'Students Summary' : 'ملخص الطلاب' }}
        </h2>
        <table class="w-full border-collapse border border-gray-300">
          <thead>
            <tr class="bg-gray-100">
              <th class="border border-gray-300 p-2 text-left">
                {{ language === 'en' ? '#' : 'الرقم' }}
              </th>
              <th class="border border-gray-300 p-2 text-left">
                {{ language === 'en' ? 'Student Name' : 'اسم الطالب' }}
              </th>
              <th class="border border-gray-300 p-2 text-center">
                {{ language === 'en' ? 'Grade' : 'الصف' }}
              </th>
              <th class="border border-gray-300 p-2 text-center">
                {{ language === 'en' ? 'Total' : 'المجموع' }}
              </th>
              <th class="border border-gray-300 p-2 text-center">
                {{ language === 'en' ? 'Minor' : 'بسيط' }}
              </th>
              <th class="border border-gray-300 p-2 text-center">
                {{ language === 'en' ? 'Major' : 'كبير' }}
              </th>
              <th class="border border-gray-300 p-2 text-center">
                {{ language === 'en' ? 'Points' : 'النقاط' }}
              </th>
              <th class="border border-gray-300 p-2 text-center">
                {{ language === 'en' ? 'Status' : 'الحالة' }}
              </th>
            </tr>
          </thead>
          <tbody>
            <tr 
              v-for="(studentData, index) in filteredStudents" 
              :key="studentData.student.id"
              :class="studentData.incidents.length === 0 ? 'bg-green-50' : index % 2 === 0 ? 'bg-white' : 'bg-gray-50'"
            >
              <td class="border border-gray-300 p-2 text-center font-semibold">
                {{ index + 1 }}
              </td>
              <td class="border border-gray-300 p-2">
                <span class="font-semibold">{{ studentData.student.name }}</span>
              </td>
              <td class="border border-gray-300 p-2 text-center">
                {{ studentData.student.grade || 'N/A' }}
              </td>
              <td class="border border-gray-300 p-2 text-center">
                <span 
                  class="px-2 py-1 rounded font-bold"
                  :class="studentData.summary.total > 0 ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'"
                >
                  {{ studentData.summary.total }}
                </span>
              </td>
              <td class="border border-gray-300 p-2 text-center">
                <span v-if="studentData.summary.minor > 0" class="text-yellow-700 font-semibold">
                  {{ studentData.summary.minor }}
                </span>
                <span v-else class="text-gray-400">-</span>
              </td>
              <td class="border border-gray-300 p-2 text-center">
                <span v-if="studentData.summary.major > 0" class="text-red-700 font-semibold">
                  {{ studentData.summary.major }}
                </span>
                <span v-else class="text-gray-400">-</span>
              </td>
              <td class="border border-gray-300 p-2 text-center">
                <span 
                  v-if="studentData.summary.points > 0"
                  class="px-2 py-1 rounded font-bold bg-red-100 text-red-800"
                >
                  -{{ studentData.summary.points }}
                </span>
                <span v-else class="text-green-600 font-semibold">0</span>
              </td>
              <td class="border border-gray-300 p-2 text-center">
                <span 
                  v-if="studentData.incidents.length === 0"
                  class="px-2 py-1 rounded text-xs font-semibold bg-green-200 text-green-900"
                >
                  {{ language === 'en' ? '✓ Excellent' : '✓ ممتاز' }}
                </span>
                <span 
                  v-else-if="studentData.summary.major > 0"
                  class="px-2 py-1 rounded text-xs font-semibold bg-red-200 text-red-900"
                >
                  {{ language === 'en' ? '⚠ Needs Attention' : '⚠ يحتاج انتباه' }}
                </span>
                <span 
                  v-else
                  class="px-2 py-1 rounded text-xs font-semibold bg-yellow-200 text-yellow-900"
                >
                  {{ language === 'en' ? '⚡ Monitor' : '⚡ مراقبة' }}
                </span>
              </td>
            </tr>
          </tbody>
          <tfoot>
            <tr class="bg-gray-200 font-bold">
              <td colspan="3" class="border border-gray-300 p-2 text-right">
                {{ language === 'en' ? 'TOTAL:' : 'المجموع:' }}
              </td>
              <td class="border border-gray-300 p-2 text-center">
                {{ filteredTotalIncidents }}
              </td>
              <td class="border border-gray-300 p-2 text-center">
                {{ minorCount }}
              </td>
              <td class="border border-gray-300 p-2 text-center">
                {{ majorCount }}
              </td>
              <td class="border border-gray-300 p-2 text-center">
                -{{ totalPoints }}
              </td>
              <td class="border border-gray-300 p-2 text-center">
                <span class="text-xs">
                  {{ filteredStudents.filter(s => s.incidents.length === 0).length }} 
                  {{ language === 'en' ? 'Good' : 'جيد' }}
                </span>
              </td>
            </tr>
          </tfoot>
        </table>
      </div>

      <!-- Students with Incidents - Organized by Student -->
      <div class="page-break-before">
        <h2 class="text-xl font-bold text-gray-800 mb-4 border-b-2 border-gray-300 pb-2">
          {{ language === 'en' ? 'Student Behavior Reports' : 'تقارير سلوك الطلاب' }}
        </h2>
        
        <div class="space-y-6">
          <!-- Loop through each student -->
          <div
            v-for="(studentData, index) in filteredStudents"
            :key="studentData.student.id"
            class="page-break-inside-avoid border-2 border-gray-300 rounded-lg p-4"
          >
            <!-- Student Header -->
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-4 rounded-lg mb-4 border-l-4"
                 :class="studentData.incidents.length > 0 ? 'border-orange-500' : 'border-green-500'">
              <div class="flex justify-between items-start">
                <div>
                  <h3 class="text-xl font-bold text-gray-800">
                    {{ index + 1 }}. {{ studentData.student.name }}
                  </h3>
                  <p class="text-sm text-gray-600">
                    {{ language === 'en' ? 'Grade' : 'الصف' }}: {{ studentData.student.grade || 'N/A' }}
                  </p>
                </div>
                <div class="text-right">
                  <div v-if="studentData.incidents.length > 0" class="space-y-1">
                    <div class="text-sm">
                      <span class="font-semibold">{{ language === 'en' ? 'Total Incidents' : 'إجمالي الحوادث' }}:</span>
                      <span class="ml-2 px-2 py-1 bg-blue-100 text-blue-800 rounded font-bold">{{ studentData.summary.total }}</span>
                    </div>
                    <div class="text-sm">
                      <span class="font-semibold">{{ language === 'en' ? 'Points Deducted' : 'النقاط المخصومة' }}:</span>
                      <span class="ml-2 px-2 py-1 bg-red-100 text-red-800 rounded font-bold">-{{ studentData.summary.points }}</span>
                    </div>
                  </div>
                  <div v-else class="flex items-center gap-2 text-green-700">
                    <span class="text-3xl">✅</span>
                    <div class="text-right">
                      <p class="font-bold">{{ language === 'en' ? 'Excellent Behavior!' : 'سلوك ممتاز!' }}</p>
                      <p class="text-sm">{{ language === 'en' ? 'No incidents recorded' : 'لا توجد حوادث مسجلة' }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Student Incidents or Good Behavior Message -->
            <div v-if="studentData.incidents.length > 0" class="space-y-3">
              <!-- Summary Stats -->
              <div class="grid grid-cols-3 gap-2 mb-3">
                <div class="bg-yellow-50 border border-yellow-200 rounded p-2 text-center">
                  <div class="text-2xl font-bold text-yellow-700">{{ studentData.summary.minor }}</div>
                  <div class="text-xs text-gray-600">{{ language === 'en' ? 'Minor' : 'بسيط' }}</div>
                </div>
                <div class="bg-red-50 border border-red-200 rounded p-2 text-center">
                  <div class="text-2xl font-bold text-red-700">{{ studentData.summary.major }}</div>
                  <div class="text-xs text-gray-600">{{ language === 'en' ? 'Major' : 'كبير' }}</div>
                </div>
                <div class="bg-purple-50 border border-purple-200 rounded p-2 text-center">
                  <div class="text-2xl font-bold text-purple-700">-{{ studentData.summary.points }}</div>
                  <div class="text-xs text-gray-600">{{ language === 'en' ? 'Points' : 'النقاط' }}</div>
                </div>
              </div>

              <!-- Incident Details -->
              <div class="space-y-2">
                <h4 class="font-semibold text-gray-700 text-sm border-b border-gray-200 pb-1">
                  {{ language === 'en' ? 'Incident Details:' : 'تفاصيل الحوادث:' }}
                </h4>
                <div
                  v-for="(incident, incidentIndex) in studentData.incidents"
                  :key="incident.id"
                  class="bg-gray-50 border border-gray-200 rounded p-3"
                >
                  <div class="flex justify-between items-start mb-2">
                    <div class="flex items-center gap-2">
                      <span class="font-bold text-gray-700">{{ incidentIndex + 1 }}.</span>
                      <span
                        class="px-2 py-1 text-xs rounded font-semibold"
                        :class="incident.severity === 'major' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800'"
                      >
                        {{ incident.incident_type[language] }}
                      </span>
                    </div>
                    <div class="text-xs text-gray-600">
                      {{ formatDateTime(incident.occurred_at || `${incident.date} ${incident.time}`) }}
                    </div>
                  </div>
                  
                  <div class="grid grid-cols-2 gap-2 text-xs mb-2">
                    <div>
                      <span class="font-semibold text-gray-600">{{ language === 'en' ? 'Location' : 'الموقع' }}:</span>
                      <span class="ml-1">{{ incident.location[language] }}</span>
                    </div>
                    <div>
                      <span class="font-semibold text-gray-600">{{ language === 'en' ? 'Behavior' : 'السلوك' }}:</span>
                      <span class="ml-1">{{ incident.behavior[language] }}</span>
                    </div>
                  </div>
                  
                  <div class="text-xs mb-2">
                    <span class="font-semibold text-gray-600">{{ language === 'en' ? 'Description' : 'الوصف' }}:</span>
                    <p class="mt-1 text-gray-700">{{ incident.description[language] }}</p>
                  </div>
                  
                  <div class="text-xs">
                    <span class="font-semibold text-gray-600">{{ language === 'en' ? 'Teacher Action' : 'إجراء المعلم' }}:</span>
                    <p class="mt-1 text-gray-700">{{ incident.teacher_action[language] }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Good Behavior Message for Students with No Incidents -->
            <div v-else class="bg-green-50 border-2 border-green-200 rounded-lg p-6 text-center">
              <div class="text-6xl mb-3">🌟</div>
              <h4 class="text-lg font-bold text-green-800 mb-2">
                {{ language === 'en' ? 'Outstanding Student!' : 'طالب متميز!' }}
              </h4>
              <p class="text-green-700">
                {{ language === 'en' 
                  ? 'This student has maintained excellent behavior with no incidents recorded during this period.' 
                  : 'حافظ هذا الطالب على سلوك ممتاز دون أي حوادث مسجلة خلال هذه الفترة.' }}
              </p>
              <div class="mt-4 inline-block px-4 py-2 bg-green-200 text-green-900 rounded-full font-semibold">
                {{ language === 'en' ? '✓ Zero Incidents' : '✓ صفر حوادث' }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="mt-8 pt-4 border-t-2 border-gray-300 text-center text-sm text-gray-600">
        <p>{{ language === 'en' ? 'Generated on' : 'تم الإنشاء في' }}: {{ new Date().toLocaleString() }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useQuasar } from 'quasar'

const props = defineProps({
  incidents: {
    type: Array,
    required: true
  },
  students: {
    type: Array,
    required: true
  },
  classroomId: {
    type: Number,
    required: false
  },
  date: {
    type: String,
    required: true
  },
  language: {
    type: String,
    default: 'en'
  }
})

const $q = useQuasar()
const printContent = ref(null)

// Filter state
const showStudentsFilter = ref('all')
const severityFilter = ref('all')

// Computed
const minorCount = computed(() => {
  return props.incidents.filter(i => i.severity === 'minor').length
})

const majorCount = computed(() => {
  return props.incidents.filter(i => i.severity === 'major' || i.severity === 'moderate').length
})

const totalPoints = computed(() => {
  return props.incidents.reduce((sum, i) => sum + (i.points_deducted || 1), 0)
})

const studentSummary = computed(() => {
  const summary = {}
  
  props.incidents.forEach(incident => {
    if (!summary[incident.student_id]) {
      summary[incident.student_id] = {
        id: incident.student_id,
        name: incident.student_name,
        total: 0,
        minor: 0,
        major: 0,
        points: 0
      }
    }
    
    summary[incident.student_id].total++
    if (incident.severity === 'minor') {
      summary[incident.student_id].minor++
    } else {
      summary[incident.student_id].major++
    }
    summary[incident.student_id].points += (incident.points_deducted || 1)
  })
  
  return Object.values(summary).sort((a, b) => b.total - a.total)
})

const studentsWithIncidents = computed(() => {
  // Create a map of incidents by student ID
  const incidentsByStudent = {}
  props.incidents.forEach(incident => {
    if (!incidentsByStudent[incident.student_id]) {
      incidentsByStudent[incident.student_id] = []
    }
    incidentsByStudent[incident.student_id].push(incident)
  })

  // Map all students with their incidents (or empty array)
  return props.students.map(student => {
    const studentIncidents = incidentsByStudent[student.id] || []
    
    // Calculate summary for this student
    const summary = {
      total: studentIncidents.length,
      minor: studentIncidents.filter(i => i.severity === 'minor').length,
      major: studentIncidents.filter(i => i.severity === 'major' || i.severity === 'moderate').length,
      points: studentIncidents.reduce((sum, i) => sum + (i.points_deducted || 1), 0)
    }

    return {
      student: {
        id: student.id,
        name: `${student.firstName} ${student.lastName}`,
        grade: student.grade
      },
      incidents: studentIncidents.sort((a, b) => {
        const dateA = new Date(a.occurred_at || `${a.date} ${a.time}`)
        const dateB = new Date(b.occurred_at || `${b.date} ${b.time}`)
        return dateB - dateA // Most recent first
      }),
      summary
    }
  }).sort((a, b) => {
    // Sort: students with incidents first, then by incident count, then alphabetically
    if (a.incidents.length === 0 && b.incidents.length === 0) {
      return a.student.name.localeCompare(b.student.name)
    }
    if (a.incidents.length === 0) return 1
    if (b.incidents.length === 0) return -1
    return b.incidents.length - a.incidents.length
  })
})

const filteredStudents = computed(() => {
  let filtered = studentsWithIncidents.value

  // Apply student filter
  if (showStudentsFilter.value === 'with-incidents') {
    filtered = filtered.filter(s => s.incidents.length > 0)
  } else if (showStudentsFilter.value === 'no-incidents') {
    filtered = filtered.filter(s => s.incidents.length === 0)
  }

  // Apply severity filter to incidents
  if (severityFilter.value !== 'all') {
    filtered = filtered.map(studentData => {
      const filteredIncidents = studentData.incidents.filter(incident => {
        if (severityFilter.value === 'minor') {
          return incident.severity === 'minor'
        } else if (severityFilter.value === 'major') {
          return incident.severity === 'major' || incident.severity === 'moderate'
        }
        return true
      })

      // Recalculate summary for filtered incidents
      const summary = {
        total: filteredIncidents.length,
        minor: filteredIncidents.filter(i => i.severity === 'minor').length,
        major: filteredIncidents.filter(i => i.severity === 'major' || i.severity === 'moderate').length,
        points: filteredIncidents.reduce((sum, i) => sum + (i.points_deducted || 1), 0)
      }

      return {
        ...studentData,
        incidents: filteredIncidents,
        summary
      }
    }).filter(s => {
      // Remove students with no incidents after severity filter (unless showing all)
      return showStudentsFilter.value === 'all' || s.incidents.length > 0
    })
  }

  return filtered
})

const filteredTotalIncidents = computed(() => {
  return filteredStudents.value.reduce((sum, s) => sum + s.incidents.length, 0)
})

// Methods
function formatDate(dateStr) {
  return new Date(dateStr).toLocaleDateString()
}

function formatDateTime(dateTimeStr) {
  return new Date(dateTimeStr).toLocaleString()
}

function printReport() {
  window.print()
}

function openInNewWindow() {
  const printContent = document.getElementById('printable-report')
  if (!printContent) return

  const newWindow = window.open('', '_blank', 'width=1200,height=800')
  if (!newWindow) {
    $q.notify({
      message: 'Please allow pop-ups to open print window',
      color: 'warning',
      position: 'top'
    })
    return
  }

  const styles = `
    <style>
      * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
      }
      body {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        padding: 20px;
        background: #f5f5f5;
      }
      .printable-content {
        background: white;
        padding: 2rem;
        max-width: 1200px;
        margin: 0 auto;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      }
      .page-break-inside-avoid {
        page-break-inside: avoid;
        break-inside: avoid;
      }
      .page-break-before {
        page-break-before: always;
        break-before: always;
      }
      @media print {
        body {
          background: white;
          padding: 0;
        }
        .printable-content {
          box-shadow: none;
          padding: 0;
        }
        .no-print {
          display: none !important;
        }
      }
      .text-center { text-align: center; }
      .text-left { text-left: left; }
      .text-right { text-align: right; }
      .font-bold { font-weight: bold; }
      .text-xl { font-size: 1.25rem; }
      .text-lg { font-size: 1.125rem; }
      .text-sm { font-size: 0.875rem; }
      .text-xs { font-size: 0.75rem; }
      .mb-2 { margin-bottom: 0.5rem; }
      .mb-3 { margin-bottom: 0.75rem; }
      .mb-4 { margin-bottom: 1rem; }
      .mb-6 { margin-bottom: 1.5rem; }
      .mb-8 { margin-bottom: 2rem; }
      .mt-1 { margin-top: 0.25rem; }
      .mt-2 { margin-top: 0.5rem; }
      .mt-3 { margin-top: 0.75rem; }
      .mt-4 { margin-top: 1rem; }
      .p-2 { padding: 0.5rem; }
      .p-3 { padding: 0.75rem; }
      .p-4 { padding: 1rem; }
      .p-6 { padding: 1.5rem; }
      .pb-1 { padding-bottom: 0.25rem; }
      .pb-2 { padding-bottom: 0.5rem; }
      .pb-4 { padding-bottom: 1rem; }
      .pt-3 { padding-top: 0.75rem; }
      .px-2 { padding-left: 0.5rem; padding-right: 0.5rem; }
      .px-4 { padding-left: 1rem; padding-right: 1rem; }
      .py-1 { padding-top: 0.25rem; padding-bottom: 0.25rem; }
      .py-2 { padding-top: 0.5rem; padding-bottom: 0.5rem; }
      .border { border: 1px solid #d1d5db; }
      .border-2 { border-width: 2px; }
      .border-l-4 { border-left-width: 4px; }
      .border-b { border-bottom: 1px solid #d1d5db; }
      .border-b-2 { border-bottom-width: 2px; }
      .rounded { border-radius: 0.25rem; }
      .rounded-lg { border-radius: 0.5rem; }
      .rounded-full { border-radius: 9999px; }
      .space-y-1 > * + * { margin-top: 0.25rem; }
      .space-y-2 > * + * { margin-top: 0.5rem; }
      .space-y-3 > * + * { margin-top: 0.75rem; }
      .space-y-4 > * + * { margin-top: 1rem; }
      .space-y-6 > * + * { margin-top: 1.5rem; }
      .gap-2 { gap: 0.5rem; }
      .gap-4 { gap: 1rem; }
      .grid { display: grid; }
      .grid-cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
      .grid-cols-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
      .grid-cols-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
      .flex { display: flex; }
      .items-center { align-items: center; }
      .items-start { align-items: flex-start; }
      .justify-between { justify-content: space-between; }
      .inline-block { display: inline-block; }
      .bg-gray-50 { background-color: #f9fafb; }
      .bg-gray-100 { background-color: #f3f4f6; }
      .bg-blue-50 { background-color: #eff6ff; }
      .bg-blue-100 { background-color: #dbeafe; }
      .bg-green-50 { background-color: #f0fdf4; }
      .bg-green-200 { background-color: #bbf7d0; }
      .bg-yellow-50 { background-color: #fefce8; }
      .bg-yellow-100 { background-color: #fef3c7; }
      .bg-red-50 { background-color: #fef2f2; }
      .bg-red-100 { background-color: #fee2e2; }
      .bg-purple-50 { background-color: #faf5ff; }
      .bg-orange-50 { background-color: #fff7ed; }
      .bg-indigo-50 { background-color: #eef2ff; }
      .bg-gradient-to-r { background-image: linear-gradient(to right, var(--tw-gradient-stops)); }
      .from-blue-50 { --tw-gradient-from: #eff6ff; --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to, rgba(239, 246, 255, 0)); }
      .to-indigo-50 { --tw-gradient-to: #eef2ff; }
      .text-gray-600 { color: #4b5563; }
      .text-gray-700 { color: #374151; }
      .text-gray-800 { color: #1f2937; }
      .text-gray-900 { color: #111827; }
      .text-blue-600 { color: #2563eb; }
      .text-blue-800 { color: #1e40af; }
      .text-green-700 { color: #15803d; }
      .text-green-800 { color: #166534; }
      .text-green-900 { color: #14532d; }
      .text-yellow-600 { color: #ca8a04; }
      .text-yellow-700 { color: #a16207; }
      .text-yellow-800 { color: #854d0e; }
      .text-red-600 { color: #dc2626; }
      .text-red-700 { color: #b91c1c; }
      .text-red-800 { color: #991b1b; }
      .text-purple-600 { color: #9333ea; }
      .text-purple-700 { color: #7e22ce; }
      .text-orange-500 { color: #f97316; }
      .text-orange-600 { color: #ea580c; }
      .border-gray-200 { border-color: #e5e7eb; }
      .border-gray-300 { border-color: #d1d5db; }
      .border-green-200 { border-color: #bbf7d0; }
      .border-green-500 { border-color: #22c55e; }
      .border-yellow-200 { border-color: #fef08a; }
      .border-red-200 { border-color: #fecaca; }
      .border-purple-200 { border-color: #e9d5ff; }
      .border-orange-500 { border-color: #f97316; }
      .w-full { width: 100%; }
      .border-collapse { border-collapse: collapse; }
      table { width: 100%; border-collapse: collapse; }
      th, td { border: 1px solid #d1d5db; padding: 0.5rem; }
      th { background-color: #f3f4f6; font-weight: bold; }
    </style>
  `

  newWindow.document.write(`
    <!DOCTYPE html>
    <html>
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Behavior Incident Report</title>
      ${styles}
    </head>
    <body>
      <div class="no-print" style="position: fixed; top: 10px; right: 10px; z-index: 1000;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #2563eb; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 14px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
          🖨️ Print
        </button>
        <button onclick="window.close()" style="padding: 10px 20px; background: #6b7280; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 14px; margin-left: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
          ✕ Close
        </button>
      </div>
      ${printContent.innerHTML}
    </body>
    </html>
  `)
  newWindow.document.close()
}

function exportPDF() {
  $q.notify({
    message: 'PDF export feature coming soon',
    color: 'info',
    position: 'top'
  })
  // Future: Implement PDF export using jsPDF or similar
}
</script>

<style scoped>
@media print {
  .no-print {
    display: none !important;
  }
  
  .printable-content {
    width: 100%;
    max-width: none;
  }
  
  .page-break-before {
    page-break-before: always;
  }
  
  .page-break-inside-avoid {
    page-break-inside: avoid;
  }
}

.print-report {
  min-height: 100vh;
}
</style>
