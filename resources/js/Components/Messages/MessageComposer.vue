<template>
    <div class="bg-white rounded-lg shadow p-6">
        <!-- Recipients Selection -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Recipients</label>
            <select
                v-model="selectedRecipients"
                multiple
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            >
                <option v-for="user in users" :key="user.id" :value="user.id">
                    {{ user.name }}
                </option>
            </select>
        </div>

        <!-- Message Input -->
        <div class="mb-4">
            <textarea
                v-model="messageContent"
                rows="4"
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                placeholder="Type your message..."
            ></textarea>
        </div>

        <!-- Send Button -->
        <button
            @click="sendMessage"
            :disabled="!canSend"
            class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50"
        >
            Send Message
        </button>

        <!-- Messages List -->
        <div class="mt-8">
            <h3 class="text-lg font-medium text-gray-900">Messages</h3>
            <div class="mt-4 space-y-4">
                <div v-for="message in filteredMessages" :key="message.id"
                     class="border rounded-lg p-4"
                     :class="'bg-gray-50'">
                    <div class="flex justify-between">
                        <span class="font-medium">{{ message.sender.name }}</span>
                        <span class="text-sm text-gray-500">
                            {{ new Date(message.created_at).toLocaleString() }}
                        </span>
                    </div>
                    <p class="mt-2">{{ message.content }}</p>
                    <div class="mt-2 text-sm text-gray-500">
                        To: {{ message.recipients.map(r => r.name).join(', ') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { database as db } from '@/firebase/init';
import { ref as dbRef, push, onValue, serverTimestamp } from 'firebase/database';
import axios from 'axios';
import { handleAxiosError } from '@/Utils/errorHandler';
import { useMessageStore } from '@/stores/messageStore';

const props = defineProps({
    currentUser: {
        type: Object,
        required: true
    }
});

const users = ref([]);
const messages = ref([]);
const selectedRecipients = ref([]);
const messageContent = ref('');
const loading = ref(false);
const error = ref(null);
const isSending = ref(false);

const messageStore = useMessageStore();

// Firebase reference for notifications
const notificationsRef = dbRef(db, 'notifications/messages');

const canSend = computed(() => {
    return selectedRecipients.value.length > 0 && messageContent.value.trim().length > 0;
});

const filteredMessages = computed(() => {
    return messages.value.filter(message => {
        // Only show messages where current user is a recipient
        return message.recipients.some(recipient => recipient.id === props.currentUser?.id);
    });
});

const fetchMessages = () => {
    loading.value = true;
    error.value = null;

    axios.get('/user-messages')
        .then(response => {
            messages.value = response.data.messages;
        })
        .catch(err => {
            error.value = 'Failed to load messages. Please try again.';
            console.error('Error fetching messages:', err);
            if (err.response?.status === 401) {
                console.log('resources/js/Components/Messages/MessageComposer.vue', err);
            }
        })
        .finally(() => {
            loading.value = false;
        });
};

const fetchUsers = () => {
    axios.get('/user-messages/users')
        .then(response => {
            users.value = response.data.users;
        })
        .catch(err => {
            console.error('Error fetching users:', err);
            error.value = 'Failed to load users. Please refresh the page.';
            if (err.response?.status === 401) {
                console.log('resources/js/Components/Messages/MessageComposer.vue', err);
            }
        });
};

const sendMessage = () => {
    if (!canSend.value || isSending.value || !isConnected.value) return;

    if (!props.currentUser?.id) {
        error.value = 'You must be logged in to send messages';
        return;
    }

    isSending.value = true;

    axios.post('/user-messages', {
        content: messageContent.value.trim(),
        recipients: selectedRecipients.value
    })
        .then(({ data }) => {
            if (data.message) {
                // Add message to store
                messageStore.addMessage(data.message);

                // Update Firebase notifications
                push(notificationsRef, {
                    type: 'new_message',
                    messageId: data.message.id,
                    senderId: props.currentUser.id,
                    timestamp: serverTimestamp()
                });

                // Notify recipients
                selectedRecipients.value.forEach(recipientId => {
                    const userNotificationRef = dbRef(db, `users/${recipientId}/notifications`);
                    push(userNotificationRef, {
                        type: 'message',
                        senderId: props.currentUser.id,
                        senderName: props.currentUser.name,
                        messageId: data.message.id,
                        content: messageContent.value.trim(),
                        timestamp: serverTimestamp(),
                        read: false
                    });
                });

                // Clear form
                messageContent.value = '';
                selectedRecipients.value = [];

                // Refresh messages
                fetchMessages();
            }
        })
        .catch((err) => {
            handleAxiosError(err);
        })
        .finally(() => {
            isSending.value = false;
        });
};

// Add real-time listener for new messages
onMounted(() => {
    Promise.all([
        fetchUsers(),
        fetchMessages()
    ])
        .then(() => {
            // Listen for new message notifications
            onValue(notificationsRef, (snapshot) => {
                const notification = snapshot.val();
                if (notification) {
                    const lastNotification = Object.values(notification).pop();
                    if (lastNotification?.timestamp > Date.now() - 1000) {
                        fetchMessages();
                    }
                }
            });
        })
        .catch(err => {
            console.error('Error in component setup:', err);
            error.value = 'Something went wrong. Please refresh the page.';
        });
});
</script>


