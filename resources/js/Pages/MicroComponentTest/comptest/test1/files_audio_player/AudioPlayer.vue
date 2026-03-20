<template>
  <Teleport to="body" v-if="!inline">
    <Transition name="player-slide">
      <div v-if="visible" class="ap-floating" :class="{ 'ap-minimized': minimized }">
        <AudioPlayerInner v-bind="innerProps" @minimize="minimized = !minimized" :minimized="minimized" />
      </div>
    </Transition>
  </Teleport>

  <div v-else-if="visible" class="ap-inline">
    <AudioPlayerInner v-bind="innerProps" />
  </div>
</template>

<script setup>
import { ref, computed, watch, provide } from 'vue'
import AudioPlayerInner from './AudioPlayerInner.vue'

const props = defineProps({
  /** Array of { id, src, title, artist, cover } objects */
  files: { type: Array, default: () => [] },
  /** 1 = play, 0 = pause */
  play: { type: Number, default: 0 },
  /** Show the player UI */
  visible: { type: Boolean, default: true },
  /** Render inline (true) or floating/fixed (false) */
  inline: { type: Boolean, default: true },
  /** Start index */
  startIndex: { type: Number, default: 0 },
  /** Playback order: 'sequential' | 'random' */
  order: { type: String, default: 'sequential' },
  /** Loop the playlist */
  loop: { type: Boolean, default: false },
  /** Loop single track */
  repeatOne: { type: Boolean, default: false },
  /** Max simultaneous audio instances (global guard) */
  maxConcurrent: { type: Number, default: 1 },
  /** Volume 0–1 */
  volume: { type: Number, default: 1 },
  /** Show waveform visualizer */
  visualizer: { type: Boolean, default: true },
  /** Autoplay next track */
  autoNext: { type: Boolean, default: true },
  /** Accent color override */
  accentColor: { type: String, default: '#7c6aff' },
})

const minimized = ref(false)

const innerProps = computed(() => ({
  files: props.files,
  play: props.play,
  startIndex: props.startIndex,
  order: props.order,
  loop: props.loop,
  repeatOne: props.repeatOne,
  maxConcurrent: props.maxConcurrent,
  volume: props.volume,
  visualizer: props.visualizer,
  autoNext: props.autoNext,
  accentColor: props.accentColor,
}))
</script>

<style scoped>
.ap-floating {
  position: fixed;
  bottom: 24px;
  left: 50%;
  transform: translateX(-50%);
  z-index: 9999;
  width: min(520px, 95vw);
}
.ap-inline { width: 100%; }

.player-slide-enter-active, .player-slide-leave-active {
  transition: transform .35s cubic-bezier(.4,0,.2,1), opacity .35s;
}
.player-slide-enter-from, .player-slide-leave-to {
  transform: translateX(-50%) translateY(30px);
  opacity: 0;
}
</style>
