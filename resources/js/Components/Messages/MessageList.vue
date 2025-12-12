<template> 
    <div :class="[
        'messages-container',
        { 'compact': compact }
    ]">
        <div v-if="messageStore.loading" class="text-center py-4">
            Loading messages...
        </div>

        <div v-else-if="messageStore.error" class="text-red-500 text-center py-4">
            {{ messageStore.error }}
        </div>

        <div v-else-if="filteredMessages.length === 0" class="text-center py-4 text-gray-500">
            No messages yet
        </div>

        <div v-else class="space-y-2">
            <div v-for="message in filteredMessages"
                 :key="message.id"
                 :class="[
                     'message-item p-3 bg-white rounded-lg',
                     { 'shadow-sm hover:bg-gray-50': compact },
                     { 'shadow hover:shadow-md': !compact }
                 ]">
                <div class="flex items-start gap-2">
                    <div class="flex-1">
                        <div class="flex items-center justify-between">
                            <span class="font-medium text-gray-900">
                                {{ message.sender?.name }}
                            </span>
                            <span class="text-xs text-gray-500">
                                {{ formatDate(message.created_at) }}
                            </span>
                        </div>
                        <p :class="[
                            'mt-1 text-gray-600',
                            { 'text-sm': compact }
                        ]">
                            {{ compact ? truncateText(message.content, 100) : message.content }}
                        </p>
                        <div :class="[
                            'text-gray-500',
                            { 'text-xs mt-1': compact },
                            { 'text-sm mt-2': !compact }
                        ]">
                            To: {{ message.recipients?.map(r => r.name).join(', ') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted, computed } from 'vue';
import { useMessageStore } from '@/stores/messageStore';
import { formatDistanceToNow } from 'date-fns';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
    compact: {
        type: Boolean,
        default: false
    },
    currentUserId: {
        type: [Number, String],
        required: false,
        default: () => usePage().props.auth.user?.id
    }
});

const messageStore = useMessageStore();

const filteredMessages = computed(() => {
    return messageStore.messages.filter(message => {
        // Only show messages where the current user is a recipient
        return message.recipients.some(recipient => recipient.id === props.currentUserId);
    });
});

const formatDate = (date) => {
    if (!date) return '';
    return formatDistanceToNow(new Date(date), { addSuffix: true });
};

const truncateText = (text, length) => {
    if (!text) return '';
    if (text.length <= length) return text;
    return text.substring(0, length) + '...';
};

onMounted(async () => {
    await messageStore.fetchLatestMessages();
});
</script>

<style scoped>
.messages-container {
    max-height: 600px;
    overflow-y: auto;
}

.messages-container.compact {
    max-height: 300px;
}

.message-item {
    transition: all 0.2s ease;
}

.message-item:hover {
    transform: translateY(-1px);
}
</style>




