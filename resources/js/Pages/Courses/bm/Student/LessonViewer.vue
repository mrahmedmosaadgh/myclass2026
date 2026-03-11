<template>
  <Head title="Basic Math - Lesson" />
  <div class="bm-lesson-viewer q-pa-md fullscreen bg-white">
    <div class="content-wrapper q-mx-auto" style="max-width: 900px">
      <!-- Header -->
      <div class="row justify-between items-center q-mb-lg">
        <q-btn flat icon="arrow_back" color="dark" class="q-mr-sm" @click="$inertia.visit(route('bm.assessment.index'))" />
        <div class="text-h5 text-weight-bold text-primary">Lesson: {{ lessonTitle }}</div>
        <q-chip color="warning" text-color="dark" icon="star">10 XP</q-chip>
      </div>

      <!-- Video / Content Area -->
      <q-card class="shadow-2 rounded-borders overflow-hidden q-mb-lg">
        <div class="video-placeholder bg-dark text-white flex flex-center" style="height: 400px;">
          <q-btn round color="primary" icon="play_arrow" size="xl" />
          <div class="q-mt-sm">Video Lesson Player</div>
        </div>
        <q-card-section>
          <div class="text-h6 q-mb-sm">Understanding the Basics</div>
          <p class="text-body1 text-grey-8">
            In this lesson, we will cover the fundamental concepts of {{ lessonDomain }}. Watch the video carefully, and then try the interactive practice exercises below to reinforce your learning.
          </p>
        </q-card-section>
      </q-card>

      <!-- Interactive Examples -->
      <div class="text-h6 text-weight-bold q-mb-md">Interactive Examples</div>
      <div class="row q-col-gutter-md q-mb-xl">
        <div class="col-12 col-md-6" v-for="i in 2" :key="i">
          <q-card class="bg-blue-1 shadow-1" flat bordered>
            <q-card-section>
              <div class="text-h4 text-center text-primary text-weight-bold q-mb-md">
                5 + {{ i * 2 }} = ?
              </div>
              <q-btn color="primary" label="Reveal Answer" class="full-width" outline />
            </q-card-section>
          </q-card>
        </div>
      </div>

      <!-- Action Footer -->
      <div class="flex justify-end">
        <q-btn color="positive" size="lg" label="Go to Practice" icon-right="arrow_forward" unelevated rounded />
      </div>

    </div>
  </div>
</template>

<script setup>
defineOptions({ layout: BMLayout });
import BMLayout from "@/Layouts/BMLayout.vue";
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

const lessonTitle = ref('Introduction to Addition');
const lessonDomain = ref('Addition');
</script>

<style scoped>
.rounded-borders {
  border-radius: 16px;
}
</style>
