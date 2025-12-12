/**
 * User Context Pinia Store for Offline-First Caching
 *
 * This store manages segmented user context with 7-day offline caching.
 * It integrates with the existing offline system and provides seamless
 * access to user profile, permissions, school, classroom, and schedule data.
 *
 * @author Education Management System
 * @version 1.0.0
 */

import { defineStore } from 'pinia';
import { ref, computed, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { db } from '@/offline/dexie.js';
import { useNetworkStore } from './networkStore.js';
import { userContextApi, userContextHelpers } from '@/api/userContextApi.js';

export const useUserContextStore = defineStore('userContext', () => {
    // ===== STATE =====
    const userProfile = ref(null);
    const userPermissions = ref(null);
    const userSchool = ref(null);
    const userClassroom = ref(null);
    const userSchedule = ref(null);

    const loading = ref({
        profile: false,
        permissions: false,
        school: false,
        classroom: false,
        schedule: false
    });

    const error = ref({
        profile: null,
        permissions: null,
        school: null,
        classroom: null,
        schedule: null
    });

    const cacheStatus = ref({
        profile: { cached: false, expires_at: null },
        permissions: { cached: false, expires_at: null },
        school: { cached: false, expires_at: null },
        classroom: { cached: false, expires_at: null },
        schedule: { cached: false, expires_at: null }
    });

    // ===== COMPUTED =====
    const currentUser = computed(() => {
        if (!userProfile.value) return null;

        return {
            id: userProfile.value.id,
            name: userProfile.value.name,
            email: userProfile.value.email,
            user_role: userProfile.value.user_role
        };
    });

    const isFullyLoaded = computed(() => {
        return userProfile.value && userPermissions.value &&
               userSchool.value && userClassroom.value;
    });

    const isAnyLoading = computed(() => {
        return Object.values(loading.value).some(l => l);
    });

    const hasAnyError = computed(() => {
        return Object.values(error.value).some(e => e !== null);
    });

    const cacheHealth = computed(() => {
        const segments = Object.keys(cacheStatus.value);
        const cached = segments.filter(s => cacheStatus.value[s].cached).length;
        const total = segments.length;

        return {
            percentage: Math.round((cached / total) * 100),
            cached,
            total,
            healthy: cached === total
        };
    });

    // ===== PRIVATE HELPERS =====
    const setLoading = (segment, value) => {
        loading.value[segment] = value;
    };

    const setError = (segment, value) => {
        error.value[segment] = value;
    };

    const updateCacheStatus = (segment, cached, expires_at = null) => {
        cacheStatus.value[segment] = { cached, expires_at };
    };

    // ===== CORE METHODS =====

    /**
     * Load user context segment from cache or server
     * @param {string} segment - Context segment name
     * @param {boolean} forceRefresh - Force refresh from server
     * @returns {Promise<Object|null>}
     */
    const loadSegment = async (segment, forceRefresh = false) => {
        const userId = usePage().props.auth?.user?.id;
        if (!userId) {
            console.warn('No user ID available for context loading');
            return null;
        }

        setLoading(segment, true);
        setError(segment, null);

        try {
            // Try cache first unless force refresh
            if (!forceRefresh) {
                const cached = await db.getUserContext(userId, segment);
                if (cached) {
                    console.log(`📦 Loaded ${segment} from cache`);
                    updateCacheStatus(segment, true, cached.expires_at);
                    return cached;
                }
            }

            // Check if online for API call
            const networkStore = useNetworkStore();

            if (networkStore.isOnline && forceRefresh) {
                // Load from API when online and force refresh
                try {
                    console.log(`🌐 Loading ${segment} from API`);
                    const apiResponse = await userContextHelpers.refreshSegment(segment);

                    if (apiResponse.success && apiResponse.data) {
                        // Store in cache
                        await db.storeUserContext(userId, segment, apiResponse.data);
                        updateCacheStatus(segment, true, apiResponse.meta?.expires_at);

                        return apiResponse.data;
                    }
                } catch (apiError) {
                    console.warn(`API call failed for ${segment}, falling back to Inertia props:`, apiError);
                }
            }

            // Load from Inertia props if available
            const pageProps = usePage().props;
            const contextData = pageProps.user_context?.[`user_${segment}`];

            if (contextData) {
                console.log(`🌐 Loaded ${segment} from Inertia props`);

                // Store in cache
                await db.storeUserContext(userId, segment, contextData);
                updateCacheStatus(segment, true, new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString());

                return contextData;
            }

            console.warn(`No data available for segment: ${segment}`);
            return null;

        } catch (err) {
            console.error(`Error loading ${segment}:`, err);
            setError(segment, `Failed to load ${segment}: ${err.message}`);
            return null;
        } finally {
            setLoading(segment, false);
        }
    };

    /**
     * Load user profile segment
     * @param {boolean} forceRefresh - Force refresh from server
     * @returns {Promise<void>}
     */
    const loadProfile = async (forceRefresh = false) => {
        const data = await loadSegment('profile', forceRefresh);
        if (data) {
            userProfile.value = data;
        }
    };

    /**
     * Load user permissions segment
     * @param {boolean} forceRefresh - Force refresh from server
     * @returns {Promise<void>}
     */
    const loadPermissions = async (forceRefresh = false) => {
        const data = await loadSegment('permissions', forceRefresh);
        if (data) {
            userPermissions.value = data;
        }
    };

    /**
     * Load user school segment
     * @param {boolean} forceRefresh - Force refresh from server
     * @returns {Promise<void>}
     */
    const loadSchool = async (forceRefresh = false) => {
        const data = await loadSegment('school', forceRefresh);
        if (data) {
            userSchool.value = data;
        }
    };

    /**
     * Load user classroom segment
     * @param {boolean} forceRefresh - Force refresh from server
     * @returns {Promise<void>}
     */
    const loadClassroom = async (forceRefresh = false) => {
        const data = await loadSegment('classroom', forceRefresh);
        if (data) {
            userClassroom.value = data;
        }
    };

    /**
     * Load user schedule segment
     * @param {boolean} forceRefresh - Force refresh from server
     * @returns {Promise<void>}
     */
    const loadSchedule = async (forceRefresh = false) => {
        const data = await loadSegment('schedule', forceRefresh);
        if (data) {
            userSchedule.value = data;
        }
    };

    /**
     * Load all user context segments
     * @param {boolean} forceRefresh - Force refresh from server
     * @returns {Promise<void>}
     */
    const loadAllSegments = async (forceRefresh = false) => {
        console.log('🔄 Loading all user context segments...');

        await Promise.allSettled([
            loadProfile(forceRefresh),
            loadPermissions(forceRefresh),
            loadSchool(forceRefresh),
            loadClassroom(forceRefresh),
            loadSchedule(forceRefresh)
        ]);

        console.log('✅ All user context segments loaded');
    };

    /**
     * Refresh specific segment from server
     * @param {string} segment - Segment to refresh
     * @returns {Promise<void>}
     */
    const refreshSegment = async (segment) => {
        console.log(`🔄 Refreshing ${segment} segment...`);

        switch (segment) {
            case 'profile':
                await loadProfile(true);
                break;
            case 'permissions':
                await loadPermissions(true);
                break;
            case 'school':
                await loadSchool(true);
                break;
            case 'classroom':
                await loadClassroom(true);
                break;
            case 'schedule':
                await loadSchedule(true);
                break;
            default:
                console.warn(`Unknown segment: ${segment}`);
        }
    };

    /**
     * Clear all cached user context
     * @returns {Promise<void>}
     */
    const clearCache = async () => {
        const userId = usePage().props.auth?.user?.id;
        if (!userId) return;

        try {
            await db.clearUserContextCache(userId);

            // Reset state
            userProfile.value = null;
            userPermissions.value = null;
            userSchool.value = null;
            userClassroom.value = null;
            userSchedule.value = null;

            // Reset cache status
            Object.keys(cacheStatus.value).forEach(segment => {
                updateCacheStatus(segment, false, null);
            });

            console.log('🗑️ User context cache cleared');
        } catch (err) {
            console.error('Error clearing cache:', err);
        }
    };

    /**
     * Get cache statistics
     * @returns {Promise<Object>}
     */
    const getCacheStats = async () => {
        const userId = usePage().props.auth?.user?.id;
        if (!userId) return null;

        try {
            // Get local cache stats
            const localStats = await db.getUserContextStats(userId);

            // Try to get server cache stats if online
            const networkStore = useNetworkStore();
            if (networkStore.isOnline) {
                try {
                    const serverStats = await userContextApi.getCacheStatus();

                    return {
                        local: localStats,
                        server: serverStats.data,
                        combined: {
                            ...localStats,
                            server_health: serverStats.data?.summary?.cache_health || 0
                        }
                    };
                } catch (apiError) {
                    console.warn('Failed to get server cache stats:', apiError);
                }
            }

            return {
                local: localStats,
                server: null,
                combined: localStats
            };
        } catch (err) {
            console.error('Error getting cache stats:', err);
            return null;
        }
    };

    // ===== BACKWARD COMPATIBILITY =====

    /**
     * Get user data in the legacy auth.user format for backward compatibility
     * @returns {Object|null} User data in legacy format
     */
    const getLegacyUserData = computed(() => {
        if (!isFullyLoaded.value) return null;

        return {
            id: userProfile.value?.id,
            name: userProfile.value?.name,
            email: userProfile.value?.email,
            user_role: userProfile.value?.user_role,
            roles: userPermissions.value?.roles || [],
            school: userSchool.value?.school || [],
            schools: userSchool.value?.schools || [],
            teacher: userClassroom.value?.teacher || null,
            classroom: userClassroom.value?.classroom || null,
            schedule: userSchedule.value?.schedule || null
        };
    });

    // ===== UTILITY METHODS =====

    /**
     * Check if a specific segment is expired
     * @param {string} segment - Segment name
     * @returns {boolean} True if expired
     */
    const isSegmentExpired = (segment) => {
        const status = cacheStatus.value[segment];
        if (!status.cached || !status.expires_at) return true;

        return new Date() > new Date(status.expires_at);
    };

    /**
     * Get segments that need refresh
     * @returns {Array<string>} Array of segment names that need refresh
     */
    const getExpiredSegments = () => {
        return Object.keys(cacheStatus.value).filter(segment =>
            isSegmentExpired(segment)
        );
    };

    /**
     * Auto-refresh expired segments
     * @returns {Promise<void>}
     */
    const refreshExpiredSegments = async () => {
        const expired = getExpiredSegments();
        if (expired.length === 0) return;

        console.log(`🔄 Auto-refreshing expired segments: ${expired.join(', ')}`);

        await Promise.allSettled(
            expired.map(segment => refreshSegment(segment))
        );
    };

    // ===== INITIALIZATION =====

    /**
     * Initialize the store
     * @returns {Promise<void>}
     */
    const initialize = async () => {
        console.log('🚀 Initializing User Context Store...');

        // Load initial data from Inertia props or cache
        await loadAllSegments();

        // Set up network monitoring for auto-refresh
        const networkStore = useNetworkStore();
        watch(() => networkStore.isOnline, (isOnline) => {
            if (isOnline) {
                console.log('🌐 Network back online, checking for context updates...');
                refreshExpiredSegments();
            }
        });

        // Set up periodic cache cleanup (every 30 minutes)
        setInterval(async () => {
            try {
                const cleared = await db.clearExpiredUserContext();
                if (cleared > 0) {
                    console.log(`🧹 Cleaned up ${cleared} expired cache entries`);
                }
            } catch (err) {
                console.error('Error during cache cleanup:', err);
            }
        }, 30 * 60 * 1000); // 30 minutes

        console.log('✅ User Context Store initialized');
    };

    return {
        // State
        userProfile,
        userPermissions,
        userSchool,
        userClassroom,
        userSchedule,
        loading,
        error,
        cacheStatus,

        // Computed
        currentUser,
        isFullyLoaded,
        isAnyLoading,
        hasAnyError,
        cacheHealth,
        getLegacyUserData,

        // Methods
        loadProfile,
        loadPermissions,
        loadSchool,
        loadClassroom,
        loadSchedule,
        loadAllSegments,
        refreshSegment,
        clearCache,
        getCacheStats,
        initialize,

        // Utility methods
        isSegmentExpired,
        getExpiredSegments,
        refreshExpiredSegments
    };
});
