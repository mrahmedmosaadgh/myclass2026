<template>
  <q-layout view="hHh lpR fFf">
  <Head title="teacher|dashboard"  />
    <q-page-container>
      <div class="q-pa-md">
        <div class="row items-center q-mb-md">
          <div class="col">
            <h1 class="text-h4 q-mb-none">Teacher Dashboard</h1>
            <p class="text-subtitle1 text-grey-7">Manage your assigned courses</p>
          </div>
          <div class="col-auto">
            <q-btn
              color="primary"
              icon="refresh"
              label="Refresh"
              @click="loadTeacherCourses"
              :loading="loading"
            />
          </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="text-center q-pa-xl">
          <q-spinner-dots size="50px" color="primary" />
          <p class="q-mt-md">Loading your courses...</p>
        </div>

        <!-- Error State -->
        <q-banner v-else-if="error" class="bg-negative text-white q-mb-md">
          <template #avatar>
            <q-icon name="error" />
          </template>
          {{ error }}
          <template #action>
            <q-btn flat color="white" label="Retry" @click="loadTeacherCourses" />
          </template>
        </q-banner>

        <!-- Empty State -->
        <q-card v-else-if="!courses.length" class="text-center q-pa-xl">
          <q-icon name="school" size="64px" color="grey-5" />
          <p class="text-h6 q-mt-md">No courses assigned</p>
          <p class="text-grey-7">Contact your administrator to get assigned to courses</p>
        </q-card>

        <!-- Courses List -->
        <div v-else class="row q-col-gutter-md">
          <div
            v-for="course in courses"
            :key="course.id"
            class="col-12 col-md-6 col-lg-4"
          >
            <q-card class="course-card">
              <q-card-section>
                <div class="text-h6">{{ course.name }}</div>
                <div class="text-subtitle2 text-grey-7">{{ course.description }}</div>
                
                <div class="q-mt-md">
                  <div class="text-caption">Structure:</div>
                  <div class="row q-gutter-sm">
                    <q-chip
                      v-for="level in course.levels.slice(0, 3)"
                      :key="level.id"
                      size="sm"
                      color="primary"
                      text-color="white"
                    >
                      {{ level.title }}
                    </q-chip>
                    <q-chip
                      v-if="course.levels.length > 3"
                      size="sm"
                      color="grey"
                      text-color="white"
                    >
                      +{{ course.levels.length - 3 }} more
                    </q-chip>
                  </div>
                </div>
              </q-card-section>

              <q-separator />

              <q-card-actions align="right">
                <q-btn
                  flat
                  color="primary"
                  icon="preview"
                  label="Preview Course"
                  @click="previewCourse(course)"
                />
                <q-btn
                  flat
                  color="secondary"
                  icon="open_in_new"
                  label="Open Interface"
                  @click="openCourseInterface(course)"
                />
              </q-card-actions>
            </q-card>
          </div>
        </div>

        <!-- Course Interface Dialog -->
        <q-dialog v-model="showInterface" maximized>
          <q-card>
            <q-card-section class="row items-center q-pb-none">
              <div class="text-h6">{{ selectedCourse?.name }}</div>
              <q-space />
              <q-btn icon="close" flat round dense v-close-popup />
            </q-card-section>

            <q-card-section style="height: calc(100vh - 60px); padding: 0;">
              <SingleCoursePreview
                v-if="selectedCourse"
                :course-id="selectedCourse.id"
                :course-data="selectedCourse"
              />
            </q-card-section>
          </q-card>
        </q-dialog>
      </div>
    </q-page-container>
  </q-layout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useQuasar } from 'quasar'
import SingleCoursePreview from './preview_course/SingleCoursePreview.vue'

const $q = useQuasar()

const courses = ref([])
const loading = ref(false)
const error = ref(null)
const showInterface = ref(false)
const selectedCourse = ref(null)

const loadTeacherCourses = async () => {
  loading.value = true
  error.value = null
  
  try {
    const response = await fetch('/course-management/api/teacher/courses')
    const data = await response.json()
    
    if (data.success) {
      courses.value = data.data
    } else {
      throw new Error(data.message || 'Failed to load courses')
    }
  } catch (err) {
    error.value = err.message || 'Failed to load your courses'
    $q.notify({
      type: 'negative',
      message: error.value,
      position: 'top'
    })
  } finally {
    loading.value = false
  }
}

const previewCourse = (course) => {
  selectedCourse.value = course
  showInterface.value = true
}

const openCourseInterface = (course) => {
  // Navigate to the full interface
  window.open(`/course-management/teachers/preview-course?course_id=${course.id}`, '_blank')
}

onMounted(() => {
  loadTeacherCourses()
})
</script>

<style scoped>
.course-card {
  transition: transform 0.2s;
}

.course-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}
</style>