<template>
    <q-dialog :model-value="show" @update:model-value="$emit('close')" persistent>
        <q-card style="width: 500px; max-width: 80vw;">
            <q-card-section>
                <div class="text-h6">Manage Roles for {{ user?.name }}</div>
            </q-card-section>

            <q-card-section class="q-pt-none">
                <p>Select the roles to assign to this user.</p>
                <div class="q-gutter-sm">
                    <q-checkbox
                        v-for="role in allRoles"
                        :key="role"
                        v-model="selectedRoles"
                        :val="role"
                        :label="role"
                        color="primary"
                    />
                </div>
            </q-card-section>

            <q-card-actions align="right">
                <q-btn flat label="Cancel" color="primary" @click="$emit('close')" />
                <q-btn
                    label="Save"
                    color="primary"
                    @click="saveRoles"
                    :loading="saving"
                />
            </q-card-actions>
        </q-card>
    </q-dialog>
</template>

<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    show: Boolean,
    user: Object,
    allRoles: Array,
});

const emit = defineEmits(['close', 'save']);

const selectedRoles = ref([]);
const saving = ref(false);

watch(() => props.user, (newUser) => {
    if (newUser) {
        selectedRoles.value = newUser.roles.map(r => r.name);
    } else {
        selectedRoles.value = [];
    }
});

const saveRoles = () => {
    saving.value = true;
    emit('save', {
        userId: props.user.id,
        roles: selectedRoles.value
    });
    // The parent component will set saving to false on completion/error
    // For now, we'll just reset it after a short delay
    setTimeout(() => saving.value = false, 2000);
};
</script>