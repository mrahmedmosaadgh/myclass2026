<template>
  <Head title="Micro Component Test" />

  <div class=" p-6">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Micro Component Test</h1>
        
        <!-- Component Switcher -->
        <q-btn-dropdown
            color="primary"
            :label="currentViewLabel"
            icon="layers"
            outline
            rounded
            class="text-blue-900"
        >
            <q-list>
                <q-item 
                    v-for="(component, key) in Components" 
                    :key="key"
                    clickable 
                    v-close-popup 
                    @click="currentView = key"
                >
                    <q-item-section avatar>
                        <span class="text-xl">{{ getComponentIcon(key) }}</span>
                    </q-item-section>
                    <q-item-section>
                        <q-item-label>{{ component.title }}</q-item-label>
                        <q-item-label caption>{{ component.description }}</q-item-label>
                    </q-item-section>
                </q-item>
            </q-list>
        </q-btn-dropdown>
    </div>

    <!-- Dynamic Component View -->
    <div v-show="currentView && getCurrentComponent()" class="animate-fade-in">
        <h2 class="text-xl font-bold mb-4">{{ getCurrentComponent()?.title || 'Loading...' }}</h2>
        <div class="bg-white rounded-lg shadow p-6">
            <component 
                :is="getCurrentComponent()?.component"
                v-bind="{ ...getCurrentComponent()?.defaultProps, ...getComponentSpecificProps() }"
                v-if="!hasSpecialHandling() && getCurrentComponent()"
            />
            
            <!-- Special handling for MultipleChoiceQuiz -->
            <MultipleChoiceQuiz
                v-if="currentView === 'MultipleChoiceQuiz'"
            />
            
            <!-- Special handling for TaskList with v-model -->
            <TaskList
                v-if="currentView === 'TaskList'"
                v-model="tasksData"
            />
        </div>
    </div>
  </div>
</template>


<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import AppLayoutDefault from '@/Layouts/AppLayoutDefault.vue';
import axios from 'axios';
import Components, { getComponentIcon, MultipleChoiceQuiz, TaskList } from './components.js';

// Define props to handle Inertia.js data
const props = defineProps({
    auth: Object,
    errors: Object,
    jetstream: Object,
    errorBags: Object,
    csrf_token: String,
    user_context: Object,
    context_meta: Object
});

// taskspro — task data
const tasksData = ref([]);

// numpad — demo state
const numpadValue = ref('');

// mytable — schedule data
import fullScheduleData from './mytable/full_schedule.json';

defineOptions({
  layout: AppLayoutDefault
});

const currentView = ref('AudioPlayer');

// Get current component configuration with error handling
const getCurrentComponent = () => {
    try {
        const component = Components[currentView.value];
        if (!component) {
            console.warn(`[getCurrentComponent] Component not found for key: ${currentView.value}`);
            console.log('[getCurrentComponent] Available components:', Object.keys(Components));
            return null;
        }
        return component;
    } catch (error) {
        console.error('[getCurrentComponent] Error getting component:', error);
        return null;
    }
};

// Get component-specific props for special cases with error handling
const getComponentSpecificProps = () => {
    try {
        switch(currentView.value) {
            case 'SecureNumpad':
                return { modelValue: numpadValue.value };
            case 'MyTableSchedule':
                return { scheduleData: fullScheduleData };
            default:
                return {};
        }
    } catch (error) {
        console.error('[getComponentSpecificProps] Error getting props:', error);
        return {};
    }
};

// Check if component needs special handling
const hasSpecialHandling = () => {
    return ['MultipleChoiceQuiz', 'TaskList'].includes(currentView.value);
};

const currentViewLabel = computed(() => {
    try {
        const component = Components[currentView.value];
        return component ? component.title : 'Micro Dropdown';
    } catch (error) {
        console.error('[currentViewLabel] Error computing label:', error);
        return 'Micro Dropdown';
    }
});

onMounted(() => {
    console.log('🎯 Micro Component Test page loaded');
    console.log('  Available components:', Object.keys(Components));
    console.log('  Current view:', currentView.value);
    console.log('  Current component:', getCurrentComponent()?.title || 'Not found');
    
    // Verify all components are properly loaded
    Object.keys(Components).forEach(key => {
        const comp = Components[key];
        if (!comp || !comp.component) {
            console.warn(`[onMounted] Component ${key} is missing or invalid`);
        }
    });
});
</script>

<style scoped>
.animate-fade-in {
  animation: fadeIn 0.3s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(5px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
