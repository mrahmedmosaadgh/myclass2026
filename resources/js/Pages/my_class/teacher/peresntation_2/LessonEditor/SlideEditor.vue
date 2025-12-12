<template>
  <div>
    <q-card class="q-pa-md">
      <q-card-section>
        <div class="text-h6">Slide Editor</div>
        <q-input v-model="local.title" label="Slide Title" dense class="q-mt-sm" />
      </q-card-section>

      <q-separator />
      <q-card-section>
        <div v-if="local.type==='video'">
          <q-input v-model="local.video.src" label="Video URL or local path" dense />
          <div class="q-mt-sm">
            <q-toggle v-model="local.video.isLocal" label="Is local file" />
          </div>
          <lesson-video-player :src="local.video.src" :start="local.video.start" :end="local.video.end" />
          <div class="row q-gutter-sm q-mt-md">
            <q-input v-model.number="local.video.start" label="Start (s)" type="number" dense />
            <q-input v-model.number="local.video.end" label="End (s)" type="number" dense />
          </div>
          <q-toggle v-model="local.video.autoNext" label="Auto Next when finished" class="q-mt-sm" />
        </div>

        <div v-else-if="local.type==='question'">
          <question-editor v-model:question="local.question" />
        </div>

        <div v-else>
          <q-input type="textarea" v-model="local.content" label="Text Content" />
        </div>
      </q-card-section>

      <q-separator />
      <q-card-actions>
        <q-btn label="Save Slide" color="primary" @click="emitUpdate" />
      </q-card-actions>
    </q-card>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import LessonVideoPlayer from './LessonVideoPlayer.vue'
import QuestionEditor from './QuestionEditor.vue'
const props = defineProps({ slide: Object })
const emit = defineEmits(['update-slide'])
const local = ref(JSON.parse(JSON.stringify(props.slide || {type:'text'})))
watch(()=>props.slide, (v)=>{ local.value = JSON.parse(JSON.stringify(v || {type:'text'})) })
function emitUpdate(){ emit('update-slide', JSON.parse(JSON.stringify(local.value))) }
</script>
