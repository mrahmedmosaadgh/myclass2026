## 🎯 **Implementation Steps for Offline-First User Context with Pinia**

### **Step 1: Backend - Modify HandleInertiaRequests.php** ✅ (Ready to implement)
- Add segmented `user_context` with 4 segments: profile, permissions, school, classroom, schedule
- Keep backward compatibility with existing `auth.user`
- Add 7-day cache metadata

### **Step 2: Update Dexie Schema**
- Add user context tables to `resources/js/offline/dexie.js`
- Add context cache management

### **Step 3: Create User Context Pinia Store**
- Create `resources/js/Stores/userContextStore.js`
- Integrate with existing offline system
- Handle 7-day cache expiry

### **Step 4: Create Vue Composable**
- Create `resources/js/composables/useUserContext.js`
- Easy access to segmented data
- Auto-sync with offline data

### **Step 5: Create API Endpoints**
- Add individual segment refresh endpoints
- Handle context updates

### **Step 6: Show Migration Examples**
- How existing pages keep working
- How new pages use segmented context

**Which step should I implement first?** (Step 1 is ready - just need your confirmation to proceed)
