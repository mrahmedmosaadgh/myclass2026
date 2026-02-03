<template>
    <div class="skill-practice-session bg-gray-50 min-h-screen">
        <div class="max-w-4xl mx-auto px-4 py-8">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">{{ skill.name }}</h1>
                <p class="text-gray-600 mt-2">{{ skill.description }}</p>
                
                <!-- SmartScore Bar -->
                <div class="mt-4">
                    <SmartScoreBar :score="userProgress.smart_score" :mastery-level="userProgress.mastery_level" />
                </div>
                
                <!-- Question Counter -->
                <div class="mt-4">
                    <QuestionCounter 
                        :answered="sessionStats.answered" 
                        :streak="sessionStats.streak" 
                        :accuracy="sessionStats.accuracy"
                    />
                </div>
            </div>

            <!-- Loading State -->
            <div v-if="loading" class="flex justify-center items-center py-12">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500"></div>
                <span class="ml-3 text-lg text-gray-600">Loading next question...</span>
            </div>

            <!-- Error State -->
            <div v-else-if="error" class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-700">{{ error }}</p>
                    </div>
                </div>
            </div>

            <!-- Question Area -->
            <div v-else-if="currentQuestion" class="bg-white rounded-lg shadow-md p-6 mb-6">
                <div class="question-display mb-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Question {{ sessionStats.answered + 1 }}</h2>
                    <div v-html="currentQuestion.question.question_text" class="text-gray-700 mb-4"></div>
                    
                    <!-- Question Image if exists -->
                    <div v-if="currentQuestion.question.question_image" class="mb-4">
                        <img :src="currentQuestion.question.question_image" alt="Question illustration" class="max-w-full h-auto rounded border">
                    </div>
                </div>

                <!-- Answer Options -->
                <div class="answer-options mb-6">
                    <div 
                        v-for="(option, index) in currentQuestion.question.options" 
                        :key="index"
                        class="mb-3"
                    >
                        <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50 transition-colors"
                               :class="{ 'border-blue-500 bg-blue-50': selectedAnswer === option, 'border-gray-300': selectedAnswer !== option }"
                        >
                            <input 
                                type="radio" 
                                :value="option" 
                                v-model="selectedAnswer"
                                :disabled="showingFeedback"
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500"
                            >
                            <span class="ml-3 text-gray-700">{{ option }}</span>
                        </label>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-between items-center">
                    <button
                        @click="endSession"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
                        :disabled="showingFeedback"
                    >
                        End Session
                    </button>
                    
                    <button
                        @click="submitAnswer"
                        :disabled="!selectedAnswer || showingFeedback"
                        class="px-6 py-2 text-base font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        {{ showingFeedback ? 'Processing...' : 'Submit Answer' }}
                    </button>
                </div>
            </div>

            <!-- Session Complete -->
            <div v-else-if="sessionComplete" class="bg-white rounded-lg shadow-md p-6 text-center">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Session Complete!</h2>
                <p class="text-gray-600 mb-6">Great job completing this practice session.</p>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <p class="text-2xl font-bold text-blue-700">{{ sessionStats.answered }}</p>
                        <p class="text-gray-600">Questions Answered</p>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg">
                        <p class="text-2xl font-bold text-green-700">{{ sessionStats.correct }}</p>
                        <p class="text-gray-600">Correct Answers</p>
                    </div>
                    <div class="bg-purple-50 p-4 rounded-lg">
                        <p class="text-2xl font-bold text-purple-700">{{ Math.round(sessionStats.accuracy) }}%</p>
                        <p class="text-gray-600">Accuracy</p>
                    </div>
                </div>
                
                <div class="flex justify-center space-x-4">
                    <Link :href="`/skill-practice/skills/${skill.id}/start`" 
                          class="px-6 py-3 text-base font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">
                        Practice Again
                    </Link>
                    <Link :href="`/skill-practice`" 
                          class="px-6 py-3 text-base font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">
                        Browse Skills
                    </Link>
                </div>
            </div>
        </div>

        <!-- Feedback Modal -->
        <FeedbackModal 
            v-if="showingFeedback" 
            :feedback="feedbackData"
            :onContinue="continueToNextQuestion"
        />
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import SmartScoreBar from '@/Components/SkillPractice/SmartScoreBar.vue';
import FeedbackModal from '@/Components/SkillPractice/FeedbackModal.vue';
import QuestionCounter from '@/Components/SkillPractice/QuestionCounter.vue';

const props = defineProps({
    skill: Object,
    session: Object,
    firstQuestion: Object,
    userProgress: Object
});

const currentQuestion = ref(props.firstQuestion);
const selectedAnswer = ref(null);
const showingFeedback = ref(false);
const sessionComplete = ref(false);
const questionStartTime = ref(Date.now());
const sessionStats = ref({
    answered: 0,
    correct: 0,
    streak: 0,
    accuracy: 0
});
const feedbackData = ref(null);

// New loading state
const loading = ref(false);
const error = ref(null);

const endSession = () => {
    router.post(`/skill-practice/end-session/${props.session.id}`, {}, {
        onSuccess: () => {
            router.visit('/skill-practice');
        }
    });
};

const continueToNextQuestion = () => {
    showingFeedback.value = false;
    nextQuestion();
};

// Modified submitAnswer method to include loading states
const submitAnswer = async () => {
    if (!selectedAnswer.value || showingFeedback.value) return;
    
    loading.value = true;
    error.value = null;
    
    try {
        const response = await axios.post('/skill-practice/submit-answer', {
            session_id: props.session.id,
            skill_question_id: currentQuestion.value.skill_question_id,
            user_answer: selectedAnswer.value,
            time_taken_ms: Date.now() - questionStartTime.value
        });

        if (response.data.success) {
            // Update session stats
            sessionStats.value.answered += 1;
            if (response.data.is_correct) {
                sessionStats.value.correct += 1;
                sessionStats.value.streak += 1;
            } else {
                sessionStats.value.streak = 0; // Reset streak on wrong answer
            }
            sessionStats.value.accuracy = (sessionStats.value.correct / sessionStats.value.answered) * 100;
            
            // Update user progress
            userProgress.value.smart_score = response.data.updated_smart_score;
            userProgress.value.current_streak = response.data.updated_streak;
            
            // Prepare feedback data
            feedbackData.value = {
                isCorrect: response.data.is_correct,
                correctAnswer: response.data.correct_answer,
                explanation: response.data.explanation || '',
                smartScoreChange: response.data.smart_score_change,
                streak: response.data.updated_streak,
                nextQuestion: response.data.next_question
            };
            
            showingFeedback.value = true;
        } else {
            error.value = response.data.message || 'Error submitting answer';
        }
    } catch (err) {
        console.error('Error:', err);
        error.value = 'Failed to submit answer. Please try again.';
    } finally {
        loading.value = false;
    }
};

// Modified nextQuestion method to include loading states
const nextQuestion = async () => {
    if (sessionComplete.value) return;
    
    loading.value = true;
    error.value = null;
    
    try {
        const response = await axios.post('/skill-practice/next-question', {
            skill_id: props.skill.id,
            session_id: props.session.id
        });
        
        if (response.data.success && response.data.question) {
            currentQuestion.value = response.data.question;
            selectedAnswer.value = null;
            showingFeedback.value = false;
            questionStartTime.value = Date.now();
        } else {
            // No more questions, end session
            sessionComplete.value = true;
        }
    } catch (err) {
        console.error('Error getting next question:', err);
        error.value = 'Failed to load next question. Please try again.';
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    if (!currentQuestion.value) {
        nextQuestion();
    }
});

</script>