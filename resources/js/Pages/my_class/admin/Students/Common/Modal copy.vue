<script setup>
import { computed, onMounted, onUnmounted, ref, watch, nextTick } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    maxWidth: {
        type: String,
        default: '2xl',
    },
    closeable: {
        type: Boolean,
        default: true,
    },
    closeOnClickOutside: {
        type: Boolean,
        default: true,
    },
    persistent: {
        type: Boolean,
        default: false,
    },
    loading: {
        type: Boolean,
        default: false,
    },
    errors: {
        type: Object,
        default: () => ({})
    },
    submitting: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['close', 'opened', 'closed']);
const dialog = ref(null);
const modalContent = ref(null);
const showSlot = ref(props.show);
const isVisible = ref(false);

// Handle modal visibility
watch(() => props.show, async (show) => {
    if (show) {
        document.body.style.overflow = 'hidden';
        showSlot.value = true;
        isVisible.value = true;
        await nextTick();
        dialog.value?.showModal();
        emit('opened');
    } else {
        isVisible.value = false;
        document.body.style.overflow = null;
        setTimeout(async () => {
            dialog.value?.close();
            showSlot.value = false;
            emit('closed');
        }, 200);
    }
});

// Close modal handlers
const close = () => {
    if (props.closeable && !props.persistent && !props.loading) {
        emit('close');
    }
};

const closeOnEscape = (e) => {
    if (e.key === 'Escape' && !props.persistent && !props.loading) {
        e.preventDefault();
        if (props.show) {
            close();
        }
    }
};

const handleClickOutside = (e) => {
    if (!modalContent.value?.contains(e.target) &&
        props.closeOnClickOutside &&
        !props.persistent &&
        !props.loading) {
        close();
    }
};

// Focus trap implementation
const focusableElements = 'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])';
let firstFocusable;
let lastFocusable;

const trapFocus = (e) => {
    if (!dialog.value?.contains(document.activeElement)) {
        firstFocusable.focus();
        return;
    }

    const isTabPressed = e.key === 'Tab';
    if (!isTabPressed) return;

    if (e.shiftKey) {
        if (document.activeElement === firstFocusable) {
            lastFocusable.focus();
            e.preventDefault();
        }
    } else {
        if (document.activeElement === lastFocusable) {
            firstFocusable.focus();
            e.preventDefault();
        }
    }
};

const setupFocusTrap = () => {
    const focusableContent = dialog.value?.querySelectorAll(focusableElements);
    if (focusableContent?.length) {
        firstFocusable = focusableContent[0];
        lastFocusable = focusableContent[focusableContent.length - 1];
        firstFocusable.focus();
    }
};

// Lifecycle hooks
onMounted(() => {
    document.addEventListener('keydown', closeOnEscape);
    document.addEventListener('keydown', trapFocus);
});

onUnmounted(() => {
    document.removeEventListener('keydown', closeOnEscape);
    document.removeEventListener('keydown', trapFocus);
    document.body.style.overflow = null;
});

// Computed styles
const maxWidthClass = computed(() => {
    return {
        'sm': 'sm:max-w-sm',
        'md': 'sm:max-w-md',
        'lg': 'sm:max-w-lg',
        'xl': 'sm:max-w-xl',
        '2xl': 'sm:max-w-2xl',
        '3xl': 'sm:max-w-3xl',
        '4xl': 'sm:max-w-4xl',
        '5xl': 'sm:max-w-5xl',
        '6xl': 'sm:max-w-6xl',
        '7xl': 'sm:max-w-7xl',
        'full': 'sm:max-w-full',
    }[props.maxWidth];
});
</script>

<template>
    <Teleport to="body">
        <div v-if="showSlot" @click="handleClickOutside">
            <dialog
                ref="dialog"
                class="fixed inset-0 overflow-y-auto px-6 py-8 sm:px-0 z-50"
                :class="[maxWidthClass, { 'pointer-events-none': loading }]"
                @close="close"
            >
                <div
                    ref="modalContent"
                    :class="[
                        'modal-content bg-white dark:bg-gray-800 rounded-lg overflow-hidden shadow-xl transform transition-all sm:w-full sm:mx-auto',
                        { 'modal-enter': isVisible }
                    ]"
                >
                    <!-- Loading overlay -->
                    <div v-if="loading" class="absolute inset-0 bg-white/50 dark:bg-gray-800/50 flex items-center justify-center z-50">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
                    </div>

                    <!-- Close button -->
                    <button
                        v-if="closeable && !persistent && !loading"
                        class="absolute top-4 right-4 text-gray-400 hover:text-gray-500 dark:text-gray-500 dark:hover:text-gray-400"
                        @click="close"
                    >
                        <!-- <span class="sr-only">Close</span> -->
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>

                    <div class="modal-body">
                        <slot />
                    </div>
                </div>
            </dialog>
        </div>
    </Teleport>
</template>

<style scoped>
.modal-content {
    max-height: 95vh;
    margin: 1rem auto;
    position: relative;
    opacity: 0;
    transform: scale(0.95);
    transition: all 0.2s ease-out;
}

.modal-content.modal-enter {
    opacity: 1;
    transform: scale(1);
}

.modal-body {
    overflow-y: auto;
    max-height: calc(95vh - 2rem);
    padding: 1.5rem;
}

dialog {
    background: transparent;
    border: none;
    padding: 0;
}

dialog::backdrop {
    background-color: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
    transition: opacity 0.2s ease-out;
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
    dialog::backdrop {
        background-color: rgba(0, 0, 0, 0.7);
    }
}
</style>





