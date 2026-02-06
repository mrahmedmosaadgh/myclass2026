# Fix Realtime Notification Type Error

## Date: 2026-02-06

## Issue
-   **Error**: `TypeError` in `RealtimeNotificationService::notifyGroup`.
-   **Message**: `Argument #2 ($groupId) must be of type int, string given`.
-   **Cause**: The method signature strictly required `int $groupId`, but chat room IDs (and potentially other group identifiers) can be strings (e.g., "room_123").

## Work Done
-   **Refactoring**: Updated `RealtimeNotificationService.php`.
    -   Changed `notifyGroup` signature to accept `string|int $groupId`.
    -   `public function notifyGroup(string $groupType, string|int $groupId, string $event, array $context = []): bool`

## Next Steps
-   Verify chat functionality with string-based room IDs.
