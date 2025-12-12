<!-- <EmojiPicker @select="handleEmojiSelect" /> -->
<!-- import EmojiPicker from '@/Components/Common/EmojiPicker.vue'; -->
<template>
    <div class="emoji-picker p-2 bg-white border rounded shadow-md max-w-xs">
      <div class="grid grid-cols-6 gap-2 text-2xl cursor-pointer">
        <span
          v-for="(emoji, index) in emojis"
          :key="index"
          @click="$emit('select', emoji)"
          class="hover:scale-110 transition-transform"
        >
          {{ emoji }}
        </span>
      </div>
    </div>
  </template>

  <script>
  export default {
    name: 'EmojiPicker',
    data() {
      return {
        emojis: [
          '✅', '❌', '🚀', '🔥', '🎯', '🎉',
          '📚', '📝', '👨‍🏫', '👩‍🏫', '👥', '📅',
          '💡', '🔒', '🔓', '🔄', '⚠️', '✅',
          '❤️', '👍', '👎', '🤖', '🎓', '🏫'
        ]
      };
    }
  };
  </script>

  <style scoped>
  .emoji-picker {
    z-index: 1000;
  }
  </style>
