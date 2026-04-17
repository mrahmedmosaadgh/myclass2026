import { ref, computed, watch } from 'vue';
import axios from 'axios';

// Timeline Authentication Service
export function useTimelineAuth() {
  const user = ref(null);
  const timelineUser = ref(null);
  const token = ref(localStorage.getItem('timeline_token') || null);
  const isLoading = ref(false);
  const error = ref(null);

  // Create axios instance with auth header
  const api = axios.create({
    baseURL: '/timeline/auth',
    headers: {
      'Content-Type': 'application/json',
      'X-Device-ID': getDeviceId()
    }
  });

  // Add auth interceptor
  api.interceptors.request.use((config) => {
    if (token.value) {
      config.headers.Authorization = `Bearer ${token.value}`;
    }
    return config;
  });

  // Response interceptor for token refresh
  api.interceptors.response.use(
    (response) => response,
    async (error) => {
      if (error.response?.status === 401) {
        await logout();
        window.location.href = '/timeline/login';
      }
      return Promise.reject(error);
    }
  );

  // Get or generate device ID
  function getDeviceId() {
    let deviceId = localStorage.getItem('timeline_device_id');
    if (!deviceId) {
      deviceId = 'device_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
      localStorage.setItem('timeline_device_id', deviceId);
    }
    return deviceId;
  }

  // Login
  async function login(credentials) {
    try {
      isLoading.value = true;
      error.value = null;

      const response = await api.post('/login', credentials);
      
      if (response.data.success) {
        const { access_token, user: userData, timeline_user: timelineUserData } = response.data;
        
        token.value = access_token;
        user.value = userData;
        timelineUser.value = timelineUserData;
        
        localStorage.setItem('timeline_token', access_token);
        localStorage.setItem('timeline_user', JSON.stringify(userData));
        localStorage.setItem('timeline_user_profile', JSON.stringify(timelineUserData));
        
        // Register device
        await registerDevice();
        
        return { success: true, data: response.data };
      } else {
        error.value = response.data.message || 'Login failed';
        return { success: false, message: error.value };
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Login failed';
      return { success: false, message: error.value };
    } finally {
      isLoading.value = false;
    }
  }

  // Register
  async function register(userData) {
    try {
      isLoading.value = true;
      error.value = null;

      const response = await api.post('/register', userData);
      
      if (response.data.success) {
        const { access_token, user: newUser, timeline_user: newTimelineUser } = response.data;
        
        token.value = access_token;
        user.value = newUser;
        timelineUser.value = newTimelineUser;
        
        localStorage.setItem('timeline_token', access_token);
        localStorage.setItem('timeline_user', JSON.stringify(newUser));
        localStorage.setItem('timeline_user_profile', JSON.stringify(newTimelineUser));
        
        // Register device
        await registerDevice();
        
        return { success: true, data: response.data };
      } else {
        error.value = response.data.message || 'Registration failed';
        return { success: false, message: error.value };
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Registration failed';
      return { success: false, message: error.value };
    } finally {
      isLoading.value = false;
    }
  }

  // Logout
  async function logout() {
    try {
      if (token.value) {
        await api.post('/logout');
      }
    } catch (err) {
      console.error('Logout error:', err);
    } finally {
      token.value = null;
      user.value = null;
      timelineUser.value = null;
      
      localStorage.removeItem('timeline_token');
      localStorage.removeItem('timeline_user');
      localStorage.removeItem('timeline_user_profile');
      localStorage.removeItem('timeline_data');
    }
  }

  // Get current user
  async function getCurrentUser() {
    try {
      isLoading.value = true;
      
      const response = await api.get('/me');
      
      if (response.data.success) {
        user.value = response.data.user;
        timelineUser.value = response.data.timeline_user;
        
        localStorage.setItem('timeline_user', JSON.stringify(response.data.user));
        localStorage.setItem('timeline_user_profile', JSON.stringify(response.data.timeline_user));
        
        return { success: true, data: response.data };
      } else {
        await logout();
        return { success: false, message: 'Session expired' };
      }
    } catch (err) {
      await logout();
      return { success: false, message: 'Session expired' };
    } finally {
      isLoading.value = false;
    }
  }

  // Register device
  async function registerDevice() {
    try {
      const deviceInfo = {
        device_id: getDeviceId(),
        device_name: getDeviceName(),
        device_type: getDeviceType()
      };

      await api.post('/register-device', deviceInfo);
    } catch (err) {
      console.error('Device registration error:', err);
    }
  }

  // Get device name
  function getDeviceName() {
    const userAgent = navigator.userAgent;
    if (userAgent.includes('Windows')) return 'Windows Desktop';
    if (userAgent.includes('Mac')) return 'Mac Desktop';
    if (userAgent.includes('Linux')) return 'Linux Desktop';
    if (userAgent.includes('iPhone')) return 'iPhone';
    if (userAgent.includes('iPad')) return 'iPad';
    if (userAgent.includes('Android')) return 'Android Device';
    return 'Unknown Device';
  }

  // Get device type
  function getDeviceType() {
    const userAgent = navigator.userAgent;
    if (userAgent.includes('iPhone') || userAgent.includes('Android')) return 'mobile';
    if (userAgent.includes('iPad') || userAgent.includes('Tablet')) return 'tablet';
    return 'desktop';
  }

  // Check if authenticated
  const isAuthenticated = computed(() => !!token.value && !!user.value);

  // Get user preferences
  const preferences = computed(() => timelineUser.value?.preferences || {});

  // Get user display name
  const displayName = computed(() => timelineUser.value?.display_name || user.value?.name || 'User');

  // Initialize auth on load
  async function initializeAuth() {
    if (token.value) {
      const savedUser = localStorage.getItem('timeline_user');
      const savedProfile = localStorage.getItem('timeline_user_profile');
      
      if (savedUser && savedProfile) {
        try {
          user.value = JSON.parse(savedUser);
          timelineUser.value = JSON.parse(savedProfile);
          
          // Verify token is still valid
          await getCurrentUser();
        } catch (err) {
          await logout();
        }
      }
    }
  }

  // Auto-initialize
  initializeAuth();

  return {
    // State
    user,
    timelineUser,
    token,
    isLoading,
    error,
    isAuthenticated,
    preferences,
    displayName,
    
    // Methods
    login,
    register,
    logout,
    getCurrentUser,
    registerDevice,
    
    // API instance
    api
  };
}
