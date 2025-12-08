<script setup>
import { ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
    isOpen: Boolean
});

const emit = defineEmits(['close', 'update:openSections']);
const page = usePage();
const openSections = ref(new Set());

watch(() => page.url, () => {
    emit('update:openSections', openSections.value);
}, { immediate: true });

defineExpose({ openSections });
</script>

<template>
    <div>
        <!-- Backdrop -->
        <transition
            enter-active-class="transition-opacity ease-linear duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity ease-linear duration-300"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="isOpen"
                class="fixed inset-0 bg-gray-600 bg-opacity-75 z-40"
                @click="$emit('close')"
            ></div>
        </transition>

        <!-- Drawer -->
        <transition
            enter-active-class="transition ease-in-out duration-300 transform"
            enter-from-class="translate-x-full"
            enter-to-class="translate-x-0"
            leave-active-class="transition ease-in-out duration-300 transform"
            leave-from-class="translate-x-0"
            leave-to-class="translate-x-full"
        >
            <div
                v-if="isOpen"
                class="fixed inset-y-0 right-0 max-w-xs w-full bg-white shadow-xl z-50 overflow-y-auto"
            >
                <slot :open-sections="openSections.value"></slot>
            </div>
        </transition>
    </div>
</template>

