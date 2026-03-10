<template>
    <Head title="Teacher Course Assignments" />
    <AppLayout title="Teacher Course Assignments">
        <div class="q-pa-md">
            <q-card flat bordered>
                <q-card-section>
                    <div class="row items-center justify-between">
                        <div>
                            <div class="text-h6">Teacher Course Assignments</div>
                            <div class="text-subtitle2">Manage course assignments for teachers</div>
                        </div>
                        <div class="q-gutter-sm">
                            <q-btn 
                                flat 
                                color="grey-7" 
                                icon="arrow_back" 
                                label="Back to Courses" 
                                @click="$inertia.visit(route('course-management.courses.index'))"
                            />
                            <q-btn 
                                color="orange" 
                                icon="person_add" 
                                label="Assign by Teacher" 
                                @click="$inertia.visit(route('course-management.teachers.assign-by-teacher'))"
                            />
                            <q-btn 
                                color="primary" 
                                icon="school" 
                                label="Assign by Course" 
                                @click="$inertia.visit(route('course-management.teachers.assign-by-course'))"
                            />
                        </div>
                    </div>
                </q-card-section>

                <!-- Stats Cards -->
                <q-card-section class="row q-col-gutter-md">
                    <div class="col-12 col-sm-6 col-md-3">
                        <q-card class="bg-blue-1 text-center">
                            <q-card-section>
                                <div class="text-h5 text-bold text-blue-8">{{ assignments.length }}</div>
                                <div class="text-subtitle2 text-grey-8">Total Assignments</div>
                            </q-card-section>
                        </q-card>
                    </div>
                    <div class="col-12 col-sm-6 col-md-3">
                        <q-card class="bg-green-1 text-center">
                            <q-card-section>
                                <div class="text-h5 text-bold text-green-8">{{ activeAssignments }}</div>
                                <div class="text-subtitle2 text-grey-8">Active Assignments</div>
                            </q-card-section>
                        </q-card>
                    </div>
                    <div class="col-12 col-sm-6 col-md-3">
                        <q-card class="bg-orange-1 text-center">
                            <q-card-section>
                                <div class="text-h5 text-bold text-orange-8">{{ uniqueTeachers }}</div>
                                <div class="text-subtitle2 text-grey-8">Teachers Assigned</div>
                            </q-card-section>
                        </q-card>
                    </div>
                    <div class="col-12 col-sm-6 col-md-3">
                        <q-card class="bg-purple-1 text-center">
                            <q-card-section>
                                <div class="text-h5 text-bold text-purple-8">{{ uniqueCourses }}</div>
                                <div class="text-subtitle2 text-grey-8">Courses Assigned</div>
                            </q-card-section>
                        </q-card>
                    </div>
                </q-card-section>

                <q-separator />

                <!-- Assignments Table -->
                <q-card-section>
                    <div class="text-h6 q-mb-md">Current Assignments</div>
                    
                    <q-table
                        :rows="assignments"
                        :columns="tableColumns"
                        row-key="id"
                        flat
                        bordered
                        :pagination="{ rowsPerPage: 15 }"
                        :filter="searchQuery"
                    >
                        <template v-slot:top>
                            <div class="row full-width items-center q-gutter-md">
                                <q-input
                                    v-model="searchQuery"
                                    outlined
                                    dense
                                    placeholder="Search assignments..."
                                    clearable
                                    class="col-12 col-md-4"
                                >
                                    <template v-slot:prepend>
                                        <q-icon name="search" />
                                    </template>
                                </q-input>
                                
                                <q-select
                                    v-model="statusFilter"
                                    :options="statusOptions"
                                    outlined
                                    dense
                                    label="Filter by Status"
                                    clearable
                                    class="col-12 col-md-3"
                                />
                            </div>
                        </template>

                        <template v-slot:body-cell-teacher="props">
                            <q-td :props="props">
                                <div class="row items-center q-gutter-sm">
                                    <q-avatar size="32px" color="primary" text-color="white">
                                        {{ getInitials(props.row.teacher?.name) }}
                                    </q-avatar>
                                    <div>
                                        <div class="text-subtitle2">{{ props.row.teacher?.name }}</div>
                                        <div class="text-caption text-grey-7">{{ props.row.teacher?.email }}</div>
                                    </div>
                                </div>
                            </q-td>
                        </template>

                        <template v-slot:body-cell-course="props">
                            <q-td :props="props">
                                <div>
                                    <div class="text-subtitle2">{{ props.row.course?.name }}</div>
                                    <div class="text-caption text-grey-7">
                                        {{ props.row.course?.levels?.length || 0 }} levels
                                    </div>
                                </div>
                            </q-td>
                        </template>

                        <template v-slot:body-cell-status="props">
                            <q-td :props="props">
                                <q-chip 
                                    :color="props.row.is_active ? 'green' : 'grey'" 
                                    :text-color="props.row.is_active ? 'white' : 'black'"
                                    size="sm"
                                >
                                    {{ props.row.is_active ? 'Active' : 'Inactive' }}
                                </q-chip>
                            </q-td>
                        </template>

                        <template v-slot:body-cell-assigned_at="props">
                            <q-td :props="props">
                                {{ formatDate(props.row.assigned_at) }}
                            </q-td>
                        </template>

                        <template v-slot:body-cell-actions="props">
                            <q-td :props="props">
                                <q-btn-group flat>
                                    <q-btn 
                                        flat 
                                        dense 
                                        :color="props.row.is_active ? 'orange' : 'green'" 
                                        :icon="props.row.is_active ? 'pause' : 'play_arrow'" 
                                        @click="toggleAssignment(props.row)"
                                    >
                                        <q-tooltip>{{ props.row.is_active ? 'Deactivate' : 'Activate' }}</q-tooltip>
                                    </q-btn>
                                    <q-btn 
                                        flat 
                                        dense 
                                        color="negative" 
                                        icon="delete" 
                                        @click="removeAssignment(props.row)"
                                    >
                                        <q-tooltip>Remove Assignment</q-tooltip>
                                    </q-btn>
                                </q-btn-group>
                            </q-td>
                        </template>
                    </q-table>
                </q-card-section>
            </q-card>
        </div>

        <!-- Remove Assignment Dialog -->
        <q-dialog v-model="showRemoveDialog" persistent>
            <q-card>
                <q-card-section class="row items-center">
                    <q-avatar icon="warning" color="negative" text-color="white" />
                    <span class="q-ml-sm">Are you sure you want to remove this assignment?</span>
                </q-card-section>
                <q-card-section v-if="assignmentToRemove">
                    <div class="text-subtitle2">
                        Teacher: {{ assignmentToRemove.teacher?.name }}
                    </div>
                    <div class="text-subtitle2">
                        Course: {{ assignmentToRemove.course?.name }}
                    </div>
                    <div class="text-caption text-grey-7">This action cannot be undone.</div>
                </q-card-section>
                <q-card-actions align="right">
                    <q-btn flat label="Cancel" color="primary" @click="showRemoveDialog = false" />
                    <q-btn flat label="Remove" color="negative" @click="confirmRemove" />
                </q-card-actions>
            </q-card>
        </q-dialog>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router, Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useQuasar } from 'quasar';

const props = defineProps({
    assignments: {
        type: Array,
        required: true,
    },
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
const searchQuery = ref('');
const statusFilter = ref(null);
const showRemoveDialog = ref(false);
const assignmentToRemove = ref(null);

// Options
const statusOptions = [
    { label: 'Active', value: true },
    { label: 'Inactive', value: false },
];

// Table columns
const tableColumns = [
    { name: 'teacher', label: 'Teacher', field: 'teacher', align: 'left', sortable: true },
    { name: 'course', label: 'Course', field: 'course', align: 'left', sortable: true },
    { name: 'status', label: 'Status', field: 'is_active', align: 'center', sortable: true },
    { name: 'assigned_at', label: 'Assigned Date', field: 'assigned_at', align: 'left', sortable: true },
    { name: 'actions', label: 'Actions', align: 'center' },
];

// Computed properties
const activeAssignments = computed(() => {
    return props.assignments.filter(assignment => assignment.is_active).length;
});

const uniqueTeachers = computed(() => {
    const teacherIds = new Set(props.assignments.map(assignment => assignment.teacher_id));
    return teacherIds.size;
});

const uniqueCourses = computed(() => {
    const courseIds = new Set(props.assignments.map(assignment => assignment.course_id));
    return courseIds.size;
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

const toggleAssignment = (assignment) => {
    router.patch(route('course-management.teachers.toggle-assignment', assignment.id), {}, {
        onSuccess: () => {
            $q.notify({
                type: 'positive',
                message: 'Assignment status updated successfully.',
                icon: 'check_circle'
            });
        },
        onError: () => {
            $q.notify({
                type: 'negative',
                message: 'Failed to update assignment status.',
                icon: 'error'
            });
        }
    });
};

const removeAssignment = (assignment) => {
    assignmentToRemove.value = assignment;
    showRemoveDialog.value = true;
};

const confirmRemove = () => {
    if (assignmentToRemove.value) {
        router.delete(route('course-management.teachers.remove-assignment', assignmentToRemove.value.id), {
            onSuccess: () => {
                $q.notify({
                    type: 'positive',
                    message: 'Assignment removed successfully.',
                    icon: 'check_circle'
                });
                showRemoveDialog.value = false;
                assignmentToRemove.value = null;
            },
            onError: () => {
                $q.notify({
                    type: 'negative',
                    message: 'Failed to remove assignment.',
                    icon: 'error'
                });
            }
        });
    }
};
</script>

<style scoped>
.q-card {
    transition: box-shadow 0.3s;
}
.q-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
</style>