<template>
    <Head title="Assign Teachers to Course" />
    <AppLayout title="Assign Teachers to Course">
        <div class="q-pa-md">
            <q-card flat bordered>
                <q-card-section>
                    <div class="row items-center justify-between">
                        <div>
                            <div class="text-h6">Assign Teachers to Course</div>
                            <div class="text-subtitle2">Select a course and assign multiple teachers to it</div>
                        </div>
                        <div class="q-gutter-sm">
                            <q-btn 
                                flat 
                                color="grey-7" 
                                icon="arrow_back" 
                                label="Back to Assignments" 
                                @click="$inertia.visit(route('course-management.teachers.index'))"
                            />
                            <q-btn 
                                flat 
                                color="orange" 
                                icon="person_add" 
                                label="Assign by Teacher" 
                                @click="$inertia.visit(route('course-management.teachers.assign-by-teacher'))"
                            />
                        </div>
                    </div>
                </q-card-section>

                <q-separator />

                <!-- Course Selection -->
                <q-card-section>
                    <div class="text-h6 q-mb-md">Step 1: Select Course</div>
                    
                    <div class="row q-col-gutter-md">
                        <div class="col-12 col-md-6">
                            <q-input
                                v-model="courseSearchQuery"
                                outlined
                                dense
                                placeholder="Search courses..."
                                clearable
                            >
                                <template v-slot:prepend>
                                    <q-icon name="search" />
                                </template>
                            </q-input>
                        </div>
                    </div>

                    <div class="row q-col-gutter-md q-mt-md">
                        <div 
                            v-for="course in filteredCourses" 
                            :key="course.id" 
                            class="col-12 col-sm-6 col-md-4"
                        >
                            <q-card 
                                flat 
                                bordered 
                                class="cursor-pointer course-card"
                                :class="{ 'bg-blue-1 border-primary': selectedCourse?.id === course.id }"
                                @click="selectCourse(course)"
                            >
                                <q-card-section>
                                    <div class="text-h6 text-primary">{{ course.name }}</div>
                                    <div class="text-caption text-grey-7 q-mt-xs">
                                        {{ course.description || 'No description' }}
                                    </div>
                                    <div class="q-mt-sm">
                                        <q-chip size="sm" color="blue-1" text-color="blue-8" icon="layers">
                                            {{ course.levels?.length || 0 }} Levels
                                        </q-chip>
                                        <q-chip size="sm" color="green-1" text-color="green-8" icon="person" class="q-ml-xs">
                                            {{ course.teachers?.length || 0 }} Teachers
                                        </q-chip>
                                    </div>
                                </q-card-section>
                            </q-card>
                        </div>
                    </div>
                </q-card-section>

                <!-- Teacher Selection -->
                <q-card-section v-if="selectedCourse">
                    <div class="text-h6 q-mb-md">Step 2: Select Teachers for "{{ selectedCourse.name }}"</div>
                    
                    <!-- Current Assignments -->
                    <div v-if="currentAssignments.length > 0" class="q-mb-md">
                        <div class="text-subtitle1 q-mb-sm">Currently Assigned Teachers:</div>
                        <div class="row q-col-gutter-sm">
                            <div 
                                v-for="teacher in currentAssignments" 
                                :key="teacher.id"
                                class="col-auto"
                            >
                                <q-chip 
                                    color="green" 
                                    text-color="white" 
                                    icon="person"
                                    removable
                                    @remove="removeCurrentAssignment(teacher)"
                                >
                                    {{ teacher.user?.name || teacher.name }}
                                </q-chip>
                            </div>
                        </div>
                    </div>

                    <!-- Teacher Search -->
                    <div class="row q-col-gutter-md q-mb-md">
                        <div class="col-12 col-md-6">
                            <q-input
                                v-model="teacherSearchQuery"
                                outlined
                                dense
                                placeholder="Search teachers..."
                                clearable
                            >
                                <template v-slot:prepend>
                                    <q-icon name="search" />
                                </template>
                            </q-input>
                        </div>
                    </div>

                    <!-- Teachers Table -->
                    <q-table
                        :rows="availableTeachers"
                        :columns="teacherColumns"
                        row-key="id"
                        selection="multiple"
                        v-model:selected="selectedTeachers"
                        flat
                        bordered
                        :pagination="{ rowsPerPage: 10 }"
                        :filter="teacherSearchQuery"
                    >
                        <template v-slot:body-cell-name="props">
                            <q-td :props="props">
                                <div class="row items-center q-gutter-sm">
                                    <q-avatar size="32px" color="primary" text-color="white">
                                        {{ getInitials(props.row.name) }}
                                    </q-avatar>
                                    <div>
                                        <div class="text-subtitle2">{{ props.row.name }}</div>
                                        <div class="text-caption text-grey-7">{{ props.row.email }}</div>
                                    </div>
                                </div>
                            </q-td>
                        </template>

                        <template v-slot:body-cell-phone="props">
                            <q-td :props="props">
                                {{ props.row.phone || 'N/A' }}
                            </q-td>
                        </template>

                        <template v-slot:body-cell-subject="props">
                            <q-td :props="props">
                                {{ props.row.subject || 'N/A' }}
                            </q-td>
                        </template>
                    </q-table>

                    <!-- Assignment Form -->
                    <div class="q-mt-md">
                        <q-input
                            v-model="assignmentNotes"
                            outlined
                            type="textarea"
                            rows="3"
                            label="Assignment Notes (Optional)"
                            placeholder="Add any notes about this assignment..."
                        />
                    </div>

                    <!-- Action Buttons -->
                    <div class="row justify-end q-gutter-sm q-mt-md">
                        <q-btn 
                            flat 
                            label="Clear Selection" 
                            color="grey-7" 
                            @click="clearSelection"
                        />
                        <q-btn 
                            color="primary" 
                            icon="person_add" 
                            label="Assign Teachers" 
                            :disable="selectedTeachers.length === 0"
                            :loading="assigning"
                            @click="assignTeachers"
                        />
                    </div>
                </q-card-section>
            </q-card>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router, Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useQuasar } from 'quasar';

const props = defineProps({
    courses: {
        type: Array,
        required: true,
    },
    teachers: {
        type: Array,
        required: true,
    }
});

const $q = useQuasar();

// State
const courseSearchQuery = ref('');
const teacherSearchQuery = ref('');
const selectedCourse = ref(null);
const selectedTeachers = ref([]);
const assignmentNotes = ref('');
const assigning = ref(false);

// Table columns
const teacherColumns = [
    { name: 'name', label: 'Teacher', field: 'name', align: 'left', sortable: true },
    { name: 'phone', label: 'Phone', field: 'phone', align: 'left', sortable: true },
    { name: 'subject', label: 'Subject', field: 'subject', align: 'left', sortable: true },
];

// Computed properties
const filteredCourses = computed(() => {
    if (!courseSearchQuery.value) return props.courses;
    
    const query = courseSearchQuery.value.toLowerCase();
    return props.courses.filter(course => 
        course.name.toLowerCase().includes(query) ||
        course.description?.toLowerCase().includes(query)
    );
});

const currentAssignments = computed(() => {
    if (!selectedCourse.value) return [];
    return selectedCourse.value.teachers || [];
});

const availableTeachers = computed(() => {
    if (!selectedCourse.value) return props.teachers;
    
    const assignedTeacherIds = currentAssignments.value.map(teacher => teacher.id);
    return props.teachers.filter(teacher => !assignedTeacherIds.includes(teacher.id));
});

// Methods
const getInitials = (name) => {
    if (!name) return '?';
    return name.split(' ').map(word => word.charAt(0)).join('').toUpperCase();
};

const selectCourse = (course) => {
    selectedCourse.value = course;
    selectedTeachers.value = [];
    assignmentNotes.value = '';
};

const clearSelection = () => {
    selectedTeachers.value = [];
    assignmentNotes.value = '';
};

const removeCurrentAssignment = (teacher) => {
    // Use post method with _method: 'delete' to send data in the request body
    router.post(route('course-management.teachers.remove-assignment-by-ids'), {
        _method: 'delete',
        teacher_id: teacher.id,
        course_id: selectedCourse.value.id
    }, {
        onSuccess: () => {
            $q.notify({
                type: 'positive',
                message: 'Teacher removed from course successfully.',
                icon: 'check_circle'
            });
            // Reload to refresh the data
            router.reload();
        },
        onError: () => {
            $q.notify({
                type: 'negative',
                message: 'Failed to remove teacher from course.',
                icon: 'error'
            });
        }
    });
};

const assignTeachers = () => {
    if (selectedTeachers.value.length === 0) {
        $q.notify({
            type: 'warning',
            message: 'Please select at least one teacher.',
            icon: 'warning'
        });
        return;
    }

    assigning.value = true;

    const data = {
        course_id: selectedCourse.value.id,
        teacher_ids: selectedTeachers.value.map(teacher => teacher.id),
        notes: assignmentNotes.value,
    };

    router.post(route('course-management.teachers.assign-teachers-to-course'), data, {
        onSuccess: () => {
            $q.notify({
                type: 'positive',
                message: `${selectedTeachers.value.length} teacher(s) assigned to course successfully!`,
                icon: 'check_circle'
            });
            
            // Clear selections
            selectedTeachers.value = [];
            assignmentNotes.value = '';
            
            // Reload the page to get updated data
            router.reload();
        },
        onError: (errors) => {
            $q.notify({
                type: 'negative',
                message: 'Failed to assign teachers to course.',
                caption: Object.values(errors).join(' '),
                icon: 'error'
            });
        },
        onFinish: () => {
            assigning.value = false;
        }
    });
};
</script>

<style scoped>
.course-card {
    transition: transform 0.2s, box-shadow 0.2s;
}

.course-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.border-primary {
    border: 2px solid #1976d2 !important;
}
</style>