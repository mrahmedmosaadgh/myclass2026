<template>
    <AppLayout title="Vocabulary Flashcards">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Grade 4 Vocabulary Flashcards
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <VocabularyFlashcardsApp :vocabulary="vocabularyData" :initial-mode="mode" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import VocabularyFlashcardsApp from './components/VocabularyFlashcardsApp.vue'

// Props
const props = defineProps({
    vocabulary: {
        type: Array,
        default: () => []
    },
    mode: {
        type: String,
        default: 'practice' // 'practice' or 'quiz'
    }
})

// Sample vocabulary data
const vocabularyData = ref(props.vocabulary.length > 0 ? props.vocabulary : [
    { text: "water weeds", translation: "أعشاب مائية" },
    { text: "The Nile River", translation: "نهر النيل" },
    { text: "insects", translation: "حشرات" },
    { text: "hunt", translation: "يصطاد" },
    { text: "crocodile", translation: "تمساح" },
    { text: "desert", translation: "صحراء" },
    { text: "oasis", translation: "واحة" },
    { text: "pyramid", translation: "هرم" },
    { text: "ancient", translation: "قديم" },
    { text: "pharaoh", translation: "فرعون" },
    { text: "temple", translation: "معبد" },
    { text: "treasure", translation: "كنز" }
])
</script>