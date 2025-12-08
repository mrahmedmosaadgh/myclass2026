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
          <span class="period-badge   px-2 rounded-full text-white absolute scale-75 -top-2 right-0">
              {{ option?.schedule?.period_order }}
          </span>

        <h3 class="subject-name flex flex-row  scale-75   text-white px-1 rounded-full">
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
  @apply relative rounded-xl p-4;
  background-color: var(--bg-color);
  color: var(--text-color);
  transform-style: preserve-3d;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  backdrop-filter: blur(4px);
  /* box-shadow:
    0 10px 20px rgba(0, 0, 0, 0.1),
    0 6px 6px rgba(0, 0, 0, 0.06),
    0 0 100px -10px var(--bg-color);
  border: 1px solid rgba(255, 255, 255, 0.1); */
}

/* .schedule-card:hover .card-content { */
    /* scale: 1.1; */
  /* transform: translateY(-5px) scale(1.02); */
  /* box-shadow:
    0 20px 25px -5px rgba(0, 0, 0, 0.15),
    0 10px 10px -5px rgba(0, 0, 0, 0.08),
    0 0 150px -10px var(--bg-color);
} */
/* } 0000000000000000000000000000000000000000000000000000000*/
/* Glass effect overlay */
.card-content::before {
  content: '';
  @apply absolute inset-0 rounded-xl;
  background: linear-gradient(
    135deg,
    rgba(255, 255, 255, 0.2) 0%,
    rgba(255, 255, 255, 0.05) 50%,
    rgba(255, 255, 255, 0) 100%
  );
  z-index: 1;
}

/* Inner glass highlight */
.card-content::after {
  content: '';
  @apply absolute rounded-xl;
  inset: 1px;
  background: linear-gradient(
    -45deg,
    rgba(255, 255, 255, 0) 0%,
    rgba(255, 255, 255, 0.1) 100%
  );
  z-index: 0;
}

.card-header {
  @apply flex justify-between items-center mb-3 relative z-10;
}

.classroom-badge {
  @apply px-3 py-1 rounded-full text-sm font-medium;
  background: rgba(255, 255, 255, 0.15);
  backdrop-filter: blur(4px);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.period-badge {
  @apply px-3 py-0.5 text-sm font-semibold;
  background: rgba(0, 0, 0, 0.75);
  backdrop-filter: blur(4px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.subject-name {
  @apply text-base font-semibold relative z-10 transition-all duration-300;
}

.subject-container {
  @apply relative z-10 mb-4;
}

/* Selected state with improved visual feedback */
.selected .card-content {
  @apply ring-4;
  ring-color: rgba(34, 197, 94, 0.5);
  animation: selectGlow 2s infinite;
}

@keyframes selectGlow {
  0% { box-shadow: 0 0 20px rgba(34, 197, 94, 0.2); }
  50% { box-shadow: 0 0 30px rgba(34, 197, 94, 0.4); }
  100% { box-shadow: 0 0 20px rgba(34, 197, 94, 0.2); }
}

/* Responsive improvements */
@media (max-width: 640px) {
  .card-content {
    @apply p-3;
  }

  .subject-name {
    @apply text-sm;
  }

  .classroom-badge,
  .period-badge {
    @apply text-xs px-2;
  }
}

/* Loading state enhancement */
.schedule-card.loading {
  @apply animate-pulse;
  background: linear-gradient(
    90deg,
    rgba(255, 255, 255, 0.1),
    rgba(255, 255, 255, 0.2),
    rgba(255, 255, 255, 0.1)
  );
  background-size: 200% 100%;
  animation: loading 1.5s infinite;
}

@keyframes loading {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}

/* Error state enhancement */
.schedule-card.error .card-content {
  border: 2px solid rgba(239, 68, 68, 0.5);
  box-shadow: 0 0 20px rgba(239, 68, 68, 0.2);
}

/* Additional hover effect for interactive elements */
.subject-name:hover,
.classroom-badge:hover,
.period-badge:hover {
  transform: scale(1.05);
  filter: brightness(1.1);
}
</style>


