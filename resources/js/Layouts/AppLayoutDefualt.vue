<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import ApplicationMark from '@/Components/ApplicationMark.vue';
import Banner from '@/Components/Banner.vue';
import NavLink from '@/Components/NavLink.vue';
import SidebarMenu from '@/Layouts/comp/SidebarMenu.vue';
import date_time from '@/Layouts/comp/date/date_time.vue';
import NotificationBell from '@/Components/NotificationBell.vue';
import MessageList from '@/Components/Messages/MessageList.vue';
import ChatNotificationListener from '@/Components/Chat/NotificationListener.vue';
import PrivateChatNotificationListener from '@/Components/PrivateChat/NotificationListener.vue';
import { useAppStore } from '@/Stores/AppStore';
import { useI18n } from 'vue-i18n';
const Ap = useAppStore();
import { Sun, Moon, Globe } from 'lucide-vue-next';

defineProps({
    title: String,
});

const { t, locale } = useI18n();
const currentLocale = computed(() => locale.value);
const isDarkMode = ref(false);
const leftDrawerOpen = ref(false);

// Handle Dark Mode
onMounted(() => {
    isDarkMode.value = localStorage.getItem('darkMode') === 'true';
    applyDarkMode();
});

const toggleDarkMode = () => {
    isDarkMode.value = !isDarkMode.value;
    localStorage.setItem('darkMode', isDarkMode.value);
    applyDarkMode();
};

const applyDarkMode = () => {
    document.documentElement.classList.toggle('dark', isDarkMode.value);
};

// Ziggy Route Helpers
const hasRoute = (name) => {
    try {
        return typeof window !== 'undefined'
            && typeof window.route === 'function'
            && window.route().has(name);
    } catch {
        return false;
    }
};

const getRoute = (name, params = {}) => {
    try {
        return hasRoute(name) ? window.route(name, params) : '#';
    } catch {
        return '#';
    }
};

const isActiveRoute = (name) => {
    try {
        return typeof window !== 'undefined'
            && typeof window.route === 'function'
            && window.route().current(name);
    } catch {
        return false;
    }
};
</script>

<template>
    <q-layout view="hHh LpR fFf">
        <Head :title="title" />
        <Banner />

        <!-- Header/Navbar -->
        <q-header elevated class="bg-white text-black dark:bg-gray-800 dark:text-white">
            <q-toolbar>
                <q-btn
                    v-if="usePage().props?.auth?.user"
                    flat
                    dense
                    round
                    icon="menu"
                    aria-label="Menu"
                    @click="leftDrawerOpen = !leftDrawerOpen"
                />

                <q-toolbar-title class="flex items-center">
                    <Link :href="getRoute('dashboard')" class="flex items-center">
                        <ApplicationMark class="block h-9 w-auto" />
                    </Link>
                </q-toolbar-title>

                <date_time class="q-mr-md" />


                
                <!-- Private Chat Link -->
                <q-btn
                    v-if="usePage().props?.auth?.user && hasRoute('private-chat.index')"
                    :to="getRoute('private-chat.index')"
                    flat
                    dense
                    :color="isActiveRoute('private-chat.index') ? 'primary' : 'grey'"
                    :class="{ 'bg-gray-100 dark:bg-gray-700': isActiveRoute('private-chat.index') }"
                    class="q-mr-sm"
                >
                    <q-icon name="chat" size="sm" class="q-mr-xs" />
                    <span class="text-sm">{{ t('common.chat') }}</span>
                </q-btn>

                <!-- Dark Mode Toggle -->
                <q-btn
                    flat
                    dense
                    round
                    @click="toggleDarkMode"
                    :icon="isDarkMode ? 'light_mode' : 'dark_mode'"
                    :title="isDarkMode ? t('common.switchToLight') : t('common.switchToDark')"
                    class="q-mr-sm"
                />

                <!-- Notification Bell -->
                <NotificationBell 
                    v-if="usePage().props?.auth?.user" 
                    :user-id="usePage().props?.auth?.user?.id" 
                    class="q-mr-sm"
                />

                <!-- Sidebar Menu (User Menu) -->
                <SidebarMenu v-if="usePage().props?.auth?.user" />
            </q-toolbar>
        </q-header>

        <!-- Left Drawer / Sidebar -->
        <q-drawer
            v-if="usePage().props?.auth?.user"
            v-model="leftDrawerOpen"
            show-if-above
            bordered
            :width="250"
            :breakpoint="700"
            class="bg-grey-1 dark:bg-gray-800"
        >
            <q-scroll-area class="fit">
                <q-list padding>
                    <!-- Sidebar content goes here -->
                    <q-item-label header>Navigation</q-item-label>
                    
                    <q-item clickable :to="getRoute('dashboard')" :active="isActiveRoute('dashboard')" active-class="bg-primary text-white">
                        <q-item-section avatar>
                            <q-icon name="dashboard" />
                        </q-item-section>
                        <q-item-section>Dashboard</q-item-section>
                    </q-item>
                    
                    <!-- Add more menu items as needed -->
                </q-list>
            </q-scroll-area>
        </q-drawer>

        <!-- Main Content -->
        <q-page-container>
            <q-page padding>
                <!-- Header Slot -->
                <div class="q-mb-md" v-if="$slots.header">
                    <slot name="header" />
                </div>

                <!-- Main Content Slot -->
                <slot />
            </q-page>
        </q-page-container>

        <!-- Footer -->
        <q-footer v-if="false" class="bg-white text-black dark:bg-gray-800 dark:text-white q-py-sm">
            <q-toolbar>
                <q-toolbar-title class="text-center text-caption">
                    © {{ new Date().getFullYear() }} MyClass. All rights reserved.
                </q-toolbar-title>
            </q-toolbar>
        </q-footer>
    </q-layout>

    <!-- Chat Notification Listener -->
    <ChatNotificationListener
        v-if="usePage().props?.auth?.user"
        :user-id="usePage().props?.auth?.user?.id"
    />

    <!-- Private Chat Notification Listener -->
    <PrivateChatNotificationListener
        v-if="usePage().props?.auth?.user"
        :user-id="usePage().props?.auth?.user?.id"
    />
</template>

<style>
/* RTL support */
[dir="rtl"] .q-mr-sm {
    margin-right: 0;
    margin-left: 8px;
}

[dir="rtl"] .q-mr-md {
    margin-right: 0;
    margin-left: 16px;
}

[dir="rtl"] .q-mr-xs {
    margin-right: 0;
    margin-left: 4px;
}

/* Dark mode styles */
.dark .q-header.bg-white {
    background: #1d1d1d !important;
}

.dark .q-drawer.bg-grey-1 {
    background: #2d2d2d !important;
}

.dark .q-item {
    color: #f5f5f5;
}

.dark .q-item-label {
    color: #e0e0e0;
}
</style>


