<script setup>
import { computed } from 'vue';

const props = defineProps({
    fullName: {
        type: String,
        required: false,
        default: ''
    },
    separator: {
        type: String,
        default: ' '
    },
    letters_count: {
        type: Number,
        default: 3
    }
});

const toProperCase = (str) => {
    return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
};

const abbreviatedName = computed(() => {
    if (!props.fullName?.trim()) return '';

    const names = props.fullName.trim().split(' ').filter(name => name && name.length > 0);
    if (!names || names.length === 0) return '';
    if (names.length === 1) return toProperCase(names[0].substring(0, props.letters_count));

    const firstName = toProperCase(names[0].substring(0, props.letters_count));
    const secondName = toProperCase(names[1].substring(0, props.letters_count));

    return `${firstName}${props.separator}${secondName}`;
});
</script>

<template>
    <div class="relative inline-block group">
        <span class="cursor-help">{{ abbreviatedName }}</span>
        <div class="absolute invisible group-hover:visible bg-gray-800 text-white text-sm rounded py-1 px-2 -mt-1
                    transform -translate-y-full opacity-0 group-hover:opacity-100 transition-opacity duration-200
                    whitespace-nowrap z-50">
            {{ fullName }}
        </div>
    </div>
</template>






