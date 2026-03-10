<template>
    <AppLayout title="Conversations">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Conversations
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <q-card flat bordered class="q-mb-md">
                    <q-card-section class="flex justify-between items-center">
                        <div class="text-h5">Your Conversations</div>
                        <q-btn
                            color="primary"
                            :to="route('conversations.create')"
                            icon="add"
                            label="New Conversation"
                        />
                    </q-card-section>
                </q-card>

                <div v-if="conversations.length === 0" class="text-center py-8">
                    <q-icon name="chat" size="4em" color="grey-5" />
                    <div class="text-h6 q-mt-md">No conversations yet</div>
                    <div class="text-grey-7 q-mb-md">Start a new conversation to chat with someone</div>
                    <q-btn color="primary" :to="route('conversations.create')" label="Start a Conversation" />
                </div>

                <q-list v-else bordered separator class="rounded-borders">
                    <q-item
                        v-for="conversation in conversations"
                        :key="conversation.id"
                        :to="route('conversations.show', conversation.id)"
                        clickable
                        v-ripple
                    >
                        <q-item-section avatar>
                            <q-avatar v-if="conversation.is_group">
                                <q-icon name="group" size="md" />
                            </q-avatar>
                            <q-avatar v-else :color="getAvatarColor(conversation.name)">
                                {{ getInitials(conversation.name) }}
                            </q-avatar>
                        </q-item-section>

                        <q-item-section>
                            <q-item-label class="flex justify-between">
                                <span>{{ conversation.name }}</span>
                                <span v-if="conversation.latest_message" class="text-grey-7 text-caption">
                                    {{ formatDate(conversation.latest_message.created_at) }}
                                </span>
                            </q-item-label>
                            <q-item-label caption lines="1" v-if="conversation.latest_message">
                                <span v-if="conversation.latest_message.is_mine">You: </span>
                                {{ conversation.latest_message.body }}
                            </q-item-label>
                            <q-item-label caption v-else>
                                <span class="text-grey-7">No messages yet</span>
                            </q-item-label>
                        </q-item-section>

                        <q-item-section side v-if="conversation.unread_count > 0">
                            <q-badge color="primary" rounded>
                                {{ conversation.unread_count }}
                            </q-badge>
                        </q-item-section>
                    </q-item>
                </q-list>
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
    conversations: {
        type: Array,
        default: () => [],
    },
});

// Format date to a readable format
const formatDate = (dateString) => {
    const date = new Date(dateString);
    const now = new Date();
    const diff = now - date;

    // If less than 24 hours, show time
    if (diff < 24 * 60 * 60 * 1000) {
        return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }

    // If less than 7 days, show day of week
    if (diff < 7 * 24 * 60 * 60 * 1000) {
        return date.toLocaleDateString([], { weekday: 'short' });
    }

    // Otherwise show date
    return date.toLocaleDateString([], { month: 'short', day: 'numeric' });
};

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
</script>
