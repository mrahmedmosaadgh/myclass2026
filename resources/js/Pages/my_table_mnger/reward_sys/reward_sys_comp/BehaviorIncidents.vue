<template>
  <div class="behavior-incidents-container">
    <!-- Header with Language Toggle -->
    <div class="flex justify-between items-center mb-6">
      <h3 class="text-2xl font-bold text-gray-800">{{ t.title }}</h3>
      <div class="flex gap-2">
        <q-btn
          flat
          icon="print"
          :label="t.printReport"
          @click="openPrintDialog"
          color="primary"
        />
        <q-btn-toggle
          v-model="language"
          :options="[
            { label: 'English', value: 'en' },
            { label: 'العربية', value: 'ar' }
          ]"
          color="primary"
          toggle-color="primary"
          unelevated
        />
      </div>
    </div>

    <!-- Student Selection Section -->
    <div class="mb-6">
      <div class="flex justify-between items-center mb-4">
        <h4 class="text-lg font-bold text-gray-700">{{ t.selectStudents }}</h4>
        <div class="flex gap-2">
          <q-btn
            flat
            dense
            color="primary"
            :label="t.selectAll"
            size="sm"
            @click="selectAllStudents"
          />
          <q-btn
            flat
            dense
            color="primary"
            :label="t.clear"
            size="sm"
            @click="selectedStudentIds = []"
          />
          <q-btn
            color="primary"
            icon="add"
            :label="`${t.recordIncident} (${selectedStudentIds.length})`"
            @click="openIncidentDialog"
            :disable="selectedStudentIds.length === 0"
            size="md"
            class="shadow-md"
          />
        </div>
      </div>

      <!-- Student Cards Grid -->
      <div class="flex flex-wrap justify-center gap-4">
        <StudentCard
          v-for="student in students"
          :key="student.id"
          :student="student"
          :selected="selectedStudentIds.includes(student.id)"
          :avatar-edit-enabled="false"
          @select="toggleStudentSelection(student.id)"
        />
      </div>
    </div>

    <!-- Selected Students Summary -->
    <div v-if="selectedStudentIds.length > 0" class="mb-4 p-4 bg-blue-50 rounded-lg border border-blue-200">
      <div class="flex items-center justify-between">
        <div>
          <span class="font-bold text-blue-800">{{ t.selectedStudents }} ({{ selectedStudentIds.length }}):</span>
          <div class="flex flex-wrap gap-2 mt-2">
            <q-chip
              v-for="id in selectedStudentIds"
              :key="id"
              removable
              @remove="toggleStudentSelection(id)"
              color="primary"
              text-color="white"
              size="sm"
            >
              {{ getStudentName(id) }}
            </q-chip>
          </div>
        </div>
      </div>
    </div>

    <!-- Incidents List -->
    <div class="space-y-4">
      <div v-if="loading" class="text-center py-8">
        <q-spinner color="primary" size="3rem" />
        <p class="text-gray-500 mt-2">{{ t.loadingIncidents }}</p>
      </div>

      <div v-else-if="incidents.length === 0" class="text-center py-12 bg-gray-50 rounded-lg">
        <q-icon name="info" size="3rem" color="grey-5" />
        <p class="text-gray-500 mt-2">{{ t.noIncidents }}</p>
      </div>

      <div v-else class="grid grid-cols-1 gap-4">
        <q-card
          v-for="incident in incidents"
          :key="incident.id"
          class="shadow-md hover:shadow-lg transition-shadow"
          :class="incident.incident_type[language] === 'Major' || incident.incident_type[language] === 'سلوك كبير' ? 'border-l-4 border-red-500' : 'border-l-4 border-yellow-500'"
        >
          <q-card-section class="pb-2">
            <div class="flex justify-between items-start">
              <div class="flex-1">
                <div class="flex items-center gap-2 mb-2">
                  <q-badge
                    :color="incident.incident_type[language] === 'Major' || incident.incident_type[language] === 'سلوك كبير' ? 'red' : 'orange'"
                    :label="incident.incident_type[language]"
                  />
                  <span class="text-sm text-gray-500">{{ incident.date }} • {{ incident.time }}</span>
                </div>
                
                <h4 class="text-lg font-bold text-gray-800 mb-1">
                  {{ incident.student_name }} (Grade {{ incident.grade }})
                </h4>
                
                <div class="grid grid-cols-2 gap-2 text-sm mt-3">
                  <div>
                    <span class="font-semibold text-gray-600">{{ t.location }}:</span>
                    <span class="ml-1">{{ incident.location[language] }}</span>
                  </div>
                  <div>
                    <span class="font-semibold text-gray-600">{{ t.behavior }}:</span>
                    <span class="ml-1">{{ incident.behavior[language] }}</span>
                  </div>
                </div>
              </div>
              
              <div class="flex gap-1">
                <q-btn
                  flat
                  round
                  dense
                  icon="visibility"
                  color="primary"
                  @click="viewIncident(incident)"
                >
                  <q-tooltip>{{ t.viewDetails }}</q-tooltip>
                </q-btn>
                <q-btn
                  flat
                  round
                  dense
                  icon="delete"
                  color="negative"
                  @click="confirmDelete(incident)"
                >
                  <q-tooltip>{{ t.delete }}</q-tooltip>
                </q-btn>
              </div>
            </div>
          </q-card-section>

          <q-card-section class="pt-0">
            <p class="text-sm text-gray-700 italic">{{ incident.description[language] }}</p>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <!-- New Incident Dialog -->
    <q-dialog v-model="showIncidentDialog" persistent maximized>
      <q-card>
        <q-toolbar class="bg-primary text-white">
          <q-toolbar-title>
            {{ t.recordIncident }} - {{ selectedStudentIds.length }} {{ t.student }}(s)
          </q-toolbar-title>
          <q-btn flat round dense icon="close" v-close-popup @click="resetForm" />
        </q-toolbar>

        <q-card-section class="q-pt-md">
          <div class="max-w-6xl mx-auto">
            <!-- Selected Students Display -->
            <div class="mb-6 p-4 bg-blue-50 rounded-lg">
              <h5 class="font-bold text-blue-900 mb-2">{{ t.selectedStudents }}:</h5>
              <div class="flex flex-wrap gap-2">
                <q-chip
                  v-for="id in selectedStudentIds"
                  :key="id"
                  color="primary"
                  text-color="white"
                  icon="person"
                >
                  {{ getStudentName(id) }}
                </q-chip>
              </div>
            </div>

            <!-- Form in Grouped Sections -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

              <!-- LEFT COLUMN -->
              <div class="space-y-4">
                <!-- When & Where Group -->
                <q-card class="shadow-sm">
                  <q-card-section class="bg-blue-50">
                    <div class="text-subtitle1 font-bold text-blue-900">📅 {{ t.whenWhere }}</div>
                  </q-card-section>
                  <q-card-section class="space-y-3">
                    <div class="grid grid-cols-2 gap-3">
                      <q-input
                        v-model="incidentForm.date"
                        :label="`${t.date} *`"
                        type="date"
                        outlined
                        dense
                      />
                      <q-input
                        v-model="incidentForm.time"
                        :label="`${t.time} *`"
                        type="time"
                        outlined
                        dense
                      />
                    </div>
                    <q-select
                      v-model="incidentForm.location"
                      :options="locations"
                      :label="`${t.location} *`"
                      outlined
                      dense
                    >
                      <template v-slot:prepend>
                        <q-icon name="place" />
                      </template>
                    </q-select>
                  </q-card-section>
                </q-card>

                <!-- Incident Details Group -->
                <q-card class="shadow-sm">
                  <q-card-section class="bg-orange-50">
                    <div class="text-subtitle1 font-bold text-orange-900">⚠️ {{ t.incidentDetails }}</div>
                  </q-card-section>
                  <q-card-section class="space-y-3">
                    <q-select
                      v-model="incidentForm.incident_type"
                      :options="incidentTypes"
                      :label="`${t.incidentType} *`"
                      outlined
                      dense
                    >
                      <template v-slot:prepend>
                        <q-icon name="warning" />
                      </template>
                    </q-select>
                    <q-select
                      v-model="incidentForm.behavior"
                      :options="behaviorOptions"
                      :label="`${t.behavior} *`"
                      outlined
                      dense
                    >
                      <template v-slot:prepend>
                        <q-icon name="psychology" />
                      </template>
                    </q-select>
                    <q-select
                      v-model="incidentForm.motivation"
                      :options="motivations"
                      :label="t.motivation"
                      outlined
                      dense
                    >
                      <template v-slot:prepend>
                        <q-icon name="lightbulb" />
                      </template>
                    </q-select>
                    <q-select
                      v-model="incidentForm.others_involved"
                      :options="othersInvolvedOptions"
                      :label="t.othersInvolved"
                      outlined
                      dense
                    >
                      <template v-slot:prepend>
                        <q-icon name="group" />
                      </template>
                    </q-select>
                  </q-card-section>
                </q-card>
              </div>

              <!-- RIGHT COLUMN -->
              <div class="space-y-4">
                <!-- Description Group -->
                <q-card class="shadow-sm">
                  <q-card-section class="bg-green-50">
                    <div class="text-subtitle1 font-bold text-green-900">📝 {{ t.description }}</div>
                  </q-card-section>
                  <q-card-section class="space-y-3">
                    <q-input
                      v-model="incidentForm.description_en"
                      :label="`${t.descriptionEn} *`"
                      type="textarea"
                      outlined
                      dense
                      rows="3"
                    />
                    <q-input
                      v-model="incidentForm.description_ar"
                      :label="t.descriptionAr"
                      type="textarea"
                      outlined
                      dense
                      rows="3"
                    />
                  </q-card-section>
                </q-card>

                <!-- Actions Taken Group -->
                <q-card class="shadow-sm">
                  <q-card-section class="bg-purple-50">
                    <div class="text-subtitle1 font-bold text-purple-900">✅ {{ t.actionsTaken }}</div>
                  </q-card-section>
                  <q-card-section class="space-y-3">
                    <q-input
                      v-model="incidentForm.teacher_action_en"
                      :label="t.teacherActionEn"
                      outlined
                      dense
                      placeholder="e.g., Verbal warning, Seat change"
                    />
                    <q-input
                      v-model="incidentForm.teacher_action_ar"
                      :label="t.teacherActionAr"
                      outlined
                      dense
                    />
                    <q-input
                      v-model="incidentForm.admin_action_en"
                      :label="t.adminActionEn"
                      outlined
                      dense
                      placeholder="Optional - if admin involved"
                    />
                    <q-checkbox
                      v-model="incidentForm.follow_up_needed"
                      :label="t.followUpNeeded"
                      color="orange"
                    />
                  </q-card-section>
                </q-card>

                <!-- Quick Actions -->
                <q-card class="shadow-sm">
                  <q-card-section class="bg-gray-50">
                    <div class="text-subtitle1 font-bold text-gray-900">⚡ {{ t.quickActions }}</div>
                  </q-card-section>
                  <q-card-section class="space-y-2">
                    <q-btn
                      flat
                      dense
                      color="primary"
                      icon="history"
                      :label="t.loadLastSettings"
                      @click="loadLastSettings"
                      class="w-full"
                    />
                    <q-btn
                      flat
                      dense
                      color="secondary"
                      icon="save"
                      :label="t.saveAsTemplate"
                      @click="saveAsTemplate"
                      class="w-full"
                    />
                  </q-card-section>
                </q-card>
              </div>
            </div>
          </div>
        </q-card-section>

        <q-card-actions align="right" class="q-px-md q-pb-md">
          <q-btn flat :label="t.cancel" v-close-popup @click="resetForm" />
          <q-btn
            color="primary"
            :label="t.save"
            @click="saveIncident"
            :loading="saving"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Print Report Dialog -->
    <q-dialog v-model="showPrintDialog" maximized>
      <q-card>
        <q-toolbar class="bg-primary text-white">
          <q-toolbar-title>{{ t.printReport }}</q-toolbar-title>
          <q-btn flat round dense icon="close" v-close-popup />
        </q-toolbar>
        <q-card-section>
          <IncidentPrintReport
            :incidents="incidentsForPrint"
            :students="selectedStudentsForPrint"
            :classroom-id="classroomId"
            :date="date"
            :language="language"
          />
        </q-card-section>
      </q-card>
    </q-dialog>

    <!-- View Incident Dialog -->
    <q-dialog v-model="showViewDialog" full-width>
      <q-card style="max-width: 900px; width: 100%;">
        <q-card-section class="bg-primary text-white">
          <div class="flex justify-between items-center">
            <div class="text-h6">{{ t.viewDetails }}</div>
            <q-btn
              flat
              dense
              icon="history"
              :label="t.viewStudentHistory"
              @click="viewStudentHistory(selectedIncident?.student_id)"
            />
          </div>
        </q-card-section>

        <q-card-section v-if="selectedIncident" class="space-y-3">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <p class="text-sm font-semibold text-gray-600">{{ t.student }}</p>
              <p class="text-base">{{ selectedIncident.student_name }}</p>
            </div>
            <div>
              <p class="text-sm font-semibold text-gray-600">{{ t.grade }}</p>
              <p class="text-base">{{ selectedIncident.grade }}</p>
            </div>
            <div>
              <p class="text-sm font-semibold text-gray-600">{{ t.dateTime }}</p>
              <p class="text-base">{{ selectedIncident.date }} {{ selectedIncident.time }}</p>
            </div>
            <div>
              <p class="text-sm font-semibold text-gray-600">{{ t.incidentType }}</p>
              <p class="text-base">{{ selectedIncident.incident_type[language] }}</p>
            </div>
            <div>
              <p class="text-sm font-semibold text-gray-600">{{ t.location }}</p>
              <p class="text-base">{{ selectedIncident.location[language] }}</p>
            </div>
            <div>
              <p class="text-sm font-semibold text-gray-600">{{ t.behavior }}</p>
              <p class="text-base">{{ selectedIncident.behavior[language] }}</p>
            </div>
          </div>

          <div>
            <p class="text-sm font-semibold text-gray-600">{{ t.description }}</p>
            <p class="text-base">{{ selectedIncident.description[language] }}</p>
          </div>

          <div>
            <p class="text-sm font-semibold text-gray-600">{{ t.motivation }}</p>
            <p class="text-base">{{ selectedIncident.motivation[language] }}</p>
          </div>

          <div>
            <p class="text-sm font-semibold text-gray-600">{{ t.othersInvolved }}</p>
            <p class="text-base">{{ selectedIncident.others_involved[language] }}</p>
          </div>

          <div>
            <p class="text-sm font-semibold text-gray-600">{{ t.teacherActionEn.replace(' (English)', '') }}</p>
            <p class="text-base">{{ selectedIncident.teacher_action[language] }}</p>
          </div>

          <div v-if="selectedIncident.admin_action">
            <p class="text-sm font-semibold text-gray-600">{{ t.adminActionEn.replace(' (English)', '') }}</p>
            <p class="text-base">{{ selectedIncident.admin_action[language] }}</p>
          </div>

          <div>
            <p class="text-sm font-semibold text-gray-600">{{ t.followUpNeeded }}</p>
            <p class="text-base">{{ selectedIncident.follow_up_needed ? t.yes : t.no }}</p>
          </div>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat :label="t.close" color="primary" v-close-popup />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Student History Dialog -->
    <q-dialog v-model="showHistoryDialog" full-width>
      <q-card style="max-width: 1000px; width: 100%;">
        <q-card-section class="bg-indigo-600 text-white">
          <div class="text-h6">{{ t.studentBehaviorHistory }}</div>
          <div class="text-subtitle2" v-if="viewingStudentHistory">
            {{ viewingStudentHistory.student_name }} - {{ t.last90Days }}
          </div>
        </q-card-section>

        <q-card-section v-if="loadingHistory" class="text-center py-8">
          <q-spinner color="primary" size="3rem" />
          <p class="text-gray-500 mt-2">{{ t.loadingHistory }}</p>
        </q-card-section>

        <q-card-section v-else-if="viewingStudentHistory" class="space-y-4">
          <!-- Summary Stats -->
          <div class="grid grid-cols-4 gap-4">
            <q-card class="bg-blue-50">
              <q-card-section class="text-center">
                <div class="text-3xl font-bold text-blue-600">{{ viewingStudentHistory.summary.total_incidents }}</div>
                <div class="text-sm text-gray-600">{{ t.totalIncidents }}</div>
              </q-card-section>
            </q-card>
            <q-card class="bg-yellow-50">
              <q-card-section class="text-center">
                <div class="text-3xl font-bold text-yellow-600">{{ viewingStudentHistory.summary.minor }}</div>
                <div class="text-sm text-gray-600">{{ t.minor }}</div>
              </q-card-section>
            </q-card>
            <q-card class="bg-red-50">
              <q-card-section class="text-center">
                <div class="text-3xl font-bold text-red-600">{{ viewingStudentHistory.summary.major }}</div>
                <div class="text-sm text-gray-600">{{ t.major }}</div>
              </q-card-section>
            </q-card>
            <q-card class="bg-purple-50">
              <q-card-section class="text-center">
                <div class="text-3xl font-bold text-purple-600">{{ viewingStudentHistory.summary.net_points }}</div>
                <div class="text-sm text-gray-600">{{ t.netPoints }}</div>
              </q-card-section>
            </q-card>
          </div>

          <!-- Recent Incidents -->
          <div>
            <h5 class="font-bold text-gray-700 mb-3">{{ t.recentIncidents }}</h5>
            <div class="space-y-2 max-h-96 overflow-y-auto">
              <q-card
                v-for="incident in viewingStudentHistory.incidents"
                :key="incident.id"
                class="shadow-sm"
              >
                <q-card-section class="py-2">
                  <div class="flex justify-between items-start">
                    <div>
                      <q-badge
                        :color="incident.severity === 'major' ? 'red' : 'orange'"
                        :label="incident.incident_type[language]"
                      />
                      <span class="ml-2 text-sm text-gray-500">{{ incident.occurred_at }}</span>
                    </div>
                    <div class="text-sm">
                      <span class="font-semibold">{{ incident.behavior[language] }}</span>
                    </div>
                  </div>
                  <p class="text-sm text-gray-600 mt-1">{{ incident.description[language] }}</p>
                </q-card-section>
              </q-card>
            </div>
          </div>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat :label="t.close" color="primary" v-close-popup />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useQuasar } from 'quasar'
import axios from 'axios'
import StudentCard from './StudentCard.vue'
import IncidentPrintReport from './IncidentPrintReport.vue'

const props = defineProps({
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
  periodCode: {
    type: String,
    required: true
  }
})

const emit = defineEmits(['incident-recorded'])

const $q = useQuasar()

// State
const language = ref('en')
const incidents = ref([])
const loading = ref(false)
const saving = ref(false)
const showIncidentDialog = ref(false)
const showViewDialog = ref(false)
const showPrintDialog = ref(false)
const showHistoryDialog = ref(false)
const selectedIncident = ref(null)
const selectedStudentIds = ref([])
const studentHistories = ref({})
const viewingStudentHistory = ref(null)
const loadingHistory = ref(false)

// Form
const incidentForm = ref({
  student_id: null,
  date: new Date().toISOString().split('T')[0],
  time: new Date().toTimeString().slice(0, 5),
  incident_type: 'Minor',
  location: 'Classroom',
  behavior: 'Disruption',
  description_en: '',
  description_ar: '',
  motivation: 'Gain peer attention',
  others_involved: 'None',
  teacher_action_en: '',
  teacher_action_ar: '',
  admin_action_en: '',
  admin_action_ar: '',
  follow_up_needed: false
})

// Options
const incidentTypes = ['Minor', 'Major']
const locations = ['Classroom', 'Playground', 'Hallway', 'Cafeteria', 'Library', 'Tech Lab', 'Gym', 'Bus']
const behaviorOptions = [
  'Disruption',
  'Physical Aggression',
  'Noncompliance',
  'Bullying',
  'Property Misuse',
  'Technology Violation',
  'Teasing',
  'Threats',
  'Inappropriate Language',
  'Defiance / Insubordination'
]
const motivations = [
  'Gain peer attention',
  'Gain items/activities',
  'Avoid task',
  'Escape/Avoid peer or adult'
]
const othersInvolvedOptions = ['None', 'Peers', 'Staff', 'Multiple students']

// Translations
const translations = {
  en: {
    title: 'Behavior Incidents',
    printReport: 'Print Report',
    selectStudents: 'Select Students',
    selectAll: 'Select All',
    clear: 'Clear',
    recordIncident: 'Record Incident',
    selectedStudents: 'Selected Students',
    loadingIncidents: 'Loading incidents...',
    noIncidents: 'No behavior incidents recorded yet',
    location: 'Location',
    behavior: 'Behavior',
    viewDetails: 'View Details',
    delete: 'Delete',
    whenWhere: 'When & Where',
    date: 'Date',
    time: 'Time',
    incidentDetails: 'Incident Details',
    incidentType: 'Incident Type',
    motivation: 'Motivation',
    othersInvolved: 'Others Involved',
    description: 'Description',
    descriptionEn: 'Description (English)',
    descriptionAr: 'Description (Arabic)',
    actionsTaken: 'Actions Taken',
    teacherActionEn: 'Teacher Action (English)',
    teacherActionAr: 'Teacher Action (Arabic)',
    adminActionEn: 'Admin Action (English)',
    followUpNeeded: 'Follow-up Needed',
    quickActions: 'Quick Actions',
    loadLastSettings: 'Load Last Incident Settings',
    saveAsTemplate: 'Save as Template',
    cancel: 'Cancel',
    save: 'Record Incident',
    close: 'Close',
    student: 'Student',
    grade: 'Grade',
    dateTime: 'Date & Time',
    viewStudentHistory: 'View Student History',
    studentBehaviorHistory: 'Student Behavior History',
    last90Days: 'Last 90 Days',
    loadingHistory: 'Loading history...',
    totalIncidents: 'Total Incidents',
    minor: 'Minor',
    major: 'Major',
    netPoints: 'Net Points',
    recentIncidents: 'Recent Incidents',
    confirmDelete: 'Confirm Delete',
    confirmDeleteMsg: 'Are you sure you want to delete this incident for',
    yes: 'Yes',
    no: 'No'
  },
  ar: {
    title: 'حوادث السلوك',
    printReport: 'طباعة التقرير',
    selectStudents: 'اختر الطلاب',
    selectAll: 'اختر الكل',
    clear: 'مسح',
    recordIncident: 'تسجيل حادثة',
    selectedStudents: 'الطلاب المختارون',
    loadingIncidents: 'جاري تحميل الحوادث...',
    noIncidents: 'لم يتم تسجيل أي حوادث سلوك بعد',
    location: 'الموقع',
    behavior: 'السلوك',
    viewDetails: 'عرض التفاصيل',
    delete: 'حذف',
    whenWhere: 'متى وأين',
    date: 'التاريخ',
    time: 'الوقت',
    incidentDetails: 'تفاصيل الحادثة',
    incidentType: 'نوع الحادثة',
    motivation: 'الدافع',
    othersInvolved: 'آخرون متورطون',
    description: 'الوصف',
    descriptionEn: 'الوصف (إنجليزي)',
    descriptionAr: 'الوصف (عربي)',
    actionsTaken: 'الإجراءات المتخذة',
    teacherActionEn: 'إجراء المعلم (إنجليزي)',
    teacherActionAr: 'إجراء المعلم (عربي)',
    adminActionEn: 'إجراء الإدارة (إنجليزي)',
    followUpNeeded: 'يحتاج متابعة',
    quickActions: 'إجراءات سريعة',
    loadLastSettings: 'تحميل آخر إعدادات',
    saveAsTemplate: 'حفظ كقالب',
    cancel: 'إلغاء',
    save: 'تسجيل الحادثة',
    close: 'إغلاق',
    student: 'الطالب',
    grade: 'الصف',
    dateTime: 'التاريخ والوقت',
    viewStudentHistory: 'عرض سجل الطالب',
    studentBehaviorHistory: 'سجل سلوك الطالب',
    last90Days: 'آخر 90 يوم',
    loadingHistory: 'جاري تحميل السجل...',
    totalIncidents: 'إجمالي الحوادث',
    minor: 'بسيط',
    major: 'كبير',
    netPoints: 'صافي النقاط',
    recentIncidents: 'الحوادث الأخيرة',
    confirmDelete: 'تأكيد الحذف',
    confirmDeleteMsg: 'هل أنت متأكد من حذف هذه الحادثة لـ',
    yes: 'نعم',
    no: 'لا'
  }
}

// Computed
const t = computed(() => translations[language.value])

const studentOptions = computed(() => {
  return props.students.map(s => ({
    id: s.id,
    name: `${s.firstName} ${s.lastName}`
  }))
})

const selectedStudentsForPrint = computed(() => {
  if (selectedStudentIds.value.length === 0) {
    return props.students
  }
  return props.students.filter(s => selectedStudentIds.value.includes(s.id))
})

const incidentsForPrint = computed(() => {
  if (selectedStudentIds.value.length === 0) {
    return incidents.value
  }
  return incidents.value.filter(i => selectedStudentIds.value.includes(i.student_id))
})

// Methods
function toggleStudentSelection(studentId) {
  const index = selectedStudentIds.value.indexOf(studentId)
  if (index === -1) {
    selectedStudentIds.value.push(studentId)
  } else {
    selectedStudentIds.value.splice(index, 1)
  }
}

function selectAllStudents() {
  selectedStudentIds.value = props.students.map(s => s.id)
}

function getStudentName(studentId) {
  const student = props.students.find(s => s.id === studentId)
  return student ? `${student.firstName} ${student.lastName}` : 'Unknown'
}

function openIncidentDialog() {
  if (selectedStudentIds.value.length === 0) {
    $q.notify({
      message: 'Please select at least one student',
      color: 'warning',
      position: 'top'
    })
    return
  }
  
  // Load last saved settings
  loadSavedSettings()
  showIncidentDialog.value = true
}

function openPrintDialog() {
  if (selectedStudentIds.value.length > 0) {
    $q.notify({
      message: `Printing report for ${selectedStudentIds.value.length} selected student(s)`,
      color: 'info',
      position: 'top'
    })
  }
  showPrintDialog.value = true
}

function loadSavedSettings() {
  const saved = localStorage.getItem('behavior_incident_template')
  if (saved) {
    try {
      const template = JSON.parse(saved)
      // Apply saved settings except student-specific fields
      incidentForm.value = {
        ...incidentForm.value,
        incident_type: template.incident_type || incidentForm.value.incident_type,
        location: template.location || incidentForm.value.location,
        behavior: template.behavior || incidentForm.value.behavior,
        motivation: template.motivation || incidentForm.value.motivation,
        others_involved: template.others_involved || incidentForm.value.others_involved,
        teacher_action_en: template.teacher_action_en || '',
        teacher_action_ar: template.teacher_action_ar || '',
      }
    } catch (e) {
      console.error('Error loading saved settings:', e)
    }
  }
}

function loadLastSettings() {
  loadSavedSettings()
  $q.notify({
    message: 'Last settings loaded',
    color: 'positive',
    position: 'top'
  })
}

function saveAsTemplate() {
  const template = {
    incident_type: incidentForm.value.incident_type,
    location: incidentForm.value.location,
    behavior: incidentForm.value.behavior,
    motivation: incidentForm.value.motivation,
    others_involved: incidentForm.value.others_involved,
    teacher_action_en: incidentForm.value.teacher_action_en,
    teacher_action_ar: incidentForm.value.teacher_action_ar,
  }
  localStorage.setItem('behavior_incident_template', JSON.stringify(template))
  $q.notify({
    message: 'Settings saved as template',
    color: 'positive',
    position: 'top',
    icon: 'save'
  })
}

async function loadStudentHistory(studentId) {
  if (studentHistories.value[studentId]) {
    return studentHistories.value[studentId]
  }
  
  try {
    const response = await axios.get(`/api/behavior-incidents/student/${studentId}/report`, {
      params: {
        start_date: new Date(Date.now() - 90 * 24 * 60 * 60 * 1000).toISOString().split('T')[0], // Last 90 days
        end_date: new Date().toISOString().split('T')[0]
      }
    })
    studentHistories.value[studentId] = response.data.data
    return response.data.data
  } catch (error) {
    console.error('Error loading student history:', error)
    return null
  }
}
async function loadIncidents() {
  loading.value = true
  try {
    const response = await axios.get('/api/behavior-incidents', {
      params: {
        classroom_id: props.classroomId,
        date: props.date
      }
    })
    incidents.value = response.data.data || []
  } catch (error) {
    console.error('Error loading incidents:', error)
    $q.notify({
      message: 'Failed to load incidents',
      color: 'negative',
      position: 'top'
    })
  } finally {
    loading.value = false
  }
}

async function saveIncident() {
  if (!incidentForm.value.description_en) {
    $q.notify({
      message: 'Please fill in required fields',
      color: 'warning',
      position: 'top'
    })
    return
  }

  saving.value = true
  let successCount = 0
  let failCount = 0

  try {
    // Save current settings as template for next time
    saveAsTemplate()

    // Create incident for each selected student
    for (const studentId of selectedStudentIds.value) {
      try {
        const student = props.students.find(s => s.id === studentId)
        
        const payload = {
          student_id: studentId,
          student_name: `${student.firstName} ${student.lastName}`,
          grade: student.grade || 5,
          date: incidentForm.value.date,
          time: incidentForm.value.time,
          classroom_id: props.classroomId,
          period_code: props.periodCode,
          incident_type: {
            en: incidentForm.value.incident_type,
            ar: incidentForm.value.incident_type === 'Minor' ? 'سلوك بسيط' : 'سلوك كبير'
          },
          location: {
            en: incidentForm.value.location,
            ar: translateLocation(incidentForm.value.location)
          },
          behavior: {
            en: incidentForm.value.behavior,
            ar: translateBehavior(incidentForm.value.behavior)
          },
          description: {
            en: incidentForm.value.description_en,
            ar: incidentForm.value.description_ar || incidentForm.value.description_en
          },
          motivation: {
            en: incidentForm.value.motivation,
            ar: translateMotivation(incidentForm.value.motivation)
          },
          others_involved: {
            en: incidentForm.value.others_involved,
            ar: translateOthersInvolved(incidentForm.value.others_involved)
          },
          teacher_action: {
            en: incidentForm.value.teacher_action_en,
            ar: incidentForm.value.teacher_action_ar || incidentForm.value.teacher_action_en
          },
          admin_action: incidentForm.value.admin_action_en ? {
            en: incidentForm.value.admin_action_en,
            ar: incidentForm.value.admin_action_ar || incidentForm.value.admin_action_en
          } : null,
          follow_up_needed: incidentForm.value.follow_up_needed
        }

        const response = await axios.post('/api/behavior-incidents', payload)
        successCount++
        emit('incident-recorded', response.data)
      } catch (error) {
        console.error(`Error saving incident for student ${studentId}:`, error)
        failCount++
      }
    }

    if (successCount > 0) {
      $q.notify({
        message: `${successCount} incident(s) recorded successfully (-1 point each)`,
        color: 'positive',
        position: 'top',
        icon: 'check_circle'
      })
    }

    if (failCount > 0) {
      $q.notify({
        message: `${failCount} incident(s) failed to save`,
        color: 'warning',
        position: 'top'
      })
    }

    showIncidentDialog.value = false
    selectedStudentIds.value = []
    resetForm()
    await loadIncidents()
  } catch (error) {
    console.error('Error saving incidents:', error)
    $q.notify({
      message: 'Failed to save incidents: ' + (error.response?.data?.message || error.message),
      color: 'negative',
      position: 'top'
    })
  } finally {
    saving.value = false
  }
}

function viewIncident(incident) {
  selectedIncident.value = incident
  showViewDialog.value = true
}

async function viewStudentHistory(studentId) {
  if (!studentId) return
  
  loadingHistory.value = true
  showHistoryDialog.value = true
  
  try {
    const history = await loadStudentHistory(studentId)
    viewingStudentHistory.value = history
  } catch (error) {
    console.error('Error loading student history:', error)
    $q.notify({
      message: 'Failed to load student history',
      color: 'negative',
      position: 'top'
    })
  } finally {
    loadingHistory.value = false
  }
}

function confirmDelete(incident) {
  $q.dialog({
    title: 'Confirm Delete',
    message: `Are you sure you want to delete this incident for ${incident.student_name}?`,
    cancel: true,
    persistent: true,
    color: 'negative'
  }).onOk(async () => {
    await deleteIncident(incident.id)
  })
}

async function deleteIncident(id) {
  try {
    await axios.delete(`/api/behavior-incidents/${id}`)
    $q.notify({
      message: 'Incident deleted successfully',
      color: 'positive',
      position: 'top'
    })
    await loadIncidents()
  } catch (error) {
    console.error('Error deleting incident:', error)
    $q.notify({
      message: 'Failed to delete incident',
      color: 'negative',
      position: 'top'
    })
  }
}

function resetForm() {
  incidentForm.value = {
    student_id: null,
    date: new Date().toISOString().split('T')[0],
    time: new Date().toTimeString().slice(0, 5),
    incident_type: 'Minor',
    location: 'Classroom',
    behavior: 'Disruption',
    description_en: '',
    description_ar: '',
    motivation: 'Gain peer attention',
    others_involved: 'None',
    teacher_action_en: '',
    teacher_action_ar: '',
    admin_action_en: '',
    admin_action_ar: '',
    follow_up_needed: false
  }
}

// Translation helpers
function translateLocation(location) {
  const translations = {
    'Classroom': 'الفصل الدراسي',
    'Playground': 'ساحة اللعب',
    'Hallway': 'الممر',
    'Cafeteria': 'المقصف',
    'Library': 'المكتبة',
    'Tech Lab': 'معمل التقنية',
    'Gym': 'الصالة الرياضية',
    'Bus': 'الحافلة'
  }
  return translations[location] || location
}

function translateBehavior(behavior) {
  const translations = {
    'Disruption': 'إحداث فوضى',
    'Physical Aggression': 'اعتداء بدني',
    'Noncompliance': 'عدم الامتثال',
    'Bullying': 'تنمّر',
    'Property Misuse': 'سوء استخدام الممتلكات',
    'Technology Violation': 'مخالفة تقنية',
    'Teasing': 'سخرية',
    'Threats': 'تهديدات',
    'Inappropriate Language': 'لغة غير لائقة',
    'Defiance / Insubordination': 'عصيان / عدم احترام التعليمات'
  }
  return translations[behavior] || behavior
}

function translateMotivation(motivation) {
  const translations = {
    'Gain peer attention': 'جذب انتباه الزملاء',
    'Gain items/activities': 'الحصول على أشياء أو أنشطة',
    'Avoid task': 'تجنب المهمة',
    'Escape/Avoid peer or adult': 'الهروب من زميل أو شخص بالغ'
  }
  return translations[motivation] || motivation
}

function translateOthersInvolved(others) {
  const translations = {
    'None': 'لا أحد',
    'Peers': 'الزملاء',
    'Staff': 'أحد الموظفين',
    'Multiple students': 'عدة طلاب'
  }
  return translations[others] || others
}

// Lifecycle
onMounted(() => {
  loadIncidents()
})

watch(() => props.date, () => {
  loadIncidents()
})

watch(() => props.classroomId, () => {
  loadIncidents()
})
</script>

<style