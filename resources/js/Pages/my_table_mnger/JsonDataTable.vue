<template>
  <div class="p-4">
    <h2 class="text-xl font-bold mb-3">
      {{ t('user.management_dashboard') }}
    </h2>

    <div class="mb-3 flex justify-between items-center">
      <input
        v-model="search"
        type="text"
        :placeholder="t('common.searchPlaceholder')"
        class="border rounded px-3 py-1 w-1/3"
      />
      <slot name="toolbar"></slot>
    </div>
<!-- min-w-full -->
    <table class=" border border-gray-300">
      <thead class="bg-gray-100">
        <tr>
          <th
            v-for="col in visibleColumns"
            :key="col.key"
            class="border px-3 py-2 text-left"
          >
            {{ getTitle(col.title) }}
          </th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="(row, rowIndex) in filteredData"
          :key="rowIndex"
          class="hover:bg-gray-50"
        >
          <td
            v-for="col in visibleColumns"
            :key="col.key"
            class="border px-3 py-2"
          >
            <component
              :is="getCellComponent(col)"
              :value="row[col.key]"
              :config="col"
              @click="handleButtonClick(col, row)"
            >
              <template v-if="col.type === 'button'">
                <span v-if="col.button?.icon" class="mr-1">
                  <i :class="'fa fa-' + col.button.icon"></i>
                </span>
                {{ getTitle(col.button?.label) }}
              </template>

              <template v-else-if="col.type === 'list'">
                {{ formatList(row[col.key]) }}
              </template>

              <template v-else>
                {{ row[col.key] }}
              </template>
            </component>
          </td>
        </tr>

        <tr v-if="!filteredData.length">
          <td
            :colspan="visibleColumns.length"
            class="text-center py-3 text-gray-500"
          >
            {{ t('common.noResults') }}
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useI18n } from 'vue-i18n'

// const { t, locale } = useI18n()
const { notsafe_t, locale } = useI18n()

// ✅ Safe translation function
const t = (key) => {
  if (!key || typeof key !== 'string') return key || ''
  try {
    return notsafe_t(key)
  } catch {
    return key
  }
}
const props = defineProps({
  data: { type: Array, default: () => [] },
  config: { type: Object, default: () => ({ columns: [] }) }
})

const emit = defineEmits(['action', 'edit', 'delete'])

const search = ref('')

// Filter visible columns
const visibleColumns = computed(() =>
  Array.isArray(props.config.columns)
    ? props.config.columns.filter((col) => col.show !== false)
    : []
)

// Filter data by keyword
const filteredData = computed(() => {
  const items = Array.isArray(props.data) ? props.data : []
  if (!search.value) return items
  const keyword = search.value.toLowerCase()
  return items.filter((item) =>
    Object.values(item).some((val) =>
      String(val).toLowerCase().includes(keyword)
    )
  )
})

// Handle button click in table
function handleButtonClick(col, row) {
  if (col.type === 'button' && col.button?.emit) {
    emit(col.button.emit, row)
  }
}

// Determine what type of cell to render
function getCellComponent(col) {
  if (col.type === 'button') return 'button'
  return 'span'
}

// Get translated title based on current locale
function getTitle(titleObj) {
  if (!titleObj) return ''
  if (typeof titleObj === 'string') return titleObj
  return titleObj[locale.value] || titleObj.en || ''
}

// Format list-type cells
function formatList(value) {
  if (Array.isArray(value)) {
    if (value.every((v) => typeof v === 'object' && v.name))
      return value.map((v) => v.name).join(', ')
    return value.join(', ')
  }
  return value
}
</script>
