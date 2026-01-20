import { defineStore } from 'pinia';
import axios from 'axios';

export const useSchoolDataStore = defineStore('schoolData', {
  state: () => ({
    schoolId: null,
    schoolName: null,
    academicYearId: null,
    academicYearName: null,
    semesterId: null,
    semesterName: null,
    schools: [],
    academicYears: [],
    semesters: [],
    loading: false,
  }),

  getters: {
    isSchoolSelected: (state) => !!state.schoolId,
    isAcademicYearSet: (state) => !!state.academicYearId,
    isSemesterSet: (state) => !!state.semesterId,
    hasAllContext: (state) => !!state.schoolId && !!state.academicYearId && !!state.semesterId,
  },

  actions: {
    async fetchSchools() {
      this.loading = true;
      try {
        const response = await axios.get(route('weekly-system.api.school-data'));
        this.schools = response.data.schools || [];

        // Automatically select the first school if none is selected
        if (!this.schoolId && this.schools.length > 0) {
          this.setSchool(this.schools[0].id);
        }
      } catch (error) {
        console.error('Failed to fetch schools:', error);
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async fetchOptionsForSchool(schoolId) {
      this.loading = true;
      try {
        // Fetch academic years
        const academicYearsResponse = await axios.get('/api/academic-years');
        this.academicYears = academicYearsResponse.data.data || academicYearsResponse.data;

        // Fetch semesters
        const semestersResponse = await axios.get('/api/semesters');
        this.semesters = semestersResponse.data.data || semestersResponse.data;

      } catch (error) {
        console.error('Failed to fetch options:', error);
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async setSchool(schoolId) {
      if (!schoolId) return;

      this.schoolId = schoolId;

      // Fetch school details to get the related data
      try {
        const response = await axios.get(`/api/schools/${schoolId}`);
        const school = response.data.data;

        this.schoolName = school.name;
        this.academicYearId = school.academic_year_id;
        this.semesterId = school.semester_id;

        // Update names based on related data if available
        if (school.active_academic_year) {
          this.academicYearName = school.active_academic_year.name;
        }
        if (school.active_semester) {
          this.semesterName = school.active_semester.name;
        }

        // Fetch options for this school
        await this.fetchOptionsForSchool(schoolId);
      } catch (error) {
        console.error('Failed to fetch school details:', error);
      }
    },

    async updateSchoolSettings(settings) {
      if (!this.schoolId) return;

      try {
        const response = await axios.put(`/api/schools/${this.schoolId}`, settings);

        // Update local state with new values
        if (settings.academic_year_id !== undefined) {
          this.academicYearId = settings.academic_year_id;
          if (settings.academic_year_id) {
            const year = this.academicYears.find(ay => ay.id === settings.academic_year_id);
            this.academicYearName = year ? year.name : null;
          } else {
            this.academicYearName = null;
          }
        }

        if (settings.semester_id !== undefined) {
          this.semesterId = settings.semester_id;
          if (settings.semester_id) {
            const sem = this.semesters.find(s => s.id === settings.semester_id);
            this.semesterName = sem ? sem.name : null;
          } else {
            this.semesterName = null;
          }
        }

        return response.data;
      } catch (error) {
        console.error('Failed to update school settings:', error);
        throw error;
      }
    },

    reset() {
      this.schoolId = null;
      this.schoolName = null;
      this.academicYearId = null;
      this.academicYearName = null;
      this.semesterId = null;
      this.semesterName = null;
    }
  },
});