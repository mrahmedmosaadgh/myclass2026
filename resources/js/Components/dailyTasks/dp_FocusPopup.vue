<template>
  <q-dialog v-model="model" persistent>
    <q-card class="q-pa-md text-center" style="min-width: 300px">
      <q-card-section>
        <div class="text-h6 text-primary">هل ما زلت تركز؟ 🧐</div>
      </q-card-section>

      <q-card-section class="q-py-none">
        <!-- Placeholder for image -->
        <div class="text-body1 q-mt-md">
          {{ randomPrompt }}
        </div>
      </q-card-section>

      <q-card-actions align="center" class="q-mt-md">
        <q-btn label="نعم، أنا بطل! 💪" color="green" v-close-popup @click="$emit('confirmed')" />
        <q-btn label="تشتت قليلاً 😅" color="orange" flat v-close-popup @click="$emit('distracted')" />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  modelValue: Boolean
})

const emit = defineEmits(['update:modelValue', 'confirmed', 'distracted'])

const model = computed({
  get: () => props.modelValue,
  set: (val) => emit('update:modelValue', val)
})

const prompts = [
  "هل أنت مع المهمة أم مع الأفكار؟",
  "تذكر هدفك! هل ما تفعله الآن يقربك منه؟",
  "تنفس بعمق... وعد للتركيز!",
  "أنت أقوى من المشتتات!",
]

const randomPrompt = computed(() => prompts[Math.floor(Math.random() * prompts.length)])
</script>
