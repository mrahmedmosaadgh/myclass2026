<template>
  <div class="q-pa-md">
    <q-card flat bordered >
      <q-card-section>
        <div class="text-h6">jet_json_test</div>
        
    <details open>{{    questions  }}</details>

         <q-btn label="questions"  @click="fetchQuestions" /> 
         
      </q-card-section>

      <q-separator />
</q-card>
 
  </div>
</template>

<script setup>
import { ref } from 'vue'
 
import { useQuasar, copyToClipboard } from 'quasar'
const emit=defineEmits(['data'])
const $q = useQuasar()

 
const questions = ref( [])
async function fetchQuestions() {
    
  const res = await axios.get('/teacher/getTeacherClasses');
    console.log(res.data);
 
//  questions.value =JSON.stringify(res.data);
  questions.value = res.data;
  emit('data', questions.value);
}
</script>
