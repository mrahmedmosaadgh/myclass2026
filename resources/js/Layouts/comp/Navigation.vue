<template>
  <nav class="navigation" :class="{ 'navigation-collapsed': rail, 'dark-mode': isDarkMode }">
      <div class="navigation-header">
        <div class="user-info">
          <img src="/img/logo.png" alt="Logo" class="logo" onerror="this.src='/img/default-avatar.png'">
          <div v-if="!rail" class="user-details">
            <div class="user-name">{{ user ? user.name : 'Guest' }}</div>
            <div class="user-email" v-if="user">{{ user.email }}</div>
          </div>
        </div>
        <button class="toggle-btn" @click.stop="rail = !rail">
          <span class="toggle-icon">{{ rail ? '→' : '←' }}</span>
        </button>
      </div>

      <div class="divider"></div>

    <div class="role-switcher" v-if="canSwitchRoles && !rail">
      <label for="role-select">View As:</label>
      <select id="role-select" v-model="currentRole" class="role-select" @change="switchRole">
        <option v-for="role in availableRoles" :key="role" :value="role">
          {{ formatRoleName(role) }}
        </option>
      </select>
      </div>
    <div class="role-indicator" v-else-if="canSwitchRoles && rail">
      <span class="role-icon" :title="`Viewing as: ${formatRoleName(currentRole)}`">👁️</span>
    </div>

    <ul class="menu-list">
      <li v-for="(item, index) in displayedMenuItems" :key="index" class="menu-item">
        <Link :href="item.to" class="menu-link">
          <span class="menu-icon">{{ item.icon }}</span>
          <span v-if="!rail" class="menu-title">{{ item.title }}</span>
        </Link>
      </li>
    </ul>

    <div class="navigation-footer" v-if="user">
      <div class="divider"></div>
      <button class="logout-btn" @click="logout">
        <span class="menu-icon">🚪</span>
        <span v-if="!rail" class="menu-title">Logout</span>
      </button>
    </div>
  </nav>
  </template>

  <script>
import { ref, computed, watch } from 'vue';
import { usePage, router, Link } from '@inertiajs/vue3';
  import SidebarMenu from '../comp/MenuConfig/index.js';

  export default {
  components: {
    Link
  },

  props: {
    userRole: {
      type: String,
      default: 'guest'
    }
  },

  setup(props) {
      const page = usePage();
      const rail = ref(false);
      const user = computed(() => page.props.auth?.user);
    const isDarkMode = computed(() => document.querySelector('.app-layout')?.classList.contains('dark-mode'));

    const availableRoles = ['admin', 'superadmin', 'developer', 'teacher', 'student', 'parent', 'guest'];
    const currentRole = computed(() => {
      return user.value?.role ? user.value.role.toLowerCase() : 'guest';
    });

    const canSwitchRoles = computed(() => {
      const actualRole = user.value?.role?.toLowerCase();
      return actualRole === 'admin' || actualRole === 'superadmin';
    });

    const displayedMenuItems = computed(() => {
      return SidebarMenu[currentRole.value] || SidebarMenu.guest;
    });
    const formatRoleName = (role) => {
      return role.charAt(0).toUpperCase() + role.slice(1);
      };

    const switchRole = () => {
      console.log(`Switched view to ${currentRole.value} role`);
      };

    const logout = () => {
      router.post(route('logout'));
    };

    return {
      rail,
      user,
      isDarkMode,
      canSwitchRoles,
      availableRoles,
      currentRole,
      displayedMenuItems,
      formatRoleName,
      switchRole,
      logout
    };
  }
}
  </script>

  <style scoped>
  .navigation {
    width: 260px;
    min-height: 100vh;
    background-color: #2c3e50;
    color: white;
  transition: width 0.3s, background-color 0.3s;
    box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
    display: flex;
    flex-direction: column;
  }

.navigation.dark-mode {
  background-color: #1a1a1a;
  box-shadow: 2px 0 5px rgba(0, 0, 0, 0.3);
  }

.navigation-collapsed {
  width: 64px;
  }

.navigation-header {
  padding: 16px;
    display: flex;
    align-items: center;
  justify-content: space-between;
  }

.user-info {
    display: flex;
    align-items: center;
  }

.logo {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  margin-right: 10px;
  background-color: white;
  object-fit: cover;
  }

.user-details {
    overflow: hidden;
  }

.user-name {
  font-weight: bold;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  }

.user-email {
  font-size: 0.8em;
  opacity: 0.8;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.toggle-btn {
    background: none;
    border: none;
    cursor: pointer;
  color: white;
  padding: 5px;
  border-radius: 50%;
  width: 28px;
  height: 28px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background-color 0.2s;
  }

.toggle-btn:hover {
    background-color: rgba(255, 255, 255, 0.1);
  }

.toggle-icon {
  font-size: 16px;
}

.divider {
  height: 1px;
  background-color: rgba(255, 255, 255, 0.1);
  margin: 0;
}

.role-switcher {
  padding: 12px 16px;
  margin-top: 8px;
  display: flex;
  flex-direction: column;
}

.role-switcher label {
  font-size: 0.8em;
  opacity: 0.8;
  margin-bottom: 4px;
}

.role-select {
  background-color: rgba(255, 255, 255, 0.1);
  color: white;
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 4px;
  padding: 6px 8px;
  width: 100%;
  cursor: pointer;
}

.role-select:focus {
  outline: none;
  border-color: rgba(255, 255, 255, 0.4);
}

.role-indicator {
  display: flex;
  justify-content: center;
  padding: 12px 0;
}

.role-icon {
  font-size: 20px;
  cursor: help;
}

.menu-list {
  list-style: none;
  padding: 8px 0;
  margin: 0;
  flex: 1;
}

.menu-item {
  margin: 4px 0;
}

.menu-link {
  display: flex;
  align-items: center;
  padding: 12px 16px;
  color: white;
  text-decoration: none;
  transition: background-color 0.2s;
  border-radius: 4px;
  margin: 0 8px;
}

.menu-link:hover {
  background-color: rgba(255, 255, 255, 0.1);
}

.menu-icon {
  margin-right: 16px;
  font-size: 20px;
  width: 24px;
  text-align: center;
}

.navigation-collapsed .menu-icon {
  margin-right: 0;
}

.menu-title {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.navigation-footer {
  margin-top: auto;
  padding: 8px;
}

.logout-btn {
  display: flex;
  align-items: center;
  width: 100%;
  padding: 12px 16px;
  background: none;
  border: none;
  color: white;
  cursor: pointer;
  text-align: left;
  transition: background-color 0.2s;
  border-radius: 4px;
}

.logout-btn:hover {
  background-color: rgba(255, 255, 255, 0.1);
}

@media (max-width: 768px) {
  .navigation {
    width: 220px;
  }

  .navigation-collapsed {
    width: 64px;
  }
}
  </style>
