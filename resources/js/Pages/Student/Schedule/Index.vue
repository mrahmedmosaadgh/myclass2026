<template>
    <div class="bg-white p-6 rounded-lg shadow-lg">
        <!-- Schedule Header -->xxxxxxxxxxxxxxxxxxxxx
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800">My Class Schedule</h1>
            <p class="text-gray-600">
                {{ classroom.grade.name }} - {{ classroom.name }}
            </p>
        </div>

        <!-- Weekly Schedule Grid -->
        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr>
                        <th class="p-3 bg-gray-50 border"></th>
                        <th v-for="day in days" :key="day.value"
                            class="p-3 bg-gray-50 border text-gray-700 font-semibold">
                            {{ day.label }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="period in periods" :key="period">
                        <td class="p-2 border bg-gray-50 text-center font-medium text-sm">
                            {{ period }}
                            <div class="text-xs text-gray-500">
                                {{ getPeriodTime(period) }}
                            </div>
                        </td>
                        <td v-for="day in days" :key="`${period}-${day.value}`"
                            class="border p-2 h-24 w-40 relative">
                            <div v-if="getSchedule(day.value, period)"
                                 :class="getCardStyle(getSchedule(day.value, period))"
                                 class="h-full p-3 rounded-lg shadow-sm transition-all duration-200 hover:shadow-md">
                                <div class="flex flex-col h-full">
                                    <!-- Subject Name -->
                                    <div class="font-semibold text-gray-800">
                                        {{ getSchedule(day.value, period).subject?.name }}
                                    </div>

                                    <!-- Teacher Name -->
                                    <div class="text-sm text-gray-600 mt-1">
                                        <i class="fas fa-user-tie mr-1"></i>
                                        {{ getSchedule(day.value, period).teacher?.name }}
                                    </div>

                                    <!-- Location -->
                                    <div v-if="getSchedule(day.value, period).place"
                                         class="text-sm text-gray-500 mt-1">
                                        <i class="fas fa-location-dot mr-1"></i>
                                        {{ getSchedule(day.value, period).place }}
                                    </div>

                                    <!-- Additional Teachers -->
                                    <div v-if="hasAdditionalTeachers(day.value, period)"
                                         class="mt-auto text-xs text-gray-500">
                                        <div v-if="getSchedule(day.value, period).co_teacher">
                                            Co-Teacher: {{ getSchedule(day.value, period).co_teacher?.name }}
                                        </div>
                                        <div v-if="getSchedule(day.value, period).teacher_substitute">
                                            Substitute: {{ getSchedule(day.value, period).teacher_substitute?.name }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    schedules: {
        type: Array,
        required: true
    },
    classroom: {
        type: Object,
        required: true
    },
    periodDetails: {
        type: Array,
        required: true
    }
});

const days = [
    { value: 1, label: 'Sunday' },
    { value: 2, label: 'Monday' },
    { value: 3, label: 'Tuesday' },
    { value: 4, label: 'Wednesday' },
    { value: 5, label: 'Thursday' }
];

const periods = [1, 2, 3, 4, 5, 6, 7, 8];

const getSchedule = (day, period) => {
    return props.schedules.find(s =>
        s.day === day &&
        s.period_number === period &&
        s.active
    );
};

const getPeriodTime = (period) => {
    const periodDetail = props.periodDetails.find(p => p.period === period);
    return periodDetail ? `${periodDetail.start_time} - ${periodDetail.end_time}` : '';
};

const getCardStyle = (schedule) => {
    if (!schedule) return '';

    if (schedule.color_custom) {
        return `bg-${schedule.color_custom}-50 border-${schedule.color_custom}-200`;
    }

    // Default color scheme based on subjects
    const colors = {
        'Mathematics': 'bg-blue-50 border-blue-200',
        'Science': 'bg-green-50 border-green-200',
        'English': 'bg-purple-50 border-purple-200',
        'Arabic': 'bg-orange-50 border-orange-200',
        'Physical Education': 'bg-red-50 border-red-200',
        'default': 'bg-gray-50 border-gray-200'
    };

    return colors[schedule.subject?.name] || colors.default;
};

const hasAdditionalTeachers = (day, period) => {
    const schedule = getSchedule(day, period);
    return schedule && (schedule.co_teacher || schedule.teacher_substitute);
};
</script>

<style scoped>
.schedule-card {
    transition: all 0.2s ease-in-out;
}

.schedule-card:hover {
    transform: scale(1.02);
}
</style>

