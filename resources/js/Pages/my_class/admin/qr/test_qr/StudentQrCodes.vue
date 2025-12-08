<template>
    <div class="print:shadow-none">
        <div class="bg-white shadow-lg overflow-hidden sm:rounded-lg print:shadow-none print:border-none">
            <div class="px-6 py-4 border-b border-gray-200 print:pb-2">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="text-xl font-semibold text-gray-900">
                            {{ studentName }}
                        </h3>
                        <p class="mt-2 text-sm text-gray-600">
                            <span class="font-medium">Code:</span> {{ studentCode }}
                            <span class="mx-2">|</span>
                            <span class="font-medium">Grade:</span> {{ grade }}
                        </p>
                    </div>
                    <div class="text-sm text-gray-500">
                        {{ date }}
                    </div>
                </div>
            </div>

            <div class="relative ">
                <!-- Main QR codes at the top min-h-screen print:h-[100vh] -->
                <div class="mb-4">
                    <qr :items="qrCodesWithStatus"></qr>
                </div>

                <!-- Absent QR code positioned at bottom left -->
                <div class="print:fixed print:bottom-4 print:left-4">
                    <qr :items="absent_data"></qr>
                </div>
            </div>

        </div>
    </div>
</template>




<script setup>
import qr from './qr.vue';
// import QrCode from '@/Components/Common/QrCode.vue';
import { computed ,ref} from 'vue';

const props = defineProps({
  studentCode: {
    type: String,
    required: true,
  },
  studentName: {
    type: String,
    required: true,
  },
  grade: {
    type: String,
    required: true,
  },
  date: {
    type: String,
    required: true,
  },
});
  const absent_data = ref([{
    mark: 'A',
    status: 'Absent',
    colorName: 'red',
    code: `${props.studentCode}-a-p`,
  }]);
const qrCodesWithStatus = computed(() => {

  const statusMap = {
    '1-p': { mark: 1, status: '1 Mark', colorName: 'blue' },
    '3-p': { mark: 3, status: '3 Marks', colorName: 'green' },
    '5-p': { mark: 5, status: '5 Marks', colorName: 'purple' },
    '7-p': { mark: 7, status: '5   + 2 bounce', colorName: 'indigo' },
  };
  const suffixes = ['1-p', '3-p', '5-p', '7-p'];
  return [
    // absent_data, // Include the absent data in the array
    ...suffixes.map((suffix) => ({
      mark: statusMap[suffix].mark,
      code: `${props.studentCode}-${suffix}`,
      status: statusMap[suffix].status,
      colorName: statusMap[suffix].colorName,
    })),
  ];
});

function repeatString(count) {
  if (typeof count === 'number') {
    return '⭐'.repeat(count);
  }
  console.log('ffff', '⭐'.repeat(count));
  return count; // Return the original value if not a number (e.g., 'A' for absent)
}
</script>


<style>
@media print {
    @page {
        size: landscape;
        margin: 0;
    }
    body {
        width: 100vw;
        height: 100vh;
        margin: 0;
        padding: 0;
    }
    /* Ensure content fits on one page */
    * {
        page-break-inside: avoid;
    }
}
</style>
