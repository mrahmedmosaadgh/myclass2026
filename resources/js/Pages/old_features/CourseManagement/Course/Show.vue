<template>
    <AppLayout :title="course.name">
        <div class="q-pa-md"> 
            <q-card flat bordered>
                <q-card-section>
                    <div class="row items-center justify-between">
                        <div>
                            <div class="text-h5">{{ course.name }}</div>
                            <div class="text-subtitle2 text-grey-7">{{ course.description || 'No description provided' }}</div>
                        </div>
                        <div class="q-gutter-sm">
                            <q-btn 
                                flat 
                                color="grey-7" 
                                icon="arrow_back" 
                                label="Back to Courses" 
                                @click="$inertia.visit(route('course-management.courses.index'))"
                            />
                            <q-btn 
                                color="orange" 
                                icon="edit" 
                                label="Edit Course" 
                                @click="$inertia.visit(route('course-management.courses.edit', course.id))"
                            />
                            <q-btn 
                                color="primary" 
                                icon="add" 
                                label="Add Level" 
                                @click="$inertia.visit(route('course-management.courses.levels.create', course.id))"
                            />
                        </div>
                    </div>
                </q-card-section>

                <!-- Course Stats -->
                <q-card-section class="row q-col-gutter-md">
                    <div class="col-12 col-sm-6 col-md-3">
                        <q-card class="bg-blue-1 text-center">
                            <q-card-section>
                                <div class="text-h5 text-bold text-blue-8">{{ course.levels?.length || 0 }}</div>
                                <div class="text-subtitle2 text-grey-8">Levels</div>
                            </q-card-section>
                        </q-card>
                    </div>
                    <div class="col-12 col-sm-6 col-md-3">
                        <q-card class="bg-green-1 text-center">
                            <q-card-section>
                                <div class="text-h5 text-bold text-green-8">{{ totalSections }}</div>
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
                        <q-card class="bg-purple-1 text-center">
                            <q-card-section>
                                <div class="text-h5 text-bold text-purple-8">{{ formatDate(course.created_at) }}</div>
                                <div class="text-subtitle2 text-grey-8">Created</div>
                            </q-card-section>
                        </q-card>
                    </div>
                </q-card-section>

                <q-separator />

                <!-- Course Content -->
                <q-card-section>
                    <div class="row items-center justify-between q-mb-md">
                        <div class="text-h6">Course Structure</div>
                        <q-toggle
                            v-model="showSections"
                            label="Show Sections"
                            color="primary"
                            left-label
                        />
                    </div>
                    
                    <div v-if="course.levels && course.levels.length > 0" class="row">
                        <!-- Left side: Tree navigation -->
                        <div class="col-12 col-md-4 course-tree">
                            <q-tree
                                :nodes="courseTreeData"
                                node-key="id"
                                :selected.sync="selectedNodeId"
                                @update:selected="handleNodeSelection"
                            >
                                <!-- Level node template -->
                                <template v-slot:header-level="prop">
                                    <div class="row items-center justify-between full-width">
                                        <div class="flex items-center">
                                            <q-icon name="layers" size="sm" class="q-mr-xs text-blue-9" />
                                            <span class="text-weight-medium">{{ prop.node.label }}</span>
                                        </div>
                                        <div>
                                            <q-btn flat dense round size="sm" icon="edit" color="blue-9" 
                                                @click.stop="$inertia.visit(route('course-management.levels.edit', prop.node.levelId))" />
                                            <q-btn flat dense round size="sm" icon="add" color="blue-9" 
                                                @click.stop="$inertia.visit(route('course-management.levels.sections.create', prop.node.levelId))" />
                                        </div>
                                    </div>
                                </template>
                                
                                <!-- Section separator template -->
                                <template v-slot:header-section="prop">
                                    <div class="row items-center justify-between full-width section-header">
                                        <div class="flex items-center">
                                            <q-icon name="folder" size="sm" class="q-mr-xs text-grey-8" />
                                            <span class="text-grey-8">{{ prop.node.label }}</span>
                                        </div>
                                        <div>
                                            <q-btn flat dense round size="xs" icon="edit" color="grey-8" 
                                                @click.stop="$inertia.visit(route('course-management.sections.edit', prop.node.sectionId))" />
                                            <q-btn flat dense round size="xs" icon="add" color="grey-8" 
                                                @click.stop="$inertia.visit(route('course-management.sections.lessons.create', prop.node.sectionId))" />
                                        </div>
                                    </div>
                                </template>
                                
                                <!-- Lesson node template -->
                                <template v-slot:header-lesson="prop">
                                    <div class="row items-center justify-between full-width">
                                        <div class="flex items-center">
                                            <q-icon name="play_lesson" size="sm" class="q-mr-xs" 
                                                :color="selectedLesson && selectedLesson.id === prop.node.lessonId ? 'blue-9' : 'grey-7'" />
                                            <span :class="{'text-blue-9 text-weight-medium': selectedLesson && selectedLesson.id === prop.node.lessonId}">
                                                {{ prop.node.label }}
                                            </span>
                                        </div>
                                        <q-btn flat dense round size="sm" icon="edit" color="orange" 
                                            @click.stop="$inertia.visit(route('course-management.lessons.edit', prop.node.lessonId))" />
                                    </div>
                                </template>
                            </q-tree>
                            
                            <!-- Add Level button -->
                            <div class="q-mt-md text-center">
                                <q-btn 
                                    color="primary" 
                                    icon="add" 
                                    label="Add Level" 
                                    @click="$inertia.visit(route('course-management.courses.levels.create', course.id))"
                                />
                            </div>
                        </div>
                        
                        <!-- Right side: Lesson content -->
                        <div class="col-12 col-md-8 q-pl-md-lg">
                            <q-card v-if="selectedLesson" flat bordered>
                                <q-card-section>
                                    <div class="text-h6">{{ selectedLesson.title }}</div>
                                    <div class="text-caption text-grey-7 q-mb-md">
                                        Last updated: {{ formatDateTime(selectedLesson.updated_at) }}
                                    </div>
                                    
                                    <div class="lesson-content q-pa-md bg-grey-1 rounded-borders">
                                        <div v-if="selectedLesson.text" v-html="selectedLesson.text"></div>
                                        <div v-else class="text-grey-6 text-center q-pa-md">
                                            No content available for this lesson.
                                        </div>
                                    </div>
                                    
                                    <div class="q-mt-md text-right">
                                        <q-btn 
                                            color="primary" 
                                            icon="edit" 
                                            label="Edit Lesson" 
                                            @click="$inertia.visit(route('course-management.lessons.edit', selectedLesson.id))"
                                        />
                                    </div>
                                </q-card-section>
                            </q-card>
                            
                            <div v-else class="flex flex-center full-height text-grey-6 q-pa-xl">
                                <div class="text-center">
                                    <q-icon name="school" size="4em" />
                                    <p class="q-mt-md">Select a lesson to view its content</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Empty course message -->
                    <div v-else class="text-center text-grey-6 q-pa-xl">
                        <q-icon name="layers" size="4em" />
                        <p class="q-mt-md">No levels yet. Start building your course structure!</p>
                        <q-btn 
                            color="primary" 
                            label="Add First Level" 
                            icon="add"
                            @click="$inertia.visit(route('course-management.courses.levels.create', course.id))"
                        />
                    </div>
                </q-card-section>

                <q-separator />

                <!-- Course Info -->
                <q-card-section>
                    <div class="text-subtitle1 q-mb-md">Course Information</div>
                    <div class="row q-col-gutter-md">
                        <div class="col-12 col-md-6">
                            <q-list>
                                <q-item>
                                    <q-item-section avatar>
                                        <q-icon name="person" color="primary" />
                                    </q-item-section>
                                    <q-item-section>
                                        <q-item-label>Created By</q-item-label>
                                        <q-item-label caption>{{ course.creator?.name || 'Unknown' }}</q-item-label>
                                    </q-item-section>
                                </q-item>
                                <q-item>
                                    <q-item-section avatar>
                                        <q-icon name="calendar_today" color="primary" />
                                    </q-item-section>
                                    <q-item-section>
                                        <q-item-label>Created Date</q-item-label>
                                        <q-item-label caption>{{ formatDateTime(course.created_at) }}</q-item-label>
                                    </q-item-section>
                                </q-item>
                            </q-list>
                        </div>
                        <div class="col-12 col-md-6">
                            <q-list>
                                <q-item>
                                    <q-item-section avatar>
                                        <q-icon name="update" color="primary" />
                                    </q-item-section>
                                    <q-item-section>
                                        <q-item-label>Last Updated</q-item-label>
                                        <q-item-label caption>{{ formatDateTime(course.updated_at) }}</q-item-label>
                                    </q-item-section>
                                </q-item>
                                <q-item>
                                    <q-item-section avatar>
                                        <q-icon name="tag" color="primary" />
                                    </q-item-section>
                                    <q-item-section>
                                        <q-item-label>Course ID</q-item-label>
                                        <q-item-label caption>#{{ course.id }}</q-item-label>
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
import { computed, ref, watch } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    course: {
        type: Object,
        required: true,
    }
});

// Track the selected lesson for display
const selectedLesson = ref(null);
const selectedNodeId = ref(null);
const showSections = ref(false); // Default to hiding sections

// Transform course data into tree structure
const courseTreeData = computed(() => {
    if (!props.course.levels) return [];
    
    return props.course.levels.map(level => {
        const levelNode = {
            id: `level-${level.id}`,
            label: level.title,
            levelId: level.id,
            icon: 'layers',
            nodeType: 'level',
            children: [],
            header: 'level'
        };
        
        if (level.sections && level.sections.length > 0) {
            // Group all lessons by section
            const lessonsBySection = {};
            
            level.sections.forEach(section => {
                if (showSections.value && section.title) {
                    // When showing sections and section has a title, create a section node as a separator
                    const sectionNode = {
                        id: `section-${section.id}`,
                        label: section.title,
                        sectionId: section.id,
                        icon: 'folder',
                        nodeType: 'section',
                        header: 'section',
                        children: []
                    };
                    
                    // Add lessons as children of the section
                    if (section.lessons && section.lessons.length > 0) {
                        sectionNode.children = section.lessons.map(lesson => ({
                            id: `lesson-${lesson.id}`,
                            label: lesson.title,
                            lessonId: lesson.id,
                            icon: 'play_lesson',
                            nodeType: 'lesson',
                            selectable: true,
                            header: 'lesson',
                            lessonData: lesson
                        }));
                    }
                    
                    levelNode.children.push(sectionNode);
                } else {
                    // When hiding sections or section has no title, add lessons directly to level
                    if (section.lessons && section.lessons.length > 0) {
                        // If showing sections is disabled but we want to group lessons, add section title as a prefix
                        const prefix = !showSections.value && section.title ? `${section.title}: ` : '';
                        
                        section.lessons.forEach(lesson => {
                            levelNode.children.push({
                                id: `lesson-${lesson.id}`,
                                label: prefix + lesson.title,
                                lessonId: lesson.id,
                                icon: 'play_lesson',
                                nodeType: 'lesson',
                                selectable: true,
                                header: 'lesson',
                                lessonData: lesson
                            });
                        });
                    }
                }
            });
        }
        
        return levelNode;
    });
});

// Function to handle node selection
const handleNodeSelection = (nodeId) => {
    if (nodeId && nodeId.startsWith('lesson-')) {
        // Find the lesson in the course data
        const lessonId = parseInt(nodeId.replace('lesson-', ''));
        
        props.course.levels.forEach(level => {
            level.sections.forEach(section => {
                const lesson = section.lessons.find(l => l.id === lessonId);
                if (lesson) {
                    selectedLesson.value = lesson;
                }
            });
        });
    }
};

// Legacy function for backward compatibility
const selectLesson = (lesson) => {
    selectedLesson.value = lesson;
    selectedNodeId.value = `lesson-${lesson.id}`;
};

// Watch for changes to showSections and preserve selected lesson
watch(showSections, () => {
    // Store current selection
    const currentLesson = selectedLesson.value;
    
    // Force tree to refresh by briefly setting selectedNodeId to null
    selectedNodeId.value = null;
    
    // Restore selection after a brief delay to allow tree to rebuild
    if (currentLesson) {
        setTimeout(() => {
            selectedNodeId.value = `lesson-${currentLesson.id}`;
        }, 50);
    }
});

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
.course-tree {
    border-right: 1px solid #e0e0e0;
}

.q-item.q-pl-lg {
    padding-left: 24px;
}

.q-col-md-8.q-pl-md-lg {
    padding-left: 24px;
}

.lesson-content {
    min-height: 200px;
}

/* Tree view styling */
:deep(.q-tree__node--parent) > .q-tree__node-header {
    padding: 8px;
    border-radius: 4px;
    background-color: #f5f5f5;
    margin-bottom: 4px;
}

:deep(.q-tree__node--child) > .q-tree__node-header {
    padding: 6px 8px;
    border-radius: 4px;
}

:deep(.q-tree__node--child) > .q-tree__node-header:hover {
    background-color: #e3f2fd;
}

:deep(.q-tree__node--selected) > .q-tree__node-header {
    background-color: #bbdefb;
}

:deep(.section-header) {
    background-color: #eeeeee;
    border-left: 3px solid #9e9e9e;
    padding-left: 8px;
    font-size: 0.9em;
    font-weight: 500;
}

@media (max-width: 1023px) {
    .course-tree {
        border-right: none;
        border-bottom: 1px solid #e0e0e0;
        margin-bottom: 16px;
        padding-bottom: 16px;
    }
    
    .q-col-md-8.q-pl-md-lg {
        padding-left: 0;
        margin-top: 16px;
    }
}
</style>