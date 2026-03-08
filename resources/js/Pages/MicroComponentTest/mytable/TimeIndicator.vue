<template>
  <div 
    class="time-indicator"
    :style="indicatorStyle"
  >
    <div class="indicator-dot"></div>
    <div class="indicator-line"></div>
  </div>
</template>

<script>
export default {
  name: 'TimeIndicator',
  props: {
    currentTime: {
      type: Date,
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
  computed: {
    indicatorStyle() {
      const now = this.currentTime;
      const currentHour = now.getHours();
      const currentMinute = now.getMinutes();
      const totalMinutes = currentHour * 60 + currentMinute;
      const dayMinutes = 24 * 60;
      
      const leftPercent = (totalMinutes / dayMinutes) * 100;
      
      return {
        left: `${leftPercent}%`
      };
    }
  }
};
</script>

<style scoped>
.time-indicator {
  position: absolute;
  top: 0;
  bottom: 0;
  width: 2px;
  z-index: 20;
  pointer-events: none;
}

.indicator-dot {
  width: 12px;
  height: 12px;
  background-color: #ff3b3b;
  border-radius: 50%;
  position: absolute;
  top: -5px;
  left: -5px;
  box-shadow: 0 2px 4px rgba(255, 59, 59, 0.4);
}

.indicator-line {
  width: 2px;
  height: 100%;
  background-color: #ff3b3b;
  position: absolute;
  top: 0;
  left: 0;
}
</style>
