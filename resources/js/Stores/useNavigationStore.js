import { defineStore } from 'pinia';
import axios from 'axios';

export const useNavigationStore = defineStore('navigation', {
    state: () => ({
        menuItems: [],
        isLoading: false,
        menuVersion: null,
    }),

    getters: {
        visibleItems: (state) => state.menuItems,
        hasItems: (state) => state.menuItems.length > 0,
    },

    actions: {
        async fetchMenu() {
            if (this.isLoading) return;
            this.isLoading = true;

            try {
                const response = await axios.get('/api/navigation/menu');
                this.menuItems = response.data.data;
                this.menuVersion = response.data.version;
            } catch (error) {
                console.error('Failed to load navigation menu:', error);
            } finally {
                this.isLoading = false;
            }
        },

        async refreshIfVersionChanged(serverVersion) {
            if (serverVersion && serverVersion !== this.menuVersion) {
                await this.fetchMenu();
            }
        }
    }
});
