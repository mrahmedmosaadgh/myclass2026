<template>
    <div class="p-0">
        <div class="grid grid-cols-5 gap-4 p-4 print:gap-2 print:p-2">
                <div v-for="item in items" :key="item.code"
                     class="flex flex-col items-center print:break-inside-avoid">
                    <div class="w-full bg-white rounded-lg overflow-hidden border"
                         :class="`border-${item.colorName}-400`">
                        <div class="relative">
                            <div class="absolute inset-x-0 -top-2 flex justify-center">
                                <div class="w-4 h-4 rounded-full border bg-white"
                                     :class="`border-${item.colorName}-500`"></div>
                            </div>
                            <div :class="`bg-${item.colorName}-500`"
                                 class="py-1 px-2 text-center">
                                <span class="text-white font-medium text-sm">
                                    {{ item.status }}
                                </span>
                            </div>
                        </div>
                        <div class="p-2 flex justify-center items-center bg-white">
                            <QrCode
                                :value="item.code"
                                :size="60"
                                level="H"
                                class="print:scale-105"
                            />
                        </div>


                    </div>
                </div>
            </div>
    </div>
</template>

<script setup>
import QrCode from '@/Components/Common/QrCode.vue';
import { ref } from 'vue';

const props = defineProps([
    'items',
]);
const items2 = ref([
    {
        code: '1-p',
        status: '1 Mark',
        colorName: 'blue',
    },
    {
        code: '3-p',
        status: '3 Marks',
        colorName: 'green',
    },
    {
        code: '5-p',
        status: '5 Marks',
        colorName: 'purple',
    },
    {
        code: '7-p',
        status: '5 Marks + 2 bounce',
        colorName: 'indigo',
    },
]);
</script>
