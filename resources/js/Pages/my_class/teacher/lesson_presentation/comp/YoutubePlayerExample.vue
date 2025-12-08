<template>
  <div class="youtube-player-example">
    <h2>YouTube Player Example</h2>
    
    <div class="input-section">
      <label for="youtube-url">Enter YouTube URL:</label>
      <div class="url-input-container">
        <input 
          id="youtube-url" 
          v-model="youtubeUrl" 
          type="text" 
          placeholder="https://www.youtube.com/watch?v=..."
          @keyup.enter="loadVideo"
        />
        <button @click="loadVideo" class="load-button">Load Video</button>
      </div>
      <div class="url-examples">
        <p>Examples:</p>
        <ul>
          <li><a href="#" @click.prevent="setExampleUrl('https://www.youtube.com/watch?v=dQw4w9WgXcQ')">Standard YouTube URL</a></li>
          <li><a href="#" @click.prevent="setExampleUrl('https://youtu.be/dQw4w9WgXcQ')">Short YouTube URL</a></li>
          <li><a href="#" @click.prevent="setExampleUrl('https://www.youtube.com/embed/dQw4w9WgXcQ')">Embed URL</a></li>
        </ul>
      </div>
    </div>
    
    <div class="player-container">
      <div v-if="!currentVideoUrl" class="empty-state">
        <div class="empty-icon">▶</div>
        <p>Enter a YouTube URL above to load a video</p>
      </div>
      <YoutubeVideoPlayer 
        v-else
        :video-url="currentVideoUrl"
        :autoplay="playerOptions.autoplay"
        :muted="playerOptions.muted"
        :controls="playerOptions.controls"
        :loop="playerOptions.loop"
        :start-at="playerOptions.startAt"
      />
    </div>
    
    <div class="player-options">
      <h3>Player Options</h3>
      <div class="options-grid">
        <label class="option-checkbox">
          <input type="checkbox" v-model="playerOptions.autoplay" />
          Autoplay
        </label>
        <label class="option-checkbox">
          <input type="checkbox" v-model="playerOptions.muted" />
          Muted
        </label>
        <label class="option-checkbox">
          <input type="checkbox" v-model="playerOptions.controls" />
          Controls
        </label>
        <label class="option-checkbox">
          <input type="checkbox" v-model="playerOptions.loop" />
          Loop
        </label>
        <div class="option-number">
          <label for="start-at">Start at (seconds):</label>
          <input 
            id="start-at" 
            type="number" 
            v-model.number="playerOptions.startAt" 
            min="0" 
            step="1"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import YoutubeVideoPlayer from './youtube_VideoPlayer.vue';

// YouTube URL input
const youtubeUrl = ref('');
const currentVideoUrl = ref('');

// Player options
const playerOptions = ref({
  autoplay: false,
  muted: false,
  controls: true,
  loop: false,
  startAt: 0
});

// Load the video with the current URL
const loadVideo = () => {
  if (youtubeUrl.value.trim()) {
    currentVideoUrl.value = youtubeUrl.value.trim();
  }
};

// Set an example URL
const setExampleUrl = (url) => {
  youtubeUrl.value = url;
  loadVideo();
};
</script>

<style scoped>
.youtube-player-example {
  max-width: 800px;
  margin: 0 auto;
  padding: 20px;
  font-family: Arial, sans-serif;
}

h2 {
  margin-bottom: 20px;
  color: #333;
}

.input-section {
  margin-bottom: 20px;
}

label {
  display: block;
  margin-bottom: 8px;
  font-weight: bold;
  color: #555;
}

.url-input-container {
  display: flex;
  gap: 10px;
}

input[type="text"] {
  flex: 1;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 16px;
}

.load-button {
  padding: 10px 16px;
  background-color: #ff0000;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-weight: bold;
  transition: background-color 0.2s;
}

.load-button:hover {
  background-color: #cc0000;
}

.url-examples {
  margin-top: 10px;
  font-size: 14px;
  color: #666;
}

.url-examples p {
  margin-bottom: 5px;
}

.url-examples ul {
  margin: 0;
  padding-left: 20px;
}

.url-examples a {
  color: #0066cc;
  text-decoration: none;
}

.url-examples a:hover {
  text-decoration: underline;
}

.player-container {
  margin-bottom: 20px;
  border: 1px solid #ddd;
  border-radius: 8px;
  overflow: hidden;
  background-color: #f5f5f5;
}

.empty-state {
  height: 450px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: #666;
  background-color: #f5f5f5;
}

.empty-icon {
  font-size: 48px;
  margin-bottom: 10px;
  width: 80px;
  height: 80px;
  background-color: rgba(0, 0, 0, 0.1);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.player-options {
  background-color: #f9f9f9;
  padding: 15px;
  border-radius: 8px;
  border: 1px solid #eee;
}

.player-options h3 {
  margin-top: 0;
  margin-bottom: 15px;
  color: #333;
  font-size: 18px;
}

.options-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
  gap: 15px;
}

.option-checkbox {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
}

.option-number {
  display: flex;
  flex-direction: column;
  gap: 5px;
}

.option-number input {
  width: 80px;
  padding: 5px;
  border: 1px solid #ddd;
  border-radius: 4px;
}
</style>
