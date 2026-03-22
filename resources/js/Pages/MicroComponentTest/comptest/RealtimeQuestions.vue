<template>
  <div class="p-6">
    <div class="bg-white rounded-lg shadow p-6">
      <h2 class="text-xl font-bold mb-4">Real-time Questions Demo</h2>
      
      <!-- Question Display -->
      <div class="mb-6">
        <h3 class="text-lg font-semibold mb-2">Current Question</h3>
        <div class="p-4 bg-gray-50 rounded-lg">
          <p class="text-lg mb-2">{{ currentQuestion }}</p>
          <p class="text-gray-600">{{ currentQuestionText }}</p>
        </div>
      </div>
      
      <!-- Answer Input -->
      <div class="mb-6">
        <h3 class="text-lg font-semibold mb-2">Submit Answer</h3>
        <div class="flex items-center space-x-4">
          <input
            v-model="answerValue"
            type="number"
            placeholder="Enter your answer (1-5)"
            min="1"
            max="5"
            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          />
          <button
            @click="submitAnswer"
            :disabled="!answerValue || submitting"
            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed"
          >
            {{ submitting ? 'Submitting...' : 'Submit Answer' }}
          </button>
        </div>
      </div>
      
      <!-- Live Answers Display -->
      <div class="mb-6">
        <h3 class="text-lg font-semibold mb-2">Live Answers ({{ realtimeAnswers.length }}/50)</h3>
        <div class="space-y-2 max-h-64 overflow-y-auto">
          <div
            v-for="answer in realtimeAnswers"
            :key="answer.id"
            class="p-3 bg-gray-50 rounded-lg border-l-4"
            :class="{
              'border-green-500 bg-green-50': answer.userName === currentUserName,
              'border-blue-500 bg-blue-50': answer.userName !== currentUserName
            }"
          >
            <div class="flex justify-between items-start">
              <div>
                <span class="font-medium">{{ answer.userName }}</span>
                <span class="text-2xl font-bold ml-2">{{ answer.value }}</span>
              </div>
              <span class="text-sm text-gray-500">{{ formatTime(answer.timestamp) }}</span>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Connection Status -->
      <div class="mb-6">
        <h3 class="text-lg font-semibold mb-2">Connection Status</h3>
        <div class="p-4 rounded-lg" :class="{
          'bg-green-100 border-green-500 text-green-800': firebaseConnectionStatus.value.connected,
          'bg-red-100 border-red-500 text-red-800': !firebaseConnectionStatus.value.connected
        }">
          <div class="flex items-center">
            <div class="w-3 h-3 rounded-full mr-3" :class="{
              'bg-green-500': firebaseConnectionStatus.value.connected,
              'bg-red-500': !firebaseConnectionStatus.value.connected
            }"></div>
            <span>{{ firebaseConnectionStatus.value.message }}</span>
          </div>
        </div>
      </div>
      
      <!-- Test Controls -->
      <div class="mb-6">
        <h3 class="text-lg font-semibold mb-2">Test Controls</h3>
        <div class="flex space-x-4">
          <button
            @click="addRandomAnswer"
            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700"
          >
            Add Random Answer
          </button>
          <button
            @click="clearAnswers"
            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700"
          >
            Clear All Answers
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

// State
const answerValue = ref('');
const submitting = ref(false);
const realtimeAnswers = ref([]);
const questionId = 'rating-test-room-1';
const currentQuestion = 'How confident are you with today\'s lesson?';
const currentQuestionText = 'Rate your confidence level from 1 (Very Low) to 5 (Very High)';
const currentUserName = computed(() => 'User' + Math.floor(Math.random() * 1000));

// Submit answer
const submitAnswer = async () => {
    if (!answerValue.value) return;
    
    submitting.value = true;
    
    try {
        const payload = {
            questionId: questionId,
            answer: answerValue.value.toString(),
            userId: currentUserName.value
        };
        
        console.log('📦 Answer submitted:', payload);
        
        // Simulate API call
        setTimeout(() => {
            console.log('✅ Answer submitted successfully');
        }, 500);
        
        answerValue.value = '';
    } catch (error) {
        console.error('❌ Failed to submit answer:', error);
        alert('Failed to submit answer');
    } finally {
        submitting.value = false;
    }
};

// Add random answer for testing
const addRandomAnswer = () => {
    const names = ['Ahmed', 'Sara', 'Mohamed', 'Fatima', 'Ali', 'Nour', 'Omar', 'Layla'];
    const randomValue = Math.floor(Math.random() * 5) + 1;
    const randomName = names[Math.floor(Math.random() * names.length)];
    
    const newAnswer = {
        id: Date.now().toString(),
        value: randomValue.toString(),
        userName: randomName
    };
    
    realtimeAnswers.value.push(newAnswer);
    console.log('✅ Random answer added:', newAnswer);
};

// Clear all answers
const clearAnswers = () => {
    realtimeAnswers.value = [];
};

// Format time
const formatTime = (timestamp) => {
    return new Date(timestamp).toLocaleTimeString();
};

// Set up real-time listener (simplified version without Firebase)
const setupRealtimeListener = () => {
    // Simulate real-time answers for demo purposes
    const mockAnswers = [
        { id: '1', value: 4, userName: 'User123', timestamp: Date.now() },
        { id: '2', value: 5, userName: 'User456', timestamp: Date.now() }
    ];
    
    // Simulate receiving new answers every 3 seconds
    setInterval(() => {
        const randomAnswer = mockAnswers[Math.floor(Math.random() * mockAnswers.length)];
        if (randomAnswer) {
            realtimeAnswers.value.push(randomAnswer);
            console.log('📥 Simulated answer received:', randomAnswer);
            
            // Keep only last 50
            if (realtimeAnswers.value.length > 50) {
                realtimeAnswers.value.shift();
            }
        }
    }, 3000);
};

onMounted(() => {
    console.log('🔍 Realtime Questions component mounted');
    console.log('  Question ID:', questionId);
    console.log('✅ Simplified real-time listener setup complete');
    setupRealtimeListener();
});
</script>

<style scoped>
.max-h-64 {
    max-height: 16rem;
}
</style>
