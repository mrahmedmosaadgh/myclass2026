<template>
  <Head title="Lesson Dashboard" />
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
              @click="viewMode = 'dashboard'"
            >
              <q-tooltip>Back to Dashboard</q-tooltip>
            </q-btn>
            <h1 class="text-h4 text-weight-bold text-grey-9 q-my-none">
              {{ viewMode === 'dashboard' ? 'My Classes' : `${selectedClassroom?.name} - ${selectedSubject?.name} Lessons` }}
            </h1>
          </div>
          <p class="text-subtitle2 text-grey-7 q-mt-xs">
            {{ viewMode === 'dashboard' ? 'Select a class to manage lessons.' : 'Manage your interactive lessons for this class.' }}
          </p>
        </div>
        
      </div>

      <!-- Loading State -->
      <div v-if="loading || teacherStore.loading" class="row justify-center q-py-xl">
        <q-spinner color="primary" size="3em" />
      </div>

      <!-- View 1: Subject Tabs & Classroom Grid -->
      <div v-else-if="viewMode === 'dashboard'">
        <div v-if="subjectsList.length === 0" class="col-12 text-center q-py-xl">
           <q-icon name="school" size="4rem" color="grey-4" class="q-mb-md" />
           <h3 class="text-h6 text-grey-7">No classes assigned.</h3>
        </div>

        <div v-else>
          <!-- Subject Tabs -->
          <div v-if="subjectsList.length > 1" class="q-mb-lg">
             <q-card flat bordered class="rounded-borders">
                <q-tabs
                  v-model="activeSubjectTab"
                  dense
                  class="text-grey-7 bg-grey-1"
                  active-color="white"
                  active-bg-color="primary"
                  indicator-color="transparent"
                  align="left"
                  narrow-indicator
                  content-class="q-pa-sm"
                >
                  <q-tab 
                    v-for="subject in subjectsList" 
                    :key="subject.id" 
                    :name="subject.id" 
                    :label="subject.name" 
                    class="rounded-borders q-mr-sm"
                  />
                </q-tabs>
            </q-card>
          </div>

          <!-- Classrooms Grid for Active Subject -->
          <div class="q-mt-md row q-col-gutter-lg">
             <div
              v-for="(classroom, index) in activeSubjectClassrooms"
              :key="classroom.id"
              class="col-12 col-sm-6 col-md-4 col-lg-3"
            >
              <q-card 
                class="cursor-pointer hover-shadow transition-generic full-height column no-wrap overflow-hidden rounded-borders-lg"
                @click="selectClassroom(classroom)"
              >
                <!-- Colorful Header -->
                <div 
                    class="q-pa-md text-white row items-center justify-between"
                    :class="['bg-blue-6', 'bg-purple-6', 'bg-teal-6', 'bg-indigo-6', 'bg-deep-orange-6'][index % 5]"
                >
                    <div class="text-h6 text-weight-bold">{{ classroom.name }}</div>
                    <q-icon name="class" size="24px" class="opacity-80" />
                </div>

                <q-card-section class="col flex flex-center column q-py-lg bg-white relative-position">
                     <q-avatar 
                        size="60px" 
                        font-size="30px" 
                        :class="['text-blue-6', 'text-purple-6', 'text-teal-6', 'text-indigo-6', 'text-deep-orange-6'][index % 5]"
                        color="grey-2" 
                        icon="school" 
                     />
                     <div class="text-subtitle1 text-grey-9 text-weight-bold q-mt-md">{{ classroom.grade_name }}</div>
                     <div class="text-caption text-grey-6 q-mt-xs text-center">
                        Manage lessons & student progress
                     </div>
                </q-card-section>
                
                <q-separator />
                
                <q-card-actions align="center" class="bg-grey-1 q-pa-md row q-gutter-md">
                   <q-btn 
                    flat 
                    dense 
                    color="grey-8" 
                    icon="visibility" 
                    label="View" 
                    class="col"
                    @click.stop="selectClassroom(classroom)"
                  />
                  <!-- Direct Create Link with params -->
                  <Link 
                    :href="route('lesson-presentation.edit', { 
                      grade_id: classroom.grade_id, 
                      subject_id: activeSubjectTab 
                    })"
                    class="col"
                  >
                    <q-btn 
                      unelevated 
                      dense 
                      :color="['blue-6', 'purple-6', 'teal-6', 'indigo-6', 'deep-orange-6'][index % 5]"
                      icon="add" 
                      label="Create" 
                      class="full-width"
                    />
                  </Link>
                </q-card-actions>
              </q-card>
            </div>
          </div>
        </div>
      </div>

      <!-- View 2: Lesson List -->
      <div v-else-if="viewMode === 'lessons'">
        <!-- Empty State -->
        <div v-if="lessons.length === 0" class="text-center q-py-xl bg-white rounded-borders shadow-1">
          <q-icon name="auto_stories" size="4rem" color="grey-4" class="q-mb-md" />
          <h3 class="text-h6 text-weight-medium text-grey-9 q-my-none">No lessons found</h3>
          <p class="text-body2 text-grey-6 q-mt-xs">Get started by creating your first interactive lesson for {{ selectedClassroom?.name }}.</p>
          <div class="q-mt-md">
            <Link :href="route('lesson-presentation.edit', { 
                  grade_id: selectedClassroom?.grade_id, 
                  subject_id: activeSubjectTab 
                })">
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
                  <q-badge color="blue-1" text-color="primary">
                    <q-icon name="menu_book" size="xs" class="q-mr-xs" />
                    {{ lesson.slides_count || 0 }}
                  </q-badge>
                  <q-badge v-if="lesson.quiz_id" color="green-1" text-color="green">
                    <q-icon name="quiz" size="xs" class="q-mr-xs" />
                    Quiz
                  </q-badge>
                  <q-space />
                  <span class="text-caption text-grey-5">{{ new Date(lesson.created_at).toLocaleDateString() }}</span>
                </div>
                <div class="text-h6 text-grey-9 ellipsis" :title="lesson.name">{{ lesson.name }}</div>
                <div class="text-body2 text-grey-6 ellipsis-2-lines q-mt-xs">
                  {{ lesson.description || 'No description provided.' }}
                </div>
              </q-card-section>
              
              <q-card-actions class="bg-grey-1 border-top-grey-3 q-px-md q-py-sm">
                <div class="column full-width q-gutter-xs">
                  <div class="row justify-between items-center">
                    <div class="row q-gutter-xs">
                      <Link :href="route('lesson-presentation.edit', { id: lesson.id })">
                        <q-btn flat dense size="sm" color="grey-7" icon="edit" label="Edit" no-caps />
                      </Link>
                      <q-btn flat dense size="sm" color="grey-7" icon="delete" label="Delete" no-caps @click="deleteLesson(lesson)" />
                    </div>
                    <div class="row q-gutter-xs">
                       <a :href="route('lesson-presentation.student.view', { id: lesson.id })" target="_blank">
                        <q-btn flat dense size="sm" color="primary" icon="play_arrow" label="Preview" no-caps />
                      </a>
                    </div>
                  </div>
                   <q-separator />
                  <div class="row q-gutter-xs full-width">
                    <Link :href="route('lesson-presentation.teacher.progress', { lessonId: lesson.id })">
                      <q-btn unelevated dense size="sm" color="primary" icon="assessment" label="View Progress" no-caps />
                    </Link>
                  </div>
                </div>
              </q-card-actions>
            </q-card>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';
import { useQuasar } from 'quasar';
import { useTeacherStore } from '@/Stores/teacherStore';

const $q = useQuasar();
const teacherStore = useTeacherStore();
const lessons = ref([]);
const loading = ref(false);

const viewMode = ref('dashboard'); // 'dashboard' or 'lessons'
const activeSubjectTab = ref(null);
const selectedClassroom = ref(null);

// Transform teacher store data into Subject -> Classrooms list
const subjectsList = computed(() => {
  const map = new Map();
  
  if (!teacherStore.grades) return [];

  teacherStore.grades.forEach(grade => {
    if (grade.classrooms) {
      grade.classrooms.forEach(classroom => {
        if (classroom.subjects) {
          classroom.subjects.forEach(subject => {
            if (!map.has(subject.id)) {
              map.set(subject.id, {
                id: subject.id,
                name: subject.name,
                classrooms: []
              });
            }
            // Add classroom to this subject collection
            map.get(subject.id).classrooms.push({
              id: classroom.id,
              name: classroom.name,
              grade_id: grade.id,
              grade_name: grade.name
            });
          });
        }
      });
    }
  });
  
  return Array.from(map.values());
});

// Set first subject as active by default
watch(subjectsList, (newVal) => {
  if (newVal.length > 0 && !activeSubjectTab.value) {
    activeSubjectTab.value = newVal[0].id;
  }
}, { immediate: true });

const activeSubjectClassrooms = computed(() => {
  const subject = subjectsList.value.find(s => s.id === activeSubjectTab.value);
  return subject ? subject.classrooms : [];
});

const selectedSubject = computed(() => {
  return subjectsList.value.find(s => s.id === activeSubjectTab.value);
});

const selectClassroom = (classroom) => {
  selectedClassroom.value = classroom;
  viewMode.value = 'lessons';
  fetchLessons();
};

const fetchLessons = async () => {
  if (!selectedClassroom.value || !activeSubjectTab.value) return;
  
  loading.value = true;
  try {
    const params = { 
      grade_id: selectedClassroom.value.grade_id,
      subject_id: activeSubjectTab.value 
    };
    const response = await axios.get(route('lesson-presentation.list'), { params });
    lessons.value = response.data;
  } catch (error) {
    console.error('Failed to fetch lessons:', error);
    $q.notify({ type: 'negative', message: 'Failed to fetch lessons.' });
  } finally {
    loading.value = false;
  }
};

const deleteLesson = async (lesson) => {
  $q.dialog({
    title: 'Confirm Deletion',
    message: `Delete "${lesson.name}"?`,
    cancel: true,
    persistent: true
  }).onOk(async () => {
    try {
      await axios.delete(route('lesson-presentation.destroy', { id: lesson.id }));
      lessons.value = lessons.value.filter(l => l.id !== lesson.id);
      $q.notify({ type: 'positive', message: 'Lesson deleted.' });
    } catch (error) {
      $q.notify({ type: 'negative', message: 'Failed to delete lesson.' });
    }
  });
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
.text-decoration-none {
  text-decoration: none;
}
</style>
