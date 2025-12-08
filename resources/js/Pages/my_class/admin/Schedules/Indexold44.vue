<template>
    <AppLayout :title="pageTitle">
        <!-- Header Section -->
        <div class="bg-white shadow-sm border-b border-gray-200 px-4 py-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
                <h2 class="text-2xl font-semibold text-gray-800">Schedule Management</h2>
                <div class="flex items-center space-x-4">
                    <button
                        @click="refreshData"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        <svg
                            class="w-4 h-4 mr-2"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                            />
                        </svg>
                        Refresh
                    </button>
                </div>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="bg-white rounded-lg shadow p-6 space-y-4">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Filters</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <FilterSelectV2
                        v-model="filters.schedule"
                        v-model:object="filters.schedule_object"
                        :options="$page.props.auth.user.schedule"
                        value-key="id"
                        :label-key="['cst.classroom_name', 'cst.subject_name', 'cst.teacher_name']"
                        placeholder="Select Schedule"
                        class="w-full"
                    />
                </div>
            </div>
        </div>

        <!-- Schedule Table -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Classroom
                                </th>
                                <template v-for="day in days" :key="`periods-${day}`">
                                    <th
                                        v-for="period in periods"
                                        :key="`${day}-${period}`"
                                        class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-l first:border-l-0"
                                    >
                                        <div class="flex flex-col">
                                            <span class="text-gray-400">Period</span>
                                            <span>{{ period }}</span>
                                            <span class="text-xs text-gray-400">{{ periodTimes[period] }}</span>
                                        </div>
                                    </th>
                                </template>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="classroom in groupedSchedules" :key="classroom.id">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 bg-gray-50">
                                    {{ classroom.name }}
                                </td>
                                <template v-for="day in days" :key="`${classroom.id}-${day}`">
                                    <td
                                        v-for="period in periods"
                                        :key="`${classroom.id}-${day}-${period}`"
                                        class="px-2 py-2 border-l first:border-l-0 hover:bg-gray-50 transition-colors duration-150"
                                    >  <card2
      v-if="getSessionFromCache(classroom.id, day, period)?.schedule?.period_number==period&&getSessionFromCache(classroom.id, day, period)?.schedule?.day==day.number&&getSessionFromCache(classroom.id, day, period) ?.schedule?.cst?.classroom ?.id==classroom.id"
  :option="getSessionFromCache(classroom.id, day, period)"
  name="radioName"
  my_class="custom-class"
  @click="filter_sessions_by_classroom(classroom.id, day, period)"

  @set_data="setSelectedSession($event,classroom.id, day, period)"
/>
                                        <!-- <ScheduleCell
                                            :schedule="getScheduleForCell(classroom.id, day, period)"
                                            @click="handleScheduleClick($event, day, period)"
                                            @add="handleAddSchedule(classroom.id, day, period)"
                                        /> -->
                                    </td>
                                </template>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <DialogModal_8
            v-if="modalOpen"
            :show="modalOpen"
            :title="editing ? 'Edit Schedule' : 'Add New Schedule'"
            :fields="formFields"
            :formData="editing"
            @close="handleModalClose"
            @submitted="handleSubmit"
            class="z-50"
        />
    </AppLayout>
</template>

<script setup>
import { ref, computed, watch, nextTick } from 'vue';
import { router } from '@inertiajs/vue3';
import { toast } from 'vue3-toastify';
import AppLayout from '@/Layouts/AppLayout.vue';
import DialogModal_8 from './DialogModal_8.vue';
import CardComponent2 from './CardComponent.vue';
// resources/js/Pages/my_class/admin/Schedules/CardComponent2.vue
import ScheduleCell from '@/Components/Schedule/ScheduleCell.vue';
import RadioButtonGroup from './RadioButtonGroup.vue';
import FilterSelectV2 from '@/Components/FilterSelectV2.vue';
import NameAbbreviator from './NameAbbreviator2.vue';
import Dropdown2 from './Dropdown2.vue';
import card2 from './card2.vue';
// import DropdownLink from '@/Components/DropdownLink.vue';
import ScheduleTree from '@/Components/TreeView/ScheduleTree.vue';
import axios from 'axios';





const selectedType  =ref('Cozy')
const my_records_filtered_selected  =ref(null)
const options = ref([
  { value: 'Cozy2', label: 'Cozy'  },
//   { value: 'Green', label: 'Green'  },
]);





// Define props first
const props = defineProps({
    records: {
        type: Object,
        default: () => ({ data: [] })
    },
    records2: {
        type: Object,
        default: () => ({ data: [] })
    },
    options: {
        type: Object,
        default: () => ({})
    },
    periodDetails: {
        type: Array,
        default: () => []
    }
});
const my_records=ref(props?.records)
const my_records_filtered=ref(props?.records)
const my_records_filtered_only_in_schedule = ref(
    props?.records?.filter(record =>
        record?.day != null &&
        record?.period_number != null
    ) || []
);
console.log('Props received:', {
    records: props.records,
    options: props.options,
    periodDetails: props.periodDetails
});

// Constants
const days = [
    { name: 'Sunday', number: 1 },
    { name: 'Monday', number: 2 },
    { name: 'Tuesday', number: 3 },
    { name: 'Wednesday', number: 4 },
    { name: 'Thursday', number: 5 },
    // { name: 'Friday', number: 5 }
];
const periods = Array.from({ length: 8 }, (_, i) => i + 1);
const baseUrl = '/admin/schedules';
const  selected_session_to_update = ref(false);

const pageTitle = 'Schedule Management';
const periodTimes = {
    1: '07:30 - 08:15',
    2: '08:15 - 09:00',
    3: '09:00 - 09:45',
    4: '10:00 - 10:45',
    5: '10:45 - 11:30',
    6: '11:30 - 12:15',
    7: '12:45 - 13:30',
    8: '13:30 - 14:15'
};

// Reactive references
const records = ref(props.records || []);
const modalOpen = ref(false);
const selected_cell_data = ref({
    day: null,
    period_number: null
});
const editing = ref(null);
const submitting = ref(false);
const filters = ref({
    school: null,
    school_object: {},
    classroom: null,
    classroom_object: {},
    schedule: null,
    schedule_object: {}
});

// Add these refs
const isLoading = ref(false);
const error = ref(null);

// At the top of your component, create reactive copies of props
const localRecords = ref(props.records || []);
const localOptions = ref(props.options || {});

// Watch for props changes
watch(() => props.records, (newRecords) => {
    localRecords.value = newRecords;
}, { deep: true });

watch(() => props.options, (newOptions) => {
    localOptions.value = newOptions;
}, { deep: true });

// Computed properties

const groupedSchedules = computed(() => {
    console.log('CSTs from options:', localOptions.value?.csts);
    const records = localRecords.value || [];
    const uniqueClassrooms = [...new Set(records.map(record => record.cst?.classroom?.id))]
        .map(classroomId => {
            const record = records.find(r => r.cst?.classroom?.id === classroomId);
            return {
                id: record.cst?.classroom?.id,
                name: record.cst?.classroom?.name,
                grade: record.cst?.classroom?.grade,
            };
        });

    console.log('Processed classrooms:', uniqueClassrooms);
    return uniqueClassrooms;
});

const scheduleMatrix = ref({});

// Add this computed property
const scheduleCache = computed(() => {
    if (!props.records?.length) return {};

    return props.records.reduce((cache, schedule) => {
        const key = `${schedule.day}-${schedule.period_number}`;
        cache[key] = {
            subject: schedule.cst?.subject?.name,
            teacher: schedule.cst?.teacher?.name,
            color_bg: schedule.cst?.subject?.color_bg,
            color_text: schedule.cst?.subject?.color_text,
            cst: schedule.cst,
            schedule: schedule,
            id: schedule?.id,
        };
        return cache;
    }, {});
});

// Methods

const getSessionFromCache = (classroomId, day, period) => {
    // const dayNumber = days.indexOf(day) + 1;
    return scheduleCache.value[`${day.number}-${period}`] || null;
};
const log = (val) => {
console.log(val);

};


const setSelectedSession=(event,classroomId, day, period) => {
    console.log('setSelectedSession________',event);

    selected_session_to_update.value = event;

    handleSubmit2(selected_session_to_update.value,classroomId, day, period);
};






const filter_sessions_by_classroom = (classroomId, day, period) => {
console.log(day, period);

    selected_cell_data.value.day=day.number
    selected_cell_data.value.period_number=period

    my_records_filtered.value = props.records?.filter(schedule =>
        schedule.cst?.classroom?.id === classroomId
    );

    console.log('Filtered records:', my_records_filtered.value); // For debugging
    return my_records_filtered.value;
};





const getScheduleForCell = (classroomId, day, period) => {
    // const dayNumber = days.indexOf(day) + 1;

    const schedule = records.value.find(schedule =>
        schedule.cst?.classroom?.id === classroomId &&
        schedule.period_number === period &&
        schedule.day === day.number &&
        schedule.active === true
    );

    if (!schedule) return null;

    return {
        id: schedule.id,
        cst_id: schedule.cst_id,
        day: schedule.day.number,
        period_number: schedule.period_number,
        subject: schedule.cst?.subject?.name,
        teacher: schedule.cst?.teacher?.name,
        is_disabled: !schedule.active,
        cst: schedule.cst
    };
};

const handleScheduleClick = (schedule, day, period) => {
    // const dayNumber = days.indexOf(day) + 1;

    if (!schedule) {
        // Handle new schedule creation
        editing.value = {
            id: schedule.id,
            day: day.number,
            period_number: period,
            school_id: props.options.csts?.[0]?.classroom?.school_id,
            copy_id: props.options.activeCopy?.id,
            active: true
        };
    } else {
        // Handle existing schedule edit
        editing.value = {
            ...schedule,
            day: day.number,
            period_number: period,
            school_id: schedule.cst?.classroom?.school_id,
            copy_id: props.options.activeCopy?.id
        };
    }

    selected_cell_data.value = {
        day: day.number,
        period_number: period
    };

    modalOpen.value = true;
};

const handleAddSchedule = (classroomId, day, period) => {
    const cst = props.options.csts.find(cst => cst.classroom.id === classroomId);
    if (!cst) return;

    editing.value = {
         id: null,
        cst_id: cst.id,
        day: days.indexOf(day) + 1,
        period_number: period,
        school_id: cst.classroom.school_id,
        copy_id: props.options.activeCopy?.id,
        active: true
    };

    modalOpen.value = true;
};

const handleModalClose = () => {
    modalOpen.value = false;
    editing.value = null;
    submitting.value = false;
};
const show_table =ref(true)
const refreshData = () => {
    show_table.value = false;
    router.reload({
        only: ['records', 'options'],
        preserveScroll: true,
        onFinish: () => {
            show_table.value = true;
            toast.success('Schedule data refreshed successfully');
        },
        onError: () => {
            show_table.value = true;
            toast.error('Failed to refresh data');
        }
    });
};
const refreshData2 = () => {
    show_table.value=false
    axios.get('/admin/schedule/load-data')
        .then(response => {
            my_records.value = response.data.records;
            my_records_filtered.value = response.data.records;
            my_records_filtered_only_in_schedule.value = response.data.records.filter(record =>
                record?.day != null &&
                record?.period_number != null
            );
            toast.success('Schedule data refreshed successfully');
            show_table.value=true
        })
        .catch(error => {
            show_table.value=true
            console.error('Error refreshing data:', error);
            toast.error('Failed to refresh data');
        });
};
const handleSubmit2 = (data_schedule, classroomId, day, period) => {
    if (submitting.value) return;
    submitting.value = true;

    const formData = {
        ...data_schedule,
        id: data_schedule.id,
        day: day.number,
        period_number: period,
        school_id: data_schedule.school_id,
        copy_id: data_schedule.copy_id,
        active: data_schedule.active ?? true
    };

    axios.post('/admin/schedule/update2', formData)
        .then(response => {
            // Update local records
            records.value = records.value.map(record =>
                record.id === formData.id ? response.data.record : record
            );
            toast.success(response.data.message);
            return refreshData();
        })
        .catch(err => {
            if (err.response?.data?.conflict) {
                toast.error(err.response.data.message, {
                    duration: 5000,
                    position: 'bottom-right',
                    closeButton: true
                });
                console.error('Conflict details:', err.response.data.conflict);
            } else {
                const errorMessage = err.response?.data?.message || 'An unexpected error occurred';
                toast.error(errorMessage);
            }
        })
        .finally(() => {
            submitting.value = false;
        });
};

const handleSubmit = async ({ form, onSuccess, onError }) => {
    if (submitting.value) return;

    submitting.value = true;
    error.value = null;

    const formData = {
        ...form,
        day: selected_cell_data.value.day,
        period_number: selected_cell_data.value.period_number,
        school_id: editing.value.school_id,
        copy_id: editing.value.copy_id,
        active: editing.value.active ?? true
    };

    try {
        const url = '/admin/schedule/update2';
        const method =   'post';
        // const method = formData.id ? 'put' : 'post';
        console.log('formData___________',formData);
return
        const response = await axios[method](url, formData);

        records.value = formData.id
            ? records.value.map(record => record.id === editing.value.id ? response.data : record)
            : [...records.value, response.data];

        handleModalClose();
        onSuccess();
        toast.success(`Schedule ${formData.id ? 'updated' : 'created'} successfully`);
    } catch (err) {
        const errorMessage = err.response?.data?.message || 'An unexpected error occurred';
        onError(err.response?.data?.errors || { error: [errorMessage] });
        toast.error(errorMessage);
    } finally {
        submitting.value = false;
    }
};

const formFields = computed(() => [
    {
        name: 'cst_id',
        label: 'Class-Subject-Teacher',
        type: 'select',
        required: true,
        options: my_records_filtered.value?.map(item => ({
            value: item.id,
            label: `${item.classroom}`
            // label: `${item.classroom_name} - ${item.subject_name} - ${item.teacher_name}`
        })) || []


        // options: props.options?.csts?.map(item => ({
        //     value: item.id,
        //     label: `${item.classroom_name} - ${item.subject_name} - ${item.teacher_name}`
        // })) || []
    },
    // {
    //     name: 'day',
    //     label: 'Day',
    //     type: 'select',
    //     required: true,
    //     options: days.map((day, index) => ({
    //         value: index + 1,
    //         label: day
    //     })),
    //     disabled: true // Make it read-only
    // },
    // {
    //     name: 'period_number',
    //     label: 'Period',
    //     type: 'select',
    //     required: true,
    //     options: periods.map(period => ({
    //         value: period,
    //         label: `Period ${period} (${periodTimes[period]})`
    //     })),
    //     disabled: true // Make it read-only
    // }
]);

const getScheduleCardColor = (schedule) => {
    if (!schedule || !schedule.cst) return '';

    return {
        'bg-blue-50': schedule.cst.subject?.type === 'regular',
        'bg-green-50': schedule.cst.subject?.type === 'special',
        // Add more color conditions as needed
    };
};

const openModal = (existingSchedule = null, defaultData = {}) => {
    if (existingSchedule) {
        // Editing existing schedule
        editing.value = {
            cst_id: existingSchedule.cst_id,
            id: existingSchedule.id
        };
    } else {
        // Creating new schedule
        editing.value = {
            cst_id: null,
            school_id: selected_school_object.value?.id,
            period_number: defaultData.period_number,
            day: defaultData.day
        };
    }
    modalOpen.value = true;
};

const closeModal = () => {
    modalOpen.value = false;
    editing.value = null;
    submitting.value = false;
};

const handleDelete = (scheduleId) => {
    axios.delete(`${baseUrl}/${scheduleId}`)
        .then(() => {
            // Update local records immediately
            records.value = records.value.filter(record => record.id !== scheduleId);
            toast.success('Schedule deleted successfully');

            // Refresh data to ensure synchronization
            refreshData();
        })
        .catch(error => {
            console.error('Error deleting schedule:', error);
            const errorMessage = error.response?.data?.message || 'Failed to delete schedule';
            toast.error(errorMessage);
        });
};

const handleSchedulePlacement = (classroomId, period) => {
    const formData = {
        ...form.value,
        classroom_id: classroomId,
        period_number: period,
        copy_id: activeCopy.value.id,
    };

    // Find existing schedule for this slot
    const existingSchedule = records.value.find(
        schedule => schedule.classroom_id === classroomId &&
                   schedule.period_number === period
    );

    const url = existingSchedule
        ? `${baseUrl}/${existingSchedule.id}`
        : baseUrl;

    const method = existingSchedule ? 'put' : 'post';

    axios[method](url, formData)
        .then(response => {
            if (existingSchedule) {
                // Update existing record in the array
                records.value = records.value.map(record =>
                    record.id === existingSchedule.id ? response.data : record
                );
                toast.success('Schedule updated successfully');
            } else {
                // Add new record to the array
                records.value = [...records.value, response.data];
                toast.success('Schedule placed successfully');
            }
        })
        .catch(error => {
            let errorMessage = `Failed to ${existingSchedule ? 'update' : 'place'} schedule`;
            if (error.response?.data?.message) {
                errorMessage = error.response.data.message;
            }
            toast.error(errorMessage);

            if (error.response?.data?.conflict) {
                console.log('Conflict details:', error.response.data.conflict);
            }
        });
};

// Helper function to find available periods for a classroom
const getAvailablePeriods = (classroomId) => {
    const occupiedPeriods = items.value
        .filter(schedule => schedule.classroom_id === classroomId)
        .map(schedule => schedule.period_number);

    // Assuming 8 periods per day
    return Array.from({length: 8}, (_, i) => i + 1)
        .filter(period => !occupiedPeriods.includes(period));
};

// Add this to your template where you want to show available slots
const isSlotAvailable = (classroomId, period) => {
    return !props.records.some(schedule =>
        schedule.classroom_id === classroomId &&
        schedule.period_number === period
    );
};

// Add these new refs for hover effects
const hoveredRow = ref(null);
const hoveredCol = ref(null);
const hoveredCell = ref({ row: null, col: null });

const setHoveredCell = (row, col) => {
    hoveredCell.value = { row, col };
    hoveredRow.value = row;
    hoveredCol.value = col;
};

const clearHoveredCell = () => {
    hoveredCell.value = { row: null, col: null };
    hoveredRow.value = null;
    hoveredCol.value = null;
};

// Add these methods for better hover control
const setHoveredRow = (id) => {
    hoveredRow.value = id;
};

const clearHoveredRow = () => {
    hoveredRow.value = null;
};

// Add these refs at the top of your script setup
const selectedCstIndex = ref(null);
const defaultScheduleData = computed(() => ({
    school_id: selected_school_object.value?.id,
    period_number: null,
    day: null,

    cst_id: null,
    place: '',
    active: true,
    notes: ''
}));

const available_sessions = computed(() => {
    return props.records2?.filter(session =>
        !getSessionFromCache(session.cst?.classroom?.id, session.day, session.period_number) &&
        session.cst?.classroom?.id === selected_classroom.value?.id
    ) || [];
});

const selectSession = (classroomId, day, period, option) => {
    // Handle the selection here
    console.log('Selected:', { classroomId, day, period, option });
    // You might want to emit an event or update some state
};
</script>

<style scoped>
.table-wrapper {
    @apply overflow-x-auto shadow rounded-lg border border-gray-200;
}

/* Hover effects */
.hover-column {
    @apply bg-gray-50;
}

.hover-row:hover {
    @apply bg-gray-50;
}

/* Custom scrollbar */
.table-wrapper::-webkit-scrollbar {
    @apply h-2 w-2;
}

.table-wrapper::-webkit-scrollbar-track {
    @apply bg-gray-100 rounded-full;
}

.table-wrapper::-webkit-scrollbar-thumb {
    @apply bg-gray-300 rounded-full hover:bg-gray-400;
}

/* Transitions */
.fade-enter-active,
.fade-leave-active {
    @apply transition-opacity duration-200;
}

.fade-enter-from,
.fade-leave-to {
    @apply opacity-0;
}
</style>

































































