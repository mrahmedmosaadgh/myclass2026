<template>
  <div class="quiz-analytics">
    <!-- Header -->
    <div class="quiz-analytics__header">
      <q-btn
        flat
        round
        icon="arrow_back"
        @click="router.visit('/quizzes')"
      />
      <div class="quiz-analytics__header-content">
        <h1 class="quiz-analytics__title">{{ quiz.name }} - Analytics</h1>
        <p class="quiz-analytics__subtitle">Performance insights and statistics</p>
      </div>
      
      <div class="quiz-analytics__header-actions">
        <q-btn
          flat
          label="Export Report"
          icon="download"
          @click="exportReport"
        />
        <q-btn
          unelevated
          color="primary"
          label="View Quiz"
          icon="visibility"
          @click="viewQuiz"
        />
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="quiz-analytics__loading">
      <q-spinner-dots color="primary" size="50px" />
      <p>Loading analytics...</p>
    </div>

    <!-- Content -->
    <div v-else class="quiz-analytics__content">
      <!-- Overview Stats -->
      <div class="quiz-analytics__overview">
        <quiz-stats
          icon="people"
          label="Total Attempts"
          :value="analytics.totalAttempts"
          variant="primary"
        />
        
        <quiz-stats
          icon="check_circle"
          label="Avg. Score"
          :value="`${analytics.avgScore}%`"
          variant="success"
          :trend="analytics.scoreTrend"
        />
        
        <quiz-stats
          icon="schedule"
          label="Avg. Time"
          :value="formatTime(analytics.avgTime)"
          variant="info"
        />
        
        <quiz-stats
          icon="trending_up"
          label="Completion Rate"
          :value="`${analytics.completionRate}%`"
          variant="warning"
          :trend="analytics.completionTrend"
        />
      </div>

      <!-- Charts Section -->
      <div class="quiz-analytics__charts">
        <!-- Score Distribution -->
        <q-card class="quiz-analytics__chart-card">
          <q-card-section>
            <h3>Score Distribution</h3>
            <div class="quiz-analytics__chart">
              <canvas ref="scoreChartRef"></canvas>
            </div>
          </q-card-section>
        </q-card>

        <!-- Performance Over Time -->
        <q-card class="quiz-analytics__chart-card">
          <q-card-section>
            <h3>Performance Over Time</h3>
            <div class="quiz-analytics__chart">
              <canvas ref="timeChartRef"></canvas>
            </div>
          </q-card-section>
        </q-card>
      </div>

      <!-- Question Analysis -->
      <q-card class="quiz-analytics__questions-card">
        <q-card-section>
          <div class="quiz-analytics__questions-header">
            <h3>Question Analysis</h3>
            <q-btn-toggle
              v-model="questionSortBy"
              :options="[
                { label: 'Difficulty', value: 'difficulty' },
                { label: 'Success Rate', value: 'success' },
                { label: 'Time', value: 'time' }
              ]"
              unelevated
              dense
            />
          </div>
          
          <div class="quiz-analytics__questions-list">
            <div
              v-for="(question, index) in sortedQuestions"
              :key="question.id"
              class="quiz-analytics__question-item"
            >
              <div class="quiz-analytics__question-info">
                <div class="quiz-analytics__question-number">Q{{ index + 1 }}</div>
                <div class="quiz-analytics__question-text" v-html="truncateText(question.question_text, 80)" />
              </div>
              
              <div class="quiz-analytics__question-stats">
                <div class="quiz-analytics__stat-item">
                  <q-circular-progress
                    :value="question.success_rate"
                    size="60px"
                    :thickness="0.15"
                    :color="getSuccessColor(question.success_rate)"
                    track-color="grey-3"
                    show-value
                  >
                    <div class="text-caption">{{ question.success_rate }}%</div>
                  </q-circular-progress>
                  <span class="text-caption">Success Rate</span>
                </div>
                
                <div class="quiz-analytics__stat-item">
                  <div class="quiz-analytics__stat-value">{{ question.avg_time }}s</div>
                  <span class="text-caption">Avg. Time</span>
                </div>
                
                <div class="quiz-analytics__stat-item">
                  <q-badge
                    :color="getDifficultyColor(question.difficulty)"
                    :label="question.difficulty || 'Medium'"
                  />
                  <span class="text-caption">Difficulty</span>
                </div>
              </div>
            </div>
          </div>
        </q-card-section>
      </q-card>

      <!-- Top Performers -->
      <div class="quiz-analytics__performers">
        <q-card class="quiz-analytics__performers-card">
          <q-card-section>
            <h3>Top Performers</h3>
            <div class="quiz-analytics__performers-list">
              <div
                v-for="(student, index) in topPerformers"
                :key="student.id"
                class="quiz-analytics__performer-item"
              >
                <div class="quiz-analytics__performer-rank">
                  <div
                    class="quiz-analytics__rank-badge"
                    :class="`quiz-analytics__rank-badge--${index + 1}`"
                  >
                    {{ index + 1 }}
                  </div>
                </div>
                
                <q-avatar size="40px" color="primary" text-color="white">
                  {{ getInitials(student.name) }}
                </q-avatar>
                
                <div class="quiz-analytics__performer-info">
                  <div class="quiz-analytics__performer-name">{{ student.name }}</div>
                  <div class="quiz-analytics__performer-score">{{ student.score }}% • {{ formatTime(student.time) }}</div>
                </div>
                
                <q-icon name="emoji_events" :color="getMedalColor(index)" size="24px" />
              </div>
            </div>
          </q-card-section>
        </q-card>

        <!-- Struggling Students -->
        <q-card class="quiz-analytics__performers-card">
          <q-card-section>
            <h3>Needs Attention</h3>
            <div class="quiz-analytics__performers-list">
              <div
                v-for="student in strugglingStudents"
                :key="student.id"
                class="quiz-analytics__performer-item"
              >
                <q-avatar size="40px" color="warning" text-color="white">
                  {{ getInitials(student.name) }}
                </q-avatar>
                
                <div class="quiz-analytics__performer-info">
                  <div class="quiz-analytics__performer-name">{{ student.name }}</div>
                  <div class="quiz-analytics__performer-score">{{ student.score }}% • {{ student.attempts }} attempts</div>
                </div>
                
                <q-btn
                  flat
                  dense
                  round
                  icon="message"
                  size="sm"
                  @click="contactStudent(student)"
                >
                  <q-tooltip>Send Message</q-tooltip>
                </q-btn>
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue';
import { router } from '@inertiajs/vue3';
import { useQuasar } from 'quasar';
import axios from 'axios';
import QuizStats from '@/Components/Quiz/QuizStats.vue';

const $q = useQuasar();
const props = defineProps({
  quizId: Number
});

// State
const loading = ref(false);
const quiz = ref({ name: '' });
const analytics = ref({
  totalAttempts: 0,
  avgScore: 0,
  avgTime: 0,
  completionRate: 0,
  scoreTrend: 0,
  completionTrend: 0
});

const questionAnalysis = ref([]);
const topPerformers = ref([]);
const strugglingStudents = ref([]);
const questionSortBy = ref('difficulty');

// Chart refs
const scoreChartRef = ref(null);
const timeChartRef = ref(null);

// Computed
const sortedQuestions = computed(() => {
  const questions = [...questionAnalysis.value];
  
  switch (questionSortBy.value) {
    case 'success':
      return questions.sort((a, b) => a.success_rate - b.success_rate);
    case 'time':
      return questions.sort((a, b) => b.avg_time - a.avg_time);
    case 'difficulty':
    default:
      const difficultyOrder = { 'Easy': 1, 'Medium': 2, 'Hard': 3 };
      return questions.sort((a, b) => 
        (difficultyOrder[b.difficulty] || 2) - (difficultyOrder[a.difficulty] || 2)
      );
  }
});

// Methods
const loadAnalytics = async () => {
  loading.value = true;
  try {
    const response = await axios.get(`/api/quizzes/${props.quizId}/analytics`);
    quiz.value = response.data.quiz;
    analytics.value = response.data.analytics;
    questionAnalysis.value = response.data.questions;
    topPerformers.value = response.data.topPerformers;
    strugglingStudents.value = response.data.strugglingStudents;
    
    await nextTick();
    initializeCharts();
  } catch (error) {
    console.error('Failed to load analytics:', error);
    
    // Mock data for demonstration
    quiz.value = { name: 'Sample Quiz' };
    analytics.value = {
      totalAttempts: 156,
      avgScore: 78,
      avgTime: 1245,
      completionRate: 92,
      scoreTrend: 5,
      completionTrend: 3
    };
    
    questionAnalysis.value = [
      { id: 1, question_text: 'What is 2+2?', success_rate: 95, avg_time: 12, difficulty: 'Easy' },
      { id: 2, question_text: 'Solve for x: 2x + 5 = 15', success_rate: 72, avg_time: 45, difficulty: 'Medium' },
      { id: 3, question_text: 'Calculate the derivative of x²', success_rate: 58, avg_time: 67, difficulty: 'Hard' }
    ];
    
    topPerformers.value = [
      { id: 1, name: 'Ahmed Ali', score: 98, time: 890 },
      { id: 2, name: 'Sara Mohamed', score: 95, time: 920 },
      { id: 3, name: 'Omar Hassan', score: 92, time: 1050 }
    ];
    
    strugglingStudents.value = [
      { id: 4, name: 'Fatima Ibrahim', score: 45, attempts: 3 },
      { id: 5, name: 'Youssef Khaled', score: 52, attempts: 2 }
    ];
    
    await nextTick();
    initializeCharts();
  } finally {
    loading.value = false;
  }
};

const initializeCharts = () => {
  // Note: In a real implementation, you would use Chart.js or similar
  // This is a placeholder for the chart initialization
  console.log('Charts would be initialized here with Chart.js');
};

const formatTime = (seconds) => {
  const mins = Math.floor(seconds / 60);
  const secs = seconds % 60;
  return `${mins}:${String(secs).padStart(2, '0')}`;
};

const truncateText = (text, maxLength) => {
  const plainText = text.replace(/<[^>]*>/g, '');
  return plainText.length > maxLength ? plainText.substring(0, maxLength) + '...' : plainText;
};

const getSuccessColor = (rate) => {
  if (rate >= 80) return 'positive';
  if (rate >= 60) return 'warning';
  return 'negative';
};

const getDifficultyColor = (difficulty) => {
  const colors = {
    'Easy': 'positive',
    'Medium': 'warning',
    'Hard': 'negative'
  };
  return colors[difficulty] || 'info';
};

const getInitials = (name) => {
  return name
    .split(' ')
    .map(n => n[0])
    .join('')
    .toUpperCase()
    .substring(0, 2);
};

const getMedalColor = (index) => {
  const colors = ['warning', 'grey-6', 'orange-8'];
  return colors[index] || 'grey';
};

const viewQuiz = () => {
  router.visit(`/quizzes/${props.quizId}/preview`);
};

const exportReport = () => {
  $q.notify({
    type: 'info',
    message: 'Exporting report...',
    icon: 'download'
  });
  // Implement export functionality
};

const contactStudent = (student) => {
  $q.notify({
    type: 'info',
    message: `Opening message to ${student.name}`,
    icon: 'message'
  });
  // Implement contact functionality
};

// Lifecycle
onMounted(() => {
  loadAnalytics();
});
</script>

<style scoped>
.quiz-analytics {
  min-height: 100vh;
  background: #f7fafc;
}

.quiz-analytics__header {
  background: white;
  border-bottom: 1px solid #e2e8f0;
  padding: 16px 24px;
  display: flex;
  align-items: center;
  gap: 16px;
  position: sticky;
  top: 0;
  z-index: 10;
}

.quiz-analytics__header-content {
  flex: 1;
}

.quiz-analytics__title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1a202c;
  margin: 0;
}

.quiz-analytics__subtitle {
  font-size: 0.875rem;
  color: #718096;
  margin: 4px 0 0 0;
}

.quiz-analytics__header-actions {
  display: flex;
  gap: 12px;
}

.quiz-analytics__loading {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 80px 20px;
}

.quiz-analytics__loading p {
  margin-top: 16px;
  color: #718096;
}

.quiz-analytics__content {
  padding: 24px;
  max-width: 1400px;
  margin: 0 auto;
}

.quiz-analytics__overview {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
  margin-bottom: 32px;
}

.quiz-analytics__charts {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
  gap: 24px;
  margin-bottom: 32px;
}

.quiz-analytics__chart-card {
  border-radius: 16px;
}

.quiz-analytics__chart-card h3 {
  margin: 0 0 16px 0;
  font-size: 1.125rem;
  font-weight: 600;
}

.quiz-analytics__chart {
  height: 300px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #a0aec0;
}

.quiz-analytics__questions-card {
  border-radius: 16px;
  margin-bottom: 32px;
}

.quiz-analytics__questions-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.quiz-analytics__questions-header h3 {
  margin: 0;
  font-size: 1.125rem;
  font-weight: 600;
}

.quiz-analytics__questions-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.quiz-analytics__question-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px;
  background: #f7fafc;
  border-radius: 12px;
  gap: 24px;
}

.quiz-analytics__question-info {
  display: flex;
  align-items: center;
  gap: 16px;
  flex: 1;
  min-width: 0;
}

.quiz-analytics__question-number {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  flex-shrink: 0;
}

.quiz-analytics__question-text {
  color: #1a202c;
  font-size: 0.875rem;
}

.quiz-analytics__question-stats {
  display: flex;
  gap: 32px;
  align-items: center;
}

.quiz-analytics__stat-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
}

.quiz-analytics__stat-value {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1a202c;
}

.quiz-analytics__performers {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
  gap: 24px;
}

.quiz-analytics__performers-card {
  border-radius: 16px;
}

.quiz-analytics__performers-card h3 {
  margin: 0 0 16px 0;
  font-size: 1.125rem;
  font-weight: 600;
}

.quiz-analytics__performers-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.quiz-analytics__performer-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px;
  background: #f7fafc;
  border-radius: 12px;
}

.quiz-analytics__performer-rank {
  flex-shrink: 0;
}

.quiz-analytics__rank-badge {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 0.875rem;
}

.quiz-analytics__rank-badge--1 {
  background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
  color: white;
}

.quiz-analytics__rank-badge--2 {
  background: linear-gradient(135deg, #9ca3af 0%, #6b7280 100%);
  color: white;
}

.quiz-analytics__rank-badge--3 {
  background: linear-gradient(135deg, #fb923c 0%, #f97316 100%);
  color: white;
}

.quiz-analytics__performer-info {
  flex: 1;
  min-width: 0;
}

.quiz-analytics__performer-name {
  font-weight: 600;
  color: #1a202c;
  font-size: 0.875rem;
}

.quiz-analytics__performer-score {
  font-size: 0.75rem;
  color: #718096;
  margin-top: 2px;
}

/* Responsive */
@media (max-width: 960px) {
  .quiz-analytics__header {
    flex-wrap: wrap;
  }
  
  .quiz-analytics__charts {
    grid-template-columns: 1fr;
  }
  
  .quiz-analytics__question-item {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .quiz-analytics__question-stats {
    width: 100%;
    justify-content: space-around;
  }
  
  .quiz-analytics__performers {
    grid-template-columns: 1fr;
  }
}
</style>
