<template>
  <q-dialog v-model="isOpen">
    <q-card style="min-width: 500px">
      <q-card-section>
        <div class="text-h6">Create New Lesson</div>
      </q-card-section>
      <q-card-section class="q-gutter-md">
        <q-input v-model="form.name" label="Lesson Name" />
        <q-select
          v-model="form.grade_id"
          :options="gradeOptions"
          label="Grade"
          option-value="id"
          option-label="name"
          emit-value
          map-options
        />
        <q-select
          v-model="form.subject_id"
          :options="subjectOptions"
          label="Subject"
          option-value="id"
          option-label="name"
          emit-value
          map-options
        />
      </q-card-section>
      <q-card-actions align="right">
        <q-btn flat label="Cancel" v-close-popup />
        <q-btn color="primary" label="Create" @click="submit" />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { ref, watch, computed } from 'vue';

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  preselectedGrade: { type: Object, default: null },
  preselectedSubject: { type: Object, default: null }
});
const emit = defineEmits(['update:modelValue', 'submit']);

const isOpen = ref(props.modelValue);
watch(() => props.modelValue, v => (isOpen.value = v));
watch(isOpen, v => emit('update:modelValue', v));

const form = ref({
  name: '',
  grade_id: props.preselectedGrade?.id || null,
  subject_id: props.preselectedSubject?.id || null
});

const gradeOptions = computed(() => (props.preselectedGrade ? [props.preselectedGrade] : []));
const subjectOptions = computed(() => (props.preselectedSubject ? [props.preselectedSubject] : []));

const submit = () => {
  if (!form.value.grade_id || !form.value.subject_id) {
    isOpen.value = false;
    return;
  }
  emit('submit', { ...form.value });
  isOpen.value = false;
};
</script>

<style scoped>
</style>
