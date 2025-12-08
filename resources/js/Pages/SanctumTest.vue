<template>
  <AppLayout title="Sanctum Test">
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
          <h2 class="text-2xl font-bold mb-4">Sanctum Authentication Test</h2>

          <div class="mb-6">
            <h3 class="text-lg font-semibold mb-2">CSRF Cookie Status</h3>
            <div class="flex space-x-4">
              <button @click="checkCsrfCookie" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                Check CSRF Cookie
              </button>
              <button @click="setCsrfCookie" class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">
                Set CSRF Cookie
              </button>
            </div>
            <div v-if="csrfStatus" class="mt-2 p-3 bg-gray-100 rounded">
              <pre>{{ csrfStatus }}</pre>
            </div>
          </div>

          <div class="mb-6">
            <h3 class="text-lg font-semibold mb-2">Session Status</h3>
            <button @click="checkSession" class="px-4 py-2 bg-purple-500 text-white rounded hover:bg-purple-600">
              Check Session
            </button>
            <div v-if="sessionStatus" class="mt-2 p-3 bg-gray-100 rounded">
              <pre>{{ sessionStatus }}</pre>
            </div>
          </div>

          <div class="mb-6">
            <h3 class="text-lg font-semibold mb-2">User Status</h3>
            <button @click="checkUser" class="px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600">
              Check User
            </button>
            <div v-if="userStatus" class="mt-2 p-3 bg-gray-100 rounded">
              <pre>{{ userStatus }}</pre>
            </div>
          </div>

          <div class="mb-6">
            <h3 class="text-lg font-semibold mb-2">Complete Auth Status</h3>
            <button @click="checkAuthStatus" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">
              Check Auth Status
            </button>
            <div v-if="authStatus" class="mt-2 p-3 bg-gray-100 rounded">
              <pre>{{ authStatus }}</pre>
            </div>
          </div>

          <div class="mt-8 p-4 border border-gray-200 rounded">
            <h3 class="text-lg font-semibold mb-2">Troubleshooting Tips</h3>
            <ul class="list-disc pl-5 space-y-1">
              <li>Make sure your domain is in the <code>stateful</code> array in <code>config/sanctum.php</code></li>
              <li>Ensure <code>withCredentials: true</code> is set in your axios configuration</li>
              <li>Check that <code>supports_credentials: true</code> is set in your CORS configuration</li>
              <li>Verify that your session cookie is being set properly</li>
              <li>Make sure your session domain matches your application domain</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';

const csrfStatus = ref(null);
const sessionStatus = ref(null);
const userStatus = ref(null);
const authStatus = ref(null);

const setCsrfCookie = async () => {
  try {
    const response = await axios.get('/api/csrf-cookie', { withCredentials: true });
    csrfStatus.value = response.data;
  } catch (error) {
    csrfStatus.value = { error: error.message, details: error.response?.data };
  }
};

const checkCsrfCookie = async () => {
  try {
    const response = await axios.get('/sanctum-test', { withCredentials: true });
    csrfStatus.value = response.data;
  } catch (error) {
    csrfStatus.value = { error: error.message, details: error.response?.data };
  }
};

const checkSession = async () => {
  try {
    const response = await axios.get('/sanctum-test', { withCredentials: true });
    sessionStatus.value = {
      session_id: response.data.session_id,
      cookies: document.cookie.split(';').map(c => c.trim())
    };
  } catch (error) {
    sessionStatus.value = { error: error.message, details: error.response?.data };
  }
};

const checkUser = async () => {
  try {
    const response = await axios.get('/api/user', { withCredentials: true });
    userStatus.value = response.data;
  } catch (error) {
    userStatus.value = { error: error.message, details: error.response?.data };
  }
};

const checkAuthStatus = async () => {
  try {
    const response = await axios.get('/auth/status', {
      withCredentials: true,
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      }
    });
    authStatus.value = response.data;
  } catch (error) {
    authStatus.value = { error: error.message, details: error.response?.data };
  }
};
</script>
