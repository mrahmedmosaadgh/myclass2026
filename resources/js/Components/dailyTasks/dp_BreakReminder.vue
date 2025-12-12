<template>
  <q-dialog v-model="model">
    <q-card class="bg-teal-1 text-center">
      <q-card-section>
        <div class="text-h5 text-teal">وقت الحركة! 🏃‍♂️</div>
      </q-card-section>

      <q-card-section>
        <div class="text-body1">
          لقد عملت بجد لمدة 25 دقيقة. حان وقت استراحة قصيرة لتجديد النشاط.
        </div>
        <ul class="text-left q-mx-auto" style="max-width: 200px; list-style: none;">
          <li>💧 اشرب ماء</li>
          <li>🙆‍♂️ تمدد قليلاً</li>
          <li>👀 أرح عينيك</li>
        </ul>
      </q-card-section>

      <q-card-actions align="center">
        <q-btn label="تم، سأعود للعمل" color="teal" v-close-popup />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  modelValue: Boolean
})

const emit = defineEmits(['update:modelValue'])

const model = computed({
  get: () => props.modelValue,
  set: (val) => emit('update:modelValue', val)
})
</script>
