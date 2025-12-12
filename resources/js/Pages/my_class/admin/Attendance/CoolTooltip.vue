<script setup>
import { ref, onMounted, onUnmounted, computed, nextTick } from 'vue';

const props = defineProps({
  color: { type: String, default: 'white' }, // background
  direction: { type: String, default: 'top' }, // top, bottom, left, right
  delay: { type: Number, default: 300 }, // show delay
  trigger: { type: String, default: 'hover' }, // hover, click, both
  simple: { type: Boolean, default: false }, // no animation
  noAnimation: { type: Boolean, default: false }, // disable all animation
  fullWidth: { type: Boolean, default: false }, // content fills trigger
  autoFlip: { type: Boolean, default: true }, // flip if near edge
});

const showTooltip = ref(false);
const isInteracting = ref(false);
const timeoutId = ref(null);
const triggerEl = ref(null);
const tooltipEl = ref(null);
const placement = ref(props.direction);

function startHover() {
  if (props.trigger !== 'hover' && props.trigger !== 'both') return;
  clearTimeout(timeoutId.value);
  timeoutId.value = setTimeout(() => {
    showTooltip.value = true;
    nextTick(adjustPosition);
  }, props.delay);
}

function stopHover() {
  if (props.trigger !== 'hover' && props.trigger !== 'both') return;
  if (isInteracting.value) return;
  clearTimeout(timeoutId.value);
  timeoutId.value = setTimeout(() => {
    showTooltip.value = false;
  }, 500);
}

function toggleClick() {
  if (props.trigger !== 'click' && props.trigger !== 'both') return;
  showTooltip.value = !showTooltip.value;
  if (showTooltip.value) {
    nextTick(adjustPosition);
  }
}

function startInteraction() {
  isInteracting.value = true;
}
function stopInteraction() {
  isInteracting.value = false;
  stopHover();
}

function adjustPosition() {
  if (!props.autoFlip) return;
  const triggerRect = triggerEl.value?.getBoundingClientRect();
  const tooltipRect = tooltipEl.value?.getBoundingClientRect();
  if (!triggerRect || !tooltipRect) return;

  if (placement.value === 'top' && triggerRect.top < tooltipRect.height + 10) {
    placement.value = 'bottom';
  } else if (placement.value === 'bottom' && window.innerHeight - triggerRect.bottom < tooltipRect.height + 10) {
    placement.value = 'top';
  } else if (placement.value === 'left' && triggerRect.left < tooltipRect.width + 10) {
    placement.value = 'right';
  } else if (placement.value === 'right' && window.innerWidth - triggerRect.right < tooltipRect.width + 10) {
    placement.value = 'left';
  } else {
    placement.value = props.direction; // Reset if enough space
  }
}

onMounted(() => {
  document.addEventListener('click', (e) => {
    if (!triggerEl.value?.contains(e.target) && !tooltipEl.value?.contains(e.target)) {
      if (props.trigger === 'click' || props.trigger === 'both') {
        showTooltip.value = false;
      }
    }
  });
});
</script>

<template>
  <div class="relative inline-block">
    <div
      ref="triggerEl"
      @mouseenter="startHover"
      @mouseleave="stopHover"
      @click="toggleClick"
      class="cursor-pointer"
    >
      <slot />
    </div>

    <transition
      name="fade-scale"
      mode="out-in"
      appear
    >
      <div
        v-if="showTooltip"
        ref="tooltipEl"
        @mouseenter="startInteraction"
        @mouseleave="stopInteraction"
        class="absolute z-50 select-text p-3 rounded-lg shadow-xl text-sm"
        :style="{ backgroundColor: props.color }"
        :class="[
          'text-white',
          'max-w-xs',
          fullWidth ? 'w-full' : '',
          noAnimation ? '' : 'transition-all duration-200 ease-out',
          placement === 'top' ? 'bottom-full left-1/2 -translate-x-1/2 mb-2' : '',
          placement === 'bottom' ? 'top-full left-1/2 -translate-x-1/2 mt-2' : '',
          placement === 'left' ? 'right-full top-1/2 -translate-y-1/2 mr-2' : '',
          placement === 'right' ? 'left-full top-1/2 -translate-y-1/2 ml-2' : '',
        ]"
      >
        <div class="relative">
          <slot name="content" />
          <div
            class="absolute w-3 h-3 bg-inherit rotate-45"
            :class="[
              placement === 'top' ? 'bottom-0 left-1/2 -translate-x-1/2 translate-y-1/2' : '',
              placement === 'bottom' ? 'top-0 left-1/2 -translate-x-1/2 -translate-y-1/2' : '',
              placement === 'left' ? 'right-0 top-1/2 -translate-y-1/2 translate-x-1/2' : '',
              placement === 'right' ? 'left-0 top-1/2 -translate-y-1/2 -translate-x-1/2' : '',
            ]"
          ></div>
        </div>
      </div>
    </transition>
  </div>
</template>

<style scoped>
.fade-scale-enter-active, .fade-scale-leave-active {
  transition: all 0.2s ease;
}
.fade-scale-enter-from, .fade-scale-leave-to {
  opacity: 0;
  transform: scale(0.95);
}
</style>
