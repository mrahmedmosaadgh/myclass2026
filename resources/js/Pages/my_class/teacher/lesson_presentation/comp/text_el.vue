<template>
  <div class="text-element-container" :class="{ 'is-editing': isEditing }">
    <div
      v-if="!readonly"
      ref="editableContent"
      contenteditable="true"
      :innerHTML="content"
      @input="handleContentInput"
      @blur="handleContentBlur"
      @focus="handleFocus"
      class="content-wrapper editable"
      :class="{
        'editing': isEditing,
        'hover-effect': !readonly,
        'empty': !content
      }"
    ></div>
    <div
      v-else
      :innerHTML="content"
      class="content-wrapper readonly"
    ></div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
  content: {
    type: String,
    default: ''
  },
  readonly: {
    type: Boolean,
    default: false
  },
  elementId: {
    type: [String, Number],
    required: true
  }
});

const editableContent = ref(null);
const emit = defineEmits(['update:content']);
const isEditing = ref(false);

const handleContentInput = (event) => {
  const newContent = event.target.innerHTML;
  console.log('Content changed in text_el:', {
    elementId: props.elementId,
    newContent: newContent
  });
  emit('update:content', newContent);
};

const handleContentBlur = () => {
  console.log('Content blur - final content:', editableContent.value?.innerHTML);
  isEditing.value = false;
};

const handleFocus = () => {
  console.log('Content focus - starting content:', editableContent.value?.innerHTML);
  isEditing.value = true;
};

// Watch for content changes from props
watch(() => props.content, (newContent) => {
  if (editableContent.value && !isEditing.value) {
    editableContent.value.innerHTML = newContent;
  }
}, { immediate: true });
</script>

<style scoped>
.text-element-container {
  min-width: 50px;
  min-height: 20px;
}

.content-wrapper {
  outline: none;
  white-space: pre-wrap;
}

.editable {
  cursor: text;
}

.editable.hover-effect:hover {
  outline: 2px solid #4299e1;
}

.editing {
  outline: 2px solid #4299e1;
}

.empty {
  min-width: 100px;
  min-height: 24px;
}
</style>







