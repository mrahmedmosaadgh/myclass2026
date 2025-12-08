<template>
    <q-card class="user-card full-height" flat bordered>
        <q-item :class="`bg-${color}-1`">
            <q-item-section avatar>
                <q-avatar :color="color" text-color="white" :icon="icon" />
            </q-item-section>
            <q-item-section>
                <q-item-label class="text-bold">{{ user.name }}</q-item-label>
                <q-item-label caption>{{ user.email }}</q-item-label>
            </q-item-section>
        </q-item>

        <q-separator />

        <q-card-section>
            <div v-if="type === 'teacher'">
                <q-item-label header class="q-pa-none">Subjects</q-item-label>
                <div class="q-gutter-xs">
                    <q-chip v-for="subject in user.subjects" :key="subject.id" dense size="sm" color="blue-grey-2">{{ subject.name }}</q-chip>
                </div>
            </div>
            <div v-if="type === 'student'">
                <q-item-label header class="q-pa-none">Classroom</q-item-label>
                <p>{{ user.classroom_name || 'N/A' }}</p>
            </div>
            <div v-if="type === 'parent'">
                <q-item-label header class="q-pa-none">Children's Classrooms</q-item-label>
                <p>{{ user.children_classrooms || 'N/A' }}</p>
            </div>

            <q-item-label header class="q-pa-none q-mt-sm">Roles</q-item-label>
            <div class="q-gutter-xs">
                <q-chip v-for="role in user.roles" :key="role.id" dense size="sm" color="secondary" text-color="white" :label="role.name" />
            </div>
        </q-card-section>

        <q-card-actions align="right" class="q-pa-sm">
            <q-btn flat dense round icon="visibility" color="grey-7" @click="$emit('view-details', user)">
                <q-tooltip>View Details</q-tooltip>
            </q-btn>
            <q-btn flat dense round icon="manage_accounts" color="primary" @click="$emit('manage-roles', user)">
                <q-tooltip>Manage Roles</q-tooltip>
            </q-btn>
        </q-card-actions>
    </q-card>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
    type: {
        type: String,
        required: true,
        validator: (value) => ['teacher', 'student', 'parent', 'other'].includes(value),
    },
});

defineEmits(['manage-roles', 'view-details']);

const color = computed(() => {
    switch (props.type) {
        case 'teacher': return 'green';
        case 'student': return 'blue';
        case 'parent': return 'orange';
        default: return 'grey';
    }
});

const icon = computed(() => {
    switch (props.type) {
        case 'teacher': return 'school';
        case 'student': return 'face';
        case 'parent': return 'supervisor_account';
        default: return 'person';
    }
});
</script>

<style scoped>
.user-card {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
</style>