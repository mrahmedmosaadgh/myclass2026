<template>
    <div class="p-4 rounded shadow bg-white max-w-md mx-auto">
      <h2 class="text-xl font-semibold mb-4">Chat with AI</h2>
      <div class="space-y-2 mb-4">
        <div v-for="(msg, index) in messages" :key="index" class="text-sm">
          <div :class="msg.role === 'user' ? 'text-blue-600' : 'text-gray-700'">
            <strong>{{ msg.role }}:</strong> {{ msg.content }}
          </div>
        </div>
      </div>
      <form @submit.prevent="sendMessage">
        <input
          v-model="input"
          type="text"
          placeholder="Type your message..."
          class="w-full border rounded p-2"
        />
        <button type="submit" class="mt-2 bg-blue-500 text-white px-4 py-1 rounded hover:bg-blue-600">
          Send
        </button>
      </form>
    </div>
  </template>

  <script setup>
  import { ref } from 'vue'
  import axios from 'axios'

  const input = ref('')
  const messages = ref([
    { role: 'system', content: 'You are a helpful assistant.' },
  ])

  const sendMessage = async () => {
    if (!input.value.trim()) return

    messages.value.push({ role: 'user', content: input.value.trim() })

    try {
      const response = await axios.post(
        'https://api.openai.com/v1/chat/completions',
        {
          model: 'gpt-3.5-turbo',
          messages: messages.value,
        },
        {
          headers: {
            'Authorization': `Bearer ${import.meta.env.VITE_OPENAI_API_KEY}`,
            'Content-Type': 'application/json',
          },
        }
      )

      const reply = response.data.choices[0].message
      messages.value.push(reply)
      input.value = ''
    } catch (error) {
      console.error('OpenAI API error:', error)
      messages.value.push({
        role: 'assistant',
        content: '⚠️ Error: Could not connect to OpenAI API.',
      })
    }
  }
  </script>

  <style scoped>
  input:focus {
    outline: none;
    border-color: #3b82f6;
  }
  </style>
