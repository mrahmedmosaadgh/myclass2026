import { computed } from 'vue'

export function useFgPriority(tasksArray) {
  
  const sortedTasks = computed(() => {
    if (!tasksArray || !tasksArray.value) return []
    
    return [...tasksArray.value].sort((a, b) => {
      // 1. Importance (Descending -> higher number first)
      if (b.importance !== a.importance) {
        return b.importance - a.importance
      }
      
      // 2. Is Today (True first)
      if (b.is_today !== a.is_today) {
        return b.is_today ? 1 : -1
      }
      
      // 3. Due Date (Earliest first, nulls last)
      if (a.due_date && b.due_date) {
        return new Date(a.due_date) - new Date(b.due_date)
      }
      if (a.due_date) return -1
      if (b.due_date) return 1
      
      // 4. Sort Order (Ascending)
      return a.sort_order - b.sort_order
    })
  })

  return {
    sortedTasks
  }
}
