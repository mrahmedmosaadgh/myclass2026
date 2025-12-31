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

        /**
         * Get menu items by module
         */
        itemsByModule: (state) => (module) => {
            return state.menuItems.filter(item => item.module === module);
        },

        /**
         * Flatten menu structure for breadcrumbs
         */
        flatMenuItems: (state) => {
            const flatten = (items, parent = null) => {
                let result = [];
                items.forEach(item => {
                    result.push({ ...item, parent });
                    if (item.children && item.children.length > 0) {
                        result = result.concat(flatten(item.children, item));
                    }
                });
                return result;
            };
            return flatten(state.menuItems);
        },
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
        },

        /**
         * Force refresh menu cache
         */
        async invalidateCache() {
            this.menuVersion = null;
            try {
                // Optionally call backend to clear server-side cache if endpoint exists
                // await axios.post('/api/admin/menus/clear-cache');
            } catch (error) {
                console.warn('Failed to clear server cache:', error);
            }
            // Always fetch fresh menu
            await this.fetchMenu();
        }
    }
});
