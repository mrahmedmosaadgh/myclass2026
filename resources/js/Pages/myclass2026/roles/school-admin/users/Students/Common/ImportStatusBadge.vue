<template>
    <span :class="[
        'px-2 py-1 text-xs font-medium rounded-full',
        status === 'new' ? 'bg-green-100 text-green-800' :
        status === 'duplicate' ? 'bg-yellow-100 text-yellow-800' :
        status === 'invalid' ? 'bg-red-100 text-red-800' :
        'bg-gray-100 text-gray-800'
    ]">
        {{ messages[status] || status }}
    </span>
</template>

<script setup>
defineProps({
    status: {
        type: String,
        required: true
    },
    messages: {
        type: Object,
        default: () => ({
            'new': 'Ready to import',
            'duplicate': 'Already exists',
            'invalid': 'Invalid data'
        })
    }
});
</script>