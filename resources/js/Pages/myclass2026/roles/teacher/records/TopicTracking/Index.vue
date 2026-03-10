<script setup>
import { ref } from 'vue';
import RoleLayout from '../../../../_shared/layouts/RoleLayout.vue';
import AppCard from '../../../../_shared/components/AppCard.vue';
import AppButton from '../../../../_shared/components/AppButton.vue';

// Mock data: Would fetch from API based on teacher's assigned subjects and classes
const classes = ref([
  { id: 1, name: 'Grade 10 - Section A - Mathematics' },
  { id: 2, name: 'Grade 10 - Section B - Mathematics' },
  { id: 3, name: 'Grade 11 - Section A - Physics' },
]);

const selectedClass = ref(classes.value[0].id);

const topics = ref([
  { id: 101, title: 'Algebraic Expressions', status: 'completed', date_completed: '2026-03-01', comprehension: 85 },
  { id: 102, title: 'Linear Equations', status: 'completed', date_completed: '2026-03-05', comprehension: 72 },
  { id: 103, title: 'Quadratic Equations', status: 'in-progress', date_completed: null, comprehension: null },
  { id: 104, title: 'Functions and Graphs', status: 'pending', date_completed: null, comprehension: null },
  { id: 105, title: 'Trigonometry', status: 'pending', date_completed: null, comprehension: null },
]);

const updateStatus = (topicId, newStatus) => {
  const topic = topics.value.find(t => t.id === topicId);
  if (topic) {
    topic.status = newStatus;
    if (newStatus === 'completed') {
      topic.date_completed = new Date().toISOString().split('T')[0];
      topic.comprehension = Math.floor(Math.random() * 30) + 70; // Mock score
    } else {
      topic.date_completed = null;
      topic.comprehension = null;
    }
  }
};
</script>

<template>
  <RoleLayout title="Topic Tracking">
    <template #header>
      <div class="flex items-center space-x-3">
        <label for="class-select" class="text-sm font-medium text-gray-700">Select Class:</label>
        <select id="class-select" v-model="selectedClass" class="rounded-md border-gray-300 py-2 pl-3 pr-10 text-base focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm">
          <option v-for="cls in classes" :key="cls.id" :value="cls.id">{{ cls.name }}</option>
        </select>
      </div>
    </template>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
      
      <!-- Progress Summary -->
      <div class="lg:col-span-1 space-y-6">
        <AppCard title="Progress Overview">
          <div class="space-y-4">
            <div>
              <div class="flex justify-between text-sm mb-1">
                <span class="text-gray-500">Syllabus Completion</span>
                <span class="font-bold text-indigo-600">40%</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-indigo-600 h-2 rounded-full" style="width: 40%"></div>
              </div>
            </div>
            
            <div class="pt-4 border-t border-gray-200">
              <div class="text-3xl font-bold text-gray-900 mb-1">2 <span class="text-base font-normal text-gray-500">/ 5</span></div>
              <div class="text-sm text-gray-500">Topics covered this term</div>
            </div>
          </div>
        </AppCard>

        <AppCard title="Legend">
          <ul class="space-y-2 text-sm">
            <li class="flex items-center"><span class="w-3 h-3 rounded-full bg-green-500 mr-2"></span> Completed</li>
            <li class="flex items-center"><span class="w-3 h-3 rounded-full bg-blue-500 mr-2"></span> In Progress</li>
            <li class="flex items-center"><span class="w-3 h-3 rounded-full bg-gray-300 mr-2"></span> Pending</li>
          </ul>
        </AppCard>
      </div>

      <!-- Topic List -->
      <div class="lg:col-span-3">
        <AppCard title="Curriculum Tracker" no-padding>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Topic</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Avg. Comprehension</th>
                  <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="topic in topics" :key="topic.id" :class="{'bg-gray-50': topic.status === 'completed'}">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">{{ topic.title }}</div>
                    <div v-if="topic.date_completed" class="text-xs text-gray-500">Completed: {{ topic.date_completed }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize"
                          :class="{
                            'bg-green-100 text-green-800': topic.status === 'completed',
                            'bg-blue-100 text-blue-800': topic.status === 'in-progress',
                            'bg-gray-100 text-gray-800': topic.status === 'pending'
                          }">
                      {{ topic.status.replace('-', ' ') }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div v-if="topic.comprehension" class="flex items-center">
                      <span class="text-sm font-semibold text-gray-700 mr-2">{{ topic.comprehension }}%</span>
                      <div class="w-16 bg-gray-200 rounded-full h-1.5">
                        <div class="h-1.5 rounded-full" 
                             :class="topic.comprehension >= 80 ? 'bg-green-500' : (topic.comprehension >= 60 ? 'bg-yellow-400' : 'bg-red-500')"
                             :style="`width: ${topic.comprehension}%`">
                        </div>
                      </div>
                    </div>
                    <span v-else class="text-xs text-gray-400">-</span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                    <div class="flex justify-center space-x-2">
                      <button v-if="topic.status !== 'completed'" @click="updateStatus(topic.id, 'completed')" class="text-green-600 hover:text-green-900" title="Mark Completed">✅</button>
                      <button v-if="topic.status === 'pending'" @click="updateStatus(topic.id, 'in-progress')" class="text-blue-600 hover:text-blue-900" title="Start Topic">▶️</button>
                      <button v-if="topic.status !== 'pending'" @click="updateStatus(topic.id, 'pending')" class="text-gray-400 hover:text-gray-600" title="Reset">↩️</button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </AppCard>
      </div>

    </div>
  </RoleLayout>
</template>
