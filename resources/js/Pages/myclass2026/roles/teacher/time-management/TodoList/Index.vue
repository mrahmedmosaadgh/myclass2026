<script setup>
import { ref, computed } from 'vue';
import RoleLayout from '../../../../_shared/layouts/RoleLayout.vue';
import AppCard from '../../../../_shared/components/AppCard.vue';
import AppButton from '../../../../_shared/components/AppButton.vue';

const tasks = ref([
  { id: 1, title: 'Grade Algebra quizzes', category: 'Grading', priority: 'high', completed: false },
  { id: 2, title: 'Prepare slides for Physics chapter 4', category: 'Prep', priority: 'medium', completed: false },
  { id: 3, title: 'Meet with Sara\'s parents', category: 'Meeting', priority: 'high', completed: true },
  { id: 4, title: 'Review new curriculum changes', category: 'Admin', priority: 'low', completed: false },
]);

const newTask = ref('');
const newCategory = ref('Prep');
const newPriority = ref('medium');

const categories = ['Grading', 'Prep', 'Meeting', 'Admin', 'Other'];
const priorities = ['low', 'medium', 'high'];

const filterCategory = ref('All');

const filteredTasks = computed(() => {
  let filtered = tasks.value;
  if (filterCategory.value !== 'All') {
    filtered = filtered.filter(t => t.category === filterCategory.value);
  }
  return filtered.sort((a, b) => {
    if (a.completed !== b.completed) return a.completed ? 1 : -1;
    const pOptions = { 'high': 3, 'medium': 2, 'low': 1 };
    return pOptions[b.priority] - pOptions[a.priority];
  });
});

const addTask = () => {
  if (!newTask.value.trim()) return;
  tasks.value.push({
    id: Date.now(),
    title: newTask.value,
    category: newCategory.value,
    priority: newPriority.value,
    completed: false
  });
  newTask.value = '';
};

const toggleComplete = (taskId) => {
  const task = tasks.value.find(t => t.id === taskId);
  if (task) task.completed = !task.completed;
};

const deleteTask = (taskId) => {
  tasks.value = tasks.value.filter(t => t.id !== taskId);
};
</script>

<template>
  <RoleLayout title="My To-Do List">
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      
      <!-- Add Task Sidebar -->
      <div class="lg:col-span-1">
        <AppCard title="Add New Task">
          <form @submit.prevent="addTask" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Task</label>
              <input v-model="newTask" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="What needs to be done?" required />
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700">Category</label>
              <select v-model="newCategory" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
              </select>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700">Priority</label>
              <div class="mt-2 flex items-center space-x-4">
                <label v-for="p in priorities" :key="p" class="inline-flex items-center">
                  <input type="radio" v-model="newPriority" :value="p" class="h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500" />
                  <span class="ml-2 text-sm text-gray-700 capitalize" 
                        :class="{'text-red-600 font-bold': p === 'high', 'text-yellow-600': p === 'medium', 'text-green-600': p === 'low'}">
                    {{ p }}
                  </span>
                </label>
              </div>
            </div>
            
            <AppButton type="submit" variant="primary" class="w-full">Add Task</AppButton>
          </form>
        </AppCard>
      </div>

      <!-- Task List Area -->
      <div class="lg:col-span-2">
        <AppCard title="My Tasks" no-padding>
          
          <template #header>
            <div class="flex items-center space-x-2">
              <span class="text-sm text-gray-500">Filter:</span>
              <select v-model="filterCategory" class="text-sm rounded-md border-gray-300 py-1 pl-2 pr-8 focus:border-indigo-500 focus:outline-none focus:ring-indigo-500">
                <option value="All">All Categories</option>
                <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
              </select>
            </div>
          </template>

          <ul class="divide-y divide-gray-200">
            <li v-for="task in filteredTasks" :key="task.id" 
                class="px-6 py-4 flex items-center justify-between hover:bg-gray-50 transition-colors"
                :class="{'bg-gray-50 opacity-60': task.completed}">
              
              <div class="flex items-center flex-1">
                <input type="checkbox" :checked="task.completed" @change="toggleComplete(task.id)" class="h-5 w-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 cursor-pointer" />
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-900" :class="{'line-through text-gray-500': task.completed}">
                    {{ task.title }}
                  </p>
                  <p class="text-xs text-gray-500 mt-1 flex items-center space-x-2">
                    <span class="bg-gray-100 px-2 py-0.5 rounded text-gray-600 border border-gray-200">{{ task.category }}</span>
                    <span class="capitalize" :class="{'text-red-500': task.priority === 'high', 'text-yellow-500': task.priority === 'medium', 'text-green-500': task.priority === 'low'}">
                      {{ task.priority }} Priority
                    </span>
                  </p>
                </div>
              </div>

              <button @click="deleteTask(task.id)" class="ml-4 text-gray-400 hover:text-red-500 focus:outline-none">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
              </button>
            </li>

            <li v-if="filteredTasks.length === 0" class="px-6 py-8 text-center text-gray-500">
              No tasks found. Everything is done! 🎉
            </li>
          </ul>

        </AppCard>
      </div>

    </div>
  </RoleLayout>
</template>
