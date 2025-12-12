<template>
    <AppLayout title="School Sections">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                School Sections
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <div class="flex justify-end mb-4">
                        <PrimaryButton @click="openModal()">
                            Add Section
                        </PrimaryButton>
                    </div>

                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Name
                                </th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    School
                                </th>
                                <th class="px-6 py-3 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="section in sections.data" :key="section.id">
                                <td class="px-6 py-4 whitespace-nowrap">{{ section.name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ section.school?.name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <button @click="openModal(section)" class="text-indigo-600 hover:text-indigo-900">
                                        Edit
                                    </button>
                                    <button @click="deleteSection(section)" class="ml-4 text-red-600 hover:text-red-900">
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
                {{ editing ? 'Edit Section' : 'Add Section' }}
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
                    <InputLabel for="school_id" value="School" />
                    <select
                        id="school_id"
                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        v-model="formData.school_id"
                        required
                    >
                        <option value="">Select School</option>
                        <option v-for="school in schools" :key="school.id" :value="school.id">
                            {{ school.name }}
                        </option>
                    </select>
                    <InputError :message="errors.school_id?.[0]" class="mt-2" />
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
import AppLayout from '@/Layouts/AppLayout.vue';
import DialogModal from '@/Components/DialogModal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import axios from 'axios';

const props = defineProps({
    sections: {
        type: Object,
        required: true
    },
    schools: {
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
    school_id: '',
    data: null
});

const resetForm = () => {
    formData.value = {
        name: '',
        school_id: '',
        data: null
    };
    errors.value = {};
};

const openModal = (section = null) => {
    editing.value = section;
    if (section) {
        formData.value = {
            name: section.name,
            school_id: section.school_id,
            data: section.data
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
    axios.get(window.location.pathname)
        .then(response => {
            props.sections = response.data.sections;
            props.schools = response.data.schools;
        })
        .catch(error => {
            console.error('Error refreshing data:', error);
            alert('An error occurred while refreshing the data.');
        });
};

const submit = () => {
    submitting.value = true;
    const url = editing.value
        ? `/admin/school_section/${editing.value.id}`
        : '/admin/school_section';

    const method = editing.value ? 'put' : 'post';

    axios[method](url, formData.value)
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

const deleteSection = (section) => {
    if (confirm('Are you sure you want to delete this section?')) {
        axios.delete(`/admin/school_section/${section.id}`)
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

