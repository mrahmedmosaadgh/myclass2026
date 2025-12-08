<template>
  <AppLayout title="Edit Course">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Edit Course: {{ course.name }}
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
              <div class="flex space-x-2">
                <Link
                  :href="route('course-management.courses.show', course.id)"
                  class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition-colors duration-200"
                >
                  Cancel
                </Link>
                <button
                  type="button"
                  @click="deleteCourse"
                  class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors duration-200"
                >
                  Delete Course
                </button>
              </div>
              <button
                type="submit"
                :disabled="form.processing"
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 transition-colors duration-200"
              >
                <span v-if="form.processing">Updating...</span>
                <span v-else>Update Course</span>
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
import { Link, router } from '@inertiajs/vue3'

const props = defineProps({
  course: Object
})

const form = useForm({
  name: props.course.name,
  description: props.course.description || ''
})

const submit = () => {
  form.put(route('course-management.courses.update', props.course.id))
}

const deleteCourse = () => {
  if (confirm('Are you sure you want to delete this course? This action cannot be undone.')) {
    router.delete(route('course-management.courses.destroy', props.course.id))
  }
}
</script>