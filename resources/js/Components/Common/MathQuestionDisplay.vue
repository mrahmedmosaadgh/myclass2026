<template>
  <div>
    <!-- Toggle Button -->
    <button
      @click="showQuestions = !showQuestions"
      class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
    >
      {{ showQuestions ? 'Hide Questions' : 'Show Math Questions' }}
    </button>

    <!-- Questions Display -->
    <div v-if="showQuestions" class="mt-4 p-4 bg-white rounded-lg shadow">
      <div v-if="loading" class="flex justify-center py-4">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
      </div>
      
      <div v-else class="space-y-4">
        <div v-for="(question, index) in formattedQuestions" :key="index" class="p-3 border-b last:border-b-0">
          <div v-html="question" class="katex-preview prose max-w-none"></div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { renderKaTeX } from '@/utils/questionParser';

const props = defineProps({
  questions: {
    type: String,
    default: "Sure! Here are five questions on adding fractions suitable for a 4th-grade level: \n\n1. **Question 1:** What is \\( \\frac{1}{4} + \\frac{2}{4} \\)? \n\n2. **Question 2:** What is \\( \\frac{3}{8} + \\frac{1}{8} \\)? \n\n3. **Question 3:** What is \\( \\frac{2}{5} + \\frac{1}{5} \\)? \n\n4. **Question 4:** What is \\( \\frac{1}{3} + \\frac{2}{3} \\)? \n\n5. **Question 5:** What is \\( \\frac{3}{10} + \\frac{4}{10} \\)? \n\nFeel free to ask if you need help with the answers or explanations!"
  }
});

const showQuestions = ref(false);
const loading = ref(false);
const formattedQuestions = ref([]);

// Process the questions text into an array of formatted HTML
const processQuestions = () => {
  loading.value = true;
  
  try {
    // Split the text by numbered questions (looking for patterns like "1.", "2.", etc.)
    const questionRegex = /\d+\.\s+\*\*Question\s+\d+:\*\*\s+(.*?)(?=\n\n\d+\.|$)/gs;
    const matches = [...props.questions.matchAll(questionRegex)];
    
    // If no matches found, try a more general approach
    if (matches.length === 0) {
      // Split by numbered items
      const lines = props.questions.split(/\n\n/);
      formattedQuestions.value = lines
        .filter(line => line.trim().match(/^\d+\./)) // Only keep numbered lines
        .map(line => renderKaTeX(line));
    } else {
      // Process each matched question
      formattedQuestions.value = matches.map(match => renderKaTeX(match[0]));
    }
  } catch (error) {
    console.error('Error processing questions:', error);
    formattedQuestions.value = [renderKaTeX(props.questions)];
  } finally {
    loading.value = false;
  }
};

// Watch for changes in the questions prop
watch(() => props.questions, () => {
  if (showQuestions.value) {
    processQuestions();
  }
});

// Watch for changes in showQuestions
watch(showQuestions, (newValue) => {
  if (newValue && formattedQuestions.value.length === 0) {
    processQuestions();
  }
});

// Process questions on mount if showQuestions is true
onMounted(() => {
  if (showQuestions.value) {
    processQuestions();
  }
});
</script>

<style scoped>
/* Reusing your existing KaTeX styles */
:deep(.katex-preview) {
  font-size: 1.1em;
  line-height: 1.5;
}

:deep(.katex) {
  font-size: 1.1em;
}

:deep(.katex-display) {
  margin: 1em 0;
  overflow-x: auto;
  overflow-y: hidden;
}

:deep(strong) {
  font-weight: 600;
}

:deep(br) {
  margin-bottom: 0.5em;
  display: block;
  content: "";
}
</style>