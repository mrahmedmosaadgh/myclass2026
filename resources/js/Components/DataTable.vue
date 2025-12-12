<template>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        #
                    </th>
                    <th v-for="column in columns"
                        :key="column.key"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                    >
                        {{ column.label }}
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="(item, index) in items" :key="item.id">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ startingNumber + index }}
                    </td>
                    <td v-for="column in columns"
                        :key="column.key"
                        class="px-6 py-4 whitespace-nowrap"
                        :class="column.class ? column.class(getValue(item, column.key)) : ''"
                    >
                        <template v-if="column.formatter">
                            {{ column.formatter(getValue(item, column.key), item) }}
                        </template>
                        <template v-else>
                            {{ getValue(item, column.key) }}
                        </template>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex justify-end gap-2">
                            <button
                                v-for="action in actions"
                                :key="action.type"
                                @click="$emit('action', { type: action.type, item })"
                                :class="action.class"
                                :title="action.label"
                            >
                                <LucideIcon
                                    :name="action.icon"
                                    class="w-4 h-4"
                                />
                                <span class="hidden md:inline">{{ action.label }}</span>
                            </button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script setup>
import { defineProps, defineEmits, computed } from 'vue';
import LucideIcon from '@/Components/Common/LucideIcon.vue';

const props = defineProps({
    items: {
        type: Array,
        required: true
    },
    columns: {
        type: Array,
        required: true
    },
    currentPage: {
        type: Number,
        default: 1
    },
    perPage: {
        type: Number,
        default: 40
    },
    actions: {
        type: Array,
        default: () => []
    }
});

defineEmits(['action']);

const startingNumber = computed(() => {
    return ((props.currentPage - 1) * props.perPage) + 1;
});

const getValue = (item, key) => {
    if (!item) return '';
    return key.split('.').reduce((obj, k) => obj?.[k], item);
};
</script>





