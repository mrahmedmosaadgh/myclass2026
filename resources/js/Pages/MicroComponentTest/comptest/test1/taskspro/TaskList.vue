<template>
  <div class="task-app">
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="sidebar-header">
        <div class="app-logo">
          <svg width="28" height="28" viewBox="0 0 28 28" fill="none">
            <rect width="28" height="28" rx="8" fill="#4ECDC4"/>
            <path d="M8 14l4 4 8-8" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          <span>Tasks</span>
        </div>
      </div>

      <nav class="sidebar-nav">
        <button
          v-for="view in views"
          :key="view.id"
          class="nav-item"
          :class="{ active: activeView === view.id }"
          @click="activeView = view.id"
        >
          <span class="nav-icon">{{ view.icon }}</span>
          <span class="nav-label">{{ view.label }}</span>
          <span v-if="getViewCount(view.id)" class="nav-count">{{ getViewCount(view.id) }}</span>
        </button>
      </nav>

      <div class="sidebar-section">
        <div class="section-title">Topics</div>
        <button
          v-for="topic in topics"
          :key="topic.id"
          class="nav-item topic-item"
          :class="{ active: activeTopicFilter === topic.id }"
          @click="toggleTopicFilter(topic.id)"
        >
          <span class="topic-dot" :style="{ background: topic.color }"></span>
          <span class="nav-label">{{ topic.name }}</span>
          <span class="nav-count">{{ getTopicCount(topic.id) }}</span>
        </button>
      </div>
    </aside>

    <!-- Main task list -->
    <main class="task-main">
      <div class="task-header">
        <h1 class="task-title">{{ currentViewLabel }}</h1>
        <div class="header-actions">
          <button class="icon-btn" title="Sort" @click="cycleSortOrder">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
              <path d="M2 4h12M4 8h8M6 12h4"/>
              <path d="M2 4h12M4 8h8M6 12h4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
          </button>
        </div>
      </div>

      <!-- Add task input -->
      <div class="add-task-bar" :class="{ focused: addingTask }">
        <span class="add-icon">+</span>
        <input
          v-if="addingTask"
          ref="addTaskInput"
          v-model="newTaskTitle"
          class="add-task-input"
          placeholder="Task name"
          @keydown.enter="confirmAddTask"
          @keydown.escape="cancelAddTask"
          @blur="handleAddBlur"
        />
        <span v-else class="add-task-placeholder" @click="startAddTask">Add Task</span>
        <div v-if="addingTask" class="add-task-meta">
          <button class="meta-btn" :class="{ active: newTaskDueDate }" @click="showDueDatePicker = !showDueDatePicker" title="Set due date">
            <svg width="14" height="14" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.5">
              <rect x="1" y="2" width="12" height="11" rx="2"/>
              <path d="M1 6h12M4 1v2M10 1v2"/>
            </svg>
            <span v-if="newTaskDueDate">{{ formatDate(newTaskDueDate) }}</span>
          </button>
          <div v-if="showDueDatePicker" class="date-picker-popup" @mousedown.prevent>
            <input type="date" v-model="newTaskDueDate" class="date-native" @change="showDueDatePicker = false" />
          </div>
          <button class="meta-btn" :class="{ active: newTaskTopic }" @click="showTopicPicker = !showTopicPicker" title="Set topic">
            <svg width="14" height="14" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.5">
              <circle cx="7" cy="7" r="5"/>
            </svg>
            <span v-if="newTaskTopic">{{ getTopicName(newTaskTopic) }}</span>
          </button>
          <div v-if="showTopicPicker" class="topic-picker-popup" @mousedown.prevent>
            <button
              v-for="t in topics"
              :key="t.id"
              class="topic-option"
              :class="{ selected: newTaskTopic === t.id }"
              @click="newTaskTopic = t.id; showTopicPicker = false"
            >
              <span class="topic-dot" :style="{ background: t.color }"></span>
              {{ t.name }}
            </button>
          </div>
          <button class="confirm-btn" @mousedown.prevent="confirmAddTask">Add</button>
        </div>
      </div>

      <!-- Task groups -->
      <div v-for="group in groupedTasks" :key="group.label" class="task-group">
        <div class="group-header" @click="toggleGroup(group.label)">
          <svg
            class="group-chevron"
            :class="{ collapsed: collapsedGroups.includes(group.label) }"
            width="12" height="12" viewBox="0 0 12 12" fill="currentColor"
          >
            <path d="M3 5l3 3 3-3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" fill="none"/>
          </svg>
          <span class="group-label">{{ group.label }}</span>
          <span class="group-count">{{ group.tasks.length }}</span>
        </div>

        <transition-group v-if="!collapsedGroups.includes(group.label)" name="task-item" tag="div">
          <div
            v-for="task in group.tasks"
            :key="task.id"
            class="task-item"
            :class="{
              completed: task.completed,
              selected: selectedTask?.id === task.id,
              expanded: expandedTasks.includes(task.id)
            }"
          >
            <div class="task-row" @click="selectTask(task)">
              <button
                class="task-checkbox"
                :class="{ checked: task.completed }"
                @click.stop="toggleTask(task)"
                :title="task.completed ? 'Mark incomplete' : 'Mark complete'"
              >
                <svg v-if="task.completed" width="10" height="10" viewBox="0 0 10 10" fill="none">
                  <path d="M2 5l2.5 2.5L8 3" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
              </button>

              <div class="task-content">
                <span class="task-name" :class="{ completed: task.completed }">{{ task.title }}</span>
                <div class="task-tags">
                  <span v-if="task.dueDate" class="tag due-tag" :class="getDueDateClass(task.dueDate)">
                    <svg width="10" height="10" viewBox="0 0 10 10" fill="none" stroke="currentColor" stroke-width="1.2">
                      <rect x="1" y="1.5" width="8" height="7.5" rx="1.5"/>
                      <path d="M1 4h8M3 0.5v2M7 0.5v2"/>
                    </svg>
                    {{ formatDate(task.dueDate) }}
                  </span>
                  <span v-if="task.topic" class="tag topic-tag" :style="{ background: getTopicColor(task.topic) + '22', color: getTopicColor(task.topic) }">
                    {{ getTopicName(task.topic) }}
                  </span>
                  <span v-if="task.subtasks?.length" class="tag subtask-tag">
                    {{ task.subtasks.filter(s => s.completed).length }}/{{ task.subtasks.length }}
                  </span>
                </div>
              </div>

              <div class="task-actions" @click.stop>
                <button
                  class="icon-btn small"
                  @click="toggleExpand(task)"
                  :title="expandedTasks.includes(task.id) ? 'Collapse' : 'Expand subtasks'"
                  v-if="task.subtasks?.length"
                >
                  <svg width="12" height="12" viewBox="0 0 12 12" fill="none" :style="{ transform: expandedTasks.includes(task.id) ? 'rotate(180deg)' : 'none', transition: 'transform 0.2s' }">
                    <path d="M3 5l3 3 3-3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                  </svg>
                </button>
                <button class="icon-btn small" @click="startAddSubtask(task)" title="Add subtask">
                  <svg width="12" height="12" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M6 2v8M2 6h8" stroke-linecap="round"/>
                  </svg>
                </button>
                <button class="icon-btn small danger" @click="deleteTask(task)" title="Delete">
                  <svg width="12" height="12" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M2 3h8M5 3V2h2v1M4 3v6h4V3" stroke-linecap="round"/>
                  </svg>
                </button>
              </div>
            </div>

            <!-- Subtasks -->
            <transition name="subtask-expand">
              <div v-if="expandedTasks.includes(task.id)" class="subtasks">
                <div
                  v-for="sub in task.subtasks"
                  :key="sub.id"
                  class="subtask-item"
                  :class="{ completed: sub.completed }"
                >
                  <button
                    class="task-checkbox small"
                    :class="{ checked: sub.completed }"
                    @click="toggleSubtask(task, sub)"
                  >
                    <svg v-if="sub.completed" width="8" height="8" viewBox="0 0 8 8" fill="none">
                      <path d="M1.5 4l1.5 1.5L6.5 2.5" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                  </button>
                  <span class="subtask-name" :class="{ completed: sub.completed }">{{ sub.title }}</span>
                  <button class="icon-btn small danger" @click="deleteSubtask(task, sub)">
                    <svg width="10" height="10" viewBox="0 0 10 10" fill="none" stroke="currentColor" stroke-width="1.5">
                      <path d="M3 5h4" stroke-linecap="round"/>
                    </svg>
                  </button>
                </div>

                <!-- Add subtask inline -->
                <div v-if="addingSubtaskFor === task.id" class="subtask-add">
                  <div class="task-checkbox small"></div>
                  <input
                    ref="subtaskInput"
                    v-model="newSubtaskTitle"
                    class="subtask-input"
                    placeholder="Subtask name"
                    @keydown.enter="confirmAddSubtask(task)"
                    @keydown.escape="cancelAddSubtask"
                    @blur="cancelAddSubtask"
                  />
                </div>
              </div>
            </transition>
          </div>
        </transition-group>
      </div>

      <div v-if="filteredTasks.length === 0" class="empty-state">
        <svg width="48" height="48" viewBox="0 0 48 48" fill="none" opacity="0.3">
          <rect x="8" y="12" width="32" height="28" rx="4" stroke="currentColor" stroke-width="2"/>
          <path d="M8 20h32M16 8v8M32 8v8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
        <p>No tasks here</p>
      </div>
    </main>

    <!-- Detail panel -->
    <aside v-if="selectedTask" class="detail-panel">
      <div class="detail-header">
        <button class="task-checkbox" :class="{ checked: selectedTask.completed }" @click="toggleTask(selectedTask)">
          <svg v-if="selectedTask.completed" width="10" height="10" viewBox="0 0 10 10" fill="none">
            <path d="M2 5l2.5 2.5L8 3" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
          </svg>
        </button>
        <input
          v-model="selectedTask.title"
          class="detail-title-input"
          @input="emitUpdate"
        />
        <button class="icon-btn" @click="selectedTask = null">
          <svg width="14" height="14" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M3 3l8 8M11 3l-8 8" stroke-linecap="round"/>
          </svg>
        </button>
      </div>

      <div class="detail-body">
        <div class="detail-field">
          <label>Due Date</label>
          <input type="date" v-model="selectedTask.dueDate" class="detail-input" @change="emitUpdate" />
        </div>
        <div class="detail-field">
          <label>Topic</label>
          <select v-model="selectedTask.topic" class="detail-input" @change="emitUpdate">
            <option value="">None</option>
            <option v-for="t in topics" :key="t.id" :value="t.id">{{ t.name }}</option>
          </select>
        </div>
        <div class="detail-field">
          <label>Notes</label>
          <textarea v-model="selectedTask.notes" class="detail-textarea" placeholder="Add notes..." @input="emitUpdate" rows="3"></textarea>
        </div>
        <div class="detail-field">
          <label>Subtasks</label>
          <div v-for="sub in selectedTask.subtasks" :key="sub.id" class="detail-subtask">
            <button class="task-checkbox small" :class="{ checked: sub.completed }" @click="toggleSubtask(selectedTask, sub)">
              <svg v-if="sub.completed" width="8" height="8" viewBox="0 0 8 8" fill="none">
                <path d="M1.5 4l1.5 1.5L6.5 2.5" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
              </svg>
            </button>
            <input v-model="sub.title" class="subtask-inline-input" @input="emitUpdate" />
            <button class="icon-btn small danger" @click="deleteSubtask(selectedTask, sub)">
              <svg width="10" height="10" viewBox="0 0 10 10" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M3 3l4 4M7 3l-4 4" stroke-linecap="round"/>
              </svg>
            </button>
          </div>
          <button class="add-subtask-btn" @click="startAddSubtask(selectedTask); expandedTasks.push(selectedTask.id)">
            + Add Subtask
          </button>
        </div>
      </div>
    </aside>
  </div>
</template>

<script>
export default {
  name: 'TaskList',
  props: {
    modelValue: {
      type: Array,
      default: () => []
    },
    topics: {
      type: Array,
      default: () => [
        { id: 'work', name: 'Work', color: '#4ECDC4' },
        { id: 'personal', name: 'Personal', color: '#FF6B6B' },
        { id: 'study', name: 'Study', color: '#A78BFA' },
        { id: 'health', name: 'Health', color: '#34D399' },
      ]
    }
  },
  emits: ['update:modelValue'],
  data() {
    return {
      tasks: this.modelValue.length ? JSON.parse(JSON.stringify(this.modelValue)) : this.defaultTasks(),
      activeView: 'next7',
      activeTopicFilter: null,
      selectedTask: null,
      addingTask: false,
      newTaskTitle: '',
      newTaskDueDate: '',
      newTaskTopic: '',
      showDueDatePicker: false,
      showTopicPicker: false,
      addingSubtaskFor: null,
      newSubtaskTitle: '',
      expandedTasks: [],
      collapsedGroups: [],
      views: [
        { id: 'today', label: 'Today', icon: '📅' },
        { id: 'next7', label: 'Next 7 Days', icon: '📋' },
        { id: 'all', label: 'All Tasks', icon: '📂' },
        { id: 'completed', label: 'Completed', icon: '✅' },
      ]
    }
  },
  computed: {
    currentViewLabel() {
      if (this.activeTopicFilter) {
        return this.topics.find(t => t.id === this.activeTopicFilter)?.name || 'Tasks'
      }
      return this.views.find(v => v.id === this.activeView)?.label || 'Tasks'
    },
    filteredTasks() {
      let list = this.tasks.filter(t => !t.completed || this.activeView === 'completed')

      if (this.activeView === 'today') {
        const today = this.todayStr()
        list = list.filter(t => t.dueDate === today)
      } else if (this.activeView === 'next7') {
        const today = new Date(); today.setHours(0,0,0,0)
        const next7 = new Date(today); next7.setDate(next7.getDate() + 7)
        list = list.filter(t => {
          if (!t.dueDate) return true
          const d = new Date(t.dueDate)
          return d >= today && d <= next7
        })
      } else if (this.activeView === 'completed') {
        list = this.tasks.filter(t => t.completed)
      }

      if (this.activeTopicFilter) {
        list = list.filter(t => t.topic === this.activeTopicFilter)
      }

      return list
    },
    groupedTasks() {
      if (this.activeView === 'completed' || this.activeView === 'all' || this.activeTopicFilter) {
        return [{ label: 'Tasks', tasks: this.filteredTasks }]
      }

      const today = this.todayStr()
      const tomorrow = this.tomorrowStr()

      const todayTasks = this.filteredTasks.filter(t => t.dueDate === today)
      const tomorrowTasks = this.filteredTasks.filter(t => t.dueDate === tomorrow)
      const laterTasks = this.filteredTasks.filter(t => {
        if (!t.dueDate) return true
        return t.dueDate !== today && t.dueDate !== tomorrow
      })

      const groups = []
      if (todayTasks.length) groups.push({ label: 'Today', tasks: todayTasks })
      if (tomorrowTasks.length) groups.push({ label: 'Tomorrow', tasks: tomorrowTasks })
      if (laterTasks.length) groups.push({ label: 'Upcoming', tasks: laterTasks })
      return groups.length ? groups : [{ label: 'Tasks', tasks: [] }]
    }
  },
  watch: {
    modelValue(val) {
      this.tasks = JSON.parse(JSON.stringify(val))
    }
  },
  methods: {
    defaultTasks() {
      const today = this.todayStr()
      const tomorrow = this.tomorrowStr()
      return [
        {
          id: 1, title: 'Morning Run', completed: false, dueDate: today, topic: 'health', notes: '',
          subtasks: []
        },
        {
          id: 2, title: 'Prepare Work Report', completed: false, dueDate: today, topic: 'work', notes: '',
          subtasks: [
            { id: 21, title: 'Organize Documents', completed: false },
            { id: 22, title: 'Prepare Presentation', completed: false },
          ]
        },
        {
          id: 3, title: 'Check Work Emails', completed: false, dueDate: tomorrow, topic: 'work', notes: '', subtasks: [] },
        {
          id: 4, title: 'Evening Reading', completed: false, dueDate: today, topic: 'personal', notes: '', subtasks: [] },
        {
          id: 5, title: 'Call Family', completed: false, dueDate: this.daysFromNow(2), topic: 'personal', notes: '', subtasks: [] },
      ]
    },
    todayStr() {
      return new Date().toISOString().split('T')[0]
    },
    tomorrowStr() {
      const d = new Date(); d.setDate(d.getDate() + 1)
      return d.toISOString().split('T')[0]
    },
    daysFromNow(n) {
      const d = new Date(); d.setDate(d.getDate() + n)
      return d.toISOString().split('T')[0]
    },
    formatDate(dateStr) {
      if (!dateStr) return ''
      const d = new Date(dateStr + 'T00:00:00')
      const today = new Date(); today.setHours(0,0,0,0)
      const tomorrow = new Date(today); tomorrow.setDate(tomorrow.getDate() + 1)
      if (d.getTime() === today.getTime()) return 'Today'
      if (d.getTime() === tomorrow.getTime()) return 'Tomorrow'
      return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
    },
    getDueDateClass(dateStr) {
      if (!dateStr) return ''
      const d = new Date(dateStr + 'T00:00:00')
      const today = new Date(); today.setHours(0,0,0,0)
      if (d < today) return 'overdue'
      if (d.getTime() === today.getTime()) return 'due-today'
      return ''
    },
    getTopicName(id) {
      return this.topics.find(t => t.id === id)?.name || id
    },
    getTopicColor(id) {
      return this.topics.find(t => t.id === id)?.color || '#999'
    },
    getViewCount(viewId) {
      if (viewId === 'today') return this.tasks.filter(t => !t.completed && t.dueDate === this.todayStr()).length
      if (viewId === 'next7') {
        const today = new Date(); today.setHours(0,0,0,0)
        const next7 = new Date(today); next7.setDate(next7.getDate() + 7)
        return this.tasks.filter(t => {
          if (t.completed) return false
          if (!t.dueDate) return true
          const d = new Date(t.dueDate)
          return d >= today && d <= next7
        }).length
      }
      if (viewId === 'all') return this.tasks.filter(t => !t.completed).length
      if (viewId === 'completed') return this.tasks.filter(t => t.completed).length
      return 0
    },
    getTopicCount(topicId) {
      return this.tasks.filter(t => !t.completed && t.topic === topicId).length
    },
    toggleTopicFilter(id) {
      this.activeTopicFilter = this.activeTopicFilter === id ? null : id
      if (this.activeTopicFilter) this.activeView = 'all'
    },
    toggleGroup(label) {
      const idx = this.collapsedGroups.indexOf(label)
      if (idx >= 0) this.collapsedGroups.splice(idx, 1)
      else this.collapsedGroups.push(label)
    },
    startAddTask() {
      this.addingTask = true
      this.$nextTick(() => this.$refs.addTaskInput?.focus())
    },
    cancelAddTask() {
      this.addingTask = false
      this.newTaskTitle = ''
      this.newTaskDueDate = ''
      this.newTaskTopic = ''
      this.showDueDatePicker = false
      this.showTopicPicker = false
    },
    handleAddBlur() {
      setTimeout(() => {
        if (!this.newTaskTitle.trim()) this.cancelAddTask()
      }, 150)
    },
    confirmAddTask() {
      if (!this.newTaskTitle.trim()) return
      const task = {
        id: Date.now(),
        title: this.newTaskTitle.trim(),
        completed: false,
        dueDate: this.newTaskDueDate || '',
        topic: this.newTaskTopic || '',
        notes: '',
        subtasks: []
      }
      this.tasks.push(task)
      this.emitUpdate()
      this.cancelAddTask()
      this.$nextTick(() => this.startAddTask())
    },
    toggleTask(task) {
      task.completed = !task.completed
      this.emitUpdate()
      if (this.selectedTask?.id === task.id && task.completed) {
        setTimeout(() => { this.selectedTask = null }, 600)
      }
    },
    deleteTask(task) {
      const idx = this.tasks.findIndex(t => t.id === task.id)
      if (idx >= 0) this.tasks.splice(idx, 1)
      if (this.selectedTask?.id === task.id) this.selectedTask = null
      this.emitUpdate()
    },
    selectTask(task) {
      this.selectedTask = this.selectedTask?.id === task.id ? null : task
    },
    toggleExpand(task) {
      const idx = this.expandedTasks.indexOf(task.id)
      if (idx >= 0) this.expandedTasks.splice(idx, 1)
      else this.expandedTasks.push(task.id)
    },
    startAddSubtask(task) {
      if (!this.expandedTasks.includes(task.id)) this.expandedTasks.push(task.id)
      this.addingSubtaskFor = task.id
      this.newSubtaskTitle = ''
      this.$nextTick(() => {
        const inputs = this.$refs.subtaskInput
        if (Array.isArray(inputs)) inputs[0]?.focus()
        else inputs?.focus()
      })
    },
    cancelAddSubtask() {
      setTimeout(() => {
        this.addingSubtaskFor = null
        this.newSubtaskTitle = ''
      }, 150)
    },
    confirmAddSubtask(task) {
      if (!this.newSubtaskTitle.trim()) return
      if (!task.subtasks) task.subtasks = []
      task.subtasks.push({ id: Date.now(), title: this.newSubtaskTitle.trim(), completed: false })
      this.newSubtaskTitle = ''
      this.emitUpdate()
      this.$nextTick(() => {
        const inputs = this.$refs.subtaskInput
        if (Array.isArray(inputs)) inputs[0]?.focus()
        else inputs?.focus()
      })
    },
    toggleSubtask(task, sub) {
      sub.completed = !sub.completed
      this.emitUpdate()
    },
    deleteSubtask(task, sub) {
      const idx = task.subtasks.indexOf(sub)
      if (idx >= 0) task.subtasks.splice(idx, 1)
      this.emitUpdate()
    },
    cycleSortOrder() {
      // Future: implement sort cycling
    },
    emitUpdate() {
      this.$emit('update:modelValue', JSON.parse(JSON.stringify(this.tasks)))
    }
  }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&display=swap');

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

.task-app {
  font-family: 'DM Sans', sans-serif;
  display: flex;
  height: 100vh;
  background: #f0f4f8;
  color: #1a202c;
  overflow: hidden;
}

/* ── Sidebar ── */
.sidebar {
  width: 220px;
  min-width: 220px;
  background: #fff;
  border-right: 1px solid #e8edf2;
  display: flex;
  flex-direction: column;
  padding: 20px 12px;
  gap: 4px;
}
.sidebar-header { padding: 4px 8px 16px; }
.app-logo { display: flex; align-items: center; gap: 10px; font-size: 15px; font-weight: 600; color: #1a202c; }
.sidebar-nav { display: flex; flex-direction: column; gap: 2px; }
.sidebar-section { margin-top: 20px; }
.section-title { font-size: 11px; font-weight: 600; color: #94a3b8; letter-spacing: .06em; text-transform: uppercase; padding: 0 8px 8px; }
.nav-item {
  display: flex; align-items: center; gap: 10px;
  padding: 7px 10px; border-radius: 8px; border: none;
  background: transparent; cursor: pointer; font-family: inherit;
  font-size: 13.5px; font-weight: 400; color: #4a5568;
  transition: background .15s, color .15s; text-align: left; width: 100%;
}
.nav-item:hover { background: #f7fafc; color: #1a202c; }
.nav-item.active { background: #eef9f8; color: #2cb5ad; font-weight: 500; }
.nav-icon { font-size: 14px; width: 18px; text-align: center; }
.nav-label { flex: 1; }
.nav-count { font-size: 11.5px; color: #94a3b8; font-weight: 500; }
.nav-item.active .nav-count { color: #4ECDC4; }
.topic-item .nav-count { background: #f0f4f8; border-radius: 10px; padding: 1px 6px; }
.topic-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }

/* ── Main ── */
.task-main {
  flex: 1; overflow-y: auto; padding: 28px 24px 40px;
  display: flex; flex-direction: column; gap: 0; min-width: 0;
}
.task-header {
  display: flex; align-items: center; justify-content: space-between;
  margin-bottom: 18px;
}
.task-title { font-size: 20px; font-weight: 600; color: #1a202c; }
.header-actions { display: flex; gap: 6px; }

/* Add task bar */
.add-task-bar {
  display: flex; align-items: center; gap: 10px;
  background: #fff; border: 1.5px solid #e2e8f0;
  border-radius: 10px; padding: 9px 14px;
  margin-bottom: 20px; cursor: text; position: relative;
  transition: border-color .2s, box-shadow .2s;
  flex-wrap: wrap;
}
.add-task-bar.focused { border-color: #4ECDC4; box-shadow: 0 0 0 3px #4ECDC420; }
.add-icon { color: #4ECDC4; font-size: 18px; font-weight: 400; line-height: 1; flex-shrink: 0; }
.add-task-placeholder { color: #94a3b8; font-size: 14px; flex: 1; }
.add-task-input {
  flex: 1; border: none; outline: none; font-family: inherit;
  font-size: 14px; color: #1a202c; background: transparent; min-width: 120px;
}
.add-task-meta { display: flex; align-items: center; gap: 6px; margin-left: auto; flex-wrap: wrap; }
.meta-btn {
  display: flex; align-items: center; gap: 5px;
  background: #f7fafc; border: 1px solid #e2e8f0; border-radius: 6px;
  padding: 4px 9px; font-size: 12px; color: #64748b;
  cursor: pointer; font-family: inherit; transition: all .15s;
}
.meta-btn:hover, .meta-btn.active { background: #eef9f8; border-color: #4ECDC4; color: #2cb5ad; }
.confirm-btn {
  background: #4ECDC4; color: #fff; border: none; border-radius: 6px;
  padding: 5px 14px; font-size: 13px; font-weight: 500; cursor: pointer;
  font-family: inherit; transition: background .15s;
}
.confirm-btn:hover { background: #3ab8b0; }
.date-picker-popup, .topic-picker-popup {
  position: absolute; top: calc(100% + 6px); right: 0;
  background: #fff; border: 1.5px solid #e2e8f0; border-radius: 10px;
  box-shadow: 0 8px 24px rgba(0,0,0,.1); z-index: 100; padding: 10px;
  min-width: 180px;
}
.date-native { border: 1px solid #e2e8f0; border-radius: 6px; padding: 6px 8px; font-family: inherit; font-size: 13px; outline: none; width: 100%; }
.topic-option {
  display: flex; align-items: center; gap: 8px;
  padding: 7px 10px; border-radius: 7px; cursor: pointer; font-size: 13.5px;
  border: none; background: transparent; width: 100%; text-align: left; font-family: inherit;
  transition: background .12s;
}
.topic-option:hover { background: #f7fafc; }
.topic-option.selected { background: #eef9f8; color: #2cb5ad; }

/* Task groups */
.task-group { margin-bottom: 8px; }
.group-header {
  display: flex; align-items: center; gap: 8px;
  padding: 6px 4px; cursor: pointer; user-select: none;
  font-size: 12.5px; font-weight: 600; color: #64748b; letter-spacing: .04em;
}
.group-header:hover { color: #1a202c; }
.group-chevron { transition: transform .2s; }
.group-chevron.collapsed { transform: rotate(-90deg); }
.group-count { margin-left: 2px; font-size: 11.5px; color: #94a3b8; }

/* Task item */
.task-item {
  background: #fff; border-radius: 10px; margin-bottom: 5px;
  border: 1.5px solid #e8edf2; overflow: hidden;
  transition: box-shadow .15s, border-color .15s;
}
.task-item:hover { box-shadow: 0 2px 10px rgba(0,0,0,.06); }
.task-item.selected { border-color: #4ECDC4; box-shadow: 0 2px 14px #4ECDC430; }
.task-item.completed { opacity: .6; }
.task-row {
  display: flex; align-items: center; gap: 12px;
  padding: 11px 14px; cursor: pointer;
}
.task-checkbox {
  width: 18px; height: 18px; flex-shrink: 0; border-radius: 50%;
  border: 2px solid #cbd5e0; background: transparent; cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  transition: all .15s; flex-shrink: 0;
}
.task-checkbox.small { width: 15px; height: 15px; }
.task-checkbox:hover { border-color: #4ECDC4; background: #eef9f8; }
.task-checkbox.checked { background: #4ECDC4; border-color: #4ECDC4; }
.task-content { flex: 1; min-width: 0; }
.task-name { font-size: 14px; font-weight: 400; color: #1a202c; display: block; }
.task-name.completed { text-decoration: line-through; color: #94a3b8; }
.task-tags { display: flex; flex-wrap: wrap; gap: 5px; margin-top: 5px; }
.tag {
  display: inline-flex; align-items: center; gap: 4px;
  font-size: 11px; padding: 2px 7px; border-radius: 20px; font-weight: 500;
}
.due-tag { background: #f1f5f9; color: #64748b; }
.due-tag.overdue { background: #fee2e2; color: #ef4444; }
.due-tag.due-today { background: #fef3c7; color: #d97706; }
.subtask-tag { background: #f1f5f9; color: #64748b; }
.task-actions { display: flex; gap: 2px; opacity: 0; transition: opacity .15s; }
.task-item:hover .task-actions { opacity: 1; }

/* Subtasks */
.subtasks { padding: 0 14px 10px 44px; display: flex; flex-direction: column; gap: 4px; }
.subtask-item {
  display: flex; align-items: center; gap: 10px;
  padding: 5px 6px; border-radius: 7px; transition: background .12s;
}
.subtask-item:hover { background: #f7fafc; }
.subtask-name { font-size: 13px; color: #4a5568; flex: 1; }
.subtask-name.completed { text-decoration: line-through; color: #94a3b8; }
.subtask-add { display: flex; align-items: center; gap: 10px; padding: 3px 6px; }
.subtask-input {
  flex: 1; border: none; border-bottom: 1.5px solid #e2e8f0;
  outline: none; font-family: inherit; font-size: 13px; padding: 2px 0;
  background: transparent; color: #1a202c;
}
.subtask-input:focus { border-bottom-color: #4ECDC4; }

/* Buttons */
.icon-btn {
  width: 28px; height: 28px; border-radius: 7px; border: none;
  background: transparent; cursor: pointer; color: #94a3b8;
  display: flex; align-items: center; justify-content: center; transition: all .15s;
}
.icon-btn:hover { background: #f1f5f9; color: #4a5568; }
.icon-btn.small { width: 22px; height: 22px; border-radius: 5px; }
.icon-btn.danger:hover { background: #fee2e2; color: #ef4444; }

/* Empty state */
.empty-state { display: flex; flex-direction: column; align-items: center; gap: 10px; padding: 60px 0; color: #94a3b8; }
.empty-state p { font-size: 14px; }

/* Detail panel */
.detail-panel {
  width: 280px; min-width: 280px; background: #fff;
  border-left: 1px solid #e8edf2; display: flex; flex-direction: column;
  overflow-y: auto;
}
.detail-header {
  display: flex; align-items: center; gap: 12px;
  padding: 18px 16px 14px; border-bottom: 1px solid #f1f5f9;
}
.detail-title-input {
  flex: 1; border: none; outline: none; font-family: inherit;
  font-size: 15px; font-weight: 600; color: #1a202c; background: transparent;
}
.detail-body { padding: 16px; display: flex; flex-direction: column; gap: 16px; }
.detail-field { display: flex; flex-direction: column; gap: 6px; }
.detail-field label { font-size: 11.5px; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: .05em; }
.detail-input, .detail-textarea {
  border: 1.5px solid #e2e8f0; border-radius: 8px;
  padding: 8px 10px; font-family: inherit; font-size: 13.5px;
  color: #1a202c; background: #fff; outline: none; resize: none;
  transition: border-color .15s;
}
.detail-input:focus, .detail-textarea:focus { border-color: #4ECDC4; }
.detail-subtask {
  display: flex; align-items: center; gap: 8px;
  padding: 4px 0; border-bottom: 1px solid #f1f5f9;
}
.subtask-inline-input {
  flex: 1; border: none; outline: none; font-family: inherit;
  font-size: 13px; color: #4a5568; background: transparent;
}
.add-subtask-btn {
  background: transparent; border: 1.5px dashed #e2e8f0; border-radius: 7px;
  padding: 6px 10px; font-family: inherit; font-size: 13px; color: #94a3b8;
  cursor: pointer; transition: all .15s; text-align: left; margin-top: 4px;
}
.add-subtask-btn:hover { border-color: #4ECDC4; color: #4ECDC4; background: #eef9f8; }

/* Animations */
.task-item-enter-active { transition: all .25s ease; }
.task-item-leave-active { transition: all .2s ease; }
.task-item-enter-from { opacity: 0; transform: translateY(-8px); }
.task-item-leave-to { opacity: 0; transform: translateX(20px); }

.subtask-expand-enter-active { transition: all .2s ease; max-height: 300px; overflow: hidden; }
.subtask-expand-leave-active { transition: all .2s ease; max-height: 300px; overflow: hidden; }
.subtask-expand-enter-from, .subtask-expand-leave-to { max-height: 0; opacity: 0; }

/* Scrollbar */
.task-main::-webkit-scrollbar, .detail-panel::-webkit-scrollbar { width: 5px; }
.task-main::-webkit-scrollbar-track, .detail-panel::-webkit-scrollbar-track { background: transparent; }
.task-main::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
</style>
