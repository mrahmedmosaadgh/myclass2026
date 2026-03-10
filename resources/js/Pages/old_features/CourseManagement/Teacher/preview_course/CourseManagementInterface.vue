<template>
  <div class="course-management-interface">
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
</template>

<script setup>
import { ref, computed } from 'vue'
import { useQuasar } from 'quasar'

const $q = useQuasar()

// Reactive data
const splitterModel = ref(25)
const selectedNode = ref(null)
const searchQuery = ref('')
const showSearch = ref(false)

// Sample course data
const courseData = ref({
  course: "Typing Course",
  levels: [
    {
      name: "Beginner",
      sections: [
        {
          name: "Getting Started",
          lessons: [
            { name: "J, F, and Space" },
            { name: "U, R, and K Keys" },
            { name: "D, E, and I Keys" },
            { name: "C, G, and N Keys" }
          ]
        },
        {
          name: "Basic Words",
          lessons: [
            { name: "Common Words" },
            { name: "Short Sentences" },
            { name: "Practice Drills" }
          ]
        }
      ]
    },
    {
      name: "Intermediate",
      sections: [
        {
          name: "Advanced Keys",
          lessons: [
            { name: "Q, W, and P Keys" },
            { name: "V, B, and M Keys" },
            { name: "Special Characters" }
          ]
        },
        {
          name: "Speed Building",
          lessons: [
            { name: "Timed Exercises" },
            { name: "Accuracy Drills" },
            { name: "Speed Tests" }
          ]
        }
      ]
    }
  ]
})

// Transform course data into tree structure with numbering
const courseTree = computed(() => {
  return courseData.value.levels.map((level, levelIndex) => ({
    id: `level-${levelIndex}`,
    name: level.name,
    displayName: `${levelIndex + 1}. ${level.name}`,
    type: 'level',
    children: level.sections.map((section, sectionIndex) => ({
      id: `section-${levelIndex}-${sectionIndex}`,
      name: section.name,
      displayName: `${levelIndex + 1}.${sectionIndex + 1}. ${section.name}`,
      type: 'section',
      lessons: section.lessons,
      levelIndex,
      sectionIndex
    }))
  }))
})

// Filtered tree based on search
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
        lesson.name.toLowerCase().includes(query)
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

// Computed property for selected section
const selectedSection = computed(() => {
  if (!selectedNode.value) return null
  
  const node = findNodeById(selectedNode.value)
  if (node && node.type === 'section') {
    // Filter lessons based on search
    let lessons = node.lessons || []
    if (searchQuery.value) {
      const query = searchQuery.value.toLowerCase()
      lessons = lessons.filter(lesson => 
        lesson.name.toLowerCase().includes(query)
      )
    }
    
    return {
      name: node.name,
      lessons: lessons,
      originalLessons: node.lessons || []
    }
  }
  return null
})

// Computed property for filtered lessons
const filteredLessons = computed(() => {
  if (!selectedSection.value) return []
  
  let lessons = selectedSection.value.originalLessons || []
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    lessons = lessons.filter(lesson => 
      lesson.name.toLowerCase().includes(query)
    )
  }
  return lessons
})

// Helper functions
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
.course-management-interface {
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