<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useQuasar } from 'quasar';

const props = defineProps({
    conversations: Object,
    statusFilter: String
});

const status = ref(props.statusFilter || 'new');

const columns = [
    { name: 'id', label: 'ID', field: 'id', sortable: true, align: 'left' },
    { name: 'user', label: 'User', field: row => row.user ? row.user.name : (row.email || 'Guest'), align: 'left' },
    { name: 'type', label: 'Type', field: 'type', sortable: true, align: 'left', format: val => val.toUpperCase() },
    { 
        name: 'message', 
        label: 'Last Message', 
        field: row => {
            const lastMsg = row.messages && row.messages.length > 0 ? row.messages[row.messages.length - 1] : null;
            let message = lastMsg ? lastMsg.message : '';
            if (message.length > 50) message = message.substring(0, 50) + '...';
            return message;
        }, 
        align: 'left',
        format: (val, row) => {
            const lastMsg = row.messages && row.messages.length > 0 ? row.messages[row.messages.length - 1] : null;
            let message = val;
            if (lastMsg && lastMsg.images && lastMsg.images.length > 0) {
                message += ` 📎${lastMsg.images.length}`;
            }
            return message;
        }
    },
    { name: 'status', label: 'Status', field: 'status', sortable: true, align: 'center' },
    { name: 'time', label: 'Last Activity', field: 'updated_at', sortable: true, align: 'right', format: val => new Date(val).toLocaleString() },
];

const getStatusColor = (status) => {
    switch(status) {
        case 'new': return 'negative';
        case 'replied': return 'positive';
        case 'waiting': return 'warning';
        case 'closed': return 'grey';
        default: return 'primary';
    }
};

const refresh = () => {
    router.get(route('admin.chatbot.index'), { status: status.value }, { preserveState: true });
};

const onRowClick = (evt, row) => {
    router.visit(route('admin.chatbot.admin.chatbot.show', row.id));
};

watch(status, () => {
    refresh();
});
</script>

<template>
    <AdminLayout>
        <div class="q-pa-md">
            <div class="row items-center justify-between q-mb-lg">
                <div>
                     <div class="text-h5 text-weight-bold">Chatbot Inbox & Support</div>
                     <div class="text-subtitle2 text-grey-7">Manage user inquiries, bug reports, and feedback</div>
                </div>
            </div>

            <q-card flat bordered>
                 <q-tabs
                    v-model="status"
                    dense
                    class="text-grey"
                    active-color="primary"
                    indicator-color="primary"
                    align="left"
                    narrow-indicator
                  >
                    <q-tab name="new" label="New" />
                    <q-tab name="replied" label="Replied" />
                    <q-tab name="waiting" label="Waiting" />
                    <q-tab name="closed" label="Closed" />
                    <q-tab name="all" label="All" />
                  </q-tabs>
                  
                  <q-separator />

                  <q-table
                    :rows="conversations.data"
                    :columns="columns"
                    row-key="id"
                    :pagination="{ rowsPerPage: 20 }"
                    @row-click="onRowClick"
                    flat
                    class="cursor-pointer"
                  >
                    <template v-slot:body-cell-user="props">
                        <q-td :props="props">
                            <div class="row items-center">
                                <q-avatar size="sm" color="blue-1" text-color="blue" class="q-mr-sm" v-if="!props.row.user">
                                    <q-icon name="person_outline" />
                                </q-avatar>
                                <q-avatar size="sm" :src="props.row.user.profile_photo_url" class="q-mr-sm" v-else />
                                <div>
                                    <div class="text-weight-medium">{{ props.row.user ? props.row.user.name : 'Guest' }}</div>
                                    <div class="text-caption text-grey" v-if="props.row.email">{{ props.row.email }}</div>
                                </div>
                            </div>
                        </q-td>
                    </template>

                    <template v-slot:body-cell-type="props">
                        <q-td :props="props">
                            <q-badge :color="props.value === 'bug' ? 'red' : (props.value === 'idea' ? 'orange' : 'blue')" :label="props.value" />
                        </q-td>
                    </template>

                    <template v-slot:body-cell-status="props">
                        <q-td :props="props">
                             <q-badge :color="getStatusColor(props.value)" :label="props.value" rounded />
                        </q-td>
                    </template>
                  </q-table>
                  
                  <div class="row justify-center q-pa-md" v-if="conversations.links">
                        <!-- Pagination controls could go here if using custom pagination -->
                  </div>
            </q-card>
        </div>
    </AdminLayout>
</template>

<style scoped>
.cursor-pointer tr {
    cursor: pointer;
}
</style>
