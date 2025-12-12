<template>
    <AppLayout :title="pageTitle">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <!-- Header with Actions -->
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">{{ pageTitle }}</h1>
                            <p class="text-sm text-gray-600 mt-1">Manage academic calendar entries and events</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <PrimaryButton @click="openModal()" :disabled="isLoading">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                                Add New Entry
                            </PrimaryButton>
                            <ImportExcel
                                @imported="refreshData"
                                :validate-url="baseUrl + '/validate-import'"
                                :import-url="baseUrl + '/import'"
                                :columns="importColumns"
                                button-text="Import Calendar"
                                preview-title="Preview Calendar Data"
                            />
                            <SecondaryButton @click="exportData" :disabled="isLoading || !items.length">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Export
                            </SecondaryButton>
                        </div>
                    </div>

                    <!-- Calendar Legend -->
                    <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                        <h3 class="text-sm font-medium text-gray-700 mb-2">Calendar Legend:</h3>
                        <div class="flex flex-wrap gap-4 text-xs">
                            <div class="flex items-center">
                                <div class="w-3 h-3 rounded-full bg-green-500 mr-2"></div>
                                <span>Work Day</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-3 h-3 rounded-full bg-red-500 mr-2"></div>
                                <span>Day Off</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-3 h-3 rounded-full bg-blue-500 mr-2"></div>
                                <span>Activity</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-3 h-3 rounded-full bg-yellow-500 mr-2"></div>
                                <span>Test</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-3 h-3 rounded-full bg-purple-500 mr-2"></div>
                                <span>Final Exam</span>
                            </div>
                        </div>
                    </div>

                    <!-- Loading State -->
                    <div v-if="isLoading" class="flex justify-center items-center py-12">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                        <span class="ml-3 text-gray-600">Loading calendar...</span>
                    </div>

                    <!-- Calendar Component -->
                    <div v-else class="calendar-container">
                        <FullCalendar
                            :events="calendarEvents"
                            @event-click="handleEventClick"
                            @date-click="handleDateClick"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Modal -->
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
import SecondaryButton from '@/Components/SecondaryButton.vue';
import FormModal from '@/Components/Common/FormModal.vue';
import ImportExcel from '@/Components/Common/ImportExcel.vue';
import * as XLSX from 'xlsx';
import FullCalendar from '@/Components/Calendar/FullCalendar.vue';

const props = defineProps({
    records: Object,
    options: Object,
});

const pageTitle = 'Calendar Management';
const modelName = 'Calendar Entry';
const baseUrl = '/admin/calendar';

const modalOpen = ref(false);
const editing = ref(null);
const isLoading = ref(false);
const selectedDate = ref(null);

const items = computed(() => props.records?.data || []);

const formFields = [
    {
        name: 'date',
        label: 'Date',
        type: 'date',
        required: true
    },
    {
        name: 'week',
        label: 'Week',
        type: 'number',
        required: true
    },
    {
        name: 'day',
        label: 'Day',
        type: 'text',
        required: true
    },
    {
        name: 'day_number',
        label: 'Day Number',
        type: 'select',
        required: true,
        options: [
            { value: 1, label: 'Sunday' },
            { value: 2, label: 'Monday' },
            { value: 3, label: 'Tuesday' },
            { value: 4, label: 'Wednesday' },
            { value: 5, label: 'Thursday' },
            { value: 6, label: 'Friday' },
            { value: 7, label: 'Saturday' }
        ]
    },
    {
        name: 'week_number',
        label: 'Week Number',
        type: 'number',
        required: true
    },
    {
        name: 'semester_number',
        label: 'Semester Number',
        type: 'number',
        required: true
    },
    {
        name: 'academic_year_id',
        label: 'Academic Year',
        type: 'select',
        required: true,
        options: computed(() => props.options?.academicYears?.map(year => ({
            value: year.id,
            label: year.name
        })) || [])
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
        name: 'status',
        label: 'Status',
        type: 'select',
        required: true,
        options: [
            { value: 1, label: 'Active' },
            { value: 0, label: 'Day Off' },
            { value: 2, label: 'Activity' },
            { value: 3, label: 'Test' },
            { value: 4, label: 'Final Exam' }
        ]
    },
    {
        name: 'event',
        label: 'Event',
        type: 'text'
    },
    {
        name: 'event_academic',
        label: 'Academic Event',
        type: 'text'
    },
    {
        name: 'vacation',
        label: 'Vacation',
        type: 'number'
    }
];

const importColumns = [
    { key: 'date', label: 'Date', required: true },
    { key: 'academic_year', label: 'Academic Year (ID or Name)', required: true },
    { key: 'school', label: 'School (ID or Name)', required: true },
    { key: 'week', label: 'Week', required: false },
    { key: 'day', label: 'Day', required: false },
    { key: 'day_number', label: 'Day Number (1-7)', required: false },
    { key: 'week_number', label: 'Week Number', required: false },
    { key: 'semester_number', label: 'Semester Number', required: false },
    { key: 'status', label: 'Status (1=Active, 0=Day Off, 2=Activity, 3=Test, 4=Final Exam)', required: false },
    { key: 'event', label: 'Event', required: false },
    { key: 'event_academic', label: 'Academic Event', required: false },
    { key: 'vacation', label: 'Vacation', required: false }
];

const closeModal = () => {
    modalOpen.value = false;
    editing.value = null;
    selectedDate.value = null;
};

const openModal = (item = null) => {
    editing.value = item;
    modalOpen.value = true;
};

const handleSubmit = ({ form }) => {
    isLoading.value = true;

    if (editing.value) {
        router.put(`${baseUrl}/${editing.value.id}`, form, {
            onSuccess: () => {
                closeModal();
                // Show success message if needed
            },
            onError: (errors) => {
                console.error('Update failed:', errors);
                // Handle errors - could show toast notification
            },
            onFinish: () => {
                isLoading.value = false;
            }
        });
    } else {
        router.post(baseUrl, form, {
            onSuccess: () => {
                closeModal();
                // Show success message if needed
            },
            onError: (errors) => {
                console.error('Creation failed:', errors);
                // Handle errors - could show toast notification
            },
            onFinish: () => {
                isLoading.value = false;
            }
        });
    }
};

const exportData = () => {
    const dataToExport = items.value.map(item => ({
        date: item.date,
        week: item.week,
        day: item.day,
        day_number: item.day_number,
        week_number: item.week_number,
        semester_number: item.semester_number,
        school: item.school?.name,
        status: item.status,
        event: item.event,
        event_academic: item.event_academic,
        vacation: item.vacation
    }));

    const ws = XLSX.utils.json_to_sheet(dataToExport);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Calendar');
    XLSX.writeFile(wb, 'calendar_export.xlsx');
};

const refreshData = () => {
    router.reload();
};

// Status configuration with colors and labels
const statusConfig = {
    1: { label: 'Work Day', color: '#10B981', bgColor: '#D1FAE5' }, // Green
    0: { label: 'Day Off', color: '#EF4444', bgColor: '#FEE2E2' }, // Red
    2: { label: 'Activity', color: '#3B82F6', bgColor: '#DBEAFE' }, // Blue
    3: { label: 'Test', color: '#F59E0B', bgColor: '#FEF3C7' }, // Yellow
    4: { label: 'Final Exam', color: '#8B5CF6', bgColor: '#EDE9FE' } // Purple
};

const calendarEvents = computed(() => {
    return items.value.map(item => {
        const config = statusConfig[item.status] || statusConfig[1];
        const title = item.event || config.label;

        return {
            id: item.id,
            title: title,
            date: item.date,
            status: item.status,
            color: config.color,
            backgroundColor: config.bgColor,
            description: `${config.label}${item.event ? ` - ${item.event}` : ''}`,
            school: item.school?.name || '',
            week: item.week,
            semester: item.semester_number,
            originalEvent: item
        };
    });
});

const handleEventClick = (event) => {
    const record = items.value.find(item => item.id === event.id);
    if (record) {
        openModal(record);
    }
};

const handleDateClick = (date) => {
    selectedDate.value = date;
    // Pre-fill the form with the selected date
    const newEntry = { date: formatDateForInput(date) };
    openModal(newEntry);
};

// Helper function to format date for input field
const formatDateForInput = (date) => {
    if (typeof date === 'string') return date;
    if (date instanceof Date) {
        return date.toISOString().split('T')[0];
    }
    return new Date().toISOString().split('T')[0];
};
</script>

<style scoped>
.calendar-container {
    min-height: 600px;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
}

/* Responsive improvements */
@media (max-width: 640px) {
    .calendar-container {
        min-height: 500px;
    }

    .flex-wrap {
        flex-direction: column;
    }

    .gap-2 > * {
        width: 100%;
    }
}

/* Loading animation */
@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.animate-spin {
    animation: spin 1s linear infinite;
}

/* Button hover effects */
button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

button:not(:disabled):hover {
    transform: translateY(-1px);
    transition: transform 0.2s ease-in-out;
}

/* Legend styling */
.legend-item {
    transition: all 0.2s ease-in-out;
}

.legend-item:hover {
    transform: scale(1.05);
}
</style>
