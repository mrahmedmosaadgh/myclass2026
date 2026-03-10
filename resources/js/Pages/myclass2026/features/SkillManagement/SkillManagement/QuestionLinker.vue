<template>
  <div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Link Questions to Skills</h1>
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
      <!-- Skills Panel -->
      <div class="bg-white shadow-md rounded-lg p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Select a Skill</h2>
        
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-1">Filter by Category</label>
          <select 
            v-model="selectedCategoryId"
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            @change="loadSkills"
          >
            <option value="">All Categories</option>
            <option v-for="category in categories" :key="category.id" :value="category.id">
              {{ category.name }}
            </option>
          </select>
        </div>
        
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-1">Select Skill</label>
          <select 
            v-model="selectedSkillId"
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            @change="loadLinkedQuestions"
          >
            <option value="">Choose a skill</option>
            <option v-for="skill in filteredSkills" :key="skill.id" :value="skill.id">
              {{ skill.name }}
            </option>
          </select>
        </div>
        
        <!-- Linked Questions -->
        <div v-if="selectedSkillId" class="mt-6">
          <h3 class="text-md font-medium text-gray-800 mb-3">Currently Linked Questions</h3>
          
          <div v-if="linkedQuestions.length === 0" class="text-center py-4 text-gray-500">
            No questions linked to this skill yet
          </div>
          
          <div v-else class="space-y-2">
            <div 
              v-for="link in linkedQuestions" 
              :key="`${link.skill_id}-${link.qu_question_id}`"
              class="flex justify-between items-center p-3 bg-gray-50 rounded-md"
            >
              <div>
                <div class="text-sm font-medium">{{ link.question.question_text.substring(0, 60) }}{{ link.question.question_text.length > 60 ? '...' : '' }}</div>
                <div class="text-xs text-gray-500">Difficulty: {{ link.pivot.difficulty_level }}/10</div>
              </div>
              <button 
                @click="unlinkQuestion(link)"
                class="text-red-600 hover:text-red-900 text-sm"
              >
                Remove
              </button>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Questions Panel -->
      <div class="bg-white shadow-md rounded-lg p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Available Questions</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Filter by Subject</label>
            <select 
              v-model="questionFilters.subjectId"
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              @change="loadQuestions"
            >
              <option value="">All Subjects</option>
              <option v-for="subject in subjects" :key="subject.id" :value="subject.id">
                {{ subject.name }}
              </option>
            </select>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Filter by Topic</label>
            <select 
              v-model="questionFilters.topicId"
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              @change="loadQuestions"
            >
              <option value="">All Topics</option>
              <option v-for="topic in topics" :key="topic.id" :value="topic.id">
                {{ topic.name }}
              </option>
            </select>
          </div>
        </div>
        
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-1">Search Questions</label>
          <input 
            v-model="questionFilters.search"
            type="text"
            placeholder="Search questions..."
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            @input="loadQuestions"
          >
        </div>
        
        <div v-if="loadingQuestions" class="flex justify-center items-center py-8">
          <div class="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-indigo-500"></div>
        </div>
        
        <div v-else-if="availableQuestions.length === 0" class="text-center py-8 text-gray-500">
          No questions available
        </div>
        
        <div v-else class="space-y-3 max-h-96 overflow-y-auto pr-2">
          <div 
            v-for="question in availableQuestions" 
            :key="question.id"
            class="p-3 border rounded-md hover:bg-gray-50"
          >
            <div class="flex justify-between">
              <div class="flex-1">
                <div class="text-sm font-medium">{{ question.question_text.substring(0, 80) }}{{ question.question_text.length > 80 ? '...' : '' }}</div>
                <div class="text-xs text-gray-500 mt-1">
                  Subject: {{ question.subject?.name || 'N/A' }} | 
                  Topic: {{ question.topic?.name || 'N/A' }}
                </div>
              </div>
              
              <div v-if="selectedSkillId">
                <button 
                  @click="openLinkModal(question)"
                  class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs py-1 px-3 rounded"
                >
                  Link to Skill
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Link Question Modal -->
    <div v-if="showLinkModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4">
        <h3 class="text-lg font-medium mb-4">Link Question to Skill</h3>
        
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-1">Question Difficulty Level</label>
          <input 
            v-model.number="linkForm.difficultyLevel"
            type="range" 
            min="1" 
            max="10" 
            class="w-full"
          >
          <div class="flex justify-between text-xs text-gray-500">
            <span>Easy</span>
            <span>Medium</span>
            <span>Hard</span>
          </div>
          <div class="text-center mt-1">{{ linkForm.difficultyLevel }}/10</div>
        </div>
        
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-1">Explanation (Optional)</label>
          <textarea 
            v-model="linkForm.explanation"
            rows="3"
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            placeholder="Provide an explanation for incorrect answers..."
          ></textarea>
        </div>
        
        <div class="flex justify-end space-x-3 mt-6">
          <button 
            type="button" 
            @click="closeLinkModal"
            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
          >
            Cancel
          </button>
          <button 
            @click="linkQuestion"
            class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-indigo-700"
          >
            Link Question
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, computed } from 'vue';

export default {
  name: 'QuestionLinker',
  
  setup() {
    const categories = ref([]);
    const skills = ref([]);
    const subjects = ref([]);
    const topics = ref([]);
    const availableQuestions = ref([]);
    const linkedQuestions = ref([]);
    
    const selectedCategoryId = ref('');
    const selectedSkillId = ref('');
    
    const questionFilters = ref({
      subjectId: '',
      topicId: '',
      search: ''
    });
    
    const loadingQuestions = ref(false);
    const showLinkModal = ref(false);
    
    const linkForm = ref({
      questionId: null,
      difficultyLevel: 5,
      explanation: ''
    });
    
    // Filter skills based on selected category
    const filteredSkills = computed(() => {
      if (!selectedCategoryId.value) {
        return skills.value;
      }
      
      return skills.value.filter(skill => 
        skill.category_id == selectedCategoryId.value
      );
    });
    
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
    
    // Load skills
    const loadSkills = async () => {
      try {
        let url = '/skill-practice/skills';
        const params = new URLSearchParams();
        
        if (selectedCategoryId.value) {
          params.append('category_id', selectedCategoryId.value);
        }
        
        if (params.toString()) {
          url += '?' + params.toString();
        }
        
        const response = await fetch(url);
        const data = await response.json();
        
        if (data.success) {
          skills.value = data.data;
        }
      } catch (error) {
        console.error('Error loading skills:', error);
      }
    };
    
    // Load subjects and topics
    const loadSubjectsAndTopics = async () => {
      // Mock data - in a real implementation, these would come from API
      subjects.value = [
        { id: 1, name: 'Mathematics' },
        { id: 2, name: 'Science' },
        { id: 3, name: 'English' },
        { id: 4, name: 'History' }
      ];
      
      topics.value = [
        { id: 1, name: 'Algebra' },
        { id: 2, name: 'Geometry' },
        { id: 3, name: 'Physics' },
        { id: 4, name: 'Chemistry' }
      ];
    };
    
    // Load available questions
    const loadQuestions = async () => {
      if (!selectedSkillId.value) return;
      
      loadingQuestions.value = true;
      try {
        // In a real implementation, this would fetch from the question bank API
        // For now, we'll simulate some data
        availableQuestions.value = [
          { 
            id: 1, 
            question_text: 'What is the formula for the area of a circle?', 
            subject: { name: 'Mathematics' },
            topic: { name: 'Geometry' }
          },
          { 
            id: 2, 
            question_text: 'Which planet is known as the Red Planet?', 
            subject: { name: 'Science' },
            topic: { name: 'Astronomy' }
          },
          { 
            id: 3, 
            question_text: 'What is the past tense of the verb "go"?', 
            subject: { name: 'English' },
            topic: { name: 'Grammar' }
          }
        ].filter(q => 
          (!questionFilters.value.subjectId || q.subject.id == questionFilters.value.subjectId) &&
          (!questionFilters.value.topicId || q.topic.id == questionFilters.value.topicId) &&
          (!questionFilters.value.search || q.question_text.toLowerCase().includes(questionFilters.value.search.toLowerCase()))
        );
      } catch (error) {
        console.error('Error loading questions:', error);
      } finally {
        loadingQuestions.value = false;
      }
    };
    
    // Load linked questions for a skill
    const loadLinkedQuestions = async () => {
      if (!selectedSkillId.value) return;
      
      try {
        // In a real implementation, this would fetch from the skill questions API
        // For now, we'll simulate some data
        linkedQuestions.value = [
          { 
            skill_id: selectedSkillId.value, 
            qu_question_id: 101, 
            pivot: { difficulty_level: 6, explanation: 'The radius is squared and multiplied by pi.' },
            question: { question_text: 'Calculate the area of a circle with radius 5cm' }
          },
          { 
            skill_id: selectedSkillId.value, 
            qu_question_id: 102, 
            pivot: { difficulty_level: 8, explanation: 'Use the quadratic formula to solve.' },
            question: { question_text: 'Solve the equation x² - 5x + 6 = 0' }
          }
        ];
      } catch (error) {
        console.error('Error loading linked questions:', error);
      }
    };
    
    // Open link modal
    const openLinkModal = (question) => {
      linkForm.value = {
        questionId: question.id,
        difficultyLevel: 5,
        explanation: ''
      };
      showLinkModal.value = true;
    };
    
    // Close link modal
    const closeLinkModal = () => {
      showLinkModal.value = false;
    };
    
    // Link question to skill
    const linkQuestion = async () => {
      if (!selectedSkillId.value || !linkForm.value.questionId) return;
      
      try {
        // In a real implementation, this would make an API call to link the question
        console.log('Linking question to skill:', {
          skill_id: selectedSkillId.value,
          qu_question_id: linkForm.value.questionId,
          difficulty_level: linkForm.value.difficultyLevel,
          explanation: linkForm.value.explanation
        });
        
        // Add to linked questions
        linkedQuestions.value.push({
          skill_id: selectedSkillId.value,
          qu_question_id: linkForm.value.questionId,
          pivot: {
            difficulty_level: linkForm.value.difficultyLevel,
            explanation: linkForm.value.explanation
          },
          question: {
            question_text: 'Sample question text...'
          }
        });
        
        closeLinkModal();
      } catch (error) {
        console.error('Error linking question:', error);
      }
    };
    
    // Unlink question from skill
    const unlinkQuestion = async (link) => {
      if (!confirm('Are you sure you want to unlink this question?')) return;
      
      try {
        // In a real implementation, this would make an API call to unlink the question
        console.log('Unlinking question:', link);
        
        // Remove from linked questions
        const index = linkedQuestions.value.findIndex(
          l => l.qu_question_id === link.qu_question_id
        );
        
        if (index !== -1) {
          linkedQuestions.value.splice(index, 1);
        }
      } catch (error) {
        console.error('Error unlinking question:', error);
      }
    };
    
    onMounted(async () => {
      await Promise.all([
        loadCategories(),
        loadSkills(), // This loads ALL skills if no category selected initially? No wait, loadSkills uses selectedCategoryId.
        loadSubjectsAndTopics()
      ]);

      // Check for query parameters to pre-select
      const urlParams = new URLSearchParams(window.location.search);
      const skillId = urlParams.get('skill_id');
      const categoryId = urlParams.get('category_id');

      if (categoryId) {
        selectedCategoryId.value = categoryId;
        // We need to reload skills filtered by this category
        await loadSkills();
      }

      if (skillId) {
        selectedSkillId.value = skillId;
        // Load linked questions for this skill
        await loadLinkedQuestions();
      }
    });
    
    return {
      categories,
      skills,
      subjects,
      topics,
      availableQuestions,
      linkedQuestions,
      selectedCategoryId,
      selectedSkillId,
      questionFilters,
      loadingQuestions,
      showLinkModal,
      linkForm,
      filteredSkills,
      loadSkills,
      loadQuestions,
      loadLinkedQuestions,
      openLinkModal,
      closeLinkModal,
      linkQuestion,
      unlinkQuestion
    };
  }
};
</script>