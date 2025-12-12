<template>
    <AppLayout title="Edit Lesson">
        <div class="q-pa-md">
            <q-card flat bordered>
                <q-card-section>
                    <div class="row items-center justify-between">
                        <div>
                            <div class="text-h6">Edit Lesson</div>
                            <div class="text-subtitle2">Update lesson information for "{{ lesson.section?.title }}"</div>
                        </div>
                        <q-btn 
                            flat 
                            color="grey-7" 
                            icon="arrow_back" 
                            label="Back to Lesson" 
                            @click="$inertia.visit(route('course-management.lessons.show', lesson.id))"
                        />
                    </div>
                </q-card-section>

                <q-separator />

                <q-form @submit="submitForm" class="q-pa-md">
                    <div class="row q-col-gutter-md">
                        <div class="col-12 col-md-8">
                            <q-card flat bordered>
                                <q-card-section>
                                    <div class="text-subtitle1 q-mb-md">Lesson Information</div>
                                    
                                    <div class="q-mb-md">
                                        <q-input
                                            v-model="form.title"
                                            label="Lesson Title *"
                                            outlined
                                            :error="!!errors.title"
                                            :error-message="errors.title"
                                            placeholder="e.g., What is a Fraction?, Basic Addition"
                                        />
                                    </div>

                                    <div class="q-mb-md">
                                        <q-input
                                            v-model="form.text"
                                            label="Lesson Description"
                                            outlined
                                            type="textarea"
                                            rows="3"
                                            :error="!!errors.text"
                                            :error-message="errors.text"
                                            placeholder="Brief description of what this lesson covers..."
                                        />
                                    </div>

                                    <div class="q-mb-md">
                                        <q-input
                                            v-model.number="form.order"
                                            label="Order"
                                            outlined
                                            type="number"
                                            min="0"
                                            :error="!!errors.order"
                                            :error-message="errors.order"
                                            hint="Position of this lesson in the section"
                                        />
                                    </div>
                                </q-card-section>
                            </q-card>

                            <!-- Lesson Metadata -->
                            <q-card flat bordered class="q-mt-md">
                                <q-card-section>
                                    <div class="text-subtitle1 q-mb-md">Lesson Settings</div>
                                    
                                    <div class="row q-col-gutter-md">
                                        <div class="col-12 col-md-6">
                                            <q-input
                                                v-model.number="form.duration"
                                                label="Duration (minutes)"
                                                outlined
                                                type="number"
                                                min="1"
                                                placeholder="30"
                                            />
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <q-select
                                                v-model="form.difficulty"
                                                :options="difficultyOptions"
                                                outlined
                                                label="Difficulty Level"
                                                emit-value
                                                map-options
                                                clearable
                                            />
                                        </div>
                                    </div>

                                    <div class="q-mt-md">
                                        <q-input
                                            v-model="objectivesText"
                                            label="Learning Objectives"
                                            outlined
                                            type="textarea"
                                            rows="3"
                                            placeholder="Enter each objective on a new line..."
                                            hint="One objective per line"
                                        />
                                    </div>
                                </q-card-section>
                            </q-card>
                        </div>

                        <div class="col-12 col-md-4">
                            <q-card flat bordered>
                                <q-card-section>
                                    <div class="text-subtitle1 q-mb-md">Section Context</div>
                                    
                                    <div class="section-context">
                                        <div class="text-h6 text-primary">
                                            {{ lesson.section?.title }}
                                        </div>
                                        <div class="text-caption text-grey-7 q-mt-xs">
                                            Section in "{{ lesson.section?.level?.title }}"
                                        </div>
                                        <div class="text-caption text-grey-7">
                                            Course: "{{ lesson.section?.level?.course?.name }}"
                                        </div>
                                    </div>
                                </q-card-section>
                            </q-card>

                            <q-card flat bordered class="q-mt-md">
                                <q-card-section>
                                    <div class="text-subtitle1 q-mb-md">Lesson Preview</div>
                                    <div class="lesson-preview">
                                        <div class="text-subtitle1 text-primary">
                                            {{ form.title || 'Lesson Title' }}
                                        </div>
                                        <div class="text-caption text-grey-7 q-mt-xs" v-if="form.text">
                                            {{ form.text }}
                                        </div>
                                        
                                        <div class="q-mt-md row q-col-gutter-xs" v-if="form.duration || form.difficulty">
                                            <div class="col-auto" v-if="form.duration">
                                                <q-chip size="sm" color="blue-1" text-color="blue-8" icon="schedule">
                                                    {{ form.duration }} min
                                                </q-chip>
                                            </div>
                                            <div class="col-auto" v-if="form.difficulty">
                                                <q-chip 
                                                    size="sm" 
                                                    :color="getDifficultyColor(form.difficulty)" 
                                                    :text-color="getDifficultyTextColor(form.difficulty)"
                                                    icon="trending_up"
                                                >
                                                    {{ form.difficulty }}
                                                </q-chip>
                                            </div>
                                        </div>
                                        
                                        <div class="text-caption text-grey-7 q-mt-sm">
                                            Order: {{ form.order || 'Auto' }}
                                        </div>
                                    </div>
                                </q-card-section>
                            </q-card>

                            <q-card flat bordered class="q-mt-md" v-if="parsedObjectives.length > 0">
                                <q-card-section>
                                    <div class="text-subtitle1 q-mb-md">Learning Objectives</div>
                                    <q-list dense>
                                        <q-item v-for="(objective, index) in parsedObjectives" :key="index">
                                            <q-item-section avatar>
                                                <q-icon name="check_circle" color="positive" size="sm" />
                                            </q-item-section>
                                            <q-item-section>
                                                <q-item-label class="text-caption">{{ objective }}</q-item-label>
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
                            @click="$inertia.visit(route('course-management.lessons.show', lesson.id))"
                        />
                        <q-btn 
                            type="submit" 
                            label="Update Lesson" 
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
    lesson: {
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
    title: props.lesson.title,
    text: props.lesson.text || '',
    order: props.lesson.order,
    duration: props.lesson.data?.duration || null,
    difficulty: props.lesson.data?.difficulty || null,
});

const objectivesText = ref(
    props.lesson.data?.objectives ? props.lesson.data.objectives.join('\n') : ''
);
const processing = ref(false);

// Options
const difficultyOptions = [
    { label: 'Beginner', value: 'beginner' },
    { label: 'Intermediate', value: 'intermediate' },
    { label: 'Advanced', value: 'advanced' },
];

// Computed
const parsedObjectives = computed(() => {
    if (!objectivesText.value) return [];
    return objectivesText.value
        .split('\n')
        .map(obj => obj.trim())
        .filter(obj => obj.length > 0);
});

// Methods
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

const submitForm = () => {
    processing.value = true;
    
    // Prepare lesson data
    const lessonData = {
        title: form.title,
        text: form.text,
        order: form.order,
        data: {}
    };

    // Add metadata to data object
    if (form.duration) lessonData.data.duration = form.duration;
    if (form.difficulty) lessonData.data.difficulty = form.difficulty;
    if (parsedObjectives.value.length > 0) lessonData.data.objectives = parsedObjectives.value;

    router.put(route('course-management.lessons.update', props.lesson.id), lessonData, {
        onSuccess: () => {
            $q.notify({
                type: 'positive',
                message: 'Lesson updated successfully!',
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
.section-context, .lesson-preview {
    padding: 16px;
    border: 1px dashed #e0e0e0;
    border-radius: 8px;
    background-color: #fafafa;
}
</style>