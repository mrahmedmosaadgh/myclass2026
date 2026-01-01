<template>
  <div>
    <q-card flat bordered>
      <q-card-section>
        <div class="text-h6 q-mb-md">Classroom Hierarchy</div>
        
        <!-- Empty State -->
        <div v-if="hierarchy.length === 0" class="text-center q-pa-xl">
          <q-icon name="folder_open" size="64px" color="grey-5" />
          <div class="text-body1 text-grey-7 q-mt-md">No stages found</div>
        </div>

        <!-- Hierarchy Tree -->
        <q-list v-else bordered separator>
          <q-expansion-item
            v-for="stage in hierarchy"
            :key="stage.id"
            :label="stage.name"
            icon="account_tree"
            header-class="text-primary text-weight-bold"
            expand-separator
          >
            <q-card flat>
              <q-card-section v-if="stage.grades.length === 0" class="text-grey-7">
                No grades in this stage
              </q-card-section>
              
              <q-list v-else>
                <q-expansion-item
                  v-for="grade in stage.grades"
                  :key="grade.id"
                  :label="grade.name"
                  icon="grade"
                  header-class="text-secondary"
                  dense
                >
                  <q-card flat>
                    <q-card-section v-if="grade.classrooms.length === 0" class="text-grey-7">
                      No classrooms in this grade
                    </q-card-section>
                    
                    <q-list v-else dense>
                      <q-item
                        v-for="classroom in grade.classrooms"
                        :key="classroom.id"
                        clickable
                        @click="showClassroomDetails(classroom)"
                      >
                        <q-item-section avatar>
                          <q-avatar color="accent" text-color="white" icon="class" />
                        </q-item-section>
                        
                        <q-item-section>
                          <q-item-label>{{ classroom.name }}</q-item-label>
                          <q-item-label caption>
                            Capacity: {{ classroom.capacity || 'N/A' }}
                          </q-item-label>
                        </q-item-section>
                        
                        <q-item-section side>
                          <q-badge color="primary" :label="`${classroom.assignments_count} assignments`" />
                        </q-item-section>
                        
                        <q-item-section side>
                          <q-icon name="chevron_right" color="grey-5" />
                        </q-item-section>
                      </q-item>
                    </q-list>
                  </q-card>
                </q-expansion-item>
              </q-list>
            </q-card>
          </q-expansion-item>
        </q-list>
      </q-card-section>
    </q-card>

    <!-- Classroom Details Dialog -->
    <q-dialog v-model="showDialog" :maximized="$q.screen.lt.md">
      <q-card style="min-width: 500px">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">{{ selectedClassroom?.name }}</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-card-section v-if="selectedClassroom">
          <div class="q-mb-md">
            <div class="text-subtitle2 text-grey-7">Capacity</div>
            <div class="text-h6">{{ selectedClassroom.capacity || 'N/A' }}</div>
          </div>

          <div class="q-mb-md">
            <div class="text-subtitle2 text-grey-7 q-mb-sm">Subjects ({{ selectedClassroom.subjects.length }})</div>
            <div class="row q-col-gutter-xs">
              <div v-for="(subject, index) in selectedClassroom.subjects" :key="index" class="col-auto">
                <q-chip color="primary" text-color="white" icon="menu_book">
                  {{ subject }}
                </q-chip>
              </div>
            </div>
          </div>

          <div>
            <div class="text-subtitle2 text-grey-7 q-mb-sm">Assignments</div>
            <q-list dense bordered separator>
              <q-item v-for="assignment in getClassroomAssignments(selectedClassroom.id)" :key="assignment.id">
                <q-item-section avatar>
                  <q-avatar
                    :style="{
                      backgroundColor: assignment.subject_color_bg,
                      color: assignment.subject_color_text
                    }"
                    icon="menu_book"
                  />
                </q-item-section>
                <q-item-section>
                  <q-item-label>{{ assignment.subject_name }}</q-item-label>
                  <q-item-label caption>{{ assignment.teacher_name }}</q-item-label>
                </q-item-section>
                <q-item-section side>
                  <q-badge color="primary" :label="`${assignment.classes_per_week} classes/week`" />
                </q-item-section>
              </q-item>
            </q-list>
          </div>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Close" color="primary" v-close-popup />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useQuasar } from 'quasar';

const $q = useQuasar();

const props = defineProps({
  hierarchy: {
    type: Array,
    required: true
  },
  assignments: {
    type: Array,
    required: true
  }
});

const showDialog = ref(false);
const selectedClassroom = ref(null);

const showClassroomDetails = (classroom) => {
  selectedClassroom.value = classroom;
  showDialog.value = true;
};

const getClassroomAssignments = (classroomId) => {
  return props.assignments.filter(a => a.classroom_id === classroomId);
};
</script>

<style scoped>
/* Add any custom styles here if needed */
</style>
