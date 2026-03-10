<script setup>
import RoleLayout from '../../../_shared/layouts/RoleLayout.vue';
import AppCard from '../../../_shared/components/AppCard.vue';
import AppButton from '../../../_shared/components/AppButton.vue';

const reportCards = [
  { term: 'Fall Term 2025', date: 'Dec 15, 2025', type: 'End of Term Report', status: 'Available' },
  { term: 'Spring Term 2025', date: 'Jun 10, 2025', type: 'Final Year Report', status: 'Available' },
];

const behaviorLogs = [
  { date: 'Mar 8, 2026', type: 'Positive Note', description: 'Excellent participation in Science project.' },
  { date: 'Feb 14, 2026', type: 'Attendance', description: 'Excused absence (Medical).' },
];
</script>

<template>
  <RoleLayout title="Reports & Records">
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
      
      <!-- Official Reports -->
      <div>
        <AppCard title="Official Report Cards" no-padding>
          <template #header>
            <p class="text-xs text-gray-500 mt-1 px-6 pb-2">Download official signed reports.</p>
          </template>
          <ul class="divide-y divide-gray-200">
            <li v-for="(report, i) in reportCards" :key="i" class="p-6 flex flex-col sm:flex-row sm:items-center justify-between hover:bg-gray-50">
              <div class="flex items-center mb-4 sm:mb-0">
                <div class="bg-indigo-100 p-3 rounded-lg text-indigo-600 mr-4">
                  <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                </div>
                <div>
                  <h4 class="text-sm font-bold text-gray-900">{{ report.term }}</h4>
                  <p class="text-xs text-gray-500">{{ report.type }} • Issued {{ report.date }}</p>
                </div>
              </div>
              
              <AppButton variant="secondary" class="text-sm px-4">Download PDF 📥</AppButton>
            </li>
          </ul>
        </AppCard>
      </div>

      <!-- Behavior Notes -->
      <div>
        <AppCard title="Behavior & Attendance Actions" no-padding>
          <template #header>
            <p class="text-xs text-gray-500 mt-1 px-6 pb-2">Recent notes from teachers regarding conduct and attendance.</p>
          </template>
          
          <ul class="divide-y divide-gray-200">
            <li v-for="(log, i) in behaviorLogs" :key="i" class="p-6 flex border-l-4"
                :class="log.type === 'Positive Note' ? 'border-green-500 bg-green-50/30' : 'border-yellow-500 bg-yellow-50/30'">
              <div class="flex-1">
                <div class="flex items-center justify-between mb-1">
                  <span class="text-xs font-bold uppercase tracking-wider"
                        :class="log.type === 'Positive Note' ? 'text-green-700' : 'text-yellow-700'">
                    {{ log.type }}
                  </span>
                  <span class="text-xs text-gray-500">{{ log.date }}</span>
                </div>
                <p class="text-sm text-gray-900 mt-2 font-medium">{{ log.description }}</p>
              </div>
            </li>
          </ul>
        </AppCard>
      </div>

    </div>

  </RoleLayout>
</template>
