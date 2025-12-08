<template>
    <div 
        v-if="message"
        class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative"
        role="alert"
    >
        <span class="block sm:inline">{{ message }}</span>
    </div>
</template>

<script setup>
defineProps({
    message: {
        type: String,
        required: true
    },
    type: {
        type: String,
        default: 'success'
    }
});
</script>