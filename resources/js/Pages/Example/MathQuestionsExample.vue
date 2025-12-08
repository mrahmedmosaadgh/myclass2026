<template>
  <div class="p-6 bg-white rounded-lg shadow">
    <h2 class="text-xl font-semibold mb-4">Math Questions Example</h2>
    
    <div class="mb-6">
      <label class="block text-sm font-medium text-gray-700 mb-2">
        Enter Math Questions Text
      </label>
      <textarea
        v-model="questionsText"
        class="w-full h-48 p-3 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500 font-mono text-sm"
        placeholder="Paste your formatted questions here..."
      ></textarea>
    </div>
    
    <div class="flex gap-4">
      <MathQuestionDisplay :questions="questionsText" />
      
      <button
        @click="parseToQuestionBank"
        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
      >
        Parse to Question Bank
      </button>
    </div>
    
    <!-- Preview of parsed questions for question bank -->
    <div v-if="parsedQuestions.length > 0" class="mt-6">
      <h3 class="text-lg font-medium text-gray-900 mb-2">Parsed Questions:</h3>
      <div class="space-y-4">
        <div v-for="(question, index) in parsedQuestions" :key="index" class="p-4 bg-gray-50 rounded-lg">
          <div class="font-medium">Question {{ index + 1 }}</div>
          <div v-html="renderKaTeX(question.body)" class="mt-1"></div>
          
          <div class="mt-2">
            <div class="font-medium">Options:</div>
            <div v-for="(option, optIndex) in question.options" :key="optIndex" class="ml-4 mt-1">
              {{ ['A', 'B', 'C', 'D'][optIndex] }}) <span v-html="renderKaTeX(option.option)"></span>
              <span v-if="option.isCorrect" class="text-green-600 ml-2">(Correct)</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import MathQuestionDisplay from '@/Components/Common/MathQuestionDisplay.vue';
import { renderKaTeX, parseQuestionText } from '@/utils/questionParser';
import { toast } from 'vue3-toastify';

const questionsText = ref("Sure! Here are five questions on adding fractions suitable for a 4th-grade level: \n\n1. **Question 1:** What is \\( \\frac{1}{4} + \\frac{2}{4} \\)? \n\n**A)** \\( \\frac{3}{4} \\) \n**B)** \\( \\frac{3}{8} \\) \n**C)** \\( 1 \\) \n**D)** \\( \\frac{1}{2} \\) \n\n2. **Question 2:** What is \\( \\frac{3}{8} + \\frac{1}{8} \\)? \n\n**A)** \\( \\frac{4}{8} \\) \n**B)** \\( \\frac{4}{16} \\) \n**C)** \\( \\frac{1}{2} \\) \n**D)** \\( \\frac{3}{4} \\) \n\n3. **Question 3:** What is \\( \\frac{2}{5} + \\frac{1}{5} \\)? \n\n**A)** \\( \\frac{3}{10} \\) \n**B)** \\( \\frac{3}{5} \\) \n**C)** \\( \\frac{2}{5} \\) \n**D)** \\( 1 \\) \n\n4. **Question 4:** What is \\( \\frac{1}{3} + \\frac{2}{3} \\)? \n\n**A)** \\( \\frac{3}{6} \\) \n**B)** \\( \\frac{3}{3} \\) \n**C)** \\( 1 \\) \n**D)** \\( 3 \\) \n\n5. **Question 5:** What is \\( \\frac{3}{10} + \\frac{4}{10} \\)? \n\n**A)** \\( \\frac{7}{10} \\) \n**B)** \\( \\frac{7}{20} \\) \n**C)** \\( \\frac{7}{100} \\) \n**D)** \\( \\frac{3}{5} \\)");

const parsedQuestions = ref([]);

const parseToQuestionBank = () => {
  try {
    // Split the text by numbered questions
    const questionRegex = /(\d+)\.\s+\*\*Question\s+\d+:\*\*(.*?)(?=\n\n\d+\.|$)/gs;
    const matches = [...questionsText.value.matchAll(questionRegex)];
    
    parsedQuestions.value = [];
    
    for (const match of matches) {
      const questionNumber = match[1];
      const questionText = match[2];
      
      // Extract the question body (everything before the first "A)")
      const questionBody = questionText.trim();
      
      // Find all options for this question
      const optionRegex = /\*\*([A-D])\)\*\*\s*(.*?)(?=\n\*\*[A-D]\)\*\*|$)/gs;
      const optionMatches = [...questionText.matchAll(optionRegex)];
      
      const options = optionMatches.map(optMatch => ({
        option: optMatch[2].trim(),
        feedback: '',
        isCorrect: optMatch[1] === 'A' // Set first option as correct by default
      }));
      
      if (options.length > 0) {
        parsedQuestions.value.push({
          body: questionBody,
          options: options
        });
      }
    }
    
    if (parsedQuestions.value.length === 0) {
      // If no questions were parsed, try using the existing parser
      const result = parseQuestionText(questionsText.value);
      if (result.success) {
        parsedQuestions.value = [result.data];
      } else {
        throw new Error(result.error);
      }
    }
    
    toast.success(`Successfully parsed ${parsedQuestions.value.length} questions`);
  } catch (error) {
    console.error('Error parsing questions:', error);
    toast.error('Failed to parse questions: ' + error.message);
  }
};
</script>

<style scoped>
/* Reusing your existing KaTeX styles */
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
</style>