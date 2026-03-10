<template>
  <q-card class="q-pa-md">
    <div class="row items-center q-col-gutter-md">
      <div class="col-12 col-md-3">
        <q-select
          dense
          outlined
          label="Teacher"
          v-model="form.teacher_id"
          :options="teacherOptions"
          option-label="name"
          option-value="id"
          emit-value
          map-options
        />
      </div>

      <div class="col-12 col-md-2">
        <q-select
          dense
          outlined
          label="Subject"
          v-model="form.subject_id"
          :options="subjectOptions"
          option-label="name"
          option-value="id"
          emit-value
          map-options
        />
      </div>

      <div class="col-12 col-md-2">
        <q-select
          dense
          outlined
          label="Classroom"
          v-model="form.classroom_id"
          :options="classroomOptions"
          option-label="name"
          option-value="id"
          emit-value
          map-options
        />
      </div>

      <div class="col-12 col-md-2">
        <q-input dense outlined label="Period code" v-model="form.period_code" />
      </div>

      <div class="col-12 col-md-2">
        <q-input dense outlined label="Date" v-model="form.date" mask="####-##-##" placeholder="YYYY-MM-DD" />
      </div>

      <div class="col-12 col-md-1">
        <q-btn color="primary" label="Load" @click="apply" />
      </div>
    </div>
  </q-card>
</template>

<script setup>
import { reactive, toRaw,ref,onMounted  } from 'vue';
import { useQuasar } from 'quasar';
import axios from 'axios';

const $q = useQuasar();
const emit = defineEmits(['filters-selected']);
// Props could be added if parent passes lists.
const teacherOptions = ref([]);
const subjectOptions = ref([]);
const classroomOptions = ref([]);

// Fetch options on mount
onMounted(async () => {
  try {
    const [t,s,c] = await Promise.all([
      axios.get('/api/teachers'),
      axios.get('/api/subjects'),
      axios.get('/api/classrooms')
    ]);
    teacherOptions.value = t.data;
    subjectOptions.value = s.data;
    classroomOptions.value = c.data;
  } catch (e) {
    console.error(e);
  }
});

const form = reactive({
  teacher_id: null,
  subject_id: null,
  classroom_id: null,
  period_code: '',
  date: ''
});

function apply(){
  // emit a single object with required data
  const payload = {
    teacher_id: form.teacher_id,
    subject_id: form.subject_id,
    classroom_id: form.classroom_id,
    period_code: form.period_code,
    date: form.date
  };
  // emit using a custom event for parent
  // in script setup, define emit
  emit('filters-selected', toRaw(payload));
}
</script>
