<template>
    <AppLayout title="HR Management">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                HR Management
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <!-- Success Message -->
                    <div v-if="$page.props.flash?.success"
                         class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        {{ $page.props.flash.success }}
                    </div>

                    <!-- Add New HR Button -->
                    <div class="mb-6">
                        <PrimaryButton @click="openModal()">
                            Add New HR Record
                        </PrimaryButton>
                    </div>

                    <!-- HR Records Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="hr in hrs.data" :key="hr.id">
                                    <td class="px-6 py-4 whitespace-nowrap">{{ hr.name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ hr.user?.name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="hr.active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                              class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                            {{ hr.active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <button @click="openModal(hr)"
                                                class="text-indigo-600 hover:text-indigo-900 mr-3">
                                            Edit
                                        </button>
                                        <button @click="deleteHR(hr)"
                                                class="text-red-600 hover:text-red-900">
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4" v-if="hrs.links">
                        <Pagination :links="hrs.links" />
                    </div>
                </div>
            </div>
        </div>

        <!-- HR Modal -->
        <DialogModal :show="modalOpen" @close="closeModal">
            <template #title>
                {{ editing ? 'Edit HR Record' : 'Create New HR Record' }}
            </template>

            <template #content>
                <div class="mt-4">
                    <InputLabel for="name" value="Name" />
                    <TextInput
                        id="name"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="formData.name"
                        required
                        autofocus
                    />
                    <InputError :message="errors.name?.[0]" class="mt-2" />
                </div>

                <div class="mt-4">
                    <InputLabel for="user_id" value="User" />
                    <select
                        id="user_id"
                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        v-model="formData.user_id"
                        required
                    >
                        <option value="">Select a user</option>
                        <option v-for="user in users" :key="user.id" :value="user.id">
                            {{ user.name }}
                        </option>
                    </select>
                    <InputError :message="errors.user_id?.[0]" class="mt-2" />
                </div>

                <div class="mt-4">
                    <InputLabel for="active" value="Status" />
                    <select
                        id="active"
                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        v-model="formData.active"
                    >
                        <option :value="true">Active</option>
                        <option :value="false">Inactive</option>
                    </select>
                    <InputError :message="errors.active?.[0]" class="mt-2" />
                </div>
            </template>

            <template #footer>
                <SecondaryButton @click="closeModal">Cancel</SecondaryButton>
                <PrimaryButton
                    class="ml-3"
                    :class="{ 'opacity-25': submitting }"
                    :disabled="submitting"
                    @click="submit"
                >
                    Save
                </PrimaryButton>
            </template>
        </DialogModal>
    </AppLayout>
</template>

<script setup>
import { ref, watch } from 'vue';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';
import DialogModal from '@/Components/DialogModal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Pagination from '@/Components/Pagination.vue';
import { router } from '@inertiajs/vue3';

// Define props with proper types
const props = defineProps({
    hrs: {
        type: Object,
        required: true
    },
    users: {  // Add this prop
        type: Array,
        required: true
    },
    errors: {
        type: Object,
        default: () => ({})
    },
    jetstream: {
        type: Object,
        required: true
    },
    auth: {
        type: Object,
        required: true
    }
});

// Add emits definition
const emit = defineEmits(['update:hrs', 'update:users']);

// Reactive references
const modalOpen = ref(false);
const editing = ref(null);
const submitting = ref(false);
const errors = ref({}); // Add this line

// Form initialization
const formData = ref({
    id: null,
    name: '',
    user_id: '',
    active: true
});

// Reset form helper
const resetForm = () => {
    formData.value = {
        id: null,
        name: '',
        user_id: '',
        active: true
    };
    errors.value = {};
};

// Open modal function
const openModal = (hr = null) => {
    console.log('Opening modal with HR:', hr);
    editing.value = hr;
    if (hr) {
        formData.value = {
            id: hr.id,
            name: hr.name,
            user_id: hr.user_id,
            active: hr.active === 1 || hr.active === true
        };
        console.log('Set form data:', formData.value);
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

const refreshData = () => {
    router.visit(window.location.pathname, {
        preserveScroll: true,
        preserveState: false,
    });
};

const submit = () => {
    submitting.value = true;

    const id = editing.value?.id;
    const url = editing.value ? `/admin/hr/${id}` : '/admin/hr';

    // For PUT requests, use post with _method
    if (editing.value) {
        axios.post(url, {
            _method: 'PUT',
            ...formData.value
        })
        .then(response => {
            closeModal();
            if (response.data.message) {
                alert(response.data.message);
            }
            refreshData();
        })
        .catch(error => {
            console.log('Full error object:', error);
            if (error.response?.data?.errors) {
                errors.value = error.response.data.errors;
            } else {
                console.error('Submission error:', error);
                alert('An error occurred while saving the record.');
            }
            submitting.value = false;
        });
    } else {
        // For new records, use regular post
        axios.post(url, formData.value)
        .then(response => {
            closeModal();
            if (response.data.message) {
                alert(response.data.message);
            }
            refreshData();
        })
        .catch(error => {
            console.log('Full error object:', error);
            if (error.response?.data?.errors) {
                errors.value = error.response.data.errors;
            } else {
                console.error('Submission error:', error);
                alert('An error occurred while saving the record.');
            }
            submitting.value = false;
        });
    }
};

const deleteHR = (hr) => {
    if (confirm('Are you sure you want to delete this record?')) {
        axios.delete(`/admin/hr/${hr.id}`)
            .then(response => {
                if (response.data.message) {
                    alert(response.data.message);
                }
                refreshData(); // Replace window.location.reload()
            })
            .catch(error => {
                console.error('Deletion error:', error);
                alert('An error occurred while deleting the record.');
            });
    }
};
</script>

























