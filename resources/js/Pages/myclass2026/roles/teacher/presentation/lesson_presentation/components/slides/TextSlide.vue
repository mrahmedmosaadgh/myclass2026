<template>
  <div class="min-h-full flex flex-col">
    <div class="flex items-center justify-between mb-2">
      <label class="block text-sm font-medium text-gray-700">Slide Text</label>
      
      <!-- AI Generate Button with History -->
      <q-btn-dropdown
        flat
        dense
        color="accent"
        icon="psychology"
        label="Generate with AI"
        size="sm"
        @click="showAIDialog"
      >
        <q-list v-if="recentPrompts.length > 0">
          <q-item-label header>Recent Prompts</q-item-label>
          <q-item
            v-for="(prompt, index) in recentPrompts"
            :key="index"
            clickable
            v-close-popup
            @click="useRecentPrompt(prompt)"
          >
            <q-item-section>
              <q-item-label lines="2">{{ prompt }}</q-item-label>
            </q-item-section>
          </q-item>
          <q-separator />
          <q-item clickable v-close-popup @click="showAIDialog">
            <q-item-section>
              <q-item-label>New Prompt...</q-item-label>
            </q-item-section>
          </q-item>
        </q-list>
      </q-btn-dropdown>
    </div>

    <RichTextEditor
      :modelValue="modelValue.text || ''"
      @update:modelValue="updateText"
      class="flex-1"
    />

    <!-- AI Assistant Dialog -->
    <AIAssistant
      v-model="showAI"
      :initial-prompt="currentPrompt"
      :on-insert="handleAIInsert"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import RichTextEditor from '../RichTextEditor.vue';
import AIAssistant from '@/Components/AI/AIAssistant.vue';

const props = defineProps({
  modelValue: {
    type: Object,
    default: () => ({})
  }
});

const emit = defineEmits(['update:modelValue']);

const showAI = ref(false);
const currentPrompt = ref('');
const recentPrompts = ref([]);

// Load recent prompts from localStorage
onMounted(() => {
  const saved = localStorage.getItem('textSlide_recentPrompts');
  if (saved) {
    try {
      recentPrompts.value = JSON.parse(saved);
    } catch (e) {
      console.error('Failed to load recent prompts:', e);
    }
  }
});

const updateText = (html) => {
  emit('update:modelValue', { ...props.modelValue, text: html });
};

const showAIDialog = () => {
  currentPrompt.value = '';
  showAI.value = true;
};

const useRecentPrompt = (prompt) => {
  currentPrompt.value = prompt;
  showAI.value = true;
};

const handleAIInsert = (text) => {
  // Get the prompt that was used (from the AI dialog)
  // We'll save it to recent prompts
  if (currentPrompt.value && currentPrompt.value.trim()) {
    savePromptToHistory(currentPrompt.value);
  }
  
  // Insert the AI-generated text
  updateText((props.modelValue.text || '') + text);
};

const savePromptToHistory = (prompt) => {
  // Remove if already exists
  const filtered = recentPrompts.value.filter(p => p !== prompt);
  
  // Add to beginning
  filtered.unshift(prompt);
  
  // Keep only last 3
  recentPrompts.value = filtered.slice(0, 3);
  
  // Save to localStorage
  localStorage.setItem('textSlide_recentPrompts', JSON.stringify(recentPrompts.value));
};
</script>
