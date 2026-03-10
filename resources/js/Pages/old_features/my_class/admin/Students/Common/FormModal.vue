<template>
    <!-- Change this value to adjust size: 3xl, 4xl, 5xl, 6xl, 7xl, or full -->
    <!--  -->
    form:{{ props }}
    <Modal max-width="6xl"
        :show="show"
        :closeable="!submitting"
        :persistent="submitting"
        :loading="submitting"
        @close="$emit('close')"
    >
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900">
                {{ props.editing ? `Edit ${props.title}` : `Create New ${props.title}` }}
            </h2>

            <form @submit.prevent="submitForm" class="mt-6">
                <div class="space-y-6">
                    <div v-for="field in normalizedFields" :key="field.name" class="mb-4">
                        <div v-if="field.type === 'checkbox'" class="flex items-center">
                            <input
                                :id="field.name"
                                :checked="form[field.name]"
                                @change="form[field.name] = $event.target.checked"
                                type="checkbox"
                                class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                            >
                            <label :for="field.name" class="ml-2 block text-sm text-gray-900">
                                {{ field.label }}
                            </label>
                            <span v-if="field.help" class="ml-2 text-sm text-gray-500">
                                {{ field.help }}
                            </span>
                        </div>

                        <template v-else>
                            <label :for="field.name" class="block text-sm font-medium text-gray-700">
                                {{ field.label }}
                                <span v-if="field.required" class="text-red-500">*</span>
                            </label>

                            <!-- Text and Number inputs -->
                            <input
                                v-if="['text', 'number', 'date'].includes(field.type)"
                                :value="form[field.name]"
                                @input="form[field.name] = $event.target.value"
                                :type="field.type"
                                :id="field.name"
                                :min="field.min"
                                :required="field.required"
                                :placeholder="field.placeholder"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            >

                            <!-- Select input -->
                            <select
                                v-if="field.type === 'select'"
                                :id="field.name"
                                v-model="form[field.name]"
                                :required="field.required"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            >
                                <option value="">{{ field.placeholder || `Select ${field.label}` }}</option>
                                <option
                                    v-for="option in field.options"
                                    :key="option.value"
                                    :value="option.value"
                                >
                                    {{ option.label }}
                                </option>
                            </select>

                            <div v-if="errors[field.name]" class="mt-1 text-sm text-red-600">
                                {{ errors[field.name] }}
                            </div>
                        </template>
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button
                        type="button"
                        class="mr-3 inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                        :disabled="submitting"
                        @click="$emit('close')"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md text-sm text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                        :disabled="submitting"
                    >
                        {{ submitting ? 'Saving...' : 'Save' }}
                    </button>
                </div>
            </form>
        </div>
    </Modal>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import Modal from './Modal.vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    title: {
        type: String,
        required: true
    },
    fields: {
        type: Array,
        required: true
    },
    editing: {
        type: [Object, null],
        default: null
    },
    errors: {
        type: Object,
        default: () => ({})
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

const emit = defineEmits(['close', 'submitted']);

// Declare reactive variables
const form = ref({});
const errors = ref({});
const submitting = ref(false);

// Normalize fields to ensure all required properties exist
const normalizedFields = computed(() => {
    return props.fields.map(field => ({
        type: 'text',
        required: false,
        placeholder: '',
        ...field,
        options: Array.isArray(field.options) ? field.options :
                (field.options?.value || [])
    }));
});

// Initialize form with default values
const initForm = () => {
    const newForm = {};

    // Get fields from props
    props.fields.forEach(field => {
        if (props.editing) {
            // If editing, use values from editing object
            newForm[field.name] = props.editing[field.name] ?? field.default ?? '';
        } else {
            // If creating new, use default values
            if (field.type === 'checkbox') {
                newForm[field.name] = field.default ?? false;
            } else if (field.type === 'select') {
                newForm[field.name] = field.default ?? '';
            } else {
                newForm[field.name] = field.default ?? '';
            }
        }
    });

    console.log('Initialized form with values:', newForm); // Debug log
    form.value = newForm;
    errors.value = {};
};

// Watch for show changes
watch(() => props.show, (newShow) => {
    console.log('Show changed:', newShow);
    if (newShow) {
        initForm();
    }
}, { immediate: true });

// Watch for editing changes
watch(() => props.editing, (newEditing) => {
    console.log('Editing changed:', newEditing);
    if (props.show) {  // Only reinit if modal is shown
        initForm();
    }
}, { deep: true });

const closeModal = () => {
    if (submitting.value) return;
    emit('close');
    errors.value = {};
};

const submitForm = () => {
    if (submitting.value) return;
    submitting.value = true;

    emit('submitted', {
        form: form.value,
        onSuccess: () => {
            submitting.value = false;
            closeModal();
        },
        onError: (validationErrors) => {
            submitting.value = false;
            errors.value = validationErrors;
        }
    });
};
</script>























