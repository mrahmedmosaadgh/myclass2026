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
    if (!props.fullName?.trim()) {
        return '';
    }

    const names = props.fullName
        .trim()
        .split(' ')
        .filter(name => name.length > 0);

    if (names.length === 0) {
        return '';
    }

    if (names.length === 1) {
        return toProperCase(names[0].slice(0, props.letters_count));
    }

    const firstName = toProperCase(names[0].slice(0, props.letters_count));
    const lastName = toProperCase(names[1].slice(0, props.letters_count));

    return  {
        firstName,
        lastName,
        separator:props.separator,
        fullName: `${firstName}${props.separator}${lastName}`
    };
    // return `${firstName}${props.separator}${lastName}`;
});
</script>

<template>
    <div class="name-abbreviator">
        <!-- <span class="abbreviation inline-flex items-center justify-center " role="button" tabindex="0">{{ abbreviatedName?.fullName }}</span><div class="tooltip" role="tooltip" :aria-label="props.fullName">{{ props.fullName }}</div> -->
        <!-- <span class="abbreviation inline-flex items-center justify-center " role="button" tabindex="0">{{ abbreviatedName?.firstName }}</span><div class="tooltip" role="tooltip" :aria-label="props.fullName"></div> -->
       <div class="p-0 inline-flex items-center justify-center scale-75 bg-black text-white px-1 rounded-full">


           <span class="abbreviation inline-flex items-center justify-center " role="button" tabindex="0">{{ abbreviatedName?.firstName }} </span><div class="tooltip" role="tooltip" :aria-label="props.fullName">{{ props.fullName }} </div>
           <span class="abbreviation inline-flex items-center justify-center px-1 " role="button" tabindex="0"> {{abbreviatedName?.separator }} </span><div class="tooltip" role="tooltip" :aria-label="props.fullName">{{ props.fullName }} </div>
           <span class="abbreviation inline-flex items-center justify-center " role="button" tabindex="0"> {{ abbreviatedName?.lastName }}</span><div class="tooltip" role="tooltip" :aria-label="props.fullName">{{ props.fullName }} </div>
        </div>
    </div>
</template>

<style scoped>
.name-abbreviator {
    @apply relative inline-flex items-center justify-center;
}

.abbreviation {
    @apply cursor-help font-medium text-sm transition-colors duration-200
           hover:text-opacity-80 select-none;
}

.tooltip {
    @apply absolute invisible opacity-0 bg-gray-800 text-white text-xs
           rounded-md py-1.5 px-3 -top-8 left-1/2 transform -translate-x-1/2
           transition-all duration-200 whitespace-nowrap z-50
           shadow-lg;
}

/* Show tooltip on hover */
.name-abbreviator:hover .tooltip {
    @apply visible opacity-100;
}

/* Add arrow to tooltip */
.tooltip::after {
    content: '';
    @apply absolute -bottom-1 left-1/2 transform -translate-x-1/2
           border-solid border-4 border-transparent border-t-gray-800;
}

/* Animation */
@keyframes tooltipFade {
    from {
        opacity: 0;
        transform: translate(-50%, -8px);
    }
    to {
        opacity: 1;
        transform: translate(-50%, 0);
    }
}

.name-abbreviator:hover .tooltip {
    animation: tooltipFade 0.2s ease-out forwards;
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
    .tooltip {
        @apply bg-gray-700;
    }

    .tooltip::after {
        @apply border-t-gray-700;
    }
}

/* Mobile optimization */
@media (max-width: 640px) {
    .tooltip {
        @apply text-[10px] py-1 px-2;
    }
}
</style>




