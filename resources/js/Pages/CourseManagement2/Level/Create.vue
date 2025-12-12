<template>
  <AppLayout title="Create Level">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Create New Level for: {{ course.name }}
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
          <form @submit.prevent="submit" class="p-6">
            <div class="mb-6">
              <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                Level Title *
              </label>
              <input
                id="title"
                v-model="form.title"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                :class="{ 'border-red-500': form.errors.title }"
                placeholder="e.g., Fractions, Algebra Basics, etc."
                required
              />
              <div v-if="form.errors.title" class="mt-1 text-sm text-red-600">
                {{ form.errors.title }}
              </div>
            </div>

            <div class="mb-6">
              <label for="order" class="block text-sm font-medium text-gray-700 mb-2">
                Order (Optional)
              </label>
              <input
                id="order"
                v-model="form.order"
                type="number"
                min="0"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                :class="{ 'border-red-500': form.errors.order }"
                placeholder="Leave empty to add at the end"
              />
              <div v-if="form.errors.order" class="mt-1 text-sm text-red-600">
                {{ form.errors.order }}
              </div>
              <p class="mt-1 text-sm text-gray-500">
                If not specified, this level will be added at the end.
              </p>
            </div>

            <div class="flex items-center justify-between">
              <Link
                :href="route('course-management.courses.show', course.id)"
                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition-colors duration-200"
              >
                Cancel
              </Link>
              <button
                type="submit"
                :disabled="form.processing"
                class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 disabled:opacity-50 transition-colors duration-200"
              >
                <span v-if="form.processing">Creating...</span>
                <span v-else>Create Level</span>
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

const props = defineProps({
  course: Object
})

const form = useForm({
  title: '',
  order: ''
})

const submit = () => {
  form.post(route('course-management.courses.levels.store', props.course.id))
}
</script>