<template>
    <Modal :show="show" @close="$emit('close')">
        <div class="p-6">
            <slot></slot>
        </div>
    </Modal>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    show: Boolean,
    title: String,
    fields: Array,
    modelValue: Object,
    errors: Object,
    submitting: Boolean
});

defineEmits(['close', 'submit', 'update:modelValue']);
</script>

