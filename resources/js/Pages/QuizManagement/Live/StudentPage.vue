<template>
    <Head title="Join Live Quiz" />
    <div class="student-page">
        <!-- Background Shapes -->
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>

        <!-- Join Screen -->
        <div v-if="!session" class="content-wrapper join-screen">
            <q-card class="game-card bounce-in">
                <q-card-section class="text-center q-pa-lg">
                    <div class="icon-wrapper q-mb-md">
                        <q-icon name="school" size="60px" color="white" />
                    </div>
                    <h1 class="text-h3 text-weight-bold text-primary q-mb-sm">Class Quiz!</h1>
                    <p class="text-h6 text-grey-7">Enter the magic code from your teacher</p>

                    <q-input
                        v-model="accessCode"
                        placeholder="CODE"
                        outlined
                        class="code-input q-mt-lg"
                        input-class="text-center text-uppercase text-weight-bold"
                        @keyup.enter="joinSession"
                        :disable="joining"
                    >
                        <template v-slot:prepend>
                            <q-icon name="vpn_key" color="primary" />
                        </template>
                    </q-input>

                    <q-btn
                        push
                        color="primary"
                        label="LET'S GO!"
                        @click="joinSession"
                        :loading="joining"
                        :disable="!accessCode"
                        size="xl"
                        class="full-width q-mt-lg game-btn"
                        icon-right="rocket_launch"
                    />
                </q-card-section>
            </q-card>
        </div>

        <!-- Waiting Screen -->
        <div v-else-if="session.status === 'waiting'" class="content-wrapper waiting-screen">
            <q-card class="game-card bounce-in">
                <q-card-section class="text-center q-pa-lg">
                    <div class="waiting-animation q-mb-lg">
                        <q-spinner-orbit color="orange" size="100px" />
                    </div>
                    <h2 class="text-h4 text-weight-bold text-primary">Get Ready!</h2>
                    <p class="text-h6 text-grey-7 q-mb-md">Waiting for the teacher to start...</p>
                    
                    <div class="participant-badge">
                        <q-icon name="group" size="24px" />
                        <span>{{ participantCount }} friends joined</span>
                    </div>
                </q-card-section>
            </q-card>
        </div>

        <!-- Active Question Screen -->
        <div
            v-else-if="session.status === 'active' && currentQuestion"
            class="content-wrapper question-screen"
        >
            <q-card class="game-card slide-up">
                <q-card-section class="q-pa-md">
                    <!-- Header with Timer -->
                    <div class="row items-center justify-between q-mb-lg">
                        <div class="question-number text-h6 text-weight-bold text-grey-8">
                            Question
                        </div>
                        <div v-if="timeRemaining > 0" class="timer-badge" :class="{ 'timer-warning': timeRemaining <= 10 }">
                            <q-icon name="timer" size="24px" />
                            <span>{{ timeRemaining }}s</span>
                        </div>
                    </div>

                    <!-- Question Text -->
                    <div
                        class="question-text text-h5 text-weight-bold text-center q-mb-xl"
                        v-html="currentQuestion.question_text"
                    ></div>

                    <!-- Answer Input -->
                    <div class="answer-section">
                        <!-- Multiple Choice -->
                        <div
                            v-if="currentQuestion.question_type?.slug === 'multiple_choice'"
                            class="options-grid"
                        >
                            <button
                                v-for="opt in questionOptions"
                                :key="opt.value"
                                class="option-btn"
                                :class="{ 'selected': answer === opt.value, 'disabled': submitted }"
                                @click="!submitted && (answer = opt.value)"
                            >
                                <span class="option-label">{{ opt.label }}</span>
                                <q-icon v-if="answer === opt.value" name="check_circle" size="32px" color="white" />
                            </button>
                        </div>

                        <!-- Numeric -->
                        <div v-else-if="currentQuestion.question_type?.slug === 'numeric'" class="numeric-input-wrapper">
                            <q-input
                                v-model="answer"
                                type="number"
                                placeholder="Type your answer..."
                                outlined
                                class="big-input"
                                input-class="text-center text-weight-bold"
                                :disable="submitted"
                            />
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <q-btn
                        v-if="!submitted"
                        push
                        color="positive"
                        label="SUBMIT ANSWER"
                        @click="submitAnswer"
                        :disable="!answer"
                        :loading="submitting"
                        size="xl"
                        class="full-width q-mt-xl game-btn"
                    />

                    <!-- Feedback Overlay -->
                    <div v-if="submitted && feedback" class="feedback-overlay" :class="feedback.is_correct ? 'correct' : 'incorrect'">
                        <div class="feedback-content bounce-in">
                            <q-icon
                                :name="feedback.is_correct ? 'emoji_events' : 'sentiment_dissatisfied'"
                                size="100px"
                                color="white"
                                class="q-mb-md"
                            />
                            <h2 class="text-h3 text-white text-weight-bold q-my-none">
                                {{ feedback.is_correct ? "Awesome!" : "Oops!" }}
                            </h2>
                            <p class="text-h5 text-white q-mt-sm">
                                {{ feedback.is_correct ? "You got it right! +10 pts" : "Nice try!" }}
                            </p>
                            <div class="score-badge q-mt-md">
                                Total Score: {{ feedback.participant.score }}
                            </div>
                        </div>
                    </div>
                    
                    <!-- Waiting for next question (if submitted but no feedback yet or waiting) -->
                    <div v-else-if="submitted && !feedback" class="text-center q-mt-xl">
                        <q-spinner-dots color="primary" size="40px" />
                        <p class="text-h6 text-grey-7">Sending answer...</p>
                    </div>
                </q-card-section>
            </q-card>
        </div>

        <!-- Completed Screen -->
        <div
            v-else-if="session.status === 'completed'"
            class="content-wrapper completed-screen"
        >
            <q-card class="game-card bounce-in">
                <q-card-section class="text-center q-pa-lg">
                    <div class="trophy-animation q-mb-lg">
                        <q-icon name="emoji_events" size="120px" color="warning" />
                    </div>
                    <h2 class="text-h3 text-weight-bold text-primary q-mb-md">Quiz Done!</h2>
                    <p class="text-h5 text-grey-7">You did great!</p>
                    
                    <div class="final-score-card q-mt-xl">
                        <div class="text-uppercase text-caption text-weight-bold text-grey-6">Final Score</div>
                        <div class="score-value text-h2 text-weight-bold text-primary">{{ participant?.score || 0 }}</div>
                    </div>
                </q-card-section>
            </q-card>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onUnmounted } from "vue";
import { Head } from "@inertiajs/vue3";
import { useQuasar } from "quasar";
import axios from "axios";
import { ref as firebaseRef, onValue, set, update } from "firebase/database";
import { database } from "@/firebase/init";

const $q = useQuasar();

// State
const accessCode = ref("");
const session = ref(null);
const participant = ref(null);
const currentQuestion = ref(null);
const answer = ref(null);
const submitted = ref(false);
const submitting = ref(false);
const joining = ref(false);
const feedback = ref(null);
const participantCount = ref(0);
const timeRemaining = ref(0);
const timerInterval = ref(null);

// Firebase listener
let sessionListener = null;

// Computed
const questionOptions = computed(() => {
    if (!currentQuestion.value?.options) return [];
    return currentQuestion.value.options.map((opt) => ({
        label: opt.option_text,
        value: opt.id,
    }));
});

// Join session
const joinSession = async () => {
    if (!accessCode.value) return;

    joining.value = true;
    try {
        const response = await axios.post("/api/quiz-sessions/join", {
            access_code: accessCode.value.toUpperCase(),
        });

        session.value = response.data.session;
        participant.value = response.data.participant;

        // Write to Firebase to update real-time list
        const participantRef = firebaseRef(
            database, 
            `quiz_sessions/${session.value.access_code}/participants/${participant.value.id}`
        );
        
        await set(participantRef, {
            student: participant.value.student,
            score: participant.value.score ?? 0,
            status: 'joined',
            id: participant.value.id,
            last_answered_question_id: null
        });

        // Setup Firebase listener
        setupFirebaseListener();

        $q.notify({
            type: "positive",
            message: "You're in! 🚀",
            position: "top"
        });
    } catch (error) {
        console.error("Failed to join session:", error);
        $q.notify({
            type: "negative",
            message: error.response?.data?.message || "Oops! Check the code.",
            position: "top"
        });
    } finally {
        joining.value = false;
    }
};

// Setup Firebase listener
const setupFirebaseListener = () => {
    const sessionRef = firebaseRef(
        database,
        `quiz_sessions/${accessCode.value.toUpperCase()}`
    );

    sessionListener = onValue(sessionRef, (snapshot) => {
        const data = snapshot.val();
        if (data) {
            // Update session status
            if (data.status) {
                session.value.status = data.status;
            }

            // Update current question
            if (data.current_question) {
                // Check if it's a new question
                if (!currentQuestion.value || currentQuestion.value.id !== data.current_question.id) {
                    currentQuestion.value = data.current_question;
                    answer.value = null;
                    submitted.value = false;
                    feedback.value = null;

                    // Start timer
                    if (session.value.settings?.timer) {
                        startTimer(session.value.settings.timer);
                    }
                }
            }

            // Update participant count
            if (data.participants) {
                participantCount.value = Object.keys(data.participants).length;
            }
        }
    });
};

// Start timer
const startTimer = (duration) => {
    timeRemaining.value = duration;

    if (timerInterval.value) {
        clearInterval(timerInterval.value);
    }

    timerInterval.value = setInterval(() => {
        timeRemaining.value--;

        if (timeRemaining.value <= 0) {
            clearInterval(timerInterval.value);

            // Auto-submit if enabled
            if (session.value.settings?.auto_submit && !submitted.value) {
                submitAnswer();
            }
        }
    }, 1000);
};

// Submit answer
const submitAnswer = async () => {
    if (!answer.value || submitted.value) return;

    submitting.value = true;
    try {
        const response = await axios.post(
            `/api/quiz-sessions/${session.value.id}/answers`,
            {
                question_id: currentQuestion.value.id,
                answer: answer.value,
            }
        );

        submitted.value = true;
        feedback.value = response.data;
        
        // Update score in local state
        if (participant.value) {
            participant.value.score = response.data.participant.score;
        }

        // Update score in Firebase
        const participantRef = firebaseRef(
            database, 
            `quiz_sessions/${session.value.access_code}/participants/${participant.value.id}`
        );
        
        await update(participantRef, {
            score: response.data.participant.score,
            status: 'active',
            last_answered_question_id: currentQuestion.value.id,
            answer_status: 'submitted'
        });

    } catch (error) {
        console.error("Failed to submit answer:", error);
        $q.notify({
            type: "negative",
            message: "Could not send answer. Try again!",
            position: "top"
        });
    } finally {
        submitting.value = false;
    }
};

// Lifecycle
onUnmounted(() => {
    if (sessionListener) {
        sessionListener();
    }
    if (timerInterval.value) {
        clearInterval(timerInterval.value);
    }
});
</script>

<style scoped lang="scss">
// Variables
$bg-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
$card-bg: rgba(255, 255, 255, 0.95);
$primary-color: #667eea;
$accent-color: #ff9966;

.student-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    background: $bg-gradient;
    position: relative;
    overflow: hidden;
    font-family: 'Nunito', sans-serif; // Assuming a rounded font is available or fallback
}

// Background Shapes
.shape {
    position: absolute;
    border-radius: 50%;
    opacity: 0.1;
    z-index: 0;
}
.shape-1 { width: 300px; height: 300px; background: white; top: -50px; left: -50px; }
.shape-2 { width: 200px; height: 200px; background: white; bottom: 50px; right: -50px; }
.shape-3 { width: 150px; height: 150px; background: white; top: 40%; left: 20%; }

.content-wrapper {
    width: 100%;
    max-width: 600px;
    z-index: 1;
}

.game-card {
    background: $card-bg;
    border-radius: 24px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    overflow: hidden;
}

// Animations
.bounce-in { animation: bounceIn 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55); }
.slide-up { animation: slideUp 0.5s ease-out; }

@keyframes bounceIn {
    0% { transform: scale(0.3); opacity: 0; }
    50% { transform: scale(1.05); }
    70% { transform: scale(0.9); }
    100% { transform: scale(1); opacity: 1; }
}

@keyframes slideUp {
    from { transform: translateY(50px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

// Join Screen
.join-screen {
    .icon-wrapper {
        background: $primary-color;
        width: 100px;
        height: 100px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    }
    
    .code-input {
        font-size: 1.5rem;
        :deep(.q-field__control) {
            border-radius: 12px;
            height: 60px;
        }
    }
}

// Waiting Screen
.waiting-screen {
    .participant-badge {
        background: #f0f4ff;
        color: $primary-color;
        padding: 12px 24px;
        border-radius: 50px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-weight: bold;
        font-size: 1.1rem;
    }
}

// Question Screen
.question-screen {
    .timer-badge {
        background: #e0e7ff;
        color: $primary-color;
        padding: 8px 16px;
        border-radius: 20px;
        display: flex;
        align-items: center;
        gap: 6px;
        font-weight: bold;
        font-size: 1.2rem;
        transition: all 0.3s ease;
        
        &.timer-warning {
            background: #ffe0e0;
            color: #ff4444;
            animation: pulse 1s infinite;
        }
    }
    
    .options-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 16px;
        
        @media (min-width: 400px) {
            grid-template-columns: 1fr 1fr;
        }
    }
    
    .option-btn {
        background: white;
        border: 2px solid #e0e0e0;
        border-radius: 16px;
        padding: 20px;
        font-size: 1.2rem;
        font-weight: bold;
        color: #555;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        min-height: 80px;
        
        &:hover:not(.disabled) {
            border-color: $primary-color;
            background: #f8faff;
            transform: translateY(-2px);
        }
        
        &.selected {
            background: $primary-color;
            border-color: $primary-color;
            color: white;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }
        
        &.disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }
    }
    
    .big-input {
        font-size: 1.5rem;
        :deep(.q-field__control) {
            border-radius: 16px;
            height: 70px;
        }
    }
}

// Feedback Overlay
.feedback-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
    border-radius: 24px;
    
    &.correct { background: rgba(76, 175, 80, 0.95); }
    &.incorrect { background: rgba(244, 67, 54, 0.95); }
    
    .feedback-content {
        text-align: center;
    }
    
    .score-badge {
        background: rgba(255,255,255,0.2);
        padding: 8px 20px;
        border-radius: 20px;
        color: white;
        font-weight: bold;
        display: inline-block;
    }
}

// Completed Screen
.completed-screen {
    .final-score-card {
        background: #f0f4ff;
        padding: 24px;
        border-radius: 20px;
        display: inline-block;
        min-width: 200px;
    }
}

// Global Button Styles
.game-btn {
    border-radius: 16px;
    font-weight: 800;
    letter-spacing: 1px;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}
</style>
