<template>
    <div class="clipboard-handler">
        <!-- Paste Buttons -->
        <div class="flex space-x-2 mb-4">
            <QBtnDropdown
                align="left"
                :auto-close="true"
            >
                <template #label>
                    <button
                        class="paste-btn flex items-center"
                        :disabled="processing"
                    >
                        <LucideIcon name="clipboard-paste" class="w-5 h-5 mr-2" />
                        <span>Paste</span>
                        <LucideIcon name="chevron-down" class="w-4 h-4 ml-1" />
                    </button>
                </template>

                <template #default>
                    <button
                        @click="requestClipboardContent('replace')"
                        class="w-full text-left px-4 py-2 hover:bg-gray-100 flex items-center"
                    >
                        <LucideIcon name="replace" class="w-4 h-4 mr-2" />
                        Replace Content
                    </button>
                    <button
                        @click="requestClipboardContent('insert')"
                        class="w-full text-left px-4 py-2 hover:bg-gray-100 flex items-center"
                    >
                        <LucideIcon name="plus" class="w-4 h-4 mr-2" />
                        Insert at Cursor
                    </button>
                </template>
            </QBtnDropdown>

            <button
                @click="requestClipboardContentWithBase64"
                class="paste-btn-base64"
                :disabled="processing"
            >
                <LucideIcon name="image" class="w-5 h-5 mr-2" />
                Paste with Base64 Images
            </button>
        </div>

        <!-- Paste Zone -->

        <div
            ref="pasteZone"
            class="paste-zone"
            :class="{ 'is-processing': processing }"
            @paste="handlePaste"
            @dragover.prevent
            @drop.prevent="handleDrop"
            tabindex="0"
        >
            <div v-if="!content" class="paste-instructions">
                <LucideIcon name="clipboard" class="icon" />
                <p>Paste content or drop files here</p>
                <small>Supports: Text, HTML, Images, URLs, Code</small>

                <!-- Add Paste Button -->
                <button
                    @click="requestClipboardContent"
                    class="paste-btn mt-4"
                    :disabled="processing"
                >
                    <LucideIcon name="clipboard-paste" class="w-5 h-5 mr-2" />
                    Paste from Clipboard
                </button>
            </div>

            <!-- Content Preview -->
            <div v-else class="content-preview">
                <!-- HTML Content -->
                <div
                    v-if="contentType === 'html'"
                    ref="editableContent"
                    contenteditable="true"
                    class="html-content editable-content"
                    @input="handleContentEdit"
                    v-html="sanitizedContent"
                ></div>

                <!-- Image Content -->
                <div v-else-if="contentType === 'image'" class="image-wrapper">
                    <img :src="content" class="image-content" />
                    <textarea
                        v-if="imageAltText !== undefined"
                        v-model="imageAltText"
                        class="image-alt-text"
                        placeholder="Enter image description..."
                        @input="handleImageAltTextChange"
                    ></textarea>
                </div>

                <!-- Code Content -->
                <div
                    v-else-if="contentType === 'code'"
                    ref="editableContent"
                    contenteditable="true"
                    class="code-content editable-content"
                    @input="handleContentEdit"
                >
                    <pre><code>{{ content }}</code></pre>
                </div>

                <!-- URL Content -->
                <div v-else-if="contentType === 'url'" class="url-content">
                    <input
                        type="url"
                        v-model="content"
                        class="url-input"
                        @input="handleContentEdit"
                    />
                </div>

                <!-- Plain Text -->
                <div
                    v-else
                    ref="editableContent"
                    contenteditable="true"
                    class="text-content editable-content"
                    @input="handleContentEdit"
                >{{ content }}</div>
            </div>
        </div>

        <!-- Controls -->
        <div v-if="content" class="controls mt-4 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <select
                    v-model="contentType"
                    @change="handleTypeChange"
                    class="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
                    <option value="text">Plain Text</option>
                    <option value="html">HTML</option>
                    <option value="image">Image</option>
                    <option value="code">Code</option>
                    <option value="url">URL</option>
                </select>

                <button
                    @click="clearContent"
                    class="px-3 py-2 text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    Clear
                </button>
            </div>
            <!-- emitSetData -->

            <button
                @click="emitSetData"
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed flex items-center space-x-2"
                :disabled="!content"
            >
                <LucideIcon name="save" class="w-5 h-5" />
                <span>Save Changes</span>
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import DOMPurify from 'dompurify';
import LucideIcon from '@/Components/Common/LucideIcon.vue';
import { QMenu, QBtnDropdown } from 'quasar';

// Props
const props = defineProps({
    maxSize: {
        type: Number,
        default: 5 * 1024 * 1024 // 5MB
    },
    allowedTypes: {
        type: Array,
        default: () => ['text', 'html', 'image', 'code', 'url']
    }
});

// Emits
const emit = defineEmits(['update:content', 'error', 'change', 'set_data']);

// Refs
const pasteZone = ref(null);
const content = ref(null);
const contentType = ref('text');
const processing = ref(false);
const editableContent = ref(null);
const imageAltText = ref('');

// Computed
const sanitizedContent = computed(() => {
    if (contentType.value === 'html') {
        return DOMPurify.sanitize(content.value);
    }
    return content.value;
});

// Methods
const detectContentType = (text) => {
    if (text.match(/<[^>]*>/)) return 'html';
    if (text.match(/^https?:\/\//)) return 'url';
    if (text.match(/^(const|let|var|function|class|import|export|if|for|while)/)) return 'code';
    return 'text';
};

const handlePaste = async (event) => {
    try {
        processing.value = true;
        event.preventDefault();

        const selection = window.getSelection();
        const range = selection.getRangeAt(0);
        const clipboardItems = event.clipboardData.items;

        // Handle HTML content
        const htmlContent = event.clipboardData.getData('text/html');
        if (htmlContent) {
            const sanitizedHtml = DOMPurify.sanitize(htmlContent);
            const div = document.createElement('div');
            div.innerHTML = sanitizedHtml;

            // Clean any Word-specific or unwanted formatting
            const cleanElement = (element) => {
                if (element.nodeType === 1) { // Element node
                    const attrs = element.attributes;
                    for (let i = attrs.length - 1; i >= 0; i--) {
                        const attr = attrs[i];
                        if (!['src', 'href', 'alt'].includes(attr.name)) {
                            element.removeAttribute(attr.name);
                        }
                    }
                    Array.from(element.children).forEach(cleanElement);
                }
            };
            cleanElement(div);

            // Insert at cursor position
            const fragment = document.createDocumentFragment();
            Array.from(div.childNodes).forEach(node => fragment.appendChild(node));
            range.deleteContents();
            range.insertNode(fragment);
            return;
        }

        // Handle plain text
        const text = event.clipboardData.getData('text/plain');
        if (text) {
            const textNode = document.createTextNode(text);
            range.deleteContents();
            range.insertNode(textNode);

            // Move cursor to end of inserted text
            range.setStartAfter(textNode);
            range.setEndAfter(textNode);
            selection.removeAllRanges();
            selection.addRange(range);
            return;
        }

        // Handle images
        for (const item of clipboardItems) {
            if (item.type.startsWith('image/')) {
                const blob = item.getAsFile();
                if (blob.size > props.maxSize) {
                    throw new Error('Image size exceeds limit');
                }
                const imageUrl = await readFileAsDataURL(blob);
                const img = document.createElement('img');
                img.src = imageUrl;
                range.deleteContents();
                range.insertNode(img);
                return;
            }
        }

    } catch (error) {
        emit('error', error);
    } finally {
        processing.value = false;
    }
};

const handleDrop = async (event) => {
    try {
        processing.value = true;
        const file = event.dataTransfer.files[0];

        if (file) {
            if (file.size > props.maxSize) {
                throw new Error('File size exceeds limit');
            }

            if (file.type.startsWith('image/')) {
                const dataUrl = await readFileAsDataURL(file);
                updateContent({
                    type: 'image',
                    data: dataUrl,
                    contentType: 'image'
                });
            } else {
                const text = await file.text();
                const detectedType = detectContentType(text);
                updateContent({
                    type: detectedType,
                    data: text,
                    contentType: detectedType
                });
            }
        }
    } catch (error) {
        emit('error', error);
    } finally {
        processing.value = false;
    }
};

const readFileAsDataURL = (file) => {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = () => resolve(reader.result);
        reader.onerror = reject;
        reader.readAsDataURL(file);
    });
};

const updateContent = (result) => {
    if (!props.allowedTypes.includes(result.type)) {
        emit('error', new Error(`Content type '${result.type}' not allowed`));
        return;
    }

    content.value = result.data;
    contentType.value = result.type;

    if (result.type === 'image') {
        imageAltText.value = '';
    }

    emit('update:content', {
        content: result.data,
        type: result.type,
        ...(result.type === 'image' ? { altText: imageAltText.value } : {})
    });
};

const handleTypeChange = () => {
    updateContent({
        type: contentType.value,
        data: content.value
    });
};

const clearContent = () => {
    content.value = null;
    contentType.value = 'text';
    emit('update:content', null);
    emit('change', null);
};

// Add new method to request clipboard content
const requestClipboardContent = async (mode = 'replace') => {
    try {
        processing.value = true;
        const clipboardItems = await navigator.clipboard.read();

        for (const item of clipboardItems) {
            // Handle text/html content
            if (item.types.includes('text/html')) {
                const blob = await item.getType('text/html');
                const html = await blob.text();
                const sanitizedHtml = DOMPurify.sanitize(html);

                if (mode === 'replace') {
                    content.value = sanitizedHtml;
                    contentType.value = 'html';
                    emit('update:content', { content: sanitizedHtml, type: 'html' });
                } else {
                    const selection = window.getSelection();
                    const range = selection.getRangeAt(0);
                    const div = document.createElement('div');
                    div.innerHTML = sanitizedHtml;
                    const fragment = document.createDocumentFragment();
                    Array.from(div.childNodes).forEach(node => fragment.appendChild(node));
                    range.insertNode(fragment);
                }
                return;
            }

            // Handle images
            if (item.types.some(type => type.startsWith('image/'))) {
                const imageType = item.types.find(type => type.startsWith('image/'));
                const blob = await item.getType(imageType);
                const imageUrl = await readFileAsDataURL(blob);

                if (mode === 'replace') {
                    content.value = imageUrl;
                    contentType.value = 'image';
                    emit('update:content', { content: imageUrl, type: 'image' });
                } else {
                    const selection = window.getSelection();
                    const range = selection.getRangeAt(0);
                    const img = document.createElement('img');
                    img.src = imageUrl;
                    range.insertNode(img);
                }
                return;
            }

            // Handle plain text
            if (item.types.includes('text/plain')) {
                const blob = await item.getType('text/plain');
                const text = await blob.text();
                const detectedType = detectContentType(text);

                if (mode === 'replace') {
                    content.value = text;
                    contentType.value = detectedType;
                    emit('update:content', { content: text, type: detectedType });
                } else {
                    const selection = window.getSelection();
                    const range = selection.getRangeAt(0);
                    const textNode = document.createTextNode(text);
                    range.insertNode(textNode);
                }
                return;
            }
        }
    } catch (error) {
        emit('error', error);
    } finally {
        processing.value = false;
    }
};

const handleContentEdit = (event) => {
    if (contentType.value === 'html') {
        content.value = event.target.innerHTML;
    } else if (contentType.value === 'url') {
        content.value = event.target.value;
    } else {
        content.value = event.target.innerText;
    }

    emit('update:content', {
        content: content.value,
        type: contentType.value
    });
};

const handleImageAltTextChange = () => {
    emit('update:content', {
        content: content.value,
        type: contentType.value,
        altText: imageAltText.value
    });
};

// Add new method to emit set_data
const emitSetData = () => {

    const data = {
        content: content.value,
        type: contentType.value
    };

    // Add additional data based on content type
    if (contentType.value === 'image') {
        data.altText = imageAltText.value;
    } else if (contentType.value === 'html') {
        // Ensure HTML is sanitized before emitting
        data.content = DOMPurify.sanitize(content.value);
    } else if (contentType.value === 'code') {
        // Preserve code formatting
        data.content = content.value.trim();
    }

    emit('set_data', data);
};

// Add new method to convert images to base64
const convertImagesToBase64 = async (html) => {
    const div = document.createElement('div');
    div.innerHTML = html;

    const images = div.getElementsByTagName('img');
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
    return div.innerHTML;
};

// Add new paste handler for base64 conversion
const handlePasteWithBase64 = async (event) => {
    try {
        processing.value = true;
        event.preventDefault();

        const editableDiv = editableContent.value;
        let range;
        let selection = window.getSelection();

        // Create a new range if there's no selection
        if (!selection.rangeCount) {
            range = document.createRange();
            if (editableDiv) {
                range.selectNodeContents(editableDiv);
                range.collapse(false); // Collapse to end
                selection = window.getSelection();
                selection.removeAllRanges();
                selection.addRange(range);
            }
        } else {
            range = selection.getRangeAt(0);
        }

        const clipboardItems = event.clipboardData.items;

        // Handle HTML content
        const htmlContent = event.clipboardData.getData('text/html');
        if (htmlContent) {
            const sanitizedHtml = DOMPurify.sanitize(htmlContent);
            const htmlWithBase64 = await convertImagesToBase64(sanitizedHtml);

            const div = document.createElement('div');
            div.innerHTML = htmlWithBase64;

            // Clean formatting
            cleanElement(div);

            // Insert at cursor position or append to content
            const fragment = document.createDocumentFragment();
            Array.from(div.childNodes).forEach(node => fragment.appendChild(node));

            if (range) {
                range.deleteContents();
                range.insertNode(fragment);
            } else if (editableDiv) {
                editableDiv.appendChild(fragment);
            }
            return;
        }

        // Handle images directly
        for (const item of clipboardItems) {
            if (item.type.startsWith('image/')) {
                const blob = item.getAsFile();
                if (blob.size > props.maxSize) {
                    throw new Error('Image size exceeds limit');
                }
                const base64 = await readFileAsDataURL(blob);
                const img = document.createElement('img');
                img.src = base64;

                if (range) {
                    range.deleteContents();
                    range.insertNode(img);
                } else if (editableDiv) {
                    editableDiv.appendChild(img);
                }
                return;
            }
        }

        // Handle plain text
        const text = event.clipboardData.getData('text/plain');
        if (text) {
            const textNode = document.createTextNode(text);
            if (range) {
                range.deleteContents();
                range.insertNode(textNode);
                // Update selection
                range.setStartAfter(textNode);
                range.setEndAfter(textNode);
                selection.removeAllRanges();
                selection.addRange(range);
            } else if (editableDiv) {
                editableDiv.appendChild(textNode);
            }
        }

    } catch (error) {
        emit('error', error);
    } finally {
        processing.value = false;
    }
};

// Add new method to request clipboard content with base64 conversion
const requestClipboardContentWithBase64 = async () => {
    try {
        const clipboardText = await navigator.clipboard.readText();
        const clipboardEvent = new ClipboardEvent('paste', {
            clipboardData: new DataTransfer()
        });

        // Add the clipboard text to the synthetic event
        Object.defineProperty(clipboardEvent.clipboardData, 'getData', {
            value: (type) => type === 'text/plain' ? clipboardText : ''
        });

        await handlePasteWithBase64(clipboardEvent);
    } catch (error) {
        // Fallback for browsers that don't support clipboard API
        try {
            const pasteZoneElement = pasteZone.value;
            if (pasteZoneElement) {
                pasteZoneElement.focus();
                document.execCommand('paste');
            }
        } catch (fallbackError) {
            emit('error', fallbackError);
        }
    }
};
</script>

<style scoped>
.clipboard-handler {
    width: 100%;
}

.paste-zone {
    min-height: 200px;
    /* border: 2px dashed #ccc; */
    border-radius: 8px;
    padding: 1rem;
    cursor: pointer;
    transition: all 0.3s ease;
}

.paste-zone:focus {
    outline: none;
    border-color: #4f46e5;
}

.paste-zone.is-processing {
    opacity: 0.7;
    pointer-events: none;
}

.paste-instructions {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
    color: #666;
}

.content-preview {
    max-height: 500px;
    overflow-y: auto;
}

.image-content {
    max-width: 100%;
    height: auto;
}

.code-content {
    background: #f4f4f4;
    padding: 1rem;
    border-radius: 4px;
    white-space: pre-wrap;
}

.controls {
    margin-top: 1rem;
    display: flex;
    gap: 1rem;
    align-items: center;
}

.clear-btn {
    padding: 0.5rem 1rem;
    background: #ef4444;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.clear-btn:hover {
    background: #dc2626;
}

.icon {
    width: 32px;
    height: 32px;
    margin-bottom: 1rem;
    color: #666;
}

.paste-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0.5rem 1rem;
    background-color: #4f46e5;
    color: white;
    border: none;
    border-radius: 0.375rem;
    cursor: pointer;
    transition: background-color 0.2s;
}

.paste-btn:hover {
    background-color: #4338ca;
}

.paste-btn:disabled {
    background-color: #9ca3af;
    cursor: not-allowed;
}

.paste-btn-base64 {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0.5rem 1rem;
    background-color: #6366f1;
    color: white;
    border: none;
    border-radius: 0.375rem;
    cursor: pointer;
    transition: background-color 0.2s;
}

.paste-btn-base64:hover {
    background-color: #4f46e5;
}

.paste-btn-base64:disabled {
    background-color: #9ca3af;
    cursor: not-allowed;
}

.editable-content {
    @apply p-4 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent;
    min-height: 100px;
}

.html-content {
    @apply prose max-w-none;
}

.code-content {
    @apply font-mono bg-gray-50;
}

.url-input {
    @apply w-full p-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent;
}

.image-wrapper {
    @apply space-y-2;
}

.image-alt-text {
    @apply w-full p-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent;
    min-height: 60px;
}

.text-content {
    @apply whitespace-pre-wrap;
}

.controls {
    @apply border-t border-gray-200 pt-4 mt-4;
}

button {
    @apply transition-all duration-200;
}

button:disabled {
    @apply opacity-50 cursor-not-allowed;
}

.controls select {
    @apply text-sm;
}

.dropdown:hover .dropdown-content {
    display: block;
}

.dropdown-content {
    min-width: 160px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}

.dropdown-content button {
    transition: background-color 0.2s;
}

.dropdown-content button:hover {
    background-color: #f3f4f6;
}
</style>



