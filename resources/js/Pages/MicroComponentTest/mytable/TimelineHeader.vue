<template>
  <div class="timeline-header">
    <div class="header-label"></div>
    <div class="header-slots">
      <div 
        v-for="hour in timeSlots" 
        :key="hour"
        class="header-slot"
      >
        {{ formatHour(hour) }}
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'TimelineHeader',
  props: {
    timeSlots: {
      type: Array,
      required: true
    },
    startHour: {
      type: Number,
      default: 0
    },
    endHour: {
      type: Number,
      default: 24
    }
  },
  methods: {
    formatHour(hour) {
      const period = hour >= 12 ? 'PM' : 'AM';
      const displayHour = hour === 0 ? 12 : hour > 12 ? hour - 12 : hour;
      return `${displayHour} ${period}`;
    }
  }
};
</script>

<style scoped>
.timeline-header {
  display: flex;
  border-bottom: 2px solid #e0e0e0;
  padding: 10px 0;
  background: #f8f9fa;
  position: sticky;
  top: 0;
  z-index: 10;
}

.header-label {
  width: 200px;
  min-width: 200px;
  padding: 0 15px;
  font-weight: 600;
  color: #333;
}

.header-slots {
  display: flex;
  flex: 1;
  position: relative;
}

.header-slot {
  flex: 1;
  text-align: center;
  font-size: 13px;
  color: #666;
  font-weight: 500;
  border-left: 1px solid #e0e0e0;
  padding: 5px 0;
}

.header-slot:first-child {
  border-left: none;
}
</style>
