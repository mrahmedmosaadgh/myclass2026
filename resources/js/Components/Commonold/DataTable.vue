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
                        <button
                            @click="$emit('edit', item)"
                            class="text-indigo-600 hover:text-indigo-900 mr-4"
                        >
                            Edit
                        </button>
                        <button
                            @click="$emit('delete', item)"
                            class="text-red-600 hover:text-red-900"
                        >
                            Delete
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script setup>
import { defineProps, defineEmits, computed } from 'vue';

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
    }
});

defineEmits(['edit', 'delete']);

const startingNumber = computed(() => {
    return ((props.currentPage - 1) * props.perPage) + 1;
});

const getValue = (item, key) => {
    if (!item) return '';
    return key.split('.').reduce((obj, k) => obj?.[k], item);
};
</script>



