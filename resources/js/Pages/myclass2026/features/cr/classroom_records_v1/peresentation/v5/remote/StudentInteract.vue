<script>
export default { layout: false };
</script>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useGameStore } from '../stores/gameStore';
import { usePresentationStore } from '../stores/presentationStore';
import { useRealtimeChannel } from '@/composables/useRealtimeChannel';
import { database } from '@/firebase/init';
import { ref as dbRef, set } from 'firebase/database';
import JoinForm from './components/student/JoinForm.vue';
import StudentSlideView from './components/student/StudentSlideView.vue';
import QuizCard from './components/student/QuizCard.vue';
import TimerBar from './components/student/TimerBar.vue';
import StudentScoreHUD from './components/student/StudentScoreHUD.vue';

const gameStore = useGameStore();
const presentation = usePresentationStore();

const isJoined = ref(false);
const currentQuiz = ref(null);
const studentName = ref('');

// Real-time listener for the student channel
const studentChannel = computed(() => 
  gameStore.accessCode ? `quiz_${gameStore.accessCode}` : null
);

useRealtimeChannel(studentChannel, (signal) => {
  console.log('Student received signal:', signal);
  
  if (signal.event === 'SLIDE_CHANGED') {
    presentation.selectSlide(signal.context.slideIndex);
  } else if (signal.event === 'QUIZ_STARTED') {
    currentQuiz.value = {
      id: signal.context.questionId,
      endTime: signal.context.endTime
    };
  } else if (signal.event === 'QUIZ_ENDED') {
    currentQuiz.value = null;
  }
});

const handleJoin = (data) => {
  studentName.value = data.name;
  isJoined.value = true;
};

// Debugging
const isTestingSignal = ref(false);
const testResult = ref(null);

const testDirectWrite = async () => {
  if (!gameStore.accessCode) return;
  isTestingSignal.value = true;
  testResult.value = '⚡ Testing...';
  
  try {
    const path = `channels/quiz_${gameStore.accessCode}_teacher`;
    const signalRef = dbRef(database, path);
    
    await set(signalRef, {
      event: 'STUDENT_TEST_PING',
      context: { name: studentName.value, time: new Date().toLocaleTimeString() },
      timestamp: Math.floor(Date.now() / 1000),
      trigger_id: 'test_' + Math.random()
    });
    
    testResult.value = '✅ Success! Check Teacher Log.';
  } catch (err) {
    console.error('Test write failed:', err);
    testResult.value = `❌ FAILED: ${err.code || err.message}`;
  } finally {
    isTestingSignal.value = false;
    setTimeout(() => { if (testResult.value?.includes('Success')) testResult.value = null; }, 5000);
  }
};
</script>

<template>
  <div class="student-interact-v5">
    <!-- Join Flow -->
    <div v-if="!isJoined" class="join-container">
      <JoinForm @joined="handleJoin" />
    </div>

    <!-- Live Interaction Flow -->
    <template v-else>
      <header class="student-header shadow-sm">
        <div class="user-info">
          <span class="avatar">👤</span>
          <span class="name">{{ studentName }}</span>
        </div>
        <div class="session-badge">
          Code: <strong>{{ gameStore.accessCode }}</strong>
        </div>
        
        <!-- Debug Tool -->
        <div class="student-debug">
          <button 
            class="btn-debug-mini" 
            :disabled="isTestingSignal"
            @click="testDirectWrite"
          >
            🧪 {{ isTestingSignal ? '...' : 'Signal' }}
          </button>
          <div v-if="testResult" class="debug-toast" :class="{ error: testResult.includes('❌') }">
            {{ testResult }}
          </div>
        </div>
      </header>

      <main class="student-main">
        <!-- Live Slide View (Synced with Teacher) -->
        <section class="slide-preview card">
          <StudentSlideView />
        </section>

        <!-- Dynamic Quiz Card overlay/section -->
        <section v-if="currentQuiz" class="quiz-interaction">
          <TimerBar :end-time="currentQuiz.endTime" />
          <QuizCard :quiz-id="currentQuiz.id" />
        </section>
        
        <section v-else class="waiting-area">
          <div class="loader-dots">
            <span></span><span></span><span></span>
          </div>
          <p>Waiting for teacher to start next activity...</p>
        </section>
      </main>

      <!-- Bottom HUD: Score + Group -->
      <StudentScoreHUD />
    </template>
  </div>
</template>

<style scoped>
.student-interact-v5 {
  min-height: 100vh;
  background-color: #f1f5f9;
  display: flex;
  flex-direction: column;
  font-family: ui-sans-serif, system-ui, -apple-system, sans-serif;
}

.join-container {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1.5rem;
}

.student-header {
  background: white;
  padding: 0.75rem 1rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid #e2e8f0;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 8px;
}

.user-info .name {
  font-weight: 700;
  color: #1e293b;
  font-size: 0.95rem;
}

.session-badge {
  font-size: 0.8rem;
  color: #64748b;
  background: #f8fafc;
  padding: 4px 10px;
  border-radius: 20px;
  border: 1px solid #e2e8f0;
}

.student-main {
  flex: 1;
  display: flex;
  flex-direction: column;
  padding: 1rem;
  gap: 1rem;
  margin-bottom: 80px; /* Space for HUD */
}

.slide-preview {
  background: white;
  border-radius: 1rem;
  overflow: hidden;
  box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
  aspect-ratio: 16 / 9;
}

.quiz-interaction {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.waiting-area {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: #64748b;
  text-align: center;
  padding: 2rem;
}

.loader-dots {
  display: flex;
  gap: 8px;
  margin-bottom: 1rem;
}

.loader-dots span {
  width: 10px;
  height: 10px;
  background: #6366f1;
  border-radius: 50%;
  animation: bounce 1.4s infinite ease-in-out both;
}

.loader-dots span:nth-child(1) { animation-delay: -0.32s; }
.loader-dots span:nth-child(2) { animation-delay: -0.16s; }

@keyframes bounce {
  0%, 80%, 100% { transform: scale(0); }
  40% { transform: scale(1.0); }
}

.card {
  border: 1px solid #e2e8f0;
}

.student-debug {
  position: relative;
}

.btn-debug-mini {
  padding: 4px 8px;
  background: #f1f5f9;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 700;
  color: #64748b;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-debug-mini:hover:not(:disabled) {
  background: #e2e8f0;
  color: #1e293b;
}

.debug-toast {
  position: absolute;
  top: 100%;
  right: 0;
  margin-top: 8px;
  background: #10b981;
  color: white;
  padding: 6px 10px;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 600;
  white-space: nowrap;
  box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
  z-index: 100;
  animation: fadeIn 0.2s ease;
}

.debug-toast.error {
  background: #ef4444;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-5px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
