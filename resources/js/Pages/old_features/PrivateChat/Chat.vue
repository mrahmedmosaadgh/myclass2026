<template>
    <Head :title="`Chat with ${conversation.user.name}`" />

            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Chat with {{ conversation.user.name }}
                </h2>
                <Link :href="route('private-chat.index')" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                    Back to Users
                </Link>
            </div>


        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="flex flex-col h-[600px]">
                        <!-- Chat Header -->
                        <div class="p-4 border-b dark:border-gray-700 flex items-center">
                            <div class="flex-shrink-0">
                                <img
                                    :src="conversation.user.profile_photo_url"
                                    alt="User avatar"
                                    class="h-10 w-10 rounded-full"
                                >
                            </div>
                            <div class="ml-3">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                    {{ conversation.user.name }}
                                </h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ conversation.user.email }}
                                </p>
                            </div>
                        </div>

                        <!-- Messages Area -->
                        <div class="flex-1 p-4 overflow-y-auto" ref="messagesContainer">
                            <div v-if="allMessages.length === 0" class="flex items-center justify-center h-full">
                                <p class="text-gray-500 dark:text-gray-400">
                                    No messages yet. Start the conversation!
                                </p>
                            </div>

                            <template v-else>
                                <div
                                    v-for="message in allMessages"
                                    :key="message.id"
                                    :class="[
                                        'mb-4',
                                        message.is_mine ? 'flex justify-end' : 'flex justify-start'
                                    ]"
                                >
                                    <div
                                        :class="[
                                            'max-w-[70%] rounded-lg p-3',
                                            message.is_mine
                                                ? 'bg-blue-500 text-white'
                                                : 'bg-gray-100 dark:bg-gray-700 dark:text-gray-200'
                                        ]"
                                    >
                                        <div class="break-words">
                                            {{ message.body }}
                                        </div>
                                        <div class="text-xs mt-1 opacity-70">
                                            {{ formatTime(message.created_at) }}
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Input Area -->
                        <div class="p-4 border-t dark:border-gray-700">
                            <form @submit.prevent="sendMessage" class="flex gap-2">
                                <input
                                    v-model="newMessage"
                                    type="text"
                                    placeholder="Type a message..."
                                    class="flex-1 px-4 py-2 border dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-600"
                                    :disabled="sending"
                                />
                                <button
                                    type="submit"
                                    class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 disabled:opacity-50 transition"
                                    :disabled="!newMessage.trim() || sending"
                                >
                                    Send
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

</template>

<script setup>
import { ref, onMounted, onUnmounted, nextTick, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import axios from 'axios';
import privateChatNotifications from '@/firebase/privateChatNotifications';
import { useQuasar } from 'quasar';

const props = defineProps({
    conversation: Object,
    messages: Array,
});

const messagesContainer = ref(null);
const newMessage = ref('');
const sending = ref(false);
const localMessages = ref([]);
const $q = useQuasar();

// Combine prop messages with local messages
const allMessages = computed(() => {
    return [...props.messages, ...localMessages.value];
});

// Format timestamp to readable time
const formatTime = (timestamp) => {
    if (!timestamp) return 'Just now';

    try {
        const date = new Date(timestamp);
        return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    } catch (error) {
        console.error('Error formatting time:', error);
        return 'Invalid time';
    }
};

// Scroll to bottom of messages container
const scrollToBottom = async () => {
    if (!messagesContainer.value) return;

    // Use nextTick to ensure DOM is updated before scrolling
    await nextTick();
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
};

// Send a message
const sendMessage = async () => {
    if (!newMessage.value.trim() || sending.value) return;

    sending.value = true;

    try {
        const response = await axios.post(
            route('private-chat.send-message', props.conversation.id),
            { message: newMessage.value.trim() }
        );

        // Add the new message to local messages
        localMessages.value.push(response.data);

        // Clear the input
        newMessage.value = '';

        // Scroll to bottom
        await scrollToBottom();

        // Show success notification (optional - can be removed if too noisy)
        /*
        $q.notify({
            message: 'Message sent',
            color: 'positive',
            icon: 'check_circle',
            position: 'bottom-right',
            timeout: 1000
        });
        */
    } catch (error) {
        console.error('Error sending message:', error);

        // Show error notification
        $q.notify({
            message: 'Failed to send message',
            caption: 'Please try again',
            color: 'negative',
            icon: 'error',
            position: 'top',
            timeout: 3000
        });
    } finally {
        sending.value = false;
    }
};

// Set up real-time listeners for new messages
const setupRealTimeListeners = () => {
    // Listen for Firebase notifications
    const currentUserId = props.conversation.user.id;

    // Mark any existing notifications as read
    privateChatNotifications.markNotificationAsRead(
        currentUserId,
        props.conversation.id
    );

    // Set up listener for new messages
    privateChatNotifications.listenForNotifications(currentUserId, (notifications) => {
        if (!notifications) return;

        // Check if there's a notification for this conversation
        const notification = notifications[props.conversation.id];
        if (!notification || notification.is_read) return;

        // Mark the notification as read
        privateChatNotifications.markNotificationAsRead(
            currentUserId,
            props.conversation.id
        );

        // Refresh the messages
        refreshMessages();
    });
};

// Refresh messages from the server
const refreshMessages = async () => {
    try {
        const response = await axios.get(route('private-chat.get-messages', props.conversation.id));

        // Get the new messages from the response
        const newMessages = response.data;

        // Add any new messages that aren't already in the local messages
        const existingIds = [...props.messages, ...localMessages.value].map(m => m.id);

        let newMessageCount = 0;
        newMessages.forEach(message => {
            if (!existingIds.includes(message.id)) {
                localMessages.value.push(message);
                newMessageCount++;
            }
        });

        // Scroll to bottom if we received new messages
        if (newMessageCount > 0) {
            scrollToBottom();

            // Show notification for new messages (optional)
            /*
            $q.notify({
                message: `${newMessageCount} new message${newMessageCount > 1 ? 's' : ''}`,
                color: 'info',
                icon: 'chat',
                position: 'bottom-right',
                timeout: 2000
            });
            */
        }
    } catch (error) {
        console.error('Error refreshing messages:', error);

        // Show error notification (only if it's not a network error, which can be common during polling)
        if (error.response) {
            $q.notify({
                message: 'Error refreshing messages',
                color: 'warning',
                icon: 'sync_problem',
                position: 'bottom-right',
                timeout: 3000
            });
        }
    }
};

// Poll for new messages every 10 seconds as a fallback
const pollingInterval = ref(null);
const startPolling = () => {
    pollingInterval.value = setInterval(() => {
        refreshMessages();
    }, 10000); // 10 seconds
};

onMounted(() => {
    // Scroll to bottom initially
    scrollToBottom();

    // Set up real-time listeners
    setupRealTimeListeners();

    // Start polling as a fallback
    startPolling();
});

onUnmounted(() => {
    // Clear polling interval
    if (pollingInterval.value) {
        clearInterval(pollingInterval.value);
    }

    // Remove Firebase listeners
    privateChatNotifications.removeNotificationListener(props.conversation.user.id);
});
</script>
