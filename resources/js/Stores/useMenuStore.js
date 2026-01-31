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
            const preview = options.preview === true;
            const previewSuffix = preview ? ':preview' : '';
            const roleKey = role ? role.toLowerCase() + previewSuffix : 'guest' + previewSuffix;

            // Check cache validity
            if (!forceIndex && !this.isStale(role) && this.menusByRole[roleKey]) {
                return this.getMenusForRole(role); // Use getter to enforce integrity check
            }

            this.loading = true;
            this.error = null;

            try {
                // We use v2=true as per our new backend spec
                const response = await axios.get('/api/navigation', {
                    params: {
                        role: role,
                        v2: true,
                        preview: preview ? 1 : undefined
                    }
                });

                const { data, version } = response.data;
                const transformedData = this.transformMenus(data);

                // Update state with integrity hash
                this.menusByRole[roleKey] = {
                    data: transformedData,
                    version: version,
                    timestamp: Date.now(),
                    integrity: simpleHash(JSON.stringify(transformedData))
                };

                this.offline = false;
                return this.menusByRole[roleKey].data;

            } catch (error) {
                console.error('Failed to fetch menus:', error);
                this.error = 'Failed to load menu structure';

                // If offline or network error, we rely on persistence (state remains unchanged)
                if (!window.navigator.onLine) {
                    this.offline = true;
                }

                // Return stale data if available as fallback (via getter for integrity)
                return this.getMenusForRole(role);
            } finally {
                this.loading = false;
            }
        },

        // Helper to transform API data to component expectations if needed
        transformMenus(apiMenus) {
            if (!Array.isArray(apiMenus)) return [];

            // Recursive function to map API fields to UI fields if different
            const mapMenu = (item) => ({
                id: item.id,
                title: item.label,
                label_ar: item.label_ar,
                module: item.module,
                icon: item.icon,
                to: item.route, // Map 'route' from DB to 'to' for Sidebar
                permission: item.permission,
                children: item.children ? item.children.map(mapMenu) : [],
                inactive: !item.is_active,
                // Add tooltip or badges from meta if needed
                tooltip: item.meta?.tooltip || item.label
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
