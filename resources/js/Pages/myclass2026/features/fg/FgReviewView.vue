<template>
  <div class="column q-gutter-y-lg q-pa-md">
    
    <!-- Review Header -->
    <div class="row items-center justify-between">
      <div class="text-h5 text-secondary text-weight-bold">
        <q-icon name="nights_stay" size="sm" class="q-mr-sm" />
        Evening Review
      </div>
      <q-btn flat icon="close" @click="uiStore.toggleMode('review')" label="Back to Focus" />
    </div>
    
    <q-separator />

    <div class="row q-col-gutter-lg" v-if="!sessionsStore.loading">
      
      <!-- Metrics -->
      <div class="col-12 col-md-4">
        <q-card flat bordered class="bg-grey-1">
          <q-card-section>
            <div class="text-subtitle2 text-grey-8 uppercase">Total Focus Time Today</div>
            <div class="text-h3 text-weight-bold q-my-sm text-primary">{{ formatTotalTime(totalSecondsToday) }}</div>
            <div class="text-caption text-grey-6">{{ sessionsToday.length }} sessions completed</div>
          </q-card-section>
        </q-card>
      </div>
      
      <!-- Session List -->
      <div class="col-12 col-md-8">
        <div class="text-h6 q-mb-md">Today's Sessions</div>
        <q-list bordered separator class="rounded-borders bg-white">
          <q-item v-if="sessionsToday.length === 0">
            <q-item-section class="text-grey italic">No focus sessions recorded today.</q-item-section>
          </q-item>
          <q-item v-for="session in sessionsToday" :key="session.id">
            <q-item-section avatar>
              <q-icon name="check_circle" :color="session.status === 'completed' ? 'positive' : 'warning'" />
            </q-item-section>
            <q-item-section>
              <q-item-label class="text-weight-bold">{{ session.task?.title || 'Unknown Task' }}</q-item-label>
              <q-item-label caption v-if="session.intention">Intention: {{ session.intention }}</q-item-label>
              <q-item-label caption v-if="session.check_in_answer">End status: {{ session.check_in_answer }}</q-item-label>
            </q-item-section>
            <q-item-section side>
               <q-badge color="grey-3" text-color="black" class="q-pa-xs">
                 {{ Math.floor(session.duration_seconds / 60) }}m
               </q-badge>
            </q-item-section>
          </q-item>
        </q-list>
      </div>

    </div>
    
    <div v-else class="text-center q-pa-xl">
      <q-spinner color="primary" size="3em" />
    </div>

  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { useFgUiStore } from './stores/fg-ui.store'
import { useFgSessionsStore } from './stores/fg-sessions.store'

const uiStore = useFgUiStore()
const sessionsStore = useFgSessionsStore()

onMounted(() => {
  // Pass 'today' flag to endpoint optionally to filter server-side
  sessionsStore.fetchSessions({ today: true })
})

const sessionsToday = computed(() => {
  // Ideally this would be correctly filtered from API due to payload, but we can secure it locally
  return sessionsStore.sessions.filter(s => s.status !== 'active')
})

const totalSecondsToday = computed(() => {
  return sessionsToday.value.reduce((acc, curr) => acc + (curr.duration_seconds || 0), 0)
})

const formatTotalTime = (totalSecs) => {
  const h = Math.floor(totalSecs / 3600)
  const m = Math.floor((totalSecs % 3600) / 60)
  if (h > 0) return `${h}h ${m}m`
  return `${m}m`
}
</script>
