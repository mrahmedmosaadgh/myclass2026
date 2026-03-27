import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { usePwaInstall } from './usePwaInstall';

const MANIFEST_HREF = '/simple-focus-app-offline/v1/manifest.webmanifest';

export function useStandaloneApp() {
  const serviceWorkerStatus = ref('idle');
  const { canInstall, isInstalled, promptInstall } = usePwaInstall();

  const manifestHref = computed(() => MANIFEST_HREF);

  async function registerServiceWorker() {
    if (!('serviceWorker' in navigator)) {
      serviceWorkerStatus.value = 'unsupported';
      return;
    }

    try {
      serviceWorkerStatus.value = 'registering';
      const registration = await navigator.serviceWorker.register('/sw.js');
      serviceWorkerStatus.value = registration.active ? 'ready' : 'registered';
    } catch (error) {
      console.error('Failed to register service worker for focus app:', error);
      serviceWorkerStatus.value = 'error';
    }
  }

  onMounted(() => {
    registerServiceWorker();
  });

  onBeforeUnmount(() => {
    // keep the registration alive; nothing to clean up here yet
  });

  const installLabel = computed(() => (isInstalled.value ? 'INSTALLED' : 'INSTALL'));

  return {
    manifestHref,
    serviceWorkerStatus,
    canInstall,
    isInstalled,
    installLabel,
    promptInstall,
    registerServiceWorker,
  };
}
