<template>
    <Head title="Period Activities" />
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <q-card flat bordered class="q-mb-md">
                <q-card-section>
                <div class="row items-center justify-between q-mb-md">
                    <div class="text-h5">Period Activities</div>
                    <q-btn
                        color="primary"
                        icon="add"
                        label="Add New"
                        @click="openModal()"
                    />
                </div>

                <q-table
                    :rows="items"
                    :columns="tableColumns"
                    :loading="loading"
                    row-key="id"
                    flat
                    bordered
                >
                    <template v-slot:body-cell-active="props">
                        <q-td :props="props">
                            <q-chip
                                :color="props.value ? 'positive' : 'negative'"
                                text-color="white"
                                dense
                            >
                                {{ props.value ? 'Active' : 'Inactive' }}
                            </q-chip>
                        </q-td>
                    </template>

                    <template v-slot:body-cell-actions="props">
                        <q-td :props="props">
                            <q-btn icon="edit" color="primary" flat dense @click.stop="openModal(props.row)" />
                            <q-btn icon="delete" color="negative" flat dense @click.stop="deleteRecord(props.row)" />
                        </q-td>
                    </template>
                </q-table>

                <q-pagination
                    v-if="pagination && pagination.last_page"
                    v-model="currentPage"
                    :max="pagination.last_page || 1"
                    @update:model-value="goToPage"
                    class="q-mt-md"
                />
            </q-card-section>
        </q-card>

        <q-dialog v-model="modalOpen" persistent>
            <q-card style="min-width: 500px">
                <q-card-section>
                    <div class="text-h6">{{ modelName }}</div>
                </q-card-section>

                <q-card-section>
                    <q-form class="q-gutter-md">
                        <div v-for="field in formFields" :key="field.name">
                            <!-- Text input -->
                            <q-input
                                v-if="field.type === 'text'"
                                v-model="formData[field.name]"
                                :label="field.label"
                                :rules="field.required ? [val => !!val || `${field.label} is required`] : []"
                                outlined
                            />

                            <!-- Textarea -->
                            <q-input
                                v-else-if="field.type === 'textarea'"
                                v-model="formData[field.name]"
                                :label="field.label"
                                type="textarea"
                                :rules="field.required ? [val => !!val || `${field.label} is required`] : []"
                                outlined
                                autogrow
                            />

                            <!-- Select -->
                            <q-select
                                v-else-if="field.type === 'select'"
                                v-model="formData[field.name]"
                                :options="field.options"
                                :label="field.label"
                                :rules="field.required ? [val => !!val || `${field.label} is required`] : []"
                                emit-value
                                map-options
                                outlined
                            />
                        </div>
                    </q-form>
                </q-card-section>

                <q-card-actions align="right">
                    <q-btn flat label="Cancel" color="grey" v-close-popup @click="closeModal" />
                    <q-btn flat label="Save" color="primary" @click="handleSubmit(formData)" />
                </q-card-actions>
            </q-card>
        </q-dialog>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, reactive } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useQuasar } from 'quasar';

const $q = useQuasar();
const props = defineProps({
    records: Object,
    options: Object
});

const items = computed(() => props.records?.data || []);
const pagination = computed(() => props.records?.links || null);
const currentPage = ref(1);
const loading = ref(false);
const modalOpen = ref(false);
const editing = ref(null);
const modelName = 'Period Activity';
const formData = reactive({});

const tableColumns = [
    { name: 'name', label: 'Name', field: 'name', sortable: true, align: 'left' },
    { name: 'description', label: 'Description', field: 'description', sortable: true, align: 'left' },
    { name: 'school.name', label: 'School', field: row => row.school?.name, sortable: true, align: 'left' },
    { name: 'active', label: 'Status', field: 'active', sortable: true, align: 'center' },
    { name: 'actions', label: 'Actions', field: 'actions', align: 'center' }
];

const formFields = [
    {
        name: 'name',
        label: 'Name',
        type: 'text',
        required: true
    },
    {
        name: 'description',
        label: 'Description',
        type: 'textarea'
    },
    {
        name: 'school_id',
        label: 'School',
        type: 'select',
        required: true,
        options: computed(() => props.options?.schools?.map(school => ({
            value: school.id,
            label: school.name
        })) || [])
    },
    {
        name: 'active',
        label: 'Status',
        type: 'select',
        options: computed(() => props.options?.statusOptions || [])
    }
];

// Initialize form data when opening modal
const openModal = (record = null) => {
    editing.value = record;

    // Reset form data
    Object.keys(formData).forEach(key => {
        delete formData[key];
    });

    // If editing, populate form with record data
    if (record) {
        formFields.forEach(field => {
            if (record[field.name] !== undefined) {
                formData[field.name] = record[field.name];
            }
        });
    }

    modalOpen.value = true;
};

const closeModal = () => {
    editing.value = null;
    modalOpen.value = false;
};

const handleSubmit = (data) => {
    loading.value = true;

    if (editing.value) {
        useForm(data).put(route('period-activities.update', editing.value.id), {
            onSuccess: () => {
                $q.notify({
                    color: 'positive',
                    message: 'Period activity updated successfully',
                    icon: 'check'
                });
                closeModal();
                loading.value = false;
            },
            onError: () => {
                $q.notify({
                    color: 'negative',
                    message: 'Error updating period activity',
                    icon: 'error'
                });
                loading.value = false;
            }
        });
    } else {
        useForm(data).post(route('period-activities.store'), {
            onSuccess: () => {
                $q.notify({
                    color: 'positive',
                    message: 'Period activity created successfully',
                    icon: 'check'
                });
                closeModal();
                loading.value = false;
            },
            onError: () => {
                $q.notify({
                    color: 'negative',
                    message: 'Error creating period activity',
                    icon: 'error'
                });
                loading.value = false;
            }
        });
    }
};

const deleteRecord = (record) => {
    $q.dialog({
        title: 'Confirm',
        message: 'Are you sure you want to delete this period activity?',
        cancel: true,
        persistent: true
    }).onOk(() => {
        loading.value = true;
        useForm().delete(route('period-activities.destroy', record.id), {
            onSuccess: () => {
                $q.notify({
                    color: 'positive',
                    message: 'Period activity deleted successfully',
                    icon: 'check'
                });
                loading.value = false;
            },
            onError: () => {
                $q.notify({
                    color: 'negative',
                    message: 'Error deleting period activity',
                    icon: 'error'
                });
                loading.value = false;
            }
        });
    });
};

// Handle pagination
const goToPage = (page) => {
    if (page) {
        window.location.href = route('period-activities.index', { page });
    }
};
</script>
