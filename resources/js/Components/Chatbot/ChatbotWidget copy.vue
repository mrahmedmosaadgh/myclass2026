<script setup>
import { ref, onMounted, computed, nextTick, watch } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    // Optional props for customizing appearance if needed
});

const page = usePage();
const authUser = computed(() => page.props.auth?.user);

const isOpen = ref(false);
const conversation = ref(null);
const messages = ref([]);
const messageInput = ref('');
const loading = ref(false);
const startingChat = ref(false);
const sendingMessage = ref(false);
const intent = ref(null); // 'bug', 'idea', 'question'
const includeUrl = ref(true);

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

const saveGuestData = () => {
    if (!guestName.value.trim()) return; 
    localStorage.setItem('chatbot_guest_name', guestName.value);
    if (guestEmail.value) {
        localStorage.setItem('chatbot_guest_email', guestEmail.value);
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
    if (!messageInput.value.trim()) return;
    
    intent.value = selectedIntent;
    startingChat.value = true;

    try {
        let finalMessage = messageInput.value;
        
        // Prepend guest info for Admin visibility if guest
        if (!authUser.value && guestName.value) {
            finalMessage = `[Guest Name: ${guestName.value}]\n[Guest Email: ${guestEmail.value}]\n\n${finalMessage}`;
        }

        const payload = {
            type: intent.value,
            message: finalMessage,
            virtual_id: getVirtualId(),
            url: includeUrl.value ? window.location.href : null,
            email: authUser.value?.email || guestEmail.value
        };

        const response = await axios.post(route('chatbot.start'), payload);
        
        if (response.data.conversation) {
            conversation.value = response.data.conversation;
            messages.value = response.data.conversation.messages || [];
            if (conversation.value.virtual_id) {
                setVirtualId(conversation.value.virtual_id);
            }
            messageInput.value = '';
            scrollToBottom();
        }
    } catch (error) {
        console.error('Error starting conversation:', error);
    } finally {
        startingChat.value = false;
    }
};

const sendMessage = async () => {
    if (!messageInput.value.trim() || sendingMessage.value) return;
    
    // If no conversation yet, treat as start (defaulting to question if not selected)
    if (!conversation.value) {
        await startConversation('question'); // Default to question if direct send
        return;
    }

    sendingMessage.value = true;
    const text = messageInput.value;
    messageInput.value = ''; // Optimistic clear

    // Optimistic append
    messages.value.push({
        id: 'temp-' + Date.now(),
        sender_type: 'user',
        message: text,
        created_at: new Date().toISOString()
    });
    scrollToBottom();

    try {
        const payload = {
            conversation_id: conversation.value.id,
            message: text,
            virtual_id: getVirtualId()
        };

        const response = await axios.post(route('chatbot.send'), payload);
        
        // Update last message with real data
        const realMessage = response.data.message;
        messages.value.pop(); // Remove temp
        messages.value.push(realMessage);
        
    } catch (error) {
        console.error('Error sending message:', error);
        // Revert on error?
        messages.value.pop(); 
        messageInput.value = text;
        alert('Failed to send message. Please try again.');
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

</script>

<template>
    <div class="fixed bottom-6 right-6 z-50 flex flex-col items-end print:hidden">
        <!-- Chat Window -->
        <transition 
            enter-active-class="transform transition duration-300 ease-out"
            enter-from-class="opacity-0 translate-y-4 scale-95"
            enter-to-class="opacity-100 translate-y-0 scale-100"
            leave-active-class="transform transition duration-200 ease-in"
            leave-from-class="opacity-100 translate-y-0 scale-100"
            leave-to-class="opacity-0 translate-y-4 scale-95"
        >
            <div v-if="isOpen" class="mb-4 bg-white dark:bg-gray-800 w-[90vw] sm:w-96 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden flex flex-col max-h-[80vh] h-[500px]">
                
                <!-- Header -->
                <div class="bg-blue-600 p-4 text-white flex justify-between items-center shadow-md shrink-0">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-white text-blue-600 flex items-center justify-center font-bold">
                            <i class="fas fa-robot"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-sm">Help Center</h3>
                            <p class="text-xs text-blue-100 opacity-80">We reply typically in a few minutes</p>
                        </div>
                    </div>
                    <button @click="isOpen = false" class="text-white hover:bg-blue-700 p-1 rounded-full w-8 h-8 flex items-center justify-center">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <!-- Body / Content -->
                <div class="flex-1 overflow-y-auto p-4 bg-gray-50 dark:bg-gray-900" id="chat-messages-container">
                    
                    <!-- Loading State -->
                    <div v-if="loading" class="flex justify-center items-center h-full">
                        <i class="fas fa-spinner fa-spin text-blue-500 text-2xl"></i>
                    </div>

                    <!-- Start Screen -->
                    <div v-else-if="showStartScreen" class="h-full flex flex-col gap-4">
                        
                        <!-- Guest Identification Form -->
                        <div v-if="!authUser && (!guestDataSaved || showGuestForm)" class="flex flex-col gap-3 bg-white dark:bg-gray-800 p-4 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
                             <div class="text-center mb-1">
                                <h4 class="font-bold text-gray-800 dark:text-gray-200">Hi there! 👋</h4>
                                <p class="text-xs text-gray-500">Please introduce yourself to start chatting.</p>
                             </div>
                             
                             <input v-model="guestName" type="text" placeholder="Your Name" class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                             <input v-model="guestEmail" type="email" placeholder="Your Email (Optional)" class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                             
                             <button @click="saveGuestData" :disabled="!guestName.trim()" class="w-full bg-blue-600 text-white rounded-lg py-2 text-sm font-medium hover:bg-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                                Continue
                             </button>

                             <div class="text-center text-xs text-gray-400 mt-2">
                                Already have an account? <Link :href="route('login')" class="text-blue-600 hover:underline">Log in here</Link>
                             </div>
                        </div>

                        <!-- Intent Selection (Only if identified) -->
                        <template v-else>
                            <div class="bg-white dark:bg-gray-800 p-4 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 relative">
                                <button v-if="!authUser" @click="editGuestData" class="absolute top-2 right-2 text-xs text-gray-400 hover:text-blue-600" title="Edit your info">
                                    <i class="fas fa-pen"></i>
                                </button>
                                <p class="text-gray-800 dark:text-gray-200 font-medium mb-1">
                                    Hello! 👋 {{ greetingName }}
                                </p>
                                <p class="text-gray-600 dark:text-gray-400 text-sm">How can we help you today?</p>
                            </div>

                            <div class="space-y-2">
                                <button @click="intent='question'" :class="intent === 'question' ? 'bg-blue-100 border-blue-500 text-blue-700' : 'bg-white border-gray-200 hover:bg-gray-50 text-gray-700'" class="w-full text-left p-3 rounded-lg border text-sm font-medium transition-colors flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-full bg-blue-500 text-white flex items-center justify-center text-xs"><i class="fas fa-question"></i></span>
                                    Ask a Question
                                </button>
                                <button @click="intent='bug'" :class="intent === 'bug' ? 'bg-red-100 border-red-500 text-red-700' : 'bg-white border-gray-200 hover:bg-gray-50 text-gray-700'" class="w-full text-left p-3 rounded-lg border text-sm font-medium transition-colors flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-full bg-red-500 text-white flex items-center justify-center text-xs"><i class="fas fa-bug"></i></span>
                                    Report a Problem
                                </button>
                                <button @click="intent='idea'" :class="intent === 'idea' ? 'bg-yellow-100 border-yellow-500 text-yellow-700' : 'bg-white border-gray-200 hover:bg-gray-50 text-gray-700'" class="w-full text-left p-3 rounded-lg border text-sm font-medium transition-colors flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-full bg-yellow-500 text-white flex items-center justify-center text-xs"><i class="fas fa-lightbulb"></i></span>
                                    Suggest an Idea
                                </button>
                            </div>

                            <div class="mt-auto">
                                <label class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 mb-2">
                                    <input type="checkbox" v-model="includeUrl" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                    Include current page URL
                                </label>

                                <textarea 
                                    v-model="messageInput" 
                                    placeholder="Type your message here..." 
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500 text-sm resize-none mb-2"
                                    rows="3"
                                    @keydown.enter.prevent="startConversation(intent || 'question')"
                                ></textarea>
                                <button 
                                    @click="startConversation(intent || 'question')" 
                                    :disabled="!messageInput.trim() || startingChat"
                                    class="w-full bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-medium py-2 rounded-lg text-sm transition-colors flex justify-center items-center gap-2"
                                >
                                    <span v-if="startingChat"><i class="fas fa-spinner fa-spin"></i> Starting...</span>
                                    <span v-else>Start Chat <i class="fas fa-paper-plane ml-1"></i></span>
                                </button>
                            </div>
                        </template>
                    </div>

                    <!-- Conversation View -->
                    <div v-else class="flex flex-col gap-3 min-h-[50%]">
                        <div class="text-center text-xs text-gray-400 my-2">
                            <span>{{ new Date(conversation.created_at).toLocaleDateString() }}</span>
                        </div>

                        <div v-for="msg in messages" :key="msg.id" 
                            :class="['flex w-full', msg.sender_type === 'user' ? 'justify-end' : 'justify-start']">
                            
                            <div v-if="msg.sender_type !== 'user'" class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center mr-2 shrink-0 self-end mb-1">
                                <i :class="msg.sender_type === 'ai' ? 'fas fa-robot text-purple-500' : 'fas fa-user-shield text-blue-600'"></i>
                            </div>

                            <div :class="[
                                'max-w-[80%] rounded-2xl px-4 py-2 text-sm shadow-sm relative group',
                                msg.sender_type === 'user' 
                                    ? 'bg-blue-600 text-white rounded-br-none' 
                                    : 'bg-white dark:bg-gray-700 dark:text-gray-200 border border-gray-100 dark:border-gray-600 rounded-bl-none text-gray-800'
                            ]">
                                <p class="whitespace-pre-wrap">{{ msg.message }}</p>
                                <span :class="['text-[10px] absolute bottom-1 block opacity-0 group-hover:opacity-70 transition-opacity', msg.sender_type === 'user' ? 'right-2 text-blue-100' : 'left-2 text-gray-400']">
                                    {{ formatTime(msg.created_at) }}
                                </span>
                            </div>
                        </div>

                         <div v-if="messages.length === 0" class="text-center text-gray-400 text-sm mt-10">
                            No messages yet.
                        </div>
                    </div>

                </div>

                <!-- Input Footer (Only detailed when chat is active) -->
                <div v-if="!showStartScreen" class="p-3 bg-white dark:bg-gray-800 border-t border-gray-100 dark:border-gray-700 shrink-0">
                    <div class="relative">
                        <input 
                            v-model="messageInput" 
                            type="text" 
                            placeholder="Type a message..." 
                            class="w-full pl-4 pr-10 py-2.5 rounded-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-0 text-sm shadow-sm"
                            @keydown.enter="sendMessage"
                        >
                        <button 
                            @click="sendMessage"
                            :disabled="!messageInput.trim() || sendingMessage"
                            class="absolute right-1 top-1 p-1.5 rounded-full text-blue-600 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-gray-600 transition-colors disabled:opacity-50"
                        >
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                     <div class="text-[10px] text-center text-gray-400 mt-2">
                        {{ conversation?.status === 'replied' ? 'Admin replied' : 'We will reply shortly' }}
                    </div>
                </div>

            </div>
        </transition>

        <!-- Toggle Button -->
        <button 
            @click="isOpen = !isOpen"
            class="w-14 h-14 rounded-full bg-blue-600 hover:bg-blue-700 text-white shadow-lg flex items-center justify-center transition-all duration-300 transform hover:scale-105 group relative"
        >
            <span v-if="!isOpen" class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full border-2 border-white" v-show="false"></span> <!-- notification dot placeholder -->
            
            <i v-if="isOpen" class="fas fa-times text-xl"></i>
            <i v-else class="fas fa-comment-dots text-2xl group-hover:animate-pulse"></i>
        </button>
    </div>
</template>
