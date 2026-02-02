<template>
  <div class="section-manager">
    <!-- Header -->
    <div class="section-manager__header">
      <h4 class="section-manager__title">
        <q-icon name="view_module" size="20px" />
        Section Management
      </h4>
      
      <q-btn
        color="primary"
        icon="add"
        label="Add Section"
        size="sm"
        @click="showAddDialog = true"
      />
    </div>
    
    <!-- Section List -->
    <div class="section-manager__list" v-if="sections.length > 0">
      <draggable
        v-model="localSections"
        item-key="id"
        handle=".section-manager__drag-handle"
        @end="onSectionReorder"
        class="section-manager__draggable"
      >
        <template #item="{ element: section, index }">
          <div class="section-manager__item">
            <!-- Drag Handle -->
            <div class="section-manager__drag-handle">
              <q-icon name="drag_indicator" size="16px" color="grey-6" />
            </div>
            
            <!-- Section Info -->
            <div class="section-manager__info">
              <div class="section-manager__name">
                {{ section.name }}
                <q-badge
                  v-if="section.questions.length > 0"
                  :label="section.questions.length"
                  color="primary"
                  class="q-ml-xs"
                />
              </div>
              
              <div class="section-manager__meta">
                <span>{{ section.totalPoints }} points</span>
                <span v-if="section.instructions">Has instructions</span>
              </div>
            </div>
            
            <!-- Actions -->
            <div class="section-manager__actions">
              <q-btn
                flat
                round
                dense
                icon="edit"
                size="sm"
                color="grey-6"
                @click="editSection(section)"
              >
                <q-tooltip>Edit section</q-tooltip>
              </q-btn>
              
              <q-btn
                flat
                round
                dense
                :icon="section.collapsed ? 'visibility_off' : 'visibility'"
                size="sm"
                color="grey-6"
                @click="toggleSectionCollapse(section.id)"
              >
                <q-tooltip>{{ section.collapsed ? 'Show' : 'Hide' }} section</q-tooltip>
              </q-btn>
              
              <q-btn
                flat
                round
                dense
                icon="delete"
                size="sm"
                color="negative"
                @click="confirmDeleteSection(section)"
                :disable="sections.length <= 1"
              >
                <q-tooltip>
                  {{ sections.length <= 1 ? 'Cannot delete the last section' : 'Delete section' }}
                </q-tooltip>
              </q-btn>
            </div>
          </div>
        </template>
      </draggable>
    </div>
    
    <!-- Empty State -->
    <div v-else class="section-manager__empty">
      <q-icon name="view_module" size="48px" color="grey-4" />
      <p class="text-grey-6">No sections created yet</p>
      <q-btn
        color="primary"
        icon="add"
        label="Create First Section"
        @click="showAddDialog = true"
      />
    </div>
    
    <!-- Question Assignment Panel -->
    <div class="section-manager__assignment" v-if="unassignedQuestions.length > 0">
      <div class="section-manager__assignment-header">
        <h5>Unassigned Questions ({{ unassignedQuestions.length }})</h5>
        <q-btn
          flat
          size="sm"
          icon="auto_fix_high"
          label="Auto-assign"
          @click="autoAssignQuestions"
        >
          <q-tooltip>Automatically assign questions to sections</q-tooltip>
        </q-btn>
      </div>
      
      <div class="section-manager__unassigned-list">
        <div
          v-for="question in unassignedQuestions.slice(0, 5)"
          :key="question.id"
          class="section-manager__unassigned-item"
        >
          <span class="section-manager__question-text">
            {{ truncateText(question.question_text, 60) }}
          </span>
          
          <q-select
            v-model="questionAssignments[question.id]"
            :options="sectionOptions"
            option-value="id"
            option-label="name"
            outlined
            dense
            style="min-width: 150px"
            @update:model-value="assignQuestionToSection(question.id, $event)"
          />
        </div>
        
        <div v-if="unassignedQuestions.length > 5" class="text-caption text-grey-6 q-mt-sm">
          And {{ unassignedQuestions.length - 5 }} more questions...
        </div>
      </div>
    </div>
    
    <!-- Add Section Dialog -->
    <q-dialog v-model="showAddDialog">
      <q-card style="min-width: 400px">
        <q-card-section>
          <div class="text-h6">Add New Section</div>
        </q-card-section>
        
        <q-card-section>
          <q-input
            v-model="newSectionName"
            outlined
            label="Section Name"
            autofocus
            @keyup.enter="addSection"
          />
          
          <q-input
            v-model="newSectionInstructions"
            type="textarea"
            outlined
            label="Instructions (optional)"
            rows="3"
            class="q-mt-md"
            hint="Instructions shown to students at the beginning of this section"
          />
        </q-card-section>
        
        <q-card-actions align="right">
          <q-btn flat label="Cancel" @click="cancelAddSection" />
          <q-btn
            flat
            label="Add Section"
            color="primary"
            @click="addSection"
            :disable="!newSectionName.trim()"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>
    
    <!-- Edit Section Dialog -->
    <q-dialog v-model="showEditDialog">
      <q-card style="min-width: 400px">
        <q-card-section>
          <div class="text-h6">Edit Section</div>
        </q-card-section>
        
        <q-card-section>
          <q-input
            v-model="editingSectionName"
            outlined
            label="Section Name"
            autofocus
          />
          
          <q-input
            v-model="editingSectionInstructions"
            type="textarea"
            outlined
            label="Instructions (optional)"
            rows="3"
            class="q-mt-md"
            hint="Instructions shown to students at the beginning of this section"
          />
        </q-card-section>
        
        <q-card-actions align="right">
          <q-btn flat label="Cancel" @click="cancelEditSection" />
          <q-btn
            flat
            label="Save Changes"
            color="primary"
            @click="saveEditSection"
            :disable="!editingSectionName.trim()"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>
    
    <!-- Delete Confirmation Dialog -->
    <q-dialog v-model="showDeleteDialog">
      <q-card>
        <q-card-section>
          <div class="text-h6">Delete Section</div>
          <div class="text-body2 q-mt-sm">
            Are you sure you want to delete "{{ deletingSection?.name }}"?
            <br>
            <span v-if="deletingSection?.questions.length > 0" class="text-warning">
              This section contains {{ deletingSection.questions.length }} question(s) that will become unassigned.
            </span>
          </div>
        </q-card-section>
        
        <q-card-actions align="right">
          <q-btn flat label="Cancel" @click="showDeleteDialog = false" />
          <q-btn flat label="Delete" color="negative" @click="deleteSection" />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import draggable from 'vuedraggable'

const props = defineProps({
  sections: {
    type: Array,
    required: true
  },
  questions: {
    type: Array,
    required: true
  }
})

const emit = defineEmits([
  'section-added',
  'section-updated',
  'section-deleted',
  'sections-reordered',
  'question-assigned',
  'toggle-section-collapse'
])

// Local state
const localSections = ref([...props.sections])
const showAddDialog = ref(false)
const showEditDialog = ref(false)
const showDeleteDialog = ref(false)

// New section form
const newSectionName = ref('')
const newSectionInstructions = ref('')

// Edit section form
const editingSection = ref(null)
const editingSectionName = ref('')
const editingSectionInstructions = ref('')

// Delete section
const deletingSection = ref(null)

// Question assignment
const questionAssignments = ref({})

// Computed properties
const sectionOptions = computed(() => {
  return localSections.value.map(section => ({
    id: section.id,
    name: section.name
  }))
})

const unassignedQuestions = computed(() => {
  return props.questions.filter(question => !question.sectionId)
})

// Watch for prop changes
watch(() => props.sections, (newSections) => {
  localSections.value = [...newSections]
}, { deep: true })

// Methods
const addSection = () => {
  if (!newSectionName.value.trim()) return
  
  const newSection = {
    id: `section-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`,
    name: newSectionName.value.trim(),
    instructions: newSectionInstructions.value.trim() || undefined,
    orderIndex: localSections.value.length,
    collapsed: false,
    questions: [],
    totalPoints: 0
  }
  
  localSections.value.push(newSection)
  emit('section-added', newSection)
  
  cancelAddSection()
}

const cancelAddSection = () => {
  newSectionName.value = ''
  newSectionInstructions.value = ''
  showAddDialog.value = false
}

const editSection = (section) => {
  editingSection.value = section
  editingSectionName.value = section.name
  editingSectionInstructions.value = section.instructions || ''
  showEditDialog.value = true
}

const saveEditSection = () => {
  if (!editingSectionName.value.trim() || !editingSection.value) return
  
  const updates = {
    name: editingSectionName.value.trim(),
    instructions: editingSectionInstructions.value.trim() || undefined
  }
  
  emit('section-updated', editingSection.value.id, updates)
  cancelEditSection()
}

const cancelEditSection = () => {
  editingSection.value = null
  editingSectionName.value = ''
  editingSectionInstructions.value = ''
  showEditDialog.value = false
}

const confirmDeleteSection = (section) => {
  deletingSection.value = section
  showDeleteDialog.value = true
}

const deleteSection = () => {
  if (!deletingSection.value) return
  
  emit('section-deleted', deletingSection.value.id)
  
  // Remove from local sections
  const index = localSections.value.findIndex(s => s.id === deletingSection.value.id)
  if (index !== -1) {
    localSections.value.splice(index, 1)
  }
  
  deletingSection.value = null
  showDeleteDialog.value = false
}

const onSectionReorder = () => {
  // Update order indices
  localSections.value.forEach((section, index) => {
    section.orderIndex = index
  })
  
  emit('sections-reordered', localSections.value)
}

const toggleSectionCollapse = (sectionId) => {
  emit('toggle-section-collapse', sectionId)
}

const assignQuestionToSection = (questionId, sectionId) => {
  if (sectionId) {
    emit('question-assigned', questionId, sectionId)
  }
}

const autoAssignQuestions = () => {
  // Simple auto-assignment: distribute questions evenly across sections
  const sectionsCount = localSections.value.length
  if (sectionsCount === 0) return
  
  unassignedQuestions.value.forEach((question, index) => {
    const sectionIndex = index % sectionsCount
    const sectionId = localSections.value[sectionIndex].id
    emit('question-assigned', question.id, sectionId)
  })
}

const truncateText = (text, maxLength) => {
  if (!text) return ''
  const cleanText = text.replace(/<[^>]*>/g, '')
  return cleanText.length > maxLength ? cleanText.substring(0, maxLength) + '...' : cleanText
}
</script>

<style scoped lang="scss">
.section-manager {
  background: white;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
  
  &__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    border-bottom: 1px solid #e2e8f0;
  }
  
  &__title {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 600;
    color: #2d3748;
    display: flex;
    align-items: center;
    gap: 8px;
  }
  
  &__list {
    padding: 12px;
  }
  
  &__draggable {
    display: flex;
    flex-direction: column;
    gap: 8px;
  }
  
  &__item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    background: #f7fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    transition: all 0.2s ease;
    
    &:hover {
      border-color: #cbd5e0;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }
  }
  
  &__drag-handle {
    cursor: grab;
    color: #a0aec0;
    
    &:active {
      cursor: grabbing;
    }
  }
  
  &__info {
    flex: 1;
    min-width: 0;
  }
  
  &__name {
    font-weight: 600;
    color: #2d3748;
    display: flex;
    align-items: center;
    margin-bottom: 2px;
  }
  
  &__meta {
    font-size: 0.75rem;
    color: #718096;
    display: flex;
    gap: 12px;
  }
  
  &__actions {
    display: flex;
    gap: 4px;
    flex-shrink: 0;
  }
  
  &__empty {
    padding: 40px 20px;
    text-align: center;
    color: #718096;
    
    p {
      margin: 12px 0 20px;
      font-size: 0.875rem;
    }
  }
  
  &__assignment {
    border-top: 1px solid #e2e8f0;
    padding: 16px 20px;
    background: #fafafa;
  }
  
  &__assignment-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
    
    h5 {
      margin: 0;
      font-size: 0.875rem;
      font-weight: 600;
      color: #4a5568;
    }
  }
  
  &__unassigned-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
  }
  
  &__unassigned-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 8px;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
  }
  
  &__question-text {
    flex: 1;
    font-size: 0.875rem;
    color: #4a5568;
    min-width: 0;
  }
}

// Responsive adjustments
@media (max-width: 768px) {
  .section-manager {
    &__header {
      flex-direction: column;
      gap: 12px;
      align-items: stretch;
    }
    
    &__item {
      flex-direction: column;
      align-items: stretch;
      gap: 8px;
    }
    
    &__actions {
      justify-content: center;
    }
    
    &__unassigned-item {
      flex-direction: column;
      align-items: stretch;
      gap: 8px;
    }
  }
}
</style>