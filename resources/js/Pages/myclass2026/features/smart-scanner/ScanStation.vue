<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import RoleLayout from '../../_shared/layouts/RoleLayout.vue';
import AppCard from '../../_shared/components/AppCard.vue';
import AppButton from '../../_shared/components/AppButton.vue';
import QrReader from './components/QrReader.vue';
import FaceGuard from './components/FaceGuard.vue';
import ResultDisplay from './components/ResultDisplay.vue';
import axios from 'axios';
import { usePage } from '@inertiajs/vue3';

const html5QrCode = ref(null);
const videoElement = ref(null);

const status = ref('idle'); // idle, staged, success, error
const studentId = ref('');
const stagedChoice = ref('');
const message = ref('');
const isFaceDetected = ref(false);

const isPaused = ref(false);
let resetTimer = null;

const onQrInit = (qrCodeInstance) => {
  html5QrCode.value = qrCodeInstance;
  
  // Try to find the video element created by html5-qrcode
  setTimeout(() => {
    const video = document.querySelector('video');
    if (video) {
      videoElement.value = video;
    }
  }, 1000);
};

const handleDecode = (text) => {
  if (isPaused.value) return;

  // The QR must be processed only if we have a face, OR if the user is confirming/cancelling (which are fast actions)
  // To enforce strict security, require face even for confirm/cancel, or require it only for staging.
  
  if (text === 'CMD_CANCEL') {
    resetToIdle();
    return;
  }
  
  if (text === 'CMD_CONFIRM') {
    if (status.value === 'staged') {
      submitAnswer();
    }
    return;
  }
  
  // If it's an answer card (e.g. "1001-A")
  const match = text.match(/^(\d+)-([A-D])$/);
  if (match) {
    if (!isFaceDetected.value) {
      showError("Please step closer! Face not detected.");
      return;
    }
    
    // Stage it
    const newStudentId = match[1];
    const newChoice = match[2];
    
    // Prevent re-triggering the same scan multiple times per second
    if (status.value === 'staged' && studentId.value === newStudentId && stagedChoice.value === newChoice) {
      return; 
    }
    
    studentId.value = newStudentId;
    stagedChoice.value = newChoice;
    status.value = 'staged';
    
    // Auto-reset if they don't confirm in 10 seconds
    if (resetTimer) clearTimeout(resetTimer);
    resetTimer = setTimeout(() => {
      if (status.value === 'staged') resetToIdle();
    }, 10000);
  } else {
    showError("Invalid QR Code");
  }
};

const submitAnswer = async () => {
  isPaused.value = true;
  if (resetTimer) clearTimeout(resetTimer);
  
  // Real implementation:
  // Capture photo if needed (user decided YES: photo capture)
  let photoData = null;
  if (videoElement.value) {
    const canvas = document.createElement('canvas');
    canvas.width = videoElement.value.videoWidth;
    canvas.height = videoElement.value.videoHeight;
    const ctx = canvas.getContext('2d');
    ctx.drawImage(videoElement.value, 0, 0, canvas.width, canvas.height);
    photoData = canvas.toDataURL('image/jpeg', 0.6); // Base64 JPEG
  }

  // Optimistic UI update
  status.value = 'success';
  
  try {
    // We will build this endpoint next
    await axios.post('/api/scan/submit', {
      student_id: studentId.value,
      choice: stagedChoice.value,
      photo: photoData
    });
    
  } catch (err) {
    console.error("Submission failed", err);
    status.value = 'error';
    message.value = "Failed to save to database";
  }
  
  // Reset back to idle after 2.5s
  setTimeout(() => {
    resetToIdle();
  }, 2500);
};

const resetToIdle = () => {
  status.value = 'idle';
  studentId.value = '';
  stagedChoice.value = '';
  message.value = '';
  isPaused.value = false;
  if (resetTimer) clearTimeout(resetTimer);
};

const showError = (msg) => {
  if (status.value === 'error' && message.value === msg) return;
  
  const prevStatus = status.value;
  status.value = 'error';
  message.value = msg;
  
  setTimeout(() => {
    if (status.value === 'error') {
      status.value = prevStatus;
      message.value = '';
    }
  }, 2000);
};

onUnmounted(() => {
  if (resetTimer) clearTimeout(resetTimer);
});
</script>

<template>
  <RoleLayout title="Smart Assessment Station">
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      
      <!-- Left side: Camera -->
      <AppCard title="Live Scanner" no-padding>
        <div class="h-96 md:h-[32rem] relative bg-black rounded-b-lg overflow-hidden">
          <QrReader 
            @decode="handleDecode" 
            @init="onQrInit"
            @error="(e) => console.log('QR err', e)"
            :paused="isPaused"
          />
          
          <!-- Face detection integration -->
          <FaceGuard 
            :video-element="videoElement"
            :active="!isPaused"
            @face-detected="isFaceDetected = true"
            @face-lost="isFaceDetected = false"
            class="absolute top-4 left-4 z-10"
          />
          
          <!-- Visual overlay for face detection status -->
          <div class="absolute top-4 right-4 z-10 flex items-center px-3 py-1 rounded-full text-xs font-bold transition-colors shadow"
               :class="isFaceDetected ? 'bg-green-500 text-white' : 'bg-yellow-500 text-white'">
            <svg v-if="isFaceDetected" class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <svg v-else class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            {{ isFaceDetected ? 'Face Detected' : 'No Face' }}
          </div>
        </div>
      </AppCard>

      <!-- Right side: Feedback -->
      <AppCard title="Status Display">
        <div class="h-96 md:h-[30rem]">
          <ResultDisplay 
            :student-id="studentId"
            :student-name="studentId ? `Student #${studentId}` : ''"
            :staged-choice="stagedChoice"
            :status="status"
            :message="message"
          />
        </div>
      </AppCard>

    </div>
  </RoleLayout>
</template>
