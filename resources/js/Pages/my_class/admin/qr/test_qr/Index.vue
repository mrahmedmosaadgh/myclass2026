<template>
    <div class="min-h-screen">
        <!-- Print Area - Only visible when printing -->
        <div class="hidden print:block print:min-h-screen print:m-0 print:p-4">
            <div class="print-content grid grid-rows-[auto_1fr] gap-4 print:min-h-[calc(100vh-2rem)]">
                <!-- QR Code Section -->
                <StudentQrCodes
                    v-if="student.code && student.name"
                    :student-code="student.code"
                    :student-name="student.name"
                    :grade="student.grade"
                    :date="currentDate"
                    class="print:mb-2"
                />

                <!-- Question and Solution Section -->
                <div class=" ">
                    <!-- Question Section -->
                    <div>
                        <h3 class="text-base font-semibold text-gray-900 mb-1">Question</h3>
                        <div class="h-[120px] border border-gray-300 rounded p-2 whitespace-pre-wrap overflow-hidden">
                            {{ question || 'No question provided' }}
                        </div>
                    </div>

                    <!-- Solution Section -->
                    <div>
                        <h3 class="text-base font-semibold text-gray-900 mb-1">Solution</h3>
                        <div class="h-[180px] border border-gray-300 rounded p-2 whitespace-pre-wrap overflow-hidden">
                            {{ solution || 'No solution provided' }}
                        </div>
                    </div>

                    <!-- Correction Section -->
                    <div>
                        <h3 class="text-base font-semibold text-gray-900 mb-1">Correction</h3>
                        <div class="h-[100px] border border-gray-300 rounded p-2 whitespace-pre-wrap overflow-hidden">
                            {{ correction || 'No correction provided' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Interface - Hidden when printing -->
        <AppLayout title="Student QR Codes" class="print:hidden">
            <template #header>
                <div class="flex justify-between items-center">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        Student QR Codes
                    </h2>
                    <button
                        @click="printCurrentCard"
                        class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500"
                        :disabled="!canPrint"
                    >
                        Print Card
                    </button>
                </div>
            </template>

            <div class="py-12">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <!-- Input Section -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 print:hidden">
                        <!-- Student Info -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-semibold text-gray-900">Student Information</h3>
                            <div class="space-y-4">
                                <!-- Student Code -->
                                <div>
                                    <label for="student-code" class="block text-sm font-medium text-gray-700">Code</label>
                                    <input
                                        type="text"
                                        id="student-code"
                                        v-model="student.code"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    />
                                </div>

                                <!-- Student Name -->
                                <div>
                                    <label for="student-name" class="block text-sm font-medium text-gray-700">Name</label>
                                    <input
                                        type="text"
                                        id="student-name"
                                        v-model="student.name"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    />
                                </div>

                                <!-- Student Grade -->
                                <div>
                                    <label for="student-grade" class="block text-sm font-medium text-gray-700">Grade</label>
                                    <input
                                        type="text"
                                        id="student-grade"
                                        v-model="student.grade"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Question and Solution Input -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-semibold text-gray-900">Question and Solution</h3>
                            <div class="space-y-4">
                                <!-- Question Input -->
                                <div>
                                    <label for="question" class="block text-sm font-medium text-gray-700">Question</label>
                                    <textarea
                                        id="question"
                                        v-model="question"
                                        rows="3"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        ></textarea>
                                        <!-- placeholder="Enter the question here..." -->
                                </div>

                                <!-- Solution Input -->
                                <div>
                                    <label for="solution" class="block text-sm font-medium text-gray-700">Solution</label>
                                    <textarea
                                        id="solution"
                                        v-model="solution"
                                        rows="3"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        ></textarea>
                                        <!-- placeholder="Enter the solution here..." -->
                                </div>

                                <!-- Correction Input -->
                                <div>
                                    <label for="correction" class="block text-sm font-medium text-gray-700">Correction</label>
                                    <textarea
                                        id="correction"
                                        v-model="correction"
                                        rows="2"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        ></textarea>
                                        <!-- placeholder="Enter any correction notes here..." -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Preview Section -->
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 mt-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Preview</h3>
                        <StudentQrCodes
                            v-if="student.code && student.name"
                            :student-code="student.code"
                            :student-name="student.name"
                            :grade="student.grade"
                            :date="currentDate"
                        />
                    </div>
                </div>
            </div>
            <div class="p-6">
                <QrCodeScanner @code-scanned="handleQrScanned" />
            </div>
        </AppLayout>
    </div>
</template>

<style>
@media print {
    @page {
        margin: 0.5cm;
        size: A4 portrait;
    }
    body {
        margin: 0;
        padding: 0;
        min-height: 100vh;
    }
    .print-content {
        min-height: calc(100vh - 1cm);
        max-height: 297mm;
        overflow: hidden;
        page-break-inside: avoid;
        page-break-after: always;
    }
    /* Hide all layout elements */
    nav, header, footer, aside {
        display: none !important;
    }
    /* Reset all layout-related styles */
    .min-h-screen {
        min-height: 0 !important;
    }
    .py-12 {
        padding: 0 !important;
    }
    .max-w-7xl {
        max-width: none !important;
    }
    .px-6 {
        padding-left: 0 !important;
        padding-right: 0 !important;
    }
}
</style>

<script>
import { defineComponent } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import StudentQrCodes from './StudentQrCodes.vue'
import QrCodeScanner from './test_auto_correction_qr/QrCodeScanner.vue'
import QrInputReceiver from './test_auto_correction_qr/QrInputReceiver.vue'
export default defineComponent({
    components: {
        AppLayout,
        StudentQrCodes,
        QrInputReceiver,QrCodeScanner
    },
    data() {
        return {
            student: { code: 'asd123', name: 'Ali', grade: '4A' },
            currentDate: new Date().toLocaleDateString('en-GB'),
            question: '',
            solution: '',
            correction: ''
        }
    },
    computed: {
        canPrint() {
            return this.student.code && this.student.name
        }
    },
    methods: {
        printCurrentCard() {
            window.print()
        },
        handleQrScanned(event){
            console.log(event);

        }
    },
})
</script>
