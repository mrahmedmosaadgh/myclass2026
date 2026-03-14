<template>
  <div class="schedule-timeline">
    <TimelineHeader 
      :time-slots="timeSlots" 
      :start-hour="startHour" 
      :end-hour="endHour"
    />
    <div class="timeline-body">
      <TimelineRow
        v-for="(item, index) in scheduleItems"
        :key="item.id || index"
        :item="item"
        :time-slots="timeSlots"
        :start-hour="startHour"
        :end-hour="endHour"
      >
        <template v-if="Array.isArray(item.events)">
          <TimelineBar
            v-for="evt in item.events"
            :key="evt.id || Math.random()"
            :start-time="evt.startTime"
            :end-time="evt.endTime"
            :color="evt.color"
            :label="evt.label"
          />
        </template>
        <template v-else>
          <TimelineBar
            :start-time="item.startTime"
            :end-time="item.endTime"
            :color="item.color"
            :label="item.label"
          />
        </template>
      </TimelineRow>
      <TimeIndicator 
        v-if="showCurrentTime" 
        :current-time="currentTime"
        :start-hour="startHour"
        :end-hour="endHour"
      />
    </div>
  </div>
</template>

<script>
import TimelineHeader from './TimelineHeader.vue';
import TimelineRow from './TimelineRow.vue';
import TimelineBar from './TimelineBar.vue';
import TimeIndicator from './TimeIndicator.vue';

export default {
  name: 'ScheduleTimeline',
  components: {
    TimelineHeader,
    TimelineRow,
    TimelineBar,
    TimeIndicator
  },
  props: {
    scheduleItems: {
      type: Array,
      required: true,
      validator: items => items.every(item => 
        (item.startTime && item.endTime) || Array.isArray(item.events)
      )
    },
    startHour: {
      type: Number,
      default: 0
    },
    endHour: {
      type: Number,
      default: 24
    },
    showCurrentTime: {
      type: Boolean,
      default: true
    }
  },
  data() {
    return {
      currentTime: new Date(),
      timeSlots: []
    };
  },
  created() {
    this.generateTimeSlots();
    if (this.showCurrentTime) {
      setInterval(() => {
        this.currentTime = new Date();
      }, 60000);
    }
  },
  methods: {
    generateTimeSlots() {
      this.timeSlots = [];
      for (let hour = this.startHour; hour <= this.endHour; hour++) {
        this.timeSlots.push(hour);
      }
    }
  }
};
</script>

<style scoped>
.schedule-timeline {
  width: 100%;
  overflow-x: auto;
  background: #ffffff;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.timeline-body {
  position: relative;
  padding: 10px 0;
}
</style>
