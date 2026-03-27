import { ref, onUnmounted } from 'vue';

export function useWakeLock() {
  const wakeLock = ref(null);
  const isSupported = ref('wakeLock' in navigator);
  const isActive = ref(false);
  const error = ref(null);
  const isFirstTab = ref(true);

  // Check if this is the first tab using localStorage
  function checkFirstTab() {
    const storageKey = 'focus-app-wake-lock-tab';
    const existingTab = localStorage.getItem(storageKey);
    
    if (!existingTab) {
      // This is the first tab
      const tabId = Date.now().toString();
      localStorage.setItem(storageKey, tabId);
      isFirstTab.value = true;
      
      // Clean up when tab closes
      window.addEventListener('beforeunload', () => {
        localStorage.removeItem(storageKey);
      });
      
      return true;
    } else {
      // Another tab already has wake lock
      isFirstTab.value = false;
      return false;
    }
  }

  async function requestWakeLock() {
    if (!isSupported.value) {
      error.value = 'Wake Lock API not supported on this device';
      return false;
    }

    // Only allow wake lock in first tab
    if (!checkFirstTab()) {
      error.value = 'Wake lock only available in first browser tab';
      return false;
    }

    try {
      // Release any existing wake lock
      if (wakeLock.value) {
        await releaseWakeLock();
      }

      // Request a new wake lock
      wakeLock.value = await navigator.wakeLock.request('screen');
      isActive.value = true;
      error.value = null;

      // Listen for wake lock release
      wakeLock.value.addEventListener('release', () => {
        isActive.value = false;
        wakeLock.value = null;
        localStorage.removeItem('focus-app-wake-lock-tab');
      });

      return true;
    } catch (err) {
      error.value = err.message || 'Failed to request wake lock';
      isActive.value = false;
      localStorage.removeItem('focus-app-wake-lock-tab');
      return false;
    }
  }

  async function releaseWakeLock() {
    if (wakeLock.value) {
      try {
        await wakeLock.value.release();
        wakeLock.value = null;
        isActive.value = false;
        error.value = null;
        localStorage.removeItem('focus-app-wake-lock-tab');
        return true;
      } catch (err) {
        error.value = err.message || 'Failed to release wake lock';
        return false;
      }
    }
    return true;
  }

  // Auto-release wake lock when component unmounts
  onUnmounted(() => {
    releaseWakeLock();
  });

  // Listen for visibility changes (user switches tabs/apps)
  function handleVisibilityChange() {
    if (document.visibilityState === 'visible' && isActive.value && !wakeLock.value) {
      // Wake lock was released by the system, try to reacquire it
      requestWakeLock();
    }
  }

  // Set up visibility change listener
  if (typeof document !== 'undefined') {
    document.addEventListener('visibilitychange', handleVisibilityChange);
  }

  // Check if we're the first tab on initialization
  checkFirstTab();

  return {
    isSupported,
    isActive,
    error,
    isFirstTab,
    requestWakeLock,
    releaseWakeLock,
  };
}
