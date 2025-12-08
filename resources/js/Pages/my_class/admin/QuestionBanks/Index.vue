<template>
    <AppLayout title="Question Bank">
        <LessonExplain />
        <img_marker_main></img_marker_main>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <h2 class="text-2xl font-bold mb-6">Question Bank</h2>
                    <QuestionParser />
                    <!-- Filter Section -->
                    <div class="mb-6 space-y-4">
                        <!-- School Selection -->
                        <div>
                            <InputLabel value="School" />
                            <select
                                v-model="selectedSchool"
                                @change="handleSchoolChange"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="">Select School</option>
                                <option
                                    v-for="school in schools"
                                    :key="school.id"
                                    :value="school"
                                >
                                    {{ school.name }}
                                </option>
                            </select>
                        </div>

                        <!-- Subject Selection -->
                        <div>
                            <InputLabel value="Subject" />
                            <select
                                v-model="selectedSubject"
                                @change="handleSubjectChange"
                                :disabled="!selectedSchool"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="">Select Subject</option>
                                <option
                                    v-for="subject in availableSubjects"
                                    :key="subject.id"
                                    :value="subject"
                                >
                                    {{ subject.name }}
                                </option>
                            </select>
                        </div>

                        <!-- Curriculum Selection -->
                        <div>
                            <InputLabel value="Curriculum" />
                            <select
                                v-model="selectedCurriculum"
                                @change="handleCurriculumChange"
                                :disabled="!selectedSubject"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="">Select Curriculum</option>
                                <option
                                    v-for="curriculum in availableCurricula"
                                    :key="curriculum.id"
                                    :value="curriculum"
                                >
                                    {{ curriculum.name }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- Add Button -->
                    <div class="mb-6 flex justify-end gap-2">
                        <PrimaryButton
                            @click="openCreateModal"
                            :disabled="!canAddQuestion"
                            class="flex items-center gap-2"
                        >
                            <LucideIcon name="plus" class="w-4 h-4" />
                            Add New Question
                        </PrimaryButton>
                        <PrimaryButton
                            @click="showBulkImport = true"
                            class="flex items-center gap-2 ml-2"
                        >
                            <LucideIcon name="file-plus" class="w-4 h-4" />
                            Bulk Import
                        </PrimaryButton>
                    </div>
                    <display_test
                        :questions="filteredItems"
                        :showSettings="showTestSettings"
                    />
                        <!-- @generate="generateTest" -->
                        <!-- :showSettings="showTestSettings" -->
                        <!-- @close="showTestSettings = false" -->

                    <!-- Table Component -->
                    <DataTableV7
                        :base-url="baseUrl"
                        :columns="tableColumns"
                        :items="filteredItems"
                        :loading="loading"
                        :actions="dropdownActions"
                        :import-columns="importColumns"
                        @action="handleAction"
                    />
                </div>
            </div>
        </div>
        <QuestionParser
                        :get_text="yourQuestionText"
                        @set_question="handleParsedQuestion"
                    />
        <!-- Modal Component -->
        <FormModal8
            v-if="modalOpen"
            v-model="modalOpen"
            :submitting="isSubmitting"
            :errors="errors"
            :form="form"
            @submit="handleSubmit"
        >
            <template #header>
                <h2 class="text-lg font-medium text-gray-900">
                    {{ editing ? 'Edit Question' : 'Create Question' }}
                </h2>
            </template>
            <div class="space-y-6">
                <!-- Clipboard paste button -->
                <div class="space-y-4">
                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            @click="handleClipboardPaste"
                            class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 transition-colors flex items-center gap-2"
                        >
                            <LucideIcon name="clipboard" class="w-4 h-4" />
                            Paste from Clipboard
                        </button>
                    </div>
                </div>

                <!-- Form Fields -->
                <div v-for="field in formFields" :key="field.name">
                    <InputLabel :for="field.name" :value="field.label" />

                    <component
                        v-if="field.type === 'custom'"
                        :is="field.component"
                        v-model="form[field.name]"
                        class="mt-1 block w-full"
                    />

                    <template v-else-if="field.type === 'select'">
                        <select
                            :id="field.name"
                            v-model="form[field.name]"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                            :required="field?.required"
                        >
                            <option value="">Select {{ field.label }}</option>
                            <option
                                v-for="option in field.options"
                                :key="option.id"
                                :value="option.id"
                            >
                                {{ option.name }}
                            </option>
                        </select>
                    </template>

                    <template v-else-if="field.type === 'textarea'">
                        <textarea
                            :id="field.name"
                            v-model="form[field.name]"
                            rows="4"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                            :required="field?.required"
                        ></textarea>
                    </template>

                    <template v-else>
                        <TextInput
                            :id="field.name"
                            v-model="form[field.name]"
                            :type="field.type"
                            class="mt-1 block w-full"
                            :required="field?.required"
                        />
                    </template>

                    <InputError :message="errors[field.name]" class="mt-2" />
                </div>
            </div>
        </FormModal8>

        <!-- TestWorksheetSettings and Modal_99 components removed -->
    </AppLayout>
    <!-- Add the modal -->
    <Modal
        :show="showBulkImport"
        @close="showBulkImport = false"
        :maxWidth="'4xl'"
    >
    <template #content>

        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">
                Bulk Import Questions
            </h2>
            <BulkQuestionImport2
                @questions-imported="handleBulkImport"
                @close="showBulkImport = false"
            />
        </div>
    </template>

    </Modal>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import axios from 'axios';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DataTableV7 from './DataTableV7.vue';
import FormModal8 from './FormModal8.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';
import QuestionOptions from './QuestionOptions.vue';
import LucideIcon from '@/Components/Common/LucideIcon.vue';
import Dropdown8 from './Dropdown8.vue';
import ExplanationSteps from './ExplanationSteps.vue';
import display_test from './display/test.vue';
import QuestionParser from './text_to_question_json/QuestionParser.vue';
import { parseQuestionText } from '@/utils/questionParser';
import BulkQuestionImport2 from './components/BulkQuestionImport2.vue';
import Modal from '@/Components/Modal.vue';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';
// resources/js/Pages/my_class/admin/QuestionBanks/img_marker/img_marker_main.vue
import img_marker_main from './img_marker/img_marker_main.vue';
import LessonExplain from './lesson_explain/lesson_explain_main.vue';

// Props
const props = defineProps({
    records: Object,
    options: Object
});

// Constants and refs that other variables depend on
const modelName = 'Question';
const baseUrl = '/admin/question-banks';
const modalOpen = ref(false);
const editing = ref(null);
const editMode = ref('all');
const isSubmitting = ref(false);
const errors = ref({});
const yourQuestionText = ref('');

// Refs for selections
const selectedSchool = ref('');
const selectedSubject = ref('');
const selectedCurriculum = ref('');
const loading = ref(false);

// Data refs
const schools = computed(() => props.options?.schools || []);
const availableSubjects = ref([]);
const availableCurricula = ref([]);

// Computed properties
const canAddQuestion = computed(() => {
    return selectedSchool.value && selectedSubject.value && selectedCurriculum.value;
});


const handleParsedQuestion = (questionData) => {
    // Handle the parsed question data here
    console.log(questionData);
    // form.value.body=questionData.body
    // form.value.options=questionData.options
};
// =============================================
const filteredItems = computed(() => {
    if (!items.value) return [];

    let filtered = [...items.value];
    if (selectedSchool.value) {
        filtered = filtered.filter(item => item.school_id === selectedSchool.value.id);
    }
    if (selectedSubject.value) {
        filtered = filtered.filter(item => item.subject_id === selectedSubject.value.id);
    }
    if (selectedCurriculum.value) {
        filtered = filtered.filter(item => item.curriculum_id === selectedCurriculum.value.id);
    }
    return filtered;
});

// Handler functions
const handleSchoolChange = async () => {
    selectedSubject.value = '';
    selectedCurriculum.value = '';
    availableCurricula.value = [];

    if (!selectedSchool.value) {
        availableSubjects.value = [];
        return;
    }

    loading.value = true;
    try {
        const response = await axios.get(`/api/schools/${selectedSchool.value.id}/subjects`);
        availableSubjects.value = response.data;
    } catch (error) {
        console.error('Error loading subjects:', error);
        availableSubjects.value = [];
    } finally {
        loading.value = false;
    }
};

const handleSubjectChange = async () => {
    selectedCurriculum.value = '';

    if (!selectedSubject.value) {
        availableCurricula.value = [];
        return;
    }

    loading.value = true;
    try {
        const response = await axios.get(`/api/subjects/${selectedSubject.value.id}/curricula`);
        availableCurricula.value = response.data;
    } catch (error) {
        console.error('Error loading curricula:', error);
        availableCurricula.value = [];
    } finally {
        loading.value = false;
    }
};

const handleCurriculumChange = () => {
    // Additional logic if needed when curriculum changes
};

const handleClipboardPaste = async () => {
    const toastId = toast.loading('Reading clipboard...');

    try {
        // Check if clipboard API is available
        if (!navigator.clipboard) {
            throw new Error('Clipboard access is not available. Please check your browser permissions or use a modern browser.');
        }

        const text = await navigator.clipboard.readText();

        // Check if clipboard is empty
        if (!text.trim()) {
            throw new Error('Clipboard is empty. Please copy some text first.');
        }

        const result = parseQuestionText(text);

        if (result.success) {
            form.value.body = result.data.body;
            form.value.options = result.data.options;

            toast.update(toastId, {
                render: 'Question parsed successfully',
                type: 'success',
                isLoading: false,
                autoClose: 3000
            });
        } else {
            throw new Error(result.error || 'Invalid question format. Please check the text structure.');
        }
    } catch (error) {
        console.error('Clipboard operation failed:', error);

        let errorMessage = 'Failed to paste from clipboard: ';

        // Handle specific error cases
        if (error.name === 'NotAllowedError') {
            errorMessage += 'Clipboard permission denied. Please allow clipboard access.';
        } else if (error.name === 'SecurityError') {
            errorMessage += 'Clipboard access blocked due to security settings.';
        } else if (error.name === 'ClipboardQuotaExceededError') {
            errorMessage += 'Clipboard content is too large to process.';
        } else {
            errorMessage += error.message || 'Unknown error occurred';
        }

        toast.update(toastId, {
            render: errorMessage,
            type: 'error',
            isLoading: false,
            autoClose: 5000
        });
    }
};

// Define questionTypes before using it in formFields
const questionTypes = [
    { id: 'mcq', name: 'Multiple Choice' },
    { id: 'true_false', name: 'True/False' },
    { id: 'fill_blank', name: 'Fill in the Blank' }
];

// Now define formFields after questionTypes is defined
const formFields = [
    { name: 'title', label: 'Title', type: 'text', required: true },
    { name: 'body', label: 'Question Body', type: 'textarea', required: true },
    {
        name: 'type',
        label: 'Question Type',
        type: 'select',
        options: questionTypes,
        required: true
    },
    {
        name: 'score',
        label: 'Score',
        type: 'number',
        required: true,
        default: '1'
    },
    {
        name: 'difficulty',
        label: 'Difficulty',
        type: 'select',
        options: [
            { id: 'easy', name: 'Easy' },
            { id: 'medium', name: 'Medium' },
            { id: 'hard', name: 'Hard' }
        ],
        required: true,
        default: 'medium'
    },
    {
        name: 'options',
        label: 'Options',
        type: 'custom',
        component: QuestionOptions,
        required: true
    },
    {
        name: 'explanation',
        label: 'Explanation Steps',
        type: 'custom',
        component: ExplanationSteps
    },
    { name: 'notes', label: 'Notes', type: 'textarea' }
];

const tableColumns = [
    { key: 'title', label: 'Title' },
    { key: 'type', label: 'Type' },
    { key: 'score', label: 'Score' },
    { key: 'school.name', label: 'School' },
    { key: 'subject.name', label: 'Subject' },
    {
        key: 'actions',
        label: 'Actions'
    }
];

const items = ref([]); // Instead of computed
const pagination = computed(() => props.records.links);

const form = ref({
    school_id: '',
    subject_id: '',
    curriculum_id: '',
    title: '',
    body: '',
    type: 'mcq',
    score: '1',
    difficulty: 'medium',
    options: [{
        option: '',
        isCorrect: false,
        feedback: ''
    }],
    explanation: [{
        id: Date.now(),
        step: '',
        note: ''
    }],
    notes: ''
});

const dropdownActions = [
    { type: 'edit', label: 'Edit', icon: 'edit' },
    { type: 'copy', label: 'Copy as New', icon: 'copy' },
    { type: 'delete', label: 'Delete', icon: 'trash-2' }
];

const handleAction = async ({ type, item }) => {
    try {
        if (!item || !item.id) {
            toast.error('Invalid item selected');
            return;
        }

        if (type === 'edit') {
            openModal(item);
        } else if (type === 'copy') {
            const copiedItem = {
                ...item,
                title: `Copy of ${item.title}`,
                id: null  // Remove the ID to ensure it's saved as a new record
            };
            openModal(copiedItem, true); // Pass true as second parameter to indicate it's a copy
        } else if (type === 'delete') {
            if (!confirm('Are you sure you want to delete this question?')) {
                return;
            }

            await router.delete(`${baseUrl}/${item.id}`, {
                preserveScroll: true,
                onSuccess: () => {
                    toast.success('Question deleted successfully');
                },
                onError: (error) => {
                    toast.error(error.message || 'Failed to delete question');
                }
            });
        }
    } catch (error) {
        toast.error('An error occurred while processing your request');
        console.error('Action error:', error);
    }
};

const prepareExplanationSteps = (explanation) => {
    if (!explanation) return [{ id: Date.now(), step: '', note: '' }];

    try {
        const parsedExplanation = typeof explanation === 'string'
            ? JSON.parse(explanation)
            : explanation;

        return Array.isArray(parsedExplanation)
            ? parsedExplanation.map(step => ({
                id: step.id || Date.now() + Math.random(),
                step: typeof step === 'string' ? step : step.step || '',
                note: step.note || ''
            }))
            : [{ id: Date.now(), step: explanation.toString(), note: '' }];
    } catch (e) {
        return [{ id: Date.now(), step: explanation?.toString() || '', note: '' }];
    }
};

const openModal = (item = null, isCopy = false) => {
    // Ensure we have the required IDs from selections
    if (!selectedSchool.value?.id || !selectedSubject.value?.id || !selectedCurriculum.value?.id) {
        toast.error('Please select school, subject and curriculum first');
        return;
    }

    if (item) {
        editing.value = isCopy ? null : item; // Set editing to null if it's a copy
        form.value = {
            // Always use the currently selected values for these IDs
            school_id: selectedSchool.value.id,
            subject_id: selectedSubject.value.id,
            curriculum_id: selectedCurriculum.value.id,
            // Copy the rest of the item data
            title: isCopy ? `Copy of ${item.title}` : item.title,
            body: item.body,
            type: item.type || 'mcq',
            score: item.score || '1',
            difficulty: item.difficulty || 'medium',
            options: Array.isArray(item.options)
                ? JSON.parse(JSON.stringify(item.options)) // Deep clone to avoid reference issues
                : [{
                    option: '',
                    isCorrect: false,
                    feedback: ''
                }],
            explanation: Array.isArray(item.explanation)
                ? item.explanation.map(exp => ({
                    id: Date.now() + Math.random(),
                    step: exp.step,
                    note: exp.note
                }))
                : [{
                    id: Date.now(),
                    step: '',
                    note: ''
                }],
            notes: item.notes || ''
        };
    } else {
        editing.value = null;
        form.value = {
            school_id: selectedSchool.value.id,
            subject_id: selectedSubject.value.id,
            curriculum_id: selectedCurriculum.value.id,
            title: '',
            body: '',
            type: 'mcq',
            score: '1',
            difficulty: 'medium',
            options: [{
                option: '',
                isCorrect: false,
                feedback: ''
            }],
            explanation: [{
                id: Date.now(),
                step: '',
                note: ''
            }],
            notes: ''
        };
    }

    // Verify the required fields are set
    if (!form.value.school_id || !form.value.subject_id || !form.value.curriculum_id) {
        toast.error('Required IDs are missing. Please check your selections.');
        return;
    }

    modalOpen.value = true;
};

const closeModal = () => {
    modalOpen.value = false;
    editing.value = null;
    errors.value = {};
    form.value = {
        school_id: selectedSchool.value?.id || '',
        subject_id: selectedSubject.value?.id || '',
        curriculum_id: selectedCurriculum.value?.id || '',
        title: '',
        body: '',
        type: 'mcq',
        score: '1',
        difficulty: 'medium',
        options: [],
        explanation: [],
        notes: ''
    };
};

const modalTitle = computed(() => {
    if (!editing.value) return `Create New ${modelName}`;

    switch (editMode.value) {
        case 'text':
            return 'Edit Question Text';
        case 'options':
            return 'Edit Question Options';
        case 'explanation':
            return 'Edit Question Explanation';
        default:
            return `Edit ${modelName}`;
    }
});

const prepareFormData = (formValue) => {
    const formData = {
        ...formValue,
        school_id: selectedSchool.value.id,
        subject_id: selectedSubject.value.id,
        curriculum_id: selectedCurriculum.value.id,
        title: formValue.title?.trim() || '',
        body: formValue.body?.trim() || '',
        type: formValue.type || 'mcq',
        score: parseInt(formValue.score) || 1,
        difficulty: formValue.difficulty || 'medium',
        notes: formValue.notes?.trim() || '',
        options: Array.isArray(formValue.options)
            ? formValue.options.map(opt => ({
                option: opt.option?.trim() || '',
                isCorrect: Boolean(opt.isCorrect),
                feedback: opt.feedback?.trim() || ''
            }))
            : [],
        explanation: Array.isArray(formValue.explanation)
            ? formValue.explanation.map(step => ({
                id: step.id,
                step: step.step.trim(),
                note: step.note.trim()
            })).filter(step => step.step || step.note)
            : []
    };

    return formData;
};

const handleSubmit = async () => {
    isSubmitting.value = true;
    errors.value = {};

    try {
        const url = editing.value
            ? `${baseUrl}/${editing.value.id}`
            : baseUrl;

        const response = await (editing.value
            ? router.put(url, form.value)
            : router.post(url, form.value));

        modalOpen.value = false;
        toast.success(`Question ${editing.value ? 'updated' : 'created'} successfully`);
    } catch (error) {
        if (error.response?.data?.errors) {
            errors.value = error.response.data.errors;
        }
        toast.error('Failed to save question');
    } finally {
        isSubmitting.value = false;
    }
};

const deleteRecord = async (record) => {
    if (!confirm('Are you sure you want to delete this question?')) return;

    try {
        const response = await axios.delete(`${baseUrl}/${record.id}`);
        router.reload({ only: ['records'] });
        toast.success('Question deleted successfully', {
            autoClose: 3000,
            position: toast.POSITION.TOP_RIGHT
        });
    } catch (error) {
        console.error('Error deleting record:', error);
        toast.error('Failed to delete question', {
            autoClose: 3000,
            position: toast.POSITION.TOP_RIGHT
        });
    }
};

// Add these new refs
const isSearching = ref(false);
const searchQuery = ref('');
const isFiltered = ref(false);

// Add these methods
const handleSort = ({ key, order }) => {
    // Implement sorting logic
};

const handlePageChange = (page) => {
    // Implement pagination logic
};

const refreshData = () => {
    // Implement refresh logic
};

const exportData = () => {
    // Implement export logic
};

const performSearch = (query) => {
    // Implement search logic
};

const clearSearch = () => {
    searchQuery.value = '';
    // Implement clear search logic
};

const importColumns = [
    { key: 'title', label: 'Title', required: true },
    { key: 'body', label: 'Question Body', required: true },
    { key: 'type', label: 'Question Type', required: true },
    { key: 'score', label: 'Score', required: true },
    { key: 'difficulty', label: 'Difficulty', required: true },
    { key: 'options', label: 'Options', required: true },
    { key: 'explanation', label: 'Explanation' },
    { key: 'notes', label: 'Notes' }
];

const initializeForm = () => {
    form.value = {
        title: '',
        body: '',
        type: 'mcq',
        score: 1,
        difficulty: 'easy',
        notes: '',
        school_id: null,
        subject_id: null,
        curriculum_id: null,
        options: [
            {
                option: '',
                isCorrect: false,
                feedback: ''
            }
        ],
        explanation: [
            {
                step: '',
                note: ''
            }
        ]
    };
};

// Call this when opening the modal for a new record
const openCreateModal = () => {
    if (!selectedSchool.value?.id || !selectedSubject.value?.id || !selectedCurriculum.value?.id) {
        toast.error('Please select school, subject and curriculum first');
        return;
    }

    editing.value = null;
    form.value = {
        school_id: selectedSchool.value.id,
        subject_id: selectedSubject.value.id,
        curriculum_id: selectedCurriculum.value.id,
        title: '',
        body: '',
        type: 'mcq',
        score: '1',
        difficulty: 'medium',
        options: [{
            option: '',
            isCorrect: false,
            feedback: ''
        }],
        explanation: [{
            id: Date.now(),
            step: '',
            note: ''
        }],
        notes: ''
    };
    modalOpen.value = true;
};

// Call this when opening the modal for editing
const openEditModal = (record) => {
    editing.value = record;
    form.value = {
        ...record,
        options: Array.isArray(record.options) ? record.options : [],
        explanation: Array.isArray(record.explanation) ? record.explanation : []
    };
    modalOpen.value = true;
};

const handleGenerateTest = () => {
    showTestSettings.value = true;
    if (!items.value || items.value.length === 0) {
        toast.error('No questions available to generate test');
        return;
    }
};

const showTestSettings = ref(false);

const generateTest = (settings) => {
    try {
        // This method will be called when test settings are submitted
        console.log('Generating test with settings:', settings);
        console.log('Selected questions:', items.value);

        // The display_test component will handle showing the preview
        showTestSettings.value = false;
    } catch (error) {
        console.error('Error generating test:', error);
        toast.error('Failed to generate test');
    }
};

// Make sure items is properly populated from your props
watch(() => props.records?.data, (newData) => {
    if (newData) {
        items.value = [...newData]; // Create a new array to avoid reference issues
    }
}, { immediate: true });

// Add a watch to update form IDs when selections change
watch([selectedSchool, selectedSubject, selectedCurriculum], ([school, subject, curriculum]) => {
    if (modalOpen.value) {
        form.value.school_id = school?.id || '';
        form.value.subject_id = subject?.id || '';
        form.value.curriculum_id = curriculum?.id || '';
    }
});

const showBulkImport = ref(false);

const handleBulkImport = async (questions) => {
    try {
        // Validate required selections
        if (!selectedSchool.value?.id || !selectedSubject.value?.id || !selectedCurriculum.value?.id) {
            toast.error('Please select school, subject and curriculum first');
            return;
        }

        // Validate questions array
        if (!Array.isArray(questions) || questions.length === 0) {
            toast.error('No valid questions to import');
            return;
        }

        // Show processing toast
        toast.info(`Processing ${questions.length} questions...`);

        // Track success and failures
        let successCount = 0;
        let failureCount = 0;

        // Process questions
        for (const question of questions) {
            try {
                const formattedQuestion = {
                    ...question,
                    school_id: selectedSchool.value.id,
                    subject_id: selectedSubject.value.id,
                    curriculum_id: selectedCurriculum.value.id,
                    // Ensure all required fields are present
                    title: question.title?.trim() || `Question ${successCount + 1}`,
                    body: question.body?.trim() || '',
                    type: question.type || 'mcq',
                    score: parseInt(question.score) || 1,
                    difficulty: question.difficulty || 'medium',
                    options: Array.isArray(question.options) ? question.options : [],
                    explanation: Array.isArray(question.explanation) ? question.explanation : []
                };

                await router.post(baseUrl, formattedQuestion);
                successCount++;
            } catch (err) {
                console.error('Failed to import question:', err);
                failureCount++;
            }
        }

        // Close modal and refresh
        showBulkImport.value = false;
        await router.reload({ only: ['records'] });

        // Show final status
        if (failureCount === 0) {
            toast.success(`Successfully imported ${successCount} questions`);
        } else {
            toast.warning(`Imported ${successCount} questions, ${failureCount} failed`);
        }
    } catch (error) {
        console.error('Bulk import error:', error);
        toast.error('Failed to process bulk import');
    }
};
</script>





























































