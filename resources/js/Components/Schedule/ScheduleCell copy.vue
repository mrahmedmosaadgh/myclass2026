<template>
    <div class="h-full">
        <!-- Schedule Content -->
        <div v-if="schedule"
             :class="[
                 'p-2 rounded-lg h-full transition-all duration-200',
                 
                 { 'opacity-75': schedule.is_disabled }
             ]"
             @click="$emit('click', schedule)">
            <div class="flex flex-col gap-1">
                <div class="font-medium text-sm">
                    {{ schedule?.cst?.subject?.name || schedule?.subject }}
                </div>
                <div class="text-xs">
                    {{ schedule?.cst?.teacher?.name || schedule?.teacher }}
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else
             class="p-2 h-full flex items-center justify-center border border-dashed
                    border-gray-300 rounded cursor-pointer hover:bg-gray-50"
             @click="$emit('add')">
            <span class="text-gray-400">+</span>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    schedule: {
        type: Object,
        default: null
    }
});

const getSubjectColor = (subject) => {
    if (!subject) return 'bg-gray-100';

    // Simple hash function to generate consistent colors
    const hash = subject.split('').reduce((acc, char) => char.charCodeAt(0) + acc, 0);
    const colors = [
        'bg-blue-100',
        'bg-green-100',
        'bg-yellow-100',
        'bg-red-100',
        'bg-purple-100',
        'bg-pink-100',
        'bg-indigo-100',
    ];
    return colors[hash % colors.length];
};
</script>

<style scoped>
.schedule-cell {
    min-height: 100px;
}
</style>



