<template>
  <div class="timeline-auth-container">
    <div class="auth-card">
      <div class="auth-header">
        <div class="auth-logo">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"></circle>
            <polyline points="12 6 12 12 16 14"></polyline>
          </svg>
          <h1>Timeline</h1>
        </div>
        <p class="auth-subtitle">Sign in to sync your timeline across devices</p>
      </div>

      <div class="auth-form">
        <!-- Login Form -->
        <form v-if="!isRegistering" @submit.prevent="handleLogin" class="login-form">
          <div class="form-group">
            <label for="email">Email</label>
            <input
              id="email"
              v-model="loginForm.email"
              type="email"
              placeholder="Enter your email"
              :disabled="isLoading"
              required
            />
          </div>

          <div class="form-group">
            <label for="password">Password</label>
            <input
              id="password"
              v-model="loginForm.password"
              type="password"
              placeholder="Enter your password"
              :disabled="isLoading"
              required
            />
          </div>

          <div class="form-options">
            <label class="checkbox-label">
              <input v-model="loginForm.remember" type="checkbox">
              <span>Remember me</span>
            </label>
            <a href="#" class="forgot-link">Forgot password?</a>
          </div>

          <button type="submit" class="auth-btn primary" :disabled="isLoading">
            <svg v-if="isLoading" class="spinner" viewBox="0 0 24 24">
              <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none" stroke-dasharray="31.416" stroke-dashoffset="31.416">
                <animate attributeName="stroke-dashoffset" dur="0.75s" values="31.416;0" repeatCount="indefinite"/>
              </circle>
            </svg>
            {{ isLoading ? 'Signing in...' : 'Sign In' }}
          </button>
        </form>

        <!-- Register Form -->
        <form v-else @submit.prevent="handleRegister" class="register-form">
          <div class="form-group">
            <label for="name">Full Name</label>
            <input
              id="name"
              v-model="registerForm.name"
              type="text"
              placeholder="Enter your full name"
              :disabled="isLoading"
              required
            />
          </div>

          <div class="form-group">
            <label for="reg-email">Email</label>
            <input
              id="reg-email"
              v-model="registerForm.email"
              type="email"
              placeholder="Enter your email"
              :disabled="isLoading"
              required
            />
          </div>

          <div class="form-group">
            <label for="reg-password">Password</label>
            <input
              id="reg-password"
              v-model="registerForm.password"
              type="password"
              placeholder="Create a password"
              :disabled="isLoading"
              required
              minlength="6"
            />
          </div>

          <div class="form-group">
            <label for="password-confirm">Confirm Password</label>
            <input
              id="password-confirm"
              v-model="registerForm.password_confirmation"
              type="password"
              placeholder="Confirm your password"
              :disabled="isLoading"
              required
              minlength="6"
            />
          </div>

          <div class="form-options">
            <label class="checkbox-label">
              <input v-model="registerForm.terms" type="checkbox" required>
              <span>I agree to the Terms of Service</span>
            </label>
          </div>

          <button type="submit" class="auth-btn primary" :disabled="isLoading || !registerForm.terms">
            <svg v-if="isLoading" class="spinner" viewBox="0 0 24 24">
              <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none" stroke-dasharray="31.416" stroke-dashoffset="31.416">
                <animate attributeName="stroke-dashoffset" dur="0.75s" values="31.416;0" repeatCount="indefinite"/>
              </circle>
            </svg>
            {{ isLoading ? 'Creating account...' : 'Create Account' }}
          </button>
        </form>

        <!-- Error Message -->
        <div v-if="error" class="error-message">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="8" x2="12" y2="12"></line>
            <line x1="12" y1="16" x2="12.01" y2="16"></line>
          </svg>
          {{ error }}
        </div>

        <!-- Success Message -->
        <div v-if="success" class="success-message">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
            <polyline points="22 4 12 14.01 9 11.01"></polyline>
          </svg>
          {{ success }}
        </div>

        <!-- Toggle Form -->
        <div class="auth-toggle">
          <span>{{ isRegistering ? 'Already have an account?' : "Don't have an account?" }}</span>
          <button type="button" @click="toggleForm" class="toggle-btn">
            {{ isRegistering ? 'Sign In' : 'Create Account' }}
          </button>
        </div>
      </div>

      <!-- Features -->
      <div class="auth-features">
        <div class="feature">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"></path>
          </svg>
          <span>Automatic sync across devices</span>
        </div>
        <div class="feature">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="11" width="18" height="10" rx="2" ry="2"></rect>
            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
          </svg>
          <span>Secure data storage</span>
        </div>
        <div class="feature">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
            <polyline points="14 2 14 8 20 8"></polyline>
            <line x1="16" y1="13" x2="8" y2="13"></line>
            <line x1="16" y1="17" x2="8" y2="17"></line>
            <polyline points="10 9 9 9 8 9"></polyline>
          </svg>
          <span>Offline access with online backup</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { useTimelineAuth } from '../composables/useTimelineAuth.js';

const { login, register, isLoading, error } = useTimelineAuth();

const isRegistering = ref(false);
const success = ref(null);

const loginForm = reactive({
  email: '',
  password: '',
  remember: false
});

const registerForm = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  terms: false
});

async function handleLogin() {
  success.value = null;
  
  const result = await login({
    email: loginForm.email,
    password: loginForm.password
  });
  
  if (result.success) {
    success.value = 'Login successful! Redirecting...';
    setTimeout(() => {
      window.location.href = '/my-fly-schedule-app/ver5';
    }, 1500);
  }
}

async function handleRegister() {
  if (registerForm.password !== registerForm.password_confirmation) {
    error.value = 'Passwords do not match';
    return;
  }
  
  success.value = null;
  
  const result = await register({
    name: registerForm.name,
    email: registerForm.email,
    password: registerForm.password,
    password_confirmation: registerForm.password_confirmation
  });
  
  if (result.success) {
    success.value = 'Account created! Redirecting...';
    setTimeout(() => {
      window.location.href = '/my-fly-schedule-app/ver5';
    }, 1500);
  }
}

function toggleForm() {
  isRegistering.value = !isRegistering.value;
  error.value = null;
  success.value = null;
  
  // Clear forms
  Object.keys(loginForm).forEach(key => {
    if (typeof loginForm[key] === 'boolean') {
      loginForm[key] = false;
    } else {
      loginForm[key] = '';
    }
  });
  
  Object.keys(registerForm).forEach(key => {
    if (typeof registerForm[key] === 'boolean') {
      registerForm[key] = false;
    } else {
      registerForm[key] = '';
    }
  });
}
</script>

<style scoped>
.timeline-auth-container {
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem;
}

.auth-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  max-width: 480px;
  width: 100%;
}

.auth-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 2rem;
  text-align: center;
}

.auth-logo {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  margin-bottom: 0.5rem;
}

.auth-logo svg {
  width: 2.5rem;
  height: 2.5rem;
  stroke: white;
}

.auth-logo h1 {
  font-size: 2rem;
  font-weight: 700;
  margin: 0;
  color: white;
}

.auth-subtitle {
  font-size: 0.875rem;
  opacity: 0.8;
  margin: 0;
  color: white;
}

.auth-form {
  padding: 2rem;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  font-size: 0.875rem;
  font-weight: 600;
  color: #374151;
  margin-bottom: 0.5rem;
}

.form-group input {
  width: 100%;
  padding: 0.75rem 1rem;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  font-size: 0.875rem;
  transition: all 0.3s ease;
}

.form-group input:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-group input:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.form-options {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  color: #6b7280;
  cursor: pointer;
}

.checkbox-label input[type="checkbox"] {
  width: auto;
  margin: 0;
}

.forgot-link {
  font-size: 0.875rem;
  color: #667eea;
  text-decoration: none;
  transition: color 0.3s ease;
}

.forgot-link:hover {
  color: #5a67d8;
}

.auth-btn {
  width: 100%;
  padding: 0.875rem 1.5rem;
  border: none;
  border-radius: 8px;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.auth-btn.primary {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.auth-btn.primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(102, 126, 234, 0.3);
}

.auth-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none;
}

.spinner {
  width: 1rem;
  height: 1rem;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.error-message {
  background: #fef2f2;
  border: 1px solid #fecaca;
  border-radius: 8px;
  padding: 0.75rem;
  margin-bottom: 1rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #dc2626;
  font-size: 0.875rem;
}

.error-message svg {
  width: 1rem;
  height: 1rem;
  flex-shrink: 0;
}

.success-message {
  background: #f0fdf4;
  border: 1px solid #bbf7d0;
  border-radius: 8px;
  padding: 0.75rem;
  margin-bottom: 1rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #16a34a;
  font-size: 0.875rem;
}

.success-message svg {
  width: 1rem;
  height: 1rem;
  flex-shrink: 0;
}

.auth-toggle {
  text-align: center;
  margin-top: 1.5rem;
  font-size: 0.875rem;
  color: #6b7280;
}

.toggle-btn {
  background: none;
  border: none;
  color: #667eea;
  font-weight: 600;
  cursor: pointer;
  text-decoration: underline;
  margin-left: 0.25rem;
}

.toggle-btn:hover {
  color: #5a67d8;
}

.auth-features {
  background: #f9fafb;
  padding: 1.5rem 2rem;
  border-top: 1px solid #e5e7eb;
}

.feature {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 0.75rem;
  font-size: 0.875rem;
  color: #6b7280;
}

.feature:last-child {
  margin-bottom: 0;
}

.feature svg {
  width: 1.25rem;
  height: 1.25rem;
  stroke: #667eea;
  flex-shrink: 0;
}

@media (max-width: 640px) {
  .timeline-auth-container {
    padding: 1rem;
  }
  
  .auth-header {
    padding: 1.5rem;
  }
  
  .auth-form {
    padding: 1.5rem;
  }
  
  .form-options {
    flex-direction: column;
    gap: 1rem;
    align-items: flex-start;
  }
}
</style>
