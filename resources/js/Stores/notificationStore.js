import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import axios from 'axios';

export const useNotificationStore = defineStore('notification', () => {
    const notifications = ref([]);
    const loading = ref(false);
    const error = ref(null);
    const pagination = ref({
        current_page: 1,
        last_page: 1,
        per_page: 15,
        total: 0
    });

    // Computed properties
    const unreadCount = computed(() => {
        return notifications.value.filter(n => n.read_at === null).length;
    });

    // Actions
    const fetchNotifications = async (page = 1) => {
        try {
            loading.value = true;
            error.value = null;

            const response = await axios.get(`/notifications?page=${page}`);

            // Handle the response format which might be different
            const data = response.data;

            // Check if the response has a data property (Laravel pagination format)
            if (data.data) {
                notifications.value = data.data;
                pagination.value = {
                    current_page: data.current_page,
                    last_page: data.last_page,
                    per_page: data.per_page,
                    total: data.total
                };
            } else {
                // If the response is directly the notifications array
                notifications.value = data;
                pagination.value = {
                    current_page: 1,
                    last_page: 1,
                    per_page: data.length,
                    total: data.length
                };
            }

            return response.data;
        } catch (err) {
            console.error('Error fetching notifications:', err);
            error.value = 'Failed to load notifications';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const markAsRead = async (notificationId) => {
        try {
            const response = await axios.post(`/notifications/${notificationId}/read`);

            // Update the notification in the local state
            const index = notifications.value.findIndex(n => n.id === notificationId);
            if (index !== -1) {
                notifications.value[index].read_at = new Date().toISOString();
            }

            return response.data;
        } catch (err) {
            console.error('Error marking notification as read:', err);
            throw err;
        }
    };

    const markAllAsRead = async () => {
        try {
            const response = await axios.post('/notifications/mark-all-read');

            // Update all notifications in the local state
            notifications.value = notifications.value.map(notification => ({
                ...notification,
                read_at: notification.read_at || new Date().toISOString()
            }));

            return response.data;
        } catch (err) {
            console.error('Error marking all notifications as read:', err);
            throw err;
        }
    };

    const deleteNotification = async (notificationId) => {
        try {
            const response = await axios.delete(`/notifications/${notificationId}`);

            // Remove the notification from the local state
            notifications.value = notifications.value.filter(n => n.id !== notificationId);

            return response.data;
        } catch (err) {
            console.error('Error deleting notification:', err);
            throw err;
        }
    };

    const sendTestNotification = async (title, body, type = 'both') => {
        try {
            const response = await axios.post('/notifications/send-test', {
                title,
                body,
                type
            });

            return response.data;
        } catch (err) {
            console.error('Error sending test notification:', err);
            throw err;
        }
    };

    return {
        notifications,
        loading,
        error,
        pagination,
        unreadCount,
        fetchNotifications,
        markAsRead,
        markAllAsRead,
        deleteNotification,
        sendTestNotification
    };
});
