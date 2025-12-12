<template>
    <div>
        <!-- Normal View -->
        <div v-show="!isFullscreen" class="relative">
            <div class="prose max-w-none" v-html="sanitizedContent"></div>
            <button
                @click="toggleFullscreen"
                class="absolute top-2 right-2 px-3 py-1 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition"
            >
                <span class="text-sm">Fullscreen</span>
            </button>
        </div>

        <!-- Fullscreen View -->
        <div
            v-if="isFullscreen"
            class="fixed inset-0 bg-white z-50 overflow-auto"
        >
            <div class="sticky top-0 z-10 bg-white border-b shadow-sm p-4 flex justify-between items-center">
                <h2 class="text-xl font-semibold">{{ title }}</h2>
                <div class="flex gap-2">
                    <button
                        @click="print"
                        class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition flex items-center gap-2"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Print
                    </button>
                    <button
                        @click="toggleFullscreen"
                        class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition"
                    >
                        Exit Fullscreen
                    </button>
                </div>
            </div>
            <div class="p-6">
                <div class="prose max-w-none" v-html="sanitizedContent"></div>
            </div>
        </div>

        <!-- Print Layout (hidden) -->
        <div ref="printContainer" class="hidden">
            <div class="print-content prose max-w-none" v-html="sanitizedContent"></div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import DOMPurify from 'dompurify';

const props = defineProps({
    content: {
        type: String,
        required: true
    },
    title: {
        type: String,
        default: 'Document'
    }
});

const isFullscreen = ref(false);
const printContainer = ref(null);

const sanitizedContent = computed(() => {
    return DOMPurify.sanitize(props.content);
});

const toggleFullscreen = () => {
    isFullscreen.value = !isFullscreen.value;
    if (isFullscreen.value) {
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = '';
    }
};

const print = () => {
    const printWindow = window.open('', '_blank');
    const printContent = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>${props.title}</title>
            <style>
                @media print {
                    @page {
                        margin: 1cm;
                    }
                    body {
                        font-family: system-ui, -apple-system, sans-serif;
                        line-height: 1.5;
                    }
                    .prose {
                        max-width: none;
                    }
                    .prose img {
                        page-break-inside: avoid;
                    }
                    .prose h1, .prose h2, .prose h3 {
                        page-break-after: avoid;
                    }
                    .prose p {
                        orphans: 3;
                        widows: 3;
                    }
                }
            </style>
        </head>
        <body>
            ${sanitizedContent.value}
        </body>
        </html>
    `;
    
    printWindow.document.write(printContent);
    printWindow.document.close();
    printWindow.onload = () => {
        printWindow.print();
        printWindow.onafterprint = () => {
            printWindow.close();
        };
    };
};
</script>

<style scoped>
@media print {
    .print-content {
        display: block !important;
    }
}
</style>