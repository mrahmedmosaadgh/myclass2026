import { defineStore } from 'pinia';
import axios from 'axios';

export const useMenuStore = defineStore('menu', {
    state: () => ({
        // Stores menus by role: { 'admin': { data: [...], version: 'hash', timestamp: 123 } }
        menusByRole: {},
        loading: false,
        error: null,
        lastFetch: null,
        offline: false
    }),

    getters: {
        getMenusForRole: (state) => (role) => {
            const roleKey = role ? role.toLowerCase() : 'guest';
            const entry = state.menusByRole[roleKey];

            if (!entry) return [];

            // Integrity check
            const currentHash = simpleHash(JSON.stringify(entry.data));
            if (entry.integrity && entry.integrity !== currentHash) {
                console.warn(`Menu integrity check failed for role: ${role}. Clearing cache.`);
                delete state.menusByRole[roleKey];
                return [];
            }

            return entry.data;
        },

        isStale: (state) => (role) => {
            const roleKey = role ? role.toLowerCase() : 'guest';
            const entry = state.menusByRole[roleKey];
            if (!entry || !entry.timestamp) return true;

            // Cache for 1 hour (matching effective backend TTL recommendation for dynamism)
            const TTL = 3600 * 1000;
            return (Date.now() - entry.timestamp) > TTL;
        }
    },

    actions: {
        async fetchMenus(role, forceIndex = false, options = {}) {
            // New Config-Based Implementation
            const roleKey = role ? role.toLowerCase() : 'guest';

            // Check cache validity (simple in-memory check) & Version Check
            // We force a refresh if the version is NOT 'config-v1' (legacy cache)
            const cachedEntry = this.menusByRole[roleKey];
            const isLegacy = cachedEntry && cachedEntry.version !== 'config-v1';

            if (!forceIndex && !this.isStale(role) && cachedEntry && !isLegacy) {
                return this.getMenusForRole(role);
            }

            // Explicitly clear legacy cache if found
            if (isLegacy) {
                console.log('Detecting legacy menu cache. Clearing...');
                delete this.menusByRole[roleKey];
            }

            this.loading = true;
            this.error = null;

            try {
                // Determine if we should use the new config-based API
                // For now, we switch everyone to the new system
                // We pass the role query param so admins can view other roles
                const response = await axios.get('/api/menu', {
                    params: { role: role }
                });

                // The new API returns the menu directly as an array
                const data = response.data;
                const transformedData = this.transformMenus(data);

                // Update state
                this.menusByRole[roleKey] = {
                    data: transformedData,
                    version: 'config-v1', // Config-based doesn't have DB version hash yet
                    timestamp: Date.now(),
                    integrity: simpleHash(JSON.stringify(transformedData))
                };

                this.offline = false;
                return this.menusByRole[roleKey].data;

            } catch (error) {
                console.error('Failed to fetch menus:', error);
                this.error = 'Failed to load menu structure';

                if (!window.navigator.onLine) {
                    this.offline = true;
                }

                return this.getMenusForRole(role);
            } finally {
                this.loading = false;
            }
        },

        // Helper to transform API data to component expectations
        transformMenus(apiMenus) {
            if (!Array.isArray(apiMenus)) return [];

            const mapMenu = (item) => ({
                id: item.id,
                title: item.label, // New API returns translated label directly
                label_ar: item.label, // Fallback for sidebar logic (it checks label_ar) 
                icon: item.icon || 'help_outline',
                to: item.route || '#',
                permission: item.permission,
                children: item.children ? item.children.map(mapMenu) : [],
                inactive: false, // Config menus are always active by default
                tooltip: item.label
            });

            return apiMenus.map(mapMenu);
        }
    },

    persist: {
        // Persist only the menusByRole object to storage
        paths: ['menusByRole'],
        storage: localStorage, // or sessionStorage
    }
});

// Simple hash function for integrity checking (DJB2 variant)
function simpleHash(str) {
    let hash = 0;
    if (str.length === 0) return hash;
    for (let i = 0; i < str.length; i++) {
        const char = str.charCodeAt(i);
        hash = ((hash << 5) - hash) + char;
        hash = hash & hash; // Convert to 32bit integer
    }
    return hash;
}
