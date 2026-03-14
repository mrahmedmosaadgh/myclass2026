<template>
  <div
    class="relative group mb-2"
    :class="{ 'pl-4': !isRoot }"
    draggable="true"
    @dragstart="handleDragStart"
    @dragover.prevent="handleDragOver"
    @dragend="handleDragEnd"
    @drop.prevent="handleDrop"
  >
    <div class="flex items-center gap-2">
      <!-- Expand/collapse toggle -->
      <div v-if="node.children && node.children.length > 0" class="cursor-pointer" @click="toggleExpand">
        <span class="text-gray-500">{{ node.expanded ? '🔽' : '▶️' }}</span>
      </div>
      <div v-else class="w-4"></div>

      <!-- Editable field -->
      <div v-if="node.editing" class="flex items-center gap-1">
        <input
          v-model="node.text"
          @blur="stopEditing"
          @keyup.enter="stopEditing"
          type="text"
          placeholder="Enter node name"
          class="border rounded p-1 text-sm w-full min-w-[150px]"
          autofocus
        />
      </div>

      <!-- Normal display -->
      <div v-else class="flex-grow">
        <span
          class="py-1 px-2 rounded cursor-pointer hover:bg-gray-100"
          :class="{ 'font-medium': isRoot }"
        >
          {{ node.text }}
        </span>
      </div>

      <!-- Action buttons -->
      <div class="hidden group-hover:flex gap-1 items-center">
        <button
          @click="addChild"
          class="p-1 rounded hover:bg-green-100 text-green-600"
          title="Add child node"
        >
          ➕
        </button>
        <button
          @click="editNode"
          class="p-1 rounded hover:bg-yellow-100 text-yellow-600"
          title="Edit node"
        >
          ✏️
        </button>
        <button
          @click="confirmDelete"
          class="p-1 rounded hover:bg-red-100 text-red-600"
          title="Delete node"
        >
          🗑️
        </button>
      </div>
    </div>

    <!-- Children nodes -->
    <div
      v-if="node.children && node.children.length > 0 && (node.expanded || expandAll)"
      class="pl-4 mt-2 border-l border-gray-200"
    >
      <TreeNode
        v-for="(child, idx) in node.children"
        :key="child.id"
        :node="child"
        :parent="node.children"
        :index="idx"
        :expand-all="expandAll"
        @update="$emit('update')"
        @node-drag="$emit('node-drag', $event)"
        @node-drop="$emit('node-drop', $event)"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import TreeNode from './TreeNode.vue'

const props = defineProps({
  node: Object,
  parent: Array,
  index: Number,
  expandAll: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update', 'node-drag', 'node-drop'])

// Computed properties
const isRoot = computed(() => {
  // Check if this node is at the root level
  return !props.parent || props.parent === props.node.parent
})

// Watch for expandAll changes
watch(() => props.expandAll, (newValue) => {
  // This will be handled by the parent component
})

// Initialize node expanded state if not set
if (props.node.expanded === undefined) {
  props.node.expanded = false
}

// Toggle expand/collapse
const toggleExpand = () => {
  props.node.expanded = !props.node.expanded
  emit('update')
}

// Add a child node
const addChild = () => {
  // Ensure children array exists
  if (!props.node.children) {
    props.node.children = []
  }

  // Generate a unique ID for the new node
  const newId = Date.now()

  props.node.children.push({
    id: newId,
    text: '',
    children: [],
    editing: true,
    expanded: true
  })

  // Expand the parent node to show the new child
  props.node.expanded = true

  emit('update')
}

// Delete node with confirmation
const confirmDelete = () => {
  if (props.node.children && props.node.children.length > 0) {
    if (!confirm(`Delete "${props.node.text}" and all its children?`)) {
      return
    }
  }

  deleteNode()
}

// Delete node
const deleteNode = () => {
  props.parent.splice(props.index, 1)
  emit('update')
}

// Edit node
const editNode = () => {
  props.node.editing = true
}

// Stop editing
const stopEditing = () => {
  if (props.node.text.trim() === '') {
    props.node.text = 'Unnamed Node'
  }
  props.node.editing = false
  emit('update')
}

// Drag and drop functionality
let draggedNode = null
let draggedNodeParent = null
let draggedNodeIndex = null

const handleDragStart = (event) => {
  draggedNode = props.node
  draggedNodeParent = props.parent
  draggedNodeIndex = props.index

  // Set data for drag operation
  event.dataTransfer.effectAllowed = 'move'
  event.dataTransfer.setData('text/plain', props.node.id)

  // Add a class to the dragged element
  event.target.classList.add('dragging')
}

const handleDragOver = (event) => {
  // Calculate position for visual indicator
  const rect = event.target.getBoundingClientRect()
  const y = event.clientY - rect.top

  emit('node-drag', { y: event.clientY })

  event.preventDefault()
}

const handleDragEnd = (event) => {
  event.target.classList.remove('dragging')
  emit('node-drag', { visible: false })
}

const handleDrop = (event) => {
  // Prevent dropping a node onto itself or its children
  if (isChildOf(props.node, draggedNode)) {
    return
  }

  // Emit drop event with all necessary information
  emit('node-drop', {
    sourceNodeId: draggedNode.id,
    sourceParent: draggedNodeParent,
    sourceIndex: draggedNodeIndex,
    targetParent: props.parent,
    targetIndex: props.index
  })
}

// Helper function to check if a node is a child of another node
const isChildOf = (parentNode, childNode) => {
  if (parentNode.id === childNode.id) {
    return true
  }

  if (parentNode.children && parentNode.children.length > 0) {
    return parentNode.children.some(node => isChildOf(node, childNode))
  }

  return false
}
</script>

<style scoped>
.dragging {
  opacity: 0.5;
}

.drag-over {
  border-top: 2px solid #4299e1;
}
</style>
