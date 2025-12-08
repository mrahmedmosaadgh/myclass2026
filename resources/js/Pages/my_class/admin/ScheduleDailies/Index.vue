<template>
    <AppLayout :title="pageTitle">
        <timeline
            title="Morning Schedule"
            v-model:events="scheduleEvents"
            @edit="handleEdit"
            @delete="handleDelete"
        />

        <!-- Add/Edit Modal -->
        <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 w-full max-w-md">
                <h3 class="text-lg font-semibold mb-4">
                    {{ editingEvent ? 'Edit Event' : 'Add New Event' }}
                </h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Title</label>
                        <input type="text" v-model="eventForm.title"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea v-model="eventForm.description"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Start Time</label>
                            <input type="time" v-model="eventForm.from"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">End Time</label>
                            <input type="time" v-model="eventForm.to"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button @click="closeModal" class="px-4 py-2 text-gray-600 hover:text-gray-800">
                        Cancel
                    </button>
                    <button @click="saveEvent" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                        {{ editingEvent ? 'Update' : 'Add' }}
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, reactive } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import timeline from './timelinev2.3/timeline_main.vue';

const pageTitle = "Schedule Daily Management";

const scheduleEvents = ref([
    {
        id: 1,
        title: 'Morning Meeting',
        description: 'Daily team standup',
        from: '08:00',
        to: '08:17',
        status: 'pending'
    },
    {
        id: 2,
        title: 'Project Review',
        description: 'Review Q3 deliverables',
        from: '08:20',  // Fixed format: added leading zero
        to: '08:30',
        status: 'pending'
    },
    {
        id: 3,
        title: 'Lunch Break',
        description: 'Team lunch',
        from: '08:35',
        to: '08:40',
        status: 'pending'
    },
    {
        id: 4,
        title: 'Client Meeting1',
        description: 'Product demo for client',
        from: '08:59',
        to: '09:30',
        status: 'pending'
    },
    {
        id: 5,
        title: 'Client Meeting2',
        description: 'Product demo for client',
        from: '09:35',
        to: '10:10',
        status: 'pending'
    },
    {
        id: 6,
        title: 'Client Meeting3',
        description: 'Product demo for client',
        from: '10:15',
        to: '10:30',
        status: 'pending'
    },
    {
        id: 7,
        title: 'Client Meeting4',
        description: 'Product demo for client',
        from: '10:35',
        to: '10:50',
        status: 'pending'
    },



]);

const showModal = ref(false);
const editingEvent = ref(null);
const eventForm = reactive({
    title: '',
    description: '',
    from: '',
    to: ''
});

const resetForm = () => {
    eventForm.title = '';
    eventForm.description = '';
    eventForm.from = '';
    eventForm.to = '';
};

const closeModal = () => {
    showModal.value = false;
    editingEvent.value = null;
    resetForm();
};

const handleEdit = (event) => {
    editingEvent.value = event;
    eventForm.title = event.title;
    eventForm.description = event.description;
    eventForm.from = event.from;
    eventForm.to = event.to;
    showModal.value = true;
};

const handleDelete = (eventId) => {
    if (confirm('Are you sure you want to delete this event?')) {
        scheduleEvents.value = scheduleEvents.value.filter(e => e.id !== eventId);
    }
};

const saveEvent = () => {
    if (!eventForm.title.trim() || !eventForm.from || !eventForm.to) {
        alert('Please fill in all required fields');
        return;
    }

    if (eventForm.from >= eventForm.to) {
        alert('End time must be after start time');
        return;
    }

    const eventData = {
        title: eventForm.title,
        description: eventForm.description,
        from: eventForm.from,
        to: eventForm.to,
        status: 'pending'
    };

    if (editingEvent.value) {
        scheduleEvents.value = scheduleEvents.value.map(e =>
            e.id === editingEvent.value.id ? { ...e, ...eventData } : e
        );
    } else {
        scheduleEvents.value.push({
            id: Date.now(),
            ...eventData
        });
    }

    closeModal();
};
</script>

















