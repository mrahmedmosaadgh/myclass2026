<template>
    <div
        class="prose max-w-none p-4"
        contenteditable="true"
        @input="handleInput"
        @click="handleClick"
        ref="content"
        :style="{
            lineHeight: lineSpacing,
            textAlign: alignment
        }"
    >
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';

const props = defineProps({
    modelValue: {
        type: String,
        required: true
    },
    lineSpacing: {
        type: Number,
        default: 1.5
    },
    alignment: {
        type: String,
        default: 'left'
    }
});

const emit = defineEmits(['update:modelValue', 'image-click']);
const content = ref(null);

// Expose the content element to the parent
defineExpose({
    getElementsByTagName: (tag) => content.value?.getElementsByTagName(tag)
});

const handleInput = (event) => {
    emit('update:modelValue', event.target.innerHTML);
};

const handleClick = (event) => {
    if (event.target.tagName === 'IMG') {
        emit('image-click', event);
    }
};

watch(() => props.modelValue, (newValue) => {
    if (content.value && content.value.innerHTML !== newValue) {
        content.value.innerHTML = newValue;
    }
});

onMounted(() => {
    if (content.value) {
        content.value.innerHTML = props.modelValue;
    }
});
</script>
