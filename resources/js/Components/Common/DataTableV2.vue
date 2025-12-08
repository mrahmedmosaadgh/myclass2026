<template>
    <div class="w-full">
        <!-- Table Toolbar -->
        <div v-if="showToolbar" class="mb-2 no_print flex scale-75  justify-between items-center">
            <!-- Left Side -->
            <div class="flex items-center space-x-2 ">
                <!-- Search -->
                <div v-if="searchable" class="relative">
                    <input
                        type="text"
                        v-model="searchQuery"
                        placeholder="Search..."
                        class="px-2 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        @input="handleSearch"
                    >
                </div>



                <div class="flex items-center space-x-2 ">
                    <SecondaryButton
                        @click="printData"
                        class="flex items-center text-sm"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 " viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5 4v3H4a2 2 0 00-2 2v3a2 2 0 002 2h1v2a2 2 0 002 2h6a2 2 0 002-2v-2h1a2 2 0 002-2V9a2 2 0 00-2-2h-1V4a2 2 0 00-2-2H7a2 2 0 00-2 2zm8 0H7v3h6V4zm0 8H7v4h6v-4z" clip-rule="evenodd" />
                        </svg>

                    </SecondaryButton>
                    <SecondaryButton
                        @click="exportData"
                        class="flex items-center text-sm"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                        Export
                    </SecondaryButton>
                </div>
                <!-- Bulk Actions -->
                <div v-if="showBulkActions && selected.length > 0" class="flex items-center space-x-2">




                    <select
                        v-model="bulkAction"
                        class="border rounded-lg px-2 py-1"
                    >
                        <option value="">Bulk Actions</option>
                        <option v-for="action in bulkActions" :key="action.value" :value="action.value">
                            {{ action.label }}
                        </option>
                    </select>
                    <button
                        @click="handleBulkAction"
                        class="px-3 py-1 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                    >
                        Apply
                    </button>
                </div>
            </div>

            <!-- Right Side -->
            <div class="flex items-center space-x-2 ">
                <!-- Column Visibility Toggle -->
                <div v-if="columnToggle" class="relative">
                    <button
                        @click="showColumnSelector = !showColumnSelector"
                        class="px-3 py-1 border rounded-lg hover:bg-gray-100"

                    >
                        Columns
                    </button>
                    <div
                        v-if="showColumnSelector"
                        class="absolute right-0 mt-2 bg-white border rounded-lg shadow-lg z-10 p-2"
                    >
                        <div v-for="col in columns" :key="col.key" class="whitespace-nowrap">
                            <label class="flex items-center space-x-2 p-2 hover:bg-gray-100">
                                <input
                                    type="checkbox"
                                    v-model="visibleColumns"
                                    :value="col.key"
                                >
                                <span>{{ col.label }}</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Per Page Selector -->
                <div v-if="showPerPage" class="flex items-center space-x-2">
                    <span class="text-sm text-gray-600">Show:</span>
                    <select
                        v-model="itemsPerPage"
                        class="border rounded-lg px-6 py-1"
                        @change="$emit('update:perPage', itemsPerPage)"
                    >
                        <option v-for="n in perPageOptions" :key="n" :value="n">{{ n }}</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Main Table -->
        <div class="overflow-x-auto border rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <!-- Checkbox Column -->
                        <th v-if="selectable" class="w-12 px-6 py-3">
                            <input
                                type="checkbox"
                                :checked="isAllSelected"
                                @change="toggleSelectAll"
                                class="rounded border-gray-300"
                            >
                        </th>

                        <!-- Regular Columns -->
                        <th
                            v-for="column in visibleColumnsList"
                            :key="column.key"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            :class="{ 'cursor-pointer': column.sortable }"
                            @click="column.sortable && handleSort(column.key)"
                        >
                            <div class="flex items-center space-x-1">
                                <span>{{ column.label }}</span>
                                <span v-if="column.sortable" class="text-gray-400">
                                    <!--Add sort icons based on sort state-->
                                    <template v-if="sortKey === column.key">
                                        <span v-if="sortOrder === 'asc'">↑</span>
                                        <span v-else>↓</span>
                                    </template>
                                    <span v-else>↕</span>
                                </span>
                            </div>
                        </th>

                        <!-- Actions Column -->
                        <th v-if="hasActions" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>

                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="(item, index) in displayedItems" :key="getItemKey(item, index)">
                        <!-- Checkbox -->
                        <td v-if="selectable" class="w-12 px-6 py-4">
                            <input
                                type="checkbox"
                                v-model="selected"
                                :value="getItemKey(item)"
                                class="rounded border-gray-300"
                            >
                        </td>

                        <!-- Data Cells -->
                        <td
                            v-for="column in visibleColumnsList"
                            :key="column.key"
                            class="px-6 py-4 whitespace-nowrap"
                            :class="getCellClass(column, item)"
                        >
                            <template v-if="column.template">
                                <slot :name="column.key" :item="item" :value="getValue(item, column.key)">
                                    {{ getValue(item, column.key) }}
                                </slot>
                            </template>
                            <template v-else-if="column.formatter">
                                {{ column.formatter(getValue(item, column.key), item) }}
                            </template>
                            <template v-else>
                                {{ getValue(item, column.key) }}
                            </template>
                        </td>

                        <!-- Actions -->
                        <td v-if="hasActions" class="px-6 py-4 whitespace-nowrap text-right text-sm">
                            <template v-for="(action, actionIndex) in actions" :key="actionIndex">
                                <button
                                    v-if="shouldShowAction(action, item)"
                                    @click="handleAction(action, item)"
                                    :class="getActionClass(action)"
                                    class="ml-2 first:ml-0"
                                >
        <LucideIcon
            v-if="action.icon"
            :name="action.icon"
            class="w-4 h-4"
            :class="action.class"
        />

                                    <!-- <span v-if="action.icon" class="mr-1">{{ action.icon }}</span> -->
                                    {{ action.label }}
                                    <!-- {{ action.icon  }} -->
                                </button>
                            </template>
                        </td>
                    </tr>

                    <!-- Empty State -->
                    <tr v-if="displayedItems.length === 0">
                        <td :colspan="totalColumns" class="px-6 py-8 text-center text-gray-500">
                            {{ emptyMessage }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Footer -->
        <div v-if="showFooter" class="mt-4 flex justify-between items-center">
            <!-- Selection Info -->
            <div v-if="selectable && selected.length > 0" class="text-sm text-gray-600">
                {{ selected.length }} item(s) selected
            </div>

            <!-- Pagination -->
            <div v-if="showPagination" class="flex justify-end">
                <slot name="pagination">
                    <div class="flex items-center space-x-2">
                        <button
                            @click="changePage(currentPage - 1)"
                            :disabled="currentPage === 1"
                            class="px-3 py-1 border rounded-lg disabled:opacity-50"
                        >
                            Previous
                        </button>
                        <span class="text-sm text-gray-600">
                            Page {{ currentPage }} of {{ totalPages }}
                        </span>
                        <button
                            @click="changePage(currentPage + 1)"
                            :disabled="currentPage === totalPages"
                            class="px-3 py-1 border rounded-lg disabled:opacity-50"
                        >
                            Next
                        </button>
                    </div>
                </slot>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import LucideIcon from '@/Components/Common/LucideIcon.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { exportToExcel } from '@/Utils/exportHelper';

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
    selectable: {
        type: Boolean,
        default: false
    },
    searchable: {
        type: Boolean,
        default: false
    },
    sortable: {
        type: Boolean,
        default: false
    },
    columnToggle: {
        type: Boolean,
        default: false
    },
    showToolbar: {
        type: Boolean,
        default: true
    },
    showFooter: {
        type: Boolean,
        default: true
    },
    showPagination: {
        type: Boolean,
        default: true
    },
    showPerPage: {
        type: Boolean,
        default: true
    },
    perPage: {
        type: Number,
        default: 10
    },
    currentPage: {
        type: Number,
        default: 1
    },
    emptyMessage: {
        type: String,
        default: 'No items found'
    },
    bulkActions: {
        type: Array,
        default: () => []
    },
    totalPages: {
        type: Number,
        default: 1
    },
    exportFileName: {
        type: String,
        default: 'export'
    },
    exportSheetName: {
        type: String,
        default: 'Sheet1'
    },
    printSettings: {
        type: Object,
        default: () => ({
            title: 'Data Report',
            showTimestamp: true,
            timestampFormat: 'long', // 'long' | 'short' | 'date' | 'time'
            orientation: 'portrait', // 'portrait' | 'landscape'
            paperSize: 'a4', // 'a4' | 'letter' | 'legal'
            fontSize: '12pt',
            headerBgColor: '#f8f9fa',
            borderColor: '#ddd',
            filename: 'report',
            logo: '', // URL to logo image
            footerText: '', // Custom footer text
            columnSettings: {} // Per-column print settings
        })
    }
});

const emit = defineEmits([
    'action',
    'sort',
    'search',
    'update:perPage',
    'page-change',
    'selection-change',
    'bulk-action'
]);



const exportData = () => {
    exportToExcel({
        items: displayedItems.value,
        columns: props.columns,
        fileName: props.exportFileName,
        sheetName: props.exportSheetName
    });
};
const printData = (customSettings = {}) => {
    // Merge default settings with props and custom settings
    const settings = {
        ...props.printSettings,
        ...customSettings
    };

    const printWindow = window.open('', '_blank');
    const table = document.createElement('table');

    // Add CSS styles with settings
    const style = document.createElement('style');
    style.textContent = `
        @page {
            size: ${settings.paperSize} ${settings.orientation};
            margin: 0.5cm;
        }
        body {
            font-size: ${settings.fontSize};
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid ${settings.borderColor};
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: ${settings.headerBgColor};
        }
        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .report-title {
            font-size: 1.5em;
            font-weight: bold;
            text-align: center;
        }
        .report-timestamp {
            text-align: right;
            font-size: 0.9em;
            color: #666;
        }
        .report-footer {
            text-align: center;
            margin-top: 20px;
            font-size: 0.9em;
            color: #666;
        }
        @media print {
            .no-print { display: none; }
        }
    `;

    // Create header section
    const header = document.createElement('div');
    header.className = 'report-header';

    // Add logo if provided
    if (settings.logo) {
        const logo = document.createElement('img');
        logo.src = settings.logo;
        logo.style.height = '50px';
        header.appendChild(logo);
    }

    // Add title
    const title = document.createElement('h1');
    title.className = 'report-title';
    title.textContent = settings.title;
    header.appendChild(title);

    // Add timestamp if enabled
    if (settings.showTimestamp) {
        const timestamp = document.createElement('div');
        timestamp.className = 'report-timestamp';
        const date = new Date();
        const timeFormat = {
            long: date.toLocaleString(),
            short: date.toLocaleDateString(),
            date: date.toLocaleDateString(),
            time: date.toLocaleTimeString()
        };
        timestamp.textContent = `Generated: ${timeFormat[settings.timestampFormat]}`;
        header.appendChild(timestamp);
    }

    // Create table with columns based on settings
    const thead = document.createElement('thead');
    const headerRow = document.createElement('tr');
    visibleColumnsList.value.forEach(column => {
        const columnSettings = settings.columnSettings[column.key] || {};
        if (columnSettings.exclude) return;

        const th = document.createElement('th');
        th.textContent = column.label;
        if (columnSettings.width) th.style.width = columnSettings.width;
        headerRow.appendChild(th);
    });
    thead.appendChild(headerRow);
    table.appendChild(thead);

    // Create body with formatted data
    const tbody = document.createElement('tbody');
    displayedItems.value.forEach(item => {
        const row = document.createElement('tr');
        visibleColumnsList.value.forEach(column => {
            const columnSettings = settings.columnSettings[column.key] || {};
            if (columnSettings.exclude) return;

            const td = document.createElement('td');
            const value = getValue(item, column.key);
            td.textContent = columnSettings.formatter ?
                columnSettings.formatter(value, item) :
                value;
            row.appendChild(td);
        });
        tbody.appendChild(row);
    });
    table.appendChild(tbody);

    // Add footer if provided
    if (settings.footerText) {
        const footer = document.createElement('div');
        footer.className = 'report-footer';
        footer.textContent = settings.footerText;
        printWindow.document.body.appendChild(footer);
    }

    // Set up the print window
    printWindow.document.title = settings.filename;
    printWindow.document.head.appendChild(style);
    printWindow.document.body.appendChild(header);
    printWindow.document.body.appendChild(table);

    // Print
    printWindow.document.close();
    printWindow.focus();
    setTimeout(() => {
        printWindow.print();
        printWindow.close();
    }, 250);
};
// State
const searchQuery = ref('');
const sortKey = ref('');
const sortOrder = ref('asc');
const selected = ref([]);
const showColumnSelector = ref(false);
const visibleColumns = ref(props.columns.map(col => col.key));
const itemsPerPage = ref(props.perPage);
const bulkAction = ref('');

// Computed
const hasActions = computed(() => props.actions.length > 0);
const showBulkActions = computed(() => props.bulkActions.length > 0);
const perPageOptions = [10, 25, 50, 100];

const visibleColumnsList = computed(() => {
    return props.columns.filter(col => visibleColumns.value.includes(col.key));
});

const totalColumns = computed(() => {
    let count = visibleColumnsList.value.length;
    if (props.selectable) count++;
    if (hasActions.value) count++;
    return count;
});

const displayedItems = computed(() => {
    let items = [...props.items];

    // Search
    if (searchQuery.value) {
        items = items.filter(item => {
            return visibleColumnsList.value.some(column => {
                const value = getValue(item, column.key);
                return String(value).toLowerCase().includes(searchQuery.value.toLowerCase());
            });
        });
    }

    // Sort
    if (sortKey.value) {
        items.sort((a, b) => {
            const aVal = getValue(a, sortKey.value);
            const bVal = getValue(b, sortKey.value);
            if (sortOrder.value === 'asc') {
                return aVal > bVal ? 1 : -1;
            }
            return aVal < bVal ? 1 : -1;
        });
    }

    return items;
});

const isAllSelected = computed(() => {
    return props.items.length > 0 && selected.value.length === props.items.length;
});

const totalPages = computed(() => {
    return Math.ceil(displayedItems.value.length / itemsPerPage.value);
});

// Methods
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

const handleAction = (action, item) => {
    console.log('action in datatablev2', action);
    emit('action', { type: action.type, action: action, item });
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

const toggleSelectAll = () => {
    if (isAllSelected.value) {
        selected.value = [];
    } else {
        selected.value = props.items.map(item => getItemKey(item));
    }
};

const handleBulkAction = () => {
    if (bulkAction.value) {
        emit('bulk-action', {
            action: bulkAction.value,
            selected: selected.value
        });
        bulkAction.value = '';
    }
};

const changePage = (page) => {
    emit('page-change', page);
};

// Watchers
watch(selected, (newVal) => {
    emit('selection-change', newVal);
});
</script>














