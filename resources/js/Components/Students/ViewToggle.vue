`<template>
  <div class="flex items-center space-x-2 bg-gray-100 p-1 rounded-lg">
    <button
      @click="$emit('update:view', 'card')"
      :class="[
        'px-4 py-2 text-sm font-medium rounded-md transition-colors duration-200',
        view === 'card'
          ? 'bg-white text-gray-900 shadow'
          : 'text-gray-500 hover:text-gray-900'
      ]"
    >
      <div class="flex items-center space-x-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
        </svg>
        <span>Cards</span>
      </div>
    </button>
    <button
      @click="$emit('update:view', 'table')"
      :class="[
        'px-4 py-2 text-sm font-medium rounded-md transition-colors duration-200',
        view === 'table'
          ? 'bg-white text-gray-900 shadow'
          : 'text-gray-500 hover:text-gray-900'
      ]"
    >
      <div class="flex items-center space-x-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
        </svg>
        <span>Table</span>
      </div>
    </button>
  </div>
</template>

<script>
export default {
  name: 'ViewToggle',
  props: {
    view: {
      type: String,
      required: true,
      validator: value => ['card', 'table'].includes(value)
    }
  },
  emits: ['update:view']
}
</script>`