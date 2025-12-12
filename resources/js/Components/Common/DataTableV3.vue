<template>
    <div>
        <!-- Search -->
        <div v-if="searchable" class="mb-4">
            <input
                type="text"
                v-model="searchQuery"
                @input="handleSearch"
                placeholder="Search..."
                class="border rounded px-3 py-2 w-full md:w-64"
            />
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th
                            v-for="(column, index) in columns"
                            :key="index"
                            @click="column.sortable ? handleSort(column.key) : null"
                            :class="[
                                'px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider',
                                column.sortable ? 'cursor-pointer hover:text-gray-700' : ''
                            ]"
                        >
                            {{ column.label }}
                            <span v-if="column.sortable && sortKey === column.key">
                                {{ sortOrder === 'asc' ? '↑' : '↓' }}
                            </span>
                        </th>
                        <th v-if="hasActions" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="(item, index) in displayedItems" :key="getItemKey(item, index)">
                        <td
                            v-for="(column, colIndex) in columns"
                            :key="colIndex"
                            class="px-6 py-4 whitespace-nowrap"
                            :class="getCellClass(column, item)"
                        >
                            {{ getValue(item, column.key) }}
                        </td>
                        <td v-if="hasActions" class="px-6 py-4 whitespace-nowrap text-right text-sm">
                            <template v-for="(action, actionIndex) in actions" :key="actionIndex">
                                <button
                                    v-if="shouldShowAction(action, item)"
                                    @click="handleActionClick(action, item)"
                                    :class="getActionClass(action)"
                                    class="ml-2 first:ml-0"
                                >
                                    <LucideIcon
                                        v-if="action.icon"
                                        :name="action.icon"
                                        class="w-4 h-4 inline-block"
                                        :class="action.iconClass"
                                    />
                                    {{ action.label }}
                                </button>
                            </template>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
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
    actions: {
        type: Array,
        default: () => []
    },
    searchable: {
        type: Boolean,
        default: false
    },
    perPage: {
        type: Number,
        default: 10
    }
});

const emit = defineEmits(['sort', 'search', 'action']);

const searchQuery = ref('');
const sortKey = ref('');
const sortOrder = ref('asc');

const hasActions = computed(() => props.actions.length > 0);

const displayedItems = computed(() => {
    return props.items;
});

const getValue = (item, key) => {
    return key.split('.').reduce((obj, k) => obj?.[k], item) ?? '';
};

const getItemKey = (item, fallbackIndex) => {
    return item.id ?? fallbackIndex;
};

const getCellClass = (column, item) => {
    if (typeof column.class === 'function') {
        return column.class(getValue(item, column.key), item);
    }
    return column.class;
};

const handleSort = (key) => {
    if (sortKey.value === key) {
        sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortKey.value = key;
        sortOrder.value = 'asc';
    }
    emit('sort', { key, order: sortOrder.value });
};

const handleSearch = () => {
    emit('search', searchQuery.value);
};

const handleActionClick = (action, item) => {
    if (typeof action.action === 'function') {
        action.action(item);
    } else {
        emit('action', { type: action.type, item });
    }
};

const shouldShowAction = (action, item) => {
    if (typeof action.show === 'function') {
        return action.show(item);
    }
    return true;
};

const getActionClass = (action) => {
    return action.class || 'text-blue-600 hover:text-blue-800';
};
</script>
