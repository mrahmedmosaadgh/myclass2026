<template>
  <q-card flat bordered class="bg-grey-1 fg-venting-area">
    <q-card-section>
      <div class="text-h6 q-mb-xs">Clear Your Mind (The Dump 🧠)</div>
      <div class="text-caption text-grey-6 q-mb-md">Write whatever is on your mind — don't filter, just dump it all out. Our AI will extract actionable tasks from your thoughts.</div>
      <q-input
        v-model="ventText"
        type="textarea"
        outlined
        autogrow
        placeholder="What's heavily on your mind? Just type stream of consciousness..."
        :disable="isVenting"
        input-class="text-body1"
      />
    </q-card-section>
    
    <q-separator />

    <q-card-actions align="right">
      <q-btn
        flat
        label="Clear"
        color="grey-7"
        @click="ventText = ''"
        :disable="!ventText || isVenting"
      >
        <q-tooltip anchor="top middle" self="bottom middle" :delay="400">Clear the text area and start fresh.</q-tooltip>
      </q-btn>
      <q-btn
        unelevated
        label="Clarify with AI"
        color="primary"
        icon="auto_awesome"
        @click="submitVent"
        :loading="isVenting"
        :disable="!ventText.trim()"
      >
        <q-tooltip anchor="top middle" self="bottom middle" :delay="400">Send your thoughts to AI — it will extract tasks and notes automatically.</q-tooltip>
      </q-btn>
    </q-card-actions>
  </q-card>
</template>

<script setup>
import { ref } from 'vue'
import { useFgAi } from '../composables/fg-use-ai'
import { useQuasar } from 'quasar'

const $q = useQuasar()
const ventText = ref('')
const { isVenting, processVent } = useFgAi()

const submitVent = async () => {
  if (!ventText.value.trim()) return
  
  try {
    await processVent(ventText.value)
    // If successful, the composable opens the AI Modal, so we can clear this Area
    ventText.value = ''
  } catch (err) {
    $q.notify({
      type: 'negative',
      message: 'Failed to process your vent. Please try again.',
      position: 'top'
    })
  }
}
</script>

<style scoped>
.fg-venting-area {
  transition: all 0.3s ease;
}
.fg-venting-area:focus-within {
  border-color: var(--q-primary);
  box-shadow: 0 1px 5px rgba(0, 0, 0, 0.1);
}
</style>
