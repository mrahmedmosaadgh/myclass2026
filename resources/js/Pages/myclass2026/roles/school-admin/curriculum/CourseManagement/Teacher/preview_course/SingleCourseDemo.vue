<template>
  <q-page padding>
    <div class="row q-col-gutter-md">
      <div class="col-12">
        <h1 class="text-h4">Course Preview Demo</h1>
        <p class="text-body1">Select a course to preview its structure:</p>
      </div>
      
      <div class="col-12 col-md-4">
        <q-card>
          <q-card-section>
            <div class="text-h6">Available Courses</div>
          </q-card-section>
          
          <q-card-section>
            <q-list>
              <q-item
                v-for="course in courses"
                :key="course.id"
                clickable
                v-ripple
                @click="selectedCourse = course"
                :active="selectedCourse?.id === course.id"
                active-class="bg-primary text-white"
              >
                <q-item-section>
                  <q-item-label>{{ course.name }}</q-item-label>
                  <q-item-label caption>{{ course.levels?.length || 0 }} levels</q-item-label>
                </q-item-section>
              </q-item>
            </q-list>
          </q-card-section>
        </q-card>
      </div>
      
      <div class="col-12 col-md-8">
        <q-card v-if="selectedCourse">
          <q-card-section>
            <div class="text-h6">Course Structure: {{ selectedCourse.name }}</div>
          </q-card-section>
          
          <q-card-section style="height: 70vh; padding: 0;">
            <SingleCoursePreview
              :course-id="selectedCourse.id"
              :course-data="selectedCourse"
            />
          </q-card-section>
        </q-card>
        
        <q-card v-else>
          <q-card-section class="text-center">
            <q-icon name="school" size="48px" color="grey-5" />
            <p class="text-grey-7 q-mt-md">Select a course to preview its structure</p>
          </q-card-section>
        </q-card>
      </div>
    </div>
  </q-page>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import SingleCoursePreview from './SingleCoursePreview.vue'

const courses = ref([])
const selectedCourse = ref(null)
const loading = ref(false)

const loadCourses = async () => {
  loading.value = true
  try {
    const response = await fetch('/course-management/api/courses/with-structure')
    if (!response.ok) {
      throw new Error('Failed to load courses')
    }
    
    const data = await response.json()
    courses.value = data.data || data
  } catch (error) {
    console.error('Error loading courses:', error)
    
    // Fallback sample data
    courses.value = [
      {
        id: 1,
        name: "Typing Course",
        levels: [
          {
            id: 1,
            title: "Beginner",
            order: 1,
            sections: [
              {
                id: 1,
                title: "Getting Started",
                order: 1,
                lessons: [
                  { id: 1, title: "J, F, and Space", order: 1, text: "Learn the basics of typing" },
                  { id: 2, title: "U, R, and K Keys", order: 2, text: "Master the home row keys" },
                  { id: 3, title: "D, E, and I Keys", order: 3, text: "Expand your typing skills" }
                ]
              },
              {
                id: 2,
                title: "Basic Words",
                order: 2,
                lessons: [
                  { id: 4, title: "Common Words", order: 1, text: "Practice with everyday words" },
                  { id: 5, title: "Short Sentences", order: 2, text: "Build sentence typing skills" }
                ]
              }
            ]
          },
          {
            id: 2,
            title: "Intermediate",
            order: 2,
            sections: [
              {
                id: 3,
                title: "Advanced Keys",
                order: 1,
                lessons: [
                  { id: 6, title: "Q, W, and P Keys", order: 1, text: "Master the top row" },
                  { id: 7, title: "V, B, and M Keys", order: 2, text: "Bottom row mastery" }
                ]
              }
            ]
          }
        ]
      },
      {
        id: 2,
        name: "Programming Basics",
        levels: [
          {
            id: 3,
            title: "Introduction",
            order: 1,
            sections: [
              {
                id: 4,
                title: "Variables and Types",
                order: 1,
                lessons: [
                  { id: 8, title: "What are Variables?", order: 1, text: "Understanding data storage" },
                  { id: 9, title: "Data Types", order: 2, text: "Numbers, strings, and booleans" }
                ]
              }
            ]
          }
        ]
      }
    ]
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadCourses()
})
</script>

<style scoped>
.q-page {
  max-width: 1200px;
  margin: 0 auto;
}
</style>