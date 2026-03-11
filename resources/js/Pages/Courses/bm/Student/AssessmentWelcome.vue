<template>
  <Head title="Basic Math - Welcome" />
  <div class="bm-assessment-welcome text-center q-pa-md fullscreen flex flex-center column bg-blue-1">
    <div class="welcome-card q-pa-xl bg-white rounded-borders shadow-4 text-center" style="max-width: 500px">
      <q-img src="/images/bm/mascot_wave.gif" width="150px" class="q-mb-md" />
      <h3 class="text-h4 text-weight-bold text-primary q-mb-sm">Basic Math Adventure</h3>
      <p class="text-subtitle1 text-grey-8 q-mb-xl">Find out your true math power! Answer carefully, but be quick.</p>

      <q-btn
        @click="startAssessment"
        color="primary"
        size="xl"
        class="full-width rounded-borders text-weight-bold"
        label="Start Mission"
        icon-right="rocket_launch"
        unelevated
        :loading="loading"
      />
    </div>
  </div>
</template>

<script setup>
defineOptions({ layout: BMLayout });
import BMLayout from "@/Layouts/BMLayout.vue";
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const loading = ref(false);

const startAssessment = () => {
  loading.value = true;
  router.post(route('bm.assessment.start'), {}, {
    onFinish: () => loading.value = false
  });
};
</script>

<style scoped>
.welcome-card {
  border-radius: 20px;
}
</style>
