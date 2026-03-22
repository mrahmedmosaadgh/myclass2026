<template>
  <div class="video-player-tabs-container">
    <div class="bg-white rounded-lg shadow">
      <!-- Tab Navigation -->
      <div class="border-b border-gray-200">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
          <button
            v-for="(tab, index) in tabs"
            :key="index"
            @click="activeTab = index"
            :class="[
              'py-2 px-1 border-b-2 font-medium text-sm',
              activeTab === index
                ? 'border-blue-500 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            <div class="flex items-center space-x-2">
              <span>{{ tab.title }}</span>
              <span v-if="tab.isPlaying" class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></span>
            </div>
          </button>
        </nav>
      </div>

      <!-- Tab Content -->
      <div class="p-6">
        <div v-for="(tab, index) in tabs" :key="index">
          <div v-show="activeTab === index" class="animate-fade-in">
            <VideoPlayer
              :src="tab.src"
              :title="tab.title"
              :description="tab.description"
              :showControls="tab.showControls"
              :autoplay="tab.autoplay"
              :loop="tab.loop"
              :muted="tab.muted"
              :audioChannel="tab.audioChannel"
              :repeatMode="tab.repeatMode"
              :repeatCount="tab.repeatCount"
              :repeatTime="tab.repeatTime"
              @update:isPlaying="(playing) => tab.isPlaying = playing"
            />
          </div>
        </div>
      </div>

      <!-- Global Controls -->
      <div class="border-t border-gray-200 p-4 bg-gray-50">
        <div class="flex items-center justify-between">
          <div class="text-sm text-gray-600">
            <span class="font-medium">Active Tab:</span> {{ tabs[activeTab].title }}
          </div>
          <div class="flex space-x-2">
            <button
              @click="stopAllTabs"
              class="px-3 py-1 bg-red-600 text-white text-sm rounded hover:bg-red-700 transition-colors"
            >
              Stop All
            </button>
            <button
              @click="syncSettings"
              class="px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 transition-colors"
            >
              Sync Settings
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import VideoPlayer from './VideoPlayer.vue';

const activeTab = ref(0);

// Tab configurations with different settings
const tabs = ref([
  {
    title: 'Tab 1 - Video',
    src: 'https://www.w3schools.com/html/mov_bbb.mp4',
    description: 'Big Buck Bunny - Short animated film for testing',
    showControls: true,
    autoplay: false,
    loop: false,
    muted: false,
    audioChannel: 'stereo',
    repeatMode: 'none',
    repeatCount: 3,
    repeatTime: 5,
    disableExternalControls: true,
    isPlaying: false
  },
  {
    title: 'Tab 2 - Audio',
    src: 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-1.mp3',
    description: 'Sample audio file for testing audio channel controls',
    showControls: true,
    autoplay: false,
    loop: false,
    muted: false,
    audioChannel: 'stereo',
    repeatMode: 'count',
    repeatCount: 2,
    repeatTime: 3,
    disableExternalControls: true,
    isPlaying: false
  },
  {
    title: 'Tab 3 - Custom',
    src: 'https://www.w3schools.com/html/mov_bbb.mp4',
    description: 'Custom settings tab - configure your own repeat and audio settings',
    showControls: true,
    autoplay: false,
    loop: false,
    muted: false,
    audioChannel: 'left',
    repeatMode: 'time',
    repeatCount: 1,
    repeatTime: 2,
    disableExternalControls: true,
    isPlaying: false
  }
]);

const stopAllTabs = () => {
  // This would require communication with child components
  // For now, we'll just switch to tab 1
  activeTab.value = 0;
};

const syncSettings = () => {
  // Sync all tabs with the settings from the active tab
  const activeTabSettings = { ...tabs.value[activeTab] };
  
  tabs.value.forEach((tab, index) => {
    if (index !== activeTab) {
      tab.audioChannel = activeTabSettings.audioChannel;
      tab.repeatMode = activeTabSettings.repeatMode;
      tab.repeatCount = activeTabSettings.repeatCount;
      tab.repeatTime = activeTabSettings.repeatTime;
    }
  });
  
  console.log('Settings synced across all tabs');
};
</script>

<style scoped>
.video-player-tabs-container {
  max-width: 1200px;
  margin: 0 auto;
}

.animate-fade-in {
  animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
