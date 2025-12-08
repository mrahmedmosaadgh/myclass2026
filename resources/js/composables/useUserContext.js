/**
 * Vue Composable for User Context Management
 * 
 * This composable provides easy access to segmented user context with offline-first caching.
 * It automatically syncs with the Pinia store and provides reactive access to user data.
 * 
 * @author Education Management System
 * @version 1.0.0
 */

import { computed, watch, onMounted, onUnmounted } from 'vue';
import { useUserContextStore } from '@/Stores/userContextStore.js';
import { usePage } from '@inertiajs/vue3';

/**
 * Main composable for user context management
 * @param {Object} options - Configuration options
 * @returns {Object} User context interface
 */
export function useUserContext(options = {}) {
    // Configuration with defaults
    const config = {
        autoLoad: true,           // Auto-load context on mount
        watchForChanges: true,    // Watch for Inertia page changes
        refreshOnFocus: false,    // Refresh when window gains focus
        segments: ['profile', 'permissions', 'school', 'classroom', 'schedule'], // Which segments to load
        ...options
    };

    // Get the store instance
    const store = useUserContextStore();

    // ===== REACTIVE STATE =====
    
    // Individual segments (reactive)
    const profile = computed(() => store.userProfile);
    const permissions = computed(() => store.userPermissions);
    const school = computed(() => store.userSchool);
    const classroom = computed(() => store.userClassroom);
    const schedule = computed(() => store.userSchedule);

    // Loading states
    const loading = computed(() => store.loading);
    const isLoading = computed(() => store.isAnyLoading);
    const isFullyLoaded = computed(() => store.isFullyLoaded);

    // Error states
    const errors = computed(() => store.error);
    const hasErrors = computed(() => store.hasAnyError);

    // Cache information
    const cacheStatus = computed(() => store.cacheStatus);
    const cacheHealth = computed(() => store.cacheHealth);

    // ===== COMPUTED USER DATA =====

    // Current user (basic info)
    const user = computed(() => store.currentUser);

    // User roles and permissions
    const roles = computed(() => permissions.value?.roles || []);
    const hasRole = (role) => roles.value.includes(role);
    const isTeacher = computed(() => hasRole('teacher') || profile.value?.user_role === 'teacher');
    const isStudent = computed(() => hasRole('student') || profile.value?.user_role === 'student');
    const isAdmin = computed(() => hasRole('admin') || profile.value?.user_role === 'admin');

    // School information
    const schools = computed(() => school.value?.schools || []);
    const primarySchool = computed(() => schools.value[0] || null);
    const hasMultipleSchools = computed(() => schools.value.length > 1);

    // Teacher-specific data
    const teacherInfo = computed(() => classroom.value?.teacher || null);
    const teacherClassrooms = computed(() => {
        const classroomData = classroom.value?.classroom;
        return Array.isArray(classroomData) ? classroomData : (classroomData ? [classroomData] : []);
    });

    // Student-specific data
    const studentClassroom = computed(() => {
        const classroomData = classroom.value?.classroom;
        return Array.isArray(classroomData) ? classroomData[0] : classroomData;
    });

    // Schedule information
    const userSchedule = computed(() => schedule.value?.schedule || null);
    const hasSchedule = computed(() => userSchedule.value && userSchedule.value.length > 0);

    // ===== METHODS =====

    /**
     * Load specific segments
     * @param {Array<string>} segments - Segments to load
     * @param {boolean} forceRefresh - Force refresh from server
     * @returns {Promise<void>}
     */
    const loadSegments = async (segments = config.segments, forceRefresh = false) => {
        const loadPromises = segments.map(segment => {
            switch (segment) {
                case 'profile':
                    return store.loadProfile(forceRefresh);
                case 'permissions':
                    return store.loadPermissions(forceRefresh);
                case 'school':
                    return store.loadSchool(forceRefresh);
                case 'classroom':
                    return store.loadClassroom(forceRefresh);
                case 'schedule':
                    return store.loadSchedule(forceRefresh);
                default:
                    console.warn(`Unknown segment: ${segment}`);
                    return Promise.resolve();
            }
        });

        await Promise.allSettled(loadPromises);
    };

    /**
     * Refresh all configured segments
     * @returns {Promise<void>}
     */
    const refresh = () => loadSegments(config.segments, true);

    /**
     * Refresh specific segment
     * @param {string} segment - Segment to refresh
     * @returns {Promise<void>}
     */
    const refreshSegment = (segment) => store.refreshSegment(segment);

    /**
     * Check if user has specific permission/role
     * @param {string} permission - Permission or role to check
     * @returns {boolean}
     */
    const can = (permission) => {
        return roles.value.includes(permission) || 
               profile.value?.user_role === permission;
    };

    /**
     * Check if user belongs to specific school
     * @param {number} schoolId - School ID to check
     * @returns {boolean}
     */
    const belongsToSchool = (schoolId) => {
        return schools.value.some(s => s.id === schoolId);
    };

    /**
     * Get user data in legacy format for backward compatibility
     * @returns {Object|null}
     */
    const getLegacyFormat = () => store.getLegacyUserData;

    /**
     * Clear all cached data
     * @returns {Promise<void>}
     */
    const clearCache = () => store.clearCache();

    /**
     * Get cache statistics
     * @returns {Promise<Object>}
     */
    const getCacheStats = () => store.getCacheStats();

    // ===== LIFECYCLE HOOKS =====

    let focusListener = null;
    let pageWatcher = null;

    onMounted(async () => {
        // Auto-load if enabled
        if (config.autoLoad) {
            await loadSegments();
        }

        // Watch for Inertia page changes if enabled
        if (config.watchForChanges) {
            pageWatcher = watch(
                () => usePage().props.user_context,
                (newContext) => {
                    if (newContext) {
                        console.log('🔄 User context updated via Inertia, refreshing...');
                        loadSegments();
                    }
                },
                { deep: true }
            );
        }

        // Refresh on window focus if enabled
        if (config.refreshOnFocus) {
            focusListener = () => {
                if (!document.hidden) {
                    console.log('🔄 Window focused, checking for expired segments...');
                    store.refreshExpiredSegments();
                }
            };
            window.addEventListener('focus', focusListener);
        }
    });

    onUnmounted(() => {
        // Clean up watchers and listeners
        if (pageWatcher) {
            pageWatcher();
        }
        if (focusListener) {
            window.removeEventListener('focus', focusListener);
        }
    });

    // ===== RETURN INTERFACE =====

    return {
        // Raw segments
        profile,
        permissions,
        school,
        classroom,
        schedule,

        // Computed user data
        user,
        roles,
        schools,
        primarySchool,
        hasMultipleSchools,
        teacherInfo,
        teacherClassrooms,
        studentClassroom,
        userSchedule,
        hasSchedule,

        // User type checks
        isTeacher,
        isStudent,
        isAdmin,
        hasRole,
        can,
        belongsToSchool,

        // Loading and error states
        loading,
        isLoading,
        isFullyLoaded,
        errors,
        hasErrors,

        // Cache information
        cacheStatus,
        cacheHealth,

        // Methods
        loadSegments,
        refresh,
        refreshSegment,
        clearCache,
        getCacheStats,
        getLegacyFormat
    };
}

/**
 * Simplified composable for basic user info (most common use case)
 * @returns {Object} Basic user interface
 */
export function useUser() {
    const { user, isTeacher, isStudent, isAdmin, roles, can, isLoading } = useUserContext({
        segments: ['profile', 'permissions'],
        autoLoad: true
    });

    return {
        user,
        isTeacher,
        isStudent,
        isAdmin,
        roles,
        can,
        isLoading
    };
}

/**
 * Composable for teacher-specific data
 * @returns {Object} Teacher interface
 */
export function useTeacher() {
    const context = useUserContext({
        segments: ['profile', 'school', 'classroom', 'schedule'],
        autoLoad: true
    });

    return {
        ...context,
        // Teacher-specific aliases
        teacher: context.teacherInfo,
        classrooms: context.teacherClassrooms,
        schedule: context.userSchedule
    };
}

/**
 * Composable for student-specific data
 * @returns {Object} Student interface
 */
export function useStudent() {
    const context = useUserContext({
        segments: ['profile', 'school', 'classroom', 'schedule'],
        autoLoad: true
    });

    return {
        ...context,
        // Student-specific aliases
        classroom: context.studentClassroom,
        schedule: context.userSchedule
    };
}

export default useUserContext;
