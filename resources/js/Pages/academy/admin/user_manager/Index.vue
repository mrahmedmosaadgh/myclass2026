<template>
    <AppLayout title="User Management">
        <div class="q-pa-md">
            <q-card flat bordered>
                <q-card-section>
                    <div class="text-h6">Users & Roles Management</div>
                    <div class="text-subtitle2">Manage all users, roles, and permissions across the platform.</div>
                </q-card-section>

                <!-- Stat Cards -->
                <q-card-section class="row q-col-gutter-md">
                    <div v-for="(stat, key) in stats" :key="key" class="col-12 col-sm-6 col-md-3">
                        <q-card class="bg-blue-grey-1 text-center">
                            <q-card-section>
                                <div class="text-h5 text-bold">{{ stat.count }}</div>
                                <div class="text-subtitle2 text-grey-8">{{ stat.label }}</div>
                            </q-card-section>
                        </q-card>
                    </div>
                </q-card-section>

                <!-- Filters -->
                <q-card-section>
                    <UserFilters
                        :schools="schools"
                        :roles="allRoles"
                        v-model:search="searchQuery"
                        v-model:school="selectedSchool"
                        v-model:role="selectedRole"
                    />
                </q-card-section>

                <q-separator />

                <!-- Tabs -->
                <q-tabs v-model="activeTab" dense class="text-grey" active-color="primary" indicator-color="primary" align="justify" narrow-indicator>
                    <q-tab name="teachers" label="Teachers" />
                    <q-tab name="students" label="Students" />
                    <!-- <q-tab name="parents" label="Parents" /> -->
                    <q-tab name="others" label="Others" />
                </q-tabs>

                <q-separator />

                <q-tab-panels v-model="activeTab" animated>
                    <q-tab-panel v-for="tab in ['teachers', 'students', /*'parents',*/ 'others']" :key="tab" :name="tab">
                        <div v-if="loading" class="text-center q-pa-lg">
                            <q-spinner-dots color="primary" size="40px" />
                            <p>Loading users...</p>
                        </div>
                        <div v-else-if="filteredUsers[tab]?.length > 0" class="row q-col-gutter-md">
                            <div v-for="user in filteredUsers[tab]" :key="user.id" class="col-12 col-sm-6 col-md-4 col-lg-3">
                                <UserCard :user="user" :type="tab.slice(0, -1)" @manage-roles="openRoleManager" />
                            </div>
                        </div>
                        <div v-else class="text-center text-grey-7 q-pa-xl">
                            <q-icon name="person_off" size="4em" />
                            <p class="q-mt-md">No users found matching your criteria.</p>
                        </div>
                    </q-tab-panel>
                </q-tab-panels>
            </q-card>
        </div>

        <!-- Role Manager Dialog -->
        <RoleManager
            :show="showRoleManager"
            :user="userForRoles"
            :all-roles="allRoles"
            @close="showRoleManager = false"
            @save="handleRoleUpdate"
        />
    </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import UserCard from './components/UserCard.vue';
import UserFilters from './components/UserFilters.vue';
import RoleManager from './components/RoleManager.vue';
import { useQuasar } from 'quasar';

const props = defineProps({
    usersData: {
        type: Object,
        required: true,
    },
    schools: {
        type: Array,
        default: () => [],
    },
    roles: {
        type: Array,
        default: () => [],
    }
});

const $q = useQuasar();

// State
const loading = ref(false);
const activeTab = ref('teachers');
const searchQuery = ref('');
const selectedSchool = ref(null);
const selectedRole = ref(null);

const showRoleManager = ref(false);
const userForRoles = ref(null);

const allUsers = ref(props.usersData);
const allRoles = computed(() => props.roles.map(r => r.name));

// Computed Properties
const stats = computed(() => ({
    total: { count: allUsers.value.stats.total, label: 'Total Users' },
    teachers: { count: allUsers.value.stats.teachers, label: 'Teachers' },
    students: { count: allUsers.value.stats.students, label: 'Students' },
    // parents: { count: allUsers.value.stats.parents, label: 'Parents' },
}));

const filteredUsers = computed(() => {
    const query = searchQuery.value.toLowerCase();
    const schoolId = selectedSchool.value?.id;
    const roleName = selectedRole.value;

    const filterLogic = (user) => {
        const nameMatch = user.name.toLowerCase().includes(query);
        const emailMatch = user.email.toLowerCase().includes(query);
        const schoolMatch = !schoolId || user.school_id === schoolId;
        const roleMatch = !roleName || user.roles.some(r => r.name === roleName);
        return (nameMatch || emailMatch) && schoolMatch && roleMatch;
    };

    return {
        teachers: allUsers.value.teachers.filter(filterLogic),
        students: allUsers.value.students.filter(filterLogic),
        // parents: allUsers.value.parents.filter(filterLogic),
        others: allUsers.value.others.filter(filterLogic),
    };
});

// Methods
const openRoleManager = (user) => {
    userForRoles.value = user;
    showRoleManager.value = true;
};

const handleRoleUpdate = ({ userId, roles }) => {
    router.post(route('admin.users.roles.update', userId), { roles }, {
        preserveScroll: true,
        onSuccess: () => {
            $q.notify({
                type: 'positive',
                message: 'User roles updated successfully.',
                icon: 'check_circle'
            });
            showRoleManager.value = false;
            // We need to refresh the user data from the server
            refreshUsers();
        },
        onError: (errors) => {
            $q.notify({
                type: 'negative',
                message: 'Failed to update roles. Please check the errors.',
                caption: Object.values(errors).join(' ')
            });
        }
    });
};

const refreshUsers = () => {
    router.get(route('admin.users.index'), {}, {
        preserveState: true,
        preserveScroll: true,
        onStart: () => loading.value = true,
        onFinish: () => loading.value = false,
        onSuccess: (page) => {
            allUsers.value = page.props.usersData;
        }
    });
};

onMounted(() => {
    // This could be used to set initial filters from URL query params if needed
});

</script>

<style scoped>
.q-tab-panel {
    padding: 0;
    padding-top: 16px;
}
.q-card {
    transition: box-shadow 0.3s;
}
.q-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
</style>