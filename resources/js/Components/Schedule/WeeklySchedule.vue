<template>
    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Period
                    </th>
                    <th v-for="day in days"
                        :key="day"
                        class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                        {{ day }}
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="period in periods"
                    :key="period"
                    :class="{ 'bg-gray-50': period % 2 === 0 }">
                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                        <div class="flex items-center gap-2">
                            <span>{{ period }}</span>
                            <span v-if="periodTimes[period]" class="text-xs text-gray-500">
                                {{ periodTimes[period] }}
                            </span>
                        </div>
                    </td>
                    <td v-for="day in days"
                        :key="`${period}-${day}`"
                        class="px-2 py-2">
                        <ScheduleCell
                            :schedule="getScheduleItem(day, period)"
                            :show-time="showTimes"
                            @click="$emit('schedule-click', getScheduleItem(day, period))"
                            @add="$emit('add-schedule', { day, period })"
                        />
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import ScheduleCell from './ScheduleCell.vue';

const props = defineProps({
    schedules: {
        type: Array,
        required: true
    },
    showTimes: {
        type: Boolean,
        default: false
    },
    periodTimes: {
        type: Object,
        default: () => ({})
    }
});

const emit = defineEmits(['schedule-click', 'add-schedule']);

const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday'];
const periods = [1, 2, 3, 4, 5, 6, 7, 8];

const getScheduleItem = (day, period) => {
    if (!props.schedules) return null;

    return props.schedules.find(schedule =>
        schedule.day === days.indexOf(day) + 1 &&
        schedule.period_number === period &&
        !schedule.is_deleted
    );
};
</script>

<style scoped>
.schedule-cell {
    min-height: 80px;
}
</style>


