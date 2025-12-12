<template>
  <q-btn
    :icon="isPlaying ? 'stop' : 'volume_up'"
    :color="isPlaying ? 'negative' : 'primary'"
    :size="size"
    :flat="flat"
    :round="round"
    :dense="dense"
    :disable="!canSpeak || isLoading"
    :loading="isLoading"
    @click="toggleSpeech"
    class="text-to-speech-btn"
  >
    <q-tooltip :delay="500">
      {{ isPlaying ? 'Stop reading' : 'Read aloud' }}
    </q-tooltip>
  </q-btn>
</template>

<script setup>
import { ref, computed, onUnmounted } from 'vue';
import { useQuasar } from 'quasar';

// Props
const props = defineProps({
  text: {
    type: String,
    required: true
  },
  size: {
    type: String,
    default: 'sm'
  },
  flat: {
    type: Boolean,
    default: true
  },
  round: {
    type: Boolean,
    default: false
  },
  dense: {
    type: Boolean,
    default: true
  },
  autoDetectLanguage: {
    type: Boolean,
    default: true
  },
  voice: {
    type: String,
    default: null // Will use default voice
  },
  rate: {
    type: Number,
    default: 1.0,
    validator: (value) => value >= 0.1 && value <= 10
  },
  pitch: {
    type: Number,
    default: 1.0,
    validator: (value) => value >= 0 && value <= 2
  }
});

// Composables
const $q = useQuasar();

// State
const isPlaying = ref(false);
const isLoading = ref(false);
const currentUtterance = ref(null);

// Computed
const canSpeak = computed(() => {
  return 'speechSynthesis' in window && props.text && props.text.trim().length > 0;
});

const isEnglish = computed(() => {
  if (!props.autoDetectLanguage) return true;
  
  // Simple English detection - check for common English words and patterns
  const text = props.text.toLowerCase();
  const englishWords = ['the', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by', 'is', 'are', 'was', 'were', 'have', 'has', 'had', 'will', 'would', 'could', 'should'];
  const englishPattern = /^[a-zA-Z0-9\s.,!?;:'"()\-]+$/;
  
  // Check if text contains English words and uses Latin characters
  const hasEnglishWords = englishWords.some(word => text.includes(` ${word} `) || text.startsWith(`${word} `) || text.endsWith(` ${word}`));
  const isLatinScript = englishPattern.test(text);
  
  return hasEnglishWords && isLatinScript;
});

// Methods
const toggleSpeech = () => {
  if (!canSpeak.value) {
    $q.notify({
      type: 'negative',
      message: 'Text-to-speech is not supported in this browser',
      position: 'top'
    });
    return;
  }

  // if (!isEnglish.value) {
  //   $q.notify({
  //     type: 'info',
  //     message: 'Text-to-speech is only available for English text',
  //     position: 'top'
  //   });
  //   return;
  // }

  if (isPlaying.value) {
    stopSpeech();
  } else {
    startSpeech();
  }
};

const startSpeech = () => {
  if (!props.text || !props.text.trim()) return;

  isLoading.value = true;

  try {
    // Stop any existing speech
    window.speechSynthesis.cancel();

    // Create new utterance
    const utterance = new SpeechSynthesisUtterance(props.text.trim());
    
    // Set voice properties
    utterance.rate = props.rate;
    utterance.pitch = props.pitch;
    utterance.lang = 'en-US';

    // Set voice if specified
    if (props.voice) {
      const voices = window.speechSynthesis.getVoices();
      const selectedVoice = voices.find(voice => voice.name === props.voice);
      if (selectedVoice) {
        utterance.voice = selectedVoice;
      }
    }

    // Event handlers
    utterance.onstart = () => {
      isPlaying.value = true;
      isLoading.value = false;
    };

    utterance.onend = () => {
      isPlaying.value = false;
      isLoading.value = false;
      currentUtterance.value = null;
    };

    utterance.onerror = (event) => {
      console.error('Speech synthesis error:', event);
      isPlaying.value = false;
      isLoading.value = false;
      currentUtterance.value = null;
      
      $q.notify({
        type: 'negative',
        message: 'Failed to read text aloud',
        position: 'top'
      });
    };

    utterance.onpause = () => {
      isPlaying.value = false;
    };

    utterance.onresume = () => {
      isPlaying.value = true;
    };

    // Store reference and start speaking
    currentUtterance.value = utterance;
    window.speechSynthesis.speak(utterance);

  } catch (error) {
    console.error('Error starting speech:', error);
    isLoading.value = false;
    $q.notify({
      type: 'negative',
      message: 'Failed to start text-to-speech',
      position: 'top'
    });
  }
};

const stopSpeech = () => {
  try {
    window.speechSynthesis.cancel();
    isPlaying.value = false;
    isLoading.value = false;
    currentUtterance.value = null;
  } catch (error) {
    console.error('Error stopping speech:', error);
  }
};

// Cleanup on unmount
onUnmounted(() => {
  stopSpeech();
});

// Watch for speech synthesis voices loaded
if ('speechSynthesis' in window) {
  window.speechSynthesis.onvoiceschanged = () => {
    // Voices are now loaded
  };
}
</script>

<style scoped>
.text-to-speech-btn {
  transition: all 0.2s ease;
}

.text-to-speech-btn:hover {
  transform: scale(1.05);
}
</style>
