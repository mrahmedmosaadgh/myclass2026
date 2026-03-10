<template>
    <Head title="Create Level" />
    <AppLayout title="Create Level">
        <div class="q-pa-md">
            <q-card flat bordered>
                <q-card-section>
                    <div class="row items-center justify-between">
                        <div>
                            <div class="text-h6">Create New Level</div>
                            <div class="text-subtitle2">Add a new level to "{{ course.name }}"</div>
                        </div>
                        <q-btn 
                            flat 
                            color="grey-7" 
                            icon="arrow_back" 
                            label="Back to Course" 
                            @click="$inertia.visit(route('course-management.courses.show', course.id))"
                        />
                    </div>
                </q-card-section>

                <q-separator />

                <q-form @submit="submitForm" class="q-pa-md">
                    <div class="row q-col-gutter-md">
                        <div class="col-12 col-md-8">
                            <q-card flat bordered>
                                <q-card-section>
                                    <div class="text-subtitle1 q-mb-md">Level Information</div>
                                    
                                    <div class="q-mb-md">
                                        <q-input
                                            v-model="form.title"
                                            label="Level Title *"
                                            outlined
                                            :error="!!errors.title"
                                            :error-message="errors.title"
                                            placeholder="e.g., Fractions, Algebra Basics"
                                        />
                                    </div>

                                    <div class="q-mb-md">
                                        <q-input
                                            v-model.number="form.order"
                                            label="Order (Optional)"
                                            outlined
                                            type="number"
                                            min="0"
                                            :error="!!errors.order"
                                            :error-message="errors.order"
                                            hint="Leave empty to add at the end"
                                        />
                                    </div>
                                </q-card-section>
                            </q-card>
                        </div>

                        <div class="col-12 col-md-4">
                            <q-card flat bordered>
                                <q-card-section>
                                    <div class="text-subtitle1 q-mb-md">Course Context</div>
                                    
                                    <div class="course-context">
                                        <div class="text-h6 text-primary">
                                            {{ course.name }}
                                        </div>
                                        <div class="text-caption text-grey-7 q-mt-xs">
                                            {{ course.description || 'No description' }}
                                        </div>
                                        
                                        <div class="q-mt-md">
                                            <q-chip size="sm" color="blue-1" text-color="blue-8" icon="layers">
                                                {{ course.levels?.length || 0 }} Existing Levels
                                            </q-chip>
                                        </div>
                                    </div>
                                </q-card-section>
                            </q-card>

                            <q-card flat bordered class="q-mt-md">
                                <q-card-section>
                                    <div class="text-subtitle1 q-mb-md">Level Preview</div>
                                    <div class="level-preview">
                                        <div class="text-subtitle1 text-primary">
                                            {{ form.title || 'Level Title' }}
                                        </div>
                                        <div class="text-caption text-grey-7">
                                            Order: {{ form.order || 'Auto' }}
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
                                                <q-item-label>Create Level</q-item-label>
                                                <q-item-label caption>Basic level information</q-item-label>
                                            </q-item-section>
                                        </q-item>
                                        <q-item>
                                            <q-item-section avatar>
                                                <q-icon name="radio_button_unchecked" color="grey-5" />
                                            </q-item-section>
                                            <q-item-section>
                                                <q-item-label>Add Sections</q-item-label>
                                                <q-item-label caption>Topic groups within this level</q-item-label>
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
                            @click="$inertia.visit(route('course-management.courses.show', course.id))"
                        />
                        <q-btn 
                            type="submit" 
                            label="Create Level" 
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
    title: '',
    order: null,
});

const processing = ref(false);

// Methods
const submitForm = () => {
    processing.value = true;
    
    router.post(route('course-management.courses.levels.store', props.course.id), form, {
        onSuccess: () => {
            $q.notify({
                type: 'positive',
                message: 'Level created successfully!',
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
.course-context, .level-preview {
    padding: 16px;
    border: 1px dashed #e0e0e0;
    border-radius: 8px;
    background-color: #fafafa;
}
</style>