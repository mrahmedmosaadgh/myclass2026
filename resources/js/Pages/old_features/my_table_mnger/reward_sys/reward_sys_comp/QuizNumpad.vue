<template>
  <q-card class="bg-blue-1 shadow-10 full-width" style="border-radius: 16px 16px 0 0;">
    
    <!-- Optional: Header/Title inside Numpad if needed, or keep external -->
    
    <q-card-section class="q-pb-none">
      <q-input
        outlined
        readonly
        bg-color="white"
        :model-value="displayValue"
        input-class="text-center text-h5 text-weight-bold"
        placeholder="Enter mark"
        :error="hasError"
        :error-message="errorMessage"
        dense
      >
        <template v-slot:append>
             <div class="text-caption text-grey-6 q-mr-sm">/ {{ max }}</div>
        </template>
      </q-input>
    </q-card-section>

    <q-card-section class="q-pa-md">
      <div class="row q-col-gutter-sm justify-center">
        <div v-for="n in [1, 2, 3, 4, 5, 6, 7, 8, 9]" :key="n" class="col-4">
          <q-btn
            unelevated
            class="full-width text-h6 text-weight-bold custom-num-btn"
            color="white"
            text-color="grey-9"
            :label="n"
            @click="handleInput(n)"
          />
        </div>

        <div class="col-4">
           <!-- Empty or utility? User had 0 in separate div below in example logic, but visually in grid usually 0 is bottom center -->
           <!-- User's example had 0 in a separate col-4 div, implying it might be in the same flow. -->
           <!-- Let's put 0 in the standard phone layout position (bottom center center) -->
           <!-- Actually user's code: v-for 1..9, then separate div col-4 with 0. This puts 0 after 9. Grid is 12 cols. -->
           <!-- 1 2 3 -->
           <!-- 4 5 6 -->
           <!-- 7 8 9 -->
           <!-- 0 (aligned left if just flow? No, justify-center makes it centered if it's the only one in the row? No, it's just the next col-4) -->
           <!-- Let's make standard layout: . C 0 Back . -->
        </div>
      </div>
      
      <!-- Better Layout for Bottom Row -->
      <div class="row q-col-gutter-sm justify-center q-mt-sm">
         <div class="col-4">
            <q-btn
              outline
              color="red-5"
              icon="backspace"
              class="full-width custom-num-btn"
              @click="backspace"
            />
         </div>
         <div class="col-4">
            <q-btn
              unelevated
              class="full-width text-h6 text-weight-bold custom-num-btn"
              color="white"
              text-color="grey-9"
              label="0"
              @click="handleInput(0)"
            />
         </div>
         <div class="col-4">
            <q-btn
              unelevated
              color="primary"
              icon="check"
              class="full-width custom-num-btn"
              @click="submit"
            />
         </div>
      </div>
    </q-card-section>
  </q-card>
</template>

<script setup>
import { ref, computed, watch } from 'vue'

const props = defineProps({
  modelValue: {
    type: [Number, String],
    default: ''
  },
  max: {
    type: Number,
    default: 10
  }
})

const emit = defineEmits(['update:modelValue', 'submit'])

// Internal error state
const hasError = ref(false)
const errorMessage = ref('')

// Computed property to handle string conversion safely
const displayValue = computed(() => {
    return props.modelValue
})

watch(() => props.modelValue, () => {
    hasError.value = false
    errorMessage.value = ''
})

const handleInput = (num) => {
  // Reset errors on new input
  hasError.value = false;
  errorMessage.value = '';

  const currentString = String(props.modelValue || '');
  const newString = currentString + num;
  const newValue = Number(newString);

  // Validate Max
  if (newValue > props.max) {
    hasError.value = true;
    errorMessage.value = `Max value is ${props.max}`;
    return; // Block input
  }

  // Update
  emit('update:modelValue', newValue);
}

const backspace = () => {
  hasError.value = false;
  const currentString = String(props.modelValue || '');
  if (!currentString) return;

  const newString = currentString.slice(0, -1);
  emit('update:modelValue', newString === '' ? null : Number(newString));
}

const submit = () => {
    if (!props.modelValue && props.modelValue !== 0) return
    emit('submit')
}
</script>

<style scoped>
/* Custom styling to match the reference image borders */
.custom-num-btn {
  height: 50px; /* Condensed slightly for mobile landscape if needed, but 50-60 is good */
  border: 1px solid #cfd8dc; /* Blue-grey light */
  border-radius: 12px;
  font-size: 1.25rem;
}
</style>
