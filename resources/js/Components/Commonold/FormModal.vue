<template>
    <Modal :show="show" @close="closeModal">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900">
                {{ editing ? `Edit ${title}` : `Create New ${title}` }}
            </h2>

            <form @submit.prevent="submitForm" class="mt-6">
                <div class="space-y-6">
                    <div v-for="field in fields" :key="field.name" class="mb-4">
                        <label :for="field.name" class="block text-sm font-medium text-gray-700">
                            {{ field.label }}
                        </label>

                        <!-- Text and Number inputs -->
                        <input v-if="field.type === 'text' || field.type === 'number' || field.type === 'date'"
                            v-model="form[field.name]"
                            :type="field.type"
                            :id="field.name"
                            :min="field.min"
                            :required="field.required"
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
                            <option value="">Select {{ field.label }}</option>
                            <option
                                v-for="option in field.options"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </option>
                        </select>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <SecondaryButton @click="closeModal">Cancel</SecondaryButton>
                    <PrimaryButton type="submit">Save</PrimaryButton>
                </div>
            </form>
        </div>
    </Modal>
</template>

<script setup>
import { ref, watch } from 'vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

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
        type: Object,
        default: null
    }
});

const emit = defineEmits(['close', 'submitted']);

const submitting = ref(false);
const errors = ref({});
const form = ref({});

// Initialize form with default values
const initForm = () => {
    const newForm = {};
    if (!props.fields) return newForm;  // Add this guard clause

    props.fields.forEach(field => {
        // If options is a computed ref, get its value
        if (field.options && typeof field.options === 'object' && 'value' in field.options) {
            field.options = field.options.value;
        }
        // Set default value
        newForm[field.name] = props.editing?.[field.name] ?? field.default ?? '';
    });
    form.value = newForm;
};

// Watch for changes in editing prop
watch(() => props.editing, () => {
    initForm();
}, { immediate: true });

// Watch for show prop changes
watch(() => props.show, (newVal) => {
    if (newVal) {
        initForm();
    }
});

const closeModal = () => {
    emit('close');
    errors.value = {};
};

const submitForm = () => {
    submitting.value = true;
console.log('submitForm inside:'+form.value);
console.log( form.value);

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








