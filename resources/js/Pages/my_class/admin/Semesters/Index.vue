<template>
    <Head :title="pageTitle" />
    <div class="q-pa-lg">
        <q-card class="q-ma-md">
            <q-card-section>
                <div class="row justify-between items-center q-mb-md">
                    <div class="row q-gutter-sm">
                        <q-btn
                            color="primary"
                            icon="add"
                            label="Add New Semester"
                            @click="openModal()"
                            unelevated
                        />
                        <ImportExcel
                            @imported="refreshData"
                            :validate-url="baseUrl + '/validate-import'"
                            :import-url="baseUrl + '/import'"
                            :columns="importColumns"
                            button-text="Import Semesters"
                            preview-title="Preview Data"
                        />
                        <q-btn
                            color="secondary"
                            icon="download"
                            label="Export"
                            @click="exportData"
                            outline
                        />
                    </div>
                </div>

                <q-table
                    :rows="items"
                    :columns="quasarTableColumns"
                    row-key="id"
                    flat
                    bordered
                    :pagination="tablePagination"
                    @request="onRequest"
                >
                    <template v-slot:body-cell-active="props">
                        <q-td :props="props">
                            <q-chip
                                :color="props.value ? 'positive' : 'negative'"
                                :label="props.value ? 'Active' : 'Inactive'"
                                text-color="white"
                                size="sm"
                            />
                        </q-td>
                    </template>

                    <template v-slot:body-cell-actions="props">
                        <q-td :props="props">
                            <q-btn
                                flat
                                round
                                color="primary"
                                icon="edit"
                                size="sm"
                                @click="openModal(props.row)"
                            >
                                <q-tooltip>Edit</q-tooltip>
                            </q-btn>
                            <q-btn
                                flat
                                round
                                color="negative"
                                icon="delete"
                                size="sm"
                                @click="deleteRecord(props.row)"
                                class="q-ml-xs"
                            >
                                <q-tooltip>Delete</q-tooltip>
                            </q-btn>
                        </q-td>
                    </template>
                </q-table>
            </q-card-section>
        </q-card>

        <q-dialog v-model="modalOpen" persistent>
            <q-card style="min-width: 500px">
                <q-card-section class="row items-center">
                    <div class="text-h6">{{ editing ? `Edit ${modelName}` : `Create New ${modelName}` }}</div>
                    <q-space />
                    <q-btn icon="close" flat round dense @click="closeModal" />
                </q-card-section>

                <q-card-section>
                    <q-form @submit="submitForm" class="q-gutter-md">
                        <div v-for="field in formFields" :key="field.name">
                            <q-input
                                v-if="field.type === 'text' || field.type === 'number'"
                                v-model="form[field.name]"
                                :label="field.label"
                                :type="field.type"
                                :required="field.required"
                                outlined
                                :error="!!errors[field.name]"
                                :error-message="errors[field.name]?.[0]"
                            />

                            <q-input
                                v-else-if="field.type === 'date'"
                                v-model="form[field.name]"
                                :label="field.label"
                                type="date"
                                outlined
                                :error="!!errors[field.name]"
                                :error-message="errors[field.name]?.[0]"
                            />

                            <q-select
                                v-else-if="field.type === 'select'"
                                v-model="form[field.name]"
                                :options="field.options"
                                :label="field.label"
                                option-value="value"
                                option-label="label"
                                emit-value
                                map-options
                                outlined
                                :required="field.required"
                                :error="!!errors[field.name]"
                                :error-message="errors[field.name]?.[0]"
                            />
                        </div>

                        <q-card-actions align="right" class="q-pt-md">
                            <q-btn flat label="Cancel" @click="closeModal" />
                            <q-btn
                                type="submit"
                                color="primary"
                                label="Save"
                                :loading="submitting"
                            />
                        </q-card-actions>
                    </q-form>
                </q-card-section>
            </q-card>
        </q-dialog>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { router, Head } from '@inertiajs/vue3';
import axios from 'axios';
import ImportExcel from '@/Components/Common/ImportExcel.vue';
import { exportToExcel } from '@/Utils/exportHelper';

const props = defineProps({
    records: Object,
    options: Object,
});

const items = computed(() => props.records?.data || []);
const pagination = computed(() => props.records?.links || null);

const pageTitle = 'Semester Management';
const modelName = 'Semester';
const baseUrl = '/admin/semester';

// Quasar table columns configuration
const quasarTableColumns = [
    { name: 'name', label: 'Name', field: 'name', align: 'left', sortable: true },
    { name: 'semester_number', label: 'Semester Number', field: 'semester_number', align: 'center', sortable: true },
    { name: 'weeks_number', label: 'Weeks', field: 'weeks_number', align: 'center', sortable: true },
    { name: 'start_date', label: 'Start Date', field: 'start_date', align: 'center', sortable: true },
    { name: 'end_date', label: 'End Date', field: 'end_date', align: 'center', sortable: true },
    { name: 'active', label: 'Status', field: 'active', align: 'center', sortable: true },
    { name: 'actions', label: 'Actions', field: 'actions', align: 'center', sortable: false }
];

// Table pagination for Quasar
const tablePagination = ref({
    sortBy: 'name',
    descending: false,
    page: 1,
    rowsPerPage: 10
});



const formFields = [
    {
        name: 'name',
        label: 'Name',
        type: 'text',
        required: true
    },
    {
        name: 'semester_number',
        label: 'Semester Number',
        type: 'number',
        required: true
    },
    {
        name: 'weeks_number',
        label: 'Number of Weeks',
        type: 'number'
    },
    {
        name: 'start_date',
        label: 'Start Date',
        type: 'date'
    },
    {
        name: 'end_date',
        label: 'End Date',
        type: 'date'
    },
    {
        name: 'school_id',
        label: 'School',
        type: 'select',
        required: true,
        options: props.options?.schools?.map(school => ({
            value: school.id,
            label: school.name
        })) || []
    },
    {
        name: 'academic_year_id',
        label: 'Academic Year',
        type: 'select',
        required: true,
        options: props.options?.academicYears?.map(year => ({
            value: year.id,
            label: year.name
        })) || []
    },
    {
        name: 'active',
        label: 'Status',
        type: 'select',
        options: [
            { value: true, label: 'Active' },
            { value: false, label: 'Inactive' }
        ]
    }
];

const modalOpen = ref(false);
const editing = ref(null);
const submitting = ref(false);
const errors = ref({});
const form = ref({});

// Table request handler for Quasar
const onRequest = (props) => {
    // Handle table sorting and pagination if needed
    tablePagination.value = props.pagination;
};

const openModal = (record = null) => {
    editing.value = record;
    modalOpen.value = true;
    initForm();
};

const closeModal = () => {
    modalOpen.value = false;
    editing.value = null;
    errors.value = {};
    form.value = {};
};

// Initialize form with default values
const initForm = () => {
    const newForm = {};
    if (!formFields) return newForm;

    formFields.forEach(field => {
        if (field.options && typeof field.options === 'object' && 'value' in field.options) {
            field.options = field.options.value;
        }
        // Set default value with special handling for checkboxes
        if (field.type === 'checkbox') {
            newForm[field.name] = editing.value?.[field.name] ?? field.value ?? false;
        } else {
            newForm[field.name] = editing.value?.[field.name] ?? field.default ?? '';
        }

        // Set default school_id from localStorage if creating new record
        if (field.name === 'school_id' && !editing.value) {
            const schoolId = localStorage.getItem('school_id') || localStorage.getItem('selected_school');
            if (schoolId) {
                newForm[field.name] = parseInt(schoolId);
            }
        }
    });
    form.value = newForm;
};

const refreshData = () => {
    const schoolId = localStorage.getItem('school_id') || localStorage.getItem('selected_school');
    const params = schoolId ? { school_id: schoolId } : {};

    router.reload({
        only: ['records'],
        data: params,
        preserveScroll: true,
        preserveState: true
    });
};

const submitForm = async () => {
    submitting.value = true;
    errors.value = {};

    const id = editing.value?.id;
    const url = id ? `${baseUrl}/${id}` : baseUrl;

    try {
        await axios.post(url, {
            ...(id && { _method: 'PUT' }),
            ...form.value
        });
        closeModal();
        refreshData();
    } catch (error) {
        if (error.response?.data?.errors) {
            errors.value = error.response.data.errors;
        }
    } finally {
        submitting.value = false;
    }
};

const deleteRecord = async (record) => {
    if (!confirm('Are you sure you want to delete this record?')) return;

    try {
        await axios.delete(`${baseUrl}/${record.id}`);
        refreshData();
    } catch (error) {
        console.error('Deletion error:', error);
        alert('An error occurred while deleting the record.');
    }
};

const importColumns = [
    { key: 'name', label: 'Name' },
    { key: 'semester_number', label: 'Semester Number' },
    { key: 'weeks_number', label: 'Weeks Number' },
    { key: 'start_date', label: 'Start Date' },
    { key: 'end_date', label: 'End Date' },
    { key: 'school_id', label: 'School ID' },
    { key: 'academic_year_id', label: 'Academic Year ID' },
    { key: 'active', label: 'Status' }
];

const exportData = () => {
    exportToExcel({
        items: items.value,
        columns: [
            { key: 'name', label: 'Name' },
            { key: 'semester_number', label: 'Semester Number' },
            { key: 'weeks_number', label: 'Weeks Number' },
            { key: 'start_date', label: 'Start Date' },
            { key: 'end_date', label: 'End Date' },
            { key: 'school.name', label: 'School' },
            { key: 'academicYear.name', label: 'Academic Year' },
            { key: 'active', label: 'Status' }
        ],
        fileName: `semesters_${new Date().toISOString().split('T')[0]}.xlsx`,
        sheetName: 'Semesters'
    });
};

// Load data with school filtering on component mount
onMounted(() => {
    const schoolId = localStorage.getItem('school_id') || localStorage.getItem('selected_school');
    if (schoolId) {
        // If we have a school_id in localStorage, reload with filtering
        refreshData();
    }
});
</script>
