<template>
  <div
    class="event-card"
    :style="{ backgroundColor: event.color || '#3B82F6' }"
    @click="$emit('click', event)"
    role="button"
    tabindex="0"
    >
 
    <div class="event-time" v-if="!event.is_full_day && formattedTime">
      {{ formattedTime }}
    </div>
    <div class="event-title" :title="event.title">{{ event.title }}</div>
    <div class="event-location" v-if="event.location">
      <i class="fas fa-map-marker-alt location-icon"></i>
      {{ event.location }}
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  event: {
    type: Object,
    required: true,
  },
});

const formattedTime = computed(() => {
  const timeStr = props.event.start_time;
  if (!timeStr) return '';

  let hours = 0, minutes = 0;
  if (typeof timeStr === 'string' && timeStr.includes('T')) {
    const [h, m] = timeStr.split('T')[1].split(':');
    hours = Number(h);
    minutes = Number(m);
  } else if (typeof timeStr === 'string') {
    [hours, minutes] = timeStr.split(':').map(Number);
  }

  if (isNaN(hours) || isNaN(minutes)) return '';

  const period = hours >= 12 ? 'PM' : 'AM';
  hours = hours % 12 || 12;
  return `${hours}:${minutes.toString().padStart(2, '0')} ${period}`;
});
</script>

<style scoped>
.event-card {
  display: flex;
  flex-direction: column;
  gap: 2px;
  padding: 6px 10px;
  border-radius: 8px;
  margin-bottom: 4px;
  color: white;
  font-size: 0.85rem;
  cursor: pointer;
  overflow: hidden;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.15);
  transition: box-shadow 0.2s, transform 0.1s;
}

.event-card:hover {
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.25);
  transform: translateY(-1px);
}

.event-time {
  font-weight: bold;
  font-size: 0.75rem;
  opacity: 0.95;
}

.event-title {
  font-weight: 600;
  font-size: 0.9rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.event-location {
  font-size: 0.75rem;
  display: flex;
  align-items: center;
  gap: 4px;
  opacity: 0.9;
}

.location-icon {
  font-size: 0.75rem;
  margin-right: 2px;
}
</style>
