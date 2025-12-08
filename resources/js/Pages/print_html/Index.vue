<template>
    <div>
        <ReusableHtmlViewer
            v-model="htmlContent"
            title="My Document"
        />
    </div>
</template>

<script setup>
import ReusableHtmlViewer from './ReusableHtmlViewer.vue';
import { ref } from 'vue';

const htmlContent = ref('<h1>Hello World</h1><p>This is some HTML content...</p>');
</script>



