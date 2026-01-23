<template>
  <div class="q-pa-md">
    <h1>Developer Tools</h1>
    <ul>
      <!-- Using Inertia Link instead of router-link -->
      <li><Link :href="route('developer.resume-themes')">Resume Themes</Link></li>
      <li><Link :href="route('developer.project-tasks')">Project Tasks</Link></li>
    </ul>
  </div>
</template>
<script setup>
import { Link } from '@inertiajs/vue3';
</script>
