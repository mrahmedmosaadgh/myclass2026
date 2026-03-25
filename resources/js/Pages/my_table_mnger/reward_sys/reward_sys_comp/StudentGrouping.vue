<template>
  <div class="flex flex-col h-full bg-gray-50">
    <!-- Toolbar -->
    <div class="bg-white p-4 border-b flex items-center justify-between shadow-sm flex-shrink-0">
      <div class="flex items-center gap-4">
        <q-input
          v-model="layoutName"
          placeholder="Layout Name (e.g., Lab Groups)"
          dense
          outlined
          class="w-64"
          :rules="[val => !!val || 'Name is required']"
        />
        <div class="text-xs text-gray-500">
          <span class="font-bold">{{ students.length }}</span> total students
        </div>
      </div>
      <div class="flex gap-2">
        <q-btn flat label="Cancel" @click="emit('close')" color="grey-7" />
        <q-btn
          color="primary"
          icon="save"
          label="Save Layout"
          @click="saveLayout"
          :disable="!layoutName"
        />
      </div>
    </div>

    <div class="flex-1 flex overflow-hidden">
      <!-- Left Sidebar: Saved Layouts -->
      <div v-if="savedLayouts.length > 0" class="w-64 bg-white border-r flex flex-col shadow-lg flex-shrink-0 z-20">
        <div class="p-4 border-b bg-indigo-50">
          <h3 class="font-bold text-indigo-900 flex items-center gap-2">
            <q-icon name="folder" />
            Saved Layouts
          </h3>
        </div>
        <div class="flex-1 overflow-y-auto p-2">
          <div
            v-for="layout in savedLayouts"
            :key="layout.id"
            class="mb-2 p-3 rounded-lg border transition-all cursor-pointer"
            :class="editingLayoutId === layout.id ? 'bg-indigo-100 border-indigo-400' : 'bg-gray-50 border-gray-200 hover:border-indigo-300 hover:bg-indigo-50'"
            @click="loadLayout(layout)"
          >
            <div class="flex items-center justify-between">
              <div class="flex-1 min-w-0">
                <div class="font-bold text-sm text-gray-800 truncate">{{ layout.name }}</div>
                <div class="text-xs text-gray-500">{{ layout.groups?.length || 0 }} groups</div>
              </div>
              <div class="flex gap-1">
                <q-btn
                  flat
                  round
                  dense
                  size="sm"
                  icon="edit"
                  color="primary"
                  @click.stop="loadLayout(layout)"
                >
                  <q-tooltip>Edit</q-tooltip>
                </q-btn>
                <q-btn
                  flat
                  round
                  dense
                  size="sm"
                  icon="delete"
                  color="negative"
                  @click.stop="deleteLayout(layout)"
                >
                  <q-tooltip>Delete</q-tooltip>
                </q-btn>
              </div>
            </div>
          </div>
        </div>
        <div class="p-3 border-t bg-gray-50">
          <q-btn
            flat
            dense
            color="primary"
            icon="add"
            label="New Layout"
            class="w-full"
            @click="createNewLayout"
          />
        </div>
      </div>

      <!-- Main Area: Groups with Tabs -->
      <div class="flex-1 flex flex-col min-w-0 bg-slate-50">
        <!-- Tabs Header -->
        <div class="bg-white border-b shadow-sm z-10">
          <div class="flex items-center">
            <q-tabs
              v-model="activeTab"
              dense
              class="text-gray-600 flex-1"
              active-color="primary"
              indicator-color="primary"
              align="left"
              inline-label
              outside-arrows
              mobile-arrows
            >
              <!-- Unassigned Tab -->
              <q-tab name="unassigned" class="px-4 py-3">
                <div class="flex items-center gap-2">
                  <q-icon name="person_off" size="xs" />
                  <span class="font-bold">Unassigned</span>
                  <q-badge color="grey-7" :label="unassignedStudents.length" rounded />
                </div>
              </q-tab>

              <!-- Group Tabs -->
              <q-tab
                v-for="(group, index) in groups"
                :key="group.id"
                :name="group.id"
                class="px-4 py-3"
              >
                <div class="flex items-center gap-2">
                  <q-icon name="groups" size="xs" />
                  <span class="font-bold max-w-[120px] truncate">{{ group.name }}</span>
                  <q-badge color="indigo" :label="group.students.length" rounded />
                </div>
              </q-tab>
            </q-tabs>
            
            <div class="px-2 border-l">
              <q-btn flat round dense color="primary" icon="add" @click="addGroup">
                <q-tooltip>Add New Group</q-tooltip>
              </q-btn>
            </div>
          </div>
        </div>

        <!-- Groups Selection Toolbar -->
        <div class="bg-indigo-50 border-b px-4 py-2 flex flex-wrap gap-3 items-center justify-between min-h-[50px]">
          <!-- Left: Selection Controls -->
          <div class="flex items-center gap-3">
            <q-checkbox
              :model-value="isAllSelected"
              @update:model-value="toggleSelectAll"
              dense
              color="primary"
              label="Select All"
            />
            <div class="h-4 w-px bg-indigo-200 mx-1"></div>
            <span class="text-sm text-indigo-900 font-medium" v-if="selectedStudentIds.size > 0">
              {{ selectedStudentIds.size }} selected
            </span>
          </div>

          <!-- Right: Actions -->
          <div class="flex items-center gap-2">
            <!-- Rename Group (if not unassigned) -->
            <div v-if="activeTab !== 'unassigned'" class="flex items-center gap-2 mr-4">
              <q-input
                v-model="currentGroupName"
                dense
                outlined
                bg-color="white"
                label="Group Name"
                class="w-48"
                @blur="updateGroupName"
                @keyup.enter="updateGroupName"
              >
                <template v-slot:append>
                  <q-icon name="edit" size="xs" color="indigo" />
                </template>
              </q-input>
              <q-btn
                flat
                round
                dense
                color="negative"
                icon="delete"
                @click="confirmDeleteGroup"
              >
                <q-tooltip>Delete Group</q-tooltip>
              </q-btn>
            </div>

            <!-- Move Actions -->
            <div class="flex items-center gap-2" v-if="selectedStudentIds.size > 0">
               <span class="text-sm text-gray-600 mr-1">Move to:</span>
               
               <!-- Move to specific group -->
               <q-btn-dropdown
                 color="primary"
                 :label="activeTab === 'unassigned' ? 'Group' : 'Other Group'"
                 outline
                 dense
                 icon="drive_file_move"
                 class="bg-white"
               >
                 <q-list class="min-w-[150px]">
                   <q-item
                     v-for="group in groups"
                     :key="group.id"
                     clickable
                     v-close-popup
                     @click="moveSelectedToGroup(group.id)"
                     :disable="group.id === activeTab"
                     v-show="group.id !== activeTab"
                   >
                     <q-item-section>
                       <q-item-label>{{ group.name }}</q-item-label>
                     </q-item-section>
                   </q-item>
                   
                   <!-- Option to move to Unassigned -->
                   <q-item
                     v-if="activeTab !== 'unassigned'"
                     clickable
                     v-close-popup
                     @click="moveSelectedToUnassigned"
                   >
                      <q-item-section class="text-orange-800">
                       <q-item-label>Unassigned</q-item-label>
                     </q-item-section>
                   </q-item>
                 </q-list>
               </q-btn-dropdown>
            </div>
          </div>
        </div>

        <!-- Content Area -->
        <div class="flex-1 overflow-y-auto p-4 custom-scrollbar">
          <!-- Empty State -->
          <div 
            v-if="currentTabStudents.length === 0" 
            class="h-full flex flex-col items-center justify-center text-gray-400 min-h-[200px]"
          >
            <q-icon 
              :name="activeTab === 'unassigned' ? 'person_off' : 'groups'" 
              size="4rem" 
              class="mb-4 opacity-50" 
            />
            <p class="text-lg">No students in {{ activeTab === 'unassigned' ? 'Unassigned' : 'this group' }}</p>
          </div>

          <!-- Students Grid -->
          <div v-else class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3">
            <div
              v-for="student in currentTabStudents"
              :key="student.id"
              class="relative bg-white p-3 rounded-lg border shadow-sm cursor-pointer transition-all hover:shadow-md select-none group"
              :class="selectedStudentIds.has(student.id) ? 'border-primary ring-1 ring-primary bg-blue-50' : 'border-gray-200 hover:border-blue-300'"
              @click="toggleSelection(student.id)"
            >
              <!-- Checkbox Overlay -->
              <div class="absolute top-2 right-2 z-10">
                <q-checkbox
                  :model-value="selectedStudentIds.has(student.id)"
                  @update:model-value="toggleSelection(student.id)"
                  dense
                  size="sm"
                  color="primary"
                  class="bg-white/80 rounded-full"
                />
              </div>

              <!-- Student Info -->
              <div class="flex flex-col items-center gap-2 pt-2">
                <q-avatar size="md" color="blue-100" text-color="blue-800" font-size="14px">
                  {{ student.firstName?.[0] }}{{ student.lastName?.[0] }}
                </q-avatar>
                <div class="text-center w-full">
                  <div class="text-sm font-bold text-gray-800 truncate px-1">
                    {{ student.firstName }}
                  </div>
                  <div class="text-xs text-gray-500 truncate px-1">
                    {{ student.lastName }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import { useQuasar } from 'quasar'

const $q = useQuasar()

const props = defineProps({
  students: {
    type: Array,
    required: true
  },
  savedLayouts: {
    type: Array,
    default: () => []
  },
  editingLayout: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['save', 'close', 'delete'])

// State
const layoutName = ref('')
const groups = ref([])
const unassignedStudents = ref([])
const editingLayoutId = ref(null)
const activeTab = ref('unassigned')
const selectedStudentIds = ref(new Set())
const currentGroupName = ref('')

// Initialize
onMounted(() => {
  if (props.editingLayout) {
    loadLayoutData(props.editingLayout)
  } else {
    // New layout: all students unassigned
    unassignedStudents.value = [...props.students]
  }
})

// Watch active tab to update group name input and clear selection
watch(activeTab, (newTab) => {
  selectedStudentIds.value.clear()
  if (newTab !== 'unassigned') {
    const group = groups.value.find(g => g.id === newTab)
    if (group) {
      currentGroupName.value = group.name
    }
  } else {
    currentGroupName.value = ''
  }
})

// Computed
const currentTabStudents = computed(() => {
  if (activeTab.value === 'unassigned') {
    return unassignedStudents.value
  }
  const group = groups.value.find(g => g.id === activeTab.value)
  return group ? group.students : []
})

const isAllSelected = computed(() => {
  const students = currentTabStudents.value
  return students.length > 0 && students.every(s => selectedStudentIds.value.has(s.id))
})

// Methods
const loadLayoutData = (layout) => {
  editingLayoutId.value = layout.id
  layoutName.value = layout.name
  
  // Reconstruct groups
  groups.value = layout.groups.map(g => ({
    id: Date.now() + Math.random(), // Temporary ID
    name: g.name,
    students: props.students.filter(s => g.student_ids.includes(s.id))
  }))
  
  // Calculate unassigned students
  const assignedIds = new Set()
  groups.value.forEach(g => {
    g.students.forEach(s => assignedIds.add(s.id))
  })
  
  unassignedStudents.value = props.students.filter(s => !assignedIds.has(s.id))
  activeTab.value = 'unassigned'
}

const loadLayout = (layout) => {
  loadLayoutData(layout)
}

const createNewLayout = () => {
  editingLayoutId.value = null
  layoutName.value = ''
  groups.value = []
  unassignedStudents.value = [...props.students]
  activeTab.value = 'unassigned'
}

const deleteLayout = (layout) => {
  $q.dialog({
    title: 'Delete Layout',
    message: `Are you sure you want to delete "${layout.name}"? This action cannot be undone.`,
    cancel: true,
    persistent: true,
    ok: { label: 'Delete', color: 'negative', flat: true }
  }).onOk(() => {
    emit('delete', layout.id)
    if (editingLayoutId.value === layout.id) {
      createNewLayout()
    }
  })
}

// Group Management
const addGroup = () => {
  const newGroup = {
    id: Date.now() + Math.random(),
    name: `Group ${groups.value.length + 1}`,
    students: []
  }
  groups.value.push(newGroup)
  activeTab.value = newGroup.id
}

const updateGroupName = () => {
  if (activeTab.value === 'unassigned') return
  const group = groups.value.find(g => g.id === activeTab.value)
  if (group) {
    group.name = currentGroupName.value || 'Untitled Group'
  }
}

const confirmDeleteGroup = () => {
  if (activeTab.value === 'unassigned') return
  
  const groupIndex = groups.value.findIndex(g => g.id === activeTab.value)
  if (groupIndex === -1) return

  const group = groups.value[groupIndex]
  
  $q.dialog({
    title: 'Delete Group',
    message: `Delete "${group.name}"? Students will be moved to Unassigned.`,
    cancel: true,
    persistent: true,
    ok: { label: 'Delete', color: 'negative' }
  }).onOk(() => {
    // Move students to unassigned
    unassignedStudents.value.push(...group.students)
    // Remove group
    groups.value.splice(groupIndex, 1)
    // Switch to unassigned
    activeTab.value = 'unassigned'
  })
}

// Selection & Movement
const toggleSelection = (id) => {
  if (selectedStudentIds.value.has(id)) {
    selectedStudentIds.value.delete(id)
  } else {
    selectedStudentIds.value.add(id)
  }
}

const toggleSelectAll = (val) => {
  if (val) {
    currentTabStudents.value.forEach(s => selectedStudentIds.value.add(s.id))
  } else {
    selectedStudentIds.value.clear()
  }
}

const moveSelectedToGroup = (targetGroupId) => {
  const targetGroup = groups.value.find(g => g.id === targetGroupId)
  if (!targetGroup) return

  const studentsToMove = currentTabStudents.value.filter(s => selectedStudentIds.value.has(s.id))
  
  // Add to target
  targetGroup.students.push(...studentsToMove)
  
  // Remove from current
  if (activeTab.value === 'unassigned') {
    unassignedStudents.value = unassignedStudents.value.filter(s => !selectedStudentIds.value.has(s.id))
  } else {
    const currentGroup = groups.value.find(g => g.id === activeTab.value)
    if (currentGroup) {
      currentGroup.students = currentGroup.students.filter(s => !selectedStudentIds.value.has(s.id))
    }
  }
  
  // Clear selection
  selectedStudentIds.value.clear()
  
  $q.notify({
    message: `Moved ${studentsToMove.length} students to ${targetGroup.name}`,
    color: 'positive',
    icon: 'check',
    timeout: 1000
  })
}

const moveSelectedToUnassigned = () => {
  const studentsToMove = currentTabStudents.value.filter(s => selectedStudentIds.value.has(s.id))
  
  // Add to unassigned
  unassignedStudents.value.push(...studentsToMove)
  
  // Remove from current group
  const currentGroup = groups.value.find(g => g.id === activeTab.value)
  if (currentGroup) {
    currentGroup.students = currentGroup.students.filter(s => !selectedStudentIds.value.has(s.id))
  }
  
  selectedStudentIds.value.clear()
  
  $q.notify({
    message: `Moved ${studentsToMove.length} students to Unassigned`,
    color: 'positive',
    icon: 'check',
    timeout: 1000
  })
}

const saveLayout = () => {
  if (!layoutName.value) return
  
  const layout = {
    id: editingLayoutId.value || Date.now().toString(),
    name: layoutName.value,
    groups: groups.value.map(g => ({
      name: g.name,
      student_ids: g.students.map(s => s.id)
    }))
  }
  
  emit('save', layout)
}
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: #cbd5e1;
  border-radius: 3px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background-color: #94a3b8;
}
</style>
