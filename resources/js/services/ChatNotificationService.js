import { ref, onUnmounted } from 'vue';
import { getDatabase, ref as dbRef, onValue, off, push, set } from 'firebase/database';
import { useQuasar } from 'quasar';

export function useChatNotifications() {
    const $q = useQuasar();
    const unreadCount = ref(0);
    const notifications = ref([]);
    let notificationsRef = null;
    let notificationsListener = null;

    // Initialize notifications
    const initNotifications = (userId) => {
        if (!userId) return;

        try {
            // Check if Firebase is initialized
            if (!window.firebaseDatabase) {
                console.warn('Firebase database not initialized. Notifications will not work.');
                return;
            }

            const db = getDatabase();
            notificationsRef = dbRef(db, `chat_notifications/${userId}`);

            // Listen for notifications
            notificationsListener = onValue(notificationsRef, (snapshot) => {
                const data = snapshot.val();
                if (!data) return;

                // Convert to array and sort by timestamp (newest first)
                const notificationsList = Object.entries(data).map(([key, value]) => ({
                    id: key,
                    ...value,
                    read: value.read || false
                })).sort((a, b) => b.timestamp - a.timestamp);

                notifications.value = notificationsList;

                // Count unread notifications
                unreadCount.value = notificationsList.filter(n => !n.read).length;

                // Show notification for the newest unread one
                const newestUnread = notificationsList.find(n => !n.read);
                if (newestUnread && newestUnread.timestamp > (Date.now() / 1000) - 5) { // Within last 5 seconds
                    showNotification(newestUnread);
                }
            });
        } catch (error) {
            console.error('Error initializing notifications:', error);
        }
    };

    // Send a notification
    const sendNotification = async (userId, notification) => {
        try {
            // Check if Firebase is initialized
            if (!window.firebaseDatabase) {
                console.warn('Firebase database not initialized. Cannot send notification.');
                return false;
            }

            const db = getDatabase();
            const userNotificationsRef = dbRef(db, `chat_notifications/${userId}`);
            const newNotificationRef = push(userNotificationsRef);

            await set(newNotificationRef, {
                ...notification,
                timestamp: Date.now() / 1000,
                read: false
            });

            return true;
        } catch (error) {
            console.error('Error sending notification:', error);
            return false;
        }
    };

    // Mark a notification as read
    const markAsRead = async (userId, notificationId) => {
        try {
            // Check if Firebase is initialized
            if (!window.firebaseDatabase) {
                console.warn('Firebase database not initialized. Cannot mark notification as read.');
                return false;
            }

            const db = getDatabase();
            const notificationRef = dbRef(db, `chat_notifications/${userId}/${notificationId}`);

            await set(notificationRef, {
                ...notifications.value.find(n => n.id === notificationId),
                read: true
            });

            return true;
        } catch (error) {
            console.error('Error marking notification as read:', error);
            return false;
        }
    };

    // Mark all notifications as read
    const markAllAsRead = async (userId) => {
        try {
            // Check if Firebase is initialized
            if (!window.firebaseDatabase) {
                console.warn('Firebase database not initialized. Cannot mark all notifications as read.');
                return false;
            }

            const db = getDatabase();

            // Update each notification
            const promises = notifications.value.map(notification => {
                if (notification.read) return Promise.resolve();

                const notificationRef = dbRef(db, `chat_notifications/${userId}/${notification.id}`);
                return set(notificationRef, {
                    ...notification,
                    read: true
                });
            });

            await Promise.all(promises);

            return true;
        } catch (error) {
            console.error('Error marking all notifications as read:', error);
            return false;
        }
    };

    // Show a notification
    const showNotification = (notification) => {
        $q.notify({
            message: notification.title,
            caption: notification.message,
            icon: 'chat',
            color: 'primary',
            position: 'top-right',
            timeout: 5000,
            actions: [
                { label: 'View', color: 'white', handler: () => {
                    // Navigate to the conversation
                    if (notification.conversationId) {
                        window.location.href = `/conversations/${notification.conversationId}`;
                    }
                }}
            ]
        });
    };

    // Clean up
    const cleanup = () => {
        if (notificationsRef && notificationsListener) {
            off(notificationsRef, 'value', notificationsListener);
        }
    };

    onUnmounted(() => {
        cleanup();
    });

    return {
        unreadCount,
        notifications,
        initNotifications,
        sendNotification,
        markAsRead,
        markAllAsRead,
        cleanup
    };
}
