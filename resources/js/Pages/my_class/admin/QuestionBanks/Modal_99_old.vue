<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

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
});

const emit = defineEmits(['close']);
const dialog = ref(null);
const showSlot = ref(props.show);
const modalContent = ref(null);

const maxWidthClass = computed(() => {
    return {
        'sm': 'sm:max-w-sm',
        'md': 'sm:max-w-md',
        'lg': 'sm:max-w-lg',
        'xl': 'sm:max-w-xl',
        '2xl': 'sm:max-w-2xl',
    }[props.maxWidth];
});

const handleClickOutside = (event) => {
    if (props.closeable && modalContent.value && !modalContent.value.contains(event.target)) {
        close();
    }
};

const close = () => {
    if (props.closeable) {
        emit('close');
    }
};

const closeOnEscape = (e) => {
    if (e.key === 'Escape' && props.closeable && props.show) {
        close();
    }
};

watch(() => props.show, () => {
    if (props.show) {
        document.body.style.overflow = 'hidden';
        showSlot.value = true;
        dialog.value?.showModal();
    } else {
        document.body.style.overflow = null;
        setTimeout(() => {
            dialog.value?.close();
            showSlot.value = false;
        }, 200);
    }
});

onMounted(() => document.addEventListener('keydown', closeOnEscape));

onUnmounted(() => {
    document.removeEventListener('keydown', closeOnEscape);
    document.body.style.overflow = null;
});
</script>

<template>
    <div v-if="showSlot">
        <dialog
            ref="dialog"
            class="fixed inset-0 overflow-y-auto px-4 py-6 sm:px-0 z-50"
            :class="maxWidthClass"
            @click="handleClickOutside"
        >
            <div
                ref="modalContent"
                class="modal-content bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:w-full sm:mx-auto"
            >
                <div class="modal-body">
                    <slot />
                </div>
            </div>
        </dialog>
    </div>
</template>

<style scoped>
.modal-content {
    max-height: 90vh;
    margin: 2rem auto;
}

.modal-body {
    overflow-y: auto;
    max-height: calc(90vh - 4rem); /* Account for margin */
}

dialog::backdrop {
    background-color: rgba(0, 0, 0, 0.5);
}
</style>


