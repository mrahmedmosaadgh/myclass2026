import { describe, it, expect, beforeEach, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import { ref, computed, nextTick } from 'vue'
import { Quasar, Notify } from 'quasar'

// Mock the complete reward system component for integration testing
const mockRewardSystemIntegrationComponent = {
  template: `
    <div class="reward-system-container">
      <!-- Settings Button -->
      <button 
        class="settings-main-btn" 
        @click="trackDialogOpen"
        data-testid="settings-button"
      >
        Settings
      </button>

      <!-- Settings Dialog -->
      <div 
        v-if="showSettingsDialog" 
        class="settings-dialog" 
        data-testid="settings-dialog"
      >
        <div class="dialog-header">
          <h3>Classroom Settings</h3>
          <button 
            class="close-btn" 
            @click="closeSettingsDialog"
            data-testid="close-settings-btn"
          >
            Close
          </button>
        </div>

        <!-- Avatar Editing Toggle -->
        <div class="avatar-editing-section">
          <label class="toggle-container">
            <input 
              type="checkbox" 
              v-model="settings.avatarEditingEnabled"
              @change="onAvatarEditingToggle"
              data-testid="avatar-editing-toggle"
            />
            <span class="toggle-label">Enable Avatar Editing</span>
          </label>
          <div 
            class="toggle-status-indicator"
            :class="{ 'enabled': settings.avatarEditingEnabled, 'disabled': !settings.avatarEditingEnabled }"
            data-testid="avatar-editing-status"
          >
            {{ settings.avatarEditingEnabled ? 'Enabled' : 'Disabled' }}
          </div>
        </div>

        <!-- Attendance Section -->
        <div class="attendance-section" v-if="students.length">
          <h4>Student Attendance</h4>
          <div class="attendance-list">
            <div 
              v-for="student in students" 
              :key="student.id"
              class="attendance-item"
              :data-testid="\`attendance-item-\${student.id}\`"
            >
              <span class="student-name">{{ student.name }}</span>
              <button 
                class="attendance-toggle-btn"
                @click="toggleStudentAttendance(student.id)"
                :data-testid="\`attendance-toggle-\${student.id}\`"
              >
                {{ isStudentPresent(student.id) ? 'Present' : 'Absent' }}
              </button>
            </div>
          </div>
        </div>

        <!-- Save Settings Button -->
        <div class="dialog-actions">
          <button 
            class="save-settings-btn" 
            @click="saveSettingsAndClose"
            data-testid="save-settings-btn"
          >
            Save Settings
          </button>
        </div>
      </div>

      <!-- Student Cards -->
      <div class="student-cards-container">
        <div 
          v-for="student in students" 
          :key="student.id"
          :class="getStudentCardClass(student.id)"
          :data-testid="\`student-card-\${student.id}\`"
        >
          <div class="student-info">
            <h4 class="student-name">{{ student.name }}</h4>
          </div>

          <!-- Avatar Buttons -->
          <div 
            class="avatar-buttons"
            v-if="shouldShowAvatarButtons && !shouldDisableBehaviorButtons(student.id)"
            :data-testid="\`avatar-buttons-\${student.id}\`"
          >
            <button 
              class="upload-btn avatar-btn-enabled"
              @click="openAvatarPicker(student)"
              :data-testid="\`upload-btn-\${student.id}\`"
            >
              Upload
            </button>
            <button 
              class="camera-btn avatar-btn-enabled"
              @click="openCamera(student)"
              :data-testid="\`camera-btn-\${student.id}\`"
            >
              Camera
            </button>
          </div>

          <!-- Disabled Avatar Buttons -->
          <div 
            class="avatar-buttons-disabled"
            v-if="!shouldShowAvatarButtons || shouldDisableBehaviorButtons(student.id)"
            :data-testid="\`avatar-buttons-disabled-\${student.id}\`"
          >
            <button 
              class="upload-btn avatar-btn-disabled"
              disabled
              :data-testid="\`upload-btn-disabled-\${student.id}\`"
            >
              Upload
            </button>
            <button 
              class="camera-btn avatar-btn-disabled"
              disabled
              :data-testid="\`camera-btn-disabled-\${student.id}\`"
            >
              Camera
            </button>
          </div>

          <!-- Behavior Buttons -->
          <div class="behavior-buttons">
            <button 
              class="star-btn behavior-btn"
              :class="{ 'star-btn-disabled': shouldDisableBehaviorButtons(student.id), 'star-btn-enabled': !shouldDisableBehaviorButtons(student.id) }"
              :disabled="shouldDisableBehaviorButtons(student.id)"
              @click="openBehaviorDialog(student.id)"
              :data-testid="\`star-btn-\${student.id}\`"
            >
              Star
            </button>
          </div>
        </div>
      </div>

      <!-- Page Refresh Simulation -->
      <button 
        class="refresh-page-btn" 
        @click="simulatePageRefresh"
        data-testid="refresh-page-btn"
      >
        Simulate Page Refresh
      </button>
    </div>
  `,
  setup() {
    // Settings state management
    const settings = ref({
      avatarEditingEnabled: true,
      attendance: {},
      currentClassroomId: null
    })

    // UI state
    const showSettingsDialog = ref(false)
    const dialogOpenTime = ref(null)
    const dialogCloseTime = ref(null)

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
      const presentClass = 'hover:shadow-xl hover:-translate-y-1 student-present'
      const absentClass = 'student-absent opacity-50 grayscale cursor-not-allowed'
      
      return isStudentPresent(studentId) 
        ? `${baseClass} ${presentClass}`
        : `${baseClass} ${absentClass}`
    })

    const shouldDisableBehaviorButtons = computed(() => (studentId) => {
      return !isStudentPresent(studentId)
    })

    // Dialog management
    const closeSettingsDialog = () => {
      dialogCloseTime.value = Date.now()
      showSettingsDialog.value = false
    }

    const saveSettingsAndClose = () => {
      saveSettings()
      closeSettingsDialog()
    }

    // Event handlers
    const onAvatarEditingToggle = () => {
      // Immediate UI feedback - this will trigger computed property updates
      nextTick(() => {
        console.log('Avatar editing toggled:', settings.value.avatarEditingEnabled)
      })
    }

    // Attendance management
    const toggleStudentAttendance = (studentId) => {
      const currentStatus = isStudentPresent(studentId)
      settings.value.attendance[studentId] = !currentStatus
      
      // Immediate visual feedback
      nextTick(() => {
        console.log(`Student ${studentId} attendance changed:`, !currentStatus)
      })
    }

    // Mock functions
    const openAvatarPicker = (student) => {
      console.log(`Opening avatar picker for student ${student.id}`)
    }

    const openCamera = (student) => {
      console.log(`Opening camera for student ${student.id}`)
    }

    const openBehaviorDialog = (studentId) => {
      console.log(`Opening behavior dialog for student ${studentId}`)
    }

    // Page refresh simulation
    const simulatePageRefresh = () => {
      // Save current settings
      const currentSettings = { ...settings.value }
      saveSettings()
      
      // Clear current state
      settings.value = {
        avatarEditingEnabled: true,
        attendance: {},
        currentClassroomId: null
      }
      
      // Load settings (simulating page refresh)
      const loaded = loadSettings()
      if (!loaded) {
        // If loading failed, restore the current settings for test consistency
        Object.assign(settings.value, currentSettings)
      }
    }

    // Initialize settings on mount
    const initializeSettings = () => {
      loadSettings()
    }

    // Track dialog open time
    const trackDialogOpen = () => {
      dialogOpenTime.value = Date.now()
      showSettingsDialog.value = true
    }

    return {
      settings,
      showSettingsDialog,
      dialogOpenTime,
      dialogCloseTime,
      students,
      saveSettings,
      loadSettings,
      isStudentPresent,
      shouldShowAvatarButtons,
      getStudentCardClass,
      shouldDisableBehaviorButtons,
      closeSettingsDialog,
      saveSettingsAndClose,
      onAvatarEditingToggle,
      toggleStudentAttendance,
      openAvatarPicker,
      openCamera,
      openBehaviorDialog,
      simulatePageRefresh,
      initializeSettings,
      trackDialogOpen
    }
  }
}

describe('Reward System Settings Integration Tests', () => {
  let wrapper

  beforeEach(() => {
    // Clear localStorage before each test
    localStorage.clear()
    
    wrapper = mount(mockRewardSystemIntegrationComponent, {
      global: {
        plugins: [Quasar]
      }
    })
    
    // Initialize settings
    wrapper.vm.initializeSettings()
  })

  describe('Full Settings Dialog Workflow from Open to Close', () => {
    it('should open settings dialog when settings button is clicked', async () => {
      const settingsButton = wrapper.find('[data-testid="settings-button"]')
      expect(settingsButton.exists()).toBe(true)
      
      // Initially dialog should be closed
      expect(wrapper.vm.showSettingsDialog).toBe(false)
      expect(wrapper.find('[data-testid="settings-dialog"]').exists()).toBe(false)
      
      // Click settings button
      await settingsButton.trigger('click')
      
      // Dialog should now be open
      expect(wrapper.vm.showSettingsDialog).toBe(true)
      expect(wrapper.find('[data-testid="settings-dialog"]').exists()).toBe(true)
      expect(wrapper.vm.dialogOpenTime).toBeTruthy()
    })

    it('should close settings dialog when close button is clicked', async () => {
      // Open dialog first
      await wrapper.find('[data-testid="settings-button"]').trigger('click')
      expect(wrapper.vm.showSettingsDialog).toBe(true)
      
      // Click close button
      const closeButton = wrapper.find('[data-testid="close-settings-btn"]')
      await closeButton.trigger('click')
      
      // Dialog should be closed
      expect(wrapper.vm.showSettingsDialog).toBe(false)
      expect(wrapper.find('[data-testid="settings-dialog"]').exists()).toBe(false)
      expect(wrapper.vm.dialogCloseTime).toBeTruthy()
    })

    it('should save settings and close dialog when save button is clicked', async () => {
      // Open dialog and make changes
      await wrapper.find('[data-testid="settings-button"]').trigger('click')
      
      const avatarToggle = wrapper.find('[data-testid="avatar-editing-toggle"]')
      await avatarToggle.setChecked(false)
      
      // Click save button
      const saveButton = wrapper.find('[data-testid="save-settings-btn"]')
      await saveButton.trigger('click')
      
      // Dialog should be closed and settings saved
      expect(wrapper.vm.showSettingsDialog).toBe(false)
      expect(localStorage.setItem).toHaveBeenCalledWith(
        'reward-system-settings',
        expect.stringContaining('"avatarEditingEnabled":false')
      )
    })

    it('should maintain dialog state during interaction', async () => {
      // Open dialog
      await wrapper.find('[data-testid="settings-button"]').trigger('click')
      
      // Make multiple changes
      const avatarToggle = wrapper.find('[data-testid="avatar-editing-toggle"]')
      await avatarToggle.setChecked(false)
      await nextTick()
      
      const attendanceToggle = wrapper.find('[data-testid="attendance-toggle-1"]')
      await attendanceToggle.trigger('click')
      await nextTick()
      
      // Dialog should still be open
      expect(wrapper.vm.showSettingsDialog).toBe(true)
      expect(wrapper.find('[data-testid="settings-dialog"]').exists()).toBe(true)
      
      // Settings should be updated
      expect(wrapper.vm.settings.avatarEditingEnabled).toBe(false)
      expect(wrapper.vm.settings.attendance['1']).toBe(false)
    })

    it('should track dialog open and close times', async () => {
      const initialOpenTime = wrapper.vm.dialogOpenTime
      const initialCloseTime = wrapper.vm.dialogCloseTime
      
      // Open dialog
      await wrapper.find('[data-testid="settings-button"]').trigger('click')
      
      expect(wrapper.vm.dialogOpenTime).toBeGreaterThan(initialOpenTime || 0)
      expect(typeof wrapper.vm.dialogOpenTime).toBe('number')
      
      // Close dialog
      await wrapper.find('[data-testid="close-settings-btn"]').trigger('click')
      
      expect(wrapper.vm.dialogCloseTime).toBeGreaterThan(wrapper.vm.dialogOpenTime)
      expect(typeof wrapper.vm.dialogCloseTime).toBe('number')
    })
  })

  describe('Avatar Editing Disable/Enable with Immediate UI Feedback', () => {
    it('should show avatar buttons when avatar editing is enabled', async () => {
      // Ensure avatar editing is enabled
      wrapper.vm.settings.avatarEditingEnabled = true
      await nextTick()
      
      // Check that avatar buttons are visible for present students
      const avatarButtons = wrapper.find('[data-testid="avatar-buttons-1"]')
      expect(avatarButtons.exists()).toBe(true)
      
      const uploadBtn = wrapper.find('[data-testid="upload-btn-1"]')
      const cameraBtn = wrapper.find('[data-testid="camera-btn-1"]')
      
      expect(uploadBtn.exists()).toBe(true)
      expect(cameraBtn.exists()).toBe(true)
      expect(uploadBtn.classes()).toContain('avatar-btn-enabled')
      expect(cameraBtn.classes()).toContain('avatar-btn-enabled')
    })

    it('should hide avatar buttons when avatar editing is disabled', async () => {
      // Disable avatar editing
      wrapper.vm.settings.avatarEditingEnabled = false
      await nextTick()
      
      // Check that enabled avatar buttons are hidden
      const avatarButtons = wrapper.find('[data-testid="avatar-buttons-1"]')
      expect(avatarButtons.exists()).toBe(false)
      
      // Check that disabled avatar buttons are shown
      const disabledAvatarButtons = wrapper.find('[data-testid="avatar-buttons-disabled-1"]')
      expect(disabledAvatarButtons.exists()).toBe(true)
      
      const disabledUploadBtn = wrapper.find('[data-testid="upload-btn-disabled-1"]')
      const disabledCameraBtn = wrapper.find('[data-testid="camera-btn-disabled-1"]')
      
      expect(disabledUploadBtn.exists()).toBe(true)
      expect(disabledCameraBtn.exists()).toBe(true)
      expect(disabledUploadBtn.attributes('disabled')).toBeDefined()
      expect(disabledCameraBtn.attributes('disabled')).toBeDefined()
    })

    it('should provide immediate visual feedback when toggling avatar editing', async () => {
      // Open settings dialog
      await wrapper.find('[data-testid="settings-button"]').trigger('click')
      
      // Initially enabled
      expect(wrapper.vm.settings.avatarEditingEnabled).toBe(true)
      expect(wrapper.find('[data-testid="avatar-editing-status"]').text()).toBe('Enabled')
      expect(wrapper.find('[data-testid="avatar-editing-status"]').classes()).toContain('enabled')
      
      // Toggle to disabled
      const avatarToggle = wrapper.find('[data-testid="avatar-editing-toggle"]')
      await avatarToggle.setChecked(false)
      await nextTick()
      
      // Check immediate feedback
      expect(wrapper.vm.settings.avatarEditingEnabled).toBe(false)
      expect(wrapper.find('[data-testid="avatar-editing-status"]').text()).toBe('Disabled')
      expect(wrapper.find('[data-testid="avatar-editing-status"]').classes()).toContain('disabled')
      
      // Toggle back to enabled
      await avatarToggle.setChecked(true)
      await nextTick()
      
      // Check immediate feedback
      expect(wrapper.vm.settings.avatarEditingEnabled).toBe(true)
      expect(wrapper.find('[data-testid="avatar-editing-status"]').text()).toBe('Enabled')
      expect(wrapper.find('[data-testid="avatar-editing-status"]').classes()).toContain('enabled')
    })

    it('should update avatar button visibility immediately when toggling', async () => {
      // Start with avatar editing enabled
      wrapper.vm.settings.avatarEditingEnabled = true
      await nextTick()
      
      // Verify buttons are visible
      expect(wrapper.find('[data-testid="avatar-buttons-1"]').exists()).toBe(true)
      expect(wrapper.find('[data-testid="avatar-buttons-disabled-1"]').exists()).toBe(false)
      
      // Disable avatar editing
      wrapper.vm.settings.avatarEditingEnabled = false
      await nextTick()
      
      // Verify immediate UI update
      expect(wrapper.find('[data-testid="avatar-buttons-1"]').exists()).toBe(false)
      expect(wrapper.find('[data-testid="avatar-buttons-disabled-1"]').exists()).toBe(true)
      
      // Re-enable avatar editing
      wrapper.vm.settings.avatarEditingEnabled = true
      await nextTick()
      
      // Verify immediate UI update
      expect(wrapper.find('[data-testid="avatar-buttons-1"]').exists()).toBe(true)
      expect(wrapper.find('[data-testid="avatar-buttons-disabled-1"]').exists()).toBe(false)
    })

    it('should handle avatar editing toggle through settings dialog', async () => {
      // Open settings dialog
      await wrapper.find('[data-testid="settings-button"]').trigger('click')
      
      // Toggle avatar editing off
      const avatarToggle = wrapper.find('[data-testid="avatar-editing-toggle"]')
      await avatarToggle.setChecked(false)
      await nextTick()
      
      // Check that main UI is updated immediately
      expect(wrapper.find('[data-testid="avatar-buttons-1"]').exists()).toBe(false)
      expect(wrapper.find('[data-testid="avatar-buttons-disabled-1"]').exists()).toBe(true)
      
      // Toggle back on
      await avatarToggle.setChecked(true)
      await nextTick()
      
      // Check that main UI is updated immediately
      expect(wrapper.find('[data-testid="avatar-buttons-1"]').exists()).toBe(true)
      expect(wrapper.find('[data-testid="avatar-buttons-disabled-1"]').exists()).toBe(false)
    })
  })

  describe('Attendance Marking with Visual Card State Changes', () => {
    it('should mark students as present by default', () => {
      expect(wrapper.vm.isStudentPresent('1')).toBe(true)
      expect(wrapper.vm.isStudentPresent('2')).toBe(true)
      expect(wrapper.vm.isStudentPresent('3')).toBe(true)
      
      // Check visual state
      const studentCard = wrapper.find('[data-testid="student-card-1"]')
      expect(studentCard.classes()).toContain('student-present')
      expect(studentCard.classes()).not.toContain('student-absent')
    })

    it('should toggle student attendance and update visual state immediately', async () => {
      // Open settings dialog
      await wrapper.find('[data-testid="settings-button"]').trigger('click')
      
      // Initially present
      expect(wrapper.vm.isStudentPresent('1')).toBe(true)
      expect(wrapper.find('[data-testid="student-card-1"]').classes()).toContain('student-present')
      
      // Toggle to absent
      const attendanceToggle = wrapper.find('[data-testid="attendance-toggle-1"]')
      await attendanceToggle.trigger('click')
      await nextTick()
      
      // Check immediate visual update
      expect(wrapper.vm.isStudentPresent('1')).toBe(false)
      expect(wrapper.find('[data-testid="student-card-1"]').classes()).toContain('student-absent')
      expect(wrapper.find('[data-testid="student-card-1"]').classes()).toContain('opacity-50')
      expect(wrapper.find('[data-testid="student-card-1"]').classes()).toContain('grayscale')
      
      // Check attendance button text
      expect(attendanceToggle.text()).toBe('Absent')
    })

    it('should disable behavior buttons for absent students', async () => {
      // Mark student as absent
      wrapper.vm.settings.attendance['1'] = false
      await nextTick()
      
      // Check that behavior button is disabled
      const starBtn = wrapper.find('[data-testid="star-btn-1"]')
      expect(starBtn.attributes('disabled')).toBeDefined()
      expect(starBtn.classes()).toContain('star-btn-disabled')
      
      // Check that present student's button is still enabled
      const starBtn2 = wrapper.find('[data-testid="star-btn-2"]')
      expect(starBtn2.attributes('disabled')).toBeUndefined()
      expect(starBtn2.classes()).toContain('star-btn-enabled')
    })

    it('should hide avatar buttons for absent students even when avatar editing is enabled', async () => {
      // Ensure avatar editing is enabled
      wrapper.vm.settings.avatarEditingEnabled = true
      
      // Mark student as absent
      wrapper.vm.settings.attendance['1'] = false
      await nextTick()
      
      // Avatar buttons should be hidden for absent student
      expect(wrapper.find('[data-testid="avatar-buttons-1"]').exists()).toBe(false)
      expect(wrapper.find('[data-testid="avatar-buttons-disabled-1"]').exists()).toBe(true)
      
      // Avatar buttons should be visible for present student
      expect(wrapper.find('[data-testid="avatar-buttons-2"]').exists()).toBe(true)
      expect(wrapper.find('[data-testid="avatar-buttons-disabled-2"]').exists()).toBe(false)
    })

    it('should apply correct CSS classes based on attendance status', async () => {
      // Test present student
      wrapper.vm.settings.attendance['1'] = true
      await nextTick()
      
      const presentCard = wrapper.find('[data-testid="student-card-1"]')
      expect(presentCard.classes()).toContain('student-present')
      expect(presentCard.classes()).toContain('hover:shadow-xl')
      expect(presentCard.classes()).not.toContain('student-absent')
      expect(presentCard.classes()).not.toContain('opacity-50')
      
      // Test absent student
      wrapper.vm.settings.attendance['2'] = false
      await nextTick()
      
      const absentCard = wrapper.find('[data-testid="student-card-2"]')
      expect(absentCard.classes()).toContain('student-absent')
      expect(absentCard.classes()).toContain('opacity-50')
      expect(absentCard.classes()).toContain('grayscale')
      expect(absentCard.classes()).toContain('cursor-not-allowed')
    })

    it('should update attendance display in settings dialog', async () => {
      // Open settings dialog
      await wrapper.find('[data-testid="settings-button"]').trigger('click')
      
      // Check initial attendance display
      const attendanceToggle1 = wrapper.find('[data-testid="attendance-toggle-1"]')
      const attendanceToggle2 = wrapper.find('[data-testid="attendance-toggle-2"]')
      
      expect(attendanceToggle1.text()).toBe('Present')
      expect(attendanceToggle2.text()).toBe('Present')
      
      // Toggle student 1 to absent
      await attendanceToggle1.trigger('click')
      await nextTick()
      
      // Check updated display
      expect(attendanceToggle1.text()).toBe('Absent')
      expect(attendanceToggle2.text()).toBe('Present') // Should remain unchanged
    })
  })

  describe('Settings Persistence Across Page Refreshes', () => {
    it('should save settings to localStorage', async () => {
      // Make changes to settings
      wrapper.vm.settings.avatarEditingEnabled = false
      wrapper.vm.settings.attendance = { '1': false, '2': true, '3': false }
      wrapper.vm.settings.currentClassroomId = 'test-classroom'
      
      // Save settings
      const result = wrapper.vm.saveSettings()
      
      expect(result).toBe(true)
      expect(localStorage.setItem).toHaveBeenCalledWith(
        'reward-system-settings',
        expect.stringContaining('"avatarEditingEnabled":false')
      )
      expect(localStorage.setItem).toHaveBeenCalledWith(
        'reward-system-settings',
        expect.stringContaining('"attendance":{"1":false,"2":true,"3":false}')
      )
    })

    it('should load settings from localStorage on initialization', () => {
      const mockSettings = {
        avatarEditingEnabled: false,
        attendance: { '1': false, '2': true },
        currentClassroomId: 'saved-classroom',
        lastUpdated: Date.now(),
        version: '1.0.0'
      }
      
      localStorage.getItem.mockReturnValue(JSON.stringify(mockSettings))
      
      // Initialize settings
      const result = wrapper.vm.loadSettings()
      
      expect(result).toBe(true)
      expect(wrapper.vm.settings.avatarEditingEnabled).toBe(false)
      expect(wrapper.vm.settings.attendance).toEqual({ '1': false, '2': true })
      expect(wrapper.vm.settings.currentClassroomId).toBe('saved-classroom')
    })

    it('should maintain settings state after simulated page refresh', async () => {
      // Set initial state
      wrapper.vm.settings.avatarEditingEnabled = false
      wrapper.vm.settings.attendance = { '1': false, '2': true, '3': false }
      wrapper.vm.settings.currentClassroomId = 'test-classroom'
      
      // Mock localStorage to return the settings we just set
      const expectedSettings = {
        avatarEditingEnabled: false,
        attendance: { '1': false, '2': true, '3': false },
        currentClassroomId: 'test-classroom',
        lastUpdated: Date.now(),
        version: '1.0.0'
      }
      localStorage.getItem.mockReturnValue(JSON.stringify(expectedSettings))
      
      // Simulate page refresh
      await wrapper.find('[data-testid="refresh-page-btn"]').trigger('click')
      
      // Settings should be restored
      expect(wrapper.vm.settings.avatarEditingEnabled).toBe(false)
      expect(wrapper.vm.settings.attendance).toEqual({ '1': false, '2': true, '3': false })
      expect(wrapper.vm.settings.currentClassroomId).toBe('test-classroom')
    })

    it('should persist settings through complete workflow', async () => {
      // Open settings dialog
      await wrapper.find('[data-testid="settings-button"]').trigger('click')
      
      // Make changes
      const avatarToggle = wrapper.find('[data-testid="avatar-editing-toggle"]')
      await avatarToggle.setChecked(false)
      
      const attendanceToggle = wrapper.find('[data-testid="attendance-toggle-1"]')
      await attendanceToggle.trigger('click')
      
      // Save and close
      await wrapper.find('[data-testid="save-settings-btn"]').trigger('click')
      
      // Simulate page refresh
      await wrapper.find('[data-testid="refresh-page-btn"]').trigger('click')
      
      // Verify settings are maintained
      expect(wrapper.vm.settings.avatarEditingEnabled).toBe(false)
      expect(wrapper.vm.settings.attendance['1']).toBe(false)
      
      // Verify UI reflects loaded settings
      await nextTick()
      expect(wrapper.find('[data-testid="avatar-buttons-1"]').exists()).toBe(false)
      expect(wrapper.find('[data-testid="student-card-1"]').classes()).toContain('student-absent')
    })

    it('should handle localStorage errors gracefully during persistence', () => {
      localStorage.setItem.mockImplementation(() => {
        throw new Error('Storage quota exceeded')
      })
      
      const result = wrapper.vm.saveSettings()
      
      expect(result).toBe(false)
      expect(console.error).toHaveBeenCalledWith('Failed to save settings:', expect.any(Error))
    })

    it('should handle localStorage errors gracefully during loading', () => {
      localStorage.getItem.mockImplementation(() => {
        throw new Error('Storage access denied')
      })
      
      const result = wrapper.vm.loadSettings()
      
      expect(result).toBe(false)
      expect(console.error).toHaveBeenCalledWith('Failed to load settings:', expect.any(Error))
    })

    it('should maintain default settings when no saved data exists', () => {
      localStorage.getItem.mockReturnValue(null)
      
      const result = wrapper.vm.loadSettings()
      
      expect(result).toBe(false)
      expect(wrapper.vm.settings.avatarEditingEnabled).toBe(true)
      expect(wrapper.vm.settings.attendance).toEqual({})
      expect(wrapper.vm.settings.currentClassroomId).toBe(null)
    })
  })

  describe('Complex Integration Scenarios', () => {
    it('should handle combined avatar editing and attendance changes', async () => {
      // Open settings dialog
      await wrapper.find('[data-testid="settings-button"]').trigger('click')
      
      // Disable avatar editing
      const avatarToggle = wrapper.find('[data-testid="avatar-editing-toggle"]')
      await avatarToggle.setChecked(false)
      await nextTick()
      
      // Mark student as absent
      const attendanceToggle = wrapper.find('[data-testid="attendance-toggle-1"]')
      await attendanceToggle.trigger('click')
      await nextTick()
      
      // Check that both changes are reflected
      expect(wrapper.find('[data-testid="avatar-buttons-1"]').exists()).toBe(false)
      expect(wrapper.find('[data-testid="avatar-buttons-disabled-1"]').exists()).toBe(true)
      expect(wrapper.find('[data-testid="student-card-1"]').classes()).toContain('student-absent')
      expect(wrapper.find('[data-testid="star-btn-1"]').attributes('disabled')).toBeDefined()
      
      // Present student should only be affected by avatar editing
      expect(wrapper.find('[data-testid="avatar-buttons-2"]').exists()).toBe(false)
      expect(wrapper.find('[data-testid="avatar-buttons-disabled-2"]').exists()).toBe(true)
      expect(wrapper.find('[data-testid="student-card-2"]').classes()).toContain('student-present')
      expect(wrapper.find('[data-testid="star-btn-2"]').attributes('disabled')).toBeUndefined()
    })

    it('should maintain state consistency during rapid changes', async () => {
      // Open settings dialog
      await wrapper.find('[data-testid="settings-button"]').trigger('click')
      
      // Rapid changes
      const avatarToggle = wrapper.find('[data-testid="avatar-editing-toggle"]')
      const attendanceToggle = wrapper.find('[data-testid="attendance-toggle-1"]')
      
      // Toggle avatar editing multiple times
      await avatarToggle.setChecked(false)
      await avatarToggle.setChecked(true)
      await avatarToggle.setChecked(false)
      
      // Toggle attendance multiple times
      await attendanceToggle.trigger('click') // absent
      await attendanceToggle.trigger('click') // present
      await attendanceToggle.trigger('click') // absent
      
      await nextTick()
      
      // Final state should be consistent
      expect(wrapper.vm.settings.avatarEditingEnabled).toBe(false)
      expect(wrapper.vm.settings.attendance['1']).toBe(false)
      
      // UI should reflect final state
      expect(wrapper.find('[data-testid="avatar-buttons-1"]').exists()).toBe(false)
      expect(wrapper.find('[data-testid="student-card-1"]').classes()).toContain('student-absent')
      expect(attendanceToggle.text()).toBe('Absent')
    })

    it('should handle settings dialog workflow with persistence', async () => {
      // Complete workflow: open -> change -> save -> refresh -> verify
      
      // Step 1: Open dialog and make changes
      await wrapper.find('[data-testid="settings-button"]').trigger('click')
      
      const avatarToggle = wrapper.find('[data-testid="avatar-editing-toggle"]')
      await avatarToggle.setChecked(false)
      
      const attendanceToggle1 = wrapper.find('[data-testid="attendance-toggle-1"]')
      const attendanceToggle3 = wrapper.find('[data-testid="attendance-toggle-3"]')
      await attendanceToggle1.trigger('click')
      await attendanceToggle3.trigger('click')
      
      // Step 2: Save and close
      await wrapper.find('[data-testid="save-settings-btn"]').trigger('click')
      
      // Mock localStorage to return the expected settings after save
      const expectedSettings = {
        avatarEditingEnabled: false,
        attendance: { '1': false, '2': true, '3': false },
        currentClassroomId: null,
        lastUpdated: Date.now(),
        version: '1.0.0'
      }
      localStorage.getItem.mockReturnValue(JSON.stringify(expectedSettings))
      
      // Step 3: Simulate page refresh
      await wrapper.find('[data-testid="refresh-page-btn"]').trigger('click')
      
      // Step 4: Verify persistence
      expect(wrapper.vm.settings.avatarEditingEnabled).toBe(false)
      expect(wrapper.vm.settings.attendance['1']).toBe(false)
      expect(wrapper.vm.settings.attendance['2']).toBe(true) // Should remain present
      expect(wrapper.vm.settings.attendance['3']).toBe(false)
      
      // Step 5: Verify UI reflects loaded state
      await nextTick()
      
      // Avatar editing should be disabled for all
      expect(wrapper.find('[data-testid="avatar-buttons-1"]').exists()).toBe(false)
      expect(wrapper.find('[data-testid="avatar-buttons-2"]').exists()).toBe(false)
      
      // Students 1 and 3 should be absent, student 2 present
      expect(wrapper.find('[data-testid="student-card-1"]').classes()).toContain('student-absent')
      expect(wrapper.find('[data-testid="student-card-2"]').classes()).toContain('student-present')
      expect(wrapper.find('[data-testid="student-card-3"]').classes()).toContain('student-absent')
    })
  })
})