<template>
    <AppLayout :title="pageTitle">


        <details>

<pre>
    props.options?.classrooms:{{ props.options?.csts[0]?.classroom }}


</pre>
</details>
<!-- <CardComponent2></CardComponent2> -->

<details>

    <div class="p-0 flex flex-wrap justify-center">

        <RadioButtonGroup
        v-model="my_records_filtered_selected"
        name="officeType"
        :my_class="'flex flex-wrap justify-center gap-1'"
        :options="filteredRecords"
        :period_day="{
              day:day?.number, period:period
        }"
        />
        <!-- disabled="disabled" -->
    </div>
</details>
        <details>

            <pre>
                props.records:{{ props.records2 }}

            </pre>
        </details>
        <!-- Filters Section -->
        <div class="mb-6 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <FilterSelectV2
                    v-model="filters.school"
                    v-model:object="filters.school_object"
                    :options="$page.props.auth.user.school"
                    value-key="id"
                    :label-key="['name', 'hr.name']"
                    placeholder="Select School"
                    label-separator=" - "
                    :default-selected-index="0"
                    :label_only="false"
                />

                <FilterSelectV2
                    v-model="filters.classroom"
                    v-model:object="filters.classroom_object"
                    :options="$page.props.auth.user.classroom"
                    value-key="id"
                    :label-key="['name']"
                    placeholder="Select Classroom"
                />

                <FilterSelectV2
                    v-model="filters.schedule"
                    v-model:object="filters.schedule_object"
                    :options="$page.props.auth.user.schedule"
                    value-key="id"
                    :label-key="['cst.classroom_name', 'cst.subject_name', 'cst.teacher_name']"
                    placeholder="Select Schedule"
                />
            </div>
        </div>

        <!-- Schedule Table -->
        <div class="bg-white rounded-lg shadow overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                            Classroom
                        </th>
                        <template v-for="day in days" :key="day">
                            <th :colspan="8" class="px-4 py-2 text-center text-sm font-medium text-gray-700 border-l">
                                {{ day.name }}
                            </th>
                        </template>


                    </tr>
                    <tr>
                        <th></th> <!-- Empty cell for classroom column -->
                        <template v-for="day in days" :key="`periods-${day}`">
                            <th v-for="period in periods"
                                :key="`${day}-${period}`"
                                class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase border-l first:border-l-0">
                                {{ period }}
                            </th>
                        </template>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="classroom in groupedSchedules" :key="classroom.id">
                        <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-900 border-r">
                            {{ classroom.name }}
                        </td>
                        <template v-for="day in days" :key="`${classroom.id}-${day}`">
                            <td v-for="period in periods"
                                :key="`${classroom.id}-${day}-${period}`"
                                class="px-2 py-2 border-l first:border-l-0">
                                <div class="p-0"

                                >
                                <!-- :style="`background-color: ${getSessionFromCache(classroom.id, day, period)?.color_bg};color:${getSessionFromCache(classroom.id, day, period)?.color_text}`" -->
<button @click="log(getSessionFromCache(classroom.id, day, period))">wwww</button>

                                <Dropdown2 width="56" align="left"  :auto-hide="true">
    <template #trigger>
  <div class="p-0">
      <card2
      v-if="getSessionFromCache(classroom.id, day, period)?.schedule?.period_number==period&&getSessionFromCache(classroom.id, day, period)?.schedule?.day==day.number"
  :option="getSessionFromCache(classroom.id, day, period)"
  name="radioName"
  my_class="custom-class"
  @click="filter_sessions_by_classroom(classroom.id, day, period)"

  @set_data="setSelectedSession($event,classroom.id, day, period)"
/>
<button
v-else
      @click="filter_sessions_by_classroom(classroom.id, day, period)"
      class="px-2 py-1 text-sm bg-gray-100 hover:bg-gray-200 rounded">
      filter
    </button>
</div>
    </template>
    <template #content>
        <div class="py-1">

            <RadioButtonGroup class="scale-75 "
        v-model="my_records_filtered_selected"
        name="officeType"
        my_class="flex flex-wrap justify-center gap-1"
        :options="my_records_filtered"
        :period_day="{
            classroom:classroom.id, day:day.number, period:period
        }"
        />


        </div>
    </template>
</Dropdown2>






                                </div>
                            </td>
                        </template>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Schedule Modal -->
        <DialogModal_8
            v-if="modalOpen"
            :show="modalOpen"
            :title="editing ? 'Edit Schedule' : 'Add New Schedule'"
            :fields="formFields"
            :formData="editing"
            @close="handleModalClose"
            @submitted="handleSubmit"
        />
    </AppLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
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

import axios from 'axios';





const selectedType  =ref('Cozy')
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
const my_records_filtered = ref(props.records || []);
const my_records_filtered_selected = ref({});

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

// Watch for props changes
watch(() => props.records, (newRecords) => {
    records.value = newRecords;
}, { deep: true });

// Computed properties
const groupedSchedules = computed(() => {
    console.log('CSTs from options:', props.options?.csts);
    const records = props.records || [];
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



const groupedSchedules2 = computed(() => {
    console.log('CSTs from options:', props.options?.csts);
    const csts = props.options?.csts || [];
    const uniqueClassrooms = [...new Set(csts.map(cst => cst.classroom.id))]
        .map(classroomId => {
            const cst = csts.find(cst => cst.classroom.id === classroomId);
            return {
                id: cst.classroom.id,
                name: cst.classroom.name,
                grade: cst.classroom.grade,

            };
        });

    console.log('Processed classrooms:', uniqueClassrooms);
    return uniqueClassrooms;
});

// Add these computed properties
const scheduleMatrix = computed(() => {
    const matrix = {};

    // Initialize empty matrix
    groupedSchedules.value.forEach(classroom => {
        matrix[classroom.id] = {};
        for (let day = 1; day <= 5; day++) {
            matrix[classroom.id][day] = {};
            for (let period = 1; period <= 8; period++) {
                matrix[classroom.id][day][period] = null;
            }
        }
    });

    // Fill in the schedules
    props.records.forEach(schedule => {
        const classroomId = schedule.cst.classroom.id;
        if (matrix[classroomId]) {
            matrix[classroomId][schedule.day][schedule.period_number] = {
                id: schedule.id,
                subject: schedule.cst.subject.name,
                teacher: schedule.cst.teacher.name,
                is_disabled: !schedule.active,
                color_bg: schedule.cst.subject.color_bg,
                color_text: schedule.cst.subject.color_text


            };
        }
    });

    return matrix;
});

// Add this computed property
const scheduleCache = computed(() => {
    if (!props.records?.length) return {};

    return props.records.reduce((cache, schedule) => {
        if (!schedule?.day || !schedule?.period_number) return cache;

        const key = `${schedule.day}-${schedule.period_number}`;
        cache[key] = {
            subject: schedule.cst?.subject?.name || '',
            teacher: schedule.cst?.teacher?.name || '',
            color_bg: schedule.cst?.subject?.color_bg || '#ffffff',
            color_text: schedule.cst?.subject?.color_text || '#000000',
            cst: schedule.cst || null,
            schedule,
            id: schedule?.id
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

const refreshData = async () => {
    isLoading.value = true;
    error.value = null;

    try {
        const response = await axios.get(baseUrl);
        records.value = response.data.records || [];
    } catch (err) {
        error.value = err.response?.data?.message || 'Failed to load schedule data';
        toast.error(error.value);
    } finally {
        isLoading.value = false;
    }
};
// handleSubmit2(selected_session_to_update.value,classroomId, day, period)
const handleSubmit2 = async (data_schedule,classroomId, day, period) => {


    const formData = {
        ...data_schedule,
        id: data_schedule.id,
        day:  day.number,
        period_number: period,
        school_id: data_schedule.school_id,
        copy_id: data_schedule.copy_id,
        active: data_schedule.active ?? true
    };

    try {
        const url = '/admin/schedule/update2';
        const method =   'post';
        // const method = formData.id ? 'put' : 'post';
        console.log('formData22222222___________',formData);
        console.log('3333___________',data_schedule,classroomId, day, period);
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
        const response = await axios.post(url, formData);

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

// Add this computed property to filter out null values
const filteredRecords = computed(() => {
  return my_records_filtered.value?.filter(record => record != null) || [];
});

// Update the my_records_filtered ref initialization

</script>

<style scoped>
.border-l {
    border-left: 1px solid #e5e7eb;
}
.border-r {
    border-right: 1px solid #e5e7eb;
}
.first\:border-l-0:first-child {
    border-left: 0;
}
</style>



















































