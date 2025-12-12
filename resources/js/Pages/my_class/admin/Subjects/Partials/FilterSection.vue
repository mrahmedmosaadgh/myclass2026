<template>
    <div class="flex space-x-2">
        <TextInput
            :model-value="search"
            @update:model-value="$emit('update:search', $event)"
            type="search"
            placeholder="Search subjects..."
            class="w-64"
            @input="$emit('search')"
        />
        
        <SelectInput
            :model-value="school"
            @update:model-value="$emit('update:school', $event)"
            :options="schoolOptions"
            placeholder="Filter by School"
            class="w-48"
            @change="$emit('filter')"
        />
        
        <SelectInput
            :model-value="status"
            @update:model-value="$emit('update:status', $event)"
            :options="[
                { value: '', label: 'All Status' },
                { value: '1', label: 'Active' },
                { value: '0', label: 'Inactive' }
            ]"
            class="w-40"
            @change="$emit('filter')"
        />
    </div>
</template>

<script setup>
import TextInput from '@/Components/TextInput.vue';
import SelectInput from '@/Components/SelectInput.vue';

defineProps({
    search: String,
    school: [String, Number],
    status: String,
    schoolOptions: Array
});

defineEmits(['update:search', 'update:school', 'update:status', 'search', 'filter']);
</script>