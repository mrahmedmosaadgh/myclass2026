<template>
  <div class="ticktick-container">
    <Head title="TickTick Tasks" />
    

    <q-layout view="hHh LpR fFf" class="bg-grey-1">
      <!-- Header -->
      <q-header elevated class="bg-primary text-white">
        <q-toolbar>
          <q-btn flat round dense icon="menu" @click="leftDrawerOpen = !leftDrawerOpen" />
          <q-toolbar-title>
            <div class="text-h6">TickTick Tasks</div>
          </q-toolbar-title>
          <q-btn flat round dense icon="search" @click="searchDialogOpen = true" />
          <q-btn flat round dense icon="timer" @click="pomodoroDialogOpen = true" />
          <q-btn flat round dense icon="settings" />
        </q-toolbar>
        <q-tabs v-model="activeTab" align="left" class="bg-primary text-white">
          <q-tab name="all" icon="list" label="All Tasks" />
          <q-tab name="today" icon="today" label="Today" />
          <q-tab name="upcoming" icon="event" label="Upcoming" />
          <q-tab name="completed" icon="check_circle" label="Completed" />
        </q-tabs>
      </q-header>

      <!-- Left drawer -->
      <q-drawer v-model="leftDrawerOpen" bordered :width="280" :breakpoint="500" class="bg-white">
        <q-scroll-area class="fit">
          <q-list padding>
            <q-item-label header>Classifications</q-item-label>
            <q-item v-for="classification in classifications" :key="classification" clickable v-ripple
              @click="filterByClassification(classification)">
              <q-item-section avatar>
                <q-icon name="label" :color="getClassificationColor(classification)" />
              </q-item-section>
              <q-item-section>{{ classification }}</q-item-section>
            </q-item>

            <q-separator spaced />

            <q-item-label header>Recent Pomodoro Sessions</q-item-label>
            <q-item v-for="(session, index) in recentSessions" :key="index" clickable v-ripple>
              <q-item-section avatar>
                <q-icon :name="session.type === 'work' ? 'work' : 'break_time'"
                  :color="session.type === 'work' ? 'red' : 'green'" />
              </q-item-section>
              <q-item-section>
                <q-item-label>{{ session.task ? session.task.title : 'No task' }}</q-item-label>
                <q-item-label caption>{{ formatDate(session.started_at) }} - {{ session.duration }}min</q-item-label>
              </q-item-section>
            </q-item>
          </q-list>
        </q-scroll-area>
      </q-drawer>

      <!-- Main content -->
      <q-page-container>
        <q-page padding>
          <div class="q-pa-md">
            <div class="row q-mb-md">
              <div class="col-12">
                <q-btn color="primary" icon="add" label="Add Task" @click="openTaskDialog()" />
                <q-btn color="primary" icon="add_box" label="Add Multiple" @click="multiTaskDialogOpen = true" class="q-ml-sm" />
                <q-btn color="secondary" icon="search" label="Search" @click="searchDialogOpen = true" class="q-ml-sm" />
                <q-btn
                  :color="selectionMode ? 'negative' : 'grey'"
                  :icon="selectionMode ? 'close' : 'select_all'"
                  :label="selectionMode ? 'Cancel Selection' : 'Select Tasks'"
                  @click="toggleSelectionMode"
                  class="q-ml-sm"
                />
                <q-btn
                  v-if="selectionMode && selectedTasks.length > 0"
                  color="negative"
                  icon="delete"
                  :label="`Delete (${selectedTasks.length})`"
                  @click="confirmBulkDelete"
                  class="q-ml-sm"
                />
              </div>
            </div>

            <!-- View Mode Tabs -->
            <!-- <div class="q-mb-md">
              <q-tabs
                v-model="viewMode"
                class="text-primary"
                active-color="primary"
                indicator-color="primary"
                align="left"
                narrow-indicator
              >
                <q-tab name="tree" icon="account_tree" label="Classification Tree" />
                <q-tab name="list" icon="format_list_bulleted" label="Main Task List" />
              </q-tabs>
            </div> -->

            <!-- Task Views -->
            <q-card flat bordered>
              <!-- Search and Controls for Tree View v-if="viewMode === 'tree'" -->
              <q-card-section  class="q-pb-none">
                <div class="row items-center q-col-gutter-md">
                  <div class="col-12 col-md-6">
                    <q-input
                      v-model="treeSearchQuery"
                      dense
                      outlined
                      placeholder="Search tasks by title or description"
                      clearable
                      @update:model-value="filterTreeTasks"
                    >
                      <template v-slot:append>
                        <q-icon name="search" />
                      </template>
                    </q-input>
                  </div>
                  <div class="col-12 col-md-6 flex justify-end">
                    <q-btn
                      :label="expandAllTasks ? 'Collapse All' : 'Expand All'"
                      :icon="expandAllTasks ? 'unfold_less' : 'unfold_more'"
                      color="primary"
                      flat
                      @click="toggleExpandAll"
                    />
                  </div>
                </div>
              </q-card-section>

              <q-card-section>
                <!-- Tree View -->
                <!-- <task-tree
                  v-if="viewMode === 'tree'"
                  :tasks="treeFilteredTasks"
                  :expand-all="expandAllTasks"
                  @toggle-complete="toggleTaskComplete"
                  @edit-task="openTaskDialog"
                  @delete-task="confirmDeleteTask"
                  @reorder-tasks="reorderTasks"
                  @add-subtask="addSubtask"
                /> -->

                <!-- List View filteredTasks-->
                <main-task-list
                  :tasks="treeFilteredTasks"
                  :selection-mode="selectionMode"
                  :selected-tasks="selectedTasks"
                  @toggle-complete="toggleTaskComplete"
                  @edit-task="openTaskDialog"
                  @delete-task="confirmDeleteTask"
                  @reorder-tasks="reorderTasks"
                  @add-subtask="addSubtask"
                  @toggle-selection="toggleTaskSelection"
                />
              </q-card-section>
            </q-card>
          </div>
        </q-page>
      </q-page-container>

      <!-- Task Dialog -->
      <q-dialog v-model="taskDialogOpen" persistent>
        <task-form
          :task="currentTask"
          :parent-tasks="parentTaskOptions"
          :classifications="classifications"
          @save="saveTask"
          @cancel="taskDialogOpen = false"
        />
      </q-dialog>

      <!-- Multi-Task Dialog -->
      <q-dialog v-model="multiTaskDialogOpen" persistent>
        <multi-task-form
          v-model="multiTaskDialogOpen"
          :parent-task="currentTask"
          :classification-options="classifications"
          @update:is-open="multiTaskDialogOpen = $event"
          @add-tasks="saveMultipleTasks"
        />
      </q-dialog>

      <!-- Search Dialog -->
      <q-dialog v-model="searchDialogOpen">
        <q-card style="width: 500px; max-width: 80vw;">
          <q-card-section>
            <div class="text-h6">Search Tasks</div>
          </q-card-section>
          <q-card-section>
            <q-input v-model="searchQuery" label="Search" autofocus @keyup.enter="searchTasks">
              <template v-slot:append>
                <q-icon name="search" />
              </template>
            </q-input>
          </q-card-section>
          <q-card-actions align="right">
            <q-btn flat label="Cancel" color="primary" v-close-popup />
            <q-btn flat label="Search" color="primary" @click="searchTasks" v-close-popup />
          </q-card-actions>
        </q-card>
      </q-dialog>

      <!-- Pomodoro Dialog -->
      <q-dialog v-model="pomodoroDialogOpen" persistent>
        <pomodoro-timer
          :tasks="allTasks"
          @close="pomodoroDialogOpen = false"
          @session-completed="refreshRecentSessions"
          @enter-focus-mode="enterFocusMode"
        />
      </q-dialog>

      <!-- Focus Mode -->
      <focus-mode
        v-if="focusModeActive"
        :task="focusTask"
        :pomodoro-session="currentPomodoroSession"
        @exit="exitFocusMode"
      />

      <!-- Delete Confirmation -->
      <q-dialog v-model="deleteConfirmOpen">
        <q-card>
          <q-card-section class="row items-center">
            <q-avatar icon="delete" color="negative" text-color="white" />
            <span class="q-ml-sm">Are you sure you want to delete this task?</span>
          </q-card-section>
          <q-card-actions align="right">
            <q-btn flat label="Cancel" color="primary" v-close-popup />
            <q-btn flat label="Delete" color="negative" @click="deleteTask" v-close-popup />
          </q-card-actions>
        </q-card>
      </q-dialog>

      <!-- Bulk Delete Confirmation -->
      <q-dialog v-model="bulkDeleteConfirmOpen">
        <q-card>
          <q-card-section class="row items-center">
            <q-avatar icon="delete" color="negative" text-color="white" />
            <span class="q-ml-sm">Delete {{ selectedTasks.length }} tasks?</span>
          </q-card-section>
          <q-card-section>
            Are you sure you want to delete the selected tasks? This action cannot be undone.
          </q-card-section>
          <q-card-actions align="right">
            <q-btn flat label="Cancel" color="primary" v-close-popup />
            <q-btn flat label="Delete" color="negative" @click="bulkDeleteTasks" v-close-popup />
          </q-card-actions>
        </q-card>
      </q-dialog>
    </q-layout>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useAppStore } from '@/Stores/AppStore';
import TaskTree from './components/TaskTree.vue';
import MainTaskList from './components/MainTaskList.vue';
import TaskForm from './components/TaskForm.vue';
import MultiTaskForm from './components/MultiTaskForm.vue';
import PomodoroTimer from './components/PomodoroTimer.vue';
import FocusMode from './components/FocusMode.vue';

import './components/icons.js';
 
// Props from controller
const props = defineProps({
  tasks: Array,
  classifications: Array,
});

// Store
const appStore = useAppStore();

// State
const leftDrawerOpen = ref(false);
const activeTab = ref('all');
const taskDialogOpen = ref(false);
const multiTaskDialogOpen = ref(false);
const searchDialogOpen = ref(false);
const pomodoroDialogOpen = ref(false);
const deleteConfirmOpen = ref(false);
const bulkDeleteConfirmOpen = ref(false);
const focusModeActive = ref(false);
const searchQuery = ref('');
const treeSearchQuery = ref('');
const currentTask = ref(null);
const taskToDelete = ref(null);
const focusTask = ref(null);
const currentPomodoroSession = ref(null);
const allTasks = ref(Array.isArray(props.tasks) ? props.tasks : []);
console.log('Initial tasks:', props.tasks);
const recentSessions = ref([]);
const selectedClassification = ref(null);
const viewMode = ref('tree'); // 'tree' or 'list'
const expandAllTasks = ref(false);
const selectedTasks = ref([]);
const selectionMode = ref(false);

// Computed
const parentTaskOptions = computed(() => {
  // Check if allTasks.value is defined and is an array
  if (!allTasks.value || !Array.isArray(allTasks.value)) {
    console.warn('parentTaskOptions: allTasks.value is not an array:', allTasks.value);
    return [];
  }

  return allTasks.value.filter(task => !task.parent_id);
});

const filteredTasks = computed(() => {
  // Check if allTasks.value is defined and is an array
  if (!allTasks.value || !Array.isArray(allTasks.value)) {
    console.warn('filteredTasks: allTasks.value is not an array:', allTasks.value);
    return [];
  }

  let filtered = [...allTasks.value];

  // Filter by tab
  if (activeTab.value === 'today') {
    const today = new Date().toISOString().split('T')[0];
    filtered = filtered.filter(task => {
      return task.due_date && task.due_date.startsWith(today);
    });
  } else if (activeTab.value === 'upcoming') {
    const today = new Date().toISOString().split('T')[0];
    filtered = filtered.filter(task => {
      return task.due_date && task.due_date > today;
    });
  } else if (activeTab.value === 'completed') {
    filtered = filtered.filter(task => task.completed_at);
  }

  // Filter by classification
  if (selectedClassification.value) {
    filtered = filtered.filter(task => task.classification === selectedClassification.value);
  }

  // Filter by search query
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(task =>
      task.title.toLowerCase().includes(query) ||
      (task.description && task.description.toLowerCase().includes(query))
    );
  }

  return filtered;
});

// Tree-specific filtered tasks (includes tree search)
const treeFilteredTasks = computed(() => {
  // Start with the regular filtered tasks
  let filtered = [...filteredTasks.value];

  // Apply tree-specific search if there is a query
  if (treeSearchQuery.value) {
    const query = treeSearchQuery.value.toLowerCase();

    // Helper function to search recursively through tasks and their children
    const searchTaskAndChildren = (task) => {
      // Check if the task matches the search
      const taskMatches =
        task.title.toLowerCase().includes(query) ||
        (task.description && task.description.toLowerCase().includes(query));

      // If the task has children, check if any of them match
      let childrenMatch = false;
      if (task.children && Array.isArray(task.children) && task.children.length > 0) {
        childrenMatch = task.children.some(child => searchTaskAndChildren(child));
      }

      // Return true if either the task or any of its children match
      return taskMatches || childrenMatch;
    };

    // Filter the tasks based on the search
    filtered = filtered.filter(task => searchTaskAndChildren(task));
  }

  return filtered;
});

// Methods
const openTaskDialog = (task = null) => {
  currentTask.value = task ? { ...task } : { title: '', description: '', parent_id: null, classification: null, due_date: null };
  taskDialogOpen.value = true;
};

const addSubtask = (parentTask) => {
  // Create a new task with the parent_id set to the selected task
  currentTask.value = {
    title: '',
    description: '',
    parent_id: parentTask.id,
    classification: parentTask.classification||'new',
    due_date: null
  };
  taskDialogOpen.value = true;
};

const saveTask = (task) => {
  const endpoint = task.id
    ? `/developer/tasks/${task.id}`
    : '/developer/tasks';

  const method = task.id ? 'put' : 'post';

  appStore.fetchData({
    endpoint,
    method,
    data: task,
    onSuccess: (response) => {
      taskDialogOpen.value = false;

      // If we get an updated task list from the server, use it
      if (response.tasks) {
        allTasks.value = response.tasks;
        console.log('Updated tasks from server:', response.tasks);
      } else {
        // Otherwise, fall back to a full refresh
        refreshTasks();
      }
    }
  });
};

const toggleTaskComplete = (task) => {
  appStore.fetchData({
    endpoint: `/developer/tasks/${task.id}/toggle-complete`,
    method: 'post',
    onSuccess: (response) => {
      refreshTasks();
    }
  });
};

const confirmDeleteTask = (task) => {
  taskToDelete.value = task;
  deleteConfirmOpen.value = true;
};

const deleteTask = () => {
  if (!taskToDelete.value) return;

  appStore.fetchData({
    endpoint: `/developer/tasks/${taskToDelete.value.id}`,
    method: 'delete',
    onSuccess: (response) => {
      refreshTasks();
    }
  });
};

const reorderTasks = (tasks) => {
  // First update the local state to provide immediate feedback
  updateLocalTasksOrder(tasks);

  // Then send the update to the server
  appStore.fetchData({
    endpoint: '/developer/tasks/reorder',
    method: 'post',
    data: { tasks },
    onSuccess: (response) => {
      // If we get an updated task list from the server, use it
      if (response && response.tasks) {
        console.log('Received updated tasks from server:', response.tasks);
        allTasks.value = response.tasks;
      } else {
        console.warn('No tasks received in response from server, refreshing tasks');
        // If we don't get tasks in the response, refresh them
        refreshTasks();
      }
    },
    onError: (error) => {
      console.error('Error reordering tasks:', error);
      // On error, refresh tasks to ensure we have the latest state
      refreshTasks();
    },
    errorMessage: 'Failed to save task order'
  });
};

// Update the local tasks array without a full page reload
const updateLocalTasksOrder = (updatedTasks) => {
  if (!updatedTasks || !Array.isArray(updatedTasks) || updatedTasks.length === 0) {
    return;
  }

  // Create a map of existing tasks for quick lookup
  const taskMap = new Map();
  const buildTaskMap = (tasks) => {
    tasks.forEach(task => {
      taskMap.set(task.id, task);
      if (task.children && Array.isArray(task.children) && task.children.length > 0) {
        buildTaskMap(task.children);
      }
    });
  };
  buildTaskMap(allTasks.value);

  // Update the tasks with their new positions and parent-child relationships
  updatedTasks.forEach(updatedTask => {
    const existingTask = taskMap.get(updatedTask.id);
    if (existingTask) {
      // Update position and parent_id
      existingTask.position = updatedTask.position;
      existingTask.parent_id = updatedTask.parent_id;

      // Update children if they exist
      if (updatedTask.children && Array.isArray(updatedTask.children)) {
        existingTask.children = updatedTask.children;
      }
    }
  });

  // Rebuild the task hierarchy
  const rootTasks = Array.from(taskMap.values()).filter(task => !task.parent_id);
  rootTasks.sort((a, b) => a.position - b.position);

  // Update the allTasks ref
  allTasks.value = rootTasks;
};

const refreshTasks = () => {
  // Fetch the latest tasks from the server
  appStore.fetchData({
    endpoint: '/developer/tasks',
    method: 'get',
    onSuccess: (response) => {
      if (response && response.tasks) {
        console.log('Refreshed tasks from server:', response.tasks);
        allTasks.value = response.tasks;
      } else {
        console.warn('No tasks received in response from server during refresh');
      }
    },
    onError: (error) => {
      console.error('Error refreshing tasks:', error);
    },
    errorMessage: 'Failed to refresh tasks'
  });
};

const searchTasks = () => {
  // The filtering is handled by the computed property
  searchDialogOpen.value = false;
};

const filterByClassification = (classification) => {
  selectedClassification.value = classification;
  leftDrawerOpen.value = false;
};

const getClassificationColor = (classification) => {
  // Simple hash function to generate consistent colors
  let hash = 0;
  for (let i = 0; i < classification.length; i++) {
    hash = classification.charCodeAt(i) + ((hash << 5) - hash);
  }
  const colors = ['red', 'pink', 'purple', 'deep-purple', 'indigo', 'blue', 'light-blue', 'cyan', 'teal', 'green', 'light-green', 'lime', 'yellow', 'amber', 'orange', 'deep-orange', 'brown', 'grey', 'blue-grey'];
  return colors[Math.abs(hash) % colors.length];
};

const formatDate = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleDateString();
};

// Multiple tasks methods
const saveMultipleTasks = (tasks) => {
  if (!tasks || !Array.isArray(tasks) || tasks.length === 0) {
    return;
  }

  // Send the tasks to the server
  appStore.fetchData({
    endpoint: '/developer/tasks/batch',
    method: 'post',
    data: { tasks },
    onSuccess: (response) => {
      // If we get an updated task list from the server, use it
      if (response && response.tasks) {
        allTasks.value = response.tasks;
      } else {
        // Otherwise, fall back to a full refresh
        refreshTasks();
      }
    },
    errorMessage: 'Failed to add tasks'
  });
};

// Selection mode methods
const toggleSelectionMode = () => {
  selectionMode.value = !selectionMode.value;

  // Clear selected tasks when exiting selection mode
  if (!selectionMode.value) {
    selectedTasks.value = [];
  }
};

const toggleTaskSelection = (task) => {
  if (!selectionMode.value) return;

  const index = selectedTasks.value.findIndex(t => t.id === task.id);
  if (index === -1) {
    // Add to selection
    selectedTasks.value.push(task);
  } else {
    // Remove from selection
    selectedTasks.value.splice(index, 1);
  }
};

const confirmBulkDelete = () => {
  if (selectedTasks.value.length === 0) return;
  bulkDeleteConfirmOpen.value = true;
};

const bulkDeleteTasks = () => {
  if (selectedTasks.value.length === 0) return;

  const taskIds = selectedTasks.value.map(task => task.id);

  appStore.fetchData({
    endpoint: '/developer/tasks/bulk-delete',
    method: 'post',
    data: { task_ids: taskIds },
    onSuccess: () => {
      // Clear selection and exit selection mode
      selectedTasks.value = [];
      selectionMode.value = false;

      // Refresh tasks
      refreshTasks();
    },
    errorMessage: 'Failed to delete tasks'
  });
};

const refreshRecentSessions = () => {
  appStore.fetchData({
    endpoint: '/developer/pomodoro/recent',
    method: 'get',
    onSuccess: (response) => {
      recentSessions.value = response.sessions;
    }
  });
};

const enterFocusMode = (task, session) => {
  console.log('Index.vue: Entering Focus Mode');
  console.log('Task:', task);
  console.log('Session:', session);

  focusTask.value = task;
  currentPomodoroSession.value = session;
  focusModeActive.value = true;

  // Close the pomodoro dialog if it's open
  pomodoroDialogOpen.value = false;
};

const exitFocusMode = () => {
  focusModeActive.value = false;
  currentPomodoroSession.value = null;
};

// Tree-specific methods
const filterTreeTasks = () => {
  // The filtering is handled by the treeFilteredTasks computed property
  // This method is just a placeholder for the @update:model-value event
};

const toggleExpandAll = () => {
  expandAllTasks.value = !expandAllTasks.value;
};

// Lifecycle hooks
onMounted(() => {
  refreshRecentSessions();
});
</script>

<style scoped>
.ticktick-container {
  height: 100vh;
}
</style>
