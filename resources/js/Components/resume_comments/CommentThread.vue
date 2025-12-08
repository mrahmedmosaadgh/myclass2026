<template>
  <div class="comment-thread">
    <div v-for="comment in comments" :key="comment.id" class="mb-4 border-b pb-2">
      <div class="flex items-center gap-2">
        <span class="font-bold">{{ comment.user?.name || 'User' }}</span>
        <span class="text-xs text-gray-500">{{ new Date(comment.created_at).toLocaleString() }}</span>
        <span v-if="!comment.is_public" class="text-xs text-red-500">Private</span>
      </div>
      <div v-if="comment.comment" class="mb-1">{{ comment.comment }}</div>
      <CommentMediaPlayer v-if="comment.media_path" :media-type="comment.media_type" :media-path="comment.media_path" />
      <div class="ml-4 mt-2">
        <CommentThread v-if="comment.replies && comment.replies.length" :comments="comment.replies" />
        <CommentComposer :question-id="questionId" :parent-id="comment.id" @submitted="$emit('refresh')" />
      </div>
    </div>
  </div>
</template>

<script setup>
import CommentMediaPlayer from './CommentMediaPlayer.vue';
import CommentComposer from './CommentComposer.vue';
const props = defineProps({
  comments: Array,
  questionId: [String, Number],
});
</script>
