<template>
  <div>
    <q-card flat>
      <q-card-section>
        <q-input v-model="question.questionText" label="Question text" />
        <q-select v-model="question.type" :options="['MultipleChoice','TrueFalse','OpenEnded']" label="Type" dense />
        <div v-if="question.type==='MultipleChoice'" class="q-mt-md">
          <div v-for="(opt, i) in question.options" :key="i" class="row items-center q-gutter-sm">
            <q-input v-model="question.options[i]" dense />
            <q-btn dense flat icon="close" @click="question.options.splice(i,1)" />
          </div>
          <q-btn flat dense label="Add Option" @click="question.options.push('')" />
          <q-input v-model.number="question.correctIndex" type="number" label="Correct Index (0-based)" dense class="q-mt-sm" />
        </div>

        <div v-if="question.type==='TrueFalse'">
          <q-toggle v-model="question.correct" label="Correct is True" />
        </div>

        <div class="q-mt-md">
          <q-input v-model.number="question.maxPoints" type="number" label="Max Points" dense />
        </div>

      </q-card-section>
    </q-card>
  </div>
</template>

<script setup>
const props = defineProps({ question: Object })
</script>
