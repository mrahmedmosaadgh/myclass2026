<template>
  <Head title="Basic Math - Results" />
  <div class="bm-assessment-results bg-blue-1 fullscreen flex flex-center column q-pa-md">
    <div class="results-card bg-white shadow-4 q-pa-xl text-center" style="max-width: 700px; width: 100%; border-radius: 24px;">
      
      <div class="text-h3 text-weight-bolder text-primary q-mb-sm">Mission Complete!</div>
      <p class="text-subtitle1 text-grey-7 q-mb-lg">Here is your official Basic Math Score Report.</p>

      <div class="row q-col-gutter-lg q-items-center">
        <!-- Main Score -->
        <div class="col-12 col-md-5">
          <div class="score-circle text-white flex flex-center column shadow-6 q-mx-auto" 
               :class="score.final_score >= 80 ? 'bg-positive' : 'bg-negative'"
               style="width: 180px; height: 180px; border-radius: 50%;">
            <div class="text-h2 text-weight-bold">{{ score.final_score }}%</div>
            <div class="text-subtitle2 text-uppercase letter-spacing-1 q-mt-xs">{{ scoreLevel }}</div>
          </div>
          
          <div class="q-mt-xl text-center">
            <template v-if="score.final_score >= 80">
              <div class="text-h6 text-positive text-weight-bold q-mb-md">Great Job! You Passed!</div>
              <q-btn icon="share" label="Share Badge" flat color="primary" class="q-mr-sm" />
              <q-btn label="Go to Dashboard" color="primary" rounded unelevated :to="route('bm.learning_path')" />
            </template>
            <template v-else>
              <div class="text-h6 text-negative text-weight-bold q-mb-sm">Score Too Low</div>
              <p class="text-grey-8 q-mb-md">You need at least 80% to proceed. Let's try again!</p>
              <q-btn icon="refresh" label="Redo Assessment" color="negative" size="lg" rounded unelevated @click="redoAssessment" class="full-width" />
            </template>
          </div>
        </div>

        <!-- Radar Chart -->
        <div class="col-12 col-md-7">
          <BMScoreRadar :scores="domainScores" />
        </div>
      </div>

      <q-separator class="q-my-xl" />

      <!-- Gap Report -->
      <div class="text-left">
        <div class="text-h5 text-weight-bold text-dark q-mb-md">Your Learning Path</div>
        <q-list bordered class="rounded-borders">
          <q-item v-for="(rec, index) in recommendations" :key="index" clickable v-ripple>
            <q-item-section avatar>
              <q-avatar :color="rec.color" text-color="white" :icon="rec.icon" />
            </q-item-section>
            <q-item-section>
              <q-item-label class="text-weight-bold">{{ rec.title }}</q-item-label>
              <q-item-label caption>{{ rec.description }}</q-item-label>
            </q-item-section>
            <q-item-section side>
              <q-btn outline color="primary" label="Start module" size="sm" />
            </q-item-section>
          </q-item>
        </q-list>
      </div>

      <!-- New Badges Dialog -->
      <q-dialog v-model="showBadgeDialog" persistent>
        <q-card class="bg-primary text-white text-center q-pa-xl" style="border-radius: 24px; max-width: 400px;">
          <q-icon name="emoji_events" size="80px" color="warning" class="q-mb-md" />
          <div class="text-h4 text-weight-bold q-mb-sm">New Badges Unlocked!</div>
          <div class="text-subtitle1 q-mb-lg">You earned {{ newBadges.length }} new badge(s).</div>
          
          <div v-for="(badge, idx) in newBadges" :key="idx" class="q-mb-md bg-white text-dark q-pa-sm" style="border-radius: 12px;">
             <q-avatar :color="badge.color" text-color="white" :icon="badge.icon" size="40px" class="q-mb-xs" />
             <div class="text-weight-bold">{{ badge.name }}</div>
             <div class="text-caption">{{ badge.description }}</div>
          </div>

          <q-btn color="white" text-color="primary" label="Awesome!" @click="showBadgeDialog = false" class="full-width q-mt-md rounded-borders text-weight-bold" size="lg" />
        </q-card>
      </q-dialog>

    </div>
  </div>
</template>

<script setup>
defineOptions({ layout: BMLayout });
import BMLayout from "@/Layouts/BMLayout.vue";
import { Head } from '@inertiajs/vue3';
import { computed, ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { useBMScore } from '@/composables/Courses/bm/useBMScore.js';
import BMScoreRadar from '@/Components/Courses/bm/UI/BMScoreRadar.vue';

const props = defineProps({
  score: Object,
  domainScores: Object,
  recommendations: Array,
  newBadges: {
    type: Array,
    default: () => []
  }
});

const { calculateLevel } = useBMScore();

const showBadgeDialog = ref(false);

onMounted(() => {
  if (props.newBadges && props.newBadges.length > 0) {
    setTimeout(() => {
      showBadgeDialog.value = true;
    }, 1000); // 1-second suspense delay
  }
});

const scoreLevel = computed(() => {
  return calculateLevel(props.score?.final_score || 0);
});

const redoAssessment = () => {
  router.post(route('bm.assessment.start'));
};
</script>

<style scoped>
.letter-spacing-1 {
  letter-spacing: 1px;
}
</style>
