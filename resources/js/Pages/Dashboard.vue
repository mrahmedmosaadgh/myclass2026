<script setup>
import { computed, ref, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

// Import role-specific dashboards
import AdminDashboard from '@/Pages/Dashboard/Components/AdminDashboard.vue';
import SuperAdminDashboard from '@/Pages/Dashboard/Components/SuperAdminDashboard.vue';
import TeacherDashboard from '@/Pages/Dashboard/Components/TeacherDashboard.vue';
import StudentDashboard from '@/Pages/Dashboard/Components/StudentDashboard.vue';
import ParentDashboard from '@/Pages/Dashboard/Components/ParentDashboard.vue';
import DefaultDashboard from '@/Pages/Dashboard/Components/DefaultDashboard.vue';

// Using CustomEvent for role change communication between components

const user = computed(() => usePage().props.auth.user);
const userRoles = computed(() => user.value?.roles || []);

// Get the selected role with priority:
// 1. From SidebarMenu if changed (via custom event)
// 2. From localStorage if available
// 3. First available role as fallback
const selectedRole = ref(
    localStorage.getItem('selectedRole') ||
    (userRoles.value.length > 0 ? userRoles.value[0] : 'guest')
);

// Determine which dashboard component to display based on the selected role
const currentDashboard = computed(() => {
    // Make sure the selected role is one the user actually has
    if (!userRoles.value.includes(selectedRole.value)) {
        // If the selected role is not valid, default to the first role
        if (userRoles.value.length > 0) {
            selectedRole.value = userRoles.value[0];
        } else {
            return DefaultDashboard;
        }
    }

    switch (selectedRole.value) {
        case 'admin':
            return AdminDashboard;
        case 'superadmin':
            return SuperAdminDashboard;
        case 'teacher':
            return TeacherDashboard;
        case 'student':
            return StudentDashboard;
        case 'parent':
            return ParentDashboard;
        default:
            return DefaultDashboard;
    }
});

// Update dashboard when role changes
onMounted(() => {
    // Create a custom event to listen for role changes from SidebarMenu
    document.addEventListener('role-changed', (event) => {
        if (event.detail && event.detail.role) {
            selectedRole.value = event.detail.role;
        }
    });

    // Also listen for localStorage changes (in case role is changed in another tab)
    window.addEventListener('storage', (event) => {
        if (event.key === 'selectedRole') {
            selectedRole.value = event.newValue;
        }
    });
});
</script>

<template>
    <AppLayout title="Dashboard">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Dynamically render the appropriate dashboard component -->
                <component :is="currentDashboard" />
            </div>
        </div>
    </AppLayout>
</template>
