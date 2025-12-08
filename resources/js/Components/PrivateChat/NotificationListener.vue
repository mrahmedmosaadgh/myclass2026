<template>
  <div style="display: none;"></div>
</template>

<script setup>
import { onMounted, onUnmounted, watch } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import privateChatNotifications from '@/firebase/privateChatNotifications';
import { useQuasar } from 'quasar';
import { computed } from 'vue';

const props = defineProps({
  userId: {
    type: [Number, String],
    required: true
  }
});

const $q = useQuasar();
const page = usePage();

// Handle a new notification
const handleNotification = (notifications) => {
  if (!notifications) return;

  // Process each notification
  Object.entries(notifications).forEach(([conversationId, notification]) => {
    // Skip if the notification is already read
    if (notification.is_read) return;

    // Skip if we're already on the chat page for this conversation
    const currentRoute = page.url;
    if (currentRoute.includes(`/private-chat/${notification.sender_id}`)) {
      // Mark as read if we're on the chat page
      privateChatNotifications.markNotificationAsRead(props.userId, conversationId);
      return;
    }

    // Show a Quasar notification
    $q.notify({
      message: `New message from ${notification.sender_name}`,
      caption: `${notification.message_preview}${notification.message_preview.length >= 50 ? '...' : ''}`,
      color: 'primary',
      icon: 'chat',
      position: 'top-right',
      timeout: 5000,
      actions: [
        {
          label: 'View',
          color: 'white',
          handler: () => {
            // Use router.visit with the URL pattern for the private chat
            router.visit(`/private-chat/${notification.sender_id}`);
          }
        },
        {
          icon: 'close',
          color: 'white'
        }
      ]
    });
  });
};

// Start listening for notifications when the component is mounted
onMounted(() => {
  if (props.userId) {
    privateChatNotifications.listenForNotifications(props.userId, handleNotification);
  }
});

// Stop listening for notifications when the component is unmounted
onUnmounted(() => {
  if (props.userId) {
    privateChatNotifications.removeNotificationListener(props.userId);
  }
});

// Watch for changes to the userId prop
watch(() => props.userId, (newUserId, oldUserId) => {
  if (oldUserId) {
    privateChatNotifications.removeNotificationListener(oldUserId);
  }

  if (newUserId) {
    privateChatNotifications.listenForNotifications(newUserId, handleNotification);
  }
});
</script>
