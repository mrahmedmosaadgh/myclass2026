<template>




    <div class="p-6">
        <TodoList7
            v-model:todos="todos"
            :show-filters="true"
            :show-priority="true"
            :show-stats="true"
            placeholder="Enter a new task..."
            add-button-text="Add Task"
            @add="handleAdd"
            @delete="handleDelete"
            @toggle="handleToggle"
            @clear-completed="handleClearCompleted"
        />
    </div>
</template>

<script setup>
import { ref } from 'vue';
// Fix the import path to match the component name exactly
import TodoList7 from './TodoList7.vue';
 
// D:\my_projects\2025\laravel12\myclass4\resources\js\Pages\Documentation\Components\tabs.vue
// Initial todos data
const todos = ref([
    {
        id: 1,
        text: 'Learn Vue 3',
        completed: false,
        priority: 'High'
    },
    {
        id: 2,
        text: 'Build a project',
        completed: true,
        priority: 'Medium'
    }
]);

// Event handlers
const handleAdd = (newTodo) => {
    console.log('New todo added:', newTodo);
    // You can make API calls here
};

const handleDelete = (id) => {
    console.log('Todo deleted:', id);
    // You can make API calls here
};

const handleToggle = (id) => {
    console.log('Todo toggled:', id);
    // You can make API calls here
};

const handleClearCompleted = () => {
    console.log('Completed todos cleared');
    // You can make API calls here
};
</script>

