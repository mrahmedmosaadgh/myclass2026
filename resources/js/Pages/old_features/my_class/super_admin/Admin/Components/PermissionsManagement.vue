<template>
  <div>
    <div class="flex justify-between items-center mb-5">
      <h3 class="text-lg font-medium">Manage Permissions</h3>
      <button
        @click="$emit('create-permission')"
        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring focus:ring-indigo-300 disabled:opacity-25 transition"
      >
        Create Permission
      </button>
    </div>

    <div class="bg-white shadow overflow-hidden sm:rounded-md">
      <ul class="divide-y divide-gray-200">
        <li v-for="permission in permissions" :key="permission.id">
          <div class="px-4 py-4 sm:px-6 flex justify-between items-center">
            <div>
              <p class="text-sm font-medium text-indigo-600 truncate">
                {{ permission.name }}
              </p>
            </div>
            <div class="flex space-x-2">
              <button
                @click="$emit('edit-permission', permission)"
                class="text-indigo-600 hover:text-indigo-900 text-sm"
              >
                Edit
              </button>
              <button
                @click="$emit('delete-permission', permission)"
                class="text-red-600 hover:text-red-900 text-sm"
              >
                Delete
              </button>
            </div>
          </div>
        </li>
      </ul>
    </div>
  </div>
</template>

<script setup>
defineProps({
  permissions: {
    type: Array,
    required: true
  }
});

defineEmits(['create-permission', 'edit-permission', 'delete-permission']);
</script>