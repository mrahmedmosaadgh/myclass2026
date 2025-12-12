<template>
    <AppLayout title="Schools">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Schools
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <div class="flex justify-end mb-4">
                        <PrimaryButton @click="openModal()">
                            Add School
                        </PrimaryButton>
                    </div>

                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Name
                                </th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    HR
                                </th>
                                <th class="px-6 py-3 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="school in schools.data" :key="school.id">
                                <td class="px-6 py-4 whitespace-nowrap">{{ school.name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ school.hr?.name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <button @click="openModal(school)" class="text-indigo-600 hover:text-indigo-900">
                                        Edit
                                    </button>
                                    <button @click="deleteSchool(school)" class="ml-4 text-red-600 hover:text-red-900">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <DialogModal :show="modalOpen" @close="closeModal">
            <template #title>
                {{ editing ? 'Edit School' : 'Add School' }}
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
                    <InputLabel for="h_r_id" value="HR" />
                    <select
                        id="h_r_id"
                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        v-model="formData.h_r_id"
                        required
                    >
                        <option value="">Select HR</option>
                        <option v-for="hr in hrs" :key="hr.id" :value="hr.id">
                            {{ hr.name }}
                        </option>
                    </select>
                    <InputError :message="errors.h_r_id?.[0]" class="mt-2" />
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
import { ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DialogModal from '@/Components/DialogModal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import axios from 'axios';

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

const modalOpen = ref(false);
const editing = ref(null);
const submitting = ref(false);
const errors = ref({});

const formData = ref({
    name: '',
    h_r_id: ''
});

const resetForm = () => {
    formData.value = {
        name: '',
        h_r_id: ''
    };
    errors.value = {};
};

const openModal = (school = null) => {
    editing.value = school;
    if (school) {
        formData.value = {
            name: school.name,
            h_r_id: school.h_r_id
        };
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
    router.reload({
        only: ['schools', 'hrs'],
        preserveScroll: true,
        preserveState: true
    });
};

const submit = () => {
    submitting.value = true;
    const url = `/admin/school${editing.value ? `/${editing.value.id}` : ''}`;

    const data = {
        ...formData.value,
        ...(editing.value && { _method: 'PUT' })  // Add _method field for PUT requests
    };

    axios.post(url, data)
        .then(response => {
            closeModal();
            if (response.data.message) {
                alert(response.data.message);
            }
            refreshData();
        })
        .catch(error => {
            if (error.response?.data?.errors) {
                errors.value = error.response.data.errors;
            } else {
                console.error('Submission error:', error);
                alert('An error occurred while saving the record.');
            }
            submitting.value = false;
        });
};

const deleteSchool = (school) => {
    if (confirm('Are you sure you want to delete this school?')) {
        const data = new FormData();
        data.append('_method', 'DELETE');

        axios.post(`/admin/school/${school.id}`, data)
            .then(response => {
                if (response.data.message) {
                    alert(response.data.message);
                }
                refreshData();
            })
            .catch(error => {
                console.error('Deletion error:', error);
                alert('An error occurred while deleting the record.');
            });
    }
};
</script>









