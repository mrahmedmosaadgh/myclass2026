<template>
  <div class="p-4 max-w-2xl mx-auto">
    <div class="flex justify-between items-center mb-4">
      <h2 class="text-xl font-semibold">AI Chat</h2>
      
      <div class="flex items-center space-x-3">
        <!-- Text-to-Speech Toggle -->
        <div class="flex items-center space-x-2">
          <span class="text-sm text-gray-600">Read Aloud:</span>
          <button 
            @click="toggleTextToSpeech" 
            class="px-3 py-1 rounded-full text-sm font-medium transition-colors"
            :class="textToSpeechEnabled ? 'bg-purple-500 text-white' : 'bg-gray-200 text-gray-700'"
          >
            {{ textToSpeechEnabled ? 'ON' : 'OFF' }}
          </button>
        </div>
        
        <!-- Teaching Mode Switch -->
        <div class="flex items-center space-x-2">
          <span class="text-sm text-gray-600">BrainSpark Mode:</span>
          <button 
            @click="toggleTeachingMode" 
            class="px-3 py-1 rounded-full text-sm font-medium transition-colors"
            :class="teachingMode ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-700'"
          >
            {{ teachingMode ? 'ON' : 'OFF' }}
          </button>
        </div>
      </div>
    </div>
    
    <!-- Teaching Mode Info Banner -->
    <div v-if="teachingMode" class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg">
      <div class="flex items-start">
        <div class="flex-shrink-0 pt-0.5">
          <svg class="h-5 w-5 text-green-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
          </svg>
        </div>
        <div class="ml-3">
          <p class="text-sm text-green-700">
            <strong>BrainSpark Mode Activated! ✨</strong> I'll guide your thinking with helpful questions instead of giving away answers. Let's discover solutions together!
          </p>
        </div>
      </div>
    </div>
    
    <OpenAiCaller 
      :apiKey="openAiKey" 
      ref="aiCaller" 
      @success="handleSuccess" 
      @error="handleError"
    >
      <template #default="{ loading, error, sendRequest }">
        <!-- Chat messages -->
        <div class="mb-4 space-y-3">
          <div v-for="(msg, index) in displayConversation" :key="index" 
               class="p-3 rounded relative" 
               :class="msg.role === 'user' ? 'bg-gray-100' : 'bg-blue-50'">
            <div class="flex justify-between items-center mb-1">
              <div class="font-semibold">{{ msg.role === 'user' ? 'You' : 'AI' }}</div>
              
              <!-- Action buttons for AI messages only -->
              <div v-if="msg.role === 'assistant'" class="flex space-x-2">
                <!-- Listen button -->
                <button 
                  @click="readMessageAloud(msg.content)"
                  class="text-xs px-2 py-1 bg-purple-500 text-white rounded hover:bg-purple-600 flex items-center"
                  title="Listen to this response"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15.414a5 5 0 001.414 1.414m0 0l-2.828 2.828m0 0a9 9 0 010-12.728m2.828 2.828a5 5 0 00-1.414 1.414m0 0L3 21" />
                  </svg>
                  Listen
                </button>
                
                <!-- Math format toggle button (if applicable) -->
                <button v-if="containsMath(msg.content)"
                  @click="toggleFormatting(index, msg)"
                  class="text-xs px-2 py-1 bg-blue-500 text-white rounded hover:bg-blue-600"
                >
                  {{ formattedMessages[index] ? 'Plain Text' : 'Math' }}
                </button>
              </div>
            </div>
            
            <!-- Message content display -->
            <div v-if="msg.role === 'assistant' && containsMath(msg.content) && formattedMessages[index]" 
                 v-html="formattedMessages[index]" 
                 class="katex-preview">
            </div>
            <div v-else>{{ msg.content }}</div>
          </div>
        </div>
        
        <!-- Input form with voice input -->
        <form @submit.prevent="submitMessage(sendRequest)" class="space-y-2">
          <div class="relative">
            <textarea 
              v-model="userMessage" 
              class="w-full border rounded p-2 min-h-[100px] pr-10"
              placeholder="Type your message here or click the microphone to speak..."
              :disabled="loading || isListening"
            ></textarea>
            
            <!-- Voice input button -->
            <button 
              type="button"
              @click="toggleVoiceInput"
              class="absolute right-2 bottom-2 p-2 rounded-full transition-colors"
              :class="isListening ? 'bg-red-500 text-white animate-pulse' : 'bg-gray-200 hover:bg-gray-300 text-gray-700'"
              title="Toggle voice input"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
              </svg>
            </button>
          </div>
          
          <!-- Voice input status -->
          <div v-if="isListening" class="text-sm text-red-500 animate-pulse flex items-center">
            <span class="mr-2">Listening...</span>
            <span v-if="interimTranscript" class="text-gray-600 italic">{{ interimTranscript }}</span>
          </div>
          
          <div class="flex justify-between items-center">
            <div v-if="error" class="text-red-500 text-sm">{{ error }}</div>
            <button 
              type="submit" 
              class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded"
              :disabled="loading || isListening || !userMessage.trim()"
            >
              {{ loading ? 'Sending...' : 'Send' }}
            </button>
          </div>
        </form>
      </template>
    </OpenAiCaller>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, onBeforeUnmount } from 'vue';
import OpenAiCaller from './OpenAiCaller.vue';
import DOMPurify from 'dompurify';
import katex from 'katex';
import 'katex/dist/katex.min.css';

// Get API key from environment variable
const openAiKey = import.meta.env.VITE_OPENAI_API_KEY;

const userMessage = ref('');
const conversation = ref([]);
const formattedMessages = ref({});
const teachingMode = ref(false);
const textToSpeechEnabled = ref(false);
const speechSynthesis = window.speechSynthesis;
const currentUtterance = ref(null);

// Voice input variables
const isListening = ref(false);
const recognition = ref(null);
const interimTranscript = ref('');

// Initialize speech recognition
const initSpeechRecognition = () => {
  try {
    // Check if browser supports speech recognition
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    if (!SpeechRecognition) {
      console.warn('Speech recognition not supported in this browser');
      return false;
    }
    
    // Create recognition instance
    recognition.value = new SpeechRecognition();
    recognition.value.continuous = true;
    recognition.value.interimResults = true;
    recognition.value.lang = 'en-US'; // Force English (US) language
    
    // Handle results
    recognition.value.onresult = (event) => {
      let finalTranscript = '';
      let currentInterimTranscript = '';
      
      for (let i = event.resultIndex; i < event.results.length; i++) {
        const transcript = event.results[i][0].transcript;
        
        if (event.results[i].isFinal) {
          finalTranscript += transcript;
        } else {
          currentInterimTranscript += transcript;
        }
      }
      
      // Update interim transcript for display
      interimTranscript.value = currentInterimTranscript;
      
      // If we have final transcript, add it to the message
      if (finalTranscript) {
        userMessage.value += (userMessage.value ? ' ' : '') + finalTranscript;
      }
    };
    
    // Handle end of speech recognition
    recognition.value.onend = () => {
      if (isListening.value) {
        // If still in listening mode but recognition ended, restart it
        recognition.value.start();
      } else {
        interimTranscript.value = '';
      }
    };
    
    // Handle errors
    recognition.value.onerror = (event) => {
      console.error('Speech recognition error', event.error);
      isListening.value = false;
      interimTranscript.value = '';
    };
    
    return true;
  } catch (error) {
    console.error('Error initializing speech recognition:', error);
    return false;
  }
};

// Toggle voice input
const toggleVoiceInput = () => {
  if (!recognition.value && !initSpeechRecognition()) {
    alert('Speech recognition is not supported in your browser.');
    return;
  }
  
  if (isListening.value) {
    // Stop listening
    recognition.value.stop();
    isListening.value = false;
    interimTranscript.value = '';
  } else {
    // Start listening
    try {
      recognition.value.start();
      isListening.value = true;
    } catch (error) {
      console.error('Error starting speech recognition:', error);
      alert('Could not start speech recognition. Please try again.');
    }
  }
};

// Socratic tutor system message with encouraging tone and emojis
// This is hidden from the UI but used in the API calls
const socraticTutorPrompt = `You are a Socratic tutor who guides students with encouraging questions rather than direct answers.

IMPORTANT GUIDELINES:
1. Use emojis frequently to create a friendly atmosphere (✨, 🧠, 🤔, 💡, 👏, 🌟, 🔍, etc.)
2. Start responses with encouraging phrases like "Great question!", "You're on the right track!", "I like how you're thinking about this!"
3. Instead of giving answers, ask guiding questions that lead students to discover solutions themselves
4. Acknowledge effort and progress with praise like "Excellent effort!" or "You're making great progress!"
5. For math problems, offer hints about the approach rather than calculations
6. Break complex problems into smaller steps with guiding questions for each step
7. If a student is stuck, provide a small hint and encourage them to try again
8. End your responses with motivational phrases like "You can do this!" or "I believe in you!"

Your goal is to cultivate independent thinking while keeping students motivated and engaged.`;

// Regular assistant system message
const regularAssistantPrompt = "You are a helpful assistant.";

// Toggle teaching mode
const toggleTeachingMode = () => {
  teachingMode.value = !teachingMode.value;
  
  // Reset conversation when switching modes
  if (conversation.value.length > 0) {
    // Keep only user messages when switching modes
    const userMessages = conversation.value.filter(msg => msg.role === 'user');
    
    // Add appropriate system message based on mode
    conversation.value = [
      { role: 'system', content: teachingMode.value ? socraticTutorPrompt : regularAssistantPrompt },
      ...userMessages
    ];
    
    // Clear formatted messages
    formattedMessages.value = {};
  } else {
    // Initialize with system message if conversation is empty
    conversation.value = [
      { role: 'system', content: teachingMode.value ? socraticTutorPrompt : regularAssistantPrompt }
    ];
  }
};

// Toggle text-to-speech functionality
const toggleTextToSpeech = () => {
  textToSpeechEnabled.value = !textToSpeechEnabled.value;
  
  // Stop any ongoing speech when toggling off
  if (!textToSpeechEnabled.value && currentUtterance.value) {
    speechSynthesis.cancel();
    currentUtterance.value = null;
  }
};

// Read text aloud using Web Speech API
const readAloud = (text) => {
  if (!text) return;
  
  // Stop any ongoing speech
  if (speechSynthesis.speaking) {
    speechSynthesis.cancel();
  }
  
  // Clean up text for better speech synthesis
  const cleanText = text
    // Remove markdown formatting
    .replace(/\*\*(.*?)\*\*/g, '$1') // Convert **bold** to just the text
    .replace(/\*(.*?)\*/g, '$1')     // Convert *italic* to just the text
    
    // Remove LaTeX expressions
    .replace(/\\\((.*?)\\\)/g, '')   // Remove inline LaTeX completely
    .replace(/\\\[(.*?)\\\]/g, '')   // Remove display LaTeX completely
    .replace(/\\frac\{(.*?)\}\{(.*?)\}/g, '$1 divided by $2') // Convert fractions to spoken form
    
    // Remove all emojis using a comprehensive regex
    .replace(/[\u{1F600}-\u{1F64F}\u{1F300}-\u{1F5FF}\u{1F680}-\u{1F6FF}\u{1F700}-\u{1F77F}\u{1F780}-\u{1F7FF}\u{1F800}-\u{1F8FF}\u{1F900}-\u{1F9FF}\u{1FA00}-\u{1FA6F}\u{1FA70}-\u{1FAFF}\u{2600}-\u{26FF}\u{2700}-\u{27BF}]/gu, '')
    
    // Clean up extra whitespace that might result from removals
    .replace(/\s+/g, ' ')
    .trim();
  
  const utterance = new SpeechSynthesisUtterance(cleanText);
  
  // Force English language
  utterance.lang = 'en-US';
  
  // Set voice properties
  utterance.rate = 1.0; // Normal speed
  utterance.pitch = 1.0; // Normal pitch
  
  // Try to use a more natural English voice if available
  const voices = speechSynthesis.getVoices();
  const preferredVoice = voices.find(voice => 
    (voice.lang === 'en-US' || voice.lang === 'en-GB') && 
    (voice.name.includes('Google') || voice.name.includes('Natural') || 
    voice.name.includes('Female') || voice.name.includes('Samantha'))
  );
  
  if (preferredVoice) {
    utterance.voice = preferredVoice;
  } else {
    // Fallback to any English voice
    const englishVoice = voices.find(voice => voice.lang === 'en-US' || voice.lang === 'en-GB');
    if (englishVoice) {
      utterance.voice = englishVoice;
    }
  }
  
  // Store current utterance for potential cancellation
  currentUtterance.value = utterance;
  
  // Speak the text
  speechSynthesis.speak(utterance);
};

// New function to read a specific message aloud
const readMessageAloud = (text) => {
  // Stop any ongoing speech
  if (speechSynthesis.speaking) {
    speechSynthesis.cancel();
  }
  
  // Use the existing readAloud function
  readAloud(text);
};

// Filter system messages from displayed conversation
const displayConversation = computed(() => {
  return conversation.value.filter(msg => msg.role !== 'system');
});

// Check if message contains math expressions
const containsMath = (text) => {
  return text && (
    text.includes('\\(') || 
    text.includes('\\)') || 
    text.includes('\\frac') ||
    text.includes('**Question')
  );
};

// Toggle between formatted and plain text
const toggleFormatting = (index, msg = null) => {
  if (formattedMessages.value[index]) {
    // If already formatted, remove formatting
    formattedMessages.value[index] = null;
  } else {
    // Format the message
    var message = conversation.value[index];
    if (msg !== null) {
      message = msg;
    }
    
    // Enhanced formatting for better readability
    let content = message.content;
    
    // Format numbered lists with proper spacing
    content = content.replace(/(\d+\.\s+\*\*.*?\*\*):(?:\s*)(-\s+.*?)(?=\d+\.|$)/gs, (match, header, items) => {
      return `<div class="mb-3"><strong>${header.replace(/\*\*/g, '')}</strong>${items}</div>`;
    });
    
    // Format bullet points
    content = content.replace(/(-\s+.*?)(?=(?:-\s+)|(?:\d+\.)|$)/g, '<div class="ml-4 mb-1">$1</div>');
    
    // Add paragraph breaks for better readability
    content = content.replace(/(\d+\.\s+\*\*.*?\*\*)/g, '<div class="font-bold mt-3">$1</div>');
    
    // Format examples with indentation
    content = content.replace(/(Example:.*?)(?=(?:-\s+)|(?:\d+\.)|$)/g, '<div class="ml-4 italic text-gray-600">$1</div>');
    
    // Apply KaTeX formatting for math expressions
    formattedMessages.value[index] = renderKaTeX(content);
  }
};

// Improved renderKaTeX function to handle more formatting
  const renderKaTeX = (text) => {
  if (!text) return ''; 

  try {
    // First, handle markdown-style bold text
    let processed = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
    
    // Handle italic text
    processed = processed.replace(/\*(.*?)\*/g, '<em>$1</em>');
    
    // Handle numbered lists
    processed = processed.replace(/(\d+\.\s+)(?!<)/g, '<div class="mt-2 font-semibold">$1</div>');
    
    // Then handle LaTeX expressions
    processed = processed.replace(/\\\((.*?)\\\)/g, (match, latex) => {
      try {
        return katex.renderToString(latex.trim(), {
          throwOnError: false,
          displayMode: false,
          strict: false
        });
      } catch (e) {
        console.warn('KaTeX rendering error:', e);
        return match;
      }
    });

    // Add line breaks
    processed = processed.replace(/\n/g, '<br>');

    return DOMPurify.sanitize(processed);
  } catch (error) {
    console.error('Error in renderKaTeX:', error);
    return text || '';
  }
};

// Basic formatting for when KaTeX is not available
const formatBasicMath = (text) => {
  let formatted = text;
  
  // Format bold text
  formatted = formatted.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
  
  // Format fractions
  formatted = formatted.replace(/\\frac\{(.*?)\}\{(.*?)\}/g, '$1/$2');
  
  // Replace LaTeX delimiters
  formatted = formatted.replace(/\\\(|\\\)/g, '');
  formatted = formatted.replace(/\\\[|\\\]/g, '');
  
  // Replace newlines with <br>
  formatted = formatted.replace(/\n\n/g, '<br><br>');
  
  return formatted;
};

// Load KaTeX dynamically
const loadKaTeX = async () => {
  if (window.katex) return;
  
  try {
    // Load KaTeX CSS
    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = 'https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/katex.min.css';
    document.head.appendChild(link);
    
    // Load KaTeX JS
    const script = document.createElement('script');
    script.src = 'https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/katex.min.js';
    script.async = true;
    
    // Wait for script to load
    await new Promise((resolve, reject) => {
      script.onload = resolve;
      script.onerror = reject;
      document.head.appendChild(script);
    });
    
    console.log('KaTeX loaded successfully');
  } catch (error) {
    console.error('Failed to load KaTeX:', error);
  }
};

const submitMessage = async (sendRequest) => {
  if (!userMessage.value.trim()) return;
  
  // Add user message to conversation
  conversation.value.push({
    role: 'user',
    content: userMessage.value.trim()
  });
  
  try {
    // Ensure system message is present
    if (!conversation.value.some(msg => msg.role === 'system')) {
      conversation.value.unshift({
        role: 'system',
        content: teachingMode.value ? socraticTutorPrompt : regularAssistantPrompt
      });
    }
    
    // Send all messages to maintain conversation context
    await sendRequest(conversation.value);
  } catch (error) {
    console.error('Failed to get AI response:', error);
  }
};

const handleSuccess = (data) => {
  if (data.choices && data.choices.length > 0) {
    // Add AI response to conversation
    const responseIndex = conversation.value.length;
    const responseContent = data.choices[0].message.content;
    
    conversation.value.push({
      role: 'assistant',
      content: responseContent
    });
    
    // Auto-format math content if detected
    if (containsMath(responseContent)) {
      formattedMessages.value[responseIndex] = renderKaTeX(responseContent);
    }
    
    // Read the response aloud if text-to-speech is enabled
    readAloud(responseContent);
    
    // Clear input
    userMessage.value = '';
  }
};

const handleError = (error) => {
  console.error('OpenAI API error:', error);
};

// Initialize conversation with system message
onMounted(() => {
  loadKaTeX();
  
  // Initialize with appropriate system message
  conversation.value = [
    { role: 'system', content: teachingMode.value ? socraticTutorPrompt : regularAssistantPrompt }
  ];
});

// Initialize voices when component mounts
onMounted(() => {
  // Load available voices
  speechSynthesis.getVoices();
  
  // Some browsers need this event to get voices
  speechSynthesis.onvoiceschanged = () => {
    speechSynthesis.getVoices();
  };
});

// Clean up speech synthesis when component is destroyed
onBeforeUnmount(() => {
  if (speechSynthesis.speaking) {
    speechSynthesis.cancel();
  }
});
</script>

<style scoped>
/* KaTeX styling */
:deep(.katex-preview) {
  font-size: 1.1em;
  line-height: 1.5;
}

:deep(.katex) {
  font-size: 1.1em;
}

:deep(.katex-display) {
  margin: 1em 0;
  overflow-x: auto;
  overflow-y: hidden;
}

:deep(strong) {
  font-weight: 600;
}

:deep(br) {
  margin-bottom: 0.5em;
  display: block;
  content: "";
}
</style>














