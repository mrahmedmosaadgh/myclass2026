<template>
    <!-- This component is INVISIBLE but handles all realtime listening -->
    <div v-if="false"></div>
</template>

<script setup>
import { computed, watch } from 'vue';
import { useRealtimeChannel } from '@/composables/useRealtimeChannel';
import { usePage } from '@inertiajs/vue3';

/**
 * Universal Realtime Listener Component
 * 
 * Drop this ONCE in your main layout, and it handles everything.
 * 
 * Usage in AppLayout.vue:
 *   <RealtimeListener />
 * 
 * It automatically subscribes to:
 *   - user.{userId} (for personal notifications)
 *   - class.{classId} (if user is in classes)
 *   - system.all (for global announcements)
 */

const page = usePage();
const user = computed(() => page.props.auth?.user);

// Personal channel
const { data: userSignal } = useRealtimeChannel(
    computed(() => user.value ? `user.${user.value.id}` : null),
    handleUserSignal
);

// System-wide channel (everyone listens to this)
const { data: systemSignal } = useRealtimeChannel(
    'system.all',
    handleSystemSignal
);

/**
 * Handle signals for the current user
 */
function handleUserSignal(signal) {
    console.log('👤 User signal:', signal);

    switch (signal.event) {
        case 'NEW_MESSAGE':
            handleNewMessage(signal);
            break;
        
        case 'GRADE_UPDATED':
            handleGradeUpdate(signal);
            break;
        
        case 'ASSIGNMENT_DUE':
            handleAssignmentReminder(signal);
            break;
        
        default:
            console.log('Unknown user event:', signal.event);
    }
}

/**
 * Handle system-wide signals
 */
function handleSystemSignal(signal) {
    console.log('🌐 System signal:', signal);

    switch (signal.event) {
        case 'MAINTENANCE':
            showMaintenanceAlert(signal);
            break;
        
        case 'SCHOOL_CLOSURE':
            showSchoolClosureAlert(signal);
            break;
        
        case 'ANNOUNCEMENT':
            showAnnouncement(signal);
            break;
        
        default:
            console.log('Unknown system event:', signal.event);
    }
}

/**
 * Event Handlers (customize these based on your needs)
 */
function handleNewMessage(signal) {
    // Option 1: Fetch the actual message from Laravel
    // axios.get(`/api/conversations/${signal.context.conversation_id}/messages`)
    
    // Option 2: Show a toast notification
    // toast.info('New message received');
    
    // Option 3: Update a badge count
    // store.commit('incrementUnreadMessages');
    
    console.log('📨 New message in conversation:', signal.context.conversation_id);
}

function handleGradeUpdate(signal) {
    console.log('📊 Grade updated:', signal.context);
    // Show notification or refresh grades page
}

function handleAssignmentReminder(signal) {
    console.log('📝 Assignment due:', signal.context);
    // Show reminder toast
}

function showMaintenanceAlert(signal) {
    console.log('🔧 Maintenance scheduled:', signal.context);
    // Show modal or banner
}

function showSchoolClosureAlert(signal) {
    console.log('🏫 School closure:', signal.context);
    // Show alert
}

function showAnnouncement(signal) {
    console.log('📢 Announcement:', signal.context);
    // Show toast or modal
}
</script>

<style scoped>
/* This component has no UI */
</style>
