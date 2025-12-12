# 📖 Basic Usage Guide

Simple examples showing how to use the offline-first system with different education resources.

## 🎯 Quick Start

### Import the Composable

```javascript
import { useOfflineResource } from '@/offline/useOfflineResource';
```

### Basic Pattern

```javascript
// 1. Initialize resource management
const { data, loading, loadAll, create, update, delete: deleteItem } = useOfflineResource('lessons');

// 2. Load data
loadAll().then(lessons => console.log(lessons));

// 3. Create new item
create({ title: 'New Lesson', content: 'Content here' });

// 4. Update existing item
update(123, { title: 'Updated Title' });

// 5. Delete item
deleteItem(123);
```

## 📚 Lesson Management

### Complete Lesson Component

```vue
<template>
  <div class="lesson-manager">
    <!-- Network status -->
    <div v-if="!isOnline" class="bg-orange-100 p-3 rounded mb-4">
      📴 Offline mode - changes will sync when connected
    </div>

    <!-- Loading state -->
    <div v-if="loading" class="text-center py-8">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500 mx-auto"></div>
      <p class="mt-2">Loading lessons...</p>
    </div>

    <!-- Lessons list -->
    <div v-else class="space-y-4">
      <div v-for="lesson in data" :key="lesson.id" class="border rounded-lg p-4">
        <div class="flex justify-between items-start">
          <div class="flex-1">
            <h3 class="text-lg font-semibold">{{ lesson.title }}</h3>
            <p class="text-gray-600 mt-1">{{ lesson.content }}</p>
            <div class="text-sm text-gray-500 mt-2">
              Course ID: {{ lesson.course_id }} | Teacher ID: {{ lesson.teacher_id }}
            </div>
          </div>
          <div class="flex space-x-2 ml-4">
            <button @click="editLesson(lesson)" class="px-3 py-1 bg-blue-500 text-white rounded text-sm">
              Edit
            </button>
            <button @click="deleteLesson(lesson.id)" class="px-3 py-1 bg-red-500 text-white rounded text-sm">
              Delete
            </button>
          </div>
        </div>
        
        <!-- Sync status indicator -->
        <div v-if="lesson.is_dirty" class="mt-2 text-xs text-orange-600">
          📤 Pending sync
        </div>
      </div>

      <!-- Empty state -->
      <div v-if="data.length === 0" class="text-center py-8 text-gray-500">
        No lessons found. Create your first lesson below.
      </div>
    </div>

    <!-- Add new lesson button -->
    <button @click="showForm = true" class="mt-6 px-4 py-2 bg-green-500 text-white rounded">
      Add New Lesson
    </button>

    <!-- Create/Edit form -->
    <div v-if="showForm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
      <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold mb-4">
          {{ editingLesson ? 'Edit Lesson' : 'Create New Lesson' }}
        </h3>
        
        <form @submit.prevent="saveLesson">
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Title</label>
              <input v-model="form.title" type="text" required 
                     class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700">Content</label>
              <textarea v-model="form.content" rows="4" required
                        class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2"></textarea>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700">Course ID</label>
              <input v-model="form.course_id" type="number" required
                     class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700">Teacher ID</label>
              <input v-model="form.teacher_id" type="number" required
                     class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
            </div>
          </div>
          
          <div class="flex justify-end space-x-3 mt-6">
            <button type="button" @click="cancelForm" class="px-4 py-2 border border-gray-300 rounded text-gray-700">
              Cancel
            </button>
            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded">
              {{ editingLesson ? 'Update' : 'Create' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useOfflineResource } from '@/offline/useOfflineResource';

// Initialize offline resource management
const {
  data,
  loading,
  error,
  isOnline,
  loadAll,
  create,
  update,
  delete: deleteItem
} = useOfflineResource('lessons');

// Component state
const showForm = ref(false);
const editingLesson = ref(null);
const form = ref({
  title: '',
  content: '',
  course_id: '',
  teacher_id: ''
});

// Load lessons on mount
onMounted(() => {
  loadAll().catch(err => {
    console.error('Failed to load lessons:', err);
  });
});

// Create or update lesson
function saveLesson() {
  const lessonData = { ...form.value };
  
  if (editingLesson.value) {
    // Update existing lesson
    update(editingLesson.value.id, lessonData)
      .then(() => {
        console.log('Lesson updated successfully');
        cancelForm();
      })
      .catch(err => {
        console.error('Failed to update lesson:', err);
      });
  } else {
    // Create new lesson
    create(lessonData)
      .then(() => {
        console.log('Lesson created successfully');
        cancelForm();
      })
      .catch(err => {
        console.error('Failed to create lesson:', err);
      });
  }
}

// Edit lesson
function editLesson(lesson) {
  editingLesson.value = lesson;
  form.value = {
    title: lesson.title,
    content: lesson.content,
    course_id: lesson.course_id,
    teacher_id: lesson.teacher_id
  };
  showForm.value = true;
}

// Delete lesson
function deleteLesson(id) {
  if (confirm('Are you sure you want to delete this lesson?')) {
    deleteItem(id)
      .then(() => {
        console.log('Lesson deleted successfully');
      })
      .catch(err => {
        console.error('Failed to delete lesson:', err);
      });
  }
}

// Cancel form
function cancelForm() {
  showForm.value = false;
  editingLesson.value = null;
  form.value = {
    title: '',
    content: '',
    course_id: '',
    teacher_id: ''
  };
}
</script>
```

## 👥 Student Management

### Simple Student List

```vue
<template>
  <div class="student-list">
    <h2 class="text-xl font-bold mb-4">Students</h2>
    
    <!-- Students -->
    <div v-for="student in data" :key="student.id" class="border-b py-2">
      <div class="flex justify-between items-center">
        <div>
          <h3 class="font-medium">{{ student.name }}</h3>
          <p class="text-sm text-gray-600">{{ student.email }}</p>
          <p class="text-xs text-gray-500">Class: {{ student.class_id }} | Grade: {{ student.grade_id }}</p>
        </div>
        <button @click="editStudent(student)" class="text-blue-500 text-sm">Edit</button>
      </div>
    </div>
    
    <!-- Add student -->
    <button @click="addStudent" class="mt-4 px-4 py-2 bg-blue-500 text-white rounded">
      Add Student
    </button>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useOfflineResource } from '@/offline/useOfflineResource';

const { data, loadAll, create, update } = useOfflineResource('students');

onMounted(() => {
  loadAll();
});

function addStudent() {
  const studentData = {
    name: 'New Student',
    email: 'student@example.com',
    class_id: 1,
    grade_id: 1
  };
  
  create(studentData)
    .then(student => console.log('Student added:', student))
    .catch(err => console.error('Failed to add student:', err));
}

function editStudent(student) {
  const updatedData = {
    name: student.name + ' (Updated)'
  };
  
  update(student.id, updatedData)
    .then(updated => console.log('Student updated:', updated))
    .catch(err => console.error('Failed to update student:', err));
}
</script>
```

## 📝 Quiz Answer Submission

### Quiz Taking Component

```vue
<template>
  <div class="quiz-component">
    <h2 class="text-xl font-bold mb-4">Quiz: {{ quizTitle }}</h2>
    
    <!-- Offline warning -->
    <div v-if="!isOnline" class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-4">
      ⚠️ You're offline. Your answers will be saved locally and submitted when you're back online.
    </div>
    
    <!-- Quiz questions -->
    <div class="space-y-6">
      <div v-for="(question, index) in questions" :key="index" class="border rounded p-4">
        <h3 class="font-medium mb-3">{{ index + 1 }}. {{ question.text }}</h3>
        
        <div class="space-y-2">
          <label v-for="option in question.options" :key="option.id" class="flex items-center">
            <input v-model="answers[question.id]" :value="option.id" type="radio" class="mr-2">
            {{ option.text }}
          </label>
        </div>
      </div>
    </div>
    
    <!-- Submit button -->
    <button @click="submitQuiz" :disabled="!canSubmit" 
            class="mt-6 px-6 py-2 bg-green-500 text-white rounded disabled:bg-gray-400">
      {{ isOnline ? 'Submit Quiz' : 'Save Answers (Will Submit When Online)' }}
    </button>
    
    <!-- Submission status -->
    <div v-if="submissionStatus" class="mt-4 p-3 rounded" 
         :class="submissionStatus.type === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'">
      {{ submissionStatus.message }}
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useOfflineResource } from '@/offline/useOfflineResource';

const props = defineProps({
  quizId: { type: Number, required: true },
  studentId: { type: Number, required: true },
  quizTitle: { type: String, default: 'Quiz' }
});

// Initialize quiz answers resource with high priority
const { create, isOnline } = useOfflineResource('quiz_answers', {
  syncPriority: 1, // Critical priority for quiz data
  offlineCapabilities: ['create'] // Only allow creating answers
});

// Component state
const questions = ref([
  {
    id: 1,
    text: 'What is 2 + 2?',
    options: [
      { id: 'a', text: '3' },
      { id: 'b', text: '4' },
      { id: 'c', text: '5' }
    ]
  },
  {
    id: 2,
    text: 'What is the capital of France?',
    options: [
      { id: 'a', text: 'London' },
      { id: 'b', text: 'Berlin' },
      { id: 'c', text: 'Paris' }
    ]
  }
]);

const answers = ref({});
const submissionStatus = ref(null);

// Check if quiz can be submitted
const canSubmit = computed(() => {
  return questions.value.every(q => answers.value[q.id]);
});

// Submit quiz answers
function submitQuiz() {
  if (!canSubmit.value) {
    submissionStatus.value = {
      type: 'error',
      message: 'Please answer all questions before submitting.'
    };
    return;
  }

  const quizAnswerData = {
    quiz_id: props.quizId,
    student_id: props.studentId,
    answers: answers.value,
    submitted_at: new Date().toISOString()
  };

  create(quizAnswerData)
    .then(result => {
      submissionStatus.value = {
        type: 'success',
        message: isOnline.value 
          ? 'Quiz submitted successfully!' 
          : 'Answers saved! Will submit when you\'re back online.'
      };
      
      // Clear form
      answers.value = {};
    })
    .catch(err => {
      console.error('Failed to submit quiz:', err);
      submissionStatus.value = {
        type: 'error',
        message: 'Failed to save answers. Please try again.'
      };
    });
}
</script>
```

## 📊 Grade Management

### Simple Grade Entry

```vue
<template>
  <div class="grade-entry">
    <h2 class="text-xl font-bold mb-4">Grade Entry</h2>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <div v-for="grade in data" :key="grade.id" class="border rounded p-4">
        <div class="flex justify-between items-start">
          <div>
            <p class="font-medium">Student ID: {{ grade.student_id }}</p>
            <p class="text-sm text-gray-600">Assignment ID: {{ grade.assignment_id }}</p>
            <p class="text-lg font-bold text-blue-600">Score: {{ grade.score }}</p>
            <p class="text-xs text-gray-500">{{ formatDate(grade.graded_at) }}</p>
          </div>
          <button @click="editGrade(grade)" class="text-blue-500 text-sm">Edit</button>
        </div>
      </div>
    </div>
    
    <!-- Add grade form -->
    <div class="mt-6 border rounded p-4 bg-gray-50">
      <h3 class="font-medium mb-3">Add New Grade</h3>
      <div class="grid grid-cols-2 gap-4">
        <input v-model="newGrade.student_id" type="number" placeholder="Student ID" 
               class="border rounded px-3 py-2">
        <input v-model="newGrade.assignment_id" type="number" placeholder="Assignment ID" 
               class="border rounded px-3 py-2">
        <input v-model="newGrade.score" type="number" placeholder="Score" 
               class="border rounded px-3 py-2">
        <button @click="addGrade" class="bg-green-500 text-white rounded px-4 py-2">
          Add Grade
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useOfflineResource } from '@/offline/useOfflineResource';

const { data, loadAll, create, update } = useOfflineResource('grades');

const newGrade = ref({
  student_id: '',
  assignment_id: '',
  score: ''
});

onMounted(() => {
  loadAll();
});

function addGrade() {
  const gradeData = {
    ...newGrade.value,
    graded_at: new Date().toISOString()
  };
  
  create(gradeData)
    .then(() => {
      newGrade.value = { student_id: '', assignment_id: '', score: '' };
    })
    .catch(err => console.error('Failed to add grade:', err));
}

function editGrade(grade) {
  const newScore = prompt('Enter new score:', grade.score);
  if (newScore !== null) {
    update(grade.id, { score: parseInt(newScore) })
      .catch(err => console.error('Failed to update grade:', err));
  }
}

function formatDate(dateString) {
  return new Date(dateString).toLocaleDateString();
}
</script>
```

## 🔄 Multiple Resources Example

### Dashboard Component

```vue
<template>
  <div class="dashboard">
    <h1 class="text-2xl font-bold mb-6">Education Dashboard</h1>
    
    <!-- Network status -->
    <div v-if="!isOnline" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
      📴 Offline Mode - Data will sync when connection is restored
    </div>
    
    <!-- Stats cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
      <div class="bg-blue-100 rounded-lg p-4">
        <h3 class="text-lg font-semibold text-blue-800">Lessons</h3>
        <p class="text-2xl font-bold text-blue-600">{{ lessons.data.length }}</p>
      </div>
      
      <div class="bg-green-100 rounded-lg p-4">
        <h3 class="text-lg font-semibold text-green-800">Students</h3>
        <p class="text-2xl font-bold text-green-600">{{ students.data.length }}</p>
      </div>
      
      <div class="bg-yellow-100 rounded-lg p-4">
        <h3 class="text-lg font-semibold text-yellow-800">Quiz Answers</h3>
        <p class="text-2xl font-bold text-yellow-600">{{ quizAnswers.data.length }}</p>
      </div>
      
      <div class="bg-purple-100 rounded-lg p-4">
        <h3 class="text-lg font-semibold text-purple-800">Grades</h3>
        <p class="text-2xl font-bold text-purple-600">{{ grades.data.length }}</p>
      </div>
    </div>
    
    <!-- Quick actions -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div class="border rounded-lg p-4">
        <h3 class="text-lg font-semibold mb-3">Recent Lessons</h3>
        <div v-for="lesson in lessons.data.slice(0, 3)" :key="lesson.id" class="mb-2">
          <p class="font-medium">{{ lesson.title }}</p>
          <p class="text-sm text-gray-600">{{ lesson.content.substring(0, 50) }}...</p>
        </div>
      </div>
      
      <div class="border rounded-lg p-4">
        <h3 class="text-lg font-semibold mb-3">Recent Students</h3>
        <div v-for="student in students.data.slice(0, 3)" :key="student.id" class="mb-2">
          <p class="font-medium">{{ student.name }}</p>
          <p class="text-sm text-gray-600">{{ student.email }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useOfflineResource } from '@/offline/useOfflineResource';

// Initialize multiple resources
const lessons = useOfflineResource('lessons');
const students = useOfflineResource('students');
const quizAnswers = useOfflineResource('quiz_answers');
const grades = useOfflineResource('grades');

// Get network status from any resource (they all share the same status)
const { isOnline } = lessons;

// Load all data on mount
onMounted(() => {
  Promise.all([
    lessons.loadAll(),
    students.loadAll(),
    quizAnswers.loadAll(),
    grades.loadAll()
  ]).catch(err => {
    console.error('Failed to load dashboard data:', err);
  });
});
</script>
```

---

**Next:** Check out [Advanced Usage](./advanced-usage.md) for more complex scenarios and optimizations.
