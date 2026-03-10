import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export function useRole() {
    const page = usePage();

    // Assumes your backend passes 'current_role' or you derive it from user permissions
    const currentRole = computed(() => page.props.auth?.current_role || 'teacher');

    const hasRole = (role) => {
        if (Array.isArray(role)) {
            return role.includes(currentRole.value);
        }
        return currentRole.value === role;
    };

    const isSuperSystem = computed(() => hasRole('super-system'));
    const isSystemAdmin = computed(() => hasRole('system-admin'));
    const isSchoolAdmin = computed(() => hasRole('school-admin'));
    const isTeacher = computed(() => hasRole('teacher'));
    const isStudent = computed(() => hasRole('student'));
    const isParent = computed(() => hasRole('parent'));
    const isHr = computed(() => hasRole('hr'));

    return {
        currentRole,
        hasRole,
        isSuperSystem,
        isSystemAdmin,
        isSchoolAdmin,
        isTeacher,
        isStudent,
        isParent,
        isHr
    };
}
