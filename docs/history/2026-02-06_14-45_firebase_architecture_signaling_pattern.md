# Firebase Communication Architecture: The "Signaling Engine" Pattern

**Date:** 2026-02-06
**Context:** Architectural blueprint for unifying Private, Group, and Public communication using Laravel and Firebase.

## 1. Core Philosophy: Separate Audience, Not Technology
Instead of building separate systems for Chat, Announcements, and Alerts, we use **one communication engine** with **multiple delivery strategies**.
-   **Backend:** Single unified Laravel backend.
-   **Realtime:** Single Firebase instance.
-   **Frontend:** Unified Vue listeners.
-   **Differentiation:** Targeting rules (scope) define the behavior.

## 2. Intent-Based Scopes
We classify communication by **Audience Scope**, not by feature name.

| Scope | Intent | Subscription / Topic | Example |
| :--- | :--- | :--- | :--- |
| **Private** | 1-to-1 | `user.{id}` | Teacher ↔ Student chat |
| **Group** | 1-to-Many (Controlled) | `class.{id}`, `group.{id}` | Class discussions, Team projects |
| **Public** | Broadcast | `system.all` | School closures, Global announcements |

## 3. Database Schema (Unified)
The database remains the "Source of Truth". Firebase is ephemeral signaling.

### `conversations`
-   `id`: PK
-   `type`: 'private' | 'group' | 'public'
-   `scope_type`: 'class', 'school', 'system'
-   `scope_id`: nullable (e.g., class_id)

### `conversation_participants`
-   `conversation_id`
-   `user_id`
-   `role`: 'admin', 'moderator', 'member'

### `messages`
-   `conversation_id`
-   `sender_id`
-   `body`: Text content
-   `created_at`

## 4. The "Signaling" Event Flow
**Crucial Rule:** Firebase does NOT carry the message payload (content). It only carries the signal *that* something happened.

1.  **Laravel:** Saves message to MySQL.
2.  **Laravel:** Fires Event (e.g., `MessageSent`).
3.  **Firebase:** Emits signal to relevant topic (e.g., `user.123` or `class.7A`).
    *   *Payload:* `{ type: "NEW_MESSAGE", conversation_id: 55, trigger_id: 99 }`
4.  **Vue (Frontend):**  Listens to the topic.
5.  **Vue:** Receives signal -> Validates -> Calls API (`GET /conversations/55/messages`).
6.  **Vue:** UI Updates with secure data from Laravel.

## 5. Security & Permissions
-   **Firebase** says: "Hey, look here!"
-   **Laravel** says: "Are you allowed to see this?"
-   **Vue (UI)**: Hides/Shows based on Laravel's response.
-   **Result:** Even if a user listens to `class.8B` topic hacked/illegally, the API call to fetch data will fail (403 Forbidden) because Laravel checks permissions.

## 6. Notification Strategy
-   **Private:** Listen to `user.{my_id}`.
-   **Class/Group:** Listen to `class.{my_class_id}`.
-   **Public:** Listen to `system.all`.

### Public Notifications
Used for "School Closed" or "Maintenance".
-   Stored in `notifications` table.
-   Target: `public`, `role:teacher`, etc.
-   Frontend hears `system.all`, fetches "my unread notifications".

## 7. Future Implementation Roadmap
1.  **Backend:** Define Laravel Events and the exact JSON payload for Firebase signals.
2.  **Frontend:** Build a composable (`useChatListener`) to handle topic subscriptions dynamically.
3.  **UI:** Create distinct UI states for Private (Quick Chat) vs Full Page (Group/Public).
4.  **Resilience:** Implement Offline-first syncing (using Dexie.js) for when the signal is missed or network is down.

---
*Summary: This approach aligns myclass2026 with enterprise-grade LMS standards (Canvas/Blackboard interaction models), ensuring security, scalability, and code reusability.*
