<script setup>
import { computed } from 'vue';
// import * as Solid from '@heroicons/vue/24/solid';
// import * as Outline from '@heroicons/vue/24/outline';
// import * as Mini from '@heroicons/vue/20/solid';

const props = defineProps({
    icon: {
        type: String,
        required: true
    },
    outline: {
        type: Boolean,
        default: false
    },
    mini: {
        type: Boolean,
        default: true
    }
});

const IconComponent = computed(() => {
    let icons;
    if (props.mini) {
        icons = Mini;
    } else {
        icons = props.outline ? Outline : Solid;
    }

    const iconName = props.icon
        .split('-')
        .map(part => part.charAt(0).toUpperCase() + part.slice(1))
        .join('') + 'Icon';

    return icons[iconName];
});
</script>

<template>
    <component
        :is="IconComponent"
        v-if="IconComponent"
        class="inline-block"
        v-bind="$attrs"
    />
</template>




