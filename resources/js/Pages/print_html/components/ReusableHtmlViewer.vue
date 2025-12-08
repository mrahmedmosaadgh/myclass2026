<template>
    <div class="relative bg-white">
        <EditorToolbar
            :title="title_new"
            :line-spacing="lineSpacing"
            :alignment="currentAlignment"
            :image-display-mode="imageDisplayMode"
            :image-width="imageWidth"
            :active-formats="activeFormats"
            :image-sizes="imageSizes"
            @update:title="title_new = $event"
            @update:line-spacing="lineSpacing = $event"
            @update:alignment="currentAlignment = $event"
            @update:image-display-mode="imageDisplayMode = $event"
            @update:image-width="imageWidth = $event"
            @format="toggleFormat"
            @convert-images-to-base64="convertAllImagesToBase64"
            @resize-images="resizeSelectedImages"
            @print="print"
        />

        <EditorContent
            :model-value="props.modelValue"
            :line-spacing="lineSpacing"
            :alignment="currentAlignment"
            @update:model-value="emit('update:modelValue', $event)"
            @image-click="handleImageClick"
            ref="editableContent"
        />

        <PrintLayout
            ref="printContainer"
            :content="sanitizedContent"
            :line-spacing="lineSpacing"
            :alignment="currentAlignment"
        />
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import DOMPurify from 'dompurify';
import EditorToolbar from './EditorToolbar.vue';
import EditorContent from './EditorContent.vue';
import PrintLayout from './PrintLayout.vue';

const props = defineProps({
    modelValue: {
        type: String,
        required: true
    },
    title: {
        type: String,
        default: 'Document'
    }
});

const emit = defineEmits(['update:modelValue']);

// State
const title_new = ref(props.title);
const printContainer = ref(null);
const editableContent = ref(null);
const lineSpacing = ref(1.5);
const currentAlignment = ref('left');
const imageDisplayMode = ref('inline-block');
const imageWidth = ref(100);
const selectedImages = ref([]);

// Constants
const imageSizes = [5, 10, 15, 20, 25, 33, 50, 75, 100, 110, 120, 150, 200, 300];

// Computed
const sanitizedContent = computed(() => {
    return DOMPurify.sanitize(props.modelValue);
});

// Methods
const handleContentEdit = (event) => {
    const newContent = event.target.innerHTML;
    const sanitizedNewContent = DOMPurify.sanitize(newContent);
    emit('update:modelValue', sanitizedNewContent);
};

const toggleFormat = (format) => {
    document.execCommand(format, false, null);
};

const handleImageClick = (event) => {
    if (event.target.tagName === 'IMG') {
        selectedImages.value = [event.target];
    }
};

const resizeSelectedImages = () => {
    if (selectedImages.value.length) {
        selectedImages.value.forEach(img => {
            img.style.width = `${imageWidth.value}%`;
        });
    }
};

// Watch for external content changes
watch(() => props.modelValue, (newContent) => {
    if (editableContent.value && editableContent.value.innerHTML !== newContent) {
        editableContent.value.innerHTML = DOMPurify.sanitize(newContent);
    }
});
</script>

<style scoped>
.prose :deep(img) {
    cursor: pointer;
}
</style>
