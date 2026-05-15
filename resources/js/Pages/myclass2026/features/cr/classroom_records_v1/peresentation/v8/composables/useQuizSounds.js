import { ref, onMounted } from 'vue'

export function useQuizSounds() {
  const audioContext = ref(null)
  const isMuted = ref(false)

  // Load mute preference from localStorage
  onMounted(() => {
    const savedMuted = localStorage.getItem('quiz-sounds-muted')
    if (savedMuted !== null) {
      isMuted.value = savedMuted === 'true'
    }
  })

  function initAudioContext() {
    if (!audioContext.value) {
      audioContext.value = new (window.AudioContext || window.webkitAudioContext)()
    }
    if (audioContext.value.state === 'suspended') {
      audioContext.value.resume()
    }
  }

  function toggleMute() {
    isMuted.value = !isMuted.value
    localStorage.setItem('quiz-sounds-muted', isMuted.value.toString())
  }

  // Soft click sound
  function playClick() {
    if (isMuted.value) return
    initAudioContext()

    const ctx = audioContext.value
    const oscillator = ctx.createOscillator()
    const gainNode = ctx.createGain()

    oscillator.connect(gainNode)
    gainNode.connect(ctx.destination)

    oscillator.frequency.setValueAtTime(800, ctx.currentTime)
    oscillator.frequency.exponentialRampToValueAtTime(600, ctx.currentTime + 0.05)

    gainNode.gain.setValueAtTime(0.1, ctx.currentTime)
    gainNode.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.05)

    oscillator.start(ctx.currentTime)
    oscillator.stop(ctx.currentTime + 0.05)
  }

  // Correct answer sound (ascending chime)
  function playCorrect() {
    if (isMuted.value) return
    initAudioContext()

    const ctx = audioContext.value

    // Play C-E-G chord
    [523.25, 659.25, 783.99].forEach((freq, i) => {
      const oscillator = ctx.createOscillator()
      const gainNode = ctx.createGain()

      oscillator.connect(gainNode)
      gainNode.connect(ctx.destination)

      oscillator.frequency.setValueAtTime(freq, ctx.currentTime + i * 0.05)

      gainNode.gain.setValueAtTime(0, ctx.currentTime + i * 0.05)
      gainNode.gain.linearRampToValueAtTime(0.15, ctx.currentTime + i * 0.05 + 0.05)
      gainNode.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + i * 0.05 + 0.4)

      oscillator.start(ctx.currentTime + i * 0.05)
      oscillator.stop(ctx.currentTime + i * 0.05 + 0.5)
    })
  }

  // Wrong answer sound (low buzz)
  function playWrong() {
    if (isMuted.value) return
    initAudioContext()

    const ctx = audioContext.value
    const oscillator = ctx.createOscillator()
    const gainNode = ctx.createGain()

    oscillator.type = 'sawtooth'
    oscillator.connect(gainNode)
    gainNode.connect(ctx.destination)

    oscillator.frequency.setValueAtTime(150, ctx.currentTime)
    oscillator.frequency.linearRampToValueAtTime(100, ctx.currentTime + 0.15)

    gainNode.gain.setValueAtTime(0.15, ctx.currentTime)
    gainNode.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.2)

    oscillator.start(ctx.currentTime)
    oscillator.stop(ctx.currentTime + 0.2)
  }

  // Timer tick sound (when timer ≤ 10s)
  function playTimerTick() {
    if (isMuted.value) return
    initAudioContext()

    const ctx = audioContext.value
    const oscillator = ctx.createOscillator()
    const gainNode = ctx.createGain()

    oscillator.connect(gainNode)
    gainNode.connect(ctx.destination)

    oscillator.frequency.setValueAtTime(1000, ctx.currentTime)

    gainNode.gain.setValueAtTime(0.08, ctx.currentTime)
    gainNode.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.03)

    oscillator.start(ctx.currentTime)
    oscillator.stop(ctx.currentTime + 0.03)
  }

  // Victory fanfare (quiz complete)
  function playComplete() {
    if (isMuted.value) return
    initAudioContext()

    const ctx = audioContext.value

    // Victory fanfare: C-E-G-C arpeggio
    const notes = [523.25, 659.25, 783.99, 1046.50]
    notes.forEach((freq, i) => {
      const oscillator = ctx.createOscillator()
      const gainNode = ctx.createGain()

      oscillator.connect(gainNode)
      gainNode.connect(ctx.destination)

      oscillator.frequency.setValueAtTime(freq, ctx.currentTime + i * 0.08)

      gainNode.gain.setValueAtTime(0, ctx.currentTime + i * 0.08)
      gainNode.gain.linearRampToValueAtTime(0.12, ctx.currentTime + i * 0.08 + 0.08)
      gainNode.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + i * 0.08 + 0.6)

      oscillator.start(ctx.currentTime + i * 0.08)
      oscillator.stop(ctx.currentTime + i * 0.08 + 0.7)
    })
  }

  return {
    isMuted,
    toggleMute,
    playClick,
    playCorrect,
    playWrong,
    playTimerTick,
    playComplete
  }
}
