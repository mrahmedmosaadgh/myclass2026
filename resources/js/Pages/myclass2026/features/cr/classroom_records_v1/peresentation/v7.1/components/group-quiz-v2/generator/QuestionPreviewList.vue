<script setup>
import QuestionPreviewCard from './QuestionPreviewCard.vue';

const props = defineProps({
  questions: { type: Array, required: true }
});

const emit = defineEmits(['submit', 'remove']);
</script>

<template>
  <div class="question-preview-list">
    <div class="row items-center q-mb-sm">
      <div class="text-subtitle2 text-weight-bold text-grey-8">
        <q-icon name="preview" size="18px" class="q-mr-xs" />
        Preview ({{ questions.length }} Questions)
      </div>
      <q-space />
    </div>

    <div class="scroll-area">
      <QuestionPreviewCard
        v-for="(q, idx) in questions"
        :key="idx"
        :question="q"
        :index="idx"
        @remove="$emit('remove', idx)"
      />
    </div>

    <q-btn
      color="positive"
      icon="check"
      label="Confirm & Add to Presentation"
      no-caps
      unelevated
      class="full-width q-mt-md"
      @click="$emit('submit')"
    />
  </div>
</template>

<style scoped>
.question-preview-list {
  max-height: 60vh;
  display: flex;
  flex-direction: column;
}
.scroll-area {
  flex: 1;
  overflow-y: auto;
  padding-right: 4px;
}
</style>
