<template>
    <Head :title="pageTitle" />
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <div class="flex justify-between mb-6">
                        <h2 class="text-2xl font-bold">{{ pageTitle_main }}</h2>
                        <PrimaryButton @click="openModal()">Add New</PrimaryButton>
                    </div>

                    <DataTable
                        :columns="tableColumns"
                        :items="items"
                        :loading="false"
                        @edit="openModal"
                        @delete="deleteRecord"
                    />

                    <Pagination v-if="pagination" :links="pagination" />
                </div>
            </div>
        </div>

        <FormModal
            :show="modalOpen"
            :title="modelName"
            :fields="formFields"
            :editing="editing"
            @close="closeModal"
            @submitted="handleSubmit"
        />
</template>

<script setup>
import { ref, computed } from 'vue';
import { router, Head } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Pagination from '@/Components/Pagination.vue';
import DataTable from '@/Components/Common/DataTable.vue';
import FormModal from '@/Components/Common/FormModal.vue';
import axios from 'axios';

const props = defineProps({
    records: {
        type: Object,
        required: true
    },
    schools: {
        type: Array,
        required: true
    },
    stages: {
        type: Array,
        required: true
    },
    subjects: {
        type: Array,
        required: true
    }
});

const items = computed(() => props.records?.data || []);
const pagination = computed(() => props.records?.links || null);

const pageTitle_main = 'Grades';
const pageTitle = pageTitle_main + ' Management';
const modelName = 'Grade';
const baseUrl = '/admin/grade';

const tableColumns = [
    { key: 'name', label: 'Name' },
    { key: 'stage.name', label: 'Stage' },
    { key: 'school.name', label: 'School' },
    {
        key: 'subjects',
        label: 'Subjects',
        formatter: (value, item) => {
            // If value is directly an array of subjects
            if (Array.isArray(value)) {
                return value.map(subject => subject.name).join(', ');
            }

            // If no subjects, return empty string
            if (!value && !item?.subjects) {
                return '';
            }

            // If subjects is available through the item
            if (item?.subjects && Array.isArray(item.subjects)) {
                return item.subjects.map(subject => subject.name).join(', ');
            }

            // If we have subject_ids as a string
            if (item?.subject_ids) {
                try {
                    const subjectIds = typeof item.subject_ids === 'string'
                        ? JSON.parse(item.subject_ids)
                        : item.subject_ids;

                    if (!Array.isArray(subjectIds)) return '';

                    return props.subjects
                        .filter(subject => subjectIds.includes(subject.id))
                        .map(subject => subject.name)
                        .join(', ');
                } catch (error) {
                    console.error('Error parsing subject_ids:', error);
                    return '';
                }
            }

            return '';
        }
    }
];

const schoolOptions = computed(() =>
    props.schools.map(school => ({
        value: school.id,
        label: school.name
    }))
);

const stageOptions = computed(() =>
    props.stages.map(stage => ({
        value: stage.id,
        label: stage.name
    }))
);

const subjectOptions = computed(() =>
    props.subjects.map(subject => ({
        value: subject.id,
        label: subject.name
    }))
);

const formFields = [
    {
        name: 'name',
        label: 'Name',
        type: 'text',
        required: true,
        autofocus: true
    },
    {
        name: 'school_id',
        label: 'School',
        type: 'select',
        required: true,
        options: schoolOptions
    },
    {
        name: 'stage_id',
        label: 'Stage',
        type: 'select',
        required: true,
        options: stageOptions
    },
    {
        name: 'subject_ids',
        label: 'Subjects',
        type: 'multiselect',
        options: subjectOptions,
        multiple: true,
        clearable: true,
        searchable: true,
        required: false
    }
];

const modalOpen = ref(false);
const editing = ref(null);
const submitting = ref(false);

const openModal = (record = null) => {
    if (record) {
        let subjectIds = [];

        // Try to get subject IDs from the relationship first
        if (record.subjects && Array.isArray(record.subjects)) {
            subjectIds = record.subjects.map(subject => subject.id);
        }
        // Fallback to subject_ids parsing
        else if (record.subject_ids) {
            try {
                subjectIds = typeof record.subject_ids === 'string'
                    ? JSON.parse(record.subject_ids)
                    : record.subject_ids;
            } catch (error) {
                console.error('Error parsing subject_ids:', error);
                subjectIds = [];
            }
        }

        editing.value = {
            ...record,
            subject_ids: subjectIds
        };
    } else {
        editing.value = {
            name: '',
            school_id: '',
            stage_id: '',
            subject_ids: []
        };
    }
    modalOpen.value = true;
};

const closeModal = () => {
    modalOpen.value = false;
    editing.value = null;
};

const handleSubmit = async ({ form, onSuccess, onError }) => {
    if (submitting.value) return;

    submitting.value = true;
    const id = editing.value?.id;
    const url = id ? `${baseUrl}/${id}` : baseUrl;

    // Ensure subject_ids is properly formatted
    const formData = {
        ...form,
        subject_ids: JSON.stringify(Array.isArray(form.subject_ids) ? form.subject_ids : [])
    };

    try {
        await axios.post(url, {
            ...(id && { _method: 'PUT' }),
            ...formData
        });

        onSuccess();
        closeModal();
        refreshData();
    } catch (error) {
        console.error('Submission error:', error);
        onError(error.response?.data?.errors || { error: ['An error occurred'] });
    } finally {
        submitting.value = false;
    }
};

const deleteRecord = async (record) => {
    if (!confirm('Are you sure you want to delete this grade?')) return;

    try {
        await axios.delete(`${baseUrl}/${record.id}`);
        refreshData();
    } catch (error) {
        console.error('Deletion error:', error);
        alert('An error occurred while deleting the record.');
    }
};

const refreshData = () => {
    router.reload({
        only: ['records'],
        preserveScroll: true,
        preserveState: true
    });
};
</script>




