<template>
  <div class="student-panel q-pa-sm">
    <h4>Student Panel</h4>
    <div>
      <button @click="$emit('requestHelp')">Request Help</button>
      <button @click="$emit('markDone')">Mark Complete</button>
    </div>
    <div class="submit q-mt-sm">
      <slot name="submission"> 
        <input v-model="answer" placeholder="Answer" />
        <button @click="submit">Submit</button>
      </slot>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
const emit = defineEmits(['requestHelp','markDone','submitAnswer']);
const answer = ref('');
function submit() { emit('submitAnswer', answer.value); answer.value = ''; }
</script>

<style scoped>
.student-panel input { width:70%; margin-right:8px }
</style>
