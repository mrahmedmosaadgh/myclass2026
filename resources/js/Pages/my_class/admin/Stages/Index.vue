<template>
    <Head title="Stages" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <div class="flex justify-between items-center mb-6">
                        <PrimaryButton @click="openModal()">
                            Create New Stage
                        </PrimaryButton>
                    </div>

                    <DataTable
                        :columns="[
                            { key: 'name', label: 'Name' },
                            { key: 'description', label: 'Description' },
                            { key: 'school.name', label: 'School' },
                            { key: 'actions', label: 'Actions' }
                        ]"
                        :items="records.data"
                        :loading="false"
                        @edit="openModal"
                        @delete="deleteRecord"
                    />

                    <Pagination
                        v-if="records.meta"
                        :links="records.meta.links"
                        class="mt-6"
                    />
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
import Pagination from '@/Components/Pagination.vue';
import DataTable from '@/Components/Common/DataTable.vue';
import FormModal from '@/Components/Common/FormModal.vue';
import axios from 'axios';

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

const modelName = 'Stage';
const baseUrl = '/admin/stage';

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
        autofocus: true
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
        options: schoolOptions
    }
];

const modalOpen = ref(false);
const editing = ref(null);
const submitting = ref(false);

const openModal = (record = null) => {
    editing.value = record;
    modalOpen.value = true;
};

const closeModal = () => {
    modalOpen.value = false;
    editing.value = null;
};

const refreshData = () => {
    router.reload({
        only: ['records'],
        preserveScroll: true,
        preserveState: true
    });
};

const handleSubmit = async ({ form, onSuccess, onError }) => {
    if (submitting.value) return;

    submitting.value = true;
    const id = editing.value?.id;
    const url = id ? `/admin/stage/${id}` : '/admin/stage';

    try {
        await axios.post(url, {
            ...(id && { _method: 'PUT' }),
            ...form
        });

        onSuccess();
        closeModal();
        refreshData();
    } catch (error) {
        console.error('Submission error:', error);
        onError(error.response?.data?.errors || { error: ['An error occurred'] });
    } finally {
        submitting.value = false;
    }
};

const deleteRecord = async (record) => {
    if (!confirm('Are you sure you want to delete this stage?')) return;

    try {
        await axios.delete(`/admin/stage/${record.id}`);
        refreshData();
    } catch (error) {
        console.error('Deletion error:', error);
        alert('An error occurred while deleting the record.');
    }
};
</script>





