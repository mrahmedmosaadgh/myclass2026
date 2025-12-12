import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useI18n } from 'vue-i18n';

export function useRtl() {
  const { locale } = useI18n();
  
  // Computed property to check if current locale is RTL
  const isRtl = computed(() => locale.value === 'ar');
  
  // Function to listen for language changes
  const handleLanguageChange = (event) => {
    // You can add additional logic here if needed
  };
  
  // Set up event listener when component is mounted
  onMounted(() => {
    document.addEventListener('language-changed', handleLanguageChange);
  });
  
  // Clean up event listener when component is unmounted
  onUnmounted(() => {
    document.removeEventListener('language-changed', handleLanguageChange);
  });
  
  return {
    isRtl
  };
}