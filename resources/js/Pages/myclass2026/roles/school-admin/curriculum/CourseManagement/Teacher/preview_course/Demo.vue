<template>
  <div class="demo-container">
    <div class="q-pa-md">
      <div class="text-center q-mb-xl">
        <h1 class="text-h4 q-mb-md">Course Management Interface Demo</h1>
        <p class="text-subtitle1 text-grey-7">Choose an interface style to preview</p>
      </div>

      <!-- Interface Selection -->
      <div class="row q-col-gutter-md q-mb-xl">
        <div class="col-12 col-md-6">
          <q-card 
            class="cursor-pointer"
            :class="{ 'selected-card': selectedInterface === 'simple' }"
            @click="selectedInterface = 'simple'"
            v-ripple
          >
            <q-card-section>
              <div class="text-h6">Simple Interface</div>
              <div class="text-subtitle2 text-grey-7">Basic two-panel layout with tree view</div>
            </q-card-section>
            <q-card-section>
              <q-icon name="view_sidebar" size="48px" color="primary" />
            </q-card-section>
          </q-card>
        </div>
        
        <div class="col-12 col-md-6">
          <q-card 
            class="cursor-pointer"
            :class="{ 'selected-card': selectedInterface === 'advanced' }"
            @click="selectedInterface = 'advanced'"
            v-ripple
          >
            <q-card-section>
              <div class="text-h6">Advanced Interface</div>
              <div class="text-subtitle2 text-grey-7">Card-based selection with search</div>
            </q-card-section>
            <q-card-section>
              <q-icon name="dashboard" size="48px" color="secondary" />
            </q-card-section>
          </q-card>
        </div>
      </div>

      <!-- Interface Preview -->
      <div v-if="selectedInterface">
        <div class="row items-center q-mb-md">
          <q-btn 
            flat 
            round 
            icon="arrow_back" 
            @click="selectedInterface = null"
            class="q-mr-md"
          />
          <div>
            <div class="text-h5">
              {{ selectedInterface === 'simple' ? 'Simple Interface' : 'Advanced Interface' }}
            </div>
            <div class="text-subtitle1 text-grey-7">
              {{ selectedInterface === 'simple' 
                ? 'Basic course structure with tree navigation' 
                : 'Card-based course selection with advanced features' }}
            </div>
          </div>
        </div>

        <div class="interface-preview">
          <CourseManagementInterface v-if="selectedInterface === 'simple'" />
          <CourseCardInterface v-else :courses="sampleCourses" />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import CourseManagementInterface from './CourseManagementInterface.vue'
import CourseCardInterface from './CourseCardInterface.vue'

const selectedInterface = ref(null)

const sampleCourses = [
  {
    id: 1,
    name: "Typing Course",
    description: "Learn touch typing from beginner to advanced levels",
    levels: [
      {
        id: 1,
        name: "Beginner",
        order: 1,
        sections: [
          {
            id: 1,
            name: "Getting Started",
            lessons: [
              { name: "J, F, and Space", text: "Learn the home row keys" },
              { name: "U, R, and K Keys", text: "Practice with top row keys" },
              { name: "D, E, and I Keys", text: "Master the bottom row" },
              { name: "C, G, and N Keys", text: "Complete the alphabet" }
            ]
          },
          {
            id: 2,
            name: "Basic Words",
            lessons: [
              { name: "Common Words", text: "Practice with frequently used words" },
              { name: "Short Sentences", text: "Type simple phrases" },
              { name: "Practice Drills", text: "Repetition exercises" }
            ]
          }
        ]
      },
      {
        id: 2,
        name: "Intermediate",
        order: 2,
        sections: [
          {
            id: 3,
            name: "Advanced Keys",
            lessons: [
              { name: "Q, W, and P Keys", text: "Master the remaining letters" },
              { name: "V, B, and M Keys", text: "Bottom row mastery" },
              { name: "Special Characters", text: "Numbers and symbols" }
            ]
          },
          {
            id: 4,
            name: "Speed Building",
            lessons: [
              { name: "Timed Exercises", text: "Improve your speed" },
              { name: "Accuracy Drills", text: "Focus on precision" },
              { name: "Speed Tests", text: "Measure your progress" }
            ]
          }
        ]
      }
    ]
  },
  {
    id: 2,
    name: "Programming Basics",
    description: "Introduction to programming concepts and syntax",
    levels: [
      {
        id: 3,
        name: "Fundamentals",
        order: 1,
        sections: [
          {
            id: 5,
            name: "Variables and Types",
            lessons: [
              { name: "Introduction to Variables", text: "Understanding data storage" },
              { name: "Data Types", text: "Numbers, strings, and booleans" },
              { name: "Type Conversion", text: "Changing between types" }
            ]
          },
          {
            id: 6,
            name: "Control Flow",
            lessons: [
              { name: "If Statements", text: "Making decisions in code" },
              { name: "Loops", text: "Repeating actions" },
              { name: "Functions", text: "Reusable code blocks" }
            ]
          }
        ]
      }
    ]
  }
]
</script>

<style scoped>
.demo-container {
  width: 100%;
  min-height: 100vh;
}

.selected-card {
  border: 2px solid var(--q-primary);
}

.interface-preview {
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  overflow: hidden;
}

.course-card {
  transition: all 0.3s ease;
}

.course-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}
</style>