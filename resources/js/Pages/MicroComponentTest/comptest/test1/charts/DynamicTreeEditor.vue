<template>
  <div class="p-6 space-y-4">
    <!-- Loading overlay -->
    <div v-if="loading" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white p-6 rounded-lg shadow-lg text-center">
        <svg class="animate-spin h-10 w-10 text-blue-500 mx-auto mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <p>{{ loadingMessage }}</p>
      </div>
    </div>

    <!-- Tree management -->
    <div class="bg-white p-4 rounded shadow">
      <div class="flex flex-col md:flex-row md:items-center gap-4 mb-4">
        <div class="flex-grow">
          <label class="block text-sm font-medium text-gray-700 mb-1">Tree Name</label>
          <input
            v-model="treeName"
            type="text"
            class="border rounded p-2 w-full"
            placeholder="Enter tree name"
          />
        </div>
        <div class="flex gap-2">
          <button @click="loadTreeList" class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded flex items-center">
            <span class="mr-1">📋</span> Load
          </button>
          <button @click="createNewTree" class="bg-teal-500 hover:bg-teal-600 text-white px-4 py-2 rounded flex items-center">
            <span class="mr-1">📝</span> New
          </button>
        </div>
      </div>

      <!-- Tree selector (shows when loadingTreeList is true) -->
      <div v-if="showTreeList" class="mb-4 bg-gray-50 p-3 rounded border">
        <h3 class="font-medium mb-2">Select a Tree</h3>
        <div v-if="treeList.length === 0" class="text-gray-500 italic">No saved trees found</div>
        <div v-else class="space-y-2 max-h-40 overflow-y-auto">
          <div
            v-for="tree in treeList"
            :key="tree.id"
            class="flex justify-between items-center p-2 hover:bg-gray-100 rounded cursor-pointer"
            @click="loadTree(tree.id)"
          >
            <span>{{ tree.name }}</span>
            <span class="text-xs text-gray-500">{{ formatDate(tree.updated_at) }}</span>
          </div>
        </div>
        <button @click="showTreeList = false" class="mt-2 text-sm text-gray-500">Cancel</button>
      </div>
    </div>

    <!-- Buttons -->
    <div class="flex flex-wrap gap-2">
      <button @click="addRootNode" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded flex items-center">
        <span class="mr-1">➕</span> Add Root Node
      </button>
      <button @click="saveTree" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded flex items-center">
        <span class="mr-1">💾</span> Save Tree
      </button>
      <button @click="toggleExpandAll" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded flex items-center">
        <span class="mr-1">{{ expandAll ? '🔼' : '🔽' }}</span> {{ expandAll ? 'Collapse All' : 'Expand All' }}
      </button>
    </div>

    <!-- Tree structure -->
    <div class="tree bg-white p-4 rounded shadow">
      <div v-if="treeData.length === 0" class="text-center text-gray-500 py-10">
        <p class="text-xl mb-2">No nodes yet</p>
        <p>Click "Add Root Node" to start building your tree</p>
      </div>
      <div v-else class="relative">
        <!-- Drag indicator -->
        <div
          v-if="dragIndicator.visible"
          class="absolute h-0.5 bg-blue-500 transition-all"
          :style="{ left: '0', right: '0', top: `${dragIndicator.y}px` }"
        ></div>

        <TreeNode
          v-for="(node, index) in treeData"
          :key="node.id"
          :node="node"
          :parent="treeData"
          :index="index"
          :expand-all="expandAll"
          @update="handleUpdate"
          @node-drag="handleNodeDrag"
          @node-drop="handleNodeDrop"
        />
      </div>
    </div>

    <!-- Confirmation modal -->
    <div v-if="showConfirmation" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white p-6 rounded-lg shadow-lg max-w-md w-full">
        <h3 class="text-lg font-medium mb-4">{{ confirmationMessage }}</h3>
        <div class="flex justify-end gap-2">
          <button @click="cancelConfirmation" class="px-4 py-2 border rounded hover:bg-gray-100">Cancel</button>
          <button @click="confirmAction" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">Confirm</button>
        </div>
      </div>
    </div>

    <!-- Toast notifications -->
    <div class="fixed bottom-4 right-4 z-50">
      <div
        v-for="(toast, index) in toasts"
        :key="index"
        class="mb-2 p-3 rounded shadow-lg max-w-xs transform transition-all duration-300"
        :class="[
          toast.type === 'success' ? 'bg-green-500 text-white' :
          toast.type === 'error' ? 'bg-red-500 text-white' :
          'bg-blue-500 text-white'
        ]"
      >
        <div class="flex items-center">
          <span class="mr-2">
            {{ toast.type === 'success' ? '✅' : toast.type === 'error' ? '❌' : 'ℹ️' }}
          </span>
          <span>{{ toast.message }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue'
import TreeNode from './TreeNode.vue'
import axios from 'axios'

// State management
const treeData = ref([])
const treeName = ref('My Tree Structure')
const loading = ref(false)
const loadingMessage = ref('')
const showTreeList = ref(false)
const treeList = ref([])
const expandAll = ref(false)
const nextId = ref(1)
const currentTreeId = ref(null)

// Drag and drop state
const dragIndicator = ref({
  visible: false,
  y: 0
})

// Confirmation modal state
const showConfirmation = ref(false)
const confirmationMessage = ref('')
const pendingAction = ref(null)
const pendingActionParams = ref(null)

// Toast notifications
const toasts = ref([])

// Load data on component mount
onMounted(async () => {
  await loadInitialData()
})

// Load initial data
const loadInitialData = async () => {
  setLoading(true, 'Loading tree data...')

  try {
    // Try to load the most recently updated tree
    const response = await axios.get('/api/tree-structures/latest')

    if (response.data) {
      treeName.value = response.data.name
      treeData.value = response.data.tree_data
      currentTreeId.value = response.data.id

      // Find the highest ID in the tree to set nextId
      updateNextId()

      showToast('Tree loaded successfully', 'success')
    } else {
      // If no trees exist, initialize with empty data
      initializeEmptyTree()
    }
  } catch (error) {
    console.error('Error loading tree data:', error)
    showToast('Failed to load tree data. Starting with empty tree.', 'error')
    initializeEmptyTree()
  } finally {
    setLoading(false)
  }
}

// Initialize empty tree
const initializeEmptyTree = () => {
  treeData.value = []
  nextId.value = 1
  currentTreeId.value = null
}

// Create a new empty tree
const createNewTree = () => {
  showConfirmationDialog('Create a new tree? Any unsaved changes will be lost.', () => {
    treeName.value = 'New Tree Structure'
    treeData.value = []
    nextId.value = 1
    currentTreeId.value = null
    showToast('New tree created', 'info')
  })
}

// Load the list of available trees
const loadTreeList = async () => {
  setLoading(true, 'Loading available trees...')

  try {
    const response = await axios.get('/api/tree-structures')
    treeList.value = response.data
    showTreeList.value = true
  } catch (error) {
    console.error('Error loading tree list:', error)
    showToast('Failed to load tree list', 'error')
  } finally {
    setLoading(false)
  }
}

// Load a specific tree by ID
const loadTree = async (id) => {
  setLoading(true, 'Loading selected tree...')
  showTreeList.value = false

  try {
    const response = await axios.get(`/api/tree-structures/${id}`)
    treeName.value = response.data.name
    treeData.value = response.data.tree_data
    currentTreeId.value = response.data.id

    // Update nextId based on the loaded tree
    updateNextId()

    showToast('Tree loaded successfully', 'success')
  } catch (error) {
    console.error('Error loading tree:', error)
    showToast('Failed to load tree', 'error')
  } finally {
    setLoading(false)
  }
}

// Update nextId based on the current tree data
const updateNextId = () => {
  let highestId = 0

  const findHighestId = (nodes) => {
    for (const node of nodes) {
      if (node.id > highestId) {
        highestId = node.id
      }
      if (node.children && node.children.length > 0) {
        findHighestId(node.children)
      }
    }
  }

  findHighestId(treeData.value)
  nextId.value = highestId + 1
}

// Add a new root node in editing mode
const addRootNode = () => {
  treeData.value.push({
    id: nextId.value++,
    text: '',
    children: [],
    editing: true,
    expanded: true
  })
}

// Save tree to backend
const saveTree = async () => {
  if (!treeName.value.trim()) {
    showToast('Please enter a tree name', 'error')
    return
  }

  setLoading(true, 'Saving tree...')

  try {
    const payload = {
      name: treeName.value,
      tree_data: treeData.value
    }

    let response

    if (currentTreeId.value) {
      // Update existing tree
      response = await axios.put(`/api/tree-structures/${currentTreeId.value}`, payload)
    } else {
      // Create new tree
      response = await axios.post('/api/tree-structures', payload)
      currentTreeId.value = response.data.id
    }

    showToast('Tree saved successfully', 'success')
  } catch (error) {
    console.error('Error saving tree:', error)
    showToast('Failed to save tree', 'error')
  } finally {
    setLoading(false)
  }
}

// Toggle expand/collapse all nodes
const toggleExpandAll = () => {
  expandAll.value = !expandAll.value

  const updateNodeExpansion = (nodes) => {
    for (const node of nodes) {
      node.expanded = expandAll.value
      if (node.children && node.children.length > 0) {
        updateNodeExpansion(node.children)
      }
    }
  }

  updateNodeExpansion(treeData.value)
}

// Handle update event from TreeNode
const handleUpdate = () => {
  // This could be used to trigger auto-save or other reactions to tree changes
}

// Handle node drag events
const handleNodeDrag = (event) => {
  dragIndicator.value.visible = true
  dragIndicator.value.y = event.y
}

// Handle node drop events
const handleNodeDrop = (event) => {
  dragIndicator.value.visible = false

  const { sourceNodeId, sourceParent, sourceIndex, targetParent, targetIndex } = event

  // Remove from source
  const [movedNode] = sourceParent.splice(sourceIndex, 1)

  // Insert at target
  targetParent.splice(targetIndex, 0, movedNode)
}

// Confirmation dialog functions
const showConfirmationDialog = (message, action, params = null) => {
  confirmationMessage.value = message
  pendingAction.value = action
  pendingActionParams.value = params
  showConfirmation.value = true
}

const cancelConfirmation = () => {
  showConfirmation.value = false
  pendingAction.value = null
  pendingActionParams.value = null
}

const confirmAction = () => {
  if (pendingAction.value) {
    if (pendingActionParams.value) {
      pendingAction.value(pendingActionParams.value)
    } else {
      pendingAction.value()
    }
  }

  showConfirmation.value = false
  pendingAction.value = null
  pendingActionParams.value = null
}

// Helper functions
const setLoading = (isLoading, message = '') => {
  loading.value = isLoading
  loadingMessage.value = message
}

const showToast = (message, type = 'info') => {
  const toast = {
    message,
    type
  }

  toasts.value.push(toast)

  // Auto-remove toast after 3 seconds
  setTimeout(() => {
    const index = toasts.value.indexOf(toast)
    if (index !== -1) {
      toasts.value.splice(index, 1)
    }
  }, 3000)
}

const formatDate = (dateString) => {
  if (!dateString) return ''

  const date = new Date(dateString)
  return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
}
</script>

<style scoped>
.tree {
  min-height: 400px;
  max-height: 600px;
  overflow-y: auto;
}

/* Fade-in animation for toasts */
.toast-enter-active, .toast-leave-active {
  transition: all 0.3s ease;
}
.toast-enter-from, .toast-leave-to {
  opacity: 0;
  transform: translateY(20px);
}
</style>
