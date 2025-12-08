<template>
    <div class="flex space-x-4">
        <div class="w-48">
            <select
                v-model="selectedSchool"
                @change="handleSchoolChange"
                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
            >
                <option value="">Select School</option>
                <option v-for="school in schools" :key="school.id" :value="school.id">
                    {{ school.name }}
                </option>
            </select>
        </div>

        <div class="w-48">
            <select
                v-model="selectedStage"
                @change="handleStageChange"
                :disabled="!selectedSchool"
                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
            >
                <option value="">Select Stage</option>
                <option v-for="stage in stages" :key="stage.id" :value="stage.id">
                    {{ stage.name }}
                </option>
            </select>
        </div>

        <div class="w-48">
            <select
                v-model="selectedGrade"
                @change="handleGradeChange"
                :disabled="!selectedStage"
                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
            >
                <option value="">Select Grade</option>
                <option v-for="grade in grades" :key="grade.id" :value="grade.id">
                    {{ grade.name }}
                </option>
            </select>
        </div>

        <div class="w-48">
            <select
                v-model="selectedClassroom"
                @change="handleClassroomChange"
                :disabled="!selectedGrade"
                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
            >
                <option value="">Select Classroom</option>
                <option v-for="classroom in classrooms" :key="classroom.id" :value="classroom.id">
                    {{ classroom.name }}
                </option>
            </select>
        </div>

        <PrimaryButton
            @click="applyFilters"
            :disabled="!canApplyFilters"
            class="h-full"
        >
            Apply Filters
        </PrimaryButton>
    </div>
</template>

<script setup>
import { ref  } from 'vue';
import axios from 'axios';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    schools: {
        type: Array,
        required: true,
        default: () => []
    }
});

const emit = defineEmits(['filter-applied']);

const stages = ref([]);
const grades = ref([]);
const classrooms = ref([]);
const selectedSchool = ref('');
const selectedStage = ref('');
const selectedGrade = ref('');
const selectedClassroom = ref('');
const canApplyFilters = ref(false);

const handleSchoolChange = () => {
    selectedStage.value = '';
    selectedGrade.value = '';
    selectedClassroom.value = '';
    stages.value = [];
    grades.value = [];
    classrooms.value = [];
    canApplyFilters.value = false;

    if (selectedSchool.value) {
        loadStages(selectedSchool.value);
    }
};

const handleStageChange = async () => {
    selectedGrade.value = '';
    selectedClassroom.value = '';
    grades.value = [];
    classrooms.value = [];
    canApplyFilters.value = false;

    if (selectedStage.value) {
        await loadGrades(selectedStage.value);
    }
};

const handleGradeChange = async () => {
    selectedClassroom.value = '';
    classrooms.value = [];
    canApplyFilters.value = false;

    if (selectedGrade.value) {
        await loadClassrooms(selectedGrade.value);
    }
};

const handleClassroomChange = () => {
    canApplyFilters.value = Boolean(selectedClassroom.value);
};

const loadStages = (schoolId) => {
    axios.get(`/admin/stages/by-school/${schoolId}`)
        .then(response => {
            stages.value = response.data;
        })
        .catch(error => {
            console.error('Error loading stages:', error);
            stages.value = [];
            if (error.response?.data?.message) {
                alert(error.response.data.message);
            } else {
                alert('Failed to load stages');
            }
        });
};

const loadGrades = async (stageId) => {
    try {
        const response = await axios.get(`/admin/grades/by-stage/${stageId}`);
        grades.value = response.data;
    } catch (error) {
        console.error('Error loading grades:', error);
        grades.value = [];
    }
};

const loadClassrooms = async (gradeId) => {
    try {
        const response = await axios.get(`/admin/classrooms/by-grade/${gradeId}`);
        classrooms.value = response.data;
    } catch (error) {
        console.error('Error loading classrooms:', error);
        classrooms.value = [];
    }
};

const applyFilters = () => {
    if (!selectedSchool.value) {
        alert('Please select a school first');
        return;
    }

    const filters = {
        school_id: selectedSchool.value,
        stage_id: selectedStage.value,
        grade_id: selectedGrade.value,
        classroom_id: selectedClassroom.value
    };

    emit('filter-applied', filters);
};
</script>

