<template>
    <div class="chat-room">
        <!-- Connection Status Banner -->
        <div v-if="connectionStatus !== 'connected'"
             :class="{
                'bg-yellow-100 border-yellow-400 text-yellow-700': connectionStatus === 'reconnecting',
                'bg-red-100 border-red-400 text-red-700': connectionStatus === 'failed'
             }"
             class="px-4 py-3 rounded relative mb-4"
             role="alert">
            <span v-if="connectionStatus === 'reconnecting'">
                Attempting to reconnect... ({{ connectionState.reconnectAttempts + 1 }}/{{ connectionState.maxReconnectAttempts }})
            </span>
            <span v-else>
                Connection failed. Please check your internet connection and refresh the page.
            </span>
        </div>

        <div class="flex flex-col h-[500px] bg-white rounded-lg shadow-lg">
            <!-- Chat Header -->
            <div class="p-4 border-b">
                <h2 class="text-lg font-semibold">{{ roomName }}</h2>
                <p class="text-sm text-gray-500">{{ userCount }} users online</p>
            </div>

            <!-- Messages Area -->
            <div class="flex-1 p-4 overflow-y-auto" ref="messagesContainer">
                <div v-for="message in messages" :key="message.id"
                     :class="['mb-4', message.userId === currentUser.id ? 'flex justify-end' : 'flex justify-start']">
                    <div :class="['max-w-[70%] rounded-lg p-3',
                                message.userId === currentUser.id
                                ? 'bg-blue-500 text-white'
                                : 'bg-gray-100']">
                        <div class="text-sm font-semibold mb-1">
                            {{ message.userName }}
                        </div>
                        <div class="break-words">
                            {{ message.text }}
                        </div>
                        <div class="text-xs mt-1 opacity-70">
                            {{ formatTime(message.timestamp) }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Input Area -->
            <div class="p-4 border-t">
                <form @submit.prevent="sendMessage" class="flex gap-2">
                    <input
                        v-model="newMessage"
                        type="text"
                        placeholder="Type a message..."
                        class="flex-1 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        :disabled="!connectionState.isConnected.value"
                    />
                    <button
                        type="submit"
                        class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 disabled:opacity-50"
                        :disabled="!connectionState.isConnected.value || !newMessage.trim()"
                    >
                        Send
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed, nextTick } from 'vue';
import { database as db } from '@/firebase/init';
import { ref as dbRef, push, onValue, off, serverTimestamp, set } from 'firebase/database';
import { connectionManager, connectionState } from '@/firebase/connectionManager';
import { toast } from 'vue3-toastify';

const props = defineProps(['roomId', 'roomName', 'currentUser']);
const messages = ref([]);
const newMessage = ref('');
const messagesContainer = ref(null);
const userCount = ref(0);

// References to Firebase paths
const messagesRef = dbRef(db, `rooms/${props.roomId}/messages`);
const usersRef = dbRef(db, `rooms/${props.roomId}/users`);

// Computed properties for connection state
const connectionStatus = computed(() => {
    if (connectionState.isConnected.value) return 'connected';
    if (connectionState.reconnectAttempts.value >= connectionState.maxReconnectAttempts) return 'failed';
    return 'reconnecting';
});

// Format timestamp to readable time
const formatTime = (timestamp) => {
    if (!timestamp) return 'Just now';

    // If timestamp is a server timestamp object, it might not have been resolved yet
    if (typeof timestamp === 'object' && timestamp !== null) {
        return 'Just now';
    }

    try {
        const date = new Date(timestamp * 1000); // Convert seconds to milliseconds
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

// Handle connection status changes
const handleConnectionChange = (status) => {
    if (status === 'connected') {
        setupMessageListener();
        setupUserListener();
        updateUserPresence();
    }
};

// Setup message listener
const setupMessageListener = () => {
    off(messagesRef); // Clear existing listener
    onValue(messagesRef, (snapshot) => {
        if (!connectionState.isConnected.value) return;
        const data = snapshot.val();
        if (data) {
            messages.value = Object.entries(data)
                .map(([id, message]) => ({ id, ...message }))
                .sort((a, b) => b.timestamp - a.timestamp);
            scrollToBottom();
        }
    }, (error) => {
        console.error('Error fetching messages:', error);
        toast.error('Error loading messages');
    });
};

// Setup user listener
const setupUserListener = () => {
    off(usersRef);
    onValue(usersRef, (snapshot) => {
        if (!connectionState.isConnected.value) return;
        const data = snapshot.val();
        userCount.value = data ? Object.keys(data).length : 0;
    });
};

// Send message with error handling
const sendMessage = async () => {
    if (!newMessage.value.trim() || !connectionState.isConnected.value) return;

    try {
        await push(messagesRef, {
            text: newMessage.value.trim(),
            userId: props.currentUser.id,
            userName: props.currentUser.name,
            timestamp: serverTimestamp()
        });
        newMessage.value = '';
        await scrollToBottom();
    } catch (error) {
        console.error('Error sending message:', error);
        toast.error('Failed to send message. Please try again.');
    }
};

// Update user presence with error handling
const updateUserPresence = async () => {
    try {
        const userRef = dbRef(db, `rooms/${props.roomId}/users/${props.currentUser.id}`);
        await set(userRef, {
            name: props.currentUser.name,
            lastSeen: serverTimestamp()
        });
    } catch (error) {
        console.error('Error updating presence:', error);
    }
};

onMounted(() => {
    connectionManager.addListener(handleConnectionChange);
    connectionManager.startListening();
});

onUnmounted(() => {
    connectionManager.removeListener(handleConnectionChange);
    connectionManager.stopListening();
    off(messagesRef);
    off(usersRef);
});
</script>

<style scoped>
/* Add any custom styles here */
</style>



