<template>
  <div class="row q-col-gutter-md">

    <StudentCard
      v-for="s in students"
      :key="s.id"
      :student="s"
      :behavior="studentBehaviors[s.id]"
      @open-behavior="openBehaviorDialog(s)"
      @open-avatar="openCameraDialog(s)"
    />

    <BehaviorManager
      v-model="behaviorDialog"
      :behaviors="behaviors"
      :student="selectedStudent"
      :student-behavior-id="selectedStudentBehaviorId"
      @recorded="refreshSummary"
    />

  </div>
</template>

<script setup>
import { ref } from "vue";
import StudentCard from "./StudentCard.vue";
import BehaviorManager from "./BehaviorManager.vue";

const students = ref([]);            // from session init
const studentBehaviors = ref({});    // id → { plus, minus, total }
const behaviors = ref([]);           // from /api/behaviors

const selectedStudent = ref(null);
const selectedStudentBehaviorId = ref(null);
const behaviorDialog = ref(false);

const openBehaviorDialog = (s) => {
  selectedStudent.value = s;
  selectedStudentBehaviorId.value = s.behaviorRecordId;
  behaviorDialog.value = true;
};

const refreshSummary = async () => {
  // GET /api/student-behaviors/{id}
};
</script>
