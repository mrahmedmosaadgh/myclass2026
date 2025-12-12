<script setup>
import { computed, watch } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    maxWidth: {
        type: String,
        default: '2xl'
    },
    closeable: {
        type: Boolean,
        default: true
    }
});

const emit = defineEmits(['close']);

const maxWidthClass = computed(() => {
    return {
        'sm': 'sm:max-w-sm',
        'md': 'sm:max-w-md',
        'lg': 'sm:max-w-lg',
        'xl': 'sm:max-w-xl',
        '2xl': 'sm:max-w-2xl',
        '7xl': 'sm:max-w-7xl'
    }[props.maxWidth];
});

const close = () => {
    if (props.closeable) {
        emit('close');
    }
};

// Handle ESC key press
const closeOnEscape = (e) => {
    if (e.key === 'Escape' && props.show && props.closeable) {
        close();
    }
};

// Add/remove event listeners and manage body scroll
watch(() => props.show, (value) => {
    if (value) {
        document.addEventListener('keydown', closeOnEscape);
        document.body.style.overflow = 'hidden';
    } else {
        document.removeEventListener('keydown', closeOnEscape);
        document.body.style.overflow = null;
    }
});
</script>

<template>
    <div v-show="show" class="fixed inset-0 overflow-y-auto px-4 py-6 sm:px-0 z-50">
        <!-- Background overlay -->
        <div
            class="fixed inset-0 transform transition-all"
            @click="close"
        >
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <!-- Modal panel -->
        <div
            class="mb-6 bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:w-full sm:mx-auto relative"
            :class="maxWidthClass"
        >
            <!-- Close button -->
            <button
                v-if="closeable"
                type="button"
                class="absolute top-4 right-4 text-gray-400 hover:text-gray-500"
                @click="close"
            >
                <span class="sr-only">Close</span>
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <!-- Title Section -->
            <div class="px-6 py-4 border-b border-gray-200">
                <slot name="title">
                    <!-- Default title slot content -->
                </slot>
            </div>

            <!-- Content Section -->
            <div class="px-6 py-4">
                <slot name="content">
                    <!-- Default content slot -->
                </slot>
            </div>

            <!-- Footer Section -->
            <div v-if="$slots.footer" class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                <slot name="footer">
                    <!-- Default footer slot content -->
                </slot>
            </div>
        </div>
    </div>
</template>

<style scoped>
.fixed {
    position: fixed;
}

/* Add smooth transitions */
.transform {
    transition-property: transform, opacity;
    transition-duration: 300ms;
    transition-timing-function: ease-out;
}

/* Optional: Add enter/leave transitions */
.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}
</style>


