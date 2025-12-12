<template>
    <div class="relative bg-white">
        <!-- Toolbar -->
        <div class="border-b">
            <div class="p-4 flex flex-wrap items-center gap-3">
                <!-- Title Input -->
                <input
                    type="text"
                    v-model="title_new"
                    class="border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    placeholder="Document Title"
                />

                <!-- Basic Text Controls -->
                <div class="flex items-center gap-1 border rounded-md bg-white shadow-sm">
                    <button
                        v-for="format in ['bold', 'italic', 'underline']"
                        :key="format"
                        @click="toggleFormat(format)"
                        class="p-2 hover:bg-gray-100 transition-colors"
                        :class="{ 'bg-blue-50 text-blue-600': isFormatActive(format) }"
                        :title="format.charAt(0).toUpperCase() + format.slice(1)"
                    >
                        <span class="font-bold" v-if="format === 'bold'">B</span>
                        <span class="italic" v-else-if="format === 'italic'">I</span>
                        <span class="underline" v-else-if="format === 'underline'">U</span>
                    </button>
                </div>

                <!-- Line Spacing Control -->
                <div class="flex items-center gap-2">
                    <label class="text-sm text-gray-600">Line Spacing:</label>
                    <input
                        type="number"
                        v-model="lineSpacing"
                        @input="updateLineSpacing"
                        min="1"
                        max="3"
                        step="0.1"
                        class="w-20 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    />
                </div>

                <!-- Text Alignment Controls -->
                <div class="flex items-center gap-1 border rounded-md bg-white shadow-sm">
                    <button
                        v-for="align in ['left', 'center', 'right']"
                        :key="align"
                        @click="setAlignment(align)"
                        class="p-2 hover:bg-gray-100 transition-colors"
                        :class="{ 'bg-blue-50 text-blue-600': currentAlignment === align }"
                        :title="`Align ${align}`"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" :d="alignmentPaths[align]" clip-rule="evenodd"/>
                        </svg>
                    </button>
                </div>

                <!-- Image Controls -->
                <button
                    @click="toggleImageDisplay"
                    :class="[
                        'px-2 py-1 rounded text-sm',
                        imageDisplayMode === 'inline-block'
                            ? 'bg-blue-500 text-white'
                            : 'bg-gray-200 text-gray-700'
                    ]"
                >
                    {{ imageDisplayMode === 'inline-block' ? 'Inline' : 'Block' }}
                </button>

                <select
                    v-model="imageWidth"
                    @change="resizeSelectedImages"
                    class="w-24 rounded-md border-gray-300 shadow-sm"
                >
                    <option v-for="size in imageSizes" :key="size" :value="size">
                        {{ size }}%
                    </option>
                </select>

                <!-- Convert Images to Base64 Button -->
                <button
                    @click="convertAllImagesToBase64"
                    class="px-2 py-1 rounded text-sm bg-gray-200 text-gray-700 hover:bg-gray-300"
                >
                    Convert Images to Base64
                </button>
                <button
                    @click="handlePasteWithBase64"
                    class="px-2 py-1 rounded text-sm bg-blue-500 text-white hover:bg-blue-600 flex items-center"
                >
                    <svg class="w-4 h-4 mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    Paste with Base64
                </button>
                <!-- Action Buttons -->
                <div class="flex items-center gap-2 ml-auto">
                    <button
                        @click="print"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition flex items-center gap-2"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5 4v3H4a2 2 0 00-2 2v3a2 2 0 002 2h1v2a2 2 0 002 2h6a2 2 0 002-2v-2h1a2 2 0 002-2V9a2 2 0 00-2-2h-1V4a2 2 0 00-2-2H7a2 2 0 00-2 2zm8 0H7v3h6V4zm0 8H7v4h6v-4z" clip-rule="evenodd"/>
                        </svg>
                        Print
                    </button>
                </div>
            </div>
        </div>

        <!-- Content Area -->
        <div class="p-6">
            <div
                class="prose max-w-none"
                v-html="sanitizedContent"
                contenteditable="true"
                ref="editableContent"
                @input="handleContentEdit"
                @click="handleImageClick"
                :style="{ lineHeight: lineSpacing, textAlign: currentAlignment }"
            ></div>
        </div>

        <!-- Print Layout (hidden) -->
        <div ref="printContainer" class="hidden">
            <div
                class="print-content prose max-w-none"
                v-html="sanitizedContent"
                :style="{ lineHeight: lineSpacing, textAlign: currentAlignment }"
            ></div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import DOMPurify from 'dompurify';

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
const alignmentPaths = {
    left: 'M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 5a1 1 0 011-1h6a1 1 0 110 2H4a1 1 0 01-1-1z',
    center: 'M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3 5a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm-3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z',
    right: 'M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm5 5a1 1 0 011-1h6a1 1 0 110 2H9a1 1 0 01-1-1zm-5 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z'
};

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

const isFormatActive = (format) => {
    return document.queryCommandState(format);
};

const updateLineSpacing = () => {
    lineSpacing.value = Math.max(1, Math.min(3, lineSpacing.value));
};

const setAlignment = (alignment) => {
    currentAlignment.value = alignment;
};

const toggleImageDisplay = () => {
    imageDisplayMode.value = imageDisplayMode.value === 'inline-block' ? 'block' : 'inline-block';
    if (selectedImages.value.length) {
        selectedImages.value.forEach(img => {
            img.style.display = imageDisplayMode.value;
        });
    }
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

    const images = editableContent.value.getElementsByTagName('img');
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
    handleContentEdit({ target: editableContent.value });
};

// Watch for external content changes
watch(() => props.modelValue, (newContent) => {
    if (editableContent.value && editableContent.value.innerHTML !== newContent) {
        editableContent.value.innerHTML = DOMPurify.sanitize(newContent);
    }
});







const handlePasteWithBase64 = async () => {
    try {
        const clipboardText = await navigator.clipboard.readText();
        if (!clipboardText) return;

        const div = document.createElement('div');
        div.innerHTML = DOMPurify.sanitize(clipboardText);
        convertAllImagesToBase64()
        // Convert all images to base64
    
    } catch (error) {
        console.error('Clipboard operation failed:', error);
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


