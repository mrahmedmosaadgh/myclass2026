<template>
    <AppLayout title="New Conversation">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                New Conversation
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <q-card flat bordered>
                    <q-card-section>
                        <div class="text-h5 q-mb-md">Start a New Conversation</div>

                        <q-form @submit="handleSubmit" class="q-gutter-md">
                            <div>
                                <q-toggle
                                    v-model="isGroup"
                                    label="Create a group chat"
                                    color="primary"
                                />
                            </div>

                            <div v-if="isGroup">
                                <q-input
                                    v-model="name"
                                    label="Group Name"
                                    :rules="[val => !!val || 'Group name is required']"
                                />
                            </div>

                            <div>
                                <q-select
                                    v-model="selectedUsers"
                                    :options="users"
                                    option-label="name"
                                    :option-value="opt => opt.id"
                                    :label="isGroup ? 'Add Participants' : 'Select User'"
                                    :multiple="isGroup"
                                    use-chips
                                    :rules="[val => val.length > 0 || 'Please select at least one user']"
                                >
                                    <template v-slot:option="scope">
                                        <q-item v-bind="scope.itemProps">
                                            <q-item-section avatar>
                                                <q-avatar :color="getAvatarColor(scope.opt.name)">
                                                    {{ getInitials(scope.opt.name) }}
                                                </q-avatar>
                                            </q-item-section>
                                            <q-item-section>
                                                <q-item-label>{{ scope.opt.name }}</q-item-label>
                                            </q-item-section>
                                        </q-item>
                                    </template>
                                </q-select>
                            </div>

                            <div class="flex justify-end q-mt-lg">
                                <q-btn
                                    flat
                                    color="negative"
                                    label="Cancel"
                                    :to="route('conversations.index')"
                                    class="q-mr-sm"
                                />
                                <q-btn
                                    type="submit"
                                    color="primary"
                                    label="Create Conversation"
                                    :loading="submitting"
                                />
                            </div>
                        </q-form>
                    </q-card-section>
                </q-card>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

// Get the route function from the window object
const route = window.route;

const props = defineProps({
    users: {
        type: Array,
        default: () => [],
    },
});

const isGroup = ref(false);
const name = ref('');
const selectedUsers = ref([]);
const submitting = ref(false);

// Get initials from name
const getInitials = (name) => {
    if (!name) return '?';
    return name.split(' ').map(n => n[0]).join('').toUpperCase();
};

// Get avatar color based on name
const getAvatarColor = (name) => {
    if (!name) return 'primary';

    const colors = ['primary', 'secondary', 'accent', 'positive', 'negative', 'info', 'warning'];
    const hash = name.split('').reduce((acc, char) => acc + char.charCodeAt(0), 0);

    return colors[hash % colors.length];
};

const handleSubmit = () => {
    submitting.value = true;

    const userIds = isGroup.value
        ? selectedUsers.value.map(user => user.id)
        : [selectedUsers.value.id];

    router.post(route('conversations.store'), {
        user_ids: userIds,
        name: isGroup.value ? name.value : null,
    }, {
        onFinish: () => {
            submitting.value = false;
        },
    });
};
</script>
