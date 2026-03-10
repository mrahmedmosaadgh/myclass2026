<script setup>
import { ref, onMounted, nextTick, watch } from 'vue';
import { useForm, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useQuasar } from 'quasar';

const props = defineProps({
    conversation: Object,
});

const form = useForm({
    message: '',
});

const $q = useQuasar();
const chatContainer = ref(null);
const sending = ref(false);
const imageDialog = ref(false);
const selectedImage = ref(null);

const scrollToBottom = () => {
    nextTick(() => {
        if (chatContainer.value) {
            chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
        }
    });
};

onMounted(() => {
    scrollToBottom();
});

// Watch for new messages to scroll
watch(() => props.conversation.messages, () => {
    scrollToBottom();
}, { deep: true });

const sendReply = () => {
    if (!form.message.trim()) return;
    
    sending.value = true;
    form.post(route('admin.chatbot.admin.chatbot.reply', props.conversation.id), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            scrollToBottom();
            $q.notify({
                type: 'positive',
                message: 'Reply sent successfully',
                position: 'top'
            });
        },
        onError: (errors) => {
            console.error('Reply error:', errors);
            $q.notify({
                type: 'negative',
                message: 'Failed to send reply. Please try again.',
                position: 'top'
            });
        },
        onFinish: () => {
            sending.value = false;
        }
    });
};

const resolveTicket = () => {
    router.patch(route('admin.chatbot.update', props.conversation.id), {
        status: 'closed'
    }, {
        preserveScroll: true,
        onSuccess: () => {
            $q.notify({
                type: 'positive',
                message: 'Ticket marked as resolved',
                position: 'top'
            });
        },
        onError: () => {
            $q.notify({
                type: 'negative',
                message: 'Failed to update ticket status',
                position: 'top'
            });
        }
    });
}

const formatFileSize = (bytes) => {
    if (!bytes) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const openImagePreview = (image) => {
    selectedImage.value = image;
    imageDialog.value = true;
};
</script>

<template>
    <AdminLayout>
        <div class="q-pa-md max-w-7xl mx-auto">
            <!-- Header -->
            <div class="row items-center justify-between q-mb-md">
                <div class="flex items-center">
                    <Link :href="route('admin.chatbot.admin.chatbot.index')" class="text-grey-7 hover:text-primary transition-colors flex items-center no-underline q-mr-md">
                        <q-btn round flat icon="arrow_back" color="grey-7" />
                    </Link>
                    <div>
                        <div class="text-h6 text-weight-bold flex items-center gap-2">
                            Conversation #{{ conversation.id }}
                            <q-badge :color="conversation.status === 'new' ? 'red' : (conversation.status === 'replied' ? 'green' : 'grey')" align="top">
                                {{ conversation.status }}
                            </q-badge>
                        </div>
                        <div class="text-caption text-grey">
                            Started {{ new Date(conversation.created_at).toLocaleString() }}
                        </div>
                    </div>
                </div>
                
                <div class="flex gap-2">
                     <!-- Action buttons -->
                </div>
            </div>

            <div class="row q-col-gutter-lg">
                <!-- Chat Area -->
                <div class="col-12 col-md-8">
                    <q-card flat bordered class="column shadow-sm rounded-lg overflow-hidden bg-gray-50 dark:bg-gray-900" style="height: 75vh">
                        <!-- Messages Window -->
                        <div class="col q-pa-md scroll relative-position" ref="chatContainer">
                            <div v-if="conversation.messages.length === 0" class="absolute-center text-center text-grey">
                                <q-icon name="chat_bubble_outline" size="xl" class="q-mb-sm opacity-50" />
                                <div>No messages yet</div>
                            </div>
                            
                            <div v-else class="q-gutter-y-md">
                                <template v-for="msg in conversation.messages" :key="msg.id">
                                    <!-- User Message (Left) -->
                                    <div v-if="msg.sender_type === 'user'" class="row justify-start">
                                        <div class="row items-end no-wrap max-w-[85%]">
                                            <q-avatar size="32px" class="q-mr-sm shadow-sm">
                                                <img :src="conversation.user?.profile_photo_url || `https://ui-avatars.com/api/?name=${conversation.user?.name || 'Guest'}&background=random`">
                                            </q-avatar>
                                            <div class="bg-white border border-gray-200 text-grey-9 rounded-tr-xl rounded-tl-xl rounded-br-xl q-pa-sm shadow-sm">
                                                <div class="text-body2 whitespace-pre-wrap">{{ msg.message }}</div>
                                                
                                                <!-- Images -->
                                                <div v-if="msg.images && msg.images.length > 0" class="row q-gutter-xs q-mt-sm">
                                                    <div
                                                        v-for="(image, index) in msg.images"
                                                        :key="index"
                                                        class="cursor-pointer"
                                                        @click="openImagePreview(image)"
                                                    >
                                                        <q-img
                                                            :src="image.data"
                                                            style="width: 80px; height: 80px;"
                                                            class="rounded-borders"
                                                        >
                                                            <q-tooltip>
                                                                {{ image.name }} ({{ formatFileSize(image.size) }})
                                                            </q-tooltip>
                                                        </q-img>
                                                    </div>
                                                </div>
                                                
                                                <div class="text-[10px] text-grey-5 text-right q-mt-xs">{{ new Date(msg.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) }}</div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Admin/AI Message (Right) -->
                                    <div v-else class="row justify-end">
                                        <div class="row items-end no-wrap max-w-[85%] flex-row-reverse">
                                            <q-avatar size="32px" class="q-ml-sm shadow-sm" :color="msg.sender_type === 'ai' ? 'purple-1' : 'blue-1'">
                                                <q-icon :name="msg.sender_type === 'ai' ? 'smart_toy' : 'support_agent'" :color="msg.sender_type === 'ai' ? 'purple' : 'blue'" />
                                            </q-avatar>
                                            <div :class="['rounded-tr-xl rounded-tl-xl rounded-bl-xl q-pa-sm shadow-sm text-white', msg.sender_type === 'ai' ? 'bg-purple-600' : 'bg-blue-600']">
                                                <div v-if="msg.sender_type === 'ai'" class="text-[10px] text-purple-100 q-mb-xs font-bold flex items-center">
                                                    <q-icon name="auto_awesome" size="xs" class="q-mr-xs" /> AI Assistant
                                                </div>
                                                <div class="text-body2 whitespace-pre-wrap">{{ msg.message }}</div>
                                                <div class="text-[10px] text-blue-100 text-right q-mt-xs">{{ new Date(msg.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <q-separator />

                        <!-- Reply Input Area -->
                        <div class="col-auto bg-white dark:bg-gray-800 q-pa-md border-t border-gray-200">
                             <q-form @submit.prevent="sendReply" class="relative-position">
                                <q-input
                                    v-model="form.message"
                                    outlined
                                    dense
                                    placeholder="Type your reply to the user..."
                                    autogrow
                                    :disable="form.processing || sending"
                                    class="bg-white"
                                    input-class="min-h-[40px] max-h-[150px]"
                                    @keydown.enter.ctrl="sendReply" 
                                >
                                    <template v-slot:append>
                                        <q-btn 
                                            round 
                                            dense 
                                            flat 
                                            icon="send" 
                                            color="primary" 
                                            @click="sendReply" 
                                            :loading="form.processing || sending" 
                                            :disable="!form.message.trim()"
                                        />
                                    </template>
                                </q-input>
                                <div class="text-[10px] text-grey-5 q-mt-xs q-ml-xs">
                                    Press Ctrl+Enter to send
                                </div>
                            </q-form>
                        </div>
                    </q-card>
                </div>

                <!-- Sidebar Info -->
                <div class="col-12 col-md-4">
                    <q-card flat bordered class="bg-white rounded-lg sticky top-6">
                        <q-card-section class="bg-grey-1 border-b border-gray-200">
                            <div class="text-subtitle2 text-uppercase text-grey-7 font-bold tracking-wide">Ticket Information</div>
                        </q-card-section>
                        
                        <q-list separator class="text-sm">
                            <q-item>
                                <q-item-section>
                                    <q-item-label caption class="q-mb-xs">User</q-item-label>
                                    <div class="row items-center">
                                         <q-avatar size="sm" class="q-mr-sm">
                                            <img :src="conversation.user?.profile_photo_url || `https://ui-avatars.com/api/?name=${conversation.user?.name || 'Guest'}&background=random`">
                                        </q-avatar>
                                        <div>
                                            <div class="font-medium">{{ conversation.user?.name || 'Guest User' }}</div>
                                            <div class="text-xs text-grey" v-if="conversation.email">{{ conversation.email }}</div>
                                        </div>
                                    </div>
                                </q-item-section>
                            </q-item>

                            <q-item>
                                <q-item-section>
                                    <q-item-label caption class="q-mb-xs">Context</q-item-label>
                                     <q-chip 
                                        :color="conversation.type === 'bug' ? 'red-1' : (conversation.type === 'idea' ? 'orange-1' : 'blue-1')" 
                                        :text-color="conversation.type === 'bug' ? 'red' : (conversation.type === 'idea' ? 'orange' : 'blue')"
                                        size="sm"
                                        class="font-medium"
                                     >
                                        <q-icon :name="conversation.type === 'bug' ? 'bug_report' : (conversation.type === 'idea' ? 'lightbulb' : 'help')" size="xs" class="q-mr-xs" />
                                        {{ conversation.type.toUpperCase() }}
                                     </q-chip>
                                </q-item-section>
                            </q-item>

                             <q-item v-if="conversation.url">
                                <q-item-section>
                                    <q-item-label caption class="q-mb-xs">Page URL</q-item-label>
                                    <a :href="conversation.url" target="_blank" class="text-primary hover:underline flex items-center gap-1 break-all bg-blue-50 p-2 rounded border border-blue-100 text-xs">
                                        <q-icon name="link" size="xs" />
                                        {{ conversation.url }}
                                        <q-icon name="open_in_new" size="xs" class="ml-auto" />
                                    </a>
                                </q-item-section>
                            </q-item>

                            <q-item>
                                <q-item-section>
                                    <q-item-label caption class="q-mb-xs">Settings</q-item-label>
                                    <div class="text-xs text-grey-7">
                                        Mode: <span class="text-weight-medium capitalize">{{ conversation.mode }}</span>
                                    </div>
                                </q-item-section>
                            </q-item>
                        </q-list>
                        
                         <q-card-actions align="center" class="q-pa-md bg-grey-1 border-t border-gray-200">
                             <q-btn outline color="primary" label="Mark as Resolved" class="full-width" @click="resolveTicket" icon="check_circle" />
                        </q-card-actions>
                    </q-card>
                </div>
            </div>
        </div>

        <!-- Image Preview Dialog -->
        <q-dialog v-model="imageDialog" maximized>
            <q-card class="column">
                <q-card-section class="row items-center q-pb-none">
                    <div class="text-h6">Image Preview</div>
                    <q-space />
                    <q-btn icon="close" flat round dense v-close-popup />
                </q-card-section>

                <q-card-section class="col flex flex-center">
                    <q-img
                        v-if="selectedImage"
                        :src="selectedImage.data"
                        :alt="selectedImage.name"
                        contain
                        style="max-width: 90vw; max-height: 80vh;"
                    >
                        <div class="absolute-bottom text-caption text-center bg-black bg-opacity-50 text-white q-pa-sm">
                            {{ selectedImage.name }} ({{ formatFileSize(selectedImage.size) }})
                        </div>
                    </q-img>
                </q-card-section>
            </q-card>
        </q-dialog>
    </AdminLayout>
</template>

<style scoped>
/* Custom Scrollbar for chat area */
.scroll::-webkit-scrollbar {
    width: 6px;
}
.scroll::-webkit-scrollbar-track {
    background: transparent;
}
.scroll::-webkit-scrollbar-thumb {
    background-color: rgba(0,0,0,0.1);
    border-radius: 4px;
}
.scroll:hover::-webkit-scrollbar-thumb {
    background-color: rgba(0,0,0,0.2);
}
</style>
