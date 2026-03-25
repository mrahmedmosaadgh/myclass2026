<template>
  <q-dialog :model-value="modelValue" @update:model-value="$emit('update:modelValue', $event)" maximized transition-show="slide-up" transition-hide="slide-down">
    <q-card class="bg-grey-1">
      <q-toolbar class="bg-primary text-white">
        <q-btn flat round dense icon="close" v-close-popup />
        <q-toolbar-title>Manage Lesson Templates</q-toolbar-title>
      </q-toolbar>

      <q-card-section class="q-pa-md">
        <div class="row q-col-gutter-md max-w-7xl mx-auto">
          <!-- Valid Subject Filter/Select -->
          <div class="col-12" v-if="subjects.length > 1">
            <q-card>
              <q-card-section>
                <div class="text-subtitle2 q-mb-sm">Select Subject to Manage</div>
                <q-select
                  v-model="selectedSubject"
                  :options="subjects"
                  option-label="name"
                  option-value="id"
                  label="Subject"
                  outlined
                  dense
                  @update:model-value="lessonPresentationStore.fetchTemplates(selectedSubject);"
                />
              </q-card-section>
            </q-card>
          </div>

          <!-- Templates List -->
          <div class="col-12" v-if="selectedSubject">
            <q-card>
              <q-card-section class="row items-center justify-between q-pb-none">
                <div>
                  <div class="text-h6">Templates for {{ selectedSubject.name }}</div>
                  <div class="text-subtitle2">Manage structural templates</div>
                </div>
                <q-btn
                  color="primary"
                  icon="add"
                  label="Add Template"
                  @click="openTemplateDialog()"
                  class="q-px-md"
                  unelevated
                  rounded
                  :loading="loading"
                />
              </q-card-section>

              <q-card-section>
                <div v-if="loading" class="row justify-center q-py-lg">
                  <q-spinner color="primary" size="3em" />
                </div>

                <q-table
                  v-else
                  :rows="lessonPresentationStore.templates.filter(t => t.subject_id === selectedSubject.id) || []"
                  :columns="templateColumns"
                  row-key="id"
                  :pagination="{ rowsPerPage: 10 }"
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
                    <q-tr :props="props" class="cursor-pointer template-row" @click="showTemplatePreview(props.row)">
                      <q-td key="name" :props="props" class="text-weight-medium">{{ props.row.name }}</q-td>
                      <q-td key="description" :props="props" class="text-grey-8">{{ props.row.description || 'No description' }}</q-td>
                      <q-td key="fields" :props="props" class="text-center">
                        <q-chip size="sm" color="primary" text-color="white" outline>
                          {{ Object.keys(props.row.structure || {}).length }} fields
                        </q-chip>
                      </q-td>
                      <q-td key="actions" :props="props" auto-width @click.stop>
                        <div class="row q-gutter-sm justify-end">
                          <q-btn flat round color="primary" icon="edit" size="sm" @click="openTemplateDialog(props.row)">
                            <q-tooltip>Edit Template</q-tooltip>
                          </q-btn>
                          <q-btn flat round color="info" icon="visibility" size="sm" @click="showTemplatePreview(props.row)">
                            <q-tooltip>Preview Template</q-tooltip>
                          </q-btn>
                          <q-btn flat round color="negative" icon="delete" size="sm" @click="confirmDeleteTemplate(props.row)">
                            <q-tooltip>Delete Template</q-tooltip>
                          </q-btn>
                        </div>
                      </q-td>
                    </q-tr>
                  </template>

                  <template v-slot:no-data>
                    <div class="full-width row flex-center q-pa-md text-grey-7" v-if="!loading">
                      <div class="text-center">
                        <q-icon name="description" size="48px" color="grey-4" class="q-mb-sm" />
                        <div>No templates found for this subject. Create one to get started.</div>
                      </div>
                    </div>
                  </template>
                </q-table>
              </q-card-section>
            </q-card>
          </div>

          <div class="col-12 text-center q-pt-xl" v-else>
            <div class="text-h6 text-grey-7">Select a subject to view templates</div>
          </div>
        </div>
      </q-card-section>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import { useQuasar } from 'quasar';
import axios from 'axios';
import draggable from 'vuedraggable';
import { useLessonPresentationStore } from '@/Stores/lessonPresentationStore';

const props = defineProps({
  modelValue: Boolean,
  subjects: {
    type: Array,
    required: true
  }
});

const emit = defineEmits(['update:modelValue']);
const $q = useQuasar();
const lessonPresentationStore = useLessonPresentationStore();

// State
const selectedSubject = ref([]);
const loading = ref(false);

// Dialogs
const editDialog = ref(false);
const previewDialog = ref(false);
const showPreview = ref(true);

// Edit State
const isEditing = ref(false);
const isSubmitting = ref(false);
const currentTemplateId = ref(null);
const previewTemplateData = ref(null);

// Form
const form = ref({
  name: '',
  description: ''
});
const templateFields = ref([]);

const fieldTypeOptions = [
  { label: 'Text', value: 'text' },
  { label: 'Long Text', value: 'textarea' },
  { label: 'Dropdown', value: 'select' },
  { label: 'Number', value: 'number' },
  { label: 'Date', value: 'date' }
];

const templateColumns = [
  { name: 'name', label: 'Name', field: 'name', align: 'left', sortable: true },
  { name: 'description', label: 'Description', field: 'description', align: 'left' },
  { name: 'fields', label: 'Fields', field: row => Object.keys(row.structure || {}).length, align: 'center' },
  { name: 'actions', label: 'Actions', field: 'actions', align: 'right' }
];

// Methods
const init = () => {
    if (props.subjects.length > 0) {
        if (!selectedSubject.value) {
            // Default to first subject if none selected
            selectedSubject.value = props.subjects[0];
        }
      lessonPresentationStore.fetchTemplates(selectedSubject.value);
    }
};

watch(() => props.modelValue, (val) => {
    if (val) init();
});

const openTemplateDialog = (template = null) => {
    if (template) {
        isEditing.value = true;
        currentTemplateId.value = template.id;
        form.value.name = template.name;
        form.value.description = template.description;
        
        // Convert structure object to array for draggable
        templateFields.value = Object.entries(template.structure || {}).map(([key, value]) => ({
            id: Date.now() + key, // simpler unique id
            key: key,
            ...value,
            options: value.options || ''
        }));
    } else {
        isEditing.value = false;
        currentTemplateId.value = null;
        form.value.name = '';
        form.value.description = '';
        templateFields.value = [
            { id: Date.now(), type: 'textarea', label: 'Learning Objectives' },
            { id: Date.now() + 1, type: 'textarea', label: 'Main Activity' }
        ];
    }
    editDialog.value = true;
};

const addField = () => {
    templateFields.value.push({
        id: Date.now(),
        type: 'textarea',
        label: '',
        options: ''
    });
};

const removeField = (index) => {
    templateFields.value.splice(index, 1);
};

const showTemplatePreview = (template) => {
    previewTemplateData.value = template;
    previewDialog.value = true;
};

const submitForm = async () => {
    if (!selectedSubject.value) {
        $q.notify({ type: 'negative', message: 'no selectedSubject  ' });
        return;
    }

    isSubmitting.value = true;
    try {
        const structure = templateFields.value.map(f => {
            const field = {
                type: f.type,
                label: f.label
            };
            if (f.type === 'select') {
                field.options = f.options;
            }
            return field;
        });

        const payload = {
            name: form.value.name,
            description: form.value.description,
            subject_id: selectedSubject.value.id,
            structure: structure,
            is_active: true
        };

        if (isEditing.value && currentTemplateId.value) {
            await axios.put(`/api/course-management/lesson-plan-templates/${currentTemplateId.value}`, payload);
            $q.notify({ type: 'positive', message: 'Template updated' });
        } else {
            await axios.post('/api/course-management/lesson-plan-templates', payload);
            $q.notify({ type: 'positive', message: 'Template created' });
        }

        editDialog.value = false;
        lessonPresentationStore.fetchTemplates(selectedSubject.value);
    } catch (error) {
        console.error('Failed to save', error);
        $q.notify({ type: 'negative', message: 'Failed to save template' });
    } finally {
        isSubmitting.value = false;
    }
};

const confirmDeleteTemplate = (template) => {
    $q.dialog({
        title: 'Delete Template',
        message: `Are you sure you want to delete "${template.name}"?`,
        cancel: true,
        persistent: true
    }).onOk(async () => {
        try {
            await axios.delete(`/api/course-management/lesson-plan-templates/${template.id}`);
            $q.notify({ type: 'positive', message: 'Template deleted' });
            lessonPresentationStore.fetchTemplates(selectedSubject.value);
        } catch (error) {
            console.error('Failed to delete', error);
            $q.notify({ type: 'negative', message: 'Failed to delete template' });
        }
    });
};

onMounted(() => {
    if (props.modelValue) {
        init();
    }
    if (lessonPresentationStore.selectedTemplate) {
        selectedSubject.value = lessonPresentationStore.selectedTemplate.subject_id;
    }
});
</script>

<style scoped>
.ghost {
  opacity: 0.5;
  background: #e3f2fd;
  border: 1px dashed #2196f3;
}
</style>