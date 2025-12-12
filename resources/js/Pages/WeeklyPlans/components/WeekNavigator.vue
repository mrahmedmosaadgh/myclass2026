<template>
  <div class="week-navigator">
    <div class="flex justify-between items-center mb-4">
      <h3 class="text-lg font-medium text-gray-900">Week Navigation</h3>
      <div class="text-sm text-gray-600">
        Semester Progress: {{ currentWeek }}/{{ totalWeeks }} weeks
      </div>
    </div>

    <!-- Week Grid -->
    <div class="grid grid-cols-6 md:grid-cols-9 lg:grid-cols-12 gap-2">
      <div
        v-for="week in totalWeeks"
        :key="week"
        class="week-item"
        :class="getWeekClass(week)"
        @click="selectWeek(week)"
      >
        <div class="week-number">{{ week }}</div>
        <div class="week-indicator" :class="getIndicatorClass(week)"></div>
      </div>
    </div>

    <!-- Legend -->
    <div class="flex justify-center mt-4 space-x-6 text-sm">
      <div class="flex items-center">
        <div class="w-3 h-3 bg-blue-500 rounded-full mr-2"></div>
        <span>Current</span>
      </div>
      <div class="flex items-center">
        <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
        <span>Complete</span>
      </div>
      <div class="flex items-center">
        <div class="w-3 h-3 bg-yellow-500 rounded-full mr-2"></div>
        <span>In Progress</span>
      </div>
      <div class="flex items-center">
        <div class="w-3 h-3 bg-gray-300 rounded-full mr-2"></div>
        <span>Planned</span>
      </div>
    </div>

    <!-- Quick Navigation -->
    <div class="flex justify-center mt-4 space-x-2">
      <q-btn
        size="sm"
        outline
        color="grey"
        icon="first_page"
        @click="selectWeek(1)"
        :disable="currentWeek === 1"
      />
      <q-btn
        size="sm"
        outline
        color="grey"
        icon="chevron_left"
        @click="selectWeek(currentWeek - 1)"
        :disable="currentWeek === 1"
      />
      <q-btn
        size="sm"
        outline
        color="grey"
        icon="chevron_right"
        @click="selectWeek(currentWeek + 1)"
        :disable="currentWeek === totalWeeks"
      />
      <q-btn
        size="sm"
        outline
        color="grey"
        icon="last_page"
        @click="selectWeek(totalWeeks)"
        :disable="currentWeek === totalWeeks"
      />
    </div>
  </div>
</template>

<script>
// import { QBtn } from 'quasar'

export default {
  name: 'WeekNavigator',
  components: {
    // QBtn
  },
  props: {
    currentWeek: {
      type: Number,
      default: 1
    },
    totalWeeks: {
      type: Number,
      default: 18
    },
    weekStatus: {
      type: Object,
      default: () => ({})
    }
  },
  emits: ['week-changed'],
  methods: {
    selectWeek(week) {
      if (week !== this.currentWeek && week >= 1 && week <= this.totalWeeks) {
        this.$emit('week-changed', week)
      }
    },
    
    getWeekClass(week) {
      const classes = ['week-item-base']
      
      if (week === this.currentWeek) {
        classes.push('week-current')
      } else {
        classes.push('week-normal')
      }
      
      return classes.join(' ')
    },
    
    getIndicatorClass(week) {
      const status = this.getWeekStatus(week)
      
      switch (status) {
        case 'complete':
          return 'bg-green-500'
        case 'in-progress':
          return 'bg-yellow-500'
        case 'planned':
          return 'bg-gray-300'
        default:
          return 'bg-gray-200'
      }
    },
    
    getWeekStatus(week) {
      return this.weekStatus[week] || 'empty'
    }
  }
}
</script>

<style scoped>
.week-navigator {
  @apply w-full;
}

.week-item-base {
  @apply relative p-3 rounded-lg cursor-pointer transition-all duration-200 text-center;
  @apply border-2 hover:shadow-md;
}

.week-normal {
  @apply border-gray-200 bg-white hover:border-gray-300;
}

.week-current {
  @apply border-blue-500 bg-blue-50 shadow-md;
}

.week-number {
  @apply font-medium text-gray-900 mb-1;
}

.week-indicator {
  @apply w-2 h-2 rounded-full mx-auto;
}

.week-current .week-number {
  @apply text-blue-700;
}
</style>