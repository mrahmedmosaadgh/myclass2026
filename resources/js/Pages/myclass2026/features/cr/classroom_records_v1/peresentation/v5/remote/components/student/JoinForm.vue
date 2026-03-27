<script setup>
import { ref, onMounted, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useGameStore } from '../../../stores/gameStore';
import axios from 'axios';

const emit = defineEmits(['joined']);
const gameStore = useGameStore();
const page = usePage();

const loggedInUser = computed(() => page.props.auth?.user);

const code = ref('');
const name = ref(loggedInUser.value?.name || '');
const isError = ref(false);
const isLoading = ref(false);

const submitJoin = async () => {
  if (!code.value || !name.value) return;
  
  isLoading.value = true;
  isError.value = false;
  
  try {
    const response = await axios.post('/api/cr/sessions/join', {
      access_code: code.value,
      name: name.value
    });
    
    const data = response.data;
    if (data.session?.id || data.success) {
      gameStore.setSession(
        data.session.id,
        data.session.access_code,
        data.session.status
      );
      if (!loggedInUser.value) {
        localStorage.setItem('quiz_nickname', name.value);
      }
      emit('joined', { name: name.value });
    } else {
      isError.value = true;
    }
  } catch (err) {
    console.error('Join failed:', err);
    isError.value = true;
  } finally {
    isLoading.value = false;
  }
};

const resetForm = () => {
  isError.value = false;
  code.value = '';
};

onMounted(() => {
  // Auto-fill code from URL if present
  const urlParams = new URLSearchParams(window.location.search);
  if (urlParams.has('code')) {
    code.value = urlParams.get('code');
    // If we already have a name (logged in), validate immediately
    if (name.value) {
      submitJoin();
    }
  }
});
</script>

<template>
  <div class="join-card shadow-xl">
    <div class="join-header">
      <div class="v5-logo">V5</div>
      <h1>Join Classroom</h1>
      <p v-if="loggedInUser">Welcome back, <strong>{{ loggedInUser.name }}</strong>!</p>
      <p v-else>Enter the code from the board to start</p>
    </div>

    <!-- Error State View -->
    <div v-if="isError" class="error-view">
      <div class="error-icon">❌</div>
      <h3>Invalid Session Code</h3>
      <p>The code <strong>{{ code }}</strong> doesn't exist or the session has ended.</p>
      
      <div class="error-actions">
        <button @click="submitJoin" class="retry-btn" :disabled="isLoading">
          {{ isLoading ? 'Checking...' : '🔄 Try Again' }}
        </button>
        <button @click="resetForm" class="change-btn" :disabled="isLoading">
           Change Code
        </button>
      </div>
    </div>

    <!-- Normal Form View -->
    <form v-else @submit.prevent="submitJoin" class="join-form">
      <div class="input-group">
        <label>Session Code</label>
        <input 
          v-model="code" 
          type="text" 
          placeholder="e.g. AB12CD" 
          maxlength="6"
          required
        >
      </div>

      <!-- Hide name input if already logged in -->
      <div v-if="!loggedInUser" class="input-group">
        <label>Your Full Name</label>
        <input 
          v-model="name" 
          type="text" 
          placeholder="Enter your name..." 
          required
        >
      </div>

      <button type="submit" class="join-btn" :disabled="isLoading">
        {{ isLoading ? 'Checking...' : '🚀 Start Interaction' }}
      </button>
    </form>
  </div>
</template>

<style scoped>
.join-card {
  background: white;
  width: 100%;
  max-width: 400px;
  border-radius: 1.5rem;
  padding: 2.5rem;
  text-align: center;
}

.join-header h1 {
  font-size: 1.75rem;
  font-weight: 800;
  color: #1e293b;
  margin-bottom: 0.5rem;
}

.join-header p {
  color: #64748b;
  margin-bottom: 2rem;
}

.v5-logo {
  background: #6366f1;
  color: white;
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 900;
  margin: 0 auto 1.5rem;
  font-size: 1.2rem;
}

.join-form {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.input-group {
  display: flex;
  flex-direction: column;
  text-align: left;
  gap: 0.4rem;
}

.input-group label {
  font-size: 0.85rem;
  font-weight: 700;
  color: #475569;
}

.input-group input {
  padding: 0.85rem 1rem;
  border: 1px solid #e2e8f0;
  border-radius: 0.75rem;
  font-size: 1rem;
  transition: all 0.2s;
}

.input-group input:focus {
  outline: none;
  border-color: #6366f1;
  box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
}

.input-group.error input {
  border-color: #ef4444;
  background: #fef2f2;
}

.error-msg {
  color: #ef4444;
  font-size: 0.85rem;
  font-weight: 600;
  margin: 0;
}

.join-btn {
  margin-top: 0.5rem;
  padding: 1rem;
  background: #6366f1;
  color: white;
  border: none;
  border-radius: 0.75rem;
  font-size: 1.1rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
}

.join-btn:hover:not(:disabled) {
  background: #4f46e5;
  transform: translateY(-2px);
  box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
}

.join-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.shadow-xl {
  box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
}

/* Error State View Styles */
.error-view {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
  padding: 1rem 0;
}

.error-icon {
  font-size: 3rem;
  margin-bottom: 0.5rem;
}

.error-view h3 {
  font-size: 1.25rem;
  font-weight: 800;
  color: #1e293b;
  margin: 0;
}

.error-view p {
  color: #64748b;
  font-size: 0.95rem;
  margin: 0 0 1rem;
}

.error-actions {
  display: flex;
  gap: 1rem;
  width: 100%;
}

.retry-btn, .change-btn {
  flex: 1;
  padding: 0.85rem;
  border-radius: 0.75rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
  font-size: 0.95rem;
}

.retry-btn {
  background: #6366f1;
  color: white;
  border: none;
}
.retry-btn:hover:not(:disabled) {
  background: #4f46e5;
  transform: translateY(-2px);
}

.change-btn {
  background: white;
  color: #64748b;
  border: 1px solid #e2e8f0;
}
.change-btn:hover:not(:disabled) {
  border-color: #cbd5e1;
  color: #1e293b;
  background: #f8fafc;
}
</style>
