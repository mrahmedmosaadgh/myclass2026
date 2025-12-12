<template>
    <div class="math-input-container">
        <!-- Editor Area -->
        <div v-if="isEditing" class="math-editor">
            <textarea
                v-model="mathInput"
                class="w-full p-2 border rounded-md font-mono text-sm"
                rows="3"
                @input="renderMath"
                :placeholder="placeholder"
            ></textarea>
            <div class="flex justify-between items-center mt-2">
                <div class="flex gap-2">
                    <button
                        v-for="template in quickTemplates"
                        :key="template.symbol"
                        @click="insertTemplate(template.symbol)"
                        class="px-2 py-1 text-sm bg-gray-100 hover:bg-gray-200 rounded"
                        :title="template.description"
                    >
                        {{ template.display }}
                    </button>
                </div>
                <button
                    @click="toggleEdit"
                    class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 text-sm"
                >
                    Done
                </button>
            </div>
            <div class="flex items-center gap-2 mt-2">
                <label class="text-sm text-gray-600">Size:</label>
                <select
                    v-model="localFontSize"
                    class="text-sm border rounded-md"
                    @change="updateFontSize"
                >
                    <option value="0.8em">Small</option>
                    <option value="1.1em">Normal</option>
                    <option value="1.5em">Large</option>
                    <option value="2em">Extra Large</option>
                </select>
            </div>
        </div>

        <!-- Preview Area -->
        <div
            v-else
            class="math-preview relative"
            @dblclick="toggleEdit"
            :style="{
                '--katex-font-size': localFontSize
            }"
        >
            <div
                class="math-content prose"
                v-html="renderedContent"
            ></div>
            <div class="absolute top-0 right-0 flex gap-1">
                <button
                    @click="copyAsHtml"
                    class="p-1 text-gray-400 hover:text-gray-600"
                    title="Copy as HTML"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M8 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z" />
                        <path d="M6 3a2 2 0 00-2 2v11a2 2 0 002 2h8a2 2 0 002-2V5a2 2 0 00-2-2 3 3 0 01-3 3H9a3 3 0 01-3-3z" />
                    </svg>
                </button>
                <button
                    @click="copyAsImage"
                    class="p-1 text-gray-400 hover:text-gray-600"
                    title="Copy as Image"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd" />
                    </svg>
                </button>
                <button
                    v-if="editable"
                    @click="toggleEdit"
                    class="p-1 text-gray-400 hover:text-gray-600"
                    title="Edit"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import katex from 'katex';
import 'katex/dist/katex.min.css';
import DOMPurify from 'dompurify';
import html2canvas from 'html2canvas';

const props = defineProps({
    initialValue: {
        type: String,
        default: ''
    },
    placeholder: {
        type: String,
        default: 'Enter mathematical expression (e.g., \\frac{1}{2})'
    },
    editable: {
        type: Boolean,
        default: true
    },
    displayMode: {
        type: Boolean,
        default: true
    },
    fontSize: {
        type: String,
        default: '1.1em'
    }
});

const emit = defineEmits(['update:content', 'get_data', 'update:fontSize']);

const mathInput = ref(props.initialValue);
const isEditing = ref(false);
const renderedContent = ref('');
const localFontSize = ref(props.fontSize);

const quickTemplates = [
    { symbol: '\\frac{}{}', display: 'a/b', description: 'Fraction' },
    { symbol: '\\sqrt{}', display: '√', description: 'Square root' },
    { symbol: '\\sum_{}^{}', display: '∑', description: 'Summation' },
    { symbol: '\\int_{}^{}', display: '∫', description: 'Integral' },
    { symbol: '\\infty', display: '∞', description: 'Infinity' },
    { symbol: '\\pm', display: '±', description: 'Plus-minus' }
];

const insertTemplate = (template) => {
    const textarea = document.querySelector('.math-editor textarea');
    if (!textarea) return;

    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;

    mathInput.value =
        mathInput.value.substring(0, start) +
        template +
        mathInput.value.substring(end);

    renderMath();
};

const renderMath = () => {
    try {
        // Pre-process the input to handle escaped backslashes
        const processedInput = mathInput.value
            .replace(/\\\(/g, '') // Remove \(
            .replace(/\\\)/g, '') // Remove \)
            .trim();

        const rendered = katex.renderToString(processedInput, {
            displayMode: props.displayMode,
            throwOnError: false,
            strict: false,
            trust: true
        });
        renderedContent.value = DOMPurify.sanitize(rendered);
        emit('update:content', renderedContent.value);
    } catch (error) {
        console.warn('KaTeX rendering error:', error);
        renderedContent.value = `<span class="text-red-500">Error: ${error.message}</span>`;
    }
};

const toggleEdit = () => {
    if (!props.editable) return;
    isEditing.value = !isEditing.value;
    if (!isEditing.value) {
        renderMath();
    }
};

const copyAsImage = async () => {
    try {
        // Create SVG data URL from KaTeX output
        const svgData = `
            <svg xmlns="http://www.w3.org/2000/svg" width="800" height="200">
                <foreignObject width="100%" height="100%">
                    <div xmlns="http://www.w3.org/1999/xhtml" style="font-family: KaTeX_Main, Times New Roman, serif; padding: 8px;">
                        ${renderedContent.value}
                    </div>
                </foreignObject>
            </svg>
        `;

        const svgBlob = new Blob([svgData], { type: 'image/svg+xml;charset=utf-8' });
        const svgUrl = URL.createObjectURL(svgBlob);

        // Convert SVG to PNG using native canvas
        const img = new Image();
        img.src = svgUrl;

        await new Promise((resolve, reject) => {
            img.onload = resolve;
            img.onerror = reject;
        });

        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        canvas.width = img.width;
        canvas.height = img.height;

        // Draw with white background
        ctx.fillStyle = 'white';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        ctx.drawImage(img, 0, 0);

        // Convert to PNG and copy
        const pngUrl = canvas.toDataURL('image/png');
        const response = await fetch(pngUrl);
        const blob = await response.blob();

        await navigator.clipboard.write([
            new ClipboardItem({
                'image/png': blob
            })
        ]);

        // Cleanup
        URL.revokeObjectURL(svgUrl);
        emit('get_data', { type: 'image', data: pngUrl });
        showCopiedMessage('Image');
    } catch (err) {
        console.error('Failed to copy as image:', err);
        const button = document.querySelector('[title="Copy as Image"]');
        button.setAttribute('title', 'Failed to copy');
        setTimeout(() => {
            button.setAttribute('title', 'Copy as Image');
        }, 2000);
    }
};

const copyAsHtml = async () => {
    try {
        await navigator.clipboard.writeText(renderedContent.value);
        emit('get_data', { type: 'html', data: renderedContent.value });
        showCopiedMessage('HTML');
    } catch (err) {
        console.error('Failed to copy HTML:', err);
    }
};

const showCopiedMessage = (type) => {
    const button = document.querySelector(`[title="Copy as ${type}"]`);
    const originalTitle = button.getAttribute('title');
    button.setAttribute('title', 'Copied!');
    setTimeout(() => {
        button.setAttribute('title', originalTitle);
    }, 2000);
};

const updateFontSize = () => {
    emit('update:fontSize', localFontSize.value);
    renderMath(); // Re-render math with new font size
};

watch(() => props.initialValue, (newValue) => {
    mathInput.value = newValue;
    renderMath();
});

watch(() => props.fontSize, (newSize) => {
    localFontSize.value = newSize;
    renderMath();
});

onMounted(() => {
    mathInput.value = props.initialValue;
    renderMath();
});
</script>

<style scoped>
.math-input-container {
    @apply relative border rounded-lg p-4 bg-white;
}

.math-editor {
    @apply space-y-2;
}

.math-preview {
    @apply min-h-[2em] p-2;
}

:deep(.katex) {
    font-size: var(--katex-font-size) !important;
}

:deep(.katex-display) {
    margin: 0;
}

.math-preview:hover .absolute {
    opacity: 1;
}

.math-preview .absolute {
    opacity: 0;
    transition: opacity 0.2s;
}

/* You can also add responsive sizes */
</style>









