<template>
    <Head :title="pageTitle" />
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <div v-if="$page.props.flash?.success"
                         class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        {{ $page.props.flash.success }}
                    </div>

                    <div class="flex justify-between items-center mb-6">
                        <div class="flex space-x-2">
                            <PrimaryButton @click="openModal()">
                                Add New Mapping
                            </PrimaryButton>
                            <ImportExcel
                                @imported="refreshData"
                                :validate-url="baseUrl + '/validate-import'"
                                :import-url="baseUrl + '/import'"
                                :columns="importColumns"
                                button-text="Import Mappings"
                                preview-title="Preview Grade Subject Data"
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
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Pagination from '@/Components/Pagination.vue';
import DataTable from '@/Components/Common/DataTable.vue';
import FormModal from '@/Components/Common/FormModal.vue';
import ImportExcel from '@/Components/Common/ImportExcel.vue';
import * as XLSX from 'xlsx';

const props = defineProps({
    records: Object,
    options: Object,
});

const pageTitle = 'Grade Subject Mappings';
const modelName = 'Grade Subject Mapping';
const baseUrl = '/admin/grade-subject';

const importColumns = [
    {
        key: 'grade',
        label: 'Grade Name',
        required: true,
        description: 'Must match an existing grade name'
    },
    {
        key: 'subject',
        label: 'Subject Name',
        required: true,
        description: 'Must match an existing subject name'
    }
];

// Add validation messages
const importValidationMessages = {
    'new': 'Ready to import',
    'duplicate': 'Already exists',
    'invalid': 'Invalid grade or subject name'
};

const modalOpen = ref(false);
const editing = ref(null);
const submitting = ref(false);

const items = computed(() => props.records.data);
const pagination = computed(() => props.records.links);

const tableColumns = [
    {
        key: 'grade.name',
        label: 'Grade'
    },
    {
        key: 'subject.name',
        label: 'Subject'
    }
];

const formFields = [
    {
        name: 'grade_id',
        label: 'Grade',
        type: 'select',
        required: true,
        placeholder: 'Select a grade',
        options: computed(() => props.options?.grades?.map(grade => ({
            value: grade.id,
            label: grade.name
        })) || [])
    },
    {
        name: 'subject_id',
        label: 'Subject',
        type: 'select',
        required: true,
        placeholder: 'Select a subject',
        options: computed(() => props.options?.subjects?.map(subject => ({
            value: subject.id,
            label: subject.name
        })) || [])
    }
];

const openModal = (record = null) => {
    editing.value = record;
    modalOpen.value = true;
};

const closeModal = () => {
    modalOpen.value = false;
    editing.value = null;
};

const refreshData = () => {
    router.reload({ only: ['records'] });
};

const handleSubmit = async ({ form, onSuccess, onError }) => {
    console.log({ form, onSuccess, onError });

    if (submitting.value) return;

    submitting.value = true;
    const id = editing.value?.id;
    const url = id ? `${baseUrl}/${id}` : baseUrl;

    try {
        // Ensure the form data contains the required fields
        const formData = {
            grade_id: form.grade_id,
            subject_id: form.subject_id
        };

        await axios.post(url, {
            ...(id && { _method: 'PUT' }),
            ...formData
        });

        onSuccess();
        closeModal();
        refreshData();
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

const deleteRecord = async (record) => {
    if (!confirm('Are you sure you want to delete this mapping?')) return;

    try {
        await axios.delete(`${baseUrl}/${record.id}`);
        refreshData();
    } catch (error) {
        console.error('Deletion error:', error);
        alert('An error occurred while deleting the record.');
    }
};

const exportData = () => {
    try {
        // Prepare data for export
        const wsData = [
            ['Grade', 'Subject'], // Headers
            ...items.value.map(item => [
                item.grade?.name || '',
                item.subject?.name || ''
            ])
        ];

        // Create worksheet
        const ws = XLSX.utils.aoa_to_sheet(wsData);
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, 'Grade Subjects');

        // Generate filename with timestamp
        const fileName = `grade_subjects_${new Date().toISOString().split('T')[0]}.xlsx`;

        // Write and download file
        XLSX.writeFile(wb, fileName);
    } catch (error) {
        console.error('Export failed:', error);
        alert('Failed to export data');
    }
};

</script>










