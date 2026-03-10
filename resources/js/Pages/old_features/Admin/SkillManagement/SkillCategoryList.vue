<template>
  <div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Skill Categories</h1>
      <button 
        @click="openCreateModal"
        class="bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-4 rounded-md text-sm font-medium"
      >
        Add Category
      </button>
    </div>
    
    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-indigo-500"></div>
    </div>
    
    <!-- Categories Table -->
    <div v-else class="bg-white shadow-md rounded-lg overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Grade</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Skills</th>
            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="category in categories" :key="category.id">
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm font-medium text-gray-900">{{ category.name }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm text-gray-500">{{ category.grade?.name || 'N/A' }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm text-gray-500">{{ category.subject?.name || 'N/A' }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
              {{ category.skills?.length || 0 }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
              <button 
                @click="editCategory(category)"
                class="text-indigo-600 hover:text-indigo-900 mr-3"
              >
                Edit
              </button>
              <button 
                @click="deleteCategory(category)"
                class="text-red-600 hover:text-red-900"
              >
                Delete
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    
    <!-- Create/Edit Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4">
        <h3 class="text-lg font-medium mb-4">{{ formTitle }}</h3>
        
        <form @submit.prevent="saveCategory">
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
            <input 
              v-model="form.name"
              type="text"
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              required
            >
          </div>
          
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Grade</label>
            <select 
              v-model="form.grade_id"
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              required
            >
              <option value="">Select Grade</option>
              <option v-for="grade in grades" :key="grade.id" :value="grade.id">{{ grade.name }}</option>
            </select>
          </div>
          
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
            <select 
              v-model="form.subject_id"
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              required
            >
              <option value="">Select Subject</option>
              <option v-for="subject in subjects" :key="subject.id" :value="subject.id">{{ subject.name }}</option>
            </select>
          </div>
          
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Icon (optional)</label>
            <input 
              v-model="form.icon"
              type="text"
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              placeholder="e.g., fas fa-calculator"
            >
          </div>
          
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Display Order</label>
            <input 
              v-model.number="form.display_order"
              type="number"
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              min="0"
            >
          </div>
          
          <div class="flex justify-end space-x-3 mt-6">
            <button 
              type="button" 
              @click="closeModal"
              class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
            >
              Cancel
            </button>
            <button 
              type="submit"
              class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-indigo-700"
            >
              Save
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';

export default {
  name: 'SkillCategoryList',
  
  setup() {
    const categories = ref([]);
    const grades = ref([]);
    const subjects = ref([]);
    const loading = ref(false);
    const showModal = ref(false);
    const isEditing = ref(false);
    
    const form = ref({
      id: null,
      name: '',
      grade_id: '',
      subject_id: '',
      icon: '',
      display_order: 0
    });
    
    const formTitle = ref('Add Category');
    
    // Load categories
    const loadCategories = async () => {
      loading.value = true;
      try {
        const response = await fetch('/skill-practice/categories');
        const data = await response.json();
        
        if (data.success) {
          categories.value = data.data;
        }
      } catch (error) {
        console.error('Error loading categories:', error);
      } finally {
        loading.value = false;
      }
    };
    
    // Load grades and subjects
    const loadGradesAndSubjects = async () => {
      // In a real implementation, these would come from API endpoints
      // For now, we'll simulate them
      grades.value = [
        { id: 1, name: 'Grade 1' },
        { id: 2, name: 'Grade 2' },
        { id: 3, name: 'Grade 3' },
        { id: 4, name: 'Grade 4' },
        { id: 5, name: 'Grade 5' },
        { id: 6, name: 'Grade 6' },
        { id: 7, name: 'Grade 7' },
        { id: 8, name: 'Grade 8' },
        { id: 9, name: 'Grade 9' },
        { id: 10, name: 'Grade 10' },
        { id: 11, name: 'Grade 11' },
        { id: 12, name: 'Grade 12' }
      ];
      
      subjects.value = [
        { id: 1, name: 'Mathematics' },
        { id: 2, name: 'Science' },
        { id: 3, name: 'English' },
        { id: 4, name: 'History' },
        { id: 5, name: 'Geography' },
        { id: 6, name: 'Computer Science' }
      ];
    };
    
    // Open create modal
    const openCreateModal = () => {
      isEditing.value = false;
      formTitle.value = 'Add Category';
      form.value = {
        id: null,
        name: '',
        grade_id: '',
        subject_id: '',
        icon: '',
        display_order: 0
      };
      showModal.value = true;
    };
    
    // Open edit modal
    const editCategory = (category) => {
      isEditing.value = true;
      formTitle.value = 'Edit Category';
      form.value = { ...category };
      showModal.value = true;
    };
    
    // Save category
    const saveCategory = async () => {
      try {
        let response;
        
        if (isEditing.value) {
          // Update existing category (not implemented in API yet)
          console.log('Update category:', form.value);
          // For now just close the modal and reload
        } else {
          // Create new category (not implemented in API yet)
          console.log('Create category:', form.value);
          // For now just close the modal and reload
        }
        
        closeModal();
        await loadCategories();
      } catch (error) {
        console.error('Error saving category:', error);
      }
    };
    
    // Delete category
    const deleteCategory = (category) => {
      if (confirm(`Are you sure you want to delete the category "${category.name}"?`)) {
        // Implement delete API call here
        console.log('Delete category:', category.id);
        // For now just remove from local array
        categories.value = categories.value.filter(cat => cat.id !== category.id);
      }
    };
    
    // Close modal
    const closeModal = () => {
      showModal.value = false;
    };
    
    onMounted(async () => {
      await Promise.all([
        loadCategories(),
        loadGradesAndSubjects()
      ]);
    });
    
    return {
      categories,
      grades,
      subjects,
      loading,
      showModal,
      form,
      formTitle,
      openCreateModal,
      editCategory,
      saveCategory,
      deleteCategory,
      closeModal
    };
  }
};
</script>