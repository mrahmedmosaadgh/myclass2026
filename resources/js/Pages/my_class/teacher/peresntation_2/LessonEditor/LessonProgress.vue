<template>
  <div class="q-mt-md">
    <q-linear-progress :value="slidePercent" color="primary" />
    <div class="row items-center q-mt-sm">
      <div class="col">Slide progress: {{ Math.round(slidePercent*100) }}% — Total points: {{ totalPoints }}</div>
      <div class="col-auto">Slide {{ current+1 }} / {{ total }}</div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
const props = defineProps({ lesson: Object, currentIndex: Number })
const total = computed(()=> props.lesson?.slides?.length || 0)
const current = computed(()=> props.currentIndex || 0)
const slidePercent = computed(()=> {
  const slide = props.lesson?.slides?.[current.value]
  if(!slide) return 0
  if(slide.type==='video') return 0 // video progress could be wired from player
  return 1
})
const totalPoints = computed(()=> {
  return (props.lesson.slides || []).reduce((acc,s)=> acc + (s._earned||0), 0)
})
</script>
