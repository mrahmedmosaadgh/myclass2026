<script setup>
import { ref, computed } from 'vue';
import RoleLayout from '../../_shared/layouts/RoleLayout.vue';
import AppCard from '../../_shared/components/AppCard.vue';
import AppButton from '../../_shared/components/AppButton.vue';

// Mock data: In reality, fetch from classroom/students API
const students = ref([
  { id: '1001', name: 'Ahmed Ali' },
  { id: '1002', name: 'Sara Mohamed' },
  { id: '1003', name: 'Omar Khaled' },
]);

const isPrinting = ref(false);

const printCards = () => {
  window.print();
};

const getQrUrl = (data) => {
  return `https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=${encodeURIComponent(data)}`;
};
</script>

<template>
  <RoleLayout title="QR Card Generator">
    <template #header>
      <AppButton @click="printCards" variant="primary" class="print:hidden">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
        </svg>
        Print Cards
      </AppButton>
    </template>

    <div class="print:hidden mb-6">
      <AppCard title="Instructions">
        <p class="text-sm text-gray-600 mb-2">
          Print this page and cut out the cards. Give one card to each student.
        </p>
        <p class="text-sm text-gray-600">
          Students hold the corner corresponding to their answer (A, B, C, D) towards the teacher's scanner, 
          and use the "Confirm" or "Cancel" codes to submit or reset their choice.
        </p>
      </AppCard>
    </div>

    <!-- Printable Area: Grid of Student Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 print:grid-cols-2 print:gap-4 print:p-0">
      <div 
        v-for="student in students" 
        :key="student.id"
        class="border-4 border-gray-800 rounded-xl p-6 bg-white shrink-0 break-inside-avoid shadow-sm print:shadow-none"
      >
        <div class="text-center mb-6">
          <h2 class="text-2xl font-bold uppercase tracking-widest text-gray-900">{{ student.name }}</h2>
          <p class="text-gray-500 font-mono">ID: {{ student.id }}</p>
        </div>

        <div class="grid grid-cols-2 gap-6 mb-6">
          <!-- A & B -->
          <div class="flex flex-col items-center">
            <span class="text-2xl font-bold text-blue-600 mb-1">A</span>
            <img :src="getQrUrl(`${student.id}-A`)" alt="QR A" class="w-24 h-24 border p-1" />
          </div>
          <div class="flex flex-col items-center">
            <span class="text-2xl font-bold text-red-600 mb-1">B</span>
            <img :src="getQrUrl(`${student.id}-B`)" alt="QR B" class="w-24 h-24 border p-1" />
          </div>
          
          <!-- C & D -->
          <div class="flex flex-col items-center">
            <span class="text-2xl font-bold text-green-600 mb-1">C</span>
            <img :src="getQrUrl(`${student.id}-C`)" alt="QR C" class="w-24 h-24 border p-1" />
          </div>
          <div class="flex flex-col items-center">
            <span class="text-2xl font-bold text-yellow-500 mb-1">D</span>
            <img :src="getQrUrl(`${student.id}-D`)" alt="QR D" class="w-24 h-24 border p-1" />
          </div>
        </div>

        <div class="border-t-2 border-dashed border-gray-300 pt-4 grid grid-cols-2 gap-4">
          <!-- Commands -->
          <div class="flex flex-col items-center bg-green-50 rounded-lg p-2 border border-green-200">
            <span class="font-bold text-green-700 text-sm mb-1 uppercase">Confirm ✅</span>
            <img :src="getQrUrl('CMD_CONFIRM')" alt="Confirm" class="w-16 h-16" />
          </div>
          <div class="flex flex-col items-center bg-red-50 rounded-lg p-2 border border-red-200">
            <span class="font-bold text-red-700 text-sm mb-1 uppercase">Cancel ❌</span>
            <img :src="getQrUrl('CMD_CANCEL')" alt="Cancel" class="w-16 h-16" />
          </div>
        </div>
      </div>
    </div>

  </RoleLayout>
</template>

<style scoped>
@media print {
  @page { margin: 0.5cm; }
  body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
}
</style>
