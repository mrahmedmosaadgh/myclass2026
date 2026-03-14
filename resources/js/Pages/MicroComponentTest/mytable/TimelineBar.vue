<template>
  <div 
    class="timeline-bar"
    :style="barStyle"
    @click="handleClick"
  >
    <span class="bar-label" v-if="label && showLabel">{{ label }}</span>
  </div>
</template>

<script>
export default {
  name: 'TimelineBar',
  props: {
    startTime: {
      type: String,
      required: true
    },
    endTime: {
      type: String,
      required: true
    },
    color: {
      type: String,
      default: '#4CAF50'
    },
    label: {
      type: String,
      default: ''
    },
    showLabel: {
      type: Boolean,
      default: true
    }
  },
  computed: {
    barStyle() {
      const startMinutes = this.getTimeInMinutes(this.startTime);
      const endMinutes = this.getTimeInMinutes(this.endTime);
      const totalMinutes = 24 * 60;
      
      const leftPercent = (startMinutes / totalMinutes) * 100;
      const widthPercent = ((endMinutes - startMinutes) / totalMinutes) * 100;
      
      return {
        left: `${leftPercent}%`,
        width: `${Math.max(widthPercent, 0.5)}%`,
        backgroundColor: this.color
      };
    }
  },
  methods: {
    getTimeInMinutes(timeStr) {
      const [hours, minutes] = timeStr.split(':').map(Number);
      return hours * 60 + (minutes || 0);
    },
    handleClick() {
      this.$emit('click', {
        startTime: this.startTime,
        endTime: this.endTime,
        label: this.label,
        color: this.color
      });
    }
  }
};
</script>

<style scoped>
.timeline-bar {
  position: absolute;
  height: 36px;
  top: 7px;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
  overflow: hidden;
}

.timeline-bar:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
  filter: brightness(1.1);
}

.bar-label {
  color: white;
  font-size: 12px;
  font-weight: 600;
  padding: 0 8px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
</style>
