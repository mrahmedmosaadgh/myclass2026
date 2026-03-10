<template>
    <AppLayout title="Teacher Dashboard">ggggg
        <div class=" ">
            <div class=" mx-auto  ">
                <!-- Assignment Cards -->
                <div class="flex flex-wrap justify-center gap-6 mb-6">
                <!-- <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6"> -->
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


            </div>




            <div class="py-12" v-if="students">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    <div v-for="student in students" :key="student.s_id">
                        <card1>
                            <template #default>
                                <div class="flex flex-col space-y-4 p-4">
                                    <div class="space-y-2">
                                        <div class="flex flex-col">
                                            <span class="text-gray-500 text-sm">Student ID:</span>
                                            <span class="font-medium">{{ student.s_id }}</span>
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-gray-500 text-sm">Grade:</span>
                                            <span class="font-medium">Grade {{ student.grade_id }}</span>
                                        </div>
                                    </div>
                                    <button
                                        @click="viewStudentDetails(student)"
                                        class="w-full py-2 px-4 bg-indigo-600 text-white rounded-md
                                               hover:bg-indigo-700 transition duration-200 ease-in-out
                                               text-sm font-medium">
                                        View Profile
                                    </button>
                                </div>
                            </template>

                            <template #extra>
                                <div class="p-3 bg-gradient-to-r from-blue-500 to-indigo-500 text-white">
                                    <span class="text-sm font-medium">
                                        Student Record
                                    </span>
                                </div>
                            </template>

                            <template #cover>
                                <div class="flex flex-col items-center justify-center h-full space-y-4 p-4">
                                    <div class="w-16 h-16 bg-gray-200 rounded-full flex items-center justify-center mb-2">
                                        <span class="text-2xl font-bold text-gray-600">
                                            {{ student.name.charAt(0) }}
                                        </span>
                                    </div>
                                    <h3 class="text-lg font-bold text-center line-clamp-2">
                                        {{ student.name }}
                                    </h3>
                                    <p class="text-sm text-gray-600">
                                        Click to view details
                                    </p>
                                </div>
                            </template>
                        </card1>
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

const loadStudents = async (classroom_id) => {
    if (!selectedClassroom.value || !selectedSubject.value) {
        students.value = [];
        return;
    }
    students.value = [];

    loading.value = true;
    try {
        const response = await axios.post('/api/teacher/students', {

                classroom_id: selectedClassroom.value,
                classroom_id2: classroom_id
            }
         );
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
    loadStudents(assignment.classroom.id);
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



