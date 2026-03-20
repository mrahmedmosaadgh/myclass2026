<template>
  <div class="demo">
    <h2>AudioPlayer Demo</h2>

    <!-- ════ EXAMPLE 1: Inline player, full controls ════ -->
    <section>
      <h3>Inline Player (Sequential → Loop)</h3>
      <AudioPlayer
        :files="playlist"
        :play="controlA"
        :loop="true"
        :autoNext="true"
        :visualizer="true"
        :volume="0.8"
        :inline="true"
        :visible="true"
        order="sequential"
        accentColor="#7c6aff"
        @update:play="controlA = $event"
        @trackChange="onTrackChange"
        @ended="onEnded"
      />
      <button @click="controlA = controlA === 1 ? 0 : 1">
        {{ controlA === 1 ? 'Pause' : 'Play' }} from Parent
      </button>
    </section>

    <!-- ════ EXAMPLE 2: Floating player, random order ════ -->
    <section>
      <h3>Floating Player (Random Order)</h3>
      <AudioPlayer
        :files="playlist"
        :play="controlB"
        :visible="showFloating"
        :inline="false"
        order="random"
        :autoNext="true"
        accentColor="#ff6a7c"
        @update:play="controlB = $event"
      />
      <button @click="showFloating = !showFloating">
        {{ showFloating ? 'Hide' : 'Show' }} Floating Player
      </button>
      <button @click="controlB = controlB === 1 ? 0 : 1">
        {{ controlB === 1 ? 'Pause' : 'Play' }}
      </button>
    </section>

    <!-- ════ EXAMPLE 3: Single track, no playlist ════ -->
    <section>
      <h3>Single Track Player</h3>
      <AudioPlayer
        :files="[playlist[0]]"
        :play="controlC"
        :loop="true"
        :visualizer="false"
        accentColor="#22d3a5"
        @update:play="controlC = $event"
      />
    </section>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import AudioPlayer from './AudioPlayer.vue'

/**
 * FILES ARE LOADED ONCE by the module-level audioRegistry inside AudioPlayerInner.
 * Every AudioPlayer instance that references the same `src` reuses the same
 * HTMLAudioElement — no re-downloading.
 */
const playlist = [
  {
    id: 'track-1',
    src: 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-1.mp3',
    title: 'Neon Drift',
    artist: 'SoundHelix',
    cover: 'https://picsum.photos/seed/neon/300/300',
  },
  {
    id: 'track-2',
    src: 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-2.mp3',
    title: 'Orbital Haze',
    artist: 'SoundHelix',
    cover: 'https://picsum.photos/seed/orbit/300/300',
  },
  {
    id: 'track-3',
    src: 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-3.mp3',
    title: 'Glass Circuit',
    artist: 'SoundHelix',
    cover: 'https://picsum.photos/seed/glass/300/300',
  },
]

const controlA    = ref(0)
const controlB    = ref(0)
const controlC    = ref(0)
const showFloating = ref(true)

function onTrackChange(index) {
  console.log('Track changed to index:', index)
}
function onEnded(index) {
  console.log('Track ended at index:', index)
}
</script>

<style scoped>
.demo { max-width: 540px; margin: 40px auto; display: flex; flex-direction: column; gap: 40px; font-family: sans-serif; }
section { display: flex; flex-direction: column; gap: 12px; }
h3 { margin: 0; font-size: .85rem; text-transform: uppercase; letter-spacing: .06em; color: #888; }
button {
  align-self: flex-start; padding: 8px 16px; border-radius: 8px;
  background: #18181b; color: #fff; border: 1px solid #333;
  cursor: pointer; font-size: .85rem;
}
button:hover { background: #27272a; }
</style>
