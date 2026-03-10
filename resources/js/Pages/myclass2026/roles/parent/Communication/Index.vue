<script setup>
import { ref } from 'vue';
import RoleLayout from '../../../_shared/layouts/RoleLayout.vue';
import AppCard from '../../../_shared/components/AppCard.vue';
import AppButton from '../../../_shared/components/AppButton.vue';

const teachers = ref([
  { id: 1, name: 'Mr. Smith', subject: 'Mathematics', role: 'Homeroom', avatar: 'M' },
  { id: 2, name: 'Ms. Johnson', subject: 'Science', role: 'Teacher', avatar: 'J' },
  { id: 3, name: 'Mrs. Davis', subject: 'Literature', role: 'Teacher', avatar: 'D' },
]);

const messages = ref([
  { id: 101, from: 'Mr. Smith', date: 'Today, 10:30 AM', subject: 'Math Exam Preparation', preview: 'Dear Parent, Sara has been doing exceptionally well in class. Please ensure she reviews chapter 4 this weekend.', read: false },
  { id: 102, from: 'School Administration', date: 'Yesterday', subject: 'Early Dismissal Notice', preview: 'Please note that the school will dismiss students at 1:00 PM this Friday due to the municipal meeting.', read: true },
]);

</script>

<template>
  <RoleLayout title="School Communication">
    
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
      
      <!-- Compose & Contacts -->
      <div class="lg:col-span-1 space-y-6">
        <AppButton variant="primary" class="w-full justify-center flex items-center space-x-2 py-3 text-lg shadow-sm">
          <span>+</span>
          <span>New Message</span>
        </AppButton>
        
        <AppCard title="Child's Teachers" no-padding>
          <ul class="divide-y divide-gray-100">
            <li v-for="teacher in teachers" :key="teacher.id" class="p-4 hover:bg-gray-50 flex items-center cursor-pointer transition-colors">
              <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold border border-indigo-200">
                {{ teacher.avatar }}
              </div>
              <div class="ml-3">
                <p class="text-sm font-medium text-gray-900">{{ teacher.name }}</p>
                <p class="text-xs text-gray-500">{{ teacher.subject }} • {{ teacher.role }}</p>
              </div>
            </li>
          </ul>
        </AppCard>
      </div>

      <!-- Inbox -->
      <div class="lg:col-span-3">
        <AppCard title="Inbox" no-padding>
          <div class="border-b border-gray-200 bg-gray-50 px-6 py-3 flex text-sm text-gray-500 font-medium">
            <div class="w-1/4">Sender</div>
            <div class="w-1/2">Subject</div>
            <div class="w-1/4 text-right">Date</div>
          </div>
          
          <ul class="divide-y divide-gray-200">
            <li v-for="msg in messages" :key="msg.id" 
                class="px-6 py-4 hover:bg-indigo-50/50 cursor-pointer transition-colors"
                :class="{'font-bold text-gray-900': !msg.read, 'text-gray-600': msg.read}">
              <div class="flex flex-col sm:flex-row sm:items-center">
                <div class="w-full sm:w-1/4 mb-1 sm:mb-0 flex items-center">
                  <span v-if="!msg.read" class="w-2 h-2 rounded-full bg-indigo-600 mr-2"></span>
                  <span class="truncate">{{ msg.from }}</span>
                </div>
                <div class="w-full sm:w-1/2 pr-4 truncate">
                  <span class="text-gray-900">{{ msg.subject }}</span>
                  <span class="text-gray-400 font-normal ml-2 hidden lg:inline">- {{ msg.preview }}</span>
                </div>
                <div class="w-full sm:w-1/4 text-left sm:text-right text-sm whitespace-nowrap" :class="!msg.read ? 'text-indigo-600 font-semibold' : 'text-gray-400'">
                  {{ msg.date }}
                </div>
              </div>
            </li>
          </ul>
          
          <div v-if="messages.length === 0" class="py-12 text-center text-gray-500">
            No new messages.
          </div>
        </AppCard>
      </div>

    </div>
  </RoleLayout>
</template>
