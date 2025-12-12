import { defineStore } from 'pinia';
import { ref } from 'vue';
import api from '@/api';

export const useUserStore = defineStore('user', () => {
    const currentUser = ref(null);
    const loading = ref(false);
    const error = ref(null);

    const fetchCurrentUser = async () => {
        try {
            loading.value = true;
            error.value = null;
            const response = await api.get('/api/user');
            currentUser.value = response.data;
        } catch (err) {
            console.error('Error fetching current user:', err);
            error.value = 'Failed to load user data';
        } finally {
            loading.value = false;
        }
    };

    return {
        currentUser,
        loading,
        error,
        fetchCurrentUser
    };
});