<template>
    <Head title="Students" />
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <div v-if="$page.props.flash?.success"
                         class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        {{ $page.props.flash.success }}
                    </div>

                    <div class="flex justify-between items-center mb-6">
                        <div class="flex space-x-2">
                            <PrimaryButton @click="openModal()">
                                Add New Student
                            </PrimaryButton>
                            <div class="mb-6">
                                <SecondaryButton @click="showColumnManager = !showColumnManager">
                                    Manage Import Columns
                                </SecondaryButton>

                                <div   class="mt-4">
                                    <ColumnManager

                                        :show="showColumnManager"
                                        v-model:columns="importColumns"
                                        @close="showColumnManager = false"
                                    />
                                </div>
                            </div>
                            <ImportExcel
                                @imported="refreshData"
                                :validate-url="baseUrl + '/validate-import'"
                                :import-url="baseUrl + '/import'"
                                :undo-url="baseUrl + '/undo-import'"
                                :columns="importColumns"
                                button-text="Import Students"
                                preview-title="Preview Student Data"
                            />
                            <SecondaryButton @click="handleExport('excel')">
                                Export Excel
                            </SecondaryButton>
                            <SecondaryButton @click="handleExport('csv')">
                                Export CSV
                            </SecondaryButton>
                        </div>
                    </div>
<button @click="first()">first</button>
                    <StudentFilters
                        :schools="localSchools"
                        @filter-applied="handleFilterApplied"
                    />

                    <DataTable
                        :columns="tableColumns"
                        :items="items"
                        :loading="false"
                        @edit="openModal"
                        @delete="deleteRecord"
                    />

                    <Pagination
                        v-if="pagination"
                        :links="pagination"
                    />
                </div>
            </div>
        </div>

        <FormModal
            :show="showModal"
            :editing="editingData"
            :fields="formFields"
            title="Student"
            @close="closeModal"
            @submitted="handleSubmit"
        >
            <template #default>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Name Fields -->
                    <div>
                        <InputLabel for="name" value="Name" required />
                        <TextInput
                            id="name"
                            v-model="form.name"
                            type="text"
                            class="mt-1 block w-full"
                            :error="formErrors.name"
                        />
                        <InputError :message="formErrors.name" class="mt-1" />
                    </div>

                    <div>
                        <InputLabel for="name_ar" value="Arabic Name" />
                        <TextInput
                            id="name_ar"
                            v-model="form.name_ar"
                            type="text"
                            class="mt-1 block w-full"
                            :error="formErrors.name_ar"
                        />
                        <InputError :message="formErrors.name_ar" class="mt-1" />
                    </div>

                    <div>
                        <InputLabel for="name_cute" value="Nickname" />
                        <TextInput
                            id="name_cute"
                            v-model="form.name_cute"
                            type="text"
                            class="mt-1 block w-full"
                            :error="formErrors.name_cute"
                        />
                        <InputError :message="formErrors.name_cute" class="mt-1" />
                    </div>

                    <!-- School Selection -->
                    <div>
                        <InputLabel for="school_id" value="School" required />
                        <select
                            id="school_id"
                            v-model="form.school_id"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                            :class="{ 'border-red-500': formErrors.school_id }"
                        >
                            <option value="">Select School</option>
                            <option v-for="school in localSchools" :key="school.id" :value="school.id">
                                {{ school.name }}
                            </option>
                        </select>
                        <InputError :message="formErrors.school_id" class="mt-1" />
                    </div>

                    <!-- Stage Selection -->
                    <div>
                        <InputLabel for="stage_id" value="Stage" required />
                        <select
                            id="stage_id"
                            v-model="form.stage_id"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                            :class="{ 'border-red-500': formErrors.stage_id }"
                        >
                            <option value="">Select Stage</option>
                            <option v-for="stage in stages" :key="stage.id" :value="stage.id">
                                {{ stage.name }}
                            </option>
                        </select>
                        <InputError :message="formErrors.stage_id" class="mt-1" />
                    </div>

                    <!-- Grade Selection -->
                    <div>
                        <InputLabel for="grade_id" value="Grade" required />
                        <select
                            id="grade_id"
                            v-model="form.grade_id"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                            :class="{ 'border-red-500': formErrors.grade_id }"
                            :disabled="!form.stage_id"
                        >
                            <option value="">Select Grade</option>
                            <option v-for="grade in grades" :key="grade.id" :value="grade.id">
                                {{ grade.name }}
                            </option>
                        </select>
                        <InputError :message="formErrors.grade_id" class="mt-1" />
                    </div>

                    <!-- Classroom Selection -->
                    <div>
                        <InputLabel for="classroom_id" value="Classroom" required />
                        <select
                            id="classroom_id"
                            v-model="form.classroom_id"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                            :class="{ 'border-red-500': formErrors.classroom_id }"
                            :disabled="!form.grade_id"
                        >
                            <option value="">Select Classroom</option>
                            <option v-for="classroom in classrooms" :key="classroom.id" :value="classroom.id">
                                {{ classroom.name }}
                            </option>
                        </select>
                        <InputError :message="formErrors.classroom_id" class="mt-1" />
                    </div>

                    <!-- Notes -->
                    <div class="col-span-full">
                        <InputLabel for="notes" value="Notes" />
                        <textarea
                            id="notes"
                            v-model="form.notes"
                            rows="3"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                            :class="{ 'border-red-500': formErrors.notes }"
                        ></textarea>
                        <InputError :message="formErrors.notes" class="mt-1" />
                    </div>
                </div>
            </template>
        </FormModal>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { router, Head } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Pagination from '@/Components/Pagination.vue';
import DataTable from './Common/DataTable.vue';
import FormModal from './Common/FormModal.vue';
import ImportExcel from './Common/ImportExcel.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import * as XLSX from 'xlsx';
import StudentFilters from '@/Components/Students/StudentFilters.vue';
import ColumnManager from './Common/ColumnManager.vue';
import { exportToExcel as exportData } from '@/Utils/exportHelper';
import { toast } from 'vue3-toastify';
import { handleAxiosError } from '@/Utils/errorHandler';

const formErrors = ref({});
const submitting = ref(false);

const showColumnManager = ref(false);
const importColumns = ref([
    { key: 's_id', label: 'ID', required: false, is_id: true },
    { key: 'name', label: 'Name', required: true },
    { key: 'name_ar', label: 'Arabic Name', required: true },
    { key: 'school', label: 'School', required: true },
    { key: 'classroom', label: 'Classroom', required: true },
    { key: 'grade', label: 'Grade', required: true },
    { key: 'stage', label: 'Stage', required: true }
]);

// Optional: Save column order to localStorage
watch(importColumns, (newColumns) => {
    localStorage.setItem('importColumnsOrder', JSON.stringify(newColumns));
}, { deep: true });

// Optional: Load saved column order on component mount
onMounted(() => {
    const savedColumns = localStorage.getItem('importColumnsOrder');
    if (savedColumns) {
        importColumns.value = JSON.parse(savedColumns);
    }
});

const props = defineProps({
    records: {
        type: Object,
        required: true
    },
    schools: {
        type: Array,
        required: true,
        default: () => []
    },
    userRoles: {
        type: Array,
        required: true,
        default: () => []
    },
    permissions: {
        type: Object,
        required: true,
        default: () => ({})
    }
});

const baseUrl = '/admin/students';
const showModal = ref(false);
const editingData = ref(null);
const form = ref({
    name: '',
    name_ar: '',
    name_cute: '',
    notes: '',
    school_id: '',
    stage_id: '',
    grade_id: '',
    classroom_id: ''
});

const tableColumns = [
    { key: 's_id', label: 'ID' },
    { key: 'name', label: 'Name' },
    { key: 'name_ar', label: 'Arabic Name' },
    { key: 'school.name', label: 'School' },
    { key: 'classroom.name', label: 'Classroom' },
    { key: 'grade.name', label: 'Grade' },
    { key: 'stage.name', label: 'Stage' }
];

const localRecords = ref(props.records);
const localSchools = ref(props.schools);
const localUserRoles = ref(props.userRoles);
const localPermissions = ref(props.permissions);

const items = ref([]);
const pagination = ref(null);

const modalTitle = computed(() => editingData.value ? 'Edit Student' : 'Add New Student');
const first = ()=>{
             selectedSchool.value   =1
             selectedStage.value    =1
             selectedGrade.value    =1
             selectedClassroom.value=1



    const filters = {
        school_id: 1,
        stage_id: 1,
        grade_id: 1,
        classroom_id: 1
    };
    handleFilterApplied(filters)


};


const openModal = (item = null) => {
    formErrors.value = {};

    if (!selectedSchool.value) {
        alert('Please select a school first');
        return;
    }

    if (item) {
        editingData.value = { ...item };  // Make a copy of the item
    } else {
        editingData.value = null;
        form.value = {
            name: '',
            name_ar: '',
            name_cute: '',
            notes: '',
            school_id: selectedSchool.value,
            stage_id: selectedStage.value || '',
            grade_id: selectedGrade.value || '',
            classroom_id: ''
        };
    }
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    editingData.value = null;
    formErrors.value = {}; // Reset errors when closing modal
};

const submitForm = async (formData1) => {
    if (submitting.value) return;

    const formData = formData1.form;
    submitting.value = true;
    formErrors.value = {};

    const url = editingData.value ? `${baseUrl}/${editingData.value.id}` : baseUrl;

    const requestData = {
        ...(editingData.value && { _method: 'PUT' }),
        name: formData.name?.trim(),
        name_ar: formData.name_ar?.trim(),
        name_cute: formData.name_cute?.trim(),
        notes: formData.notes?.trim(),
        school_id: selectedSchool.value,
        stage_id: selectedStage.value,
        grade_id: selectedGrade.value,
        classroom_id: selectedClassroom.value,
    };

    // Validate required fields
    const requiredFields = {
        name: 'Name',
        school_id: 'School',
        stage_id: 'Stage',
        grade_id: 'Grade',
        classroom_id: 'Classroom'
    };

    const missingFields = Object.entries(requiredFields)
        .filter(([key]) => !requestData[key])
        .map(([, label]) => label);

    if (missingFields.length > 0) {
        formErrors.value = {
            error: [`Please fill in the following required fields: ${missingFields.join(', ')}`]
        };
        submitting.value = false;
        toast.error('Please fill in all required fields');
        return;
    }

    try {
        const response = await axios.post(url, requestData);

        if (response.data.records) {
            localRecords.value = response.data.records;
            closeModal();
            toast.success(editingData.value ? 'Student updated successfully' : 'Student created successfully');
            return { success: true };
        }
    } catch (error) {
        handleAxiosError(error, formErrors, {
            logToConsole: true,
            showToast: true,
            customMessages: {
                422: 'Please check the form for errors',
                404: 'Student not found',
                403: 'You do not have permission to perform this action',
                default: 'Failed to save student'
            }
        });
    } finally {
        submitting.value = false;
    }
};

const deleteRecord = async (item) => {
    if (!confirm('Are you sure you want to delete this student?')) return;

    try {
        const response = await axios.delete(`${baseUrl}/${item.id}`);
        localRecords.value = response.data.records;
        toast.success('Student deleted successfully');
    } catch (error) {
        handleAxiosError(error, null, {
            showToast: true,
            customMessages: {
                404: 'Student not found',
                403: 'You do not have permission to delete this student',
                default: 'Failed to delete student'
            }
        });
    }
};

const refreshData = async () => {
    try {
        const response = await axios.get(baseUrl);
        localRecords.value = response.data.records;
        localSchools.value = response.data.schools;
        localUserRoles.value = response.data.userRoles;
        localPermissions.value = response.data.permissions;
    } catch (error) {
        console.error('Error refreshing data:', error);
    }
};

const handleExport = (format = 'excel') => {
    exportData({
        items: items.value,
        columns: tableColumns,
        fileName: 'students',
        sheetName: 'Students',
        format: format // 'excel' or 'csv'
    });
};

const formFields = [
    {
        name: 'name',
        label: 'Name',
        type: 'text',
        required: true,
        placeholder: 'Enter student name'
    },
    {
        name: 'name_ar',
        label: 'Arabic Name',
        type: 'text',
        required: false,
        placeholder: 'Enter Arabic name'
    },
    {
        name: 'name_cute',
        label: 'Nickname',
        type: 'text',
        required: false,
        placeholder: 'Enter nickname'
    },
    {
        name: 'notes',
        label: 'Notes',
        type: 'textarea',
        required: false,
        placeholder: 'Enter any additional notes'
    }
];

// Refs for filtering and data
const stages = ref([]);
const grades = ref([]);
const classrooms = ref([]);
const selectedSchool = ref('');
const selectedStage = ref('');
const selectedGrade = ref('');
const selectedClassroom = ref('');

// Methods for handling dropdowns and data loading
const handleSchoolChange = async () => {
    selectedStage.value = '';
    selectedGrade.value = '';
    stages.value = [];
    grades.value = [];

    if (selectedSchool.value) {
        await loadStages(selectedSchool.value);
    }
    await filterData();
};

const handleStageChange =  () => {
    console.log(props?.schools);

    selectedGrade.value = '';
    grades.value = [];

    // if (selectedStage.value) {
    //     await loadGrades(selectedStage.value);
    // }
      filterData();
};

const handleGradeChange = () => {
    selectedClassroom.value = '';
    classrooms.value = [];

    if (selectedGrade.value) {
        axios.get(`/admin/classrooms/by-grade/${selectedGrade.value}`)
            .then(response => {
                classrooms.value = response.data;
            })
            .catch(error => {
                console.error('Error loading classrooms:', error);
                classrooms.value = [];
            });
    }
};

const loadStages = async (schoolId) => {
    try {
        const response = await axios.get(`/admin/stages/by-school/${schoolId}`);
        stages.value = response.data;
    } catch (error) {
        console.error('Error loading stages:', error);
        stages.value = [];
    }
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

const filterData = async () => {
    try {
        const params = {
            school_id: selectedSchool.value,
            stage_id: selectedStage.value,
            grade_id: selectedGrade.value
        };

        // const response = await axios.get(baseUrl, { params });
        // localRecords.value = response.data.records;
    } catch (error) {
        console.error('Error filtering data:', error);
    }
};

const handleFilterApplied = (filters) => {
    console.log(    filters);

    const params = {
        school_id: filters.school_id || '',
        stage_id: filters.stage_id || '',
        grade_id: filters.grade_id || '',
        classroom_id: filters.classroom_id || ''
    };
    selectedSchool.value = filters.school_id;
    selectedStage.value = filters.stage_id;
    selectedGrade.value = filters.grade_id;
    selectedClassroom.value = filters.classroom_id;

    axios.get(`${baseUrl}/filtered`, { params })
        .then(response => {
            if (response.data && response.data.records) {
                items.value = response.data.records.data;
                pagination.value = response.data.records.links;
                localRecords.value = response.data.records;
            }
        })
        .catch(error => {
            console.error('Error applying filters:', error);
            if (error.response?.data?.message) {
                alert(error.response.data.message);
            } else {
                alert('An error occurred while filtering students');
            }
        });
};

onMounted(() => {
    if (props.records) {
        items.value = props.records.data || [];
        pagination.value = props.records.links || null;
        localRecords.value = props.records;
    }
});
</script>
