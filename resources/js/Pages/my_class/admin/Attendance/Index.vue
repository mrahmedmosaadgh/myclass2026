<template>
    <Head :title="pageTitle" />
<br>
<DraggableSquare_use />

<br>
<br>
<br>
<br>
<br>
<!-- <DraggableTree_use /> -->
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>v
<br>
<q1_use />
<br>
<br>

        <q-btn label="Click me" color="primary" />
        <div class="q-pa-md q-gutter-sm">
    <q-btn push color="white" text-color="primary" label="Unread Mails">
      <q-badge color="orange" floating>22</q-badge>
    </q-btn>

    <q-btn dense color="purple" round icon="email" class="q-ml-md">
      <q-badge color="red" floating>4</q-badge>
    </q-btn>
  </div>
        <!-- <DynamicTreeEditor /> -->
        <img_trans />
        <div class="p-4  ">

            <!-- <CoolTooltip_use /> -->
        </div>
        <!-- <test_chart_v2 /> -->
        <!-- Attendance Management Section -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8 transition-all duration-300 hover:shadow-lg">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold">Attendance Management</h2>
                <div class="flex space-x-2">
                    <PrimaryButton @click="refreshData" class="flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Refresh
                    </PrimaryButton>
                </div>
            </div>
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-4 rounded">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">
                            Select a school and grade to manage attendance for students.
                        </p>
                    </div>
                </div>
            </div>
            <SchoolGradeSelector @selectionChanged="onSelectionChanged" />
            <BulkAttendanceActions @bulkAction="onBulkAction" />
            <StudentAttendanceTable
                :students="students"
                :attendanceData="attendanceData"
                @statusChanged="onStatusChanged"
            />
            <AttendanceHistory :history="attendanceHistory" />
        </div>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <div class="flex justify-between items-center mb-6">
                        <div class="flex space-x-2">
                            <PrimaryButton @click="openModal()">
                                Add New Calendar Entry
                            </PrimaryButton>
                            <ImportExcel
                                @imported="refreshData"
                                :validate-url="baseUrl + '/validate-import'"
                                :import-url="baseUrl + '/import'"
                                :columns="importColumns"
                                button-text="Import Calendar"
                                preview-title="Preview Calendar Data"
                            />
                          <SecondaryButton @click="exportData">
                                Export
                            </SecondaryButton>
                            <SecondaryButton @click="exportAsJson" class="ml-2">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                Save as JSON
                            </SecondaryButton>
                            <SecondaryButton @click="importFromJson" class="ml-2">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                </svg>
                                Import JSON
                            </SecondaryButton>
                        </div>
                    </div>

                    <FullCalendar
                        :events="calendarEvents"
                        @event-click="handleEventClick"
                        @date-click="handleDateClick"
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
<!--
Attendance management section added above the calendar. Components are imported and rendered with demo state and handlers.
-->

<script setup>
import { ref, computed } from 'vue';
import { router, Head } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import FormModal from '@/Components/Common/FormModal.vue';
import ImportExcel from '@/Components/Common/ImportExcel.vue';
import * as XLSX from 'xlsx';
import FullCalendar from '@/Components/Calendar/FullCalendar.vue';
import SchoolGradeSelector from './components/SchoolGradeSelector.vue';
import BulkAttendanceActions from './components/BulkAttendanceActions.vue';
import StudentAttendanceTable from './components/StudentAttendanceTable.vue';
import AttendanceHistory from './components/AttendanceHistory.vue';
import DynamicTreeEditor from '@/Pages/my_class/admin/test1/charts/DynamicTreeEditor.vue'
import img_trans from './img_trans.vue'
import CoolTooltip_use from './CoolTooltip_use.vue';
import DraggableTree_use from './quasar/DraggableTree_use.vue';
import q1_use from './quasar/1_use.vue';
// import CustomCursor from './quasar/CustomCursor.vue';
import DraggableSquare_use from './square/box/box_use.vue';
// resources/js/Pages/my_class/admin/Attendance/square/box/box_use.vue
// resources\js\Pages\my_class\admin\Attendance\square\DraggableSquare_use.vue
// resources\js\Pages\my_class\admin\Attendance\quasar\DraggableTree_use.vue
// resources\js\Pages\my_class\admin\Attendance\quasar\1_use.vue
// resources/js/Pages/my_class/admin/Attendance/quasar/CustomCursor.vue
// UI state

const isLoading = ref(false);
const searchQuery = ref('');
const selectedDate = ref(formatDate(new Date()));

// Get today's date in YYYY-MM-DD format for max date input
const maxDate = formatDate(new Date());

// Demo state for attendance management
const students = ref([
    { id: 1, name: 'Ahmed Ali' },
    { id: 2, name: 'Sara Hassan' },
    { id: 3, name: 'Mohamed Youssef' },
]);
const attendanceData = ref({
    1: { status: 'present', time: 0 },
    2: { status: 'late', time: 15 },
    3: { status: 'absent', time: 0 }
});
const attendanceHistory = ref([
    { id: 1, date: '2025-04-24', status: 'present' },
    { id: 2, date: '2025-04-23', status: 'late' }
]);

// Attendance export modal
const attendanceExportModalOpen = ref(false);
const exportAttendanceFields = [
    {
        name: 'format',
        label: 'Export Format',
        type: 'select',
        required: true,
        options: [
            { value: 'xlsx', label: 'Excel (.xlsx)' },
            { value: 'csv', label: 'CSV (.csv)' },
            { value: 'pdf', label: 'PDF (.pdf)' }
        ]
    },
    {
        name: 'dateRange',
        label: 'Date Range',
        type: 'select',
        required: true,
        options: [
            { value: 'today', label: 'Today' },
            { value: 'week', label: 'This Week' },
            { value: 'month', label: 'This Month' },
            { value: 'custom', label: 'Custom Range' }
        ]
    },
    {
        name: 'startDate',
        label: 'Start Date',
        type: 'date',
        required: false,
        showWhen: (form) => form.dateRange === 'custom'
    },
    {
        name: 'endDate',
        label: 'End Date',
        type: 'date',
        required: false,
        showWhen: (form) => form.dateRange === 'custom'
    }
];

// Filter students based on search query
const filteredStudents = computed(() => {
    if (!searchQuery.value) return students.value;

    const query = searchQuery.value.toLowerCase();
    return students.value.filter(student =>
        student.name.toLowerCase().includes(query)
    );
});

// Format date to YYYY-MM-DD
function formatDate(date) {
    const d = new Date(date);
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

// Handle school/grade selection change
function onSelectionChanged(selection) {
    isLoading.value = true;

    // Simulate API call
    setTimeout(() => {
        console.log('Selection changed:', selection);
        // In a real app, you would fetch data from the server here
        isLoading.value = false;
    }, 1000);
}

// Apply bulk action to all students
function onBulkAction(action) {
    isLoading.value = true;

    // Simulate API call
    setTimeout(() => {
        // Apply action to all students
        students.value.forEach(student => {
            attendanceData.value[student.id] = attendanceData.value[student.id] || { status: 'present', time: 0 };
            attendanceData.value[student.id].status = action;
            if (action === 'late') attendanceData.value[student.id].time = 10;
            else if (action === 'leftEarly') attendanceData.value[student.id].time = 5;
            else attendanceData.value[student.id].time = 0;
        });

        isLoading.value = false;
    }, 500);
}

// Handle individual student status change
function onStatusChanged({ studentId, status }) {
    // Update the attendance data
    attendanceData.value[studentId].status = status;
    if (status === 'late') attendanceData.value[studentId].time = 10;
    else if (status === 'leftEarly') attendanceData.value[studentId].time = 5;
    else attendanceData.value[studentId].time = 0;

    // In a real app, you would send this update to the server
    console.log(`Updated student ${studentId} status to ${status}`);
}

// Export attendance data
function exportAttendanceData() {
    attendanceExportModalOpen.value = true;
}

// Handle attendance export form submission
function handleAttendanceExport({ form }) {
    isLoading.value = true;

    // Prepare data for export
    const exportData = students.value.map(student => ({
        id: student.id,
        name: student.name,
        status: attendanceData.value[student.id]?.status || 'unknown',
        time: attendanceData.value[student.id]?.time || 0,
        date: selectedDate.value
    }));

    // Export based on selected format
    if (form.format === 'xlsx' || form.format === 'csv') {
        const ws = XLSX.utils.json_to_sheet(exportData);
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, 'Attendance');

        if (form.format === 'xlsx') {
            XLSX.writeFile(wb, `attendance_${selectedDate.value}.xlsx`);
        } else {
            XLSX.writeFile(wb, `attendance_${selectedDate.value}.csv`);
        }
    } else if (form.format === 'pdf') {
        // In a real app, you would generate a PDF here
        console.log('PDF export not implemented in this demo');
        alert('PDF export would be generated here');
    }

    isLoading.value = false;
    attendanceExportModalOpen.value = false;
}

const props = defineProps({
    records: Object,
    options: Object,
});

const pageTitle = 'Calendar Management';
const modelName = 'Calendar Entry';
const baseUrl = '/admin/calendar';

const modalOpen = ref(false);
const editing = ref(null);

const items = computed(() => props.records?.data || []);

// Calendar data and configuration
const calendarData = ref({
    loading: false,
    error: null
});

// Add refresh data function
const refreshData = () => {
    calendarData.value.loading = true;
    router.reload({
        only: ['records'],
        preserveScroll: true,
        preserveState: true,
        onFinish: () => {
            calendarData.value.loading = false;
        }
    });
};

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
};

const openModal = (item = null) => {
    editing.value = item;
    modalOpen.value = true;
};

const handleSubmit = ({ form }) => {
    if (editing.value) {
        router.put(`${baseUrl}/${editing.value.id}`, form);
    } else {
        router.post(baseUrl, form);
    }
    closeModal();
};

// Delete record function - will be implemented when needed

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

// refreshData function is defined above

const calendarEvents = computed(() => {
    return items.value.map(item => ({
        id: item.id,
        title: item.event || getStatusLabel(item.status),
        date: item.date,
        status: item.status,
        // Add any other relevant fields
    }));
});

const getStatusLabel = (status) => {
    const labels = {
        1: 'Work Day',
        0: 'Day Off',
        2: 'Activity',
        3: 'Test',
        4: 'Final Exam'
    };
    return labels[status] || 'Unknown';
};

const handleEventClick = (event) => {
    const record = items.value.find(item => item.id === event.id);
    if (record) {
        openModal(record);
    }
}

const handleDateClick = () => {
    // Open modal with empty form when clicking on a date
    // In a real implementation, we would pre-fill the date
    openModal(null);
};

const exportAsJson = () => {
    try {
        const exportData = {
            students: students.value,
            attendanceData: attendanceData.value,
            attendanceHistory: attendanceHistory.value,
            exportDate: new Date().toISOString()
        };

        const blob = new Blob([JSON.stringify(exportData, null, 2)], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        const timestamp = new Date().toISOString().slice(0,19).replace(/[:]/g, '-');
        const filename = `attendance_data_${timestamp}.json`;

        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);

        toast.success('Attendance data exported successfully');
    } catch (error) {
        console.error('Error exporting attendance data:', error);
        toast.error('Error exporting attendance data: ' + error.message);
    }
};

const importFromJson = () => {
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = '.json';

    input.onchange = async (e) => {
        try {
            const file = e.target.files[0];
            if (!file) return;

            const text = await file.text();
            const data = JSON.parse(text);

            // Validate the imported data structure
            if (!data.students || !data.attendanceData || !data.attendanceHistory) {
                throw new Error('Invalid attendance data format');
            }

            // Update the reactive refs with imported data
            students.value = data.students;
            attendanceData.value = data.attendanceData;
            attendanceHistory.value = data.attendanceHistory;

            toast.success('Attendance data imported successfully');
        } catch (error) {
            console.error('Error importing attendance data:', error);
            toast.error('Error importing attendance data: ' + error.message);
        }
    };

    input.click();
};
</script>


