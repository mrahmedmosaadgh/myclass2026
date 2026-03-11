<template>
  <Head title="Basic Math - Learning Path" />
  <div class="bm-learning-path q-pa-md fullscreen bg-grey-1">
    <div class="content-wrapper q-mx-auto" style="max-width: 800px">
      <div class="row items-center q-mb-xl">
        <q-btn flat icon="arrow_back" color="dark" class="q-mr-sm" @click="$inertia.visit(route('bm.assessment.index'))" />
        <h3 class="text-h4 text-weight-bold text-primary q-my-none">My Learning Path</h3>
      </div>

      <q-timeline color="primary">
        <q-timeline-entry 
          v-for="(module, index) in modules" 
          :key="index"
          :title="module.title"
          :subtitle="module.domain"
          :color="module.isCurrent ? 'warning' : (module.isCompleted ? 'positive' : 'grey')"
          :icon="module.isCompleted ? 'check_circle' : 'school'"
        >
          <div>
            <div class="text-body1 q-mb-sm">{{ module.description }}</div>
            <div class="row q-gutter-sm">
              <q-btn v-if="module.isCurrent || module.isCompleted" :color="module.isCurrent ? 'warning' : 'positive'" :label="module.isCompleted ? 'Review Lesson' : 'Start Lesson'" unelevated rounded />
              <q-btn v-if="module.isCurrent || module.isCompleted" color="secondary" label="Practice Docs" outline rounded icon="edit" />
              <q-btn v-if="!module.isCurrent && !module.isCompleted" color="grey" label="Locked" disable flat rounded icon="lock" />
            </div>
          </div>
        </q-timeline-entry>
      </q-timeline>
    </div>
  </div>
</template>

<script setup>
defineOptions({ layout: BMLayout });
import BMLayout from "@/Layouts/BMLayout.vue";
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

// Mock data for the structure
const modules = ref([
  {
    title: 'Addition Basics',
    domain: 'Addition',
    description: 'Learn how to combine single-digit numbers quickly.',
    isCompleted: true,
    isCurrent: false
  },
  {
    title: 'Speed Subtraction',
    domain: 'Subtraction',
    description: 'Master borrowing and speed drills for 2-digit numbers.',
    isCompleted: false,
    isCurrent: true
  },
  {
    title: 'Introduction to Multiplication',
    domain: 'Multiplication',
    description: 'Understanding pairs and groups of numbers.',
    isCompleted: false,
    isCurrent: false
  },
  {
    title: 'Division Fundamentals',
    domain: 'Division',
    description: 'Splitting numbers into equal parts.',
    isCompleted: false,
    isCurrent: false
  }
]);
</script>
