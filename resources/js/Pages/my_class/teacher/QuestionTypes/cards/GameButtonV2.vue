<template>
    <button
        @click="handleClick"
        @mouseenter="handleHover"
        :disabled="disabled"
        class="game-button-v2 group relative inline-flex items-center justify-center overflow-visible rounded-xl px-8 py-3 font-bold"
        :class="[disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer']"
    >
        <!-- Main button background -->
        <div class="absolute inset-0 rounded-xl bg-gradient-to-br from-violet-600 via-indigo-500 to-purple-500"></div>

        <!-- Glow effect -->
        <div class="absolute inset-0 rounded-xl opacity-0 transition-opacity duration-300 group-hover:opacity-100">
            <div class="absolute inset-[-2px] rounded-xl bg-gradient-to-br from-violet-400 via-indigo-300 to-purple-400 blur-lg"></div>
        </div>

        <!-- Magic circles on hover -->
        <div v-for="(circle, index) in magicCircles"
             :key="index"
             class="magic-circle absolute rounded-full"
             :style="circle.style">
        </div>

        <!-- Ripple effects from clicks -->
        <div v-for="(ripple, index) in ripples"
             :key="'ripple-' + index"
             class="ripple absolute rounded-full bg-white/30"
             :style="ripple.style">
        </div>

        <!-- Floating particles -->
        <div v-if="showParticles" class="particles-container absolute inset-0">
            <div v-for="(particle, index) in particles"
                 :key="'particle-' + index"
                 class="particle absolute rounded-full"
                 :style="particle.style">
            </div>
        </div>

        <!-- Button content -->
        <div class="relative z-10 flex items-center gap-3">
            <slot>Click Me!</slot>
            <div v-if="showSparkles" class="sparkles">
                ✨
            </div>
        </div>
    </button>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    disabled: {
        type: Boolean,
        default: false
    }
});

const ripples = ref([]);
const particles = ref([]);
const magicCircles = ref([]);
const showParticles = ref(false);
const showSparkles = ref(false);

const handleClick = (event) => {
    if (props.disabled) return;

    // Get click position relative to button
    const rect = event.currentTarget.getBoundingClientRect();
    const x = event.clientX - rect.left;
    const y = event.clientY - rect.top;

    // Create ripple effect
    createRipple(x, y);

    // Create particles
    createParticles(x, y);

    // Show sparkles
    showSparkles.value = true;
    setTimeout(() => showSparkles.value = false, 1000);
};

const handleHover = (event) => {
    if (props.disabled) return;

    const rect = event.currentTarget.getBoundingClientRect();
    const x = event.clientX - rect.left;
    const y = event.clientY - rect.top;

    // createMagicCircle(x, y);
};

const createRipple = (x, y) => {
    const ripple = {
        style: {
            left: `${x}px`,
            top: `${y}px`,
            animation: 'ripple 1s cubic-bezier(0.4, 0, 0.2, 1) forwards',
        }
    };
    ripples.value.push(ripple);
    setTimeout(() => {
        ripples.value.shift();
    }, 1000);
};

const createParticles = (x, y) => {
    showParticles.value = true;
    particles.value = Array.from({ length: 12 }, (_, i) => {
        const angle = (i * 30) * (Math.PI / 180);
        const velocity = 2 + Math.random() * 3;
        const size = 4 + Math.random() * 6;
        const hue = Math.random() * 60 - 30;

        return {
            style: {
                left: `${x}px`,
                top: `${y}px`,
                width: `${size}px`,
                height: `${size}px`,
                backgroundColor: `hsl(${280 + hue}, 70%, 60%)`,
                animation: `particle 0.8s cubic-bezier(0.4, 0, 0.2, 1) forwards`,
                transform: `rotate(${angle}rad)`,
                '--velocity': velocity,
                '--angle': angle
            }
        };
    });

    setTimeout(() => {
        showParticles.value = false;
        particles.value = [];
    }, 800);
};

const createMagicCircle = (x, y) => {
    const circle = {
        style: {
            left: `${x}px`,
            top: `${y}px`,
            animation: 'magic-circle 1.5s cubic-bezier(0.4, 0, 0.2, 1) forwards',
        }
    };
    magicCircles.value.push(circle);
    setTimeout(() => {
        magicCircles.value.shift();
    }, 1500);
};
</script>

<style scoped>
.game-button-v2 {
    text-shadow: 0 2px 4px rgba(0,0,0,0.3);
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    transition: all 0.3s ease;
}

.game-button-v2:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.3);
}

.game-button-v2:active {
    transform: translateY(1px);
}

.ripple {
    transform: scale(0);
    pointer-events: none;
}

.magic-circle {
    width: 50px;
    height: 50px;
    border: 2px solid rgba(255, 255, 255, 0.5);
    pointer-events: none;
}

.particle {
    position: absolute;
    pointer-events: none;
}

.sparkles {
    font-size: 1.5em;
    animation: sparkle 1s ease-in-out;
}

@keyframes ripple {
    0% {
        transform: scale(0);
        opacity: 0.5;
    }
    100% {
        transform: scale(4);
        opacity: 0;
    }
}

@keyframes magic-circle {
    0% {
        transform: scale(0);
        opacity: 0.8;
    }
    100% {
        transform: scale(2);
        opacity: 0;
    }
}

@keyframes particle {
    0% {
        transform: translateX(0) translateY(0) scale(1);
        opacity: 1;
    }
    100% {
        transform:
            translateX(calc(cos(var(--angle)) * var(--velocity) * 50px))
            translateY(calc(sin(var(--angle)) * var(--velocity) * 50px))
            scale(0);
        opacity: 0;
    }
}

@keyframes sparkle {
    0%, 100% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.2); opacity: 0.8; }
}
</style>
