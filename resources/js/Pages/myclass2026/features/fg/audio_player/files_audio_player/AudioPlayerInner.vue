<template>
  <div
    class="ap-root"
    :style="cssVars"
    :class="{ 'ap-mini': minimized, 'ap-empty': !currentTrack }"
  >
    <!-- Background blur cover art -->
    <div class="ap-bg" :style="currentTrack?.cover ? `background-image:url(${currentTrack.cover})` : ''" />

    <!-- ── MINI bar ── -->
    <div v-if="minimized" class="ap-mini-bar">
      <button class="ap-icon-btn" @click="togglePlay">
        <svg v-if="isPlaying" viewBox="0 0 24 24"><rect x="6" y="5" width="4" height="14" rx="1"/><rect x="14" y="5" width="4" height="14" rx="1"/></svg>
        <svg v-else viewBox="0 0 24 24"><polygon points="5,3 19,12 5,21"/></svg>
      </button>
      <div class="ap-mini-info">
        <span class="ap-mini-title">{{ currentTrack?.title ?? 'No track' }}</span>
        <div class="ap-mini-progress">
          <div class="ap-mini-fill" :style="`width:${progressPct}%`" />
        </div>
      </div>
      <button class="ap-icon-btn" @click="$emit('minimize')"><svg viewBox="0 0 24 24"><path d="M18 15l-6-6-6 6"/></svg></button>
    </div>

    <!-- ── FULL player ── -->
    <template v-else>
      <!-- Cover art -->
      <div class="ap-cover-wrap">
        <div class="ap-cover" :class="{ 'ap-spinning': isPlaying }">
          <img v-if="currentTrack?.cover" :src="currentTrack.cover" alt="cover" />
          <div v-else class="ap-cover-fallback">
            <svg viewBox="0 0 48 48"><circle cx="24" cy="24" r="22" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="24" cy="24" r="6" fill="currentColor"/><line x1="24" y1="2" x2="24" y2="18" stroke="currentColor" stroke-width="2"/></svg>
          </div>
        </div>
        <!-- Visualizer rings -->
        <div v-if="visualizer" class="ap-rings" :class="{ active: isPlaying }">
          <div class="ap-ring ap-ring-1" />
          <div class="ap-ring ap-ring-2" />
          <div class="ap-ring ap-ring-3" />
        </div>
      </div>

      <!-- Track info -->
      <div class="ap-info">
        <p class="ap-title">{{ currentTrack?.title ?? '—' }}</p>
        <p class="ap-artist">{{ currentTrack?.artist ?? '' }}</p>
      </div>

      <!-- Waveform / progress -->
      <div class="ap-progress-wrap" @mousedown="onScrubStart" @touchstart.passive="onScrubStart" ref="progressRef">
        <canvas v-if="visualizer" class="ap-waveform" ref="waveformRef" />
        <div class="ap-track">
          <div class="ap-fill" :style="`width:${progressPct}%`" />
          <div class="ap-thumb" :style="`left:${progressPct}%`" />
        </div>
        <div class="ap-times">
          <span>{{ fmtTime(currentTime) }}</span>
          <span>{{ fmtTime(duration) }}</span>
        </div>
      </div>

      <!-- Controls -->
      <div class="ap-controls">
        <button class="ap-ctrl-btn ap-sm" :class="{ active: orderMode === 'random' }" @click="toggleOrder" title="Shuffle">
          <svg viewBox="0 0 24 24"><path d="M16 3h5v5M4 20l16-16M15 20h5v-5M4 4l7 7"/></svg>
        </button>

        <button class="ap-ctrl-btn" @click="prev" title="Previous">
          <svg viewBox="0 0 24 24"><polygon points="19,20 9,12 19,4"/><line x1="5" y1="4" x2="5" y2="20"/></svg>
        </button>

        <button class="ap-ctrl-btn ap-play" @click="togglePlay">
          <svg v-if="isPlaying" viewBox="0 0 24 24"><rect x="6" y="5" width="4" height="14" rx="1.5"/><rect x="14" y="5" width="4" height="14" rx="1.5"/></svg>
          <svg v-else viewBox="0 0 24 24"><polygon points="5,3 19,12 5,21"/></svg>
        </button>

        <button class="ap-ctrl-btn" @click="next" title="Next">
          <svg viewBox="0 0 24 24"><polygon points="5,4 15,12 5,20"/><line x1="19" y1="4" x2="19" y2="20"/></svg>
        </button>

        <button class="ap-ctrl-btn ap-sm" :class="{ active: repeatMode !== 'none' }" @click="cycleRepeat" title="Repeat">
          <svg v-if="repeatMode === 'one'" viewBox="0 0 24 24"><path d="M17 2l4 4-4 4"/><path d="M3 11V9a4 4 0 014-4h14"/><path d="M7 22l-4-4 4-4"/><path d="M21 13v2a4 4 0 01-4 4H3"/><text x="9" y="15" font-size="6" fill="currentColor">1</text></svg>
          <svg v-else viewBox="0 0 24 24"><path d="M17 2l4 4-4 4"/><path d="M3 11V9a4 4 0 014-4h14"/><path d="M7 22l-4-4 4-4"/><path d="M21 13v2a4 4 0 01-4 4H3"/></svg>
        </button>
      </div>

      <!-- Volume -->
      <div class="ap-volume">
        <button class="ap-icon-btn ap-sm" @click="toggleMute">
          <svg v-if="isMuted || volDisplay === 0" viewBox="0 0 24 24"><polygon points="11,5 6,9 2,9 2,15 6,15 11,19"/><line x1="23" y1="9" x2="17" y2="15"/><line x1="17" y1="9" x2="23" y2="15"/></svg>
          <svg v-else-if="volDisplay < 0.5" viewBox="0 0 24 24"><polygon points="11,5 6,9 2,9 2,15 6,15 11,19"/><path d="M15.54 8.46a5 5 0 010 7.07"/></svg>
          <svg v-else viewBox="0 0 24 24"><polygon points="11,5 6,9 2,9 2,15 6,15 11,19"/><path d="M19.07 4.93a10 10 0 010 14.14M15.54 8.46a5 5 0 010 7.07"/></svg>
        </button>
        <input class="ap-vol-slider" type="range" min="0" max="1" step="0.01"
          :value="isMuted ? 0 : volDisplay"
          @input="onVolChange"
        />
      </div>

      <!-- Playlist -->
      <div v-if="files.length > 1" class="ap-playlist">
        <div
          v-for="(track, i) in files" :key="track.id ?? track.src"
          class="ap-track-row"
          :class="{ 'ap-current': i === currentIndex }"
          @click="playAt(i)"
        >
          <span class="ap-track-num">{{ i + 1 }}</span>
          <div class="ap-track-meta">
            <span class="ap-track-name">{{ track.title ?? track.src }}</span>
            <span class="ap-track-sub">{{ track.artist ?? '' }}</span>
          </div>
          <span class="ap-track-dur">{{ cachedDurations[i] ? fmtTime(cachedDurations[i]) : '—' }}</span>
        </div>
      </div>

      <!-- Minimize button (floating mode) -->
      <button v-if="$attrs.onMinimize !== undefined" class="ap-icon-btn ap-minimize" @click="$emit('minimize')">
        <svg viewBox="0 0 24 24"><path d="M18 15l-6 6-6-6"/></svg>
      </button>
    </template>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted, onBeforeUnmount, nextTick } from 'vue'

/* ─── REGISTRY (module-level singleton) ─────────────────────────── */
// Shared Audio cache: src → HTMLAudioElement
const audioRegistry = (() => {
  if (!window.__apRegistry) window.__apRegistry = new Map()
  return window.__apRegistry
})()

// Active player set for concurrency control
const activePlayers = (() => {
  if (!window.__apActive) window.__apActive = new Set()
  return window.__apActive
})()

function getAudio(src) {
  if (!audioRegistry.has(src)) {
    const a = new Audio()
    a.preload = 'metadata'
    a.src = src
    audioRegistry.set(src, a)
  }
  return audioRegistry.get(src)
}

/* ─── PROPS / EMITS ─────────────────────────────────────────────── */
const props = defineProps({
  files: { type: Array, default: () => [] },
  play: { type: Number, default: 0 },
  startIndex: { type: Number, default: 0 },
  order: { type: String, default: 'sequential' },
  loop: { type: Boolean, default: false },
  repeatOne: { type: Boolean, default: false },
  maxConcurrent: { type: Number, default: 1 },
  volume: { type: Number, default: 1 },
  visualizer: { type: Boolean, default: true },
  autoNext: { type: Boolean, default: true },
  accentColor: { type: String, default: '#7c6aff' },
  minimized: { type: Boolean, default: false },
})

const emit = defineEmits(['update:play', 'trackChange', 'ended', 'minimize'])

/* ─── STATE ─────────────────────────────────────────────────────── */
const currentIndex = ref(props.startIndex)
const isPlaying    = ref(false)
const isMuted      = ref(false)
const volDisplay   = ref(props.volume)
const currentTime  = ref(0)
const duration     = ref(0)
const orderMode    = ref(props.order)       // 'sequential' | 'random'
const repeatMode   = ref(                  // 'none' | 'all' | 'one'
  props.repeatOne ? 'one' : props.loop ? 'all' : 'none'
)
const cachedDurations = reactive({})
const progressRef  = ref(null)
const waveformRef  = ref(null)

let rafId     = null
let audioNode = null          // currently bound Audio element
let scrubbing = false
let waveCtx   = null

/* ─── COMPUTED ──────────────────────────────────────────────────── */
const currentTrack = computed(() => props.files[currentIndex.value] ?? null)

const progressPct = computed(() =>
  duration.value ? (currentTime.value / duration.value) * 100 : 0
)

const cssVars = computed(() => ({
  '--ap-accent': props.accentColor,
  '--ap-accent-dim': props.accentColor + '33',
}))

/* ─── AUDIO BINDING ─────────────────────────────────────────────── */
function bindAudio(index) {
  const track = props.files[index]
  if (!track) return

  // Detach previous
  if (audioNode) {
    audioNode.removeEventListener('timeupdate', onTimeUpdate)
    audioNode.removeEventListener('ended', onEnded)
    audioNode.removeEventListener('loadedmetadata', onMeta)
  }

  audioNode = getAudio(track.src)
  audioNode.volume = isMuted.value ? 0 : volDisplay.value

  audioNode.addEventListener('timeupdate', onTimeUpdate)
  audioNode.addEventListener('ended', onEnded)
  audioNode.addEventListener('loadedmetadata', onMeta)

  // Populate duration if already known
  if (audioNode.readyState >= 1) {
    duration.value = audioNode.duration
    cachedDurations[index] = audioNode.duration
  } else {
    duration.value = 0
  }
  currentTime.value = audioNode.currentTime
}

function onMeta() {
  duration.value = audioNode.duration
  cachedDurations[currentIndex.value] = audioNode.duration
}

function onTimeUpdate() {
  if (!scrubbing) currentTime.value = audioNode.currentTime
  drawWaveform()
}

function onEnded() {
  activePlayers.delete(audioNode)
  isPlaying.value = false
  emit('ended', currentIndex.value)
  if (props.autoNext) advance()
}

/* ─── PLAYBACK ──────────────────────────────────────────────────── */
function doPlay() {
  if (!audioNode) return

  // Enforce maxConcurrent
  if (!activePlayers.has(audioNode)) {
    while (activePlayers.size >= props.maxConcurrent) {
      const oldest = activePlayers.values().next().value
      oldest.pause()
      activePlayers.delete(oldest)
    }
    activePlayers.add(audioNode)
  }

  audioNode.play().then(() => { isPlaying.value = true }).catch(() => {})
}

function doPause() {
  if (!audioNode) return
  audioNode.pause()
  activePlayers.delete(audioNode)
  isPlaying.value = false
}

function togglePlay() {
  isPlaying.value ? doPause() : doPlay()
}

function playAt(index) {
  if (index === currentIndex.value && isPlaying.value) { doPause(); return }
  doPause()
  currentIndex.value = index
  bindAudio(index)
  audioNode.currentTime = 0
  doPlay()
  emit('trackChange', index)
}

function advance() {
  if (repeatMode.value === 'one') {
    audioNode.currentTime = 0; doPlay(); return
  }
  const len = props.files.length
  if (!len) return
  let next
  if (orderMode.value === 'random') {
    next = Math.floor(Math.random() * len)
  } else {
    next = (currentIndex.value + 1) % len
  }
  if (next === 0 && repeatMode.value === 'none') return
  playAt(next)
}

function prev() {
  if (currentTime.value > 3) { audioNode.currentTime = 0; return }
  const len = props.files.length
  if (!len) return
  playAt((currentIndex.value - 1 + len) % len)
}

function next() { advance() }

function toggleOrder() {
  orderMode.value = orderMode.value === 'random' ? 'sequential' : 'random'
}

function cycleRepeat() {
  repeatMode.value = { none: 'all', all: 'one', one: 'none' }[repeatMode.value]
}

/* ─── VOLUME ────────────────────────────────────────────────────── */
function onVolChange(e) {
  volDisplay.value = parseFloat(e.target.value)
  isMuted.value = volDisplay.value === 0
  if (audioNode) audioNode.volume = volDisplay.value
}

function toggleMute() {
  isMuted.value = !isMuted.value
  if (audioNode) audioNode.volume = isMuted.value ? 0 : volDisplay.value
}

/* ─── SCRUBBING ─────────────────────────────────────────────────── */
function onScrubStart(e) {
  scrubbing = true
  seek(e.touches?.[0] ?? e)
  window.addEventListener('mousemove', onScrubMove)
  window.addEventListener('touchmove', onScrubMove, { passive: true })
  window.addEventListener('mouseup', onScrubEnd)
  window.addEventListener('touchend', onScrubEnd)
}

function onScrubMove(e) {
  if (!scrubbing) return
  seek(e.touches?.[0] ?? e)
}

function onScrubEnd() {
  scrubbing = false
  window.removeEventListener('mousemove', onScrubMove)
  window.removeEventListener('touchmove', onScrubMove)
  window.removeEventListener('mouseup', onScrubEnd)
  window.removeEventListener('touchend', onScrubEnd)
}

function seek(e) {
  if (!progressRef.value || !duration.value) return
  const rect = progressRef.value.getBoundingClientRect()
  const pct  = Math.max(0, Math.min(1, (e.clientX - rect.left) / rect.width))
  currentTime.value = pct * duration.value
  if (audioNode) audioNode.currentTime = currentTime.value
}

/* ─── WAVEFORM VISUALIZER ───────────────────────────────────────── */
function drawWaveform() {
  if (!props.visualizer || !waveformRef.value) return
  const canvas = waveformRef.value
  const ctx    = canvas.getContext('2d')
  const W = canvas.width  = canvas.offsetWidth
  const H = canvas.height = canvas.offsetHeight
  ctx.clearRect(0, 0, W, H)

  const pct  = duration.value ? currentTime.value / duration.value : 0
  const bars = Math.floor(W / 4)

  for (let i = 0; i < bars; i++) {
    const x    = i * 4
    const seed = Math.sin(i * 0.4) * 0.5 + Math.sin(i * 0.13) * 0.3 + Math.sin(i * 1.1) * 0.2
    const h    = Math.max(4, (seed + 1) * 0.5 * H * 0.85)
    const y    = (H - h) / 2
    const prog = i / bars

    ctx.beginPath()
    ctx.roundRect(x, y, 3, h, 2)
    if (prog < pct) {
      ctx.fillStyle = props.accentColor
    } else {
      ctx.fillStyle = props.accentColor + '30'
    }
    ctx.fill()
  }
}

/* ─── WATCHERS ──────────────────────────────────────────────────── */
watch(() => props.play, (v) => {
  v === 1 ? doPlay() : doPause()
}, { immediate: false })

watch(() => props.files, (v) => {
  if (!v.length) return
  bindAudio(currentIndex.value)
  // Preload all tracks' metadata
  v.forEach(t => getAudio(t.src))
}, { immediate: true })

watch(() => props.volume, (v) => {
  volDisplay.value = v
  if (audioNode && !isMuted.value) audioNode.volume = v
})

watch(currentIndex, (idx) => {
  bindAudio(idx)
})

watch(isPlaying, (v) => {
  emit('update:play', v ? 1 : 0)
})

/* ─── LIFECYCLE ─────────────────────────────────────────────────── */
onMounted(() => {
  if (props.play === 1) doPlay()
  // Preload all durations
  props.files.forEach((t, i) => {
    const a = getAudio(t.src)
    if (a.readyState >= 1) {
      cachedDurations[i] = a.duration
    } else {
      a.addEventListener('loadedmetadata', () => { cachedDurations[i] = a.duration }, { once: true })
    }
  })
})

onBeforeUnmount(() => {
  doPause()
  if (audioNode) {
    audioNode.removeEventListener('timeupdate', onTimeUpdate)
    audioNode.removeEventListener('ended', onEnded)
    audioNode.removeEventListener('loadedmetadata', onMeta)
  }
})

/* ─── UTIL ──────────────────────────────────────────────────────── */
function fmtTime(s) {
  if (!s || isNaN(s)) return '0:00'
  const m = Math.floor(s / 60)
  const sec = Math.floor(s % 60).toString().padStart(2, '0')
  return `${m}:${sec}`
}
</script>

<style scoped>
/* ── Imports ── */
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap');

/* ── Root ── */
.ap-root {
  --ap-bg:        rgba(12, 10, 22, 0.92);
  --ap-surface:   rgba(255,255,255,0.055);
  --ap-border:    rgba(255,255,255,0.09);
  --ap-text:      #f0eeff;
  --ap-sub:       rgba(240,238,255,0.45);
  --ap-radius:    20px;

  position: relative;
  background: var(--ap-bg);
  backdrop-filter: blur(32px) saturate(1.6);
  -webkit-backdrop-filter: blur(32px) saturate(1.6);
  border: 1px solid var(--ap-border);
  border-radius: var(--ap-radius);
  padding: 24px;
  font-family: 'DM Sans', sans-serif;
  color: var(--ap-text);
  overflow: hidden;
  box-shadow:
    0 32px 64px rgba(0,0,0,.55),
    0 0 0 1px rgba(255,255,255,.04) inset,
    0 1px 0 rgba(255,255,255,.12) inset;
  display: flex;
  flex-direction: column;
  gap: 18px;
  user-select: none;
}

/* ── Blurred BG cover ── */
.ap-bg {
  position: absolute; inset: 0;
  background-size: cover; background-position: center;
  filter: blur(48px) brightness(.3) saturate(2);
  transform: scale(1.1);
  z-index: 0; pointer-events: none;
}
.ap-root > *:not(.ap-bg) { position: relative; z-index: 1; }

/* ── Cover art ── */
.ap-cover-wrap {
  position: relative;
  display: flex; align-items: center; justify-content: center;
  height: 160px;
}

.ap-cover {
  width: 130px; height: 130px;
  border-radius: 50%;
  overflow: hidden;
  box-shadow: 0 12px 40px rgba(0,0,0,.6), 0 0 0 3px var(--ap-accent-dim);
  transition: box-shadow .3s;
  flex-shrink: 0;
}
.ap-cover img { width: 100%; height: 100%; object-fit: cover; }
.ap-cover-fallback {
  width: 100%; height: 100%;
  background: var(--ap-surface);
  display: grid; place-items: center; color: var(--ap-accent);
}
.ap-cover-fallback svg { width: 56px; height: 56px; }

@keyframes spin { to { transform: rotate(360deg); } }
.ap-spinning { animation: spin 8s linear infinite; }

/* Rings */
.ap-rings { position: absolute; inset: 0; pointer-events: none; }
.ap-ring {
  position: absolute; inset: 0; margin: auto;
  border-radius: 50%; border: 1px solid var(--ap-accent);
  opacity: 0; transition: opacity .4s;
}
.ap-rings.active .ap-ring { opacity: 1; }

@keyframes ring-pulse {
  0%, 100% { transform: scale(1); opacity: .3; }
  50%       { transform: scale(1.18); opacity: .06; }
}
.ap-ring-1 { width: 140px; height: 140px; animation: ring-pulse 2.4s ease-in-out infinite; }
.ap-ring-2 { width: 160px; height: 160px; animation: ring-pulse 2.4s ease-in-out .6s infinite; }
.ap-ring-3 { width: 180px; height: 180px; animation: ring-pulse 2.4s ease-in-out 1.2s infinite; }

/* ── Info ── */
.ap-info { text-align: center; }
.ap-title { font-size: 1.05rem; font-weight: 600; margin: 0 0 3px; letter-spacing: -.01em; }
.ap-artist { font-size: .82rem; color: var(--ap-sub); margin: 0; }

/* ── Progress ── */
.ap-progress-wrap {
  cursor: pointer;
  display: flex; flex-direction: column; gap: 6px;
  touch-action: none;
}

.ap-waveform {
  width: 100%; height: 48px;
  display: block; pointer-events: none;
}

.ap-track {
  position: relative; height: 3px;
  background: var(--ap-surface); border-radius: 4px; overflow: visible;
}
.ap-fill {
  position: absolute; left: 0; top: 0; bottom: 0;
  background: var(--ap-accent); border-radius: 4px;
  transition: width .1s linear;
}
.ap-thumb {
  position: absolute; top: 50%; transform: translate(-50%, -50%);
  width: 12px; height: 12px;
  background: #fff; border-radius: 50%;
  box-shadow: 0 0 0 3px var(--ap-accent);
  transition: left .1s linear;
  pointer-events: none;
}
.ap-times {
  display: flex; justify-content: space-between;
  font-size: .72rem; font-family: 'DM Mono', monospace;
  color: var(--ap-sub);
}

/* ── Controls ── */
.ap-controls {
  display: flex; align-items: center; justify-content: center; gap: 12px;
}
.ap-ctrl-btn {
  background: var(--ap-surface); border: 1px solid var(--ap-border);
  border-radius: 50%; color: var(--ap-text);
  cursor: pointer; display: grid; place-items: center;
  width: 42px; height: 42px;
  transition: background .18s, transform .12s, color .18s, box-shadow .18s;
}
.ap-ctrl-btn svg { width: 18px; height: 18px; fill: none; stroke: currentColor; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
.ap-ctrl-btn:hover { background: rgba(255,255,255,.1); transform: scale(1.06); }
.ap-ctrl-btn:active { transform: scale(.94); }
.ap-ctrl-btn.active { color: var(--ap-accent); background: var(--ap-accent-dim); }

.ap-play {
  width: 56px; height: 56px;
  background: var(--ap-accent) !important;
  border-color: transparent !important;
  box-shadow: 0 4px 20px var(--ap-accent-dim);
  color: #fff !important;
}
.ap-play:hover { filter: brightness(1.12); }
.ap-play svg { width: 22px; height: 22px; }

.ap-ctrl-btn.ap-sm { width: 34px; height: 34px; }
.ap-ctrl-btn.ap-sm svg { width: 15px; height: 15px; }

/* ── Volume ── */
.ap-volume {
  display: flex; align-items: center; gap: 10px;
}
.ap-icon-btn {
  background: none; border: none; color: var(--ap-sub);
  cursor: pointer; display: grid; place-items: center;
  padding: 4px; flex-shrink: 0;
  transition: color .18s;
}
.ap-icon-btn:hover { color: var(--ap-text); }
.ap-icon-btn svg { width: 16px; height: 16px; fill: none; stroke: currentColor; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
.ap-icon-btn.ap-sm svg { width: 14px; height: 14px; }

.ap-vol-slider {
  flex: 1; -webkit-appearance: none; height: 3px;
  background: var(--ap-surface); border-radius: 4px;
  outline: none; cursor: pointer;
}
.ap-vol-slider::-webkit-slider-thumb {
  -webkit-appearance: none;
  width: 12px; height: 12px;
  border-radius: 50%; background: var(--ap-accent);
  cursor: pointer;
}
.ap-vol-slider::-moz-range-thumb {
  width: 12px; height: 12px;
  border-radius: 50%; background: var(--ap-accent);
  border: none; cursor: pointer;
}

/* ── Playlist ── */
.ap-playlist {
  max-height: 168px; overflow-y: auto;
  display: flex; flex-direction: column; gap: 2px;
  scrollbar-width: thin; scrollbar-color: var(--ap-accent-dim) transparent;
}
.ap-track-row {
  display: flex; align-items: center; gap: 10px;
  padding: 7px 10px; border-radius: 10px;
  cursor: pointer; transition: background .15s;
}
.ap-track-row:hover { background: var(--ap-surface); }
.ap-track-row.ap-current { background: var(--ap-accent-dim); }
.ap-track-row.ap-current .ap-track-name { color: var(--ap-accent); }

.ap-track-num { font-size: .7rem; color: var(--ap-sub); width: 16px; text-align: right; flex-shrink: 0; font-family: 'DM Mono', monospace; }
.ap-track-meta { flex: 1; overflow: hidden; }
.ap-track-name { font-size: .82rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: block; }
.ap-track-sub  { font-size: .72rem; color: var(--ap-sub); display: block; }
.ap-track-dur  { font-size: .7rem; color: var(--ap-sub); font-family: 'DM Mono', monospace; flex-shrink: 0; }

/* ── Mini bar ── */
.ap-mini-bar {
  display: flex; align-items: center; gap: 12px;
}
.ap-mini-info { flex: 1; overflow: hidden; }
.ap-mini-title { font-size: .85rem; font-weight: 500; display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.ap-mini-progress {
  margin-top: 5px; height: 3px; background: var(--ap-surface); border-radius: 4px; overflow: hidden;
}
.ap-mini-fill { height: 100%; background: var(--ap-accent); border-radius: 4px; transition: width .1s; }

/* ── Minimize btn ── */
.ap-minimize {
  position: absolute; top: 12px; right: 12px;
  background: var(--ap-surface) !important;
  border: 1px solid var(--ap-border) !important;
  border-radius: 50%; width: 28px; height: 28px;
}

/* ── Mini mode ── */
.ap-mini { padding: 12px 16px; gap: 0; }
</style>
