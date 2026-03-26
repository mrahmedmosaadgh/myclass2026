<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { useGameStore } from '../../../stores/gameStore';
import { useRealtimeChannel } from '@/composables/useRealtimeChannel';
import axios from 'axios';

const gameStore = useGameStore();
let statsInterval = null;
let lastFetchTime = 0;
const FETCH_THROTTLE_MS = 2000; // Minimum 2 seconds between fetches

const results = ref({
  totalResponses: 0,
  correctCount: 0,
  wrongCount: 0,
  options: []
});

const isQuizActive = computed(() => gameStore.sessionStatus === 'active' && gameStore.sessionId);

const fetchStats = async (force = false) => {
  if (!isQuizActive.value) return;
  
  const now = Date.now();
  if (!force && now - lastFetchTime < FETCH_THROTTLE_MS) return;
  
  lastFetchTime = now;
  try {
    const response = await axios.get(`/api/cr/sessions/${gameStore.sessionId}/stats`);
    results.value.totalResponses = response.data.total;
    results.value.type = response.data.type;
    
    if (results.value.type === 'short_answer') {
      results.value.responses = response.data.responses;
    } else {
      // Map backend stats to visual options
      results.value.options = (response.data.stats || []).map(s => ({
        text: s.text,
        count: s.count,
        percentage: response.data.total > 0 ? (s.count / response.data.total) * 100 : 0
      }));
    }
  } catch (err) {
    console.error('Failed to fetch stats:', err);
  }
};

const markCorrect = async (resp) => {
  if (resp.is_correct) return;
  
  try {
    await axios.post(`/api/cr/sessions/${gameStore.sessionId}/mark-answer`, {
      nickname: resp.guest_nickname,
      student_id: resp.student_id,
      question_id: results.value.current_question_id
    });
    
    // Optimistic update
    resp.is_correct = true;
  } catch (err) {
    console.error('Failed to mark answer:', err);
  }
};

// Listen for student submissions to trigger refresh (throttled)
const teacherChannel = computed(() => 
  gameStore.accessCode ? `quiz_${gameStore.accessCode}_teacher` : null
);

useRealtimeChannel(teacherChannel, (signal) => {
  if (signal.event === 'ANSWER_SUBMITTED') {
    fetchStats();
  }
});

watch(isQuizActive, (active) => {
  if (active) {
    // Fallback interval (longer)
    statsInterval = setInterval(() => fetchStats(false), 10000);
    fetchStats(true); // Initial fetch (forced)
  } else {
    clearInterval(statsInterval);
  }
});

onUnmounted(() => clearInterval(statsInterval));
</script>

<template>
  <div class="live-results card-accent">
    <div class="results-header">
      <h3 class="panel-subtitle">Live Responses</h3>
      <div v-if="isQuizActive" class="pulse-icon">
        <span class="dot"></span>
        LIVE
      </div>
    </div>

    <div v-if="!isQuizActive" class="empty-state">
      <p>Launch a quiz to see live responses</p>
    </div>

    <div v-else class="results-content">
      <!-- MCQ View -->
      <template v-if="results.type === 'multiple_choice'">
        <div class="summary-stats">
          <div class="summary-item total">
            <span class="val">{{ results.totalResponses }}</span>
            <span class="lbl">Total Responses</span>
          </div>
        </div>

        <div class="bars-container">
          <div v-for="(opt, idx) in results.options" :key="idx" class="bar-row">
            <div class="bar-label">{{ opt.text || 'Option ' + String.fromCharCode(65 + idx) }}</div>
            <div class="bar-wrapper">
              <div 
                class="bar-fill" 
                :style="{ width: opt.percentage + '%' }"
              ></div>
              <span class="bar-count">{{ opt.count }}</span>
            </div>
          </div>
        </div>
      </template>

      <!-- Short Answer View -->
      <template v-else-if="results.type === 'short_answer'">
        <div class="summary-header">
          <span class="total-badge">{{ results.responses?.length || 0 }} Responses Received</span>
        </div>
        <div class="responses-list">
          <div v-for="(resp, idx) in results.responses" :key="idx" class="response-card">
            <div class="resp-main">
              <div class="student-info">
                <span class="name">{{ resp.display_name }}</span>
                <span class="time">{{ new Date(resp.answered_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) }}</span>
              </div>
              <p class="answer-text">"{{ resp.answer }}"</p>
            </div>
            <div class="resp-actions">
              <button 
                class="btn-mark" 
                :class="{ correct: resp.is_correct }"
                @click="markCorrect(resp)"
              >
                {{ resp.is_correct ? '✅ +10 pts' : 'Mark Correct' }}
              </button>
            </div>
          </div>
        </div>
      </template>
    </div>
  </div>
</template>

<style scoped>
.live-results {
  background: #f8fafc;
  border-radius: 0.75rem;
  padding: 1.25rem;
  border: 1px solid #e2e8f0;
}

.results-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.25rem;
}

.panel-subtitle {
  font-size: 0.95rem;
  font-weight: 700;
  margin: 0;
  color: #475569;
}

.pulse-icon {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 0.7rem;
  font-weight: 800;
  color: #ef4444;
}

.pulse-icon .dot {
  width: 6px;
  height: 6px;
  background: #ef4444;
  border-radius: 50%;
  animation: pulse 1.5s infinite;
}

@keyframes pulse {
  0% { transform: scale(0.95); opacity: 0.7; }
  50% { transform: scale(1.2); opacity: 1; }
  100% { transform: scale(0.95); opacity: 0.7; }
}

.empty-state {
  text-align: center;
  padding: 2rem 0;
  color: #94a3b8;
  font-size: 0.9rem;
}

.summary-stats {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap: 1rem;
  margin-bottom: 2rem;
}

.summary-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 0.75rem;
  border-radius: 0.5rem;
  background: white;
  border: 1px solid #e2e8f0;
}

.summary-item .val {
  font-size: 1.25rem;
  font-weight: 800;
}

.summary-item .lbl {
  font-size: 0.65rem;
  font-weight: 700;
  text-transform: uppercase;
  color: #64748b;
}

.correct .val { color: #10b981; }
.wrong .val { color: #ef4444; }
.total .val { color: #6366f1; }

.bars-container {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.bar-row {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.bar-label {
  width: 60px;
  font-size: 0.8rem;
  font-weight: 600;
  color: #64748b;
}

.bar-wrapper {
  flex: 1;
  height: 24px;
  background: white;
  border-radius: 12px;
  position: relative;
  overflow: hidden;
  border: 1px solid #e2e8f0;
}

.bar-fill {
  height: 100%;
  background: #6366f1;
  transition: width 0.5s ease-out;
}

.bar-count {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  font-size: 0.75rem;
  font-weight: 700;
  color: #475569;
}

/* Short Answer Styles */
.summary-header {
  margin-bottom: 1rem;
}

.total-badge {
  background: #e0e7ff;
  color: #4338ca;
  font-size: 0.75rem;
  font-weight: 700;
  padding: 4px 10px;
  border-radius: 20px;
}

.responses-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  max-height: 400px;
  overflow-y: auto;
  padding-right: 4px;
}

.response-card {
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 0.75rem;
  padding: 0.75rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
  transition: all 0.2s;
}

.response-card:hover {
  border-color: #cbd5e1;
  box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.resp-main {
  flex: 1;
}

.student-info {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 4px;
}

.student-info .name {
  font-size: 0.85rem;
  font-weight: 700;
  color: #1e293b;
}

.student-info .time {
  font-size: 0.7rem;
  color: #94a3b8;
}

.answer-text {
  margin: 0;
  font-size: 0.9rem;
  color: #475569;
  font-style: italic;
  line-height: 1.4;
}

.btn-mark {
  padding: 6px 12px;
  background: #f1f5f9;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 700;
  color: #64748b;
  cursor: pointer;
  transition: all 0.2s;
  white-space: nowrap;
}

.btn-mark:hover:not(.correct) {
  background: #e2e8f0;
  color: #1e293b;
}

.btn-mark.correct {
  background: #ecfdf5;
  border-color: #10b981;
  color: #059669;
  cursor: default;
}
</style>
