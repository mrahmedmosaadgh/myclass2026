<template>
  <div class="p-6 bg-gray-50 min-h-screen">
    <div class="max-w-6xl mx-auto">
      <div class="flex items-center justify-between mb-6">
        <div>
          <h1 class="text-2xl font-bold">Lesson Section Templates</h1>
          <p class="text-gray-600 mt-1">Manage section templates for your lessons</p>
        </div>
        <q-btn unelevated color="primary" icon="add" label="New Template" @click="createNew" />
      </div>

      <div class="grid gap-4">
        <q-card v-for="template in templates" :key="template.id" flat bordered>
          <q-card-section class="flex items-start justify-between">
            <div class="flex-1">
              <div class="flex items-center gap-2 mb-2">
                <h3 class="text-lg font-semibold">{{ template.name }}</h3>
                <q-badge v-if="template.is_active" color="positive" label="Active" />
              </div>
              
              <div class="flex flex-wrap gap-2">
                <q-chip
                  v-for="section in template.structure?.sections || []"
                  :key="section.id"
                  :style="{ 
                    backgroundColor: section.bg || '#f3f4f6',
                    color: section.textColor || '#1f2937',
                    borderColor: section.borderColor || '#e5e7eb'
                  }"
                  dense
                  class="border"
                >
                  <span class="mr-1">{{ section.icon }}</span>
                  {{ section.title }}
                </q-chip>
              </div>
            </div>

            <div class="flex gap-2 ml-4">
              <q-btn flat dense round icon="edit" color="primary" @click="editTemplate(template)">
                <q-tooltip>Edit</q-tooltip>
              </q-btn>
              <q-btn v-if="!template.is_active" flat dense round icon="check_circle" color="positive" @click="setActive(template.id)">
                <q-tooltip>Set as Active</q-tooltip>
              </q-btn>
              <q-btn v-if="!template.is_active" flat dense round icon="delete" color="negative" @click="deleteTemplate(template.id)">
                <q-tooltip>Delete</q-tooltip>
              </q-btn>
            </div>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <!-- Editor Dialog -->
    <q-dialog v-model="showEditor" persistent maximized>
      <q-card>
        <q-card-section class="bg-primary text-white row items-center">
          <div class="text-h6">{{ editingTemplate.id ? 'Edit' : 'Create' }} Template</div>
          <q-space />
          <q-btn flat round dense icon="close" v-close-popup />
        </q-card-section>

        <q-card-section class="q-pa-md">
          <div class="max-w-4xl mx-auto">
            <q-input v-model="editingTemplate.name" label="Template Name *" outlined dense class="mb-6" />

            <div class="mb-4 flex items-center justify-between">
              <h3 class="text-lg font-semibold">Sections</h3>
              <q-btn flat dense color="primary" icon="add" label="Add Section" @click="addSection" />
            </div>

            <draggable v-model="editingTemplate.structure.sections" handle=".drag-handle" item-key="id">
              <template #item="{ element: section, index }">
                <q-card flat bordered class="mb-3">
                  <q-card-section class="flex items-start gap-4">
                    <q-icon name="drag_indicator" class="drag-handle cursor-move text-gray-400" size="24px" />

                    <div class="flex-1 grid grid-cols-2 gap-3">
                      <q-input v-model="section.id" label="Section ID *" dense outlined />
                      <q-input v-model="section.title" label="Title *" dense outlined />
                      <q-input v-model="section.icon" label="Emoji Icon" dense outlined />
                      <q-input v-model="section.qIcon" label="Quasar Icon" dense outlined />
                      <q-input v-model="section.bg" label="Background Color" dense outlined :style="{ backgroundColor: section.bg }">
                        <template v-slot:append>
                          <q-icon name="colorize" class="cursor-pointer">
                            <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                              <q-color v-model="section.bg" />
                            </q-popup-proxy>
                          </q-icon>
                        </template>
                      </q-input>
                      <q-input v-model="section.bgActive" label="Active BG" dense outlined :style="{ backgroundColor: section.bgActive }">
                        <template v-slot:append>
                          <q-icon name="colorize" class="cursor-pointer">
                            <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                              <q-color v-model="section.bgActive" />
                            </q-popup-proxy>
                          </q-icon>
                        </template>
                      </q-input>
                      <q-input v-model="section.borderColor" label="Border Color" dense outlined :style="{ backgroundColor: section.borderColor }">
                        <template v-slot:append>
                          <q-icon name="colorize" class="cursor-pointer">
                            <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                              <q-color v-model="section.borderColor" />
                            </q-popup-proxy>
                          </q-icon>
                        </template>
                      </q-input>
                      <q-input v-model="section.textColor" label="Text Color" dense outlined :style="{ color: section.textColor }">
                        <template v-slot:append>
                          <q-icon name="colorize" class="cursor-pointer">
                            <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                              <q-color v-model="section.textColor" />
                            </q-popup-proxy>
                          </q-icon>
                        </template>
                      </q-input>
                    </div>

                    <q-btn flat dense round icon="delete" color="negative" @click="removeSection(index)" />
                  </q-card-section>
                </q-card>
              </template>
            </draggable>
          </div>
        </q-card-section>

        <q-card-actions align="right" class="border-t">
          <q-btn flat label="Cancel" @click="showEditor = false" />
          <q-btn unelevated color="primary" label="Save" @click="saveTemplate" :loading="isSaving" />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useQuasar } from 'quasar';
import axios from 'axios';
import draggable from 'vuedraggable';

const $q = useQuasar();
const templates = ref([]);
const showEditor = ref(false);
const isSaving = ref(false);
const editingTemplate = ref({ name: '', structure: { sections: [] } });

const fetchTemplates = async () => {
  try {
    const response = await axios.get(route('lesson-presentation.section-templates.index'));
    templates.value = response.data;
  } catch (error) {
    console.error(error);
    $q.notify({ type: 'negative', message: 'Failed to load templates' });
  }
};

const createNew = () => {
  editingTemplate.value = {
    name: 'New Template',
    structure: {
      sections: [
        { id: 'objectives', title: 'Objectives', icon: '🎯', qIcon: 'flag', 
          bg: '#fffbeb', bgActive: '#fef3c7', borderColor: '#f59e0b', textColor: '#92400e' }
      ]
    }
  };
  showEditor.value = true;
};

const editTemplate = (template) => {
  editingTemplate.value = JSON.parse(JSON.stringify(template));
  if (!editingTemplate.value.structure) {
      editingTemplate.value.structure = { sections: [] };
  }
  showEditor.value = true;
};

const addSection = () => {
  editingTemplate.value.structure.sections.push({
    id: `section_${Date.now()}`,
    title: 'New Section',
    icon: '📝',
    qIcon: 'article',
    bg: '#f3f4f6',
    bgActive: '#e5e7eb',
    borderColor: '#9ca3af',
    textColor: '#1f2937'
  });
};

const removeSection = (index) => {
  editingTemplate.value.structure.sections.splice(index, 1);
};

const saveTemplate = async () => {
  if (!editingTemplate.value.name || !editingTemplate.value.structure.sections || editingTemplate.value.structure.sections.length === 0) {
    $q.notify({ type: 'warning', message: 'Name and at least one section required' });
    return;
  }

  isSaving.value = true;
  try {
    if (editingTemplate.value.id) {
      await axios.put(route('lesson-presentation.section-templates.update', { id: editingTemplate.value.id }), editingTemplate.value);
    } else {
      await axios.post(route('lesson-presentation.section-templates.store'), editingTemplate.value);
    }

    $q.notify({ type: 'positive', message: 'Template saved successfully' });
    showEditor.value = false;
    fetchTemplates();
  } catch (error) {
    console.error(error);
    $q.notify({ type: 'negative', message: 'Failed to save template' });
  } finally {
    isSaving.value = false;
  }
};

const setActive = async (id) => {
  try {
    await axios.post(route('lesson-presentation.section-templates.set-active', { id }));
    $q.notify({ type: 'positive', message: 'Active template updated' });
    fetchTemplates();
  } catch (error) {
    console.error(error);
    $q.notify({ type: 'negative', message: 'Failed to set active' });
  }
};

const deleteTemplate = async (id) => {
  $q.dialog({
    title: 'Confirm Delete',
    message: 'Are you sure?',
    cancel: true
  }).onOk(async () => {
    try {
      await axios.delete(route('lesson-presentation.section-templates.destroy', { id }));
      $q.notify({ type: 'positive', message: 'Template deleted' });
      fetchTemplates();
    } catch (error) {
      console.error(error);
      $q.notify({ type: 'negative', message: 'Failed to delete' });
    }
  });
};

onMounted(() => {
  fetchTemplates();
});
</script>
