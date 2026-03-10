<template>
    <AppLayout title="Chat">ffffffffffffffff
        <!-- <coderunner /> -->
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <ChatRoom
                    room-id="general"
                    room-name="General Chat"
                    :current-user="currentUser"
                />
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import ChatRoom from '@/Components/Chat/ChatRoom.vue';
import { ref, onMounted, computed } from 'vue';
import { database } from '@/firebase/init';
import { usePage } from '@inertiajs/vue3';
// import coderunner from './coderunner.vue';

// Get the authenticated user from Inertia page props
const page = usePage();
const authUser = computed(() => page.props.auth?.user);

// Create a currentUser object with the authenticated user's information
const currentUser = ref({
    id: authUser.value?.id?.toString() || 'guest',
    name: authUser.value?.name || 'Guest User'
});

onMounted(() => {
    // Add any initialization code here if needed
});
</script>

