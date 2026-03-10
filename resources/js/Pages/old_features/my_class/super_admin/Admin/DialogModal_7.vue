<script setup>
import { ref, watch, computed, onMounted, onUnmounted } from 'vue';

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

const dialog = ref(null);
const showModal = ref(props.show);

watch(() => props.show, (show) => {
    if (show) {
        showModal.value = true;
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = null;
        setTimeout(() => {
            showModal.value = false;
        }, 200);
    }
});

const close = () => {
    if (props.closeable) {
        emit('close');
    }
};

const maxWidthClass = computed(() => {
    return {
        'sm': 'sm:max-w-sm',
        'md': 'sm:max-w-md',
        'lg': 'sm:max-w-lg',
        'xl': 'sm:max-w-xl',
        '2xl': 'sm:max-w-2xl',
    }[props.maxWidth];
});

const closeOnEscape = (e) => {
    if (e.key === 'Escape' && props.show) {
        close();
    }
};

onMounted(() => {
    document.addEventListener('keydown', closeOnEscape);
});

onUnmounted(() => {
    document.removeEventListener('keydown', closeOnEscape);
});
</script>

<template>
    <teleport to="body">
        <div v-if="showModal" class="fixed inset-0 overflow-y-auto px-4 py-6 sm:px-0 z-50">
            <!-- Background overlay -->
            <div
                class="fixed inset-0 transform transition-all"
                @click="close"
            >
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>

            <!-- Modal panel -->
            <div
                class="mb-6 bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:w-full sm:mx-auto"
                :class="[maxWidthClass]"
            >
                <!-- Modal title -->
                <div class="px-6 py-4">
                    <div class="text-lg font-medium text-gray-900">
                        <slot name="title"></slot>
                    </div>
                </div>

                <!-- Modal content -->
                <div class="px-6 py-4">
                    <slot name="content"></slot>
                </div>

                <!-- Modal footer -->
                <div class="px-6 py-4 bg-gray-100 text-right">
                    <slot name="footer"></slot>
                </div>

                <!-- Close button -->
                <button
                    v-if="closeable"
                    type="button"
                    class="absolute top-4 right-4 text-gray-400 hover:text-gray-500"
                    @click="close"
                >
                    <span class="sr-only">Close</span>
                    <svg
                        class="h-6 w-6"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </button>
            </div>
        </div>
    </teleport>
</template>

<style scoped>
.fixed {
    position: fixed;
}

/* Fade transition */
.transform {
    transition-property: opacity, transform;
    transition-duration: 0.3s;
}

/* Modal animation */
.mb-6 {
    animation: modal-in 0.3s ease-out;
}

@keyframes modal-in {
    0% {
        opacity: 0;
        transform: translateY(-1rem);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
