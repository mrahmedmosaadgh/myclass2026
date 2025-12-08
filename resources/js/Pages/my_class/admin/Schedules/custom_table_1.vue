<template>
    <div class="overflow-x-auto shadow-lg rounded-lg">
        <table class="min-w-full divide-y divide-gray-200 table-fixed">
            <thead class="bg-gray-50">
                <tr>
                    <th v-for="(column, index) in columns"
                        :key="index"
                        :class="[
                            'px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider',
                            { 'sticky left-0 z-10 bg-gray-50': column.sticky },
                            { 'hover-column': hoveredCell.col === index },
                            { 'sticky-header': column.sticky }
                        ]">
                        {{ column.label }}
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="(row, rowIndex) in data"
                    :key="rowIndex"
                    :class="[
                        'transition-colors duration-200',
                        { 'bg-gray-50': rowIndex % 2 === 0 },
                        { 'hover-row': hoveredCell.row === rowIndex }
                    ]">

                    <td v-for="(column, colIndex) in columns"
                        :key="colIndex"
                        :class="[
                            'px-6 py-4 whitespace-nowrap text-sm relative',
                            { 'sticky left-0': column.sticky },
                            { 'font-medium text-gray-900': column.sticky },
                            { 'text-gray-500': !column.sticky },
                            { 'hover-column': hoveredCell.col === colIndex },
                            { 'hover-cell': hoveredCell.row === rowIndex && hoveredCell.col === colIndex },
                            { 'sticky-cell': column.sticky }
                        ]"
                        @mouseenter="setHoveredCell(rowIndex, colIndex)"
                        @mouseleave="clearHoveredCell()">

                        <slot :name="`cell-${column.key}`" :row="row" :value="row[column.key]">
                            {{ row[column.key] }}
                        </slot>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
    columns: {
        type: Array,
        required: true,
        // Example: [{ key: 'name', label: 'Name', sticky: true }, { key: 'age', label: 'Age' }]
    },
    data: {
        type: Array,
        required: true,
    }
});

const hoveredCell = ref({ row: null, col: null });

const setHoveredCell = (row, col) => {
    hoveredCell.value = { row, col };
};

const clearHoveredCell = () => {
    hoveredCell.value = { row: null, col: null };
};
</script>

<style scoped>
.table-fixed {
    table-layout: fixed;
}

/* Sticky positioning */
.sticky {
    position: sticky;
    background-color: inherit;
    z-index: 10;
}

.sticky-header {
    position: sticky;
    top: 0;
    z-index: 20;
    background-color: rgb(249 250 251);
}

.sticky-cell {
    position: sticky;
    left: 0;
    z-index: 15;
    background-color: inherit;
}

/* Base transitions */
td, th {
    position: relative;
    transition: all 0.2s ease;
}

/* Row hover effect */
.hover-row {
    @apply bg-gradient-to-r from-purple-50/80 to-pink-50/80;
    box-shadow: inset 0 0 12px rgba(219, 39, 119, 0.05);
}

/* Ensure sticky cell shows hover state */
.sticky-cell.hover-row {
    @apply bg-gradient-to-r from-purple-50 to-purple-50/90;
}

/* Column hover effect */
.hover-column {
    @apply bg-gradient-to-b from-indigo-50/80 to-blue-50/80;
    box-shadow: inset 0 0 12px rgba(99, 102, 241, 0.05);
}

/* Cell hover effect */
.hover-cell {
    @apply bg-gradient-to-br from-cyan-50 to-blue-50;
    box-shadow:
        inset 0 0 15px rgba(6, 182, 212, 0.1),
        0 0 10px rgba(6, 182, 212, 0.1);
    z-index: 30;
}

/* Table styling */
table {
    @apply bg-gradient-to-br from-white to-gray-50;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

/* Ensure sticky elements maintain proper background during hover */
.sticky-cell.hover-column {
    @apply bg-gradient-to-b from-indigo-50 to-indigo-50/90;
}

.sticky-header.hover-column {
    @apply bg-gradient-to-r from-indigo-50 to-indigo-50/95;
}
</style>
