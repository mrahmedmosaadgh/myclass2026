<template>
  <div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Manage Skills</h1>
    
    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-indigo-500"></div>
    </div>
    
    <div v-else class="bg-white shadow-md rounded-lg overflow-hidden">
      <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Skill Category</label>
            <select 
              v-model="selectedCategoryId"
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              @change="filterSkills"
            >
              <option value="">All Categories</option>
              <option v-for="category in categories" :key="category.id" :value="category.id">
                {{ category.name }}
              </option>
            </select>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Search Skills</label>
            <input 
              v-model="searchQuery"
              type="text"
              placeholder="Search skills..."
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              @input="filterSkills"
            >
          </div>
        </div>
        
        <button 
          @click="openCreateModal"
          class="bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-4 rounded-md text-sm font-medium mb-4"
        >
          Add Skill
        </button>
        
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Questions</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Active</th>
                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="skill in filteredSkills" :key="skill.id">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">{{ skill.name }}</div>
                  <div class="text-sm text-gray-500">{{ skill.description }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-500">{{ skill.category?.name || 'N/A' }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ skill.questions?.length || 0 }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span 
                    v-if="skill.is_active" 
                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800"
                  >
                    Active
                  </span>
                  <span 
                    v-else 
                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800"
                  >
                    Inactive
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <a 
                    :href="`/admin/skills/manage-questions?category_id=${skill.category_id}&skill_id=${skill.id}`"
                    class="text-blue-600 hover:text-blue-900 mr-3"
                  >
                    Link Questions
                  </a>
                  <button 
                    @click="editSkill(skill)"
                    class="text-indigo-600 hover:text-indigo-900 mr-3"
                  >
                    Edit
                  </button>
                  <button 
                    @click="toggleSkillStatus(skill)"
                    class="mr-3"
                    :class="skill.is_active ? 'text-red-600 hover:text-red-900' : 'text-green-600 hover:text-green-900'"
                  >
                    {{ skill.is_active ? 'Deactivate' : 'Activate' }}
                  </button>
                  <button 
                    @click="deleteSkill(skill)"
                    class="text-red-600 hover:text-red-900"
                  >
                    Delete
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    
    <!-- Create/Edit Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
        <h3 class="text-lg font-medium mb-4">{{ formTitle }}</h3>
        
        <form @submit.prevent="saveSkill">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
              <input 
                v-model="form.name"
                type="text"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                required
              >
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
              <select 
                v-model="form.category_id"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                required
              >
                <option value="">Select Category</option>
                <option v-for="category in categories" :key="category.id" :value="category.id">
                  {{ category.name }}
                </option>
              </select>
            </div>
          </div>
          
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea 
              v-model="form.description"
              rows="3"
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              placeholder="Describe the skill..."
            ></textarea>
          </div>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Min Difficulty</label>
              <input 
                v-model.number="form.difficulty_min"
                type="number"
                min="1"
                max="10"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              >
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Max Difficulty</label>
              <input 
                v-model.number="form.difficulty_max"
                type="number"
                min="1"
                max="10"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              >
            </div>
          </div>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Mastery Threshold</label>
              <input 
                v-model.number="form.mastery_threshold"
                type="number"
                min="0"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              >
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Estimated Time (minutes)</label>
              <input 
                v-model.number="form.estimated_time_minutes"
                type="number"
                min="0"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              >
            </div>
          </div>
          
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Is Active</label>
            <div class="flex items-center">
              <input 
                v-model="form.is_active"
                type="checkbox"
                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
              >
              <span class="ml-2 text-sm text-gray-700">Enable this skill for practice</span>
            </div>
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
import { ref, onMounted, computed } from 'vue';

export default {
  name: 'SkillEditor',
  
  setup() {
    const skills = ref([]);
    const categories = ref([]);
    const loading = ref(false);
    const showModal = ref(false);
    const isEditing = ref(false);
    
    const selectedCategoryId = ref('');
    const searchQuery = ref('');
    
    const form = ref({
      id: null,
      name: '',
      category_id: '',
      description: '',
      difficulty_min: 1,
      difficulty_max: 10,
      mastery_threshold: 80,
      estimated_time_minutes: 10,
      is_active: true
    });
    
    const formTitle = ref('Add Skill');
    
    // Load skills
    const loadSkills = async () => {
      loading.value = true;
      try {
        const response = await fetch('/skill-practice/skills');
        const data = await response.json();
        
        if (data.success) {
          skills.value = data.data;
        }
      } catch (error) {
        console.error('Error loading skills:', error);
      } finally {
        loading.value = false;
      }
    };
    
    // Load categories
    const loadCategories = async () => {
      try {
        const response = await fetch('/skill-practice/categories');
        const data = await response.json();
        
        if (data.success) {
          categories.value = data.data;
        }
      } catch (error) {
        console.error('Error loading categories:', error);
      }
    };
    
    // Filter skills based on category and search
    const filteredSkills = computed(() => {
      let result = skills.value;
      
      if (selectedCategoryId.value) {
        result = result.filter(skill => skill.category_id == selectedCategoryId.value);
      }
      
      if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        result = result.filter(skill => 
          skill.name.toLowerCase().includes(query) || 
          skill.description.toLowerCase().includes(query)
        );
      }
      
      return result;
    });
    
    // Open create modal
    const openCreateModal = () => {
      isEditing.value = false;
      formTitle.value = 'Add Skill';
      form.value = {
        id: null,
        name: '',
        category_id: '',
        description: '',
        difficulty_min: 1,
        difficulty_max: 10,
        mastery_threshold: 80,
        estimated_time_minutes: 10,
        is_active: true
      };
      showModal.value = true;
    };
    
    // Open edit modal
    const editSkill = (skill) => {
      isEditing.value = true;
      formTitle.value = 'Edit Skill';
      form.value = { ...skill };
      showModal.value = true;
    };
    
    // Save skill
    const saveSkill = async () => {
      try {
        let response;
        
        if (isEditing.value) {
          // Update existing skill (not implemented in API yet)
          console.log('Update skill:', form.value);
        } else {
          // Create new skill (not implemented in API yet)
          console.log('Create skill:', form.value);
        }
        
        closeModal();
        await loadSkills(); // Reload skills after saving
      } catch (error) {
        console.error('Error saving skill:', error);
      }
    };
    
    // Toggle skill status
    const toggleSkillStatus = (skill) => {
      const updatedSkill = { ...skill, is_active: !skill.is_active };
      // API call to update skill status would go here
      console.log('Toggle skill status:', updatedSkill);
      
      // Update locally for immediate feedback
      const index = skills.value.findIndex(s => s.id === skill.id);
      if (index !== -1) {
        skills.value[index].is_active = updatedSkill.is_active;
      }
    };
    
    // Delete skill
    const deleteSkill = (skill) => {
      if (confirm(`Are you sure you want to delete the skill "${skill.name}"?`)) {
        // API call to delete skill would go here
        console.log('Delete skill:', skill.id);
        
        // Remove from local array for immediate feedback
        skills.value = skills.value.filter(s => s.id !== skill.id);
      }
    };
    
    // Close modal
    const closeModal = () => {
      showModal.value = false;
    };
    
    // Filter skills when category or search changes
    const filterSkills = () => {
      // Computed property handles filtering
    };
    
    onMounted(async () => {
      await Promise.all([
        loadSkills(),
        loadCategories()
      ]);
    });
    
    return {
      skills,
      categories,
      loading,
      showModal,
      selectedCategoryId,
      searchQuery,
      form,
      formTitle,
      filteredSkills,
      openCreateModal,
      editSkill,
      saveSkill,
      toggleSkillStatus,
      deleteSkill,
      closeModal,
      filterSkills
    };
  }
};
</script>