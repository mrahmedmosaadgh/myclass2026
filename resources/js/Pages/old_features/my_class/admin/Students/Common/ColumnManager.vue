<template>
<div class="p-2">
 
    <Modal :show="show" @close="$emit('close')">
        <div class="p-6">
            <h3 class="text-lg font-semibold mb-4">Manage Import Columns</h3>

            <draggable
                v-model="localColumns"
                class="space-y-2"
                handle=".handle"
                :item-key="'key'"
                @end="updateColumns"
            >
                <template #item="{ element }">
                    <div class="flex items-center space-x-4 p-2 bg-gray-50 rounded border border-gray-200">
                        <div class="handle cursor-move px-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </div>

                        <div class="flex-grow">
                            <input
                                type="text"
                                v-model="element.label"
                                class="w-full px-2 py-1 border rounded"
                                :placeholder="element.key"
                                @change="updateColumns"
                            />
                        </div>

                        <div class="flex items-center space-x-4">
                            <!-- ID Field Radio -->
                            <label class="flex items-center space-x-2">
                                <input
                                    type="radio"
                                    :value="element.key"
                                    v-model="idField"
                                    @change="setIdField(element.key)"
                                    name="id_field"
                                />
                                <span class="text-sm">ID Field</span>
                            </label>

                            <!-- Required Checkbox -->
                            <label class="flex items-center space-x-2">
                                <input
                                    type="checkbox"
                                    v-model="element.required"
                                    @change="updateColumns"
                                />
                                <span class="text-sm">Required</span>
                            </label>

                            <!-- Visibility Toggle -->
                            <button
                                @click="toggleVisibility(element)"
                                class="p-1 rounded hover:bg-gray-200"
                                :title="element.hidden ? 'Show column' : 'Hide column'"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg"
                                     class="h-5 w-5"
                                     :class="element.hidden ? 'text-gray-400' : 'text-gray-600'"
                                     fill="none"
                                     viewBox="0 0 24 24"
                                     stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </template>
            </draggable>

            <div class="mt-4 flex justify-end space-x-2">
                <button
                    @click="resetColumns"
                    class="px-4 py-2 text-sm text-gray-600 border rounded hover:bg-gray-50"
                >
                    Reset
                </button>
                <button
                    @click="saveColumns"
                    class="px-4 py-2 text-sm text-white bg-blue-600 rounded hover:bg-blue-700"
                >
                    Save Changes
                </button>
            </div>
        </div>
    </Modal>
</div>

</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import draggable from 'vuedraggable';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    columns: {
        type: Array,
        required: true
    }
});

const emit = defineEmits(['update:columns', 'close']);

const localColumns = ref([...props.columns]);
const idField = ref('');

// Initialize idField on mount
onMounted(() => {
    const currentIdField = localColumns.value.find(col => col.is_id === true);
    if (currentIdField) {
        idField.value = currentIdField.key;
    }
});

// Watch for external changes
watch(() => props.columns, (newColumns) => {
    if (JSON.stringify(newColumns) !== JSON.stringify(localColumns.value)) {
        localColumns.value = [...newColumns];
        const currentIdField = localColumns.value.find(col => col.is_id === true);
        if (currentIdField) {
            idField.value = currentIdField.key;
        }
    }
}, { deep: true });

const setIdField = (key) => {
    // Remove is_id from all columns
    localColumns.value.forEach(col => {
        col.is_id = col.key === key;
    });
    updateColumns();
};

const updateColumns = () => {
    emit('update:columns', [...localColumns.value]);
};

const toggleVisibility = (column) => {
    column.hidden = !column.hidden;
    updateColumns();
};

const resetColumns = () => {
    localColumns.value = [...props.columns];
    const currentIdField = localColumns.value.find(col => col.is_id === true);
    if (currentIdField) {
        idField.value = currentIdField.key;
    }
    updateColumns();
};

const saveColumns = () => {
    console.log('Updated columns:', localColumns.value);
    updateColumns();
    emit('close');
};
</script>


