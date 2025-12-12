<template>
    <AppLayout title="Question Types">
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Control Panel -->
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <div class="flex flex-wrap gap-4">
                        <button
                            @click="my_comp.flashcards=!my_comp.flashcards"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md transition"
                        >
                            <span>Flashcards</span>
                        </button>

                        <button
                            @click="my_comp.cards=!my_comp.cards"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md transition"
                        >
                            <span>Cards</span>
                        </button>

                        <button
                            @click="my_comp.Emoji=!my_comp.Emoji"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md transition"
                        >
                            <span>Emoji</span>
                        </button>

                        <button
                            @click="my_comp.Type=!my_comp.Type"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md transition"
                        >
                            <span>Question Type</span>
                        </button>
                    </div>
                </div>

                <!-- Content Sections -->
                <div class="space-y-6">
                    <!-- Flashcards Section -->
                    <div v-if="my_comp.flashcards" class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-lg font-semibold mb-4">Flashcards</h2>
                        <flashcards />
                    </div>

                    <!-- Cards Section -->
                    <div v-if="my_comp.cards" class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-lg font-semibold mb-4">Cards</h2>
                        <cards />
                    </div>

                    <!-- Emoji Section -->
                    <div v-if="my_comp.Emoji" class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-lg font-semibold mb-4">Emoji Picker</h2>
                        <div class="space-y-4">
                            <EmojiPickerMartV2 v-model="selectedEmoji" />
                            <AdvancedEmojiPicker />
                            <g_classroom />
                        </div>
                    </div>

                    <!-- Question Type Section -->
                    <div v-if="my_comp.Type" class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-lg font-semibold mb-4">Question Type</h2>
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Select Question Type
                            </label>
                            <select
                                v-model="selectedType"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition"
                            >
                                <option v-for="type in questionTypes" :key="type.id" :value="type.id">
                                    {{ type.name }}
                                </option>
                            </select>
                        </div>

                        <!-- Question Components -->
                        <div class="space-y-6">
                            <QuestionType
                                v-if="selectedType === 'mcq'"
                                type="mcq"
                                :value="currentQuestion"
                                @update:value="updateQuestion"
                            />
                            <QuestionType
                                v-if="selectedType === 'true_false'"
                                type="true_false"
                                :value="currentQuestion"
                                @update:value="updateQuestion"
                            />
                            <QuestionType
                                v-if="selectedType === 'fill_blank'"
                                type="fill_blank"
                                :value="currentQuestion"
                                @update:value="updateQuestion"
                            />

                            <!-- Preview Section -->
                            <div class="mt-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                                <h3 class="text-lg font-medium text-gray-900 mb-2">Preview</h3>
                                <pre class="text-sm text-gray-600 bg-white p-4 rounded-md">{{ currentQuestion }}</pre>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import EmojiPickerMartV2 from "@/components/Common/EmojiPickerMartV2.vue";
  import AppLayout from '@/Layouts/AppLayout.vue';
// import AppLayout  from '@/Layouts/AppLayout.vue';
import AdvancedEmojiPicker from '@/Components/Common/AdvancedEmojiPicker.vue';
import QuestionType from './QuestionType.vue';
import g_classroom from './gclassroom.vue';
import cards from './cards.vue';
import flashcards from './cards/flashcards/FlashcardDeck.vue';
// resources/js/Pages/my_class/teacher/QuestionTypes/cards/flashcards/Flashcard.vue
// D:\my_projects\2025\laravel12\myclass5\resources\js\Pages/my_class/admin/Teacher/card1.vue
// import BookFlip_use from '@/Components/Common/book/BookFlip_use.vue';

// resources/js/Components/Common/AdvancedEmojiPicker.vue
import MessageComposer from '@/Components/Messages/MessageComposer.vue';
// D:/my_projects\2025\laravel12\myclass5\resources\js\Components\Messages\MessageComposer.vue
const props = defineProps({
    questionTypes: {
        type: Array,
        required: true
    }
});
    const selectedEmoji = ref('');
    const my_comp = ref({flashcards:false});

const selectedType = ref('mcq');
const currentQuestion = ref({
    type: 'mcq',
    question: '',
    options: [],
    correctAnswer: null,
    explanation: ''
});

const updateQuestion = (newValue) => {
    currentQuestion.value = newValue;
};
</script>

<style scoped>
.transition {
    @apply transition-all duration-200 ease-in-out;
}

button {
    @apply transform hover:scale-105 active:scale-95;
}

select {
    @apply cursor-pointer;
}

pre {
    @apply whitespace-pre-wrap break-words;
}
</style>

