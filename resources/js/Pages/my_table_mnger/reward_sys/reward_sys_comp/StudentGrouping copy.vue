<template>
  <div class="flex flex-col h-full bg-gray-50">
    <!-- Toolbar -->
    <div class="bg-white p-4 border-b flex items-center justify-between shadow-sm">
      <div class="flex items-center gap-4">
        <q-input
          v-model="layoutName"
          placeholder="Layout Name (e.g., Lab Groups)"
          dense
          outlined
          class="w-64"
          :rules="[val => !!val || 'Name is required']"
        />
        <q-btn
          color="primary"
          icon="add"
          label="Add Group"
          flat
          @click="addGroup"
        />
      </div>
      <div class="flex gap-2">
        <q-btn flat label="Cancel" @click="emit('close')" color="grey-7" />
        <q-btn
          color="primary"
          icon="save"
          label="Save Layout"
          @click="saveLayout"
          :disable="!layoutName || groups.length === 0"
        />
      </div>
    </div>

    <div class="flex-1 flex overflow-hidden">
      <!-- Left Sidebar: Saved Layouts -->
      <div v-if="savedLayouts.length > 0" class="w-64 bg-white border-r flex flex-col shadow-lg">
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
                  @click="loadLayout(layout)"
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
                  @click="deleteLayout(layout)"
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

      <!-- Unassigned Students Sidebar -->
      <div class="w-64 bg-white border-r flex flex-col z-10 shadow-lg">
        <div class="p-4 border-b bg-gray-50">
          <h3 class="font-bold text-gray-700 flex items-center gap-2">
            <q-icon name="person_off" />
            Unassigned ({{ unassignedStudents.length }})
          </h3>
        </div>
        <div class="flex-1 overflow-y-auto p-2 bg-gray-100/50">
          <draggable
            v-model="unassignedStudents"
            group="students"
            item-key="id"
            class="flex flex-col gap-2 min-h-[100px]"
            :animation="200"
            ghost-class="opacity-50"
          >
            <template #item="{ element }">
              <div class="bg-white p-3 rounded-lg shadow-sm border border-gray-200 cursor-move hover:border-blue-400 hover:shadow-md transition-all flex items-center gap-3">
                <q-avatar size="sm" color="blue-100" text-color="blue-800" font-size="12px">
                  {{ element.firstName?.[0] }}{{ element.lastName?.[0] }}
                </q-avatar>
                <div class="text-sm font-medium text-gray-800 truncate">
                  {{ element.firstName }} {{ element.lastName }}
                </div>
              </div>
            </template>
          </draggable>
        </div>
      </div>

      <!-- Main Area: Groups -->
      <div class="flex-1 overflow-y-auto p-6 bg-slate-100">
        <div v-if="groups.length === 0" class="h-full flex flex-col items-center justify-center text-gray-400">
          <q-icon name="groups" size="4rem" class="mb-4 opacity-50" />
          <p class="text-lg">No groups created yet.</p>
          <q-btn flat color="primary" label="Create your first group" @click="addGroup" />
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 items-start">
          <div
            v-for="(group, index) in groups"
            :key="group.id"
            class="bg-white rounded-xl shadow-sm border border-gray-200 flex flex-col overflow-hidden transition-all hover:shadow-md"
          >
            <!-- Group Header -->
            <div class="p-3 border-b bg-gray-50 flex items-center justify-between group-header">
              <input
                v-model="group.name"
                class="bg-transparent font-bold text-gray-700 border-none focus:ring-0 p-0 w-full"
                placeholder="Group Name"
              />
              <q-btn
                flat
                round
                dense
                color="negative"
                icon="close"
                size="sm"
                class="opacity-0 group-hover:opacity-100 transition-opacity"
                @click="removeGroup(index)"
              />
            </div>

            <!-- Group Content -->
            <div class="p-2 min-h-[150px] bg-white">
              <draggable
                v-model="group.students"
                group="students"
                item-key="id"
                class="flex flex-col gap-2 h-full min-h-[100px]"
                :animation="200"
                ghost-class="opacity-50"
              >
                <template #item="{ element }">
                  <div class="bg-blue-50 p-2 rounded-lg border border-blue-100 cursor-move flex items-center gap-2 hover:bg-blue-100 transition-colors">
                    <q-avatar size="xs" color="blue-600" text-color="white">
                      {{ element.firstName?.[0] }}
                    </q-avatar>
                    <span class="text-sm font-medium text-blue-900 truncate">
                      {{ element.firstName }} {{ element.lastName }}
                    </span>
                  </div>
                </template>
              </draggable>
            </div>
            
            <!-- Group Footer -->
            <div class="p-2 bg-gray-50 border-t text-xs text-gray-500 text-center">
              {{ group.students.length }} students
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useQuasar } from 'quasar'
import draggable from 'vuedraggable'

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

const layoutName = ref('')
const groups = ref([])
const unassignedStudents = ref([])
const editingLayoutId = ref(null)

// Initialize
onMounted(() => {
  if (props.editingLayout) {
    loadLayoutData(props.editingLayout)
  } else {
    // New layout: all students unassigned
    unassignedStudents.value = [...props.students]
  }
})

const loadLayoutData = (layout) => {
  editingLayoutId.value = layout.id
  layoutName.value = layout.name
  
  // Reconstruct groups with full student objects
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
}

const loadLayout = (layout) => {
  loadLayoutData(layout)
}

const createNewLayout = () => {
  editingLayoutId.value = null
  layoutName.value = ''
  groups.value = []
  unassignedStudents.value = [...props.students]
}

const deleteLayout = (layout) => {
  $q.dialog({
    title: 'Delete Layout',
    message: `Are you sure you want to delete "${layout.name}"? This action cannot be undone.`,
    cancel: true,
    persistent: true,
    ok: {
      label: 'Delete',
      color: 'negative',
      flat: true
    }
  }).onOk(() => {
    emit('delete', layout.id)
    
    // If we're currently editing this layout, reset to new
    if (editingLayoutId.value === layout.id) {
      createNewLayout()
    }
  })
}

const addGroup = () => {
  groups.value.push({
    id: Date.now(), // Temporary ID
    name: `Group ${groups.value.length + 1}`,
    students: []
  })
}

const removeGroup = (index) => {
  // Move students back to unassigned
  const groupStudents = groups.value[index].students
  unassignedStudents.value.push(...groupStudents)
  groups.value.splice(index, 1)
}

const saveLayout = () => {
  // Validate
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
.group-header:hover .opacity-0 {
  opacity: 1;
}
</style>
