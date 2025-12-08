<template>
    <div class="space-y-4">
        <div class="flex items-center gap-2 mb-4">
            <select
                v-model="contentType"
                class="rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                @change="handleTypeChange"
            >
                <option value="text">Plain Text</option>
                <option value="html">Rich HTML</option>
                <option value="screenshot">Screenshot</option>
                <option value="text_img">Text with Image</option>
                <option value="todo_list">todo_list</option>
 <option value="force_html">force_html</option>
            </select>
 
        </div>

        <!-- Plain Text -->
        <textarea class="  "
            v-if="contentType === 'text'"
            :value="modelValue"
            @input="$emit('update:modelValue', $event.target.value)"
            :rows="rows"
            :class="baseClasses"
            :required="required"
            :placeholder="placeholder"
        ></textarea>

        <!-- Rich HTML Editor -->
        <div class="p-0" v-if="contentType === 'html'">
            <Editor7
                v-model="editorContent"
                :locale="'en'"
                :mode="'default'"
                :height="'300px'"
                :placeholder="placeholder"
                @update:modelValue="handleEditorChange"
                @error="handleEditorError"
            />
        </div>

        <!-- Screenshot Upload -->
        <div v-if="contentType === 'screenshot'" class="space-y-2">
            <input
                type="file"
                accept="image/*"
                @change="handleImageUpload"
                class="hidden"
                ref="fileInput"
            />
            <button
                @click="$refs.fileInput.click()"
                class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700"
            >
                Upload Screenshot
            </button>
            <img
                v-if="modelValue"
                :src="modelValue"
                class="max-w-full h-auto rounded"
                alt="Screenshot"
            />
        </div>

        <!-- Text with Image -->
        <div v-if="contentType === 'text_img'" class="space-y-4">
            <textarea
                :value="textContent"
                @input="updateTextAndImage('text', $event.target.value)"
                :rows="rows"
                :class="baseClasses"
                placeholder="Enter text content..."
            ></textarea>
            <div>
                <input
                    type="file"
                    accept="image/*"
                    @change="handleTextImageUpload"
                    class="hidden"
                    ref="textImageInput"
                />
                <button
                    @click="$refs.textImageInput.click()"
                    class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700"
                >
                    Add Image
                </button>
                <img
                    v-if="imageUrl"
                    :src="imageUrl"
                    class="max-w-full h-auto mt-2 rounded"
                    alt="Uploaded image"
                />
            </div>
        </div>

        <!-- todo_list -->
        <div v-if="contentType === 'todo_list'" class="space-y-4">
            <TodoList7
                v-model:todos="todos"
                :show-filters="true"
                :show-priority="true"
                :show-stats="true"
                placeholder="Enter a new task..."
                add-button-text="Add Task"
                @add="handleTodoDelete"
                @delete="handleTodoDelete"
                @toggle="handleTodoToggle"
                @clear-completed="handleTodoClearCompleted"
                @save_data="$emit('save_data')"
            />
        </div>

        <div class="p-0" v-if="contentType === 'force_html'">
        <div class="p-0" v-html="editorContent">
        </div>
        <details>
            <summary>code</summary>
            <pre>{{ editorContent }}</pre>
        </details>

        </div>

    </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted } from 'vue';
import Editor7 from './Editor7tiptap.vue';
import TodoList7 from './TodoList7.vue';

const props = defineProps({
    modelValue: {
        type: String,
        default: ''
    },
    contentType: {
        type: String,
        default: 'text'
    },
    rows: {
        type: [String, Number],
        default: 4
    },
    required: {
        type: Boolean,
        default: false
    },
    placeholder: {
        type: String,
        default: ''
    }
});

const emit = defineEmits(['update:modelValue', 'update:contentType','save_data']);

const showEditor = ref(false);
const contentType = ref(props.contentType);
const textContent = ref('');
const imageUrl = ref('');
const fileInput = ref(null);
const textImageInput = ref(null);
const editorContent = ref(props.modelValue);
const todos = ref((() => {
    if (props.modelValue && props.contentType === 'todo_list') {
        try {
            return JSON.parse(props.modelValue);
        } catch (e) {
            console.error('Error parsing initial todos:', e);
            return [];
        }
    }
    return [];
})());

const baseClasses = "mt-1 block w-full rounded-lg border-gray-300 shadow-sm transition-all duration-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500";

watch(contentType, (newType) => {
    emit('update:contentType', newType);
});

watch(() => props.modelValue, (newValue) => {
    if (contentType.value === 'todo_list' && newValue) {
        try {
            const parsedTodos = JSON.parse(newValue);
            // Only update if the todos are different
            if (JSON.stringify(todos.value) !== JSON.stringify(parsedTodos)) {
                todos.value = parsedTodos;
            }
        } catch (e) {
            console.error('Error parsing todos:', e);
            todos.value = [];
        }
    } else if (contentType.value === 'html') {
        editorContent.value = newValue || '';
    }
});

watch(contentType, (newType, oldType) => {
    if (newType === 'todo_list' && oldType !== 'todo_list') {
        try {
            todos.value = props.modelValue ? JSON.parse(props.modelValue) : [];
        } catch (e) {
            console.error('Error parsing todos on type change:', e);
            todos.value = [];
        }
    }
});

const handleTypeChange = async () => {
    if (contentType.value === 'html') {
        editorContent.value = props.modelValue || '';
        showEditor.value = false;
        await nextTick();
        showEditor.value = true;
    } else if (contentType.value === 'todo_list') {
        todos.value = [];
        emit('update:modelValue', JSON.stringify(todos.value));
    } else {
        emit('update:modelValue', '');
    }
};

const handleImageUpload = async (event) => {
    const file = event.target.files[0];
    if (file) {
        try {
            // Convert to base64
            const base64String = await new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.onload = () => resolve(reader.result);
                reader.onerror = reject;
                reader.readAsDataURL(file);
            });

            // Emit the base64 string
            emit('update:modelValue', base64String);
        } catch (error) {
            console.error('Error converting image to base64:', error);
        }
    }
};

const handleTextImageUpload = async (event) => {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            imageUrl.value = e.target.result;
            updateTextAndImage('image', e.target.result);
        };
        reader.readAsDataURL(file);
    }
};

const updateTextAndImage = (type, value) => {
    const content = {
        text: type === 'text' ? value : textContent.value,
        image: type === 'image' ? value : imageUrl.value
    };
    textContent.value = content.text;
    imageUrl.value = content.image;
    emit('update:modelValue', JSON.stringify(content));
};

const handleEditorChange = (newContent) => {
    try {
        editorContent.value = newContent;
        emit('update:modelValue', newContent);
    } catch (error) {
        handleEditorError(error);
    }
};

const handleEditorError = (error) => {
    console.error('Editor error:', error);
    // Reset content if there's an error
    editorContent.value = '';
    // Optionally show an error message to the user
    // You can use your preferred notification system here
};

// Remove the handleTodoAdd function since v-model:todos will handle additions

// Add a watch for todos
watch(todos, (newTodos) => {
    emit('update:modelValue', JSON.stringify(newTodos));
}, { deep: true });

const handleTodoDelete = (todoId) => {
    const updatedTodos = todos.value.filter(todo => todo.id !== todoId);
    todos.value = updatedTodos;
    emit('update:modelValue', JSON.stringify(updatedTodos));
};

const handleTodoToggle = (todo) => {
    const updatedTodos = todos.value.map(t =>
        t.id === todo.id ? { ...t, completed: !t.completed } : t
    );
    todos.value = updatedTodos;
    emit('update:modelValue', JSON.stringify(updatedTodos));
};

const handleTodoClearCompleted = () => {
    const updatedTodos = todos.value.filter(todo => !todo.completed);
    todos.value = updatedTodos;
    emit('update:modelValue', JSON.stringify(updatedTodos));
};

// Initialize editor when component is mounted if contentType is 'html'
onMounted(async () => {
    if (contentType.value === 'html') {
        await nextTick();
        showEditor.value = true;
    }
});
</script>


















