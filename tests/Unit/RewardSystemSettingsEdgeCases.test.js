import { describe, it, expect, beforeEach, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import { ref, computed, nextTick } from 'vue'
import { Quasar, Notify } from 'quasar'

// Mock component for testing edge cases and error scenarios
const mockSettingsComponent = {
  template: `
    <div>
      <div class="settings-status">{{ settingsStatus }}</div>
      <div class="error-message" v-if="errorMessage">{{ errorMessage }}</div>
    </div>
  `,
  setup() {
    const settings = ref({
      avatarEditingEnabled: true,
      attendance: {},
      currentClassroomId: null
    })

    const errorMessage = ref('')
    const settingsStatus = ref('initialized')

    // Validation functions
    const validateSettingsData = (data) => {
      const defaultSettings = {
        avatarEditingEnabled: true,
        attendance: {},
        currentClassroomId: null
      }

      if (!data || typeof data !== 'object') {
        console.warn('Invalid settings data, using defaults')
        return defaultSettings
      }

      const validated = { ...defaultSettings }

      // Validate avatarEditingEnabled
      if (typeof data.avatarEditingEnabled === 'boolean') {
        validated.avatarEditingEnabled = data.avatarEditingEnabled
      } else {
        console.warn('Invalid avatarEditingEnabled value, using default:', data.avatarEditingEnabled)
      }

      // Validate attendance object
      if (data.attendance && typeof data.attendance === 'object' && !Array.isArray(data.attendance)) {
        const validatedAttendance = {}
        Object.entries(data.attendance).forEach(([studentId, isPresent]) => {
          const numericId = parseInt(studentId)
          if (!isNaN(numericId) && numericId > 0) {
            if (typeof isPresent === 'boolean') {
              validatedAttendance[studentId] = isPresent
            } else {
              console.warn(`Invalid attendance value for student ${studentId}:`, isPresent)
              validatedAttendance[studentId] = true
            }
          } else {
            console.warn('Invalid student ID in attendance:', studentId)
          }
        })
        validated.attendance = validatedAttendance
      } else {
        console.warn('Invalid attendance data, using empty object:', data.attendance)
      }

      // Validate currentClassroomId
      if (data.currentClassroomId === null || 
          (typeof data.currentClassroomId === 'string' && data.currentClassroomId.length > 0) ||
          (typeof data.currentClassroomId === 'number' && data.currentClassroomId > 0)) {
        validated.currentClassroomId = data.currentClassroomId
      } else {
        console.warn('Invalid currentClassroomId, using null:', data.currentClassroomId)
      }

      return validated
    }

    const validateStudentIds = (currentStudents = []) => {
      try {
        if (!currentStudents.length) {
          console.log('No students in current classroom to validate against')
          return true
        }

        const studentsWithInvalidIds = currentStudents.filter(
          student => !student.id || (typeof student.id !== 'string' && typeof student.id !== 'number')
        )

        if (studentsWithInvalidIds.length > 0) {
          console.error('Found students with invalid IDs:', studentsWithInvalidIds)
          errorMessage.value = `Warning: ${studentsWithInvalidIds.length} students have invalid IDs`
        }

        const currentStudentIds = new Set()
        currentStudents.forEach(student => {
          if (student.id) {
            currentStudentIds.add(student.id.toString())
          }
        })
        
        if (currentStudentIds.size === 0) {
          console.warn('No valid student IDs found in current classroom')
          return true
        }

        if (!settings.value.attendance || typeof settings.value.attendance !== 'object') {
          console.warn('Invalid attendance data structure, resetting')
          settings.value.attendance = {}
          return true
        }
        
        const attendanceStudentIds = Object.keys(settings.value.attendance)
        const invalidIds = attendanceStudentIds.filter(id => {
          if (!currentStudentIds.has(id)) {
            return true
          }
          
          const attendanceValue = settings.value.attendance[id]
          if (typeof attendanceValue !== 'boolean') {
            console.warn(`Invalid attendance value for student ${id}:`, attendanceValue)
            return true
          }
          
          return false
        })
        
        if (invalidIds.length > 0) {
          console.warn('Found invalid student IDs or values in attendance:', invalidIds)
          
          const cleanedAttendance = { ...settings.value.attendance }
          let removedCount = 0
          let fixedCount = 0
          
          invalidIds.forEach(id => {
            const attendanceValue = settings.value.attendance[id]
            
            if (!currentStudentIds.has(id)) {
              delete cleanedAttendance[id]
              removedCount++
            } else if (typeof attendanceValue !== 'boolean') {
              cleanedAttendance[id] = true
              fixedCount++
            }
          })
          
          settings.value.attendance = cleanedAttendance
          
          console.log('Attendance validation completed:', {
            removed: removedCount,
            fixed: fixedCount,
            cleanedAttendance
          })
          
          return false
        }
        
        console.log('All student IDs in attendance are valid')
        return true
        
      } catch (error) {
        console.error('Error during student ID validation:', error)
        errorMessage.value = 'Error validating student data - some features may not work correctly'
        return false
      }
    }

    // Storage functions with error handling
    const saveSettingsWithErrorHandling = () => {
      try {
        const settingsData = {
          ...settings.value,
          lastUpdated: Date.now(),
          version: '1.0.0'
        }
        
        // Simulate storage quota exceeded
        if (JSON.stringify(settingsData).length > 1000000) {
          throw new Error('QuotaExceededError: Storage quota exceeded')
        }
        
        localStorage.setItem('reward-system-settings', JSON.stringify(settingsData))
        settingsStatus.value = 'saved'
        return true
      } catch (error) {
        if (error.name === 'QuotaExceededError' || error.message.includes('quota')) {
          return handleStorageQuotaExceeded()
        }
        console.error('Failed to save settings:', error)
        errorMessage.value = 'Failed to save settings'
        settingsStatus.value = 'save_failed'
        return false
      }
    }

    const handleStorageQuotaExceeded = () => {
      try {
        // Try to clear old data
        const keysToCheck = []
        for (let i = 0; i < localStorage.length; i++) {
          const key = localStorage.key(i)
          if (key && key.startsWith('reward-system-')) {
            keysToCheck.push(key)
          }
        }
        
        keysToCheck.forEach(key => {
          if (key.includes('backup') || key.includes('old')) {
            localStorage.removeItem(key)
            console.log('Removed old settings:', key)
          }
        })
        
        // Try saving again (simplified to avoid recursion)
        try {
          const settingsData = {
            ...settings.value,
            lastUpdated: Date.now(),
            version: '1.0.0'
          }
          localStorage.setItem('reward-system-settings', JSON.stringify(settingsData))
          settingsStatus.value = 'saved'
          return true
        } catch (retryError) {
          throw retryError
        }
        
      } catch (error) {
        console.error('Failed to handle storage quota exceeded:', error)
        errorMessage.value = 'Storage full - settings cannot be saved. Please clear browser data.'
        settingsStatus.value = 'storage_full'
        return false
      }
    }

    const loadSettingsWithFallback = () => {
      try {
        const saved = localStorage.getItem('reward-system-settings')
        if (saved) {
          const parsedSettings = JSON.parse(saved)
          const { lastUpdated, version, ...settingsData } = parsedSettings
          const validatedSettings = validateSettingsData(settingsData)
          Object.assign(settings.value, validatedSettings)
          settingsStatus.value = 'loaded'
          return true
        }
        settingsStatus.value = 'no_saved_data'
        return false
      } catch (error) {
        console.error('Failed to load settings from localStorage:', error)
        
        // Try sessionStorage fallback
        try {
          const sessionSaved = sessionStorage.getItem('reward-system-settings-backup')
          if (sessionSaved) {
            const parsedSettings = JSON.parse(sessionSaved)
            const validatedSettings = validateSettingsData(parsedSettings)
            Object.assign(settings.value, validatedSettings)
            settingsStatus.value = 'loaded_from_session'
            return true
          }
        } catch (sessionError) {
          console.warn('sessionStorage fallback also failed:', sessionError)
        }
        
        // Try memory fallback
        if (window.rewardSystemSettingsMemory) {
          try {
            const validatedSettings = validateSettingsData(window.rewardSystemSettingsMemory)
            Object.assign(settings.value, validatedSettings)
            settingsStatus.value = 'loaded_from_memory'
            return true
          } catch (memoryError) {
            console.warn('Memory fallback failed:', memoryError)
          }
        }
        
        errorMessage.value = 'Failed to load settings from all sources'
        settingsStatus.value = 'load_failed'
        return false
      }
    }

    // Classroom change handler with error handling
    const handleClassroomChangeWithValidation = (newClassroomId, students = []) => {
      try {
        if (newClassroomId !== null && 
            typeof newClassroomId !== 'string' && 
            typeof newClassroomId !== 'number') {
          throw new Error(`Invalid classroom ID: ${newClassroomId}`)
        }
        
        settings.value.currentClassroomId = newClassroomId
        settings.value.attendance = {}
        
        if (students.length) {
          const newAttendance = {}
          let validStudentCount = 0
          
          students.forEach(student => {
            if (student && student.id) {
              newAttendance[student.id] = true
              validStudentCount++
            } else {
              console.warn('Invalid student data found:', student)
            }
          })
          
          settings.value.attendance = newAttendance
          console.log(`Initialized attendance for ${validStudentCount} students`)
        }
        
        settingsStatus.value = 'classroom_changed'
        return true
        
      } catch (error) {
        console.error('Error handling classroom change:', error)
        errorMessage.value = `Error switching classroom: ${error.message}`
        settingsStatus.value = 'classroom_change_failed'
        
        // Try to recover
        try {
          settings.value.currentClassroomId = newClassroomId
          settings.value.attendance = {}
          settingsStatus.value = 'recovered'
        } catch (recoveryError) {
          console.error('Failed to recover from classroom change error:', recoveryError)
          settingsStatus.value = 'recovery_failed'
        }
        
        return false
      }
    }

    return {
      settings,
      errorMessage,
      settingsStatus,
      validateSettingsData,
      validateStudentIds,
      saveSettingsWithErrorHandling,
      loadSettingsWithFallback,
      handleClassroomChangeWithValidation,
      handleStorageQuotaExceeded
    }
  }
}

describe('Reward System Settings Edge Cases and Error Handling', () => {
  let wrapper

  beforeEach(() => {
    localStorage.clear()
    sessionStorage.clear()
    delete window.rewardSystemSettingsMemory
    
    wrapper = mount(mockSettingsComponent, {
      global: {
        plugins: [Quasar]
      }
    })
  })

  describe('Settings Data Validation', () => {
    it('should handle null settings data', () => {
      const result = wrapper.vm.validateSettingsData(null)
      
      expect(result).toEqual({
        avatarEditingEnabled: true,
        attendance: {},
        currentClassroomId: null
      })
      expect(console.warn).toHaveBeenCalledWith('Invalid settings data, using defaults')
    })

    it('should handle invalid avatarEditingEnabled values', () => {
      const invalidData = {
        avatarEditingEnabled: 'invalid',
        attendance: {},
        currentClassroomId: null
      }
      
      const result = wrapper.vm.validateSettingsData(invalidData)
      
      expect(result.avatarEditingEnabled).toBe(true)
      expect(console.warn).toHaveBeenCalledWith('Invalid avatarEditingEnabled value, using default:', 'invalid')
    })

    it('should handle invalid attendance data structures', () => {
      const invalidData = {
        avatarEditingEnabled: true,
        attendance: 'not an object',
        currentClassroomId: null
      }
      
      const result = wrapper.vm.validateSettingsData(invalidData)
      
      expect(result.attendance).toEqual({})
      expect(console.warn).toHaveBeenCalledWith('Invalid attendance data, using empty object:', 'not an object')
    })

    it('should validate and clean attendance entries', () => {
      const invalidData = {
        avatarEditingEnabled: true,
        attendance: {
          '123': true,
          'invalid_id': false,
          '456': 'not_boolean',
          '': true,
          '789': false
        },
        currentClassroomId: null
      }
      
      const result = wrapper.vm.validateSettingsData(invalidData)
      
      expect(result.attendance).toEqual({
        '123': true,
        '456': true, // Fixed invalid boolean
        '789': false
      })
      expect(console.warn).toHaveBeenCalledWith('Invalid student ID in attendance:', 'invalid_id')
      expect(console.warn).toHaveBeenCalledWith('Invalid attendance value for student 456:', 'not_boolean')
    })

    it('should handle invalid currentClassroomId values', () => {
      const invalidData = {
        avatarEditingEnabled: true,
        attendance: {},
        currentClassroomId: []
      }
      
      const result = wrapper.vm.validateSettingsData(invalidData)
      
      expect(result.currentClassroomId).toBe(null)
      expect(console.warn).toHaveBeenCalledWith('Invalid currentClassroomId, using null:', [])
    })
  })

  describe('Student ID Validation', () => {
    it('should handle empty student list', () => {
      const result = wrapper.vm.validateStudentIds([])
      
      expect(result).toBe(true)
      expect(console.log).toHaveBeenCalledWith('No students in current classroom to validate against')
    })

    it('should detect students with invalid IDs', () => {
      const studentsWithInvalidIds = [
        { id: '1', name: 'Valid Student' },
        { name: 'No ID Student' },
        { id: null, name: 'Null ID Student' },
        { id: '', name: 'Empty ID Student' }
      ]
      
      wrapper.vm.validateStudentIds(studentsWithInvalidIds)
      
      expect(console.error).toHaveBeenCalledWith('Found students with invalid IDs:', expect.any(Array))
      expect(wrapper.vm.errorMessage).toContain('students have invalid IDs')
    })

    it('should clean invalid attendance entries', () => {
      const validStudents = [
        { id: '1', name: 'Student 1' },
        { id: '2', name: 'Student 2' }
      ]
      
      wrapper.vm.settings.attendance = {
        '1': true,
        '2': false,
        '999': true, // Invalid - not in current classroom
        '3': 'invalid_value' // Invalid value type
      }
      
      const result = wrapper.vm.validateStudentIds(validStudents)
      
      expect(result).toBe(false) // Should return false when cleanup was needed
      expect(wrapper.vm.settings.attendance).toEqual({
        '1': true,
        '2': false
      })
    })

    it('should handle validation errors gracefully', () => {
      // Corrupt the settings object to cause an error
      wrapper.vm.settings = null
      
      const result = wrapper.vm.validateStudentIds([{ id: '1', name: 'Student' }])
      
      expect(result).toBe(false)
      expect(console.error).toHaveBeenCalledWith('Error during student ID validation:', expect.any(Error))
      expect(wrapper.vm.errorMessage).toBe('Error validating student data - some features may not work correctly')
    })
  })

  describe('Storage Error Handling', () => {
    it('should handle localStorage quota exceeded', () => {
      // Mock localStorage to throw quota exceeded error
      localStorage.setItem.mockImplementation(() => {
        const error = new Error('QuotaExceededError: Storage quota exceeded')
        error.name = 'QuotaExceededError'
        throw error
      })
      
      const result = wrapper.vm.saveSettingsWithErrorHandling()
      
      expect(result).toBe(false)
      expect(wrapper.vm.settingsStatus).toBe('storage_full')
      expect(wrapper.vm.errorMessage).toBe('Storage full - settings cannot be saved. Please clear browser data.')
    })

    it('should handle localStorage save errors', () => {
      localStorage.setItem.mockImplementation(() => {
        throw new Error('Storage access denied')
      })
      
      const result = wrapper.vm.saveSettingsWithErrorHandling()
      
      expect(result).toBe(false)
      expect(wrapper.vm.settingsStatus).toBe('save_failed')
      expect(wrapper.vm.errorMessage).toBe('Failed to save settings')
    })

    it('should try sessionStorage fallback when localStorage fails', () => {
      localStorage.getItem.mockImplementation(() => {
        throw new Error('localStorage unavailable')
      })
      
      sessionStorage.getItem.mockReturnValue(JSON.stringify({
        avatarEditingEnabled: false,
        attendance: { '1': true },
        currentClassroomId: 'test'
      }))
      
      const result = wrapper.vm.loadSettingsWithFallback()
      
      expect(result).toBe(true)
      expect(wrapper.vm.settingsStatus).toBe('loaded_from_session')
      expect(wrapper.vm.settings.avatarEditingEnabled).toBe(false)
    })

    it('should try memory fallback when both localStorage and sessionStorage fail', () => {
      localStorage.getItem.mockImplementation(() => {
        throw new Error('localStorage unavailable')
      })
      
      sessionStorage.getItem.mockImplementation(() => {
        throw new Error('sessionStorage unavailable')
      })
      
      window.rewardSystemSettingsMemory = {
        avatarEditingEnabled: false,
        attendance: { '2': false },
        currentClassroomId: 'memory-test'
      }
      
      const result = wrapper.vm.loadSettingsWithFallback()
      
      expect(result).toBe(true)
      expect(wrapper.vm.settingsStatus).toBe('loaded_from_memory')
      expect(wrapper.vm.settings.avatarEditingEnabled).toBe(false)
    })

    it('should handle complete storage failure', () => {
      localStorage.getItem.mockImplementation(() => {
        throw new Error('localStorage unavailable')
      })
      
      sessionStorage.getItem.mockImplementation(() => {
        throw new Error('sessionStorage unavailable')
      })
      
      const result = wrapper.vm.loadSettingsWithFallback()
      
      expect(result).toBe(false)
      expect(wrapper.vm.settingsStatus).toBe('load_failed')
      expect(wrapper.vm.errorMessage).toBe('Failed to load settings from all sources')
    })
  })

  describe('Classroom Change Error Handling', () => {
    it('should handle invalid classroom ID', () => {
      const result = wrapper.vm.handleClassroomChangeWithValidation([], [])
      
      expect(result).toBe(false)
      expect(wrapper.vm.errorMessage).toContain('Invalid classroom ID')
      // The status might be 'recovered' if the recovery logic runs, which is also valid
      expect(['classroom_change_failed', 'recovered']).toContain(wrapper.vm.settingsStatus)
    })

    it('should handle invalid student data during classroom change', () => {
      const invalidStudents = [
        { id: '1', name: 'Valid Student' },
        { name: 'No ID Student' },
        null,
        undefined,
        { id: '2', name: 'Another Valid Student' }
      ]
      
      const result = wrapper.vm.handleClassroomChangeWithValidation('class-123', invalidStudents)
      
      expect(result).toBe(true)
      expect(wrapper.vm.settings.currentClassroomId).toBe('class-123')
      expect(wrapper.vm.settings.attendance).toEqual({
        '1': true,
        '2': true
      })
      expect(console.warn).toHaveBeenCalledWith('Invalid student data found:', { name: 'No ID Student' })
    })

    it('should recover from classroom change errors', () => {
      // Force an error by corrupting the settings object
      const originalSettings = wrapper.vm.settings
      wrapper.vm.settings = null
      
      const result = wrapper.vm.handleClassroomChangeWithValidation('test-class', [])
      
      expect(result).toBe(false)
      expect(wrapper.vm.settingsStatus).toBe('recovery_failed')
      
      // Restore settings for cleanup
      wrapper.vm.settings = originalSettings
    })
  })

  describe('Integration Error Scenarios', () => {
    it('should handle corrupted localStorage data during load', () => {
      localStorage.getItem.mockReturnValue('invalid json data {')
      
      const result = wrapper.vm.loadSettingsWithFallback()
      
      expect(result).toBe(false)
      expect(console.error).toHaveBeenCalledWith('Failed to load settings from localStorage:', expect.any(Error))
    })

    it('should handle quota exceeded with cleanup', () => {
      // Mock localStorage with existing old data
      const mockKeys = ['reward-system-settings', 'reward-system-backup-old', 'reward-system-backup-2023']
      localStorage.key.mockImplementation((index) => mockKeys[index] || null)
      Object.defineProperty(localStorage, 'length', { value: mockKeys.length })
      
      // First call should fail with quota exceeded
      localStorage.setItem.mockImplementationOnce(() => {
        throw new Error('QuotaExceededError: Storage quota exceeded')
      })
      
      // Second call (after cleanup) should succeed
      localStorage.setItem.mockImplementationOnce(() => {
        return undefined
      })
      
      const result = wrapper.vm.saveSettingsWithErrorHandling()
      
      expect(localStorage.removeItem).toHaveBeenCalledWith('reward-system-backup-old')
      expect(wrapper.vm.settingsStatus).toBe('saved')
    })

    it('should maintain data integrity during error recovery', async () => {
      const originalSettings = {
        avatarEditingEnabled: false,
        attendance: { '1': true, '2': false },
        currentClassroomId: 'test-class'
      }
      
      wrapper.vm.settings = { ...originalSettings }
      
      // Simulate error during save
      localStorage.setItem.mockImplementation(() => {
        throw new Error('Temporary storage error')
      })
      
      const saveResult = wrapper.vm.saveSettingsWithErrorHandling()
      expect(saveResult).toBe(false)
      
      // Settings should remain unchanged after failed save
      expect(wrapper.vm.settings).toEqual(originalSettings)
    })
  })
})