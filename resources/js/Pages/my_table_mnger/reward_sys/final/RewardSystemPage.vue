<template>
 

    <!-- HEADER -->
    <div class="row justify-between items-center q-mb-md">
      <div class="text-h5">Classroom Reward System</div>
      <div>
        <q-btn label="Leaderboard" color="primary" @click="leaderboardOpen = true" />
      </div>
    </div>

    <!-- CLASSROOM & PERIOD SELECT -->
    <div class="row q-mb-md q-gutter-md">
      <q-select
        outlined
        label="Select Classroom"
        v-model="selectedClassroomId"
        :options="classroomsOptions"
        @update:model-value="loadStudents"
        option-label="name"
        option-value="id"
      />

      <q-select
        outlined
        label="Select Period"
        v-model="selectedPeriod"
        :options="periodOptions"
      />
    </div>

    <!-- STUDENT GRID -->
    <div class="q-gutter-md row wrap">
      <StudentCard
        v-for="student in students"
        :key="student.id"
        :student="student"
        @open-behavior="openBehaviorManager"
      />
    </div>

    <!-- BEHAVIOR MANAGER DIALOG -->
    <BehaviorManager
      v-model="behaviorDialog"
      :student="currentStudent"
      :studentBehaviorId="currentStudentBehaviorId"
      :behaviors="behaviors"
      @recorded="refreshStudentSummary"
    />

    <!-- LEADERBOARD DIALOG (placeholder) -->
    <q-dialog v-model="leaderboardOpen" persistent>
      <q-card class="q-pa-md" style="width: 400px;">
        <div class="text-h6 q-mb-md">Leaderboard</div>
        <div v-for="student in leaderboard" :key="student.id" class="row justify-between q-mb-sm">
          <div>{{ student.name }}</div>
          <div>{{ student.total_points }}</div>
        </div>
        <q-btn flat label="Close" @click="leaderboardOpen = false" />
      </q-card>
    </q-dialog>

 
</template>

<script setup>
import { ref, reactive, onMounted } from "vue";
import axios from "axios";
import { useQuasar } from "quasar";
import StudentCard from "./StudentCard.vue";
import BehaviorManager from "./BehaviorManager.vue";

// QUASAR
const $q = useQuasar();

// STATE
const classrooms = ref([]);
const classroomsOptions = ref([]);
const selectedClassroomId = ref(null);

const periodOptions = ref([
  "Week 1", "Week 2", "Week 3", "Week 4"
]);
const selectedPeriod = ref(periodOptions.value[0]);

const students = ref([]);
const behaviors = ref([]);

// DIALOG STATE
const behaviorDialog = ref(false);
const currentStudent = ref(null);
const currentStudentBehaviorId = ref(null);

const leaderboardOpen = ref(false);
const leaderboard = ref([]);

// LOAD CLASSROOMS
const loadClassrooms = async () => {
  try {
    const { data } = await axios.get("/my_classes_with_students");
    classrooms.value = data;
    classroomsOptions.value = data.map(c => ({ id: c.id, name: c.name }));
  } catch (err) {
    console.error(err);
    $q.notify({ type: "negative", message: "Failed to load classrooms." });
  }
};

// LOAD STUDENTS
const loadStudents = async () => {
  const classroom = classrooms.value.find(c => c.id === selectedClassroomId.value);
  students.value = classroom?.students || [];

  // Fetch behaviors if not already loaded
  if (behaviors.value.length === 0) {
    try {
      const { data } = await axios.get("/api/behaviors");
      behaviors.value = data;
    } catch (err) {
      console.error(err);
      $q.notify({ type: "negative", message: "Failed to load behaviors." });
    }
  }
};

// OPEN BEHAVIOR MANAGER
const openBehaviorManager = (student) => {
  currentStudent.value = student;
  currentStudentBehaviorId.value = student.behavior_id; // assign proper backend ID
  behaviorDialog.value = true;
};

// REFRESH STUDENT SUMMARY
const refreshStudentSummary = async () => {
  if (!currentStudent.value) return;
  try {
    const { data } = await axios.get(`/api/student-behaviors/${currentStudent.value.id}`);
    currentStudent.value.total_points = data.total_points;
  } catch (err) {
    console.error(err);
  }
};

// INITIAL LOAD
onMounted(() => {
  loadClassrooms();
});
</script>

<style scoped>
/* Quick styling for student cards & behavior buttons */
.behavior-btn {
  cursor: pointer;
  transition: transform 0.1s;
}
.behavior-btn:hover {
  transform: scale(1.03);
}
.point-badge {
  font-weight: bold;
}
.plus {
  color: green;
}
.minus {
  color: red;
}
</style>
