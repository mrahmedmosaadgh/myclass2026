<template>
    <div class="border-b border-gray-200">
        <div class="p-4 flex flex-wrap items-center gap-3">
            <TitleInput
                :model-value="title"
                @update:model-value="$emit('update:title', $event)"
            />

            <TextFormatControls
                @format="$emit('format', $event)"
                :active-formats="activeFormats"
            />

            <LineSpacingControl
                :model-value="lineSpacing"
                @update:model-value="$emit('update:lineSpacing', $event)"
            />

            <AlignmentControls
                :model-value="alignment"
                @update:model-value="$emit('update:alignment', $event)"
            />

            <ImageControls
                :display-mode="imageDisplayMode"
                :width="imageWidth"
                :image-sizes="imageSizes"
                @update:display-mode="$emit('update:imageDisplayMode', $event)"
                @update:width="$emit('update:imageWidth', $event)"
                @convert-to-base64="$emit('convert-images-to-base64')"
                @resize-images="$emit('resize-images', $event)"
            />

            <div class="flex items-center gap-2 ml-auto">
                <PrintButton @click="$emit('print')" />
            </div>
        </div>
    </div>
</template>

<script setup>
import TitleInput from './TitleInput.vue';
import TextFormatControls from './TextFormatControls.vue';
import LineSpacingControl from './LineSpacingControl.vue';
import AlignmentControls from './AlignmentControls.vue';
import ImageControls from './ImageControls.vue';
import PrintButton from './PrintButton.vue';

const props = defineProps({
    title: String,
    lineSpacing: Number,
    alignment: String,
    imageDisplayMode: String,
    imageWidth: Number,
    activeFormats: Array,
    imageSizes: Array
});

const emit = defineEmits([
    'update:title',
    'update:lineSpacing',
    'update:alignment',
    'update:imageDisplayMode',
    'update:imageWidth',
    'format',
    'convert-images-to-base64',
    'resize-images',
    'print'
]);
</script>


