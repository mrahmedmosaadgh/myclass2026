<template>
    <AppLayout3 :title="pageTitle">ffffffff
        <template #header>
            <q-toolbar>
                <q-toolbar-title class="text-h6">
                    {{ pageTitle }}
                </q-toolbar-title>
            </q-toolbar>
        </template>

        <div class="q-pa-md">
            <q-card class="q-pa-md">
                <q-banner v-if="$page.props.flash?.success" class="bg-green-1 text-green">
                    {{ $page.props.flash.success }}
                </q-banner>

                <div class="row justify-between items-center q-mb-md">
                    <div class="row q-gutter-sm">
                        <q-btn color="primary" @click="openModal()" label="Add New Teacher" ref="addTeacherButtonRef" />
                        <ImportExcel
                            @imported="refreshData"
                            :validate-url="baseUrl + '/validate-import'"
                            :import-url="baseUrl + '/import'"
                            :columns="importColumns"
                            button-text="Import Teachers"
                            preview-title="Preview Teacher Data"
                        />
                        <q-btn color="secondary" @click="exportData" label="Export" />
                    </div>
                </div>

                <q-table
                    :rows="items"
                    :columns="tableColumns"
                    @row-click="(evt, row) => openModal(row)"
                    flat
                    bordered
                >
                    <template v-slot:body-cell-actions="props">
                        <q-td :props="props">
                            <q-btn icon="edit" @click="openModal(props.row)" flat dense/>
                            <q-btn icon="delete" @click="deleteRecord(props.row)" flat dense/>
                        </q-td>
                    </template>
                </q-table>

                <q-pagination 
                    v-if="pagination"
                    v-model="currentPage" 
                    :max="pagination.last_page"
                    @update:model-value="goToPage"
                    class="q-mt-md"
                />
            </q-card>
        </div>

        <q-dialog v-model="modalOpen" persistent>
            <q-card style="min-width: 500px">
                <q-card-section>
                    <div class="text-h6">{{ modelName }}</div>
                </q-card-section>

                <q-card-section>
                    <q-form @submit="handleSubmit">
                        <div class="row q-gutter-md">
                            <div class="col-6">
                                <q-input 
                                    v-model="formFields[0].value" 
                                    :label="formFields[0].label" 
                                    :type="formFields[0].type" 
                                    :rules="formFields[0].required ? [val => !!val || 'Field is required'] : []"
                                />
                            </div>
                            <div class="col-6">
                                <q-input 
                                    v-model="formFields[1].value" 
                                    :label="formFields[1].label" 
                                    :type="formFields[1].type" 
                                    :rules="formFields[1].required ? [val => !!val || 'Field is required'] : []"
                                />
                            </div>
                        </div>
                        <div class="row q-gutter-md">
                            <div class="col-6">
                                <q-select 
                                    v-model="formFields[2].value" 
                                    :label="formFields[2].label" 
                                    :options="schoolOptions" 
                                    :rules="formFields[2].required ? [val => !!val || 'Field is required'] : []"
                                />
                            </div>
                            <div class="col-6">
                                <q-input 
                                    v-model="formFields[3].value" 
                                    :label="formFields[3].label" 
                                    :type="formFields[3].type" 
                                    :rules="formFields[3].required ? [val => !!val || 'Field is required'] : []"
                                />
                            </div>
                        </div>
                        <div class="row q-gutter-md">
                            <div class="col-6">
                                <q-input 
                                    v-model="formFields[4].value" 
                                    :label="formFields[4].label" 
                                    :type="formFields[4].type" 
                                    :rules="formFields[4].required ? [val => !!val || 'Field is required'] : []"
                                />
                            </div>
                            <div class="col-6">
                                <q-input 
                                    v-model="formFields[5].value" 
                                    :label="formFields[5].label" 
                                    :type="formFields[5].type" 
                                    :rules="formFields[5].required ? [val => !!val || 'Field is required'] : []"
                                />
                            </div>
                        </div>
                        <div class="row q-gutter-md">
                            <div class="col-6">
                                <q-select 
                                    v-model="formFields[6].value" 
                                    :label="formFields[6].label" 
                                    :options="formFields[6].options" 
                                    :rules="formFields[6].required ? [val => !!val || 'Field is required'] : []"
                                />
                            </div>
                            <div class="col-6">
                                <q-input 
                                    v-model="formFields[7].value" 
                                    :label="formFields[7].label" 
                                    :type="formFields[7].type" 
                                    :rules="formFields[7].required ? [val => !!val || 'Field is required'] : []"
                                />
                            </div>
                        </div>
                        <div class="row q-gutter-md">
                            <div class="col-6">
                                <q-input 
                                    v-model="formFields[8].value" 
                                    :label="formFields[8].label" 
                                    :type="formFields[8].type" 
                                    :rules="formFields[8].required ? [val => !!val || 'Field is required'] : []"
                                />
                            </div>
                            <div class="col-6">
                                <q-input 
                                    v-model="formFields[9].value" 
                                    :label="formFields[9].label" 
                                    :type="formFields[9].type" 
                                    :rules="formFields[9].required ? [val => !!val || 'Field is required'] : []"
                                />
                            </div>
                        </div>
                        <div class="row q-gutter-md">
                            <div class="col-12">
                                <q-input 
                                    v-model="formFields[10].value" 
                                    :label="formFields[10].label" 
                                    :type="formFields[10].type" 
                                    :rules="formFields[10].required ? [val => !!val || 'Field is required'] : []"
                                />
                            </div>
                        </div>
                        <div class="row q-gutter-md">
                            <div class="col-12">
                                <q-input 
                                    v-model="formFields[11].value" 
                                    :label="formFields[11].label" 
                                    :type="formFields[11].type" 
                                    :rules="formFields[11].required ? [val => !!val || 'Field is required'] : []"
                                />
                            </div>
                        </div>
                        <div class="q-mt-md row justify-end">
                            <q-btn flat label="Cancel" @click="closeModal" />
                            <q-btn type="submit" color="primary" label="Save" />
                        </div>
                    </q-form>
                </q-card-section>
            </q-card>
        </q-dialog>
    </AppLayout3>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import NProgress from 'nprogress';
import 'nprogress/nprogress.css';
// import AppLayout3 from '@/Layouts/AppLayout.vue';
import ImportExcel from '@/Components/Common/ImportExcel.vue';
import { useQuasar } from 'quasar';

const $q = useQuasar();

const props = defineProps({
    records: {
        type: Object,
        required: true
    },
    schools: {
        type: Array,
        required: true
    }
});

const items = computed(() => props.records?.data || []);
const pagination = computed(() => props.records?.links || null);

const pageTitle = 'Teachers Management';
const modelName = 'Teacher';
const baseUrl = '/admin/teacher';

const tableColumns = [
    { key: 'name', label: 'Name' },
    { key: 'name_ar', label: 'Arabic Name' },
    { key: 'school.name', label: 'School' },
    { key: 'email', label: 'Email' },
    { key: 'phone_number', label: 'Phone' },
    { key: 'gender', label: 'Gender' },
    { key: 'actions', label: 'Actions' }
];

const schoolOptions = computed(() =>
    props.schools.map(school => ({
        value: school.id,
        label: school.name
    }))
);

const formFields = [
    {
        name: 'name',
        label: 'Name',
        type: 'text',
        required: true,
        value: ''
    },
    {
        name: 'name_ar',
        label: 'Arabic Name',
        type: 'text',
        value: ''
    },
    {
        name: 'school_id',
        label: 'School',
        type: 'select',
        required: true,
        options: schoolOptions,
        value: ''
    },
    {
        name: 'email',
        label: 'Email',
        type: 'email',
        value: ''
    },
    {
        name: 'phone_number',
        label: 'Phone Number',
        type: 'text',
        value: ''
    },
    {
        name: 'whatsapp_number',
        label: 'WhatsApp Number',
        type: 'text',
        value: ''
    },
    {
        name: 'gender',
        label: 'Gender',
        type: 'select',
        options: [
            { value: 'male', label: 'Male' },
            { value: 'female', label: 'Female' }
        ],
        value: ''
    },
    {
        name: 'nationality',
        label: 'Nationality',
        type: 'text',
        value: ''
    },
    {
        name: 'date_of_birth',
        label: 'Date of Birth',
        type: 'date',
        value: ''
    },
    {
        name: 'address',
        label: 'Address',
        type: 'textarea',
        value: ''
    },
    {
        name: 'notes',
        label: 'Notes',
        type: 'textarea',
        value: ''
    }
];

const modalOpen = ref(false);
const editing = ref(null);
const submitting = ref(false);
const currentPage = ref(1);
const addTeacherButtonRef = ref(null);

const openModal = (record = null) => {
    editing.value = record;
    modalOpen.value = true;
    if (record) {
        formFields.forEach(field => {
            field.value = record[field.name];
        });
    } else {
        formFields.forEach(field => {
            field.value = '';
        });
    }
};

const closeModal = () => {
    modalOpen.value = false;
    editing.value = null;
};

const handleSubmit = ({ }, onSuccess, onError) => {
    if (submitting.value) return; // Prevent double submission

    submitting.value = true;
    NProgress.start();

    const url = editing.value ? `${baseUrl}/${editing.value.id}` : baseUrl;

    axios({
        method: editing.value ? 'put' : 'post',
        url: url,
        data: formFields.reduce((acc, field) => ({ ...acc, [field.name]: field.value }), {})
    })
        .then(response => {
            onSuccess();
            closeModal();
            refreshData();
        })
        .catch(error => {
            if (error.response?.data?.errors) {
                onError(error.response.data.errors);
            } else {
                onError({ error: ['An unexpected error occurred'] });
            }
        })
        .finally(() => {
            submitting.value = false;
            NProgress.done();
        });
};

const deleteRecord = (record) => {
    if (!$q.dialog({
        title: 'Confirm',
        message: 'Are you sure you want to delete this record?',
        cancel: true,
        persistent: true
    }).then(() => true).catch(() => false)) return;

    NProgress.start();

    axios.delete(`${baseUrl}/${record.id}`)
        .then(() => {
            refreshData();
        })
        .catch(error => {
            console.error('Error:', error);
            $q.notify({
                type: 'negative',
                message: 'An error occurred while deleting the record.'
            });
        })
        .finally(() => {
            NProgress.done();
        });
};

const refreshData = () => {
    router.reload({
        only: ['records'],
        preserveScroll: true,
        preserveState: true
    });
};

const exportData = () => {
    window.location.href = `${baseUrl}/export`;
};

const goToPage = (page) => {
    router.get(`${baseUrl}?page=${page}`);
};

const importColumns = [
    { key: 'name', label: 'Name' },
    { key: 'school', label: 'school' }
];
</script>
