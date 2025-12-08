<template>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th v-for="column in columns"
                        :key="column.key"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                    >
                        {{ column.label }}
                    </th>
                    <th v-if="hasActions" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="item in items" :key="item.id">
                    <td v-for="column in columns"
                        :key="column.key"
                        class="px-6 py-4 whitespace-nowrap"
                    >
                        <template v-if="column.formatter">
                            {{ column.formatter(getValue(item, column.key)) }}
                        </template>
                        <template v-else>
                            {{ getValue(item, column.key) }}
                        </template>
                    </td>
                    <td v-if="hasActions" class="px-6 py-4 whitespace-nowrap text-right text-sm">
                        <template v-for="(action, actionIndex) in actions" :key="actionIndex">
                            <button
                                v-if="shouldShowAction(action, item)"
                                @click="handleActionClick(action, item)"
                                :disabled="loading"
                                :class="[
                                    'ml-2 first:ml-0',
                                    action.class || 'text-indigo-600 hover:text-indigo-900',
                                    { 'opacity-50 cursor-not-allowed': loading }
                                ]"
                            >
                                {{ loading && action.type === 'delete' ? 'Deleting...' : action.label }}
                            </button>
                        </template>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';

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
    loading: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['action']);

const hasActions = computed(() => props.actions && props.actions.length > 0);

const handleActionClick = (action, item) => {
    emit('action', { type: action.type, item });
};

const shouldShowAction = (action, item) => {
    if (typeof action.show === 'function') {
        return action.show(item);
    }
    return true;
};

const getValue = (item, key) => {
    if (!item) return '';
    return key.split('.').reduce((obj, k) => obj?.[k], item) ?? '';
};
</script>


