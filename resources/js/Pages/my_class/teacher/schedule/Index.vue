<template>
    <Head title="Teacher Schedule" />
    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <!-- Schedule Display -->
                <div class="schedule-container">
                    <timeline_main
                        :schedule="schedule"
                        :timings="timings"
                        :loading="loading"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import timeline_main from './timelinev2.3/timeline_main.vue';

const loading = ref(false);
const schedule = ref([]);
const timings = ref(null);

const loadTeacherSchedule = async () => {
    loading.value = true;
    try {
        const response = await axios.post(route('teacher.timeline.schedule'), {
            day_code: `d${new Date().getDay() + 1}`
        });

        if (response.data.success) {
            schedule.value = response.data.data.schedule;
            timings.value = response.data.data.timings;
        }
    } catch (error) {
        console.error('Failed to load schedule:', error);
        schedule.value = [];
        timings.value = null;
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    loadTeacherSchedule();
});
</script>

<style scoped>
.schedule-container {
    @apply mt-4;
}
</style>

















