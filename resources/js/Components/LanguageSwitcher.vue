<template>
  <div class="language-switcher">
    <!-- Desktop version - Chip with dropdown menu -->
    <div class="gt-xs">
      <q-chip
        clickable
        class="lang-chip shadow-3"
        :color="getCurrentLocaleObj.activeColor"
        text-color="white"
      >
        <q-avatar>
          <img :src="getCurrentFlag" />
        </q-avatar>
        {{ getCurrentLocaleObj.name }}
        <q-icon name="arrow_drop_down" class="q-ml-xs" />

        <q-menu
          transition-show="scale"
          transition-hide="scale"
          class="lang-dropdown"
        >
          <q-list style="min-width: 150px">
            <q-item-label header class="text-center">
              {{ t('common.selectLanguage') || 'Select Language' }}
            </q-item-label>
            <q-separator />

            <q-item
              v-for="locale in availableLocales"
              :key="locale.code"
              clickable
              v-close-popup
              @click="switchLanguage(locale.code)"
              :active="currentLocale === locale.code"
              :active-class="`bg-${locale.activeColor} text-white`"
            >
              <q-item-section avatar>
                <q-avatar size="28px">
                  <img :src="locale.flagSrc" />
                </q-avatar>
              </q-item-section>
              <q-item-section>
                <q-item-label :class="locale.code === 'ar' ? 'arabic-font' : ''">
                  {{ locale.name }}
                </q-item-label>
                <q-item-label caption>
                  {{ locale.nativeName }}
                </q-item-label>
              </q-item-section>
              <q-item-section side v-if="currentLocale === locale.code">
                <q-icon name="check" color="green" />
              </q-item-section>
            </q-item>
          </q-list>
        </q-menu>
      </q-chip>
    </div>

    <!-- Mobile version - Compact chip with dropdown -->
    <div class="lt-sm">
      <q-chip
        clickable
        class="lang-chip-mobile shadow-3"
        :color="getCurrentLocaleObj.activeColor"
        text-color="white"
      >
        <q-avatar>
          <img :src="getCurrentFlag" />
        </q-avatar>
        {{ currentLocale.toUpperCase() }}

        <q-menu
          transition-show="jump-down"
          transition-hide="jump-up"
          class="lang-menu"
        >
          <q-list style="min-width: 150px">
            <q-item-label header class="text-center">
              {{ t('common.selectLanguage') || 'Select Language' }}
            </q-item-label>
            <q-separator />

            <q-item
              v-for="locale in availableLocales"
              :key="locale.code"
              clickable
              v-close-popup
              @click="switchLanguage(locale.code)"
              :active="currentLocale === locale.code"
              :active-class="`bg-${locale.activeColor} text-white`"
            >
              <q-item-section avatar>
                <q-avatar size="28px">
                  <img :src="locale.flagSrc" />
                </q-avatar>
              </q-item-section>
              <q-item-section>
                <q-item-label :class="locale.code === 'ar' ? 'arabic-font' : ''">
                  {{ locale.name }}
                </q-item-label>
                <q-item-label caption>
                  {{ locale.nativeName }}
                </q-item-label>
              </q-item-section>
              <q-item-section side v-if="currentLocale === locale.code">
                <q-icon name="check" color="green" />
              </q-item-section>
            </q-item>
          </q-list>
        </q-menu>
      </q-chip>
    </div>

    <!-- Language change animation -->
    <transition name="globe-spin">
      <div v-if="isChanging" class="globe-animation">
        <div class="globe-content">
          <q-icon name="language" size="3rem" color="primary" class="rotating-globe" />
          <div class="greeting-text">
            {{ getCurrentGreeting }}
          </div>
          <div class="language-particles">
            <div v-for="n in 20" :key="n" class="particle" :style="{
              '--delay': Math.random() * 2 + 's',
              '--size': Math.random() * 10 + 5 + 'px',
              '--left': Math.random() * 100 + '%',
              '--color': n % 2 === 0 ? '#1976D2' : '#4CAF50'
            }"></div>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed, getCurrentInstance, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useQuasar } from 'quasar';

const $q = useQuasar();
const { locale, t } = useI18n();
const currentLocale = computed(() => locale.value);
const app = getCurrentInstance();
const isChanging = ref(false);

// Enhanced locale data with flag icons and colors
const availableLocales = [
  {
    code: 'en',
    name: 'English',
    nativeName: 'English',
    translationKey: 'english',
    icon: 'img:https://flagcdn.com/w40/gb.png',
    flagSrc: 'https://flagcdn.com/w40/gb.png',
    activeColor: 'blue-8',
    greeting: 'Hello!'
  },
  {
    code: 'ar',
    name: 'Arabic',
    nativeName: 'العربية',
    translationKey: 'arabic',
    icon: 'img:https://flagcdn.com/w40/sa.png',
    flagSrc: 'https://flagcdn.com/w40/sa.png',
    activeColor: 'green-8',
    greeting: 'مرحبا!'
  }
];

// Get current flag for mobile dropdown
const getCurrentFlag = computed(() => {
  const current = availableLocales.find(l => l.code === currentLocale.value);
  return current ? current.flagSrc : availableLocales[0].flagSrc;
});

// Get current greeting for animation
const getCurrentGreeting = computed(() => {
  // Get greeting for the language we're switching to
  const targetLocale = availableLocales.find(l => l.code !== currentLocale.value) || availableLocales[0];
  return targetLocale.greeting;
});

// Get current locale object for the chip
const getCurrentLocaleObj = computed(() => {
  return availableLocales.find(l => l.code === currentLocale.value) || availableLocales[0];
});

// Switch language with animation
const switchLanguage = (localeCode) => {
  if (localeCode === currentLocale.value) return;

  // Show animation
  isChanging.value = true;

  // Play sound effect if available
  try {
    const audio = new Audio('/sounds/language-switch.mp3');
    audio.volume = 0.5;
    audio.play().catch(() => {
      // Ignore errors - audio might not be available or allowed
    });
  } catch (e) {
    // Ignore errors
  }

  // Switch language after a short delay for animation
  setTimeout(() => {
    // Access the method from app.config.globalProperties
    app.appContext.config.globalProperties.$switchLanguage(localeCode);

    // Notify user
    $q.notify({
      message: `Language changed to ${availableLocales.find(l => l.code === localeCode)?.name}`,
      color: 'positive',
      icon: 'translate',
      position: 'bottom-right',
      timeout: 2000
    });

    // Hide animation after language change
    setTimeout(() => {
      isChanging.value = false;
    }, 800);
  }, 500);
};

// Watch for dark mode changes to adjust styling
watch(() => $q.dark.isActive, (isDark) => {
  document.documentElement.style.setProperty(
    '--lang-switcher-shadow',
    isDark ? '0 4px 8px rgba(0, 0, 0, 0.5)' : '0 2px 10px rgba(0, 0, 0, 0.1)'
  );
}, { immediate: true });
</script>

<style scoped>
.language-switcher {
  position: relative;
  display: flex;
  align-items: center;
}

/* Chip styling for desktop */
.lang-chip {
  height: 36px;
  border-radius: 18px;
  transition: all 0.3s ease;
  font-weight: 500;
  cursor: pointer;
  box-shadow: var(--lang-switcher-shadow, 0 2px 10px rgba(0, 0, 0, 0.1));
}

.lang-chip:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.lang-chip .q-avatar {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  overflow: hidden;
  border: 1px solid rgba(255, 255, 255, 0.3);
}

.lang-chip .q-avatar img {
  object-fit: cover;
  width: 100%;
  height: 100%;
}

/* Mobile chip styling */
.lang-chip-mobile {
  height: 32px;
  border-radius: 16px;
  transition: all 0.3s ease;
  font-weight: 600;
  cursor: pointer;
  box-shadow: var(--lang-switcher-shadow, 0 2px 10px rgba(0, 0, 0, 0.1));
}

.lang-chip-mobile:hover {
  transform: scale(1.05);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.lang-chip-mobile .q-avatar {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  overflow: hidden;
  border: 1px solid rgba(255, 255, 255, 0.3);
}

/* Dropdown menu styling */
.lang-dropdown, .lang-menu {
  border-radius: 12px;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
}

.lang-dropdown .q-item, .lang-menu .q-item {
  min-height: 48px;
  border-radius: 8px;
  margin: 4px;
  transition: all 0.2s ease;
}

.lang-dropdown .q-item:hover, .lang-menu .q-item:hover {
  background-color: rgba(0, 0, 0, 0.05);
}

:global(.body--dark) .lang-dropdown .q-item:hover,
:global(.body--dark) .lang-menu .q-item:hover {
  background-color: rgba(255, 255, 255, 0.1);
}

/* Animation for language change */
.globe-animation {
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  z-index: 9999;
  background-color: rgba(255, 255, 255, 0.9);
  border-radius: 50%;
  padding: 20px;
  box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
}

.globe-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  position: relative;
}

.greeting-text {
  margin-top: 10px;
  font-weight: 500;
  color: #1976D2;
  font-size: 1.2rem;
}

.rotating-globe {
  animation: rotate 1.5s infinite linear;
}

@keyframes rotate {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

/* Particles animation */
.language-particles {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  pointer-events: none;
  z-index: -1;
}

.particle {
  position: absolute;
  width: var(--size);
  height: var(--size);
  background-color: var(--color);
  border-radius: 50%;
  top: 50%;
  left: var(--left);
  opacity: 0;
  animation: particle 2s var(--delay) infinite;
}

@keyframes particle {
  0% {
    transform: translateY(0);
    opacity: 0;
  }
  20% {
    opacity: 1;
  }
  100% {
    transform: translateY(-100px);
    opacity: 0;
  }
}

.globe-spin-enter-active,
.globe-spin-leave-active {
  transition: opacity 0.3s, transform 0.3s;
}

.globe-spin-enter-from,
.globe-spin-leave-to {
  opacity: 0;
  transform: translate(-50%, -50%) scale(0.5);
}

/* RTL support */
:global([dir="rtl"]) .lang-chip,
:global([dir="rtl"]) .lang-chip-mobile {
  flex-direction: row-reverse;
}

/* Arabic font adjustment */
.arabic-font {
  font-family: 'Tajawal', sans-serif;
  letter-spacing: 0;
}

/* Dark mode adjustments */
:global(.body--dark) .lang-chip,
:global(.body--dark) .lang-chip-mobile {
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.5);
}

:global(.body--dark) .globe-animation {
  background-color: rgba(30, 30, 30, 0.9);
}

:global(.body--dark) .greeting-text {
  color: #64B5F6;
}

/* Pulse animation for mobile */
@keyframes pulse {
  0% {
    box-shadow: 0 0 0 0 rgba(25, 118, 210, 0.4);
  }
  70% {
    box-shadow: 0 0 0 10px rgba(25, 118, 210, 0);
  }
  100% {
    box-shadow: 0 0 0 0 rgba(25, 118, 210, 0);
  }
}

.pulse-animation {
  animation: pulse 2s infinite;
}
</style>
