<template>
  <div class="comments-section">
    <!-- Add Comment Form -->
    <q-card flat bordered class="q-mb-lg">
      <q-card-section class="q-pa-md">
        <div class="text-subtitle2 q-mb-md text-primary">
          <q-icon name="add_comment" class="q-mr-xs" />
          Add a Comment
        </div>
        <div class="row q-gutter-md items-end">
          <div class="col">
            <q-input
              v-model="newComment"
              placeholder="Share your thoughts..."
              outlined
              type="textarea"
              rows="3"
              @keyup.ctrl.enter="addComment"
            >
              <template v-slot:hint>
                Press Ctrl+Enter to post
              </template>
            </q-input>
          </div>
          <div class="col-auto">
            <q-btn
              color="primary"
              icon="send"
              label="Post"
              @click="addComment"
              :disable="!newComment.trim()"
              :loading="addingComment"
            />
          </div>
        </div>
      </q-card-section>
    </q-card>

    <!-- Comments List -->
    <div v-if="comments.length === 0" class="text-center text-grey-6 q-py-xl">
      <q-icon name="chat_bubble_outline" size="4em" color="grey-4" />
      <div class="text-h6 q-mt-md text-grey-5">No comments yet</div>
      <div class="text-body2 q-mt-xs">Be the first to share your thoughts!</div>
    </div>

    <div v-else class="comments-list">
      <div class="text-subtitle2 q-mb-md text-grey-7">
        {{ comments.length }} comment{{ comments.length !== 1 ? 's' : '' }}
      </div>

      <q-separator class="q-mb-lg" />

      <div
        v-for="comment in comments"
        :key="comment.id"
        class="comment-item q-mb-lg"
      >
        <q-card flat bordered class="comment-card">
          <q-card-section class="q-pa-md">
            <div class="row q-gutter-md">
              <!-- Avatar -->
              <div class="col-auto">
                <q-avatar size="40px" color="primary" text-color="white">
                  {{ comment.user?.name?.charAt(0)?.toUpperCase() || 'U' }}
                </q-avatar>
              </div>

          <!-- Comment Content -->
          <div class="col">
            <div class="comment-header q-mb-xs">
              <span class="text-weight-medium">
                {{ comment.user?.name || `User ${comment.user_id}` }}
              </span>
              <span class="text-caption text-grey-6 q-ml-sm">
                {{ formatDate(comment.created_at) }}
              </span>
              <q-badge
                v-if="comment.is_edited"
                label="edited"
                color="grey-5"
                class="q-ml-xs"
              />
            </div>

            <!-- Comment Text -->
            <div
              v-if="editingComment !== comment.id"
              class="comment-text q-mb-xs"
            >
              <div class="row items-start q-gutter-sm">
                <div class="col text-body2">
                  {{ comment.text }}
                </div>
                <div class="col-auto">
                  <TextToSpeechButton
                    :text="comment.text"
                    size="xs"
                    round
                    flat
                  />
                </div>
              </div>
            </div>

            <!-- Edit Form -->
            <div
              v-else
              class="edit-comment-form q-mb-xs"
            >
              <q-input
                v-model="editCommentText"
                outlined
                dense
                autofocus
                @keyup.enter="saveComment(comment.id)"
                @keyup.esc="cancelEdit"
              />
              <div class="row q-gutter-xs q-mt-xs">
                <q-btn
                  size="sm"
                  color="primary"
                  label="Save"
                  @click="saveComment(comment.id)"
                />
                <q-btn
                  size="sm"
                  flat
                  label="Cancel"
                  @click="cancelEdit"
                />
              </div>
            </div>

            <!-- Comment Actions -->
            <div class="comment-actions">
              <q-btn-group flat>
                <q-btn
                  size="sm"
                  flat
                  :color="comment.user_liked ? 'positive' : 'grey-7'"
                  :icon="comment.user_liked ? 'thumb_up' : 'thumb_up_off_alt'"
                  @click="toggleCommentLike(comment.id)"
                >
                  {{ comment.likes_count || 0 }}
                </q-btn>
                <q-btn
                  size="sm"
                  flat
                  color="grey-7"
                  icon="reply"
                  @click="replyToComment(comment.id)"
                >
                  Reply
                </q-btn>
                <q-btn
                  v-if="canEditComment(comment)"
                  size="sm"
                  flat
                  color="grey-7"
                  icon="edit"
                  @click="startEdit(comment)"
                />
                <q-btn
                  v-if="canDeleteComment(comment)"
                  size="sm"
                  flat
                  color="negative"
                  icon="delete"
                  @click="deleteComment(comment.id)"
                />
              </q-btn-group>
            </div>

            <!-- Replies -->
            <div
              v-if="comment.replies && comment.replies.length > 0"
              class="replies q-mt-md q-ml-md"
            >
              <div
                v-for="reply in comment.replies"
                :key="reply.id"
                class="reply-item q-mb-sm q-pa-sm bg-grey-1 rounded-borders"
              >
                <div class="row q-gutter-sm">
                  <div class="col-auto">
                    <q-avatar size="24px" color="blue-grey-4" text-color="white">
                      <q-icon name="person" />
                    </q-avatar>
                  </div>
                  <div class="col">
                    <div class="reply-header q-mb-xs">
                      <span class="text-weight-medium text-body2">
                        {{ reply.user?.name || `User ${reply.user_id}` }}
                      </span>
                      <span class="text-caption text-grey-6 q-ml-sm">
                        {{ formatDate(reply.created_at) }}
                      </span>
                    </div>
                    <div class="reply-text text-body2">
                      {{ reply.text }}
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Reply Form -->
            <div
              v-if="replyingTo === comment.id"
              class="reply-form q-mt-sm q-ml-md"
            >
              <div class="row q-gutter-sm">
                <div class="col">
                  <q-input
                    v-model="replyText"
                    placeholder="Write a reply..."
                    outlined
                    dense
                    autofocus
                    @keyup.enter="submitReply(comment.id)"
                    @keyup.esc="cancelReply"
                  />
                </div>
                <div class="col-auto">
                  <q-btn
                    size="sm"
                    color="primary"
                    icon="send"
                    @click="submitReply(comment.id)"
                    :disable="!replyText.trim()"
                  />
                  <q-btn
                    size="sm"
                    flat
                    icon="close"
                    @click="cancelReply"
                    class="q-ml-xs"
                  />
                </div>
              </div>
            </div>
          </div>
        </div>

        </q-card-section>
      </q-card> 

      </div>
    </div>

    <!-- Load More Comments -->
    <div
      v-if="hasMoreComments"
      class="text-center q-mt-md"
    >
      <q-btn
        flat
        color="primary"
        label="Load more comments"
        @click="loadMoreComments"
        :loading="loadingMore"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useQuasar } from 'quasar';
import TextToSpeechButton from './components/TextToSpeechButton.vue';
import resumeApi from './resumeApi.js';

// Props and Emits
const props = defineProps({
  answerId: {
    type: Number,
    required: true
  },
  comments: {
    type: Array,
    default: () => []
  }
});

const emit = defineEmits(['comment-added', 'comment-updated', 'comment-deleted']);

// Composables
const $q = useQuasar();

// State
const newComment = ref('');
const addingComment = ref(false);
const editingComment = ref(null);
const editCommentText = ref('');
const replyingTo = ref(null);
const replyText = ref('');
const loadingMore = ref(false);
const hasMoreComments = ref(false);

// Mock current user (in real app, get from auth)
const currentUser = ref({ id: 1, name: 'Current User' });

// Methods
const addComment = () => {
  if (!newComment.value.trim()) return;

  addingComment.value = true;

  resumeApi.createComment(props.answerId, { text: newComment.value })
    .then(response => {
      // Add the new comment to the list
      props.comments.unshift(response);
      newComment.value = '';
      emit('comment-added', response);

      $q.notify({
        type: 'positive',
        message: 'Comment added successfully!',
        position: 'top'
      });
    })
    .catch(error => {
      console.error('Error adding comment:', error);
      $q.notify({
        type: 'negative',
        message: 'Failed to add comment. Please try again.',
        position: 'top'
      });
    })
    .finally(() => {
      addingComment.value = false;
    });
};

const startEdit = (comment) => {
  editingComment.value = comment.id;
  editCommentText.value = comment.text;
};

const saveComment = (commentId) => {
  if (!editCommentText.value.trim()) return;

  resumeApi.updateComment(commentId, { text: editCommentText.value })
    .then(response => {
      const comment = props.comments.find(c => c.id === commentId);
      if (comment) {
        comment.text = editCommentText.value;
        comment.is_edited = true;
      }

      cancelEdit();
      emit('comment-updated', commentId);

      $q.notify({
        type: 'positive',
        message: 'Comment updated successfully',
        position: 'top'
      });
    })
    .catch(error => {
      console.error('Error updating comment:', error);
      $q.notify({
        type: 'negative',
        message: 'Failed to update comment',
        position: 'top'
      });
    });
};

const cancelEdit = () => {
  editingComment.value = null;
  editCommentText.value = '';
};

const deleteComment = (commentId) => {
  $q.dialog({
    title: 'Confirm Delete',
    message: 'Are you sure you want to delete this comment?',
    cancel: true,
    persistent: true
  }).onOk(() => {
    resumeApi.deleteComment(commentId)
      .then(() => {
        const index = props.comments.findIndex(c => c.id === commentId);
        if (index > -1) {
          props.comments.splice(index, 1);
        }

        emit('comment-deleted', commentId);

        $q.notify({
          type: 'positive',
          message: 'Comment deleted successfully',
          position: 'top'
        });
      })
      .catch(error => {
        console.error('Error deleting comment:', error);
        $q.notify({
          type: 'negative',
          message: 'Failed to delete comment',
          position: 'top'
        });
      });
  });
};

const toggleCommentLike = (commentId) => {
  resumeApi.toggleCommentLike(commentId)
    .then(response => {
      const comment = props.comments.find(c => c.id === commentId);
      if (comment) {
        comment.user_liked = response.liked;
        comment.likes_count = response.likes_count;
      }
    })
    .catch(error => {
      console.error('Error toggling comment like:', error);
      $q.notify({
        type: 'negative',
        message: 'Failed to update like',
        position: 'top'
      });
    });
};

const replyToComment = (commentId) => {
  replyingTo.value = commentId;
  replyText.value = '';
};

const submitReply = (commentId) => {
  if (!replyText.value.trim()) return;

  // TODO: Implement API call to add reply
  const reply = {
    id: Date.now(),
    text: replyText.value,
    user_id: currentUser.value.id,
    user: currentUser.value,
    created_at: new Date().toISOString(),
    parent_comment_id: commentId
  };

  const comment = props.comments.find(c => c.id === commentId);
  if (comment) {
    if (!comment.replies) comment.replies = [];
    comment.replies.push(reply);
  }

  cancelReply();
  
  $q.notify({
    type: 'positive',
    message: 'Reply added successfully',
    position: 'top'
  });
};

const cancelReply = () => {
  replyingTo.value = null;
  replyText.value = '';
};

const loadMoreComments = () => {
  loadingMore.value = true;
  // TODO: Implement API call to load more comments
  setTimeout(() => {
    loadingMore.value = false;
    hasMoreComments.value = false;
  }, 1000);
};

const canEditComment = (comment) => {
  return comment.user_id === currentUser.value.id;
};

const canDeleteComment = (comment) => {
  return comment.user_id === currentUser.value.id;
};

const formatDate = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  const now = new Date();
  const diffMs = now - date;
  const diffMins = Math.floor(diffMs / 60000);
  const diffHours = Math.floor(diffMs / 3600000);
  const diffDays = Math.floor(diffMs / 86400000);

  if (diffMins < 1) return 'just now';
  if (diffMins < 60) return `${diffMins}m ago`;
  if (diffHours < 24) return `${diffHours}h ago`;
  if (diffDays < 7) return `${diffDays}d ago`;
  return date.toLocaleDateString();
};
</script>

<style scoped>
.comments-section {
  background-color: #fafafa;
  border-radius: 8px;
}

.comment-item {
  background-color: white;
  border-radius: 8px;
  padding: 12px;
}

.comment-text {
  line-height: 1.5;
  white-space: pre-wrap;
}

.reply-item {
  border-left: 3px solid #e0e0e0;
}

.reply-text {
  line-height: 1.4;
  white-space: pre-wrap;
}

.comment-actions .q-btn {
  font-size: 12px;
}
</style>
