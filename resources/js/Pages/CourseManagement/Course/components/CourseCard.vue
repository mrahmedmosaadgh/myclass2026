<template>
    <q-card class="course-card" bordered>
        <q-card-section>
            <div class="row items-start justify-between">
                <div class="col">
                    <div class="text-h6 text-primary">{{ course.name }}</div>
                    <div class="text-caption text-grey-7 q-mt-xs" v-if="course.description">
                        {{ truncatedDescription }}
                    </div>
                </div>
                <q-btn-dropdown 
                    flat 
                    dense 
                    round 
                    icon="more_vert"
                    dropdown-icon=""
                >
                    <q-list>
                        <q-item 
                            clickable 
                            v-close-popup 
                            @click="$inertia.visit(route('course-management.courses.show', course.id))"
                        >
                            <q-item-section avatar>
                                <q-icon name="visibility" color="primary" />
                            </q-item-section>
                            <q-item-section>View Details</q-item-section>
                        </q-item>
                        <q-item clickable v-close-popup @click="$emit('edit', course)">
                            <q-item-section avatar>
                                <q-icon name="edit" color="orange" />
                            </q-item-section>
                            <q-item-section>Edit Course</q-item-section>
                        </q-item>
                        <q-separator />
                        <q-item clickable v-close-popup @click="$emit('delete', course)">
                            <q-item-section avatar>
                                <q-icon name="delete" color="negative" />
                            </q-item-section>
                            <q-item-section>Delete Course</q-item-section>
                        </q-item>
                    </q-list>
                </q-btn-dropdown>
            </div>
        </q-card-section>

        <q-card-section class="q-pt-none">
            <div class="row q-col-gutter-sm">
                <div class="col-6">
                    <q-chip 
                        size="sm" 
                        color="blue-1" 
                        text-color="blue-8" 
                        icon="layers"
                    >
                        {{ course.levels?.length || 0 }} Levels
                    </q-chip>
                </div>
                <div class="col-6">
                    <q-chip 
                        size="sm" 
                        color="green-1" 
                        text-color="green-8" 
                        icon="menu_book"
                    >
                        {{ totalSections }} Sections
                    </q-chip>
                </div>
            </div>
        </q-card-section>

        <q-card-section class="q-pt-none">
            <div class="text-caption text-grey-6">
                <div>Created by: {{ course.creator?.name || 'Unknown' }}</div>
                <div>{{ formatDate(course.created_at) }}</div>
            </div>
        </q-card-section>

        <q-card-actions align="right">
            <q-btn 
                flat 
                color="primary" 
                label="View" 
                @click="$inertia.visit(route('course-management.courses.show', course.id))"
            />
            <q-btn 
                flat 
                color="orange" 
                label="Edit" 
                @click="$emit('edit', course)"
            />
        </q-card-actions>
    </q-card>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    course: {
        type: Object,
        required: true,
    }
});

defineEmits(['edit', 'delete']);

const truncatedDescription = computed(() => {
    if (!props.course.description) return '';
    return props.course.description.length > 100 
        ? props.course.description.substring(0, 100) + '...'
        : props.course.description;
});

const totalSections = computed(() => {
    if (!props.course.levels) return 0;
    return props.course.levels.reduce((total, level) => {
        return total + (level.sections?.length || 0);
    }, 0);
});

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};
</script>

<style scoped>
.course-card {
    height: 100%;
    transition: transform 0.2s, box-shadow 0.2s;
}

.course-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.1);
}
</style>