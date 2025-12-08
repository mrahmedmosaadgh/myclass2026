import { database } from './init';
import { ref as dbRef, set, onValue, off, serverTimestamp, get } from 'firebase/database';
import { ToolsSwitcher } from '@/Utils/toolsSwitcher';

/**
 * Service for handling private chat notifications using Firebase
 */
class PrivateChatNotificationService {
    /**
     * Initialize the service
     */
    constructor() {
        this.listeners = new Map();
    }

    /**
     * Send a notification when a new message is sent
     */
    async sendMessageNotification(message, conversationId, senderId, recipientId) {
        if (!ToolsSwitcher.isNotificationsEnabled() || !database) {
            console.log('🚫 Private chat notifications disabled or Firebase database not available');
            return;
        }

        try {
            const notificationRef = dbRef(
                database, 
                `private_chat_notifications/${recipientId}/${conversationId}`
            );
            
            await set(notificationRef, {
                message_id: message.id,
                sender_id: senderId,
                conversation_id: conversationId,
                message_preview: message.body.substring(0, 50),
                timestamp: serverTimestamp(),
                is_read: false
            });
            
            console.log('Message notification sent successfully');
        } catch (error) {
            console.log('Error sending message notification:', error);
        }
    }

    /**
     * Listen for new message notifications for a specific user
     */
    listenForNotifications(userId, callback) {
        if (!ToolsSwitcher.isNotificationsEnabled() || !database) {
            console.log('🚫 Private chat notifications disabled or Firebase database not available');
            callback(null);
            return;
        }

        if (!userId) {
            console.log('User ID is required to listen for notifications');
            return;
        }
        
        const notificationsRef = dbRef(database, `private_chat_notifications/${userId}`);
        
        this.removeNotificationListener(userId);
        
        const listener = onValue(notificationsRef, (snapshot) => {
            const notifications = snapshot.val() || {};
            callback(notifications);
        }, (error) => {
            console.log('Error listening for notifications:', error);
        });
        
        this.listeners.set(userId, { ref: notificationsRef, listener });
        
        console.log(`Listening for notifications for user ${userId}`);
    }

    /**
     * Remove a notification listener for a specific user
     */
    removeNotificationListener(userId) {
        const listenerInfo = this.listeners.get(userId);
        
        if (listenerInfo) {
            off(listenerInfo.ref, listenerInfo.listener);
            this.listeners.delete(userId);
            console.log(`Stopped listening for notifications for user ${userId}`);
        }
    }

    /**
     * Mark a notification as read
     */
    async markNotificationAsRead(userId, conversationId) {
        if (!ToolsSwitcher.isNotificationsEnabled() || !database) {
            console.log('🚫 Cannot mark notification as read - service disabled');
            return;
        }

        try {
            const notificationRef = dbRef(
                database, 
                `private_chat_notifications/${userId}/${conversationId}`
            );
            
            const snapshot = await get(notificationRef);
            
            if (snapshot.exists()) {
                const notification = snapshot.val();
                
                await set(notificationRef, {
                    ...notification,
                    is_read: true
                });
                
                console.log('Notification marked as read');
            }
        } catch (error) {
            console.log('Error marking notification as read:', error);
        }
    }

    /**
     * Get the count of unread notifications for a user
     */
    async getUnreadNotificationsCount(userId) {
        if (!ToolsSwitcher.isNotificationsEnabled() || !database) {
            console.log('🚫 Cannot get unread count - service disabled');
            return 0;
        }

        try {
            const notificationsRef = dbRef(database, `private_chat_notifications/${userId}`);
            const snapshot = await get(notificationsRef);
            
            if (!snapshot.exists()) {
                return 0;
            }
            
            const notifications = snapshot.val();
            let count = 0;
            
            Object.values(notifications).forEach(notification => {
                if (!notification.is_read) {
                    count++;
                }
            });
            
            return count;
        } catch (error) {
            console.log('Error getting unread notifications count:', error);
            return 0;
        }
    }
}

export default new PrivateChatNotificationService();

