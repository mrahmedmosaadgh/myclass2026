<template>
    <div class="hidden">
        <div 
            class="print-content prose max-w-none"
            v-html="content"
            :style="{ lineHeight: lineSpacing, textAlign: alignment }"
        ></div>
    </div>
</template>

<script setup>
defineProps({
    content: String,
    lineSpacing: Number,
    alignment: String
});
</script>