`<template>
  <div class="overflow-x-auto">
    <div class="mb-4 flex items-center justify-between">
      <div class="flex items-center space-x-4">
        <button
          @click="toggleColumnSelector"
          class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
        >
          Customize Columns
        </button>
      </div>
    </div>

    <!-- Column Selector Modal -->
    <div v-if="showColumnSelector" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="toggleColumnSelector"></div>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
          <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Customize Columns</h3>
            <div class="space-y-2">
              <div v-for="column in availableColumns" :key="column.key" class="flex items-center">
                <input
                  type="checkbox"
                  :id="column.key"
                  v-model="column.visible"
                  class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                >
                <label :for="column.key" class="ml-2 text-sm text-gray-700">{{ column.label }}</label>
              </div>
            </div>
          </div>
          <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <button
              @click="toggleColumnSelector"
              class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
            >
              Close
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Table -->
    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gray-50">
        <tr>
          <th
            v-for="column in visibleColumns"
            :key="column.key"
            scope="col"
            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer"
            @click="sortBy(column.key)"
          >
            <div class="flex items-center space-x-1">
              <span>{{ column.label }}</span>
              <span v-if="sortColumn === column.key" class="ml-2">
                {{ sortDirection === 'asc' ? '↑' : '↓' }}
              </span>
            </div>
          </th>
          <th scope="col" class="relative px-6 py-3">
            <span class="sr-only">Actions</span>
          </th>
        </tr>
      </thead>
      <tbody class="bg-white divide-y divide-gray-200">
        <tr 
          v-for="student in sortedStudents" 
          :key="student.id"
          :class="{ 'opacity-60 bg-gray-50': student.attend !== 1 }"
        >
          <td
            v-for="column in visibleColumns"
            :key="column.key"
            class="px-6 py-4 whitespace-nowrap"
          >
            <component
              :is="column.component || 'span'"
              v-bind="column.props ? column.props(student) : {}"
            >
              {{ getValue(student, column.key) }}
            </component>
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
            <template v-if="student.attend === 1">
              <slot name="actions" :student="student"></slot>
            </template>
            <span v-else class="text-red-500">Not Attending</span>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
export default {
  name: 'StudentTable',
  props: {
    students: {
      type: Array,
      required: true
    }
  },
  data() {
    return {
      showColumnSelector: false,
      sortColumn: 'name',
      sortDirection: 'asc',
      availableColumns: [
        { key: 'id', label: 'ID', visible: true },
        { key: 'name', label: 'Name', visible: true },
        { key: 'class', label: 'Class', visible: true },
        { key: 'grade', label: 'Grade', visible: true },
        { key: 'status', label: 'Status', visible: true },
        { key: 'email', label: 'Email', visible: true },
        { key: 'phone', label: 'Phone', visible: false },
        { key: 'address', label: 'Address', visible: false }
      ]
    }
  },
  computed: {
    visibleColumns() {
      return this.availableColumns.filter(column => column.visible)
    },
    sortedStudents() {
      return [...this.students].sort((a, b) => {
        const aVal = this.getValue(a, this.sortColumn)
        const bVal = this.getValue(b, this.sortColumn)
        
        if (this.sortDirection === 'asc') {
          return aVal > bVal ? 1 : -1
        }
        return aVal < bVal ? 1 : -1
      })
    }
  },
  methods: {
    toggleColumnSelector() {
      this.showColumnSelector = !this.showColumnSelector
    },
    sortBy(column) {
      if (this.sortColumn === column) {
        this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc'
      } else {
        this.sortColumn = column
        this.sortDirection = 'asc'
      }
    },
    getValue(obj, key) {
      return key.split('.').reduce((o, i) => o?.[i], obj)
    }
  },
  created() {
    // Load column preferences from localStorage if available
    const savedColumns = localStorage.getItem('studentTableColumns')
    if (savedColumns) {
      const parsed = JSON.parse(savedColumns)
      this.availableColumns = this.availableColumns.map(col => ({
        ...col,
        visible: parsed[col.key] ?? col.visible
      }))
    }
  },
  watch: {
    availableColumns: {
      handler(newColumns) {
        // Save column preferences to localStorage
        const preferences = {}
        newColumns.forEach(col => {
          preferences[col.key] = col.visible
        })
        localStorage.setItem('studentTableColumns', JSON.stringify(preferences))
      },
      deep: true
    }
  }
}
</script>`