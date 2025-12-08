<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    href: {
        type: String,
        default: '',
    },
    active: {
        type: Boolean,
        default: false,
    },
    as: {
        type: String,
        default: 'link',
    },
});

const classes = computed(() => `block pl-3 pr-4 py-2 border-l-4 text-base font-medium transition duration-150 ease-in-out ${
    props.active
        ? 'border-indigo-400 text-indigo-700 bg-indigo-50 focus:text-indigo-800 focus:bg-indigo-100 focus:border-indigo-700'
        : 'border-transparent text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 focus:text-gray-800 focus:bg-gray-50 focus:border-gray-300'
}`);

const isLink = computed(() => props.as === 'link' && props.href && props.href.length > 0);
</script>

<template>
    <component
        :is="isLink ? Link : 'button'"
        :href="isLink ? href : undefined"
        :type="!isLink ? 'button' : undefined"
        :class="classes"
    >
        <slot />
    </component>
</template>



