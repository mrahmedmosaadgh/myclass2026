<template>
  <q-card class="curriculum-form-card">
    <q-card-section>
      <div class="text-h6 text-weight-bold q-mb-md">{{ title }}</div>
      
      <div class="row q-col-gutter-md">
        <!-- Curriculum Name -->
        <div class="col-12">
          <q-input
            v-model="formData.name"
            label="Curriculum Name *"
            outlined
            dense
            :error="!!errors.name"
            :error-message="errors.name"
          />
        </div>
        
        <!-- Grade Selection -->
        <div class="col-12 col-md-6">
          <q-select
            v-model="formData.grade_id"
            :options="gradeOptions"
            option-value="id"
            option-label="name"
            emit-value
            map-options
            outlined
            dense
            label="Grade *"
            :error="!!errors.grade_id"
            :error-message="errors.grade_id"
          />
        </div>
        
        <!-- Subject Selection -->
        <div class="col-12 col-md-6">
          <q-select
            v-model="formData.subject_id"
            :options="subjectOptions"
            option-value="id"
            option-label="name"
            emit-value
            map-options
            outlined
            dense
            label="Subject *"
            :error="!!errors.subject_id"
            :error-message="errors.subject_id"
          />
        </div>
        
        <!-- Description -->
        <div class="col-12">
          <q-input
            v-model="formData.description"
            label="Description"
            type="textarea"
            outlined
            rows="3"
          />
        </div>
        
        <!-- Lock Date -->
        <div class="col-12">
          <q-input
            v-model="formData.edit_lock_date"
            label="Edit Lock Date"
            type="date"
            outlined
            dense
            hint="After this date, teachers cannot edit this curriculum"
          >
            <template v-slot:prepend>
              <q-icon name="event" class="cursor-pointer">
                <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                  <q-date v-model="formData.edit_lock_date">
                    <div class="row items-center justify-end">
                      <q-btn v-close-popup label="Close" color="primary" flat />
                    </div>
                  </q-date>
                </q-popup-proxy>
              </q-icon>
            </template>
          </q-input>
        </div>
      </div>
    </q-card-section>
    
    <q-separator />
    
    <q-card-actions align="right">
      <q-btn flat label="Cancel" color="grey" v-close-popup />
      <q-btn 
        flat 
        :label="submitLabel" 
        color="primary" 
        :loading="loading"
        @click="$emit('submit', formData)"
      />
    </q-card-actions>
  </q-card>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  title: {
    type: String,
    default: 'Create Curriculum'
  },
  submitLabel: {
    type: String,
    default: 'Create'
  },
  initialData: {
    type: Object,
    default: () => ({
      name: '',
      grade_id: null,
      subject_id: null,
      description: '',
      edit_lock_date: null
    })
  },
  gradeOptions: {
    type: Array,
    default: () => []
  },
  subjectOptions: {
    type: Array,
    default: () => []
  },
  errors: {
    type: Object,
    default: () => ({})
  },
  loading: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['submit', 'close'])

const formData = ref({ ...props.initialData })

watch(() => props.initialData, (newData) => {
  formData.value = { ...newData }
}, { deep: true })
</script>

<style scoped>
.curriculum-form-card {
  min-width: 500px;
}
</style>
