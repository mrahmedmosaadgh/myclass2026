<template>
  <div class="relative">
    <!-- Lock/Unlock Control -->
    <div 
      class="absolute -top-1 scale-75 right-0 p-1" 
      :class="{ 'opacity-0 hover:opacity-100 transition-opacity': !locked }" 
      @click.stop="$emit('toggle-lock')"
    >
      <q-icon :name="locked ? 'lock' : 'lock_open'" size="xs" />
    </div>
    
    <!-- Toggle Control -->
    <div 
      class="absolute -top-2 scale-75 left-0 p-1" 
      :class="{ 'opacity-0 hover:opacity-100 transition-opacity': !disabled }" 
      @click.stop="$emit('toggle-disable')"
    >
      <q-icon :name="disabled ? 'toggle_on' : 'toggle_off'" size="xs" />
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  locked: {
    type: Boolean,
    default: false
  },
  disabled: {
    type: Boolean,
    default: false
  }
});

defineEmits(['toggle-lock', 'toggle-disable']);
</script>
