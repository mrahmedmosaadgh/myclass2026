<template>
    <div class="row q-col-gutter-md items-center">
        <div class="col-12 col-md-5">
            <q-input
                :model-value="search"
                @update:model-value="emit('update:search', $event)"
                outlined
                dense
                debounce="300"
                placeholder="Search by name or email..."
                clearable
            >
                <template v-slot:prepend>
                    <q-icon name="search" />
                </template>
            </q-input>
        </div>
        <div class="col-12 col-md-3">
            <q-select
                :model-value="school"
                @update:model-value="emit('update:school', $event)"
                :options="schools"
                option-value="id"
                option-label="name"
                label="Filter by School"
                outlined
                dense
                clearable
                emit-value
                map-options
            />
        </div>
        <div class="col-12 col-md-3">
             <q-select
                :model-value="role"
                @update:model-value="emit('update:role', $event)"
                :options="roles"
                label="Filter by Role"
                outlined
                dense
                clearable
            />
        </div>
        <div class="col-12 col-md-1">
            <q-btn flat round icon="refresh" @click="emit('refresh')" >
                <q-tooltip>Refresh Data</q-tooltip>
            </q-btn>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    search: String,
    school: [Object, String, Number],
    role: String,
    schools: Array,
    roles: Array,
});

const emit = defineEmits(['update:search', 'update:school', 'update:role', 'refresh']);
</script>