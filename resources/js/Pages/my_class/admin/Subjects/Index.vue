<template>
    <Head :title="pageTitle" />

        <q-card flat class="q-pa-md">
            <q-card-section>
                <div class="text-h5 text-weight-bold">{{ pageTitle }}</div>
            </q-card-section>

            <q-card-section>
                <q-btn-group spread>
                    <q-btn
                        color="primary"
                        label="Add Subject"
                        icon="add"
                        @click="openModal"
                    />
                    <q-btn
                        color="secondary"
                        label="Import"
                        icon="upload_file"
                        @click="importSubjects"
                    />
                    <q-btn
                        color="info"
                        label="Export"
                        icon="download"
                        @click="exportData"
                    />
                </q-btn-group>
            </q-card-section>

            <q-card-section>
                <q-input
                    v-model="filters.search"
                    label="Search Subjects"
                    clearable
                    @input="debounceSearch"
                >
                    <template v-slot:prepend>
                        <q-icon name="search" />
                    </template>
                </q-input>

                <div class="row q-col-gutter-md q-mt-sm">
                    <div class="col-12 col-md-6">
                        <q-select
                            v-model="filters.school_id"
                            :options="schoolOptions"
                            label="Filter by School"
                            option-value="value"
                            option-label="label"
                            emit-value
                            map-options
                            clearable
                            @update:model-value="refreshData"
                        />
                    </div>
                    <div class="col-12 col-md-6">
                        <q-select
                            v-model="filters.status"
                            :options="[
                                { label: 'Active', value: true },
                                { label: 'Inactive', value: false }
                            ]"
                            label="Filter by Status"
                            emit-value
                            map-options
                            clearable
                            @update:model-value="refreshData"
                        />
                    </div>
                </div>
            </q-card-section>

            <q-card-section>
                <q-table
                    :rows="items"
                    :columns="tableColumns"
                    :loading="loading"
                    row-key="id"
                    :pagination="{ rowsPerPage: perPage }"
                >
                    <template v-slot:body-cell-actions="props">
                        <q-td :props="props">
                            <q-btn-group flat>
                                <q-btn
                                    icon="edit"
                                    color="primary"
                                    size="sm"
                                    @click="openModal(props.row)"
                                    class="q-mr-xs"
                                />
                                <q-btn
                                    icon="delete"
                                    color="negative"
                                    size="sm"
                                    @click="confirmDelete(props.row)"
                                    class="q-mr-xs"
                                />
                                <q-btn
                                    icon="description"
                                    color="info"
                                    size="sm"
                                    @click="manageLessonPlanTemplates(props.row)"
                                    class="q-mr-xs"
                                >
                                    <q-tooltip>Manage Lesson Plan Templates</q-tooltip>
                                </q-btn>
                            </q-btn-group>
                        </q-td>
                    </template>
                </q-table>
            </q-card-section>
        </q-card>

        <q-dialog v-model="modalOpen" persistent>
            <q-card style="min-width: 500px">
                <q-card-section>
                    <div class="text-h6">{{ editing ? 'Edit' : 'Add' }} Subject</div>
                </q-card-section>

                <q-card-section>
                    <q-form @submit="handleSubmit" class="q-gutter-md">
                        <q-input
                            v-model="form.name"
                            label="Name"
                            :rules="[val => !!val || 'Name is required']"
                            required
                        />
                        <q-input
                            v-model="form.nour_name"
                            label="Nour Name"
                            :rules="[val => !!val || 'Nour Name is required']"
                            required
                        />
                        <q-select
                            v-model="form.school_id"
                            :options="schoolOptions"
                            label="School"
                            option-value="value"
                            option-label="label"
                            emit-value
                            map-options
                            :rules="[val => !!val || 'School is required']"
                            required
                        />
                        <q-toggle
                            v-model="form.active"
                            label="Active"
                        />
                        <q-card-actions align="right">
                            <q-btn flat label="Cancel" color="primary" @click="closeModal" />
                            <q-btn label="Save" type="submit" color="primary" />
                        </q-card-actions>
                    </q-form>
                </q-card-section>
            </q-card>
        </q-dialog>

        <q-dialog v-model="showDeleteModal" persistent>
            <q-card>
                <q-card-section>
                    <div class="text-h6">Delete Subject</div>
                </q-card-section>

                <q-card-section>
                    Are you sure you want to delete this subject? This action cannot be undone.
                </q-card-section>

                <q-card-actions align="right">
                    <q-btn flat label="Cancel" color="primary" @click="showDeleteModal = false" />
                    <q-btn label="Delete" color="negative" @click="deleteRecord" />
                </q-card-actions>
            </q-card>
        </q-dialog>
</template>

<script setup>
import { ref, computed, reactive } from 'vue';
import { router, Head } from '@inertiajs/vue3';
import debounce from 'lodash/debounce';
import { useQuasar } from 'quasar';
import { exportToExcel } from '@/Utils/exportHelper';

// Props
const props = defineProps({
    records: Object,
    schools: Array
});

const $q = useQuasar();

// State
const modalOpen = ref(false);
const showDeleteModal = ref(false);
const editing = ref(null);
const loading = ref(false);
const selectedRecord = ref(null);
const form = reactive({
    name: '',
    nour_name: '',
    school_id: null,
    active: true,
    description: '',
    notes: ''
});

const filters = ref({
    search: '',
    school_id: null,
    status: null
});

// Constants
const pageTitle = 'Subjects Management';
const baseUrl = '/admin/subject';

// Computed
const items = computed(() => props.records?.data || []);
const currentPage = computed(() => props.records?.current_page || 1);
const perPage = computed(() => props.records?.per_page || 10);

const schoolOptions = computed(() => [
    ...props.schools.map(school => ({
        value: school.id,
        label: school.name
    }))
]);

const tableColumns = [
    {
        name: 'name',
        label: 'Name',
        field: 'name',
        align: 'left'
    },
    {
        name: 'nour_name',
        label: 'Nour Name',
        field: 'nour_name'
    },
    {
        name: 'school',
        label: 'School',
        field: row => row.school?.name
    },
    {
        name: 'active',
        label: 'Status',
        field: 'active',
        format: val => val ? 'Active' : 'Inactive'
    },
    {
        name: 'actions',
        label: 'Actions',
        field: 'actions',
        align: 'right'
    }
];

const refreshData = () => {
    loading.value = true;
    router.get('/admin/subject', {
        ...filters.value,
        preserveState: true,
        preserveScroll: true,
        only: ['records']
    });
};

const debounceSearch = debounce(refreshData, 300);

const resetForm = () => {
    form.name = '';
    form.nour_name = '';
    form.school_id = null;
    form.active = true;
    form.description = '';
    form.notes = '';
};

const openModal = (record = null) => {
    editing.value = record;
    if (record) {
        form.name = record.name;
        form.nour_name = record.nour_name;
        form.school_id = record.school_id;
        form.active = record.active;
        form.description = record.description || '';
        form.notes = record.notes || '';
    } else {
        resetForm();
    }
    modalOpen.value = true;
};

const closeModal = () => {
    modalOpen.value = false;
    editing.value = null;
    resetForm();
};

const confirmDelete = (record) => {
    selectedRecord.value = record;
    showDeleteModal.value = true;
};

const deleteRecord = () => {
    if (!selectedRecord.value) return;

    router.delete(`${baseUrl}/${selectedRecord.value.id}`, {
        onSuccess: () => {
            $q.notify({
                type: 'positive',
                message: 'Subject deleted successfully'
            });
            showDeleteModal.value = false;
            selectedRecord.value = null;
        },
        onError: () => {
            $q.notify({
                type: 'negative',
                message: 'Failed to delete subject'
            });
        }
    });
};

const handleSubmit = () => {
    const id = editing.value?.id;
    const url = id ? `${baseUrl}/${id}` : baseUrl;
    const method = id ? 'put' : 'post';

    router[method](url, form, {
        onSuccess: () => {
            $q.notify({
                type: 'positive',
                message: `Subject ${id ? 'updated' : 'created'} successfully`
            });
            closeModal();
        },
        onError: (errors) => {
            Object.values(errors).forEach(errorMsg => {
                $q.notify({
                    type: 'negative',
                    message: errorMsg
                });
            });
        }
    });
};

const importSubjects = () => {
    // Implement import functionality
    $q.dialog({
        title: 'Import Subjects',
        message: 'Import functionality coming soon!'
    });
};

const exportData = () => {
    exportToExcel({
        items: items.value,
        columns: [
            { key: 'name', label: 'Name' },
            { key: 'nour_name', label: 'Nour Name' },
            { key: 'school.name', label: 'School' },
            { key: 'active', label: 'Status' }
        ],
        fileName: 'subjects',
        sheetName: 'Subjects'
    });
};

const manageLessonPlanTemplates = (subject) => {
    // Use the correct route name as defined in routes/r_hr.php
    router.get(route('admin.subject.lesson-plan-templates', subject.id));
};
</script>







