<template>
    <AppLayout title="Edit Course">
        <div class="q-pa-md">
            <q-card flat bordered>
                <q-card-section>
                    <div class="row items-center justify-between">
                        <div>
                            <div class="text-h6">Edit Course</div>
                            <div class="text-subtitle2">Update course information.</div>
                        </div>
                        <div class="q-gutter-sm">
                            <q-btn 
                                flat 
                                color="grey-7" 
                                icon="arrow_back" 
                                label="Back to Course" 
                                @click="$inertia.visit(route('course-management.courses.show', course.id))"
                            />
                        </div>
                    </div>
                </q-card-section>

                <q-separator />

                <q-form @submit="submitForm" class="q-pa-md">
                    <div class="row q-col-gutter-md">
                        <div class="col-12 col-md-8">
                            <q-card flat bordered>
                                <q-card-section>
                                    <div class="text-subtitle1 q-mb-md">Course Information</div>
                                    
                                    <div class="q-mb-md">
                                        <q-input
                                            v-model="form.name"
                                            label="Course Name *"
                                            outlined
                                            :error="!!errors.name"
                                            :error-message="errors.name"
                                            placeholder="e.g., Grade 5 Mathematics"
                                        />
                                    </div>

                                    <div class="q-mb-md">
                                        <q-input
                                            v-model="form.description"
                                            label="Course Description"
                                            outlined
                                            type="textarea"
                                            rows="4"
                                            :error="!!errors.description"
                                            :error-message="errors.description"
                                            placeholder="Describe what this course covers..."
                                        />
                                    </div>
                                </q-card-section>
                            </q-card>
                        </div>

                        <div class="col-12 col-md-4">
                            <q-card flat bordered>
                                <q-card-section>
                                    <div class="text-subtitle1 q-mb-md">Course Preview</div>
                                    
                                    <div class="course-preview">
                                        <div class="text-h6 text-primary">
                                            {{ form.name || 'Course Name' }}
                                        </div>
                                        <div class="text-caption text-grey-7 q-mt-xs">
                                            {{ form.description || 'Course description will appear here...' }}
                                        </div>
                                        
                                        <div class="q-mt-md">
                                            <q-chip size="sm" color="blue-1" text-color="blue-8" icon="layers">
                                                {{ course.levels?.length || 0 }} Levels
                                            </q-chip>
                                            <q-chip size="sm" color="green-1" text-color="green-8" icon="menu_book" class="q-ml-xs">
                                                {{ totalSections }} Sections
                                            </q-chip>
                                        </div>
                                    </div>
                                </q-card-section>
                            </q-card>

                            <q-card flat bordered class="q-mt-md">
                                <q-card-section>
                                    <div class="text-subtitle1 q-mb-md">Course Stats</div>
                                    <q-list dense>
                                        <q-item>
                                            <q-item-section avatar>
                                                <q-icon name="layers" color="blue" />
                                            </q-item-section>
                                            <q-item-section>
                                                <q-item-label>{{ course.levels?.length || 0 }} Levels</q-item-label>
                                            </q-item-section>
                                        </q-item>
                                        <q-item>
                                            <q-item-section avatar>
                                                <q-icon name="menu_book" color="green" />
                                            </q-item-section>
                                            <q-item-section>
                                                <q-item-label>{{ totalSections }} Sections</q-item-label>
                                            </q-item-section>
                                        </q-item>
                                        <q-item>
                                            <q-item-section avatar>
                                                <q-icon name="school" color="orange" />
                                            </q-item-section>
                                            <q-item-section>
                                                <q-item-label>{{ totalLessons }} Lessons</q-item-label>
                                            </q-item-section>
                                        </q-item>
                                        <q-item>
                                            <q-item-section avatar>
                                                <q-icon name="person" color="purple" />
                                            </q-item-section>
                                            <q-item-section>
                                                <q-item-label>{{ course.creator?.name || 'Unknown' }}</q-item-label>
                                                <q-item-label caption>Creator</q-item-label>
                                            </q-item-section>
                                        </q-item>
                                    </q-list>
                                </q-card-section>
                            </q-card>
                        </div>
                    </div>

                    <q-separator class="q-my-md" />

                    <div class="row justify-end q-gutter-sm">
                        <q-btn 
                            flat 
                            label="Cancel" 
                            color="grey-7" 
                            @click="$inertia.visit(route('course-management.courses.show', course.id))"
                        />
                        <q-btn 
                            type="submit" 
                            label="Update Course" 
                            color="primary" 
                            icon="save"
                            :loading="processing"
                        />
                    </div>
                </q-form>
            </q-card>
        </div>
    </AppLayout>
</template>

<script setup>
import { reactive, ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useQuasar } from 'quasar';

const props = defineProps({
    course: {
        type: Object,
        required: true,
    },
    errors: {
        type: Object,
        default: () => ({}),
    }
});

const $q = useQuasar();

// Form state
const form = reactive({
    name: props.course.name,
    description: props.course.description || '',
});

const processing = ref(false);

// Computed properties
const totalSections = computed(() => {
    if (!props.course.levels) return 0;
    return props.course.levels.reduce((total, level) => {
        return total + (level.sections?.length || 0);
    }, 0);
});

const totalLessons = computed(() => {
    if (!props.course.levels) return 0;
    return props.course.levels.reduce((total, level) => {
        if (!level.sections) return total;
        return total + level.sections.reduce((sectionTotal, section) => {
            return sectionTotal + (section.lessons?.length || 0);
        }, 0);
    }, 0);
});

// Methods
const submitForm = () => {
    processing.value = true;
    
    router.put(route('course-management.courses.update', props.course.id), form, {
        onSuccess: () => {
            $q.notify({
                type: 'positive',
                message: 'Course updated successfully!',
                icon: 'check_circle'
            });
        },
        onError: (errors) => {
            $q.notify({
                type: 'negative',
                message: 'Please check the form for errors.',
                icon: 'error'
            });
        },
        onFinish: () => {
            processing.value = false;
        }
    });
};
</script>

<style scoped>
.course-preview {
    padding: 16px;
    border: 1px dashed #e0e0e0;
    border-radius: 8px;
    background-color: #fafafa;
}
</style>