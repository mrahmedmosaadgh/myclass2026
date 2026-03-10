<template>
    <AppLayout :title="lesson.title">
        <div class="q-pa-md">
            <q-card flat bordered>
                <q-card-section>
                    <div class="row items-center justify-between">
                        <div>
                            <div class="text-h5">{{ lesson.title }}</div>
                            <div class="text-subtitle2 text-grey-7">
                                Lesson in "{{ lesson.section?.title }}" - "{{ lesson.section?.level?.title }}" - "{{ lesson.section?.level?.course?.name }}"
                            </div>
                        </div>
                        <div class="q-gutter-sm">
                            <q-btn 
                                flat 
                                color="grey-7" 
                                icon="arrow_back" 
                                label="Back to Section" 
                                @click="$inertia.visit(route('course-management.sections.show', lesson.section.id))"
                            />
                            <q-btn 
                                color="orange" 
                                icon="edit" 
                                label="Edit Lesson" 
                                @click="$inertia.visit(route('course-management.lessons.edit', lesson.id))"
                            />
                        </div>
                    </div>
                </q-card-section>

                <!-- Lesson Content -->
                <q-card-section>
                    <div class="row q-col-gutter-md">
                        <div class="col-12 col-md-8">
                            <q-card flat bordered>
                                <q-card-section>
                                    <div class="text-h6 q-mb-md">Lesson Content</div>
                                    
                                    <div class="text-body1" v-if="lesson.text">
                                        {{ lesson.text }}
                                    </div>
                                    <div class="text-grey-6" v-else>
                                        No lesson description provided.
                                    </div>

                                    <!-- Learning Objectives -->
                                    <div v-if="lesson.data?.objectives" class="q-mt-lg">
                                        <div class="text-subtitle1 q-mb-md">Learning Objectives</div>
                                        <q-list>
                                            <q-item 
                                                v-for="(objective, index) in lesson.data.objectives" 
                                                :key="index"
                                                class="q-pl-none"
                                            >
                                                <q-item-section avatar>
                                                    <q-icon name="check_circle" color="positive" />
                                                </q-item-section>
                                                <q-item-section>
                                                    <q-item-label>{{ objective }}</q-item-label>
                                                </q-item-section>
                                            </q-item>
                                        </q-list>
                                    </div>
                                </q-card-section>
                            </q-card>
                        </div>

                        <div class="col-12 col-md-4">
                            <!-- Lesson Metadata -->
                            <q-card flat bordered>
                                <q-card-section>
                                    <div class="text-subtitle1 q-mb-md">Lesson Details</div>
                                    
                                    <q-list>
                                        <q-item>
                                            <q-item-section avatar>
                                                <q-icon name="format_list_numbered" color="primary" />
                                            </q-item-section>
                                            <q-item-section>
                                                <q-item-label>Order</q-item-label>
                                                <q-item-label caption>{{ lesson.order }}</q-item-label>
                                            </q-item-section>
                                        </q-item>
                                        
                                        <q-item v-if="lesson.data?.duration">
                                            <q-item-section avatar>
                                                <q-icon name="schedule" color="blue" />
                                            </q-item-section>
                                            <q-item-section>
                                                <q-item-label>Duration</q-item-label>
                                                <q-item-label caption>{{ lesson.data.duration }} minutes</q-item-label>
                                            </q-item-section>
                                        </q-item>
                                        
                                        <q-item v-if="lesson.data?.difficulty">
                                            <q-item-section avatar>
                                                <q-icon 
                                                    name="trending_up" 
                                                    :color="getDifficultyIconColor(lesson.data.difficulty)" 
                                                />
                                            </q-item-section>
                                            <q-item-section>
                                                <q-item-label>Difficulty</q-item-label>
                                                <q-item-label caption>
                                                    <q-chip 
                                                        size="sm" 
                                                        :color="getDifficultyColor(lesson.data.difficulty)" 
                                                        :text-color="getDifficultyTextColor(lesson.data.difficulty)"
                                                    >
                                                        {{ lesson.data.difficulty }}
                                                    </q-chip>
                                                </q-item-label>
                                            </q-item-section>
                                        </q-item>
                                        
                                        <q-item>
                                            <q-item-section avatar>
                                                <q-icon name="person" color="purple" />
                                            </q-item-section>
                                            <q-item-section>
                                                <q-item-label>Created By</q-item-label>
                                                <q-item-label caption>{{ lesson.creator?.name || 'Unknown' }}</q-item-label>
                                            </q-item-section>
                                        </q-item>
                                        
                                        <q-item>
                                            <q-item-section avatar>
                                                <q-icon name="calendar_today" color="grey" />
                                            </q-item-section>
                                            <q-item-section>
                                                <q-item-label>Created</q-item-label>
                                                <q-item-label caption>{{ formatDateTime(lesson.created_at) }}</q-item-label>
                                            </q-item-section>
                                        </q-item>
                                    </q-list>
                                </q-card-section>
                            </q-card>

                            <!-- Navigation -->
                            <q-card flat bordered class="q-mt-md">
                                <q-card-section>
                                    <div class="text-subtitle1 q-mb-md">Navigation</div>
                                    
                                    <q-list>
                                        <q-item 
                                            clickable
                                            @click="$inertia.visit(route('course-management.sections.show', lesson.section.id))"
                                        >
                                            <q-item-section avatar>
                                                <q-icon name="menu_book" color="green" />
                                            </q-item-section>
                                            <q-item-section>
                                                <q-item-label>{{ lesson.section?.title }}</q-item-label>
                                                <q-item-label caption>Section</q-item-label>
                                            </q-item-section>
                                        </q-item>
                                        
                                        <q-item 
                                            clickable
                                            @click="$inertia.visit(route('course-management.levels.show', lesson.section.level.id))"
                                        >
                                            <q-item-section avatar>
                                                <q-icon name="layers" color="blue" />
                                            </q-item-section>
                                            <q-item-section>
                                                <q-item-label>{{ lesson.section?.level?.title }}</q-item-label>
                                                <q-item-label caption>Level</q-item-label>
                                            </q-item-section>
                                        </q-item>
                                        
                                        <q-item 
                                            clickable
                                            @click="$inertia.visit(route('course-management.courses.show', lesson.section.level.course.id))"
                                        >
                                            <q-item-section avatar>
                                                <q-icon name="school" color="primary" />
                                            </q-item-section>
                                            <q-item-section>
                                                <q-item-label>{{ lesson.section?.level?.course?.name }}</q-item-label>
                                                <q-item-label caption>Course</q-item-label>
                                            </q-item-section>
                                        </q-item>
                                    </q-list>
                                </q-card-section>
                            </q-card>
                        </div>
                    </div>
                </q-card-section>

                <q-separator />

                <!-- Actions -->
                <q-card-section>
                    <div class="row justify-end q-gutter-sm">
                        <q-btn 
                            flat 
                            color="grey-7" 
                            icon="arrow_back" 
                            label="Back to Section" 
                            @click="$inertia.visit(route('course-management.sections.show', lesson.section.id))"
                        />
                        <q-btn 
                            color="orange" 
                            icon="edit" 
                            label="Edit Lesson" 
                            @click="$inertia.visit(route('course-management.lessons.edit', lesson.id))"
                        />
                        <q-btn 
                            color="negative" 
                            icon="delete" 
                            label="Delete Lesson" 
                            @click="deleteLesson"
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
                    <span class="q-ml-sm">Are you sure you want to delete this lesson?</span>
                </q-card-section>
                <q-card-section>
                    <div class="text-subtitle2">Lesson: {{ lesson.title }}</div>
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
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useQuasar } from 'quasar';

const props = defineProps({
    lesson: {
        type: Object,
        required: true,
    }
});

const $q = useQuasar();
const showDeleteDialog = ref(false);

const formatDateTime = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const getDifficultyColor = (difficulty) => {
    const colors = {
        'beginner': 'green-1',
        'intermediate': 'orange-1',
        'advanced': 'red-1'
    };
    return colors[difficulty] || 'grey-1';
};

const getDifficultyTextColor = (difficulty) => {
    const colors = {
        'beginner': 'green-8',
        'intermediate': 'orange-8',
        'advanced': 'red-8'
    };
    return colors[difficulty] || 'grey-8';
};

const getDifficultyIconColor = (difficulty) => {
    const colors = {
        'beginner': 'green',
        'intermediate': 'orange',
        'advanced': 'red'
    };
    return colors[difficulty] || 'grey';
};

const deleteLesson = () => {
    showDeleteDialog.value = true;
};

const confirmDelete = () => {
    router.delete(route('course-management.lessons.destroy', props.lesson.id), {
        onSuccess: () => {
            $q.notify({
                type: 'positive',
                message: 'Lesson deleted successfully.',
                icon: 'check_circle'
            });
            showDeleteDialog.value = false;
        },
        onError: () => {
            $q.notify({
                type: 'negative',
                message: 'Failed to delete lesson.',
                icon: 'error'
            });
        }
    });
};
</script>

<style scoped>
.q-item {
    padding-left: 0;
}
</style>