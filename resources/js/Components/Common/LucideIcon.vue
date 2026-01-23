<script setup>
import { computed } from 'vue';
// Import only the icons actually used in the application (27 icons instead of 1000+)
import {
    ChevronDown,
    ChevronLeft,
    ChevronRight,
    Clipboard,
    Copy,
    Download,
    FileDown,
    FilePlus,
    FileText,
    Image as ImageIcon,
    Loader2,
    Menu,
    Play,
    Plus,
    Printer,
    Replace,
    Save,
    Shuffle,
    Trash2,
    Upload,
    X,
    HelpCircle, // Fallback icon
    Lock,
    Unlock,
    Minimize,
    Maximize,
    Presentation
} from 'lucide-vue-next';

const props = defineProps({
    name: {
        type: String,
        required: true
    },
    size: {
        type: [Number, String],
        default: 24
    },
    color: {
        type: String,
        default: 'currentColor'
    },
    strokeWidth: {
        type: [Number, String],
        default: 2
    },
    class: {
        type: String,
        default: ''
    }
});

// Map of icon names to components (only icons we actually use)
const iconMap = {
    'chevron-down': ChevronDown,
    'chevron-left': ChevronLeft,
    'chevron-right': ChevronRight,
    'clipboard': Clipboard,
    'clipboard-paste': Clipboard, // Use Clipboard for paste as well
    'copy': Copy,
    'download': Download,
    'file-down': FileDown,
    'file-plus': FilePlus,
    'file-presentation': Presentation,
    'file-text': FileText,
    'image': ImageIcon,
    'loader-2': Loader2,
    'menu': Menu,
    'play': Play,
    'plus': Plus,
    'printer': Printer,
    'replace': Replace,
    'save': Save,
    'shuffle': Shuffle,
    'trash-2': Trash2,
    'upload': Upload,
    'x': X,
    'lock': Lock,
    'unlock': Unlock,
    'minimize': Minimize,
    'maximize': Maximize
};

const iconComponent = computed(() => {
    return iconMap[props.name] || HelpCircle; // Fallback to HelpCircle if icon not found
});
</script>

<template>
    <component
        :is="iconComponent"
        :size="size"
        :color="color"
        :stroke-width="strokeWidth"
        :class="class"
        style="display: inline-block; vertical-align: middle;"
    />
</template>
