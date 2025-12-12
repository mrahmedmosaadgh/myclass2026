<template>




<div class="p-0">

    <div class="p-6">
                <div class="space-y-6">
                    <!-- Display Options Section -->
                    <div>
                        <h3 class="text-sm font-medium text-gray-900 mb-3">Display Options</h3>
                        <div class="space-y-2">
                            <label class="inline-flex items-center">
                                <input type="checkbox" v-model="settings.show_title"
                                       class="rounded border-gray-300 text-indigo-600">
                                <span class="ml-2">Show question title  </span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="checkbox" v-model="settings.showOptions"
                                       class="rounded border-gray-300 text-indigo-600">
                                <span class="ml-2">Show Answer Options</span>
                            </label>


                            <div v-if="settings.showOptions" class="ml-6 space-y-2">
                                <label class="block">
                                    <span class="text-sm text-gray-700">Options per row:</span>
                                    <select v-model="settings.optionsPerRow"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <option value="1">One option per line</option>
                                        <option value="2">Two options per line</option>
                                        <option value="3">Three options per line</option>
                                        <option value="4">Four options per line</option>
                                        <option value="5">Five options per line</option>
                                        <option value="0">Full width</option>
                                    </select>
                                </label>
                            </div>

                            <label class="inline-flex items-center">
                                <input type="checkbox" v-model="settings.showCorrectAnswers"
                                       class="rounded border-gray-300 text-indigo-600">
                                <span class="ml-2">Show Correct Answers</span>
                            </label>

                            <label class="inline-flex items-center">
                                <input type="checkbox" v-model="settings.showExplanations"
                                       class="rounded border-gray-300 text-indigo-600">
                                <span class="ml-2">Show Explanations</span>
                            </label>

                            <label class="inline-flex items-center">
                                <input type="checkbox" v-model="settings.showScore"
                                       class="rounded border-gray-300 text-indigo-600">
                                <span class="ml-2">Show Score</span>
                            </label>
                        </div>
                    </div>

                    <!-- Layout Options Section -->
                    <div>
                        <h3 class="text-sm font-medium text-gray-900 mb-3">Layout Options</h3>
                        <div class="space-y-2">
                            <label class="inline-flex items-center">
                                <input type="radio" v-model="settings.layout" value="compact"
                                       class="border-gray-300 text-indigo-600">
                                <span class="ml-2">Compact Layout</span>
                            </label>

                            <label class="inline-flex items-center">
                                <input type="radio" v-model="settings.layout" value="spacious"
                                       class="border-gray-300 text-indigo-600">
                                <span class="ml-2">Spacious Layout</span>
                            </label>
                        </div>
                    </div>

                    <!-- Explanation Display Options -->
                    <div v-if="settings.showExplanations">
                        <h3 class="text-sm font-medium text-gray-900 mb-3">Explanation Options</h3>
                        <div class="space-y-2">
                            <label class="inline-flex items-center">
                                <input type="checkbox" v-model="settings.showStepNumbers"
                                       class="rounded border-gray-300 text-indigo-600">
                                <span class="ml-2">Show Step Numbers</span>
                            </label>

                            <label class="inline-flex items-center">
                                <input type="checkbox" v-model="settings.showNotes"
                                       class="rounded border-gray-300 text-indigo-600">
                                <span class="ml-2">Show Step Notes</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <button type="button"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                    @click="handleGenerate">
                    Generate Test
                </button>














</div>












<!-- :show="show" -->
    <Modal_99  @close="$emit('close')" :closeable="true">
        <template #title>
            <h2 class="text-lg font-medium">Test/Worksheet Settings</h2>
        </template>
        <template #content>

        </template>

        <template #footer>
            <div class="flex justify-end space-x-3 px-6 py-4 bg-gray-50">
                <button type="button"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                    @click="handleGenerate">
                    Generate Test
                </button>
                <button type="button"
                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300"
                    @click="$emit('close')">
                    Cancel
                </button>
            </div>
        </template>
    </Modal_99>
</template>

<script setup>
import { ref } from 'vue';
import Modal_99 from './Modal_99.vue';

const emit = defineEmits(['close', 'generate']);

const settings = ref({
    showOptions: true,
    show_title: true,

    showCorrectAnswers: false,
    showExplanations: false,
    showScore: true,
    layout: 'compact',
    showStepNumbers: true,
    showNotes: true,
    optionsPerRow: "2"  // Make sure this is a string
});

const handleGenerate = () => {
    console.log('Emitting settings:', settings.value); // Add this for debugging
    emit('generate', settings.value);
};
</script>



