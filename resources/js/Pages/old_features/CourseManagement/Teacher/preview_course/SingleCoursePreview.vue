<template>
  <div class="single-course-preview">
    <q-splitter
      v-model="splitterModel"
      style="height: 100vh"
      :limits="[20, 50]"
    >
      <template #before>
        <q-scroll-area class="fit">
          <div class="q-pa-md">
            <div v-if="loading" class="text-center q-pa-md">
              <q-spinner-dots color="primary" size="40px" />
              <div class="q-mt-md">Loading course data...</div>
            </div>
            
            <div v-else-if="error" class="text-center q-pa-md">
              <q-icon name="error" color="negative" size="40px" />
              <div class="q-mt-md text-negative">{{ error }}</div>
            </div>
            
            <div v-else-if="!course" class="text-center q-pa-md">
              <q-icon name="info" color="grey" size="40px" />
              <div class="q-mt-md text-grey">No course data available</div>
            </div>
            
            <div v-else>
              <div class="row items-center q-mb-md">
                <h3 class="text-h5 col">{{ course.name }}</h3>
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
            
              <!-- Section Toggle -->
              <div class="row items-center justify-between q-mb-sm">
                <div class="text-caption text-grey-8">Course Structure</div>
                <q-toggle
                  v-model="showSections"
                  label="Show Sections"
                  color="primary"
                  dense
                  size="sm"
                />
              </div>
              
              <!-- Course Tree -->
              <q-tree
                :nodes="filteredTree"
                node-key="id"
                label-key="name"
                children-key="children"
                v-model:selected="selectedNode"
                @update:selected="handleNodeSelection"
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
          </div>
        </q-scroll-area>
      </template>

      <template #after>
        <q-scroll-area class="fit">
          <div class="q-pa-md">
            <div v-if="selectedLesson" class="lesson-content">
              <div class="row items-center q-mb-md">
                <h2 class="text-h4 col">{{ selectedLesson.title }}</h2>
              </div>
              
              <q-card flat bordered class="q-pa-md">
                <div v-if="selectedLesson.text" v-html="selectedLesson.text" class="lesson-text"></div>
                <div v-else class="text-center q-pa-xl text-grey-7">
                  No content available for this lesson.
                </div>
                
                <q-separator class="q-my-md" v-if="selectedLesson.attachments && selectedLesson.attachments.length" />
                
                <div v-if="selectedLesson.attachments && selectedLesson.attachments.length" class="q-mt-md">
                  <div class="text-subtitle1 q-mb-sm">Attachments</div>
                  <q-list bordered separator>
                    <q-item v-for="attachment in selectedLesson.attachments" :key="attachment.id" clickable>
                      <q-item-section avatar>
                        <q-icon name="attachment" color="primary" />
                      </q-item-section>
                      <q-item-section>{{ attachment.name }}</q-item-section>
                      <q-item-section side>
                        <q-btn flat round icon="download" color="primary" />
                      </q-item-section>
                    </q-item>
                  </q-list>
                </div>
              </q-card>
              
              <div class="row justify-end q-mt-md">
                <q-btn 
                  color="primary" 
                  icon="play_arrow" 
                  label="Start Lesson" 
                  @click="startLesson(selectedLesson)" 
                />
              </div>
            </div>
            
            <div v-else-if="selectedLevel" class="level-lessons">
              <div class="row items-center q-mb-md">
                <h2 class="text-h4 col">{{ selectedLevel.name }}</h2>
              </div>
              
              <q-list bordered separator>
                <q-item
                  v-for="(lesson, index) in levelLessons"
                  :key="lesson.id"
                  clickable
                  v-ripple
                  class="q-py-md"
                  @click="selectLesson(lesson)"
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
                    <q-item-label v-if="lesson.section_title" caption class="text-grey-8">
                      Section: {{ lesson.section_title }}
                    </q-item-label>
                  </q-item-section>
                  
                  <q-item-section side>
                    <q-btn
                      flat
                      round
                      icon="visibility"
                      color="primary"
                    />
                  </q-item-section>
                </q-item>
              </q-list>
              
              <div v-if="!levelLessons || levelLessons.length === 0" class="text-center q-mt-xl">
                <q-icon name="info" size="48px" color="grey-5" />
                <p class="text-grey-7 q-mt-md">
                  <span v-if="searchQuery">No lessons match "{{ searchQuery }}"</span>
                  <span v-else>No lessons available in this level</span>
                </p>
              </div>
            </div>
            
            <div v-else-if="!loading" class="text-center q-mt-xl">
              <q-icon name="touch_app" size="48px" color="grey-5" />
              <p class="text-grey-7 q-mt-md">Select a level or lesson to view content</p> 
            </div>
          </div>
        </q-scroll-area>
      </template>
    </q-splitter>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useQuasar } from 'quasar'

const $q = useQuasar()

// Props
const props = defineProps({
  courseId: {
    type: [String, Number],
    required: true
  },
  courseData: {
    type: Object,
    default: null
  }
})

// Reactive data
const splitterModel = ref(25)
const selectedNode = ref(null)
const searchQuery = ref('')
const showSearch = ref(false)
const showSections = ref(false) // Default to hiding sections
const course = ref(null)
const loading = ref(false)
const error = ref(null)
const selectedLesson = ref(null)

// Computed properties
const courseTree = computed(() => {
  if (!course.value) return []
  
  return [{
    id: `course-${course.value.id}`,
    name: course.value.name,
    type: 'course',
    children: course.value.levels?.map(level => {
      // Create level node
      const levelNode = {
        id: `level-${level.id}`,
        name: level.title,
        displayName: `${level.order || 1}. ${level.title}`,
        type: 'level',
        levelId: level.id,
        children: []
      }
      
      // Process sections based on showSections setting
      if (level.sections && level.sections.length > 0) {
        if (showSections.value) {
          // When showing sections, create section nodes with lessons as children
          levelNode.children = level.sections.map(section => ({
            id: `section-${section.id}`,
            name: section.title || 'Untitled Section',
            displayName: `${level.order || 1}.${section.order || 1}. ${section.title || 'Untitled Section'}`,
            type: 'section',
            levelId: level.id,
            sectionId: section.id,
            children: section.lessons?.map(lesson => ({
              id: `lesson-${lesson.id}`,
              name: lesson.title,
              type: 'lesson',
              lessonId: lesson.id,
              lesson: lesson
            })) || []
          }))
        } else {
          // When hiding sections, add lessons directly to level
          level.sections.forEach(section => {
            if (section.lessons && section.lessons.length > 0) {
              section.lessons.forEach(lesson => {
                levelNode.children.push({
                  id: `lesson-${lesson.id}`,
                  name: lesson.title,
                  type: 'lesson',
                  lessonId: lesson.id,
                  section_title: section.title, // Keep section info for reference
                  lesson: lesson
                })
              })
            }
          })
        }
      }
      
      return levelNode
    }) || []
  }]
})

const filteredTree = computed(() => {
  if (!searchQuery.value || !courseTree.value.length) {
    return courseTree.value
  }

  const query = searchQuery.value.toLowerCase()
  
  const course = courseTree.value[0]
  if (!course) return []
  
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
  
  return [{
    ...course,
    children: matchingLevels
  }]
})

const selectedLevel = computed(() => {
  if (!selectedNode.value) return null
  
  const node = findNodeById(selectedNode.value)
  if (node && node.type === 'level') {
    return {
      id: node.levelId,
      name: node.name
    }
  }
  return null
})

const levelLessons = computed(() => {
  if (!selectedLevel.value || !course.value) return []
  
  const levelId = selectedLevel.value.id
  const level = course.value.levels?.find(l => l.id === levelId)
  if (!level) return []
  
  // Collect all lessons from all sections in this level
  let lessons = []
  level.sections?.forEach(section => {
    if (section.lessons && section.lessons.length > 0) {
      // Add section title to each lesson for reference
      const lessonsWithSection = section.lessons.map(lesson => ({
        ...lesson,
        section_title: section.title
      }))
      lessons = [...lessons, ...lessonsWithSection]
    }
  })
  
  // Apply search filter if needed
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
const loadCourse = async () => {
  if (props.courseData) {
    course.value = props.courseData
    return
  }

  loading.value = true
  error.value = null
  
  try {
    const response = await fetch(`/course-management/api/courses/${props.courseId}/structure`)
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`)
    }
    
    const data = await response.json()
    course.value = data.data || data
  } catch (err) {
    console.error('Error loading course:', err)
    error.value = 'Failed to load course. Please try again.'
    $q.notify({
      type: 'negative',
      message: 'Failed to load course',
      position: 'top'
    })
  } finally {
    loading.value = false
  }
}

const findNodeById = (id) => {
  if (!courseTree.value.length) return null
  
  const course = courseTree.value[0]
  if (course.id === id) return course
  
  for (const level of course.children || []) {
    if (level.id === id) return level
    for (const section of level.children || []) {
      if (section.id === id) return section
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
  if (!nodeId) {
    selectedLesson.value = null
    return
  }
  
  const node = findNodeById(nodeId)
  if (!node) return
  
  if (node.type === 'lesson') {
    // If lesson node is selected, show lesson content
    selectedLesson.value = node.lesson
  } else if (node.type === 'level') {
    // If level node is selected, show level lessons
    selectedLesson.value = null
  } else {
    // For other node types, clear selection
    selectedLesson.value = null
  }
}

const selectLesson = (lesson) => {
  selectedLesson.value = lesson
  selectedNode.value = `lesson-${lesson.id}`
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
  loadCourse()
})

// Watch for courseId changes
watch(() => props.courseId, () => {
  loadCourse()
})
</script>

<style scoped>
.single-course-preview {
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
