`<template>
  <div 
    class="bg-white rounded-lg shadow-md p-4 transition-shadow duration-200"
    :class="{
      'hover:shadow-lg': isAttending,
      'opacity-60 cursor-not-allowed': !isAttending
    }"
  >
    <div class="flex items-center space-x-4">
      <div class="flex-shrink-0">
        <img :src="student.avatar || '/default-avatar.png'" class="h-12 w-12 rounded-full" :alt="student.name">
      </div>
      <div class="flex-1">
        <h3 class="text-lg font-semibold text-gray-900">{{ student.name }}</h3>
        <p class="text-sm text-gray-600">ID: {{ student.id }}</p>
      </div>
      <div class="flex-shrink-0">
        <div v-if="!isAttending" class="text-sm text-red-500">Not Attending</div>
        <slot name="actions" v-else></slot>
      </div>
    </div>
    <div class="mt-4 grid grid-cols-2 gap-4">
      <!-- <div class="text-sm">
        <span class="text-gray-500">Class:</span>
        <span class="ml-2 text-gray-900">{{ student }}</span>
      </div> -->
      <!-- <div class="text-sm">
        <span class="text-gray-500">Grade:</span>
        <span class="ml-2 text-gray-900">{{ student.grade.name }}</span>
      </div> -->
      <div class="text-sm">
        <span class="text-gray-500">Status:</span>
        <span class="ml-2" :class="statusClass">{{ student.status }}</span>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'StudentCard',
  props: {
    student: {
      type: Object,
      required: true
    }
  },
  computed: {
    isAttending() {
      return this.student.attend === 1
    },
    statusClass() {
      return {
        'text-green-600': this.student.status === 'active',
        'text-red-600': this.student.status === 'inactive',
        'text-yellow-600': this.student.status === 'pending'
      }
    }
  }
}
</script>`