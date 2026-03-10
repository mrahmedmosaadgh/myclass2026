<template>
  <div class="course-preview">
    <q-splitter
      v-model="splitterModel"
      style="height: 100vh"
      :limits="[20, 50]"
    >
      <template #before>
        <q-scroll-area class="fit">
          <div class="q-pa-md">
            <div class="row items-center q-mb-md">
              <h3 class="text-h5 col">Course Structure</h3>
              <q-btn 
                flat 
                round 
                icon="search" 
                @click="toggleSearch"
                :color="showSearch ? 'primary' : 'grey'"
              />
            </div>
            
            <!-- Search Input -->
            <q-input
              v-if="showSearch"
              v-model="searchQuery"
              placeholder="Search levels, sections, and lessons..."
              dense
              outlined
              class="q-mb-md"
              clearable
            >
              <template #prepend>
                <q-icon name="search" />
              </template>
            </q-input>
            
            <!-- Loading State -->
            <div v-if="loading" class="text-center q-pa-md">
              <q-spinner color="primary" size="3em" />
              <div class="q-mt-sm">Loading courses...</div>
            </div>
            
            <!-- Error State -->
            <div v-else-if="error" class="text-center q-pa-md">
              <q-icon name="error" size="3em" color="negative" />
              <div class="text-negative q-mt-sm">{{ error }}</div>
              <q-btn 
                color="primary" 
                label="Retry" 
                @click="loadCourses" 
                class="q-mt-md"
              />
            </div>
            
            <!-- Course Tree -->
            <q-tree
              v-else
              :nodes="filteredTree"
              node-key="id"
              label-key="name"
              children-key="children"
              v-model:selected="selectedNode"
              @update:selected="handleNodeSelection"
              default-expand-all
            >
              <template #default-header="prop">
                <div class="row items-center">
                  <q-icon
                    :name="getNodeIcon(prop.node)"
                    size="20px"
                    class="q-mr-sm"
                    :color="getNodeColor(prop.node)"
                  />
                  <span>{{ prop.node.displayName || prop.node.name }}</span>
                </div>
              </template>
            </q-tree>
          </div>
        </q-scroll-area>
      </template>

      <template #after>
        <q-scroll-area class="fit">
          <div class="q-pa-md">
            <div v-if="selectedSection">
              <div class="row items-center q-mb-md">
                <h2 class="text-h4 col">{{ selectedSection.name }}</h2>
                <q-badge 
                  v-if="searchQuery" 
                  color="primary" 
                  class="q-ml-sm"
                >
                  {{ filteredLessons.length }} of {{ selectedSection.originalLessons?.length || 0 }} lessons
                </q-badge>
              </div>
              
              <q-list bordered separator>
                <q-item
                  v-for="(lesson, index) in filteredLessons"
                  :key="lesson.id"
                  clickable
                  v-ripple
                  class="q-py-md"
                >
                  <q-item-section avatar>
                    <q-avatar color="primary" text-color="white">
                      {{ index + 1 }}
                    </q-avatar>
                  </q-item-section>
                  
                  <q-item-section>
                    <q-item-label class="text-subtitle1">
                      {{ lesson.title }}
                    </q-item-label>
                    <q-item-label v-if="lesson.text" caption>
                      {{ lesson.text }}
                    </q-item-label>
                  </q-item-section>
                  
                  <q-item-section side>
                    <q-btn
                      flat
                      round
                      icon="play_arrow"
                      color="primary"
                      @click="startLesson(lesson)"
                    />
                  </q-item-section>
                </q-item>
              </q-list>
              
              <div v-if="!filteredLessons || filteredLessons.length === 0" class="text-center q-mt-xl">
                <q-icon name="info" size="48px" color="grey-5" />
                <p class="text-grey-7 q-mt-md">
                  <span v-if="searchQuery">No lessons match "{{ searchQuery }}"</span>
                  <span v-else>No lessons available in this section</span>
                </p>
              </div>
            </div>
            
            <div v-else-if="!loading" class="text-center q-mt-xl">
              <q-icon name="touch_app" size="48px" color="grey-5" />
              <p class="text-grey-7 q-mt-md">Select a section to view lessons</p>33333
            </div>
          </div>
        </q-scroll-area>
      </template>
    </q-splitter>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useQuasar } from 'quasar'

const $q = useQuasar()

// Reactive data
const splitterModel = ref(25)
const selectedNode = ref(null)
const searchQuery = ref('')
const showSearch = ref(false)
const courses = ref([])
const loading = ref(false)
const error = ref(null)

// Computed properties
const courseTree = computed(() => {
  return courses.value.map(course => ({
    id: `course-${course.id}`,
    name: course.name,
    type: 'course',
    children: course.levels?.map(level => ({
      id: `level-${level.id}`,
      name: level.title,
      displayName: `${level.order || 1}. ${level.title}`,
      type: 'level',
      courseId: course.id,
      levelId: level.id,
      children: level.sections?.map(section => ({
        id: `section-${section.id}`,
        name: section.title,
        displayName: `${level.order || 1}.${section.order || 1}. ${section.title}`,
        type: 'section',
        courseId: course.id,
        levelId: level.id,
        sectionId: section.id,
        lessons: section.lessons || []
      })) || []
    })) || []
  }))
})

const filteredTree = computed(() => {
  if (!searchQuery.value) {
    return courseTree.value
  }

  const query = searchQuery.value.toLowerCase()
  
  return courseTree.value.map(course => {
    // Search in course name
    if (course.name.toLowerCase().includes(query)) {
      return course
    }
    
    // Filter levels based on search
    const matchingLevels = course.children.filter(level => {
      // Search in level name
      if (level.name.toLowerCase().includes(query)) {
        return true
      }
      
      // Filter sections based on search
      const matchingSections = level.children.filter(section => {
        // Search in section name
        if (section.name.toLowerCase().includes(query)) {
          return true
        }
        
        // Search in lessons
        const matchingLessons = section.lessons?.filter(lesson =>
          lesson.title.toLowerCase().includes(query) || 
          (lesson.text && lesson.text.toLowerCase().includes(query))
        ) || []
        
        return matchingLessons.length > 0
      })
      
      if (matchingSections.length > 0) {
        return {
          ...level,
          children: matchingSections
        }
      }
      
      return null
    }).filter(Boolean)
    
    if (matchingLevels.length > 0) {
      return {
        ...course,
        children: matchingLevels
      }
    }
    
    return null
  }).filter(Boolean)
})

const selectedSection = computed(() => {
  if (!selectedNode.value) return null
  
  const node = findNodeById(selectedNode.value)
  if (node && node.type === 'section') {
    return {
      name: node.name,
      lessons: node.lessons || [],
      originalLessons: node.lessons || []
    }
  }
  return null
})

const filteredLessons = computed(() => {
  if (!selectedSection.value) return []
  
  let lessons = selectedSection.value.originalLessons || []
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    lessons = lessons.filter(lesson => 
      lesson.title.toLowerCase().includes(query) || 
      (lesson.text && lesson.text.toLowerCase().includes(query))
    )
  }
  return lessons
})

// Methods
const loadCourses = async () => {
  loading.value = true
  error.value = null
  
  try {
    const response = await fetch('/course-management/api/courses/with-structure')
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`)
    }
    
    const data = await response.json()
    courses.value = data.data || data
  } catch (err) {
    console.error('Error loading courses:', err)
    error.value = 'Failed to load courses. Please try again.'
    $q.notify({
      type: 'negative',
      message: 'Failed to load courses',
      position: 'top'
    })
  } finally {
    loading.value = false
  }
}

const findNodeById = (id) => {
  for (const course of courseTree.value) {
    if (course.id === id) return course
    for (const level of course.children || []) {
      if (level.id === id) return level
      for (const section of level.children || []) {
        if (section.id === id) return section
      }
    }
  }
  return null
}

const getNodeIcon = (node) => {
  switch (node.type) {
    case 'course':
      return 'school'
    case 'level':
      return 'layers'
    case 'section':
      return 'description'
    default:
      return 'help_outline'
  }
}

const getNodeColor = (node) => {
  switch (node.type) {
    case 'course':
      return 'primary'
    case 'level':
      return 'secondary'
    case 'section':
      return 'accent'
    default:
      return 'grey'
  }
}

const handleNodeSelection = (nodeId) => {
  console.log('Selected node:', nodeId)
}

const startLesson = (lesson) => {
  $q.notify({
    type: 'positive',
    message: `Starting lesson: ${lesson.title}`,
    position: 'top'
  })
}

const toggleSearch = () => {
  showSearch.value = !showSearch.value
  if (!showSearch.value) {
    searchQuery.value = ''
  }
}

// Lifecycle
onMounted(() => {
  loadCourses()
})
</script>

<style scoped>
.course-preview {
  width: 100%;
  height: 100vh;
}

.q-tree {
  font-size: 14px;
}

.q-tree__node-header {
  padding: 8px 16px;
}

.q-tree__node-header:hover {
  background-color: rgba(0, 0, 0, 0.05);
}

.q-item {
  transition: all 0.3s ease;
}

.q-item:hover {
  background-color: rgba(0, 0, 0, 0.05);
}
</style>