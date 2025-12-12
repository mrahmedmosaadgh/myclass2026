<template>
    <AppLayout :title="pageTitle">
                                    <!-- Preview Modal -->
                                     {{ selectedSlide_contents_all?.content }}
                                    <FormModal8
                                        :show="previewModalOpen"
                                        @close="closePreviewModal"
                                        :max-width="'2xl'"
                                        title="Preview Documentation"
                                    >
                                        <div class="p-6">
                                            <!-- Preview Controls -->
                                            <div class="mb-4 flex justify-end">
                                                <button
                                                    @click="forceHtmlView = !forceHtmlView"
                                                    class="px-3 py-1 text-sm rounded-md"
                                                    :class="forceHtmlView ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700'"
                                                >
                                                    <span v-if="forceHtmlView">Viewing as HTML</span>
                                                    <span v-else>View as HTML</span>
                                                </button>
                                            </div>


                                            <!-- HTML Content -->
                                            <div
                                                class="prose max-w-none"
                                                v-if="forceHtmlView || selectedSlide?.content_type === 'html'"
                                                v-html="selectedSlide.slide_content"
                                            ></div>

                                            <!-- Plain Text Content -->
                                            <div
                                                class="whitespace-pre-wrap"
                                                v-else-if="selectedSlide?.content_type === 'text'"
                                            >{{ selectedSlide.slide_content }}</div>

                                            <!-- Screenshot/Image Content -->
                                            <img
                                                v-else-if="selectedSlide?.content_type === 'screenshot' || selectedSlide?.content_type === 'text_img'"
                                                :src="selectedSlide.slide_content"
                                                class="max-w-full h-auto"
                                                alt="Screenshot"
                                            />

                                            <!-- Todo List Content -->
                                            <div
                                                v-else-if="selectedSlide?.content_type === 'todo_list'"
                                                class="space-y-2"
                                            >
                                                <div v-for="(todo, index) in JSON.parse(selectedSlide.slide_content)" :key="index">
                                                    - {{ todo.text }}
                                                </div>
                                            </div>

                                            <!-- Fallback for unknown content types -->
                                            <div v-else class="text-red-600">
                                                Unknown content type: {{ selectedSlide?.content_type }}
                                                <pre class="mt-2 text-sm text-gray-600">{{ JSON.stringify(selectedSlide, null, 2) }}</pre>
                                            </div>
                                        </div>

                                        <template #footer>
                                            <SecondaryButton @click="previewModalOpen = false">
                                                Close
                                            </SecondaryButton>
                                        </template>
                                    </FormModal8>



        <!-- <TodoList7_main></TodoList7_main> -->
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">


                    <!-- Data Table -->
                    <DataTableV7
                        :items="items"
                        :columns="tableColumns"
                        :actions="actions"
                        :loading="isSearching"
                        :per-page="3"
                        :pagination="pagination"
                        :base-url="baseUrl"
                        :import-columns="importColumns"
                        :is-filtered="isFiltered"
                        :search-query="searchQuery"
                        @sort="handleSort"
                        @action="handleAction"
                        @page-changed="handlePageChange"
                        @add-new="openModal"
                        @import="refreshData"
                        @export="exportData"
                        @search="performSearch"
                        @clear-search="clearSearch"
                        @update:search-query="searchQuery = $event"
                    />
                </div>
            </div>

            <!-- Modal Form -->
            <FormModal8
                :show="showModal"
                :title="modalTitle"
                :submitting="isSubmitting"
                @close="closeModal"
                @submit="handleSubmit"


            >
                <template #header>
                    <h2 class="text-lg font-medium text-gray-900">
                        {{ editing ? 'Edit Documentation' : 'Create Documentation' }}
                    </h2>
                </template>

                <!-- Main Form Content -->
                <div class="space-y-6">
                    <!-- Title Field -->
                    <div>
                        <InputLabel for="title" value="Title" />
                        <TextInput
                            id="title"
                            v-model="form.title"
                            type="text"
                            class="mt-1 block w-full"
                            required
                        />
                        <InputError :message="errors.title" class="mt-2" />
                    </div>

                    <!-- Type Field -->
                    <div>
                        <InputLabel for="type" value="Type" />
                        <select
                            id="type"
                            v-model="form.type"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                            required
                        >
                            <option v-for="type in documentationTypes" :key="type.value" :value="type.value">
                                {{ type.label }}
                            </option>
                        </select>
                        <InputError :message="errors.type" class="mt-2" />
                    </div>

                    <!-- Status Field -->
                    <div>
                        <InputLabel for="status" value="Status" />
                        <select
                            id="status"
                            v-model="form.status"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                            required
                        >
                            <option v-for="status in documentationStatuses" :key="status.value" :value="status.value">
                                {{ status.label }}
                            </option>
                        </select>
                        <InputError :message="errors.status" class="mt-2" />
                    </div>

                    <!-- Slides Section -->
                    <div class="space-y-6 ">
                        <div class="flex justify-between items-center bg-gradient-to-r from-indigo-500 to-purple-600 p-4 rounded-lg shadow-lg">
                            <h3 class="text-lg font-medium text-white flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" />
                                </svg>
                                <div class="p-0 relative ">

                                    <div class="px-2 scale-50 absolute rounded-full bg-black -top-4 -left-4 ">
{{ form.content?.length }}
                                    </div>
                                    <div class="p-0">Slides</div>
                                </div>
                                :
                            </h3>
                            <PrimaryButton
                                type="button"
                                @click="addSlide"
                                class="bg-white text-blue-600 hover:bg-indigo-50 transition-all duration-200 transform hover:scale-105"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-600" fill="blue" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                <span
                                class="  text-blue-600 hover:bg-indigo-50 transition-all duration-200 transform hover:scale-105"

                                >

                                Add Slide
                            </span>
                            </PrimaryButton>
                        </div>

                        <div class="grid gap-6">
                            <div v-for="(slide, index) in form.content"
                                 :key="index"
                                 class="bg-white rounded-xl shadow-md overflow-hidden transition-all duration-200 hover:shadow-xl border border-gray-100"
                            >
                                <div class="bg-gradient-to-r from-indigo-500 to-purple-600  px-6 py-4 flex justify-between items-center border-b">
                                    <h4 class="font-medium text-gray-700 flex items-center gap-2">
                                        <span class="bg-indigo-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm">
                                            {{ index + 1 }}
                                        </span>
                                        Slide {{ index + 1 }}
                                    </h4>
                                    <button
                                        type="button"
                                        @click="removeSlide(index)"
                                        class="text-red-500 hover:text-red-700 transition-colors duration-200 flex items-center gap-1"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        Remove
                                    </button>
                                </div>

                                <div class="p-6 space-y-4 bg-gradient-to-r from-blue-300 to-blue-200">
                                    <!-- Slide Title -->
                                    <div class="group ">
                                        <InputLabel :for="'slide-title-' + index" value="Slide Title" class="text-gray-700" />
                                        <TextInput
                                            :id="'slide-title-' + index"
                                            v-model="slide.slide_title"
                                            type="text"
                                            class="mt-1 block w-full transition-all duration-200 focus:ring-2 focus:ring-indigo-500"
                                            required
                                            placeholder="Enter slide title..."
                                        />
                                    </div>

                                    <!-- Slide Content -->
                                     <details :open='true'>
                                        <summary  class="bg-blue-400  cursor-pointer hover:scale-105 transition-all  ">
click to see contents
                                        </summary>

                                        <div class="group">
                                        <InputLabel :for="'slide-content-' + index" value="Slide Content" class="text-gray-700" />
                                        <div class="flex justify-between items-center mb-2">
                                            <!-- <button
                                                type="button"
                                                @click="previewSlide(slide)"
                                                class="px-3 py-1.5 bg-indigo-50 text-indigo-600 rounded-md hover:bg-indigo-100 transition-colors duration-200 flex items-center gap-2"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                Preview
                                            </button> -->
                                            <!-- <ClipboardHandler
                                                locale="en"
                                                @get_data="(result) => {
                                                    if (result.type !== 'error') {
                                                        slide.slide_content = result.data;
                                                        slide.content_type = result.content_type;
                                                    }
                                                }"
                                            /> -->

                                        </div>
                                        <input type="checkbox" v-model="autosave">
                                        <PrimaryButton
                            @click="handleSubmit_keep_open"
                            :disabled="isSubmitting || !isFormValid"
                            class="bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 transition-all duration-200 transform hover:scale-105"
                        >
                            <span v-if="isSubmitting" class="flex items-center gap-2">
                                <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Saving...
                            </span>
                            <span v-else>Save keep_open</span>
                        </PrimaryButton>
                                        <SlideContent
                                            :id="'slide-content-' + index"
                                            v-model="slide.slide_content"
                                            v-model:contentType="slide.content_type"
                                            :rows="4"
                                            required
                                            placeholder="Enter slide content..."
                                            @save_data="handleSubmit_keep_open"
                                        />
                                    </div>
                                </details>

                                    <!-- Notes -->
                                    <div class="group">
                                        <InputLabel :for="'slide-notes-' + index" value="Notes" class="text-gray-700" />
                                        <textarea
                                            :id="'slide-notes-' + index"
                                            v-model="slide.notes"
                                            rows="2"
                                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm transition-all duration-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                            placeholder="Add any additional notes..."
                                        ></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tags Field -->
                        <div class="bg-gradient-to-r from-blue-300 to-blue-200 rounded-xl shadow-md p-6 border border-gray-100">
                            <InputLabel for="tags" value="Tags" class="text-gray-700 flex items-center justify-between">
                                <span>Tags</span>
                                <span class="text-sm text-gray-500">{{ form.tags.length }} tags</span>
                            </InputLabel>
                            <div class="space-y-4">
                                <div class="flex items-center gap-2">
                                    <div class="relative flex-1">
                                        <input
                                            type="text"
                                            v-model="newTag"
                                            @keydown.enter.prevent="addTag"
                                            @keydown.tab.prevent="addTag"
                                            @keydown.comma.prevent="addTag"
                                            placeholder="Add tag and press Enter"
                                            class="w-full rounded-lg border-gray-300 shadow-sm transition-all duration-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 pr-10"
                                        />
                                        <div class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">
                                            press Enter
                                        </div>
                                    </div>
                                    <button
                                        @click="addTag"
                                        type="button"
                                        class="px-4 py-2  text-white rounded-lg hover:bg-indigo-700 transition-colors duration-200 flex items-center gap-2"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                                        </svg>
                                        <!-- Add -->
                                    </button>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <div
                                        v-for="(tag, index) in form.tags"
                                        :key="index"
                                        class="flex items-center gap-2 bg-gradient-to-r from-indigo-50 to-purple-50 px-3 py-1.5 rounded-full border border-indigo-100 group hover:from-indigo-100 hover:to-purple-100 transition-all duration-200"
                                    >
                                        <span class="text-indigo-700">{{ typeof tag === 'string' ? tag : tag.toString() }}</span>
                                        <button
                                            type="button"
                                            @click="removeTag(index)"
                                            class="text-indigo-400 group-hover:text-indigo-600 transition-colors duration-200 hover:bg-indigo-100 rounded-full w-5 h-5 flex items-center justify-center"
                                        >
                                            ×
                                        </button>
                                    </div>
                                    <div v-if="form.tags.length === 0" class="text-gray-400 text-sm italic">
                                        No tags added yet
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <template #footer>
                    <div class="flex items-center justify-end gap-4">
                        <SecondaryButton
                            @click="closeModal"
                            :disabled="isSubmitting"
                            class="hover:bg-gray-100 transition-colors duration-200"
                        >
                            Cancel
                        </SecondaryButton>
                        <PrimaryButton
                            @click="handleSubmit"
                            :disabled="isSubmitting || !isFormValid"
                            class="bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 transition-all duration-200 transform hover:scale-105"
                        >
                            <span v-if="isSubmitting" class="flex items-center gap-2">
                                <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Saving...
                            </span>
                            <span v-else>Save</span>
                        </PrimaryButton>



                    </div>
                </template>
            </FormModal8>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import debounce from 'lodash/debounce';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Pagination7 from './Components/Pagination7.vue';
import ImportExcel from '@/Components/Common/ImportExcel.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';
import DataTableV7 from './Components/DataTableV7.vue';
import FormModal8 from './Components/FormModal8.vue';
import FormModal9 from './Components/FormModal9.vue';
import SlideContent from './Components/slide_content.vue';
// import TodoList7_main from './Components/TodoList7_main.vue';
// resources/js/Pages/Documentation/Components/TodoList7_main.vue

import axios from 'axios';
import { toast } from 'vue3-toastify';
import { exportToExcel } from '@/Utils/exportHelper';


import ClipboardHandler from '@/Components/ClipboardHandler.vue';










const props = defineProps({
    records: Object,
    options: Object,
});

// Constants
const pageTitle = 'Documentation Management';
const modelName = 'Documentation';
const baseUrl = '/admin/documentation';

const documentationTypes = [
    { value: 'code', label: 'Code' },
    { value: 'comment', label: 'Comment' },
    { value: 'idea', label: 'Idea' },
    { value: 'tutorial', label: 'Tutorial' },
    { value: 'reference', label: 'Reference' },
    { value: 'question', label: 'Question' },
    { value: 'note', label: 'Note' },
    { value: 'research', label: 'Research' },
    { value: 'guide', label: 'Guide' },
    { value: 'api', label: 'API' }
];

const documentationStatuses = [
    { value: 'draft', label: 'Draft' },
    { value: 'published', label: 'Published' },
    { value: 'archived', label: 'Archived' }
];

// First, declare all refs including those needed for preview
const showModal = ref(false);
const autosave = ref(false);
const editing = ref(null);
const isSubmitting = ref(false);
const errors = ref({});
const newTag = ref('');
const form = ref({
    title: '',
    content: [],
    type: 'note',
    status: 'draft',
    tags: []
});
const searchQuery = ref('');
const isSearching = ref(false);
const isFiltered = ref(false);
const previewModalOpen = ref(false);
const selectedSlide = ref(null);
const forceHtmlView = ref(false);

// Reset the force HTML view when closing the modal
const closePreviewModal = () => {
    previewModalOpen.value = false;
    forceHtmlView.value = false;
};

// Define the preview function
const selectedSlide_contents_all = ref(null);
const previewSlide_html = (item) => {

    try {
        selectedSlide_contents_all.value=JSON.parse(item.content);


} catch (error) {

}

}

    const previewSlide = (item) => {
    if (!item) return;
    // previewSlide_html  (item)



    try {
        // First try to parse if it's a string
        let content = item.content;
        if (typeof content === 'string') {
            content = JSON.parse(content);
        }

        // Ensure content is an array and has at least one item
        if (!Array.isArray(content) || content.length === 0) {
            console.warn('Invalid content format - expected non-empty array');
            return;
        }

        // Get the first slide
        const firstSlide = content[0];

        // Set the selected slide with proper structure
        selectedSlide.value = {
            content_type: firstSlide.content_type || firstSlide.type || 'text',
            slide_content: firstSlide.slide_content || firstSlide.content || ''
        };

        console.log('Preview slide:', selectedSlide.value); // Debug log
        previewModalOpen.value = true;
    } catch (e) {
        console.warn('Error parsing content:', e);
        toast.error('Unable to preview content');
    }
};

// Then define actions after previewSlide is declared
const actions = ref([
    {
        type: 'preview', // Add type for proper action handling
        label: 'Preview',
        icon: 'eye',
        class: 'text-indigo-600 hover:text-indigo-800',
        // action: (item) => previewSlide(item) // Make sure to pass the item
    },
    {
        type: 'edit',
        label: 'Edit',
        icon: 'pencil',
        class: 'text-blue-600 hover:text-blue-800'
    },
    {
        type: 'delete',
        label: 'Delete',
        icon: 'trash',
        class: 'text-red-600 hover:text-red-800'
    }
]);

const importColumns = [
    { key: 'title', label: 'Title', required: true },
    { key: 'type', label: 'Type', required: true },
    { key: 'status', label: 'Status', required: true },
    { key: 'content', label: 'Content', required: true },
    { key: 'tags', label: 'Tags' }
];

// Computed
const items = computed(() => props.records.data || []);
const pagination = computed(() => {
    if (!props.records?.links) return [];
    return props.records.links.map(link => ({
        ...link,
        label: link.label.replace('&laquo;', '«').replace('&raquo;', '»')
    }));
});
const modalTitle = computed(() => editing.value ? `Edit ${modelName}` : `Add New ${modelName}`);
const isFormValid = computed(() => {
    return form.value.title.trim() !== '' &&
           form.value.type !== '' &&
           form.value.status !== '' &&
           Array.isArray(form.value.content) &&
           form.value.content.length > 0 &&
           form.value.content.every(slide =>
               slide.slide_title.trim() !== '' &&
               slide.slide_content.trim() !== ''
           );
});

// Methods
const createEmptySlide = () => ({
    slide_title: '',
    slide_content: '',
    content_type: 'text',
    notes: '',
    timestamp: new Date().toISOString()
});

const addSlide = () => {
    form.value.content.push(createEmptySlide());
};

const removeSlide = (index) => {
    form.value.content.splice(index, 1);
};

const addTag = () => {
    if (!Array.isArray(form.value.tags)) {
        form.value.tags = [];
    }

    const tag = newTag.value.trim();
    if (tag && !form.value.tags.includes(tag)) {
        form.value.tags.push(tag);
    }
    newTag.value = ''; // Clear the input after adding
};

const removeTag = (index) => {
    form.value.tags.splice(index, 1);
};

const openModal = (record = null) => {
    if (record) {
        let parsedContent;
        try {
            // Parse content if it's a string, otherwise use as is
            parsedContent = typeof record.content === 'string'
                ? JSON.parse(record.content)
                : record.content;
        } catch (e) {
            console.warn('Invalid content format, using empty array');
            parsedContent = [createEmptySlide()];
        }

        form.value = {
            id: record.id,
            title: record.title,
            type: record.type,
            status: record.status,
            content: parsedContent,
            tags: Array.isArray(record.tags)
                ? record.tags
                : (typeof record.tags === 'string'
                    ? JSON.parse(record.tags)
                    : [])
        };
        editing.value = record;
    } else {
        form.value = {
            title: '',
            content: [createEmptySlide()],
            type: 'note',
            status: 'draft',
            tags: []
        };
        editing.value = null;
    }
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    editing.value = null;
    form.value = {
        title: '',
        content: [createEmptySlide()],
        type: 'note',
        status: 'draft',
        tags: []
    };
    errors.value = {};
};

const handleSubmit = async () => {
    if (!isFormValid.value || isSubmitting.value) return;

    isSubmitting.value = true;
    try {
        const payload = {
            ...form.value,
            content: JSON.stringify(form.value.content),
            tags: JSON.stringify(form.value.tags)
        };

        if (editing.value) {
            await axios.put(route('documentation.update', editing.value.id), payload);
        } else {
            await axios.post(route('documentation.store'), payload);
        }

        closeModal();
        toast.success(`Documentation ${editing.value ? 'updated' : 'created'} successfully`);
        router.reload();
    } catch (error) {
        console.error('Error submitting form:', error);
        toast.error('An error occurred while saving the documentation');
    } finally {
        isSubmitting.value = false;
    }
};
const handleSubmit_keep_open = async () => {
    if (!isFormValid.value || isSubmitting.value) return;

    isSubmitting.value = true;
    try {
        const payload = {
            ...form.value,
            content: JSON.stringify(form.value.content),
            tags: JSON.stringify(form.value.tags)
        };

        if (editing.value) {
            await axios.put(route('documentation.update', editing.value.id), payload);
        } else {
            await axios.post(route('documentation.store'), payload);
        }


          router.reload();
    } catch (error) {
        console.error('Error submitting form:', error);
        toast.error('An error occurred while saving the documentation');
    } finally {
        isSubmitting.value = false;
    }
};

const refreshData = () => {
    router.reload({ only: ['records'] });
};

const tableColumns = [
    { key: 'id', label: 'ID', sortable: true },
    { key: 'title', label: 'Title', sortable: true },
    { key: 'type', label: 'Type', sortable: true },
    { key: 'status', label: 'Status', sortable: true },
    { key: 'author.name', label: 'Author', sortable: true }
];

const handleSort = ({ key, order }) => {
    router.get(
        route('documentation.index'),
        {
            sort: key,
            direction: order,
            search: searchQuery.value // Preserve search when sorting
        },
        {
            preserveState: true,
            preserveScroll: true
        }
    );
};

const performSearch = () => {
    if (!searchQuery.value.trim()) return;

    isSearching.value = true;
    isFiltered.value = true;

    router.get(
        route('documentation.index'),
        {
            search: searchQuery.value,
            page: 1
        },
        {
            preserveState: true,
            preserveScroll: false,
            onFinish: () => {
                isSearching.value = false;
            }
        }
    );
};

const clearSearch = () => {
    searchQuery.value = '';
    isFiltered.value = false;
    router.get(
        route('documentation.index'),
        {},
        {
            preserveState: true,
            preserveScroll: true
        }
    );
};

const handleAction = async ({ type, item }) => {
    console.log({ type, item });
// return
    if (type === 'preview') {
        previewSlide(item);
        return
    } else if (type === 'edit') {
        try {
            openModal(item);
        } catch (error) {
            console.error('Error preparing item for edit:', error);
            toast.error('Error preparing content for editing');
        }
    } else if (type === 'delete') {
        if (!confirm('Are you sure you want to delete this documentation?')) return;

        try {
            await axios.delete(`${baseUrl}/${item.id}`);
            refreshData();
            toast.success('Documentation deleted successfully');
        } catch (error) {
            console.error('Error deleting documentation:', error);
            toast.error(error.response?.data?.message || 'Error deleting documentation');
        }
    }
};

const deleteRecord = async (item) => {
    if (!confirm('Are you sure you want to delete this documentation?')) return;

    try {
        await axios.delete(`${baseUrl}/${item.id}`);
        refreshData();
        toast.success('Documentation deleted successfully');
    } catch (error) {
        console.error('Error deleting documentation:', error);
        toast.error(error.response?.data?.message || 'Error deleting documentation');
    }
};

// Add a validation function for the content
const validateContent = (content) => {
    try {
        JSON.stringify(content);
        return true;
    } catch (error) {
        return false;
    }
};

const exportData = () => {
    exportToExcel({
        items: items.value,
        columns: [
            { key: 'id', label: 'ID' },
            { key: 'title', label: 'Title' },
            { key: 'type', label: 'Type' },
            { key: 'status', label: 'Status' },
            { key: 'content', label: 'Content' },
            { key: 'tags', label: 'Tags' }
        ],
        fileName: 'documentation',
        sheetName: 'Documentation'
    });
};

const handlePageChange = (url) => {
    if (!url || isSearching.value) return;

    isSearching.value = true;
    router.get(url, {
        search: searchQuery.value,
        sort: props.records?.filters?.sort,
        direction: props.records?.filters?.direction
    }, {
        preserveState: true,
        preserveScroll: true,
        onFinish: () => {
            isSearching.value = false;
        }
    });
};
</script>
































































