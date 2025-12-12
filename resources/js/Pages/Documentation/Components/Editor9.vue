<template>
    <div>
        <!-- Editor -->
        <div v-if="showEditor" class="border border-gray-300 rounded-md">
            <Toolbar
                :editor="editorRef"
                :defaultConfig="toolbarConfig"
                :mode="mode"
                style="border-bottom: 1px solid #ccc"
            />
            <Editor
                :defaultConfig="editorConfig"
                :mode="mode"
                v-model="content"
                @onCreated="handleCreated"
                @onChange="handleChange"
                style="height: 500px; overflow-y: hidden;"
            />
        </div>
    </div>
</template>

<script setup>
import { ref, watch, onBeforeUnmount } from 'vue';
// import { Editor, Toolbar } from '@wangeditor/editor-for-vue';
// import { getEditorConfig, toolbarConfig } from '@/Utils/editorConfig';

const props = defineProps({
    modelValue: {
        type: String,
        default: ''
    },
    locale: {
        type: String,
        default: 'en'
    },
    mode: {
        type: String,
        default: 'default'
    },
    height: {
        type: String,
        default: '500px'
    },
    placeholder: {
        type: String,
        default: 'Please enter content...'
    }
});

const emit = defineEmits(['update:modelValue']);

const editorRef = ref(null);
const showEditor = ref(true);
const content = ref(props.modelValue || '');

// Handle editor creation
const handleCreated = (editor) => {
    editorRef.value = editor;
    // Set initial content
    if (props.modelValue) {
        editor.setHtml(props.modelValue);
    }
};

// Handle content changes
const handleChange = (editor) => {
    const html = editor.getHtml();
    content.value = html;
    emit('update:modelValue', html);
};

// Watch for external modelValue changes
watch(() => props.modelValue, (newValue) => {
    content.value = newValue || '';
    if (editorRef.value && newValue !== editorRef.value.getHtml()) {
        editorRef.value.setHtml(newValue || '');
    }
}, { immediate: true });

// Clean up
onBeforeUnmount(() => {
    if (editorRef.value) {
        editorRef.value.destroy();
    }
});

const editorConfig = {
    ...getEditorConfig(),
    placeholder: props.placeholder,
    MENU_CONF: {
        ...getEditorConfig().MENU_CONF,
        uploadImage: {
            maxFileSize: 2 * 1024 * 1024,
            maxNumberOfFiles: 10,
            allowedFileTypes: ['image/*'],
            customUpload(file, insertFn) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    insertFn(e.target.result);
                };
                reader.readAsDataURL(file);
            }
        }
    }
};
</script>

<!-- <style src="@wangeditor/editor/dist/css/style.css"></style> -->



