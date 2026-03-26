<script>
export default { layout: false };
</script>

<script setup>
import { onMounted, onUnmounted, ref, computed } from 'vue';
import { usePresentationStore } from '../stores/presentationStore';
import { useGameStore } from '../stores/gameStore';
import { useRealtimeChannel } from '@/composables/useRealtimeChannel';
import SessionHeader from './components/teacher/SessionHeader.vue';
import SlideRemoteControl from './components/teacher/SlideRemoteControl.vue';
import QuizLauncher from './components/teacher/QuizLauncher.vue';
import LiveResultsPanel from './components/teacher/LiveResultsPanel.vue';
import ParticipantRoster from './components/teacher/ParticipantRoster.vue';
import EditorCanvas from '../components/EditorCanvas.vue';
import LeaderboardOverlay from '../components/LeaderboardOverlay.vue';
import QrcodeVue from 'qrcode.vue';
import axios from 'axios';

const props = defineProps({
  initialSession: {
    type: Object,
    default: () => ({})
  }
});

const presentation = usePresentationStore();
const gameStore = useGameStore();

// UI state
const isSidebarOpen = ref(true);
const activeTab = ref('quiz'); // 'quiz' or 'participants'
const isQRModalOpen = ref(false);

const studentJoinUrl = computed(() => {
  const baseUrl = window.location.origin;
  return `${baseUrl}/classroom-records/presentation/remote/student?code=${gameStore.accessCode}`;
});

// Initialize session if passed from Inertia
onMounted(() => {
  if (props.initialSession?.id) {
    gameStore.setSession(
      props.initialSession.id, 
      props.initialSession.access_code,
      props.initialSession.status
    );
  }
});

const toggleSidebar = () => isSidebarOpen.value = !isSidebarOpen.value;

// Debugging
const isDebuggingFirebase = ref(false);
const debugOutput = ref([]);

const debugFirebase = async () => {
  if (!gameStore.accessCode) return;
  isDebuggingFirebase.value = true;
  debugOutput.value.unshift({ time: new Date().toLocaleTimeString(), type: 'info', message: '🚀 Starting Firebase Test...' });
  
  try {
    const response = await axios.post(route('cr.debug-firebase'), {
      access_code: gameStore.accessCode
    });
    
    debugOutput.value.unshift({ 
      time: new Date().toLocaleTimeString(), 
      type: response.data.success ? 'success' : 'error', 
      message: `📡 Server: ${response.data.message}`,
      details: response.data.details
    });
  } catch (err) {
    debugOutput.value.unshift({ 
      time: new Date().toLocaleTimeString(), 
      type: 'error', 
      message: `❌ API Error: ${err.response?.data?.message || err.message}` 
    });
  } finally {
    isDebuggingFirebase.value = false;
  }
};

// Real-time listener for signals from students
const teacherChannel = computed(() => 
  gameStore.accessCode ? `quiz_${gameStore.accessCode}_teacher` : null
);

useRealtimeChannel(teacherChannel, (signal) => {
  console.log('Teacher received signal:', signal);
  debugOutput.value.unshift({ 
    time: new Date().toLocaleTimeString(), 
    type: 'signal', 
    message: `🔔 Received: ${signal.event}`,
    context: signal.context 
  });
});
</script>

<template>
  <div class="teacher-presenter-v5">
    <!-- Header: Code, Class, Presence -->
    <SessionHeader @show-qr="isQRModalOpen = true" />

    <main class="main-layout" :class="{ 'sidebar-closed': !isSidebarOpen }">
      <!-- Left: Slide Navigation & Canvas -->
      <section class="presentation-area">
        <div class="canvas-wrapper card">
          <EditorCanvas :read-only="true" />
          
          <!-- Slide Navigation HUD -->
          <SlideRemoteControl />
        </div>
      </section>

      <!-- Right: Control Panel (Mobile: Stacks below) -->
      <aside class="control-panel card">
        <div class="panel-tabs">
          <button 
            :class="{ active: activeTab === 'quiz' }" 
            @click="activeTab = 'quiz'"
          >
            Quiz Control
          </button>
          <button 
            :class="{ active: activeTab === 'participants' }" 
            @click="activeTab = 'participants'"
          >
            Participants
          </button>
        </div>

        <div class="panel-content">
          <div v-show="activeTab === 'quiz'">
            <QuizLauncher />
            <LiveResultsPanel class="mt-4" />
          </div>
          <div v-show="activeTab === 'participants'">
            <ParticipantRoster />
            
            <!-- Debug Tools -->
            <div class="debug-tools mt-8 pt-4 border-top">
              <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Debug Tools</h4>
              <button 
                class="btn-debug" 
                :disabled="isDebuggingFirebase"
                @click="debugFirebase"
              >
                {{ isDebuggingFirebase ? 'Testing...' : '🧪 Test Firebase' }}
              </button>
              
              <div v-if="debugOutput.length > 0" class="debug-log mt-2">
                <div v-for="(log, i) in debugOutput.slice(0, 5)" :key="i" :class="['log-entry', log.type]">
                  <span class="log-time">[{{ log.time }}]</span>
                  <span class="log-msg">{{ log.message }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <button class="toggle-btn" @click="toggleSidebar">
          {{ isSidebarOpen ? '→' : '←' }}
        </button>
      </aside>
    </main>

    <!-- Floating Leaderboard Overlay -->
    <LeaderboardOverlay />
    
    <button 
      class="fab-leaderboard" 
      @click="gameStore.isLeaderboardOpen = true"
    >
      🏆
    </button>

    <!-- QR Modal -->
    <div v-if="isQRModalOpen" class="modal-overlay" @click.self="isQRModalOpen = false">
      <div class="qr-modal card shadow-2xl">
        <button class="close-qr" @click="isQRModalOpen = false">×</button>
        <h2>Join as Student</h2>
        <p>Scan this code to participate in the live quiz</p>
        
        <div class="qr-wrapper">
          <qrcode-vue :value="studentJoinUrl" :size="200" level="H" />
        </div>
        
        <div class="join-info">
          <span class="url">{{ studentJoinUrl }}</span>
          <span class="code">Code: {{ gameStore.accessCode }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10000;
  backdrop-filter: blur(4px);
}

.qr-modal {
  background: white;
  padding: 2.5rem;
  border-radius: 1.5rem;
  text-align: center;
  position: relative;
  width: 90%;
  max-width: 400px;
}

.qr-modal h2 { margin: 0 0 0.5rem; font-weight: 800; color: #1e293b; }
.qr-modal p { color: #64748b; margin-bottom: 2rem; }

.qr-wrapper {
  background: white;
  padding: 1rem;
  border: 1px solid #e2e8f0;
  border-radius: 1rem;
  display: inline-block;
  margin-bottom: 1.5rem;
}

.join-info {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.join-info .url {
  font-size: 0.75rem;
  color: #64748b;
  word-break: break-all;
}

.join-info .code {
  font-size: 1.25rem;
  font-weight: 800;
  color: #1e293b;
}

.close-qr {
  position: absolute;
  top: 1rem;
  right: 1.25rem;
  background: none;
  border: none;
  font-size: 1.75rem;
  color: #94a3b8;
  cursor: pointer;
}
.teacher-presenter-v5 {
  display: flex;
  flex-direction: column;
  height: 100vh;
  background-color: #f8fafc;
  color: #1e293b;
  overflow: hidden;
}

.main-layout {
  display: flex;
  flex: 1;
  gap: 1.5rem;
  padding: 1.5rem;
  overflow: hidden;
  transition: all 0.3s ease;
}

.presentation-area {
  flex: 1;
  display: flex;
  flex-direction: column;
  min-width: 0;
}

.canvas-wrapper {
  flex: 1;
  position: relative;
  background: white;
  border-radius: 1rem;
  box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.control-panel {
  width: 400px;
  background: white;
  border-radius: 1rem;
  box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
  display: flex;
  flex-direction: column;
  position: relative;
  transition: all 0.3s ease;
}

.sidebar-closed .control-panel {
  width: 0;
  padding: 0;
  margin: 0;
  overflow: hidden;
  opacity: 0;
}

.panel-tabs {
  display: flex;
  border-bottom: 1px solid #e2e8f0;
}

.panel-tabs button {
  flex: 1;
  padding: 1rem;
  border: none;
  background: none;
  font-weight: 600;
  color: #64748b;
  cursor: pointer;
  transition: all 0.2s;
}

.panel-tabs button.active {
  color: #6366f1;
  border-bottom: 2px solid #6366f1;
  background: #f8faff;
}

.panel-content {
  flex: 1;
  padding: 1.5rem;
  overflow-y: auto;
}

.toggle-btn {
  position: absolute;
  left: -12px;
  top: 50%;
  transform: translateY(-50%);
  width: 24px;
  height: 24px;
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  z-index: 10;
}

.fab-leaderboard {
  position: fixed;
  bottom: 2rem;
  left: 2rem;
  width: 56px;
  height: 56px;
  background: #6366f1;
  color: white;
  border: none;
  border-radius: 50%;
  font-size: 1.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1);
  transition: transform 0.2s;
}

.fab-leaderboard:hover {
  transform: scale(1.1);
}

.mt-4 { margin-top: 1rem; }

@media (max-width: 1024px) {
  .main-layout {
    flex-direction: column;
    overflow-y: auto;
  }
  
  .control-panel {
    width: 100%;
    min-height: 500px;
  }
  
  .teacher-presenter-v5 {
    overflow-y: auto;
  }
  
  .canvas-wrapper {
    min-height: 400px;
  }
}

.card {
  border: 1px solid #e2e8f0;
}

.btn-debug {
  width: 100%;
  padding: 0.5rem;
  background: #f1f5f9;
  border: 1px solid #e2e8f0;
  border-radius: 0.5rem;
  font-size: 0.75rem;
  font-weight: 700;
  color: #475569;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-debug:hover:not(:disabled) {
  background: #e2e8f0;
  color: #1e293b;
}

.btn-debug:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.debug-log {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.log-entry {
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
  font-size: 10px;
  padding: 4px 6px;
  border-radius: 4px;
  background: #f8fafc;
  border-left: 3px solid #cbd5e1;
}

.log-entry.success { border-left-color: #10b981; background: #f0fdf4; color: #166534; }
.log-entry.error { border-left-color: #ef4444; background: #fef2f2; color: #991b1b; }
.log-entry.signal { border-left-color: #6366f1; background: #f5f3ff; color: #4338ca; }

.log-time { color: #94a3b8; margin-right: 4px; }
.log-msg { font-weight: 600; }
</style>
