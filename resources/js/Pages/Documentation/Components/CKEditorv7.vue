<template>
    <div class="editor-wrapper">
        <CKEditor
            v-model="editorData"
            />
            <!-- :config="editorConfig"
            @ready="onEditorReady"
            @blur="onEditorBlur"
            @focus="onEditorFocus" -->
    </div>
</template>
<script setup>
import { ref, watch, onMounted, onBeforeUnmount } from 'vue';
// import { component as CKEditor } from '@mayasabha/ckeditor4-vue3';
// import CKEditor from '@mayasabha/ckeditor4-vue3/dist/legacy.js'
import { CKEditor } from '@mayasabha/ckeditor4-vue3';
// The component will be automatically registered in <script setup>

const props = defineProps({
    modelValue: {
        type: String,
        default: ''
    },
    locale: {
        type: String,
        default: 'en'
    },
    placeholder: {
        type: String,
        default: 'Start typing...'
    },
    readOnly: {
        type: Boolean,
        default: false
    },
    height: {
        type: String,
        default: '300px'
    }
});

const emit = defineEmits(['update:modelValue', 'ready', 'change', 'blur', 'focus', 'error']);

const editorData = ref(props.modelValue);
const editorInstance = ref(null);

const editorConfig = {
    placeholder: props.placeholder,
    language: props.locale,
    direction: props.locale === 'ar' ? 'rtl' : 'ltr',
    removePlugins: ['CKFinderUploadAdapter', 'EasyImage'],
    toolbar: {
        items: [
            'heading',
            '|',
            'bold',
            'italic',
            'link',
            'bulletedList',
            'numberedList',
            '|',
            'indent',
            'outdent',
            '|',
            'imageUpload',
            'blockQuote',
            'insertTable',
            'mediaEmbed',
            'undo',
            'redo'
        ]
    },
    heading: {
        options: [
            { model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
            { model: 'heading1', view: 'h1', title: 'Heading 1', class: 'ck-heading_heading1' },
            { model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2' },
            { model: 'heading3', view: 'h3', title: 'Heading 3', class: 'ck-heading_heading3' }
        ]
    },
    image: {
        toolbar: [
            'imageStyle:inline',
            'imageStyle:block',
            'imageStyle:side',
            '|',
            'toggleImageCaption',
            'imageTextAlternative'
        ]
    },
    table: {
        contentToolbar: ['tableColumn', 'tableRow', 'mergeTableCells']
    },
    paste: {
        handling: {
            googleDocs: true,
            msWord: true,
        }
    }
};

const onEditorReady = (editor) => {
    editorInstance.value = editor;
    editor.editing.view.change(writer => {
        writer.setStyle(
            'height',
            props.height,
            editor.editing.view.document.getRoot()
        );
    });
    emit('ready', editor);
};

const onEditorChange = () => {
    emit('update:modelValue', editorData.value);
    emit('change', editorData.value);
};

const onEditorBlur = (event, editor) => {
    emit('blur', editor);
};

const onEditorFocus = (event, editor) => {
    emit('focus', editor);
};

watch(() => props.modelValue, (newValue) => {
    if (newValue !== editorData.value) {
        editorData.value = newValue;
    }
});

watch(() => props.readOnly, (newValue) => {
    if (editorInstance.value) {
        editorInstance.value.isReadOnly = newValue;
    }
});

onBeforeUnmount(() => {
    if (editorInstance.value) {
        editorInstance.value.destroy()
            .catch(error => console.error('Error destroying editor:', error));
    }
});
</script>

<style>
.editor-wrapper {
    @apply w-full border border-gray-300 rounded-lg overflow-hidden;
}

.ck-editor__editable {
    /* Minimum height */
    min-height: 200px;
}

/* RTL Support */
[dir="rtl"] .ck.ck-editor__editable {
    text-align: right;
}

/* Custom styling for editor */
.ck.ck-editor__main > .ck-editor__editable {
    background-color: white;
}

.ck.ck-editor__main > .ck-editor__editable.ck-focused {
    border-color: #4f46e5;
}

.ck.ck-toolbar {
    border-color: #e5e7eb;
    background: #f9fafb;
}

/* Dark mode support if needed */
@media (prefers-color-scheme: dark) {
    .ck.ck-editor__main > .ck-editor__editable {
        background-color: #1f2937;
        color: white;
    }

    .ck.ck-toolbar {
        background: #374151;
        border-color: #4b5563;
    }
}
</style>





















