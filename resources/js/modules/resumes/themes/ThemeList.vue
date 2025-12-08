<template>
  <div>
    <!-- Theme List UI -->
    <h2>Resume Themes</h2>
    <div v-if="loading">Loading...</div>
    <div v-else>
      <div v-if="themes.length === 0">No themes found.</div>
      <div v-else class="row q-col-gutter-md">
        <div v-for="theme in themes" :key="theme.id" class="col-12 col-md-4">
          <ThemeCard :theme="theme" />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import ThemeCard from './ThemeCard.vue';
import { useResumeThemeStore } from './useResumeThemeStore';

const { themes, loading, fetchThemes } = useResumeThemeStore();

onMounted(fetchThemes);
</script>
