<template>
    <!-- This component doesn't render anything, it just listens for notifications -->
    <div style="display: none;"></div>
</template>

<script setup>
import { onMounted, onUnmounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useChatNotifications } from '@/services/ChatNotificationService';
import { ToolsSwitcher } from '@/Utils/toolsSwitcher';

const props = defineProps({
    userId: {
        type: [String, Number],
        required: true
    }
});

let echoChannel = null;

const { initNotifications, sendNotification } = useChatNotifications();
const { auth } = usePage().props;

onMounted(() => {
    // Check if notifications are enabled
    if (!ToolsSwitcher.isNotificationsEnabled()) {
        console.log('🚫 Notifications disabled by toolsSwitcher');
        return;
    }

    console.log('🔔 Notifications enabled - initializing...');
    
    try {
        // Initialize notifications only if enabled
        if (ToolsSwitcher.isEnabled('backgroundServices', 'notifications')) {
            initNotifications(props.userId);
        }

        // Set up Laravel Echo listeners only if realtime is enabled
        if (ToolsSwitcher.isEnabled('backgroundServices', 'realtime') && window.Echo) {
            echoChannel = window.Echo.private(`user.${props.userId}`)
                .listen('.message.sent', (e) => {
                    if (ToolsSwitcher.isNotificationsEnabled()) {
                        sendNotification(props.userId, {
                            title: `New message from ${e.user_name}`,
                            message: e.body,
                            conversationId: e.conversation_id,
                            senderId: e.user_id,
                            type: 'message'
                        });
                    }
                });
        }
    } catch (error) {
        console.error('Error setting up notification listeners:', error);
    }
});

onUnmounted(() => {
    if (echoChannel) {
        echoChannel.stopListening('.message.sent');
    }
});
</script>



