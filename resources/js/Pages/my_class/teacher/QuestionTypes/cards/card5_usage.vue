<template>
  <div class="p-6">
    <CardOptionsEditor v-model="options" />

    <!-- Preview the card with current options -->
    <div class="mt-8">
      <card5 :options="options">
        <div class="p-4">
          <h3>Preview Content</h3>
          <p>Your content here...</p>
        </div>
      </card5>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import CardOptionsEditor from './CardOptionsEditor.vue';
import card5 from './card5.vue';

const options = ref({
  title: 'Title',
  subtitle: 'Subtitle',
  width: 280,
  height: 380,
  colors: {
    start: '#6366f1',
    end: '#4f46e5'
  }
});
</script>

