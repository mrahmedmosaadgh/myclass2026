<script setup>
import { ref, computed, onMounted, watch } from 'vue';

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
    },
    title: {
        type: String,
        required: true
    },
    fields: {
        type: Array,
        default: () => []
    },
    formData: {
        type: Object,
        default: () => ({})
    }
});

const emit = defineEmits(['close', 'submitted']);

const form = ref({});

// Initialize form with formData when component mounts or formData changes
watch(() => props.formData, (newVal) => {
    form.value = { ...newVal };
}, { immediate: true });

const maxWidthClass = computed(() => {
    return {
        'sm': 'sm:max-w-sm',
        'md': 'sm:max-w-md',
        'lg': 'sm:max-w-lg',
        'xl': 'sm:max-w-xl',
        '2xl': 'sm:max-w-2xl',
    }[props.maxWidth];
});

const close = () => {
    if (props.closeable) {
        emit('close');
    }
};

const submitForm = () => {
    emit('submitted', {
        form: form.value,
        onSuccess: () => {
            close();
        },
        onError: (errors) => {
            console.error('Form submission errors:', errors);
        }
    });
};
</script>

<template>
    <div>
        <teleport to="body">
            <div v-if="show" class="fixed inset-0 overflow-y-auto px-4 py-6 sm:px-0 z-50">
                <div class="fixed inset-0 transform transition-all" @click="close">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>

                <div class="mb-6 bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:w-full sm:mx-auto" :class="maxWidthClass">
                    <div class="px-6 py-4">
                        <div class="text-lg font-medium text-gray-900">
                            {{ title }}
                        </div>

                        <div class="mt-4">
                            <form @submit.prevent="submitForm">
                                <div class="space-y-4">
                                    <div v-for="field in fields" :key="field.name" class="form-group">
                                        <label :for="field.name" class="block text-sm font-medium text-gray-700">
                                            {{ field.label }}
                                        </label>
                                        <input 
                                            v-if="field.type === 'text' || field.type === 'number'"
                                            v-model="form[field.name]"
                                            :type="field.type"
                                            :required="field.required"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        >
                                        <select 
                                            v-else-if="field.type === 'select'"
                                            v-model="form[field.name]"
                                            :required="field.required"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        >
                                            <option value="">Select an option</option>
                                            <option v-for="option in field.options" 
                                                    :key="option.value" 
                                                    :value="option.value">
                                                {{ option.label }}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-gray-100 text-right">
                        <button type="button" 
                                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:ring focus:ring-blue-200 active:text-gray-800 active:bg-gray-50 disabled:opacity-25 transition" 
                                @click="close">
                            Cancel
                        </button>
                        <button type="button" 
                                @click="submitForm" 
                                class="ml-3 inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition">
                            {{ formData?.id ? 'Update' : 'Save' }}
                        </button>
                    </div>
                </div>
            </div>
        </teleport>
    </div>
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





