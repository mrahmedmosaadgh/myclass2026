<template>
    <AppLayout :title="pageTitle">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ pageTitle }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <div v-if="$page.props.flash?.success"
                         class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        {{ $page.props.flash.success }}
                    </div>

                    <div class="mb-6">
                        <PrimaryButton @click="openModal()">
                            Add New Record
                        </PrimaryButton>
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
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Pagination from '@/Components/Pagination.vue';
import DataTable from '@/Components/Common/DataTable.vue';
import FormModal from '@/Components/Common/FormModal.vue';
import axios from 'axios';

const props = defineProps({
    records: {
        type: Object,
        required: true
    },
    options: {
        type: Object,
        default: () => ({})
    }
});

// Computed properties to handle data structure
const items = computed(() => props.records?.data || []);
const pagination = computed(() => props.records?.links || null);

const pageTitle_main = 'HR';
const pageTitle = pageTitle_main + ' Management';
const modelName = 'Record';
const baseUrl = '/admin/hr'; // Fixed the URL to match your API endpoint

const tableColumns = [
    { key: 'name', label: 'Name' },
    { key: 'active', label: 'Status', type: 'status' }
];

const formFields = [
    {
        name: 'name',
        label: 'Name',
        type: 'text',
        required: true,
        autofocus: true
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

// If you have additional options (like users for a select field)
if (props.options.users) {
    formFields.push({
        name: 'user_id',
        label: 'User',
        type: 'select',
        required: true,
        options: props.options.users.map(user => ({
            value: user.id,
            label: user.name
        }))
    });
}

const modalOpen = ref(false);
const editing = ref(null);
const submitting = ref(false); // Add this at the top with other refs

const openModal = (record = null) => {
    editing.value = record;
    modalOpen.value = true;
};

const closeModal = () => {
    modalOpen.value = false;
    editing.value = null;
};

const refreshData = () => {
    // Use Inertia's preserveScroll to maintain scroll position
    router.reload({
        only: ['records'],  // Only refresh the records data
        preserveScroll: true,
        preserveState: true
    });
};

const handleSubmit = async ({ form, onSuccess, onError }) => {
    if (submitting.value) return; // Prevent double submission

    submitting.value = true;
    const id = editing.value?.id;
    const url = id ? `${baseUrl}/${id}` : baseUrl;

    try {
        await axios.post(url, {
            ...(id && { _method: 'PUT' }), // Only add _method for updates
            ...form
        });

        onSuccess();
        closeModal();
        refreshData();
    } catch (error) {
        onError(error);
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
</script>





























