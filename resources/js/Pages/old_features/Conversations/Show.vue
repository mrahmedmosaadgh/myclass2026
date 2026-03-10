<template>
    <AppLayout :title="conversation.name">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ conversation.name }}
                </h2>
                <q-btn
                    flat
                    round
                    color="primary"
                    icon="arrow_back"
                    :to="route('conversations.index')"
                />
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <q-card flat bordered class="chat-container">
                    <!-- Chat Header -->
                    <q-card-section class="bg-grey-2">
                        <div class="flex items-center">
                            <q-avatar v-if="conversation.is_group">
                                <q-icon name="group" size="md" />
                            </q-avatar>
                            <q-avatar v-else :color="getAvatarColor(conversation.name)">
                                {{ getInitials(conversation.name) }}
                            </q-avatar>

                            <div class="q-ml-md">
                                <div class="text-weight-bold">{{ conversation.name }}</div>
                                <div class="text-caption" v-if="conversation.is_group">
                                    {{ conversation.users.length }} participants
                                </div>
                                <div v-else-if="typingUser" class="text-caption text-primary">
                                    {{ typingUser }} is typing...
                                </div>
                            </div>
                        </div>
                    </q-card-section>

                    <!-- Messages -->
                    <q-card-section class="messages-container q-pa-none">
                        <q-scroll-area
                            ref="scrollArea"
                            style="height: 400px;"
                            class="q-px-md"
                        >
                            <div class="q-py-md">
                                <div v-if="messages.length === 0" class="text-center text-grey q-py-xl">
                                    No messages yet. Start the conversation!
                                </div>

                                <template v-else>
                                    <div
                                        v-for="(message, index) in messages"
                                        :key="message.id"
                                        class="q-mb-md"
                                    >
                                        <!-- Date separator -->
                                        <div
                                            v-if="shouldShowDate(message, index)"
                                            class="text-center text-grey-7 text-caption q-my-sm"
                                        >
                                            {{ formatDateHeader(message.created_at) }}
                                        </div>

                                        <!-- Message bubble -->
                                        <div
                                            :class="[
                                                'message-bubble flex',
                                                message.is_mine ? 'justify-end' : 'justify-start'
                                            ]"
                                        >
                                            <!-- Avatar for other users -->
                                            <q-avatar
                                                v-if="!message.is_mine && conversation.is_group"
                                                size="sm"
                                                :color="getAvatarColor(message.user.name)"
                                                class="q-mr-sm self-end"
                                            >
                                                {{ getInitials(message.user.name) }}
                                            </q-avatar>

                                            <div
                                                :class="[
                                                    'message-content rounded-borders q-pa-sm',
                                                    message.is_mine
                                                        ? 'bg-primary text-white'
                                                        : 'bg-grey-3'
                                                ]"
                                            >
                                                <!-- Sender name for group chats -->
                                                <div
                                                    v-if="!message.is_mine && conversation.is_group"
                                                    class="text-caption text-weight-bold q-mb-xs"
                                                >
                                                    {{ message.user.name }}
                                                </div>

                                                <!-- Message content -->
                                                <div v-if="message.type === 'text'">
                                                    {{ message.body }}
                                                </div>

                                                <div v-else-if="message.type === 'image'">
                                                    <q-img
                                                        :src="message.attachment_url"
                                                        spinner-color="white"
                                                        style="max-width: 250px; max-height: 250px"
                                                    />
                                                </div>

                                                <div v-else-if="message.type === 'file'">
                                                    <q-btn
                                                        flat
                                                        :color="message.is_mine ? 'white' : 'primary'"
                                                        icon="attach_file"
                                                        :label="getFileName(message.attachment_url)"
                                                        :href="message.attachment_url"
                                                        target="_blank"
                                                    />
                                                </div>

                                                <!-- Timestamp -->
                                                <div
                                                    :class="[
                                                        'text-caption q-mt-xs text-right',
                                                        message.is_mine ? 'text-white-7' : 'text-grey-7'
                                                    ]"
                                                >
                                                    {{ formatTime(message.created_at) }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </q-scroll-area>
                    </q-card-section>

                    <!-- Message Input -->
                    <q-card-section class="message-input bg-grey-2">
                        <q-form @submit="sendMessage" class="row items-center">
                            <q-btn
                                round
                                flat
                                color="primary"
                                icon="attach_file"
                                @click="$refs.fileInput.click()"
                            >
                                <q-tooltip>Attach File</q-tooltip>
                            </q-btn>

                            <input
                                type="file"
                                ref="fileInput"
                                style="display: none"
                                @change="handleFileSelected"
                            />

                            <q-input
                                v-model="newMessage"
                                placeholder="Type a message..."
                                dense
                                outlined
                                class="col q-mx-sm"
                                bg-color="white"
                                @keydown="handleTyping"
                            />

                            <q-btn
                                round
                                flat
                                color="primary"
                                icon="send"
                                type="submit"
                                :disable="!canSendMessage"
                            />
                        </q-form>

                        <!-- Preview for selected file -->
                        <div v-if="selectedFile" class="q-mt-sm flex items-center bg-white rounded-borders q-pa-xs">
                            <q-icon name="attach_file" color="primary" />
                            <span class="q-ml-sm">{{ selectedFile.name }}</span>
                            <q-btn
                                round
                                flat
                                dense
                                icon="close"
                                @click="selectedFile = null"
                            />
                        </div>
                    </q-card-section>
                </q-card>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import axios from 'axios';

// Get the route function from the window object
const route = window.route;

const props = defineProps({
    conversation: {
        type: Object,
        required: true,
    },
    messages: {
        type: Array,
        default: () => [],
    },
});

const scrollArea = ref(null);
const newMessage = ref('');
const selectedFile = ref(null);
const fileInput = ref(null);
const typingUser = ref(null);
const typingTimeout = ref(null);
const typingThrottled = ref(false);
const localMessages = ref([...props.messages]);

// Computed
const canSendMessage = computed(() => {
    return newMessage.value.trim() !== '' || selectedFile.value !== null;
});

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

// Format time for message bubbles
const formatTime = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
};

// Format date for date headers
const formatDateHeader = (dateString) => {
    const date = new Date(dateString);
    const today = new Date();
    const yesterday = new Date(today);
    yesterday.setDate(yesterday.getDate() - 1);

    if (date.toDateString() === today.toDateString()) {
        return 'Today';
    } else if (date.toDateString() === yesterday.toDateString()) {
        return 'Yesterday';
    } else {
        return date.toLocaleDateString([], { weekday: 'long', month: 'long', day: 'numeric' });
    }
};

// Determine if we should show a date header
const shouldShowDate = (message, index) => {
    if (index === 0) return true;

    const currentDate = new Date(message.created_at).toDateString();
    const prevDate = new Date(localMessages.value[index - 1].created_at).toDateString();

    return currentDate !== prevDate;
};

// Get filename from URL
const getFileName = (url) => {
    if (!url) return 'File';
    return url.split('/').pop();
};

// Handle file selection
const handleFileSelected = (event) => {
    selectedFile.value = event.target.files[0] || null;
};

// Send a message
const sendMessage = async () => {
    if (!canSendMessage.value) return;

    const formData = new FormData();

    if (newMessage.value.trim() !== '') {
        formData.append('body', newMessage.value);
        formData.append('type', 'text');
    }

    if (selectedFile.value) {
        formData.append('attachment', selectedFile.value);

        // Determine type based on file
        const fileType = selectedFile.value.type;
        if (fileType.startsWith('image/')) {
            formData.append('type', 'image');
        } else {
            formData.append('type', 'file');
        }
    }

    try {
        const response = await axios.post(
            route('messages.store', props.conversation.id),
            formData,
            {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            }
        );

        // Add the new message to the local messages
        localMessages.value.push({
            ...response.data,
            is_mine: true,
            user: {
                id: response.data.user.id,
                name: response.data.user.name,
                profile_photo_url: response.data.user.profile_photo_url,
            },
        });

        // Clear the input
        newMessage.value = '';
        selectedFile.value = null;

        // Scroll to bottom
        scrollToBottom();
    } catch (error) {
        console.error('Error sending message:', error);
    }
};

// Handle typing events
const handleTyping = () => {
    if (typingThrottled.value) return;

    typingThrottled.value = true;

    // Send typing event
    axios.post(route('messages.typing', props.conversation.id))
        .catch(error => console.error('Error sending typing event:', error));

    // Reset throttle after 3 seconds
    setTimeout(() => {
        typingThrottled.value = false;
    }, 3000);
};

// Scroll to bottom of messages
const scrollToBottom = () => {
    nextTick(() => {
        if (scrollArea.value) {
            const scrollEl = scrollArea.value.$el.querySelector('.scroll');
            scrollEl.scrollTop = scrollEl.scrollHeight;
        }
    });
};

// Listen for new messages
const listenForMessages = () => {
    try {
        if (!window.Echo) {
            console.warn('Laravel Echo not initialized. Real-time messaging will not work.');
            return;
        }

        window.Echo.join(`conversation.${props.conversation.id}`)
            .listen('.message.sent', (e) => {
                // Add the new message to the local messages
                localMessages.value.push({
                    id: e.id,
                    body: e.body,
                    type: e.type,
                    attachment_url: e.attachment_url,
                    created_at: e.created_at,
                    is_mine: false,
                    user: {
                        id: e.user_id,
                        name: e.user_name,
                        profile_photo_url: e.user_photo,
                    },
                });

                // Clear typing indicator
                typingUser.value = null;

                // Scroll to bottom
                scrollToBottom();

                // Mark message as seen
                markMessageAsSeen(e.id);
            })
            .listen('.user.typing', (e) => {
                // Only show typing indicator for other users
                if (e.user_id !== window.userId) {
                    typingUser.value = e.user_name;

                    // Clear typing indicator after 3 seconds
                    clearTimeout(typingTimeout.value);
                    typingTimeout.value = setTimeout(() => {
                        typingUser.value = null;
                    }, 3000);
                }
            });
    } catch (error) {
        console.error('Error setting up message listeners:', error);
    }
};

// Mark message as seen
const markMessageAsSeen = (messageId) => {
    axios.post(route('messages.mark-seen', props.conversation.id), {
        message_ids: [messageId],
    }).catch(error => console.error('Error marking message as seen:', error));
};

// Lifecycle hooks
onMounted(() => {
    // Initialize Echo
    listenForMessages();

    // Scroll to bottom on initial load
    scrollToBottom();
});

onUnmounted(() => {
    try {
        // Clean up
        clearTimeout(typingTimeout.value);

        // Leave the channel
        if (window.Echo) {
            window.Echo.leave(`conversation.${props.conversation.id}`);
        }
    } catch (error) {
        console.error('Error cleaning up message listeners:', error);
    }
});

// Watch for changes in messages prop
watch(() => props.messages, (newMessages) => {
    localMessages.value = [...newMessages];
    scrollToBottom();
}, { deep: true });
</script>

<style scoped>
.chat-container {
    display: flex;
    flex-direction: column;
    height: calc(100vh - 200px);
}

.messages-container {
    flex: 1;
    overflow: hidden;
}

.message-bubble {
    margin-bottom: 8px;
}

.message-content {
    max-width: 70%;
    word-break: break-word;
}
</style>
