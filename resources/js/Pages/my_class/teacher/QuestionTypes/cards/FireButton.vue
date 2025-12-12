<template>
    <button
        class="fire-button"
        :style="{
            '--start-color': startColor,
            '--stop-color': stopColor,
            '--middle-color': middleColor,
            '--button-bg': buttonBg,
            '--text-color': textColor,
            '--hover-text-color': hoverTextColor,
            '--border-radius': borderRadius + 'px',
            '--animation-speed': animationSpeed + 's',
            '--glow-intensity': glowIntensity,
            '--hover-lift-height': hoverLiftHeight,
            '--press-depth': pressDepth + 'px',
            '--button-padding': `${paddingY}em ${paddingX}em`,
            '--font-size': fontSize + 'em',
        }"
    >
        <slot>🔥 Fire Button 🔥</slot>
    </button>
</template>

<script setup>
defineProps({
    // Colors
    startColor: {
        type: String,
        default: '#ff0000'
    },
    middleColor: {
        type: String,
        default: '#ff8800'
    },
    stopColor: {
        type: String,
        default: '#ffff00'
    },
    buttonBg: {
        type: String,
        default: '#222'
    },
    textColor: {
        type: String,
        default: 'white'
    },
    hoverTextColor: {
        type: String,
        default: '#ffd700'
    },

    // Dimensions
    borderRadius: {
        type: Number,
        default: 10
    },
    paddingX: {
        type: Number,
        default: 2
    },
    paddingY: {
        type: Number,
        default: 1
    },
    fontSize: {
        type: Number,
        default: 1.2
    },

    // Animation
    animationSpeed: {
        type: Number,
        default: 3
    },
    glowIntensity: {
        type: Number,
        default: 1.5
    },
    hoverLiftHeight: {
        type: Number,
        default: 6
    },
    pressDepth: {
        type: Number,
        default: 4
    },

    // Effects
    enableGlow: {
        type: Boolean,
        default: true
    },
    enable3D: {
        type: Boolean,
        default: true
    },
    enableFireEffect: {
        type: Boolean,
        default: true
    }
});
</script>

<style scoped>
.fire-button {
    position: relative;
    padding: var(--button-padding);
    background: var(--button-bg);
    color: var(--text-color);
    font-size: var(--font-size);
    border: none;
    cursor: pointer;
    z-index: 0;
    overflow: hidden;
    border-radius: var(--border-radius);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    transform-style: preserve-3d;
    transform: perspective(1000px) translateZ(0) translateY(0);
    box-shadow:
        0 6px 0 #111,
        0 8px 15px rgba(0, 0, 0, 0.3);
}

.fire-button::before {
    content: '';
    position: absolute;
    inset: -2px;
    background: linear-gradient(
        90deg,
        var(--start-color),
        var(--middle-color),
        var(--stop-color),
        var(--middle-color),
        var(--start-color)
    );
    background-size: 400% 100%;
    border-radius: calc(var(--border-radius) + 2px);
    z-index: -2;
    animation: move-fire var(--animation-speed) linear infinite;
    filter: blur(2px) brightness(var(--glow-intensity));
    opacity: 0.9;
    transform: translateZ(-1px);
}

.fire-button::after {
    content: '';
    position: absolute;
    inset: 2px;
    background: linear-gradient(
        to bottom,
        #333,
        var(--button-bg)
    );
    border-radius: var(--border-radius);
    z-index: -1;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    transform: translateZ(0);
}

.fire-button:hover {
    color: var(--hover-text-color);
    transform: perspective(1000px) translateZ(10px) translateY(calc(var(--hover-lift-height) * -1px));
    box-shadow:
        0 8px 0 #111,
        0 16px 25px rgba(255, 61, 0, 0.3),
        0 0 20px rgba(255, 136, 0, 0.5),
        0 0 40px rgba(255, 61, 0, 0.3);
}

.fire-button:active {
    transform: perspective(1000px) translateZ(5px) translateY(var(--press-depth));
    box-shadow:
        0 2px 0 #111,
        0 4px 8px rgba(255, 61, 0, 0.2),
        0 0 10px rgba(255, 136, 0, 0.3),
        0 0 20px rgba(255, 61, 0, 0.2);
    transition: all 0.1s cubic-bezier(0.4, 0, 0.2, 1);
}

.fire-button:active::after {
    transform: translateZ(0) scale(0.98);
}

.fire-button:hover::before {
    filter: blur(3px) brightness(calc(var(--glow-intensity) + 0.5));
    animation: move-fire calc(var(--animation-speed) - 1s) linear infinite;
}

@keyframes move-fire {
    0% { background-position: 0% 50%; }
    100% { background-position: 400% 50%; }
}

.fire-button {
    animation: pulse-glow 2s ease-in-out infinite;
}

@keyframes pulse-glow {
    0%, 100% {
        box-shadow:
            0 6px 0 #111,
            0 8px 15px rgba(0, 0, 0, 0.3),
            0 0 5px rgba(255, 136, 0, 0.3),
            0 0 10px rgba(255, 61, 0, 0.2);
    }
    50% {
        box-shadow:
            0 6px 0 #111,
            0 8px 15px rgba(0, 0, 0, 0.3),
            0 0 10px rgba(255, 136, 0, 0.5),
            0 0 20px rgba(255, 61, 0, 0.3);
    }
}
</style>


