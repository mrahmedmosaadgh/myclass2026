# Adding "Quiz Mode" to Teacher Menu

To make the "Quiz Mode" appear in the teacher's sidebar menu, follow these steps:

1. **Verify Route exists**:
   - I have added `Route::get('/reward_sys/quiz', ...)->name('reward_sys.quiz');` to `web.php`.
   - This ensures the link works.

2. **Add Menu Item (SQL)**:
   - Run this SQL query in your database:

```sql
INSERT INTO menus (label, label_ar, route, permission, module, parent_id, `order`, icon, is_active, is_feature_flag, feature_flag_key, v2_component, requires_context, role_specific, v2_enabled, created_at, updated_at) 
VALUES ('Quiz Mode', 'وضع الاختبار', 'reward_sys.quiz', NULL, NULL, NULL, 5, 'quiz', 1, 0, NULL, NULL, 0, 'teacher', 0, NOW(), NOW());
```

**Note:**
- **route**: Must be `reward_sys.quiz`.
- **icon**: `quiz` (Check if this icon exists in your Quasar icon set, otherwise use `timer`).
- **parent_id**: Set to `NULL` for top-level, or find the ID of a parent menu if you want it nested.
