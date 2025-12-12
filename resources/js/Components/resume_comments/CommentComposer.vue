<template>
  <div class="comment-composer">
    <textarea v-model="comment" placeholder="Write a comment..." rows="3" class="w-full mb-2"></textarea>
    <div class="flex gap-2 mb-2">
      <input type="file" ref="fileInput" @change="onFileChange" accept="audio/*,video/*" class="hidden" />
      <button @click="$refs.fileInput.click()">Upload Audio/Video</button>
      <button @click="startRecording" v-if="!isRecording">🎤 Record Voice</button>
      <button @click="stopRecording" v-if="isRecording">⏹️ Stop</button>
      <audio v-if="audioUrl" :src="audioUrl" controls class="ml-2" />
      <video v-if="videoUrl" :src="videoUrl" controls class="ml-2" width="120" />
    </div>
    <div class="flex items-center gap-2">
      <label><input type="checkbox" v-model="isPublic" /> Public</label>
      <button @click="submit" :disabled="loading">Send</button>
    </div>
    <div v-if="error" class="text-red-500 mt-1">{{ error }}</div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps({
  questionId: [String, Number],
  parentId: [String, Number],
});

const comment = ref('');
const isPublic = ref(true);
const file = ref(null);
const audioUrl = ref(null);
const videoUrl = ref(null);
const isRecording = ref(false);
const mediaRecorder = ref(null);
const recordedChunks = ref([]);
const loading = ref(false);
const error = ref('');

function onFileChange(e) {
  const f = e.target.files[0];
  if (!f) return;
  file.value = f;
  if (f.type.startsWith('audio')) {
    audioUrl.value = URL.createObjectURL(f);
    videoUrl.value = null;
  } else if (f.type.startsWith('video')) {
    videoUrl.value = URL.createObjectURL(f);
    audioUrl.value = null;
  }
}

function startRecording() {
  error.value = '';
  if (!navigator.mediaDevices || !window.MediaRecorder) {
    error.value = 'Voice recording not supported.';
    return;
  }
  navigator.mediaDevices.getUserMedia({ audio: true })
    .then(stream => {
      mediaRecorder.value = new MediaRecorder(stream);
      recordedChunks.value = [];
      mediaRecorder.value.ondataavailable = e => {
        if (e.data.size > 0) recordedChunks.value.push(e.data);
      };
      mediaRecorder.value.onstop = () => {
        const blob = new Blob(recordedChunks.value, { type: 'audio/webm' });
        file.value = new File([blob], 'voice.webm', { type: 'audio/webm' });
        audioUrl.value = URL.createObjectURL(blob);
      };
      mediaRecorder.value.start();
      isRecording.value = true;
    })
    .catch(() => {
      error.value = 'Could not access microphone.';
    });
}

function stopRecording() {
  if (mediaRecorder.value) {
    mediaRecorder.value.stop();
    isRecording.value = false;
  }
}

async function submit() {
  loading.value = true;
  error.value = '';
  try {
    const form = new FormData();
    form.append('comment', comment.value);
    form.append('is_public', isPublic.value ? 1 : 0);
    if (props.parentId) form.append('parent_id', props.parentId);
    if (file.value) form.append('file', file.value);
    await axios.post(`/api/resume-comments/${props.questionId}`, form);
    comment.value = '';
    file.value = null;
    audioUrl.value = null;
    videoUrl.value = null;
    if (mediaRecorder.value) mediaRecorder.value = null;
    recordedChunks.value = [];
    // emit event to parent to refresh comments
    emit('submitted');
  } catch (e) {
    error.value = e.response?.data?.errors?.file?.[0] || 'Failed to submit.';
  } finally {
    loading.value = false;
  }
}
</script>

<style scoped>
.comment-composer textarea {
  resize: vertical;
}
</style>
