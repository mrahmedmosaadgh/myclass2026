<template>
    <div class="editor-wrapper">
        <div class="editor-container" :class="{ 'editor-focused': isFocused }">
            <div v-if="editor" class="editor-toolbar border-b border-gray-200 p-2 flex gap-2">
                <template v-for="(action, index) in textFormatActions" :key="index">
                    <button
                        class="p-2 hover:bg-gray-100 rounded"
                        :class="{ 'bg-gray-200': action.isActive() }"
                        @click="action.action"
                    >
                        <i :class="'icon-' + action.icon"></i>
                        {{ action.icon }}
                    </button>
                </template>
            </div>
            <div class="editor-main p-4" :class="{ 'editor-loading': !isEditorReady }">
                <EditorContent v-if="editor" :editor="editor" />
                <div v-else class="text-gray-400">Loading editor...</div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { Editor, EditorContent } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';
import TextStyle from '@tiptap/extension-text-style';
import { Color } from '@tiptap/extension-color';
import Highlight from '@tiptap/extension-highlight';
import Image from '@tiptap/extension-image';
import TextAlign from '@tiptap/extension-text-align';
import Link from '@tiptap/extension-link';

const props = defineProps({
    modelValue: {
        type: String,
        default: '',
    },
});

const emit = defineEmits(['update:modelValue']);

const editor = ref(null);
const isEditorReady = ref(false);
const isFocused = ref(false);

const textFormatActions = computed(() => [
    {
        icon: 'bold',
        action: () => editor.value?.chain().focus().toggleBold().run(),
        isActive: () => editor.value?.isActive('bold') ?? false,
    },
    {
        icon: 'italic',
        action: () => editor.value?.chain().focus().toggleItalic().run(),
        isActive: () => editor.value?.isActive('italic') ?? false,
    },
    {
        icon: 'underline',
        action: () => editor.value?.chain().focus().toggleUnderline().run(),
        isActive: () => editor.value?.isActive('underline') ?? false,
    },
    {
        icon: 'strike',
        action: () => editor.value?.chain().focus().toggleStrike().run(),
        isActive: () => editor.value?.isActive('strike') ?? false,
    },
    {
        icon: 'h1',
        action: () => editor.value?.chain().focus().toggleHeading({ level: 1 }).run(),
        isActive: () => editor.value?.isActive('heading', { level: 1 }) ?? false,
    },
    {
        icon: 'h2',
        action: () => editor.value?.chain().focus().toggleHeading({ level: 2 }).run(),
        isActive: () => editor.value?.isActive('heading', { level: 2 }) ?? false,
    },
    {
        icon: 'h3',
        action: () => editor.value?.chain().focus().toggleHeading({ level: 3 }).run(),
        isActive: () => editor.value?.isActive('heading', { level: 3 }) ?? false,
    },
    {
        icon: 'bullet-list',
        action: () => editor.value?.chain().focus().toggleBulletList().run(),
        isActive: () => editor.value?.isActive('bulletList') ?? false,
    },
    {
        icon: 'ordered-list',
        action: () => editor.value?.chain().focus().toggleOrderedList().run(),
        isActive: () => editor.value?.isActive('orderedList') ?? false,
    },
    {
        icon: 'quote',
        action: () => editor.value?.chain().focus().toggleBlockquote().run(),
        isActive: () => editor.value?.isActive('blockquote') ?? false,
    },
    {
        icon: 'align-left',
        action: () => editor.value?.chain().focus().setTextAlign('left').run(),
        isActive: () => editor.value?.isActive({ textAlign: 'left' }) ?? false,
    },
    {
        icon: 'align-center',
        action: () => editor.value?.chain().focus().setTextAlign('center').run(),
        isActive: () => editor.value?.isActive({ textAlign: 'center' }) ?? false,
    },
    {
        icon: 'align-right',
        action: () => editor.value?.chain().focus().setTextAlign('right').run(),
        isActive: () => editor.value?.isActive({ textAlign: 'right' }) ?? false,
    },
    {
        icon: 'link',
        action: () => {
            const url = window.prompt('URL:');
            if (url) {
                editor.value?.chain().focus().setLink({ href: url }).run();
            }
        },
        isActive: () => editor.value?.isActive('link') ?? false,
    },
    {
        icon: 'unlink',
        action: () => editor.value?.chain().focus().unsetLink().run(),
        isActive: () => false,
    },
    {
        icon: 'image',
        action: () => {
            const url = window.prompt('Image URL:');
            if (url) {
                editor.value?.chain().focus().setImage({ src: url }).run();
            }
        },
        isActive: () => false,
    },
    {
        icon: 'clear',
        action: () => editor.value?.chain().focus().clearNodes().unsetAllMarks().run(),
        isActive: () => false,
    },
    {
        icon: 'undo',
        action: () => editor.value?.chain().focus().undo().run(),
        isActive: () => false,
    },
    {
        icon: 'redo',
        action: () => editor.value?.chain().focus().redo().run(),
        isActive: () => false,
    },
]);

onMounted(() => {
    editor.value = new Editor({
        content: props.modelValue,
        extensions: [
    StarterKit,
    TextStyle,
    Color,
    Highlight,
    Image,
    // Underline,
    TextAlign.configure({
        types: ['heading', 'paragraph'],
    }),
    Link.configure({
        openOnClick: false,
    }),
],
        onUpdate: ({ editor }) => {
            emit('update:modelValue', editor.getHTML());
        },
        onFocus: () => {
            isFocused.value = true;
        },
        onBlur: () => {
            isFocused.value = false;
        },
    });
    isEditorReady.value = true;
});

onBeforeUnmount(() => {
    editor.value?.destroy();
});
</script>

<style>
.editor-wrapper {
    @apply w-full;
}

.editor-container {
    @apply border border-gray-200 rounded-lg overflow-hidden;
}

.editor-main {
    min-height: 200px;
}

.editor-focused {
    @apply ring-2 ring-blue-500 ring-opacity-50;
}

.ProseMirror {
    @apply p-4 min-h-[200px] outline-none;
}

.ProseMirror p.is-editor-empty:first-child::before {
    content: attr(data-placeholder);
    @apply text-gray-400 float-left h-0 pointer-events-none;
}
</style>


