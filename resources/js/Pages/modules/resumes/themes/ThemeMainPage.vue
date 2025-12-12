<template>
  <div class="q-pa-md">
    <h1>Resume</h1>
    <q-tabs v-model="tab" class="text-primary q-mb-md">
      <q-tab name="themes" label="Resume Themes" />
      <q-tab name="question-bank" label="Resume Question Bank" />
      <q-tab name="practice" label="Resume Practice" />
    </q-tabs>
    <div v-if="tab === 'themes'">
      <ThemeList />
    </div>
    <div v-else-if="tab === 'question-bank'">
      <QuestionTable :questions="[]" :loading="false" :is-admin="true" />
    </div>
    <div v-else-if="tab === 'practice'">
      <div class="q-pa-md">Resume Practice (Coming soon)</div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import ThemeList from '@/modules/resumes/themes/ThemeList.vue';
import QuestionTable from '@/modules/resumes/components/QuestionTable.vue';
const tab = ref('themes');
</script>
