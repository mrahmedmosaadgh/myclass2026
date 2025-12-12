<template>
    <AppLayout
        :title="pageTitle"
        >
        <!-- :school_id="primarySchoolId":{{ primarySchoolId }} -->

        <button @click="filter_sessions_no_classroom()">
            {{ my_records_filter_sessions_no_classroom?.length }}
            / {{ localRecords.value?.length }}
        </button>
        <!-- Optional: Custom cell rendering -->
        <!-- <custom_table_1
        :columns="columns"
        :data="tableData"
    >
        <template #cell-actions="{ row }">
            <button class="text-blue-600 hover:text-blue-800">
                Edit {{ row.name }}
            </button>
        </template>
    </custom_table_1> -->
        <!-- <div class="p-6">
      <ScheduleTree :schedules="my_records_filtered_only_in_schedule" />
    </div>


<div v-for="item in my_records_filtered_only_in_schedule" :key="item" class="p-2">

    {{ item?.period_number }}
    {{ item?.day }}



</div> -->
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
                    :label-key="[
                        'cst.classroom_name',
                        'cst.subject_name',
                        'cst.teacher_name',
                    ]"
                    placeholder="Select Schedule"
                />
            </div>
        </div>
        <div class="mb-4 flex justify-end">
            <button
                @click="refreshData"
                class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
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
                Refresh Table
            </button>
        </div>






<div class="p-4 mb-6">







    <hr>
        by day



             :
        <div class="mb-2 absolute flex gap-2">
            <!-- {{ days }} -->
            <div class="p-0"
            v-for="day1 in days"
            :key="day1"

            >

            <button
                @click="selected_day = day1"
                class="px-2 py-1 rounded-full text-xs border-4 border-solid hover:scale-150"
                :class="
                    selected_day?.number === day1?.number
                        ? 'bg-yellow-400 border-black text-black'
                        : 'border-gray-200 text-white bg-gray-500'
                "
            >
                {{ day1?.name }}[{{ day1?.number }}]
            </button>
            </div>
        <!-- :style="`background-color: ${subject.color_bg}; color: ${subject.color_text}`" -->
            <button
                @click="selected_day = null"
                class="px-2 py-1 rounded-full text-xs border-4 border-solid hover:scale-150 bg-gray-400 text-white hover:bg-gray-300"
                :class="
                    !selected_day ? 'border-blue-700' : 'border-gray-200'
                "
            >
                all
            </button>
        </div>
</div>









        <!-- Schedule Table all-->

        <div class="bg-white rounded-lg shadow overflow-x-auto" v-if="!updating">
            <table class="min-w-full divide-y divide-gray-200" v-if="!selected_day">
                <thead class="bg-gray-50">
                    <tr>
                        <th
                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                        >
                            Classroom
                        </th>
                        <template v-for="day_ in days" :key="day_">
                            <th
                                :colspan="8"
                                class="px-4 py-2 text-center text-sm font-medium text-gray-700 border-l"

                            >
                                {{ day_.name }}:
                            </th>
                        </template>
                    </tr>
                    <tr>
                        <th></th>
                        <!-- Empty cell for classroom column -->
                        <template v-for="day_y in days" :key="`periods-${day_y}`">
                            <th

                                v-for="period in periods"
                                :key="`${day_y}-${period}`"
                                class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase border-l first:border-l-0"


                                >
                                {{ period }}
                            </th>
                        </template>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr
                        v-for="classroom in groupedSchedules"
                        :key="classroom.id"
                    >
                        <td
                            class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-900 border-r sticky left-0 bg-white"
                            :class="{ 'hover-row': hoveredRow === classroom.id }"
                            >
                            <!-- @mouseenter="setHoveredRow(classroom.id)"
                            @mouseleave="clearHoveredRow()" -->
                            <div class="p-0"

                            >

                                {{ classroom.name }}
                            </div>
                        </td>
                        <template
                        v-for="day_a in days"
                        :key="`${classroom.id}-${day_a}`"
                        >

                        <td

                                v-for="period in periods"
                                :key="`${classroom.id}-${day_a}-${period}`"
                                class="px-2 py-2 border-l first:border-l-4 first:border-l-black  first:border-l-solid   "

                                >
                                <div class="p-0">
                                    <!-- :style="`background-color: ${getSessionFromCache(classroom.id, day, period)?.color_bg};color:${getSessionFromCache(classroom.id, day, period)?.color_text}`" -->
                                    <!-- <button @click="log(getSessionFromCache(classroom.id, day, period))">wwww</button> -->

                                    <Dropdown2
                                        width="56"
                                        align="left"
                                        :auto-hide="true"
                                    >
                                        <template #trigger>
                                            <div class="p-0">
                                                <card2
                                                    v-if="
                                                        getSessionFromCache(
                                                            classroom.id,
                                                            day_a,
                                                            period
                                                        )?.schedule
                                                            ?.period_number ==
                                                            period &&
                                                        getSessionFromCache(
                                                            classroom.id,
                                                            day_a,
                                                            period
                                                        )?.schedule?.day ==
                                                        day_a?.number &&
                                                        getSessionFromCache(
                                                            classroom.id,
                                                            day_a,
                                                            period
                                                        )?.schedule?.cst
                                                            ?.classroom?.id ==
                                                            classroom.id
                                                    "
                                                    :option="
                                                        getSessionFromCache(
                                                            classroom.id,
                                                            day_a,
                                                            period
                                                        )
                                                    "
                                                    name="radioName"
                                                    my_class="custom-class"
                                                    @click="
                                                        filter_sessions_by_classroom(
                                                            classroom.id,
                                                            day_a,
                                                            period
                                                        )
                                                    "
                                                    @remove_session="
                                                        remove_SelectedSession(
                                                            $event,
                                                            classroom.id,
                                                            day_a,
                                                            period
                                                        )
                                                    "
                                                >
                                                    <template #main>
                                                        <!-- <period_table
                                                        @click="
                                                                    remove_SelectedSession(
                                                                        getSessionFromCache(
                                                                            classroom.id,
                                                                            day_a,
                                                                            period
                                                                        ),
                                                                        classroom.id,
                                                                        day_a,
                                                                        period
                                                                    )
                                                                "
                                                        /> -->
                                                        <div
                                                            class="p-0 overflow-visible"
                                                        >
                                                            <button
                                                                class="scale-75 overflow-visible px-3 absolute rounded-full pb-1 -right-4 -top-4 bg-red-500 z-40 mb-6 hover:scale-150 hover:bg-red-800 hover:text-white"
                                                                @click="
                                                                    remove_SelectedSession(
                                                                        getSessionFromCache(
                                                                            classroom.id,
                                                                            day_a,
                                                                            period
                                                                        ),
                                                                        classroom.id,
                                                                        day_a,
                                                                        period
                                                                    )
                                                                "
                                                            >
                                                                x
                                                            </button>
                                                        </div>
                                                    </template>
                                                </card2>
                                                <!-- @set_data="setSelectedSession($event,classroom.id, day, period)" -->
                                                <button
                                                    v-else
                                                    @click="
                                                        filter_sessions_by_classroom(
                                                            classroom.id,
                                                            day_a,
                                                            period
                                                        )
                                                    "
                                                    class="px-2 py-1 text-sm bg-gray-100 hover:bg-gray-200 rounded"
                                                >
                                                    +
                                                </button>
                                            </div>
                                        </template>
                                        <template #content>
                                            <div class="py-1">
                                                <RadioButtonGroup
                                                    v-model="
                                                        my_records_filtered_selected
                                                    "
                                                    name="officeType"
                                                    my_class="flex flex-wrap justify-center gap-1"
                                                    :options="
                                                        my_records_filtered
                                                    "
                                                    @set_data="
                                                        setSelectedSession(
                                                            $event,
                                                            classroom.id,
                                                            day_a,
                                                            period
                                                        )
                                                    "
                                                    :period_day="{
                                                        classroom: classroom.id,
                                                        day: day_a?.number,
                                                        period: period,
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
























        <!-- Schedule Table -->

        <div class="bg-white rounded-lg shadow overflow-x-auto" v-if="selected_day">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th
                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                        >
                            Classroom
                        </th>

                            <th
                                :colspan="8"
                                class="px-4 py-2 text-center text-sm font-medium text-gray-700 border-l"
                            >
                                {{ selected_day.name }}:
                            </th>

                    </tr>
                    <tr>
                        <th></th>
                        <!-- Empty cell for classroom column -->

                            <th

                                v-for="period in periods"
                                :key="` ${period}`"
                                class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase border-l first:border-l-0"
                            >
                                {{ period }}
                            </th>

                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr
                        v-for="classroom in groupedSchedules"
                        :key="classroom.id"
                    >
                        <td
                            class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-900 border-r sticky left-0 bg-white"
                            :class="{ 'hover-row': hoveredRow === classroom.id }"
                            >
                            <!-- @mouseenter="setHoveredRow(classroom.id)"
                            @mouseleave="clearHoveredRow()" -->
                            {{ classroom.name }}
                        </td>


                        <td

                                v-for="period in periods"
                                :key="`${classroom.id} -${period}`"
                                class="px-2 py-2 border-l first:border-l-4 first:border-l-black  first:border-l-solid   "
                            >
                                <div class="p-0">


                                    <Dropdown2
                                        width="56"
                                        align="left"
                                        :auto-hide="true"
                                    >
                                        <template #trigger>
                                            <div class="p-0">
                                                <card2
                                                    v-if="
                                                        getSessionFromCache(
                                                            classroom.id,
                                                            selected_day,
                                                            period
                                                        )?.schedule
                                                            ?.period_number ==
                                                            period &&
                                                        getSessionFromCache(
                                                            classroom.id,
                                                            selected_day,
                                                            period
                                                        )?.schedule?.day ==
                                                            selected_day?.number &&
                                                        getSessionFromCache(
                                                            classroom.id,
                                                            selected_day,
                                                            period
                                                        )?.schedule?.cst
                                                            ?.classroom?.id ==
                                                            classroom.id
                                                    "
                                                    :option="
                                                        getSessionFromCache(
                                                            classroom.id,
                                                            selected_day,
                                                            period
                                                        )
                                                    "
                                                    name="radioName"
                                                    my_class="custom-class"
                                                    @click="
                                                        filter_sessions_by_classroom(
                                                            classroom.id,
                                                            selected_day,
                                                            period
                                                        )
                                                    "
                                                    @remove_session="
                                                        remove_SelectedSession(
                                                            $event,
                                                            classroom.id,
                                                            selected_day,
                                                            period
                                                        )
                                                    "
                                                >
                                                    <template #main>
                                                        <div
                                                            class="p-0 overflow-visible"
                                                        >
                                                            <button
                                                                class="scale-75 overflow-visible px-2 absolute rounded-full pb-1 -right-4 -top-4 bg-red-500 z-40 mb-6 hover:scale-100 hover:bg-red-800 hover:text-white"
                                                                @click="
                                                                    remove_SelectedSession(
                                                                        getSessionFromCache(
                                                                            classroom.id,
                                                                            selected_day,
                                                                            period
                                                                        ),
                                                                        classroom.id,
                                                                        selected_day,
                                                                        period
                                                                    )
                                                                "
                                                            >
                                                                x
                                                            </button>
                                                        </div>
                                                    </template>
                                                </card2>
                                                  <button
                                                    v-else
                                                    @click="
                                                        filter_sessions_by_classroom(
                                                            classroom.id,
                                                            selected_day,
                                                            period
                                                        )
                                                    "
                                                    class="px-2 py-1 text-sm bg-gray-100 hover:bg-gray-200 rounded"
                                                >
                                                    +
                                                </button>
                                            </div>
                                        </template>
                                        <template #content>
                                            <div class="py-1">
                                                <RadioButtonGroup
                                                    v-model="
                                                        my_records_filtered_selected
                                                    "
                                                    name="officeType"
                                                    my_class="flex flex-wrap justify-center gap-1"
                                                    :options="
                                                        my_records_filtered
                                                    "
                                                    @set_data="
                                                        setSelectedSession(
                                                            $event,
                                                            classroom.id,
                                                            selected_day,
                                                            period
                                                        )
                                                    "
                                                    :period_day="{
                                                        classroom: classroom.id,
                                                        day: selected_day?.number,
                                                        period: period,
                                                    }"
                                                />
                                            </div>
                                        </template>
                                    </Dropdown2>
                                </div>
                            </td>

                    </tr>
                </tbody>
            </table>
        </div>



        <!-- Schedule Modal -->
        <FormModal10
            :show="modalOpen"
            @close="handleModalClose"
        >
            <div class="space-y-4">
                <h2 class="text-lg font-medium text-gray-900">
                    {{ editing ? 'Edit Schedule' : 'Add Schedule' }}
                </h2>

                <form @submit.prevent="handleSubmit" class="space-y-4">
                    <!-- CST Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Class-Subject-Teacher
                        </label>
                        <select
                            v-model="form.cst_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        >
                            <option value="">Select CST</option>
                            <option
                                v-for="item in my_records_filtered"
                                :key="item.id"
                                :value="item.id"
                            >
                                {{ item.classroom }}
                            </option>
                        </select>
                        <div v-if="errors?.cst_id" class="mt-1 text-sm text-red-600">
                            {{ errors.cst_id[0] }}
                        </div>
                    </div>

                    <!-- Day and Period Display -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Day</label>
                            <div class="mt-1 text-sm text-gray-900">
                                {{ selected_cell_data.day }}
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Period</label>
                            <div class="mt-1 text-sm text-gray-900">
                                {{ selected_cell_data.period_number }}
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Notes</label>
                        <textarea
                            v-model="form.notes"
                            rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        ></textarea>
                    </div>

                    <!-- Active Status -->
                    <div class="flex items-center">
                        <input
                            type="checkbox"
                            v-model="form.active"
                            class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                        >
                        <label class="ml-2 block text-sm text-gray-900">Active</label>
                    </div>

                    <!-- Action Buttons -->
                    <div class="mt-6 flex justify-end space-x-3">
                        <button
                            type="button"
                            class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition"
                            @click="handleModalClose"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition"
                            :disabled="submitting"
                        >
                            {{ submitting ? 'Saving...' : 'Save' }}
                        </button>
                    </div>
                </form>
            </div>
        </FormModal10>
    </AppLayout>
</template>

<script setup>
import { ref, computed, watch, nextTick } from "vue";
import { router } from "@inertiajs/vue3";
import { toast } from "vue3-toastify";
// import AppLayout from "@/Layouts/AppLayout.vue";
import FormModal10 from "./FormModal10.vue";
import CardComponent2 from "./CardComponent.vue";
import period_table from "./period_table.vue";

// resources/js/Pages/my_class/admin/Schedules/CardComponent2.vue
import ScheduleCell from "@/Components/Schedule/ScheduleCell.vue";
import RadioButtonGroup from "./RadioButtonGroup.vue";
import FilterSelectV2 from "@/Components/FilterSelectV2.vue";
import NameAbbreviator from "./NameAbbreviator2.vue";
import Dropdown2 from "./Dropdown2.vue";
import card2 from "./card2.vue";

// import DropdownLink from '@/Components/DropdownLink.vue';
import ScheduleTree from "@/Components/TreeView/ScheduleTree.vue";
import axios from "axios";


import custom_table_1 from './custom_table_1.vue';

const columns = [
    { key: 'name', label: 'Name', sticky: true },
    { key: 'grade', label: 'Grade' },
    { key: 'period1', label: 'Period 1' },
    { key: 'period2', label: 'Period 2' },
    { key: 'period3', label: 'Period 3' },
    { key: 'actions', label: 'Actions' },
];

const tableData = [
{
        name: 'Class A',
        grade: '10th',
        period1: 'Math',
        period2: 'Science',
        period3: 'English',
    },
    {
        name: 'Class A',
        grade: '10th',
        period1: 'Math',
        period2: 'Science',
        period3: 'English',
    },
    {
        name: 'Class A',
        grade: '10th',
        period1: 'Math',
        period2: 'Science',
        period3: 'English',
    },
    {
        name: 'Class A',
        grade: '10th',
        period1: 'Math',
        period2: 'Science',
        period3: 'English',
    },



    // ... more data
];







const selectedType = ref("Cozy");
const selected_day = ref(null);
const updating = ref(false);

const my_records_filtered_selected = ref(null);
const options = ref([
    { value: "Cozy2", label: "Cozy" },
    //   { value: 'Green', label: 'Green'  },
]);
const hover_vars = ref(
    {
        day:null,period:null

    }
 );
// Define props first
const props = defineProps({
    records: {
        type: Object,
        default: () => ({ data: [] }),
    },

    options: {
        type: Object,
        default: () => ({}),
    },
    periodDetails: {
        type: Array,
        default: () => [],
    },
    school_id: {
        type: Number,
        required: true
    },
    copy_id: {
        type: Number,
        required: true
    },
});

// const records = ref(props.records || []);
const localRecords = ref(props.records || []);
const localOptions = ref(props.options || {});
const my_records = ref(props?.records);
const my_records_filtered = ref(props?.records);
const my_records_filtered_only_in_schedule = ref(
    props?.records?.filter(
        (record) => record?.day != null && record?.period_number != null
    ) || []
);
console.log("Props received:", {
    records: localRecords.value,
    options: localOptions.value,
    periodDetails: props.periodDetails,
});

// Constants
const days = [
    { name: "Sunday", number: 1 },
    { name: "Monday", number: 2 },
    { name: "Tuesday", number: 3 },
    { name: "Wednesday", number: 4 },
    { name: "Thursday", number: 5 },
    // { name: 'Friday', number: 5 }
];
const periods = Array.from({ length: 8 }, (_, i) => i + 1);
const baseUrl = "/admin/schedules";
const selected_session_to_update = ref(false);

const pageTitle = "Schedule Management";
const periodTimes = {
    1: "07:30 - 08:15",
    2: "08:15 - 09:00",
    3: "09:00 - 09:45",
    4: "10:00 - 10:45",
    5: "10:45 - 11:30",
    6: "11:30 - 12:15",
    7: "12:45 - 13:30",
    8: "13:30 - 14:15",
};

// Reactive references

const modalOpen = ref(false);
const submitting = ref(false);
const form = ref({
    cst_id: null,
    notes: '',
    active: true
});
const errors = ref({});
const selected_cell_data = ref({
    day: null,
    period_number: null,
});
const editing = ref(null);
const filters = ref({
    school: null,
    school_object: {},
    classroom: null,
    classroom_object: {},
    schedule: null,
    schedule_object: {},
});

// Add these refs
const isLoading = ref(false);
const error = ref(null);

// At the top of your component, create reactive copies of props


// Watch for props changes
// watch(
//     () => localRecords,
//     (newRecords) => {
//         localRecords.value = newRecords;
//     },
//     { deep: true }
// );

watch(
    () => props.options,
    (newOptions) => {
        localOptions.value = newOptions;
    },
    { deep: true }
);

// Computed properties

const groupedSchedules = computed(() => {
    console.log("CSTs from options:", localOptions.value?.csts);
    const records = localRecords.value || [];
    const uniqueClassrooms = [
        ...new Set(records.map((record) => record.cst?.classroom?.id)),
    ].map((classroomId) => {
        const record = records.find(
            (r) => r.cst?.classroom?.id === classroomId
        );
        return {
            id: record.cst?.classroom?.id,
            name: record.cst?.classroom?.name,
            grade: record.cst?.classroom?.grade,
        };
    });

    console.log("Processed classrooms:", uniqueClassrooms);
    return uniqueClassrooms;
});

const scheduleMatrix = ref({});

// Add this computed property
const scheduleCache = computed(() => {
    if (!localRecords.value?.length) return {};

    return localRecords.value.reduce((cache, schedule) => {
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

const setSelectedSession = async (data_schedule, classroomId, day, period) => {
    if (submitting.value) return;
    if (!data_schedule) return;

    submitting.value = true;

    try {
        const formData = {
            id: data_schedule.id,
            cst_id: data_schedule.cst_id,
            day: day?.number,
            period_number: period,
            active: true,
            school_id: filters.value.school_object?.id || props.school_id
        };

        const response = await axios.post('/admin/schedule/update2', formData);

        if (response.data.success) {
            // Handle single record update
            if (response.data.record) {
                const updatedRecord = response.data.record;
                // Update the specific record in localRecords
                if (localRecords.value) {
                    localRecords.value = localRecords.value.map(record =>
                        record.id === updatedRecord.id ? updatedRecord : record
                    );
                }

                toast.success(response.data.message);
                await refreshData();
            } else {
                console.error('No record in response:', response.data);
                toast.error('No updated record received from server');
            }
        } else {
            throw new Error(response.data.message || 'Update failed');
        }
    } catch (err) {
        console.error('Full error:', err);
        const errorMessage = err.response?.data?.message
            || err.message
            || 'An error occurred while updating the schedule';
        toast.error(errorMessage);
    } finally {
        submitting.value = false;
    }
};
const remove_SelectedSession = (event, classroomId, day, period) => {
    if (!confirm("Are you sure you want to delete this ?")) return;
    handleSubmit_remove(event, classroomId, day, period);
};

const handleSubmit_remove = async (data_schedule, classroomId, day, period) => {
    if (submitting.value) return;
    if (!data_schedule?.id) {
        toast.error('Invalid schedule ID');
        return;
    }
    submitting.value = true;

    const formData = {
        id: data_schedule.id,
        school_id: data_schedule.school_id,
        copy_id: data_schedule.copy_id,
        remove_session: 1,
        day: null,
        id: data_schedule?.id || null, // Add this line before day: null
        period_number: null
    };

    try {
        // Log the data being sent
        console.log('Removing schedule with data:', formData);

        const response = await axios.post("/admin/schedule/update2", formData);

        if (response.data.success) {
            localRecords.value = localRecords.value.map((record) =>
                record.id === formData.id ? response.data.record : record
            );
            toast.success(response.data.message);
            await refreshData();
        } else {
            throw new Error(response.data.message);
        }
    } catch (err) {
        console.error('Error details:', {
            formData,
            error: err.response?.data || err.message
        });
        const errorMessage = err.response?.data?.message || "Failed to clear schedule slot";
        toast.error(errorMessage);
    } finally {
        submitting.value = false;
    }
};

const filter_sessions_by_classroom = (classroomId, day, period) => {
    console.log(day, period);

    selected_cell_data.value.day = day.number;
    selected_cell_data.value.period_number = period;

    my_records_filtered.value = localRecords.value?.filter(
        (schedule) => schedule.cst?.classroom?.id === classroomId
    );

    console.log("Filtered records:", my_records_filtered.value); // For debugging
    return my_records_filtered.value;
};
const my_records_filter_sessions_no_classroom = ref(props?.records);
const filter_sessions_no_classroom = () => {
    my_records_filter_sessions_no_classroom.value = localRecords.value?.filter(
        (schedule) => schedule.day !== null && schedule.period_number !== null
    );
    return my_records_filter_sessions_no_classroom.value;
};

const getScheduleForCell = (classroomId, day, period) => {
    // const dayNumber = days.indexOf(day) + 1;

    const schedule = localRecords.value.find(
        (schedule) =>
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
        cst: schedule.cst,
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
            active: true,
        };
    } else {
        // Handle existing schedule edit
        editing.value = {
            ...schedule,
            day: day.number,
            period_number: period,
            school_id: schedule.cst?.classroom?.school_id,
            copy_id: props.options.activeCopy?.id,
        };
    }

    selected_cell_data.value = {
        day: day.number,
        period_number: period,
    };

    modalOpen.value = true;
};

const handleAddSchedule = (classroomId, day, period) => {
    const cst = localOptions.value?.csts?.find(
        (cst) => cst.classroom.id === classroomId
    );
    if (!cst) return;

    editing.value = {
        id: null,
        cst_id: cst.id,
        day: days.indexOf(day) + 1,
        period_number: period,
        school_id: cst.classroom.school_id,
        copy_id: localOptions.value?.activeCopy?.id,
        active: true,
    };

    modalOpen.value = true;
};

const handleModalClose = () => {
    modalOpen.value = false;
    form.value = {
        cst_id: null,
        notes: '',
        active: true
    };
    errors.value = {};
};
const show_table = ref(true);
const refreshData = async () => {
    updating.value=true
    try {
        const response = await axios.get('/admin/schedule/load_data');
        if (response.data.success) {
    updating.value=false

            localRecords.value = response.data.records;
            // Update any other necessary data from the response
        }
    } catch (error) {
    updating.value=false

        console.error('Failed to refresh data:', error);
        toast.error('Failed to refresh schedule data');
    }
};



const handleSubmit = async () => {
    if (submitting.value) return;
    submitting.value = true;

    try {
        const formData = {
            ...form.value,
            school_id: filters.value.school_object?.id || props.school_id,
            day: selected_cell_data.value.day,
            period_number: selected_cell_data.value.period_number,
            copy_id: props.options?.copy_id
        };

        const response = await axios.post('/admin/schedule/update2', formData);

        // Update local records
        records.value = records.value.map(record =>
            record.id === formData.id ? response.data.record : record
        );

        toast.success(response.data.message);
        await refreshData();
        handleModalClose();
    } catch (err) {
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
    } finally {
        submitting.value = false;
    }
};

const formFields = computed(() => [
    {
        name: "cst_id",
        label: "Class-Subject-Teacher",
        type: "select",
        required: true,
        options:
            my_records_filtered.value?.map((item) => ({
                value: item.id,
                label: `${item.classroom}`,
                // label: `${item.classroom_name} - ${item.subject_name} - ${item.teacher_name}`
            })) || [],

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
    if (!schedule || !schedule.cst) return "";

    return {
        "bg-blue-50": schedule.cst.subject?.type === "regular",
        "bg-green-50": schedule.cst.subject?.type === "special",
        // Add more color conditions as needed
    };
};

const openModal = (existingSchedule = null, defaultData = {}) => {
    if (existingSchedule) {
        // Editing existing schedule
        editing.value = {
            cst_id: existingSchedule.cst_id,
            id: existingSchedule.id,
        };
    } else {
        // Creating new schedule
        editing.value = {
            cst_id: null,
            school_id: selected_school_object.value?.id,
            period_number: defaultData.period_number,
            day: defaultData.day,
        };
    }
    modalOpen.value = true;
};

const closeModal = () => {
    modalOpen.value = false;
    form.value = {};
};

const handleDelete = (scheduleId) => {
    if (!confirm('Are you sure you want to delete this schedule?')) return;

    toast.info(`${baseUrl}/${scheduleId}`);

    axios.delete(`${baseUrl}/${scheduleId}`)
        .then((response) => {
            if (response.data.success) {
                // Update local records immediately
                records.value = records.value.filter(
                    (record) => record.id !== scheduleId
                );
                toast.success(response.data.message);

                // Refresh data to ensure synchronization
                return refreshData();
            } else {
                toast.error(response.data.message);
            }
        })
        .catch((error) => {
            console.error("Error deleting schedule:", error);
            const errorMessage = error.response?.data?.message || "Failed to delete schedule";
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
        (schedule) =>
            schedule.classroom_id === classroomId &&
            schedule.period_number === period
    );

    const url = existingSchedule
        ? `${baseUrl}/${existingSchedule.id}`
        : baseUrl;

    const method = existingSchedule ? "put" : "post";

    axios[method](url, formData)
        .then((response) => {
            if (existingSchedule) {
                // Update existing record in the array
                records.value = records.value.map((record) =>
                    record.id === existingSchedule.id ? response.data : record
                );
                toast.success("Schedule updated successfully");
            } else {
                // Add new record to the array
                records.value = [...records.value, response.data];
                toast.success("Schedule placed successfully");
            }
        })
        .catch((error) => {
            let errorMessage = `Failed to ${
                existingSchedule ? "update" : "place"
            } schedule`;
            if (error.response?.data?.message) {
                errorMessage = error.response.data.message;
            }
            toast.error(errorMessage);

            if (error.response?.data?.conflict) {
                console.log("Conflict details:", error.response.data.conflict);
            }
        });
};

// Helper function to find available periods for a classroom
const getAvailablePeriods = (classroomId) => {
    const occupiedPeriods = items.value
        .filter((schedule) => schedule.classroom_id === classroomId)
        .map((schedule) => schedule.period_number);

    // Assuming 8 periods per day
    return Array.from({ length: 8 }, (_, i) => i + 1).filter(
        (period) => !occupiedPeriods.includes(period)
    );
};

// Add this to your template where you want to show available slots
const isSlotAvailable = (classroomId, period) => {
    return !localRecords.value.some(
        (schedule) =>
            schedule.classroom_id === classroomId &&
            schedule.period_number === period
    );
};

// Add these new refs for hover effects
const hoveredRow = ref(null);
const hoveredCol = ref(null);
const hoveredCell = ref({ row: null, col: null, day: null });

const setHoveredCell = (row, col, day) => {
    hoveredCell.value = { row, col, day };
    hoveredRow.value = row;
    hoveredCol.value = col;
};

const clearHoveredCell = () => {
    hoveredCell.value = { row: null, col: null, day: null };
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
    place: "",
    active: true,
    notes: "",
}));

const available_sessions = computed(() => {
    return (
        localRecords.value?.filter(
            (session) =>
                !getSessionFromCache(
                    session.cst?.classroom?.id,
                    session.day,
                    session.period_number
                ) && session.cst?.classroom?.id === selected_classroom.value?.id
        ) || []
    );
});

const selectSession = (classroomId, day, period, option) => {
    // Handle the selection here
    console.log("Selected:", { classroomId, day, period, option });
    // You might want to emit an event or update some state
};
</script>

<style scoped>
/* Base styles */
td, th {
    position: relative;
    transition: all 0.2s ease-in-out;
}

/* Hover effects */
.hover-row {
    background-color: rgba(243, 244, 246, 0.8) !important;
}

.hover-column {
    background-color: rgba(238, 242, 255, 0.8);
}

.hover-cell {
    background-color: rgba(224, 231, 255, 0.9) !important;
    box-shadow: inset 0 0 0 2px rgba(99, 102, 241, 0.2);
    z-index: 10;
}

/* Ensure sticky column maintains hover effect */
.sticky.hover-row {
    background-color: rgba(243, 244, 246, 0.95) !important;
}
</style>
