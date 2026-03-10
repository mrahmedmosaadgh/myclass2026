<template>
    <Head title="Private Chat" />

            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Private Chat
            </h2>


        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex flex-col md:flex-row gap-6">
                            <!-- Recent Conversations -->
                            <div class="w-full md:w-1/3">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                                    Recent Conversations
                                </h3>

                                <div v-if="conversations.length === 0" class="text-gray-500 dark:text-gray-400">
                                    No recent conversations
                                </div>

                                <div v-else class="space-y-3">
                                    <div
                                        v-for="conversation in conversations"
                                        :key="conversation.id"
                                        class="border dark:border-gray-700 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer transition"
                                        @click="startChat(conversation.user.id)"
                                    >
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <img
                                                    :src="conversation.user.profile_photo_url"
                                                    alt="User avatar"
                                                    class="h-10 w-10 rounded-full"
                                                >
                                            </div>
                                            <div class="ml-4 flex-1">
                                                <div class="flex items-center justify-between">
                                                    <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                        {{ conversation.user.name }}
                                                    </h4>
                                                    <span v-if="conversation.unread_count > 0" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100">
                                                        {{ conversation.unread_count }}
                                                    </span>
                                                </div>
                                                <p v-if="conversation.latest_message" class="text-sm text-gray-500 dark:text-gray-400 truncate">
                                                    {{ conversation.latest_message.body }}
                                                </p>
                                                <p v-else class="text-sm text-gray-400 dark:text-gray-500 italic">
                                                    No messages yet
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- User List -->
                            <div class="w-full md:w-2/3 mt-6 md:mt-0">
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                        All Users
                                    </h3>
                                    <div class="relative">
                                        <input
                                            v-model="search"
                                            type="text"
                                            placeholder="Search users..."
                                            class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 shadow-sm"
                                        >
                                    </div>
                                </div>

                                <div v-if="filteredUsers.length === 0" class="text-center py-8">
                                    <p v-if="search" class="text-gray-500 dark:text-gray-400">
                                        No users found matching "{{ search }}"
                                    </p>
                                    <p v-else class="text-gray-500 dark:text-gray-400">
                                        No users available
                                    </p>
                                </div>

                                <div v-else class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div
                                        v-for="user in filteredUsers"
                                        :key="user.id"
                                        class="border dark:border-gray-700 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer transition"
                                        @click="startChat(user.id)"
                                    >
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <img
                                                    :src="user.profile_photo_url"
                                                    alt="User avatar"
                                                    class="h-10 w-10 rounded-full"
                                                >
                                            </div>
                                            <div class="ml-4">
                                                <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                    {{ user.name }}
                                                </h4>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                                    {{ user.email }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    users: Array,
    conversations: Array,
});

const search = ref('');

// Filter users based on search term
const filteredUsers = computed(() => {
    if (!search.value) return props.users;

    const searchTerm = search.value.toLowerCase();
    return props.users.filter(user =>
        user.name.toLowerCase().includes(searchTerm) ||
        user.email.toLowerCase().includes(searchTerm)
    );
});

// Start a chat with a user
const startChat = (userId) => {
    router.visit(route('private-chat.chat', userId));
};
</script>
