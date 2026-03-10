<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <h3 class="text-lg font-medium">User Management</h3>
      <button
        @click="$emit('edit-user')"
        class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
      >
        Create User
      </button>
    </div>

    <div class="bg-white shadow overflow-hidden sm:rounded-md">
      <ul class="divide-y divide-gray-200">
        <li v-for="user in users" :key="user.id">
          <div class="px-4 py-4 sm:px-6 flex justify-between items-center">
            <div>
              <p class="text-sm font-medium text-indigo-600 truncate">
                {{ user.name }}
              </p>
              <p class="text-sm text-gray-500">
                {{ user.email }}
              </p>
            </div>
            <div class="flex space-x-2">
              <button
                @click="$emit('edit-user', user)"
                class="text-indigo-600 hover:text-indigo-900 text-sm"
              >
                Edit
              </button>
              <button
                @click="resetPassword(user)"
                class="text-orange-600 hover:text-orange-900 text-sm"
              >
                Reset Password
              </button>
              <button
                v-if="!user.deleted_at"
                @click="$emit('delete-user', user)"
                class="text-red-600 hover:text-red-900 text-sm"
              >
                Delete
              </button>
              <button
                v-else
                @click="$emit('restore-user', user)"
                class="text-green-600 hover:text-green-900 text-sm"
              >
                Restore
              </button>
            </div>
          </div>
        </li>
      </ul>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
  users: {
    type: Array,
    required: true
  }
});

defineEmits(['edit-user', 'delete-user', 'restore-user']);

const resetPassword = (user) => {
  if (confirm(`Are you sure you want to reset the password for ${user.name} to 12345678?`)) {
    useForm({}).put(route('admin.users.reset-password', user.id), {
      preserveScroll: true,
      onSuccess: () => {
        // Optional: Show success message
      }
    });
  }
};
</script>



