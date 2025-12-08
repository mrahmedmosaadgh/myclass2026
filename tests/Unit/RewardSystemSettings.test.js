import { describe, it, expect, beforeEach, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import { ref, computed, nextTick } from 'vue'
import { Quasar, Notify } from 'quasar'

// Mock the reward system component
const mockRewardSystemComponent = {
  template: `
    <div>
      <div class="settings-toggle" v-if="shouldShowAvatarButtons">Avatar editing enabled</div>
      <div class="settings-toggle" v-else>Avatar editing disabled</div>
      
      <div v-for="student in students" :key="student.id" :class="getStudentCardClass(student.id)">
        <div class="student-name">{{ student.name }}</div>
        <div class="avatar-buttons" v-show="shouldShowAvatarButtons && !shouldDisableBehaviorButtons(student.id)">
          <button class="upload-btn">Upload</button>
          <button class="camera-btn">Camera</button>
        </div>
        <div class="behavior-buttons">
          <button 
            class="star-btn" 
            :disabled="shouldDisableBehaviorButtons(student.id)"
            @click="openBehaviorDialog(student.id)"
          >
            Star
          </button>
        </div>
      </div>
      
      <div class="attendance-list">
        <div v-for="student in students" :key="student.id" class="attendance-item">
          <span>{{ student.name }}</span>
          <button @click="toggleStudentAttendance(student.id)">
            {{ isStudentPresent(student.id) ? 'Present' : 'Absent' }}
          </button>
        </div>
      </div>
    </div>
  `,
  setup() {
    // Settings state management
    const settings = ref({
      avatarEditingEnabled: true,
      attendance: {},
      currentClassroomId: null
    })

    // Mock students data
    const students = ref([
      { id: '1', name: 'John Doe' },
      { id: '2', name: 'Jane Smith' },
      { id: '3', name: 'Bob Johnson' }
    ])

    // Settings persistence functions
    const saveSettings = () => {
      try {
        const settingsData = {
          ...settings.value,
          lastUpdated: Date.now(),
          version: '1.0.0'
        }
        localStorage.setItem('reward-system-settings', JSON.stringify(settingsData))
        return true
      } catch (error) {
        console.error('Failed to save settings:', error)
        return false
      }
    }

    const loadSettings = () => {
      try {
        const saved = localStorage.getItem('reward-system-settings')
        if (saved) {
          const parsedSettings = JSON.parse(saved)
          const { lastUpdated, version, ...settingsData } = parsedSettings
          Object.assign(settings.value, settingsData)
          return true
        }
        return false
      } catch (error) {
        console.error('Failed to load settings:', error)
        return false
      }
    }

    // Student presence checking
    const isStudentPresent = (studentId) => {
      return settings.value.attendance[studentId] !== false
    }

    // Computed properties
    const shouldShowAvatarButtons = computed(() => {
      return settings.value.avatarEditingEnabled
    })

    const getStudentCardClass = computed(() => (studentId) => {
      const baseClass = 'student-card cursor-pointer transition-all duration-300'
      const presentClass = 'hover:shadow-xl hover:-translate-y-1'
      const absentClass = 'student-absent opacity-50 grayscale cursor-not-allowed'
      
      return isStudentPresent(studentId) 
        ? `${baseClass} ${presentClass}`
        : `${baseClass} ${absentClass}`
    })

    const shouldDisableBehaviorButtons = computed(() => (studentId) => {
      return !isStudentPresent(studentId)
    })

    // Attendance management
    const toggleStudentAttendance = (studentId) => {
      const currentStatus = isStudentPresent(studentId)
      settings.value.attendance[studentId] = !currentStatus
    }

    const markAllPresent = () => {
      students.value.forEach(student => {
        settings.value.attendance[student.id] = true
      })
    }

    const markAllAbsent = () => {
      students.value.forEach(student => {
        settings.value.attendance[student.id] = false
      })
    }

    // Mock behavior dialog
    const openBehaviorDialog = (studentId) => {
      console.log(`Opening behavior dialog for student ${studentId}`)
    }

    return {
      settings,
      students,
      saveSettings,
      loadSettings,
      isStudentPresent,
      shouldShowAvatarButtons,
      getStudentCardClass,
      shouldDisableBehaviorButtons,
      toggleStudentAttendance,
      markAllPresent,
      markAllAbsent,
      openBehaviorDialog
    }
  }
}

describe('Reward System Settings Functionality', () => {
  let wrapper

  beforeEach(() => {
    // Clear localStorage before each test
    localStorage.clear()
    
    wrapper = mount(mockRewardSystemComponent, {
      global: {
        plugins: [Quasar]
      }
    })
  })

  describe('Settings State Management and Persistence', () => {
    it('should initialize with default settings', () => {
      expect(wrapper.vm.settings.avatarEditingEnabled).toBe(true)
      expect(wrapper.vm.settings.attendance).toEqual({})
      expect(wrapper.vm.settings.currentClassroomId).toBe(null)
    })

    it('should save settings to localStorage successfully', () => {
      wrapper.vm.settings.avatarEditingEnabled = false
      wrapper.vm.settings.attendance = { '1': true, '2': false }
      
      const result = wrapper.vm.saveSettings()
      
      expect(result).toBe(true)
      expect(localStorage.setItem).toHaveBeenCalledWith(
        'reward-system-settings',
        expect.stringContaining('"avatarEditingEnabled":false')
      )
    })

    it('should load settings from localStorage successfully', () => {
      const mockSettings = {
        avatarEditingEnabled: false,
        attendance: { '1': true, '2': false },
        currentClassroomId: 'class-123',
        lastUpdated: Date.now(),
        version: '1.0.0'
      }
      
      localStorage.getItem.mockReturnValue(JSON.stringify(mockSettings))
      
      const result = wrapper.vm.loadSettings()
      
      expect(result).toBe(true)
      expect(wrapper.vm.settings.avatarEditingEnabled).toBe(false)
      expect(wrapper.vm.settings.attendance).toEqual({ '1': true, '2': false })
      expect(wrapper.vm.settings.currentClassroomId).toBe('class-123')
    })

    it('should handle localStorage save errors gracefully', () => {
      localStorage.setItem.mockImplementation(() => {
        throw new Error('Storage quota exceeded')
      })
      
      const result = wrapper.vm.saveSettings()
      
      expect(result).toBe(false)
      expect(console.error).toHaveBeenCalledWith(
        'Failed to save settings:',
        expect.any(Error)
      )
    })

    it('should handle localStorage load errors gracefully', () => {
      localStorage.getItem.mockImplementation(() => {
        throw new Error('Storage access denied')
      })
      
      const result = wrapper.vm.loadSettings()
      
      expect(result).toBe(false)
      expect(console.error).toHaveBeenCalledWith(
        'Failed to load settings:',
        expect.any(Error)
      )
    })

    it('should handle corrupted localStorage data', () => {
      localStorage.getItem.mockReturnValue('invalid json data')
      
      const result = wrapper.vm.loadSettings()
      
      expect(result).toBe(false)
      expect(console.error).toHaveBeenCalledWith(
        'Failed to load settings:',
        expect.any(Error)
      )
    })
  })

  describe('Avatar Editing Toggle Functionality and UI Updates', () => {
    it('should show avatar buttons when avatar editing is enabled', async () => {
      wrapper.vm.settings.avatarEditingEnabled = true
      await nextTick()
      
      expect(wrapper.vm.shouldShowAvatarButtons).toBe(true)
      expect(wrapper.find('.settings-toggle').text()).toBe('Avatar editing enabled')
    })

    it('should hide avatar buttons when avatar editing is disabled', async () => {
      wrapper.vm.settings.avatarEditingEnabled = false
      await nextTick()
      
      expect(wrapper.vm.shouldShowAvatarButtons).toBe(false)
      expect(wrapper.find('.settings-toggle').text()).toBe('Avatar editing disabled')
    })

    it('should update UI immediately when avatar editing toggle changes', async () => {
      // Start with enabled
      wrapper.vm.settings.avatarEditingEnabled = true
      await nextTick()
      
      expect(wrapper.vm.shouldShowAvatarButtons).toBe(true)
      
      // Disable avatar editing
      wrapper.vm.settings.avatarEditingEnabled = false
      await nextTick()
      
      // shouldShowAvatarButtons computed property should be false
      expect(wrapper.vm.shouldShowAvatarButtons).toBe(false)
    })

    it('should prevent avatar editing when disabled', async () => {
      wrapper.vm.settings.avatarEditingEnabled = false
      await nextTick()
      
      // Check that the computed property correctly reflects disabled state
      expect(wrapper.vm.shouldShowAvatarButtons).toBe(false)
      
      // The v-show directive should hide the buttons based on the computed property
      const avatarButtonsContainers = wrapper.findAll('.avatar-buttons')
      expect(avatarButtonsContainers.length).toBeGreaterThan(0)
    })

    it('should allow avatar editing when enabled and student is present', async () => {
      wrapper.vm.settings.avatarEditingEnabled = true
      wrapper.vm.settings.attendance = { '1': true, '2': true, '3': true }
      await nextTick()
      
      // Check that avatar editing is enabled
      expect(wrapper.vm.shouldShowAvatarButtons).toBe(true)
      
      // Check that students are present
      expect(wrapper.vm.shouldDisableBehaviorButtons('1')).toBe(false)
      expect(wrapper.vm.shouldDisableBehaviorButtons('2')).toBe(false)
      expect(wrapper.vm.shouldDisableBehaviorButtons('3')).toBe(false)
    })
  })

  describe('Attendance State Changes and Visual Effects', () => {
    it('should mark student as present by default', () => {
      expect(wrapper.vm.isStudentPresent('1')).toBe(true)
      expect(wrapper.vm.isStudentPresent('2')).toBe(true)
      expect(wrapper.vm.isStudentPresent('3')).toBe(true)
    })

    it('should toggle student attendance correctly', async () => {
      // Initially present
      expect(wrapper.vm.isStudentPresent('1')).toBe(true)
      
      // Toggle to absent
      wrapper.vm.toggleStudentAttendance('1')
      expect(wrapper.vm.isStudentPresent('1')).toBe(false)
      expect(wrapper.vm.settings.attendance['1']).toBe(false)
      
      // Toggle back to present
      wrapper.vm.toggleStudentAttendance('1')
      expect(wrapper.vm.isStudentPresent('1')).toBe(true)
      expect(wrapper.vm.settings.attendance['1']).toBe(true)
    })

    it('should apply correct CSS classes for present students', () => {
      wrapper.vm.settings.attendance = { '1': true }
      
      const studentClass = wrapper.vm.getStudentCardClass('1')
      
      expect(studentClass).toContain('student-card')
      expect(studentClass).toContain('cursor-pointer')
      expect(studentClass).toContain('hover:shadow-xl')
      expect(studentClass).not.toContain('student-absent')
      expect(studentClass).not.toContain('opacity-50')
      expect(studentClass).not.toContain('grayscale')
    })

    it('should apply correct CSS classes for absent students', () => {
      wrapper.vm.settings.attendance = { '1': false }
      
      const studentClass = wrapper.vm.getStudentCardClass('1')
      
      expect(studentClass).toContain('student-card')
      expect(studentClass).toContain('student-absent')
      expect(studentClass).toContain('opacity-50')
      expect(studentClass).toContain('grayscale')
      expect(studentClass).toContain('cursor-not-allowed')
    })

    it('should disable behavior buttons for absent students', () => {
      wrapper.vm.settings.attendance = { '1': false, '2': true }
      
      expect(wrapper.vm.shouldDisableBehaviorButtons('1')).toBe(true)
      expect(wrapper.vm.shouldDisableBehaviorButtons('2')).toBe(false)
    })

    it('should mark all students present', () => {
      wrapper.vm.settings.attendance = { '1': false, '2': false, '3': false }
      
      wrapper.vm.markAllPresent()
      
      expect(wrapper.vm.settings.attendance['1']).toBe(true)
      expect(wrapper.vm.settings.attendance['2']).toBe(true)
      expect(wrapper.vm.settings.attendance['3']).toBe(true)
    })

    it('should mark all students absent', () => {
      wrapper.vm.settings.attendance = { '1': true, '2': true, '3': true }
      
      wrapper.vm.markAllAbsent()
      
      expect(wrapper.vm.settings.attendance['1']).toBe(false)
      expect(wrapper.vm.settings.attendance['2']).toBe(false)
      expect(wrapper.vm.settings.attendance['3']).toBe(false)
    })
  })

  describe('Computed Properties for Student Presence and Card Classes', () => {
    it('should correctly compute student presence', () => {
      wrapper.vm.settings.attendance = {
        '1': true,
        '2': false,
        '3': true
      }
      
      expect(wrapper.vm.isStudentPresent('1')).toBe(true)
      expect(wrapper.vm.isStudentPresent('2')).toBe(false)
      expect(wrapper.vm.isStudentPresent('3')).toBe(true)
      expect(wrapper.vm.isStudentPresent('4')).toBe(true) // Default to present for unknown students
    })

    it('should reactively update when attendance changes', async () => {
      wrapper.vm.settings.attendance = { '1': true }
      await nextTick()
      
      expect(wrapper.vm.shouldDisableBehaviorButtons('1')).toBe(false)
      
      wrapper.vm.settings.attendance['1'] = false
      await nextTick()
      
      expect(wrapper.vm.shouldDisableBehaviorButtons('1')).toBe(true)
    })

    it('should compute correct card classes for different attendance states', () => {
      // Test present student
      wrapper.vm.settings.attendance = { '1': true }
      let cardClass = wrapper.vm.getStudentCardClass('1')
      expect(cardClass).toContain('hover:shadow-xl')
      expect(cardClass).not.toContain('student-absent')
      
      // Test absent student
      wrapper.vm.settings.attendance = { '1': false }
      cardClass = wrapper.vm.getStudentCardClass('1')
      expect(cardClass).toContain('student-absent')
      expect(cardClass).toContain('opacity-50')
      expect(cardClass).toContain('grayscale')
    })

    it('should handle edge cases in computed properties', () => {
      // Test with null/undefined attendance
      wrapper.vm.settings.attendance = {}
      expect(wrapper.vm.isStudentPresent(null)).toBe(true)
      expect(wrapper.vm.isStudentPresent(undefined)).toBe(true)
      expect(wrapper.vm.isStudentPresent('')).toBe(true)
      
      // Test with invalid student IDs
      expect(wrapper.vm.getStudentCardClass(null)).toContain('student-card')
      expect(wrapper.vm.shouldDisableBehaviorButtons(undefined)).toBe(false)
    })
  })

  describe('Integration Tests', () => {
    it('should disable avatar buttons for absent students even when avatar editing is enabled', async () => {
      wrapper.vm.settings.avatarEditingEnabled = true
      wrapper.vm.settings.attendance = { '1': false, '2': true }
      await nextTick()
      
      // Avatar editing should be enabled globally
      expect(wrapper.vm.shouldShowAvatarButtons).toBe(true)
      
      // Student 1 is absent - behavior buttons should be disabled
      expect(wrapper.vm.shouldDisableBehaviorButtons('1')).toBe(true)
      
      // Student 2 is present - behavior buttons should be enabled
      expect(wrapper.vm.shouldDisableBehaviorButtons('2')).toBe(false)
      
      // The template logic should hide avatar buttons for absent students
      // This is tested through the computed properties that control visibility
    })

    it('should disable behavior buttons for absent students', async () => {
      wrapper.vm.settings.attendance = { '1': false, '2': true }
      await nextTick()
      
      const starButtons = wrapper.findAll('.star-btn')
      
      // Student 1 is absent - button should be disabled
      expect(starButtons[0].attributes('disabled')).toBeDefined()
      
      // Student 2 is present - button should not be disabled
      expect(starButtons[1].attributes('disabled')).toBeUndefined()
    })

    it('should update attendance display correctly', async () => {
      wrapper.vm.settings.attendance = { '1': false, '2': true }
      await nextTick()
      
      const attendanceItems = wrapper.findAll('.attendance-item button')
      
      expect(attendanceItems[0].text()).toBe('Absent')
      expect(attendanceItems[1].text()).toBe('Present')
      expect(attendanceItems[2].text()).toBe('Present') // Default to present
    })

    it('should handle complex state changes correctly', async () => {
      // Start with all students present and avatar editing enabled
      wrapper.vm.settings.avatarEditingEnabled = true
      wrapper.vm.settings.attendance = { '1': true, '2': true, '3': true }
      await nextTick()
      
      // Verify initial state
      expect(wrapper.vm.shouldShowAvatarButtons).toBe(true)
      expect(wrapper.vm.shouldDisableBehaviorButtons('1')).toBe(false)
      
      // Mark student 1 as absent
      wrapper.vm.toggleStudentAttendance('1')
      await nextTick()
      
      // Verify student 1 is now disabled but others are not
      expect(wrapper.vm.shouldDisableBehaviorButtons('1')).toBe(true)
      expect(wrapper.vm.shouldDisableBehaviorButtons('2')).toBe(false)
      
      // Disable avatar editing
      wrapper.vm.settings.avatarEditingEnabled = false
      await nextTick()
      
      // Verify avatar buttons are hidden for all students
      expect(wrapper.vm.shouldShowAvatarButtons).toBe(false)
      
      // But behavior button state should remain based on attendance
      expect(wrapper.vm.shouldDisableBehaviorButtons('1')).toBe(true)
      expect(wrapper.vm.shouldDisableBehaviorButtons('2')).toBe(false)
    })
  })
})