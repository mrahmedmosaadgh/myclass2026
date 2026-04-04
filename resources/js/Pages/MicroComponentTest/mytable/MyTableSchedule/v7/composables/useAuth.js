import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';

export function useAuth() {
  // State
  const user = ref(null);
  const loading = ref(false);
  const error = ref('');

  // Computed
  const isAuthenticated = computed(() => !!user.value);

  // Methods
  const checkAuth = async () => {
    loading.value = true;
    error.value = '';

    try {
      // Check current auth status via API
      const response = await fetch('/api/schedule-app-v7/health', {
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        }
      });

      if (response.ok) {
        const data = await response.json();
        if (data.authenticated && data.user) {
          user.value = data.user;
          return true;
        }
      }

      // If we get here, user is not authenticated
      user.value = null;
      return false;

    } catch (err) {
      console.error('Auth check failed:', err);
      error.value = 'Failed to check authentication status';
      user.value = null;
      return false;
    } finally {
      loading.value = false;
    }
  };

  const logout = async () => {
    try {
      await router.post('/logout');
      user.value = null;
    } catch (err) {
      console.error('Logout failed:', err);
      error.value = 'Failed to logout';
    }
  };

  const refreshUser = async () => {
    await checkAuth();
  };

  // Initialize on first use
  const init = async () => {
    if (!user.value) {
      await checkAuth();
    }
  };

  return {
    // State
    user,
    loading,
    error,
    
    // Computed
    isAuthenticated,
    
    // Methods
    checkAuth,
    logout,
    refreshUser,
    init
  };
}
