<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <h3 class="text-lg font-medium">Roles Management</h3>
      <button
        @click="$emit('create-role')"
        class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
      >
        Create Role
      </button>
    </div>

    <div class="bg-white shadow overflow-hidden sm:rounded-md">
      <ul class="divide-y divide-gray-200">
        <li v-for="role in roles" :key="role.id" class="px-4 py-4 sm:px-6">
          <div class="flex justify-between items-center">
            <div>
              <p class="text-sm font-medium text-indigo-600">{{ role.name }}</p>
              <p class="mt-1 text-xs text-gray-500">Guard: {{ role.guard_name }}</p>
              <div class="mt-2">
                <span 
                  v-for="permission in role.permissions" 
                  :key="permission.id"
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 mr-2 mb-1"
                >
                  {{ permission.name }}
                </span>
              </div>
            </div>
            <div class="flex space-x-2">
              <button
                @click="$emit('edit-role', role)"
                class="text-indigo-600 hover:text-indigo-900"
              >
                Edit
              </button>
              <button
                @click="$emit('delete-role', role)"
                class="text-red-600 hover:text-red-900"
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
  roles: {
    type: Array,
    required: true
  }
});

defineEmits(['create-role', 'edit-role', 'delete-role']);
</script>