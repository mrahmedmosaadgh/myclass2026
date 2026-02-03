<template>
  <div class="w-full">
    <div class="flex justify-between text-sm mb-1">
      <span>SmartScore: {{ smartScore }}</span>
      <span>{{ masteryLevel }}</span>
    </div>
    <div class="w-full bg-gray-200 rounded-full h-4 overflow-hidden">
      <transition 
        enter-active-class="transition-all duration-500 ease-out"
        leave-active-class="transition-all duration-150 ease-in"
        enter-from-class="transform scale-0"
        enter-to-class="transform scale-100"
        leave-from-class="transform scale-100"
        leave-to-class="transform scale-0"
      >
        <div 
          class="h-full rounded-full flex items-center justify-center text-xs font-bold text-white"
          :style="{ width: `${Math.min(100, Math.max(0, smartScore))}%` }"
          :class="getScoreColorClass(smartScore)"
        >
          <span v-if="smartScore > 10">{{ smartScore }}</span>
        </div>
      </transition>
    </div>
  </div>
</template>

<script>
export default {
  name: 'SmartScoreBar',
  props: {
    smartScore: {
      type: Number,
      default: 0
    },
    masteryLevel: {
      type: String,
      default: 'Beginner'
    }
  },
  methods: {
    getScoreColorClass(score) {
      if (score < 20) return 'bg-red-500';
      if (score < 50) return 'bg-orange-500';
      if (score < 80) return 'bg-yellow-500';
      if (score < 100) return 'bg-green-500';
      return 'bg-purple-600'; // Master level
    }
  }
};
</script>