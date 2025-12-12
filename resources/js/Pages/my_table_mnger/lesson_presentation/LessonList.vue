<template>
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
            {{ viewMode === 'grades' ? 'Select a grade to view lessons.' : 'Manage your interactive lessons for this grade.' }}
          </p>
        </div>
        
        <q-btn
          color="primary"
          icon="add"
          label="Create New Lesson"
          no-caps
          @click="showAddDialog = true"
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
            class="cursor-pointer hover-shadow transition-generic full-height"
            @click="selectGrade(grade)"
          >
            <q-card-section class="text-center q-py-lg">
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
            <q-card-actions align="center" class="bg-grey-1 q-py-sm">
              <span class="text-primary text-weight-medium text-caption">View Lessons <q-icon name="arrow_forward" size="xs" /></span>
            </q-card-actions>
          </q-card>
        </div>
      </div>

      <!-- View 2: Lesson List -->
      <div v-else-if="viewMode === 'lessons'">
        <!-- Empty State -->
        <div v-if="lessons.length === 0" class="text-center q-py-xl bg-white rounded-borders shadow-1">
          <q-icon name="auto_stories" size="4rem" color="grey-4" class="q-mb-md" />
          <h3 class="text-h6 text-weight-medium text-grey-9 q-my-none">No lessons found for {{ selectedGrade?.name }}</h3>
          <p class="text-body2 text-grey-6 q-mt-xs">Get started by creating your first interactive lesson.</p>
          <div class="q-mt-md">
            <Link :href="route('lesson-presentation.edit')">
              <q-btn
                color="primary"
                icon="add"
                label="Create Lesson"
                no-caps
              />
            </Link>
          </div>
        </div>

        <!-- Lesson Grid -->
        <div v-else class="row q-col-gutter-md">
          <div
            v-for="lesson in lessons"
            :key="lesson.id"
            class="col-12 col-sm-6 col-lg-4"
          >
            <q-card class="column full-height hover-shadow transition-generic">
              <q-card-section class="col q-pb-none">
                <div class="row items-center q-gutter-xs q-mb-sm">
                  <!-- Learn Section Badge -->
                  <q-badge color="blue-1" text-color="primary">
                    <q-icon name="menu_book" size="xs" class="q-mr-xs" />
                    {{ lesson.slides_count || 0 }}
                  </q-badge>
                  
                  <!-- Practice Section Badge -->
                  <q-badge color="purple-1" text-color="purple">
                    <q-icon name="edit" size="xs" class="q-mr-xs" />
                    Practice
                  </q-badge>

                  <!-- Quiz Section Badge -->
                  <q-badge v-if="lesson.quiz_id" color="green-1" text-color="green">
                    <q-icon name="quiz" size="xs" class="q-mr-xs" />
                    Quiz: {{ lesson.quiz_id }}
                  </q-badge>
                  
                  <q-space />
                  
                  <span class="text-caption text-grey-5">
                    {{ new Date(lesson.created_at).toLocaleDateString() }}
                  </span>
                </div>
                <div class="text-h6 text-grey-9 ellipsis" :title="lesson.name">
                  {{ lesson.name }}
                </div>
                <div class="text-body2 text-grey-6 ellipsis-2-lines q-mt-xs">
                  {{ lesson.description || 'No description provided.' }}
                </div>
              </q-card-section>
              
              <q-card-actions class="bg-grey-1 border-top-grey-3 q-px-md q-py-sm">
                <div class="column full-width q-gutter-xs">
                  <!-- First Row: Edit, Delete, Preview -->
                  <div class="row justify-between items-center">
                    <div class="row q-gutter-xs">
                      <Link :href="route('lesson-presentation.edit', { id: lesson.id })">
                        <q-btn
                          flat
                          dense
                          size="sm"
                          color="grey-7"
                          icon="edit"
                          label="Edit"
                          no-caps
                          class="hover-text-primary"
                        />
                      </Link>
                      <q-btn
                        flat
                        dense
                        size="sm"
                        color="grey-7"
                        icon="delete"
                        label="Delete"
                        no-caps
                        class="hover-text-negative"
                        @click="deleteLesson(lesson)"
                      />
                    </div>
                    
                    <div class="row q-gutter-xs">
                       <a
                        :href="route('lesson-presentation.student.view', { id: lesson.id })"
                        target="_blank"
                        class="text-decoration-none"
                      >
                        <q-btn
                          flat
                          dense
                          size="sm"
                          color="primary"
                          icon="play_arrow"
                          label="Preview"
                          no-caps
                        />
                      </a>
                      <q-btn
                        flat
                        round
                        dense
                        size="sm"
                        color="grey-6"
                        icon="link"
                        @click="copyLink(lesson.id)"
                      >
                        <q-tooltip>Copy Student Link</q-tooltip>
                      </q-btn>
                      <a
                        :href="route('lesson-presentation.print', { id: lesson.id })"
                        target="_blank"
                        class="text-decoration-none"
                      >
                        <q-btn
                          flat
                          round
                          dense
                          size="sm"
                          color="grey-6"
                          icon="print"
                        >
                          <q-tooltip>Print Lesson</q-tooltip>
                        </q-btn>
                      </a>
                    </div>
                  </div>

                  <!-- Second Row: Progress Management -->
                  <q-separator />
                  <div class="row q-gutter-xs full-width">
                    <q-btn
                      unelevated
                      dense
                      size="sm"
                      color="positive"
                      icon="lock_open"
                      label="Open to All Students"
                      no-caps
                      class="col"
                      @click="openToAllStudents(lesson)"
                    />
                    <Link :href="route('lesson-presentation.teacher.progress', { lessonId: lesson.id })">
                      <q-btn
                        unelevated
                        dense
                        size="sm"
                        color="primary"
                        icon="assessment"
                        label="View Progress"
                        no-caps
                      />
                    </Link>
                  </div>
                </div>
              </q-card-actions>
            </q-card>
          </div>
        </div>
      </div>
    </div>

    <!-- Add New Presentation Dialog -->
    <AddNewPresentationDialog 
      v-model="showAddDialog"
      @submit="handleCreateLesson"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import { useQuasar } from 'quasar';
import { useTeacherStore } from '@/Stores/teacherStore';
import AddNewPresentationDialog from './components/AddNewPresentationDialog.vue';

const $q = useQuasar();
const teacherStore = useTeacherStore();
const lessons = ref([]);
const loading = ref(false);
const selectedGrade = ref(null);
const viewMode = ref('grades'); // 'grades' or 'lessons'
const showAddDialog = ref(false);

const selectGrade = (grade) => {
  selectedGrade.value = grade;
  viewMode.value = 'lessons';
  fetchLessons();
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
  // Navigate to edit page with query parameters for grade_id and subject_id
  const queryParams = new URLSearchParams({
    grade_id: data.grade_id,
    subject_id: data.subject_id
  }).toString();
  
  window.location.href = route('lesson-presentation.edit') + '?' + queryParams;
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
