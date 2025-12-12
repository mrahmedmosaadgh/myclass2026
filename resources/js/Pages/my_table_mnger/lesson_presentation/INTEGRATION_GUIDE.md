# StudentLessonView.vue Integration Guide

## What to Add

### 1. Import PracticeSubmission Component

Add to imports section (around line 182):
```javascript
import PracticeSubmission from './components/PracticeSubmission.vue';
import { useQuasar } from 'quasar';
```

### 2. Add State Variables

Add after line 196:
```javascript
const $q = useQuasar();
const studentId = ref(1); // Get from auth/props
const lessonProgress = ref(null);
const showPracticeDialog = ref(false);
const isCompletingLearn = ref(false);
```

### 3. Fetch Lesson Progress

Add to `fetchLesson` function (after line 213):
```javascript
// Fetch student progress for this lesson
try {
  const progressResponse = await axios.get(
    route('lesson-presentation.progress.student', { studentId: studentId.value })
  );
  
  const progress = progressResponse.data.find(p => p.lesson_presentation_id == props.presentationId);
  lessonProgress.value = progress || null;
} catch (error) {
  console.error('Failed to load progress:', error);
}
```

### 4. Add Complete Learn Button

Replace the "Next" button section (lines 167-173) with:
```vue
<!-- Show Complete Learn button on last slide if learn not completed -->
<q-btn
  v-if="currentSlideIndex === slides.length - 1 && !lessonProgress?.learn_completed_at"
  unelevated
  label="Complete Learn Stage"
  color="positive"
  icon="check_circle"
  @click="completeLearn"
  :loading="isCompletingLearn"
  class="px-6 py-2"
/>

<!-- Show Practice button if learn completed but practice not submitted -->
<q-btn
  v-else-if="lessonProgress?.status === 'practice_pending'"
  unelevated
  label="Submit Practice"
  color="primary"
  icon="edit_note"
  @click="showPracticeDialog = true"
  class="px-6 py-2"
/>

<!-- Show waiting message if practice submitted -->
<div v-else-if="lessonProgress?.status === 'practice_submitted'" class="text-center">
  <q-icon name="hourglass_empty" size="md" color="purple" />
  <div class="text-caption text-grey-7 q-mt-sm">Waiting for teacher to grade your practice</div>
  <div v-if="lessonProgress?.practice_data?.teacher_feedback" class="text-caption text-grey-8 q-mt-xs">
    Feedback: {{ lessonProgress.practice_data.teacher_feedback }}
  </div>
</div>

<!-- Show quiz button if unlocked -->
<q-btn
  v-else-if="lessonProgress?.status === 'quiz_unlocked'"
  unelevated
  label="Take Quiz"
  color="primary"
  icon="quiz"
  @click="startQuiz"
  class="px-6 py-2"
/>

<!-- Regular Next button -->
<q-btn
  v-else
  @click="nextSlide"
  :disabled="currentSlideIndex === slides.length - 1"
  unelevated
  label="Next"
  color="primary"
  icon-right="arrow_forward"
  class="px-6 py-2"
/>
```

### 5. Add Practice Dialog

Add before closing `</template>` tag (before line 178):
```vue
<!-- Practice Submission Dialog -->
<q-dialog v-model="showPracticeDialog" persistent>
  <PracticeSubmission
    v-if="lessonProgress"
    :progress-id="lessonProgress.id"
    @submitted="handlePracticeSubmitted"
    @close="showPracticeDialog = false"
  />
</q-dialog>
```

### 6. Add Methods

Add these methods to the script section:
```javascript
const completeLearn = async () => {
  if (!lessonProgress.value) {
    $q.notify({
      type: 'negative',
      message: 'Progress not found. Please refresh the page.',
      position: 'top'
    });
    return;
  }

  isCompletingLearn.value = true;
  try {
    const response = await axios.put(
      route('lesson-presentation.progress.complete-learn', { id: lessonProgress.value.id })
    );
    
    lessonProgress.value = response.data.progress;
    
    $q.notify({
      type: 'positive',
      message: 'Learn stage completed! You can now submit your practice.',
      position: 'top'
    });
    
    // Show practice dialog
    showPracticeDialog.value = true;
  } catch (error) {
    console.error('Failed to complete learn:', error);
    $q.notify({
      type: 'negative',
      message: error.response?.data?.error || 'Failed to complete learn stage',
      position: 'top'
    });
  } finally {
    isCompletingLearn.value = false;
  }
};

const handlePracticeSubmitted = (data) => {
  lessonProgress.value = data.progress;
  showPracticeDialog.value = false;
  
  $q.notify({
    type: 'positive',
    message: 'Practice submitted! Your teacher will grade it soon.',
    position: 'top',
    timeout: 3000
  });
};

const startQuiz = () => {
  // Navigate to quiz (will be implemented with quiz system)
  $q.notify({
    type: 'info',
    message: 'Quiz system will be integrated later',
    position: 'top'
  });
};
```

## Summary

This integration adds:
1. ✅ Progress tracking for the lesson
2. ✅ "Complete Learn" button on last slide
3. ✅ Practice submission dialog
4. ✅ Status-based UI (waiting for teacher, quiz ready, etc.)
5. ✅ Notifications for user feedback

The student flow becomes:
1. View slides → Complete Learn
2. Submit Practice (upload or draw)
3. Wait for teacher grading
4. Take Quiz (when unlocked)
