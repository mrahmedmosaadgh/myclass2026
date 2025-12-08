<template>
  <div class="lesson-editor q-gutter-md">
    <div class="row">
        <div class="col-7">
          <div class="player-area">
            <LessonVideoPlayer
              :slide="currentSlide"
              @timelineEvent="onTimelineEvent"
              ref="player"
            />
            <QuestionDisplay
              v-if="activeQuestion"
              :question="activeQuestion"
              @answered="onQuestionAnswered"
            />
          </div>
          <div class="activities q-mt-md">
            <ActivityCard v-for="act in activities" :key="act.id" :activity="act" />
          </div>
        </div>
        <div class="col-5">
          <div class="side-panels q-pa-sm">
            <div class="timeline-card q-mb-md">
              <h4>Timeline</h4>
              <LessonTimeline :activities="activities" @seekTo="onSeekTo" @selectQuestion="onSelectQuestion" />
            </div>

            <div class="teacher-card q-mb-md">
              <TeacherPanel @start="onStart" @pause="onPause" @next="onNext" @feedback="onFeedback" />
            </div>

            <div class="student-card q-mb-md">
              <StudentPanel @requestHelp="onRequestHelp" @markDone="onMarkDone" @submitAnswer="onStudentSubmit" />
            </div>

            <QuestionEditor
              :questions="currentSlide.questions"
              @updateQuestions="(q) => (currentSlide.questions = q)"
            />
            <LessonControls
              :lesson="lesson"
              @save="saveLesson"
              @export="exportLesson"
              @imported="onImportedLesson"
            />
          </div>
        </div>
      </div>
    <div class="row q-mt-md">
      <div class="col-12">
        <div>Slide progress: {{slideProgress}}% — Total points: {{totalPoints}}</div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue';
import sampleLesson from './sampleLesson';
import LessonVideoPlayer from './LessonVideoPlayer.vue';
import LessonTimeline from './LessonTimeline.vue';
import QuestionDisplay from './QuestionDisplay.vue';
import QuestionEditor from './QuestionEditor.vue';
import LessonControls from './LessonControls.vue';
import ActivityCard from './ActivityCard.vue';
import TeacherPanel from './TeacherPanel.vue';
import StudentPanel from './StudentPanel.vue';
import storage from './utils/storage';
import fileManager from './utils/fileManager';

const lesson = reactive(JSON.parse(JSON.stringify(sampleLesson)));
const currentSlideIndex = ref(0);
const currentSlide = computed(() => lesson.slides[currentSlideIndex.value]);
const activeQuestion = ref(null);
const totalPoints = ref(0);
const slideProgress = ref(0);

// template ref to the player component
const player = ref(null);

// Auto-save to IndexedDB
watch(lesson, (v) => {
  storage.saveLessonToIndexedDB(v).catch(() => {});
}, { deep: true });

async function saveLesson() {
  await storage.saveLessonToIndexedDB(lesson);
}

async function exportLesson() {
  await fileManager.exportLessonAsZip(lesson);
}

async function onImportedLesson({ lesson: importedLesson }) {
  if (importedLesson) {
    // replace reactive content
    Object.keys(lesson).forEach(k => delete lesson[k]);
    Object.assign(lesson, importedLesson);
  }
}

function onTimelineEvent(event) {
  if (!event) return;
  if (event.type === 'question') {
    const q = currentSlide.value.questions.find(x => x.id === event.questionId);
    if (q) activeQuestion.value = q;
  }
}

function onQuestionAnswered({ questionId, correct, points }) {
  totalPoints.value += points || 0;
  activeQuestion.value = null;
}

function onSeekTo(time) {
  if (player.value && player.value.seekTo) player.value.seekTo(time);
}

function onSelectQuestion(qId) {
  const q = currentSlide.value.questions.find(x => x.id === qId);
  activeQuestion.value = q || null;
}

// Convert slides to a flattened activities array for the timeline
import { toRaw } from 'vue';
const activities = computed(() => {
  const s = toRaw(lesson).slides || [];
  const acts = [];
  s.forEach((slide, si) => {
    if (slide.video) {
      acts.push({ id: `slide-${si}-video`, title: slide.title, type: 'videoSegment', startTime: slide.video.playFrom || 0, duration: (slide.video.playTo ? slide.video.playTo - (slide.video.playFrom||0) : (slide.video.duration || 0)) });
    }
    (slide.timeline || []).forEach((t, ti) => {
      acts.push({ id: t.id || `t-${si}-${ti}`, title: t.title || t.type, type: t.type, startTime: t.time ?? t.start ?? 0, duration: (t.end != null && t.start != null) ? (t.end - t.start) : t.duration ?? 0, question: slide.questions?.find(q=>q.id===t.questionId) });
    });
  });
  acts.sort((a,b)=> (a.startTime || 0) - (b.startTime || 0));
  return acts;
});

function onStart() { console.log('Teacher start'); }
function onPause() { console.log('Teacher pause'); }
function onNext() { console.log('Teacher next'); }
function onFeedback(text) { console.log('Feedback:', text); }
function onRequestHelp() { console.log('Student requested help'); }
function onMarkDone() { console.log('Student marked done'); }
function onStudentSubmit(answer) { console.log('Student submitted:', answer); }

</script>

<style scoped>
.lesson-editor { padding: 8px; }
.timeline-item { display:flex; align-items:center; justify-content:space-between; padding:6px; border:1px solid #eee; margin-bottom:6px; }
</style>
