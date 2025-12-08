<template>
  <Head :title="t('permissions.dashboard')" />
    <div>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
           {{ t('user.management_dashboard') }}
        </h2>

        <button
          @click="exportUsers"
          class="px-4 py-2 bg-green-600 text-white text-sm rounded-md hover:bg-green-700 flex items-center gap-2"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
          </svg>
          {{ t('user.export_users') }}
        </button>
      </div>
    </div>

    <div class="py-12" v-if="users">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
          <!-- Tabs -->
          <div class="border-b border-gray-200 mb-5">
            <nav class="-mb-px flex space-x-8">
              <button
                v-for="tab in translatedTabs"
                :key="tab.id"
                @click="activeTab = tab.id"
                :class="[
                  activeTab === tab.id
                    ? 'border-indigo-500 text-indigo-600'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                  'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm',
                ]"
              >
                {{ tab.name }}
              </button>
            </nav>
          </div>

          <UsersManagement
            v-if="activeTab === 'users-management'"
            :users="users"
            @edit-user="openUserModal"
            @delete-user="confirmDeleteUser"
            @restore-user="restoreUser"
          />

          <RolesManagement
            v-if="activeTab === 'roles'"
            :roles="roles"
            @create-role="openRoleModal()"
            @edit-role="openRoleModal"
            @delete-role="deleteRole"
          />

          <PermissionsManagement
            v-if="activeTab === 'permissions'"
            :permissions="permissions"
            @create-permission="openPermissionModal()"
            @edit-permission="openPermissionModal"
            @delete-permission="deletePermission"
          />

          <UsersRoles
            v-if="activeTab === 'users'"
            :users="users"
            :enhanced-user-data="enhancedUserData"
            @manage-user-roles="openUserRolesModal"
          />
        </div>
      </div>
    </div>

    <!-- Role Modal -->
    <DialogModal_7 :show="roleModalOpen" @close="roleModalOpen = false">
      <template #title>
        {{ editingRole ? t('role.edit') : t('role.create') }}
      </template>

      <template #content>
        <div>
          <div class="mb-4">
            <label for="role-name" class="block text-sm font-medium text-gray-700">{{ t('role.name') }}</label>
            <input
              type="text"
              id="role-name"
              v-model="roleForm.name"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            />
            <div v-if="roleForm.errors.name" class="text-red-500 text-sm mt-1">
              {{ roleForm.errors.name }}
            </div>
          </div>

          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">{{ t('permissions.label') }}</label>
            <div class="max-h-60 overflow-y-auto p-2 border rounded-md">
              <div v-for="permission in permissions" :key="permission.id" class="flex items-start mb-2">
                <div class="flex items-center h-5">
                  <input
                    :id="`permission-${permission.id}`"
                    type="checkbox"
                    :value="permission.id"
                    v-model="roleForm.permissionIds"
                    class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
                  />
                </div>
                <div class="ml-3 text-sm">
                  <label :for="`permission-${permission.id}`" class="font-medium text-gray-700">
                    {{ permission.name }}
                  </label>
                </div>
              </div>
            </div>
            <div v-if="roleForm.errors.permissionIds" class="text-red-500 text-sm mt-1">
              {{ roleForm.errors.permissionIds }}
            </div>
          </div>
        </div>
      </template>

      <template #footer>
        <SecondaryButton @click="roleModalOpen = false">
          {{ t('common.cancel') }}
        </SecondaryButton>

        <PrimaryButton
          class="ml-3"
          @click="saveRole"
          :disabled="roleForm.processing"
        >
          {{ editingRole ? t('common.update') : t('common.create') }}
        </PrimaryButton>
      </template>
    </DialogModal_7>

    <!-- Permission Modal -->
    <DialogModal_7 :show="permissionModalOpen" @close="permissionModalOpen = false">
      <template #title>
        {{ editingPermission ? t('permission.edit') : t('permission.create') }}
      </template>

      <template #content>
        <div>
          <div class="mb-4">
            <label for="permission-name" class="block text-sm font-medium text-gray-700">{{ t('permission.name') }}</label>
            <input
              type="text"
              name="permission-name"
              id="permission-name"
              v-model="permissionForm.name"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            />
            <div v-if="permissionForm.errors.name" class="text-red-500 text-sm mt-1">
              {{ permissionForm.errors.name }}
            </div>
          </div>
        </div>
      </template>

      <template #footer>
        <SecondaryButton @click="permissionModalOpen = false">
          {{ t('common.cancel') }}
        </SecondaryButton>

        <PrimaryButton
          class="ml-3"
          @click="savePermission"
          :disabled="permissionForm.processing"
        >
          {{ editingPermission ? t('common.update') : t('common.create') }}
        </PrimaryButton>
      </template>
    </DialogModal_7>

    <!-- User Roles Modal -->
    <DialogModal_7 :show="userRolesModalOpen" @close="userRolesModalOpen = false">
      <template #title>
        {{ t('user.manage_roles_for', { name: selectedUser ? selectedUser.name : '' }) }}
      </template>

      <template #content>
        <div>
          <div class="mb-4">
            <div class="max-h-60 overflow-y-auto p-2 border rounded-md">
              <div v-for="role in roles" :key="role.id" class="flex items-start mb-2">
                <div class="flex items-center h-5">
                  <input
                    :id="`user-role-${role.id}`"
                    type="checkbox"
                    :value="role.id"
                    v-model="userRolesForm.roleIds"
                    class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
                  />
                </div>
                <div class="ml-3 text-sm">
                  <label :for="`user-role-${role.id}`" class="font-medium text-gray-700">{{ role.name }}</label>
                </div>
              </div>
            </div>
          </div>
        </div>
      </template>

      <template #footer>
        <SecondaryButton @click="userRolesModalOpen = false">
          {{ t('common.cancel') }}
        </SecondaryButton>

        <PrimaryButton
          class="ml-3"
          @click="saveUserRoles"
        >
          {{ t('user.save_roles') }}
        </PrimaryButton>
      </template>
    </DialogModal_7>

    <!-- User Modal -->
    <DialogModal_7 :show="userModalOpen" @close="closeUserModal">
      <template #title>
        {{ editingUser ? t('user.edit') : t('user.create') }}
      </template>
      <template #content>
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">{{ t('user.name') }}</label>
            <input
              type="text"
              v-model="userForm.name"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            />
            <div v-if="userForm.errors.name" class="text-red-500 text-sm mt-1">
              {{ userForm.errors.name }}
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">{{ t('user.email') }}</label>
            <input
              type="email"
              v-model="userForm.email"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            />
            <div v-if="userForm.errors.email" class="text-red-500 text-sm mt-1">
              {{ userForm.errors.email }}
            </div>
          </div>
          <div v-if="!editingUser">
            <label class="block text-sm font-medium text-gray-700">{{ t('user.password') }}</label>
            <input
              type="password"
              v-model="userForm.password"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            />
            <div v-if="userForm.errors.password" class="text-red-500 text-sm mt-1">
              {{ userForm.errors.password }}
            </div>
          </div>
          <div v-if="!editingUser || userForm.password">
            <label class="block text-sm font-medium text-gray-700">{{ t('user.confirm_password') }}</label>
            <input
              type="password"
              v-model="userForm.password_confirmation"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">{{ t('user.roles') }}</label>
            <select
              multiple
              v-model="userForm.roles"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            >
              <option v-for="role in roles" :key="role.id" :value="role.id">
                {{ role.name }}
              </option>
            </select>
            <div v-if="userForm.errors.roles" class="text-red-500 text-sm mt-1">
              {{ userForm.errors.roles }}
            </div>
          </div>
        </div>
      </template>
      <template #footer>
        <button
          @click="closeUserModal"
          class="mr-3 px-4 py-2 text-gray-700 hover:text-gray-900"
        >
          {{ t('common.cancel') }}
        </button>
        <button
          @click="saveUser"
          class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
        >
          {{ editingUser ? t('common.update') : t('common.create') }}
        </button>
      </template>
    </DialogModal_7>

    <!-- Delete Confirmation Modal -->
    <DialogModal_7 :show="deleteModalOpen" @close="closeDeleteModal">
      <template #title>
        {{ t('user.delete') }}
      </template>
      <template #content>
        {{ t('user.confirm_delete') }}
      </template>
      <template #footer>
        <button
          @click="closeDeleteModal"
          class="mr-3 px-4 py-2 text-gray-700 hover:text-gray-900"
        >
          {{ t('common.cancel') }}
        </button>
        <button
          @click="deleteUser"
          class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
        >
          {{ t('user.delete') }}
        </button>
      </template>
    </DialogModal_7>

</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
// import AppLayout from '@/Layouts/AppLayout.vue';
import DialogModal_7 from './DialogModal_7.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { useForm } from '@inertiajs/vue3';
import UsersManagement from './Components/UsersManagement.vue';
import RolesManagement from './Components/RolesManagement.vue';
import PermissionsManagement from './Components/PermissionsManagement.vue';
import UsersRoles from './Components/UsersRoles.vue';
import { useI18n } from 'vue-i18n';

// Use the Composition API for i18n
const { t } = useI18n();

// Props from the controller
const props = defineProps({
  roles: Array,
  permissions: Array,
  users: Array,
  enhancedUserData: {
    type: Object,
    default: () => ({})
  }
});

// Tab state
const activeTab = ref('users-management');
const userSearch = ref('');
const userFilter = ref('all');
const userModalOpen = ref(false);
const deleteModalOpen = ref(false);
const editingUser = ref(null);
const userToDelete = ref(null);

const tabs = [
  { id: 'users-management', name: 'Users Management' },
  { id: 'roles', name: 'Roles' },
  { id: 'permissions', name: 'Permissions' },
  { id: 'users', name: 'Users & Roles' }
];

// Use t() from useI18n instead of $t
const translatedTabs = computed(() => [
  { id: 'users-management', name: t('tabs.users_management') },
  { id: 'roles', name: t('tabs.roles') },
  { id: 'permissions', name: t('tabs.permissions') },
  { id: 'users', name: t('tabs.users_roles') }
]);

const userForm = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '', // Add this field
  roles: []
});

// Add these refs after the existing refs
const roleModalOpen = ref(false);
const editingRole = ref(null);
const roleForm = useForm({
  name: '',
  permissionIds: []
});

// Add these after the roleForm definition
const permissionModalOpen = ref(false);
const editingPermission = ref(null);
const permissionForm = useForm({
  name: ''
});

// Add these for user roles modal
const userRolesModalOpen = ref(false);
const selectedUser = ref(null);
const userRolesForm = useForm({
  userId: '',
  roleIds: []
});

// Computed properties
const filteredUsers = computed(() => {
  let filtered = [...props.users];

  // Apply search
  if (userSearch.value) {
    const search = userSearch.value.toLowerCase();
    filtered = filtered.filter(user =>
      user.name.toLowerCase().includes(search) ||
      user.email.toLowerCase().includes(search)
    );
  }

  // Apply filter
  if (userFilter.value === 'active') {
    filtered = filtered.filter(user => !user.deleted_at);
  } else if (userFilter.value === 'deleted') {
    filtered = filtered.filter(user => user.deleted_at);
  }

  return filtered;
});

// Modal handlers
function openRoleModal(role = null) {
  editingRole.value = role;
  if (role) {
    roleForm.name = role.name;
    roleForm.permissionIds = role.permissions.map(p => p.id);
  } else {
    roleForm.reset();
  }
  roleModalOpen.value = true;
}

function openPermissionModal(permission = null) {
  if (permission) {
    editingPermission.value = permission;
    permissionForm.name = permission.name;
  } else {
    editingPermission.value = null;
    permissionForm.reset();
  }
  permissionModalOpen.value = true;
}

function openUserRolesModal(user) {
  selectedUser.value = user;
  userRolesForm.userId = user.id;
  userRolesForm.roleIds = user.roles.map(r => r.id);
  userRolesModalOpen.value = true;
}

// CRUD operations
function saveRole() {
  if (editingRole.value) {
    roleForm.put(`/admin/roles/${editingRole.value.id}`, {
      preserveScroll: true,
      onSuccess: () => {
        roleModalOpen.value = false;
        roleForm.reset();
      },
      onError: (errors) => {
        console.error(errors);
      }
    });
  } else {
    roleForm.post('/admin/roles', {
      preserveScroll: true,
      onSuccess: () => {
        roleModalOpen.value = false;
        roleForm.reset();
      },
      onError: (errors) => {
        console.error(errors);
      }
    });
  }
}

function deleteRole(role) {
  if (confirm(t('role.confirm_delete', { name: role.name }))) {
    router.delete(`/admin/roles/${role.id}`, {
      preserveScroll: true
    });
  }
}

function savePermission() {
  if (editingPermission.value) {
    permissionForm.put(`/admin/permissions/${editingPermission.value.id}`, {
      preserveScroll: true,
      onSuccess: () => {
        permissionModalOpen.value = false;
        permissionForm.reset();
      },
      onError: (errors) => {
        console.error(errors);
      }
    });
  } else {
    permissionForm.post('/admin/permissions', {
      preserveScroll: true,
      onSuccess: () => {
        permissionModalOpen.value = false;
        permissionForm.reset();
      },
      onError: (errors) => {
        console.error(errors);
      }
    });
  }
}

function deletePermission(permission) {
  if (confirm(t('permission.confirm_delete', { name: permission.name }))) {
    router.delete(`/admin/permissions/${permission.id}`, {
      preserveScroll: true
    });
  }
}

function saveUserRoles() {
  router.put(`/admin/users/${selectedUser.value.id}/roles`, userRolesForm, {
    preserveScroll: true,
    onSuccess: () => {
      userRolesModalOpen.value = false;
      userRolesForm.reset();
    }
  });
}

const openUserModal = (user = null) => {
  editingUser.value = user;
  if (user) {
    userForm.name = user.name;
    userForm.email = user.email;
    userForm.password = ''; // Clear password when editing
    userForm.password_confirmation = ''; // Clear password confirmation
    userForm.roles = user.roles.map(role => role.id);
  } else {
    userForm.reset();
  }
  userModalOpen.value = true;
};

const closeUserModal = () => {
  userModalOpen.value = false;
  editingUser.value = null;
  userForm.reset();
};

const saveUser = () => {
  if (editingUser.value) {
    // Remove password fields if they're empty
    if (!userForm.password) {
      delete userForm.password;
      delete userForm.password_confirmation;
    }

    userForm.put(route('admin.users.update', editingUser.value.id), {
      preserveScroll: true,
      onSuccess: () => {
        closeUserModal();
        // Optional: Show success message
      },
      onError: (errors) => {
        // Handle validation errors if needed
        console.error(errors);
      }
    });
  } else {
    userForm.post(route('admin.users.store'), {
      preserveScroll: true,
      onSuccess: () => {
        closeUserModal();
        // Optional: Show success message
      },
      onError: (errors) => {
        // Handle validation errors if needed
        console.error(errors);
      }
    });
  }
};

const confirmDeleteUser = (user) => {
  userToDelete.value = user;
  deleteModalOpen.value = true;
};

const closeDeleteModal = () => {
  deleteModalOpen.value = false;
  userToDelete.value = null;
};

const deleteUser = () => {
  if (userToDelete.value) {
    useForm({}).delete(route('admin.users.destroy', userToDelete.value.id), {
      onSuccess: () => closeDeleteModal()
    });
  }
};

const restoreUser = (user) => {
  useForm({}).put(route('admin.users.restore', user.id));
};

const exportUsers = () => {
  // Convert users to CSV format
  const headers = ['name', 'email', 'password', 'roles'];
  const usersData = props.users.map(user => ({
    name: user.name,
    email: user.email,
    password: '12345678', // Default password
    roles: user.roles.map(role => role.name).join(';')
  }));

  // Create CSV content
  const csvContent = [
    headers.join(','),
    ...usersData.map(user => [
      `"${user.name}"`,
      `"${user.email}"`,
      `"${user.password}"`,
      `"${user.roles}"`
    ].join(','))
  ].join('\n');

  // Create and download the file
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
  const link = document.createElement('a');
  const url = URL.createObjectURL(blob);
  link.setAttribute('href', url);
  link.setAttribute('download', 'users_export.csv');
  link.style.visibility = 'hidden';
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
};
</script>
























