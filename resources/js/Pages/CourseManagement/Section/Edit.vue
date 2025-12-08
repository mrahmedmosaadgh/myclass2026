<template>
    <AppLayout title="Edit Section">
        <div class="q-pa-md">
            <q-card flat bordered>
                <q-card-section>
                    <div class="row items-center justify-between">
                        <div>
                            <div class="text-h6">Edit Section</div>
                            <div class="text-subtitle2">Update section information for "{{ section.level?.title }}"</div>
                        </div>
                        <q-btn 
                            flat 
                            color="grey-7" 
                            icon="arrow_back" 
                            label="Back to Section" 
                            @click="$inertia.visit(route('course-management.sections.show', section.id))"
                        />
                    </div>
                </q-card-section>

                <q-separator />

                <q-form @submit="submitForm" class="q-pa-md">
                    <div class="row q-col-gutter-md">
                        <div class="col-12 col-md-8">
                            <q-card flat bordered>
                                <q-card-section>
                                    <div class="text-subtitle1 q-mb-md">Section Information</div>
                                    
                                    <div class="q-mb-md">
                                        <q-input
                                            v-model="form.title"
                                            label="Section Title *"
                                            outlined
                                            :error="!!errors.title"
                                            :error-message="errors.title"
                                            placeholder="e.g., Adding Fractions, Basic Shapes"
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
                                            hint="Position of this section in the level"
                                        />
                                    </div>
                                </q-card-section>
                            </q-card>
                        </div>

                        <div class="col-12 col-md-4">
                            <q-card flat bordered>
                                <q-card-section>
                                    <div class="text-subtitle1 q-mb-md">Level Context</div>
                                    
                                    <div class="level-context">
                                        <div class="text-h6 text-primary">
                                            {{ section.level?.title }}
                                        </div>
                                        <div class="text-caption text-grey-7 q-mt-xs">
                                            Level in "{{ section.level?.course?.name }}"
                                        </div>
                                    </div>
                                </q-card-section>
                            </q-card>

                            <q-card flat bordered class="q-mt-md">
                                <q-card-section>
                                    <div class="text-subtitle1 q-mb-md">Section Stats</div>
                                    <q-list dense>
                                        <q-item>
                                            <q-item-section avatar>
                                                <q-icon name="school" color="orange" />
                                            </q-item-section>
                                            <q-item-section>
                                                <q-item-label>{{ section.lessons?.length || 0 }} Lessons</q-item-label>
                                            </q-item-section>
                                        </q-item>
                                        <q-item>
                                            <q-item-section avatar>
                                                <q-icon name="person" color="purple" />
                                            </q-item-section>
                                            <q-item-section>
                                                <q-item-label>{{ section.creator?.name || 'Unknown' }}</q-item-label>
                                                <q-item-label caption>Creator</q-item-label>
                                            </q-item-section>
                                        </q-item>
                                    </q-list>
                                </q-card-section>
                            </q-card>

                            <q-card flat bordered class="q-mt-md">
                                <q-card-section>
                                    <div class="text-subtitle1 q-mb-md">Section Preview</div>
                                    <div class="section-preview">
                                        <div class="text-subtitle1 text-primary">
                                            {{ form.title || 'Section Title' }}
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
                            @click="$inertia.visit(route('course-management.sections.show', section.id))"
                        />
                        <q-btn 
                            type="submit" 
                            label="Update Section" 
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
import { reactive, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useQuasar } from 'quasar';

const props = defineProps({
    section: {
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
    title: props.section.title,
    order: props.section.order,
});

const processing = ref(false);

// Methods
const submitForm = () => {
    processing.value = true;
    
    router.put(route('course-management.sections.update', props.section.id), form, {
        onSuccess: () => {
            $q.notify({
                type: 'positive',
                message: 'Section updated successfully!',
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
.level-context, .section-preview {
    padding: 16px;
    border: 1px dashed #e0e0e0;
    border-radius: 8px;
    background-color: #fafafa;
}
</style>