<template>
  <div class="course-card-interface">
    <!-- Course Selection -->
    <div v-if="!selectedCourse" class="q-pa-md">
      <div class="text-center q-mb-xl">
        <h1 class="text-h4 q-mb-md">Select a Course</h1>
        <p class="text-subtitle1 text-grey-7">Choose a course to view its structure and lessons</p>
      </div>
      
      <div class="row q-col-gutter-md">
        <div 
          v-for="course in courses" 
          :key="course.id" 
          class="col-12 col-md-6 col-lg-4"
        >
          <q-card 
            class="course-card cursor-pointer"
            @click="selectCourse(course)"
            v-ripple
          >
            <q-card-section>
              <div class="text-h6">{{ course.name }}</div>
              <div class="text-subtitle2 text-grey-7">{{ course.description }}</div>
            </q-card-section>
            
            <q-card-section>
              <div class="row items-center">
                <q-icon name="layers" class="q-mr-sm" />
                <span>{{ course.levels?.length || 0 }} levels</span>
              </div>
              <div class="row items-center">
                <q-icon name="description" class="q-mr-sm" />
                <span>{{ totalLessons(course) }} lessons</span>
              </div>
            </q-card-section>
          </q-card>
        </div>
      </div>
    </div>

    <!-- Course Structure View -->
    <div v-else>
      <!-- Header with back button -->
      <div class="q-pa-md bg-grey-2">
        <div class="row items-center">
          <q-btn 
            flat 
            round 
            icon="arrow_back" 
            @click="selectedCourse = null"
            class="q-mr-md"
          />
          <div>
            <div class="text-h5">{{ selectedCourse.name }}</div>
            <div class="text-subtitle1 text-grey-7">{{ selectedCourse.description }}</div>
          </div>
        </div>
      </div>

      <!-- Main Content -->
      <q-splitter
        v-model="splitterModel"
        style="height: calc(100vh - 80px)"
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
              
              <q-tree
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
                    :key="index"
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
                        {{ lesson.name }}
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
              
              <div v-else class="text-center q-mt-xl">
                <q-icon name="touch_app" size="48px" color="grey-5" />
                <p class="text-grey-7 q-mt-md">Select a section to view lessons</p>
              </div>
            </div>
          </q-scroll-area>
        </template>
      </q-splitter>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useQuasar } from 'quasar'

const props = defineProps({
  courses: {
    type: Array,
    default: () => []
  }
})

const $q = useQuasar()

// Reactive data
const splitterModel = ref(25)
const selectedCourse = ref(null)
const selectedNode = ref(null)
const searchQuery = ref('')
const showSearch = ref(false)

// Computed properties
const courseTree = computed(() => {
  if (!selectedCourse.value) return []
  
  return selectedCourse.value.levels.map((level, levelIndex) => ({
    id: `level-${level.id}-${levelIndex}`,
    name: level.name,
    displayName: `${level.order || levelIndex + 1}. ${level.name}`,
    type: 'level',
    children: level.sections?.map((section, sectionIndex) => ({
      id: `section-${section.id}-${levelIndex}-${sectionIndex}`,
      name: section.name,
      displayName: `${level.order || levelIndex + 1}.${sectionIndex + 1}. ${section.name}`,
      type: 'section',
      lessons: section.lessons || [],
      levelIndex,
      sectionIndex
    })) || []
  }))
})

const filteredTree = computed(() => {
  if (!searchQuery.value) {
    return courseTree.value
  }

  const query = searchQuery.value.toLowerCase()
  
  return courseTree.value.map(level => {
    // Search in level name
    if (level.name.toLowerCase().includes(query)) {
      return level
    }
    
    // Filter sections based on search
    const matchingSections = level.children.filter(section => {
      // Search in section name
      if (section.name.toLowerCase().includes(query)) {
        return true
      }
      
      // Search in lessons
      const matchingLessons = section.lessons?.filter(lesson =>
        lesson.name.toLowerCase().includes(query) || 
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
      lesson.name.toLowerCase().includes(query) || 
      (lesson.text && lesson.text.toLowerCase().includes(query))
    )
  }
  return lessons
})

// Methods
const totalLessons = (course) => {
  return course.levels?.reduce((total, level) => {
    return total + (level.sections?.reduce((sectionTotal, section) => {
      return sectionTotal + (section.lessons?.length || 0)
    }, 0) || 0)
  }, 0) || 0
}

const selectCourse = (course) => {
  selectedCourse.value = course
  selectedNode.value = null
  searchQuery.value = ''
}

const findNodeById = (id) => {
  for (const level of courseTree.value) {
    if (level.id === id) return level
    for (const section of level.children || []) {
      if (section.id === id) return section
    }
  }
  return null
}

const getNodeIcon = (node) => {
  switch (node.type) {
    case 'level':
      return 'folder'
    case 'section':
      return 'description'
    default:
      return 'help_outline'
  }
}

const getNodeColor = (node) => {
  switch (node.type) {
    case 'level':
      return 'primary'
    case 'section':
      return 'secondary'
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
    message: `Starting lesson: ${lesson.name}`,
    position: 'top'
  })
}

const toggleSearch = () => {
  showSearch.value = !showSearch.value
  if (!showSearch.value) {
    searchQuery.value = ''
  }
}
</script>

<style scoped>
.course-card-interface {
  width: 100%;
  height: 100vh;
}

.course-card {
  transition: all 0.3s ease;
}

.course-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
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