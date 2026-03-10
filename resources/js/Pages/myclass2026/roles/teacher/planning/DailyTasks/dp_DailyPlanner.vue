<template>
  <div class="daily-planner-container">
    <!-- Header with Fun Progress -->
    <div class="planner-header">
      <div class="welcome-section">
        <h1 class="welcome-title">{{ t('dailyPlanner.welcomeTitle') }}</h1>
        <div class="current-time">
          <i class="material-icons">schedule</i>
          <span>{{ currentTime }}</span>
        </div>
      </div>
      
      <!-- Fun Progress Bar -->
      <div class="progress-section">
        <div class="points-display">
          <span class="points-label">{{ t('dailyPlanner.points') }}:</span>
          <span class="points-value">{{ points || 0 }}</span>
        </div>
        <div class="achievement-badges">
          <div 
            v-for="badge in recentBadges" 
            :key="badge.id"
            class="badge bounce-in"
            :style="{ backgroundColor: badge.color }"
          >
            <i class="material-icons">{{ badge.icon }}</i>
            {{ badge.name }}
          </div>
        </div>
      </div>
    </div>

    <!-- Calendar Timeline -->
    <div class="calendar-timeline">
      <!-- Time Scale -->
      <div class="time-scale">
        <div 
          v-for="hour in timeScale" 
          :key="hour"
          class="time-marker"
          :class="{ 'current-hour': isCurrentHour(hour) }"
        >
          {{ formatHour(hour) }}
        </div>
      </div>

      <!-- Current Time Indicator -->
      <div 
        class="current-time-line"
        :style="{ left: currentTimePosition + '%' }"
      >
        <div class="time-indicator">
          <i class="material-icons">schedule</i>
          <span class="pulse-dot"></span>
        </div>
      </div>

      <!-- Task Timeline -->
      <div class="tasks-timeline">
        <div 
          v-for="task in (tasks || [])" 
          :key="task.id"
          class="task-block"
          :class="[
            `status-${task.status}`,
            { 'current-task': isCurrentTask(task) },
            { 'past-task': isPastTask(task) }
          ]"
          :style="getTaskPosition(task)"
        >
          <!-- Task Card -->
          <div class="task-card">
            <div class="task-icon">
              {{ getTaskEmoji(task) }}
            </div>
            
            <div class="task-content">
              <h3 class="task-title">{{ task.title }}</h3>
              <p class="task-time">
                {{ formatTime(task.start_time) }} - {{ formatTime(task.end_time) }}
              </p>
              <p class="task-description" v-if="task.description">
                {{ task.description }}
              </p>
            </div>

            <!-- Task Actions -->
            <div class="task-actions">
              <!-- Complete Button -->
              <button 
                v-if="task.status !== 'completed' && isCurrentTask(task)"
                class="action-btn complete-btn bounce"
                @click.stop="completeTask(task)"
                :title="t('dailyPlanner.completeTask')"
              >
                <i class="material-icons">check</i>
              </button>
              
              <!-- Focus Button -->
              <button 
                v-if="isCurrentTask(task) && !isFocusing"
                class="action-btn focus-btn"
                @click.stop="startFocus(task)"
                :title="t('dailyPlanner.startFocus')"
              >
                <i class="material-icons">timer</i>
              </button>

              <!-- Edit Button -->
              <button 
                class="action-btn edit-btn"
                @click.stop="editTask(task)"
                :title="t('dailyPlanner.editTask')"
              >
                <i class="material-icons">edit</i>
              </button>

              <!-- Delete Button -->
              <button 
                class="action-btn delete-btn"
                @click.stop="deleteTask(task)"
                :title="t('dailyPlanner.deleteTask')"
              >
                <i class="material-icons">delete</i>
              </button>

              <!-- Completion Badge -->
              <div v-if="task.status === 'completed'" class="completion-badge">
                <i class="material-icons rotate">star</i>
                <span class="badge-text">{{ t('dailyPlanner.completed') }}</span>
              </div>
            </div>
          </div>

          <!-- Task Progress Bar -->
          <div class="task-progress">
            <div 
              class="progress-bar"
              :style="{ 
                width: (getTaskProgress(task) * 100) + '%',
                backgroundColor: getTaskColorHex(task)
              }"
            ></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Motivational Messages -->
    <div class="motivation-section" v-if="currentMotivation">
      <div class="motivation-card">
        <div class="motivation-content">
          <div class="motivation-icon">{{ currentMotivation.emoji }}</div>
          <p class="motivation-text">{{ currentMotivation.message }}</p>
        </div>
      </div>
    </div>

    <!-- Floating Action Button -->
    <div class="floating-btn">
      <button 
        class="fab pulse"
        @click="addNewTask"
        :title="t('dailyPlanner.addTask')"
      >
        <i class="material-icons">add_task</i>
      </button>
    </div>

    <!-- Edit Task Modal -->
    <div v-if="showEditModal" class="modal-overlay" @click="closeEditModal">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h3>{{ t('dailyPlanner.editTask') }}</h3>
          <button class="close-btn" @click="closeEditModal">
            <i class="material-icons">close</i>
          </button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>{{ t('dailyPlanner.taskTitle') }}</label>
            <input 
              v-model="editingTask.title" 
              type="text" 
              class="form-input"
              :placeholder="t('dailyPlanner.enterTitle')"
            >
          </div>
          <div class="form-group">
            <label>{{ t('dailyPlanner.description') }}</label>
            <textarea 
              v-model="editingTask.description" 
              class="form-textarea"
              :placeholder="t('dailyPlanner.enterDescription')"
            ></textarea>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>{{ t('dailyPlanner.startTime') }}</label>
              <input 
                v-model="editingTask.start_time" 
                type="time" 
                class="form-input"
              >
            </div>
            <div class="form-group">
              <label>{{ t('dailyPlanner.endTime') }}</label>
              <input 
                v-model="editingTask.end_time" 
                type="time" 
                class="form-input"
              >
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" @click="closeEditModal">
            {{ t('common.cancel') }}
          </button>
          <button class="btn btn-primary" @click="saveTask">
            {{ t('common.save') }}
          </button>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="modal-overlay" @click="closeDeleteModal">
      <div class="modal-content delete-modal" @click.stop>
        <div class="modal-header">
          <h3>{{ t('dailyPlanner.confirmDelete') }}</h3>
        </div>
        <div class="modal-body">
          <p>{{ t('dailyPlanner.deleteMessage', { title: taskToDelete?.title }) }}</p>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" @click="closeDeleteModal">
            {{ t('common.cancel') }}
          </button>
          <button class="btn btn-danger" @click="confirmDelete">
            {{ t('common.delete') }}
          </button>
        </div>
      </div>
    </div>

    <!-- Celebration Animation -->
    <div v-if="showCelebration" class="celebration-overlay">
      <div class="celebration-message">
        <h2>🎉 {{ t('dailyPlanner.congratulations') }} 🎉</h2>
        <p>{{ t('dailyPlanner.taskCompleted') }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useI18n } from 'vue-i18n'

const { t, locale } = useI18n()

const props = defineProps({
  tasks: {
    type: Array,
    default: () => []
  }
})

// Reactive data
const currentTime = ref('')
const showCelebration = ref(false)
const showEditModal = ref(false)
const showDeleteModal = ref(false)
const editingTask = ref({})
const taskToDelete = ref(null)
const recentBadges = ref([])
const points = ref(0)
const isFocusing = ref(false)

// Time scale for calendar (6 AM to 10 PM)
const timeScale = computed(() => {
  const hours = []
  for (let i = 6; i <= 22; i++) {
    hours.push(i)
  }
  return hours
})

// Current time position on timeline (percentage)
const currentTimePosition = computed(() => {
  const now = new Date()
  const currentHour = now.getHours() + now.getMinutes() / 60
  const startHour = 6
  const endHour = 22
  const totalHours = endHour - startHour
  
  if (currentHour < startHour) return 0
  if (currentHour > endHour) return 100
  
  return ((currentHour - startHour) / totalHours) * 100
})

// Motivational messages based on locale
const motivationalMessages = computed(() => {
  if (locale.value === 'ar') {
    return [
      { emoji: '🌅', message: 'صباح الخير! استعد لبداية يوم رائع!' },
      { emoji: '💪', message: 'أنت تقوم بعمل رائع! استمر!' },
      { emoji: '🎯', message: 'ركز على هدفك وستصل إليه!' },
      { emoji: '⭐', message: 'كل مهمة تكملها تجعلك أقوى!' },
      { emoji: '🏆', message: 'أنت بطل حقيقي!' }
    ]
  } else {
    return [
      { emoji: '🌅', message: 'Good morning! Get ready for an amazing day!' },
      { emoji: '💪', message: 'You\'re doing great! Keep going!' },
      { emoji: '🎯', message: 'Focus on your goal and you\'ll reach it!' },
      { emoji: '⭐', message: 'Every task you complete makes you stronger!' },
      { emoji: '🏆', message: 'You are a true champion!' }
    ]
  }
})

const currentMotivation = computed(() => {
  const hour = new Date().getHours()
  const messages = motivationalMessages.value
  if (hour < 9) return messages[0]
  if (hour < 12) return messages[1]
  if (hour < 15) return messages[2]
  if (hour < 18) return messages[3]
  return messages[4]
})

// Methods
const updateCurrentTime = () => {
  const now = new Date()
  const options = { 
    hour: '2-digit', 
    minute: '2-digit',
    hour12: true 
  }
  currentTime.value = now.toLocaleTimeString(locale.value === 'ar' ? 'ar-SA' : 'en-US', options)
}

const formatTime = (timeStr) => {
  if (!timeStr) return ''
  const [hours, minutes] = timeStr.split(':')
  const d = new Date()
  d.setHours(hours, minutes)
  const options = { 
    hour: '2-digit', 
    minute: '2-digit',
    hour12: true 
  }
  return d.toLocaleTimeString(locale.value === 'ar' ? 'ar-SA' : 'en-US', options)
}

const formatHour = (hour) => {
  const d = new Date()
  d.setHours(hour, 0)
  const options = { 
    hour: '2-digit',
    hour12: true 
  }
  return d.toLocaleTimeString(locale.value === 'ar' ? 'ar-SA' : 'en-US', options)
}

const isCurrentHour = (hour) => {
  return new Date().getHours() === hour
}

const isCurrentTask = (task) => {
  if (!task.start_time || !task.end_time) return false
  const now = new Date()
  const [startH, startM] = task.start_time.split(':')
  const [endH, endM] = task.end_time.split(':')
  
  const start = new Date()
  start.setHours(startH, startM, 0)
  
  const end = new Date()
  end.setHours(endH, endM, 0)
  
  return now >= start && now <= end
}

const isPastTask = (task) => {
  if (!task.end_time) return false
  const now = new Date()
  const [endH, endM] = task.end_time.split(':')
  const end = new Date()
  end.setHours(endH, endM, 0)
  return now > end
}

const getTaskPosition = (task) => {
  if (!task.start_time || !task.end_time) return {}
  
  const [startH, startM] = task.start_time.split(':')
  const [endH, endM] = task.end_time.split(':')
  
  const startTime = parseInt(startH) + parseInt(startM) / 60
  const endTime = parseInt(endH) + parseInt(endM) / 60
  const duration = endTime - startTime
  
  const startHour = 6
  const totalHours = 16 // 6 AM to 10 PM
  
  const left = ((startTime - startHour) / totalHours) * 100
  const width = (duration / totalHours) * 100
  
  return {
    left: `${Math.max(0, left)}%`,
    width: `${Math.min(100 - left, width)}%`
  }
}

const getTaskEmoji = (task) => {
  const title = task.title.toLowerCase()
  if (title.includes('رياضة') || title.includes('تمرين') || title.includes('sport') || title.includes('exercise')) return '🏃‍♂️'
  if (title.includes('دراسة') || title.includes('واجب') || title.includes('study') || title.includes('homework')) return '📚'
  if (title.includes('إفطار') || title.includes('غداء') || title.includes('عشاء') || title.includes('breakfast') || title.includes('lunch') || title.includes('dinner')) return '🍽️'
  if (title.includes('استراحة') || title.includes('break') || title.includes('rest')) return '☕'
  if (title.includes('لعب') || title.includes('play') || title.includes('game')) return '🎮'
  if (title.includes('قراءة') || title.includes('read')) return '📖'
  if (title.includes('موسيقى') || title.includes('music')) return '🎵'
  return '⭐'
}

const getTaskColorHex = (task) => {
  if (task.status === 'completed') return '#00b894'
  if (isCurrentTask(task)) return '#fdcb6e'
  if (isPastTask(task)) return '#636e72'
  return '#74b9ff'
}

const getTaskProgress = (task) => {
  if (task.status === 'completed') return 1
  if (!isCurrentTask(task)) return 0
  
  const now = new Date()
  const [startH, startM] = task.start_time.split(':')
  const [endH, endM] = task.end_time.split(':')
  
  const start = new Date()
  start.setHours(startH, startM, 0)
  
  const end = new Date()
  end.setHours(endH, endM, 0)
  
  const total = end - start
  const elapsed = now - start
  
  return Math.min(1, Math.max(0, elapsed / total))
}

const completeTask = async (task) => {
  task.status = 'completed'
  points.value += 10
  
  showCelebration.value = true
  setTimeout(() => {
    showCelebration.value = false
  }, 3000)
  
  const completedText = locale.value === 'ar' ? 'مكتمل!' : 'Done!'
  recentBadges.value.unshift({
    id: Date.now(),
    name: completedText,
    color: '#00b894',
    icon: 'star'
  })
  
  if (recentBadges.value.length > 3) {
    recentBadges.value.pop()
  }
}

const startFocus = (task) => {
  console.log('Starting focus for task:', task.title)
}

const editTask = (task) => {
  editingTask.value = { ...task }
  showEditModal.value = true
}

const deleteTask = (task) => {
  taskToDelete.value = task
  showDeleteModal.value = true
}

const addNewTask = () => {
  editingTask.value = {
    id: Date.now(),
    title: '',
    description: '',
    start_time: '09:00',
    end_time: '10:00',
    status: 'pending'
  }
  showEditModal.value = true
}

const saveTask = () => {
  // Here you would typically save to backend
  console.log('Saving task:', editingTask.value)
  
  // For demo, just update the local task
  const taskIndex = props.tasks.findIndex(t => t.id === editingTask.value.id)
  if (taskIndex >= 0) {
    Object.assign(props.tasks[taskIndex], editingTask.value)
  } else {
    props.tasks.push(editingTask.value)
  }
  
  closeEditModal()
}

const confirmDelete = () => {
  const taskIndex = props.tasks.findIndex(t => t.id === taskToDelete.value.id)
  if (taskIndex >= 0) {
    props.tasks.splice(taskIndex, 1)
  }
  closeDeleteModal()
}

const closeEditModal = () => {
  showEditModal.value = false
  editingTask.value = {}
}

const closeDeleteModal = () => {
  showDeleteModal.value = false
  taskToDelete.value = null
}

// Lifecycle
let timeInterval

onMounted(() => {
  updateCurrentTime()
  timeInterval = setInterval(updateCurrentTime, 1000)
})

onUnmounted(() => {
  if (timeInterval) {
    clearInterval(timeInterval)
  }
})
</script>

<style scoped>
.daily-planner-container {
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 20px;
  direction: ltr;
}

[dir="rtl"] .daily-planner-container {
  direction: rtl;
}

.planner-header {
  background: rgba(255, 255, 255, 0.95);
  border-radius: 20px;
  padding: 20px;
  margin-bottom: 20px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
  backdrop-filter: blur(10px);
}

.welcome-section {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 15px;
}

.welcome-title {
  font-size: 2rem;
  font-weight: bold;
  color: #2c3e50;
  margin: 0;
  text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
}

.current-time {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 1.2rem;
  font-weight: bold;
  color: #3498db;
  background: rgba(52, 152, 219, 0.1);
  padding: 8px 16px;
  border-radius: 25px;
}

.progress-section {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.points-display {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 1.1rem;
  font-weight: bold;
}

.points-label {
  color: #7f8c8d;
}

.points-value {
  color: #e74c3c;
  background: rgba(231, 76, 60, 0.1);
  padding: 4px 12px;
  border-radius: 15px;
}

.achievement-badges {
  display: flex;
  gap: 8px;
}

.badge {
  display: flex;
  align-items: center;
  gap: 4px;
  padding: 4px 12px;
  border-radius: 15px;
  color: white;
  font-size: 0.9rem;
  font-weight: bold;
}

.calendar-timeline {
  position: relative;
  background: rgba(255, 255, 255, 0.95);
  border-radius: 20px;
  padding: 20px;
  margin-bottom: 20px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
  backdrop-filter: blur(10px);
  min-height: 400px;
}

.time-scale {
  display: flex;
  justify-content: space-between;
  margin-bottom: 20px;
  padding: 0 10px;
}

.time-marker {
  font-size: 0.9rem;
  color: #7f8c8d;
  font-weight: 500;
  padding: 5px 10px;
  border-radius: 10px;
  transition: all 0.3s ease;
}

.time-marker.current-hour {
  background: #3498db;
  color: white;
  transform: scale(1.1);
}

.current-time-line {
  position: absolute;
  top: 80px;
  height: calc(100% - 100px);
  width: 2px;
  background: #e74c3c;
  z-index: 10;
  transition: left 1s ease;
}

.time-indicator {
  position: absolute;
  top: -10px;
  left: -8px;
  background: #e74c3c;
  border-radius: 50%;
  padding: 4px;
  box-shadow: 0 2px 8px rgba(231, 76, 60, 0.3);
  color: white;
}

.pulse-dot {
  display: block;
  width: 8px;
  height: 8px;
  background: #e74c3c;
  border-radius: 50%;
  animation: pulse 2s infinite;
}

.tasks-timeline {
  position: relative;
  height: 300px;
  margin-top: 40px;
}

.task-block {
  position: absolute;
  top: 0;
  height: 100%;
  cursor: pointer;
  transition: all 0.3s ease;
  z-index: 5;
}

.task-block:hover {
  transform: translateY(-5px);
  z-index: 15;
}

.task-block.current-task {
  z-index: 10;
  animation: glow 2s infinite alternate;
}

.task-card {
  background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);
  border-radius: 15px;
  padding: 15px;
  height: 100%;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
  color: white;
  position: relative;
  overflow: hidden;
}

.task-block.status-completed .task-card {
  background: linear-gradient(135deg, #00b894 0%, #00a085 100%);
}

.task-block.past-task .task-card {
  background: linear-gradient(135deg, #636e72 0%, #2d3436 100%);
  opacity: 0.7;
}

.task-icon {
  font-size: 2rem;
  text-align: center;
  margin-bottom: 10px;
}

.task-title {
  font-size: 1.1rem;
  font-weight: bold;
  margin: 0 0 5px 0;
  text-align: center;
}

.task-time {
  font-size: 0.9rem;
  opacity: 0.9;
  text-align: center;
  margin: 0 0 10px 0;
}

.task-description {
  font-size: 0.8rem;
  opacity: 0.8;
  text-align: center;
  margin: 0;
}

.task-actions {
  display: flex;
  justify-content: center;
  gap: 8px;
  margin-top: 10px;
  flex-wrap: wrap;
}

.action-btn {
  border: none;
  border-radius: 50%;
  width: 35px;
  height: 35px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s ease;
  font-size: 18px;
}

.complete-btn {
  background: #00b894;
  color: white;
  animation: bounce 2s infinite;
}

.focus-btn {
  background: #a29bfe;
  color: white;
}

.edit-btn {
  background: #fdcb6e;
  color: white;
}

.delete-btn {
  background: #e17055;
  color: white;
}

.action-btn:hover {
  transform: scale(1.1);
}

.completion-badge {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 5px;
}

.badge-text {
  font-size: 0.8rem;
  font-weight: bold;
}

.task-progress {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  height: 8px;
  border-radius: 0 0 15px 15px;
  overflow: hidden;
  background: rgba(255, 255, 255, 0.3);
}

.progress-bar {
  height: 100%;
  transition: width 1s ease;
  border-radius: 0 0 15px 15px;
}

.motivation-section {
  margin-bottom: 20px;
}

.motivation-card {
  background: rgba(255, 255, 255, 0.95);
  border-radius: 15px;
  backdrop-filter: blur(10px);
  padding: 20px;
  text-align: center;
}

.motivation-icon {
  font-size: 3rem;
  margin-bottom: 10px;
}

.motivation-text {
  font-size: 1.2rem;
  font-weight: 500;
  color: #2c3e50;
  margin: 0;
}

.floating-btn {
  position: fixed;
  bottom: 20px;
  right: 20px;
  z-index: 1000;
}

[dir="rtl"] .floating-btn {
  right: auto;
  left: 20px;
}

.fab {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  background: #74b9ff;
  color: white;
  border: none;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  transition: all 0.3s ease;
}

.fab:hover {
  transform: scale(1.1);
  background: #0984e3;
}

/* Modal Styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.8);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  animation: fadeIn 0.3s ease;
}

.modal-content {
  background: white;
  border-radius: 15px;
  max-width: 500px;
  width: 90%;
  max-height: 80vh;
  overflow-y: auto;
  animation: bounceIn 0.5s ease;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  border-bottom: 1px solid #eee;
}

.modal-header h3 {
  margin: 0;
  color: #2c3e50;
}

.close-btn {
  background: none;
  border: none;
  font-size: 24px;
  cursor: pointer;
  color: #7f8c8d;
  padding: 0;
  width: 30px;
  height: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  transition: all 0.3s ease;
}

.close-btn:hover {
  background: #f8f9fa;
  color: #2c3e50;
}

.modal-body {
  padding: 20px;
}

.form-group {
  margin-bottom: 20px;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 15px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 500;
  color: #2c3e50;
}

.form-input, .form-textarea {
  width: 100%;
  padding: 12px;
  border: 2px solid #e9ecef;
  border-radius: 8px;
  font-size: 14px;
  transition: border-color 0.3s ease;
}

.form-input:focus, .form-textarea:focus {
  outline: none;
  border-color: #74b9ff;
}

.form-textarea {
  resize: vertical;
  min-height: 80px;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  padding: 20px;
  border-top: 1px solid #eee;
}

.btn {
  padding: 10px 20px;
  border: none;
  border-radius: 8px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-primary {
  background: #74b9ff;
  color: white;
}

.btn-primary:hover {
  background: #0984e3;
}

.btn-secondary {
  background: #ddd;
  color: #666;
}

.btn-secondary:hover {
  background: #bbb;
}

.btn-danger {
  background: #e17055;
  color: white;
}

.btn-danger:hover {
  background: #d63031;
}

.delete-modal {
  max-width: 400px;
}

.celebration-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.8);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  animation: fadeIn 0.5s ease;
}

.celebration-message {
  background: white;
  padding: 40px;
  border-radius: 20px;
  text-align: center;
  animation: bounceIn 0.8s ease;
}

.celebration-message h2 {
  font-size: 2.5rem;
  margin: 0 0 15px 0;
  color: #f39c12;
}

.celebration-message p {
  font-size: 1.3rem;
  color: #2c3e50;
  margin: 0;
}

/* Animations */
@keyframes pulse {
  0% { transform: scale(1); opacity: 1; }
  50% { transform: scale(1.2); opacity: 0.7; }
  100% { transform: scale(1); opacity: 1; }
}

@keyframes bounce {
  0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
  40% { transform: translateY(-10px); }
  60% { transform: translateY(-5px); }
}

@keyframes glow {
  0% { box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15); }
  100% { box-shadow: 0 8px 30px rgba(116, 185, 255, 0.4); }
}

@keyframes rotate {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

@keyframes fadeIn {
  0% { opacity: 0; }
  100% { opacity: 1; }
}

@keyframes bounceIn {
  0% { transform: scale(0.3); opacity: 0; }
  50% { transform: scale(1.05); }
  70% { transform: scale(0.9); }
  100% { transform: scale(1); opacity: 1; }
}

.bounce-in {
  animation: bounceIn 0.6s ease;
}

.rotate {
  animation: rotate 2s linear infinite;
}

/* Responsive Design */
@media (max-width: 768px) {
  .welcome-title {
    font-size: 1.5rem;
  }
  
  .task-card {
    padding: 10px;
  }
  
  .task-title {
    font-size: 1rem;
  }
  
  .time-scale {
    font-size: 0.8rem;
  }
  
  .action-btn {
    width: 30px;
    height: 30px;
    font-size: 16px;
  }
  
  .form-row {
    grid-template-columns: 1fr;
  }
}
</style>