<template>
          <q-btn flat round size="sm" color="grey-7" icon="close" @click="hideNewsTicker">
          <q-tooltip>Close</q-tooltip>
        </q-btn>
  <div class="news-ticker-wrapper" :class="[{ 'hidden': !isVisible }, $attrs.class]">
    <div class="news-ticker-container">
      <transition name="news-transition" mode="out-in">
        <div v-if="currentNews" :key="currentNews.id" class="news-item flex">
          <div class="news-title">{{ currentNews.title }}</div>
          <div v-if="currentNews.details" class="news-details m-auto px-2">{{ currentNews.details }}</div>
        </div>
      </transition>

      <!-- Controls -->
      <div class="ticker-controls">
        <q-btn flat round size="sm" color="grey-7" icon="pause_circle" @click="togglePause" v-if="!isPaused">
          <q-tooltip>Pause</q-tooltip>
        </q-btn>
        <q-btn flat round size="sm" color="grey-7" icon="play_circle" @click="togglePause" v-else>
          <q-tooltip>Play</q-tooltip>
        </q-btn>
        <q-btn flat round size="sm" color="grey-7" icon="skip_next" @click="showNextNews">
          <q-tooltip>Next</q-tooltip>
        </q-btn>
        <q-btn flat round size="sm" color="grey-7" icon="close" @click="hideNewsTicker">
          <q-tooltip>Close</q-tooltip>
        </q-btn>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue';
import { useQuasar } from 'quasar';

// Allow class attributes to be passed to the component
defineOptions({
  inheritAttrs: false
});

const $q = useQuasar();

const props = defineProps({
  newsItems: {
    type: Array,
    default: () => [
      { id: 1, title: 'Welcome to the News Ticker', details: 'Add your news items to see them here' },
      { id: 2, title: 'This is a sample news item', details: 'You can customize this component as needed' }
    ]
  },
  displayTime: {
    type: Number,
    default: 5000 // 5 seconds
  },
  autoStart: {
    type: Boolean,
    default: true
  }
});

// State
const currentIndex = ref(0);
const isVisible = ref(true);
const isPaused = ref(!props.autoStart);
let timer = null;

// Computed
const currentNews = computed(() => {
  if (props.newsItems && props.newsItems.length > 0) {
    return props.newsItems[currentIndex.value];
  }
  return null;
});

// Methods
const showNextNews = () => {
  if (props.newsItems && props.newsItems.length > 0) {
    currentIndex.value = (currentIndex.value + 1) % props.newsItems.length;
  }
};

const startNewsRotation = () => {
  clearTimeout(timer);
  if (!isPaused.value) {
    timer = setTimeout(() => {
      showNextNews();
      startNewsRotation();
    }, props.displayTime);
  }
};

const togglePause = () => {
  isPaused.value = !isPaused.value;
  if (!isPaused.value) {
    startNewsRotation();
  }
};

const hideNewsTicker = () => {
  isVisible.value = false;

  // Show notification with option to restore
  $q.notify({
    message: 'News ticker hidden',
    color: 'primary',
    position: 'bottom-right',
    timeout: 5000,
    actions: [
      { label: 'Restore', color: 'white', handler: () => { isVisible.value = true; } }
    ]
  });

  // Save state to localStorage
  localStorage.setItem('newsTickerVisible', isVisible.value.toString());
};

// Lifecycle hooks
onMounted(() => {
  // Check localStorage for visibility state
  const savedVisibility = localStorage.getItem('newsTickerVisible');
  if (savedVisibility !== null) {
    isVisible.value = savedVisibility === 'true';
  }

  // Start the news rotation if autoStart is true
  if (!isPaused.value) {
    startNewsRotation();
  }
});

onBeforeUnmount(() => {
  clearTimeout(timer);
});

// Watch for changes in isPaused
watch(isPaused, (newValue) => {
  if (!newValue) {
    startNewsRotation();
  }
});

// Expose methods for parent components
defineExpose({
  showNextNews,
  togglePause,
  hideNewsTicker,
  restoreNewsTicker: () => { isVisible.value = true; }
});
</script>

<style scoped>
.news-ticker-wrapper {
  width: 100%;
}

.news-ticker-container {
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 0.75rem;
  position: relative;
  overflow: hidden;
  min-height: 60px;
  background-color: rgba(44, 62, 80, 0.9);
}

.news-item {
  text-align: center;
  max-width: 800px;
  padding: 0.5rem 1rem;
  /* background-color: rgba(29, 27, 27, 0.603); */
  border-radius: 8px;
  color: white;
  border-left: 4px solid #f1c40f;
}

.news-title {
  font-size: 1.1rem;
  font-weight: 600;
  margin-bottom: 0.25rem;
}

.news-details {
  font-size: 0.9rem;
  opacity: 0.9;
}

.ticker-controls {
  position: absolute;
  right: 10px;
  top: 10px;
  display: flex;
  gap: 0.25rem;
  /* background-color: rgba(255, 255, 255, 0.2); */
  border-radius: 20px;
  padding: 0.25rem;
}

.hidden {
  display: none;
}

/* Transition animations */
.news-transition-enter-active,
.news-transition-leave-active {
  transition: all 0.5s ease;
}

.news-transition-enter-from {
  opacity: 0;
  transform: translateY(-20px);
}

.news-transition-leave-to {
  opacity: 0;
  transform: translateY(20px);
}
</style>
