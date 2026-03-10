<template>
    <Head title="Assign Courses to Teacher" />
    <AppLayout title="Assign Courses to Teacher">
        <div class="q-pa-md">
            <q-card flat bordered>
                <q-card-section>
                    <div class="row items-center justify-between">
                        <div>
                            <div class="text-h6">Assign Courses to Teacher</div>
                            <div class="text-subtitle2">Select a teacher and assign multiple courses to them</div>
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
                                color="primary" 
                                icon="school" 
                                label="Assign by Course" 
                                @click="$inertia.visit(route('course-management.teachers.assign-by-course'))"
                            />
                        </div>
                    </div>
                </q-card-section>

                <q-separator />

                <!-- Teacher Selection -->
                <q-card-section>
                    <div class="text-h6 q-mb-md">Step 1: Select Teacher</div>
                    
                    <div class="row q-col-gutter-md">
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

                    <div class="row q-col-gutter-md q-mt-md">
                        <div 
                            v-for="teacher in filteredTeachers" 
                            :key="teacher.id" 
                            class="col-12 col-sm-6 col-md-4"
                        >
                            <q-card 
                                flat 
                                bordered 
                                class="cursor-pointer teacher-card"
                                :class="{ 'bg-orange-1 border-orange': selectedTeacher?.id === teacher.id }"
                                @click="selectTeacher(teacher)"
                            >
                                <q-card-section>
                                    <div class="row items-center q-gutter-md">
                                        <q-avatar size="48px" color="primary" text-color="white">
                                            {{ getInitials(teacher.name) }}
                                        </q-avatar>
                                        <div class="col">
                                            <div class="text-h6 text-primary">{{ teacher.name }}</div>
                                            <div class="text-caption text-grey-7">{{ teacher.email }}</div>
                                            <div class="text-caption text-grey-7">{{ teacher.phone || 'No phone' }}</div>
                                        </div>
                                    </div>
                                    <div class="q-mt-sm">
                                        <q-chip size="sm" color="orange-1" text-color="orange-8" icon="school">
                                            {{ teacher.course_assignments?.length || 0 }} Courses
                                        </q-chip>
                                        <q-chip size="sm" color="blue-1" text-color="blue-8" icon="subject" class="q-ml-xs">
                                            {{ teacher.subject || 'No subject' }}
                                        </q-chip>
                                    </div>
                                </q-card-section>
                            </q-card>
                        </div>
                    </div>
                </q-card-section>

                <!-- Course Selection -->
                <q-card-section v-if="selectedTeacher">
                    <div class="text-h6 q-mb-md">Step 2: Select Courses for {{ selectedTeacher.name }}</div>
                    
                    <!-- Current Assignments -->
                    <div v-if="currentAssignments.length > 0" class="q-mb-md">
                        <div class="text-subtitle1 q-mb-sm">Currently Assigned Courses:</div>
                        <div class="row q-col-gutter-sm">
                            <div 
                                v-for="assignment in currentAssignments" 
                                :key="assignment.course.id"
                                class="col-auto"
                            >
                                <q-chip 
                                    color="orange" 
                                    text-color="white" 
                                    icon="school"
                                    removable
                                    @remove="removeCurrentAssignment(assignment)"
                                >
                                    {{ assignment.course.name }}
                                </q-chip>
                            </div>
                        </div>
                    </div>

                    <!-- Course Search -->
                    <div class="row q-col-gutter-md q-mb-md">
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

                    <!-- Courses Table -->
                    <q-table
                        :rows="availableCourses"
                        :columns="courseColumns"
                        row-key="id"
                        selection="multiple"
                        v-model:selected="selectedCourses"
                        flat
                        bordered
                        :pagination="{ rowsPerPage: 10 }"
                        :filter="courseSearchQuery"
                    >
                        <template v-slot:body-cell-name="props">
                            <q-td :props="props">
                                <div>
                                    <div class="text-subtitle2">{{ props.row.name }}</div>
                                    <div class="text-caption text-grey-7">
                                        {{ props.row.description || 'No description' }}
                                    </div>
                                </div>
                            </q-td>
                        </template>

                        <template v-slot:body-cell-levels="props">
                            <q-td :props="props">
                                <q-chip size="sm" color="blue-1" text-color="blue-8" icon="layers">
                                    {{ props.row.levels?.length || 0 }} Levels
                                </q-chip>
                            </q-td>
                        </template>

                        <template v-slot:body-cell-teachers="props">
                            <q-td :props="props">
                                <q-chip size="sm" color="green-1" text-color="green-8" icon="person">
                                    {{ props.row.teacher_assignments?.length || 0 }} Teachers
                                </q-chip>
                            </q-td>
                        </template>

                        <template v-slot:body-cell-created_at="props">
                            <q-td :props="props">
                                {{ formatDate(props.row.created_at) }}
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
                            icon="school" 
                            label="Assign Courses" 
                            :disable="selectedCourses.length === 0"
                            :loading="assigning"
                            @click="assignCourses"
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
    teachers: {
        type: Array,
        required: true,
    },
    courses: {
        type: Array,
        required: true,
    }
});

const $q = useQuasar();

// State
const teacherSearchQuery = ref('');
const courseSearchQuery = ref('');
const selectedTeacher = ref(null);
const selectedCourses = ref([]);
const assignmentNotes = ref('');
const assigning = ref(false);

// Table columns
const courseColumns = [
    { name: 'name', label: 'Course', field: 'name', align: 'left', sortable: true },
    { name: 'levels', label: 'Levels', field: 'levels', align: 'center' },
    { name: 'teachers', label: 'Teachers', field: 'teachers', align: 'center' },
    { name: 'created_at', label: 'Created', field: 'created_at', align: 'left', sortable: true },
];

// Computed properties
const filteredTeachers = computed(() => {
    if (!teacherSearchQuery.value) return props.teachers;
    
    const query = teacherSearchQuery.value.toLowerCase();
    return props.teachers.filter(teacher => 
        teacher.name.toLowerCase().includes(query) ||
        teacher.email.toLowerCase().includes(query) ||
        teacher.subject?.toLowerCase().includes(query)
    );
});

const currentAssignments = computed(() => {
    if (!selectedTeacher.value) return [];
    return selectedTeacher.value.course_assignments || [];
});

const availableCourses = computed(() => {
    if (!selectedTeacher.value) return props.courses;
    
    const assignedCourseIds = currentAssignments.value.map(assignment => assignment.course.id);
    return props.courses.filter(course => !assignedCourseIds.includes(course.id));
});

// Methods
const getInitials = (name) => {
    if (!name) return '?';
    return name.split(' ').map(word => word.charAt(0)).join('').toUpperCase();
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

const selectTeacher = (teacher) => {
    selectedTeacher.value = teacher;
    selectedCourses.value = [];
    assignmentNotes.value = '';
};

const clearSelection = () => {
    selectedCourses.value = [];
    assignmentNotes.value = '';
};

const removeCurrentAssignment = (course) => {
    router.delete(route('course-management.teachers.remove-assignment-by-ids'), {
        teacher_id: selectedTeacher.value.id,
        course_id: course.id
    }, {
        onSuccess: () => {
            $q.notify({
                type: 'positive',
                message: 'Course removed from teacher successfully.',
                icon: 'check_circle'
            });
        },
        onError: () => {
            $q.notify({
                type: 'negative',
                message: 'Failed to remove course from teacher.',
                icon: 'error'
            });
        }
    });
};

const assignCourses = () => {
    if (selectedCourses.value.length === 0) {
        $q.notify({
            type: 'warning',
            message: 'Please select at least one course.',
            icon: 'warning'
        });
        return;
    }

    assigning.value = true;

    const data = {
        teacher_id: selectedTeacher.value.id,
        course_ids: selectedCourses.value.map(course => course.id),
        notes: assignmentNotes.value,
    };

    router.post(route('course-management.teachers.assign-courses-to-teacher'), data, {
        onSuccess: () => {
            $q.notify({
                type: 'positive',
                message: `${selectedCourses.value.length} course(s) assigned to teacher successfully!`,
                icon: 'check_circle'
            });
            
            // Clear selections
            selectedCourses.value = [];
            assignmentNotes.value = '';
            
            // Reload the page to get updated data
            router.reload();
        },
        onError: (errors) => {
            $q.notify({
                type: 'negative',
                message: 'Failed to assign courses to teacher.',
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
.teacher-card {
    transition: transform 0.2s, box-shadow 0.2s;
}

.teacher-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.border-orange {
    border: 2px solid #ff9800 !important;
}
</style>