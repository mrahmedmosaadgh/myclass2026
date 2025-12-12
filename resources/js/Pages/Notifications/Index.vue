<template>
    <AppLayout title="Notifications">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Notifications
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <q-card flat bordered class="q-pa-md">
                    <q-card-section>
                        <div class="flex justify-between items-center">
                            <div class="text-h5">Your Notifications</div>
                            <div class="q-gutter-sm">
                                <q-btn
                                    v-if="unreadCount > 0"
                                    @click="handleMarkAllAsRead"
                                    color="primary"
                                    :disable="loading"
                                    icon="done_all"
                                    label="Mark All as Read"
                                />
                                <q-btn
                                    @click="showSendForm = true"
                                    color="positive"
                                    icon="send"
                                    label="Send Test Notification"
                                />
                            </div>
                        </div>
                    </q-card-section>

                    <q-card-section>
                        <!-- Loading state -->
                        <div v-if="loading" class="flex justify-center q-py-md">
                            <q-spinner
                                color="primary"
                                size="3em"
                            />
                        </div>

                        <!-- Error state -->
                        <q-banner v-else-if="error" class="bg-negative text-white q-mb-md">
                            {{ error }}
                            <template v-slot:action>
                                <q-btn flat color="white" label="Retry" @click="fetchNotifications()" />
                            </template>
                        </q-banner>

                        <!-- Empty state -->
                        <div v-else-if="notifications.length === 0" class="text-center q-py-xl">
                            <q-icon name="notifications_off" size="4em" color="grey-5" />
                            <div class="text-h6 q-mt-md">No notifications</div>
                            <div class="text-grey-7">You don't have any notifications yet.</div>
                        </div>

                        <!-- Notifications list -->
                        <div v-else>
                            <q-list separator>
                                <q-item
                                    v-for="notification in notifications"
                                    :key="notification.id"
                                    :class="notification.read_at ? '' : 'bg-blue-1'"
                                    clickable
                                    v-ripple
                                >
                                    <q-item-section>
                                        <q-item-label class="text-weight-medium">{{ notification.data.title }}</q-item-label>
                                        <q-item-label caption>{{ notification.data.body }}</q-item-label>
                                        <q-item-label caption>{{ formatDate(notification.created_at) }}</q-item-label>
                                    </q-item-section>

                                    <q-item-section side>
                                        <div class="q-gutter-sm">
                                            <q-btn
                                                v-if="!notification.read_at"
                                                flat
                                                round
                                                color="primary"
                                                icon="done"
                                                @click.stop="handleMarkAsRead(notification.id)"
                                            >
                                                <q-tooltip>Mark as Read</q-tooltip>
                                            </q-btn>
                                            <q-btn
                                                flat
                                                round
                                                color="negative"
                                                icon="delete"
                                                @click.stop="confirmDelete(notification.id)"
                                            >
                                                <q-tooltip>Delete</q-tooltip>
                                            </q-btn>
                                        </div>
                                    </q-item-section>
                                </q-item>
                            </q-list>
                        </div>
                    </q-card-section>

                    <!-- Pagination -->
                    <q-card-section v-if="pagination.last_page > 1" class="flex justify-center">
                        <q-pagination
                            v-model="currentPage"
                            :max="pagination.last_page"
                            :max-pages="6"
                            boundary-links
                            direction-links
                            @update:model-value="handlePageChange"
                        />
                    </q-card-section>
                </q-card>
            </div>
        </div>

        <!-- Send Test Notification Dialog -->
        <q-dialog v-model="showSendForm" persistent>
            <q-card style="min-width: 350px">
                <q-card-section>
                    <div class="text-h6">Send Test Notification</div>
                </q-card-section>

                <q-card-section class="q-pt-none">
                    <q-form @submit="handleSendTestNotification">
                        <q-input
                            v-model="testNotification.title"
                            label="Title"
                            :rules="[val => !!val || 'Title is required']"
                            class="q-mb-md"
                        />

                        <q-input
                            v-model="testNotification.body"
                            label="Message"
                            type="textarea"
                            :rules="[val => !!val || 'Message is required']"
                            class="q-mb-md"
                        />

                        <q-select
                            v-model="testNotification.type"
                            :options="[
                                { label: 'Database Only', value: 'database' },
                                { label: 'Push Only', value: 'push' },
                                { label: 'Both Database & Push', value: 'both' }
                            ]"
                            label="Notification Type"
                            emit-value
                            map-options
                        />
                    </q-form>
                </q-card-section>

                <q-card-actions align="right">
                    <q-btn flat label="Cancel" color="negative" v-close-popup />
                    <q-btn
                        :loading="sendingTest"
                        color="positive"
                        label="Send"
                        @click="handleSendTestNotification"
                    />
                </q-card-actions>
            </q-card>
        </q-dialog>
    </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useNotificationStore } from '@/Stores/notificationStore';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useQuasar } from 'quasar';

// Props
const props = defineProps({
    notifications: {
        type: Object,
        default: () => ({
            data: [],
            current_page: 1,
            last_page: 1,
            per_page: 15,
            total: 0
        })
    }
});

// Quasar
const $q = useQuasar();

// Store
const notificationStore = useNotificationStore();

// State
const loading = ref(false);
const error = ref(null);
const notifications = ref(props.notifications.data || []);
const pagination = ref({
    current_page: props.notifications.current_page || 1,
    last_page: props.notifications.last_page || 1,
    per_page: props.notifications.per_page || 15,
    total: props.notifications.total || 0
});
const currentPage = ref(pagination.value.current_page);
const showSendForm = ref(false);
const sendingTest = ref(false);
const testNotification = ref({
    title: 'Test Notification',
    body: 'This is a test notification message.',
    type: 'both'
});

// Computed
const unreadCount = computed(() => {
    return notifications.value.filter(n => n.read_at === null).length;
});

// Methods
const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleString();
};

const fetchNotifications = async (page = 1) => {
    try {
        loading.value = true;
        error.value = null;

        const response = await notificationStore.fetchNotifications(page);

        // Handle the response format which might be different
        const data = response;

        // Check if the response has a data property (Laravel pagination format)
        if (data.data) {
            notifications.value = data.data;
            pagination.value = {
                current_page: data.current_page,
                last_page: data.last_page,
                per_page: data.per_page,
                total: data.total
            };
        } else {
            // If the response is directly the notifications array
            notifications.value = data;
            pagination.value = {
                current_page: 1,
                last_page: 1,
                per_page: data.length,
                total: data.length
            };
        }
    } catch (err) {
        error.value = 'Failed to load notifications';
        console.error(err);
    } finally {
        loading.value = false;
    }
};

const handleMarkAsRead = async (notificationId) => {
    try {
        await notificationStore.markAsRead(notificationId);

        // Update the notification in the local state
        const index = notifications.value.findIndex(n => n.id === notificationId);
        if (index !== -1) {
            notifications.value[index].read_at = new Date().toISOString();
        }

        $q.notify({
            type: 'positive',
            message: 'Notification marked as read',
            position: 'top-right'
        });
    } catch (err) {
        $q.notify({
            type: 'negative',
            message: 'Failed to mark notification as read',
            position: 'top-right'
        });
        console.error(err);
    }
};

const handleMarkAllAsRead = async () => {
    try {
        await notificationStore.markAllAsRead();

        // Update all notifications in the local state
        notifications.value = notifications.value.map(notification => ({
            ...notification,
            read_at: notification.read_at || new Date().toISOString()
        }));

        $q.notify({
            type: 'positive',
            message: 'All notifications marked as read',
            position: 'top-right'
        });
    } catch (err) {
        $q.notify({
            type: 'negative',
            message: 'Failed to mark all notifications as read',
            position: 'top-right'
        });
        console.error(err);
    }
};

const confirmDelete = (notificationId) => {
    $q.dialog({
        title: 'Confirm',
        message: 'Are you sure you want to delete this notification?',
        cancel: true,
        persistent: true
    }).onOk(() => {
        handleDeleteNotification(notificationId);
    });
};

const handleDeleteNotification = async (notificationId) => {
    try {
        await notificationStore.deleteNotification(notificationId);

        // Remove the notification from the local state
        notifications.value = notifications.value.filter(n => n.id !== notificationId);

        $q.notify({
            type: 'positive',
            message: 'Notification deleted',
            position: 'top-right'
        });
    } catch (err) {
        $q.notify({
            type: 'negative',
            message: 'Failed to delete notification',
            position: 'top-right'
        });
        console.error(err);
    }
};

const handlePageChange = (page) => {
    if (page < 1 || page > pagination.value.last_page) return;
    fetchNotifications(page);
};

const handleSendTestNotification = async () => {
    try {
        sendingTest.value = true;

        await notificationStore.sendTestNotification(
            testNotification.value.title,
            testNotification.value.body,
            testNotification.value.type
        );

        showSendForm.value = false;
        $q.notify({
            type: 'positive',
            message: 'Test notification sent successfully',
            position: 'top-right'
        });

        // Refresh notifications list if database notification was sent
        if (testNotification.value.type === 'database' || testNotification.value.type === 'both') {
            await fetchNotifications(1);
        }
    } catch (err) {
        $q.notify({
            type: 'negative',
            message: 'Failed to send test notification',
            position: 'top-right'
        });
        console.error(err);
    } finally {
        sendingTest.value = false;
    }
};

// Lifecycle hooks
onMounted(() => {
    // Initialize with props data if available, otherwise fetch
    if (!props.notifications.data || props.notifications.data.length === 0) {
        fetchNotifications();
    }
});
</script>
