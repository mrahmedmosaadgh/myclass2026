<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';

const props = defineProps({
    align: {
        type: String,
        default: 'right',
    },
    width: {
        type: String,
        default: '48',
    },
    contentClasses: {
        type: Array,
        default: () => ['py-1', 'bg-white'],
    },
    autoHide: {
        type: Boolean,
        default: false,
    }
});

let open = ref(false);
let hideTimeout = null;

const clearHideTimeout = () => {
    if (hideTimeout) {
        clearTimeout(hideTimeout);
        hideTimeout = null;
    }
};

const handleMouseLeave = () => {
    if (props.autoHide) {
        hideTimeout = setTimeout(() => {
            open.value = false;
        }, 500);
    }
};

const handleMouseEnter = () => {
    clearHideTimeout();
};

onUnmounted(() => {
    clearHideTimeout();
});

// Close dropdown when clicking outside
const handleClickOutside = (event) => {
    if (open.value && !event.target.closest('.relative')) {
        open.value = false;
    }
};

// Close on escape key
const closeOnEscape = (e) => {
    if (open.value && e.key === 'Escape') {
        open.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
    document.addEventListener('keydown', closeOnEscape);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
    document.removeEventListener('keydown', closeOnEscape);
});

const widthClass = computed(() => {
    return {
        '48': 'w-48',
        '56': 'w-56',
        '64': 'w-64',
    }[props.width.toString()] || 'w-48';
});

const alignmentClasses = computed(() => {
    if (props.align === 'left') {
        return 'ltr:origin-top-left rtl:origin-top-right start-0';
    }
    if (props.align === 'right') {
        return 'ltr:origin-top-right rtl:origin-top-left end-0';
    }
    return 'origin-top';
});
</script>

<template>
    <div
        class="relative inline-block text-left"
        @mouseleave="handleMouseLeave"
        @mouseenter="handleMouseEnter"
    >
        <div @click="open = !open">
            <slot name="trigger" />
        </div>

        <transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="transform opacity-0 scale-95"
            enter-to-class="transform opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="transform opacity-100 scale-100"
            leave-to-class="transform opacity-0 scale-95"
        >
            <div
                v-show="open"
                class="absolute z-[100] mt-2 rounded-md shadow-lg bg-white"
                :class="[widthClass, alignmentClasses]"
                @click.stop
                style="min-width: max-content; transform-origin: top;"
            >
                <div
                    class="rounded-md ring-1 ring-black ring-opacity-5 max-h-60 overflow-y-auto"
                    :class="contentClasses"
                >
                    <slot name="content" />
                </div>
            </div>
        </transition>
    </div>
</template>

<style scoped>
.max-h-60 {
    max-height: 15rem;
}

.overflow-y-auto {
    scrollbar-width: thin;
    scrollbar-color: #CBD5E0 #EDF2F7;
}

.overflow-y-auto::-webkit-scrollbar {
    width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: #EDF2F7;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background-color: #CBD5E0;
    border-radius: 3px;
}
</style>





