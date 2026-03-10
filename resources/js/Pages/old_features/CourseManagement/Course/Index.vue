<template>
    <Head title="Course Management" />
    <AppLayout title="Course Management">
        <div class="q-pa-md">
            <q-card flat bordered>
                <q-card-section>
                    <div class="row items-center justify-between">
                        <div>
                            <div class="text-h6">Course Management</div>
                            <div class="text-subtitle2">Manage educational courses, levels, sections, and lessons.</div>
                        </div>
                        <div class="q-gutter-sm">
                            <q-btn 
                                flat
                                color="purple" 
                                icon="person_add" 
                                label="Assign Teachers" 
                                @click="$inertia.visit(route('course-management.teachers.index'))"
                            />
                            <q-btn 
                                flat
                                color="orange" 
                                icon="upload" 
                                label="Import Excel" 
                                @click="$inertia.visit(route('course-management.import.index'))"
                            />
                            <q-btn 
                                color="primary" 
                                icon="add" 
                                label="New Course" 
                                @click="$inertia.visit(route('course-management.courses.create'))"
                            />
                        </div>
                    </div>
                </q-card-section>

                <!-- Stats Cards -->
                <q-card-section class="row q-col-gutter-md">
                    <div class="col-12 col-sm-6 col-md-3">
                        <q-card class="bg-blue-1 text-center">
                            <q-card-section>
                                <div class="text-h5 text-bold text-blue-8">{{ courses.length }}</div>
                                <div class="text-subtitle2 text-grey-8">Total Courses</div>
                            </q-card-section>
                        </q-card>
                    </div>
                    <div class="col-12 col-sm-6 col-md-3">
                        <q-card class="bg-green-1 text-center">
                            <q-card-section>
                                <div class="text-h5 text-bold text-green-8">{{ totalLevels }}</div>
                                <div class="text-subtitle2 text-grey-8">Total Levels</div>
                            </q-card-section>
                        </q-card>
                    </div>
                    <div class="col-12 col-sm-6 col-md-3">
                        <q-card class="bg-orange-1 text-center">
                            <q-card-section>
                                <div class="text-h5 text-bold text-orange-8">{{ activeCourses }}</div>
                                <div class="text-subtitle2 text-grey-8">Active Courses</div>
                            </q-card-section>
                        </q-card>
                    </div>
                    <div class="col-12 col-sm-6 col-md-3">
                        <q-card class="bg-purple-1 text-center">
                            <q-card-section>
                                <div class="text-h5 text-bold text-purple-8">{{ recentCourses }}</div>
                                <div class="text-subtitle2 text-grey-8">Recent Courses</div>
                            </q-card-section>
                        </q-card>
                    </div>
                </q-card-section>

                <!-- Search and Filters -->
                <q-card-section>
                    <div class="row q-col-gutter-md items-center">
                        <div class="col-12 col-md-6">
                            <q-input
                                v-model="searchQuery"
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
                        <div class="col-12 col-md-3">
                            <q-select
                                v-model="sortBy"
                                :options="sortOptions"
                                outlined
                                dense
                                label="Sort by"
                                emit-value
                                map-options
                            />
                        </div>
                        <div class="col-12 col-md-3">
                            <q-select
                                v-model="viewMode"
                                :options="viewOptions"
                                outlined
                                dense
                                label="View"
                                emit-value
                                map-options
                            />
                        </div>
                    </div>
                </q-card-section>

                <q-separator />

                <!-- Course List -->
                <q-card-section>
                    <div v-if="loading" class="text-center q-pa-lg">
                        <q-spinner-dots color="primary" size="40px" />
                        <p>Loading courses...</p>
                    </div>
                    
                    <!-- Grid View -->
                    <div v-else-if="viewMode === 'grid' && filteredCourses.length > 0" class="row q-col-gutter-md">
                        <div v-for="course in filteredCourses" :key="course.id" class="col-12 col-sm-6 col-md-4 col-lg-3">
                            <CourseCard :course="course" @edit="editCourse" @delete="deleteCourse" />
                        </div>
                    </div>

                    <!-- Table View -->
                    <q-table
                        v-else-if="viewMode === 'table' && filteredCourses.length > 0"
                        :rows="filteredCourses"
                        :columns="tableColumns"
                        row-key="id"
                        flat
                        bordered
                        :pagination="{ rowsPerPage: 10 }"
                    >
                        <template v-slot:body-cell-actions="props">
                            <q-td :props="props">
                                <q-btn-group flat>
                                    <q-btn 
                                        flat 
                                        dense 
                                        color="primary" 
                                        icon="visibility" 
                                        @click="$inertia.visit(route('course-management.courses.show', props.row.id))"
                                    >
                                        <q-tooltip>View Course</q-tooltip>
                                    </q-btn>
                                    <q-btn 
                                        flat 
                                        dense 
                                        color="orange" 
                                        icon="edit" 
                                        @click="editCourse(props.row)"
                                    >
                                        <q-tooltip>Edit Course</q-tooltip>
                                    </q-btn>
                                    <q-btn 
                                        flat 
                                        dense 
                                        color="negative" 
                                        icon="delete" 
                                        @click="deleteCourse(props.row)"
                                    >
                                        <q-tooltip>Delete Course</q-tooltip>
                                    </q-btn>
                                </q-btn-group>
                            </q-td>
                        </template>
                    </q-table>

                    <!-- Empty State -->
                    <div v-else class="text-center text-grey-7 q-pa-xl">
                        <q-icon name="school" size="4em" />
                        <p class="q-mt-md">No courses found matching your criteria.</p>
                        <q-btn 
                            color="primary" 
                            label="Create First Course" 
                            @click="$inertia.visit(route('course-management.courses.create'))"
                        />
                    </div>
                </q-card-section>
            </q-card>
        </div>

        <!-- Delete Confirmation Dialog -->
        <q-dialog v-model="showDeleteDialog" persistent>
            <q-card>
                <q-card-section class="row items-center">
                    <q-avatar icon="warning" color="negative" text-color="white" />
                    <span class="q-ml-sm">Are you sure you want to delete this course?</span>
                </q-card-section>
                <q-card-section v-if="courseToDelete">
                    <div class="text-subtitle2">Course: {{ courseToDelete.name }}</div>
                    <div class="text-caption text-grey-7">This action cannot be undone.</div>
                </q-card-section>
                <q-card-actions align="right">
                    <q-btn flat label="Cancel" color="primary" @click="showDeleteDialog = false" />
                    <q-btn flat label="Delete" color="negative" @click="confirmDelete" />
                </q-card-actions>
            </q-card>
        </q-dialog>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router, Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import CourseCard from './components/CourseCard.vue';
import { useQuasar } from 'quasar';

const props = defineProps({
    courses: {
        type: Array,
        required: true,
    }
});

const $q = useQuasar();

// State
const loading = ref(false);
const searchQuery = ref('');
const sortBy = ref('created_at');
const viewMode = ref('grid');
const showDeleteDialog = ref(false);
const courseToDelete = ref(null);

// Options
const sortOptions = [
    { label: 'Recently Created', value: 'created_at' },
    { label: 'Name (A-Z)', value: 'name' },
    { label: 'Most Levels', value: 'levels_count' },
];

const viewOptions = [
    { label: 'Grid View', value: 'grid' },
    { label: 'Table View', value: 'table' },
];

const tableColumns = [
    { name: 'name', label: 'Course Name', field: 'name', align: 'left', sortable: true },
    { name: 'description', label: 'Description', field: 'description', align: 'left' },
    { name: 'levels_count', label: 'Levels', field: row => row.levels?.length || 0, align: 'center' },
    { name: 'creator', label: 'Created By', field: row => row.creator?.name || 'Unknown', align: 'left' },
    { name: 'created_at', label: 'Created', field: 'created_at', align: 'left', format: val => new Date(val).toLocaleDateString() },
    { name: 'actions', label: 'Actions', align: 'center' },
];

// Computed Properties
const filteredCourses = computed(() => {
    let filtered = props.courses;

    // Search filter
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(course => 
            course.name.toLowerCase().includes(query) ||
            course.description?.toLowerCase().includes(query)
        );
    }

    // Sort
    filtered.sort((a, b) => {
        if (sortBy.value === 'name') {
            return a.name.localeCompare(b.name);
        } else if (sortBy.value === 'levels_count') {
            return (b.levels?.length || 0) - (a.levels?.length || 0);
        } else {
            return new Date(b.created_at) - new Date(a.created_at);
        }
    });

    return filtered;
});

const totalLevels = computed(() => {
    return props.courses.reduce((total, course) => total + (course.levels?.length || 0), 0);
});

const activeCourses = computed(() => {
    return props.courses.filter(course => course.levels?.length > 0).length;
});

const recentCourses = computed(() => {
    const oneWeekAgo = new Date();
    oneWeekAgo.setDate(oneWeekAgo.getDate() - 7);
    return props.courses.filter(course => new Date(course.created_at) > oneWeekAgo).length;
});

// Methods
const editCourse = (course) => {
    router.visit(route('course-management.courses.edit', course.id));
};

const deleteCourse = (course) => {
    courseToDelete.value = course;
    showDeleteDialog.value = true;
};

const confirmDelete = () => {
    if (courseToDelete.value) {
        router.delete(route('course-management.courses.destroy', courseToDelete.value.id), {
            onSuccess: () => {
                $q.notify({
                    type: 'positive',
                    message: 'Course deleted successfully.',
                    icon: 'check_circle'
                });
                showDeleteDialog.value = false;
                courseToDelete.value = null;
            },
            onError: () => {
                $q.notify({
                    type: 'negative',
                    message: 'Failed to delete course.',
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