<template>
  <div class="users-roles-management">
    <!-- Header with Statistics -->
    <div class="header-section q-mb-lg">
      <div class="row items-center justify-between q-mb-md">
        <div class="col">
          <h3 class="text-h5 text-weight-bold q-ma-none">
            <q-icon name="people" class="q-mr-sm" color="primary" />
            Users & Roles Management
          </h3>
          <p class="text-subtitle2 text-grey-7 q-mt-xs">
            Manage user roles across different user types
          </p>
        </div>
        <div class="col-auto">
          <q-btn
            color="primary"
            icon="refresh"
            label="Refresh"
            @click="refreshData"
            outline
          />
        </div>
      </div>

      <!-- Statistics Cards -->
      <div class="row q-gutter-md q-mb-lg">
        <div class="col-12 col-sm-6 col-md-3">
          <q-card class="stat-card">
            <q-card-section class="text-center">
              <q-icon name="people" size="2rem" color="primary" />
              <div class="text-h5 text-weight-bold q-mt-sm">{{ enhancedUserData?.stats?.total || 0 }}</div>
              <div class="text-caption text-grey-7">Total Users</div>
            </q-card-section>
          </q-card>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
          <q-card class="stat-card">
            <q-card-section class="text-center">
              <q-icon name="school" size="2rem" color="green" />
              <div class="text-h5 text-weight-bold q-mt-sm">{{ enhancedUserData?.stats?.teachers || 0 }}</div>
              <div class="text-caption text-grey-7">Teachers</div>
            </q-card-section>
          </q-card>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
          <q-card class="stat-card">
            <q-card-section class="text-center">
              <q-icon name="person" size="2rem" color="blue" />
              <div class="text-h5 text-weight-bold q-mt-sm">{{ enhancedUserData?.stats?.students || 0 }}</div>
              <div class="text-caption text-grey-7">Students</div>
            </q-card-section>
          </q-card>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
          <q-card class="stat-card">
            <q-card-section class="text-center">
              <q-icon name="family_restroom" size="2rem" color="orange" />
              <div class="text-h5 text-weight-bold q-mt-sm">{{ enhancedUserData?.stats?.parents || 0 }}</div>
              <div class="text-caption text-grey-7">Parents</div>
            </q-card-section>
          </q-card>
        </div>
      </div>
    </div>

    <!-- Search and Filters -->
    <q-card class="search-card q-mb-lg">
      <q-card-section>
        <div class="row q-gutter-md items-end">
          <div class="col-12 col-md-4">
            <q-input
              v-model="searchQuery"
              placeholder="Search by name, email, subject, classroom..."
              outlined
              dense
              debounce="300"
            >
              <template v-slot:prepend>
                <q-icon name="search" />
              </template>
              <template v-slot:append>
                <q-btn
                  v-if="searchQuery"
                  icon="clear"
                  flat
                  round
                  dense
                  @click="clearSearch"
                />
              </template>
            </q-input>
          </div>
          <div class="col-12 col-md-3">
            <q-select
              v-model="selectedSchool"
              :options="schoolOptions"
              label="Filter by School"
              outlined
              dense
              clearable
              emit-value
              map-options
            />
          </div>
          <div class="col-12 col-md-3">
            <q-select
              v-model="selectedRole"
              :options="roleOptions"
              label="Filter by Role"
              outlined
              dense
              clearable
              emit-value
              map-options
            />
          </div>
          <div class="col-12 col-md-2">
            <q-btn
              color="secondary"
              icon="clear_all"
              label="Clear All"
              @click="clearAllFilters"
              outline
              dense
            />
          </div>
        </div>
      </q-card-section>
    </q-card>

    <!-- Tabs -->
    <q-card class="main-card">
      <q-tabs
        v-model="activeTab"
        dense
        class="text-grey"
        active-color="primary"
        indicator-color="primary"
        align="justify"
        narrow-indicator
      >
        <q-tab name="teachers" icon="school" label="Teachers" :badge="getTabBadge('teachers')" />
        <q-tab name="students" icon="person" label="Students" :badge="getTabBadge('students')" />
        <q-tab name="parents" icon="family_restroom" label="Parents" :badge="getTabBadge('parents')" />
        <q-tab name="others" icon="people_outline" label="Others" :badge="getTabBadge('others')" />
      </q-tabs>

      <q-separator />

      <q-tab-panels v-model="activeTab" animated>
        <!-- Teachers Tab -->
        <q-tab-panel name="teachers" class="q-pa-none">
          <UsersList
            :users="filteredTeachers"
            type="teacher"
            :loading="loading"
            @manage-roles="handleManageRoles"
          />
        </q-tab-panel>

        <!-- Students Tab -->
        <q-tab-panel name="students" class="q-pa-none">
          <UsersList
            :users="filteredStudents"
            type="student"
            :loading="loading"
            @manage-roles="handleManageRoles"
          />
        </q-tab-panel>

        <!-- Parents Tab -->
        <q-tab-panel name="parents" class="q-pa-none">
          <UsersList
            :users="filteredParents"
            type="parent"
            :loading="loading"
            @manage-roles="handleManageRoles"
          />
        </q-tab-panel>

        <!-- Others Tab -->
        <q-tab-panel name="others" class="q-pa-none">
          <UsersList
            :users="filteredOthers"
            type="other"
            :loading="loading"
            @manage-roles="handleManageRoles"
          />
        </q-tab-panel>
      </q-tab-panels>
    </q-card>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import UsersList from './UsersList.vue';

// Props
const props = defineProps({
  users: {
    type: Array,
    required: true
  },
  enhancedUserData: {
    type: Object,
    default: () => ({})
  }
});

// Emits
const emit = defineEmits(['manage-user-roles']);

// Reactive data
const activeTab = ref('teachers');
const searchQuery = ref('');
const selectedSchool = ref(null);
const selectedRole = ref(null);
const loading = ref(false);

// Computed properties for options
const schoolOptions = computed(() => {
  if (!props.enhancedUserData?.schools) return [];
  return props.enhancedUserData.schools.map(school => ({
    label: school.name,
    value: school.id
  }));
});

const roleOptions = computed(() => {
  const roles = new Set();

  // Collect all unique roles from all user types
  ['teachers', 'students', 'parents', 'others'].forEach(type => {
    props.enhancedUserData[type]?.forEach(user => {
      user.roles?.forEach(role => {
        roles.add(role.name);
      });
    });
  });

  return Array.from(roles).map(role => ({
    label: role,
    value: role
  }));
});

// Filter function for advanced search
const filterUsers = (users) => {
  if (!users) return [];

  return users.filter(user => {
    // Text search
    if (searchQuery.value) {
      const search = searchQuery.value.toLowerCase();
      const searchFields = [
        user.name,
        user.email,
        user.school_name,
        user.classroom_name,
        user.grade_name,
        user.stage_name,
        ...(user.subjects || []),
        ...(user.classrooms || []),
        ...(user.children_classrooms || [])
      ].filter(Boolean);

      const matches = searchFields.some(field =>
        field.toString().toLowerCase().includes(search)
      );

      if (!matches) return false;
    }

    // School filter
    if (selectedSchool.value && user.school_id !== selectedSchool.value) {
      return false;
    }

    // Role filter
    if (selectedRole.value) {
      const hasRole = user.roles?.some(role => role.name === selectedRole.value);
      if (!hasRole) return false;
    }

    return true;
  });
};

// Filtered data for each tab
const filteredTeachers = computed(() => filterUsers(props.enhancedUserData?.teachers));
const filteredStudents = computed(() => filterUsers(props.enhancedUserData?.students));
const filteredParents = computed(() => filterUsers(props.enhancedUserData?.parents));
const filteredOthers = computed(() => filterUsers(props.enhancedUserData?.others));

// Methods
const getTabBadge = (tabName) => {
  const counts = {
    teachers: filteredTeachers.value?.length || 0,
    students: filteredStudents.value?.length || 0,
    parents: filteredParents.value?.length || 0,
    others: filteredOthers.value?.length || 0
  };
  return counts[tabName] || 0;
};

const clearSearch = () => {
  searchQuery.value = '';
};

const clearAllFilters = () => {
  searchQuery.value = '';
  selectedSchool.value = null;
  selectedRole.value = null;
};

const refreshData = () => {
  loading.value = true;
  router.reload({ only: ['enhancedUserData'] });
  setTimeout(() => {
    loading.value = false;
  }, 1000);
};

const handleManageRoles = (user) => {
  emit('manage-user-roles', user);
};
</script>

<style scoped>
.users-roles-management {
  padding: 16px;
}

.stat-card {
  transition: all 0.3s ease;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.stat-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.search-card {
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.main-card {
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

/* Dark mode support */
.body--dark .stat-card,
.body--dark .search-card,
.body--dark .main-card {
  background: #2d2d2d;
  color: #f5f5f5;
}

/* Responsive adjustments */
@media (max-width: 600px) {
  .users-roles-management {
    padding: 8px;
  }

  .header-section .row {
    flex-direction: column;
    gap: 16px;
  }

  .search-card .row {
    flex-direction: column;
    gap: 16px;
  }
}
</style>
