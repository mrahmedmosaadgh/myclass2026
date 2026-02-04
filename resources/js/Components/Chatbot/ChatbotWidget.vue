<script setup>
import { ref, onMounted, computed, nextTick, watch } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';
import { useQuasar } from 'quasar';
import axios from 'axios';

const $q = useQuasar();

const props = defineProps({
    // Optional props for customizing appearance if needed
    customTrigger: {
        type: Boolean,
        default: false
    }
});

const page = usePage();
const authUser = computed(() => page.props.auth?.user);

const isOpen = ref(false);

const toggle = () => {
    isOpen.value = !isOpen.value;
};

defineExpose({ toggle, isOpen });
const conversation = ref(null);
const messages = ref([]);
const messageInput = ref('');
const loading = ref(false);
const startingChat = ref(false);
const sendingMessage = ref(false);
const intent = ref(null); // 'bug', 'idea', 'question'
const includeUrl = ref(true);
const attachedImages = ref([]);
const MAX_IMAGE_SIZE = 5 * 1024 * 1024; // 5MB
const MAX_IMAGES_PER_MESSAGE = 3;

// Guest Data
const guestName = ref('');
const guestEmail = ref('');
const showGuestForm = ref(false);
const guestDataSaved = ref(false);

const showStartScreen = computed(() => !conversation.value);
const showIntentSelection = computed(() => {
    // Show intents if user is logged in OR guest data is saved
    return authUser.value || guestDataSaved.value;
});

const greetingName = computed(() => {
    if (authUser.value) return authUser.value.name;
    if (guestName.value) return guestName.value;
    return '';
});

// Initialize
onMounted(() => {
    loadGuestData();
    fetchHistory();
    // Poll for new messages every 10 seconds if chat is open and active
    setInterval(() => {
        if (isOpen.value && conversation.value && conversation.value.status !== 'closed') {
           fetchHistory(true);
        }
    }, 10000);
});

const loadGuestData = () => {
    const storedName = localStorage.getItem('chatbot_guest_name');
    const storedEmail = localStorage.getItem('chatbot_guest_email');
    if (storedName) {
        guestName.value = storedName;
        guestEmail.value = storedEmail || '';
        guestDataSaved.value = true;
    } else {
        showGuestForm.value = true;
    }
};

const validateEmail = (email) => {
    if (!email) return true; // Empty email is valid
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
};

const saveGuestData = () => {
    if (!guestName.value.trim()) return; 
    
    // Validate email if provided
    if (guestEmail.value && !validateEmail(guestEmail.value)) {
        $q.notify({
            type: 'negative',
            message: 'Please enter a valid email address or leave it empty.'
        });
        return;
    }
    
    localStorage.setItem('chatbot_guest_name', guestName.value);
    if (guestEmail.value?.trim()) {
        localStorage.setItem('chatbot_guest_email', guestEmail.value.trim());
    }
    guestDataSaved.value = true;
    showGuestForm.value = false;
};

const editGuestData = () => {
    showGuestForm.value = true;
    guestDataSaved.value = false;
};

const getVirtualId = () => localStorage.getItem('chatbot_virtual_id');
const setVirtualId = (id) => {
    if (id) localStorage.setItem('chatbot_virtual_id', id);
};

const fetchHistory = async (silent = false) => {
    if (!silent) loading.value = true;
    try {
        const virtualId = getVirtualId();
        // Only fetch if we have user or virtual id
        if (!authUser.value && !virtualId) {
            loading.value = false;
            return;
        }

        // Check if route exists to avoid ziggy error
        if (typeof route !== 'function' || !route().has('chatbot.history')) {
             console.warn('Chatbot routes not available via Ziggy yet.');
             loading.value = false;
             return;
        }

        const response = await axios.get(route('chatbot.history'), {
            params: { virtual_id: virtualId }
        });

        if (response.data.conversation) {
            conversation.value = response.data.conversation;
            messages.value = response.data.conversation.messages || [];
            if (conversation.value.virtual_id) {
                setVirtualId(conversation.value.virtual_id);
            }
            scrollToBottom();
        }
    } catch (error) {
        console.error('Failed to fetch chat history:', error);
    } finally {
        if (!silent) loading.value = false;
    }
};

const startConversation = async (selectedIntent) => {
    if (!messageInput.value.trim() && attachedImages.value.length === 0) return;
    
    intent.value = selectedIntent;
    startingChat.value = true;

    try {
        let finalMessage = messageInput.value;
        
        // Prepend guest info for Admin visibility if guest
        if (!authUser.value && guestName.value) {
            finalMessage = `[Guest Name: ${guestName.value}]\n[Guest Email: ${guestEmail.value}]\n\n${finalMessage}`;
        }

        const messagePayload = prepareMessagePayload(finalMessage);
        // For authenticated users, never use guest data
        const payload = {
            type: intent.value,
            ...messagePayload,
            virtual_id: authUser.value ? null : getVirtualId(), // Auth users don't need virtual_id
            url: includeUrl.value ? window.location.href : null
        };
        
        // Only add email field for guests who provided one
        if (!authUser.value && guestEmail.value?.trim()) {
            payload.email = guestEmail.value.trim();
        }
        
        console.log('Final payload being sent:', payload);
        
        try {
            const response = await axios.post(route('chatbot.start'), payload);
            console.log('Response received:', response.data);
        } catch (error) {
            console.error('Request failed:', error.response?.data || error);
            throw error;
        }
        
        if (response.data.conversation) {
            conversation.value = response.data.conversation;
            messages.value = response.data.conversation.messages || [];
            if (conversation.value.virtual_id) {
                setVirtualId(conversation.value.virtual_id);
            }
            messageInput.value = '';
            attachedImages.value = [];
            scrollToBottom();
        }
    } catch (error) {
        console.error('Error starting conversation:', error);
        
        // Handle validation errors specifically
        if (error.response?.status === 422) {
            const errors = error.response.data?.errors || {};
            const errorMessages = Object.values(errors).flat();
            
            if (errorMessages.includes('The email field must be a valid email address.')) {
                $q.notify({
                    type: 'negative',
                    message: 'Please provide a valid email address or leave it empty.',
                    timeout: 4000
                });
            } else {
                $q.notify({
                    type: 'negative',
                    message: errorMessages[0] || 'Validation error occurred'
                });
            }
        } else {
            $q.notify({
                type: 'negative',
                message: 'Failed to start conversation. Please try again.'
            });
        }
    } finally {
        startingChat.value = false;
    }
};

const sendMessage = async () => {
    if ((!messageInput.value.trim() && attachedImages.value.length === 0) || sendingMessage.value) return;
    
    // If no conversation yet, treat as start (defaulting to question if not selected)
    if (!conversation.value) {
        await startConversation('question'); // Default to question if direct send
        return;
    }

    sendingMessage.value = true;
    const text = messageInput.value;
    const images = [...attachedImages.value];
    messageInput.value = ''; // Optimistic clear
    attachedImages.value = []; // Optimistic clear

    // Optimistic append
    messages.value.push({
        id: 'temp-' + Date.now(),
        sender_type: 'user',
        message: text,
        images: images,
        created_at: new Date().toISOString()
    });
    scrollToBottom();

    try {
        const messagePayload = prepareMessagePayload(text);
        const payload = {
            conversation_id: conversation.value.id,
            ...messagePayload,
            virtual_id: authUser.value ? null : getVirtualId() // Auth users don't need virtual_id
        };

        const response = await axios.post(route('chatbot.send'), payload);
        
        // Update last message with real data
        const realMessage = response.data.message;
        messages.value.pop(); // Remove temp
        messages.value.push(realMessage);
        
    } catch (error) {
        console.error('Error sending message:', error);
        
        // Handle validation errors specifically
        if (error.response?.status === 422) {
            const errors = error.response.data?.errors || {};
            const errorMessages = Object.values(errors).flat();
            
            if (errorMessages.includes('The email field must be a valid email address.')) {
                $q.notify({
                    type: 'negative',
                    message: 'Please enter a valid email address or leave it empty.',
                    timeout: 4000
                });
            } else {
                messages.value.pop(); 
                messageInput.value = text;
                attachedImages.value = images;
                $q.notify({
                    type: 'negative',
                    message: errorMessages[0] || 'Validation error occurred'
                });
            }
        } else {
            messages.value.pop(); 
            messageInput.value = text;
            attachedImages.value = images;
            $q.notify({
                type: 'negative',
                message: 'Failed to send message. Please try again.'
            });
        }
    } finally {
        sendingMessage.value = false;
        scrollToBottom();
    }
};

const scrollToBottom = () => {
    nextTick(() => {
        const container = document.getElementById('chat-messages-container');
        if (container) container.scrollTop = container.scrollHeight;
    });
};

const formatTime = (isoString) => {
    const date = new Date(isoString);
    return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
};

const handlePaste = async (event) => {
    const items = event.clipboardData?.items;
    if (!items) return;

    for (let i = 0; i < items.length; i++) {
        const item = items[i];
        if (item.type.indexOf('image') !== -1) {
            event.preventDefault();
            
            // Show success message for image paste
            $q.notify({
                type: 'positive',
                message: 'Image added successfully! You can paste up to 3 images.',
                timeout: 2000
            });
            
            const file = item.getAsFile();
            
            if (file.size > MAX_IMAGE_SIZE) {
                $q.notify({
                    type: 'negative',
                    message: 'Image size must be less than 5MB'
                });
                return;
            }

            if (attachedImages.value.length >= MAX_IMAGES_PER_MESSAGE) {
                $q.notify({
                    type: 'negative',
                    message: `Maximum ${MAX_IMAGES_PER_MESSAGE} images allowed per message`
                });
                return;
            }

            const reader = new FileReader();
            reader.onload = (e) => {
                attachedImages.value.push({
                    id: Date.now() + '-' + Math.random(),
                    url: e.target.result,
                    name: file.name || 'image.png',
                    size: file.size
                });
            };
            reader.readAsDataURL(file);
        }
    }
};

const removeImage = (imageId) => {
    attachedImages.value = attachedImages.value.filter(img => img.id !== imageId);
};

const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const prepareMessagePayload = (baseMessage) => {
    const payload = {
        message: baseMessage
    };
    
    // Only include images if there are any and backend supports them
    if (attachedImages.value.length > 0) {
        payload.images = attachedImages.value.map(img => ({
            data: img.url,
            name: img.name,
            size: img.size
        }));
        
        console.log('Preparing images for payload:', payload.images);
    }
    
    return payload;
};

const openImagePreview = (imageSrc) => {
    $q.dialog({
        component: 'QImg',
        src: imageSrc,
        style: 'max-width: 90vw; max-height: 90vh;'
    }).onDismiss(() => {
        // Dialog closed
    });
};

</script>

<template>
    <div class="fixed" style="bottom: 24px; right: 24px; z-index: 9999;">
        <!-- Chat Window -->
        <q-slide-transition>
            <q-card v-if="isOpen" class="q-mb-md" style="width: 90vw; max-width: 384px; max-height: 80vh; display: flex; flex-direction: column;">
                
                <!-- Header -->
                <q-card-section class="bg-primary text-white q-pa-md flex justify-between items-center flex-shrink-0">
                    <div class="flex items-center q-gutter-sm">
                        <q-avatar size="32px" color="white" text-color="primary" icon="robot" />
                        <div>
                            <div class="text-weight-bold text-subtitle2">Help Center</div>
                            <div class="text-caption text-blue-1">We reply typically in a few minutes</div>
                        </div>
                    </div>
                    <q-btn flat round dense icon="close" @click="isOpen = false" class="text-white" />
                </q-card-section>

                <!-- Body / Content -->
                <q-card-section class="q-pa-md bg-grey-1 flex-grow" style="overflow-y: auto; max-height: calc(80vh - 180px);" id="chat-messages-container">
                    
                    <!-- Loading State -->
                    <div v-if="loading" class="flex flex-center" style="height: 200px;">
                        <q-spinner color="primary" size="2em" />
                    </div>

                    <!-- Start Screen -->
                    <div v-else-if="showStartScreen" class="column q-gutter-md">
                        
                        <!-- Guest Identification Form -->
                        <q-card v-if="!authUser && (!guestDataSaved || showGuestForm)" flat bordered class="q-pa-md">
                            <div class="text-center q-mb-md">
                                <div class="text-weight-bold text-h6">Hi there! 👋</div>
                                <div class="text-caption text-grey-6">Please introduce yourself to start chatting.</div>
                            </div>
                            
                            <q-input
                                v-model="guestName"
                                label="Your Name"
                                outlined
                                dense
                                class="q-mb-sm"
                            />
                            <q-input
                                v-model="guestEmail"
                                label="Your Email (Optional)"
                                type="email"
                                outlined
                                dense
                                class="q-mb-md"
                                :rules="[val => !val || /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val) || 'Please enter a valid email address']"
                                lazy-rules
                            />
                            
                            <q-btn
                                @click="saveGuestData"
                                :disable="!guestName.trim()"
                                color="primary"
                                class="full-width"
                            >
                                Continue
                            </q-btn>

                            <div class="text-center text-caption text-grey-5 q-mt-sm">
                                Already have an account? <Link :href="route('login')" class="text-primary">Log in here</Link>
                            </div>
                        </q-card>

                        <!-- Intent Selection (Only if identified) -->
                        <template v-else>
                            <q-card flat bordered class="q-pa-md relative-position">
                                <q-btn
                                    v-if="!authUser"
                                    @click="editGuestData"
                                    flat
                                    dense
                                    round
                                    icon="edit"
                                    size="sm"
                                    class="absolute-top-right text-grey-5"
                                    title="Edit your info"
                                />
                                <div class="text-body1 text-weight-medium q-mb-sm">
                                    Hello! 👋 {{ greetingName }}
                                </div>
                                <div class="text-grey-6 text-body2">How can we help you today?</div>
                            </q-card>

                            <div class="column q-gutter-sm">
                                <q-card
                                    clickable
                                    @click="intent='question'"
                                    :class="intent === 'question' ? 'bg-blue-1' : 'bg-white'"
                                    flat
                                    bordered
                                    class="q-pa-md"
                                >
                                    <div class="flex items-center q-gutter-sm">
                                        <q-avatar size="24px" color="blue" icon="help" text-color="white" />
                                        <span class="text-body2 text-weight-medium">Ask a Question</span>
                                    </div>
                                </q-card>
                                
                                <q-card
                                    clickable
                                    @click="intent='bug'"
                                    :class="intent === 'bug' ? 'bg-red-1' : 'bg-white'"
                                    flat
                                    bordered
                                    class="q-pa-md"
                                >
                                    <div class="flex items-center q-gutter-sm">
                                        <q-avatar size="24px" color="red" icon="bug_report" text-color="white" />
                                        <span class="text-body2 text-weight-medium">Report a Problem</span>
                                    </div>
                                </q-card>
                                
                                <q-card
                                    clickable
                                    @click="intent='idea'"
                                    :class="intent === 'idea' ? 'bg-yellow-1' : 'bg-white'"
                                    flat
                                    bordered
                                    class="q-pa-md"
                                >
                                    <div class="flex items-center q-gutter-sm">
                                        <q-avatar size="24px" color="yellow" icon="lightbulb" text-color="white" />
                                        <span class="text-body2 text-weight-medium">Suggest an Idea</span>
                                    </div>
                                </q-card>
                            </div>

                            <div class="col">
                                <q-checkbox
                                    v-model="includeUrl"
                                    label="Include current page URL"
                                    size="sm"
                                    class="text-caption text-grey-6 q-mb-sm"
                                />

                                    <!-- Image Attachments -->
                                    <div v-if="attachedImages.length > 0" class="q-mb-sm">
                                        <div class="text-caption text-grey-6 q-mb-xs">
                                            Attached Images ({{ attachedImages.length }}/{{ MAX_IMAGES_PER_MESSAGE }})
                                        </div>
                                        <div class="row q-gutter-sm">
                                            <div v-for="image in attachedImages" :key="image.id" class="relative-position">
                                                <q-img
                                                    :src="image.url"
                                                    style="width: 80px; height: 80px;"
                                                    class="rounded-borders"
                                                >
                                                    <q-btn
                                                        round
                                                        dense
                                                        size="sm"
                                                        color="negative"
                                                        icon="close"
                                                        class="absolute-top-right"
                                                        @click="removeImage(image.id)"
                                                    />
                                                </q-img>
                                                <div class="text-caption text-center text-grey-6">
                                                    {{ formatFileSize(image.size) }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <q-input
                                        v-model="messageInput"
                                        type="textarea"
                                        placeholder="Type your message here... (Ctrl+V to paste images)"
                                        outlined
                                        rows="3"
                                        class="q-mb-sm"
                                        @paste="handlePaste"
                                        @keydown.enter.prevent="startConversation(intent || 'question')"
                                    >
                                        <template v-slot:prepend>
                                            <q-icon name="image" class="text-grey-6" />
                                        </template>
                                    </q-input>
                                <q-btn
                                    @click="startConversation(intent || 'question')"
                                    :disable="!messageInput.trim() || startingChat"
                                    color="primary"
                                    class="full-width"
                                    :loading="startingChat"
                                >
                                    <template v-if="startingChat">
                                        <q-spinner-dots size="sm" class="q-mr-sm" />
                                        Starting...
                                    </template>
                                    <template v-else>
                                        Start Chat
                                        <q-icon name="send" class="q-ml-sm" />
                                    </template>
                                </q-btn>
                            </div>
                        </template>
                    </div>

                    <!-- Conversation View -->
                    <div v-else class="column q-gutter-md">
                        <div class="text-center text-caption text-grey-5 q-my-sm">
                            {{ new Date(conversation.created_at).toLocaleDateString() }}
                        </div>

                            <div v-for="msg in messages" :key="msg.id" class="row items-end q-gutter-sm" :class="msg.sender_type === 'user' ? 'justify-end' : 'justify-start'">
                                
                                <q-avatar v-if="msg.sender_type !== 'user'" size="32px" color="blue-1" :icon="msg.sender_type === 'ai' ? 'smart_toy' : 'admin_panel_settings'" :text-color="msg.sender_type === 'ai' ? 'purple' : 'blue'" class="q-mr-sm" />

                                <div class="max-width-80">
                                    <q-chat-message
                                        :text="[msg.message || '']"
                                        :sent="msg.sender_type === 'user'"
                                        :bg-color="msg.sender_type === 'user' ? 'primary' : 'white'"
                                        :text-color="msg.sender_type === 'user' ? 'white' : 'dark'"
                                        :stamp="formatTime(msg.created_at)"
                                    />
                                    
                                    <!-- Display images in messages -->
                                    <div v-if="msg.images && msg.images.length > 0" class="row q-gutter-xs q-mt-xs" :class="msg.sender_type === 'user' ? 'justify-end' : 'justify-start'">
                                        <q-img
                                            v-for="(img, index) in msg.images"
                                            :key="index"
                                            :src="img.data || img.url"
                                            style="width: 120px; height: 120px;"
                                            class="rounded-borders cursor-pointer"
                                            @click="openImagePreview(img.data || img.url)"
                                        >
                                            <q-tooltip>
                                                {{ img.name || `Image ${index + 1}` }} ({{ formatFileSize(img.size || 0) }})
                                            </q-tooltip>
                                        </q-img>
                                    </div>
                                </div>
                            </div>

                        <div v-if="messages.length === 0" class="text-center text-grey-5 text-body2 q-mt-xl">
                            No messages yet.
                        </div>
                    </div>

                </q-card-section>

                <!-- Input Footer (Only detailed when chat is active) -->
                <q-card-section v-if="!showStartScreen" class="q-pa-sm bg-white border-top flex-shrink-0">
                    <div class="row items-center q-gutter-sm">
                        <!-- Image Attachments for Conversation View -->
                        <div v-if="attachedImages.length > 0" class="q-mb-sm">
                            <div class="row q-gutter-sm">
                                <div v-for="image in attachedImages" :key="image.id" class="relative-position">
                                    <q-img
                                        :src="image.url"
                                        style="width: 60px; height: 60px;"
                                        class="rounded-borders"
                                    >
                                        <q-btn
                                            round
                                            dense
                                            size="xs"
                                            color="negative"
                                            icon="close"
                                            class="absolute-top-right"
                                            @click="removeImage(image.id)"
                                        />
                                    </q-img>
                                </div>
                            </div>
                        </div>

                        <q-input
                            v-model="messageInput"
                            placeholder="Type a message... (Ctrl+V to paste images)"
                            outlined
                            dense
                            class="col"
                            @paste="handlePaste"
                            @keydown.enter="sendMessage"
                        >
                            <template v-slot:before>
                                <q-icon name="image" class="text-grey-6" />
                            </template>
                            <template v-slot:after>
                                <q-btn
                                    @click="sendMessage"
                                    round
                                    dense
                                    flat
                                    icon="send"
                                    color="primary"
                                    :disable="(!messageInput.trim() && attachedImages.length === 0) || sendingMessage"
                                    :loading="sendingMessage"
                                />
                            </template>
                        </q-input>
                    </div>
                    <div class="text-center text-caption text-grey-5 q-mt-xs">
                        {{ conversation?.status === 'replied' ? 'Admin replied' : 'We will reply shortly' }}
                    </div>
                </q-card-section>

            </q-card>
        </q-slide-transition>

        <!-- Toggle Button -->
        <q-btn
            v-if="!customTrigger"
            @click="isOpen = !isOpen"
            round
            color="primary"
            size="lg"
            class="shadow-6"
        >
            <q-icon :name="isOpen ? 'close' : 'chat'" size="md" />
            <q-badge v-if="!isOpen" color="red" floating class="q-mt-xs q-mr-xs" v-show="false" />
        </q-btn>
    </div>
</template>
