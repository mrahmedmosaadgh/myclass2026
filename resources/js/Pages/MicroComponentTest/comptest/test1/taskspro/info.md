how to use
Here's your TickTick-style Vue component! Here's a quick overview of what's included:

**Features:**
- **3-panel layout** — sidebar, task list, detail panel (just like TickTick)
- **Add tasks** with inline `+` bar, pressing Enter adds and keeps input open for fast entry
- **Subtasks** — expandable per task, add/complete/delete inline
- **Due date** — date picker on each task, color-coded (overdue = red, today = amber)
- **Topics** — color-coded badges, filterable from sidebar
- **Views** — Today, Next 7 Days, All Tasks, Completed with live counts
- **Detail panel** — click any task to edit title, notes, due date, topic, subtasks
- **Animated** — smooth add/remove transitions

**Component API:**
```vue
<TaskList
  v-model="tasks"        <!-- two-way JSON binding -->
  :topics="topics"       <!-- optional custom topics -->
/>
```

**Task JSON shape:**
```json
{
  "id": 1,
  "title": "Task name",
  "completed": false,
  "dueDate": "2024-09-05",
  "topic": "work",
  "notes": "...",
  "subtasks": [
    { "id": 11, "title": "Sub item", "completed": false }
  ]
}
```

Every change emits `update:modelValue` with the full updated array — plug in your API call or Pinia store there.