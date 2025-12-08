<template>
  <div
  class="schedule-card"
  :class="[my_class, { 'selected': model === option }]"
  >
  <!-- <button  class="p-4 m-2" @click="$emit('remove_session',option)">x</button> -->
  <div

      class="card-content"
      :style="`
        --bg-color: ${option?.cst?.subject?.color_bg || '#4F46E5'};
        --text-color: ${option?.cst?.subject?.color_text || '#ffffff'};
      `"
    ><div class="p-0 relative">
    <slot name="main"/>

</div>
      <!-- Header -->
      <div class="card-header">
        <span class="classroom-badge">
          <!-- {{ option?.cst?.classroom?.name }}hhhh -->
        </span>
    </div>

    <!-- Subject -->
    <div class="subject-container relative">
          <span class="period-badge bg-black px-2 rounded-full text-white absolute scale-75 -top-2 right-0">
              {{ option?.schedule?.period_order }}
          </span>

        <h3 class="subject-name flex flex-row  scale-75 bg-black text-white px-2 rounded-full">
            <div class="px-1 m-auto">
   {{ option?.cst?.subject?.name }}
</div>

<div class="px-2 my-1 bg-green-200 text-black   rounded-full ">
 {{ option?.cst?.classroom?.name }}
</div>


        </h3>
      </div>

      <!-- Teacher -->
       <div class="        ">
        <!-- <div class="-p-8 relative h-12 w-full overflow-hidden ">

            <div class="p-0 absolute top-0 -translate-x-1/2 left-1/2 h-12 w-32 bg-blue-600 "></div>
</div> -->

            <div class="  absolute top-0 left-0">
                <NameAbbreviator
                :full-name="option?.cst?.teacher?.name"
                separator=" "
                :letters_count="2"
                />
            </div>

        </div>

      <!-- Selection Indicator -->
      <!-- <div class="selection-indicator">
        <svg
          v-if="model === option"
          class="check-icon"
          viewBox="0 0 20 20"
          fill="currentColor"
        >
          <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
        </svg>

      </div> -->
    </div>
  </div>
</template>

<script setup>
import NameAbbreviator from './NameAbbreviator2.vue';

const model = defineModel();
const emit = defineEmits([ 'remove_session']);

defineProps({
  name: {
    type: String,
    required: true
  },
  my_class: {
    type: String,
    default: ''
  },
  option: {
    type: Object,
    required: true
  }
});
</script>

<style scoped>
.schedule-card {
  @apply relative cursor-pointer transition-all duration-300 ease-in-out;
  perspective: 1000px;
}

.card-content {
  @apply relative   rounded-xl shadow-lg  ;
  background-color: var(--bg-color);
  color: var(--text-color);
  transform-style: preserve-3d;
  transition: all 0.1s ease;
}

.schedule-card:hover .card-content {
  @apply shadow-xl;
  transform: translateY(-2px);
}

.card-header {
  @apply flex justify-between items-center mb-3;
}

.classroom-badge {
  @apply px-2 py-1 rounded-lg text-sm font-medium;
  /* background-color: rgba(255, 255, 255, 0.2); */
}

.period-badge {
  @apply px-2   text-sm font-semibold;
  /* background-color: rgba(0, 0, 0, 0.1); */
}





.selection-indicator {
  @apply absolute top-2 right-2 w-6 h-6 flex items-center justify-center;
}

.check-icon {
  @apply w-5 h-5 text-green-500;
  filter: drop-shadow(0 0 2px rgba(0, 0, 0, 0.3));
}

/* Selected state */
.selected .card-content {
  @apply ring-4 ring-green-500 ring-opacity-50;
}

/* Glass effect */
.card-content::before {
  content: '';
  @apply absolute inset-0 opacity-20;
  background: linear-gradient(
    45deg,
    transparent 0%,
    rgba(255, 255, 255, 0.1) 100%
  );
}

/* Responsive design */
@media (max-width: 640px) {
  .card-content {
    @apply p-3;
  }

  .subject-name {
    @apply text-base;
  }

  .classroom-badge,
  .period-badge {
    @apply text-xs;
  }
}

/* Animation for selection */
@keyframes selectPulse {
  0% { transform: scale(1); }
  50% { transform: scale(1.02); }
  100% { transform: scale(1); }
}

.selected {
  animation: selectPulse 0.3s ease-in-out;
}

/* Hover effects */
.schedule-card:hover .card-content::before {
  @apply opacity-30;
}

/* Loading state */
.schedule-card.loading {
  @apply animate-pulse;
}

/* Error state */
.schedule-card.error .card-content {
  @apply border-red-500 border-2;
}
</style>

