# Teacher Session Troubleshooting

## 🚨 **Teacher Not Working - Quick Fix**

### **Step 1: Check Browser Console**

1. Open teacher view: `https://qudratpro.com/remote-control/question-responses/teacher`
2. Press `F12` to open browser console
3. Look for these messages:
   - ✅ `🔥 Firebase enabled by toolsSwitcher`
   - ✅ `✅ Signed in anonymously`
   - ✅ `Connected to channel: [CODE]`
   - ❌ Any red error messages?

### **Step 2: Test Connection**

Copy and paste this debug code in console:

```javascript
// Quick connection test
fetch('/remote-control/api/test-firebase')
  .then(r => r.json())
  .then(data => console.log('Firebase test:', data))
  .catch(e => console.error('Firebase test failed:', e))
```

### **Step 3: Common Issues & Fixes**

#### **Issue 1: Firebase Not Connected**

**Symptoms:**
- "Rate limit exceeded" errors
- No Firebase messages in console
- Start session button spins forever

**Fix:**
```javascript
// Clear Firebase cache and reload
localStorage.clear()
sessionStorage.clear()
location.reload()
```

#### **Issue 2: Rate Limiting**

**Symptoms:**
- `Rate limit exceeded, command dropped`
- Commands not sending

**Fix:** The rate limit has been increased to 50 calls/sec. If still happening:
```javascript
// Wait a few seconds and try again
```

#### **Issue 3: Network Issues**

**Symptoms:**
- Connection timeout
- "Failed to start session" error

**Fix:**
1. Check internet connection
2. Try different browser
3. Disable VPN/Proxy
4. Clear browser cache

#### **Issue 4: Browser Compatibility**

**Symptoms:**
- Nothing happens when clicking buttons
- Console shows JavaScript errors

**Fix:**
- Use Chrome, Firefox, or Edge
- Update browser to latest version
- Disable ad blockers temporarily

### **Step 4: Manual Test**

If automated fixes don't work, try this manual test:

1. **Generate Code**: Click "Generate Code" button
2. **Check Console**: Should show generated 6-digit code
3. **Start Session**: Click "Start Session"
4. **Watch Console**: Should show connection messages
5. **Test Question**: Try publishing a simple question

### **Step 5: Advanced Debug**

Run this in console for detailed debugging:

```javascript
// Load debug script and run tests
const script = document.createElement('script')
script.src = '/resources/js/Pages/myclass2026/features/remot_control/v1/examples/question_responses/debug_teacher.js'
document.head.appendChild(script)
```

### **Step 6: If Still Not Working**

**Check these files exist:**
- `/resources/js/Pages/myclass2026/features/remot_control/v1/core/composables/useRealtimeChannel.js`
- `/resources/js/Pages/myclass2026/features/remot_control/v1/examples/question_responses/composables/useQuestionSession.js`
- `/resources/js/Pages/myclass2026/features/remot_control/v1/examples/question_responses/TeacherView.vue`

**Check Firebase Rules:**
- Firebase should allow anonymous auth
- Database rules should allow read/write to `question_sessions` path

### **Step 7: Report Issue**

If none of the above works, provide this info:

1. **Browser and version**
2. **Console errors (screenshot)**
3. **Network tab errors**
4. **What exactly happens when you click buttons**

---

## 📋 **Working vs Not Working**

### **✅ Working Teacher Session Should Show:**

1. **Generate Code** → 6-digit code appears
2. **Start Session** → "Starting Session..." → "Session Active"
3. **Connection Status** → Green dot + "Connected"
4. **Publish Question** → Question appears in "Current Question"
5. **Student Join** → Response count increases

### **❌ Not Working Symptoms:**

1. **Button Clicks** → Nothing happens
2. **Loading Spinners** → Spin forever
3. **Red Error Messages** → In console
4. **Connection Status** → Red dot + "Disconnected"

---

## 🔧 **Quick Fixes Applied**

The following fixes have been implemented:

1. **Rate Limit Increased**: 10 → 50 calls/sec
2. **Better Error Handling**: Timeout and error messages
3. **Connection Retry**: Automatic reconnection
4. **Console Logging**: Better debug information
5. **Rate Limit Priority**: High-priority commands queue

---

## 🎯 **Test These URLs**

1. **Teacher View**: `https://qudratpro.com/remote-control/question-responses/teacher`
2. **Student View**: `https://qudratpro.com/remote-control/question-responses/student`
3. **Firebase Test**: `https://qudratpro.com/remote-control/api/test-firebase`
4. **Examples**: `https://qudratpro.com/remote-control/examples`

---

**If teacher still doesn't work after these steps, the issue is likely:**
- Firebase configuration problem
- Network/firewall blocking
- Server-side deployment issue
- Browser extension interference
