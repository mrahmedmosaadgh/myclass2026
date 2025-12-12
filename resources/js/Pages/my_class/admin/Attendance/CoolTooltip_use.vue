<template>
 <div class="p-0 full-width-row">
<TreeEditor />
    <dialog1 />
  <div class="q-pa-md q-gutter-sm">
    <q-btn :loading="loading[0]" color="secondary" @click="simulateProgress(0)" label="Button" />
    <q-btn :loading="loading[1]" color="red" @click="simulateProgress(1)">
      Button
      <template v-slot:loading>
        Loading...
      </template>
    </q-btn>
    <q-btn :loading="loading[2]" color="purple" @click="simulateProgress(2)">
      Button
      <template v-slot:loading>
        <q-spinner-radio />
      </template>
    </q-btn>
    <q-btn :loading="loading[3]" color="primary" @click="simulateProgress(3)" style="width: 150px">
      Button
      <template v-slot:loading>
        <q-spinner-hourglass class="on-left" />
        Loading...
      </template>
    </q-btn>
    <br>
    <q-btn round :loading="loading[4]" color="brown" @click="simulateProgress(4)" icon="camera_front">
      <template v-slot:loading>
        <q-spinner-facebook />
      </template>
    </q-btn>
    <q-btn round :loading="loading[5]" color="black" @click="simulateProgress(5)" icon="camera_rear">
      <template v-slot:loading>
        <q-spinner-gears />
      </template>
    </q-btn>
    <br>

    <q-btn :loading="progress" color="primary" @click="progress = true">
      Controlled from outside
      <template v-slot:loading>
        <q-spinner-radio class="on-left" />
        Click "Stop" Button
      </template>
    </q-btn>
    <q-btn :disable="!progress" color="negative" @click="progress = false" label="Stop" />
  </div>



















<CoolTooltip color="#1e293b" direction="bottom">
  <button class="p-2 bg-blue-600 text-white rounded">Hover me</button>
  <template #content>
    <div>This is a <b>cool</b> tooltip content!</div>
  </template>
</CoolTooltip>
Click trigger:

vue
Copy
Edit
<CoolTooltip trigger="click" color="#4ade80" direction="top">
  <span class="underline cursor-pointer">Click me!</span>
  <template #content>
    <div>You clicked me! 🎯</div>
  </template>
</CoolTooltip>
Both hover and click:

vue
Copy
Edit
<CoolTooltip trigger="both" color="#facc15" direction="right">
  <span class="p-2">Hover or Click me!</span>
  <template #content>
    <div>Flexible trigger!</div>
  </template>
</CoolTooltip>
🚀 Why is this better than PrimeVue?
Full content selectable ✅

Custom HTML inside ✅

Auto-flip on screen edge ✅

Smooth open/close animation ✅

Full-width / normal width ✅

Minimal and easy styling ✅

Super lightweight ✅



 </div>
  </template>

  <script setup>
  import { ref } from 'vue';
  import CoolTooltip from './CoolTooltip.vue';
  import dialog1 from './dialog1.vue';
  import TreeEditor from '@/Pages/my_class/admin/Attendance/tree/tree_use.vue';
//   resources/js/Pages/my_class/admin/Attendance/tree/tree_use.vue

  const loading = ref([
      false,
      false,
      false,
      false,
      false,
      false
    ])

    const progress = ref(false)

    function simulateProgress (number) {
      // we set loading state
      loading.value[ number ] = true

      // simulate a delay
      setTimeout(() => {
        // we're done, we reset loading state
        loading.value[ number ] = false
      }, 3000)
    }
  </script>
