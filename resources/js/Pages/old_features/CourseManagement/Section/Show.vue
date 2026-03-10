<template>
    <AppLayout :title="section.title">
        <div class="q-pa-md">
            <q-card flat bordered>
                <q-card-section>
                    <div class="row items-center justify-between">
                        <div>
                            <div class="text-h5">{{ section.title }}</div>
                            <div class="text-subtitle2 text-grey-7">
                                Section in "{{ section.level?.title }}" - "{{ section.level?.course?.name }}"
                            </div>
                        </div>
                        <div class="q-gutter-sm">
                            <q-btn 
                                flat 
                                color="grey-7" 
                                icon="arrow_back" 
                                label="Back to Level" 
                                @click="$inertia.visit(route('course-management.levels.show', section.level.id))"
                            />
                            <q-btn 
                                color="orange" 
                                icon="edit" 
                                label="Edit Section" 
                                @click="$inertia.visit(route('course-management.sections.edit', section.id))"
                            />
                            <q-btn 
                                color="primary" 
                                icon="add" 
                                label="Add Lesson" 
                                @click="$inertia.visit(route('course-management.sections.lessons.create', section.id))"
                            />
                        </div>
                    </div>
                </q-card-section>

                <!-- Section Stats -->
                <q-card-section class="row q-col-gutter-md">
                    <div class="col-12 col-sm-6 col-md-3">
                        <q-card class="bg-orange-1 text-center">
                            <q-card-section>
                                <div class="text-h5 text-bold text-orange-8">{{ section.lessons?.length || 0 }}</div>
                                <div class="text-subtitle2 text-grey-8">Lessons</div>
                            </q-card-section>
                        </q-card>
                    </div>
                    <div class="col-12 col-sm-6 col-md-3">
                        <q-card class="bg-blue-1 text-center">
                            <q-card-section>
                                <div class="text-h5 text-bold text-blue-8">{{ section.order }}</div>
                                <div class="text-subtitle2 text-grey-8">Order</div>
                            </q-card-section>
                        </q-card>
                    </div>
                    <div class="col-12 col-sm-6 col-md-3">
                        <q-card class="bg-green-1 text-center">
                            <q-card-section>
                                <div class="text-h5 text-bold text-green-8">{{ averageDuration }}</div>
                                <div class="text-subtitle2 text-grey-8">Avg Duration</div>
                            </q-card-section>
                        </q-card>
                    </div>
                    <div class="col-12 col-sm-6 col-md-3">
                        <q-card class="bg-purple-1 text-center">
                            <q-card-section>
                                <div class="text-h5 text-bold text-purple-8">{{ formatDate(section.created_at) }}</div>
                                <div class="text-subtitle2 text-grey-8">Created</div>
                            </q-card-section>
                        </q-card>
                    </div>
                </q-card-section>

                <q-separator />

                <!-- Lessons -->
                <q-card-section>
                    <div class="text-h6 q-mb-md">Lessons</div>
                    
                    <div v-if="section.lessons && section.lessons.length > 0" class="q-gutter-md">
                        <q-card 
                            v-for="lesson in section.lessons" 
                            :key="lesson.id" 
                            flat 
                            bordered
                            class="lesson-card cursor-pointer"
                            @click="$inertia.visit(route('course-management.lessons.show', lesson.id))"
                        >
                            <q-card-section>
                                <div class="row items-start justify-between">
                                    <div class="col">
                                        <div class="text-h6">{{ lesson.order }}. {{ lesson.title }}</div>
                                        <div class="text-body2 text-grey-7 q-mt-xs" v-if="lesson.text">
                                            {{ lesson.text }}
                                        </div>
                                        
                                        <!-- Lesson metadata -->
                                        <div class="row q-col-gutter-sm q-mt-md" v-if="lesson.data">
                                            <div class="col-auto" v-if="lesson.data.duration">
                                                <q-chip size="sm" color="blue-1" text-color="blue-8" icon="schedule">
                                                    {{ lesson.data.duration }} min
                                                </q-chip>
                                            </div>
                                            <div class="col-auto" v-if="lesson.data.difficulty">
                                                <q-chip 
                                                    size="sm" 
                                                    :color="getDifficultyColor(lesson.data.difficulty)" 
                                                    :text-color="getDifficultyTextColor(lesson.data.difficulty)"
                                                    icon="trending_up"
                                                >
                                                    {{ lesson.data.difficulty }}
                                                </q-chip>
                                            </div>
                                            <div class="col-auto" v-if="lesson.data.objectives">
                                                <q-chip size="sm" color="green-1" text-color="green-8" icon="check_circle">
                                                    {{ lesson.data.objectives.length }} objectives
                                                </q-chip>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <q-btn-dropdown 
                                            flat 
                                            dense 
                                            round 
                                            icon="more_vert"
                                            dropdown-icon=""
                                            @click.stop
                                        >
                                            <q-list>
                                                <q-item 
                                                    clickable 
                                                    v-close-popup 
                                                    @click="$inertia.visit(route('course-management.lessons.show', lesson.id))"
                                                >
                                                    <q-item-section avatar>
                                                        <q-icon name="visibility" color="primary" />
                                                    </q-item-section>
                                                    <q-item-section>View Lesson</q-item-section>
                                                </q-item>
                                                <q-item 
                                                    clickable 
                                                    v-close-popup 
                                                    @click="$inertia.visit(route('course-management.lessons.edit', lesson.id))"
                                                >
                                                    <q-item-section avatar>
                                                        <q-icon name="edit" color="orange" />
                                                    </q-item-section>
                                                    <q-item-section>Edit Lesson</q-item-section>
                                                </q-item>
                                            </q-list>
                                        </q-btn-dropdown>
                                    </div>
                                </div>
                            </q-card-section>
                        </q-card>
                    </div>
                    
                    <div v-else class="text-center text-grey-6 q-pa-xl">
                        <q-icon name="school" size="4em" />
                        <p class="q-mt-md">No lessons yet. Start building this section!</p>
                        <q-btn 
                            color="primary" 
                            label="Add First Lesson" 
                            icon="add"
                            @click="$inertia.visit(route('course-management.sections.lessons.create', section.id))"
                        />
                    </div>
                </q-card-section>

                <q-separator />

                <!-- Section Info -->
                <q-card-section>
                    <div class="text-subtitle1 q-mb-md">Section Information</div>
                    <div class="row q-col-gutter-md">
                        <div class="col-12 col-md-6">
                            <q-list>
                                <q-item>
                                    <q-item-section avatar>
                                        <q-icon name="layers" color="primary" />
                                    </q-item-section>
                                    <q-item-section>
                                        <q-item-label>Level</q-item-label>
                                        <q-item-label caption>{{ section.level?.title }}</q-item-label>
                                    </q-item-section>
                                </q-item>
                                <q-item>
                                    <q-item-section avatar>
                                        <q-icon name="school" color="primary" />
                                    </q-item-section>
                                    <q-item-section>
                                        <q-item-label>Course</q-item-label>
                                        <q-item-label caption>{{ section.level?.course?.name }}</q-item-label>
                                    </q-item-section>
                                </q-item>
                            </q-list>
                        </div>
                        <div class="col-12 col-md-6">
                            <q-list>
                                <q-item>
                                    <q-item-section avatar>
                                        <q-icon name="person" color="primary" />
                                    </q-item-section>
                                    <q-item-section>
                                        <q-item-label>Created By</q-item-label>
                                        <q-item-label caption>{{ section.creator?.name || 'Unknown' }}</q-item-label>
                                    </q-item-section>
                                </q-item>
                                <q-item>
                                    <q-item-section avatar>
                                        <q-icon name="calendar_today" color="primary" />
                                    </q-item-section>
                                    <q-item-section>
                                        <q-item-label>Created Date</q-item-label>
                                        <q-item-label caption>{{ formatDateTime(section.created_at) }}</q-item-label>
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
    section: {
        type: Object,
        required: true,
    }
});

const averageDuration = computed(() => {
    if (!props.section.lessons || props.section.lessons.length === 0) return '0 min';
    
    const totalDuration = props.section.lessons.reduce((total, lesson) => {
        return total + (lesson.data?.duration || 0);
    }, 0);
    
    const avg = Math.round(totalDuration / props.section.lessons.length);
    return avg > 0 ? `${avg} min` : 'N/A';
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
</script>

<style scoped>
.lesson-card {
    transition: transform 0.2s, box-shadow 0.2s;
}

.lesson-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
</style>