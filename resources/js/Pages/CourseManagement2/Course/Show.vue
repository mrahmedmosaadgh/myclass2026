<template>
  <AppLayout :title="course.name">
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          {{ course.name }}
        </h2>
        <div class="flex space-x-2">
          <Link
            :href="route('course-management.courses.levels.create', course.id)"
            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors duration-200"
          >
            Add Level
          </Link>
          <Link
            :href="route('course-management.courses.edit', course.id)"
            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors duration-200"
          >
            Edit Course
          </Link>
        </div>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
          <div class="p-6">
            <!-- Course Info -->
            <div class="mb-8">
              <h3 class="text-lg font-medium text-gray-900 mb-2">Course Information</h3>
              <p class="text-gray-600 mb-4">{{ course.description || 'No description provided' }}</p>
              <div class="text-sm text-gray-500">
                Created by {{ course.creator?.name }} on {{ formatDate(course.created_at) }}
              </div>
            </div>

            <!-- Course Structure -->
            <div>
              <h3 class="text-lg font-medium text-gray-900 mb-4">Course Structure</h3>
              
              <div v-if="course.levels?.length === 0" class="text-center py-8 bg-gray-50 rounded-lg">
                <p class="text-gray-500 mb-4">No levels created yet.</p>
                <Link
                  :href="route('course-management.courses.levels.create', course.id)"
                  class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors duration-200"
                >
                  Create First Level
                </Link>
              </div>

              <div v-else class="space-y-4">
                <div
                  v-for="level in course.levels"
                  :key="level.id"
                  class="border border-gray-200 rounded-lg"
                >
                  <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                      <h4 class="font-medium text-gray-900">
                        Level {{ level.order }}: {{ level.title }}
                      </h4>
                      <div class="flex space-x-2">
                        <Link
                          :href="route('course-management.levels.sections.create', level.id)"
                          class="text-sm px-3 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200 transition-colors duration-200"
                        >
                          Add Section
                        </Link>
                        <Link
                          :href="route('course-management.levels.show', level.id)"
                          class="text-sm px-3 py-1 bg-gray-100 text-gray-700 rounded hover:bg-gray-200 transition-colors duration-200"
                        >
                          View
                        </Link>
                      </div>
                    </div>
                  </div>

                  <div class="p-4">
                    <div v-if="level.sections?.length === 0" class="text-gray-500 text-sm">
                      No sections created yet.
                    </div>
                    <div v-else class="space-y-3">
                      <div
                        v-for="section in level.sections"
                        :key="section.id"
                        class="bg-white border border-gray-100 rounded p-3"
                      >
                        <div class="flex justify-between items-center mb-2">
                          <h5 class="font-medium text-gray-800">
                            Section {{ section.order }}: {{ section.title }}
                          </h5>
                          <div class="flex space-x-2">
                            <Link
                              :href="route('course-management.sections.lessons.create', section.id)"
                              class="text-xs px-2 py-1 bg-green-100 text-green-700 rounded hover:bg-green-200 transition-colors duration-200"
                            >
                              Add Lesson
                            </Link>
                            <Link
                              :href="route('course-management.sections.show', section.id)"
                              class="text-xs px-2 py-1 bg-gray-100 text-gray-700 rounded hover:bg-gray-200 transition-colors duration-200"
                            >
                              View
                            </Link>
                          </div>
                        </div>
                        
                        <div v-if="section.lessons?.length === 0" class="text-gray-400 text-xs">
                          No lessons created yet.
                        </div>
                        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                          <Link
                            v-for="lesson in section.lessons"
                            :key="lesson.id"
                            :href="route('course-management.lessons.show', lesson.id)"
                            class="text-xs p-2 bg-blue-50 text-blue-700 rounded hover:bg-blue-100 transition-colors duration-200 block"
                          >
                            {{ lesson.order }}. {{ lesson.title }}
                          </Link>
                        </div>
                      </div>
                    </div>
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
  course: Object
})

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString()
}
</script>