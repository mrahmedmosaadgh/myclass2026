<template>
  <div class="behavior-manager">
    <!-- Fun Header with Student Name -->
    <div class="student-header">
      <div class="student-avatar">
        <q-icon name="face" size="3rem" color="primary" />
      </div>
      <div class="student-info">
        <h2 class="student-name">{{ studentName }}</h2>
        <p class="encouragement-text">{{ encouragementMessage }}</p>
      </div>
      <q-btn round color="primary" icon="volume_up" @click="speakStudentName" class="speak-btn" size="lg">
        <q-tooltip>Say student name</q-tooltip>
      </q-btn>
    </div>

    <!-- Fun Tabs with Icons -->
    <q-tabs v-model="tab" class="fun-tabs" indicator-color="transparent" active-color="white" align="justify">
      <q-tab name="positive" class="positive-tab" :class="{ 'active-positive': tab === 'positive' }">
        <div class="tab-content">
          <q-icon name="star" size="2rem" />
          <div class="tab-label">Great Job!</div>
          <div class="tab-emoji">⭐</div>
        </div>
      </q-tab>
      <q-tab name="negative" class="negative-tab" :class="{ 'active-negative': tab === 'negative' }">
        <div class="tab-content">
          <q-icon name="school" size="2rem" />
          <div class="tab-label">Let's Practice</div>
          <div class="tab-emoji">📚</div>
        </div>
      </q-tab>
    </q-tabs>

    <!-- Behavior Cards -->
    <q-tab-panels v-model="tab" animated class="behavior-panels">
      <!-- Positive Behaviors -->
      <q-tab-panel name="positive" class="positive-panel">
        <div class="panel-header">
          <h3>🌟 Awesome Behaviors! 🌟</h3>
          <p>Click on what {{ studentName }} did great!</p>
        </div>
        <div class="behavior-grid">
          <q-card v-for="b in positive" :key="b.id" class="behavior-card positive-card" @click="showConfirmDialog(b)">
            <q-card-section class="card-content">
              <div class="behavior-icon positive-icon">
                {{ getBehaviorEmoji(b.name, 'positive') }}
              </div>
              <div class="behavior-name">{{ b.name }}</div>
              <div class="behavior-points positive-points">
                +{{ b.points }} points
              </div>
              <div class="sparkles">✨</div>
            </q-card-section>
          </q-card>
        </div>
      </q-tab-panel>

      <!-- Negative Behaviors -->
      <q-tab-panel name="negative" class="negative-panel">
        <div class="panel-header">
          <h3>📚 Let's Learn Together! 📚</h3>
          <p>We can practice and get better at these!</p>
        </div>
        <div class="behavior-grid">
          <q-card v-for="b in negative" :key="b.id" class="behavior-card negative-card" @click="showConfirmDialog(b)">
            <q-card-section class="card-content">
              <div class="behavior-icon negative-icon">
                {{ getBehaviorEmoji(b.name, 'negative') }}
              </div>
              <div class="behavior-name">{{ b.name }}</div>
              <div class="behavior-points negative-points">
                {{ b.points }} points
              </div>
              <div class="practice-text">Let's practice!</div>
            </q-card-section>
          </q-card>
        </div>
      </q-tab-panel>
    </q-tab-panels>

    <!-- Fun Score Display -->
    <div class="score-display">
      <div class="score-card positive-score">
        <q-icon name="star" size="2rem" color="positive" />
        <div class="score-number">+{{ summary.positive }}</div>
        <div class="score-label">Great Jobs!</div>
      </div>
      <div class="score-card total-score">
        <q-icon name="emoji_events" size="2rem" color="primary" />
        <div class="score-number">{{ summary.total }}</div>
        <div class="score-label">Total Points</div>
      </div>
      <div class="score-card negative-score">
        <q-icon name="school" size="2rem" color="orange" />
        <div class="score-number">{{ Math.abs(summary.negative) }}</div>
        <div class="score-label">Practice More</div>
      </div>
    </div>

    <!-- Confirmation Dialog -->
    <q-dialog v-model="showConfirm" persistent>
      <q-card class="confirm-dialog">
        <q-card-section class="dialog-header">
          <div class="confirm-icon">
            {{ selectedBehavior?.type === 'positive' ? '🌟' : '📚' }}
          </div>
          <h3 class="confirm-title">
            {{ selectedBehavior?.type === 'positive' ? 'Great Job!' : 'Let\'s Practice!' }}
          </h3>
        </q-card-section>

        <q-card-section class="dialog-content">
          <div class="confirm-message">
            <p class="student-name-large">{{ studentName }}</p>
            <p class="behavior-description">
              {{ selectedBehavior?.type === 'positive' ? 'did a great job with:' : 'needs to practice:' }}
            </p>
            <p class="behavior-name-large">{{ selectedBehavior?.name }}</p>
            <p class="points-info">
              This will {{ selectedBehavior?.type === 'positive' ? 'add' : 'subtract' }}
              <strong>{{ Math.abs(selectedBehavior?.points || 0) }} points</strong>
            </p>
          </div>
        </q-card-section>

        <q-card-actions class="dialog-actions">
          <q-btn flat label="Cancel" color="grey" @click="cancelConfirm" class="cancel-btn" size="lg" />
          <q-btn :color="selectedBehavior?.type === 'positive' ? 'positive' : 'orange'"
            :label="selectedBehavior?.type === 'positive' ? 'Yes, Great Job!' : 'Yes, Let\'s Practice!'"
            @click="confirmBehavior" class="confirm-btn" size="lg" />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Success Animation -->
    <div v-if="showSuccess" class="success-animation">
      <div class="success-content">
        <div class="success-icon">🎉</div>
        <div class="success-text">{{ successMessage }}</div>
      </div>
    </div>

    <!-- Audio elements for sound effects -->
    <audio ref="positiveSound" preload="auto">
      <source src="/sounds/positive-chime.mp3" type="audio/mpeg">
      <source src="/sounds/positive-chime.wav" type="audio/wav">
    </audio>
    <audio ref="negativeSound" preload="auto">
      <source src="/sounds/gentle-reminder.mp3" type="audio/mpeg">
      <source src="/sounds/gentle-reminder.wav" type="audio/wav">
    </audio>
  </div>
</template>

<script setup>
import { ref, computed, nextTick } from 'vue'
import { useQuasar } from 'quasar'
import axios from 'axios'

const props = defineProps({
  studentId: Number,read:Boolean,
  studentName: { type: String, default: 'Student' },
  periodCode: String,
  date: { type: String, default: () => new Date().toISOString().slice(0, 10) },
  behaviors: { type: Array, default: () => [] },
  summary: { type: Object, default: () => ({ positive: 0, negative: 0, total: 0 }) }
})

const emit = defineEmits(['recorded', 'close'])

const $q = useQuasar()
const tab = ref('positive')
const showConfirm = ref(false)
const selectedBehavior = ref(null)
const showSuccess = ref(false)
const successMessage = ref('')
const positiveSound = ref(null)
const negativeSound = ref(null)

// Computed properties
const summary = computed(() => props.summary || { positive: 0, negative: 0, total: 0 })
const positive = computed(() => (props.behaviors || []).filter(b => b.type === 'positive'))
const negative = computed(() => (props.behaviors || []).filter(b => b.type === 'negative'))

// Encouragement messages that rotate
const encouragementMessages = [
  "You're doing great!",
  "Keep up the good work!",
  "You're awesome!",
  "Learning every day!",
  "You're a star!",
  "Great effort!",
  "You're amazing!"
]

const encouragementMessage = computed(() => {
  const index = Math.floor(Math.random() * encouragementMessages.length)
  return encouragementMessages[index]
})

// Text-to-Speech functionality
const speak = (text, rate = 1, pitch = 1.2) => {
  if(!props.read){return}
  if ('speechSynthesis' in window) {
    // Cancel any ongoing speech
    speechSynthesis.cancel()

    const utterance = new SpeechSynthesisUtterance(text)
    utterance.rate = rate
    utterance.pitch = pitch
    utterance.volume = 0.8

    // Try to use a child-friendly voice
    const voices = speechSynthesis.getVoices()
    const childVoice = voices.find(voice =>
      voice.name.includes('child') ||
      voice.name.includes('kid') ||
      voice.name.includes('young') ||
      (voice.gender === 'female' && voice.lang.startsWith('en'))
    )

    if (childVoice) {
      utterance.voice = childVoice
    }

    speechSynthesis.speak(utterance)
  }
}

const speakStudentName = () => {
  speak(`  ${props.studentName} `, 0.9, 1.3)
  // speak(`Hello ${props.studentName}! You're doing great today!`, 0.9, 1.3)
}

// Sound effects
const playPositiveSound = () => {
  try {
    // Try to play custom sound first
    if (positiveSound.value) {
      positiveSound.value.currentTime = 0
      positiveSound.value.play().catch(() => {
        // Fallback to Web Audio API generated sound
        playGeneratedSound('positive')
      })
    } else {
      playGeneratedSound('positive')
    }
  } catch (error) {
    console.log('Sound not available')
  }
}

const playNegativeSound = () => {
  try {
    if (negativeSound.value) {
      negativeSound.value.currentTime = 0
      negativeSound.value.play().catch(() => {
        playGeneratedSound('negative')
      })
    } else {
      playGeneratedSound('negative')
    }
  } catch (error) {
    console.log('Sound not available')
  }
}

// Generate sounds using Web Audio API as fallback
const playGeneratedSound = (type) => {
  try {
    const audioContext = new (window.AudioContext || window.webkitAudioContext)()
    const oscillator = audioContext.createOscillator()
    const gainNode = audioContext.createGain()

    oscillator.connect(gainNode)
    gainNode.connect(audioContext.destination)

    if (type === 'positive') {
      // Happy ascending chime
      oscillator.frequency.setValueAtTime(523.25, audioContext.currentTime) // C5
      oscillator.frequency.setValueAtTime(659.25, audioContext.currentTime + 0.1) // E5
      oscillator.frequency.setValueAtTime(783.99, audioContext.currentTime + 0.2) // G5
      oscillator.type = 'sine'
      gainNode.gain.setValueAtTime(0.3, audioContext.currentTime)
      gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5)
      oscillator.start(audioContext.currentTime)
      oscillator.stop(audioContext.currentTime + 0.5)
    } else {
      // Gentle reminder tone
      oscillator.frequency.setValueAtTime(440, audioContext.currentTime) // A4
      oscillator.type = 'sine'
      gainNode.gain.setValueAtTime(0.2, audioContext.currentTime)
      gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.3)
      oscillator.start(audioContext.currentTime)
      oscillator.stop(audioContext.currentTime + 0.3)
    }
  } catch (error) {
    console.log('Web Audio not available')
  }
}

// Emoji mapping for behaviors
const getBehaviorEmoji = (behaviorName, type) => {
  const emojiMap = {
    positive: {
      'listening': '👂',
      'helping': '🤝',
      'sharing': '🤲',
      'following directions': '✅',
      'being kind': '💝',
      'working hard': '💪',
      'raising hand': '🙋',
      'good effort': '⭐',
      'teamwork': '👥',
      'respect': '🙏',
      'default': '🌟'
    },
    negative: {
      'talking out': '🗣️',
      'not listening': '👂',
      'not following directions': '📋',
      'disrupting': '⚠️',
      'not sharing': '🤲',
      'not helping': '🤝',
      'off task': '📚',
      'default': '📖'
    }
  }

  const typeMap = emojiMap[type] || emojiMap.positive
  const behaviorKey = behaviorName.toLowerCase()

  for (const key in typeMap) {
    if (behaviorKey.includes(key)) {
      return typeMap[key]
    }
  }

  return typeMap.default
}

// Dialog functions
const showConfirmDialog = (behavior) => {
  selectedBehavior.value = behavior
  showConfirm.value = true

  // Speak the behavior
  const actionText = behavior.type === 'positive'
    ? `Great job   ${behavior.name}!`     : `  ${behavior.name}`
    // ? `Great job with ${behavior.name}!`     : `Let's practice ${behavior.name}`
  speak(actionText, 0.9, 1.2)
}

const cancelConfirm = () => {
  showConfirm.value = false
  selectedBehavior.value = null
}

const confirmBehavior = async () => {
  if (!selectedBehavior.value) return

  const behavior = selectedBehavior.value

  try {
    await axios.post('/api/student-behaviors', {
      student_id: props.studentId,
      behavior_id: behavior.id,
      points: behavior.points,
      period_code: props.periodCode,
      date: props.date
    })

    // Play appropriate sound
    if (behavior.type === 'positive') {
      playPositiveSound()
      successMessage.value = `🎉 Great job, ${props.studentName}! You earned ${behavior.points} points for ${behavior.name}!`
      speak(`Fantastic ${props.studentName}! You earned ${behavior.points} points for ${behavior.name}! Keep up the great work!`, 0.9, 1.3)
    } else {
      playNegativeSound()
      successMessage.value = `  ${props.studentName}  ${behavior.name} do better next time!`
      // successMessage.value = `📚 That's okay, ${props.studentName}! Let's practice ${behavior.name} and do better next time!`
      speak(` ${props.studentName}.   ${behavior.name}.  you lost ${ Math.abs(behavior.points) || 0 } points  `, 0.9, 1.1)
      // speak(`That's okay ${props.studentName}. Let's practice ${behavior.name} and you'll do better next time!`, 0.9, 1.1)
    }

    // Show success animation
    showSuccess.value = true

    // Hide dialogs
    showConfirm.value = false
    selectedBehavior.value = null

    // Tell parent to refresh
    emit('recorded')

    // Hide success animation and close dialog after delay
    setTimeout(() => {
      showSuccess.value = false
      setTimeout(() => {
        emit('close')
      }, 500)
    }, 3000)

  } catch (err) {
    let errorMessage = 'Oops! Something went wrong.'

    if (err.response?.status === 409) {
      errorMessage = 'This was already recorded today!'
    } else if (err.response?.data?.errors) {
      const msgs = Object.values(err.response.data.errors).flat()
      errorMessage = msgs.join('. ')
    } else if (err.response?.data?.message) {
      errorMessage = err.response.data.message
    }

    $q.notify({
      message: errorMessage,
      color: 'negative',
      position: 'top',
      timeout: 3000,
      actions: [{ icon: 'close', color: 'white' }]
    })

    speak(errorMessage, 0.9, 1.0)
  }
}
</script>

<style scoped>
.behavior-manager {
  max-width: 800px;
  margin: 0 auto;
  font-family: 'Comic Sans MS', cursive, sans-serif;
}

/* Student Header */
.student-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.5rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 20px;
  color: white;
  margin-bottom: 1.5rem;
  box-shadow: 0 8px 32px rgba(102, 126, 234, 0.3);
}

.student-avatar {
  background: rgba(255, 255, 255, 0.2);
  border-radius: 50%;
  padding: 1rem;
  backdrop-filter: blur(10px);
}

.student-info {
  flex: 1;
}

.student-name {
  font-size: 2rem;
  font-weight: bold;
  margin: 0;
  text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
}

.encouragement-text {
  font-size: 1.2rem;
  margin: 0.5rem 0 0 0;
  opacity: 0.9;
}

.speak-btn {
  background: rgba(255, 255, 255, 0.2);
  backdrop-filter: blur(10px);
  transition: all 0.3s ease;
}

.speak-btn:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: scale(1.1);
}

/* Fun Tabs */
.fun-tabs {
  margin-bottom: 1.5rem;
  border-radius: 15px;
  overflow: hidden;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.positive-tab {
  background: linear-gradient(135deg, #4CAF50, #8BC34A);
  color: white;
  transition: all 0.3s ease;
}

.negative-tab {
  background: linear-gradient(135deg, #FF9800, #FFC107);
  color: white;
  transition: all 0.3s ease;
}

.active-positive {
  background: linear-gradient(135deg, #2E7D32, #4CAF50) !important;
  transform: scale(1.05);
  box-shadow: 0 4px 15px rgba(76, 175, 80, 0.4);
}

.active-negative {
  background: linear-gradient(135deg, #F57C00, #FF9800) !important;
  transform: scale(1.05);
  box-shadow: 0 4px 15px rgba(255, 152, 0, 0.4);
}

.tab-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  padding: 1rem;
}

.tab-label {
  font-size: 1.1rem;
  font-weight: bold;
}

.tab-emoji {
  font-size: 1.5rem;
}

/* Behavior Panels */
.behavior-panels {
  background: transparent;
}

.positive-panel {
  background: linear-gradient(135deg, #E8F5E8, #F1F8E9);
  border-radius: 20px;
  padding: 1.5rem;
}

.negative-panel {
  background: linear-gradient(135deg, #FFF3E0, #FFF8E1);
  border-radius: 20px;
  padding: 1.5rem;
}

.panel-header {
  text-align: center;
  margin-bottom: 2rem;
}

.panel-header h3 {
  font-size: 1.8rem;
  margin: 0 0 0.5rem 0;
  color: #333;
}

.panel-header p {
  font-size: 1.2rem;
  color: #666;
  margin: 0;
}

/* Behavior Grid */
.behavior-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1.5rem;
}

.behavior-card {
  border-radius: 20px;
  cursor: pointer;
  transition: all 0.3s ease;
  border: none;
  overflow: hidden;
  position: relative;
}

.positive-card {
  background: linear-gradient(135deg, #4CAF50, #8BC34A);
  color: white;
  box-shadow: 0 4px 20px rgba(76, 175, 80, 0.3);
}

.positive-card:hover {
  transform: translateY(-8px) scale(1.05);
  box-shadow: 0 12px 40px rgba(76, 175, 80, 0.4);
}

.negative-card {
  background: linear-gradient(135deg, #FF9800, #FFC107);
  color: white;
  box-shadow: 0 4px 20px rgba(255, 152, 0, 0.3);
}

.negative-card:hover {
  transform: translateY(-8px) scale(1.05);
  box-shadow: 0 12px 40px rgba(255, 152, 0, 0.4);
}

.card-content {
  text-align: center;
  padding: 2rem 1rem;
  position: relative;
}

.behavior-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
  display: block;
}

.behavior-name {
  font-size: 1.3rem;
  font-weight: bold;
  margin-bottom: 0.5rem;
  text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);
}

.behavior-points {
  font-size: 1.1rem;
  font-weight: bold;
  margin-bottom: 0.5rem;
}

.positive-points {
  color: #E8F5E8;
}

.negative-points {
  color: #FFF3E0;
}

.sparkles {
  font-size: 1.5rem;
  position: absolute;
  top: 10px;
  right: 15px;
  animation: sparkle 2s infinite;
}

.practice-text {
  font-size: 0.9rem;
  opacity: 0.9;
  font-style: italic;
}

@keyframes sparkle {

  0%,
  100% {
    opacity: 1;
    transform: scale(1);
  }

  50% {
    opacity: 0.7;
    transform: scale(1.2);
  }
}

/* Score Display */
.score-display {
  display: flex;
  justify-content: center;
  gap: 1.5rem;
  margin-top: 2rem;
  flex-wrap: wrap;
}

.score-card {
  background: white;
  border-radius: 15px;
  padding: 1.5rem;
  text-align: center;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
  min-width: 120px;
  transition: all 0.3s ease;
}

.score-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
}

.positive-score {
  border: 3px solid #4CAF50;
}

.total-score {
  border: 3px solid #2196F3;
}

.negative-score {
  border: 3px solid #FF9800;
}

.score-number {
  font-size: 2rem;
  font-weight: bold;
  margin: 0.5rem 0;
}

.score-label {
  font-size: 1rem;
  color: #666;
  font-weight: 500;
}

/* Confirmation Dialog */
.confirm-dialog {
  border-radius: 25px;
  overflow: hidden;
  max-width: 500px;
  margin: 0 auto;
}

.dialog-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  text-align: center;
  padding: 2rem;
}

.confirm-icon {
  font-size: 4rem;
  margin-bottom: 1rem;
}

.confirm-title {
  font-size: 1.8rem;
  margin: 0;
  font-weight: bold;
}

.dialog-content {
  padding: 2rem;
  text-align: center;
}

.confirm-message {
  font-size: 1.2rem;
  line-height: 1.6;
}

.student-name-large {
  font-size: 1.8rem;
  font-weight: bold;
  color: #2196F3;
  margin: 0 0 1rem 0;
}

.behavior-description {
  color: #666;
  margin: 0 0 1rem 0;
}

.behavior-name-large {
  font-size: 1.5rem;
  font-weight: bold;
  color: #333;
  margin: 0 0 1rem 0;
}

.points-info {
  color: #666;
  margin: 0;
}

.dialog-actions {
  padding: 1.5rem 2rem;
  gap: 1rem;
}

.cancel-btn {
  border-radius: 25px;
  padding: 0.8rem 2rem;
  font-size: 1.1rem;
}

.confirm-btn {
  border-radius: 25px;
  padding: 0.8rem 2rem;
  font-size: 1.1rem;
  font-weight: bold;
}

/* Success Animation */
.success-animation {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(76, 175, 80, 0.9);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  animation: fadeIn 0.5s ease;
}

.success-content {
  text-align: center;
  color: white;
  animation: bounceIn 0.8s ease;
}

.success-icon {
  font-size: 6rem;
  margin-bottom: 1rem;
  animation: rotate 2s linear infinite;
}

.success-text {
  font-size: 2rem;
  font-weight: bold;
  max-width: 600px;
  line-height: 1.4;
  text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }

  to {
    opacity: 1;
  }
}

@keyframes bounceIn {
  0% {
    transform: scale(0.3);
    opacity: 0;
  }

  50% {
    transform: scale(1.1);
    opacity: 1;
  }

  100% {
    transform: scale(1);
    opacity: 1;
  }
}

@keyframes rotate {
  from {
    transform: rotate(0deg);
  }

  to {
    transform: rotate(360deg);
  }
}

/* Responsive Design */
@media (max-width: 768px) {
  .student-header {
    flex-direction: column;
    text-align: center;
    gap: 1rem;
  }

  .student-name {
    font-size: 1.5rem;
  }

  .behavior-grid {
    grid-template-columns: 1fr;
  }

  .score-display {
    flex-direction: column;
    align-items: center;
  }

  .success-text {
    font-size: 1.5rem;
    padding: 0 1rem;
  }
}

@media (max-width: 480px) {
  .behavior-manager {
    padding: 0.5rem;
  }

  .student-header {
    padding: 1rem;
  }

  .panel-header h3 {
    font-size: 1.4rem;
  }

  .behavior-name {
    font-size: 1.1rem;
  }
}
</style>
