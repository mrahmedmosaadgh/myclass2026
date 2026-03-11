<template>
  <Head title="Basic Math - History" />
  <div class="bm-assessment-history q-pa-md fullscreen bg-grey-1">
    <div class="content-wrapper q-mx-auto" style="max-width: 800px">
      <div class="row justify-between items-center q-mb-lg">
        <h3 class="text-h4 text-weight-bold text-primary q-my-none">My Math History</h3>
        <q-btn color="primary" icon="play_arrow" label="New Assessment" :to="route('bm.assessment.index')" unelevated rounded />
      </div>

      <q-card class="shadow-2 rounded-borders q-mb-xl">
        <q-card-section>
          <BadgeShowcase :badges="badges" />
        </q-card-section>
      </q-card>

      <div class="text-h5 text-weight-bold text-dark q-mb-md">Past Assessments</div>
      
      <q-list separator class="bg-white shadow-2 rounded-borders">
        <q-item v-if="history.length === 0" class="q-pa-lg">
          <q-item-section class="text-center text-grey-7">
            No past assessments found. Time to take your first one!
          </q-item-section>
        </q-item>
        
        <q-item v-for="assessment in history" :key="assessment.id" class="q-py-md" clickable @click="viewResults(assessment.bm_assessment_id)">
          <q-item-section avatar>
            <q-avatar :color="getScoreColor(assessment.final_score)" text-color="white" icon="analytics" />
          </q-item-section>
          
          <q-item-section>
            <q-item-label class="text-weight-bold text-h6">{{ assessment.final_score }} Points</q-item-label>
            <q-item-label caption>Score Level: {{ getScoreLevel(assessment.final_score) }}</q-item-label>
          </q-item-section>
          
          <q-item-section side>
            <div class="text-caption text-grey-7">{{ formatDate(assessment.updated_at) }}</div>
            <q-btn flat color="primary" icon="chevron_right" dense />
          </q-item-section>
        </q-item>
      </q-list>
    </div>
  </div>
</template>

<script setup>
defineOptions({ layout: BMLayout });
import BMLayout from "@/Layouts/BMLayout.vue";
import { Head } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import BadgeShowcase from './BadgeShowcase.vue';
import { useBMScore } from '@/composables/Courses/bm/useBMScore.js';

const props = defineProps({
  history: Array,
  badges: Array
});

const { calculateLevel } = useBMScore();

const getScoreColor = (score) => {
  if (score >= 80) return 'positive';
  if (score >= 50) return 'warning';
  return 'negative';
};

const getScoreLevel = (score) => {
  return calculateLevel(score);
};

const formatDate = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleDateString(undefined, { year: 'numeric', month: 'long', day: 'numeric' });
};

const viewResults = (assessmentId) => {
  router.get(route('bm.assessment.results', { id: assessmentId }));
};
</script>

<style scoped>
.rounded-borders {
  border-radius: 16px;
}
</style>
