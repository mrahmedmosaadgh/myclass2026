<template>
  <Head title="Micro Component Test" />

  <div class="max-w-3xl mx-auto p-6">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Micro Component Test</h1>
        
        <!-- Component Switcher -->
        <q-btn-dropdown
            color="primary"
            :label="currentViewLabel"
            icon="layers"
            outline
            rounded
            class="text-blue-900"
        >
            <q-list>
                <q-item clickable v-close-popup @click="currentView = 'audio'">
                    <q-item-section avatar>
                        <span class="text-xl">🎵</span>
                    </q-item-section>
                    <q-item-section>
                        <q-item-label>Audio Player</q-item-label>
                        <q-item-label caption>Interactive Audio Components</q-item-label>
                    </q-item-section>
                </q-item>
                
                <q-item clickable v-close-popup @click="currentView = 'numpad'">
                    <q-item-section avatar>
                        <span class="text-xl">🔢</span>
                    </q-item-section>
                    <q-item-section>
                        <q-item-label>Secure Numpad</q-item-label>
                        <q-item-label caption>Custom Input with Sound</q-item-label>
                    </q-item-section>
                </q-item>
                
                <q-item clickable v-close-popup @click="currentView = 'dropdown'">
                    <q-item-section avatar>
                         <span class="text-xl">🔽</span>
                    </q-item-section>
                    <q-item-section>
                        <q-item-label>Micro Dropdown</q-item-label>
                        <q-item-label caption>Legacy Component Test</q-item-label>
                    </q-item-section>
                </q-item>
                
                <q-item clickable v-close-popup @click="currentView = 'realtime'">
                    <q-item-section avatar>
                        <span class="text-xl">❓</span>
                    </q-item-section>
                    <q-item-section>
                        <q-item-label>Real-time Questions</q-item-label>
                        <q-item-label caption>Live Q&A Components</q-item-label>
                    </q-item-section>
                </q-item>
            </q-list>
        </q-btn-dropdown>
    </div>

    <!-- Audio Component View -->
    <div v-show="currentView === 'audio'" class="animate-fade-in">
        <!-- Audio Component Test Section -->
        <div>
        <h2 class="text-xl font-bold mb-4">Audio Component Test</h2>
        
        <div class="grid gap-6 md:grid-cols-2">
            <!-- Case 1: Instant Replay -->
            <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-2">Option 1: Instant Replay</h3>
            <p class="text-sm text-gray-600 mb-4">Clicking while playing restarts the sound immediately (spam-able).</p>
            <AudioPlayer 
                src="/audio/click-234708.mp3" 
                :allow-replay-when-playing="true"
                label="Click Me (Instant)"
            />
            </div>

            <!-- Case 2: Wait to Finish -->
            <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-2">Option 2: Wait to Finish</h3>
            <p class="text-sm text-gray-600 mb-4">Clicking while playing does nothing until the sound finishes.</p>
            <AudioPlayer 
                src="/audio/purchase-success-384963.mp3" 
                :allow-replay-when-playing="false"
                label="Purchase (Wait)"
            />
            </div>
        </div>

        <!-- Case 3: Full UI -->
        <div class="bg-white rounded-lg shadow p-6 mt-6">
            <h3 class="text-lg font-semibold mb-4">Full Player UI</h3>
            <AudioPlayer 
            src="/audio/background_music1.mp3" 
            :show-controls="true"
            title="Background Music Demo"
            />
        </div>

        <!-- Playground Section -->
        <div class="bg-blue-50 border border-blue-100 rounded-lg shadow-sm p-6 mt-8">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-blue-900">🎛️ Audio Playground</h3>
                <span class="text-xs bg-blue-200 text-blue-800 px-2 py-1 rounded">Live Preview</span>
            </div>

            <div class="grid md:grid-cols-2 gap-8">
                <!-- Controls -->
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-blue-800 mb-1">Audio Source</label>
                        <select v-model="playground.src" class="w-full text-sm rounded-md border-blue-200 focus:border-blue-500 focus:ring-blue-500">
                            <option value="/audio/click/mixkit-mouse-click-close-1113.wav">Mouse Click (WAV)</option>
                            <option value="/audio/click/mixkit-fast-double-click-on-mouse-275.wav">Double Click (WAV)</option>
                            <option value="/audio/click/mixkit-gear-fast-lock-tap-2857.wav">Gear Lock (WAV)</option>
                            <option value="/audio/click/mixkit-on-or-off-light-switch-tap-2585.wav">Switch Tap (WAV)</option>
                            <option value="/audio/click-234708.mp3">Original Click (MP3)</option>
                            <option value="/audio/purchase-success-384963.mp3">Success (Medium)</option>
                            <option value="/audio/background_music1.mp3">Background Music (Long)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-blue-800 mb-1">Label / Title</label>
                        <input type="text" v-model="playground.label" class="w-full text-sm rounded-md border-blue-200 focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div class="space-y-2">
                        <label class="flex items-center gap-2 text-sm text-blue-900 cursor-pointer">
                            <input type="checkbox" v-model="playground.showControls" class="rounded text-blue-600 focus:ring-blue-500">
                            <span>Show Full Controls UI</span>
                        </label>
                        
                        <label class="flex items-center gap-2 text-sm text-blue-900 cursor-pointer">
                            <input type="checkbox" v-model="playground.allowReplay" class="rounded text-blue-600 focus:ring-blue-500">
                            <span>Allow Instant Replay (if no controls)</span>
                        </label>

                        <label class="flex items-center gap-2 text-sm text-blue-900 cursor-pointer">
                            <input type="checkbox" v-model="playground.loop" class="rounded text-blue-600 focus:ring-blue-500">
                            <span>Loop Audio</span>
                        </label>
                    </div>
                </div>

                <!-- Live Preview -->
                <div class="flex flex-col items-center justify-center p-6 bg-white/50 rounded-xl border border-blue-100">
                    <div class="mb-2 text-xs text-blue-400 font-mono">Component Preview</div>
                    <AudioPlayer 
                        :key="playground.src + playground.showControls"
                        :src="playground.src"
                        :show-controls="playground.showControls"
                        :allow-replay-when-playing="playground.allowReplay"
                        :loop="playground.loop"
                        :label="playground.label"
                        :title="playground.label"
                    />
                </div>
            </div>
        </div>
        </div>
    </div>

    <!-- Secure Numpad View -->
    <div v-show="currentView === 'numpad'" class="animate-fade-in">
        <h2 class="text-xl font-bold mb-4">Secure Numpad Test</h2>
        <div class="grid md:grid-cols-2 gap-8">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-2">POS / Kiosk Input</h3>
                <p class="text-sm text-gray-600 mb-6">Restricts input to onscreen pads only. Plays sound on every tap.</p>
                
                <div class="max-w-xs mx-auto">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Enter Amount</label>
                    <SecureNumpad 
                      v-model="numpadValue" 
                      placeholder="0.00" 
                      :max-length="6"
                      :allow-keyboard="true" 
                    />
                </div>

                <div class="mt-8 p-4 bg-gray-50 rounded-lg text-center">
                    <div class="text-xs text-gray-500 uppercase tracking-widest mb-1">Current Value</div>
                    <div class="text-3xl font-mono font-bold text-blue-600">{{ numpadValue || '---' }}</div>
                </div>
            </div>

            <div class="bg-blue-900 text-white rounded-lg shadow p-6 flex flex-col justify-center items-center text-center">
                <div class="text-4xl mb-4">🔒</div>
                <h3 class="text-xl font-bold mb-2">Why use this?</h3>
                <ul class="text-blue-100 text-sm space-y-2 text-left bg-blue-800/50 p-6 rounded-xl">
                    <li>✓ Prevents physical keyboard keyloggers</li>
                    <li>✓ Touch-optimized large targets</li>
                    <li>✓ Audio feedback validation</li>
                    <li>✓ Controlled input length</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Dropdown Component View (Legacy) -->
    <div v-show="currentView === 'dropdown'" class="animate-fade-in">
        <h2 class="text-xl font-bold mb-4">Micro Dropdown Test</h2>
        <div class="bg-white rounded-lg shadow p-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Choose an option</label>
            <MicroDropdown v-model="selected" :options="options" />

            <div class="mt-4 flex items-center gap-3">
                <button @click="showSelected"
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Show Selected</button>

                <div class="text-sm text-gray-700">Current: <strong class="ml-2">{{ selected || 'None' }}</strong></div>
            </div>
        </div>
    </div>

    <!-- Real-time Questions View -->
    <div v-show="currentView === 'realtime'" class="animate-fade-in">
        <h2 class="text-xl font-bold mb-4">Real-time Question Components</h2>
        
        <div class="grid gap-6 lg:grid-cols-2">
            <!-- Question Display Component (Teacher/Admin View) -->
            <QuestionDisplay
                question-title="Live Poll"
                question-text="How confident are you with today's lesson? (1-10)"
                :answers="realtimeAnswers"
            />

            <!-- Question Input Component (Student View) -->
            <QuestionInput
                question-text="How confident are you with today's lesson? (1-10)"
                question-id="lesson-confidence-q1"
                :min-value="1"
                :max-value="10"
                :allow-multiple-submissions="true"
                :default-user-name="'Student ' + Math.floor(Math.random() * 100)"
                @submit="handleQuestionSubmit"
            />
        </div>

        <!-- Demo Controls -->
        <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-3 text-yellow-900">📊 Demo Controls</h3>
            <div class="flex gap-3">
                <button 
                    @click="addRandomAnswer"
                    class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition-colors"
                >
                    Add Random Answer
                </button>
                <button 
                    @click="clearAnswers"
                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
                >
                    Clear All Answers
                </button>
            </div>
            <p class="text-xs text-yellow-700 mt-3">
                💡 Tip: Submit answers using the input component or click "Add Random Answer" to simulate multiple users.
            </p>
        </div>

        <!-- Integration Info -->
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-2 text-blue-900">🔗 Integration Guide</h3>
            <p class="text-sm text-blue-800 mb-3">
                These components are ready to integrate with your Firebase real-time database:
            </p>
            <ul class="text-sm text-blue-700 space-y-1 list-disc list-inside">
                <li>Use <code class="bg-blue-100 px-1 rounded">useRealtimeChannel</code> composable to listen for answers</li>
                <li>Send answers to backend via <code class="bg-blue-100 px-1 rounded">/api/realtime/test/question</code></li>
                <li>Both components support dark mode and are fully responsive</li>
                <li>See <code class="bg-blue-100 px-1 rounded">README.md</code> in the component directory for full documentation</li>
            </ul>
        </div>
    </div>

    <div class="mt-6 text-xs text-gray-500">This page and components are intentionally minimal so you can iterate and copy them elsewhere when ready.</div>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayoutDefault from '@/Layouts/AppLayoutDefault.vue';
import MicroDropdown from './MicroDropdown.vue';
import AudioPlayer from './comptest/AudioPlayer.vue';
import SecureNumpad from './comptest/SecureNumpad/SecureNumpad.vue';
import QuestionDisplay from './comptest/realtimetest/QuestionDisplay.vue';
import QuestionInput from './comptest/realtimetest/QuestionInput.vue';


defineOptions({
  layout: AppLayoutDefault
});

const currentView = ref('audio');
const currentViewLabel = computed(() => {
    switch(currentView.value) {
        case 'audio': return 'Audio Player';
        case 'numpad': return 'Secure Numpad';
        case 'realtime': return 'Real-time Questions';
        default: return 'Micro Dropdown';
    }
});

// Numpad Demo State
const numpadValue = ref('');

const selected = ref('');
const options = [
  { value: '', label: 'Select an option' },
  { value: 'alpha', label: 'Alpha' },
  { value: 'beta', label: 'Beta' },
  { value: 'gamma', label: 'Gamma' }
];

const playground = reactive({
  src: '/audio/click/mixkit-mouse-click-close-1113.wav',
  label: 'Test Sound',
  showControls: false,
  allowReplay: true,
  loop: false
});

function showSelected() {
  // simple demonstration; you can replace with more advanced test logic
  alert(`Selected: ${selected.value || 'None'}`);
}

// Real-time Questions Demo State
const realtimeAnswers = ref([]);

const handleQuestionSubmit = (answerData) => {
  // Add the answer to the display
  realtimeAnswers.value.push({
    id: Date.now().toString(),
    ...answerData
  });
  
  console.log('Answer submitted:', answerData);
  // In production, you would send this to your backend/Firebase here
  // Example: await axios.post('/api/realtime/test/question', answerData);
};

const addRandomAnswer = () => {
  const names = ['Ahmed', 'Sara', 'Mohamed', 'Fatima', 'Ali', 'Nour', 'Omar', 'Layla'];
  const randomName = names[Math.floor(Math.random() * names.length)];
  const randomValue = Math.floor(Math.random() * 10) + 1;
  
  realtimeAnswers.value.push({
    id: Date.now().toString(),
    senderName: randomName,
    value: randomValue,
    timestamp: Date.now() / 1000
  });
};

const clearAnswers = () => {
  realtimeAnswers.value = [];
};
</script>

<style scoped>
.animate-fade-in {
    animation: fadeIn 0.3s ease-out;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(5px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
