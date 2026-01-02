<template>
    <AppLayout title="Schools">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Schools Management
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    
                    <!-- Top Bar -->
                    <div class="flex justify-between items-center mb-6">
                        <div class="flex space-x-2">
                             <q-input
                                v-model="search"
                                dense
                                outlined
                                placeholder="Search schools..."
                                class="w-64"
                            >
                                <template v-slot:append>
                                    <q-icon name="search" />
                                </template>
                            </q-input>
                        </div>
                        <q-btn
                            color="primary"
                            icon="add"
                            label="Add School"
                            @click="openModal()"
                        />
                    </div>

                    <!-- Schools Table -->
                    <q-table
                        :rows="schools.data"
                        :columns="columns"
                        row-key="id"
                        :filter="search"
                        :loading="loading"
                        flat
                        bordered
                    >
                        <!-- Status Column -->
                        <template v-slot:body-cell-status="props">
                            <q-td :props="props" class="text-center">
                                <q-badge
                                    :color="props.row.is_active ? 'positive' : 'negative'"
                                    :label="props.row.is_active ? 'Active' : 'Inactive'"
                                    class="q-px-sm q-py-xs"
                                />
                            </q-td>
                        </template>

                        <!-- Actions Column -->
                        <template v-slot:body-cell-actions="props">
                            <q-td :props="props" class="text-right">
                                <q-btn-group flat>
                                    <q-btn
                                        flat
                                        round
                                        size="sm"
                                        :color="props.row.is_active ? 'warning' : 'positive'"
                                        :icon="props.row.is_active ? 'block' : 'check_circle'"
                                        @click="toggleStatus(props.row)"
                                    >
                                        <q-tooltip>
                                            {{ props.row.is_active ? 'Deactivate' : 'Activate' }}
                                        </q-tooltip>
                                    </q-btn>
                                    
                                    <q-btn
                                        flat
                                        round
                                        size="sm"
                                        color="primary"
                                        icon="edit"
                                        @click="openModal(props.row)"
                                    >
                                        <q-tooltip>Edit</q-tooltip>
                                    </q-btn>
                                    
                                    <q-btn
                                        flat
                                        round
                                        size="sm"
                                        color="negative"
                                        icon="delete"
                                        @click="deleteSchool(props.row)"
                                    >
                                        <q-tooltip>Delete</q-tooltip>
                                    </q-btn>
                                </q-btn-group>
                            </q-td>
                        </template>
                    </q-table>
                    
                </div>
            </div>
        </div>

        <!-- Add/Edit Modal -->
        <q-dialog v-model="modalOpen" persistent>
            <q-card style="min-width: 500px">
                <q-card-section>
                    <div class="text-h6">{{ editing ? 'Edit School' : 'Add New School' }}</div>
                </q-card-section>

                <q-card-section class="q-pt-none">
                    <q-form @submit="submit" class="q-gutter-md">
                        
                        <!-- School Details -->
                        <q-input
                            v-model="formData.name"
                            label="School Name *"
                            outlined
                            :rules="[val => !!val || 'Name is required']"
                        />

                        <!-- Toggle HR Mode -->
                        <div class="q-my-md bg-gray-50 p-3 rounded border border-gray-200">
                             <q-toggle
                                v-model="formData.create_new_hr"
                                label="Create New HR Manager Account"
                                color="indigo"
                            />
                        </div>

                        <!-- HR Fields (Conditional) -->
                        <div v-if="formData.create_new_hr" class="q-gutter-md q-pl-md border-l-4 border-indigo-500 q-mb-md">
                            <div class="text-subtitle2 text-indigo-700 q-mb-sm">New HR Details</div>
                            <q-input
                                v-model="formData.hr_name"
                                label="HR Manager Name *"
                                outlined
                                dense
                                :rules="[val => !!val || 'HR Name is required']"
                            />
                            <q-input
                                v-model="formData.hr_email"
                                label="HR Email *"
                                type="email"
                                outlined
                                dense
                                :rules="[
                                    val => !!val || 'Email is required',
                                    val => /.+@.+\..+/.test(val) || 'Invalid email format'
                                ]"
                            />
                            <q-input
                                v-model="formData.hr_password"
                                label="Password *"
                                type="password"
                                outlined
                                dense
                                :rules="[val => !!val || 'Password is required']"
                            />
                        </div>

                        <!-- Select Existing HR -->
                        <div v-else>
                            <q-select
                                v-model="formData.h_r_id"
                                :options="hrOptions"
                                label="Assign Existing HR *"
                                outlined
                                emit-value
                                map-options
                                :rules="[val => !!val || 'Please select an HR Manager']"
                            />
                        </div>

                    </q-form>
                </q-card-section>

                <q-card-actions align="right" class="text-primary">
                    <q-btn flat label="Cancel" v-close-popup @click="resetForm" />
                    <q-btn 
                        color="primary" 
                        :label="editing ? 'Update School' : 'Create School'" 
                        :loading="submitting"
                        @click="submit"
                    />
                </q-card-actions>
            </q-card>
        </q-dialog>

    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { useQuasar } from 'quasar';
import AppLayout from '@/Layouts/AppLayout.vue';
import axios from 'axios';

const $q = useQuasar();
const page = usePage();

const props = defineProps({
    schools: {
        type: Object,
        required: true
    },
    hrs: {
        type: Array,
        required: true
    }
});

// Table Configuration
const columns = [
    { name: 'name', required: true, label: 'School Name', align: 'left', field: 'name', sortable: true },
    { name: 'hr', align: 'left', label: 'Assigned HR', field: row => row.hr?.name || 'N/A', sortable: true },
    { name: 'status', align: 'center', label: 'Status', field: 'is_active', sortable: true },
    { name: 'actions', align: 'right', label: 'Actions' }
];

const hrOptions = computed(() => props.hrs.map(hr => ({
    label: hr.name,
    value: hr.id
})));

const search = ref('');
const loading = ref(false);
const modalOpen = ref(false);
const editing = ref(null);
const submitting = ref(false);

const formData = ref({
    name: '',
    h_r_id: null,
    create_new_hr: false,
    hr_name: '',
    hr_email: '',
    hr_password: ''
});

const resetForm = () => {
    formData.value = {
        name: '',
        h_r_id: null,
        create_new_hr: false,
        hr_name: '',
        hr_email: '',
        hr_password: ''
    };
    editing.value = null;
    modalOpen.value = false;
};

const openModal = (school = null) => {
    editing.value = school;
    if (school) {
        formData.value = {
            name: school.name,
            h_r_id: school.h_r_id,
            create_new_hr: false, // Default to false when editing
            hr_name: '',
            hr_email: '',
            hr_password: ''
        };
    } else {
        formData.value = {
            name: '',
            h_r_id: null,
            create_new_hr: false,
            hr_name: '',
            hr_email: '',
            hr_password: ''
        };
    }
    modalOpen.value = true;
};

const refreshData = () => {
    router.reload({
        only: ['schools', 'hrs'],
        preserveScroll: true,
        preserveState: true
    });
};

const submit = async () => {
    // Basic frontend validation
    if (!formData.value.name) {
        $q.notify({ type: 'warning', message: 'School Name is required' });
        return;
    }
    if (formData.value.create_new_hr) {
        if (!formData.value.hr_name || !formData.value.hr_email || !formData.value.hr_password) {
             $q.notify({ type: 'warning', message: 'All HR details are required' });
             return;
        }
    } else {
        if (!formData.value.h_r_id) {
             $q.notify({ type: 'warning', message: 'Please select an HR Manager' });
             return;
        }
    }

    submitting.value = true;
    const url = `/admin/school${editing.value ? `/${editing.value.id}` : ''}`;
    
    const data = {
        ...formData.value,
        ...(editing.value && { _method: 'PUT' })
    };

    try {
        const response = await axios.post(url, data);
        
        $q.notify({
            type: 'positive',
            message: response.data.message || (editing.value ? 'School updated successfully' : 'School created successfully')
        });
        
        closeModal();
        refreshData();
    } catch (error) {
        const message = error.response?.data?.message || 'An error occurred while saving.';
        $q.notify({ type: 'negative', message });
        
        // Handle validation errors if any (could display field-specific errors too)
        if (error.response?.data?.errors) {
            console.error(error.response.data.errors);
        }
    } finally {
        submitting.value = false;
    }
};

const toggleStatus = (school) => {
    const action = school.is_active ? 'Deactivate' : 'Activate';
    
    $q.dialog({
        title: 'Confirm Action',
        message: `Are you sure you want to ${action.toLowerCase()} <strong>${school.name}</strong>?`,
        html: true,
        cancel: true,
        persistent: true
    }).onOk(() => {
        axios.post(`/admin/school/${school.id}/toggle-status`)
            .then(response => {
                $q.notify({
                    type: 'positive',
                    message: response.data.message || `School ${action}d successfully`
                });
                refreshData();
            })
            .catch(error => {
                $q.notify({
                    type: 'negative',
                    message: 'Failed to update status'
                });
            });
    });
};

const deleteSchool = (school) => {
    $q.dialog({
        title: 'Delete School',
        message: `Are you sure you want to delete <strong>${school.name}</strong>? This cannot be undone.`,
        html: true,
        ok: { label: 'Delete', color: 'negative' },
        cancel: true,
        persistent: true
    }).onOk(() => {
        const data = new FormData();
        data.append('_method', 'DELETE');

        axios.post(`/admin/school/${school.id}`, data)
            .then(response => {
                 $q.notify({
                    type: 'positive',
                    message: 'School deleted successfully'
                });
                refreshData();
            })
            .catch(error => {
                $q.notify({
                    type: 'negative',
                    message: 'Failed to delete school'
                });
            });
    });
};
</script>
