<template>
  <Head title="Basic Math - Teacher Dashboard" />
  <div class="bm-teacher-dashboard q-pa-md bg-grey-1" style="min-height: 100vh;">
    <div class="row items-center justify-between q-mb-xl">
      <div>
        <h3 class="text-h4 text-weight-bold text-primary q-my-none">Teacher Dashboard</h3>
        <div class="text-subtitle1 text-grey-7">Basic Math Period 1</div>
      </div>
      <q-btn color="primary" label="New Assessment" icon="add" unelevated rounded />
    </div>

    <!-- Quick Stats -->
    <div class="row q-col-gutter-md q-mb-xl">
      <div class="col-12 col-md-4">
        <q-card class="shadow-2 rounded-borders text-center q-pa-md border-top-primary">
          <q-icon name="people" size="40px" color="primary" class="q-mb-sm" />
          <div class="text-h4 text-weight-bold text-dark">24</div>
          <div class="text-subtitle2 text-grey-6 uppercase">Total Students</div>
        </q-card>
      </div>
      <div class="col-12 col-md-4">
        <q-card class="shadow-2 rounded-borders text-center q-pa-md border-top-positive">
          <q-icon name="trending_up" size="40px" color="positive" class="q-mb-sm" />
          <div class="text-h4 text-weight-bold text-dark">68%</div>
          <div class="text-subtitle2 text-grey-6 uppercase">Class Average Proficiency</div>
        </q-card>
      </div>
      <div class="col-12 col-md-4">
        <q-card class="shadow-2 rounded-borders text-center q-pa-md border-top-warning">
          <q-icon name="warning" size="40px" color="warning" class="q-mb-sm" />
          <div class="text-h4 text-weight-bold text-dark">5</div>
          <div class="text-subtitle2 text-grey-6 uppercase">Needs Attention</div>
        </q-card>
      </div>
    </div>

    <!-- Main Content Area -->
    <div class="row q-col-gutter-lg">
      <div class="col-12 col-lg-8">
        <q-card class="shadow-2 rounded-borders q-mb-md">
          <q-card-section class="row justify-between items-center bg-grey-2">
            <div class="text-h6 text-weight-bold">Recent Assessments</div>
            <q-btn flat color="primary" label="View All" :to="route('bm.teacher.class_scores')" />
          </q-card-section>
          <q-list separator>
            <q-item v-for="i in 5" :key="i" class="q-py-md">
              <q-item-section avatar>
                <q-avatar color="primary" text-color="white">{{ String.fromCharCode(64 + i) }}</q-avatar>
              </q-item-section>
              <q-item-section>
                <q-item-label class="text-weight-bold">Student {{ i }}</q-item-label>
                <q-item-label caption>Completed Placement Test</q-item-label>
              </q-item-section>
              <q-item-section side>
                <q-chip :color="i > 2 ? 'positive' : 'warning'" text-color="white" class="text-weight-bold">
                  {{ i > 2 ? 'Proficient' : 'Developing' }}
                </q-chip>
              </q-item-section>
            </q-item>
          </q-list>
        </q-card>
      </div>

      <div class="col-12 col-lg-4">
        <q-card class="shadow-2 rounded-borders bg-white">
          <q-card-section class="bg-primary text-white">
            <div class="text-h6 text-weight-bold row items-center">
              <q-icon name="insights" size="md" class="q-mr-sm" />
              Class Insights
            </div>
          </q-card-section>
          <q-card-section>
            <p class="text-body1 text-grey-8">
              Based on recent assessments, your class is struggling most with <strong>Fractions</strong>. 
              We recommend reviewing denominators before moving on to Multiplication.
            </p>
            <q-btn color="secondary" label="View Gap Analysis" :to="route('bm.teacher.gap_analysis')" class="full-width q-mt-sm" outline rounded />
          </q-card-section>
        </q-card>
      </div>
    </div>
  </div>
</template>

<script setup>
defineOptions({ layout: BMLayout });
import BMLayout from "@/Layouts/BMLayout.vue";
import { Head } from '@inertiajs/vue3';
import { useQuasar } from 'quasar';
const $q = useQuasar();
</script>

<style scoped>
.rounded-borders {
  border-radius: 12px;
}
.border-top-primary {
  border-top: 4px solid var(--q-primary);
}
.border-top-positive {
  border-top: 4px solid var(--q-positive);
}
.border-top-warning {
  border-top: 4px solid var(--q-warning);
}
</style>
