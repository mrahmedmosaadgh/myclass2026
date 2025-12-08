<template>
    <AdvancedClipboardHandler
        v-model:content="my_model"
        :max-size="10 * 1024 * 1024"
        :allowed-types="['text', 'html', 'image']"
        @error="handleError"
        @change="handleChange"
         @set_data="$emit('set_data',$event)"
    />
<!-- <div class="p-0">
   ----- <div v-html="pastedContent"></div>

</div>00000 -->
    <!-- <div v-if="pastedContent">
        <h3>Pasted Content:</h3>
        <pre>{{ pastedContent.content }}</pre>
    </div> -->
</template>

<script setup>
import { ref } from 'vue';
import AdvancedClipboardHandler from './AdvancedClipboardHandler.vue';
const my_model=defineModel()
const emit=defineEmits(['set_data'])

const pastedContent = ref(null);

const handleError = (error) => {
    console.error('Clipboard error:', error);
    // Handle error (e.g., show toast notification)
};

const handleChange = (content) => {
    console.log('Content changed:', content);
    // Handle content change
};
</script>
