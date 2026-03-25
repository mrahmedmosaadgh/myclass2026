<template>
  <Head :title="pageTitle" />
  <div class="q-pa-md bg-grey-2">
    <div class="max-w-7xl mx-auto">
      
      <!-- Header -->
      <div class="row justify-between items-center q-mb-lg">
        <div>
          <div class="row items-center gap-2">
            <q-btn
              v-if="viewMode === 'lessons'"
              flat
              round
              dense
              icon="arrow_back"
              color="primary"
              @click="viewMode = 'grades'; selectedGrade = null"
            >
              <q-tooltip>Back to Grades</q-tooltip>
            </q-btn>
            <h1 class="text-h4 text-weight-bold text-grey-9 q-my-none">
              {{ viewMode === 'grades' ? 'My Classes' : `${selectedGrade?.name} Lessons` }}
            </h1>
          </div>
          <p class="text-subtitle2 text-grey-7 q-mt-xs">
            {{ viewMode === 'grades' ? 'Select a grade to view or create lessons.' : 'Manage your interactive lessons for this grade.' }}
          </p>
        </div>
        <!-- Lesson Templates Management -->
        <q-btn
          v-if="availableSubjects.length > 0"
          color="secondary"
          icon="tune"
          label="Manage Templates"
          no-caps
          flat
          dense
          class="q-mr-sm"
          @click="showTemplateManager = true"
        /> 
      </div>

      <!-- Loading State -->
      <div v-if="loading || teacherStore.loading" class="row justify-center q-py-xl">
        <q-spinner color="primary" size="3em" />
      </div>

      <!-- View 1: Grade Cards -->
      <div v-else-if="viewMode === 'grades'" class="row q-col-gutter-md">
        <div v-if="teacherStore.grades.length === 0" class="col-12 text-center q-py-xl">
           <q-icon name="school" size="4rem" color="grey-4" class="q-mb-md" />
           <h3 class="text-h6 text-grey-7">No grades assigned.</h3>
        </div>

        <div
          v-for="grade in teacherStore.grades"
          :key="grade.id"
          class="col-12 col-sm-6 col-md-4 col-lg-3"
        >
          <q-card 
            class="hover-shadow transition-generic full-height column"
          >
            <q-card-section class="text-center q-py-lg col" @click="selectGrade(grade)" style="cursor: pointer;">
              <q-avatar size="80px" font-size="40px" color="blue-1" text-color="primary" icon="school" />
              <div class="text-h5 q-mt-md text-weight-bold text-grey-9">{{ grade.name }}</div>
              <div class="text-caption text-grey-6 q-mt-sm">
                <div v-if="grade.subjects && grade.subjects.length">
                  <q-chip 
                    v-for="subject in grade.subjects" 
                    :key="subject.id"
                    dense 
                    size="sm" 
                    color="blue-grey-1" 
                    text-color="blue-grey-8"
                  >
                    {{ subject.name }}
                  </q-chip>
                </div>
                <span v-else>No subjects assigned</span>
              </div>
            </q-card-section>
            <q-separator />
            <q-card-actions align="center" class="bg-grey-1 q-py-md q-px-md column q-gutter-sm">
 
              <q-btn
                flat
                color="primary"
                icon="arrow_forward"
                label="View Lessons"
                no-caps
                class="full-width"
                @click="selectGrade(grade)"
              />
            </q-card-actions>
          </q-card>
        </div>
      </div>

      <!-- View 2: Lesson List -->
      <div v-else-if="viewMode === 'lessons'">
        <!-- Subject Tabs or Single Subject Header -->
        <div v-if="gradeSubjects.length > 0">
          <!-- Multiple Subjects: Show Tabs -->
          <q-tabs
            v-if="gradeSubjects.length > 1"
            v-model="selectedSubjectTab"
            dense
            class="text-grey-7 bg-white rounded-borders shadow-1 q-mb-md"
            active-color="primary"
            indicator-color="primary"
            align="left"
          >
            <q-tab
              v-for="subject in gradeSubjects"
              :key="subject.id"
              :name="subject.id"
              :label="subject.name"
              class="text-weight-medium"
            />
          </q-tabs>
          
          <!-- Single Subject: Show Header -->
          <div v-else class="bg-white rounded-borders shadow-1 q-pa-md q-mb-md">
            <div class="row items-center justify-between">
              <div class="text-h6 text-grey-9">{{ gradeSubjects[0].name }}</div>
              <q-btn
                color="primary"
                icon="add"
                label="Create New Lesson"
                no-caps
                unelevated
                @click="openCreateDialogWithSubject(gradeSubjects[0])"
              />
            </div>
          </div>
        </div>

        <!-- Subject Tab Content (for multiple subjects) -->
        <div v-if="gradeSubjects.length > 1">
          <q-tab-panels v-model="selectedSubjectTab" animated>
            <q-tab-panel
              v-for="subject in gradeSubjects"
              :key="subject.id"
              :name="subject.id"
              class="q-pa-none"
            >
              <!-- Create Button for this subject -->
              <div class="row justify-end q-mb-md">
                <q-btn
                  color="primary"
                  icon="add"
                  label="Create New Lesson"
                  no-caps
                  unelevated
                  @click="openCreateDialogWithSubject(subject)"
                />
              </div>
              
              <!-- Lessons for this subject -->
              <div v-if="getSubjectLessons(subject.id).length === 0" class="text-center q-py-xl bg-white rounded-borders shadow-1">
                <q-icon name="auto_stories" size="4rem" color="grey-4" class="q-mb-md" />
                <h3 class="text-h6 text-weight-medium text-grey-9 q-my-none">No lessons found for {{ subject.name }}</h3>
                <p class="text-body2 text-grey-6 q-mt-xs">Get started by creating your first interactive lesson.</p>
              </div>
              
              <div v-else class="row q-col-gutter-md">
                <div
                  v-for="lesson in getSubjectLessons(subject.id)"
                  :key="lesson.id"
                  class="col-12 col-sm-6 col-lg-4"
                >
                  <LessonCard :lesson="lesson" @delete="deleteLesson" @copy-link="copyLink" @open-to-all="openToAllStudents" />
                </div>
              </div>
            </q-tab-panel>
          </q-tab-panels>
        </div>
        
        <!-- Lessons for single subject -->
        <div v-else-if="gradeSubjects.length === 1">
          <div v-if="lessons.length === 0" class="text-center q-py-xl bg-white rounded-borders shadow-1">
            <q-icon name="auto_stories" size="4rem" color="grey-4" class="q-mb-md" />
            <h3 class="text-h6 text-weight-medium text-grey-9 q-my-none">No lessons found for {{ gradeSubjects[0].name }}</h3>
            <p class="text-body2 text-grey-6 q-mt-xs">Get started by creating your first interactive lesson.</p>
          </div>
          
          <div v-else class="row q-col-gutter-md">
            <div
              v-for="lesson in lessons"
              :key="lesson.id"
              class="col-12 col-sm-6 col-lg-4"
            >
              <LessonCard :lesson="lesson" @delete="deleteLesson" @copy-link="copyLink" @open-to-all="openToAllStudents" />
            </div>
          </div>
        </div>
        
        <!-- No subjects assigned -->
        <div v-else class="text-center q-py-xl bg-white rounded-borders shadow-1">
          <q-icon name="subject" size="4rem" color="grey-4" class="q-mb-md" />
          <h3 class="text-h6 text-weight-medium text-grey-9 q-my-none">No subjects assigned to {{ selectedGrade?.name }}</h3>
          <p class="text-body2 text-grey-6 q-mt-xs">Please assign subjects to this grade first.</p>
        </div>


      </div>
    </div>

    <!-- Add New Presentation Dialog -->
    <AddNewPresentationDialog 
      v-model="showAddDialog"
      :preselected-grade="preselectedGrade"
      :preselected-subject="preselectedSubject"
      @submit="handleCreateLesson"
    />
    <!-- Lesson Template Manager Dialog -->
    <LessonTemplateManager
      v-model="showTemplateManager"
      :subjects="availableSubjects"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import { useQuasar } from 'quasar';
import { useTeacherStore } from '@/Stores/teacherStore';
import AddNewPresentationDialog from './components/AddNewPresentationDialog.vue';
import LessonTemplateManager from './components/LessonTemplateManager.vue';
import LessonCard from './components/LessonCard.vue';
import { Head } from '@inertiajs/vue3';

const $q = useQuasar();
const teacherStore = useTeacherStore();
const lessons = ref([]);
const loading = ref(false);
const selectedGrade = ref(null);
const viewMode = ref('grades'); // 'grades' or 'lessons'
const showAddDialog = ref(false);
const preselectedGrade = ref(null);
const preselectedSubject = ref(null);
const selectedSubjectTab = ref(null);
const showTemplateManager = ref(false);

const pageTitle = computed(() => {
  if (viewMode.value === 'lessons' && selectedGrade.value) {
    return `${selectedGrade.value.name} Lessons`;
  }
  return 'My Classes';
});

const gradeSubjects = computed(() => {
  if (!selectedGrade.value || !selectedGrade.value.subjects) return [];
  return selectedGrade.value.subjects;
});

const availableSubjects = computed(() => {
  // If in lesson mode (specific grade), show that grade's subjects
  if (viewMode.value === 'lessons' && selectedGrade.value) {
    return gradeSubjects.value;
  }
  // Otherwise (grades mode), collect unique subjects from all grades
  const subjectsMap = new Map();
  teacherStore.grades.forEach(grade => {
    if (grade.subjects) {
      grade.subjects.forEach(subject => {
        if (!subjectsMap.has(subject.id)) {
          subjectsMap.set(subject.id, subject);
        }
      });
    }
  });
  return Array.from(subjectsMap.values());
});

const selectGrade = (grade) => {
  selectedGrade.value = grade;
  viewMode.value = 'lessons';
  // Set first subject as default tab
  if (grade.subjects && grade.subjects.length > 0) {
    selectedSubjectTab.value = grade.subjects[0].id;
  }
  fetchLessons();
};

const openCreateDialog = (grade) => {
  preselectedGrade.value = grade;
  preselectedSubject.value = null;
  showAddDialog.value = true;
};

const openCreateDialogWithSubject = (subject) => {
  preselectedGrade.value = selectedGrade.value;
  preselectedSubject.value = subject;
  showAddDialog.value = true;
};

const getSubjectLessons = (subjectId) => {
  return lessons.value.filter(lesson => lesson.subject_id === subjectId);
};

const openCreateDialogForCurrentView = () => {
  // When in lesson view with single subject, preselect it
  if (gradeSubjects.value.length === 1) {
    openCreateDialogWithSubject(gradeSubjects.value[0]);
  } else if (gradeSubjects.value.length > 1 && selectedSubjectTab.value) {
    // When in lesson view with multiple subjects, use the active tab
    const activeSubject = gradeSubjects.value.find(s => s.id === selectedSubjectTab.value);
    if (activeSubject) {
      openCreateDialogWithSubject(activeSubject);
    } else {
      openCreateDialog(selectedGrade.value);
    }
  } else {
    openCreateDialog(selectedGrade.value);
  }
};

const fetchLessons = async () => {
  if (!selectedGrade.value) return;
  
  loading.value = true;
  try {
    const params = { grade_id: selectedGrade.value.id };
    const response = await axios.get(route('lesson-presentation.list'), { params });
    lessons.value = response.data;
  } catch (error) {
    console.error('Failed to fetch lessons:', error);
    $q.notify({
      type: 'negative',
      message: 'Failed to fetch lessons.'
    });
  } finally {
    loading.value = false;
  }
};

const deleteLesson = async (lesson) => {
  $q.dialog({
    title: 'Confirm Deletion',
    message: `Are you sure you want to delete "${lesson.name}"?`,
    cancel: true,
    persistent: true
  }).onOk(async () => {
    try {
      await axios.delete(route('lesson-presentation.destroy', { id: lesson.id }));
      lessons.value = lessons.value.filter(l => l.id !== lesson.id);
      $q.notify({
        type: 'positive',
        message: 'Lesson deleted successfully.'
      });
    } catch (error) {
      console.error('Failed to delete lesson:', error);
      $q.notify({
        type: 'negative',
        message: 'Failed to delete lesson.'
      });
    }
  });
};

const openToAllStudents = async (lesson) => {
  $q.dialog({
    title: 'Open Lesson to All Students',
    message: `This will unlock "${lesson.name}" for all students in ${selectedGrade.value?.name}. Continue?`,
    cancel: true,
    persistent: true
  }).onOk(async () => {
    try {
      // Fetch all students for the selected grade
      const studentsResponse = await axios.get(route('lesson-presentation.students.by-grade', { gradeId: selectedGrade.value.id }));
      const studentIds = studentsResponse.data.map(s => s.id);

      if (studentIds.length === 0) {
        $q.notify({
          type: 'warning',
          message: 'No students found in this grade.',
          position: 'top'
        });
        return;
      }

      // Open lesson for all students
      await axios.post(route('lesson-presentation.progress.open'), {
        lesson_id: lesson.id,
        student_ids: studentIds
      });

      $q.notify({
        type: 'positive',
        message: `Lesson opened for ${studentIds.length} student(s)!`,
        position: 'top'
      });
    } catch (error) {
      console.error('Failed to open lesson:', error);
      $q.notify({
        type: 'negative',
        message: error.response?.data?.message || 'Failed to open lesson for students.',
        position: 'top'
      });
    }
  });
};

const copyLink = (id) => {
  const url = route('lesson-presentation.student.view', { id });
  navigator.clipboard.writeText(url).then(() => {
    $q.notify({
      type: 'positive',
      message: 'Student link copied to clipboard!',
      position: 'top'
    });
  });
};

const handleCreateLesson = (data) => {
  // Navigate to edit page with query parameters for grade_id, subject_id, and template_id
  const queryParams = {
    grade_id: data.grade_id,
    subject_id: data.subject_id,
  };

  // Add template_id if provided
  if (data.template_id) {
    queryParams.template_id = data.template_id;
  }

  router.visit(route('lesson-presentation.edit', queryParams));
};

onMounted(async () => {
  await teacherStore.fetchTeacherData();
});
</script>

<style scoped>
.hover-shadow:hover {
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
.transition-generic {
  transition: all 0.3s ease;
}
.hover-text-primary:hover {
  color: var(--q-primary) !important;
}
.hover-text-negative:hover {
  color: var(--q-negative) !important;
}
.text-decoration-none {
  text-decoration: none;
}
</style>
