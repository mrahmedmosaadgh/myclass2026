<template>
  <Head title="Micro Component Test" />

  <div class=" p-6">
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
                
                <!-- New chart components -->
                <q-item clickable v-close-popup @click="currentView = 'chart'">
                    <q-item-section avatar>
                        <span class="text-xl">📊</span>
                    </q-item-section>
                    <q-item-section>
                        <q-item-label>ECharts Components</q-item-label>
                        <q-item-label caption>Various Chart Components</q-item-label>
                    </q-item-section>
                </q-item>
                
                <q-item clickable v-close-popup @click="currentView = 'multiplication'">
                    <q-item-section avatar>
                        <span class="text-xl">✖️</span>
                    </q-item-section>
                    <q-item-section>
                        <q-item-label>Multiplication Table</q-item-label>
                        <q-item-label caption>Multiplication Learning Component</q-item-label>
                    </q-item-section>
                </q-item>
                
                <q-item clickable v-close-popup @click="currentView = 'multiplication2'">
                    <q-item-section avatar>
                        <span class="text-xl">🎯</span>
                    </q-item-section>
                    <q-item-section>
                        <q-item-label>Multiplication Drag &amp; Drop</q-item-label>
                        <q-item-label caption>Drag and Match Multiplication</q-item-label>
                    </q-item-section>
                </q-item>

                <q-item clickable v-close-popup @click="currentView = 'multiplication3'">
                    <q-item-section avatar>
                        <span class="text-xl">🔢</span>
                    </q-item-section>
                    <q-item-section>
                        <q-item-label>Multiple Choice Quiz</q-item-label>
                        <q-item-label caption>Modular Multiplication Quiz</q-item-label>
                    </q-item-section>
                </q-item>

                <q-item clickable v-close-popup @click="currentView = 'multiplication4'">
                    <q-item-section avatar>
                        <span class="text-xl">📝</span>
                    </q-item-section>
                    <q-item-section>
                        <q-item-label>Tables Diploma</q-item-label>
                        <q-item-label caption>Input Based Multiplcation Quiz</q-item-label>
                    </q-item-section>
                </q-item>
                
                <q-item clickable v-close-popup @click="currentView = 'ixllineplot'">
                    <q-item-section avatar>
                        <span class="text-xl">📈</span>
                    </q-item-section>
                    <q-item-section>
                        <q-item-label>IXL Line Plot</q-item-label>
                        <q-item-label caption>Interactive Line Plot Visualization</q-item-label>
                    </q-item-section>
                </q-item>
                
                <q-item clickable v-close-popup @click="currentView = 'presentation'">
                    <q-item-section avatar>
                        <span class="text-xl">📽️</span>
                    </q-item-section>
                    <q-item-section>
                        <q-item-label>Presentation Editor</q-item-label>
                        <q-item-label caption>PowerPoint-like Slide Editor</q-item-label>
                    </q-item-section>
                </q-item>
                
                <q-item clickable v-close-popup @click="currentView = 'mytable'">
                    <q-item-section avatar>
                        <span class="text-xl">🗓️</span>
                    </q-item-section>
                    <q-item-section>
                        <q-item-label>Schedule Timeline</q-item-label>
                        <q-item-label caption>Interactive HTML Schedule</q-item-label>
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
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold">Real-time Question Components</h2>
            
            <!-- Firebase Connection Status -->
            <div class="flex items-center gap-2 px-3 py-1 rounded-full text-sm font-medium"
                 :class="firebaseConnectionStatus.connected ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'">
                <span class="animate-pulse">●</span>
                {{ firebaseConnectionStatus.message }}
            </div>
        </div>
        
        <!-- Room ID Display -->
        <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
            <div class="text-sm text-blue-800">
                <strong>Room ID:</strong> <code class="px-2 py-1 bg-blue-100 rounded">{{ questionId }}</code>
                <span class="ml-2 text-xs text-blue-600">(Channel: question.{{ questionId }})</span>
            </div>
        </div>
        
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
    
    <!-- Chart Components View -->
    <div v-show="currentView === 'chart'" class="animate-fade-in">
        <h2 class="text-xl font-bold mb-4">ECharts Components Test</h2>
        <div class="grid gap-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-2">EChart Component</h3>
                <EChartComponent 
                  title="Sample Chart"
                  type="bar"
                  :labels="['Jan', 'Feb', 'Mar', 'Apr', 'May']"
                  :datasets="[{ name: 'Sales', data: [10, 20, 15, 25, 30] }]"
                />
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-2">EChart Component V2</h3>
                <EChartComponent_v2 
                  title="Performance Metrics"
                  type="line"
                  :labels="['Mon', 'Tue', 'Wed', 'Thu', 'Fri']"
                  :datasets="[{ name: 'Tasks', data: [12, 19, 3, 5, 2] }]"
                />
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-2">Test Chart V2</h3>
                <TestChartV2 />
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-2">Dynamic Tree Editor</h3>
                <DynamicTreeEditor />
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-2">Drag & Drop Component</h3>
                <DragDrop>
                  <div class="p-4 bg-gray-200 rounded">Drag me around!</div>
                </DragDrop>
            </div>
        </div>
    </div>
    
    <!-- Multiplication Component View -->
    <div v-show="currentView === 'multiplication'" class="animate-fade-in">
        <h2 class="text-xl font-bold mb-4">Multiplication Table Component</h2>
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-2">Multiplication Practice</h3>
            <mulitp />
        </div>
    </div>
    
    <!-- Multiplication Drag & Drop Component View -->
    <div v-show="currentView === 'multiplication2'" class="animate-fade-in">
        <h2 class="text-xl font-bold mb-4">Multiplication Drag &amp; Drop Component</h2>
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-2">Drag and Match Multiplication</h3>
            <multip2 />
        </div>
    </div>

    <!-- Multiple Choice Quiz (multip3) -->
    <div v-show="currentView === 'multiplication3'" class="animate-fade-in">
        <h2 class="text-xl font-bold mb-4">Multiple Choice Quiz</h2>
        <div class="bg-white rounded-lg shadow p-6">
            <MultipleChoiceQuiz
                :questions="multip3Questions"
                @quiz-complete="handleQuizComplete"
            />

            <!-- Progress Badge — shown after the quiz is submitted at least once -->
            <div v-if="studentProgress.completedAt" class="mt-6 p-4 bg-green-50 border border-green-200 rounded-lg text-center">
                <p class="text-green-800 font-semibold text-sm">✅ Last Result saved</p>
                <p class="text-2xl font-bold text-green-700 mt-1">
                    {{ studentProgress.correctCount }} / {{ studentProgress.total }}
                    <span class="text-base font-normal text-green-600">({{ studentProgress.percentage }}%)</span>
                </p>
                <p class="text-xs text-green-500 mt-1">Completed at {{ new Date(studentProgress.completedAt).toLocaleTimeString() }}</p>
            </div>
        </div>
    </div>

    <!-- Tables Diploma (multip4) -->
    <div v-show="currentView === 'multiplication4'" class="animate-fade-in">
        <h2 class="text-xl font-bold mb-4">Tables Diploma Quiz</h2>
        <div class="bg-white rounded-lg shadow p-6">
            <InputQuiz 
                @quiz-complete="handleMultip4Complete"
            />
            
            <!-- Progress log -->
            <div v-if="multip4Progress.completedAt" class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg text-center">
                <p class="text-blue-800 font-semibold text-sm">✅ Diploma Finished!</p>
                <p class="text-2xl font-bold text-blue-700 mt-1">
                    Time Limit Challenge Passed
                </p>
                <p class="text-md text-blue-600 font-medium">Clear Time: {{ multip4Progress.formattedTime }}</p>
                <p class="text-xs text-blue-500 mt-1">Completed at {{ new Date(multip4Progress.completedAt).toLocaleTimeString() }}</p>
            </div>
        </div>
    </div>

    <!-- IXL Line Plot Component -->
    <div v-show="currentView === 'ixllineplot'" class="animate-fade-in">
        <h2 class="text-xl font-bold mb-4">IXL Line Plot Visualization</h2>
        <div class="bg-white rounded-lg shadow p-6">
            <IXLLinePlotExample />
        </div>
        
        <!-- Component Documentation -->
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-3 text-blue-900">📋 Component Features</h3>
            <div class="grid md:grid-cols-2 gap-4 text-sm text-blue-800">
                <div>
                    <h4 class="font-medium mb-2">📊 Visualization Components</h4>
                    <ul class="space-y-1 list-disc list-inside">
                        <li>X Marks on Number Line</li>
                        <li>Configurable Ticks and Labels</li>
                        <li>Responsive SVG Rendering</li>
                        <li>Animation Support</li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-medium mb-2">🎯 Smart Score Features</h4>
                    <ul class="space-y-1 list-disc list-inside">
                        <li>Visual Progress Representation</li>
                        <li>Color-Coded Mastery Levels</li>
                        <li>Adaptive Scoring Display</li>
                        <li>Performance Analytics</li>
                    </ul>
                </div>
            </div>
            
            <div class="mt-4 pt-4 border-t border-blue-200">
                <h4 class="font-medium text-blue-900 mb-2">🔧 Usage Information</h4>
                <p class="text-sm text-blue-700">
                    This component demonstrates reusable Vue components for educational data visualization.
                    The components are designed to work with any lesson or question type and use JSON-based
                    configuration for easy maintenance and scaling.
                </p>
            </div>
        </div>
    </div>

    <!-- Presentation Editor Component -->
    <div v-show="currentView === 'presentation'" class="animate-fade-in h-screen -mx-6 -mt-6">
        <PresentationEditor />
    </div>
    
    <!-- Schedule Timeline Component -->
    <div v-show="currentView === 'mytable'" class="animate-fade-in">
        <h2 class="text-xl font-bold mb-4">Schedule Timeline</h2>
        <div class="bg-white rounded-lg shadow p-6">
            <MyTableSchedule />
        </div>
    </div>

    <div class="mt-6 text-xs text-gray-500">This page and components are intentionally minimal so you can iterate and copy them elsewhere when ready.</div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import AppLayoutDefault from '@/Layouts/AppLayoutDefault.vue';
import { useRealtimeChannel } from '@/composables/useRealtimeChannel';
import axios from 'axios';
import MicroDropdown from './MicroDropdown.vue';
import AudioPlayer from './comptest/AudioPlayer.vue';
import SecureNumpad from './comptest/SecureNumpad/SecureNumpad.vue';
import QuestionDisplay from './comptest/realtimetest/QuestionDisplay.vue';
import QuestionInput from './comptest/realtimetest/QuestionInput.vue';

// Import chart components from test1/charts directory
import EChartComponent from './comptest/test1/charts/EChartComponent.vue';
import EChartComponent_v2 from './comptest/test1/charts/EChartComponent_v2.vue';
import TestChartV2 from './comptest/test1/charts/TestChartV2.vue';
import DynamicTreeEditor from './comptest/test1/charts/DynamicTreeEditor.vue';
import DragDrop from './comptest/test1/charts/DragDrop.vue';
// Removed TreeNode import to prevent circular dependency - it's imported internally by DynamicTreeEditor

// Import multiplication components
import mulitp from './comptest/test1/multiplication/mulitp.vue';
import multip2 from './comptest/test1/multiplication/multip2.vue';
import MultipleChoiceQuiz from './comptest/test1/multiplication/multip3/MultipleChoiceQuiz.vue';
import quizData from './comptest/test1/multiplication/multip3/quizData.js';
import InputQuiz from './comptest/test1/multiplication/multip4/InputQuiz.vue';

// Import IXL Line Plot components
import IXLLinePlotExample from './comptest/test1/smartscore/examples/IXLLinePlotExample.vue';

// Import Presentation Editor component
import { PresentationEditor } from './comptest/test1/ppt';

// Import MyTableSchedule component
import MyTableSchedule from './mytable/MyTableSchedule/MyTableSchedule.vue';

// multip3 — quiz data and student progress
const multip3Questions = ref(quizData);
const studentProgress = ref({
    correctCount: 0,
    total:        0,
    percentage:   0,
    completedAt:  null,
});

const handleQuizComplete = ({ correctCount, total, percentage }) => {
    studentProgress.value = {
        correctCount,
        total,
        percentage,
        completedAt: new Date().toISOString(),
    };
    console.log('[Index] Quiz complete — studentProgress saved:', studentProgress.value);
};

// multip4 — Tables Diploma
const multip4Progress = ref({
    time: 0,
    formattedTime: '',
    completedAt: null,
});

const handleMultip4Complete = ({ time, formattedTime }) => {
    multip4Progress.value = {
        time,
        formattedTime,
        completedAt: new Date().toISOString(),
    };
    console.log('[Index] multip4 Quiz complete:', multip4Progress.value);
};

defineOptions({
  layout: AppLayoutDefault
});

const currentView = ref('audio');
const currentViewLabel = computed(() => {
    switch(currentView.value) {
        case 'audio': return 'Audio Player';
        case 'numpad': return 'Secure Numpad';
        case 'realtime': return 'Real-time Questions';
        case 'chart': return 'ECharts Components';
        case 'multiplication': return 'Multiplication Table';
        case 'multiplication2': return 'Multiplication Drag & Drop';
        case 'multiplication3': return 'Multiple Choice Quiz';
        case 'multiplication4': return 'Tables Diploma';
        case 'ixllineplot': return 'IXL Line Plot';
        case 'presentation': return 'Presentation Editor';
        case 'mytable': return 'Schedule Timeline';
        default: return 'Micro Dropdown';
    }
});

// Numpad Demo State
const numpadValue = ref('');

// Schedule Timeline Demo State
import fullScheduleData from './mytable/full_schedule.json';

const periodTimes = {
  1: { start: '07:30', end: '08:15' },
  2: { start: '08:15', end: '09:00' },
  3: { start: '09:00', end: '09:45' },
  4: { start: '10:00', end: '10:45' }, // 15m break
  5: { start: '10:45', end: '11:30' },
  6: { start: '11:45', end: '12:30' }, // 15m break
  7: { start: '12:30', end: '13:15' },
  8: { start: '13:15', end: '14:00' },
};

const classColors = {
  '7A': '#FF6B6B',
  '5B': '#4ECDC4',
  '4A': '#45B7D1',
  '5A': '#FDCB6E',
};

const mappedScheduleItems = computed(() => {
    return fullScheduleData.map(day => {
        return {
            id: day.dayIndex,
            label: day.day,
            events: day.classes.filter(c => c.sub).map((c) => ({
                id: `${day.dayIndex}-${c.p}`,
                startTime: periodTimes[c.p]?.start || '00:00',
                endTime: periodTimes[c.p]?.end || '01:00',
                color: c.nafs ? '#A55EEA' : (classColors[c.sub] || '#8e44ad'),
                label: c.sub + (c.nafs ? ' (NAFS)' : '')
            }))
        };
    });
});

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
const questionId = 'rating-test-room-1'; // Fixed room ID for testing
const currentUserId = computed(() => usePage().props.auth?.user?.id || Math.floor(Math.random() * 1000));

// Firebase connection status
import { database } from '@/firebase/init';
const firebaseConnectionStatus = ref({
    connected: !!database,
    message: database ? 'Firebase Connected' : 'Firebase Not Configured'
});

// Log Firebase status immediately
console.log('🔍 Firebase initialization check:');
console.log('  Database object:', database);
console.log('  Question ID:', questionId);
console.log('  Channel:', `question.${questionId}`);
console.log('  Status:', firebaseConnectionStatus.value.message);

// Set up real-time listener at TOP LEVEL (critical for lifecycle hooks to work!)
useRealtimeChannel(`question.${questionId}`, (signal) => {
    console.log('🔔 SIGNAL RECEIVED:', signal);
    
    if (signal.event === 'NEW_RESPONSE') {
        console.log('📥 Processing NEW_RESPONSE event:', signal.context);
        
        const newAnswer = {
            id: Date.now().toString(),
            value: parseInt(signal.context.answer), // Ensure value is number for stats
            userName: signal.context.userName,
            timestamp: signal.timestamp
        };
        
        console.log('✅ Adding answer to display:', newAnswer);
        
        // Add to reactive array
        realtimeAnswers.value.push(newAnswer);
        
        // Keep only last 50
        if (realtimeAnswers.value.length > 50) {
            realtimeAnswers.value.shift();
        }
        
        console.log('📊 Total answers now:', realtimeAnswers.value.length);
    } else {
        console.log('⚠️ Received event is not NEW_RESPONSE:', signal.event);
    }
});

console.log('✅ Listener registered for channel: question.' + questionId);

const handleQuestionSubmit = async (answerData) => {
  console.log('📤 Submitting answer:', answerData);
  console.log('📍 To room:', questionId);
  console.log('👤 User ID:', currentUserId.value);
  
  try {
      const payload = {
          questionId: questionId,
          answer: answerData.value.toString(),
          userId: currentUserId.value
      };
      
      console.log('📦 API Payload:', payload);
      
      const response = await axios.post('/api/realtime/test/question', payload);
      
      console.log('✅ API Response:', response.data);
      console.log('Answer submitted to backend - waiting for Firebase broadcast...');
      
      // No local push needed, we wait for broadcast
  } catch (error) {
      console.error('❌ Failed to submit answer:', error);
      console.error('Error details:', error.response?.data);
      alert('Failed to submit answer. Check console.');
  }
};

const addRandomAnswer = () => {
  const names = ['Ahmed', 'Sara', 'Mohamed', 'Fatima', 'Ali', 'Nour', 'Omar', 'Layla'];
  const randomValue = Math.floor(Math.random() * 5) + 1;
  const randomName = names[Math.floor(Math.random() * names.length)];
  
  // Simulate via API to test loopback
  handleQuestionSubmit({
      value: randomValue
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