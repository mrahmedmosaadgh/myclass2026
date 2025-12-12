<template>
    <div class="h-full min-h-[80px]">
        <!-- Schedule Content -->
        <div v-if="schedule"
             :style="`background-color: ${schedule?.cst?.subject?.color_bg}; color: ${schedule?.cst?.subject?.color_text}`"
             :class="[
                 'p-2 rounded-lg h-full transition-all duration-200 hover:shadow-md cursor-pointer',
                 { 'opacity-75': schedule.is_disabled }
             ]"
             @click="$emit('click', schedule)">
            <div class="flex flex-col gap-1">
                <div class="font-medium text-sm truncate">
                    {{ schedule?.cst?.subject?.name || schedule?.subject }}
                </div>
                <div class="text-xs truncate">
                    {{ schedule?.cst?.teacher?.name || schedule?.teacher }}
                </div>
                <div class="text-xs opacity-75 truncate">
                    {{ schedule?.cst?.classroom?.name }}
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else
             class="p-2 h-full flex items-center justify-center border border-dashed
                    border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50
                    transition-colors duration-200"
             @click="$emit('add')">
            <span class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
            </span>
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




