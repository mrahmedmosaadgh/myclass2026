<template>
    <div class="lesson-explain-container">        <!-- Background Image Selection -->
        <div class="controls-panel p-4 bg-white shadow-md mb-4 rounded-lg">            <div class="flex gap-4 items-center mb-4">
                <input                     type="file"
                    @change="handleBackgroundImage"                     accept="image/*"
                    class="hidden"                     ref="fileInput"
                >                <button
                    @click="$refs.fileInput.click()"                    class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
                >                    Choose Background
                </button>                <button
                    @click="toggleAllElements"                    class="px-4 py-2 bg-purple-500 text-white rounded hover:bg-purple-600"
                >                    {{ showAll ? 'Hide All' : 'Show All' }}
                </button>                <button
                    @click="addNewElement"                    class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600"
                >                    Add Element
                </button>            </div>
            <!-- Element Type Selection for New Elements -->
            <div v-if="isAddingElement" class="bg-gray-100 p-4 rounded-lg mb-4">                <h3 class="font-medium mb-2">Choose Element Type:</h3>
                <div class="flex gap-4">                    <button
                        v-for="type in ['text', 'image', 'rectangle']"                         :key="type"
                        @click="createNewElement(type)"                        class="px-3 py-1 bg-white rounded shadow hover:bg-gray-50"
                    >                        {{ type.charAt(0).toUpperCase() + type.slice(1) }}
                    </button>                </div>
            </div>        </div>
        <!-- Main Canvas Area -->
        <div
            class="canvas-area pt-8 relative border-2 border-gray-300 rounded-lg overflow-hidden"
            style="min-height: 600px; height: auto; width: 100%;"
            @click="handleCanvasClick"
            @keydown="handleKeyboardPaste"
            tabindex="0"
            ref="canvasArea"
        >
            <!-- Background Image -->
            <img
                v-if="backgroundImage"
                :src="backgroundImage"
                class="absolute top-0 left-0 w-full h-full object-contain"
                @load="adjustCanvasToImage"
                ref="backgroundImageRef"
            >
            <!-- Elements Container -->
            <TransitionGroup
                name="element"
                tag="div"
                class="relative z-10 w-full h-full"
            >
                <div
                    v-for="element in elements"
                    :key="element.id"
                    class="absolute transition-transform duration-100 group element-container"
                    :style="{
                        left: `${element.x}%`,
                        top: `${element.y}%`,
                        opacity: element.visible ? 1 : 0.3,
                        transform: `scale(${element.visible ? 1 : 1.1})`,
                        position: 'absolute',
                        zIndex: 10,
                        maxWidth: '50%',
                        willChange: 'transform' // Optimize for animations
                    }"
                >
                    <!-- Drag Handle -->
                    <div
                    @dblclick="toggleElement(element)"
                        class="absolute  -top-2 left-0 w-8 h-8 bg-gray-500 text-white flex items-center justify-center text-sm hover:bg-gray-600 cursor-move z-50 rounded-full opacity-0 group-hover:opacity-100 transition-opacity"
                        @mousedown.stop="startDrag($event, element)"
                        title="Drag"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M15 3h6v6M14 10l7-7m-7 17v-6m0 6h6m-6 0l7-7M9 3H3v6m0-6l7 7m-7 4v6h6m-6-6l7 7"/>
                        </svg>
                    </div>

                    <!-- Text Element -->
                    <div v-if="element.type === 'text'"
                        class="p-2 rounded relative group inline-block"
                        :style="{
                            backgroundColor: element.transparentBg ? 'transparent' : (element.backgroundColor || '#ffffff'),
                            border: element.borderWidth ? `${element.borderWidth}px solid ${element.borderColor}` : 'none',
                            borderRadius: element.borderRadius ? `${element.borderRadius}px` : '0',
                            userSelect: 'none',
                            maxWidth: '100%', // Ensure text wraps within container
                            wordBreak: 'break-word',
                            willChange: 'transform' // Optimize for animations
                        }"
                    >
                    <div
@dblclick="toggleElement(element)"
                    class="p-0" v-if="element.visible">

                        <span
                        class="bg-transparent outline-none inline-block"
                        :style="{
                            color: element.textColor || '#000000',
                            fontSize: `${element.fontSize || 16}px`,
                            wordWrap: 'break-word',
                            userSelect: 'none',
                            maxWidth: '100%'
                        }"
                            v-html="element.content"
                            ></span>

                        </div>
                        <!-- Add Element Controls for text -->
                        <div class="absolute -top-8 right-0 flex gap-1 bg-white p-1 rounded shadow-md opacity-0 group-hover:opacity-100 transition-opacity">
                            <button
                                @click.stop="handlePasteHtml(element)"
                                class="w-6 h-6 rounded-full bg-blue-500 text-white flex items-center justify-center text-sm hover:bg-blue-600"
                                title="Paste HTML"
                            >
                                📋
                            </button>
                            <button
                                @click.stop="copyElement(element)"
                                class="w-6 h-6 rounded-full bg-green-500 text-white flex items-center justify-center text-sm hover:bg-green-600"
                                title="Copy Element"
                            >
                                📋
                            </button>
                            <button
                                @click.stop="openElementSettings(element)"
                                class="w-6 h-6 rounded-full bg-gray-500 text-white flex items-center justify-center text-sm hover:bg-gray-600"
                                title="Settings"
                            >
                                ⚙️
                            </button>
                            <button
                                @click.stop="removeElement(element)"
                                class="w-6 h-6 rounded-full bg-red-500 text-white flex items-center justify-center text-sm hover:bg-red-600"
                                title="Remove"
                            >
                                ×
                            </button>
                        </div>
                    </div>
                    <!-- Image Element -->
                    <div v-else-if="element.type === 'image'"                        class="w-32 h-32 overflow-hidden rounded"
                    >                        <img
                            :src="element.content"                            class="w-full h-full object-cover"
                        >                    </div>
                    <!-- Rectangle Element -->
                    <div v-else-if="element.type === 'rectangle'"
                        class="relative group"
                        :style="{
                            width: `${element.width || 128}px`,
                            height: `${element.height || 96}px`,
                            backgroundColor: element.backgroundColor || '#ffffff',
                            border: `${element.borderWidth || 1}px solid ${element.borderColor || '#000000'}`,
                            borderRadius: `${element.borderRadius || 4}px`,
                            position: 'relative'
                        }"
                    >
                        <!-- Add Resize Handles -->
                        <div class="resize-handles opacity-0 group-hover:opacity-100">
                            <!-- Center-right handle -->
                            <div class="absolute w-3 h-3 top-1/2 -right-1.5 -mt-1.5 bg-white border-2 border-blue-500 rounded-full cursor-e-resize"
                                @mousedown.stop="startResize($event, element, 'right')"></div>

                            <!-- Center-bottom handle -->
                            <div class="absolute w-3 h-3 -bottom-1.5 left-1/2 -ml-1.5 bg-white border-2 border-blue-500 rounded-full cursor-s-resize"
                                @mousedown.stop="startResize($event, element, 'bottom')"></div>

                            <!-- Bottom-right corner handle -->
                            <div class="absolute w-3 h-3 -bottom-1.5 -right-1.5 bg-white border-2 border-blue-500 rounded-full cursor-se-resize"
                                @mousedown.stop="startResize($event, element, 'bottom-right')"></div>
                        </div>

                        <!-- Element Controls -->
                        <div class="absolute -top-8 right-0 flex gap-1 bg-white p-1 rounded shadow-md opacity-0 group-hover:opacity-100 transition-opacity">
                            <button
                                @click.stop="copyElement(element)"
                                class="w-6 h-6 rounded-full bg-green-500 text-white flex items-center justify-center text-sm hover:bg-green-600"
                                title="Copy Element"
                            >
                                📋
                            </button>
                            <button
                                @click.stop="toggleElement(element)"
                                class="w-6 h-6 rounded-full bg-blue-500 text-white flex items-center justify-center text-sm hover:bg-blue-600"
                                title="Toggle Visibility"
                            >
                                {{ element.visible ? '👁️' : '👁️‍🗨️' }}
                            </button>
                            <button
                                @click.stop="openElementSettings(element)"
                                class="w-6 h-6 rounded-full bg-gray-500 text-white flex items-center justify-center text-sm hover:bg-gray-600"
                                title="Settings"
                            >
                                ⚙️
                            </button>
                            <button
                                @click.stop="removeElement(element)"
                                class="w-6 h-6 rounded-full bg-red-500 text-white flex items-center justify-center text-sm hover:bg-red-600"
                                title="Remove"
                            >
                                ×
                            </button>
                        </div>
                    </div>
                    <!-- Element Controls -->
                                  <!-- <div class="absolute top-0 right-0 -mt-2 -mr-2 flex gap-1">
                        <button                             @click="toggleElement(element)"
                            class="w-6 h-6 rounded-full bg-blue-500 text-white flex items-center justify-center text-sm"                        >
                            {{ element.visible ? '👁️' : '👁️‍🗨️' }}                        </button>
                        <button                             @click="removeElement(element)"
                            class="w-6 h-6 rounded-full bg-red-500 text-white flex items-center justify-center text-sm"                        >
                            ×                        </button>
                    </div> -->


                </div>
            </TransitionGroup>        </div>
        <!-- Rectangle Settings Modal -->
        <Modal
            :show="!!selectedElement && selectedElement.type === 'rectangle'"
            @close="closeElementSettings"
        >
            <template #title>Rectangle Settings</template>
            <template #content>
                <div class="space-y-4 p-4" v-if="tempSettings">
                    <!-- Dimensions -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Width (px)</label>
                            <input
                                type="number"
                                v-model="tempSettings.width"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Height (px)</label>
                            <input
                                type="number"
                                v-model="tempSettings.height"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                        </div>
                    </div>

                    <!-- Background Color -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Background Color</label>
                        <input
                            type="color"
                            v-model="tempSettings.backgroundColor"
                            class="mt-1 block w-full"
                        >
                    </div>

                    <!-- Border Settings -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Border Width (px)</label>
                            <input
                                type="number"
                                v-model="tempSettings.borderWidth"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Border Color</label>
                            <input
                                type="color"
                                v-model="tempSettings.borderColor"
                                class="mt-1 block w-full"
                            >
                        </div>
                    </div>

                    <!-- Border Radius -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Border Radius (px)</label>
                        <input
                            type="number"
                            v-model="tempSettings.borderRadius"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                    </div>
                </div>
            </template>
            <template #footer>
                <div class="flex justify-end space-x-3 px-6 py-4 bg-gray-50 border-t">
                    <SecondaryButton @click="closeElementSettings">
                        Cancel
                    </SecondaryButton>
                    <PrimaryButton @click="saveElementSettings">
                        Save
                    </PrimaryButton>
                </div>
            </template>
        </Modal>
        <!-- Text Settings Modal -->
        <Modal
            :show="!!selectedElement && selectedElement.type === 'text'"
            @close="closeElementSettings"
        >
            <template #title>Text Settings</template>
            <template #content>
                <div class="space-y-4 p-4" v-if="tempSettings">
                    <!-- Text Content -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Text Content</label>
                        <textarea

                            v-model="tempSettings.content"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        ></textarea>
                    </div>

                    <!-- Text Color -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Text Color</label>
                        <input
                            type="color"
                            v-model="tempSettings.textColor"
                            class="mt-1 block w-full"
                        >
                    </div>

                    <!-- Background Color -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Background Color</label>
                        <div class="flex items-center gap-2">
                            <input
                                type="color"
                                v-model="tempSettings.backgroundColor"
                                class="mt-1 block w-full"
                            >
                            <label class="inline-flex items-center">
                                <input
                                    type="checkbox"
                                    v-model="tempSettings.transparentBg"
                                    class="rounded border-gray-300"
                                >
                                <span class="ml-2 text-sm text-gray-600">Transparent</span>
                            </label>
                        </div>
                    </div>

                    <!-- Font Size -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Font Size (px)</label>
                        <input
                            type="number"
                            v-model="tempSettings.fontSize"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            min="8"
                            max="72"
                        >
                    </div>

                    <!-- Border Settings -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Border Width (px)</label>
                            <input
                                type="number"
                                v-model="tempSettings.borderWidth"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                min="0"
                                max="10"
                            >
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Border Color</label>
                            <input
                                type="color"
                                v-model="tempSettings.borderColor"
                                class="mt-1 block w-full"
                            >
                        </div>
                    </div>

                    <!-- Border Radius -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Border Radius (px)</label>
                        <input
                            type="number"
                            v-model="tempSettings.borderRadius"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            min="0"
                            max="50"
                        >
                    </div>

                    <!-- Min Width -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Minimum Width (px)</label>
                        <input
                            type="number"
                            v-model="tempSettings.minWidth"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            min="100"
                        >
                    </div>

                    <!-- Word Wrap -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Text Wrap</label>
                        <select
                            v-model="tempSettings.wordWrap"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                            <option value="break-word">Break Word</option>
                            <option value="normal">No Wrap</option>
                        </select>
                    </div>
                </div>
            </template>
            <template #footer>
                <div class="flex justify-end space-x-3 px-6 py-4 bg-gray-50 border-t">
                    <SecondaryButton @click="closeElementSettings">Cancel</SecondaryButton>
                    <PrimaryButton @click="saveElementSettings">Save</PrimaryButton>
                </div>
            </template>
        </Modal>
    </div></template>
<script setup>
import { ref } from 'vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DOMPurify from 'dompurify';

// State
const elements = ref([]);
const isAddingElement = ref(false);
const showAll = ref(true);
const draggedElement = ref(null);
const dragOffset = ref({ x: 0, y: 0 });
const fileInput = ref(null);
const canvasArea = ref(null);
const selectedElement = ref(null);
const backgroundImageRef = ref(null);
const backgroundImage = ref(null);
const resizing = ref(null);
const resizeHandle = ref(null);
const initialSize = ref({ width: 0, height: 0 });
const initialPosition = ref({ x: 0, y: 0 });
const initialMousePosition = ref({ x: 0, y: 0 });
const copiedElement = ref(null);
// Add this ref to store the last edited rectangle settings
const lastRectangleSettings = ref({
    width: 200,
    height: 100,
    backgroundColor: '#ffffff',
    borderWidth: 2,
    borderColor: '#000000',
    borderRadius: 4,
});

// Add this ref to store the last edited text settings
const lastTextSettings = ref({
    textColor: 'black',
    backgroundColor: '#ffffff',
    fontSize: 16,
    transparentBg: false,
    borderWidth: 0,
    borderColor: '#000000',
    borderRadius: 0,
    minWidth: '100px',
    wordWrap: 'break-word'
});

// Add this ref
const tempSettings = ref({
    // Text settings
    content: '',
    textColor: 'black',
    backgroundColor: '#ffffff',
    fontSize: 16,
    transparentBg: false,
    borderWidth: 0,
    borderColor: '#000000',
    borderRadius: 0,
    minWidth: '100px',
    wordWrap: 'break-word',
    // Rectangle settings
    width: 200,
    height: 100,
    backgroundColor: '#ffffff',
    borderWidth: 2,
    borderColor: '#000000',
    borderRadius: 4
});

// Add these refs at the top of your script
const dragStartPos = ref({ x: 0, y: 0 });
const canvasRect = ref(null);

// Element Management
const addNewElement = () => {
    isAddingElement.value = true;
};

const createNewElement = (type) => {
    const newElement = {
        id: Date.now(),
        type,
        x: 50, // This is now percentage (50%)
        y: 50, // This is now percentage (50%)
        visible: true,
        content: type === 'text' ? 'New Text' : '',
        backgroundColor: type === 'rectangle' ? '#ffffff' : 'transparent',
        textColor: 'black',
        width: type === 'rectangle' ? 200 : undefined,
        height: type === 'rectangle' ? 100 : undefined,
        borderWidth: type === 'rectangle' ? 2 : undefined,
        borderColor: type === 'rectangle' ? '#000000' : undefined,
        borderRadius: type === 'rectangle' ? 4 : undefined,
    };

    if (type === 'rectangle') {
        Object.assign(newElement, { ...lastRectangleSettings.value });
    } else if (type === 'text') {
        Object.assign(newElement, { ...lastTextSettings.value });
    }

    elements.value.push(newElement);
    isAddingElement.value = false;

    // Automatically open settings for new elements
    if (type === 'rectangle' || type === 'text') {
        selectedElement.value = newElement;
    }
};

const handleCanvasClick = (event) => {
    if (isAddingElement.value) {
        isAddingElement.value = false;
    }
    // Don't cancel copy here as it will interfere with paste functionality
};

// Drag and Drop Functionality
const startDrag = (event, element) => {
    event.preventDefault();
    draggedElement.value = element;

    // Store canvas dimensions once at drag start
    canvasRect.value = canvasArea.value.getBoundingClientRect();

    // Store initial mouse position
    dragStartPos.value = {
        x: event.clientX,
        y: event.clientY
    };

    // Store initial element position
    dragOffset.value = {
        x: element.x,
        y: element.y
    };

    document.addEventListener('mousemove', handleDrag);
    document.addEventListener('mouseup', stopDrag);
};

const handleDrag = (event) => {
    if (!draggedElement.value || !canvasRect.value) return;

    // Calculate delta movement in pixels
    const deltaX = event.clientX - dragStartPos.value.x;
    const deltaY = event.clientY - dragStartPos.value.y;

    // Convert delta to percentage of canvas size
    const percentX = (deltaX / canvasRect.value.width) * 100;
    const percentY = (deltaY / canvasRect.value.height) * 100;

    // Calculate new position
    let newX = dragOffset.value.x + percentX;
    let newY = dragOffset.value.y + percentY;

    // Apply constraints
    newX = Math.max(0, Math.min(90, newX)); // Leave some space for right edge
    newY = Math.max(0, Math.min(90, newY)); // Leave some space for bottom edge

    // Update position
    draggedElement.value.x = newX;
    draggedElement.value.y = newY;
};

const stopDrag = () => {
    draggedElement.value = null;
    canvasRect.value = null;
    document.removeEventListener('mousemove', handleDrag);
    document.removeEventListener('mouseup', stopDrag);
};

// Element settings functions
const openElementSettings = (element) => {
    selectedElement.value = element;
    // Create a deep copy of the element's settings
    if (element.type === 'text') {
        tempSettings.value = {
            content: element.content || '',
            textColor: element.textColor || 'black',
            backgroundColor: element.backgroundColor || '#ffffff',
            fontSize: element.fontSize || 16,
            transparentBg: element.transparentBg || false,
            borderWidth: element.borderWidth || 0,
            borderColor: element.borderColor || '#000000',
            borderRadius: element.borderRadius || 0,
            minWidth: element.minWidth || '100px',
            wordWrap: element.wordWrap || 'break-word',
            type: 'text'
        };
    } else if (element.type === 'rectangle') {
        tempSettings.value = {
            width: element.width || 200,
            height: element.height || 100,
            backgroundColor: element.backgroundColor || '#ffffff',
            borderWidth: element.borderWidth || 2,
            borderColor: element.borderColor || '#000000',
            borderRadius: element.borderRadius || 4,
            type: 'rectangle'
        };
    }
};

const closeElementSettings = () => {
    selectedElement.value = null;
    tempSettings.value = {
        content: '',
        textColor: '#000000',  // Changed from 'black'
        backgroundColor: '#ffffff',
        fontSize: 16,
        transparentBg: false,
        width: 200,
        height: 100,
        borderWidth: 2,
        borderColor: '#000000',  // Make sure this is also in hex format
        borderRadius: 4
    };
};

const handleBackgroundImage = (event) => {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            backgroundImage.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const adjustCanvasToImage = () => {
    if (backgroundImageRef.value) {
        const img = backgroundImageRef.value;
        const canvas = canvasArea.value;
        if (canvas) {
            canvas.style.height = `${img.naturalHeight}px`;
            canvas.style.width = `${img.naturalWidth}px`;
        }
    }
};

const startResize = (event, element, handle) => {
    event.stopPropagation();
    event.preventDefault();
    resizing.value = element;
    resizeHandle.value = handle;

    initialSize.value = {
        width: element.width,
        height: element.height
    };

    initialMousePosition.value = {
        x: event.clientX,
        y: event.clientY
    };

    document.addEventListener('mousemove', handleResize);
    document.addEventListener('mouseup', stopResize);
};

const handleResize = (event) => {
    if (!resizing.value) return;

    const canvasRect = canvasArea.value.getBoundingClientRect();
    const dx = event.clientX - initialMousePosition.value.x;
    const dy = event.clientY - initialMousePosition.value.y;

    switch (resizeHandle.value) {
        case 'right':
            resizing.value.width = Math.max(20, initialSize.value.width + dx);
            break;
        case 'bottom':
            resizing.value.height = Math.max(20, initialSize.value.height + dy);
            break;
        case 'bottom-right':
            resizing.value.width = Math.max(20, initialSize.value.width + dx);
            resizing.value.height = Math.max(20, initialSize.value.height + dy);
            break;
    }
};

const stopResize = () => {
    resizing.value = null;
    resizeHandle.value = null;
    document.removeEventListener('mousemove', handleResize);
    document.removeEventListener('mouseup', stopResize);
};

const toggleElement = (element) => {
    element.visible = !element.visible;
};

const toggleAllElements = () => {
    showAll.value = !showAll.value;
    elements.value.forEach(element => {
        element.visible = showAll.value;
    });
};

const removeElement = (element) => {
    const index = elements.value.findIndex(e => e.id === element.id);
    if (index !== -1) {
        elements.value.splice(index, 1);
    }
};

// Copy element function
const copyElement = (element) => {
    copiedElement.value = { ...element };
    canvasArea.value.addEventListener('click', pasteElement);
};

// Paste element function
const pasteElement = (event) => {
    if (!copiedElement.value) return;

    const canvasRect = canvasArea.value.getBoundingClientRect();
    const x = ((event.clientX - canvasRect.left) / canvasRect.width) * 100;
    const y = ((event.clientY - canvasRect.top) / canvasRect.height) * 100;

    const newElement = {
        ...copiedElement.value,
        id: Date.now(),
        x,
        y
    };

    elements.value.push(newElement);
    cancelCopy();
};

// Cancel copy function
const cancelCopy = () => {
    copiedElement.value = null;
    canvasArea.value.removeEventListener('click', pasteElement);
};

// Add this new function for handling transparent background
const toggleTransparentBg = () => {
    if (selectedElement.value && selectedElement.value.type === 'text') {
        if (selectedElement.value.transparentBg) {
            selectedElement.value.backgroundColor = 'transparent';
        } else {
            selectedElement.value.backgroundColor = '#ffffff';
        }
    }
};

// Add saveElementSettings function
const saveElementSettings = () => {
    if (!selectedElement.value || !tempSettings.value) return;

    const elementIndex = elements.value.findIndex(el => el.id === selectedElement.value.id);
    if (elementIndex !== -1) {
        if (selectedElement.value.type === 'text') {
            // Save text settings
            elements.value[elementIndex] = {
                ...elements.value[elementIndex],
                content: tempSettings.value.content,
                textColor: tempSettings.value.textColor,
                backgroundColor: tempSettings.value.backgroundColor,
                fontSize: tempSettings.value.fontSize,
                transparentBg: tempSettings.value.transparentBg,
                borderWidth: tempSettings.value.borderWidth,
                borderColor: tempSettings.value.borderColor,
                borderRadius: tempSettings.value.borderRadius,
                minWidth: tempSettings.value.minWidth,
                wordWrap: tempSettings.value.wordWrap
            };

            // Update lastTextSettings
            lastTextSettings.value = {
                textColor: tempSettings.value.textColor,
                backgroundColor: tempSettings.value.backgroundColor,
                fontSize: tempSettings.value.fontSize,
                transparentBg: tempSettings.value.transparentBg,
                borderWidth: tempSettings.value.borderWidth,
                borderColor: tempSettings.value.borderColor,
                borderRadius: tempSettings.value.borderRadius,
                minWidth: tempSettings.value.minWidth,
                wordWrap: tempSettings.value.wordWrap
            };
        } else if (selectedElement.value.type === 'rectangle') {
            // Save rectangle settings
            elements.value[elementIndex] = {
                ...elements.value[elementIndex],
                width: tempSettings.value.width,
                height: tempSettings.value.height,
                backgroundColor: tempSettings.value.backgroundColor,
                borderWidth: tempSettings.value.borderWidth,
                borderColor: tempSettings.value.borderColor,
                borderRadius: tempSettings.value.borderRadius
            };

            // Update lastRectangleSettings
            lastRectangleSettings.value = {
                width: tempSettings.value.width,
                height: tempSettings.value.height,
                backgroundColor: tempSettings.value.backgroundColor,
                borderWidth: tempSettings.value.borderWidth,
                borderColor: tempSettings.value.borderColor,
                borderRadius: tempSettings.value.borderRadius
            };
        }
    }

    closeElementSettings();
};

// Add these utility functions
const readFileAsDataURL = (blob) => {
    return new Promise((resolve) => {
        const reader = new FileReader();
        reader.onload = (e) => resolve(e.target.result);
        reader.readAsDataURL(blob);
    });
};

const handlePasteHtml = async (element) => {
    try {
        const clipboardItems = await navigator.clipboard.read();
        let result = {
            type: null,
            data: null,
            content_type: null
        };

        // Check for images first
        for (const item of clipboardItems) {
            const imageTypes = item.types.filter(type => type.startsWith('image/'));
            if (imageTypes.length > 0) {
                const imageType = imageTypes[0];
                const blob = await item.getType(imageType);
                const base64 = await readFileAsDataURL(blob);

                if (element.type === 'text') {
                    // If it's a text element, wrap the image in an HTML img tag
                    element.content = `<img src="${base64}" style="max-width: 100%; height: auto;">`;
                } else if (element.type === 'image') {
                    // If it's an image element, update the source directly
                    element.content = base64;
                }
                return;
            }
        }

        // If no image, try to get HTML content
        for (const item of clipboardItems) {
            if (item.types.includes('text/html')) {
                const blob = await item.getType('text/html');
                const html = await blob.text();
                const sanitizedHtml = DOMPurify.sanitize(html, {
                    ALLOWED_TAGS: ['p', 'b', 'i', 'u', 'strong', 'em', 'span', 'div', 'br', 'img'],
                    ALLOWED_ATTR: ['style', 'class', 'src'],
                    ADD_TAGS: ['img'],
                    ADD_ATTR: ['src']
                });
                element.content = sanitizedHtml;
                return;
            }
        }

        // Fallback to plain text
        const text = await navigator.clipboard.readText();
        element.content = text;

    } catch (err) {
        // Final fallback using older clipboard API
        try {
            const text = await navigator.clipboard.readText();
            element.content = text;
        } catch (error) {
            console.error('Clipboard operation failed:', error);
        }
    }
};

// Add a method to create a new element for pasted content
const createElementFromPaste = async (x, y) => {
    try {
        const clipboardItems = await navigator.clipboard.read();

        // Check for images first
        for (const item of clipboardItems) {
            const imageTypes = item.types.filter(type => type.startsWith('image/'));
            if (imageTypes.length > 0) {
                const imageType = imageTypes[0];
                const blob = await item.getType(imageType);
                const base64 = await readFileAsDataURL(blob);

                // Create new image element
                elements.value.push({
                    id: Date.now(),
                    type: 'image',
                    content: base64,
                    x: x,
                    y: y,
                    visible: true
                });
                return;
            }
        }

        // If no image, handle as text/html
        const text = await navigator.clipboard.readText();
        elements.value.push({
            id: Date.now(),
            type: 'text',
            content: text,
            x: x,
            y: y,
            visible: true,
            fontSize: 16,
            textColor: '#000000',
            backgroundColor: '#ffffff',
            transparentBg: false
        });

    } catch (error) {
        console.error('Failed to create element from paste:', error);
    }
};

// Optional: Add keyboard shortcut for paste
const handleKeyboardPaste = async (event) => {
    if (event.ctrlKey && event.key === 'v') {
        event.preventDefault();
        const rect = canvasArea.value.getBoundingClientRect();
        const x = ((event.clientX - rect.left) / rect.width) * 100;
        const y = ((event.clientY - rect.top) / rect.height) * 100;
        await createElementFromPaste(x, y);
    }
};
</script>
<style scoped>
.lesson-explain-container {    user-select: none;
}
.element-enter-active,.element-leave-active {
    transition: all 0.3s ease;}
.element-enter-from,
.element-leave-to {    opacity: 0;
    transform: scale(0.8);}
.canvas-area {
    background-color: #f8f8f8;    background-image: linear-gradient(45deg, #eee 25%, transparent 25%),
        linear-gradient(-45deg, #eee 25%, transparent 25%),        linear-gradient(45deg, transparent 75%, #eee 75%),
        linear-gradient(-45deg, transparent 75%, #eee 75%);    background-size: 20px 20px;
    background-position: 0 0, 0 10px, 10px -10px, -10px 0px;
}
/* Add styles for element controls */
.element-controls {
    opacity: 0;
    transition: opacity 0.2s ease;
}

.group:hover .element-controls {
    opacity: 1;
}
</style>
























































































































