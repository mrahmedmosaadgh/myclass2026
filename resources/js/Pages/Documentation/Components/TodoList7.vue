<template>
    <div class="max-w-2xl mx-auto p-6 bg-white rounded-xl shadow-lg">


        <!-- Add Todo Form -->
        <div class="flex flex-col gap-2 mb-6">
            <div class="mb-4 flex items-center gap-2">
                <!-- <input
                    type="checkbox"
                    id="enableHtml"
                    v-model="enableHtmlEditor"
                    @click="showEditor=!enableHtmlEditor"
                    class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500"
                > -->

                <label for="enableHtml" class="text-sm text-gray-600">
                    Enable Rich Text Editor
                    <TabButtons
            v-model="selectedTab"
            :tabs="tabs"
            @change="handleTabChange"
        />{{ selectedTab }}
                </label>
            </div>
            <div class="flex-1" @keyup.ctrl.enter="addTodo">


                <div class="p-0"
                v-if="selectedTab=='past_html'"
                >




                <use_paste
                v-model="newTodo2"
                @set_data="handleSetData"
                />


        Editor9   <Editor9
                   v-if="selectedTab=='html'"
            v-model="newTodo2"
             locale="en"
            :placeholder="'Write your content here...'"
            :height="'400px'"
            />
            <!-- @ready="handleEditorReady"
            @change="handleEditorChange"
            @blur="handleEditorBlur"
            @focus="handleEditorFocus" -->
                </div>
                <div class="p-0"
                v-if="enableHtmlEditor"
                >
                Editor7  <Editor9
                  v-if="selectedTab=='html'"
                    v-model="newTodo2"
                    :locale="'en'"
                    :mode="'default'"
                    :placeholder="placeholder"
                    :maxLength="1000"
                    :showWordCount="true"
                    :customToolbar="['bold', 'italic', '|', 'insertImage']"
                    />



                </div>
                <textarea
                   v-if="selectedTab=='textArea'"
                    v-model="newTodo2"
                    :placeholder="placeholder"
                    class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    rows="3"
                ></textarea>
                <input
@keyup.enter="addTodo()"
      v-model="newTodo2"
      :placeholder="placeholder"
                    class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"

                type="text"
v-if="selectedTab=='text'"
                >
            </div>
            <button
                @click="addTodo"
                class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors"
            >
                {{ addButtonText }}
            </button>
        </div>

        <!-- Filters -->
        <div v-if="showFilters" class="flex gap-4 mb-4 text-sm">
            <button
                v-for="filter in filters"
                :key="filter"
                @click="currentFilter = filter"
                :class="[
                    'px-3 py-1 rounded-full',
                    currentFilter === filter
                        ? 'bg-blue-500 text-white'
                        : 'bg-gray-100 hover:bg-gray-200'
                ]"
            >
                {{ filter }}
            </button>
        </div>

        <!-- Todo List -->
        <div class="space-y-2">
            <TransitionGroup name="list">
                <div
                    v-for="todo in filteredTodos"
                    :key="todo.id"
                    class="flex relative items-center gap-3 p-3 bg-gray-50 rounded-lg group hover:bg-gray-100 transition-colors"
                >
                    <!-- Checkbox -->
                    <input
                        type="checkbox"
                        :checked="todo.completed"
                        @change="toggleComplete(todo)"
                        class="w-5 h-5 rounded text-blue-500 focus:ring-blue-500"
                    />

                    <!-- Todo Text - Changed from span to div with v-html -->
                    <div class="flex flex-col">
                        <div @dblclick="toggleComplete(todo)"
                            v-html="todo.text"
                            :class="[
                                'flex-1',
                                todo.completed ? 'line-through text-gray-400 opacity-40' : ''
                            ]"
                        ></div>
                        <span class="text-xs text-gray-400 mt-1">
                            {{ new Date(todo.id).toLocaleString() }}
                        </span>
                    </div>

                    <!-- Priority Badge -->
                    <span
                        v-if="showPriority && todo.priority"
                        :class="[
                            'px-2   absolute -left-4 scale-75  top-0 py-1 rounded-full text-xs',
                            priorityColors[todo.priority]
                        ]"
                    >
                        {{ todo.priority }}
                    </span>

                    <!-- Actions -->
                    <div class="flex absolute right-4   gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button
                            v-if="showPriority"
                            @click="setPriority(todo)"
                            class="text-gray-500  hover:text-yellow-500"
                        >
                            ⭐
                        </button>
                        <button
                            @click="editTodo(todo)"
                            class="text-gray-500 hover:text-blue-500"
                        >
                            ✎
                        </button>
                        <button
                            @click="deleteTodo(todo.id)"
                            class="text-gray-500 hover:text-red-500"
                        >
                            ✕
                        </button>
                    </div>
                </div>
            </TransitionGroup>
        </div>

        <!-- Stats -->
        <div v-if="showStats" class="mt-4 flex justify-between text-sm text-gray-500">
            <span>{{ activeTodos.length }} items left</span>
            <button
                v-if="completedTodos.length"
                @click="clearCompleted"
                class="text-blue-500 hover:underline"
            >
                Clear completed
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch,onBeforeUnmount } from 'vue';
import Editor7 from './Editor7tiptap.vue';
import TabButtons from './TabButtons.vue';

// import Editor9 from './Editor9.vue';
import Editor9 from './ReusableHtmlViewer_Editor9.vue';
// ReusableHtmlViewer_Editor9
import use_paste from './use_paste.vue';
// import use_paste_mammoth from './use_paste_mammoth.vue';


// resources/js/Pages/Documentation/Components/use_paste.vue
const selectedTab = ref('text');

const tabs = [
    {
        label: 'Rich Text',
        value: 'html',
        // icon: HomeIcon
    },
    {
        label: ' Text Area',
        value: 'textArea',
        // icon: UserIcon
    },
    {
        label: ' Text',
        value: 'text',
        // icon: UserIcon
    },
    {
        label: 'view pasted contents only',
        value: 'past_html',
        // icon: UserIcon
    },
];
const handleTabChange = (tab) => {
    console.log('Tab changed:', tab);
    // selectedTab.value = tab;

    if (selectedTab.value === 'html') {
        enableHtmlEditor.value = true;
        showEditor.value = true;
    }

    // if (selectedTab.value === 'textArea') {
    //     enableHtmlEditor.value = true;
    //     showEditor.value = true;
    // }
    // if (selectedTab.value === 'text') {
    //     enableHtmlEditor.value = true;
    //     showEditor.value = true;
    // }


    // {
    //     enableHtmlEditor.value = false;
    //     showEditor.value = false;
    // }
};

const showEditor = ref(true);
    const enableHtmlEditor = ref(false);

// Add new ref for editing state
const editingTodo = ref(null);

// Props
const props = defineProps({
    todos: {
        type: Array,
        default: () => []
    },
    showFilters: {
        type: Boolean,
        default: true
    },
    showPriority: {
        type: Boolean,
        default: true
    },
    showStats: {
        type: Boolean,
        default: true
    },
    placeholder: {
        type: String,
        default: 'What needs to be done?'
    },
    addButtonText: {
        type: String,
        default: 'Add'
    }
});

// Emits
const emit = defineEmits(['update:todos', 'add', 'delete', 'toggle', 'clear-completed','save_data']);

// State
const localTodos = ref([...props.todos]);
const newTodo = ref('');
    const newTodo2 = ref('');
const currentFilter = ref('All');
const filters = ['All', 'Active', 'Completed' , 'Too_High','High', 'Medium', 'Low'];
const priorityColors = {
    Too_High: 'bg-red-800 text-red-100',
    High: 'bg-red-100 text-red-800',
    Medium: 'bg-yellow-100 text-yellow-800',
    Low: 'bg-green-100 text-green-800'
};

// Watch for external changes
watch(() => props.todos, (newTodos) => {
    localTodos.value = [...newTodos];
}, { deep: true });

// Computed
const filteredTodos = computed(() => {
    return localTodos.value.filter(todo => {
        // Status filters
        if (currentFilter.value === 'Active') return !todo.completed;
        if (currentFilter.value === 'Completed') return todo.completed;

        // Priority filters
        if (['High', 'Medium', 'Low', 'Too_High'].includes(currentFilter.value)) {
            return todo.priority === currentFilter.value;
        }

        // Show all if no filter matches
        return true;
    });
});

const activeTodos = computed(() => localTodos.value.filter(todo => !todo.completed));
const completedTodos = computed(() => localTodos.value.filter(todo => todo.completed));

// Methods
const updateTodos = (newTodos) => {
    localTodos.value = newTodos;
    emit('update:todos', newTodos);
};

const handleSetData = (data) => {
    console.log('data',data);

    newTodo2.value = data ;
    // newTodo2.value = data.content;
    addTodo();
};

const addTodo = () => {
    let htmlContent2;

    // Handle both string and object cases
    if (typeof newTodo2.value === 'object' && newTodo2.value !== null) {
        htmlContent2 = newTodo2.value.content;
    } else {
        htmlContent2 = newTodo2.value;
    }

    showEditor.value = false;
    newTodo2.value = '';
    const htmlContent = htmlContent2?.toString().trim();

    // Check if the content is not just empty HTML tags
    const hasContent = htmlContent &&
        htmlContent.replace(/<[^>]*>/g, '').trim().length > 0;

    if (hasContent) {
        if (editingTodo.value) {
            // Update existing todo
            const updatedTodos = localTodos.value.map(todo =>
                todo.id === editingTodo.value.id
                    ? { ...todo, text: htmlContent }
                    : todo
            );
            updateTodos(updatedTodos);
            editingTodo.value = null;
        } else {
            // Create new todo
            const newTodoItem = {
                id: Date.now(),
                text: htmlContent,
                completed: false,
                priority: null
            };
            const updatedTodos = [newTodoItem, ...localTodos.value];
            updateTodos(updatedTodos);
            emit('add', newTodoItem);
            emit('save_data', newTodoItem);
        }
    }
};

const deleteTodo = (id) => {
    const updatedTodos = localTodos.value.filter(todo => todo.id !== id);
    updateTodos(updatedTodos);
    emit('delete', id);
};

const toggleComplete = (todo) => {
    const updatedTodos = localTodos.value.map(t =>
        t.id === todo.id ? { ...t, completed: !t.completed } : t
    );
    updateTodos(updatedTodos);
    emit('toggle', todo.id);
};

const setPriority = (todo) => {
    const priorities = ['','Low', 'Medium', 'High', 'Too_High'];
    const currentIndex = priorities.indexOf(todo.priority);
    const newPriority = priorities[(currentIndex + 1) % priorities.length] || priorities[0];

    const updatedTodos = localTodos.value.map(t =>
        t.id === todo.id ? { ...t, priority: newPriority } : t
    );
    updateTodos(updatedTodos);
};

const clearCompleted = () => {
    const updatedTodos = localTodos.value.filter(todo => !todo.completed);
    updateTodos(updatedTodos);
    emit('clear-completed');
};

// Add edit method
const editTodo = (todo) => {
    editingTodo.value = todo;
    newTodo2.value = todo.text;
    showEditor.value = true;
    enableHtmlEditor.value = true;
    selectedTab.value = 'html';
};

// Editor methods
const editorRef = ref(null);

const handleCreated = (editor) => {
    editorRef.value = editor;
};

const handleChange = (editor) => {
    try {
        const html = editor.getHtml();
        // Normalize line breaks
        const normalizedHtml = html.replace(/<br\s*\/?>/gi, '</p><p>');
        newTodo.value = normalizedHtml;
    } catch (error) {
        console.error('Editor change error:', error);
        // Fallback to empty string if there's an error
        newTodo.value = '';
    }
};

// Cleanup on unmount
onBeforeUnmount(() => {
    if (editorRef.value) {
        editorRef.value.destroy();
    }
});
</script>

<style scoped>
/* Add these styles to ensure proper editor display */
:deep(.w-e-text-container) {
    min-height: 100px !important;
    max-height: 200px !important;
}

:deep(.w-e-toolbar) {
    border-bottom: 1px solid #e5e7eb;
}

:deep(.w-e-text-container [data-slate-editor]) {
    padding: 8px 12px;
}

/* Hide toolbar in simple mode */
:deep(.w-e-toolbar) {
    display: none;
}

/* Existing styles */
.list-enter-active,
.list-leave-active {
    transition: all 0.3s ease;
}
.list-leave-to,
.list-enter-from {
    opacity: 0;
    transform: translateX(30px);
}
</style>



























