<template>
  <div class="q-pa-md">
    <h1>Resume Themes</h1>
    <q-tabs v-model="tab" class="text-primary" align="left" dense>
      <q-tab name="themes" label="Themes" />
      <q-tab name="questions" label="Questions" />
      <q-tab name="answers" label="Answers" />
      <q-tab name="comments" label="Comments" />
    </q-tabs>
    <q-separator />
    <div v-show="tab === 'themes'">
      <ThemeList />
    </div>
    <div v-show="tab === 'questions'">
      <ResumeQuestionBank />
    </div>
    <div v-show="tab === 'answers'">
      <ResumeAnswersPanel />
    </div>
    <!-- <div v-show="tab === 'comments'">
      <ResumeCommentsPanel :question-id="null" />
    </div> -->
  </div>
</template>

<script setup>
import { ref } from 'vue';
import ThemeList from './ThemeList.vue';
import ResumeQuestionBank from '@/Pages/modules/resumes/qbank/ResumeQuestionBank.vue';
import ResumeAnswersPanel from '@/components/resume_comments/ResumeAnswersPanel.vue';
// import ResumeCommentsPanel from '@/components/resume_comments/ResumeCommentsPanel.vue';

const tab = ref('themes');
</script>
