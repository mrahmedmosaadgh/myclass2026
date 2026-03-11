<template>
  <Head title="Basic Math - Gap Analysis" />
  <div class="bm-teacher-gap-analysis q-pa-md bg-white">
    <div class="row justify-between items-center q-mb-lg">
      <div class="text-h4 text-weight-bold text-primary">Class Gap Analysis</div>
      <q-btn flat icon="arrow_back" label="Dashboard" :to="route('bm.teacher.dashboard')" />
    </div>

    <!-- Alert / Recommendation Header -->
    <q-card class="bg-red-1 shadow-1 border-left-negative q-mb-xl rounded-borders">
      <q-card-section class="row items-center q-pa-md">
        <q-icon name="warning" color="negative" size="xl" class="q-mr-md" />
        <div>
          <div class="text-h6 text-negative text-weight-bold">Critical Class-Wide Gap Identified</div>
          <div class="text-body1 text-grey-9">
            <strong>65% of students</strong> in Period 1 are failing Basic Fractions. The Adaptive Engine recommends 
            assigning the "Visual Fractions" foundational module to the entire class before proceeding.
          </div>
        </div>
        <q-space />
        <q-btn color="negative" label="Auto-Assign Module" unelevated class="rounded-borders" />
      </q-card-section>
    </q-card>

    <div class="row q-col-gutter-lg">
      <!-- Domain Breakdown -->
      <div class="col-12 col-md-6">
        <q-card class="shadow-2 rounded-borders">
          <q-card-section class="bg-primary text-white">
            <div class="text-h6 font-weight-bold">Proficiency by Domain</div>
          </q-card-section>
          
          <q-list separator>
            <q-item v-for="(domain, idx) in domainAggregates" :key="idx" class="q-py-md">
              <q-item-section>
                <q-item-label class="text-weight-bold text-h6">{{ domain.name }}</q-item-label>
                <div class="row items-center q-mt-sm">
                  <q-linear-progress :value="domain.score / 100" :color="getScoreColor(domain.score)" class="col q-mr-md" size="10px" rounded />
                  <span class="text-weight-bold" :class="`text-${getScoreColor(domain.score)}`">{{ domain.score }}%</span>
                </div>
              </q-item-section>
            </q-item>
          </q-list>
        </q-card>
      </div>

      <!-- Students needing intervention -->
      <div class="col-12 col-md-6">
        <q-card class="shadow-2 rounded-borders" style="height: 100%">
          <q-card-section class="bg-warning text-dark">
            <div class="text-h6 font-weight-bold row items-center">
              <q-icon name="group_remove" class="q-mr-sm" size="sm" /> Require Immediate Intervention
            </div>
          </q-card-section>
          <q-list separator>
            <q-item v-for="(student, idx) in atRiskStudents" :key="idx" class="q-py-md">
              <q-item-section avatar>
                <q-avatar color="warning" text-color="white">{{ student.name.charAt(0) }}</q-avatar>
              </q-item-section>
              <q-item-section>
                <q-item-label class="text-weight-bold">{{ student.name }}</q-item-label>
                <q-item-label caption class="text-negative">{{ student.issue }}</q-item-label>
              </q-item-section>
              <q-item-section side>
                <q-btn flat outline color="primary" label="View Profile" :to="route('bm.teacher.student_detail', {studentId: student.id})" dense />
              </q-item-section>
            </q-item>
          </q-list>
        </q-card>
      </div>
    </div>
  </div>
</template>

<script setup>
defineOptions({ layout: BMLayout });
import BMLayout from "@/Layouts/BMLayout.vue";
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

const domainAggregates = ref([
  { name: 'Addition', score: 92 },
  { name: 'Subtraction', score: 85 },
  { name: 'Multiplication', score: 76 },
  { name: 'Division', score: 58 },
  { name: 'Fractions', score: 35 },
]);

const atRiskStudents = ref([
  { id: 3, name: 'Charlie Brown', issue: 'Failed last 3 Fraction assessments.' },
  { id: 7, name: 'Emma Davis', issue: 'Division speed is currently 3x slower than class average.' },
  { id: 9, name: 'Liam Wilson', issue: 'Accuracy below 40% across all domains.' }
]);

const getScoreColor = (score) => {
  if (score >= 80) return 'positive';
  if (score >= 60) return 'info';
  if (score >= 40) return 'warning';
  return 'negative';
};
</script>

<style scoped>
.rounded-borders {
  border-radius: 12px;
}
.border-left-negative {
  border-left: 6px solid var(--q-negative);
}
</style>
