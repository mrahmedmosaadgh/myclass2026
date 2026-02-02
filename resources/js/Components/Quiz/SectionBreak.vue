<template>
  <div class="section-break" :class="{ 'section-break--collapsed': section.collapsed }">
    <!-- Section Header -->
    <div class="section-break__header" @click="toggleCollapse">
      <div class="section-break__header-content">
        <!-- Collapse/Expand Icon -->
        <q-btn
          flat
          round
          dense
          :icon="section.collapsed ? 'expand_more' : 'expand_less'"
          size="sm"
          color="primary"
          class="section-break__collapse-btn"
        />
        
        <!-- Section Number and Name -->
        <div class="section-break__title">
          <span class="section-break__number">Section {{ sectionNumber }}</span>
          <h3 class="section-break__name" v-if="!isEditing">{{ section.name }}</h3>
          <q-input
            v-else
            v-model="editedName"
            outlined
            dense
            autofocus
            class="section-break__name-input"
            @blur="saveName"
            @keyup.enter="saveName"
            @keyup.escape="cancelEdit"
            @click.stop
          />
        </div>
        
        <!-- Section Stats -->
        <div class="section-break__stats">
          <q-chip
            outline
            color="primary"
            size="sm"
            :label="`${section.questions.length} questions`"
          />
          <q-chip
            outline
            color="secondary"
            size="sm"
            :label="`${section.totalPoints} pts`"
          />
        </div>
        
        <!-- Actions -->
        <div class="section-break__actions" @click.stop>
          <q-btn
            flat
            round
            dense
            icon="edit"
            size="sm"
            color="grey-6"
            @click="startEdit"
            v-if="!isEditing"
          >
            <q-tooltip>Edit section name</q-tooltip>
          </q-btn>
          
          <q-btn
            flat
            round
            dense
            icon="settings"
            size="sm"
            color="grey-6"
            @click="showInstructionsDialog = true"
          >
            <q-tooltip>Edit instructions</q-tooltip>
          </q-btn>
          
          <q-btn
            flat
            round
            dense
            icon="delete"
            size="sm"
            color="negative"
            @click="confirmDelete"
            v-if="allowDelete"
          >
            <q-tooltip>Delete section</q-tooltip>
          </q-btn>
        </div>
      </div>
    </div>
    
    <!-- Section Instructions -->
    <div 
      v-if="section.instructions && !section.collapsed" 
      class="section-break__instructions"
    >
      <q-icon name="info" size="16px" color="info" />
      <span v-html="section.instructions"></span>
    </div>
    
    <!-- Section Divider -->
    <div class="section-break__divider">
      <div class="section-break__line"></div>
    </div>
    
    <!-- Instructions Dialog -->
    <q-dialog v-model="showInstructionsDialog">
      <q-card style="min-width: 400px">
        <q-card-section>
          <div class="text-h6">Section Instructions</div>
          <div class="text-subtitle2 text-grey-6">
            Optional instructions shown to students at the beginning of this section
          </div>
        </q-card-section>
        
        <q-card-section>
          <q-input
            v-model="editedInstructions"
            type="textarea"
            outlined
            rows="4"
            label="Instructions"
            hint="You can use HTML formatting"
          />
        </q-card-section>
        
        <q-card-actions align="right">
          <q-btn flat label="Cancel" @click="cancelInstructionsEdit" />
          <q-btn flat label="Save" color="primary" @click="saveInstructions" />
        </q-card-actions>
      </q-card>
    </q-dialog>
    
    <!-- Delete Confirmation Dialog -->
    <q-dialog v-model="showDeleteDialog">
      <q-card>
        <q-card-section>
          <div class="text-h6">Delete Section</div>
          <div class="text-body2 q-mt-sm">
            Are you sure you want to delete "{{ section.name }}"?
            <br>
            <span v-if="section.questions.length > 0" class="text-warning">
              This section contains {{ section.questions.length }} question(s) that will be moved to the default section.
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
import { ref, computed } from 'vue'

const props = defineProps({
  section: {
    type: Object,
    required: true
  },
  sectionNumber: {
    type: Number,
    required: true
  },
  allowDelete: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits([
  'toggle-collapse',
  'update-name',
  'update-instructions',
  'delete-section'
])

// Editing state
const isEditing = ref(false)
const editedName = ref('')
const showInstructionsDialog = ref(false)
const editedInstructions = ref('')
const showDeleteDialog = ref(false)

// Methods
const toggleCollapse = () => {
  emit('toggle-collapse', props.section.id)
}

const startEdit = () => {
  editedName.value = props.section.name
  isEditing.value = true
}

const saveName = () => {
  if (editedName.value.trim() && editedName.value !== props.section.name) {
    emit('update-name', props.section.id, editedName.value.trim())
  }
  isEditing.value = false
}

const cancelEdit = () => {
  editedName.value = ''
  isEditing.value = false
}

const saveInstructions = () => {
  emit('update-instructions', props.section.id, editedInstructions.value)
  showInstructionsDialog.value = false
}

const cancelInstructionsEdit = () => {
  editedInstructions.value = props.section.instructions || ''
  showInstructionsDialog.value = false
}

const confirmDelete = () => {
  showDeleteDialog.value = true
}

const deleteSection = () => {
  emit('delete-section', props.section.id)
  showDeleteDialog.value = false
}

// Initialize instructions when dialog opens
const openInstructionsDialog = () => {
  editedInstructions.value = props.section.instructions || ''
  showInstructionsDialog.value = true
}
</script>

<style scoped lang="scss">
.section-break {
  margin: 24px 0;
  
  &--collapsed {
    .section-break__instructions {
      display: none;
    }
  }
  
  &__header {
    cursor: pointer;
    border-radius: 12px;
    background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
    border: 2px solid #e2e8f0;
    transition: all 0.2s ease;
    
    &:hover {
      border-color: #cbd5e0;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }
  }
  
  &__header-content {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px 20px;
  }
  
  &__collapse-btn {
    flex-shrink: 0;
  }
  
  &__title {
    flex: 1;
    min-width: 0;
  }
  
  &__number {
    font-size: 0.75rem;
    font-weight: 600;
    color: #718096;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: block;
    margin-bottom: 2px;
  }
  
  &__name {
    font-size: 1.25rem;
    font-weight: 600;
    color: #2d3748;
    margin: 0;
    line-height: 1.2;
  }
  
  &__name-input {
    max-width: 300px;
  }
  
  &__stats {
    display: flex;
    gap: 8px;
    flex-shrink: 0;
  }
  
  &__actions {
    display: flex;
    gap: 4px;
    flex-shrink: 0;
  }
  
  &__instructions {
    margin: 12px 0;
    padding: 12px 16px;
    background: #f7fafc;
    border-left: 4px solid #4299e1;
    border-radius: 0 8px 8px 0;
    display: flex;
    align-items: flex-start;
    gap: 8px;
    font-size: 0.875rem;
    color: #4a5568;
    line-height: 1.5;
  }
  
  &__divider {
    margin: 16px 0;
    position: relative;
  }
  
  &__line {
    height: 2px;
    background: linear-gradient(90deg, transparent 0%, #e2e8f0 20%, #e2e8f0 80%, transparent 100%);
    border-radius: 1px;
  }
}

// Responsive adjustments
@media (max-width: 768px) {
  .section-break {
    &__header-content {
      padding: 12px 16px;
      gap: 8px;
    }
    
    &__stats {
      flex-direction: column;
      gap: 4px;
    }
    
    &__actions {
      flex-direction: column;
    }
    
    &__name {
      font-size: 1.1rem;
    }
  }
}
</style>