
<template>
    <div class="q-pa-md">
        <q-card class="q-mb-md">
            <q-card-section>
                <!-- Header with Actions -->
                <div class="row justify-between items-center q-mb-md">
                    <div class="col-auto">
                        <div class="text-h4 text-weight-bold">{{ pageTitle }}</div>
                        <div class="text-caption text-grey-6">Manage academic calendar entries and events</div>
                    </div>
                    <div class="col-auto">
                        <div class="row q-gutter-sm items-center">
                            <!-- Edit Mode Toggle -->
                            <q-card flat bordered class="q-pa-sm">
                                <div class="row items-center q-gutter-sm">
                                    <q-icon
                                        :name="editMode ? 'edit' : 'visibility'"
                                        :color="editMode ? 'positive' : 'grey-6'"
                                        size="sm"
                                    />
                                    <span class="text-body2 text-weight-medium">
                                        {{ editMode ? 'Edit Mode' : 'View Mode' }}
                                    </span>
                                    <q-toggle
                                        v-model="editMode"
                                        color="positive"
                                        size="sm"
                                        :icon="editMode ? 'edit' : 'visibility'"
                                    />
                                </div>
                            </q-card>

                            <q-separator vertical inset />

                            <q-btn
                                color="primary"
                                icon="add"
                                label="Add New Entry"
                                @click="openModal()"
                                :loading="isLoading"
                                :disable="!editMode"
                                unelevated
                            />
                            <q-btn
                                color="secondary"
                                icon="upload"
                                label="Import Events"
                                @click="showImportDialog = true"
                                :disable="isLoading || !editMode"
                                unelevated
                            />
                            <q-btn
                                color="accent"
                                icon="download"
                                label="Export"
                                @click="exportData"
                                :disable="isLoading || !items.length"
                                unelevated
                            />
                        </div>
                    </div>
                </div>

                <!-- Upcoming Events and Legend Row -->
                <div class="row q-gutter-md q-mb-md">
                    <!-- Upcoming Events Card -->
                    <div class="col-12 col-md-4">
                        <q-card flat class="upcoming-events-card" style="background: linear-gradient(135deg, #fff7ed 0%, #fed7aa 100%); border: 1px solid #fb923c;">
                            <q-card-section class="q-pa-lg">
                                <div class="row items-center justify-between q-mb-md">
                                    <div class="text-h6 text-weight-medium text-grey-8">
                                        <q-icon name="schedule" class="q-mr-sm" color="orange" />
                                        Upcoming Events
                                    </div>
                                    <q-chip
                                        outline
                                        color="orange"
                                        text-color="orange"
                                        size="sm"
                                        class="text-weight-medium"
                                    >
                                        Next {{ upcomingEvents.length }}
                                    </q-chip>
                                </div>

                                <div v-if="upcomingEvents.length === 0" class="text-center q-py-md">
                                    <q-icon name="event_available" size="48px" color="grey-4" />
                                    <div class="text-body2 text-grey-6 q-mt-sm">No upcoming events</div>
                                </div>

                                <div v-else class="column q-gutter-sm">
                                    <div
                                        v-for="event in upcomingEvents"
                                        :key="event.id"
                                        class="upcoming-event-item"
                                    >
                                        <q-card
                                            flat
                                            class="upcoming-event-card q-pa-sm cursor-pointer"
                                            :style="{
                                                background: 'white',
                                                border: `2px solid ${getEventColor(event.type)}`,
                                                borderRadius: '8px'
                                            }"
                                            @click="openModal(event)"
                                        >
                                            <div class="row items-center no-wrap q-gutter-sm">
                                                <q-avatar
                                                    :style="{
                                                        background: getEventColor(event.type),
                                                        boxShadow: `0 2px 8px ${getEventColor(event.type)}40`
                                                    }"
                                                    size="32px"
                                                >
                                                    <q-icon
                                                        :name="getEventIcon(event.type)"
                                                        color="white"
                                                        size="16px"
                                                    />
                                                </q-avatar>
                                                <div class="column flex-1">
                                                    <div class="text-weight-medium text-body2 text-grey-8">
                                                        {{ event.title }}
                                                    </div>
                                                    <div class="text-caption text-grey-6">
                                                        {{ formatEventDate(event.date) }}
                                                    </div>
                                                </div>
                                                <div class="column items-end">
                                                    <q-chip
                                                        :color="getDaysRemainingColor(event.daysRemaining)"
                                                        text-color="white"
                                                        size="sm"
                                                        class="text-weight-bold"
                                                    >
                                                        {{ event.daysRemaining === 0 ? 'Today' :
                                                           event.daysRemaining === 1 ? 'Tomorrow' :
                                                           `${event.daysRemaining} days` }}
                                                    </q-chip>
                                                </div>
                                            </div>
                                        </q-card>
                                    </div>
                                </div>
                            </q-card-section>
                        </q-card>
                    </div>

                    <!-- Calendar Legend -->
                    <div class="col-12 col-md-8">
                        <q-card flat class="legend-card" style="background: linear-gradient(135deg, #f8f9ff 0%, #f0f4ff 100%); border: 1px solid #e1e8ff;">
                            <q-card-section class="q-pa-lg">
                                <div class="row items-center justify-between q-mb-md">
                                    <div class="text-h6 text-weight-medium text-grey-8">
                                        <q-icon name="palette" class="q-mr-sm" color="primary" />
                                        Event Types
                                    </div>
                                    <q-chip
                                        outline
                                        color="primary"
                                        text-color="primary"
                                        size="sm"
                                        class="text-weight-medium"
                                    >
                                        {{ events.length }} Events
                                    </q-chip>
                                </div>

                                <div class="row q-gutter-md items-center">
                                    <div
                                        v-for="eventType in legendItems"
                                        :key="eventType.type"
                                        class="legend-item"
                                        :class="{ 'legend-item-active': hoveredType === eventType.type }"
                                        @mouseenter="hoveredType = eventType.type"
                                        @mouseleave="hoveredType = null"
                                    >
                                        <q-card
                                            flat
                                            class="legend-item-card q-pa-sm cursor-pointer"
                                            :style="{
                                                background: hoveredType === eventType.type ? eventType.bgColor : 'white',
                                                border: `2px solid ${eventType.color}`,
                                                borderRadius: '12px'
                                            }"
                                        >
                                            <div class="row items-center no-wrap q-gutter-xs">
                                                <q-avatar
                                                    :style="{
                                                        background: `linear-gradient(135deg, ${eventType.color}, ${eventType.darkColor})`,
                                                        boxShadow: `0 2px 8px ${eventType.color}40`
                                                    }"
                                                    size="24px"
                                                    class="legend-avatar"
                                                >
                                                    <q-icon
                                                        :name="eventType.icon"
                                                        color="white"
                                                        size="14px"
                                                    />
                                                </q-avatar>
                                                <div class="column">
                                                    <span
                                                        class="text-weight-medium text-body2"
                                                        :style="{ color: hoveredType === eventType.type ? eventType.color : '#374151' }"
                                                    >
                                                        {{ eventType.label }}
                                                    </span>
                                                    <span class="text-caption text-grey-6">
                                                        {{ getEventCount(eventType.type) }} events
                                                    </span>
                                                </div>
                                            </div>
                                        </q-card>
                                    </div>
                                </div>
                            </q-card-section>
                        </q-card>
                    </div>
                </div>

                <!-- Loading State -->
                <!-- <div v-if="isLoading" class="row justify-center q-py-xl">
                    <q-spinner-dots color="primary" size="40px" />
                    <span class="q-ml-md text-grey-6">Loading calendar...</span>
                </div> -->

                <!--  v-else  Calendar Component -->
                <div class="calendar-container" :class="{ 'edit-mode': editMode, 'view-mode': !editMode }">
                    <FullCalendar
                        :events="enhancedCalendarEvents"
                        :initial-edit-mode="editMode"
                        @event-click="handleEventClick"
                        @date-click="handleDateClick"
                        @add-event="handleDateClick"
                        @event-edit="handleEventEdit"
                        @event-delete="handleEventDelete"
                        @edit-mode-change="handleEditModeChange"
                    />

                    <!-- Edit Mode Instructions -->
                    <div v-if="editMode" class="edit-instructions">
                        <q-banner inline-actions class="bg-blue-1 text-blue-8">
                            <template v-slot:avatar>
                                <q-icon name="info" color="blue" />
                            </template>
                            <strong>Edit Mode Active:</strong> Click on any day to add events, or click on existing events to edit them.
                            <template v-slot:action>
                                <q-btn flat color="blue" label="Got it" size="sm" />
                            </template>
                        </q-banner>
                    </div>

                    <!-- View Mode Instructions -->
                    <div v-else class="view-instructions">
                        <q-banner inline-actions class="bg-grey-2 text-grey-8">
                            <template v-slot:avatar>
                                <q-icon name="visibility" color="grey-6" />
                            </template>
                            <strong>View Mode:</strong> Calendar is in read-only mode. Enable Edit Mode to make changes.
                        </q-banner>
                    </div>
                </div>
            </q-card-section>
        </q-card>

        <!-- Event Form Dialog -->
        <q-dialog v-model="modalOpen" persistent>
            <q-card style="min-width: 500px">
                <q-card-section class="row items-center q-pb-none">
                    <div class="text-h6">{{ editing ? 'Edit Event' : 'Add New Event' }}</div>
                    <q-space />
                    <q-btn icon="close" flat round dense v-close-popup />
                </q-card-section>

                <q-card-section>
                    <q-form @submit="handleSubmit" class="q-gutter-md">
                        <q-input
                            v-model="eventForm.title"
                            label="Event Title *"
                            outlined
                            :rules="[val => !!val || 'Title is required']"
                        />

                        <q-input
                            v-model="eventForm.description"
                            label="Description"
                            outlined
                            type="textarea"
                            rows="3"
                        />

                        <q-select
                            v-model="eventForm.type"
                            :options="eventTypeOptions"
                            label="Event Type *"
                            outlined
                            emit-value
                            map-options
                            :rules="[val => !!val || 'Event type is required']"
                        />

                        <q-input
                            v-model="eventForm.date"
                            label="Date *"
                            outlined
                            type="date"
                            :rules="[val => !!val || 'Date is required']"
                        />

                        <q-checkbox
                            v-model="eventForm.is_full_day"
                            label="All Day Event"
                        />

                        <div v-if="!eventForm.is_full_day" class="row q-gutter-md">
                            <q-input
                                v-model="eventForm.start_time"
                                label="Start Time"
                                outlined
                                type="time"
                                class="col"
                            />

                            <q-input
                                v-model="eventForm.end_time"
                                label="End Time"
                                outlined
                                type="time"
                                class="col"
                            />
                        </div>

                        <q-input
                            v-model="eventForm.location"
                            label="Location"
                            outlined
                        />

                        <q-select
                            v-model="eventForm.status"
                            :options="statusOptions"
                            label="Status *"
                            outlined
                            emit-value
                            map-options
                            :rules="[val => !!val || 'Status is required']"
                        />

                        <div class="row justify-end q-gutter-sm q-mt-md">
                            <q-btn
                                label="Cancel"
                                color="grey"
                                flat
                                @click="closeModal"
                            />
                            <q-btn
                                label="Save"
                                color="primary"
                                type="submit"
                                :loading="isLoading"
                            />
                        </div>
                    </q-form>
                </q-card-section>
            </q-card>
        </q-dialog>

        <!-- Import Dialog -->
        <q-dialog v-model="showImportDialog">
            <q-card style="min-width: 400px">
                <q-card-section>
                    <div class="text-h6">Import Calendar Events</div>
                </q-card-section>

                <q-card-section>
                    <q-file
                        v-model="importFile"
                        label="Select Excel file"
                        outlined
                        accept=".xlsx,.xls"
                        @input="handleFileSelect"
                    >
                        <template v-slot:prepend>
                            <q-icon name="attach_file" />
                        </template>
                    </q-file>
                </q-card-section>

                <q-card-actions align="right">
                    <q-btn flat label="Cancel" color="grey" v-close-popup />
                    <q-btn
                        label="Import"
                        color="primary"
                        @click="handleImport"
                        :loading="isImporting"
                        :disable="!importFile"
                    />
                </q-card-actions>
            </q-card>
        </q-dialog>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useQuasar } from 'quasar';
import * as XLSX from 'xlsx';
import FullCalendar from '@/Components/Calendar/FullCalendar.vue';
import axios from 'axios';
import { validateCsrfToken, refreshCsrfToken } from '@/utils/csrf.js';

const $q = useQuasar();

const props = defineProps({
    records: Object,
    options: Object,
});

const pageTitle = 'Calendar Management';

const modalOpen = ref(false);
const editing = ref(null);
const isLoading = ref(false);
const isImporting = ref(false);
const selectedDate = ref(null);
const events = ref([]);
const showImportDialog = ref(false);
const importFile = ref(null);
const hoveredType = ref(null);
const editMode = ref(true);
// Removed unused variables

// Event form data
const eventForm = ref({
    title: '',
    description: '',
    type: '',
    date: '',
    is_full_day: false,
    start_time: '',
    end_time: '',
    location: '',
    status: 'active'
});

const items = computed(() => events.value || []);

// Quasar select options
const eventTypeOptions = [
    { value: 'holiday', label: 'Holiday' },
    { value: 'meeting', label: 'Meeting' },
    { value: 'exam', label: 'Exam' },
    { value: 'activity', label: 'Activity' },
    { value: 'other', label: 'Other' }
];

const statusOptions = [
    { value: 'active', label: 'Active' },
    { value: 'cancelled', label: 'Cancelled' },
    { value: 'completed', label: 'Completed' }
];

// Enhanced legend items with icons and colors
const legendItems = [
    {
        type: 'holiday',
        label: 'Holiday',
        icon: 'beach_access',
        color: '#EF4444',
        darkColor: '#DC2626',
        bgColor: '#FEF2F2'
    },
    {
        type: 'meeting',
        label: 'Meeting',
        icon: 'groups',
        color: '#3B82F6',
        darkColor: '#2563EB',
        bgColor: '#EFF6FF'
    },
    {
        type: 'exam',
        label: 'Exam',
        icon: 'quiz',
        color: '#8B5CF6',
        darkColor: '#7C3AED',
        bgColor: '#F5F3FF'
    },
    {
        type: 'activity',
        label: 'Activity',
        icon: 'sports_esports',
        color: '#10B981',
        darkColor: '#059669',
        bgColor: '#ECFDF5'
    },
    {
        type: 'other',
        label: 'Other',
        icon: 'event_note',
        color: '#6B7280',
        darkColor: '#4B5563',
        bgColor: '#F9FAFB'
    }
];

// Helper function to get event count by type
const getEventCount = (type) => {
    return events.value.filter(event => event.type === type).length;
};

// Helper function to get event icon by type
const getEventIcon = (type) => {
    const iconMap = {
        holiday: 'beach_access',
        meeting: 'groups',
        exam: 'quiz',
        activity: 'sports_esports',
        other: 'event_note'
    };
    return iconMap[type] || iconMap.other;
};

// Helper function to calculate days remaining until event
const calculateDaysRemaining = (eventDate) => {
    const today = new Date();
    const event = new Date(eventDate);

    // Reset time to start of day for accurate day calculation
    today.setHours(0, 0, 0, 0);
    event.setHours(0, 0, 0, 0);

    const diffTime = event - today;
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

    return Math.max(0, diffDays);
};

// Helper function to get color based on days remaining
const getDaysRemainingColor = (daysRemaining) => {
    if (daysRemaining === 0) return 'red';      // Today
    if (daysRemaining === 1) return 'orange';   // Tomorrow
    if (daysRemaining <= 7) return 'amber';     // This week
    return 'green';                             // Future
};

// Helper function to format event date for display
const formatEventDate = (dateString) => {
    const date = new Date(dateString);
    const options = {
        weekday: 'short',
        month: 'short',
        day: 'numeric'
    };
    return date.toLocaleDateString('en-US', options);
};

// Computed property for upcoming events (next 3 events)
const upcomingEvents = computed(() => {
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    return events.value
        .filter(event => {
            const eventDate = new Date(event.date);
            eventDate.setHours(0, 0, 0, 0);
            return eventDate >= today; // Only future events (including today)
        })
        .map(event => ({
            ...event,
            daysRemaining: calculateDaysRemaining(event.date)
        }))
        .sort((a, b) => a.daysRemaining - b.daysRemaining) // Sort by days remaining
        .slice(0, 3); // Take only the first 3
});

// Removed unused helper functions for cleaner code

// Fetch events
const fetchEvents = async () => {
    try {
        const response = await axios.get('/api/calendar-events');
        events.value = processEvents(response.data);
        console.log('Events fetched successfully:', events.value.length, 'events');
    } catch (error) {
        console.error('Error fetching events:', error);
        $q.notify({
            color: 'negative',
            message: 'Failed to load calendar events',
            icon: 'error'
        });
        // Keep existing events if fetch fails
    }
};

// Process events from the API to ensure they have the correct format
const processEvents = (events) => {
  return events.map(event => {
    // Extract date from calendar.date, event.date, or start_time
    let eventDate;
    if (event.calendar && event.calendar.date) {
      eventDate = new Date(event.calendar.date).toISOString().split('T')[0];
    } else if (event.date) {
      eventDate = event.date.includes('T') ? event.date.split('T')[0] : event.date;
    } else if (event.start_time && event.start_time.includes('T')) {
      eventDate = event.start_time.split('T')[0];
    } else {
      // Default to today if no date can be determined
      eventDate = new Date().toISOString().split('T')[0];
    }

    // Create a properly formatted event object
    return {
      id: event.id,
      title: event.title,
      description: event.description,
      date: eventDate,
      is_full_day: Boolean(event.is_full_day),
      start_time: event.start_time,
      end_time: event.end_time,
      location: event.location,
      type: event.type || 'other',
      color: getEventColor(event.type),
      affects_all_schedules: Boolean(event.affects_all_schedules),
      status: event.status,
      calendar: event.calendar
    };
  });
};

// Get color for event type
const getEventColor = (type) => {
  const colors = {
    holiday: '#ef4444', // red
    meeting: '#3b82f6', // blue
    exam: '#8b5cf6', // purple
    activity: '#10b981', // green
    other: '#6b7280' // gray
  };

  return colors[type] || colors.other;
};

const closeModal = () => {
    modalOpen.value = false;
    editing.value = null;
    selectedDate.value = null;
    // Reset form
    eventForm.value = {
        title: '',
        description: '',
        type: '',
        date: '',
        is_full_day: false,
        start_time: '',
        end_time: '',
        location: '',
        status: 'active'
    };
};

const openModal = (item = null) => {
    if (item && item.id) {
        // Editing existing event
        editing.value = item;
        // Pre-fill form with existing data
        eventForm.value = {
            title: item.title || '',
            description: item.description || '',
            type: item.type || '',
            date: item.date || '',
            is_full_day: item.is_full_day || false,
            start_time: item.start_time || '',
            end_time: item.end_time || '',
            location: item.location || '',
            status: item.status || 'active'
        };
    } else {
        // Creating new event
        editing.value = null;
        // Reset form to defaults
        eventForm.value = {
            title: '',
            description: '',
            type: '',
            date: selectedDate.value ? formatDateForInput(selectedDate.value) : '',
            is_full_day: false,
            start_time: '',
            end_time: '',
            location: '',
            status: 'active'
        };
    }
    modalOpen.value = true;
};

const handleSubmit = async () => {
    isLoading.value = true;

    try {
        if (editing.value && editing.value.id) {
            // Update existing event
            await axios.put(`/api/calendar-events/${editing.value.id}`, eventForm.value);

            $q.notify({
                color: 'positive',
                message: 'Event updated successfully',
                icon: 'check'
            });
        } else {
            // Create new event
            await axios.post('/api/calendar-events', eventForm.value);

            $q.notify({
                color: 'positive',
                message: 'Event created successfully',
                icon: 'check'
            });
        }

        // Always refresh the events list to ensure data consistency
        await fetchEvents();

        closeModal();
    } catch (error) {
        console.error('Error saving event:', error);
        $q.notify({
            color: 'negative',
            message: 'Failed to save event',
            icon: 'error'
        });
    } finally {
        isLoading.value = false;
    }
};

const exportData = () => {
    const dataToExport = events.value.map(event => ({
        title: event.title,
        description: event.description,
        type: event.type,
        date: event.date,
        is_full_day: event.is_full_day,
        start_time: event.start_time,
        end_time: event.end_time,
        location: event.location,
        status: event.status
    }));

    const ws = XLSX.utils.json_to_sheet(dataToExport);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Calendar Events');
    XLSX.writeFile(wb, 'calendar_events_export.xlsx');
};

// Import functionality
const handleFileSelect = (file) => {
    importFile.value = file;
};

const handleImport = async () => {
    if (!importFile.value) return;

    isImporting.value = true;

    try {
        const formData = new FormData();
        formData.append('file', importFile.value);

        const response = await axios.post('/api/calendar-events/import', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });

        $q.notify({
            color: 'positive',
            message: response.data.message || 'Events imported successfully',
            icon: 'check'
        });

        // Refresh events
        await fetchEvents();

        // Close dialog and reset
        showImportDialog.value = false;
        importFile.value = null;

    } catch (error) {
        console.error('Import failed:', error);
        $q.notify({
            color: 'negative',
            message: 'Failed to import events',
            icon: 'error'
        });
    } finally {
        isImporting.value = false;
    }
};

// Status configuration with colors and labels
const statusConfig = {
    'holiday': { label: 'Holiday', color: '#EF4444', bgColor: '#FEE2E2' }, // Red
    'meeting': { label: 'Meeting', color: '#3B82F6', bgColor: '#DBEAFE' }, // Blue
    'exam': { label: 'Exam', color: '#8B5CF6', bgColor: '#EDE9FE' }, // Purple
    'activity': { label: 'Activity', color: '#10B981', bgColor: '#D1FAE5' }, // Green
    'other': { label: 'Other', color: '#6B7280', bgColor: '#F3F4F6' } // Gray
};

// Enhanced calendar events with interactive features
const enhancedCalendarEvents = computed(() => {
    return events.value.map(event => {
        const config = statusConfig[event.type] || statusConfig['other'];

        // Create enhanced event with interactive features
        const enhancedEvent = {
            id: event.id,
            title: event.title,
            date: event.date,
            type: event.type,
            color: config.color,
            backgroundColor: config.bgColor,
            description: event.description || config.label,
            location: event.location || '',
            start_time: event.start_time,
            end_time: event.end_time,
            originalEvent: event
        };

        // Add interactive features if in edit mode
        if (editMode.value) {
            enhancedEvent.interactive = true;
            enhancedEvent.editable = true;
            enhancedEvent.deletable = true;

            // Add custom rendering for edit/delete buttons
            enhancedEvent.extendedProps = {
                ...enhancedEvent.extendedProps,
                showEditButton: true,
                showDeleteButton: true,
                onEdit: () => handleEventEdit(event),
                onDelete: () => handleEventDelete(event)
            };
        }

        return enhancedEvent;
    });
});

const handleEventClick = (event) => {
    if (!editMode.value) return; // Only allow clicks in edit mode

    const record = events.value.find(item => item.id === event.id);
    if (record) {
        openModal(record);
    }
};

const handleDateClick = (date) => {
  console.log('handleDateClick', date);

    if (!editMode.value) return; // Only allow clicks in edit mode

    selectedDate.value = date;
    // Open modal for new event (pass null to indicate new event)
    openModal(null);
};

// New event handlers for interactive features
const handleEventEdit = (event) => {
    if (!editMode.value) return;

    const record = events.value.find(item => item.id === event.id);
    if (record) {
        openModal(record);
    }
};

const handleEventDelete = async (event) => {
    if (!editMode.value) return;

    $q.dialog({
        title: 'Delete Event',
        message: `Are you sure you want to delete "${event.title}"?`,
        cancel: true,
        persistent: true,
        color: 'negative'
    }).onOk(async () => {
        try {
            await axios.delete(`/api/calendar-events/${event.id}`);

            $q.notify({
                color: 'positive',
                message: 'Event deleted successfully',
                icon: 'check'
            });

            // Refresh the events list to ensure data consistency
            await fetchEvents();
        } catch (error) {
            console.error('Error deleting event:', error);
            $q.notify({
                color: 'negative',
                message: 'Failed to delete event',
                icon: 'error'
            });
        }
    });
};

// Handle edit mode change from calendar component
const handleEditModeChange = (newEditMode) => {
    editMode.value = newEditMode;

    $q.notify({
        color: newEditMode ? 'positive' : 'info',
        message: `Edit mode ${newEditMode ? 'enabled' : 'disabled'}`,
        icon: newEditMode ? 'edit' : 'visibility',
        timeout: 1500
    });
};

// Removed unused handleDateAdd function

// Helper function to format date for input field
const formatDateForInput = (date) => {
    if (typeof date === 'string') return date;
    if (date instanceof Date) {
        // Use local date to avoid timezone offset issues
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }
    // Default to today's local date
    const today = new Date();
    const year = today.getFullYear();
    const month = String(today.getMonth() + 1).padStart(2, '0');
    const day = String(today.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
};

// Load events when component is mounted
onMounted(async () => {
    isLoading.value = true;

    // Validate CSRF token before making requests
    if (!validateCsrfToken()) {
        console.warn('CSRF token validation failed. Refreshing token...');
        try {
            await refreshCsrfToken();
        } catch (error) {
            console.error('Failed to refresh CSRF token:', error);
        }
    }

    await fetchEvents();
    isLoading.value = false;
});
</script>

<style scoped>
.calendar-container {
    min-height: 600px;
    border-radius: 8px;
    overflow: hidden;
}

/* Enhanced Legend Styling */
.legend-card {
    backdrop-filter: blur(10px);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    border-radius: 16px !important;
    overflow: hidden;
}

/* Upcoming Events Styling */
.upcoming-events-card {
    backdrop-filter: blur(10px);
    box-shadow: 0 4px 20px rgba(251, 146, 60, 0.15);
    border-radius: 16px !important;
    overflow: hidden;
    height: 100%;
}

.upcoming-event-item {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.upcoming-event-item:hover {
    transform: translateY(-2px) scale(1.02);
}

.upcoming-event-card {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    backdrop-filter: blur(8px);
}

.upcoming-event-card:hover {
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    transform: translateY(-1px);
}

.legend-item {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.legend-item:hover {
    transform: translateY(-2px) scale(1.02);
}

.legend-item-card {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    backdrop-filter: blur(8px);
    min-width: 120px;
}

.legend-item-card:hover {
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    transform: translateY(-1px);
}

.legend-avatar {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.legend-item:hover .legend-avatar {
    transform: scale(1.1);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2) !important;
}

.legend-item-active {
    transform: translateY(-2px) scale(1.02);
}

.legend-item-active .legend-item-card {
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

/* Responsive improvements for mobile */
@media (max-width: 600px) {
    .calendar-container {
        min-height: 500px;
    }

    .row.q-gutter-sm {
        flex-direction: column;
    }

    .row.q-gutter-sm > * {
        width: 100%;
        margin-bottom: 8px;
    }

    .legend-item-card {
        min-width: 100px;
    }

    .row.q-gutter-md {
        flex-wrap: wrap;
    }

    .legend-item {
        flex: 1;
        min-width: calc(50% - 8px);
    }
}

/* Custom button hover effects */
.q-btn:not(.q-btn--disable):hover {
    transform: translateY(-1px);
    transition: transform 0.2s ease-in-out;
}

/* Form styling */
.q-form .q-field {
    margin-bottom: 16px;
}

/* Dialog responsive */
@media (max-width: 600px) {
    .q-dialog .q-card {
        min-width: 90vw !important;
        max-width: 90vw !important;
    }
}

/* Enhanced animations */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.legend-item {
    animation: fadeInUp 0.6s ease-out;
}

.legend-item:nth-child(1) { animation-delay: 0.1s; }
.legend-item:nth-child(2) { animation-delay: 0.2s; }
.legend-item:nth-child(3) { animation-delay: 0.3s; }
.legend-item:nth-child(4) { animation-delay: 0.4s; }
.legend-item:nth-child(5) { animation-delay: 0.5s; }

/* Glassmorphism effect */
.legend-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.05) 100%);
    border-radius: 16px;
    pointer-events: none;
}

/* Interactive Calendar Features */
.calendar-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    pointer-events: none;
    z-index: 10;
}

.day-add-button {
    position: absolute;
    pointer-events: auto;
    z-index: 15;
}

.day-add-btn {
    opacity: 0;
    transform: scale(0.8);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.calendar-container:hover .day-add-btn {
    opacity: 0.7;
    transform: scale(1);
}

.day-add-btn:hover {
    opacity: 1 !important;
    transform: scale(1.1) !important;
    box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
}

.event-actions {
    position: absolute;
    pointer-events: auto;
    z-index: 20;
    opacity: 0;
    transform: translateY(-5px);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.event-actions.show {
    opacity: 1;
    transform: translateY(0);
}

.event-actions .q-btn {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    backdrop-filter: blur(8px);
}

.event-actions .q-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
}

/* Edit Mode Toggle Styling */
.q-toggle {
    transform: scale(0.9);
}

.q-card.q-pa-sm {
    border-radius: 12px;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(248, 250, 252, 0.9) 100%);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(226, 232, 240, 0.8);
    transition: all 0.3s ease;
}

.q-card.q-pa-sm:hover {
    background: linear-gradient(135deg, rgba(255, 255, 255, 1) 0%, rgba(248, 250, 252, 1) 100%);
    border-color: rgba(59, 130, 246, 0.3);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1);
}

/* Calendar Day Hover Effects */
.fc-day:hover .day-add-btn {
    opacity: 1 !important;
    transform: scale(1) !important;
}

/* Event Hover Effects */
.fc-event:hover + .event-actions,
.event-actions:hover {
    opacity: 1;
    transform: translateY(0);
}

/* Disabled State Styling */
.q-btn--disable {
    opacity: 0.4 !important;
}

/* Smooth transitions for edit mode */
.calendar-container {
    transition: all 0.3s ease;
}

.calendar-container.edit-mode {
    filter: brightness(1.05);
}

.calendar-container.view-mode {
    filter: brightness(0.95);
}

/* Tooltip styling */
.q-tooltip {
    font-size: 12px;
    padding: 6px 10px;
    border-radius: 6px;
    backdrop-filter: blur(8px);
}
</style>





