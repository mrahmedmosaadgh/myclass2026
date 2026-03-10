<template>
  <Head :title="presentation.name ? `Lesson: ${presentation.name}` : 'Lesson View'" />
  <LessonPlayer
    v-if="!loading"
    :presentation="presentation"
    :sections="sections"
    :slides="slides"
    :progress="progress"
    :loading="loading"
    @complete-learn="completeLearn"
    @submit-practice="onPracticeSubmitted"
  />
  <div v-else class="h-screen flex items-center justify-center bg-gray-50">
    <q-spinner color="primary" size="3em" />
  </div>
  <!-- Teacher Controls -->
  <div v-if="isTeacher" class="fixed-bottom-right q-ma-md" style="z-index: 2000">
      <q-btn fab icon="emoji_events" color="warning" @click="showRewardDialog = true; isRewardMinimized = false">
          <q-tooltip>Reward System</q-tooltip>
      </q-btn>
  </div>

  <q-dialog 
    v-model="showRewardDialog" 
    :maximized="!isRewardMinimized" 
    :seamless="isRewardMinimized"
    :position="isRewardMinimized ? 'bottom' : 'standard'"
    transition-show="slide-up" 
    transition-hide="slide-down"
  >
     <q-card v-bind:style="isRewardMinimized ? 'width: 300px' : ''">
        <q-bar class="bg-primary text-white">
           <q-icon name="emoji_events" />
           <div class="text-h6 q-ml-sm" v-if="!isRewardMinimized">Reward System</div>
           <div class="text-subtitle2 q-ml-sm" v-else>Rewards</div>
           <q-space />
           
           <!-- Explicit Maximize Button when Minimized -->
           <q-btn 
             v-if="isRewardMinimized" 
             dense 
             flat 
             no-caps
             label="Maximize"
             icon="open_in_full" 
             @click="isRewardMinimized = false"
           >
              <q-tooltip>Maximize</q-tooltip>
           </q-btn>
           
           <q-btn 
             v-else 
             dense 
             flat 
             icon="minimize" 
             @click="isRewardMinimized = true"
           >
              <q-tooltip>Minimize</q-tooltip>
           </q-btn>
           
           <q-btn dense flat icon="close" v-close-popup>
              <q-tooltip>Close</q-tooltip>
           </q-btn>
        </q-bar>
        
        <q-card-section v-show="!isRewardMinimized" class="q-pa-none" style="height: calc(100vh - 32px)">
           <keep-alive>
              <RewardSystem :isDialog="true" />
           </keep-alive>
        </q-card-section>
     </q-card>
  </q-dialog>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { useQuasar } from 'quasar';
import LessonPlayer from './components/LessonPlayer.vue';
import RewardSystem from '@/Pages/old_features/my_table_mnger/reward_sys/reward_sys.vue';

const $q = useQuasar();
const page = usePage();

const props = defineProps({
  presentationId: {
    type: [String, Number],
    required: true
  },
  studentId: {
    type: [String, Number],
    required: true
  },
  sections: {
    type: Array,
    default: () => []
  }
});

const presentation = ref({});
const slides = ref([]);
const progress = ref(null);
const loading = ref(true);
const showRewardDialog = ref(false);
const isRewardMinimized = ref(false);

const isTeacher = computed(() => {
  const role = page.props.auth?.user?.role;
  return role === 'teacher' || role === 'admin';
});

const fetchLessonData = async () => {
  try {
    loading.value = true;
    // Fetch Lesson
    const lessonResponse = await axios.get(route('lesson-presentation.show', { id: props.presentationId }));
    presentation.value = lessonResponse.data;
    slides.value = (lessonResponse.data.slides || []).map(slide => ({
      ...slide,
      section: slide.section || 'learn'
    }));

    // Fetch Progress
    const progressResponse = await axios.get(route('lesson-presentation.progress.student', { studentId: props.studentId }));
    // Filter for this lesson
    const myProgress = progressResponse.data.find(p => p.lesson_presentation_id == props.presentationId);
    progress.value = myProgress;

  } catch (error) {
    console.error('Failed to load data:', error);
    $q.notify({ type: 'negative', message: 'Failed to load lesson data' });
  } finally {
    loading.value = false;
  }
};

const completeLearn = async () => {
  try {
    await axios.put(route('lesson-presentation.progress.complete-learn', { id: progress.value.id }));
    await fetchLessonData();
    $q.notify({ type: 'positive', message: 'Learn section completed! Practice unlocked.' });
  } catch (error) {
    console.error('Failed to complete learn:', error);
  }
};

const onPracticeSubmitted = async () => {
  await fetchLessonData();
  $q.notify({ type: 'positive', message: 'Practice submitted! Waiting for teacher review.' });
};

onMounted(() => {
  fetchLessonData();
});
</script>

<style scoped>
/* Add any specific styles here */
</style>
