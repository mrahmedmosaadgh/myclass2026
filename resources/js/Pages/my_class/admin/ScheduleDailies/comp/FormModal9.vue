<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const emit = defineEmits(['close', 'submit']);
const modalRef = ref(null);

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    title: {
        type: String,
        required: true
    },
    submitting: {
        type: Boolean,
        default: false
    },
    maxWidth: {
        type: String,
        default: '2xl'
    }
});

const closeModal = () => {
    emit('close');
};

const handleSubmit = () => {
    emit('submit');
};

const closeOnEscape = (e) => {
    if (e.key === 'Escape' && props.show && !props.submitting) {
        closeModal();
    }
};

onMounted(() => {
    document.addEventListener('keydown', closeOnEscape);
});

onUnmounted(() => {
    document.removeEventListener('keydown', closeOnEscape);
});

const maxWidthClass = computed(() => {
    return {
        'sm': 'sm:max-w-sm',
        'md': 'sm:max-w-md',
        'lg': 'sm:max-w-lg',
        'xl': 'sm:max-w-xl',
        '2xl': 'sm:max-w-2xl',
    }[props.maxWidth];
});
</script>

<template>
    <div v-if="show" class="fixed inset-0 overflow-y-auto px-4 py-6 sm:px-0 z-50">
        <!-- Background overlay -->
        <div
            class="fixed inset-0 transform transition-all"
            @click="!submitting && closeModal()"
        >
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <!-- Modal panel -->
        <div
            ref="modalRef"
            class="mb-6 bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:w-full sm:mx-auto"
            :class="maxWidthClass"
        >
            <!-- Header slot -->
            <div class="px-6 py-4 border-b border-gray-200">
                <slot name="header">
                    <h2 class="text-lg font-medium text-gray-900">
                        {{ title }}
                    </h2>
                </slot>
            </div>

            <!-- Body slot -->
            <div class="p-6">
                <slot></slot>
            </div>

            <!-- Footer slot -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
                <slot name="footer">
                    <SecondaryButton
                        @click="closeModal"
                        :disabled="submitting"
                    >
                        Cancel
                    </SecondaryButton>
                    <PrimaryButton
                        @click="handleSubmit"
                        :disabled="submitting"
                    >
                        <span v-if="submitting">Processing...</span>
                        <span v-else>Save</span>
                    </PrimaryButton>
                </slot>
            </div>
        </div>
    </div>
</template>
