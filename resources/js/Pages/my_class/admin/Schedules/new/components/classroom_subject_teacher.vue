<template>
<div class="p-0 space-y-4">
  <schools v-if="userRole" class="w-96"
    :role="userRole"
    :teacher-id="teacherId"
    @selected_school="handleSchoolSelected"
  />

  <load_classroom_subject_teacher
    v-if="school_id"
    :school-id="school_id"
    class="mt-4"
  />


</div>
</template>

<script setup>
import schools from './schools.vue'
import load_classroom_subject_teacher from './load_classroom_subject_teacher.vue'
import { ref } from 'vue'
 
const userRole = ref(localStorage.getItem('selectedRole'))
const teacherId = ref(parseInt(localStorage.getItem('user_id')))
const school_id = ref(null)

const handleSchoolSelected = (id) => {
  school_id.value = id
  localStorage.setItem('selected_school', id)
}
</script>
