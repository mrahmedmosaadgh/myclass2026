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
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue';
import { useAppStore } from '@/Stores/AppStore';
import { useI18n } from 'vue-i18n';
const Ap = useAppStore();
// D:\my_projects\2025\laravel12\myclass7\resources\js\Pages\my_class\admin\Schedules\new\components\schools.vue
// resources\js\Layouts/comp/schools.vue
import { Sun, Moon, Globe } from 'lucide-vue-next';
// D:\my_projects\2025\laravel12\myclass7\resources\js\Layouts\comp\date_time.vue
defineProps({
    title: String,
});

const { t, locale } = useI18n();
const currentLocale = computed(() => locale.value);
const isDarkMode = ref(false);

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
    <div>
        <Head :title="title" />
        <Banner />
<date_time />
<!-- $page?.props?.auth?.user?.teacher:{{$page?.props?.auth?.user?.teacher }} -->
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            <!-- Navigation Bar -->
            <nav class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <div class="shrink-0 flex items-center">
                                <Link :href="getRoute('dashboard')">
                                    <ApplicationMark class="block h-9 w-auto" />
                                </Link>
                            </div>


                        </div>

                        <div class="flex items-center space-x-4">
                            <!-- Language Switcher -->
                            <div class="relative">
                                <LanguageSwitcher />
                            </div>
                            
                            <!-- Private Chat Link -->
                            <Link
                                v-if="usePage().props?.auth?.user && hasRoute('private-chat.index')"
                                :href="getRoute('private-chat.index')"
                                class="p-2 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors flex items-center"
                                :class="{ 'bg-gray-100 dark:bg-gray-700': isActiveRoute('private-chat.index') }"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-500 dark:text-gray-400 mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                                </svg>
                                <span class="text-sm font-medium">{{ t('common.chat') }}</span>
                            </Link>

                            <!-- Dark Mode Toggle -->
                            <button
                                @click="toggleDarkMode"
                                class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                :title="isDarkMode ? t('common.switchToLight') : t('common.switchToDark')"
                            >
                                <Sun v-if="isDarkMode" class="w-5 h-5 text-gray-500 dark:text-gray-400" />
                                <Moon v-else class="w-5 h-5 text-gray-500 dark:text-gray-400" />
                            </button>

                            <SidebarMenu v-if="usePage().props?.auth?.user" />
                            <NotificationBell v-if="usePage().props?.auth?.user"  :user-id="usePage().props?.auth?.user?.id" />
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Main Content -->
            <main>
                <div class="py-6">
                    <div class="px-2 flex justify-center">
                        <div class="p-0">
                            <slot name="header" />
                        </div>
                    </div>

                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <slot />
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Optional Messages Section -->
    <!--
    <div class="messages-section">
        <MessageList v-if="usePage().props?.auth?.user" :current-user-id="usePage().props?.auth?.user?.id" />
    </div>
    -->

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
/* Add any custom dark mode styles here if needed */
[dir="rtl"] .space-x-4 > * + * {
    margin-left: 0;
    margin-right: 1rem;
}

[dir="rtl"] .mr-1 {
    margin-right: 0;
    margin-left: 0.25rem;
}

/* Add more RTL-specific styles as needed */
</style>

