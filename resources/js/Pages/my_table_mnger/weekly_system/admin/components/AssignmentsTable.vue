<template>
  <div>
    <q-card flat bordered>
      <q-card-section>
        <div class="row items-center justify-between q-mb-md">
          <div class="text-h6">Classroom-Subject-Teacher Assignments</div>
          <q-toggle
            v-model="editMode"
            label="Edit Mode"
            color="primary"
            left-label
          />
        </div>
        
        <!-- Filters Row -->
        <div class="row q-col-gutter-md q-mb-md">
          <div class="col-12 col-md-3">
            <q-select
              v-model="filters.classroom"
              :options="classroomOptions"
              label="Filter by Classroom"
              outlined
              dense
              clearable
              emit-value
              map-options
            />
          </div>
          <div class="col-12 col-md-3">
            <q-select
              v-model="filters.subject"
              :options="subjectOptions"
              label="Filter by Subject"
              outlined
              dense
              clearable
              emit-value
              map-options
            />
          </div>
          <div class="col-12 col-md-3">
            <q-select
              v-model="filters.teacher"
              :options="teacherOptions"
              label="Filter by Teacher"
              outlined
              dense
              clearable
              emit-value
              map-options
            />
          </div>
          <div class="col-12 col-md-3 flex items-center">
            <q-btn
              v-if="hasActiveFilters"
              flat
              color="grey-7"
              icon="clear"
              label="Clear Filters"
              @click="clearFilters"
            />
          </div>
        </div>
        
        <q-table
          :rows="filteredAssignments"
          :columns="columns"
          row-key="id"
          :filter="filter"
          :pagination="pagination"
          flat
          bordered
        >
          <template v-slot:top-right>
            <q-input
              v-model="filter"
              dense
              debounce="300"
              placeholder="Search assignments..."
              outlined
            >
              <template v-slot:prepend>
                <q-icon name="search" />
              </template>
              <template v-slot:append>
                <q-icon
                  v-if="filter"
                  name="clear"
                  class="cursor-pointer"
                  @click="filter = ''"
                />
              </template>
            </q-input>
          </template>

          <template v-slot:body-cell-classroom_name="props">
            <q-td :props="props">
              <q-select
                v-if="editMode"
                v-model="props.row.classroom_id"
                :options="classroomOptions"
                dense
                outlined
                emit-value
                map-options
                @update:model-value="(val) => updateAssignment(props.row, 'classroom_id', val)"
              />
              <span v-else>{{ props.row.classroom_name }}</span>
            </q-td>
          </template>

          <template v-slot:body-cell-subject_name="props">
            <q-td :props="props">
              <q-chip
                v-if="!editMode"
                :style="{
                  backgroundColor: props.row.subject_color_bg,
                  color: props.row.subject_color_text
                }"
                icon="menu_book"
                dense
              >
                {{ props.row.subject_name }}
              </q-chip>
              <q-select
                v-else
                v-model="props.row.subject_id"
                :options="subjectOptions"
                dense
                outlined
                emit-value
                map-options
                @update:model-value="(val) => updateAssignment(props.row, 'subject_id', val)"
              />
            </q-td>
          </template>

          <template v-slot:body-cell-teacher_name="props">
            <q-td :props="props">
              <div v-if="!editMode" class="row items-center">
                <q-avatar color="primary" text-color="white" size="32px" icon="person" class="q-mr-sm" />
                <span>{{ props.row.teacher_name }}</span>
              </div>
              <q-select
                v-else
                v-model="props.row.teacher_id"
                :options="teacherOptions"
                dense
                outlined
                emit-value
                map-options
                @update:model-value="(val) => updateAssignment(props.row, 'teacher_id', val)"
              />
            </q-td>
          </template>

          <template v-slot:body-cell-classes_per_week="props">
            <q-td :props="props">
              <q-input
                v-if="editMode"
                v-model.number="props.row.classes_per_week"
                type="number"
                dense
                outlined
                style="width: 80px"
                @update:model-value="(val) => updateAssignment(props.row, 'classes_per_week', val)"
              />
              <q-badge v-else color="primary" :label="props.row.classes_per_week" />
            </q-td>
          </template>

          <template v-slot:body-cell-actions="props">
            <q-td :props="props">
              <q-btn
                v-if="editMode"
                flat
                round
                dense
                color="negative"
                icon="delete"
                @click="deleteAssignment(props.row)"
              >
                <q-tooltip>Delete Assignment</q-tooltip>
              </q-btn>
            </q-td>
          </template>

          <template v-slot:no-data>
            <div class="full-width row flex-center q-gutter-sm q-pa-lg">
              <q-icon name="assignment" size="48px" color="grey-5" />
              <span class="text-grey-7">No assignments found</span>
            </div>
          </template>
        </q-table>

        <!-- Add New Assignment (Edit Mode Only) -->
        <div v-if="editMode" class="q-mt-md">
          <q-btn
            color="primary"
            icon="add"
            label="Add New Assignment"
            @click="showAddDialog = true"
          />
        </div>
      </q-card-section>
    </q-card>

    <!-- Add Assignment Dialog -->
    <q-dialog v-model="showAddDialog">
      <q-card style="min-width: 400px">
        <q-card-section>
          <div class="text-h6">Add New Assignment</div>
        </q-card-section>
        <q-card-section class="q-pt-none">
          <q-select
            v-model="newAssignment.classroom_id"
            :options="classroomOptions"
            label="Classroom"
            outlined
            class="q-mb-md"
            emit-value
            map-options
          />
          <q-select
            v-model="newAssignment.subject_id"
            :options="subjectOptions"
            label="Subject"
            outlined
            class="q-mb-md"
            emit-value
            map-options
          />
          <q-select
            v-model="newAssignment.teacher_id"
            :options="teacherOptions"
            label="Teacher"
            outlined
            class="q-mb-md"
            emit-value
            map-options
          />
          <q-input
            v-model.number="newAssignment.classes_per_week"
            label="Classes per Week"
            type="number"
            outlined
          />
        </q-card-section>
        <q-card-actions align="right">
          <q-btn flat label="Cancel" color="grey-7" v-close-popup />
          <q-btn
            flat
            label="Save"
            color="primary"
            :disable="!isValidNewAssignment"
            @click="addAssignment"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useQuasar } from 'quasar';

const $q = useQuasar();

const props = defineProps({
  assignments: {
    type: Array,
    required: true
  },
  schoolId: {
    type: [Number, String],
    required: true
  },
  classrooms: {
    type: Array,
    default: () => []
  },
  subjects: {
    type: Array,
    default: () => []
  },
  teachers: {
    type: Array,
    default: () => []
  }
});

// Emit events for parent
const emit = defineEmits(['update:assignment', 'delete:assignment', 'create:assignment']);

// Edit mode state
const editMode = ref(false);
const filter = ref('');
const showAddDialog = ref(false);

// Filter state
const filters = ref({
  classroom: null,
  subject: null,
  teacher: null
});

// New assignment form
const newAssignment = ref({
  classroom_id: null,
  subject_id: null,
  teacher_id: null,
  classes_per_week: 3
});

// Pagination
const pagination = ref({
  rowsPerPage: 0,
  sortBy: 'classroom_name',
  descending: false
});

// Extract unique options for filters
const classroomOptions = computed(() => {
  // Use provided classrooms or fallback to assignments
  let classrooms = props.classrooms.length > 0 ? props.classrooms : [];
  
  // If no classrooms prop, extract from assignments
  if (classrooms.length === 0 && props.assignments.length > 0) {
    classrooms = props.assignments
      .map(a => ({
        id: a.classroom_id,
        name: a.classroom_name,
        grade_id: null,
        grade_name: a.grade_name || 'Unknown Grade'
      }))
      .filter((c, index, self) => 
        index === self.findIndex(x => x.id === c.id)
      );
  }
  
  // Return flat list sorted by grade and name
  return classrooms
    .map(c => ({
      label: `${c.grade_name || 'N/A'} - ${c.name}`,
      value: c.id,
      grade_name: c.grade_name || 'Unknown Grade'
    }))
    .sort((a, b) => {
      // Sort by grade first, then by classroom name
      const gradeCompare = a.grade_name.localeCompare(b.grade_name);
      if (gradeCompare !== 0) return gradeCompare;
      return a.label.localeCompare(b.label);
    });
});

const subjectOptions = computed(() => {
  // Use provided subjects or fallback to assignments
  if (props.subjects.length > 0) {
    return props.subjects
      .map(s => ({
        label: s.name,
        value: s.id
      }))
      .sort((a, b) => a.label.localeCompare(b.label));
  }
  
  // Fallback to assignments
  return props.assignments
    .map(a => ({
      label: a.subject_name,
      value: a.subject_id
    }))
    .filter((s, index, self) => 
      index === self.findIndex(x => x.value === s.value)
    )
    .sort((a, b) => a.label.localeCompare(b.label));
});

const teacherOptions = computed(() => {
  // Use provided teachers or fallback to assignments
  if (props.teachers.length > 0) {
    return props.teachers
      .map(t => ({
        label: t.name,
        value: t.id
      }))
      .sort((a, b) => a.label.localeCompare(b.label));
  }
  
  // Fallback to assignments
  return props.assignments
    .map(a => ({
      label: a.teacher_name,
      value: a.teacher_id
    }))
    .filter((t, index, self) => 
      index === self.findIndex(x => x.value === t.value)
    )
    .sort((a, b) => a.label.localeCompare(b.label));
});

// Filtered assignments
const filteredAssignments = computed(() => {
  return props.assignments.filter(assignment => {
    const matchClassroom = !filters.value.classroom || 
      assignment.classroom_id === filters.value.classroom;
    const matchSubject = !filters.value.subject || 
      assignment.subject_id === filters.value.subject;
    const matchTeacher = !filters.value.teacher || 
      assignment.teacher_id === filters.value.teacher;
    return matchClassroom && matchSubject && matchTeacher;
  });
});

// Check if any filters are active
const hasActiveFilters = computed(() => {
  return filters.value.classroom || filters.value.subject || filters.value.teacher;
});

// Clear all filters
const clearFilters = () => {
  filters.value.classroom = null;
  filters.value.subject = null;
  filters.value.teacher = null;
};

// Validate new assignment
const isValidNewAssignment = computed(() => {
  return newAssignment.value.classroom_id && 
    newAssignment.value.subject_id && 
    newAssignment.value.teacher_id &&
    newAssignment.value.classes_per_week > 0;
});

// Update assignment
const updateAssignment = (assignment, field, value) => {
  emit('update:assignment', {
    ...assignment,
    [field]: value
  });
};

// Delete assignment
const deleteAssignment = (assignment) => {
  $q.dialog({
    title: 'Delete Assignment',
    message: `Are you sure you want to delete the assignment for ${assignment.classroom_name} - ${assignment.subject_name}?`,
    cancel: true,
    persistent: true
  }).onOk(() => {
    emit('delete:assignment', assignment.id);
  });
};

// Add new assignment
const addAssignment = () => {
  if (isValidNewAssignment.value) {
    emit('create:assignment', {
      ...newAssignment.value,
      school_id: props.schoolId
    });
    showAddDialog.value = false;
    // Reset form
    newAssignment.value = {
      classroom_id: null,
      subject_id: null,
      teacher_id: null,
      classes_per_week: 3
    };
  }
};

// Columns definition
const columns = [
  {
    name: 'grade_name',
    label: 'Grade',
    field: 'grade_name',
    align: 'left',
    sortable: true
  },
  {
    name: 'classroom_name',
    label: 'Classroom',
    field: 'classroom_name',
    align: 'left',
    sortable: true
  },
  {
    name: 'subject_name',
    label: 'Subject',
    field: 'subject_name',
    align: 'left',
    sortable: true
  },
  {
    name: 'teacher_name',
    label: 'Teacher',
    field: 'teacher_name',
    align: 'left',
    sortable: true
  },
  {
    name: 'classes_per_week',
    label: 'Classes/Week',
    field: 'classes_per_week',
    align: 'center',
    sortable: true
  },
  {
    name: 'actions',
    label: 'Actions',
    align: 'center'
  }
];
</script>

<style scoped>
.q-table th {
  font-weight: 600;
}
</style>
