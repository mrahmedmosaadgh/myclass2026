<template>
    <button
        @click="$emit('click')"
        type="button"
        class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition"
    >
        <Printer class="w-4 h-4 mr-2" />
        Print
    </button>
</template>

<script setup>
import { Printer } from 'lucide-vue-next';

defineEmits(['click']);
</script>