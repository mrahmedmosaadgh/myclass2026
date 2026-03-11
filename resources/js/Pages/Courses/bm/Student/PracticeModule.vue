<template>
  <Head title="Basic Math - Practice" />
  <div class="bm-practice-module q-pa-md fullscreen bg-blue-grey-1">
    <div class="content-wrapper q-mx-auto" style="max-width: 600px">
      <!-- Header -->
      <div class="row justify-between items-center q-mb-xl q-pt-md">
        <q-btn flat icon="close" color="dark" class="q-mr-sm" @click="$inertia.visit(route('bm.assessment.index'))" />
        <div class="text-h5 text-weight-bold text-primary">Daily Practice</div>
        <q-chip color="positive" text-color="white" icon="local_fire_department">Streak: 3</q-chip>
      </div>

      <!-- Game Card -->
      <q-card class="shadow-4 q-pa-xl text-center" style="border-radius: 24px;">
        <div class="text-overline text-grey-7 q-mb-md">QUESTION {{ currentIndex + 1 }} OF 10</div>
        
        <div class="text-h2 text-weight-bold text-dark q-mb-xl" style="letter-spacing: 2px;">
          {{ currentQuestion.text }}
        </div>

        <!-- Answer Input -->
        <q-input 
          v-model="userAnswer" 
          outlined 
          class="text-h4 q-mb-lg" 
          input-class="text-center text-weight-bold" 
          placeholder="?" 
          type="number" 
          color="primary"
          @keyup.enter="checkAnswer"
        />

        <q-btn 
          color="primary" 
          size="lg" 
          label="Submit Answer" 
          class="full-width q-py-sm" 
          style="border-radius: 12px; font-weight: bold; font-size: 1.2rem;" 
          unelevated 
          @click="checkAnswer"
        />
      </q-card>
      
    </div>
  </div>
</template>

<script setup>
defineOptions({ layout: BMLayout });
import BMLayout from "@/Layouts/BMLayout.vue";
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useQuasar } from 'quasar';

const $q = useQuasar();

const currentIndex = ref(0);
const userAnswer = ref('');

// Mock questions for the UI setup
const questions = [
  { text: '12 + 8 =', answer: 20 },
  { text: '15 - 7 =', answer: 8 },
  { text: '4 × 6 =', answer: 24 }
];

const currentQuestion = ref(questions[0]);

const checkAnswer = () => {
  if (!userAnswer.value) return;

  const isCorrect = parseInt(userAnswer.value) === currentQuestion.value.answer;

  $q.notify({
    message: isCorrect ? 'Correct! Great job!' : 'Oops! Try again.',
    color: isCorrect ? 'positive' : 'negative',
    icon: isCorrect ? 'check_circle' : 'warning',
    position: 'top',
    timeout: 2000
  });

  if (isCorrect) {
    setTimeout(() => {
      currentIndex.value++;
      if (currentIndex.value < questions.length) {
        currentQuestion.value = questions[currentIndex.value];
        userAnswer.value = '';
      } else {
        $q.notify({ message: 'Practice Complete! +50 XP', color: 'info', icon: 'star' });
      }
    }, 1000);
  }
};
</script>
