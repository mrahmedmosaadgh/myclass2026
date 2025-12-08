<template>
    <AppLayout :title="level.title">
        <div class="q-pa-md">
            <q-card flat bordered>
                <q-card-section>
                    <div class="row items-center justify-between">
                        <div>
                            <div class="text-h5">{{ level.title }}</div>
                            <div class="text-subtitle2 text-grey-7">Level in "{{ level.course?.name }}"</div>
                        </div>
                        <div class="q-gutter-sm">
                            <q-btn 
                                flat 
                                color="grey-7" 
                                icon="arrow_back" 
                                label="Back to Course" 
                                @click="$inertia.visit(route('course-management.courses.show', level.course.id))"
                            />
                            <q-btn 
                                color="orange" 
                                icon="edit" 
                                label="Edit Level" 
                                @click="$inertia.visit(route('course-management.levels.edit', level.id))"
                            />
                            <q-btn 
                                color="primary" 
                                icon="add" 
                                label="Add Section" 
                                @click="$inertia.visit(route('course-management.levels.sections.create', level.id))"
                            />
                        </div>
                    </div>
                </q-card-section>

                <!-- Level Stats -->
                <q-card-section class="row q-col-gutter-md">
                    <div class="col-12 col-sm-6 col-md-3">
                        <q-card class="bg-green-1 text-center">
                            <q-card-section>
                                <div class="text-h5 text-bold text-green-8">{{ level.sections?.length || 0 }}</div>
                                <div class="text-subtitle2 text-grey-8">Sections</div>
                            </q-card-section>
                        </q-card>
                    </div>
                    <div class="col-12 col-sm-6 col-md-3">
                        <q-card class="bg-orange-1 text-center">
                            <q-card-section>
                                <div class="text-h5 text-bold text-orange-8">{{ totalLessons }}</div>
                                <div class="text-subtitle2 text-grey-8">Lessons</div>
                            </q-card-section>
                        </q-card>
                    </div>
                    <div class="col-12 col-sm-6 col-md-3">
                        <q-card class="bg-blue-1 text-center">
                            <q-card-section>
                                <div class="text-h5 text-bold text-blue-8">{{ level.order }}</div>
                                <div class="text-subtitle2 text-grey-8">Order</div>
                            </q-card-section>
                        </q-card>
                    </div>
                    <div class="col-12 col-sm-6 col-md-3">
                        <q-card class="bg-purple-1 text-center">
                            <q-card-section>
                                <div class="text-h5 text-bold text-purple-8">{{ formatDate(level.created_at) }}</div>
                                <div class="text-subtitle2 text-grey-8">Created</div>
                            </q-card-section>
                        </q-card>
                    </div>
                </q-card-section>

                <q-separator />

                <!-- Sections -->
                <q-card-section>
                    <div class="text-h6 q-mb-md">Sections</div>
                    
                    <div v-if="level.sections && level.sections.length > 0" class="q-gutter-md">
                        <q-card 
                            v-for="section in level.sections" 
                            :key="section.id" 
                            flat 
                            bordered
                            class="section-card"
                        >
                            <q-card-section>
                                <div class="row items-center justify-between">
                                    <div>
                                        <div class="text-h6">{{ section.title }}</div>
                                        <div class="text-caption text-grey-7">
                                            {{ section.lessons?.length || 0 }} lessons
                                        </div>
                                    </div>
                                    <div class="q-gutter-xs">
                                        <q-btn 
                                            size="sm" 
                                            color="primary" 
                                            icon="add" 
                                            label="Add Lesson" 
                                            @click="$inertia.visit(route('course-management.sections.lessons.create', section.id))"
                                        />
                                        <q-btn 
                                            size="sm" 
                                            flat 
                                            color="primary" 
                                            icon="visibility" 
                                            @click="$inertia.visit(route('course-management.sections.show', section.id))"
                                        />
                                        <q-btn 
                                            size="sm" 
                                            flat 
                                            color="orange" 
                                            icon="edit" 
                                            @click="$inertia.visit(route('course-management.sections.edit', section.id))"
                                        />
                                    </div>
                                </div>
                            </q-card-section>

                            <q-card-section v-if="section.lessons && section.lessons.length > 0" class="q-pt-none">
                                <div class="row q-col-gutter-sm">
                                    <div 
                                        v-for="lesson in section.lessons" 
                                        :key="lesson.id" 
                                        class="col-12 col-sm-6 col-md-4"
                                    >
                                        <q-card 
                                            flat 
                                            class="bg-blue-1 cursor-pointer lesson-card"
                                            @click="$inertia.visit(route('course-management.lessons.show', lesson.id))"
                                        >
                                            <q-card-section class="q-pa-sm">
                                                <div class="text-subtitle2 text-blue-8">
                                                    {{ lesson.order }}. {{ lesson.title }}
                                                </div>
                                                <div class="text-caption text-grey-7" v-if="lesson.text">
                                                    {{ truncateText(lesson.text, 50) }}
                                                </div>
                                            </q-card-section>
                                        </q-card>
                                    </div>
                                </div>
                            </q-card-section>

                            <q-card-section v-else class="q-pt-none">
                                <div class="text-center text-grey-6 q-pa-md">
                                    <q-icon name="school" size="2em" />
                                    <p>No lessons yet. Add your first lesson!</p>
                                </div>
                            </q-card-section>
                        </q-card>
                    </div>
                    
                    <div v-else class="text-center text-grey-6 q-pa-xl">
                        <q-icon name="menu_book" size="4em" />
                        <p class="q-mt-md">No sections yet. Start building this level!</p>
                        <q-btn 
                            color="primary" 
                            label="Add First Section" 
                            icon="add"
                            @click="$inertia.visit(route('course-management.levels.sections.create', level.id))"
                        />
                    </div>
                </q-card-section>

                <q-separator />

                <!-- Level Info -->
                <q-card-section>
                    <div class="text-subtitle1 q-mb-md">Level Information</div>
                    <div class="row q-col-gutter-md">
                        <div class="col-12 col-md-6">
                            <q-list>
                                <q-item>
                                    <q-item-section avatar>
                                        <q-icon name="school" color="primary" />
                                    </q-item-section>
                                    <q-item-section>
                                        <q-item-label>Course</q-item-label>
                                        <q-item-label caption>{{ level.course?.name }}</q-item-label>
                                    </q-item-section>
                                </q-item>
                                <q-item>
                                    <q-item-section avatar>
                                        <q-icon name="person" color="primary" />
                                    </q-item-section>
                                    <q-item-section>
                                        <q-item-label>Created By</q-item-label>
                                        <q-item-label caption>{{ level.creator?.name || 'Unknown' }}</q-item-label>
                                    </q-item-section>
                                </q-item>
                            </q-list>
                        </div>
                        <div class="col-12 col-md-6">
                            <q-list>
                                <q-item>
                                    <q-item-section avatar>
                                        <q-icon name="calendar_today" color="primary" />
                                    </q-item-section>
                                    <q-item-section>
                                        <q-item-label>Created Date</q-item-label>
                                        <q-item-label caption>{{ formatDateTime(level.created_at) }}</q-item-label>
                                    </q-item-section>
                                </q-item>
                                <q-item>
                                    <q-item-section avatar>
                                        <q-icon name="update" color="primary" />
                                    </q-item-section>
                                    <q-item-section>
                                        <q-item-label>Last Updated</q-item-label>
                                        <q-item-label caption>{{ formatDateTime(level.updated_at) }}</q-item-label>
                                    </q-item-section>
                                </q-item>
                            </q-list>
                        </div>
                    </div>
                </q-card-section>
            </q-card>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    level: {
        type: Object,
        required: true,
    }
});

const totalLessons = computed(() => {
    if (!props.level.sections) return 0;
    return props.level.sections.reduce((total, section) => {
        return total + (section.lessons?.length || 0);
    }, 0);
});

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric'
    });
};

const formatDateTime = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const truncateText = (text, length) => {
    if (!text) return '';
    return text.length > length ? text.substring(0, length) + '...' : text;
};
</script>

<style scoped>
.section-card {
    transition: transform 0.2s, box-shadow 0.2s;
}

.section-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.lesson-card {
    transition: transform 0.2s;
}

.lesson-card:hover {
    transform: scale(1.02);
}
</style>