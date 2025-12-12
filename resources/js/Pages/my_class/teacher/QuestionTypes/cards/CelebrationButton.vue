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
    // Main burst
    confetti({
        particleCount: 80,
        spread: 55,
        origin: { y: 0.7 },
        colors: ['#FFD700', '#FF69B4', '#00CED1', '#9370DB'],
        gravity: 1.2,
        scalar: 1.2,
        ticks: 150
    });

    // Side bursts for added dimension
    setTimeout(() => {
        confetti({
            particleCount: 30,
            angle: 60,
            spread: 40,
            origin: { x: 0, y: 0.8 },
            colors: ['#FFD700', '#FF69B4', '#00CED1', '#9370DB']
        });

        confetti({
            particleCount: 30,
            angle: 120,
            spread: 40,
            origin: { x: 1, y: 0.8 },
            colors: ['#FFD700', '#FF69B4', '#00CED1', '#9370DB']
        });
    }, 100);
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




