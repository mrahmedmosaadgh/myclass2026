<template>
    <AppLayout title="Component Testing">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Component Testing Page
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <my_tabs
                        v-model="selectedTab"
                        :options="tabs"
                        name="test-components"
                    />

                    <div class="mt-6">
                        <!-- Add your test components here -->
                        <div v-if="selectedTab === 'card2'">
<div class="p-4 flex w-full gap-4">

    <card2 />
    <card2 />
    <card2 />
</div>
                        </div>
                        <!-- resources\js\Pages\my_class\page_test\ExampleUsage_my_button1.vue -->
                        <div v-if="selectedTab === 'ExampleUsage_my_button1'">
                            <h3 class="text-lg font-medium mb-4">ExampleUsage_my_button1</h3>
                            <ExampleUsage_my_button1 />
                        </div>

                        <div v-if="selectedTab === 'component3'">
                            <h3 class="text-lg font-medium mb-4">Component 3</h3>
                            <!-- <TestComponent3 /> -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, watch } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import my_tabs from './my_tabs.vue';
import my_tabs2 from './my_tabs2.vue';
import card2 from './cards/card2.vue';
import ExampleUsage_my_button1 from './ExampleUsage_my_button1.vue';

// resources/js/Pages/my_class/page_test/cards/card2.vue

const selectedTab = ref('component1');

const tabs = [
    {
        label: 'card2',
        value: 'card2',
    },
    {
        label: 'ExampleUsage_my_button1',
        value: 'ExampleUsage_my_button1',
    },
    {
        label: 'Component 3',
        value: 'component3',
    },
];

// Watch for tab changes if needed
watch(selectedTab, (newValue) => {
    console.log('Selected tab changed:', newValue);
});
</script>


































