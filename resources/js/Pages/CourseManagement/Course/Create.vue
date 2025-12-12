<template>
    <Head title="Create Course" />
    <AppLayout title="Create Course">
        <div class="q-pa-md">
            <q-card flat bordered>
                <q-card-section>
                    <div class="row items-center justify-between">
                        <div>
                            <div class="text-h6">Create New Course</div>
                            <div class="text-subtitle2">Add a new educational course to the system.</div>
                        </div>
                        <q-btn 
                            flat 
                            color="grey-7" 
                            icon="arrow_back" 
                            label="Back to Courses" 
                            @click="$inertia.visit(route('course-management.courses.index'))"
                        />
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
                                                0 Levels
                                            </q-chip>
                                            <q-chip size="sm" color="green-1" text-color="green-8" icon="menu_book" class="q-ml-xs">
                                                0 Sections
                                            </q-chip>
                                        </div>
                                    </div>
                                </q-card-section>
                            </q-card>

                            <q-card flat bordered class="q-mt-md">
                                <q-card-section>
                                    <div class="text-subtitle1 q-mb-md">Next Steps</div>
                                    <q-list dense>
                                        <q-item>
                                            <q-item-section avatar>
                                                <q-icon name="check_circle" color="positive" />
                                            </q-item-section>
                                            <q-item-section>
                                                <q-item-label>Create Course</q-item-label>
                                                <q-item-label caption>Basic course information</q-item-label>
                                            </q-item-section>
                                        </q-item>
                                        <q-item>
                                            <q-item-section avatar>
                                                <q-icon name="radio_button_unchecked" color="grey-5" />
                                            </q-item-section>
                                            <q-item-section>
                                                <q-item-label>Add Levels</q-item-label>
                                                <q-item-label caption>Major units of study</q-item-label>
                                            </q-item-section>
                                        </q-item>
                                        <q-item>
                                            <q-item-section avatar>
                                                <q-icon name="radio_button_unchecked" color="grey-5" />
                                            </q-item-section>
                                            <q-item-section>
                                                <q-item-label>Add Sections</q-item-label>
                                                <q-item-label caption>Topic groups within levels</q-item-label>
                                            </q-item-section>
                                        </q-item>
                                        <q-item>
                                            <q-item-section avatar>
                                                <q-icon name="radio_button_unchecked" color="grey-5" />
                                            </q-item-section>
                                            <q-item-section>
                                                <q-item-label>Add Lessons</q-item-label>
                                                <q-item-label caption>Individual lesson content</q-item-label>
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
                            @click="$inertia.visit(route('course-management.courses.index'))"
                        />
                        <q-btn 
                            type="submit" 
                            label="Create Course" 
                            color="primary" 
                            icon="add"
                            :loading="processing"
                        />
                    </div>
                </q-form>
            </q-card>
        </div>
    </AppLayout>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { router, Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useQuasar } from 'quasar';

const props = defineProps({
    errors: {
        type: Object,
        default: () => ({}),
    }
});

const $q = useQuasar();

// Form state
const form = reactive({
    name: '',
    description: '',
});

const processing = ref(false);

// Methods
const submitForm = () => {
    processing.value = true;
    
    router.post(route('course-management.courses.store'), form, {
        onSuccess: () => {
            $q.notify({
                type: 'positive',
                message: 'Course created successfully!',
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