<template>
    <Head :title="pageTitle" />
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <div class="flex justify-between mb-6">
                        <h2 class="text-2xl font-bold">{{ pageTitle }}</h2>
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
    records: Object,
    schools: Array,
    stages: Array,
    grades: Array,
});

const pageTitle = 'Classrooms';
const modelName = 'Classroom';
const baseUrl = '/admin/classroom';

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

const gradeOptions = computed(() =>
    props.grades.map(grade => ({
        value: grade.id,
        label: grade.name
    }))
);

const formFields = [
    {
        name: 'name',
        label: 'Name',
        type: 'text',
        required: true
    },
    {
        name: 'capacity',
        label: 'Capacity',
        type: 'number',
        required: true,
        min: 1
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
        name: 'grade_id',
        label: 'Grade',
        type: 'select',
        required: true,
        options: gradeOptions
    }
];

const tableColumns = [
    { key: 'name', label: 'Name' },
    { key: 'capacity', label: 'Capacity' },
    { key: 'school.name', label: 'School' },
    { key: 'stage.name', label: 'Stage' },
    { key: 'grade.name', label: 'Grade' },
    { key: 'actions', label: 'Actions' }
];

const modalOpen = ref(false);
const editing = ref(null);
const items = computed(() => props.records.data);
const pagination = computed(() => props.records.links);

const openModal = (record = null) => {
    editing.value = record;
    modalOpen.value = true;
};

const closeModal = () => {
    modalOpen.value = false;
    editing.value = null;
};

const submitting = ref(false);

const refreshData = () => {
    router.reload({
        only: ['records'],
        preserveScroll: true,
        preserveState: true
    });
};

const handleSubmit = async ({ form, onSuccess, onError }) => {
    if (submitting.value) return;

    submitting.value = true;
    const id = editing.value?.id;
    const url = id ? `${baseUrl}/${id}` : baseUrl;

    try {
        const response = await axios.post(url, {
            ...(id && { _method: 'PUT' }),
            ...form
        });

        onSuccess();
        closeModal();
        refreshData(); // This will now properly refresh the table
    } catch (error) {
        if (error.response?.data?.errors) {
            onError(error.response.data.errors);
        } else {
            onError({ error: ['An unexpected error occurred'] });
        }
    } finally {
        submitting.value = false;
    }
};

const deleteRecord = (record) => {
    if (confirm('Are you sure you want to delete this classroom?')) {
        axios.delete(`${baseUrl}/${record.id}`)
            .then(response => {
                if (response.data.message) {
                    alert(response.data.message);
                }
                refreshData(); // Use refreshData instead of router.reload()
            })
            .catch(error => {
                console.error('Deletion error:', error);
                alert('An error occurred while deleting the record.');
            });
    }
};

const exportToExcel = async () => {
    try {
        const response = await axios.get(`${baseUrl}/export`, {
            responseType: 'blob' // Important for handling file downloads
        });

        // Create a blob from the response data
        const blob = new Blob([response.data], {
            type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        });

        // Create a link element and trigger download
        const link = document.createElement('a');
        link.href = window.URL.createObjectURL(blob);
        link.download = 'classrooms.xlsx';
        link.click();

        // Cleanup
        window.URL.revokeObjectURL(link.href);
    } catch (error) {
        console.error('Export failed:', error);
        alert('Failed to export data');
    }
};
</script>



