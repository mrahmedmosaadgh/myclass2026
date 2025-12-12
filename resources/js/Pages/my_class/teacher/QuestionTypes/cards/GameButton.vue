<template>
    <button
        @click="triggerCelebration"
        :disabled="disabled"
        class="game-button group relative inline-flex items-center justify-center overflow-hidden rounded-lg px-8 py-3 font-bold transition-all duration-300 ease-out"
        :class="[disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer']"
    >
        <!-- Background Effects -->
        <div class="absolute inset-0 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500"></div>
        <div class="stars-container absolute inset-0 opacity-0 group-hover:opacity-100"></div>

        <!-- Shine effect -->
        <div class="absolute inset-0 transition-all duration-1000 ease-out group-hover:translate-x-full">
            <div class="absolute inset-0 transform rotate-12 translate-x-[-120%] bg-white/30 blur-sm"></div>
        </div>

        <!-- Button Content -->
        <div class="relative flex items-center gap-2 text-white">
            <slot>Click Me!</slot>
            <svg
:class="showStar?'opacity-100':'opacity-10'"
            class="star-icon h-5 w-5 animate-spin-slow" viewBox="0 0 24 24">
                <path fill="currentColor" d="M12,17.27L18.18,21L16.54,13.97L22,9.24L14.81,8.62L12,2L9.19,8.62L2,9.24L7.45,13.97L5.82,21L12,17.27Z" />
            </svg>
        </div>

        <!-- Celebration particles -->
        <div v-if="celebrating" class="particles absolute inset-0"></div>
    </button>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
    disabled: {
        type: Boolean,
        default: false
    }
});

const celebrating = ref(false);
const showStar = ref(false);

const triggerCelebration = () => {
    if (props.disabled) return;

    celebrating.value = true;
    showStar.value = true;

    setTimeout(() => {
        celebrating.value = false;
    }, 1000);

    setTimeout(() => {
        showStar.value = false;
    }, 1500);
};
</script>

<style scoped>
.game-button {
    text-shadow: 0 2px 4px rgba(0,0,0,0.3);
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

.game-button::before {
    content: '';
    position: absolute;
    inset: 1px;
    background: rgba(0,0,0,0.2);
    border-radius: inherit;
    transition: opacity 0.3s;
}

.game-button:hover::before {
    opacity: 0;
}

.stars-container {
    background-image:
        radial-gradient(2px 2px at 20px 30px, #fff, rgba(0,0,0,0)),
        radial-gradient(2px 2px at 40px 70px, #fff, rgba(0,0,0,0)),
        radial-gradient(2px 2px at 50px 160px, #fff, rgba(0,0,0,0)),
        radial-gradient(2px 2px at 90px 40px, #fff, rgba(0,0,0,0)),
        radial-gradient(2px 2px at 130px 80px, #fff, rgba(0,0,0,0));
    background-size: 200px 200px;
    animation: stars 4s linear infinite;
}

.particles {
    background-image:
        radial-gradient(circle, #ff0 1px, transparent 1px),
        radial-gradient(circle, #f0f 1px, transparent 1px),
        radial-gradient(circle, #0ff 1px, transparent 1px);
    background-size: 20px 20px;
    animation: particles 1s ease-out forwards;
}

@keyframes stars {
    from { background-position: 0 0; }
    to { background-position: -200px 200px; }
}

@keyframes particles {
    0% {
        opacity: 1;
        transform: scale(0);
    }
    100% {
        opacity: 0;
        transform: scale(4);
    }
}

.animate-spin-slow {
    animation: spin 2s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>
