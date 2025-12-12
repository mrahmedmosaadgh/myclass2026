<template>
    <div class="flex items-center gap-4">
        <!-- Display Mode -->
        <div class="flex items-center gap-2">
            <label class="text-sm text-gray-600">Display:</label>
            <select
                :value="displayMode"
                @change="$emit('update:display-mode', $event.target.value)"
                class="border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
            >
                <option value="inline-block">Inline</option>
                <option value="block">Block</option>
            </select>
        </div>

        <!-- Width Control -->
        <div class="flex items-center gap-2">
            <label class="text-sm text-gray-600">Width:</label>
            <input
                type="number"
                :value="width"
                @input="$emit('update:width', parseInt($event.target.value))"
                min="10"
                max="100"
                class="w-20 border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
            >
            <span class="text-sm text-gray-600">%</span>
        </div>

        <!-- Image Actions -->
        <div class="flex items-center gap-2">
            <button
                @click="$emit('convert-to-base64')"
                class="px-3 py-1 text-sm text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
                title="Convert external images to base64"
            >
                <Image class="w-4 h-4" />
                <span class="sr-only">Convert to Base64</span>
            </button>

            <button
                @click="handleResize"
                class="px-3 py-1 text-sm text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
                :disabled="!imageSizes.length"
                title="Resize selected images"
            >
                <ImageDown class="w-4 h-4" />
                <span class="sr-only">Resize Images</span>
            </button>
        </div>
    </div>
</template>

<script setup>
import { Image, ImageDown } from 'lucide-vue-next';

const props = defineProps({
    displayMode: {
        type: String,
        default: 'inline-block'
    },
    width: {
        type: Number,
        default: 100
    },
    imageSizes: {
        type: Array,
        default: () => []
    }
});

const handleResize = () => {
    if (props.imageSizes.length) {
        emit('resize-images', props.width);
    }
};

const emit = defineEmits([
    'update:display-mode',
    'update:width',
    'convert-to-base64',
    'resize-images'
]);
</script>