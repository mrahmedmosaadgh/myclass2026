<template>
  <div class="main-task-list">
    <!-- Show All Button -->
    <div class="q-mb-md">
      <q-btn
        color="primary"
        :label="showAllTasks ? 'Show Parent Tasks Only' : 'Show All Tasks'"
        @click="toggleShowAll"
        icon-right="expand_more"
        flat
      />
    </div>

    <!-- Task List -->
    <q-list bordered separator>
      <template v-if="showAllTasks">
        <!-- Show all tasks in tree format -->
        <task-tree
          :tasks="tasks"
          @toggle-complete="$emit('toggle-complete', $event)"
          @edit-task="$emit('edit-task', $event)"
          @delete-task="$emit('delete-task', $event)"
          @reorder-tasks="$emit('reorder-tasks', $event)"
          @add-subtask="$emit('add-subtask', $event)"
        />
      </template>
      <template v-else>
        <!-- Show only parent tasks with expandable children -->
        <draggable-task-item
          v-for="(task, index) in parentTasks"
          :key="task.id"
          :task="task"
          :index="index"
          @reorder="handleReorder"
        >
          <q-item
            clickable
            v-ripple
            :class="{ 'completed-task': task.completed_at, 'task-item': true }"
          >
            <!-- Drag handle -->
            <div class="drag-handle-container q-mr-sm">
              <q-icon name="drag_indicator" size="sm" class="drag-handle cursor-grab" />
            </div>
            <q-item-section avatar>
              <div class="row items-center">
                <q-checkbox
                  v-if="selectionMode"
                  :model-value="isTaskSelected(task)"
                  @update:model-value="$emit('toggle-selection', task)"
                  class="q-mr-sm"
                />
                <q-checkbox
                  v-else
                  :model-value="task.completed_at !== null"
                  @update:model-value="$emit('toggle-complete', task)"
                />
              </div>
            </q-item-section>

            <q-item-section @click="toggleTaskExpand(task.id)">
                <div class="p-0 flex relative">
                  <!-- Editable title -->
                  <div v-if="editingTaskId === task.id && editingField === 'title'" class="editable-field w-full" @click.stop>
                    <q-input
                      v-model="editingValue"
                      dense
                      autofocus
                      @keyup="setupAutoSave(task)"
                      @keyup.enter="saveEdit(task)"
                      @blur="saveEdit(task)"
                      @keyup.esc="cancelEdit"
                    />
                  </div>
                  <q-item-label v-else @dblclick.stop="startEdit(task, 'title')">
                    {{ task.title }}
                  </q-item-label>

                  <q-item-label caption v-if="task.due_date">
                    <q-icon name="event" size="xs" /> {{ formatDate(task.due_date) }}
                  </q-item-label>
                  <div class="p-0 scale-50 w-fit flex absolute -top-4 right-10">
                    <q-chip v-if="task.classification" size="sm" :color="getClassificationColor(task.classification)" text-color="white">
                      {{ task.classification }}
                    </q-chip>
                  </div>
                </div>
            </q-item-section>

            <q-item-section side>
              <div class="row items-center">
                <!-- Description button/editor -->
                <div class="description-area">
                  <div v-if="editingTaskId === task.id && editingField === 'description'" class="editable-field description-editor" @click.stop>
                    <q-input
                      v-model="editingValue"
                      type="textarea"
                      autofocus
                      dense
                      @keyup="setupAutoSave(task)"
                      @blur="saveEdit(task)"
                      @keyup.esc="cancelEdit"
                    />
                  </div>
                  <q-btn
                    v-else
                    flat
                    round
                    dense
                    :icon="task.description ? 'description' : 'add_comment'"
                    size="sm"
                    :color="task.description ? 'blue-grey' : 'grey'"
                    @click.stop="startEdit(task, 'description')"
                    :class="{ 'has-description': task.description }"
                  >
                    <q-tooltip>
                      {{ task.description ? 'Edit description' : 'Add description' }}
                    </q-tooltip>
                  </q-btn>
                </div>

                <!-- Action buttons -->
                <div class="action-buttons">
                  <q-badge v-if="task.children && task.children.length" color="grey" :label="task.children.length" class="q-mr-sm" />
                  <q-btn flat round dense icon="add" size="sm" color="positive" @click.stop="$emit('add-subtask', task)" />
                  <q-btn flat round dense icon="edit" size="sm" @click.stop="$emit('edit-task', task)" />
                  <q-btn flat round dense icon="delete" size="sm" color="negative" @click.stop="$emit('delete-task', task)" />
                  <q-btn
                    v-if="task.children && task.children.length"
                    flat round dense
                    :icon="expandedTasks.has(task.id) ? 'expand_less' : 'expand_more'"
                    @click.stop="toggleTaskExpand(task.id)"
                  />
                </div>
              </div>
            </q-item-section>
          </q-item>
        </draggable-task-item>

        <!-- Child tasks (when expanded) -->
        <template v-for="task in parentTasks" :key="`children-${task.id}`">
          <template v-if="expandedTasks.has(task.id) && task.children && task.children.length">
            <draggable-task-item
              v-for="(child, childIndex) in task.children"
              :key="child.id"
              :task="child"
              :index="childIndex"
              :parent-id="task.id"
              @reorder="handleChildReorder(task.id, $event)"
            >
              <q-item
                clickable
                v-ripple
                :class="{ 'completed-task': child.completed_at, 'task-item': true }"
                class="q-pl-xl"
              >
                <!-- Drag handle -->
                 <div class="ml-4 drag-handle-container q-mr-sm">
                  <q-icon name="drag_indicator" size="sm" class="drag-handle cursor-grab" />
                </div>
                <q-item-section avatar>
                  <div class="row items-center">
                    <q-checkbox
                      v-if="selectionMode"
                      :model-value="isTaskSelected(child)"
                      @update:model-value="$emit('toggle-selection', child)"
                      class="q-mr-sm"
                    />
                    <q-checkbox
                      v-else
                      :model-value="child.completed_at !== null"
                      @update:model-value="$emit('toggle-complete', child)"
                    />
                  </div>
                </q-item-section>

                <q-item-section>
                  <div class="p-0 relative">
                    <!-- Editable title -->
                    <div v-if="editingTaskId === child.id && editingField === 'title'" class="editable-field w-full" @click.stop>
                      <q-input
                        v-model="editingValue"
                        dense
                        autofocus
                        @keyup="setupAutoSave(child)"
                        @keyup.enter="saveEdit(child)"
                        @blur="saveEdit(child)"
                        @keyup.esc="cancelEdit"
                      />
                    </div>
                    <q-item-label v-else @dblclick.stop="startEdit(child, 'title')">
                      {{ child.title }}
                    </q-item-label>

                    <q-item-label caption v-if="child.due_date">
                      <q-icon name="event" size="xs" /> {{ formatDate(child.due_date) }}
                    </q-item-label>
                    <div class="p-0 scale-75 w-fit flex absolute -top-4 right-10">
                      <q-chip v-if="child.classification" size="sm" :color="getClassificationColor(child.classification)" text-color="white">
                        {{ child.classification }}
                      </q-chip>
                    </div>
                  </div>
                </q-item-section>

                <q-item-section side>
                  <div class="row items-center">
                    <!-- Description button/editor -->
                    <div class="description-area">
                      <div v-if="editingTaskId === child.id && editingField === 'description'" class="editable-field description-editor" @click.stop>
                        <q-input
                          v-model="editingValue"
                          type="textarea"
                          autofocus
                          dense
                          @keyup="setupAutoSave(child)"
                          @blur="saveEdit(child)"
                          @keyup.esc="cancelEdit"
                        />
                      </div>



                      <q-btn
                        v-else
                        flat
                        round
                        dense
                        :icon="child.description ? 'description' : 'add_comment'"
                        size="sm"
                        :color="child.description ? 'blue-grey' : 'grey'"
                        @click.stop="startEdit(child, 'description')"
                        :class="{ 'has-description': child.description }"
                      >
                        <q-tooltip>
                          {{ child.description ? 'Edit description' : 'Add description' }}
                        </q-tooltip>
                      </q-btn>
                    </div>

                    <!-- Action buttons -->
                    <div class="action-buttons">
                      <q-btn flat round dense icon="add" size="sm" color="positive" @click.stop="$emit('add-subtask', child)" />
                      <q-btn flat round dense icon="edit" size="sm" @click.stop="$emit('edit-task', child)" />
                      <q-btn flat round dense icon="delete" size="sm" color="negative" @click.stop="$emit('delete-task', child)" />
                    </div>
                  </div>
                </q-item-section>
              </q-item>
            </draggable-task-item>
          </template>
        </template>
      </template>
    </q-list>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import TaskTree from './TaskTree.vue';
import DraggableTaskItem from './DraggableTaskItem.vue';

const props = defineProps({
  tasks: {
    type: Array,
    required: true
  },
  selectionMode: {
    type: Boolean,
    default: false
  },
  selectedTasks: {
    type: Array,
    default: () => []
  }
});

const emit = defineEmits(['toggle-complete', 'edit-task', 'delete-task', 'reorder-tasks', 'add-subtask', 'toggle-selection']);

// State
const expandedTasks = ref(new Set());
const showAllTasks = ref(false);
const editingTaskId = ref(null);
const editingField = ref(null);
const editingValue = ref('');
const saveTimeout = ref(null);

// Computed
const parentTasks = computed(() => {
  return props.tasks.filter(task => !task.parent_id);
});

// Methods
const toggleTaskExpand = (taskId) => {
  if (expandedTasks.value.has(taskId)) {
    expandedTasks.value.delete(taskId);
  } else {
    expandedTasks.value.add(taskId);
  }
};

const toggleShowAll = () => {
  showAllTasks.value = !showAllTasks.value;
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

// Selection methods
const isTaskSelected = (task) => {
  return props.selectedTasks.some(t => t.id === task.id);
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

// Reordering methods
const handleReorder = ({ fromIndex, toIndex, fromParentId, toParentId }) => {
  // If this is a reorder within the same parent level (both null)
  if (fromParentId === null && toParentId === null) {
    // Create a copy of the parent tasks array
    const updatedTasks = [...parentTasks.value];

    // Remove the task from its original position
    const [movedTask] = updatedTasks.splice(fromIndex, 1);

    // Insert the task at the new position
    updatedTasks.splice(toIndex, 0, movedTask);

    // Update the position property for each task
    const tasksWithUpdatedPositions = updatedTasks.map((task, index) => ({
      ...task,
      position: index
    }));

    // Emit the reorder event with the updated tasks
    emit('reorder-tasks', tasksWithUpdatedPositions);
  }
  // If this is a move from a child to parent level
  else if (fromParentId !== null && toParentId === null) {
    // Find the source parent task
    const sourceParent = parentTasks.value.find(task => task.id === fromParentId);
    if (!sourceParent || !sourceParent.children) return;

    // Get the child task being moved
    const childTask = sourceParent.children[fromIndex];
    if (!childTask) return;

    // Create a copy of the parent tasks array
    const updatedParentTasks = [...parentTasks.value];

    // Create an updated version of the source parent with the child removed
    const updatedSourceParent = {
      ...sourceParent,
      children: sourceParent.children.filter((_, i) => i !== fromIndex)
    };

    // Replace the source parent in the array
    const sourceParentIndex = updatedParentTasks.findIndex(task => task.id === fromParentId);
    if (sourceParentIndex !== -1) {
      updatedParentTasks[sourceParentIndex] = updatedSourceParent;
    }

    // Create a new task object for the moved child, setting parent_id to null
    const newParentTask = {
      ...childTask,
      parent_id: null
    };

    // Insert the new parent task at the target position
    updatedParentTasks.splice(toIndex, 0, newParentTask);

    // Update positions for all parent tasks
    const tasksWithUpdatedPositions = updatedParentTasks.map((task, index) => ({
      ...task,
      position: index
    }));

    // Emit the reorder event with all updated tasks
    emit('reorder-tasks', tasksWithUpdatedPositions);
  }
  // If this is a move from parent to child level or between different children
  else {
    // Handle in the child reorder method
    handleChildReorder(toParentId, { fromIndex, toIndex, fromParentId });
  }
};

const handleChildReorder = (parentId, { fromIndex, toIndex, fromParentId }) => {
  // Find the target parent task
  const targetParent = parentTasks.value.find(task => task.id === parentId);

  if (!targetParent) return;

  // Initialize children array if it doesn't exist
  const targetChildren = targetParent.children || [];

  // If this is a reorder within the same parent
  if (fromParentId === parentId) {
    // Create a copy of the children array
    const updatedChildren = [...targetChildren];

    // Remove the child from its original position
    const [movedChild] = updatedChildren.splice(fromIndex, 1);

    // Insert the child at the new position
    updatedChildren.splice(toIndex, 0, movedChild);

    // Update the position property for each child
    const childrenWithUpdatedPositions = updatedChildren.map((child, index) => ({
      ...child,
      position: index
    }));

    // Create an updated parent task with the reordered children
    const updatedParentTask = {
      ...targetParent,
      children: childrenWithUpdatedPositions
    };

    // Emit the reorder event with the updated parent task
    emit('reorder-tasks', [updatedParentTask]);
  }
  // If this is a move from a different parent or from the parent level
  else {
    let taskToMove;
    let updatedTasks = [...parentTasks.value];

    // If moving from parent level to child level
    if (fromParentId === null) {
      // Get the parent task being moved
      taskToMove = parentTasks.value[fromIndex];

      // Remove the task from the parent tasks array
      updatedTasks.splice(fromIndex, 1);
    }
    // If moving from a different parent
    else {
      // Find the source parent
      const sourceParent = parentTasks.value.find(task => task.id === fromParentId);
      if (!sourceParent || !sourceParent.children) return;

      // Get the child task being moved
      taskToMove = sourceParent.children[fromIndex];

      // Create an updated version of the source parent with the child removed
      const updatedSourceParent = {
        ...sourceParent,
        children: sourceParent.children.filter((_, i) => i !== fromIndex)
      };

      // Replace the source parent in the array
      const sourceParentIndex = updatedTasks.findIndex(task => task.id === fromParentId);
      if (sourceParentIndex !== -1) {
        updatedTasks[sourceParentIndex] = updatedSourceParent;
      }
    }

    if (!taskToMove) return;

    // Create a new child task with the parent_id set to the target parent
    const newChildTask = {
      ...taskToMove,
      parent_id: targetParent.id
    };

    // Create a copy of the target parent's children
    const updatedTargetChildren = [...targetChildren];

    // Insert the new child at the target position
    updatedTargetChildren.splice(toIndex, 0, newChildTask);

    // Update positions for all children
    const childrenWithUpdatedPositions = updatedTargetChildren.map((child, index) => ({
      ...child,
      position: index
    }));

    // Create an updated target parent with the new child
    const updatedTargetParent = {
      ...targetParent,
      children: childrenWithUpdatedPositions
    };

    // Replace the target parent in the array
    const targetParentIndex = updatedTasks.findIndex(task => task.id === parentId);
    if (targetParentIndex !== -1) {
      updatedTasks[targetParentIndex] = updatedTargetParent;
    }

    // Update positions for all parent tasks
    const tasksWithUpdatedPositions = updatedTasks.map((task, index) => ({
      ...task,
      position: index
    }));

    // Emit the reorder event with all updated tasks
    emit('reorder-tasks', tasksWithUpdatedPositions);
  }
};
</script>

<style scoped>
.main-task-list {
  width: 100%;
}

.completed-task {
  text-decoration: line-through;
  color: #9e9e9e;
}

.q-item__section--side {
  padding-right: 0;
}

.task-item {
  transition: all 0.2s ease;
  border-radius: 4px;
  padding: 4px;
}

.task-item:hover {
  background-color: rgba(0, 0, 0, 0.03);
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

.task-item:hover .action-buttons {
  opacity: 1;
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
