<template>
  <Head title="Exam Analytics" />
  <div class="q-pa-md">
    <div class="row items-center q-mb-md">
      <div class="text-h5">Exam Analytics</div>
      <q-space />
      <q-btn 
        color="secondary" 
        icon="table_chart" 
        label="Export All" 
        disable
        outline
      >
        <q-tooltip>Coming Soon</q-tooltip>
      </q-btn>
    </div>

    <!-- Summary Cards -->
    <div class="row q-col-gutter-md q-mb-lg">
      <div class="col-12 col-sm-6 col-md-3">
        <q-card class="bg-primary text-white">
          <q-card-section>
            <div class="text-subtitle2">Total Exams</div>
            <div class="text-h4 text-weight-bold">{{ exams.length }}</div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-12 col-sm-6 col-md-3">
        <q-card class="bg-teal text-white">
          <q-card-section>
            <div class="text-subtitle2">Total Attempts</div>
            <div class="text-h4 text-weight-bold">{{ totalAttempts }}</div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-12 col-sm-6 col-md-3">
        <q-card class="bg-orange text-white">
          <q-card-section>
            <div class="text-subtitle2">Pending Grading</div>
            <div class="text-h4 text-weight-bold">{{ totalPending }}</div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-12 col-sm-6 col-md-3">
        <q-card class="bg-indigo text-white">
          <q-card-section>
            <div class="text-subtitle2">Avg Score</div>
            <div class="text-h4 text-weight-bold">{{ overallAvgScore }}%</div>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <!-- Exam List Table -->
    <q-card>
      <q-table
        title="Exam Performance"
        :rows="exams"
        :columns="columns"
        row-key="id"
        :filter="filter"
        :pagination="{ rowsPerPage: 10 }"
      >
        <template v-slot:top-right>
          <q-input borderless dense debounce="300" v-model="filter" placeholder="Search">
            <template v-slot:append>
              <q-icon name="search" />
            </template>
          </q-input>
        </template>

        <template v-slot:body-cell-average_score="props">
          <q-td :props="props">
            <q-badge :color="getScoreColor(props.value, props.row.total_marks)">
              {{ props.value }} / {{ props.row.total_marks }}
            </q-badge>
            <span class="text-caption q-ml-xs text-grey">
              ({{ Math.round((props.value / props.row.total_marks) * 100) }}%)
            </span>
          </q-td>
        </template>

        <template v-slot:body-cell-actions="props">
          <q-td :props="props" align="right">
            <q-btn 
              flat 
              round 
              color="primary" 
              icon="analytics" 
              @click="viewDetails(props.row)"
            >
              <q-tooltip>View Detailed Analytics</q-tooltip>
            </q-btn>
          </q-td>
        </template>
      </q-table>
    </q-card>

    <!-- Detailed Analytics Dialog -->
    <q-dialog v-model="showDetailsDialog" maximized transition-show="slide-up" transition-hide="slide-down">
      <q-card class="bg-grey-1">
        <q-toolbar class="bg-primary text-white">
          <q-btn flat round dense icon="close" v-close-popup />
          <q-toolbar-title>
            Analytics: {{ selectedExam?.title }}
          </q-toolbar-title>
          <q-btn flat icon="download" label="Export Report" @click="exportReport" disable />
        </q-toolbar>

        <q-card-section class="q-pa-md" v-if="detailsLoading">
          <div class="row justify-center q-py-xl">
             <q-spinner-dots color="primary" size="3em" />
             <div class="text-grey q-mt-md">Loading analytics data...</div>
          </div>
        </q-card-section>

        <q-card-section class="q-pa-md" v-else-if="detailsData">
          <!-- Overview Stats -->
          <div class="row q-col-gutter-md q-mb-lg">
             <div class="col-6 col-md-3">
                <q-card flat bordered class="text-center q-pa-sm">
                   <div class="text-caption text-grey">Attempts</div>
                   <div class="text-h5 text-weight-bold">{{ detailsData.overview.total_attempts }}</div>
                </q-card>
             </div>
             <div class="col-6 col-md-3">
                <q-card flat bordered class="text-center q-pa-sm">
                   <div class="text-caption text-grey">Average Score</div>
                   <div class="text-h5 text-weight-bold text-primary">{{ detailsData.overview.avg_score }}</div>
                </q-card>
             </div>
             <div class="col-6 col-md-3">
                <q-card flat bordered class="text-center q-pa-sm">
                   <div class="text-caption text-grey">Median Score</div>
                   <div class="text-h5 text-weight-bold text-indigo">{{ detailsData.overview.median_score }}</div>
                </q-card>
             </div>
             <div class="col-6 col-md-3">
                <q-card flat bordered class="text-center q-pa-sm">
                   <div class="text-caption text-grey">Pass Rate</div>
                   <div class="text-h5 text-weight-bold text-green">{{ detailsData.overview.pass_rate }}%</div>
                </q-card>
             </div>
          </div>

          <div class="row q-col-gutter-md">
            <!-- Score Distribution Chart -->
            <div class="col-12 col-md-6">
              <q-card flat bordered class="full-height">
                <q-card-section>
                  <div class="text-h6">Score Distribution</div>
                </q-card-section>
                <q-card-section style="height: 300px">
                  <v-chart class="chart" :option="scoreDistributionOption" autoresize />
                </q-card-section>
              </q-card>
            </div>

            <!-- Bloom Level Performance -->
             <div class="col-12 col-md-6">
              <q-card flat bordered class="full-height">
                <q-card-section>
                  <div class="text-h6">Bloom's Taxonomy Performance</div>
                </q-card-section>
                <q-card-section style="height: 300px">
                   <v-chart class="chart" :option="bloomStatsOption" autoresize />
                </q-card-section>
              </q-card>
            </div>
          </div>
          
          <!-- Question Analysis Table -->
          <div class="q-mt-md">
            <q-card flat bordered>
               <q-card-section>
                  <div class="text-h6">Question Difficulty Analysis</div>
               </q-card-section>
               <q-table
                  :rows="detailsData.question_stats"
                  :columns="questionColumns"
                  row-key="id"
                  :pagination="{ rowsPerPage: 10 }"
                  dense
               >
                  <template v-slot:body-cell-difficulty_label="props">
                    <q-td :props="props">
                       <q-chip 
                         dense 
                         :color="getDifficultyColor(props.row.percentage_correct)" 
                         text-color="white"
                       >
                         {{ props.value }} ({{ props.row.percentage_correct }}%)
                       </q-chip>
                    </q-td>
                  </template>
                  
                  <template v-slot:body-cell-text="props">
                    <q-td :props="props">
                       <div style="max-width: 400px; white-space: normal; overflow: hidden; text-overflow: ellipsis;">
                          {{ props.value }}
                       </div>
                    </q-td>
                  </template>
               </q-table>
            </q-card>
          </div>

        </q-card-section>
      </q-card>
    </q-dialog>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { useQuasar } from 'quasar';
import axios from 'axios';
import { route } from 'ziggy-js';

// ECharts
import VChart from 'vue-echarts';
import { use } from 'echarts/core';
import { CanvasRenderer } from 'echarts/renderers';
import { BarChart, PieChart } from 'echarts/charts';
import {
  TitleComponent,
  TooltipComponent,
  LegendComponent,
  GridComponent
} from 'echarts/components';

use([
  CanvasRenderer,
  BarChart,
  PieChart,
  TitleComponent,
  TooltipComponent,
  LegendComponent,
  GridComponent
]);

const props = defineProps({
  exams: Array
});

const $q = useQuasar();
const filter = ref('');
const showDetailsDialog = ref(false);
const selectedExam = ref(null);
const detailsLoading = ref(false);
const detailsData = ref(null);

// Summary Stats
const totalAttempts = computed(() => {
  return props.exams.reduce((sum, exam) => sum + exam.attempts_count, 0); // Oops, check prop name
  // Prop is 'total_attempts' or 'completed_attempts'
  // Controller returns: 'total_attempts', 'completed_attempts'
  return props.exams.reduce((sum, exam) => sum + exam.completed_attempts, 0);
});

const totalPending = computed(() => {
  return props.exams.reduce((sum, exam) => sum + exam.pending_grading, 0);
});

const overallAvgScore = computed(() => {
   // Weighted average? Or average of percentages?
   // Let's do simple average of percentages
   const validExams = props.exams.filter(e => e.completed_attempts > 0);
   if (validExams.length === 0) return 0;
   
   const totalPct = validExams.reduce((sum, exam) => {
      return sum + (exam.average_score / exam.total_marks) * 100;
   }, 0);
   
   return Math.round(totalPct / validExams.length);
});


const columns = [
  { name: 'title', required: true, label: 'Exam Title', align: 'left', field: 'title', sortable: true },
  { name: 'created_at', label: 'Date', field: 'created_at', sortable: true },
  { name: 'completed_attempts', label: 'Attempts', field: 'completed_attempts', sortable: true },
  { name: 'average_score', label: 'Avg Score', field: 'average_score', sortable: true },
  { name: 'pending_grading', label: 'Pending Grading', field: 'pending_grading', sortable: true, classes: row => row.pending_grading > 0 ? 'text-negative text-weight-bold' : '' },
  { name: 'actions', label: 'Actions', field: 'actions', align: 'right' }
];

const questionColumns = [
  { name: 'text', label: 'Question', field: 'text', align: 'left' },
  { name: 'type', label: 'Type', field: 'type', align: 'left' },
  { name: 'bloom_level', label: 'Bloom Level', field: 'bloom_level', align: 'left', sortable: true },
  { name: 'difficulty_label', label: 'Difficulty', field: 'difficulty_label', align: 'left', sortable: true },
];

const getScoreColor = (score, total) => {
  const percentage = (score / total) * 100;
  if (percentage >= 80) return 'green';
  if (percentage >= 60) return 'blue';
  if (percentage >= 40) return 'orange';
  return 'red';
};

const getDifficultyColor = (percentage) => {
  if (percentage >= 80) return 'green'; // Easy
  if (percentage >= 50) return 'orange'; // Medium
  return 'red'; // Hard
};

const viewDetails = async (exam) => {
  selectedExam.value = exam;
  showDetailsDialog.value = true;
  detailsLoading.value = true;
  detailsData.value = null;

  try {
    const response = await axios.get(route('qu-analytics.exam', exam.id));
    detailsData.value = response.data;
  } catch (error) {
    console.error(error);
    $q.notify({
       type: 'negative',
       message: 'Failed to load analytics data'
    });
  } finally {
    detailsLoading.value = false;
  }
};

const exportReport = () => {
   // Should trigger export route
   $q.notify({ type: 'info', message: 'Export coming soon' });
};

// Chart Options
const scoreDistributionOption = computed(() => {
  if (!detailsData.value) return {};
  
  const dist = detailsData.value.score_distribution; // Object: "0-9": count
  const categories = Object.keys(dist);
  const data = Object.values(dist);
  
  return {
    tooltip: { trigger: 'axis' },
    xAxis: { 
       type: 'category', 
       data: categories,
       name: 'Score Range'
    },
    yAxis: { type: 'value', name: 'Students' },
    series: [{
      data: data,
      type: 'bar',
      color: '#3f51b5'
    }]
  };
});

const bloomStatsOption = computed(() => {
  if (!detailsData.value) return {};
  
  const stats = detailsData.value.bloom_stats; // Array: { level, avg_percentage, count }
  
  return {
     tooltip: { trigger: 'axis' },
     xAxis: { 
        type: 'category', 
        data: stats.map(s => s.level),
        axisLabel: { interval: 0, rotate: 30 }
     },
     yAxis: { type: 'value', name: 'Avg Score %', max: 100 },
     series: [{
        data: stats.map(s => s.avg_percentage),
        type: 'bar',
        color: '#ff9800',
        label: { show: true, position: 'top', formatter: '{c}%' }
     }]
  };
});
</script>

<style scoped>
.chart {
  height: 100%;
}
</style>
