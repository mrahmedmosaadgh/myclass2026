<template>
  <AppLayout title="Create Course">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Create New Course
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
          <form @submit.prevent="submit" class="p-6">
            <div class="mb-6">
              <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                Course Name *
              </label>
              <input
                id="name"
                v-model="form.name"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                :class="{ 'border-red-500': form.errors.name }"
                required
              />
              <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">
                {{ form.errors.name }}
              </div>
            </div>

            <div class="mb-6">
              <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                Description
              </label>
              <textarea
                id="description"
                v-model="form.description"
                rows="4"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                :class="{ 'border-red-500': form.errors.description }"
                placeholder="Enter course description..."
              ></textarea>
              <div v-if="form.errors.description" class="mt-1 text-sm text-red-600">
                {{ form.errors.description }}
              </div>
            </div>

            <div class="flex items-center justify-between">
              <Link
                :href="route('course-management.courses.index')"
                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition-colors duration-200"
              >
                Cancel
              </Link>
              <button
                type="submit"
                :disabled="form.processing"
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 transition-colors duration-200"
              >
                <span v-if="form.processing">Creating...</span>
                <span v-else>Create Course</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link } from '@inertiajs/vue3'

const form = useForm({
  name: '',
  description: ''
})

const submit = () => {
  form.post(route('course-management.courses.store'))
}
</script>