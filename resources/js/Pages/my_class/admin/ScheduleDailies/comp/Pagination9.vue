<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    links: {
        type: Array,
        required: true
    },
    loading: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['page-changed']);

// Helper to determine if it's a number link
const isNumberLink = (label) => !isNaN(parseInt(label));

// Helper to determine if it's a previous/next link
const isNavigationLink = (label) => {
    const cleanLabel = label.replace('&laquo;', '«').replace('&raquo;', '»');
    return cleanLabel === '«' || cleanLabel === '»';
};
</script>

<template>
    <div v-if="links.length > 3" class="flex justify-center items-center space-x-1">
        <!-- Previous and Next buttons with arrows -->
        <template v-for="(link, key) in links" :key="key">
            <!-- Navigation Links (Previous/Next) -->
            <template v-if="isNavigationLink(link.label)">
                <button
                    v-if="link.url === null"
                    class="px-4 py-2 text-sm font-medium text-gray-400 bg-white border border-gray-300 rounded-md cursor-not-allowed"
                    :disabled="true"
                    v-html="link.label"
                />
                <button
                    v-else
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200"
                    :class="{ 'opacity-50 cursor-not-allowed': loading }"
                    @click="!loading && emit('page-changed', link.url)"
                    v-html="link.label"
                />
            </template>

            <!-- Number Links -->
            <template v-else-if="isNumberLink(link.label)">
                <button
                    v-if="link.url === null"
                    class="px-4 py-2 text-sm font-medium text-gray-400 bg-white border border-gray-300 rounded-md cursor-not-allowed"
                    :disabled="true"
                    v-html="link.label"
                />
                <button
                    v-else
                    class="px-4 py-2 text-sm font-medium border rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200"
                    :class="{
                        'bg-indigo-600 text-white border-indigo-600 hover:bg-indigo-700': link.active,
                        'text-gray-700 bg-white border-gray-300 hover:bg-gray-50': !link.active,
                        'opacity-50 cursor-not-allowed': loading,
                        'cursor-pointer': !loading
                    }"
                    @click="!loading && emit('page-changed', link.url)"
                    v-html="link.label"
                />
            </template>

            <!-- Ellipsis -->
            <template v-else>
                <span
                    class="px-4 py-2 text-sm font-medium text-gray-700"
                    v-html="link.label"
                />
            </template>
        </template>
    </div>

    <!-- Loading Indicator -->
    <div
        v-if="loading"
        class="absolute inset-0 bg-white bg-opacity-50 flex items-center justify-center"
    >
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
    </div>
</template>

<style scoped>
/* Optional: Add smooth transitions */
button {
    transition: all 0.2s ease-in-out;
}

/* Optional: Add hover effect for active buttons */
button:not(:disabled):hover {
    transform: translateY(-1px);
}

/* Optional: Add active state effect */
button:not(:disabled):active {
    transform: translateY(0);
}
</style>

