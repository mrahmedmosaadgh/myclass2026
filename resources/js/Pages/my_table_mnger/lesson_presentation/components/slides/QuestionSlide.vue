<template>
  <div class="min-h-full flex flex-col">
    <!-- Editor Mode: Show question editors -->
    <div v-if="mode === 'edit'" class="flex-1 space-y-4 p-1">
      <div v-if="!modelValue.questions || modelValue.questions.length === 0" class="text-center py-10 text-gray-500">
        No questions added yet. Click "Add Question" to get started.
      </div>
      
      <EnhancedQuestionEditor
        v-for="(question, index) in (modelValue.questions || [])"
        :key="question.id || index"
        :modelValue="question"
        @update:modelValue="updateQuestion(index, $event)"
        @delete="deleteQuestion(index)"
        :uniqueId="question.id || `q_${index}`"
      />
      
      <div class="mt-4 pt-4 border-t border-gray-200">
        <div class="grid grid-cols-2 gap-4">
          <button 
            @click="showTypeSelector = true"
            class="py-3 border-2 border-dashed border-blue-300 text-blue-600 rounded-lg hover:bg-blue-50 transition-colors font-medium flex items-center justify-center gap-2"
          >
            <i class="fas fa-plus-circle"></i> Add Question
          </button>
          
          <button 
            @click="showAIDialog = true"
            class="py-3 border-2 border-dashed border-purple-300 text-purple-600 rounded-lg hover:bg-purple-50 transition-colors font-medium flex items-center justify-center gap-2"
          >
            <q-icon name="auto_awesome" /> AI Generate
          </button>
        </div>
      </div>

      <!-- AI Generator Dialog -->
      <QuAIGeneratorDialog
        v-model="showAIDialog"
        :emit-data-only="true"
        :subject-name="context?.subject_name"
        :grade-name="context?.grade_name"
        @imported="handleAIQuestions"
      />

      <!-- Question Type Selector Modal -->
      <div v-if="showTypeSelector" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
          <div class="p-6">
            <QuestionTypeSelector
              @select="addQuestionWithType"
              @cancel="showTypeSelector = false"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Player Mode: Show QuizEngine for compatible questions -->
    <div v-else-if="mode === 'play' && hasCompatibleQuestions" class="flex-1">
      <QuizEngine
        :quiz="convertedQuestions"
        :config="quizConfig"
        :attemptId="attemptId"
        @answer-selected="handleAnswerSelected"
        @question-changed="handleQuestionChanged"
        @quiz-completed="handleQuizCompleted"
      />
    </div>

    <!-- Player Mode: Fallback to legacy player for incompatible questions -->
    <div v-else-if="mode === 'play'" class="flex-1 space-y-8">
      <UniversalQuestionPlayer
        v-for="(question, qIndex) in (modelValue.questions || [])"
        :key="question.id || qIndex"
        :question="question"
        v-model:answer="legacyAnswers[question.id]"
        v-model:attempts="legacyAttempts[question.id]"
        v-model:solved="legacySolved[question.id]"
        v-model:submitted="legacySubmitted[question.id]"
        :mode="legacyMode"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import EnhancedQuestionEditor from '../questions/EnhancedQuestionEditor.vue';
import QuestionTypeSelector from '../questions/QuestionTypeSelector.vue';
import QuizEngine from '../../quiz/QuizEngine.vue';
import UniversalQuestionPlayer from '@/Components/QuestionSystem/UniversalQuestionPlayer.vue';
import QuAIGeneratorDialog from '@/Pages/my_class/QuQuestionBankSystem/QuAIGeneratorDialog.vue';
import { useQuasar } from 'quasar';

const props = defineProps({
  modelValue: {
    type: Object,
    default: () => ({ questions: [] })
  },
  mode: {
    type: String,
    default: 'edit', // 'edit' or 'play'
    validator: (value) => ['edit', 'play'].includes(value)
  },
  quizConfig: {
    type: Object,
    default: () => ({
      allowReviewMode: false,
      autoAdvance: false,
      showRationaleOnCorrect: true
    })
  },
  attemptId: {
    type: [String, Number],
    default: undefined
  },
  legacyMode: {
    type: String,
    default: 'learn'
  },
  context: {
    type: Object,
    default: () => ({})
  }
});

const emit = defineEmits(['update:modelValue', 'answer-selected', 'quiz-completed']);
const $q = useQuasar();

const showTypeSelector = ref(false);
const showAIDialog = ref(false);
const legacyAnswers = ref({});
const legacyAttempts = ref({});
const legacySolved = ref({});
const legacySubmitted = ref({});

/**
 * Check if questions are compatible with QuizEngine
 * QuizEngine supports: single_choice, multiple_choice, true_false
 */
const hasCompatibleQuestions = computed(() => {
  const questions = props.modelValue.questions || [];
  if (questions.length === 0) return false;
  
  const compatibleTypes = ['single_choice', 'multiple_choice', 'true_false'];
  return questions.every(q => compatibleTypes.includes(q.type));
});

/**
 * Convert legacy question format to QuizEngine format
 */
const convertedQuestions = computed(() => {
  const questions = props.modelValue.questions || [];
  
  return questions.map((question, index) => {
    // Map question type to QuestionType object
    const questionType = {
      id: getQuestionTypeId(question.type),
      slug: question.type,
      name: getQuestionTypeName(question.type),
      hasOptions: ['single_choice', 'multiple_choice', 'true_false'].includes(question.type),
      supportsHints: true,
      supportsExplanation: true
    };

    // Convert options to AnswerOption format
    let answerOptions = [];
    
    if (question.type === 'single_choice' || question.type === 'multiple_choice') {
      answerOptions = (question.options || []).map((option, optIndex) => {
        const isCorrect = question.type === 'single_choice'
          ? question.correct_answer === option.id
          : (question.correct_answer || []).includes(option.id);
        
        return {
          id: option.id || `opt_${optIndex}`,
          text: option.text || '',
          isCorrect: isCorrect,
          rationale: option.rationale || undefined
        };
      });
    } else if (question.type === 'true_false') {
      answerOptions = [
        {
          id: 'true',
          text: 'True',
          isCorrect: question.correct_answer === true,
          rationale: undefined
        },
        {
          id: 'false',
          text: 'False',
          isCorrect: question.correct_answer === false,
          rationale: undefined
        }
      ];
    }

    return {
      id: question.id || `q_${index}`,
      questionNumber: index + 1,
      questionTypeId: questionType.id,
      questionType: questionType,
      question: question.text || question.question || '',
      answerOptions: answerOptions,
      explanation: question.explanation || undefined,
      hints: question.hints || undefined,
      bloomLevel: question.bloomLevel || undefined,
      difficultyLevel: question.difficultyLevel || undefined,
      estimatedTimeSec: question.timer || undefined,
      metadata: {
        originalType: question.type,
        points: question.points || 0
      }
    };
  });
});

/**
 * Helper function to get question type ID
 */
const getQuestionTypeId = (type) => {
  const typeMap = {
    'single_choice': 1,
    'multiple_choice': 2,
    'true_false': 3,
    'fill_blank': 4,
    'short_answer': 5,
    'essay': 6
  };
  return typeMap[type] || 1;
};

/**
 * Helper function to get question type name
 */
const getQuestionTypeName = (type) => {
  const nameMap = {
    'single_choice': 'Multiple Choice',
    'multiple_choice': 'Multi-Select',
    'true_false': 'True/False',
    'fill_blank': 'Fill in the Blank',
    'short_answer': 'Short Answer',
    'essay': 'Essay'
  };
  return nameMap[type] || 'Unknown';
};

/**
 * Handle answer selection from QuizEngine
 */
const handleAnswerSelected = (record) => {
  emit('answer-selected', record);
};

/**
 * Handle question change from QuizEngine
 */
const handleQuestionChanged = (index) => {
  // Can be used for analytics or progress tracking
  console.log('Question changed to:', index);
};

/**
 * Handle quiz completion from QuizEngine
 */
const handleQuizCompleted = (result) => {
  emit('quiz-completed', result);
};

/**
 * Update a question in the editor
 */
const updateQuestion = (index, newQuestion) => {
  const questions = [...(props.modelValue.questions || [])];
  questions[index] = newQuestion;
  emit('update:modelValue', { ...props.modelValue, questions });
};

/**
 * Delete a question from the editor
 */
const deleteQuestion = (index) => {
  const questions = [...(props.modelValue.questions || [])];
  questions.splice(index, 1);
  emit('update:modelValue', { ...props.modelValue, questions });
};

/**
 * Add a new question with the selected type
 */
const addQuestionWithType = (type) => {
  const questions = props.modelValue.questions || [];
  const newId = 'q_' + Math.random().toString(36).substr(2, 6);
  
  const newQuestion = {
    id: newId,
    type: type.value,
    active: true,
  };

  // Add type-specific defaults
  if (type.category === 'classic') {
    // Classic question types
    newQuestion.text = '';
    newQuestion.timer = 0;
    
    if (type.value === 'single_choice' || type.value === 'multiple_choice') {
      newQuestion.options = [
        { id: 'opt_1', text: 'Option 1' },
        { id: 'opt_2', text: 'Option 2' }
      ];
      newQuestion.correct_answer = type.value === 'single_choice' ? null : [];
    } else if (type.value === 'true_false') {
      newQuestion.correct_answer = true;
    } else if (type.value === 'step_by_step') {
      newQuestion.steps = [];
    }
  } else {
    // New interactive question types
    newQuestion.title = '';
    newQuestion.description = '';
    newQuestion.points = 10;
    newQuestion.timeLimit = 0;
    newQuestion.version = 'default';

    if (type.value === 'labelled-diagram') {
      newQuestion.imageUrl = '';
      newQuestion.labels = [];
      newQuestion.instructions = 'Click on each point to label it';
    } else if (type.value === 'match-up') {
      newQuestion.pairs = [];
      newQuestion.shuffleRight = true;
    } else if (type.value === 'speaking-cards') {
      newQuestion.cards = [];
      newQuestion.recordingRequired = false;
    } else if (type.value === 'image-quiz') {
      newQuestion.question = '';
      newQuestion.options = [];
    } else if (type.value === 'group-sort') {
      newQuestion.categories = [];
      newQuestion.items = [];
    } else if (type.value === 'sequence') {
      newQuestion.items = [];
    } else if (type.value === 'missing-word') {
      newQuestion.sentence = '';
      newQuestion.blanks = [];
    } else if (type.value === 'anagram') {
      newQuestion.scrambledWord = '';
      newQuestion.correctWord = '';
    }
  }
  
  questions.push(newQuestion);
  emit('update:modelValue', { ...props.modelValue, questions });
  showTypeSelector.value = false;
};

const handleAIQuestions = (aiQuestions) => {
  const newQuestions = aiQuestions.map((q, index) => {
    // Generate base question structure
    const id = 'q_' + Math.random().toString(36).substr(2, 6) + '_' + index;
    
    // Determine type
    let type = 'single_choice';
    // AI Types: mcq, true_false, short, long
    if (q.question_type === 'mcq') {
      type = q.correct_answer && q.correct_answer.length > 1 ? 'multiple_choice' : 'single_choice';
    } else if (q.question_type === 'true_false') {
      type = 'true_false';
    } else if (q.question_type === 'short') {
      type = 'short_answer';
    } else if (q.question_type === 'long') {
      type = 'essay';
    }

    // Map content
    const mapped = {
      id: id,
      type: type,
      text: q.question_text || '',
      points: q.marks || 1,
      explanation: q.explanation || '', // Assuming AI might provide this later
      active: true,
      difficulty: q.difficulty,
      bloom_level: q.bloom_level
    };

    // Handle Options
    if (['single_choice', 'multiple_choice'].includes(type)) {
      if (q.options && typeof q.options === 'object') {
        mapped.options = Object.keys(q.options).map(key => ({
          id: key,
          text: q.options[key]
        }));
      } else {
        mapped.options = [
          { id: 'A', text: 'Option A' },
          { id: 'B', text: 'Option B' }
        ];
      }
      
      mapped.correct_answer = type === 'single_choice' 
        ? (q.correct_answer && q.correct_answer[0] ? q.correct_answer[0] : null)
        : (q.correct_answer || []);
        
    } else if (type === 'true_false') {
      // AI usually returns ["true"] or ["false"]
      const correctKey = q.correct_answer && q.correct_answer[0] ? q.correct_answer[0].toString().toLowerCase() : 'true';
      mapped.correct_answer = correctKey === 'true';
    }

    return mapped;
  });

  const questions = [...(props.modelValue.questions || []), ...newQuestions];
  emit('update:modelValue', { ...props.modelValue, questions });
  
  $q.notify({
    type: 'positive',
    message: `Added ${newQuestions.length} AI generated questions`,
    icon: 'auto_awesome',
    position: 'top'
  });
};
</script>

<style scoped>
/* Modal animations */
.fixed {
  animation: fadeIn 0.2s ease-in-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}
</style>
