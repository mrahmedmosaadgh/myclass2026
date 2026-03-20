import { defineStore } from 'pinia'
import { fgIdb } from '../services/fg-idb.service'
import { useFgSync } from '../composables/fg-use-sync'
import { useFgTasksStore } from './fg-tasks.store'

export const useFgSubTasksStore = defineStore('fg-sub-tasks', {
  state: () => ({
    subTasks: [],
    loading: false,
    error: null,
  }),

  getters: {
    getByTaskId: (state) => (taskId) => state.subTasks.filter(st => st.task_id === taskId),
  },

  actions: {
    async fetchSubTasks(taskId) {
      this.loading = true
      try {
        const localData = await fgIdb.getAllActive('sub_tasks')
        this.subTasks = localData.filter(st => st.task_id === taskId)
        
        const { isOnline, syncAll } = useFgSync()
        if (isOnline.value) syncAll()
        
        this.error = null
      } catch (err) {
        this.error = 'Failed to fetch sub-tasks'
        console.error(err)
      } finally {
        this.loading = false
      }
    },

    async createSubTask(payload) {
      try {
        const saved = await fgIdb.saveLocally('sub_tasks', {
            ...payload,
            is_done: false,
            sort_order: payload.sort_order ?? 0
        })
        
        this.subTasks.push(saved)
        
        const { syncAll } = useFgSync()
        syncAll()
        
        return saved
      } catch (err) {
        console.error('Failed to create sub-task strictly locally', err)
        throw err
      }
    },

    async updateSubTask(id, payload) {
      try {
        const existing = this.subTasks.find(st => st.id === id)
        if (!existing) throw new Error('Sub-task not found locally')
        
        const updated = await fgIdb.saveLocally('sub_tasks', { ...existing, ...payload })
        
        const index = this.subTasks.findIndex(st => st.id === id)
        if (index !== -1) {
            this.subTasks.splice(index, 1, updated)
        }
        
        const { syncAll } = useFgSync()
        syncAll()
        
        // Auto-complete parent task logic if all subtasks are done
        if (payload.is_done) {
          const taskObj = existing.task_id
          const allForTask = this.getByTaskId(taskObj)
          if (allForTask.length > 0 && allForTask.every(st => st.is_done || (st.id === id && payload.is_done))) {
             const tasksStore = useFgTasksStore()
             const pTask = tasksStore.getById(taskObj)
             if (pTask && pTask.status !== 'done') {
                await tasksStore.updateTask(taskObj, { status: 'done' })
             }
          }
        }
        
        return updated
      } catch (err) {
        console.error('Failed to update sub-task locally', err)
        throw err
      }
    },

    async deleteSubTask(id) {
      try {
        const existing = this.subTasks.find(t => t.id === id)
        if (!existing) return
        await fgIdb.saveLocally('sub_tasks', { ...existing, deleted_at: new Date().toISOString() })
        
        this.subTasks = this.subTasks.filter(st => st.id !== id)
        
        const { syncAll } = useFgSync()
        syncAll()
      } catch (err) {
        console.error('Failed to soft delete sub-task', err)
        throw err
      }
    }
  }
})
