<script setup>
import { ref } from 'vue';
import RoleLayout from '../../../../_shared/layouts/RoleLayout.vue';
import AppCard from '../../../../_shared/components/AppCard.vue';
import AppButton from '../../../../_shared/components/AppButton.vue';

const meetings = ref([
  { id: 1, title: 'Grade 10 Math - Section A', time: '09:00 AM', duration: '45 mins', link: 'https://zoom.us/j/123456789' },
  { id: 2, title: 'Department Faculty Meeting', time: '12:30 PM', duration: '60 mins', link: 'https://meet.google.com/abc-defg-hij' },
  { id: 3, title: 'Physics Tutorial', time: '02:00 PM', duration: '30 mins', link: 'https://zoom.us/j/987654321' },
]);

const openMeeting = (link) => {
  window.open(link, '_blank');
};
</script>

<template>
  <RoleLayout title="Live Lessons">
    <div class="max-w-4xl mx-auto space-y-6">
      
      <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-6 flex flex-col sm:flex-row items-center justify-between">
        <div class="mb-4 sm:mb-0">
          <h2 class="text-xl font-bold text-indigo-900 mb-1">Start a Quick Meeting</h2>
          <p class="text-indigo-700 text-sm">Launch your personal meeting room instantly.</p>
        </div>
        <AppButton variant="primary" class="px-6 py-3 text-lg w-full sm:w-auto">Launch Personal Room 🎥</AppButton>
      </div>

      <AppCard title="Upcoming Scheduled Lessons" no-padding>
        <ul class="divide-y divide-gray-200">
          <li v-for="meeting in meetings" :key="meeting.id" class="p-6 flex flex-col sm:flex-row sm:items-center justify-between hover:bg-gray-50 transition-colors">
            
            <div class="flex items-start sm:items-center mb-4 sm:mb-0">
              <div class="bg-blue-100 rounded-lg p-3 mr-4 text-blue-600 hidden sm:block">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                </svg>
              </div>
              <div>
                <h3 class="text-lg font-bold text-gray-900">{{ meeting.title }}</h3>
                <div class="flex items-center text-sm text-gray-500 mt-1 space-x-4">
                  <span class="flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    {{ meeting.time }}
                  </span>
                  <span class="flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    {{ meeting.duration }}
                  </span>
                </div>
              </div>
            </div>

            <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3 w-full sm:w-auto">
              <button class="text-sm font-medium border border-gray-300 bg-white text-gray-700 px-4 py-2 rounded-md hover:bg-gray-50 w-full sm:w-auto">
                Copy Link
              </button>
              <AppButton @click="openMeeting(meeting.link)" variant="success" class="w-full sm:w-auto">
                Join Now <span class="ml-2">↗</span>
              </AppButton>
            </div>

          </li>
        </ul>
      </AppCard>

    </div>
  </RoleLayout>
</template>
