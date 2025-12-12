<template>
  <div class="teacher-dashboard q-pa-md">
    <div class="row items-center q-mb-lg">
      <div class="col">
        <h4 class="text-h4 q-my-none">Student Progress Dashboard</h4>
        <p class="text-grey-7 q-mb-none">{{ lesson?.name }}</p>
      </div>
      <div class="col-auto">
        <q-btn-group outline>
          <q-btn label="Open for All" icon="lock_open" color="positive" @click="openForAll" />
          <q-btn label="Refresh" icon="refresh" @click="fetchProgress" />
        </q-btn-group>
      </div>
    </div>

    <!-- Progress Table -->
    <q-table
      :rows="students"
      :columns="columns"
      row-key="id"
      :loading="loading"
      flat
      bordered
      class="shadow-2"
    >
      <!-- Status Column -->
      <template v-slot:body-cell-status="props">
        <q-td :props="props">
          <q-badge :color="getStatusColor(props.row.progress)" :label="getStatusLabel(props.row.progress)" />
        </q-td>
      </template>

      <!-- Color Column -->
      <template v-slot:body-cell-color="props">
        <q-td :props="props">
          <div class="row items-center q-gutter-xs">
            <div
              class="color-indicator"
              :style="{ backgroundColor: getColorHex(props.row.progress?.color_status) }"
            ></div>
            <span class="text-caption">{{ props.row.progress?.color_status || 'gray' }}</span>
          </div>
        </q-td>
      </template>

      <!-- Learn Column -->
      <template v-slot:body-cell-learn="props">
        <q-td :props="props">
          <q-icon
            :name="props.row.progress?.learn_completed_at ? 'check_circle' : 'radio_button_unchecked'"
            :color="props.row.progress?.learn_completed_at ? 'positive' : 'grey'"
            size="sm"
          />
        </q-td>
      </template>

      <!-- Practice Column -->
      <template v-slot:body-cell-practice="props">
        <q-td :props="props">
          <div v-if="props.row.progress?.practice_score !== null">
            <q-badge :color="props.row.progress.practice_score >= 6 ? 'positive' : 'negative'">
              {{ props.row.progress.practice_score }}/10
            </q-badge>
          </div>
          <div v-else-if="props.row.progress?.status === 'practice_submitted'">
            <q-btn
              size="sm"
              color="primary"
              label="Grade"
              @click="openGradingDialog(props.row)"
            />
          </div>
          <span v-else class="text-grey-5">-</span>
        </q-td>
      </template>

      <!-- Quiz Column -->
      <template v-slot:body-cell-quiz="props">
        <q-td :props="props">
          <div v-if="props.row.progress?.quiz_passed">
            <q-icon name="check_circle" color="positive" size="sm" />
            <span class="text-caption q-ml-xs">{{ props.row.progress.quiz_best_score }}%</span>
          </div>
          <div v-else-if="props.row.progress?.quiz_attempts > 0">
            <span class="text-caption">{{ props.row.progress.quiz_attempts }} attempts</span>
            <br />
            <span class="text-caption text-grey-7">Best: {{ props.row.progress.quiz_best_score }}%</span>
          </div>
          <span v-else class="text-grey-5">-</span>
        </q-td>
      </template>

      <!-- Actions Column -->
      <template v-slot:body-cell-actions="props">
        <q-td :props="props">
          <q-btn-dropdown size="sm" color="primary" label="Actions" dense>
            <q-list>
              <!-- Open/Lock -->
              <q-item
                v-if="!props.row.progress || props.row.progress.status === 'locked'"
                clickable
                v-close-popup
                @click="openLesson(props.row)"
              >
                <q-item-section avatar>
                  <q-icon name="lock_open" color="positive" />
                </q-item-section>
                <q-item-section>Open Lesson</q-item-section>
              </q-item>

              <q-item
                v-else
                clickable
                v-close-popup
                @click="lockLesson(props.row)"
              >
                <q-item-section avatar>
                  <q-icon name="lock" color="negative" />
                </q-item-section>
                <q-item-section>Lock Lesson</q-item-section>
              </q-item>

              <q-separator />

              <!-- View Practice -->
              <q-item
                v-if="props.row.progress?.practice_submitted_at"
                clickable
                v-close-popup
                @click="viewPractice(props.row)"
              >
                <q-item-section avatar>
                  <q-icon name="visibility" color="info" />
                </q-item-section>
                <q-item-section>View Practice</q-item-section>
              </q-item>

              <!-- Grant Extra Attempt -->
              <q-item
                v-if="props.row.progress?.quiz_attempts >= 3 && !props.row.progress?.quiz_passed"
                clickable
                v-close-popup
                @click="grantAttempt(props.row)"
              >
                <q-item-section avatar>
                  <q-icon name="add_circle" color="warning" />
                </q-item-section>
                <q-item-section>Grant Extra Attempt</q-item-section>
              </q-item>

              <!-- Force Pass -->
              <q-item
                v-if="props.row.progress?.status === 'failed'"
                clickable
                v-close-popup
                @click="forcePass(props.row)"
              >
                <q-item-section avatar>
                  <q-icon name="done_all" color="positive" />
                </q-item-section>
                <q-item-section>Force Pass</q-item-section>
              </q-item>

              <q-separator />

              <!-- Reset -->
              <q-item
                v-if="props.row.progress"
                clickable
                v-close-popup
                @click="resetProgress(props.row)"
              >
                <q-item-section avatar>
                  <q-icon name="restart_alt" color="negative" />
                </q-item-section>
                <q-item-section>Reset Progress</q-item-section>
              </q-item>
            </q-list>
          </q-btn-dropdown>
        </q-td>
      </template>
    </q-table>

    <!-- Grading Dialog -->
    <q-dialog v-model="showGradingDialog" persistent>
      <q-card style="min-width: 600px">
        <q-card-section>
          <div class="text-h6">Grade Practice Submission</div>
          <div class="text-caption text-grey-7">Student: {{ selectedStudent?.name }}</div>
        </q-card-section>

        <q-card-section>
          <!-- Practice Submission View -->
          <div v-if="practiceSubmission" class="q-mb-md">
            <div class="text-subtitle2 q-mb-sm">Submission:</div>
            <div v-if="practiceSubmission.submission_type === 'upload'" class="submission-preview">
              <img :src="practiceSubmission.file_url" alt="Practice submission" style="max-width: 100%; max-height: 400px;" />
            </div>
            <div v-else class="submission-preview">
              <img :src="practiceSubmission.drawing_data" alt="Drawing submission" style="max-width: 100%; max-height: 400px;" />
            </div>
          </div>

          <!-- Score Input -->
          <q-input
            v-model.number="gradeScore"
            type="number"
            label="Score (0-10)"
            :min="0"
            :max="10"
            outlined
            class="q-mb-md"
          >
            <template v-slot:append>
              <span class="text-caption">/10</span>
            </template>
          </q-input>

          <!-- Feedback -->
          <q-input
            v-model="gradeFeedback"
            type="textarea"
            label="Feedback (optional)"
            rows="3"
            outlined
            maxlength="500"
            counter
          />
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Cancel" color="grey" v-close-popup />
          <q-btn
            unelevated
            label="Submit Grade"
            color="primary"
            @click="submitGrade"
            :disable="gradeScore === null || gradeScore < 0 || gradeScore > 10"
            :loading="isGrading"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useQuasar } from 'quasar';

const $q = useQuasar();

const props = defineProps({
  lessonId: {
    type: Number,
    required: true
  },
  teacherId: {
    type: Number,
    required: true
  }
});

const lesson = ref(null);
const students = ref([]);
const loading = ref(false);
const showGradingDialog = ref(false);
const selectedStudent = ref(null);
const practiceSubmission = ref(null);
const gradeScore = ref(null);
const gradeFeedback = ref('');
const isGrading = ref(false);

const columns = [
  { name: 'name', label: 'Student Name', field: 'name', align: 'left', sortable: true },
  { name: 'status', label: 'Status', field: 'status', align: 'center' },
  { name: 'color', label: 'Color', field: 'color', align: 'center' },
  { name: 'learn', label: 'Learn', field: 'learn', align: 'center' },
  { name: 'practice', label: 'Practice', field: 'practice', align: 'center' },
  { name: 'quiz', label: 'Quiz', field: 'quiz', align: 'center' },
  { name: 'actions', label: 'Actions', field: 'actions', align: 'center' }
];

const colorMap = {
  gray: '#9E9E9E',
  light_blue: '#03A9F4',
  blue: '#2196F3',
  purple: '#9C27B0',
  green: '#4CAF50',
  yellow: '#FFEB3B',
  dark_yellow: '#FFC107',
  orange: '#FF9800',
  red: '#F44336'
};

const fetchProgress = async () => {
  loading.value = true;
  try {
    // Fetch lesson details
    const lessonResponse = await axios.get(route('lesson-presentation.show', { id: props.lessonId }));
    lesson.value = lessonResponse.data;

    // Fetch progress for all students
    const progressResponse = await axios.get(
      route('lesson-presentation.progress.lesson', { lessonId: props.lessonId })
    );

    // Get all students (you'll need to implement this endpoint or get from classroom)
    // For now, using progress data
    const progressMap = {};
    progressResponse.data.forEach(p => {
      progressMap[p.student_id] = p;
    });

    // Mock students - replace with actual student list
    students.value = progressResponse.data.map(p => ({
      id: p.student_id,
      name: p.student?.name || `Student ${p.student_id}`,
      progress: p
    }));

  } catch (error) {
    console.error('Failed to fetch progress:', error);
    $q.notify({
      type: 'negative',
      message: 'Failed to load progress data',
      position: 'top'
    });
  } finally {
    loading.value = false;
  }
};

const getStatusColor = (progress) => {
  if (!progress) return 'grey';
  if (progress.status === 'completed') return 'positive';
  if (progress.status === 'failed') return 'negative';
  if (progress.status === 'practice_submitted') return 'purple';
  return 'info';
};

const getStatusLabel = (progress) => {
  if (!progress || progress.status === 'locked') return 'Locked';
  return progress.status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
};

const getColorHex = (colorStatus) => {
  return colorMap[colorStatus] || colorMap.gray;
};

const openLesson = async (student) => {
  try {
    await axios.post(route('lesson-presentation.progress.open'), {
      lesson_id: props.lessonId,
      student_ids: [student.id],
      teacher_id: props.teacherId
    });

    $q.notify({
      type: 'positive',
      message: `Lesson opened for ${student.name}`,
      position: 'top'
    });

    fetchProgress();
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'Failed to open lesson',
      position: 'top'
    });
  }
};

const lockLesson = async (student) => {
  try {
    await axios.post(route('lesson-presentation.progress.lock'), {
      progress_id: student.progress.id
    });

    $q.notify({
      type: 'positive',
      message: `Lesson locked for ${student.name}`,
      position: 'top'
    });

    fetchProgress();
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'Failed to lock lesson',
      position: 'top'
    });
  }
};

const openForAll = async () => {
  $q.dialog({
    title: 'Confirm',
    message: 'Open this lesson for all students?',
    cancel: true
  }).onOk(async () => {
    try {
      const studentIds = students.value.map(s => s.id);
      await axios.post(route('lesson-presentation.progress.open'), {
        lesson_id: props.lessonId,
        student_ids: studentIds,
        teacher_id: props.teacherId
      });

      $q.notify({
        type: 'positive',
        message: 'Lesson opened for all students',
        position: 'top'
      });

      fetchProgress();
    } catch (error) {
      $q.notify({
        type: 'negative',
        message: 'Failed to open lesson for all',
        position: 'top'
      });
    }
  });
};

const openGradingDialog = async (student) => {
  selectedStudent.value = student;
  gradeScore.value = null;
  gradeFeedback.value = '';

  try {
    const response = await axios.get(
      route('lesson-presentation.progress.submission', { progressId: student.progress.id })
    );
    practiceSubmission.value = response.data.submission;
    showGradingDialog.value = true;
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'Failed to load practice submission',
      position: 'top'
    });
  }
};

const submitGrade = async () => {
  isGrading.value = true;
  try {
    await axios.put(
      route('lesson-presentation.progress.grade-practice', { id: selectedStudent.value.progress.id }),
      {
        score: gradeScore.value,
        feedback: gradeFeedback.value
      }
    );

    $q.notify({
      type: 'positive',
      message: 'Practice graded successfully',
      position: 'top'
    });

    showGradingDialog.value = false;
    fetchProgress();
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'Failed to submit grade',
      position: 'top'
    });
  } finally {
    isGrading.value = false;
  }
};

const viewPractice = async (student) => {
  openGradingDialog(student);
};

const grantAttempt = async (student) => {
  try {
    await axios.post(route('lesson-presentation.progress.grant-attempt'), {
      progress_id: student.progress.id
    });

    $q.notify({
      type: 'positive',
      message: 'Extra attempt granted',
      position: 'top'
    });

    fetchProgress();
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'Failed to grant attempt',
      position: 'top'
    });
  }
};

const forcePass = async (student) => {
  $q.dialog({
    title: 'Confirm Force Pass',
    message: `Force pass ${student.name}? This will mark the lesson as completed.`,
    cancel: true
  }).onOk(async () => {
    try {
      await axios.post(route('lesson-presentation.progress.force-pass'), {
        progress_id: student.progress.id
      });

      $q.notify({
        type: 'positive',
        message: 'Student force passed',
        position: 'top'
      });

      fetchProgress();
    } catch (error) {
      $q.notify({
        type: 'negative',
        message: 'Failed to force pass',
        position: 'top'
      });
    }
  });
};

const resetProgress = async (student) => {
  $q.dialog({
    title: 'Confirm Reset',
    message: `Reset all progress for ${student.name}? This cannot be undone.`,
    cancel: true
  }).onOk(async () => {
    try {
      await axios.post(route('lesson-presentation.progress.reset'), {
        progress_id: student.progress.id
      });

      $q.notify({
        type: 'positive',
        message: 'Progress reset successfully',
        position: 'top'
      });

      fetchProgress();
    } catch (error) {
      $q.notify({
        type: 'negative',
        message: 'Failed to reset progress',
        position: 'top'
      });
    }
  });
};

onMounted(() => {
  fetchProgress();
});
</script>

<style scoped>
.teacher-dashboard {
  max-width: 1400px;
  margin: 0 auto;
}

.color-indicator {
  width: 20px;
  height: 20px;
  border-radius: 50%;
  border: 2px solid #e0e0e0;
}

.submission-preview {
  border: 1px solid #e0e0e0;
  border-radius: 4px;
  padding: 12px;
  background: #f5f5f5;
  text-align: center;
}
</style>
