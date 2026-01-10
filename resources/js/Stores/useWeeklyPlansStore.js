import { defineStore } from 'pinia'
import { useSchoolDataStore } from '@/Stores/schoolData'

export const useWeeklyPlansStore = defineStore('weekly-plans', {
    state: () => ({
        weekNumber: 1,
        maxWeeks: 18,
        currentWeek: 1
    }),

    getters: {
        // Proxy getters to SchoolData store
        selectedCopyId: () => {
            const schoolData = useSchoolDataStore()
            return schoolData.scheduleCopyId
        },
        selectedSchoolId: () => {
            const schoolData = useSchoolDataStore()
            return schoolData.schoolId
        },
        selectedAcademicYearId: () => {
            const schoolData = useSchoolDataStore()
            return schoolData.academicYearId
        },
        semesterNumber: () => {
            const schoolData = useSchoolDataStore()
            // Try to parse number from name, e.g. "Semester 1" -> 1. Default to 1.
            if (schoolData.semesterName) {
                const match = schoolData.semesterName.match(/\d+/)
                return match ? parseInt(match[0]) : 1
            }
            return 1
        },
        activeCopies: () => {
            const schoolData = useSchoolDataStore()
            return schoolData.scheduleCopies || []
        }
    },

    actions: {
        setWeekNumber(week) {
            this.weekNumber = week
        },

        calculateCurrentWeek() {
            const now = new Date()
            const startOfYear = new Date(now.getFullYear(), 0, 1)
            const week = Math.ceil(((now - startOfYear) / 86400000 + startOfYear.getDay() + 1) / 7)
            this.currentWeek = week

            // Set initial week if not set or out of range
            if (this.currentWeek > this.maxWeeks) {
                this.weekNumber = 1 // Reset to 1 if we are past max weeks (e.g. summer break)
            } else {
                this.weekNumber = this.currentWeek
            }
        },

        // Helper to ensure data is loaded if needed (though MainSchoolData usually handles it)
        async fetchActiveCopies() {
            const schoolData = useSchoolDataStore()
            if (!schoolData.scheduleCopies?.length && schoolData.schoolId) {
                await schoolData.fetchOptionsForSchool(schoolData.schoolId)
            }
        }
    }
})
