<template>
  <div class="task-tree">
    <q-tree
      :nodes="formattedTasks"
      node-key="id"
      tick-strategy="leaf"
      :ticked="tickedTasks"
      @update:ticked="updateTicked"
      @update:nodes="handleNodeUpdate"
      no-connectors
      :default-expand-all="expandAll"
      draggable
    >
      <template v-slot:default-header="prop">
        <div class="row items-center full-width task-header" :class="{ 'is-dragging': isDragging }">
          <!-- Drag handle -->
          <div class="drag-handle-container q-mr-sm">
            <q-icon name="drag_indicator" size="sm" class="drag-handle cursor-grab" />
          </div>

          <!-- Task content -->
          <div class="col-grow task-title" :class="{ 'completed-task': isCompleted(prop.node) }">
            <!-- Editable title -->
            <div v-if="editingTaskId === prop.node.id && editingField === 'title'" class="editable-field">
              <q-input
                v-model="editingValue"
                dense
                autofocus
                @keyup="setupAutoSave(prop.node.original)"
                @keyup.enter="saveEdit(prop.node.original)"
                @blur="saveEdit(prop.node.original)"
                @keyup.esc="cancelEdit"
              />
            </div>
            <div v-else @dblclick.stop="startEdit(prop.node.original, 'title')">
              {{ prop.node.label }}
            </div>

            <q-chip v-if="prop.node.classification" size="sm" :color="getClassificationColor(prop.node.classification)" text-color="white">
              {{ prop.node.classification }}
            </q-chip>

            <q-chip v-if="prop.node.due_date" size="sm" color="blue-grey-2" text-color="black" icon="event">
              {{ formatDate(prop.node.due_date) }}
            </q-chip>
          </div>

          <!-- Description and Action buttons -->
          <div class="col-auto">
            <!-- Description button/editor -->
            <div class="description-area">
              <div v-if="editingTaskId === prop.node.id && editingField === 'description'" class="editable-field description-editor">
                <q-input
                  v-model="editingValue"
                  type="textarea"
                  autofocus
                  dense
                  @keyup="setupAutoSave(prop.node.original)"
                  @blur="saveEdit(prop.node.original)"
                  @keyup.esc="cancelEdit"
                />
              </div>
              <q-btn
                v-else
                flat
                round
                dense
                :icon="prop.node.original.description ? 'description' : 'add_comment'"
                size="sm"
                :color="prop.node.original.description ? 'blue-grey' : 'grey'"
                @click.stop="startEdit(prop.node.original, 'description')"
                :class="{ 'has-description': prop.node.original.description }"
              >
                <q-tooltip>
                  {{ prop.node.original.description ? 'Edit description' : 'Add description' }}
                </q-tooltip>
              </q-btn>
            </div>

            <!-- Action buttons -->
            <div class="action-buttons">
              <q-btn flat round dense icon="add" size="sm" color="positive" @click.stop="addSubtask(prop.node.original)" />
              <q-btn flat round dense icon="edit" size="sm" @click.stop="$emit('edit-task', prop.node.original)" />
              <q-btn flat round dense icon="delete" size="sm" color="negative" @click.stop="$emit('delete-task', prop.node.original)" />
            </div>
          </div>
        </div>
      </template>
    </q-tree>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { mapIcon } from './icons.js';

const props = defineProps({
  tasks: {
    type: Array,
    required: true
  },
  expandAll: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['toggle-complete', 'edit-task', 'delete-task', 'reorder-tasks', 'add-subtask']);

// State
const tickedTasks = ref([]);
const isDragging = ref(false);
const editingTaskId = ref(null);
const editingField = ref(null);
const editingValue = ref('');
const saveTimeout = ref(null);

// Computed
const formattedTasks = computed(() => {
  console.log('Formatting tasks for tree:', props.tasks);
  return formatTasksForTree(props.tasks);
});

// Methods
const formatTasksForTree = (tasks) => {
  // Check if tasks is an array and has items
  if (!tasks || !Array.isArray(tasks) || !tasks.length) {
    console.warn('formatTasksForTree: Invalid tasks input:', tasks);
    return [];
  }

  // Map each task to the tree format
  return tasks.map(task => formatTask(task));
};

const formatTask = (task) => {
  // Check if task is valid
  if (!task || typeof task !== 'object') {
    console.warn('formatTask: Invalid task input:', task);
    return null;
  }

  // Create the formatted task object
  const formattedTask = {
    id: task.id || 0,
    label: task.title || 'Untitled Task',
    original: task,
    due_date: task.due_date || null,
    classification: task.classification || null,
    completed_at: task.completed_at || null,
    children: []
  };

  // Add children if they exist
  if (task.children && Array.isArray(task.children) && task.children.length > 0) {
    formattedTask.children = task.children
      .filter(child => child) // Filter out null/undefined children
      .map(child => formatTask(child))
      .filter(child => child); // Filter out any null results from formatTask
  }

  return formattedTask;
};

const isCompleted = (node) => {
  return node.completed_at !== null;
};

const updateTicked = (ticked) => {
  try {
    // Ensure ticked is an array
    if (!Array.isArray(ticked)) {
      console.warn('updateTicked: ticked is not an array:', ticked);
      return;
    }

    // Find which tasks were added or removed from the ticked list
    const previouslyTicked = new Set(tickedTasks.value || []);
    const newlyTicked = new Set(ticked);

    // Find tasks that were just ticked
    for (const taskId of newlyTicked) {
      if (!previouslyTicked.has(taskId)) {
        // This task was just checked
        const task = findTaskById(taskId, props.tasks);
        if (task) {
          emit('toggle-complete', task);
        }
      }
    }

    // Find tasks that were just unticked
    for (const taskId of previouslyTicked) {
      if (!newlyTicked.has(taskId)) {
        // This task was just unchecked
        const task = findTaskById(taskId, props.tasks);
        if (task) {
          emit('toggle-complete', task);
        }
      }
    }

    tickedTasks.value = ticked;
  } catch (error) {
    console.error('Error in updateTicked:', error);
  }
};

const findTaskById = (id, tasks) => {
  try {
    // Check if tasks is an array
    if (!tasks || !Array.isArray(tasks)) {
      return null;
    }

    for (const task of tasks) {
      if (!task) continue;

      if (task.id === id) {
        return task;
      }

      if (task.children && Array.isArray(task.children) && task.children.length) {
        const found = findTaskById(id, task.children);
        if (found) return found;
      }
    }
  } catch (error) {
    console.error('Error in findTaskById:', error);
  }

  return null;
};

const formatDate = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleDateString();
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

const addSubtask = (parentTask) => {
  // Create a new empty task with the parent_id set to the selected task
  emit('add-subtask', parentTask);
};

// Editing methods
const startEdit = (task, field) => {
  // Cancel any previous editing
  cancelEdit();

  // Set the editing state
  editingTaskId.value = task.id;
  editingField.value = field;
  editingValue.value = field === 'title' ? task.title : (task.description || '');
};

const cancelEdit = () => {
  // Clear any pending auto-save
  if (saveTimeout.value) {
    clearTimeout(saveTimeout.value);
    saveTimeout.value = null;
  }

  // Reset the editing state
  editingTaskId.value = null;
  editingField.value = null;
  editingValue.value = '';
};

const saveEdit = (task) => {
  // Clear any pending auto-save
  if (saveTimeout.value) {
    clearTimeout(saveTimeout.value);
    saveTimeout.value = null;
  }

  // Only save if there's a change
  if (editingField.value === 'title' && editingValue.value !== task.title) {
    const updatedTask = { ...task, title: editingValue.value };
    emit('edit-task', updatedTask);
  } else if (editingField.value === 'description' && editingValue.value !== task.description) {
    const updatedTask = { ...task, description: editingValue.value };
    emit('edit-task', updatedTask);
  }

  // Reset the editing state
  editingTaskId.value = null;
  editingField.value = null;
  editingValue.value = '';
};

// Auto-save after 5 seconds of editing
const setupAutoSave = (task) => {
  // Clear any existing timeout
  if (saveTimeout.value) {
    clearTimeout(saveTimeout.value);
  }

  // Set a new timeout
  saveTimeout.value = setTimeout(() => {
    if (editingTaskId.value === task.id) {
      saveEdit(task);
    }
  }, 5000); // 5 seconds
};

// Handle node updates from drag and drop operations
const handleNodeUpdate = (nodes) => {
  // Set isDragging to false when the update is complete
  isDragging.value = false;

  // Convert the tree nodes back to task format
  const updatedTasks = convertNodesToTasks(nodes);

  // Emit the reorder event with the updated tasks
  emit('reorder-tasks', updatedTasks);
};

// Add event listeners for drag start and end
document.addEventListener('dragstart', () => {
  isDragging.value = true;
});

document.addEventListener('dragend', () => {
  isDragging.value = false;
});

// Helper function to convert tree nodes back to task format
const convertNodesToTasks = (nodes) => {
  return nodes.map((node, index) => {
    // Create a task object from the node
    const task = {
      ...node.original,
      position: index
    };

    // Process children if they exist
    if (node.children && node.children.length > 0) {
      task.children = convertNodesToTasks(node.children);
    }

    return task;
  });
};

// Initialize ticked tasks based on completed status
watch(() => props.tasks, (newTasks) => {
  const completedTaskIds = [];

  const findCompletedTasks = (tasks) => {
    // Check if tasks is an array or iterable
    if (!tasks || !Array.isArray(tasks)) {
      console.warn('Tasks is not an array:', tasks);
      return;
    }

    for (const task of tasks) {
      if (task && task.completed_at) {
        completedTaskIds.push(task.id);
      }
      if (task && task.children && Array.isArray(task.children) && task.children.length) {
        findCompletedTasks(task.children);
      }
    }
  };

  // Make sure newTasks is an array before processing
  if (newTasks && Array.isArray(newTasks)) {
    console.log('Processing tasks for completion status:', newTasks);
    findCompletedTasks(newTasks);
    tickedTasks.value = completedTaskIds;
  } else {
    console.warn('New tasks is not an array:', newTasks);
    tickedTasks.value = [];
  }
}, { immediate: true });
</script>

<style scoped>
.task-tree {
  width: 100%;
}

.completed-task {
  text-decoration: line-through;
  color: #9e9e9e;
}

.task-title {
  display: flex;
  align-items: center;
  gap: 8px;
}

.task-header {
  transition: all 0.2s ease;
  border-radius: 4px;
  padding: 4px;
}

.task-header:hover {
  background-color: rgba(0, 0, 0, 0.03);
}

.task-header.is-dragging {
  background-color: rgba(0, 0, 0, 0.05);
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.drag-handle-container {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 24px;
  height: 24px;
  border-radius: 4px;
  color: #9e9e9e;
}

.drag-handle-container:hover {
  background-color: rgba(0, 0, 0, 0.05);
  color: #616161;
}

.drag-handle {
  cursor: grab;
}

.drag-handle:active {
  cursor: grabbing;
}

.action-buttons {
  opacity: 0.7;
  transition: opacity 0.2s ease;
}

.task-header:hover .action-buttons {
  opacity: 1;
}

/* Styling for drag and drop */
:deep(.q-tree__node--parent) {
  padding: 4px 0;
}

:deep(.q-tree__node--child) {
  padding: 2px 0;
}

:deep(.q-tree__node--dragging) {
  opacity: 0.7;
  z-index: 100;
}

:deep(.q-tree__node--drag-target) {
  background-color: rgba(25, 118, 210, 0.1);
  border-radius: 4px;
  position: relative;
}

:deep(.q-tree__node--drag-target::before) {
  content: '';
  position: absolute;
  left: 0;
  right: 0;
  height: 2px;
  background-color: #1976d2;
  top: 0;
}

:deep(.q-tree__node-header) {
  border-radius: 4px;
  transition: background-color 0.2s ease;
}

:deep(.q-tree__node-header:hover) {
  background-color: rgba(0, 0, 0, 0.03);
}

/* Editable fields */
.editable-field {
  width: 100%;
}

.description-editor {
  min-width: 200px;
  position: absolute;
  right: 0;
  z-index: 10;
  background-color: white;
  border-radius: 4px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  padding: 8px;
}

.description-area {
  position: relative;
  display: inline-block;
  margin-right: 8px;
}

.has-description {
  color: #1976d2;
}
</style>
