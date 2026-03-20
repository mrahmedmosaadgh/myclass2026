<template>
  <q-card bordered class="fg-session-panel q-mt-md" :class="isRunning ? 'bg-primary text-white' : 'bg-grey-1'">
    <q-card-section>
      
      <div class="row items-center justify-between">
         <div>
            <div class="text-h6">
              <q-icon :name="isRunning ? 'timer' : 'timer_off'" class="q-mr-sm" />
              {{ formattedTime }}
            </div>
            <div class="text-caption" :class="isRunning ? 'text-white' : 'text-grey'">Current Session</div>
         </div>
         
         <div v-if="!isRunning" class="row q-gutter-sm">
            <q-select 
              v-model="intention" 
              outlined dense bg-color="white" 
              placeholder="Intention (optional)" 
              style="min-width: 150px" 
            />
            <q-select 
              v-model="energy" 
              :options="['high', 'medium', 'low']" 
              outlined dense bg-color="white" 
              placeholder="Energy" 
              style="width: 100px" 
            />
            <q-btn unelevated color="primary" label="Start Focus" icon="play_arrow" @click="startFocus" />
         </div>
         
         <div v-else class="row q-gutter-sm">
            <q-btn 
              outline color="white" 
              label="Drifted" 
              icon="directions_walk" 
              @click="endFocus('drifted')" 
            />
            <q-btn 
              unelevated color="white" class="text-primary"
              label="Stop & Save" 
              icon="stop" 
              @click="endFocus('on_track')" 
            />
         </div>
      </div>

    </q-card-section>
  </q-card>
</template>

<script setup>
import { ref } from 'vue'
import { useFgSession } from '../composables/fg-use-session'

const props = defineProps({
  taskId: {
    type: String,
    required: true
  }
})

const intention = ref('')
const energy = ref(null)

const { isRunning, formattedTime, startSession, completeSession } = useFgSession(props.taskId)

const startFocus = async () => {
  await startSession(intention.value, energy.value)
}

const endFocus = async (checkInStatus) => {
  await completeSession(checkInStatus)
}
</script>

<style scoped>
.fg-session-panel {
  transition: background-color 0.5s ease;
}
</style>
