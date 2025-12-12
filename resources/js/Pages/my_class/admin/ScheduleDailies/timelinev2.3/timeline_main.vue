<template>
    <div class="p-6">
        <!-- Add/Edit Modal -->
        <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 w-full max-w-md">
                <h3 class="text-lg font-semibold mb-4">
                    {{ editingEvent ? 'Edit Event' : 'Add New Event' }}
                </h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                        <input
                            v-model="eventForm.title"
                            type="text"
                            class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea
                            v-model="eventForm.description"
                            class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        ></textarea>
                    </div>

                    <div class="flex gap-4">
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">From</label>
                            <input
                                v-model="eventForm.from"
                                type="time"
                                class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">To</label>
                            <input
                                v-model="eventForm.to"
                                type="time"
                                :min="eventForm.from"
                                class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button
                        @click="closeModal"
                        class="px-4 py-2 text-gray-600 hover:text-gray-800"
                    >
                        Cancel
                    </button>
                    <button
                        @click="saveEvent"
                        class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600"
                    >
                        {{ editingEvent ? 'Update' : 'Add' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Timeline Header -->
        <div class="mb-4 flex justify-between items-center">
            <h2 class="text-xl font-bold">{{ title || 'Daily Schedule' }}</h2>
            <button
                @click="handleAdd"
                class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors"
            >
                Add Event
            </button>
        </div>

        <!-- Timeline Component -->
        <timeline
            :events="sortedAndGroupedEvents"
            @add="handleAdd"
            @edit="handleEdit"
            @delete="handleDelete"
        />
    </div>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue';
import timeline from './timeline.vue';

const props = defineProps({
    title: {
        type: String,
        default: ''
    },
    events: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['update:events']);

// Convert time string to today's date with that time
const timeToDateTime = (timeStr) => {
    const today = new Date();
    const [hours, minutes] = timeStr.split(':');
    today.setHours(parseInt(hours), parseInt(minutes), 0, 0);
    return today;
};

const timelineEvents = ref([...props.events]);

// Watch for changes in props.events
watch(() => props.events, (newEvents) => {
    timelineEvents.value = [...newEvents];
}, { deep: true });

// Sort and group events to handle overlaps
const sortedAndGroupedEvents = computed(() => {
    // Sort events by from time
    const sorted = [...timelineEvents.value].sort((a, b) => {
        return timeToDateTime(a.from) - timeToDateTime(b.from);
    });

    // Group overlapping events
    return sorted.map(event => {
        const startTime = timeToDateTime(event.from);
        const endTime = timeToDateTime(event.to);

        // Find overlapping events
        const overlaps = sorted.filter(other => {
            if (other.id === event.id) return false;

            const otherStart = timeToDateTime(other.from);
            const otherEnd = timeToDateTime(other.to);

            return (startTime < otherEnd && endTime > otherStart);
        });

        // Calculate column position for overlapping events
        const column = overlaps.length > 0 ? overlaps.length : 0;
        const totalColumns = Math.max(column + 1, 1);

        return {
            ...event,
            column,
            totalColumns,
            startTime,
            endTime
        };
    });
});

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

const handleAdd = () => {
    resetForm();
    showModal.value = true;
};

const handleEdit = (event) => {
    editingEvent.value = event;
    eventForm.title = event.title;
    eventForm.description = event.description;
    eventForm.from = event.from;
    eventForm.to = event.to;
    showModal.value = true;
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

    const newStart = timeToDateTime(eventForm.from);
    const newEnd = timeToDateTime(eventForm.to);

    const overlappingEvent = timelineEvents.value.find(event => {
        if (editingEvent.value?.id === event.id) return false;

        const eventStart = timeToDateTime(event.from);
        const eventEnd = timeToDateTime(event.to);

        return (newStart < eventEnd && newEnd > eventStart);
    });

    if (overlappingEvent) {
        if (!confirm(`There is already an event "${overlappingEvent.title}" from ${overlappingEvent.from} to ${overlappingEvent.to}. Do you want to schedule anyway?`)) {
            return;
        }
    }

    const eventData = {
        title: eventForm.title,
        description: eventForm.description,
        from: eventForm.from,
        to: eventForm.to
    };

    let updatedEvents;
    if (editingEvent.value) {
        updatedEvents = timelineEvents.value.map(e =>
            e.id === editingEvent.value.id ? { ...e, ...eventData } : e
        );
    } else {
        updatedEvents = [...timelineEvents.value, {
            id: Date.now(),
            ...eventData
        }];
    }

    timelineEvents.value = updatedEvents;
    emit('update:events', updatedEvents);
    closeModal();
};

const handleDelete = (event) => {
    if (confirm('Are you sure you want to delete this event?')) {
        const updatedEvents = timelineEvents.value.filter(e => e.id !== event.id);
        timelineEvents.value = updatedEvents;
        emit('update:events', updatedEvents);
    }
};
</script>


