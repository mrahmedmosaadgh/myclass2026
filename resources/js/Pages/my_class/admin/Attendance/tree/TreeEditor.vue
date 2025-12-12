<template>
    <div>
        <q-toggle
        v-model="edit_mood"
        label="edit_mood"
      /> {{ edit_mood }}

      <q-toggle
        v-model="isDraggable"
        label="isDraggable"
      /> {{ isDraggable }}

      <q-tree
        :nodes="localNodes"
        node-key="id"
        :selected.sync="selected"
        :expanded.sync="expanded"
        :filter="filter"
      >
        <template v-slot:default-header="props">
          <draggable-item
         :draggable="isDraggable"
            :id="props.node.id"
            @drop="onDrop"
            @dragover="onDragOverNode"
            @dragleave="onDragLeaveNode"
          >
            <div class="row items-center justify-between full-width">
              <div class="row items-center text-xs">
                <q-icon :name="props.node.icon || 'folder'" class="q-mr-sm" />
                <div v-if="!props.node.editing">
                  {{ props.node.label }}
                </div>
                <q-input
                  v-else
                  v-model="props.node.label"
                  dense
                  autofocus
                  @blur="stopEditing(props.node)"
                  @keyup.enter="stopEditing(props.node)"
                />
              </div>
              <div v-if="edit_mood" class="row items-center  text-xs full-width-row">
                <q-btn flat    dense icon="edit" size="sm" @click.stop="editNode(props.node)" />
                <q-btn flat dense icon="add" size="sm" @click.stop="addNode(props.node)" />
                <q-btn flat dense icon="delete" size="sm" color="negative" @click.stop="deleteNode(props.node)" />
              </div>
            </div>
          </draggable-item>
        </template>
      </q-tree>
    </div>
  </template>

  <script setup>
  import { ref, watch } from 'vue'
  import { uid } from 'quasar'
  import DraggableItem from './DraggableItem.vue'

  // Props
  const props = defineProps({
    modelValue: {
      type: Array,
      required: true
    },
    filter: {
      type: String,
      default: ''
    }
  })

  // Emits
  const emit = defineEmits(['update:modelValue'])

  // Local reactive states
  const localNodes = ref(JSON.parse(JSON.stringify(props.modelValue)))
  const selected = ref(null)
  const expanded = ref([])
  const hoverTimer = ref(null)
  const edit_mood = ref(false)
  const isDraggable = ref(false)

  // Watch external model changes
  watch(
    () => props.modelValue,
    (newVal) => {
      localNodes.value = JSON.parse(JSON.stringify(newVal))
    },
    { deep: true }
  )

  // Update parent
  function updateModel() {
    emit('update:modelValue', JSON.parse(JSON.stringify(localNodes.value)))
  }

  // Node Actions
  function editNode(node) {
    node.editing = true
  }

  function stopEditing(node) {
    node.editing = false
    updateModel()
  }

  function addNode(parentNode) {
    const newNode = {
      id: uid(),
      label: 'New Node',
      children: []
    }
    if (!parentNode.children) {
      parentNode.children = []
    }
    parentNode.children.push(newNode)
    expanded.value.push(parentNode.id)
    updateModel()
  }

  function deleteNode(node) {
    function recursiveDelete(nodes, targetId) {
      return nodes.filter(n => {
        if (n.id === targetId) return false
        if (n.children) {
          n.children = recursiveDelete(n.children, targetId)
        }
        return true
      })
    }
    localNodes.value = recursiveDelete(localNodes.value, node.id)
    updateModel()
  }

  // Drag & Drop Logic
  function findNodeAndRemove(nodes, id) {
    for (let i = 0; i < nodes.length; i++) {
      if (nodes[i].id == id) {
        const node = nodes[i]
        nodes.splice(i, 1)
        return node
      }
      if (nodes[i].children) {
        const result = findNodeAndRemove(nodes[i].children, id)
        if (result) return result
      }
    }
  }

  function findNodeAndInsert(nodes, targetId, nodeToInsert) {
    for (const node of nodes) {
      if (node.id == targetId) {
        if (!node.children) node.children = []
        node.children.push(nodeToInsert)
        return true
      }
      if (node.children) {
        const inserted = findNodeAndInsert(node.children, targetId, nodeToInsert)
        if (inserted) return true
      }
    }
  }

  // Check if node is descendant
  function isDescendant(targetNode, idToCheck) {
    if (!targetNode.children) return false
    for (const child of targetNode.children) {
      if (child.id === idToCheck || isDescendant(child, idToCheck)) {
        return true
      }
    }
    return false
  }

  function onDrop({ draggedId, targetId }) {
    if (draggedId === targetId) return

    const nodeToMove = findNodeAndRemove(localNodes.value, draggedId)
    const targetNode = findNodeById(localNodes.value, targetId)

    if (!nodeToMove || !targetNode) return

    if (isDescendant(nodeToMove, targetId)) {
      console.warn('Cannot move parent into its child')
      updateModel()
      return
    }

    findNodeAndInsert(localNodes.value, targetId, nodeToMove)
    expanded.value.push(targetId)
    updateModel()
  }

  function findNodeById(nodes, id) {
    for (const node of nodes) {
      if (node.id == id) return node
      if (node.children) {
        const found = findNodeById(node.children, id)
        if (found) return found
      }
    }
  }

  // Auto-expand when hover
  function onDragOverNode(nodeId) {
    clearTimeout(hoverTimer.value)
    hoverTimer.value = setTimeout(() => {
      if (!expanded.value.includes(nodeId)) {
        expanded.value.push(nodeId)
      }
    }, 500)
  }

  function onDragLeaveNode() {
    clearTimeout(hoverTimer.value)
  }
  </script>

  <style scoped>
  .q-tree__node-header {
    position: relative;
  }
  </style>


