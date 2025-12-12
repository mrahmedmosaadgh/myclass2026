<template>
    <div class="relative">
        <!-- Notification Bell -->
        <button
            class="relative p-2 text-gray-600 hover:text-gray-800"
            @click="toggleNotifications"
        >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
                />
            </svg>

            <!-- Notification Badge -->
            <div v-if="unreadCount > 0"
                class="absolute -top-1 -right-1 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs">
                {{ unreadCount }}
            </div>
        </button>

        <!-- Notifications Dropdown -->
        <div v-if="isOpen"
            class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg z-50 notifications-dropdown">
            <div class="p-4">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">Messages & Notifications</h3>
                    <button @click="isOpen = false" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Messages Section -->
                <div class="mb-4">
                    <h4 class="text-sm font-medium text-gray-700 mb-2">Recent Messages</h4>
                    <MessageList compact />
                </div>

                <!-- Notifications Section -->
                <div>
                    <h4 class="text-sm font-medium text-gray-700 mb-2">Notifications</h4>
                    <div v-if="notifications.length > 0">
                        <div v-for="notification in notifications"
                            :key="notification.id"
                            class="mb-3 p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100"
                            @click="handleNotificationClick(notification)">
                            <div class="font-medium">{{ notification.title }}</div>
                            <div class="text-sm text-gray-600">{{ notification.message }}</div>
                            <div class="text-xs text-gray-500 mt-1">
                                {{ formatTime(notification.timestamp) }}
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center text-gray-500 py-2">
                        No new notifications
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { notificationService } from '@/services/NotificationService';
import MessageList from '@/Components/Messages/MessageList.vue';
import { useMessageStore } from '@/stores/messageStore';

const props = defineProps({
    userId: {
        type: [String, Number],
        required: true,
        validator: (value) => {
            return value !== undefined && value !== null;
        }
    }
});

const messageStore = useMessageStore();
const notifications = ref([]);
const isOpen = ref(false);

const unreadCount = computed(() => {
    const unreadMessages = messageStore.messages.filter(m => !m.read).length;
    const unreadNotifications = notifications.value.filter(n => !n.read).length;
    return unreadMessages + unreadNotifications;
});

const formatTime = (timestamp) => {
    if (!timestamp) return '';
    const date = new Date(timestamp);
    return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
};

const handleNotificationClick = async (notification) => {
    if (!notification.read) {
        await notificationService.markNotificationAsRead(props.userId, notification.id);
    }
    // Handle navigation or action based on notification type
    if (notification.type === 'chat') {
        // Navigate to chat room
        // router.push(`/chat/${notification.roomId}`);
    }
    isOpen.value = false;
};

const toggleNotifications = () => {
    isOpen.value = !isOpen.value;
    if (isOpen.value) {
        messageStore.fetchLatestMessages();
    }
};

// Handle clicks outside to close dropdown
const handleClickOutside = (event) => {
    const dropdown = event.target.closest('.notifications-dropdown');
    const bell = event.target.closest('button');
    if (isOpen.value && !dropdown && !bell) {
        isOpen.value = false;
    }
};

onMounted(() => {
    notificationService.subscribeToNotifications(props.userId, ({ data }) => {
        notifications.value = data;
    });
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    notificationService.unsubscribeAll();
    document.removeEventListener('click', handleClickOutside);
});
</script>

<style scoped>
.notifications-dropdown {
    max-height: 80vh;
    overflow-y: auto;
}
</style>

