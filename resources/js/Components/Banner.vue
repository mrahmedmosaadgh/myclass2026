<script setup>
import { ref, watchEffect, computed, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useQuasar } from 'quasar';
import '@quasar/extras/material-icons-outlined/material-icons-outlined.css';
import '@quasar/extras/mdi-v6/mdi-v6.css'; // Additional icons

const page = usePage();
const $q = useQuasar();
const show = ref(true);
const style = ref('success');
const message = ref('');
const isHovered = ref(false);
const showConfetti = ref(false);

// Map Jetstream banner styles to flag colors and icons
const bannerProps = computed(() => {
  if (style.value === 'success') {
    return {
      color: 'positive',
      icon: 'mdi-flag-checkered',
      flagColor: 'green-8',
      waveColor: 'green-4',
      emoji: '🎉',
      sound: 'success'
    };
  } else {
    return {
      color: 'negative',
      icon: 'mdi-flag-outline',
      flagColor: 'red-8',
      waveColor: 'red-4',
      emoji: '⚠️',
      sound: 'warning'
    };
  }
});

// Initialize confetti effect for success messages
onMounted(() => {
  // Check if there's already a message on page load
  const initialStyle = page.props.jetstream.flash?.bannerStyle || 'success';
  const initialMessage = page.props.jetstream.flash?.banner || '';

  if (initialMessage) {
    style.value = initialStyle;
    message.value = initialMessage;
    show.value = true;

    if (initialStyle === 'success') {
      showConfetti.value = true;
      setTimeout(() => {
        showConfetti.value = false;
      }, 3000);
    }
  }
});

watchEffect(async () => {
  const newStyle = page.props.jetstream.flash?.bannerStyle || 'success';
  const newMessage = page.props.jetstream.flash?.banner || '';

  if (newMessage && newMessage !== message.value) {
    style.value = newStyle;
    message.value = newMessage;
    show.value = true;

    // Show notification
    $q.notify({
      message: newMessage,
      color: newStyle === 'success' ? 'positive' : 'negative',
      icon: bannerProps.value.icon,
      position: 'top-right',
      timeout: 3000,
      actions: [{ icon: 'close', color: 'white' }]
    });

    // Show confetti for success messages
    if (newStyle === 'success') {
      showConfetti.value = true;
      setTimeout(() => {
        showConfetti.value = false;
      }, 3000);
    }
  }
});

// Handle dismiss
const dismiss = () => {
  show.value = false;
};

// Handle hover state
const onHover = (value) => {
  isHovered.value = value;
};
</script>

<template>
  <div class="banner-wrapper">
    <!-- Confetti effect for success messages -->
    <div v-if="showConfetti" class="confetti-container">
      <div v-for="n in 50" :key="n" class="confetti" :style="{
        '--delay': Math.random() * 5 + 's',
        '--left': Math.random() * 100 + '%',
        '--bg': `hsl(${Math.random() * 360}, 100%, 50%)`
      }"></div>
    </div>

    <transition
      appear
      enter-active-class="banner-enter"
      leave-active-class="banner-leave"
    >
      <div
        v-if="show && message"
        class="banner-container"
        @mouseenter="onHover(true)"
        @mouseleave="onHover(false)"
      >
        <!-- Wave background -->
        <div class="banner-wave" :class="'bg-' + bannerProps.waveColor"></div>

        <!-- Main banner content -->
        <q-card
          :class="['banner-card', 'bg-' + bannerProps.color]"
          flat
          bordered
        >
          <q-card-section class="banner-content">
            <!-- Flag icon with animation -->
            <div class="flag-container" :class="{ 'flag-wave': isHovered }">
              <q-icon
                :name="bannerProps.icon"
                :color="bannerProps.flagColor"
                size="md"
                class="flag-icon q-mr-md"
              />
            </div>

            <!-- Message with emoji -->
            <div class="message-container">
              <span class="emoji-prefix">{{ bannerProps.emoji }}</span>
              <span class="banner-message">{{ message }}</span>
            </div>

            <!-- Action buttons -->
            <div class="action-buttons">
              <!-- Info button for success messages -->
              <q-btn
                v-if="style === 'success'"
                flat
                round
                dense
                icon="info"
                color="white"
                class="action-btn q-mr-xs"
                :ripple="false"
              >
                <q-tooltip>More information</q-tooltip>
              </q-btn>

              <!-- Close button -->
              <q-btn
                flat
                round
                dense
                icon="close"
                color="white"
                @click="dismiss"
                class="close-btn"
                :ripple="false"
              >
                <q-tooltip>Dismiss</q-tooltip>
              </q-btn>
            </div>
          </q-card-section>
        </q-card>
      </div>
    </transition>
  </div>
</template>

<style scoped>
/* Banner container and positioning */
.banner-wrapper {
  position: relative;
  width: 100%;
  z-index: 9999;
  padding: 0 16px;
  margin-bottom: 16px;
}

.banner-container {
  position: relative;
  max-width: 1200px;
  margin: 0 auto;
  overflow: hidden;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
}

.banner-container:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
}

/* Confetti animation */
.confetti-container {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  pointer-events: none;
  z-index: 9998;
  overflow: hidden;
}

.confetti {
  position: absolute;
  top: -10px;
  left: var(--left);
  width: 10px;
  height: 20px;
  background-color: var(--bg);
  opacity: 0.8;
  animation: confetti 5s var(--delay) infinite;
  transform-origin: center;
}

@keyframes confetti {
  0% {
    transform: translateY(-10px) rotate(0deg);
    opacity: 0;
  }
  10% {
    opacity: 1;
  }
  100% {
    transform: translateY(calc(100vh + 10px)) rotate(720deg);
    opacity: 0;
  }
}

/* Wave background effect */
.banner-wave {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 100%;
  opacity: 0.3;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1440 320'%3E%3Cpath fill='white' fill-opacity='1' d='M0,192L48,176C96,160,192,128,288,122.7C384,117,480,139,576,165.3C672,192,768,224,864,213.3C960,203,1056,149,1152,133.3C1248,117,1344,139,1392,149.3L1440,160L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z'%3E%3C/path%3E%3C/svg%3E");
  background-size: cover;
  background-position: center;
  z-index: 0;
}

/* Card styling */
.banner-card {
  position: relative;
  z-index: 1;
  border: none !important;
  border-radius: 12px !important;
}

.banner-content {
  display: flex;
  align-items: center;
  padding: 12px 16px;
  color: white;
}

/* Flag icon styling and animation */
.flag-container {
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
}

.flag-wave {
  animation: flagWave 1s ease-in-out;
}

@keyframes flagWave {
  0% { transform: rotate(0deg); }
  25% { transform: rotate(-15deg); }
  50% { transform: rotate(10deg); }
  75% { transform: rotate(-5deg); }
  100% { transform: rotate(0deg); }
}

.flag-icon {
  filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
}

/* Message styling */
.message-container {
  flex: 1;
  display: flex;
  align-items: center;
  font-weight: 500;
}

.emoji-prefix {
  margin-right: 8px;
  font-size: 1.2rem;
}

.banner-message {
  letter-spacing: 0.01em;
  line-height: 1.4;
}

/* Action buttons styling */
.action-buttons {
  display: flex;
  align-items: center;
}

.action-btn,
.close-btn {
  opacity: 0.8;
  transition: all 0.2s ease;
}

.action-btn:hover,
.close-btn:hover {
  opacity: 1;
  transform: scale(1.1);
  background: rgba(255, 255, 255, 0.1);
}

/* Enter/leave animations */
.banner-enter {
  animation: slideIn 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94) both;
}

.banner-leave {
  animation: slideOut 0.5s cubic-bezier(0.55, 0.085, 0.68, 0.53) both;
}

@keyframes slideIn {
  0% {
    transform: translateY(-50px);
    opacity: 0;
  }
  100% {
    transform: translateY(0);
    opacity: 1;
  }
}

@keyframes slideOut {
  0% {
    transform: translateY(0);
    opacity: 1;
  }
  100% {
    transform: translateY(-50px);
    opacity: 0;
  }
}

/* Dark mode enhancements */
:global(.body--dark) .banner-container {
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.4);
}

:global(.body--dark) .banner-container:hover {
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.5);
}

:global(.body--dark) .banner-card {
  border: 1px solid rgba(255, 255, 255, 0.05) !important;
}

:global(.body--dark) .flag-icon {
  filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.4));
}

/* Responsive adjustments */
@media (max-width: 599px) {
  .banner-wrapper {
    padding: 0;
  }

  .banner-container {
    border-radius: 0;
  }

  .banner-card {
    border-radius: 0 !important;
  }

  .flag-icon {
    margin-right: 8px;
  }

  .emoji-prefix {
    display: none;
  }

  .banner-message {
    font-size: 0.9rem;
  }

  .confetti {
    width: 6px;
    height: 12px;
  }
}
</style>
