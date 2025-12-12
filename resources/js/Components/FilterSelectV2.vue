<template>
    <div class="w-full relative">
        <div v-if="show_length" class="p-0 absolute  -top-2   -right-4 text-xs text-gray-500    border-2 rounded-full   px-2  ">
            {{ options.length }}
        </div>
        <!-- label_only:{{ label_only }}{{ my_label }} -->
        <div class="p-0" v-if="label_only">
{{ my_label }}
        </div>
        <div class="p-0"  v-else>

        <select
        @mouseenter="show_length=true"
        @mouseleave ="show_length=false"
            v-if="options && options.length > 0"
            :value="model"
            @input="handleInput($event.target.value)"
            @change="handleChange($event.target.value)"
            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
        >
            <option value="">{{ placeholder }}</option>
            <option
                v-for="(option, index) in options"
                :key="option[valueKey]"
                :value="option[valueKey]"
                :selected="index === defaultSelectedIndex"
            >
                {{ getOptionLabel(option) }}
            </option>
        </select>
    </div>

    </div>
</template>

<script setup>
import {   onMounted,ref } from 'vue';

const model = defineModel();
const modelObject = defineModel('object');
const show_length = ref(false);
const my_label = ref(null);
const props = defineProps({
    options: {
        type: Array,
        default: () => []
    },
    valueKey: {
        type: String,
        default: 'id'
    },
    labelKey: {
        type: [String, Array],
        default: 'name'
    },
    labelSeparator: {
        type: String,
        default: ' - '
    },
    labelFormatter: {
        type: Function,
        default: null
    },
    placeholder: {
        type: String,
        default: 'Select an option'
    },
    defaultSelectedIndex: {
        type: Number,
        default: null
    },
    label_only: {
        type: Boolean,
        default: false
    },
});

const setDefaultSelection = () => {
    try {
        // Check if options is valid and has items
        if (!props.options || !Array.isArray(props.options) || props.options.length === 0) {
            return;
        }

        // Check if defaultSelectedIndex is valid
        if (props.defaultSelectedIndex === null || props.defaultSelectedIndex < 0 || props.defaultSelectedIndex >= props.options.length) {
            return;
        }

        const defaultOption = props.options[props.defaultSelectedIndex];
        if (defaultOption && defaultOption[props.valueKey]) {
            model.value = defaultOption[props.valueKey];
            modelObject.value = defaultOption;
            my_label.value = getOptionLabel(defaultOption);
        }
    } catch (error) {
        console.error('Error setting default selection:', error);
    }
};

onMounted(() => {
    setDefaultSelection();
});

const getOptionLabel = (option) => {
    if (props.labelFormatter) {
        my_label.value=props.labelFormatter(option)
        return props.labelFormatter(option);
    }

    if (typeof props.labelKey === 'string') {
        return option[props.labelKey];
    }

    if (Array.isArray(props.labelKey)) {
        return props.labelKey
            .map(key => {
                return key.split('.').reduce((obj, k) => obj?.[k], option);
            })
            .filter(Boolean)
            .join(props.labelSeparator);
    }

    return '';
};

const handleInput = (value) => {
    model.value = value;
};

const handleChange = (value) => {
    const selectedObject = props.options.find(option => option[props.valueKey] == value) || null;
    modelObject.value = selectedObject;
};
</script>









