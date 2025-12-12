<template>
    <div class="flex flex-col h-full">

        <OpenAiChat />
        -----------------============------------- <br>
        <!-- Messages Container -->
        <div class="flex-1 overflow-y-auto p-4 space-y-4" ref="messagesContainer">
            <div v-for="(message, index) in messages"
                 :key="index"
                 :class="[
                     'rounded-lg p-4 max-w-3xl mx-auto',
                     message.role === 'user' ? 'bg-blue-50' : 'bg-gray-50'
                 ]">
                <div class="prose max-w-none" v-html="message.content"></div>
            </div>

            <div v-if="isLoading" class="animate-pulse bg-gray-50 rounded-lg p-4 max-w-3xl mx-auto">
                <div class="h-4 bg-gray-200 rounded w-3/4"></div>
            </div>
        </div>

        <!-- Input Form -->
        <div class="border-t p-4 bg-white">
            <form @submit.prevent="sendMessage" class="flex gap-2">
                <textarea
                    v-model="newMessage"
                    @keydown.enter.prevent="handleEnter"
                    ref="inputField"
                    :disabled="isLoading"
                    class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                    :class="{ 'opacity-50': isLoading }"
                    rows="2"
                    placeholder="Type your message..."
                ></textarea>

                <button
                    type="submit"
                    :disabled="isLoading || !newMessage.trim()"
                    class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    {{ isLoading ? 'Sending...' : 'Send' }}
                </button>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, nextTick } from 'vue';
import axios from 'axios';
import DOMPurify from 'dompurify';
import  OpenAiChat  from "./OpenAiChat.vue";

const messages = ref([]);
const newMessage = ref('');
const isLoading = ref(false);
const messagesContainer = ref(null);
const inputField = ref(null);

const scrollToBottom = async () => {
    await nextTick();
    if (messagesContainer.value) {
        messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
    }
};

const handleEnter = (e) => {
    if (e.shiftKey) return;
    sendMessage();
};

const sendMessage = async () => {
    const message = newMessage.value.trim();
    if (!message || isLoading.value) return;

    try {
        isLoading.value = true;
        messages.value.push({
            role: 'user',
            content: DOMPurify.sanitize(message)
        });

        newMessage.value = '';
        await scrollToBottom();

        const response = await axios.post('/api/ai-proxy', { text: message });

        if (response.data.output) {
            messages.value.push({
                role: 'assistant',
                content: DOMPurify.sanitize(response.data.output)
            });
            await scrollToBottom();
        }
    } catch (error) {
        console.error('API Error:', error);
        messages.value.push({
            role: 'assistant',
            content: 'Sorry, I encountered an error. Please try again.'
        });
    } finally {
        isLoading.value = false;
        inputField.value?.focus();
    }
};
</script>

<style scoped>
/* Add any additional styling here */
</style>


