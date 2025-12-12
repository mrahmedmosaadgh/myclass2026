<template>
    <div class="p-4">
        <TabButtons
            v-model="selectedTab"
            :tabs="tabs"
            @change="handleTabChange"
        />
        <div class="mt-4">
            Selected Tab: {{ selectedTab }}
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import TabButtons from './TabButtons.vue';
// import { HomeIcon, UserIcon, CogIcon } from '@heroicons/vue/24/outline';

const selectedTab = ref('home');

const tabs = [
    {
        label: 'Rich Text',
        value: 'home',
        // icon: HomeIcon
    },
    {
        label: ' Text',
        value: 'profile',
        // icon: UserIcon
    },
 
];

const handleTabChange = (tab) => {
    console.log('Tab changed:', tab);
};
</script>
