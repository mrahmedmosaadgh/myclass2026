<template>
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
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useQuasar } from 'quasar';
import LessonPlayer from './components/LessonPlayer.vue';

const $q = useQuasar();

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
