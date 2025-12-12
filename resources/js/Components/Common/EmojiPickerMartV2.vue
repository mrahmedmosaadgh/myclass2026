<template>
  <div class="emoji-picker">
    <!-- Recently Used Emojis -->
    <div v-if="recent.length" class="recent-emojis mb-2">
      <h4 class="text-sm mb-1">Recently Used</h4>
      <div class="flex flex-wrap gap-2">
        <span
          v-for="(emoji, index) in recent"
          :key="index"
          class="text-xl cursor-pointer hover:scale-110 transition-transform"
          @click="selectEmojiFromRecent(emoji)"
        >
          {{ emoji }}
        </span>
      </div>
    </div>

    <!-- Emoji Picker -->
    <div v-if="isPickerLoaded" ref="pickerContainer"></div>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue';

const props = defineProps({
  modelValue: {
    type: String,
    default: ""
  }
});

const emit = defineEmits(['update:modelValue', 'select']);
const recent = ref(JSON.parse(localStorage.getItem("recentEmojis") || "[]"));
const pickerContainer = ref(null);
const isPickerLoaded = ref(false);

onMounted(async () => {
  try {
    await import('emoji-picker-element');
    isPickerLoaded.value = true;

    nextTick(() => {
      const picker = document.createElement('emoji-picker');
      picker.classList.add('light');
      picker.addEventListener('emoji-click', onEmojiClick);
      pickerContainer.value.appendChild(picker);
    });
  } catch (error) {
    console.error('Failed to load emoji picker:', error);
  }
});

const onEmojiClick = (event) => {
  const emoji = event.detail.unicode;
  updateRecent(emoji);
  emit('update:modelValue', emoji);
  emit('select', emoji);
};

const selectEmojiFromRecent = (emoji) => {
  updateRecent(emoji);
  emit('update:modelValue', emoji);
  emit('select', emoji);
};

const updateRecent = (emoji) => {
  recent.value = [emoji, ...recent.value.filter(e => e !== emoji)].slice(0, 12);
  localStorage.setItem("recentEmojis", JSON.stringify(recent.value));
};
</script>

<style>
.emoji-picker {
  max-width: 320px;
}

.recent-emojis {
  border: 1px solid #eee;
  border-radius: 4px;
  padding: 0.5rem;
  background-color: #f9f9f9;
}

/* Custom styles for the emoji-picker-element */
emoji-picker {
  --num-columns: 8;
  width: 100%;
  height: 400px;
}

/* Light theme customization */
emoji-picker.light {
  --background: #fff;
  --border-color: #e2e8f0;
  --indicator-color: #60a5fa;
  --input-border-color: #e2e8f0;
  --input-font-color: #1f2937;
  --input-placeholder-color: #9ca3af;
  --outline-color: #60a5fa;
}
</style>



