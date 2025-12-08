<template>
    <AppLayout title="Project Manager">
        <div class="q-pa-md">
            <q-card flat bordered>

                
            </q-card>
        </div>

    </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';

import AppLayout from '@/Layouts/AppLayout.vue';


</script>

<style scoped>
.q-tab-panel {
    padding: 0;
    padding-top: 16px;
}
.q-card {
    transition: box-shadow 0.3s;
}
.q-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
</style>