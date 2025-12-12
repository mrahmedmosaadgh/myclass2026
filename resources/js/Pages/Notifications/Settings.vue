<template>
    <AppLayout title="Notifications">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Push Notifications
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <div class="text-gray-700 mb-4">
                        <h3 class="font-semibold text-lg mb-2">Push Notification Settings</h3>
                        <p class="mb-4">Enable push notifications to stay updated with important events and messages.</p>
                    </div>

                    <PushNotificationSubscribe />

                    <div class="mt-6 text-sm text-gray-500">
                        <p>Once enabled, you'll receive notifications even when the browser is closed.</p>
                        <p class="mt-2">You can disable notifications at any time through your browser settings or by clicking the button above.</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import PushNotificationSubscribe from '@/Components/PushNotificationSubscribe.vue';
</script>
