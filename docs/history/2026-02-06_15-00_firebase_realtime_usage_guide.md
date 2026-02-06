# Firebase Realtime System: Complete Usage Guide

**Date:** 2026-02-06
**Status:** Ready for Production

---

## Overview
You now have a **fully reusable** Firebase notification system. You'll **NEVER** need to think about Firebase implementation details again.

---

## Backend Usage (Laravel)

### 1. Notify a User
```php
use App\Events\RealtimeEvent;

// When a message is sent
event(new RealtimeEvent(
    "user.{$recipientId}",
    'NEW_MESSAGE',
    ['conversation_id' => $conversation->id]
));
```

### 2. Notify a Class/Group
```php
// When a teacher posts an announcement
event(new RealtimeEvent(
    "class.{$classId}",
    'ANNOUNCEMENT',
    ['title' => 'Test Tomorrow', 'announcement_id' => 123]
));
```

### 3. System-Wide Broadcast
```php
// School closure notification
event(new RealtimeEvent(
    'system.all',
    'SCHOOL_CLOSURE',
    ['date' => '2026-02-10', 'reason' => 'Weather']
));
```

### 4. Using the Service Directly (Alternative)
```php
use App\Services\RealtimeNotificationService;

app(RealtimeNotificationService::class)->notifyUser(
    $userId,
    'GRADE_UPDATED',
    ['exam_id' => 55, 'score' => 92]
);
```

---

## Frontend Usage (Vue)

### 1. Global Setup (One-Time)
In your main layout (`AppLayout.vue`, `AppLayoutDefault.vue`, etc.):

```vue
<template>
    <div>
        <!-- Your normal layout -->
        <slot />
        
        <!-- Add this ONCE -->
        <RealtimeListener />
    </div>
</template>

<script setup>
import RealtimeListener from '@/Components/Realtime/RealtimeListener.vue';
</script>
```

**That's it!** Now the entire app listens for realtime updates.

---

### 2. Custom Listening in a Specific Page
If you want to listen to a specific channel in ONE page (without global listening):

```vue
<script setup>
import { useRealtimeChannel } from '@/composables/useRealtimeChannel';
import { computed } from 'vue';

const props = defineProps(['classId']);

// Listen to class chat
const { data: signal } = useRealtimeChannel(
    computed(() => `class.${props.classId}`),
    (signal) => {
        if (signal.event === 'NEW_MESSAGE') {
            // Refresh messages
            fetchMessages();
        }
    }
);
</script>
```

---

### 3. Listening to Multiple Channels
```vue
<script setup>
import { useRealtimeChannel } from '@/composables/useRealtimeChannel';

// User notifications
useRealtimeChannel('user.123', (signal) => {
    console.log('Personal signal:', signal);
});

// Class updates
useRealtimeChannel('class.7A', (signal) => {
    console.log('Class signal:', signal);
});

// System broadcasts
useRealtimeChannel('system.all', (signal) => {
    console.log('System signal:', signal);
});
</script>
```

---

## Real-World Examples

### Example 1: Chat Message Notification

**Backend (MessageController.php):**
```php
public function store(Request $request)
{
    $message = Message::create([
        'conversation_id' => $request->conversation_id,
        'sender_id' => auth()->id(),
        'body' => $request->body
    ]);

    // Get recipient
    $recipientId = $message->conversation
        ->participants()
        ->where('user_id', '!=', auth()->id())
        ->first()->id;

    // Send Firebase signal
    event(new RealtimeEvent(
        "user.{$recipientId}",
        'NEW_MESSAGE',
        ['conversation_id' => $message->conversation_id]
    ));

    return response()->json($message);
}
```

**Frontend (RealtimeListener.vue - already set up!):**
```js
function handleNewMessage(signal) {
    // Fetch the actual message from Laravel
    axios.get(`/api/conversations/${signal.context.conversation_id}/messages`)
        .then(response => {
            // Update UI
            updateChatMessages(response.data);
        });
}
```

---

### Example 2: Grade Posted Notification

**Backend (GradeController.php):**
```php
public function update(Request $request, Grade $grade)
{
    $grade->update(['score' => $request->score]);

    // Notify student
    event(new RealtimeEvent(
        "user.{$grade->student_id}",
        'GRADE_UPDATED',
        [
            'exam_id' => $grade->exam_id,
            'score' => $grade->score
        ]
    ));

    return response()->json($grade);
}
```

**Frontend (Student Dashboard):**
```vue
<script setup>
import { useRealtimeChannel } from '@/composables/useRealtimeChannel';
import { ref } from 'vue';

const showGradeToast = ref(false);

useRealtimeChannel(`user.${studentId}`, (signal) => {
    if (signal.event === 'GRADE_UPDATED') {
        showGradeToast.value = true;
        // Or refresh the grades table
    }
});
</script>
```

---

### Example 3: Class Announcement

**Backend (AnnouncementController.php):**
```php
public function store(Request $request)
{
    $announcement = Announcement::create($request->all());

    // Notify entire class
    event(new RealtimeEvent(
        "class.{$announcement->class_id}",
        'ANNOUNCEMENT',
        [
            'title' => $announcement->title,
            'announcement_id' => $announcement->id
        ]
    ));

    return response()->json($announcement);
}
```

**Frontend:**
Already handled by `RealtimeListener.vue` automatically!

---

## Customizing Event Handlers

Open `resources/js/Components/Realtime/RealtimeListener.vue` and add your custom logic:

```js
function handleUserSignal(signal) {
    switch (signal.event) {
        case 'NEW_MESSAGE':
            // Your custom logic
            break;
        
        case 'CUSTOM_EVENT':
            // Add new event types here
            break;
    }
}
```

---

## Best Practices

### ✅ DO:
- Fire `RealtimeEvent` after saving to database
- Keep `context` minimal (IDs, not full objects)
- Use descriptive event names (`NEW_MESSAGE`, not `msg`)

### ❌ DON'T:
- Put message content in the signal
- Call Firebase directly from controllers
- Create new event classes for each feature

---

## Troubleshooting

### Signal not received?
1. Check `.env`: `FIREBASE_DATABASE_URL` is set
2. Check console: `🔔 Signal received on user.123`
3. Verify Firebase rules allow read/write

### Multiple signals firing?
- This is normal! Firebase sends the signal every time the data changes.
- Use `trigger_id` to detect duplicates if needed.

---

## Summary

**Backend:** Just fire one event
```php
event(new RealtimeEvent($channel, $event, $context));
```

**Frontend:** Drop one component
```vue
<RealtimeListener />
```

**Done!** 🎉
