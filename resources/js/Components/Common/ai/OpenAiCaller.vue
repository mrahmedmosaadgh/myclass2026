<template>
  <div>
    <slot
      :response="response"
      :loading="loading"
      :error="error"
      :sendRequest="sendRequest"
      :reset="reset"
    ></slot>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
  apiKey: {
    type: String,
    default: import.meta.env.VITE_OPENAI_API_KEY || ''
  },
  model: {
    type: String,
    default: 'gpt-4o-mini'
  },
  maxTokens: {
    type: Number,
    default: 1000
  },
  temperature: {
    type: Number,
    default: 0.7
  },
  store: {
    type: Boolean,
    default: true
  }
});

const emit = defineEmits(['success', 'error']);

const response = ref(null);
const loading = ref(false);
const error = ref(null);
const lastRequestTime = ref(0);
const retryCount = ref(0);
const MAX_RETRIES = 3;

// Implement exponential backoff for retries
const getBackoffTime = (retry) => {
  // Start with 1 second, then 2, then 4, etc.
  return Math.min(Math.pow(2, retry) * 1000, 30000); // Cap at 30 seconds
};

// Throttle requests to avoid hitting rate limits
const throttleRequest = async () => {
  const now = Date.now();
  const timeSinceLastRequest = now - lastRequestTime.value;
  const minRequestInterval = 1000; // 1 second minimum between requests
  
  if (timeSinceLastRequest < minRequestInterval) {
    // Wait for the remaining time to reach the minimum interval
    const waitTime = minRequestInterval - timeSinceLastRequest;
    await new Promise(resolve => setTimeout(resolve, waitTime));
  }
  
  lastRequestTime.value = Date.now();
};

const sendRequest = async (messages, customOptions = {}, retry = 0) => {
  if (!messages || !Array.isArray(messages) || messages.length === 0) {
    error.value = 'Messages are required and must be an array';
    return;
  }

  const apiKey = customOptions.apiKey || props.apiKey;
  if (!apiKey) {
    error.value = 'OpenAI API key is required';
    return;
  }

  // Only set loading state on first attempt, not retries
  if (retry === 0) {
    loading.value = true;
    error.value = null;
    retryCount.value = 0;
  }

  // Throttle requests to avoid rate limits
  await throttleRequest();

  const options = {
    model: customOptions.model || props.model,
    messages: messages,
    max_tokens: customOptions.maxTokens || props.maxTokens,
    temperature: customOptions.temperature || props.temperature,
    store: customOptions.store !== undefined ? customOptions.store : props.store
  };

  try {
    const result = await fetch('https://api.openai.com/v1/chat/completions', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${apiKey}`
      },
      body: JSON.stringify(options)
    });

    // Handle rate limiting (429 errors)
    if (result.status === 429) {
      const retryAfter = parseInt(result.headers.get('Retry-After') || '20');
      
      // If we haven't exceeded max retries, wait and try again
      if (retry < MAX_RETRIES) {
        const waitTime = retryAfter * 1000 || getBackoffTime(retry);
        console.log(`Rate limited. Retrying in ${waitTime/1000} seconds...`);
        
        // Wait for the specified time
        await new Promise(resolve => setTimeout(resolve, waitTime));
        
        // Retry the request with incremented retry count
        return sendRequest(messages, customOptions, retry + 1);
      } else {
        throw new Error('Rate limit exceeded. Maximum retries reached.');
      }
    }

    if (!result.ok) {
      const errorData = await result.json();
      throw new Error(errorData.error?.message || `API request failed with status ${result.status}`);
    }

    const data = await result.json();
    response.value = data;
    emit('success', data);
    return data;
  } catch (err) {
    error.value = err.message || 'An error occurred';
    emit('error', err);
    throw err;
  } finally {
    // Only clear loading state if this is the original request or final retry
    if (retry === 0 || retry >= MAX_RETRIES) {
      loading.value = false;
    }
  }
};

const reset = () => {
  response.value = null;
  error.value = null;
};

defineExpose({
  sendRequest,
  reset,
  response,
  loading,
  error
});
</script>
