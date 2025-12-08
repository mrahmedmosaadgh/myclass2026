<template>
    <div class="relative bg-white">
        <EditorToolbar
            v-model:title="title_new"
            v-model:line-spacing="lineSpacing"
            v-model:alignment="currentAlignment"
            v-model:image-display-mode="imageDisplayMode"
            v-model:image-width="imageWidth"
            :active-formats="activeFormats"
            :image-sizes="imageSizes"
            @format="toggleFormat"
            @convert-images-to-base64="convertAllImagesToBase64"
            @resize-images="resizeSelectedImages"
            @print="print"
        />

        <EditorContent
            :model-value="modelValue"
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
import EditorToolbar from './components/EditorToolbar.vue';
import EditorContent from './components/EditorContent.vue';
import PrintLayout from './components/PrintLayout.vue';

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

// Computed properties
const sanitizedContent = computed(() => {
    return DOMPurify.sanitize(props.modelValue);
});

const activeFormats = computed(() => {
    return ['bold', 'italic', 'underline'].filter(format => {
        return document.queryCommandState(format);
    });
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

const isFormatActive = (format) => {
    return document.queryCommandState(format);
};

const updateLineSpacing = () => {
    lineSpacing.value = Math.max(1, Math.min(3, lineSpacing.value));
};

const setAlignment = (alignment) => {
    currentAlignment.value = alignment;
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

const print = () => {
    const printWindow = window.open('', '_blank');
    const printContent = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>${title_new.value}</title>
            <style>
                @page {
                    margin: 1cm;
                    size: A4;
                }
                body {
                    font-family: system-ui, -apple-system, sans-serif;
                    line-height: ${lineSpacing.value};
                    margin: 0;
                    padding: 1cm;
                    text-align: ${currentAlignment.value};
                }
                .prose {
                    max-width: none;
                }
                .prose img {
                    max-width: 100%;
                    height: auto;
                    page-break-inside: avoid;
                }
                @media print {
                    html, body {
                        width: 210mm;
                        height: 297mm;
                    }
                }
            </style>
        </head>
        <body>
            ${sanitizedContent.value}
        </body>
        </html>
    `;

    printWindow.document.write(printContent);
    printWindow.document.close();
    printWindow.onload = () => {
        printWindow.print();
        printWindow.onafterprint = () => {
            printWindow.close();
        };
    };
};

const readFileAsDataURL = (blob) => {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = () => resolve(reader.result);
        reader.onerror = reject;
        reader.readAsDataURL(blob);
    });
};

const convertAllImagesToBase64 = async () => {
    if (!editableContent.value) return;

    // Get the actual DOM element
    const contentElement = editableContent.value.$el || editableContent.value;
    const images = contentElement.getElementsByTagName('img');

    const promises = Array.from(images).map(async (img) => {
        if (img.src.startsWith('data:')) return;

        try {
            const response = await fetch(img.src);
            const blob = await response.blob();
            const base64 = await readFileAsDataURL(blob);
            img.src = base64;
        } catch (error) {
            console.error('Failed to convert image to base64:', error);
        }
    });

    await Promise.all(promises);
    handleContentEdit({ target: contentElement });
};

// Watch for external content changes
watch(() => props.modelValue, (newContent) => {
    if (editableContent.value && editableContent.value.innerHTML !== newContent) {
        editableContent.value.innerHTML = DOMPurify.sanitize(newContent);
    }
});





const copyToClipboardForWord = async () => {
  try {
    const htmlContent = contentToCopy.value.innerHTML;

    // Create a Blob containing HTML content
    const blob = new Blob([htmlContent], { type: "text/html" });
    const clipboardItem = new ClipboardItem({ "text/html": blob });

    // Use Clipboard API
    await navigator.clipboard.write([clipboardItem]);

    alert("Copied successfully! Now paste it into Word.");
  } catch (err) {
    console.error("Failed to copy:", err);
  }
};

</script>

<style scoped>
.prose :deep(img) {
    cursor: pointer;
}

.prose :deep(img.selected) {
    outline: 2px solid #3b82f6;
}
</style>





