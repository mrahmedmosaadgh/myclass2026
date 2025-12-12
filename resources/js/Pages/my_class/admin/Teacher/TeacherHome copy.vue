<template>
    <AppLayout title="Teacher Dashboard">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Assignment Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                    <div v-for="assignment in $page.props.assignments" :key="assignment.id">
                        <card1>
                            <template #default>
                                <div class="flex flex-col space-y-4 p-4">
                                    <div class="space-y-2">
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-500">Classes per week:</span>
                                            <span class="font-medium">{{ assignment.classes_per_week }}</span>
                                        </div>
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-500">Total Students:</span>
                                            <span class="font-medium">{{ assignment.classroom.capacity || 'N/A' }}</span>
                                        </div>
                                    </div>
                                    <button @click="selectAssignment(assignment)"
                                        class="w-full py-2 px-4 bg-indigo-600 text-white rounded-md hover:bg-indigo-700
                                               transition duration-200 ease-in-out text-sm font-medium">
                                        View Details
                                    </button>
                                </div>
                            </template>

                            <template #extra>
                                <div class="p-3 bg-gradient-to-r from-indigo-500 to-purple-500 text-white">
                                    <span class="text-sm font-medium">
                                        {{ assignment.classes_per_week }} classes/week
                                    </span>
                                </div>
                            </template>

                            <template #cover>
                                <div class="flex flex-col items-center justify-center h-full space-y-4 p-4">
                                    <span class="px-4 py-2 rounded-full text-sm font-semibold"
                                        :class="{
                                            'bg-blue-100 text-blue-800': assignment.subject.name === 'Math',
                                            'bg-green-100 text-green-800': assignment.subject.name === 'Science',
                                            'bg-purple-100 text-purple-800': assignment.subject.name === 'English',
                                            'bg-yellow-100 text-yellow-800': true
                                        }">
                                        {{ assignment.subject.name }}
                                    </span>
                                    <h3 class="text-xl font-bold text-center">
                                        {{ assignment.classroom.name }}
                                    </h3>
                                    <p class="text-sm text-gray-600">
                                        Click to view details
                                    </p>
                                </div>
                            </template>
                        </card1>
                    </div>
                </div>

                <!-- Existing Filters and Table -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <!-- Teacher Info Summary -->

                    <!-- <pre>
                        {{ $page?.props?.classrooms  }}

                    </pre> -->
                    <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                        <h2 class="text-xl font-semibold mb-2">My Classes</h2>

                        <!-- Filters -->
                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Classroom
                                </label>
                                <select
                                    v-model="selectedClassroom"
                                    @change="handleClassroomChange"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="">Select Classroom</option>
                                    <option
                                        v-for="classroom in $page?.props?.classrooms"
                                        :key="classroom.id"
                                        :value="classroom.id"
                                    >
                                        {{ classroom.name }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Subject
                                </label>
                                <select
                                    v-model="selectedSubject"
                                    @change="loadStudents"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="">Select Subject</option>
                                    <option
                                        v-for="subject in $page?.props?.subjects"
                                        :key="subject.id"
                                        :value="subject.id"
                                    >
                                        {{ subject.name }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- Students Table -->
                        <div v-if="students.length > 0" class="mt-6">
                            <h3 class="text-lg font-medium mb-4">Students List</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Name
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Arabic Name
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                ID
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="student in students" :key="student.id">
                                            <td class="px-6 py-4 whitespace-nowrap">{{ student.name }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">{{ student.name_ar }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">{{ student.student_id }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div v-else-if="selectedClassroom && selectedSubject" class="mt-6 text-center text-gray-500">
                            No students found for the selected classroom and subject.
                        </div>
                    </div>
                </div>
            </div>

            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Assignment Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                    <div v-for="assignment in $page.props.assignments" :key="assignment.id"
                        class="bg-white overflow-hidden shadow-sm rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">
                                    {{ assignment.classroom.name }}
                                  cff  {{ assignment.classroom  }}
                                </h3>
                                <span class="px-3 py-1 text-sm rounded-full"
                                    :class="{'bg-blue-100 text-blue-800': assignment.subject.name === 'Math',
                                            'bg-green-100 text-green-800': assignment.subject.name === 'Science',
                                            'bg-purple-100 text-purple-800': assignment.subject.name === 'English',
                                            'bg-yellow-100 text-yellow-800': true }">
                                    {{ assignment.subject.name }}
                                </span>
                            </div>
                            <div class="space-y-2 pl-2 bg-green-300">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500">Classes per week:</span>
                                    <span class="font-medium">{{ assignment.classes_per_week }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500">Total Students:</span>
                                    <span class="font-medium">{{ assignment.classroom.capacity || 'N/A' }}</span>
                                </div>
                            </div>
                            <div class="mt-4 flex justify-end">
                              hhh  <button @click="selectAssignment(assignment)"
                                    class="text-sm text-indigo-600 hover:text-indigo-900">
                                    View Details →
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Existing Filters and Table -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <!-- Teacher Info Summary -->

                    <!-- <pre>
                        {{ $page?.props?.classrooms  }}

                    </pre> -->
                    <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                        <h2 class="text-xl font-semibold mb-2">My Classes</h2>

                        <!-- Filters -->
                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Classroom
                                </label>
                                <select
                                    v-model="selectedClassroom"
                                    @change="handleClassroomChange"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="">Select Classroom</option>
                                    <option
                                        v-for="classroom in $page?.props?.classrooms"
                                        :key="classroom.id"
                                        :value="classroom.id"
                                    >
                                        {{ classroom.name }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Subject
                                </label>
                                <select
                                    v-model="selectedSubject"
                                    @change="loadStudents"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="">Select Subject</option>
                                    <option
                                        v-for="subject in $page?.props?.subjects"
                                        :key="subject.id"
                                        :value="subject.id"
                                    >
                                        {{ subject.name }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- Students Table -->
                        <div v-if="students.length > 0" class="mt-6">
                            <h3 class="text-lg font-medium mb-4">Students List</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Name
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Arabic Name
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                ID
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="student in students" :key="student.id">
                                            <td class="px-6 py-4 whitespace-nowrap">{{ student.name }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">{{ student.name_ar }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">{{ student.student_id }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div v-else-if="selectedClassroom && selectedSubject" class="mt-6 text-center text-gray-500">
                            No students found for the selected classroom and subject.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import axios from 'axios';
import { usePage } from '@inertiajs/vue3';
import card1 from './card1.vue';

// Data
const classrooms = ref([]);
const subjects = ref([]);
const students = ref([]);
const selectedClassroom = ref('');
const selectedSubject = ref('');
const loading = ref(false);

// Get CSRF token from Inertia page props
const token = usePage().props.csrf_token;

// Configure axios defaults
// axios.defaults.withCredentials = true;
// axios.defaults.headers.common['X-CSRF-TOKEN'] = token;

// Computed
const filteredSubjects = computed(() => {
    if (!selectedClassroom.value) return [];
    return subjects.value.filter(subject =>
        subject.classroom_id === selectedClassroom.value
    );
});

// Methods
const loadTeacherData = async () => {
    try {
        const response = await axios.get('/api/teacher/classes');
        const { data } = response;
        classrooms.value = data.classrooms;
        subjects.value = data.subjects;
    } catch (error) {
        console.error('Error loading teacher data:', error);
        if (error.response?.status === 401) {
            window.location.href = '/login';
        }
    }
};

const handleClassroomChange = () => {
    selectedSubject.value = '';
    students.value = [];
};

const loadStudents = async () => {
    if (!selectedClassroom.value || !selectedSubject.value) {
        students.value = [];
        return;
    }

    loading.value = true;
    try {
        const response = await axios.get('/api/teacher/students', {
            params: {
                classroom_id: selectedClassroom.value,
                subject_id: selectedSubject.value
            }
        });
        students.value = response.data.students;
    } catch (error) {
        console.error('Error loading students:', error);
        students.value = [];
    } finally {
        loading.value = false;
    }
};

const selectAssignment = (assignment) => {
    selectedClassroom.value = assignment.classroom.id;
    selectedSubject.value = assignment.subject.id;
    loadStudents();
};

// Lifecycle hooks
// onMounted(() => {
//     loadTeacherData();
// });
</script>

<style scoped>
.grid {
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
}
</style>



