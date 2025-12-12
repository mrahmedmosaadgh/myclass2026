<template>
  <div>
    <video ref="videoRef" :src="src" controls style="width:100%" @timeupdate="onTime" @ended="onEnded"></video>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
const props = defineProps({ src: String, start: { type: Number, default: 0 }, end: { type: Number, default: null } })
const videoRef = ref(null)
onMounted(()=>{ if(videoRef.value && props.start) videoRef.value.currentTime = props.start })
watch(()=>props.src, (s)=>{ if(videoRef.value){ videoRef.value.load(); try{ if(props.start) videoRef.value.currentTime = props.start }catch(e){} } })
function onTime(){ const v = videoRef.value; if(props.end && v.currentTime >= props.end){ v.pause(); v.currentTime = props.end; } }
function onEnded(){ /* placeholder */ }
</script>
