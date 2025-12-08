<template>
  <AppLayout title="Course Management">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Course Management
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
          <div class="p-6">
            <div class="flex justify-between items-center mb-6">
              <h3 class="text-lg font-medium text-gray-900">Courses</h3>
              <Link
                :href="route('course-management.courses.create')"
                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
              >
                Create Course
              </Link>
            </div>

            <div v-if="courses.length === 0" class="text-center py-8">
              <p class="text-gray-500">No courses found. Create your first course to get started.</p>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              <div
                v-for="course in courses"
                :key="course.id"
                class="bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200"
              >
                <div class="p-6">
                  <h4 class="text-lg font-semibold text-gray-900 mb-2">
                    {{ course.name }}
                  </h4>
                  <p class="text-gray-600 text-sm mb-4">
                    {{ course.description || 'No description provided' }}
                  </p>
                  <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
                    <span>{{ course.levels?.length || 0 }} levels</span>
                    <span>Created by {{ course.creator?.name }}</span>
                  </div>
                  <div class="flex space-x-2">
                    <Link
                      :href="route('course-management.courses.show', course.id)"
                      class="flex-1 text-center px-3 py-2 bg-blue-100 text-blue-700 rounded-md hover:bg-blue-200 transition-colors duration-200"
                    >
                      View
                    </Link>
                    <Link
                      :href="route('course-management.courses.edit', course.id)"
                      class="flex-1 text-center px-3 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition-colors duration-200"
                    >
                      Edit
                    </Link>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { defineProps } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link } from '@inertiajs/vue3'

defineProps({
  courses: Array
})
</script>