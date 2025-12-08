<template>
    <button
        @click="handleClipboard"
        class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 transition-colors"
        :disabled="processing"
    >
        <slot>
            {{ locale === 'ar' ? 'لصق من الحافظة' : 'Paste from Clipboard' }}
        </slot>
    </button>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
    locale: {
        type: String,
        default: 'en'
    }
});

const emit = defineEmits(['get_data']);
const processing = ref(false);

const handleClipboard = async () => {
    processing.value = true;
    try {
        const clipboardItems = await navigator.clipboard.read();
        let result = {
            type: null,
            data: null,
            content_type: null
        };

        // Check for images first
        for (const item of clipboardItems) {
            const imageTypes = item.types.filter(type => type.startsWith('image/'));
            if (imageTypes.length > 0) {
                const imageType = imageTypes[0];
                const blob = await item.getType(imageType);
                const base64 = await new Promise((resolve) => {
                    const reader = new FileReader();
                    reader.onload = (e) => resolve(e.target.result);
                    reader.readAsDataURL(blob);
                });

                result = {
                    type: 'image',
                    data: base64,
                    content_type: 'image',
                    mimeType: imageType
                };
                break;
            }
        }

        // If no image, try to get HTML content
        if (!result.data) {
            for (const item of clipboardItems) {
                if (item.types.includes('text/html')) {
                    const blob = await item.getType('text/html');
                    const html = await blob.text();
                    result = {
                        type: 'html',
                        data: html,
                        content_type: 'html'
                    };
                    break;
                }
            }
        }

        // Fallback to plain text
        if (!result.data) {
            const text = await navigator.clipboard.readText();
            result = {
                type: 'text',
                data: `<p>${text}</p>`,
                content_type: 'html'
            };
        }

        emit('get_data', result);

    } catch (err) {
        console.error('Clipboard error:', err);
        emit('get_data', {
            type: 'error',
            error: err.message
        });
    } finally {
        processing.value = false;
    }
};
</script>
