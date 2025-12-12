<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const emit = defineEmits(['close', 'submit', 'update:form']);
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
    fields: {
        type: Array,
        required: true
    },
    form: {
        type: Object,
        required: true
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
    },
    options: {
        type: Object,
        default: () => ({
            schools: [],
            academicYears: [],
            semesters: [],
            statuses: [
                { id: 'draft', name: 'Draft' },
                { id: 'active', name: 'Active' },
                { id: 'archived', name: 'Archived' }
            ]
        })
    }
});

const form = ref({
    school_id: '',
    name: '',
    description: '',
    active: true,
    copy_date: null,
    academic_year_id: '',
    semester_id: '',
    week_number: null,
    status: 'draft',
    metadata: null,
    notes: ''
});

// Initialize local form data
const localForm = ref({ ...props.form });

// Watch for form prop changes
watch(() => props.form, (newValue) => {
    localForm.value = { ...newValue };
}, { deep: true });

// Watch for show prop changes to reset form
watch(() => props.show, (newValue) => {
    if (newValue) {
        localForm.value = { ...props.form };
    }
});

const updateFormValue = (fieldName, value) => {
    localForm.value[fieldName] = value;
    emit('update:form', { ...localForm.value });
};

const handleSubmit = () => {
    emit('submit', {
        form: localForm.value,
        onSuccess: () => {
            closeModal();
        },
        onError: (errors) => {
            // Handle errors if needed
        }
    });
};

const closeModal = () => {
    emit('close');
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

const normalizedFields = computed(() => {
    return props.fields.map(field => {
        let options = field.options;

        // Handle special cases for select fields
        if (field.type === 'select') {
            switch (field.name) {
                case 'status':
                    options = [
                        { value: 'draft', label: 'Draft' },
                        { value: 'active', label: 'Active' },
                        { value: 'archived', label: 'Archived' }
                    ];
                    break;
                case 'school_id':
                    options = props.options.schools.map(school => ({
                        value: school.id,
                        label: school.name
                    }));
                    break;
                case 'academic_year_id':
                    options = props.options.academicYears.map(year => ({
                        value: year.id,
                        label: year.name
                    }));
                    break;
            }
        }

        return {
            type: 'text',
            required: false,
            placeholder: '',
            ...field,
            options: options || []
        };
    });
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
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    {{ title }}
                </h2>

                <form @submit.prevent="handleSubmit" class="mt-6">
                    <div class="space-y-6">
                        <div v-for="field in normalizedFields" :key="field.name" class="mb-4">
                            <!-- Checkbox Input -->
                            <div v-if="field.type === 'checkbox'" class="flex items-center">
                                <input
                                    :id="field.name"
                                    type="checkbox"
                                    :checked="localForm[field.name]"
                                    @change="updateFormValue(field.name, $event.target.checked)"
                                    class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                    :disabled="submitting"
                                >
                                <InputLabel :for="field.name" class="ml-2">
                                    {{ field.label }}
                                </InputLabel>
                            </div>

                            <!-- Other Input Types -->
                            <template v-else>
                                <InputLabel :for="field.name">
                                    {{ field.label }}
                                    <span v-if="field.required" class="text-red-500">*</span>
                                </InputLabel>

                                <!-- Select Input -->
                                <select
                                    v-if="field.type === 'select'"
                                    :id="field.name"
                                    v-model="localForm[field.name]"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    :required="field.required"
                                    :disabled="submitting"
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

                                <!-- Textarea Input -->
                                <textarea
                                    v-else-if="field.type === 'textarea'"
                                    :id="field.name"
                                    :value="localForm[field.name]"
                                    @input="updateFormValue(field.name, $event.target.value)"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    :required="field.required"
                                    :disabled="submitting"
                                    :rows="field.rows || 3"
                                    :placeholder="field.placeholder"
                                />

                                <!-- Text, Number, Date Inputs -->
                                <input
                                    v-else
                                    :id="field.name"
                                    :type="field.type"
                                    :value="localForm[field.name]"
                                    @input="updateFormValue(field.name, $event.target.value)"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    :required="field.required"
                                    :disabled="submitting"
                                    :min="field.min"
                                    :max="field.max"
                                    :maxlength="field.maxLength"
                                    :placeholder="field.placeholder"
                                />
                            </template>

                            <!-- Error Message -->
                            <InputError
                                v-if="errors[field.name]"
                                :message="errors[field.name]"
                                class="mt-2"
                            />
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <SecondaryButton
                            type="button"
                            @click="closeModal"
                            :disabled="submitting"
                        >
                            Cancel
                        </SecondaryButton>

                        <PrimaryButton
                            type="submit"
                            :disabled="submitting"
                            :class="{ 'opacity-75 cursor-not-allowed': submitting }"
                        >
                            {{ submitting ? 'Saving...' : 'Save' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>












