<template>
  <Head :title="`Manage Lesson - ${lesson?.name || 'Loading...'}`" />
  <div class="q-pa-md bg-grey-2 window-height">
    <div class="max-w-4xl mx-auto">
      
      <!-- Back Button -->
      <div class="q-mb-md">
        <Link :href="route('lesson-presentation.index')">
          <q-btn flat dense icon="arrow_back" label="Back to Lessons" color="primary" no-caps />
        </Link>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="row justify-center q-py-xl">
        <q-spinner color="primary" size="3em" />
        <div class="q-mt-md text-grey-7">Loading lesson details...</div>
      </div>

      <div v-else-if="lesson">
        <!-- Lesson Info Application Card -->
        <q-card class="q-mb-lg shadow-1 rounded-borders-lg">
          <q-card-section class="bg-primary text-white q-py-lg">
            <div class="text-h4 text-weight-bold">{{ lesson.name }}</div>
            <div class="text-subtitle1 opacity-80 q-mt-sm">
              <q-icon name="class" size="sm" class="q-mr-xs" />
              {{ lesson.grade?.name || 'No Grade' }} - {{ lesson.subject?.name || 'No Subject' }}
            </div>
          </q-card-section>

          <q-card-section class="q-pa-lg">
            <div class="text-body1 text-grey-8" style="white-space: pre-wrap;">
              {{ lesson.description || 'No description provided.' }}
            </div>
            
            <div class="row q-gutter-md q-mt-md">
              <q-badge color="blue-1" text-color="primary" class="q-py-xs q-px-sm">
                <q-icon name="menu_book" size="xs" class="q-mr-xs" />
                {{ lesson.slides_count || 0 }} Slides
              </q-badge>
              <q-badge v-if="lesson.quiz_id" color="green-1" text-color="green" class="q-py-xs q-px-sm">
                <q-icon name="quiz" size="xs" class="q-mr-xs" />
                Quiz Attached
              </q-badge>
              <q-badge color="grey-2" text-color="grey-8" class="q-py-xs q-px-sm">
                <q-icon name="event" size="xs" class="q-mr-xs" />
                Created: {{ new Date(lesson.created_at).toLocaleDateString() }}
              </q-badge>
            </div>
          </q-card-section>
        </q-card>

        <!-- Management Options Grid -->
        <div class="text-h5 text-weight-bold text-grey-9 q-mb-md">Management Options</div>
        
        <div class="row q-col-gutter-md">
          <!-- Edit Lesson -->
          <div class="col-12 col-sm-6">
            <Link :href="route('lesson-presentation.edit', { id: lesson.id })" class="text-decoration-none">
              <q-card class="hover-card cursor-pointer full-height bg-white">
                <q-card-section class="text-center q-py-xl">
                  <q-avatar color="blue-1" text-color="primary" size="70px" icon="edit" class="q-mb-md shadow-1" />
                  <div class="text-h6 text-grey-9 text-weight-bold">Edit Content</div>
                  <p class="text-grey-6 q-mt-sm">Modify slides, add media, and update quiz.</p>
                </q-card-section>
              </q-card>
            </Link>
          </div>

          <!-- Preview Lesson -->
          <div class="col-12 col-sm-6">
            <a :href="route('lesson-presentation.student.view', { id: lesson.id })" target="_blank" class="text-decoration-none">
              <q-card class="hover-card cursor-pointer full-height bg-white">
                <q-card-section class="text-center q-py-xl">
                  <q-avatar color="purple-1" text-color="purple" size="70px" icon="visibility" class="q-mb-md shadow-1" />
                  <div class="text-h6 text-grey-9 text-weight-bold">Preview Lesson</div>
                  <p class="text-grey-6 q-mt-sm">View the lesson as a student would see it.</p>
                </q-card-section>
              </q-card>
            </a>
          </div>

          <!-- Student Progress -->
          <div class="col-12 col-sm-6">
            <Link :href="route('lesson-presentation.teacher.progress', { lessonId: lesson.id })" class="text-decoration-none">
              <q-card class="hover-card cursor-pointer full-height bg-white">
                <q-card-section class="text-center q-py-xl">
                  <q-avatar color="teal-1" text-color="teal" size="70px" icon="assessment" class="q-mb-md shadow-1" />
                  <div class="text-h6 text-grey-9 text-weight-bold">Student Progress</div>
                  <p class="text-grey-6 q-mt-sm">Track student completion and quiz scores.</p>
                </q-card-section>
              </q-card>
            </Link>
          </div>
          
           <!-- Print View -->
          <div class="col-12 col-sm-6">
            <a :href="route('lesson-presentation.print', { id: lesson.id })" target="_blank" class="text-decoration-none">
              <q-card class="hover-card cursor-pointer full-height bg-white">
                <q-card-section class="text-center q-py-xl">
                  <q-avatar color="orange-1" text-color="orange" size="70px" icon="print" class="q-mb-md shadow-1" />
                  <div class="text-h6 text-grey-9 text-weight-bold">Print View</div>
                  <p class="text-grey-6 q-mt-sm">Printer-friendly version of the lesson.</p>
                </q-card-section>
              </q-card>
            </a>
          </div>

          <!-- Delete Lesson -->
          <div class="col-12">
            <q-card 
              class="hover-card cursor-pointer bg-red-1 border-red"
              @click="deleteLesson"
            >
              <q-card-section class="row items-center">
                <q-avatar color="red-2" text-color="red" size="50px" icon="delete" class="q-mr-md" />
                <div>
                  <div class="text-h6 text-red-9 text-weight-bold">Delete Lesson</div>
                  <div class="text-red-7">Permanently remove this lesson and all associated data.</div>
                </div>
                <q-space />
                <q-icon name="chevron_right" color="red" size="md" />
              </q-card-section>
            </q-card>
          </div>

        </div>
      </div>
      
      <div v-else class="text-center q-py-xl">
         <q-icon name="error_outline" size="4rem" color="grey-4" />
         <div class="text-h6 text-grey-7 q-mt-md">Lesson not found</div>
         <div class="q-mt-md">
            <Link :href="route('lesson-presentation.index')">
              <q-btn color="primary" label="Return to Dashboard" no-caps />
            </Link>
         </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import { useQuasar } from 'quasar';

const props = defineProps({
  lessonId: {
    type: [Number, String],
    required: true
  }
});

const $q = useQuasar();
const lesson = ref(null);
const loading = ref(true);

const fetchLesson = async () => {
  loading.value = true;
  try {
    const response = await axios.get(route('lesson-presentation.show', props.lessonId));
    lesson.value = response.data;
  } catch (error) {
    console.error('Failed to fetch lesson:', error);
    $q.notify({ type: 'negative', message: 'Failed to load lesson details.' });
  } finally {
    loading.value = false;
  }
};

const deleteLesson = () => {
  $q.dialog({
    title: 'Delete Lesson?',
    message: `Are you sure you want to delete "${lesson.value.name}"? This action cannot be undone.`,
    ok: {
      label: 'Delete',
      color: 'negative'
    },
    cancel: true,
  }).onOk(async () => {
    try {
      await axios.delete(route('lesson-presentation.destroy', { id: props.lessonId }));
      $q.notify({ type: 'positive', message: 'Lesson deleted successfully.' });
      router.visit(route('lesson-presentation.index'));
    } catch (error) {
      console.error('Failed to delete lesson:', error);
      $q.notify({ type: 'negative', message: 'Failed to delete lesson.' });
    }
  });
};

onMounted(() => {
  if (props.lessonId) {
    fetchLesson();
  }
});
</script>

<style scoped>
.hover-card {
  transition: all 0.3s ease;
  border: 1px solid transparent;
}
.hover-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 20px rgba(0,0,0,0.1);
  border-color: var(--q-primary);
}
.border-red {
    border: 1px solid #fecaca;
}
.border-red:hover {
    border-color: #ef4444;
    background-color: #fee2e2;
}
.text-decoration-none {
  text-decoration: none;
}
</style>
