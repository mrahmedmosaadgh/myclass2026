<template>
    <div class="typing-lesson">
        <div class="lesson-header">
            <h1>U, R, and K Keys</h1>
            <p>Home Row: Place your fingers on the home row keys: ASDF JKL;</p>
        </div>

        <KeyboardComponent 
            :activeKey="currentKey"
            :highlightKeys="['U', 'R', 'K']"
        />

        <TypingArea
            :lessonText="currentExercise.text"
            @key-press="handleKeyPress"
            @exercise-complete="handleExerciseComplete"
        />

        <LessonStats
            :accuracy="stats.accuracy"
            :wpm="stats.wpm"
            :errors="stats.errors"
        />
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import KeyboardComponent from '../components/KeyboardComponent.vue'
import TypingArea from '../components/TypingArea.vue'
import LessonStats from '../components/LessonStats.vue'

const currentKey = ref(null)
const currentExerciseIndex = ref(0)
const stats = ref({
    accuracy: 100,
    wpm: 0,
    errors: 0
})

const exercises = [
    { text: "u u u u u r r r r r k k k k k" },
    { text: "ur ur ur rk rk rk ku ku ku" },
    { text: "run rug key kit kin" },
    { text: "trunk kurk turn rank" }
]

const currentExercise = computed(() => exercises[currentExerciseIndex.value])

const handleKeyPress = (key) => {
    currentKey.value = key
    // Update stats based on key press
}

const handleExerciseComplete = (exerciseStats) => {
    stats.value = { ...exerciseStats }
    // Handle exercise completion
}
</script>