<template>
  <div class="bg-white rounded-lg shadow-lg p-6">
    <div class="mb-4">
      <h2 class="text-xl font-bold text-gray-800 mb-2">Simple Real-Time Chat</h2>
      <div class="flex items-center space-x-4 text-sm">
        <div class="flex items-center">
          <div 
            class="w-2 h-2 rounded-full mr-2"
            :class="isConnected ? 'bg-green-500' : 'bg-red-500'"
          ></div>
          <span :class="isConnected ? 'text-green-600' : 'text-red-600'">
            {{ isConnected ? 'Connected' : 'Disconnected' }}
          </span>
        </div>
        <div class="text-gray-500">
          Channel: {{ channelId }}
        </div>
        <div class="text-gray-500">
          Users: {{ userCount }}
        </div>
      </div>
    </div>

    <!-- Chat Messages -->
    <div class="bg-gray-50 rounded-lg p-4 h-96 overflow-y-auto mb-4">
      <div v-if="messages.length === 0" class="text-center text-gray-500 py-8">
        No messages yet. Start the conversation!
      </div>
      
      <div v-else class="space-y-3">
        <div
          v-for="(message, index) in messages"
          :key="message.id"
          class="flex"
          :class="message.senderId === currentUserId ? 'justify-end' : 'justify-start'"
        >
          <div
            class="max-w-xs lg:max-w-md px-4 py-2 rounded-lg"
            :class="message.senderId === currentUserId 
              ? 'bg-blue-500 text-white' 
              : 'bg-gray-200 text-gray-800'"
          >
            <div class="text-xs opacity-75 mb-1">
              {{ message.senderId === currentUserId ? 'You' : message.senderName }}
            </div>
            <div class="break-words">
              {{ message.text }}
            </div>
            <div class="text-xs opacity-75 mt-1">
              {{ formatTime(message.timestamp) }}
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Message Input -->
    <div class="space-y-3">
      <div class="flex space-x-2">
        <input
          v-model="newMessage"
          @keyup.enter="sendMessage"
          type="text"
          placeholder="Type your message..."
          class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          :disabled="!isConnected"
        />
        <button
          @click="sendMessage"
          :disabled="!isConnected || !newMessage.trim()"
          class="px-6 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors"
        >
          Send
        </button>
      </div>
      
      <!-- User Name Input -->
      <div class="flex items-center space-x-2">
        <input
          v-model="userName"
          type="text"
          placeholder="Your name..."
          class="px-3 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500"
        />
        <button
          @click="updateUserName"
          :disabled="!userName.trim()"
          class="px-3 py-1 text-sm bg-gray-500 text-white rounded hover:bg-gray-600 disabled:bg-gray-300 transition-colors"
        >
          Update Name
        </button>
      </div>
    </div>

    <!-- Typing Indicator -->
    <div v-if="typingUsers.length > 0" class="mt-3 text-sm text-gray-500 italic">
      {{ typingText }}
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRealtimeChannel } from '../../core/composables/useRealtimeChannel.js'

export default {
  name: 'ChatExample',
  props: {
    channelId: {
      type: String,
      default: 'chat-example-1'
    }
  },
  setup(props) {
    // Initialize channel
    const channel = useRealtimeChannel(props.channelId, {
      persistence: true,
      debounce: 100,
      validateCommands: true
    })

    // Reactive state
    const messages = ref([])
    const newMessage = ref('')
    const userName = ref('')
    const currentUserId = ref('')
    const userCount = ref(0)
    const typingUsers = ref([])
    const typingTimeout = ref(null)

    // Computed properties
    const isConnected = computed(() => channel.isConnected.value === 'connected')
    const typingText = computed(() => {
      if (typingUsers.value.length === 1) {
        return `${typingUsers.value[0]} is typing...`
      } else if (typingUsers.value.length === 2) {
        return `${typingUsers.value[0]} and ${typingUsers.value[1]} are typing...`
      } else if (typingUsers.value.length > 2) {
        return `${typingUsers.value.length} people are typing...`
      }
      return ''
    })

    // Initialize user
    const initializeUser = () => {
      const storedUserId = localStorage.getItem(`chat_user_id_${props.channelId}`)
      const storedUserName = localStorage.getItem(`chat_user_name_${props.channelId}`)
      
      currentUserId.value = storedUserId || `user_${Date.now()}`
      userName.value = storedUserName || `User ${currentUserId.value.slice(-4)}`
      
      localStorage.setItem(`chat_user_id_${props.channelId}`, currentUserId.value)
      localStorage.setItem(`chat_user_name_${props.channelId}`, userName.value)
      
      // Announce user joined
      channel.sendCommand('user_joined', {
        userId: currentUserId.value,
        userName: userName.value
      })
    }

    // Send message
    const sendMessage = () => {
      if (!newMessage.value.trim() || !isConnected.value) return
      
      const message = {
        id: `msg_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`,
        text: newMessage.value.trim(),
        senderId: currentUserId.value,
        senderName: userName.value,
        timestamp: new Date().toISOString()
      }
      
      channel.sendCommand('new_message', message)
      newMessage.value = ''
      
      // Stop typing indicator
      stopTyping()
    }

    // Update user name
    const updateUserName = () => {
      if (!userName.value.trim()) return
      
      localStorage.setItem(`chat_user_name_${props.channelId}`, userName.value.trim())
      
      channel.sendCommand('user_name_changed', {
        userId: currentUserId.value,
        userName: userName.value.trim()
      })
    }

    // Handle typing indicator
    const startTyping = () => {
      if (typingTimeout.value) clearTimeout(typingTimeout.value)
      
      channel.sendCommand('typing_start', {
        userId: currentUserId.value,
        userName: userName.value
      })
      
      typingTimeout.value = setTimeout(() => {
        stopTyping()
      }, 3000)
    }

    const stopTyping = () => {
      if (typingTimeout.value) {
        clearTimeout(typingTimeout.value)
        typingTimeout.value = null
      }
      
      channel.sendCommand('typing_stop', {
        userId: currentUserId.value
      })
    }

    // Handle incoming commands
    const handleCommand = (command) => {
      switch (command.type) {
        case 'new_message':
          if (command.payload) {
            messages.value.push(command.payload)
            // Keep only last 50 messages
            if (messages.value.length > 50) {
              messages.value = messages.value.slice(-50)
            }
          }
          break
          
        case 'user_joined':
          updateUserList()
          if (command.payload && command.payload.userId !== currentUserId.value) {
            // Add system message
            messages.value.push({
              id: `sys_${Date.now()}`,
              text: `${command.payload.userName} joined the chat`,
              senderId: 'system',
              senderName: 'System',
              timestamp: new Date().toISOString(),
              isSystem: true
            })
          }
          break
          
        case 'user_name_changed':
          updateUserList()
          if (command.payload && command.payload.userId !== currentUserId.value) {
            messages.value.push({
              id: `sys_${Date.now()}`,
              text: `${command.payload.userName} changed their name`,
              senderId: 'system',
              senderName: 'System',
              timestamp: new Date().toISOString(),
              isSystem: true
            })
          }
          break
          
        case 'typing_start':
          if (command.payload && command.payload.userId !== currentUserId.value) {
            if (!typingUsers.value.find(u => u === command.payload.userName)) {
              typingUsers.value.push(command.payload.userName)
            }
          }
          break
          
        case 'typing_stop':
          if (command.payload && command.payload.userId !== currentUserId.value) {
            typingUsers.value = typingUsers.value.filter(u => u !== userName.value)
          }
          break
      }
    }

    // Update user list
    const updateUserList = () => {
      // This is a simplified version - in a real app you'd maintain a proper user list
      userCount.value = Math.max(1, userCount.value + (Math.random() > 0.5 ? 1 : -1))
    }

    // Format time
    const formatTime = (timestamp) => {
      const date = new Date(timestamp)
      return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
    }

    // Watch for typing
    const handleTyping = () => {
      if (newMessage.value.trim()) {
        startTyping()
      } else {
        stopTyping()
      }
    }

    // Initialize
    onMounted(async () => {
      await channel.connect()
      initializeUser()
      
      // Set up command listener
      channel.onCommand(handleCommand)
      
      // Set up typing watcher
      const unwatch = channel.watch(newMessage, handleTyping)
      
      // Initial user count
      userCount.value = 1
    })

    onUnmounted(() => {
      // Announce user left
      if (isConnected.value) {
        channel.sendCommand('user_left', {
          userId: currentUserId.value,
          userName: userName.value
        })
      }
      
      channel.disconnect()
    })

    return {
      messages,
      newMessage,
      userName,
      currentUserId,
      userCount,
      typingUsers,
      typingText,
      isConnected,
      sendMessage,
      updateUserName,
      formatTime
    }
  }
}
</script>

<style scoped>
/* Custom scrollbar for chat messages */
.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}
</style>
