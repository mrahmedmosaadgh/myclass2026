<template>
  <div class="student-lesson-list q-pa-md">
    <!-- Header with Progress -->
    <div class="row items-center q-mb-lg">
      <div class="col">
        <h4 class="text-h4 q-my-none">My Lessons</h4>
        <p class="text-grey-7 q-mb-none">{{ subject?.name }} - Grade {{ grade?.name }}</p>
      </div>
      <div class="col-auto">
        <div class="progress-card">
          <div class="text-h6">{{ progressPercentage }}%</div>
          <div class="text-caption text-grey-7">Progress</div>
          <q-linear-progress :value="progressPercentage / 100" color="primary" class="q-mt-sm" />
        </div>
      </div>
    </div>

    <!-- Lessons Grid -->
    <div class="row q-col-gutter-md">
      <div
        v-for="(lesson, index) in lessons"
        :key="lesson.id"
        class="col-12 col-sm-6 col-md-4"
      >
        <q-card
          :class="getLessonCardClass(lesson)"
          class="lesson-card cursor-pointer"
          @click="handleLessonClick(lesson)"
        >
          <!-- Lock Overlay for Locked Lessons -->
          <div v-if="lesson.progress?.status === 'locked'" class="lock-overlay">
            <q-icon name="lock" size="48px" color="grey-5" />
            <div class="text-caption text-grey-6 q-mt-sm">Waiting for teacher</div>
          </div>

          <q-card-section>
            <div class="row items-center q-mb-sm">
              <div class="col">
                <div class="text-overline text-grey-7">Lesson {{ lesson.order || index + 1 }}</div>
              </div>
              <div class="col-auto">
                <q-badge :color="getStatusBadgeColor(lesson)" :label="getStatusLabel(lesson)" />
              </div>
            </div>

            <div class="text-h6 q-mb-sm">{{ lesson.name }}</div>
            <div class="text-caption text-grey-7 line-clamp-2">{{ lesson.description }}</div>

            <!-- Progress Indicators -->
            <div class="progress-indicators q-mt-md">
              <div class="row q-gutter-xs">
                <!-- Learn Stage -->
                <div class="col">
                  <div class="stage-indicator" :class="getStageClass(lesson, 'learn')">
                    <q-icon name="school" size="sm" />
                    <div class="text-caption">Learn</div>
                  </div>
                </div>

                <!-- Practice Stage -->
                <div class="col">
                  <div class="stage-indicator" :class="getStageClass(lesson, 'practice')">
                    <q-icon name="edit_note" size="sm" />
                    <div class="text-caption">Practice</div>
                  </div>
                </div>

                <!-- Quiz Stage -->
                <div class="col">
                  <div class="stage-indicator" :class="getStageClass(lesson, 'quiz')">
                    <q-icon name="quiz" size="sm" />
                    <div class="text-caption">Quiz</div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Action Button -->
            <div class="q-mt-md">
              <q-btn
                :label="getActionButtonLabel(lesson)"
                :color="getActionButtonColor(lesson)"
                :icon="getActionButtonIcon(lesson)"
                :disable="!canAccessLesson(lesson)"
                unelevated
                no-caps
                class="full-width"
                @click.stop="handleActionClick(lesson)"
              />
            </div>

            <!-- Additional Info -->
            <div v-if="lesson.progress" class="q-mt-sm">
              <div v-if="lesson.progress.practice_score !== null" class="text-caption text-grey-7">
                Practice Score: {{ lesson.progress.practice_score }}/10
              </div>
              <div v-if="lesson.progress.quiz_attempts > 0" class="text-caption text-grey-7">
                Quiz Attempts: {{ lesson.progress.quiz_attempts }}
                <span v-if="lesson.progress.quiz_best_score !== null">
                  (Best: {{ lesson.progress.quiz_best_score }}%)
                </span>
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="lessons.length === 0" class="text-center q-pa-xl">
      <q-icon name="school" size="64px" color="grey-5" />
      <div class="text-h6 text-grey-7 q-mt-md">No lessons available yet</div>
      <div class="text-caption text-grey-6">Your teacher will open lessons for you soon</div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import { router } from '@inertiajs/vue3';

const props = defineProps({
  studentId: {
    type: Number,
    required: true
  },
  gradeId: {
    type: Number,
    required: true
  },
  subjectId: {
    type: Number,
    required: true
  }
});

const lessons = ref([]);
const subject = ref(null);
const grade = ref(null);
const isLoading = ref(false);

const progressPercentage = computed(() => {
  const openedLessons = lessons.value.filter(l => l.progress?.opened_at);
  if (openedLessons.length === 0) return 0;
  
  const completedLessons = openedLessons.filter(l => 
    l.progress?.status === 'completed' || l.progress?.force_passed
  );
  
  return Math.round((completedLessons.length / openedLessons.length) * 100);
});

// Fetch lessons and progress
const fetchLessons = async () => {
  isLoading.value = true;
  try {
    // Fetch student progress
    const progressResponse = await axios.get(
      route('lesson-presentation.progress.student', { studentId: props.studentId })
    );
    
    const progressMap = {};
    progressResponse.data.forEach(p => {
      progressMap[p.lesson_presentation_id] = p;
    });

    // Fetch lessons for this grade/subject
    const lessonsResponse = await axios.get(route('lesson-presentation.list'), {
      params: {
        grade_id: props.gradeId,
        subject_id: props.subjectId
      }
    });

    // Merge progress data with lessons
    lessons.value = lessonsResponse.data.map(lesson => ({
      ...lesson,
      progress: progressMap[lesson.id] || { status: 'locked', color_status: 'gray' }
    })).sort((a, b) => (a.order || 0) - (b.order || 0));

  } catch (error) {
    console.error('Failed to fetch lessons:', error);
  } finally {
    isLoading.value = false;
  }
};

// Color mapping
const colorClasses = {
  gray: 'card-gray',
  light_blue: 'card-light-blue',
  blue: 'card-blue',
  purple: 'card-purple',
  green: 'card-green',
  yellow: 'card-yellow',
  dark_yellow: 'card-dark-yellow',
  orange: 'card-orange',
  red: 'card-red'
};

const getLessonCardClass = (lesson) => {
  return colorClasses[lesson.progress?.color_status] || 'card-gray';
};

const getStatusLabel = (lesson) => {
  const status = lesson.progress?.status;
  if (!status || status === 'locked') return 'Locked';
  if (status === 'opened') return 'Ready';
  if (status === 'learning') return 'In Progress';
  if (status === 'practice_pending') return 'Practice';
  if (status === 'practice_submitted') return 'Waiting';
  if (status === 'quiz_unlocked') return 'Quiz Ready';
  if (status === 'completed') return 'Completed';
  if (status === 'failed') return 'Failed';
  return status;
};

const getStatusBadgeColor = (lesson) => {
  const colorStatus = lesson.progress?.color_status;
  if (colorStatus === 'green') return 'positive';
  if (colorStatus === 'yellow' || colorStatus === 'dark_yellow') return 'warning';
  if (colorStatus === 'red') return 'negative';
  if (colorStatus === 'purple') return 'purple';
  if (colorStatus === 'blue' || colorStatus === 'light_blue') return 'info';
  return 'grey';
};

const getStageClass = (lesson, stage) => {
  const progress = lesson.progress;
  if (!progress) return 'stage-locked';

  if (stage === 'learn') {
    if (progress.learn_completed_at) return 'stage-completed';
    if (progress.status === 'learning' || progress.status === 'opened') return 'stage-active';
    return 'stage-locked';
  }

  if (stage === 'practice') {
    if (progress.practice_score !== null) return 'stage-completed';
    if (progress.status === 'practice_pending' || progress.status === 'practice_submitted') return 'stage-active';
    if (progress.learn_completed_at) return 'stage-available';
    return 'stage-locked';
  }

  if (stage === 'quiz') {
    if (progress.quiz_passed) return 'stage-completed';
    if (progress.status === 'quiz_unlocked') return 'stage-active';
    if (progress.practice_score >= 6) return 'stage-available';
    return 'stage-locked';
  }

  return 'stage-locked';
};

const canAccessLesson = (lesson) => {
  return lesson.progress?.status !== 'locked';
};

const getActionButtonLabel = (lesson) => {
  const status = lesson.progress?.status;
  if (!status || status === 'locked') return 'Locked';
  if (status === 'opened') return 'Start Learning';
  if (status === 'learning') return 'Continue Learning';
  if (status === 'practice_pending') return 'Submit Practice';
  if (status === 'practice_submitted') return 'Waiting for Teacher';
  if (status === 'quiz_unlocked') return 'Take Quiz';
  if (status === 'completed') return 'Review';
  if (status === 'failed') return 'Contact Teacher';
  return 'Open';
};

const getActionButtonColor = (lesson) => {
  const status = lesson.progress?.status;
  if (status === 'completed') return 'positive';
  if (status === 'failed') return 'negative';
  if (status === 'practice_submitted') return 'grey';
  return 'primary';
};

const getActionButtonIcon = (lesson) => {
  const status = lesson.progress?.status;
  if (!status || status === 'locked') return 'lock';
  if (status === 'opened' || status === 'learning') return 'play_arrow';
  if (status === 'practice_pending') return 'edit_note';
  if (status === 'practice_submitted') return 'hourglass_empty';
  if (status === 'quiz_unlocked') return 'quiz';
  if (status === 'completed') return 'check_circle';
  if (status === 'failed') return 'error';
  return 'arrow_forward';
};

const handleLessonClick = (lesson) => {
  if (canAccessLesson(lesson)) {
    handleActionClick(lesson);
  }
};

const handleActionClick = (lesson) => {
  if (!canAccessLesson(lesson)) return;

  // Navigate to lesson view using Inertia
  router.visit(route('lesson-presentation.student.view', { id: lesson.id }));
};

onMounted(() => {
  fetchLessons();
});
</script>

<style scoped>
.student-lesson-list {
  max-width: 1200px;
  margin: 0 auto;
}

.progress-card {
  background: white;
  padding: 16px 24px;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  min-width: 200px;
  text-align: center;
}

.lesson-card {
  position: relative;
  transition: transform 0.2s, box-shadow 0.2s;
  height: 100%;
}

.lesson-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.lock-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(255, 255, 255, 0.9);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  z-index: 10;
  border-radius: 4px;
}

/* Color Classes */
.card-gray { border-left: 4px solid #9E9E9E; background: #FAFAFA; }
.card-light-blue { border-left: 4px solid #03A9F4; background: #E1F5FE; }
.card-blue { border-left: 4px solid #2196F3; background: #BBDEFB; }
.card-purple { border-left: 4px solid #9C27B0; background: #E1BEE7; }
.card-green { border-left: 4px solid #4CAF50; background: #C8E6C9; }
.card-yellow { border-left: 4px solid #FFEB3B; background: #FFF9C4; }
.card-dark-yellow { border-left: 4px solid #FFC107; background: #FFE082; }
.card-orange { border-left: 4px solid #FF9800; background: #FFE0B2; }
.card-red { border-left: 4px solid #F44336; background: #FFCDD2; }

/* Stage Indicators */
.stage-indicator {
  padding: 8px;
  border-radius: 4px;
  text-align: center;
  transition: all 0.2s;
}

.stage-locked {
  background: #F5F5F5;
  color: #9E9E9E;
}

.stage-available {
  background: #E3F2FD;
  color: #1976D2;
}

.stage-active {
  background: #2196F3;
  color: white;
  font-weight: 500;
}

.stage-completed {
  background: #4CAF50;
  color: white;
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
