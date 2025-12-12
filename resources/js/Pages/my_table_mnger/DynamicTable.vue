
<template>
  <div class="w-full overflow-x-auto border rounded-lg shadow-sm bg-white">
    <!-- Optional Table Title -->
    <h2
      v-if="config.title"
      class="text-lg font-semibold mb-2 px-3 pt-3 text-gray-700"
    >
      {{ t(config.title) }}
    </h2>
    <details>
        {{ data }}
        <br>
        <br>
        <br>
        <br>
        {{ config }}
    </details>

    <table class="  border border-gray-300">
      <thead class="bg-gray-100">
        <tr>
          <th
            v-for="(column, index) in config.columns"
            :key="index"
            class="border px-3 py-2 text-left font-semibold text-gray-700"
          >
            {{ t(column.title) }}
          </th>

          <th
            v-if="config.actions && config.actions.length"
            class="border px-3 py-2 text-center font-semibold text-gray-700"
          >
            {{ t('common.actions') }}
          </th>
        </tr>
      </thead>

      <tbody>
        <tr
          v-for="(item, rowIndex) in data"
          :key="rowIndex"
          class="hover:bg-gray-50"
        >
          <!-- Dynamic Columns -->
          <td
            v-for="(column, colIndex) in config.columns"
            :key="colIndex"
            class="border px-3 py-2"
          >
            {{ resolveValue(item, column.key) }}
          </td>

          <!-- Actions -->
          <td
            v-if="config.actions && config.actions.length"
            class="border px-3 py-2 text-center"
          >
            <button
              v-for="(action, aIndex) in config.actions"
              :key="aIndex"
              :class="action.class"
              class="mx-1 px-3 py-1 rounded text-white"
              @click="$emit(action.event, item)"
            >
              {{ t(action.label) }}
            </button>
          </td>
        </tr>

        <!-- Empty State -->
        <tr v-if="!data.length">
          <td
            :colspan="(config.columns?.length || 0) + (config.actions?.length ? 1 : 0)"
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
import { useI18n } from 'vue-i18n'

const { notsafe_t } = useI18n()

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
  data: {
    type: Array,
    required: true,
  },
  config: {
    type: Object,
    required: true,
    default: () => ({
      title: '',
      columns: [],
      actions: [],
    }),
  },
})










// Helper for nested properties
const resolveValue = (obj, path) => {
  return path.split('.').reduce((acc, part) => acc && acc[part], obj) ?? ''
}
</script>

<style scoped>
table {
  border-collapse: collapse;
}
</style>
