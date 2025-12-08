<template>
    <Head :title="pageTitle" />
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <div class="flex justify-between items-center mb-6">
                        <div class="flex space-x-2">
                            <PrimaryButton @click="openModal()">
                                Add New Assignment
                            </PrimaryButton>
                            <ImportExcel
                                @imported="refreshData"
                                :validate-url="baseUrl + '/validate-import'"
                                :import-url="baseUrl + '/import'"
                                :columns="importColumns"
                                button-text="Import Assignments"
                                preview-title="Preview Data"
                            />
                            <SecondaryButton @click="exportData">
                                Export
                            </SecondaryButton>
                        </div>
                    </div>

                    <DataTable
                        :items="items"
                        :columns="tableColumns"
                        @edit="openModal"
                        @delete="deleteRecord"
                    />

                    <div class="mt-4" v-if="pagination">
                        <Pagination :links="pagination" />
                    </div>
                </div>
            </div>
        </div>


    <DialogModal_7
        :show="modalOpen"
        :title="editing ? 'Edit Assignment' : 'Add New Assignment'"
        :fields="formFields"
        :editing="!!editing"
        @close="closeModal"
        @submitted="handleSubmit"
    >
        <template #title>
            {{ editing ? 'Edit Assignment' : 'Add New Assignment' }}
        </template>

        <template #content>
            <div class="space-y-4">
                <div v-for="field in formFields" :key="field.name" class="grid grid-cols-1 gap-2">
                    <label :for="field.name" class="block text-sm font-medium text-gray-700">
                        {{ field.label }}
                    </label>
                    <select
                        v-if="field.type === 'select'"
                        :id="field.name"
                        v-model="form[field.name]"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="">Select {{ field.label }}</option>
                        <option
                            v-for="option in field.options"
                            :key="option.value"
                            :value="option.value"
                        >
                            {{ option.label }}
                        </option>
                    </select>
                    <input
                        v-else
                        :type="field.type"
                        :id="field.name"
                        v-model="form[field.name]"
                        :min="field.min"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                </div>
            </div>
        </template>

        <template #footer>
            <SecondaryButton @click="closeModal">
                Cancel
            </SecondaryButton>
            <PrimaryButton class="ml-3" @click="handleSubmit">
                {{ editing ? 'Update' : 'Create' }}
            </PrimaryButton>
        </template>
    </DialogModal_7>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { router, Head } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Pagination from '@/Components/Pagination.vue';
import DataTable from '@/Components/Common/DataTable.vue';
import DialogModal_7 from './DialogModal_7.vue';
import ImportExcel from '@/Components/Common/ImportExcel.vue';
import * as XLSX from 'xlsx';
import NProgress from 'nprogress';

const props = defineProps({
    records: Object,
    options: {
        type: Object,
        default: () => ({
            schools: [],
            grades: [],
            classrooms: [],
            subjects: [],
            teachers: []
        })
    }
});

const pageTitle = 'Classroom Subject Teachers';
const modelName = 'Teacher Assignment';
const baseUrl = '/admin/classroom-subject-teacher';

const modalOpen = ref(false);
const editing = ref(null);

const items = computed(() => props.records?.data || []);
const pagination = computed(() => props.records?.links || null);

const tableColumns = [
    { key: 'school.name', label: 'School' },
    { key: 'grade.name', label: 'Grade' },
    { key: 'classroom.name', label: 'Classroom' },
    { key: 'subject.name', label: 'Subject' },
    { key: 'teacher.name', label: 'Teacher' },
    { key: 'classes_per_week', label: 'Classes/Week' },
];

const formFields = computed(() => [
    {
        name: 'school_id',
        label: 'School',
        type: 'select',
        required: true,
        options: props.options?.schools?.map(item => ({
            value: item.id,
            label: item.name
        })) || []
    },
    {
        name: 'classroom_id',
        label: 'Classroom',
        type: 'select',
        required: true,
        options: props.options?.classrooms?.map(item => ({
            value: item.id,
            label: item.name
        })) || []
    },
    {
        name: 'subject_id',
        label: 'Subject',
        type: 'select',
        required: true,
        options: props.options?.subjects?.map(item => ({
            value: item.id,
            label: item.name
        })) || []
    },
    {
        name: 'teacher_id',
        label: 'Teacher',
        type: 'select',
        required: true,
        options: props.options?.teachers?.map(item => ({
            value: item.id,
            label: item.name
        })) || []
    },
    {
        name: 'classes_per_week',
        label: 'Classes per Week',
        type: 'number',
        required: true,
        min: 1
    }
]);

const form = ref({
    school_id: '',
    classroom_id: '',
    subject_id: '',
    teacher_id: '',
    classes_per_week: ''
});

// Update form when editing
watch(editing, (newValue) => {
    if (newValue) {
        form.value = {
            school_id: newValue.school_id,
            classroom_id: newValue.classroom_id,
            subject_id: newValue.subject_id,
            teacher_id: newValue.teacher_id,
            classes_per_week: newValue.classes_per_week
        };
    } else {
        form.value = {
            school_id: '',
            classroom_id: '',
            subject_id: '',
            teacher_id: '',
            classes_per_week: ''
        };
    }
});

const openModal = (record = null) => {
    editing.value = record;
    modalOpen.value = true;
};

const closeModal = () => {
    modalOpen.value = false;
    editing.value = null;
    // Reset the form
    form.value = {
        school_id: '',
        classroom_id: '',
        subject_id: '',
        teacher_id: '',
        classes_per_week: ''
    };
};

const refreshData = () => {
    NProgress.start();
    router.reload({
        only: ['records'],
        preserveScroll: true,
        preserveState: true
    })
        .then(() => {
            NProgress.done();
        });
};

const handleSubmit = () => {
    NProgress.start();
    const url = editing.value
        ? `${baseUrl}/${editing.value.id}`
        : baseUrl;

    // Use POST and include _method for PUT requests
    axios.post(url, {
        ...(editing.value && { _method: 'PUT' }),
        ...form.value
    })
        .then(() => {
            // First close the modal
            closeModal();
            // Then refresh the data
            refreshData();
        })
        .catch(error => {
            if (error.response?.data?.errors) {
                console.error(error.response.data.errors);
            }
        })
        .finally(() => {
            NProgress.done();
        });
};

const deleteRecord = async (record) => {
    if (confirm('Are you sure you want to delete this record?')) {
        NProgress.start();
        try {
            await axios.delete(`${baseUrl}/${record.id}`);
            refreshData();
        } catch (error) {
            console.error('Delete error:', error);
        } finally {
            NProgress.done();
        }
    }
};

const exportData = () => {
    NProgress.start();
    try {
        const wsData = [
            ['School', 'Grade', 'Classroom', 'Subject', 'Teacher', 'Classes/Week'],
            ...items.value.map(item => [
                item.school?.name || '',
                item.grade?.name || '',
                item.classroom?.name || '',
                item.subject?.name || '',
                item.teacher?.name || '',
                item.classes_per_week
            ])
        ];

        const ws = XLSX.utils.aoa_to_sheet(wsData);
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, 'Teacher Assignments');

        const fileName = `teacher_assignments_${new Date().toISOString().split('T')[0]}.xlsx`;
        XLSX.writeFile(wb, fileName);
    } catch (error) {
        console.error('Export failed:', error);
        alert('Failed to export data');
    } finally {
        NProgress.done();
    }
};

const importColumns = [
    {
        key: 'school',
        label: 'School Name',
        required: true,
        description: 'Must match an existing school name'
    },
    {
        key: 'grade',
        label: 'Grade Name',
        required: true,
        description: 'Must match an existing grade name'
    },
    {
        key: 'classroom',
        label: 'Classroom Name',
        required: true,
        description: 'Must match an existing classroom name'
    },
    {
        key: 'subject',
        label: 'Subject Name',
        required: true,
        description: 'Must match an existing subject name'
    },
    {
        key: 'teacher',
        label: 'Teacher Name',
        required: true,
        description: 'Must match an existing teacher name'
    },
    {
        key: 'classes_per_week',
        label: 'Classes per Week',
        required: true,
        type: 'number',
        description: 'Number of classes per week'
    }
];
</script>









