<template>
  <q-card class="quiz-canvas rounded-xl shadow-3 full-height bg-grey-1">
    <!-- Header -->
    <q-card-section class="quiz-canvas__header bg-white text-primary rounded-borders-top">
      <div class="row items-center justify-between">
        <div class="text-h6 text-weight-bold">
          <q-icon name="edit_document" class="q-mr-sm" />
          Quiz Questions
        </div>
        <div class="row q-gutter-x-sm">
          <!-- Section Manager Toggle -->
          <q-btn
            flat
            round
            dense
            color="primary"
            :icon="showSectionManager ? 'view_list' : 'view_module'"
            @click="showSectionManager = !showSectionManager"
          >
            <q-tooltip>{{ showSectionManager ? 'Hide' : 'Show' }} Section Manager</q-tooltip>
          </q-btn>
          
          <q-btn
            v-if="questions.length > 0"
            flat
            round
            dense
            color="negative"
            icon="delete_sweep"
            @click="$emit('clear-all')"
          >
            <q-tooltip>Clear All</q-tooltip>
          </q-btn>
          
          <q-btn
            v-if="questions.length > 1"
            flat
            round
            dense
            color="secondary"
            icon="shuffle"
            @click="$emit('shuffle')"
          >
            <q-tooltip>Shuffle</q-tooltip>
          </q-btn>
        </div>
      </div>
    </q-card-section>

    <!-- Section Manager Panel -->
    <q-slide-transition>
      <div v-show="showSectionManager" class="quiz-canvas__section-manager">
        <SectionManager
          :sections="sections"
          :questions="questions"
          @section-added="handleSectionAdded"
          @section-updated="handleSectionUpdated"
          @section-deleted="handleSectionDeleted"
          @sections-reordered="handleSectionsReordered"
          @question-assigned="handleQuestionAssigned"
          @toggle-section-collapse="handleToggleSectionCollapse"
        />
      </div>
    </q-slide-transition>

    <!-- Canvas Area -->
    <q-card-section
      class="quiz-canvas__area scroll q-pa-md"
      :style="canvasStyle"
      :class="{ 'bg-blue-1': isDragOver }"
      @drop="handleDrop"
      @dragover.prevent="isDragOver = true"
      @dragleave="isDragOver = false"
    >
      <!-- Empty State -->
      <div v-if="questions.length === 0" class="quiz-canvas__empty">
        <q-icon name="add_circle" size="64px" class="q-mb-md opacity-50" />
        <h5 class="q-my-none text-weight-bold">Your quiz is empty!</h5>
        <p>Drag questions here or click them from the pool</p>
      </div>

      <!-- Questions with Sections -->
      <div v-else class="quiz-canvas__content">
        <template v-for="(item, index) in organizedContent" :key="item.id">
          <!-- Section Break -->
          <SectionBreak
            v-if="item.type === 'section'"
            :section="item.data"
            :section-number="item.sectionNumber"
            :allow-delete="sections.length > 1"
            @toggle-collapse="handleToggleSectionCollapse"
            @update-name="handleSectionNameUpdate"
            @update-instructions="handleSectionInstructionsUpdate"
            @delete-section="handleSectionDeleted"
            class="quiz-canvas__section-break"
          />

          <!-- Question Item -->
          <div
            v-else-if="item.type === 'question' && !item.hidden"
            class="quiz-canvas__question-item animate-pop"
          >
            <draggable
              :model-value="[item.data]"
              @update:model-value="handleQuestionReorder(item.data, $event)"
              item-key="id"
              handle=".drag-handle"
              group="questions"
              @start="isDragging = true"
              @end="isDragging = false"
              @change="handleQuestionMove"
            >
              <template #item="{ element }">
                <div class="row items-start no-wrap q-gutter-x-sm">
                  <!-- Question Number -->
                  <div class="column items-center q-pt-sm">
                    <q-badge color="primary" rounded class="text-weight-bold shadow-1">
                      {{ getQuestionNumber(element.id) }}
                    </q-badge>
                    <q-icon name="drag_indicator" class="drag-handle cursor-move text-grey-5 q-mt-xs" size="20px" />
                  </div>
                  
                  <!-- Question Card -->
                  <question-card
                    :question="element"
                    class="col"
                    :show-remove="true"
                    :show-points="true"
                    @preview="$emit('preview-question', element)"
                    @remove="handleQuestionRemove(element)"
                    @points-updated="$emit('points-updated', element.id, $event)"
                  />
                </div>
              </template>
            </draggable>
          </div>
        </template>
      </div>
    </q-card-section>
  </q-card>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import draggable from 'vuedraggable'
import SectionBreak from './SectionBreak.vue'
import SectionManager from './SectionManager.vue'
import QuestionCard from './QuestionCard.vue'
import { useSectionStore } from '@/composables/useSectionStore'

const props = defineProps({
  questions: {
    type: Array,
    required: true
  },
  sections: {
    type: Array,
    default: () => []
  },
  isDragOver: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits([
  'clear-all',
  'shuffle',
  'preview-question',
  'points-updated',
  'question-removed',
  'questions-reordered',
  'section-added',
  'section-updated',
  'section-deleted',
  'sections-reordered',
  'question-assigned'
])

// Section store
const {
  getQuestionNumbering,
  findSectionByQuestionId
} = useSectionStore()

// Local state
const showSectionManager = ref(false)
const isDragging = ref(false)

// Computed properties
const canvasStyle = computed(() => {
  const baseHeight = 'calc(100vh - 280px)'
  const managerHeight = showSectionManager.value ? '200px' : '0px'
  return {
    height: `calc(${baseHeight} - ${managerHeight})`
  }
})

const organizedContent = computed(() => {
  const content = []
  
  if (props.sections.length === 0) {
    // No sections - show questions directly
    props.questions.forEach(question => {
      content.push({
        id: `question-${question.id}`,
        type: 'question',
        data: question,
        hidden: false
      })
    })
  } else {
    // Organize by sections
    const sortedSections = [...props.sections].sort((a, b) => a.orderIndex - b.orderIndex)
    
    sortedSections.forEach((section, sectionIndex) => {
      // Add section break
      content.push({
        id: `section-${section.id}`,
        type: 'section',
        data: section,
        sectionNumber: sectionIndex + 1
      })
      
      // Add questions in this section
      const sectionQuestions = section.questions
        .sort((a, b) => a.orderInSection - b.orderInSection)
      
      sectionQuestions.forEach(question => {
        content.push({
          id: `question-${question.id}`,
          type: 'question',
          data: question,
          hidden: section.collapsed
        })
      })
    })
    
    // Add unassigned questions at the end
    const unassignedQuestions = props.questions.filter(q => !q.sectionId)
    unassignedQuestions.forEach(question => {
      content.push({
        id: `question-${question.id}`,
        type: 'question',
        data: question,
        hidden: false
      })
    })
  }
  
  return content
})

// Methods
const getQuestionNumber = (questionId) => {
  const numbering = getQuestionNumbering(questionId.toString())
  return numbering ? numbering.globalNumber : '?'
}

const handleDrop = (event) => {
  event.preventDefault()
  
  try {
    const questionData = event.dataTransfer.getData('question')
    if (questionData) {
      const question = JSON.parse(questionData)
      // Emit to parent to handle adding the question
      emit('question-added', question)
    }
  } catch (error) {
    console.error('Failed to handle drop:', error)
  }
}

const handleQuestionRemove = (question) => {
  emit('question-removed', question)
}

const handleQuestionReorder = (question, newOrder) => {
  // Handle reordering within sections
  emit('questions-reordered', newOrder)
}

const handleQuestionMove = (event) => {
  // Handle moving questions between sections
  if (event.moved) {
    const { element, newIndex, oldIndex } = event.moved
    // Determine target section based on position
    // This would need more sophisticated logic to determine section boundaries
    console.log('Question moved:', { element, newIndex, oldIndex })
  }
}

// Section Management Methods
const handleSectionAdded = (section) => {
  emit('section-added', section)
}

const handleSectionUpdated = (sectionId, updates) => {
  emit('section-updated', sectionId, updates)
}

const handleSectionDeleted = (sectionId) => {
  emit('section-deleted', sectionId)
}

const handleSectionsReordered = (sections) => {
  emit('sections-reordered', sections)
}

const handleQuestionAssigned = (questionId, sectionId) => {
  emit('question-assigned', questionId, sectionId)
}

const handleToggleSectionCollapse = (sectionId) => {
  const section = props.sections.find(s => s.id === sectionId)
  if (section) {
    emit('section-updated', sectionId, { collapsed: !section.collapsed })
  }
}

const handleSectionNameUpdate = (sectionId, name) => {
  emit('section-updated', sectionId, { name })
}

const handleSectionInstructionsUpdate = (sectionId, instructions) => {
  emit('section-updated', sectionId, { instructions })
}
</script>

<style scoped lang="scss">
.quiz-canvas {
  &__header {
    border-bottom: 1px solid #e2e8f0;
  }
  
  &__section-manager {
    border-bottom: 1px solid #e2e8f0;
    background: #fafafa;
  }
  
  &__area {
    transition: height 0.3s ease;
  }
  
  &__empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
    color: #718096;
    text-align: center;
    
    h5 {
      font-size: 1.25rem;
      margin-bottom: 8px;
    }
    
    p {
      font-size: 0.875rem;
      margin: 0;
    }
  }
  
  &__content {
    display: flex;
    flex-direction: column;
    gap: 16px;
  }
  
  &__section-break {
    margin: 8px 0;
  }
  
  &__question-item {
    transition: all 0.2s ease;
  }
}

.animate-pop {
  animation: popIn 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

@keyframes popIn {
  from {
    opacity: 0;
    transform: scale(0.9);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

.rounded-borders-top {
  border-top-left-radius: 24px;
  border-top-right-radius: 24px;
}

// Responsive adjustments
@media (max-width: 768px) {
  .quiz-canvas {
    &__question-item {
      .row {
        flex-direction: column;
        gap: 8px;
      }
    }
  }
}
</style>