<template>
    <AppLayout title="Edit Level">
        <div class="q-pa-md">
            <q-card flat bordered>
                <q-card-section>
                    <div class="row items-center justify-between">
                        <div>
                            <div class="text-h6">Edit Level</div>
                            <div class="text-subtitle2">Update level information for "{{ level.course?.name }}"</div>
                        </div>
                        <q-btn 
                            flat 
                            color="grey-7" 
                            icon="arrow_back" 
                            label="Back to Level" 
                            @click="$inertia.visit(route('course-management.levels.show', level.id))"
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
                                            label="Order"
                                            outlined
                                            type="number"
                                            min="0"
                                            :error="!!errors.order"
                                            :error-message="errors.order"
                                            hint="Position of this level in the course"
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
                                            {{ level.course?.name }}
                                        </div>
                                        <div class="text-caption text-grey-7 q-mt-xs">
                                            {{ level.course?.description || 'No description' }}
                                        </div>
                                    </div>
                                </q-card-section>
                            </q-card>

                            <q-card flat bordered class="q-mt-md">
                                <q-card-section>
                                    <div class="text-subtitle1 q-mb-md">Level Stats</div>
                                    <q-list dense>
                                        <q-item>
                                            <q-item-section avatar>
                                                <q-icon name="menu_book" color="green" />
                                            </q-item-section>
                                            <q-item-section>
                                                <q-item-label>{{ level.sections?.length || 0 }} Sections</q-item-label>
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
                                                <q-item-label>{{ level.creator?.name || 'Unknown' }}</q-item-label>
                                                <q-item-label caption>Creator</q-item-label>
                                            </q-item-section>
                                        </q-item>
                                    </q-list>
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
                        </div>
                    </div>

                    <q-separator class="q-my-md" />

                    <div class="row justify-end q-gutter-sm">
                        <q-btn 
                            flat 
                            label="Cancel" 
                            color="grey-7" 
                            @click="$inertia.visit(route('course-management.levels.show', level.id))"
                        />
                        <q-btn 
                            type="submit" 
                            label="Update Level" 
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
    level: {
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
    title: props.level.title,
    order: props.level.order,
});

const processing = ref(false);

// Computed properties
const totalLessons = computed(() => {
    if (!props.level.sections) return 0;
    return props.level.sections.reduce((total, section) => {
        return total + (section.lessons?.length || 0);
    }, 0);
});

// Methods
const submitForm = () => {
    processing.value = true;
    
    router.put(route('course-management.levels.update', props.level.id), form, {
        onSuccess: () => {
            $q.notify({
                type: 'positive',
                message: 'Level updated successfully!',
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