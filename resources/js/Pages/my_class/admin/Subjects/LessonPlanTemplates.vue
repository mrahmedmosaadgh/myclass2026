<template>
  <div class="lesson-plan-templates">
    <Head :title="`Lesson Plan Templates - ${subject.name}`" />

    <div class="q-pa-md">
      <div class="row q-col-gutter-md">
        <!-- Subject Info Card -->
        <div class="col-12">
          <q-card class="subject-info">
            <q-card-section>
              <div class="text-h5">{{ subject.name }}</div>
              <div class="text-subtitle2" v-if="subject.school">
                {{ subject.school.name }}
              </div>
              <div v-if="subject.description" class="q-mt-sm">
                {{ subject.description }}
              </div>
            </q-card-section>
          </q-card>
        </div>

        <!-- Templates List -->
        <div class="col-12">
          <q-card>
            <q-card-section class="row items-center justify-between q-pb-none">
              <div>
                <div class="text-h6">Lesson Plan Templates</div>
                <div class="text-subtitle2">Manage templates for this subject</div>
              </div>
              <q-btn
                color="primary"
                icon="add"
                label="Add Template"
                @click="openTemplateDialog()"
                class="q-px-md"
                unelevated
                rounded
              />
            </q-card-section>

            <q-card-section>
              <q-table
                :rows="templates"
                :columns="templateColumns"
                row-key="name"
                :pagination="{ rowsPerPage: 10 }"
                v-if="templates.length > 0"
                flat
                bordered
                class="template-table"
              >
                <template v-slot:header="props">
                  <q-tr :props="props">
                    <q-th v-for="col in props.cols" :key="col.name" :props="props" class="text-primary">
                      {{ col.label }}
                    </q-th>
                  </q-tr>
                </template>
                
                <template v-slot:body="props">
                  <q-tr :props="props" class="cursor-pointer template-row" @click="showTemplatePreview(props.rowIndex)">
                    <q-td key="name" :props="props" class="text-weight-medium">{{ props.row.name }}</q-td>
                    <q-td key="description" :props="props" class="text-grey-8">{{ props.row.description || 'No description' }}</q-td>
                    <q-td key="fields" :props="props" class="text-center">
                      <q-chip size="sm" color="primary" text-color="white" outline>
                        {{ Object.keys(props.row.structure || {}).length }} fields
                      </q-chip>
                    </q-td>
                    <q-td key="actions" :props="props" auto-width @click.stop>
                      <div class="row q-gutter-sm justify-end">
                        <q-btn flat round color="primary" icon="edit" size="sm" @click="openTemplateDialog(props.rowIndex)">
                          <q-tooltip>Edit Template</q-tooltip>
                        </q-btn>
                        <q-btn flat round color="info" icon="visibility" size="sm" @click="showTemplatePreview(props.rowIndex)">
                          <q-tooltip>Preview Template</q-tooltip>
                        </q-btn>
                        <q-btn flat round color="negative" icon="delete" size="sm" @click="confirmDeleteTemplate(props.rowIndex)">
                          <q-tooltip>Delete Template</q-tooltip>
                        </q-btn>
                      </div>
                    </q-td>
                  </q-tr>
                </template>
              </q-table>

              <div v-if="templates.length === 0" class="q-pa-xl text-center empty-state">
                <q-icon name="description" size="64px" color="primary" class="q-mb-md" />
                <div class="text-h6 text-grey-8">No Templates Yet</div>
                <div class="text-body1 q-mb-md text-grey-7">Create your first lesson plan template to get started</div>
                <q-btn
                  color="primary"
                  icon="add"
                  label="Create Template"
                  @click="openTemplateDialog()"
                  class="q-px-md"
                  unelevated
                  rounded
                />
              </div>
            </q-card-section>
          </q-card>
        </div>
      </div>
    </div>

    <!-- Template Edit Dialog -->
    <q-dialog v-model="templateDialog" persistent maximized>
      <q-card>
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">{{ isEditing ? 'Edit Template' : 'Add New Template' }}</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-card-section class="q-pt-none">
          <div class="row q-col-gutter-md">
            <!-- Template Form -->
            <div class="col-12 col-md-6">
              <q-form @submit="submitForm" class="q-gutter-md">
                <q-input
                  v-model="form.name"
                  label="Template Name"
                  :rules="[val => !!val || 'Name is required']"
                  outlined
                />

                <q-input
                  v-model="form.description"
                  label="Description"
                  type="textarea"
                  outlined
                />

                <div class="text-h6 q-mt-md">Template Fields</div>
                <div class="text-caption q-mb-md">Add fields to your lesson plan template. Drag to reorder.</div>

                <draggable 
                  v-model="templateFields" 
                  item-key="index"
                  handle=".drag-handle"
                  ghost-class="ghost"
                  class="q-gutter-y-md"
                >
                  <template #item="{ element: field, index }">
                    <div class="template-field-item">
                      <div class="row q-col-gutter-sm">
                        <div class="col-12 col-sm-4">
                          <q-input
                            v-model="field.key"
                            label="Field Name"
                            outlined
                            dense
                            :rules="[val => !!val || 'Field name is required']"
                          />
                        </div>
                        <div class="col-12 col-sm-4">
                          <q-select
                            v-model="field.type"
                            :options="fieldTypeOptions"
                            label="Field Type"
                            outlined
                            dense
                            emit-value
                            map-options
                          />
                        </div>
                        <div class="col-12 col-sm-3">
                          <q-input
                            v-model="field.label"
                            label="Display Label"
                            outlined
                            dense
                          />
                        </div>
                        <div class="col-12 col-sm-1 flex items-center justify-between">
                          <q-btn
                            flat
                            round
                            color="primary"
                            icon="drag_indicator"
                            size="sm"
                            class="drag-handle cursor-move"
                          />
                          <q-btn
                            flat
                            round
                            color="negative"
                            icon="delete"
                            @click="removeField(index)"
                            size="sm"
                          />
                        </div>
                      </div>

                      <div class="row q-col-gutter-sm q-mt-xs" v-if="field.type === 'select'">
                        <div class="col-12">
                          <q-input
                            v-model="field.options"
                            label="Options (comma separated)"
                            outlined
                            dense
                            hint="Enter options separated by commas"
                          />
                        </div>
                      </div>
                    </div>
                  </template>
                </draggable>

                <div class="q-mt-md">
                  <q-btn
                    color="primary"
                    outline
                    icon="add"
                    label="Add Field"
                    @click="addField"
                  />
                </div>

                <div class="row q-gutter-sm justify-end q-mt-lg">
                  <q-btn
                    label="Cancel"
                    color="grey"
                    v-close-popup
                    outline
                  />
                  <q-btn
                    label="Preview"
                    color="info"
                    @click="showPreview = true"
                    outline
                  />
                  <q-btn
                    :label="isEditing ? 'Update Template' : 'Save Template'"
                    type="submit"
                    color="primary"
                    :loading="isSubmitting"
                  />
                </div>
              </q-form>
            </div>

            <!-- Template Preview -->
            <div class="col-12 col-md-6" v-if="showPreview">
              <q-card flat bordered>
                <q-card-section>
                  <div class="text-h6">Template Preview</div>
                  <div class="text-subtitle2">This is how your template will look</div>
                </q-card-section>

                <q-separator />

                <q-card-section>
                  <div class="text-h5">{{ form.name }}</div>
                  <div class="text-caption q-mb-md" v-if="form.description">{{ form.description }}</div>

                  <div v-for="(field, index) in templateFields" :key="index" class="q-mb-md preview-field">
                    <div class="field-number">{{ index + 1 }}</div>
                    <q-input
                      v-if="field.type === 'text'"
                      outlined
                      :label="field.label || field.key"
                      readonly
                    />
                    <q-input
                      v-else-if="field.type === 'textarea'"
                      type="textarea"
                      outlined
                      :label="field.label || field.key"
                      readonly
                    />
                    <q-select
                      v-else-if="field.type === 'select'"
                      outlined
                      :label="field.label || field.key"
                      :options="field.options ? field.options.split(',').map(o => o.trim()) : []"
                      readonly
                    />
                    <q-input
                      v-else-if="field.type === 'number'"
                      type="number"
                      outlined
                      :label="field.label || field.key"
                      readonly
                    />
                    <q-date
                      v-else-if="field.type === 'date'"
                      outlined
                      :label="field.label || field.key"
                      readonly
                      flat
                      bordered
                    />
                  </div>
                </q-card-section>
              </q-card>
            </div>
          </div>
        </q-card-section>
      </q-card>
    </q-dialog>

    <!-- Template Preview Dialog -->
    <q-dialog v-model="previewDialog" maximized>
      <q-card>
        <q-card-section class="row items-center">
          <div class="text-h6">Template Preview: {{ previewTemplateData ? previewTemplateData.name : '' }}</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-card-section v-if="previewTemplateData">
          <div class="text-h5">{{ previewTemplateData.name }}</div>
          <div class="text-caption q-mb-md" v-if="previewTemplateData.description">{{ previewTemplateData.description }}</div>

          <div v-for="(field, key) in previewTemplateData.structure" :key="key" class="q-mb-md">
            <q-input
              v-if="field.type === 'text'"
              outlined
              :label="field.label || key"
              readonly
            />
            <q-input
              v-else-if="field.type === 'textarea'"
              type="textarea"
              outlined
              :label="field.label || key"
              readonly
            />
            <q-select
              v-else-if="field.type === 'select'"
              outlined
              :label="field.label || key"
              :options="field.options ? field.options.split(',').map(o => o.trim()) : []"
              readonly
            />
            <q-input
              v-else-if="field.type === 'number'"
              type="number"
              outlined
              :label="field.label || key"
              readonly
            />
            <q-date
              v-else-if="field.type === 'date'"
              outlined
              :label="field.label || key"
              readonly
              flat
              bordered
            />
          </div>
        </q-card-section>
      </q-card>
    </q-dialog>

    <!-- Delete Confirmation Dialog -->
    <q-dialog v-model="deleteDialog" persistent>
      <q-card>
        <q-card-section class="row items-center">
          <q-avatar icon="warning" color="negative" text-color="white" />
          <span class="q-ml-sm">Are you sure you want to delete this template?</span>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Cancel" color="primary" v-close-popup />
          <q-btn flat label="Delete" color="negative" @click="deleteTemplate" :loading="isDeleting" v-close-popup />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { useQuasar } from 'quasar';
import axios from 'axios';
import draggable from 'vuedraggable';

// Configure axios to include CSRF token
axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
axios.defaults.withCredentials = true;

// Props
const props = defineProps({
  subject: Object,
  templates: {
    type: Array,
    default: () => []
  }
});

// Setup
const $q = useQuasar();
// Ensure templates is always an array
const templates = ref(Array.isArray(props.templates) ? props.templates : []);
const isEditing = ref(false);
const editingIndex = ref(null);
const isSubmitting = ref(false);
const isDeleting = ref(false);
const deleteDialog = ref(false);
const templateDialog = ref(false);
const previewDialog = ref(false);
const templateToDelete = ref(null);
const showPreview = ref(false);
const previewTemplateData = ref(null);

// Template fields for the form-based approach
const templateFields = ref([]);

// Field type options
const fieldTypeOptions = [
  { label: 'Text', value: 'text' },
  { label: 'Textarea', value: 'textarea' },
  { label: 'Select', value: 'select' },
  { label: 'Number', value: 'number' },
  { label: 'Date', value: 'date' }
];

// Table columns for templates list
const templateColumns = [
  {
    name: 'name',
    label: 'Template Name',
    field: 'name',
    align: 'left',
    sortable: true
  },
  {
    name: 'description',
    label: 'Description',
    field: 'description'
  },
  {
    name: 'fields',
    label: 'Fields',
    field: row => Object.keys(row.structure || {}).length,
    align: 'center'
  },
  {
    name: 'actions',
    label: 'Actions',
    field: 'actions',
    align: 'right'
  }
];

// Form
const form = ref({
  name: '',
  description: ''
});

// Methods
const resetForm = () => {
  form.value = {
    name: '',
    description: ''
  };
  templateFields.value = [];
  isEditing.value = false;
  editingIndex.value = null;
  showPreview.value = false;
};

const openTemplateDialog = (index = null) => {
  resetForm();

  if (index !== null) {
    // Edit existing template
    const template = templates.value[index];
    form.value = {
      name: template.name,
      description: template.description || ''
    };

    // Convert structure object to fields array
    templateFields.value = Object.entries(template.structure || {}).map(([key, field]) => ({
      key,
      type: field.type || 'text',
      label: field.label || key,
      options: field.options || ''
    }));

    isEditing.value = true;
    editingIndex.value = index;
  } else {
    // Add a default field for new templates
    templateFields.value = [
      { key: 'objectives', type: 'textarea', label: 'Learning Objectives' },
      { key: 'materials', type: 'textarea', label: 'Materials Needed' },
 
  { key: 'warmup', type: 'textarea', label: 'Warm-Up Activity' },
  { key: 'instruction', type: 'textarea', label: 'Direct Instruction' },
  { key: 'activity', type: 'textarea', label: 'Guided Practice' },
  { key: 'independent', type: 'textarea', label: 'Independent Work' },
  { key: 'assessment', type: 'textarea', label: 'Assessment' },
  { key: 'reflection', type: 'textarea', label: 'Student Reflection' }
    ];
  }

  templateDialog.value = true;
};

const addField = () => {
  templateFields.value.push({
    key: '',
    type: 'text',
    label: '',
    options: ''
  });
};

const removeField = (index) => {
  templateFields.value.splice(index, 1);
};

const showTemplatePreview = (index) => {
  previewTemplateData.value = templates.value[index];
  previewDialog.value = true;
};

const submitForm = async () => {
  // Validate form
  if (!form.value.name) {
    $q.notify({
      color: 'negative',
      message: 'Template name is required',
      icon: 'error'
    });
    return;
  }

  // Validate fields
  for (const field of templateFields.value) {
    if (!field.key) {
      $q.notify({
        color: 'negative',
        message: 'All fields must have a name',
        icon: 'error'
      });
      return;
    }
  }

  isSubmitting.value = true;

  try {
    // Convert fields array to structure object
    const structure = {};
    for (const field of templateFields.value) {
      structure[field.key] = {
        type: field.type,
        label: field.label
      };

      if (field.type === 'select' && field.options) {
        structure[field.key].options = field.options;
      }
    }

    // Prepare the template object
    const templateObj = {
      name: form.value.name,
      description: form.value.description,
      structure: structure
    };

    // Update templates array
    if (isEditing.value) {
      templates.value[editingIndex.value] = templateObj;
    } else {
      templates.value.push(templateObj);
    }

    // Save to server
    await saveTemplates();

    // Close dialog and reset form
    templateDialog.value = false;
    resetForm();

    $q.notify({
      color: 'positive',
      message: isEditing.value ? 'Template updated successfully' : 'Template added successfully',
      icon: 'check_circle'
    });
  } catch (error) {
    console.error('Error submitting form:', error);
    $q.notify({
      color: 'negative',
      message: 'An error occurred. Please try again.',
      icon: 'error'
    });
  } finally {
    isSubmitting.value = false;
  }
};

const confirmDeleteTemplate = (index) => {
  templateToDelete.value = index;
  deleteDialog.value = true;
};

const deleteTemplate = async () => {
  if (templateToDelete.value === null) return;

  isDeleting.value = true;

  try {
    // Remove from the array
    templates.value.splice(templateToDelete.value, 1);

    // Save to server
    await saveTemplates();

    $q.notify({
      color: 'positive',
      message: 'Template deleted successfully',
      icon: 'check_circle'
    });
  } catch (error) {
    console.error('Error deleting template:', error);
    $q.notify({
      color: 'negative',
      message: 'An error occurred while deleting the template',
      icon: 'error'
    });
  } finally {
    isDeleting.value = false;
    templateToDelete.value = null;
  }
};

const saveTemplates = async () => {
  try {
    // Use axios to send the request to the correct URL
    const url = `/admin/subject/${props.subject.id}/lesson-plan-templates`;

    const response = await axios.patch(url, {
      lesson_plan_templates: JSON.stringify(templates.value)
    });

    return response.data;
  } catch (error) {
    console.error('Error saving templates:', error);
    throw error;
  }
};
</script>

<style scoped>
.lesson-plan-templates {
  max-width: 1200px;
  margin: 0 auto;
}

.template-item {
  transition: background-color 0.2s;
}

.template-item:hover {
  background-color: rgba(0, 0, 0, 0.03);
}

.template-field-item {
  padding: 12px;
  border-radius: 8px;
  border: 1px solid #e0e0e0;
  background-color: #f8f8f8;
}

.flex {
  display: flex;
}

.items-center {
  align-items: center;
}

/* Add some animation for the preview */
.q-card-section {
  transition: all 0.3s ease;
}

/* Make the preview card look like a form */
.q-card.flat.bordered {
  box-shadow: 0 1px 5px rgba(0, 0, 0, 0.1);
  border-radius: 8px;
  overflow: hidden;
}

/* Empty state styling */
.empty-state {
  padding: 3rem 1rem;
  background-color: #f7fafc;
  border-radius: 8px;
}

/* Table styling */
.template-table {
  border-radius: 8px;
  overflow: hidden;
}

.template-row {
  transition: background-color 0.2s ease;
}

.template-row:hover {
  background-color: #f0f7ff !important;
}

/* Add Template button styling */
.q-btn.unelevated.rounded {
  font-weight: 500;
  letter-spacing: 0.5px;
  transition: transform 0.2s ease;
}

.q-btn.unelevated.rounded:hover {
  transform: translateY(-2px);
}

/* Drag and drop styling */
.drag-handle {
  cursor: move;
  opacity: 0.7;
  transition: opacity 0.2s ease;
}

.drag-handle:hover {
  opacity: 1;
}

.ghost {
  opacity: 0.5;
  background: #c8ebfb;
  border: 1px dashed #2196f3;
}

.template-field-item {
  transition: all 0.3s ease;
}

/* Preview field numbering */
.preview-field {
  position: relative;
}

.field-number {
  position: absolute;
  top: -8px;
  left: -8px;
  width: 24px;
  height: 24px;
  background-color: #1976d2;
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  font-weight: bold;
  z-index: 1;
}
</style>
