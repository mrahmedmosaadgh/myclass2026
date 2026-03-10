
const tickingAudio = new Audio('/audio/timer/ticking-clock_1-27477.mp3')
tickingAudio.loop = true
tickingAudio.preload = 'auto'

const alarmAudio = new Audio('/audio/timer/countdown-timer-pressure.mp3')
alarmAudio.preload = 'auto'

export const TimerAudio = {
    playTicking() {
        tickingAudio.play().catch(e => console.warn('Ticking play failed', e))
    },
    pauseTicking() {
        tickingAudio.pause()
    },
    playAlarm() {
        // Reset to ensure it plays from start if called again
        alarmAudio.currentTime = 0
        alarmAudio.play().catch(e => console.warn('Alarm play failed', e))
    },
    stopAll() {
        tickingAudio.pause()
        // We don't necessarily reset time for ticking to 0, but good practice if stopping "session"
        // But for pausing, we might want to resume. 
        // StopAll implies reset.
        tickingAudio.currentTime = 0
        alarmAudio.pause()
        alarmAudio.currentTime = 0
    }
}
