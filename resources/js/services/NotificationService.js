import { database } from '@/firebase/init';
import { ref as dbRef, onValue, off, serverTimestamp, push, update } from 'firebase/database';
import { ToolsSwitcher } from '@/Utils/toolsSwitcher';

class NotificationService {
    constructor() {
        this.listeners = new Map();
    }

    // Subscribe to notifications
    subscribeToNotifications(userId, callback) {
        // Check if notifications are enabled
        if (!ToolsSwitcher.isNotificationsEnabled() || !database) {
            console.log('🚫 Notifications disabled or Firebase database not available');
            callback({ data: [] });
            return;
        }

        const userNotificationsRef = dbRef(database, `users/${userId}/notifications`);

        onValue(userNotificationsRef, (snapshot) => {
            const data = snapshot.val();
            if (data) {
                const notifications = Object.entries(data)
                    .map(([id, notification]) => ({
                        id,
                        ...notification,
                    }));
                callback({ data: notifications });
            } else {
                callback({ data: [] });
            }
        });

        this.listeners.set(`notifications_${userId}`, userNotificationsRef);
    }

    // Mark notification as read
    async markNotificationAsRead(userId, notificationId) {
        if (!ToolsSwitcher.isNotificationsEnabled() || !database) {
            console.log('🚫 Cannot mark notification as read - service disabled');
            return;
        }

        const notificationRef = dbRef(database, `users/${userId}/notifications/${notificationId}`);
        await update(notificationRef, {
            read: true
        });
    }

    // Unsubscribe from all listeners
    unsubscribeAll() {
        this.listeners.forEach((ref) => {
            off(ref);
        });
        this.listeners.clear();
    }
}

export default new NotificationService();


