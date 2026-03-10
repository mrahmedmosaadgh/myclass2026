<script setup>
import { computed, ref } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    modelValue: {
        type: Boolean,
        default: false
    },
    submitting: {
        type: Boolean,
        default: false
    },
    errors: {
        type: Object,
        default: () => ({})
    },
    maxWidth: {
        type: String,
        default: '2xl'
    },
    form: {
        type: Object,
        required: true
    }
});

const emit = defineEmits(['update:modelValue', 'submit']);

const maxWidthClass = computed(() => {
    return {
        'sm': 'sm:max-w-sm',
        'md': 'sm:max-w-md',
        'lg': 'sm:max-w-lg',
        'xl': 'sm:max-w-xl',
        '2xl': 'sm:max-w-2xl',
    }[props.maxWidth];
});

const closeModal = () => {
    if (!props.submitting) {
        emit('update:modelValue', false);
    }
};

const onSubmit = () => {
    emit('submit', {
        form: props.form,
        onSuccess: () => {
            emit('update:modelValue', false);
        },
        onError: (errors) => {
            // Handle errors if needed
        }
    });
};
</script>

<template>
    <div v-if="modelValue" class="fixed inset-0 overflow-y-auto px-4 py-6 sm:px-0 z-50">
        <div class="fixed inset-0 transform transition-all" @click="closeModal">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <div class="mb-6 bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:w-full sm:mx-auto" :class="maxWidthClass">
            <form @submit.prevent="onSubmit">
                <div class="px-6 py-4 border-b border-gray-200">
                    <slot name="header"></slot>
                </div>

                <div class="p-6">
                    <div v-if="errors?.error" class="mb-4 p-4 bg-red-50 border border-red-200 rounded-md">
                        <p class="text-red-600">{{ errors.error }}</p>
                    </div>
                    <slot></slot>
                </div>

                <div class="px-6 py-4 bg-gray-50 text-right">
                    <SecondaryButton type="button" @click="closeModal" :disabled="submitting">
                        Cancel
                    </SecondaryButton>
                    <PrimaryButton class="ml-3" type="submit" :disabled="submitting">
                        {{ submitting ? 'Saving...' : 'Save' }}
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </div>
</template>



