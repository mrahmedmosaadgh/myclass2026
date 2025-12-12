<template>
  <div class="typing-app q-pa-md">
    <div class="text-h5 q-mb-md">Typing Practice</div>
    
    <q-tabs
      v-model="activeTab"
      dense
      class="text-grey"
      active-color="primary"
      indicator-color="primary"
      align="justify"
      narrow-indicator
    >
      <q-tab name="intro" icon="school" label="Introduction" />
      <q-tab name="home" icon="keyboard" label="Home Row" />
      <q-tab name="urk" icon="edit" label="U, R, K Keys" />
      <q-tab name="exercises" icon="assignment" label="Exercises" />
    </q-tabs>

    <q-tab-panels v-model="activeTab" animated>
      <q-tab-panel name="intro">
        <div class="text-h6">Welcome to Typing Practice</div>
        <p class="q-mt-md">Learn touch typing with our structured lessons.</p>
      </q-tab-panel>

      <q-tab-panel name="home">
        <div class="text-h6">Home Row Position</div>
        <p>Place your fingers on: ASDF JKL;</p>
      </q-tab-panel>

      <q-tab-panel name="urk">
        <URKLesson />
      </q-tab-panel>

      <q-tab-panel name="exercises">
        <div class="text-h6">Practice Exercises</div>
        <p>Coming soon...</p>
      </q-tab-panel>
    </q-tab-panels>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import URKLesson from './views/URKLesson.vue'

const activeTab = ref('intro')
</script>

<style scoped>
.typing-app {
  max-width: 1200px;
  margin: 0 auto;
  background: white;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
</style>
