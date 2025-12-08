<template>
    <div class="celebration-wrapper">
        <slot @click="handleClick;launchConfetti()">
            <button class="celebration-button" @click="handleClick;launchConfetti()">
                Celebrate!
            </button>
        </slot>
    </div>
</template>

<script setup>
import confetti from 'canvas-confetti'
import { ref, onMounted } from 'vue'

const emit = defineEmits(['celebrate'])

// Create an audio instance that can be reused
const audioElement = ref(null)

onMounted(() => {
    try {
        audioElement.value = new Audio('/sounds/celebration.mp3')
        audioElement.value.load()
    } catch (error) {
        console.warn('Audio initialization failed:', error)
    }
})

const playSound = async () => {
    if (!audioElement.value) return

    try {
        audioElement.value.currentTime = 0
        await audioElement.value.play()
    } catch (error) {
        console.warn('Failed to play celebration sound:', error)
    }
}

const launchConfetti = () => {
    confetti({
        particleCount: 100,
        spread: 70,
        origin: { y: 0.6 },
        colors: ['#ff0000', '#00ff00', '#0000ff', '#ffff00', '#ff00ff']
    })
}

const handleClick = () => {
    launchConfetti()
    playSound()
    emit('celebrate')
}
</script>

<style scoped>
.celebration-wrapper {
    display: inline-block;
}

.celebration-button {
    background-color: #ff4081;
    color: white;
    border: none;
    border-radius: 12px;
    padding: 12px 20px;
    font-size: 1.2rem;
    cursor: pointer;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    transition: transform 0.2s ease;
}

.celebration-button:hover {
    transform: scale(1.05);
}

.celebration-button:active {
    transform: scale(0.98);
}
</style>


