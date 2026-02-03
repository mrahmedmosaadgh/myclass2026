<template>
  <div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-8">Skill Practice</h1>
    
    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Filter by Grade</label>
          <select 
            v-model="filters.gradeId" 
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            @change="applyFilters"
          >
            <option value="">All Grades</option>
            <option v-for="grade in uniqueGrades" :key="grade.id" :value="grade.id">{{ grade.name }}</option>
          </select>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Filter by Subject</label>
          <select 
            v-model="filters.subjectId" 
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            @change="applyFilters"
          >
            <option value="">All Subjects</option>
            <option v-for="subject in uniqueSubjects" :key="subject.id" :value="subject.id">{{ subject.name }}</option>
          </select>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Filter by Category</label>
          <select 
            v-model="filters.categoryId" 
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            @change="applyFilters"
          >
            <option value="">All Categories</option>
            <option v-for="category in filteredCategories" :key="category.id" :value="category.id">{{ category.name }}</option>
          </select>
        </div>
      </div>
      
      <!-- Reset Filters Button -->
      <div class="mt-4 text-right">
        <button
          @click="resetFilters"
          class="text-sm text-indigo-600 hover:text-indigo-800 underline"
          v-if="hasActiveFilters"
        >
          Reset Filters
        </button>
      </div>
    </div>
    
    <!-- Error State -->
    <div v-if="error" class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
      <div class="flex">
        <div class="flex-shrink-0">
          <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
          </svg>
        </div>
        <div class="ml-3">
          <p class="text-sm text-red-700">{{ error }}</p>
          <button
            @click="retryLoadData"
            class="mt-2 text-sm text-indigo-600 hover:text-indigo-800 underline"
          >
            Try Again
          </button>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-else-if="loading" class="flex justify-center items-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-indigo-500"></div>
      <span class="ml-3 text-lg text-gray-600">Loading skills...</span>
    </div>
    
    <!-- Skills Grid -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div 
        v-for="skill in filteredSkills" 
        :key="skill.id"
        class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300"
      >
        <div class="p-6">
          <div class="flex justify-between items-start">
            <div>
              <h3 class="text-lg font-semibold text-gray-800">{{ skill.name }}</h3>
              <p class="text-sm text-gray-500 mt-1">{{ skill.description }}</p>
            </div>
            <span 
              v-if="skill.category" 
              class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800"
            >
              {{ skill.category.name }}
            </span>
          </div>
          
          <!-- SmartScore Progress Bar -->
          <div class="mt-4">
            <div class="flex justify-between text-sm mb-1">
              <span>SmartScore</span>
              <span>{{ skill.user_progress?.smart_score || 0 }}/100</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2.5">
              <div 
                class="h-2.5 rounded-full" 
                :style="{ width: `${calculateMasteryPercentage(skill.user_progress?.smart_score || 0)}%` }"
                :class="getScoreColorClass(skill.user_progress?.smart_score || 0)"
              ></div>
            </div>
            <div class="mt-1 text-xs text-gray-500">
              {{ skill.user_progress?.mastery_level || 'Beginner' }}
            </div>
          </div>
          
          <!-- Questions Count -->
          <div class="mt-4 flex items-center text-sm text-gray-500">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            {{ skill.questions_count }} questions
          </div>
          
          <!-- Start Practice Button -->
          <div class="mt-6">
            <button
              @click="startPractice(skill)"
              class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-4 rounded-md text-sm font-medium transition-colors duration-300"
            >
              Start Practice
            </button>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Empty State -->
    <div v-if="!loading && filteredSkills.length === 0" class="text-center py-12">
      <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
      <h3 class="mt-2 text-sm font-medium text-gray-900">No skills</h3>
      <p class="mt-1 text-sm text-gray-500">Get started by selecting different filters or come back later.</p>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';

export default {
  name: 'SkillBrowser',
  
  setup() {
    const skills = ref([]);
    const categories = ref([]);
    const grades = ref([]);
    const subjects = ref([]);
    const loading = ref(true);
    const error = ref(null);
    
    const filters = ref({
      gradeId: '',
      subjectId: '',
      categoryId: ''
    });
    
    // Check if any filters are active
    const hasActiveFilters = computed(() => {
      return filters.value.gradeId !== '' || 
             filters.value.subjectId !== '' || 
             filters.value.categoryId !== '';
    });
    
    // Get unique grades from skills
    const uniqueGrades = computed(() => {
      const gradeMap = new Map();
      skills.value.forEach(skill => {
        if (skill.grade && !gradeMap.has(skill.grade.id)) {
          gradeMap.set(skill.grade.id, skill.grade);
        }
      });
      return Array.from(gradeMap.values());
    });
    
    // Get unique subjects from skills
    const uniqueSubjects = computed(() => {
      const subjectMap = new Map();
      skills.value.forEach(skill => {
        if (skill.subject && !subjectMap.has(skill.subject.id)) {
          subjectMap.set(skill.subject.id, skill.subject);
        }
      });
      return Array.from(subjectMap.values());
    });
    
    // Filtered categories based on selected grade and subject
    const filteredCategories = computed(() => {
      let result = [...categories.value];
      
      // If grade is selected, filter categories by grade
      if (filters.value.gradeId) {
        result = result.filter(category => 
          category.grade_id ? category.grade_id === parseInt(filters.value.gradeId) : true
        );
      }
      
      // If subject is selected, filter categories by subject
      if (filters.value.subjectId) {
        result = result.filter(category => 
          category.subject_id ? category.subject_id === parseInt(filters.value.subjectId) : true
        );
      }
      
      return result;
    });
    
    // Filtered skills based on all filters
    const filteredSkills = computed(() => {
      let result = [...skills.value];
      
      // Filter by grade
      if (filters.value.gradeId) {
        result = result.filter(skill => 
          skill.grade_id ? skill.grade_id === parseInt(filters.value.gradeId) : false
        );
      }
      
      // Filter by subject
      if (filters.value.subjectId) {
        result = result.filter(skill => 
          skill.subject_id ? skill.subject_id === parseInt(filters.value.subjectId) : false
        );
      }
      
      // Filter by category
      if (filters.value.categoryId) {
        result = result.filter(skill => 
          skill.category_id ? skill.category_id === parseInt(filters.value.categoryId) : false
        );
      }
      
      return result;
    });
    
    // Apply filters and reload data
    const applyFilters = () => {
      loadSkills();
    };
    
    // Reset all filters
    const resetFilters = () => {
      filters.value = {
        gradeId: '',
        subjectId: '',
        categoryId: ''
      };
      loadSkills();
    };
    
    // Retry loading data
    const retryLoadData = () => {
      error.value = null;
      loadData();
    };
    
    // Load all required data with error handling
    const loadData = async () => {
      error.value = null;
      loading.value = true;
      
      try {
        // Load categories first
        await loadCategories();
        
        // Then load skills
        await loadSkills();
        
        // Extract unique grades and subjects from skills data
        if (skills.value.length > 0) {
          const gradeMap = new Map();
          const subjectMap = new Map();
          
          skills.value.forEach(skill => {
            if (skill.grade && !gradeMap.has(skill.grade.id)) {
              gradeMap.set(skill.grade.id, skill.grade);
            }
            if (skill.subject && !subjectMap.has(skill.subject.id)) {
              subjectMap.set(skill.subject.id, skill.subject);
            }
          });
          
          grades.value = Array.from(gradeMap.values());
          subjects.value = Array.from(subjectMap.values());
        }
      } catch (err) {
        console.error('Error loading data:', err);
        error.value = 'Failed to load skills. Please check your connection and try again.';
      } finally {
        loading.value = false;
      }
    };
    
    // Fetch skills with filters
    const loadSkills = async () => {
      try {
        // Build query parameters
        const params = new URLSearchParams();
        if (filters.value.gradeId) params.append('grade_id', filters.value.gradeId);
        if (filters.value.subjectId) params.append('subject_id', filters.value.subjectId);
        if (filters.value.categoryId) params.append('category_id', filters.value.categoryId);
        
        const response = await fetch(`/skill-practice/skills?${params.toString()}`);
        
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.success) {
          skills.value = data.data.map(skill => ({
            ...skill,
            questions_count: skill.questions?.length || 0
          }));
        } else {
          throw new Error(data.message || 'Failed to load skills');
        }
      } catch (err) {
        console.error('Error loading skills:', err);
        throw err;
      }
    };
    
    // Load categories
    const loadCategories = async () => {
      try {
        const response = await fetch('/skill-practice/categories');
        
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.success) {
          categories.value = data.data;
        } else {
          throw new Error(data.message || 'Failed to load categories');
        }
      } catch (err) {
        console.error('Error loading categories:', err);
        throw err;
      }
    };
    
    // Calculate mastery percentage based on smart score
    const calculateMasteryPercentage = (score) => {
      if (score <= 0) return 0;
      if (score >= 100) return 100;
      return Math.min(100, score);
    };
    
    // Get color class based on smart score
    const getScoreColorClass = (score) => {
      if (score < 20) return 'bg-red-500';
      if (score < 50) return 'bg-yellow-500';
      if (score < 80) return 'bg-blue-500';
      if (score < 100) return 'bg-green-500';
      return 'bg-purple-600'; // Master level
    };
    
    // Start practice session for a skill
    const startPractice = (skill) => {
      router.post(`/skill-practice/skills/${skill.id}/start`, { skill_id: skill.id }, {
        onSuccess: (page) => {
          // Navigate to the practice session page
          router.visit(`/skill-practice/session/${page.props.session.id}`);
        },
        onError: (errors) => {
          error.value = 'Failed to start practice session. Please try again.';
        }
      });
    };
    
    onMounted(async () => {
      await loadData();
    });
    
    return {
      skills,
      categories,
      grades,
      subjects,
      loading,
      error,
      filters,
      hasActiveFilters,
      uniqueGrades,
      uniqueSubjects,
      filteredCategories,
      filteredSkills,
      calculateMasteryPercentage,
      getScoreColorClass,
      startPractice,
      resetFilters,
      retryLoadData
    };
  }
};
</script>