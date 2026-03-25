<template>
  <q-dialog v-model="model" persistent>
    <q-card class="q-pa-md" style="width: 380px;">

      <!-- HEADER -->
      <div class="row items-center justify-between q-mb-md">
        <div class="text-h6">{{ student?.name }}</div>
        <q-btn dense flat icon="close" @click="model = false" />
      </div>

      <!-- TABS -->
      <q-tabs v-model="tab" active-color="primary" indicator-color="primary">
        <q-tab name="positive" label="Positive" />
        <q-tab name="negative" label="Needs Work" />
      </q-tabs>

      <q-separator class="q-my-sm" />

      <!-- TAB PANELS -->
      <q-tab-panels v-model="tab" animated>

        <!-- POSITIVE BEHAVIORS -->
        <q-tab-panel name="positive">
          <div class="column q-gutter-md">
            <q-card
              v-for="b in positiveBehaviors"
              :key="b.id"
              class="behavior-btn behavior-positive"
              clickable
              @click="selectBehavior(b)"
            >
              <div class="row items-center justify-between">
                <div class="text-subtitle1 text-primary">{{ b.name }}</div>
                <div class="point-badge plus">+{{ b.value }}</div>
              </div>
            </q-card>
          </div>
        </q-tab-panel>

        <!-- NEGATIVE BEHAVIORS -->
        <q-tab-panel name="negative">
          <div class="column q-gutter-md">
            <q-card
              v-for="b in negativeBehaviors"
              :key="b.id"
              class="behavior-btn behavior-negative"
              clickable
              @click="selectBehavior(b)"
            >
              <div class="row items-center justify-between">
                <div class="text-subtitle1 text-negative">{{ b.name }}</div>
                <div class="point-badge minus">-{{ Math.abs(b.value) }}</div>
              </div>
            </q-card>
          </div>
        </q-tab-panel>

      </q-tab-panels>

      <!-- CONFIRM -->
      <q-dialog v-model="confirmOpen">
        <q-card>
          <q-card-section>
            <div class="text-h6">Confirm Behavior</div>
            <div class="q-mt-sm">{{ selectedBehavior?.name }} ({{ selectedBehavior?.value }})</div>
          </q-card-section>

          <q-card-actions align="right">
            <q-btn flat label="Cancel" v-close-popup />
            <q-btn color="primary" label="Apply" @click="applyBehavior" />
          </q-card-actions>
        </q-card>
      </q-dialog>

    </q-card>
  </q-dialog>
</template>

<script setup>
import { ref, computed } from "vue";
import { useQuasar } from "quasar";
import axios from "axios";

const model = defineModel({ type: Boolean, default: false });

const props = defineProps({
  behaviors: Array,
  student: Object,
  studentBehaviorId: Number      // student_behaviors.id
});

const emit = defineEmits(["recorded"]);

const $q = useQuasar();
const tab = ref("positive");
const confirmOpen = ref(false);
const selectedBehavior = ref(null);

// ==============================
// COMPUTED LISTS
// ==============================
const positiveBehaviors = computed(() =>
  props.behaviors.filter(b => b.value > 0)
);

const negativeBehaviors = computed(() =>
  props.behaviors.filter(b => b.value < 0)
);

// ==============================
// SELECTION
// ==============================
const selectBehavior = (b) => {
  selectedBehavior.value = b;
  confirmOpen.value = true;
};

// ==============================
// APPLY BEHAVIOR
// ==============================
const applyBehavior = async () => {
  $q.loading.show();

  try {
    await axios.post("/api/student-behaviors", {
      student_behaviors_id: props.studentBehaviorId,
      reason_id: selectedBehavior.value.id,
      value: selectedBehavior.value.value,
      action_type: "behavior",
      note: null
    });

    emit("recorded");
    model.value = false;

    // optional TTS
    // new SpeechSynthesisUtterance(`Good job ${props.student.name}`)
  } catch (err) {
    console.error(err);
    $q.notify({ type: "negative", message: "Error adding behavior" });
  }

  $q.loading.hide();
};
</script>

<style scoped>
.behavior-btn {
  padding: 14px;
  border-radius: 12px;
  cursor: pointer;
  transition: 0.16s ease;
}
.behavior-positive:hover { background: #e8f7ff; }
.behavior-negative:hover { background: #ffe8e8; }

.point-badge {
  padding: 4px 10px;
  border-radius: 12px;
  font-weight: bold;
  font-size: 14px;
}
.plus { background: #d1f2d1; color: #108210; }
.minus { background: #f7c5c5; color: #b30000; }
</style>
