<template>
  <div class="q-pa-md row">
    <div class="col-3">
      <q-card flat>
        <q-card-section class="text-h6">Slides</q-card-section>
        <q-separator />
        <draggable v-model="lesson.slides" item-key="id" @end="saveDraft">
          <template #item="{element, index}">
            <q-item clickable @click="selectSlide(index)" :class="{ 'bg-grey-2': index===currentIndex }">
              <q-item-section>
                <div class="text-subtitle2">{{ index+1 }}. {{ element.type }}</div>
                <div class="text-caption">{{ slideLabel(element) }}</div>
              </q-item-section>
              <q-item-section side>
                <q-btn dense flat color="negative" icon="delete" @click.stop="removeSlide(index)"/>
              </q-item-section>
            </q-item>
          </template>
        </draggable>
        <q-separator />
        <q-card-actions>
          <q-btn label="Add Video" color="primary" @click="addSlide('video')" />
          <q-btn label="Add Question" color="accent" @click="addSlide('question')" />
          <q-btn label="Add Text" color="secondary" @click="addSlide('text')" />
        </q-card-actions>
      </q-card>
    </div>

    <div class="col-6">
      <slide-editor :slide="currentSlide" @update-slide="onUpdateSlide" v-if="currentSlide"/>
      <div v-else class="q-pa-md">No slide selected</div>
      <lesson-progress :lesson="lesson" :current-index="currentIndex"/>
    </div>

    <div class="col-3">
      <lesson-controls :lesson="lesson" @export="exportLesson" @import="importLesson" @save="saveToServer" />
      <q-separator class="q-mt-md" />
      <q-card>
        <q-card-section>
          <div class="text-h6">Selected Slide</div>
          <div class="text-subtitle2">{{ currentIndex+1 }} / {{ lesson.slides.length }}</div>
        </q-card-section>
        <q-separator />
        <q-card-section v-if="currentSlide">
          <div><strong>Type:</strong> {{ currentSlide.type }}</div>
          <div v-if="currentSlide.type==='video'">
            <div><strong>Video:</strong> {{ currentSlide.video.src || '—' }}</div>
            <div><strong>Start:</strong> {{ currentSlide.video.start || 0 }}s</div>
            <div><strong>End:</strong> {{ currentSlide.video.end || 'end' }}s</div>
            <q-toggle v-model="currentSlide.video.autoNext" label="Auto Next" @update:model-value="saveDraft" />
          </div>
        </q-card-section>
      </q-card>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import draggable from 'vuedraggable'
import SlideEditor from './11SlideEditor.vue'
import LessonProgress from './LessonProgress.vue'
import LessonControls from './LessonControls.vue'
import { useLessonStore } from './utils/storage'

const store = useLessonStore()
const lesson = ref(store.loadDraft() || { id: Date.now().toString(), title: 'New Lesson', slides: [] })
const currentIndex = ref(0)
const currentSlide = computed(() => lesson.value.slides[currentIndex.value] || null)

function addSlide(type){
  const id = 's_' + Date.now()
  const slide = { id, type }
  if(type==='video') slide.video = { src: '', start: 0, end: null, autoNext: true, isLocal: false }
  if(type==='question') slide.question = { id: 'q_' + Date.now(), type: 'MultipleChoice', questionText: '', options: ['',''], correctIndex: 0, maxPoints: 5 }
  if(type==='text') slide.content = ''
  lesson.value.slides.push(slide)
  currentIndex.value = lesson.value.slides.length - 1
  saveDraft()
}

function removeSlide(i){ lesson.value.slides.splice(i,1); if(currentIndex.value>=lesson.value.slides.length) currentIndex.value = lesson.value.slides.length - 1; saveDraft() }
function selectSlide(i){ currentIndex.value = i }
function slideLabel(s){ if(!s) return ''; if(s.type==='video') return s.video?.src || 'Video slide'; if(s.type==='question') return s.question?.questionText?.slice(0,40) || 'Question slide'; return s.content?.slice(0,40) || 'Text slide' }

function onUpdateSlide(updated){ lesson.value.slides[currentIndex.value] = updated; saveDraft() }
function saveDraft(){ store.saveDraft(lesson.value) }
async function exportLesson(){ const blob = new Blob([JSON.stringify(lesson.value, null, 2)], {type:'application/json'}); const url = URL.createObjectURL(blob); const a = document.createElement('a'); a.href = url; a.download = (lesson.value.title||'lesson') + '.json'; a.click(); URL.revokeObjectURL(url) }
async function importLesson(file){ const text = await file.text(); try{ const data = JSON.parse(text); lesson.value = data; store.saveDraft(lesson.value); } catch(e){ console.error(e) } }
function saveToServer(){ /* Placeholder for upload */ console.log('Save to server not implemented') }

watch(lesson, (v)=> store.saveDraft(v), { deep: true })
</script>
