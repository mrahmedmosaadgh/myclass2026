<template>
  <div>
    <div class="q-pa-md">
      <div class="row">
        <div class="col-8">
          <div v-if="currentSlide?.type==='video'">
            <lesson-video-player :src="currentSlide.video.src" :start="currentSlide.video.start" :end="currentSlide.video.end" ref="player" />
            <div class="row q-mt-sm items-center">
              <q-btn label="Next" @click="nextSlide" />
            </div>
          </div>
          <div v-else-if="currentSlide?.type==='question'">
            <question-display :question="currentSlide.question" @answered="onAnswered" />
          </div>
          <div v-else>
            <q-card><q-card-section>{{ currentSlide?.content }}</q-card-section></q-card>
            <div class="q-mt-md"><q-btn label="Next" @click="nextSlide" /></div>
          </div>
        </div>
        <div class="col-4">
          <lesson-progress :lesson="lesson" :current-index="index" />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import LessonVideoPlayer from './LessonVideoPlayer.vue'
import QuestionDisplay from './QuestionDisplay.vue'
import LessonProgress from './LessonProgress.vue'

const props = defineProps({ lesson: Object })
const emit = defineEmits(['completed'])
const index = ref(0)
const currentSlide = computed(()=> props.lesson.slides[index.value] || null)

function nextSlide(){ if(index.value < props.lesson.slides.length -1){ index.value++ } else { emit('completed') } }

function onAnswered(payload){
  if(payload.correct){
    // mark earned points
    const s = props.lesson.slides[index.value]
    s._earned = (s._earned || 0) + payload.points
    nextSlide()
  } else {
    // do nothing, allow retry
  }
}
</script>
