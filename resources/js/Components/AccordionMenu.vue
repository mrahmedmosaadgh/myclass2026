<script setup>
import { watch } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const props = defineProps({
    menuGroups: {
        type: Array,
        required: true
    },
    openSections: {
        type: Set,
        required: true
    }
});

const emit = defineEmits(['update:openSections']);
const page = usePage();

const isActive = (routeName) => {
    const url = new URL(route(routeName));
    const currentPath = page.url;
    return url.pathname === currentPath;
};

const toggleSection = (index) => {
    const newOpenSections = new Set(props.openSections);
    if (newOpenSections.has(index)) {
        newOpenSections.delete(index);
    } else {
        newOpenSections.add(index);
    }
    emit('update:openSections', newOpenSections);
};

watch(() => page.url, () => {
    props.menuGroups.forEach((group, index) => {
        if (group.items.some(item => isActive(item.route))) {
            const newOpenSections = new Set(props.openSections);
            newOpenSections.add(index);
            emit('update:openSections', newOpenSections);
        }
    });
}, { immediate: true });
</script>

<template>
    <div class="space-y-1 py-2">
        <div v-for="(group, index) in menuGroups" :key="index"
             class="overflow-hidden border-b border-gray-100 last:border-b-0">
            <button
                @click="toggleSection(index)"
                class="w-full px-4 py-3 text-left flex justify-between items-center
                       transition-colors duration-200 ease-in-out
                       hover:bg-gray-50 focus:outline-none focus:bg-gray-50"
                :class="{
                    'bg-gray-50': openSections.has(index)
                }"
            >
                <span class="text-sm font-semibold text-gray-800 flex items-center space-x-2">
                    {{ group.title }}
                </span>
                <svg
                    class="h-5 w-5 text-gray-400 transform transition-transform duration-300 ease-in-out"
                    :class="{ 'rotate-180': openSections.has(index) }"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                >
                    <path fill-rule="evenodd"
                          d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                          clip-rule="evenodd" />
                </svg>
            </button>
            <transition
                enter-active-class="transition-all duration-300 ease-in-out"
                enter-from-class="max-h-0 opacity-0"
                enter-to-class="max-h-[500px] opacity-100"
                leave-active-class="transition-all duration-300 ease-in-out"
                leave-from-class="max-h-[500px] opacity-100"
                leave-to-class="max-h-0 opacity-0"
            >
                <div v-show="openSections.has(index)"
                     class="overflow-hidden bg-gray-50/50">
                    <div class="px-4 py-2 space-y-1">
                        <Link
                            v-for="item in group.items.filter(item =>
                                !item.permission ||
                                page.props.auth.user.permissions.includes(item.permission)
                            )"
                            :key="item.name"
                            :href="route(item.route)"
                            class="block px-3 py-2 text-sm rounded-lg transition-all duration-200 ease-in-out"
                            :class="{
                                'text-gray-600 hover:text-gray-900 hover:bg-gray-100/70': !isActive(item.route),
                                'text-blue-700 bg-blue-50 hover:bg-blue-100 font-medium': isActive(item.route)
                            }"
                        >
                            {{ item.name }}
                        </Link>
                    </div>
                </div>
            </transition>
        </div>
    </div>
</template>

<style scoped>
.overflow-hidden {
    overflow: hidden;
}
</style>



