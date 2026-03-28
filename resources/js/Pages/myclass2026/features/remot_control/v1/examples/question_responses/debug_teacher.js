/**
 * Debug Teacher Session - Simple Test
 * Run this in browser console to debug teacher issues
 */

// Test 1: Check Firebase is available
console.log('=== Firebase Debug ===')
console.log('Firebase available:', typeof database !== 'undefined')

// Test 2: Try to create a simple channel
try {
  const testChannel = useRealtimeChannel('debug-test-123', {
    firebasePath: 'question_sessions',
    persistence: true,
    logEvents: true,
    rateLimitMaxCalls: 50,
    rateLimitWindowMs: 1000
  })
  
  console.log('Channel created:', testChannel)
  console.log('Is connected:', testChannel.isConnected.value)
  console.log('Last error:', testChannel.lastError?.value)
  
  // Test 3: Try to send a test command
  setTimeout(() => {
    console.log('Sending test command...')
    const result = testChannel.sendCommand('test', { message: 'Hello from teacher' }, {
      priority: 'high'
    })
    console.log('Command sent result:', result)
  }, 2000)
  
} catch (error) {
  console.error('Channel creation failed:', error)
}

// Test 4: Check useQuestionSession
try {
  const testSession = useQuestionSession('TEST123', 'teacher')
  console.log('Session created:', testSession)
  console.log('Session connected:', testSession.isConnected.value)
  console.log('Session status:', testSession.sessionStatus.value)
  
  // Test publishing
  setTimeout(() => {
    console.log('Testing publish question...')
    const result = testSession.publishQuestion({
      type: 'multiple_choice',
      title: 'Test Question',
      options: ['Option 1', 'Option 2']
    })
    console.log('Publish result:', result)
  }, 3000)
  
} catch (error) {
  console.error('Session creation failed:', error)
}

console.log('=== End Debug ===')

// Instructions:
// 1. Open teacher view: https://qudratpro.com/remote-control/question-responses/teacher
// 2. Open browser console (F12)
// 3. Paste and run this code
// 4. Check the output for errors
