<template>
  <q-card class="answer-card q-mb-lg shadow-3" bordered>
    <!-- Answer Header -->
    <q-card-section class="q-pb-none">
      <div class="row items-center">
        <div class="col">
          <div class="row items-center q-gutter-sm">
            <q-avatar size="36px" color="primary" text-color="white">
              {{ answer.user?.name?.charAt(0)?.toUpperCase() || 'U' }}
            </q-avatar>
            <div>
              <div class="text-subtitle2">{{ answer.user?.name || `User ${answer.user_id}` }}</div>
              <div class="text-caption text-grey-6">{{ formatDate(answer.created_at) }}</div>
            </div>
            <q-chip
              :color="getStatusColor(answer.status)"
              :icon="getStatusIcon(answer.status)"
              :label="answer.status || 'draft'"
              size="sm"
              class="q-ml-sm"
            />
          </div>
        </div>
        
        <!-- Answer Actions -->
        <div class="col-auto">
          <q-btn-group flat>
            <q-btn
              icon="edit"
              size="sm"
              flat
              color="primary"
              @click="$emit('edit', answer)"
            >
              <q-tooltip>Edit Answer</q-tooltip>
            </q-btn>
            <q-btn
              icon="delete"
              size="sm"
              flat
              color="negative"
              @click="$emit('delete', answer.id)"
            >
              <q-tooltip>Delete Answer</q-tooltip>
            </q-btn>
            <q-btn
              icon="more_vert"
              size="sm"
              flat
              color="grey-7"
            >
              <q-tooltip>More Options</q-tooltip>
              <q-menu>
                <q-list style="min-width: 150px">
                  <q-item clickable v-close-popup @click="reportAnswer">
                    <q-item-section avatar>
                      <q-icon name="flag" />
                    </q-item-section>
                    <q-item-section>Report</q-item-section>
                  </q-item>
                  <q-item clickable v-close-popup @click="shareAnswer">
                    <q-item-section avatar>
                      <q-icon name="share" />
                    </q-item-section>
                    <q-item-section>Share</q-item-section>
                  </q-item>
                </q-list>
              </q-menu>
            </q-btn>
          </q-btn-group>
        </div>
      </div>
    </q-card-section>

    <!-- Answer Content -->
    <q-card-section>
      <div class="answer-text q-mb-md">
        <div class="row items-start q-gutter-sm">
          <div class="col text-body1">
            {{ answer.answer_text }}
          </div>
          <div class="col-auto">
            <TextToSpeechButton
              :text="answer.answer_text"
              size="xs"
              round
              :dense="false"
            />
          </div>
        </div>
      </div>

      <!-- Media Links -->
      <div v-if="answer.media_links && answer.media_links.length > 0" class="q-mb-md">
        <div class="text-subtitle2 q-mb-sm">
          <q-icon name="link" class="q-mr-xs" />
          Media Links
        </div>
        <div class="row q-gutter-sm">
          <q-chip
            v-for="(link, index) in answer.media_links"
            :key="index"
            clickable
            color="blue-1"
            text-color="blue-8"
            icon="open_in_new"
            @click="openLink(link)"
          >
            {{ truncateUrl(link) }}
          </q-chip>
        </div>
      </div>

      <!-- Attachments -->
      <div v-if="answer.attachments && answer.attachments.length > 0" class="q-mb-md">
        <div class="text-subtitle2 q-mb-sm">
          <q-icon name="attach_file" class="q-mr-xs" />
          Attachments
        </div>
        <div class="row q-gutter-sm">
          <q-chip
            v-for="(file, index) in answer.attachments"
            :key="index"
            clickable
            color="green-1"
            text-color="green-8"
            :icon="getFileIcon(file.type)"
            @click="downloadFile(file)"
          >
            {{ file.name }}
            <q-tooltip>{{ formatFileSize(file.size) }}</q-tooltip>
          </q-chip>
        </div>
      </div>

      <!-- Voice Note -->
      <div v-if="answer.voice_note_path || answer.voice_note_url" class="q-mb-md">
        <q-card flat bordered class="bg-blue-1">
          <q-card-section class="q-pa-md">
            <div class="text-subtitle2 q-mb-sm text-blue-8">
              <q-icon name="mic" class="q-mr-xs" />
              Voice Note
              <q-chip
                size="sm"
                color="blue-3"
                text-color="blue-8"
                class="q-ml-sm"
              >
                {{ formatFileSize2(answer.voice_note_size) }}
              </q-chip>
            </div>
            <VoicePlayer :audio-url="answer.voice_note_url || answer.voice_note_path" />
          </q-card-section>
        </q-card>
      </div>

      <!-- Voice Note Debug Info (remove in production) -->
      <div v-if="$q.dev" class="q-mb-md">
        <q-expansion-item
          icon="bug_report"
          label="Debug: Voice Note Data"
          header-class="text-caption text-grey-6"
        >
          <q-card flat bordered class="bg-grey-1">
            <q-card-section class="q-pa-sm">
              <pre class="text-caption">{{ JSON.stringify({
                voice_note_path: answer.voice_note_path,
                voice_note_url: answer.voice_note_url,
                voice_note_size: answer.voice_note_size
              }, null, 2) }}</pre>
            </q-card-section>
          </q-card>
        </q-expansion-item>
      </div>

      <!-- Notes -->
      <div v-if="answer.notes" class="q-mb-md">
        <q-expansion-item
          icon="note"
          label="Notes"
          header-class="text-subtitle2"
        >
          <q-card flat class="bg-grey-1 q-pa-md">
            {{ answer.notes }}
          </q-card>
        </q-expansion-item>
      </div>
    </q-card-section>

    <!-- Rating and Interaction Section -->
    <q-card-section class="q-pt-none">
      <q-separator class="q-mb-md" />
      
      <div class="row items-center justify-between">
        <!-- Rating -->
        <div class="col-auto">
          <div class="row items-center q-gutter-sm">
            <span class="text-body2">Rate this answer:</span>
            <q-rating
              v-model="currentRating"
              size="sm"
              color="amber"
              icon="star"
              @update:model-value="handleRating"
            />
            <span class="text-caption text-grey-6">
              ({{ answer.average_rating || 0 }}/5 from {{ answer.ratings_count || 0 }} votes)
            </span>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="col-auto">
          <q-btn-group flat>
            <q-btn
              icon="thumb_up"
              size="sm"
              flat
              :color="userLiked ? 'positive' : 'grey-7'"
              @click="toggleLike"
            >
              {{ answer.likes_count || 0 }}
              <q-tooltip>{{ userLiked ? 'Unlike' : 'Like' }}</q-tooltip>
            </q-btn>
            <q-btn
              icon="comment"
              size="sm"
              flat
              color="grey-7"
              @click="openCommentsDialog"
            >
              {{ answer.comments_count || 0 }}
              <q-tooltip>View Comments</q-tooltip>
            </q-btn>
            <q-btn
              icon="mic"
              size="sm"
              flat
              color="red-6"
              @click="showVoiceRecorder"
            >
              <q-tooltip>Add Voice Note</q-tooltip>
            </q-btn>
          </q-btn-group>
        </div>

        <!-- Edit/Delete Actions -->
        <div class="col-auto">
          <q-btn-group flat>
            <q-btn
              icon="edit"
              size="sm"
              flat
              color="blue-6"
              @click="emit('edit', answer)"
            >
              <q-tooltip>Edit Answer</q-tooltip>
            </q-btn>
            <q-btn
              icon="delete"
              size="sm"
              flat
              color="red-6"
              @click="emit('delete', answer)"
            >
              <q-tooltip>Delete Answer</q-tooltip>
            </q-btn>
          </q-btn-group>
        </div>
      </div>
    </q-card-section>

    <!-- Comments Dialog -->
    <q-dialog v-model="commentsDialogVisible" maximized transition-show="slide-up" transition-hide="slide-down">
      <q-card class="column bg-grey-1">
        <!-- Header -->
        <q-card-section class="row items-center q-pb-none bg-white shadow-1">
          <div class="column">
            <div class="text-h6 text-primary">
              <q-icon name="comment" class="q-mr-sm" />
              Comments
            </div>
            <div class="text-caption text-grey-6">
              {{ answer.comments_count || 0 }} comment{{ (answer.comments_count || 0) !== 1 ? 's' : '' }}
            </div>
          </div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup color="grey-7">
            <q-tooltip>Close</q-tooltip>
          </q-btn>
        </q-card-section>

        <!-- Question Context -->
        <q-card-section class="q-py-sm bg-blue-1 border-bottom">
          <div class="text-body2 text-blue-8">
            <q-icon name="help_outline" class="q-mr-xs" />
            <strong>Question:</strong> {{ question.question_text || question.title }}
          </div>
          <div class="text-body2 text-grey-7 q-mt-xs">
            <q-icon name="person" class="q-mr-xs" />
            <strong>Answer by:</strong> {{ answer.user?.name || 'Unknown User' }}
          </div>
        </q-card-section>

        <!-- Comments Content -->
        <q-card-section class="col q-pt-md bg-white" style="overflow-y: auto;">
          <CommentsSection
            :answer-id="answer.id"
            :comments="comments"
            @comment-added="onCommentChanged"
            @comment-updated="onCommentChanged"
            @comment-deleted="onCommentChanged"
          />
        </q-card-section>
      </q-card>
    </q-dialog>

    <!-- Voice Recorder Dialog -->
    <VoiceRecorderDialog
      v-model="voiceRecorderVisible"
      @voice-recorded="handleVoiceNote"
    />
  </q-card>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useQuasar } from 'quasar';
import CommentsSection from './CommentsSection.vue';
import VoicePlayer from './VoicePlayer.vue';
import VoiceRecorderDialog from './VoiceRecorderDialog.vue';
import TextToSpeechButton from './components/TextToSpeechButton.vue';
import resumeApi from './resumeApi.js';

// Props and Emits
const props = defineProps({
  answer: {
    type: Object,
    required: true
  },
  question: {
    type: Object,
    required: true
  }
});

const emit = defineEmits(['edit', 'delete', 'rate', 'comment', 'refresh']);

// Composables
const $q = useQuasar();

// State
const currentRating = ref(0);
const userLiked = ref(false);
const commentsDialogVisible = ref(false);
const comments = ref([]);
const voiceRecorderVisible = ref(false);

// Methods
const handleRating = (rating) => {
  resumeApi.rateAnswer(props.answer.id, rating)
    .then(response => {
      // Update the answer rating data
      if (props.answer.average_rating !== undefined) {
        props.answer.average_rating = response.average_rating;
      }
      if (props.answer.ratings_count !== undefined) {
        props.answer.ratings_count = response.ratings_count;
      }

      emit('rate', props.answer.id, rating);
      $q.notify({
        type: 'positive',
        message: `Rated ${rating} stars`,
        position: 'top'
      });
    })
    .catch(error => {
      console.error('Error submitting rating:', error);
      // Reset the rating on error
      currentRating.value = props.answer.user_rating || 0;
      $q.notify({
        type: 'negative',
        message: 'Failed to submit rating',
        position: 'top'
      });
    });
};

const toggleLike = () => {
  resumeApi.toggleAnswerLike(props.answer.id)
    .then(response => {
      userLiked.value = response.liked;
      // Update the answer likes count
      if (props.answer.likes_count !== undefined) {
        props.answer.likes_count = response.likes_count;
      }
      $q.notify({
        type: 'positive',
        message: response.liked ? 'Liked!' : 'Unliked',
        position: 'top'
      });
    })
    .catch(error => {
      console.error('Error toggling like:', error);
      $q.notify({
        type: 'negative',
        message: 'Failed to update like',
        position: 'top'
      });
    });
};

const openCommentsDialog = () => {
  commentsDialogVisible.value = true;
  loadComments();
};

const loadComments = () => {
  console.log('Loading comments for answer:', props.answer.id);

  resumeApi.getComments(props.answer.id)
    .then(response => {
      comments.value = response;
      console.log('Comments loaded:', response);
    })
    .catch(error => {
      console.error('Error loading comments:', error);
      $q.notify({
        type: 'negative',
        message: 'Failed to load comments',
        position: 'top'
      });
    });
};

const onCommentChanged = () => {
  loadComments();
  // Refresh the answer to get updated comment count
  emit('refresh');
};

const showVoiceRecorder = () => {
  voiceRecorderVisible.value = true;
};

const handleVoiceNote = (audioBlob) => {
  console.log('Voice note recorded:', audioBlob);

  // Upload voice note to server
  resumeApi.uploadVoiceNote(audioBlob, props.answer.id)
    .then(response => {
      console.log('Voice note uploaded successfully:', response);

      // Update the answer with voice note data immediately for instant UI update
      if (response.path) {
        props.answer.voice_note_path = response.path;
        props.answer.voice_note_url = response.url;
        props.answer.voice_note_size = audioBlob.size;
      }

      // Force reactivity update
      emit('refresh');

      $q.notify({
        type: 'positive',
        message: 'Voice note saved successfully!',
        position: 'top',
        icon: 'mic'
      });
    })
    .catch(error => {
      console.error('Error uploading voice note:', error);
      $q.notify({
        type: 'negative',
        message: 'Failed to save voice note. Please try again.',
        position: 'top',
        icon: 'error'
      });
    });
};



const reportAnswer = () => {
  $q.notify({
    type: 'info',
    message: 'Answer reported',
    position: 'top'
  });
};

const shareAnswer = () => {
  // TODO: Implement share functionality
  $q.notify({
    type: 'positive',
    message: 'Share link copied to clipboard',
    position: 'top'
  });
};

const openLink = (url) => {
  window.open(url, '_blank');
};

const downloadFile = (file) => {
  // TODO: Implement file download
  console.log('Download file:', file);
};

const getStatusColor = (status) => {
  const colors = {
    'draft': 'grey-6',
    'published': 'positive',
    'review': 'warning',
    'archived': 'negative'
  };
  return colors[status] || 'grey-6';
};

const getStatusIcon = (status) => {
  const icons = {
    'draft': 'edit',
    'published': 'check_circle',
    'review': 'schedule',
    'archived': 'archive'
  };
  return icons[status] || 'help';
};

const getFileIcon = (mimeType) => {
  if (mimeType?.includes('image')) return 'image';
  if (mimeType?.includes('video')) return 'videocam';
  if (mimeType?.includes('audio')) return 'audiotrack';
  if (mimeType?.includes('pdf')) return 'picture_as_pdf';
  return 'description';
};

const formatDate = (dateString) => {
  if (!dateString) return '';
  return new Date(dateString).toLocaleDateString();
};

const formatFileSize = (bytes) => {
  if (!bytes) return '';
  const sizes = ['Bytes', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(1024));
  return Math.round(bytes / Math.pow(1024, i) * 100) / 100 + ' ' + sizes[i];
};

const formatFileSize2 = (bytes) => {
  if (!bytes) return '';
  if (bytes < 1024) return bytes + ' B';
  if (bytes < 1024 * 1024) return Math.round(bytes / 1024) + ' KB';
  return Math.round(bytes / (1024 * 1024)) + ' MB';
};

const truncateUrl = (url) => {
  if (!url) return '';
  return url.length > 30 ? url.substring(0, 30) + '...' : url;
};

// Lifecycle
onMounted(() => {
  // Initialize user's rating if available
  currentRating.value = props.answer.user_rating || 0;
  userLiked.value = props.answer.user_liked || false;
});
</script>

<style scoped>
.answer-card {
  border-radius: 16px;
  transition: all 0.3s ease;
  overflow: hidden;
}

.answer-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
}

.bg-gradient-primary {
  background: linear-gradient(135deg, #1976d2 0%, #42a5f5 100%);
}

.comment-card {
  border-radius: 12px;
  transition: all 0.2s ease;
}

.comment-card:hover {
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.border-bottom {
  border-bottom: 1px solid rgba(0, 0, 0, 0.12);
} 

.answer-text {
  font-size: 16px;
  line-height: 1.6;
  white-space: pre-wrap;
}

.q-rating {
  font-size: 18px;
}
</style>
