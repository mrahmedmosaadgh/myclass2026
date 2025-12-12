// Pinia/Vuex store for resume themes
import { ref } from 'vue';
import * as api from './resumeThemeApi';

export function useResumeThemeStore() {
  const themes = ref([]);
  const loading = ref(false);

  async function fetchThemes() {
    loading.value = true;
    themes.value = await api.getThemes();
    loading.value = false;
  }

  return { themes, loading, fetchThemes };
}
