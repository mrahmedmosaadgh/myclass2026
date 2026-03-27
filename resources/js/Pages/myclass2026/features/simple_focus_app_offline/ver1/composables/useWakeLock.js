import { ref, onUnmounted } from 'vue';

export function useWakeLock() {
  const wakeLock = ref(null);
  const isSupported = ref('wakeLock' in navigator);
  const isActive = ref(false);
  const error = ref(null);

  async function requestWakeLock() {
    if (!isSupported.value) {
      error.value = 'Wake Lock API not supported on this device';
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
      });

      return true;
    } catch (err) {
      error.value = err.message || 'Failed to request wake lock';
      isActive.value = false;
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

  return {
    isSupported,
    isActive,
    error,
    requestWakeLock,
    releaseWakeLock,
  };
}
