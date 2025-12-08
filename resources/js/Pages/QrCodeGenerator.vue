<template>
    <AppLayout title="QR Code Generator">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                QR Code Generator
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <div class="space-y-6">
                        <!-- Input Section -->
                        <div>
                            <label for="qrText" class="block text-sm font-medium text-gray-700">Enter Text or URL</label>
                            <input
                                id="qrText"
                                type="text"
                                v-model="qrText"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="Enter text or URL to generate QR code"
                            />
                        </div>

                        <!-- QR Code Section -->
                        <div class="flex justify-center">
                            <QrCode
                                :value="qrText || 'https://example.com'"
                                :size="250"
                                level="H"
                                class="border p-4 rounded-lg shadow-sm"
                            />
                        </div>

                        <!-- Options Section -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="size" class="block text-sm font-medium text-gray-700">Size</label>
                                <input
                                    id="size"
                                    type="number"
                                    v-model.number="size"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    min="100"
                                    max="500"
                                    step="50"
                                />
                            </div>
                            <div>
                                <label for="level" class="block text-sm font-medium text-gray-700">Error Correction Level</label>
                                <select
                                    id="level"
                                    v-model="level"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="L">Low (L)</option>
                                    <option value="M">Medium (M)</option>
                                    <option value="Q">Quartile (Q)</option>
                                    <option value="H">High (H)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script>
import { defineComponent } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import QrCode from '@/Components/Common/QrCode.vue'

export default defineComponent({
    components: {
        AppLayout,
        QrCode,
    },
    data() {
        return {
            qrText: '',
            size: 250,
            level: 'H',
        }
    },
})
</script>
