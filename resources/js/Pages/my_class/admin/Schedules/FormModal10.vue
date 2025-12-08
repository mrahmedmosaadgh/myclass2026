<script setup>
const props = defineProps({
    show: Boolean
});

defineEmits(['close']);</script>

<template>const props = defineProps({
    show: Boolean
});

defineEmits(['close']);show{{ show }}
    <div v-if="show" class="fixed inset-0 overflow-y-auto px-4 py-6 sm:px-0 z-50">
        <!-- Background overlay -->
        <div
            class="fixed inset-0 transform transition-all"
            @click="!submitting && closeModal()"
        >
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <!-- Modal panel -->
        <div class="p-6">
            <slot></slot>
        </div>
    </div>
</template>
