<template>
  <div class="timeline-row">
    <div class="row-label">
      <span class="label-text">{{ item.label || item.title || '' }}</span>
    </div>
    <div class="row-content">
      <div class="grid-lines">
        <div 
          v-for="slot in timeSlots" 
          :key="slot"
          class="grid-line"
        ></div>
      </div>
      <div class="bars-container">
        <slot></slot>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'TimelineRow',
  props: {
    item: {
      type: Object,
      required: true
    },
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
  }
};
</script>

<style scoped>
.timeline-row {
  display: flex;
  border-bottom: 1px solid #f0f0f0;
  height: 50px;
  align-items: center;
}

.row-label {
  width: 200px;
  min-width: 200px;
  padding: 0 15px;
  font-size: 14px;
  color: #333;
  font-weight: 500;
}

.row-content {
  flex: 1;
  position: relative;
  height: 100%;
}

.grid-lines {
  display: flex;
  width: 100%;
  height: 100%;
  position: absolute;
  top: 0;
  left: 0;
  pointer-events: none;
}

.grid-line {
  flex: 1;
  border-left: 1px solid #f5f5f5;
  height: 100%;
}

.grid-line:first-child {
  border-left: none;
}

.bars-container {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
}
</style>
