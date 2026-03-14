<template>
  <div style="height: 100vh; overflow: hidden;">
    <TaskList
      v-model="tasks"
      :topics="topics"
      @update:modelValue="onTasksUpdated"
    />
  </div>
</template>

<script>
import TaskList from './TaskList.vue'

export default {
  components: { TaskList },
  data() {
    return {
      // Define your topics
      topics: [
        { id: 'work',     name: 'Work',     color: '#4ECDC4' },
        { id: 'personal', name: 'Personal', color: '#FF6B6B' },
        { id: 'study',    name: 'Study',    color: '#A78BFA' },
        { id: 'health',   name: 'Health',   color: '#34D399' },
      ],

      // Your initial task data (JSON)
      tasks: [
        {
          id: 1,
          title: 'Prepare Work Report',
          completed: false,
          dueDate: '2024-09-05',   // YYYY-MM-DD or ''
          topic: 'work',            // topic id or ''
          notes: 'Deadline is Friday',
          subtasks: [
            { id: 11, title: 'Organize Documents', completed: false },
            { id: 12, title: 'Prepare Presentation', completed: false },
          ]
        },
        {
          id: 2,
          title: 'Morning Run',
          completed: false,
          dueDate: '2024-09-05',
          topic: 'health',
          notes: '',
          subtasks: []
        },
        {
          id: 3,
          title: 'Call Family',
          completed: false,
          dueDate: '2024-09-07',
          topic: 'personal',
          notes: '',
          subtasks: []
        }
      ]
    }
  },
  methods: {
    onTasksUpdated(updatedTasks) {
      // updatedTasks is the full updated JSON array
      // Save to your API, store, localStorage, etc.
      console.log('Tasks updated:', JSON.stringify(updatedTasks, null, 2))
    }
  }
}
</script>
