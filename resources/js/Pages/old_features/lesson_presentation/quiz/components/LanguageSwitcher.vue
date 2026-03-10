<template>
  <div class="language-switcher">
    <button
      @click="toggleLanguage"
      :aria-label="isRtl ? 'Switch to English' : 'التبديل إلى العربية'"
      class="language-switcher-button"
      type="button"
    >
      <span class="language-icon">🌐</span>
      <span class="language-text">{{ currentLanguageLabel }}</span>
    </button>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { locale } = useI18n();

const isRtl = computed(() => locale.value === 'ar');

const currentLanguageLabel = computed(() => {
  return locale.value === 'ar' ? 'العربية' : 'English';
});

const toggleLanguage = () => {
  const newLocale = locale.value === 'ar' ? 'en' : 'ar';
  locale.value = newLocale;
  
  // Update document direction
  document.documentElement.dir = newLocale === 'ar' ? 'rtl' : 'ltr';
  document.documentElement.lang = newLocale;
  
  // Store in localStorage
  localStorage.setItem('locale', newLocale);
  
  // Dispatch event for other components
  document.dispatchEvent(new CustomEvent('language-changed', {
    detail: {
      locale: newLocale,
      isRtl: newLocale === 'ar'
    }
  }));
};
</script>

<style scoped>
.language-switcher {
  display: inline-flex;
  align-items: center;
}

.language-switcher-button {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background-color: var(--color-bg-secondary, #f3f4f6);
  border: 1px solid var(--color-border, #d1d5db);
  border-radius: 0.375rem;
  cursor: pointer;
  transition: all 0.2s ease;
  font-size: 0.875rem;
  font-weight: 500;
  color: var(--color-text-primary, #1f2937);
}

.language-switcher-button:hover {
  background-color: var(--color-bg-hover, #e5e7eb);
  border-color: var(--color-border-hover, #9ca3af);
}

.language-switcher-button:focus {
  outline: 2px solid var(--color-primary, #3b82f6);
  outline-offset: 2px;
}

.language-switcher-button:active {
  transform: scale(0.98);
}

.language-icon {
  font-size: 1.25rem;
  line-height: 1;
}

.language-text {
  line-height: 1;
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
  .language-switcher-button {
    background-color: var(--color-bg-secondary-dark, #374151);
    border-color: var(--color-border-dark, #4b5563);
    color: var(--color-text-primary-dark, #f9fafb);
  }
  
  .language-switcher-button:hover {
    background-color: var(--color-bg-hover-dark, #4b5563);
    border-color: var(--color-border-hover-dark, #6b7280);
  }
}

/* RTL support */
[dir="rtl"] .language-switcher-button {
  flex-direction: row-reverse;
}
</style>
