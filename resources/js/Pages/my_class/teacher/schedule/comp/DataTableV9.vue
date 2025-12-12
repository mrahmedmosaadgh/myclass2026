<template>
    <div class="space-y-4">
        <!-- Header Actions -->
        <div class="flex justify-between items-center mb-6 bg-white p-4 rounded-lg shadow-sm">
            <!-- Left side actions -->
            <div class="flex space-x-3">
                <PrimaryButton
                    @click="$emit('add-new')"
                    class="flex items-center transition-colors duration-150 bg-transparent"
                    title="Add New Documentation"
                >
                  <LucideIcon
                        name="plus"
                        class="w-6 h-6 text-blue-600"
                    />
                </PrimaryButton>

                <!-- Using Dropdown7 Component -->
                <Dropdown9
                    align="left"
                    width="48"
                    :auto-hide="true"
                >
                    <template #trigger>
                        <SecondaryButton
                            class="flex items-center"
                            title="More Actions"
                        >
                            <LucideIcon
                                name="more-vertical"
                                class="w-5 h-5"
                            />
                        </SecondaryButton>
                    </template>

                    <template #content>
                        <div class="py-1">
                            <!-- Import Option -->
                            <ImportExcel
                                @imported="$emit('import')"
                                :validate-url="baseUrl + '/validate-import'"
                                :import-url="baseUrl + '/import'"
                                :columns="importColumns"
                                button-text="Import Documentation"
                                preview-title="Preview Data"
                            >
                                <template #default="{ openModal }">
                                    <button
                                        @click="openModal"
                                        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center"
                                    >
                                        <LucideIcon
                                            name="upload"
                                            class="w-4 h-4 mr-3"
                                        />
                                        Import
                                    </button>
                                </template>
                            </ImportExcel>

                            <!-- Export Option -->
                            <button
                                @click="$emit('export')"
                                class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center"
                            >
                                <LucideIcon
                                    name="download"
                                    class="w-4 h-4 mr-3"
                                />
                                Export
                            </button>

                            <!-- Print Option -->
                            <button
                                @click="$emit('print')"
                                class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center"
                            >
                                <LucideIcon
                                    name="printer"
                                    class="w-4 h-4 mr-3"
                                />
                                Print
                            </button>
                        </div>
                    </template>
                </Dropdown9>
            </div>

            <!-- Search Section -->
            <div class="flex items-center space-x-2">
                <div class="relative">
                    <TextInput
                        :model-value="searchQuery"
                        @update:model-value="$emit('update:search-query', $event)"
                        type="text"
                        placeholder="Search..."
                        class="w-64 pl-10"
                        @keyup.enter="$emit('search')"
                    />
                    <LucideIcon
                        name="search"
                        class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 transform -translate-y-1/2"
                    />
                </div>

                <PrimaryButton
                    @click="$emit('search')"
                    :disabled="loading"
                      class="flex items-center bg-transparent"
                    title="Search"
                >
                    <LucideIcon
                        name="search"
                        class="w-5 h-5 text-blue-500"
                        :class="{ 'animate-spin': loading }"
                    />


                    <span v-if="loading" class="sr-only">Searching...</span>
                </PrimaryButton>

                <!-- 🔎🔎 Search Section -->


                        <button
                                                v-if="isFiltered"
                    @click="$emit('clear-search')"
                            class="text-gray-500 hover:text-red-500"
                        >
                            ✕
                        </button>
            </div>
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

        <!-- Pagination -->
        <div v-if="pagination?.length > 0" class="mt-4">
            <Pagination9
                :links="pagination"
                :loading="loading"
                @page-changed="(url) => $emit('page-changed', url)"
            />
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import LucideIcon from '@/Components/Common/LucideIcon.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import ImportExcel from '@/Components/Common/ImportExcel.vue';
import Pagination9 from './Pagination9.vue';
import Dropdown9 from './Dropdown9.vue';

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
    },
    loading: {
        type: Boolean,
        default: false
    },
    pagination: {
        type: Array,
        default: () => []
    },
    baseUrl: {
        type: String,
        required: true
    },
    importColumns: {
        type: Array,
        required: true
    },
    isFiltered: {
        type: Boolean,
        default: false
    },
    searchQuery: {
        type: String,
        default: ''
    }
});

const emit = defineEmits([
    'sort',
    'search',
    'action',
    'page-changed',
    'add-new',
    'import',
    'export',
    'clear-search',
    'update:search-query',
    'print'
]);

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

<style scoped>
.dropdown-enter-active,
.dropdown-leave-active {
    transition: opacity 0.2s, transform 0.2s;
}

.dropdown-enter-from,
.dropdown-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}
</style>







