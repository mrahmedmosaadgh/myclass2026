<template>
    <Head title="Live Quiz - Teacher Control" />
    <div class="teacher-test-page">
        <!-- Header -->
        <div class="page-header">
            <h1>Live Quiz Session</h1>
            <p>Create and control a live quiz session for your students</p>
        </div>

        <!-- Session Creation / Control -->
        <div v-if="!session" class="session-setup">
            <q-card class="setup-card">
                <q-card-section>
                    <h2>Create New Session</h2>
                    
                    <!-- Question Selector -->
                    <div class="question-selector">
                        <q-select
                            v-model="selectedQuestion"
                            :options="questions?.questions || []"
                            option-label="question_text"
                            option-value="id"
                            label="Select a Question"
                            outlined
                            use-input
                            @filter="filterQuestions"
                        >
                            <template v-slot:no-option>
                                <q-item>
                                    <q-item-section class="text-grey">
                                        No questions found
                                    </q-item-section>
                                </q-item>
                            </template>
                            <template v-slot:option="scope">
                                <q-item v-bind="scope.itemProps">
                                    <q-item-section>
                                        <q-item-label v-html="scope.opt.question_text"></q-item-label>
                                        <q-item-label caption>
                                            {{ scope.opt.options?.length || 0 }} options
                                        </q-item-label>
                                    </q-item-section>
                                </q-item>
                            </template>
                        </q-select>
                    </div>

                    <!-- Settings -->
                    <div class="session-settings">
                        <h3>Session Settings</h3>

                        <q-input
                            v-model.number="settings.timer"
                            type="number"
                            label="Timer (seconds)"
                            outlined
                            min="10"
                        />

                        <q-toggle
                            v-model="settings.auto_submit"
                            label="Auto-submit when timer ends"
                        />

                        <q-toggle
                            v-model="settings.show_correct_answer"
                            label="Show correct answer after submission"
                        />
                    </div>

                    <!-- Create Button -->
                    <q-btn
                        color="primary"
                        label="Create Session"
                        @click="createSession"
                        :loading="creating"
                        :disable="!selectedQuestion"
                        size="lg"
                        class="full-width"
                    />
                </q-card-section>
            </q-card>
        </div>

        <!-- Active Session Control -->
        <div v-else class="session-control">
            <!-- Access Code Display -->
            <q-card class="access-code-card">
                <q-card-section class="text-center">
                    <h2>Access Code</h2>
                    <div class="access-code">{{ session.access_code }}</div>
                    <p>
                        Students can join at: <strong>/quiz/live/join</strong>
                    </p>
                </q-card-section>
            </q-card>

            <!-- Timer Display -->
            <q-card v-if="session.status === 'active'" class="timer-card q-mb-md">
                <q-card-section class="text-center">
                    <div class="text-h4 text-weight-bold" :class="{'text-negative': timeRemaining <= 10, 'text-primary': timeRemaining > 10}">
                        <q-icon name="timer" /> {{ timeRemaining }}s
                    </div>
                </q-card-section>
            </q-card>

            <!-- Participants List -->
            <q-card class="participants-card">
                <q-card-section>
                    <h3>Participants ({{ participants.length }})</h3>
                    <q-list separator>
                        <q-item
                            v-for="participant in participants"
                            :key="participant.id"
                        >
                            <q-item-section avatar>
                                <q-avatar color="primary" text-color="white">
                                    {{ participant.student?.name?.charAt(0) || '?' }}
                                </q-avatar>
                            </q-item-section>
                            <q-item-section>
                                <q-item-label>{{ participant.student?.name || 'Unknown' }}</q-item-label>
                                <q-item-label caption>Score: {{ participant.score }}</q-item-label>
                            </q-item-section>
                            <q-item-section side>
                                <div class="row items-center q-gutter-sm">
                                    <q-badge
                                        v-if="participant.last_answered_question_id === session?.current_question_id"
                                        color="positive"
                                        label="Answered"
                                    >
                                        <q-icon name="check" color="white" class="q-ml-xs" />
                                    </q-badge>
                                    <q-badge
                                        v-else
                                        color="warning"
                                        text-color="black"
                                        label="Thinking..."
                                    >
                                        <q-icon name="more_horiz" class="q-ml-xs" />
                                    </q-badge>
                                    
                                    <q-badge
                                        :color="participant.status === 'active' ? 'primary' : 'grey'"
                                        outline
                                    >
                                        {{ participant.status }}
                                    </q-badge>
                                </div>
                            </q-item-section>
                        </q-item>
                    </q-list>
                </q-card-section>
            </q-card>

            <!-- Session Controls -->
            <q-card class="controls-card bounce-in">
                <q-card-section>
                    <h3 class="text-center text-primary text-weight-bold q-mb-lg">🎮 Game Controls</h3>

                    <div class="control-buttons-grid">
                        <q-btn
                            v-if="session.status === 'waiting'"
                            push
                            color="positive"
                            label="START GAME! 🚀"
                            @click="startSession"
                            size="xl"
                            class="game-btn start-btn"
                        />

                        <q-btn
                            v-if="session.status === 'active'"
                            push
                            color="warning"
                            text-color="black"
                            label="PAUSE ⏸️"
                            @click="pauseSession"
                            size="lg"
                            class="game-btn pause-btn"
                        />

                        <q-btn
                            push
                            color="negative"
                            label="END GAME 🛑"
                            @click="endSession"
                            size="lg"
                            class="game-btn end-btn"
                        />
                    </div>
                </q-card-section>
            </q-card>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import { Head } from "@inertiajs/vue3";
import { useQuasar } from "quasar";
import axios from "axios";
import { ref as firebaseRef, onValue, set } from "firebase/database";
import { database } from "@/firebase/init";

const $q = useQuasar();

// State
const session = ref(null);
const selectedQuestion = ref(null);
const questions = ref({ questions: [] }); // Initialize with correct structure
const participants = ref([]);
const creating = ref(false);
const timeRemaining = ref(0);
const timerInterval = ref(null);

const settings = ref({
    timer: 60,
    auto_submit: true,
    show_correct_answer: false,
});

// Firebase listener
let participantsListener = null;

// Load questions
const loadQuestions = async () => {
    try {
        const response = await axios.get("/api/questions");
        questions.value = response.data.data || response.data;
    } catch (error) {
        console.error("Failed to load questions:", error);
        $q.notify({
            type: "negative",
            message: "Failed to load questions",
        });
    }
};

// Filter questions
const filterQuestions = (val, update) => {
    update(() => {
        if (val === "") {
            loadQuestions();
        } else {
            // Client-side filtering if we have all questions, or server-side if needed
            // For now assuming we have loaded a batch
            const needle = val.toLowerCase();
            if (questions.value.questions) {
                // This is a simple client-side filter on the loaded page
                // Ideally should call API with search param
            }
        }
    });
};

// Create session
const createSession = async () => {
    creating.value = true;
    try {
        const response = await axios.post("/api/quiz-sessions", {
            settings: settings.value,
        });

        session.value = response.data.session;

        // Initialize Firebase node
        const sessionRef = firebaseRef(
            database,
            `quiz_sessions/${session.value.access_code}`
        );
        await set(sessionRef, {
            status: "waiting",
            current_question_id: null,
            participants: {},
        });

        // Listen to participants
        setupFirebaseListeners();

        $q.notify({
            type: "positive",
            message: "Session created successfully",
        });
    } catch (error) {
        console.error("Failed to create session:", error);
        $q.notify({
            type: "negative",
            message: "Failed to create session",
        });
    } finally {
        creating.value = false;
    }
};

// Setup Firebase listeners
const setupFirebaseListeners = () => {
    console.log("Setting up Firebase listeners for code:", session.value.access_code);
    const participantsRef = firebaseRef(
        database,
        `quiz_sessions/${session.value.access_code}/participants`
    );

    participantsListener = onValue(participantsRef, (snapshot) => {
        const data = snapshot.val();
        console.log("Firebase participants update:", data);
        if (data) {
            // Convert object to array
            participants.value = Object.values(data);
            console.log("Participants array:", participants.value);
        } else {
            participants.value = [];
        }
    });
};

// Start timer
const startTimer = (duration) => {
    timeRemaining.value = duration;
    if (timerInterval.value) clearInterval(timerInterval.value);
    
    timerInterval.value = setInterval(() => {
        timeRemaining.value--;
        if (timeRemaining.value <= 0) {
            clearInterval(timerInterval.value);
            // Optionally auto-end or notify
        }
    }, 1000);
};

// Start session
const startSession = async () => {
    if (!selectedQuestion.value) return;

    try {
        await axios.post(`/api/quiz-sessions/${session.value.id}/state`, {
            action: "start",
            question_id: selectedQuestion.value.id,
        });

        // Update Firebase
        const sessionRef = firebaseRef(
            database,
            `quiz_sessions/${session.value.access_code}`
        );
        
        // Ensure options are included
        const questionData = JSON.parse(JSON.stringify(selectedQuestion.value));
        
        await set(sessionRef, {
            status: "active",
            current_question_id: selectedQuestion.value.id,
            current_question: questionData,
        });

        session.value.status = "active";
        
        // Start local timer
        if (settings.value.timer) {
            startTimer(settings.value.timer);
        }

        $q.notify({
            type: "positive",
            message: "Session started",
        });
    } catch (error) {
        console.error("Failed to start session:", error);
    }
};

// Pause session
const pauseSession = async () => {
    try {
        await axios.post(`/api/quiz-sessions/${session.value.id}/state`, {
            action: "pause",
        });
        
        if (timerInterval.value) clearInterval(timerInterval.value);

        $q.notify({
            type: "info",
            message: "Session paused",
        });
    } catch (error) {
        console.error("Failed to pause session:", error);
    }
};

// End session
const endSession = async () => {
    try {
        await axios.post(`/api/quiz-sessions/${session.value.id}/state`, {
            action: "end",
        });

        session.value.status = "completed";
        if (timerInterval.value) clearInterval(timerInterval.value);

        $q.notify({
            type: "positive",
            message: "Session ended",
        });
    } catch (error) {
        console.error("Failed to end session:", error);
    }
};

// Lifecycle
onMounted(() => {
    loadQuestions();
});

onUnmounted(() => {
    if (participantsListener) {
        participantsListener();
    }
    if (timerInterval.value) {
        clearInterval(timerInterval.value);
    }
});
</script>

<style scoped lang="scss">
.teacher-test-page {
    max-width: 1200px;
    margin: 0 auto;
    padding: 24px;
    background-color: #f0f4f8;
    min-height: 100vh;
}

.page-header {
    margin-bottom: 32px;
    text-align: center;

    h1 {
        font-size: 2.5rem;
        font-weight: 800;
        margin-bottom: 8px;
        color: #2c3e50;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    p {
        color: #666;
        font-size: 1.1rem;
    }
}

.setup-card, .access-code-card, .participants-card, .controls-card, .timer-card {
    border-radius: 20px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    border: none;
    overflow: hidden;
    margin-bottom: 24px;
}

.setup-card {
    max-width: 600px;
    margin: 0 auto;
}

.question-selector {
    margin-bottom: 24px;
}

.session-settings {
    margin-bottom: 24px;

    h3 {
        font-size: 1.25rem;
        margin-bottom: 16px;
        color: #34495e;
        font-weight: 700;
    }

    .q-input,
    .q-toggle {
        margin-bottom: 16px;
    }
}

.access-code-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;

    .access-code {
        font-size: 4rem;
        font-weight: 800;
        letter-spacing: 8px;
        margin: 16px 0;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
    }
}

.control-buttons-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    padding: 10px;
}

.game-btn {
    border-radius: 15px;
    font-weight: 800;
    letter-spacing: 1px;
    transition: transform 0.2s, box-shadow 0.2s;
    
    &:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }
    
    &:active {
        transform: translateY(1px);
    }
}

.start-btn {
    background: linear-gradient(45deg, #2ecc71, #27ae60) !important;
}

.pause-btn {
    background: linear-gradient(45deg, #f1c40f, #f39c12) !important;
}

.end-btn {
    background: linear-gradient(45deg, #e74c3c, #c0392b) !important;
}

.bounce-in {
    animation: bounceIn 0.8s cubic-bezier(0.215, 0.610, 0.355, 1.000);
}

@keyframes bounceIn {
    0% { opacity: 0; transform: scale3d(.3, .3, .3); }
    20% { transform: scale3d(1.1, 1.1, 1.1); }
    40% { transform: scale3d(.9, .9, .9); }
    60% { opacity: 1; transform: scale3d(1.03, 1.03, 1.03); }
    80% { transform: scale3d(.97, .97, .97); }
    100% { opacity: 1; transform: scale3d(1, 1, 1); }
}
</style>
