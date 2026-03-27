import { onBeforeUnmount, onMounted, ref } from 'vue';

export function usePwaInstall() {
  const deferredPrompt = ref(null);
  const canInstall = ref(false);
  const isInstalled = ref(false);

  function handleBeforeInstallPrompt(event) {
    event.preventDefault();
    deferredPrompt.value = event;
    canInstall.value = true;
  }

  function handleAppInstalled() {
    isInstalled.value = true;
    deferredPrompt.value = null;
    canInstall.value = false;
  }

  async function promptInstall() {
    if (!deferredPrompt.value) {
      return { accepted: false };
    }

    deferredPrompt.value.prompt();
    const choiceResult = await deferredPrompt.value.userChoice;

    deferredPrompt.value = null;
    canInstall.value = false;

    return {
      accepted: choiceResult.outcome === 'accepted',
      outcome: choiceResult.outcome,
    };
  }

  onMounted(() => {
    window.addEventListener('beforeinstallprompt', handleBeforeInstallPrompt);
    window.addEventListener('appinstalled', handleAppInstalled);

    if (window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone) {
      isInstalled.value = true;
    }
  });

  onBeforeUnmount(() => {
    window.removeEventListener('beforeinstallprompt', handleBeforeInstallPrompt);
    window.removeEventListener('appinstalled', handleAppInstalled);
  });

  return {
    canInstall,
    isInstalled,
    promptInstall,
  };
}
