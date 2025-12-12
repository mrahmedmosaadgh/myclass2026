import { defineStore } from 'pinia';
import { ref } from 'vue';
import axios from 'axios';

export const useMessageStore = defineStore('messages', () => {
    const messages = ref([]);
    const loading = ref(false);
    const error = ref(null);

    const fetchLatestMessages = () => {
        loading.value = true;
        error.value = null;

        axios.get('/user-messages')
            .then(response => {
                messages.value = response.data.messages;
            })
            .catch(err => {
                console.error('Error fetching messages:', err);
                error.value = 'Failed to load messages';
                if (err.response?.status === 401) {
                    window.location.href = '/login';
                }
            })
            .finally(() => {
                loading.value = false;
            });
    };

    const addMessage = (message) => {
        // Add new message to the beginning of the array
        messages.value = [message, ...messages.value].slice(0, 10);
    };

    return {
        messages,
        loading,
        error,
        fetchLatestMessages,
        addMessage
    };
});



