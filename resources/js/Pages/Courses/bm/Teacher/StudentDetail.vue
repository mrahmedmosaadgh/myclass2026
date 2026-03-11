<template>
  <Head title="Basic Math - Student Detail" />
  <div class="bm-teacher-student-detail q-pa-md bg-grey-1" style="min-height: 100vh;">
    <div class="row justify-between items-center q-mb-lg">
      <div class="row items-center">
        <q-btn flat icon="arrow_back" color="dark" class="q-mr-sm" :to="route('bm.teacher.class_scores')" />
        <q-avatar size="60px" color="primary" text-color="white" class="q-mr-md shadow-2">
          {{ studentName.charAt(0) }}
        </q-avatar>
        <div>
          <h3 class="text-h4 text-weight-bold text-dark q-my-none">{{ studentName }}</h3>
          <div class="text-subtitle1 text-grey-7">Current Level: <span class="text-weight-bold text-warning">{{ currentLevel }}</span></div>
        </div>
      </div>
      <q-btn outline color="primary" icon="description" label="Export Report" class="rounded-borders" />
    </div>

    <div class="row q-col-gutter-lg">
      <!-- Radar Chart -->
      <div class="col-12 col-md-5">
        <q-card class="shadow-2 rounded-borders text-center q-pa-md" style="height: 100%">
          <div class="text-h6 text-weight-bold q-mb-md">Domain Proficiency</div>
          <BMScoreRadar :domains="domainScores" size="300" />
        </q-card>
      </div>

      <!-- Key Insights -->
      <div class="col-12 col-md-7">
        <q-card class="shadow-2 rounded-borders q-pa-md q-mb-md">
          <div class="text-h6 text-weight-bold q-mb-sm row items-center">
            <q-icon name="lightbulb" color="warning" size="sm" class="q-mr-sm" /> Teacher Summary
          </div>
          <p class="text-body1 text-grey-8">
            {{ studentName }} is performing well in Addition and Multiplication, but shows significant gaps in <strong>Division</strong> and <strong>Fractions</strong>. 
            Fluency (speed) is above average, but accuracy drops under pressure.
          </p>
        </q-card>

        <q-card class="shadow-2 rounded-borders q-pa-md">
          <div class="text-h6 text-weight-bold q-mb-md">Assigned Learning Path</div>
          <q-list separator>
            <q-item>
              <q-item-section avatar>
                <q-icon name="school" color="primary" />
              </q-item-section>
              <q-item-section>
                <q-item-label class="text-weight-bold">Master Division Core</q-item-label>
                <q-item-label caption>Due: Friday</q-item-label>
              </q-item-section>
              <q-item-section side>
                <q-chip color="warning" text-color="dark">In Progress</q-chip>
              </q-item-section>
            </q-item>
            <q-item>
              <q-item-section avatar>
                <q-icon name="speed" color="info" />
              </q-item-section>
              <q-item-section>
                <q-item-label class="text-weight-bold">Fraction Speed Drills</q-item-label>
                <q-item-label caption>Recommended by BMGapAnalyzer</q-item-label>
              </q-item-section>
              <q-item-section side>
                <q-chip color="grey-3" text-color="dark">Not Started</q-chip>
              </q-item-section>
            </q-item>
          </q-list>
          <q-btn flat color="primary" class="full-width q-mt-sm" label="Assign New Practice" />
        </q-card>
      </div>

      <!-- Assessment History -->
      <div class="col-12">
        <q-card class="shadow-2 rounded-borders q-pa-md q-mt-md">
          <div class="text-h6 text-weight-bold q-mb-md">Recent Assessments</div>
          <q-table
            flat
            bordered
            :rows="history"
            :columns="historyColumns"
            row-key="id"
            :pagination="{ rowsPerPage: 5 }"
          >
            <template v-slot:body-cell-score="props">
              <q-td :props="props" class="text-weight-bold" :class="props.row.score > 70 ? 'text-positive' : 'text-negative'">
                {{ props.row.score }}
              </q-td>
            </template>
          </q-table>
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
import BMScoreRadar from '@/Components/Courses/bm/UI/BMScoreRadar.vue';

const studentName = ref('Alice Smith');
const currentLevel = ref('Proficient');

const domainScores = ref({
  addition: 95,
  subtraction: 85,
  multiplication: 80,
  division: 45,
  fractions: 30
});

const historyColumns = [
  { name: 'date', label: 'Date Taken', field: 'date', align: 'left', sortable: true },
  { name: 'type', label: 'Assessment Type', field: 'type', align: 'left' },
  { name: 'score', label: 'Final Score', field: 'score', align: 'center', sortable: true },
  { name: 'time', label: 'Avg Time/Q', field: 'time', align: 'right' }
];

const history = ref([
  { id: 1, date: '2026-03-10', type: 'Placement Test', score: 67, time: '8.2s' },
  { id: 2, date: '2026-02-15', type: 'Mid-Term Drill', score: 55, time: '11.5s' }
]);
</script>

<style scoped>
.rounded-borders {
  border-radius: 12px;
}
</style>
