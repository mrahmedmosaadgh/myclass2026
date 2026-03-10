<template>
  <div class="hero-header">
    <div class="hero-content">
      <div class="row items-center q-gutter-md">
        <q-btn
          flat
          dense
          round
          icon="menu"
          color="white"
          @click="$emit('toggle-sidebar')"
          class="lt-md"
        />
        
        <div class="col">
          <div class="text-h4 text-white text-weight-bold q-mb-xs">
            {{ title }}
          </div>
          <div class="text-subtitle1 text-white-8">
            {{ description }}
          </div>
        </div>

        <q-badge
          v-if="statusLabel"
          :color="statusColor"
          :label="statusLabel"
          class="text-weight-bold q-px-md q-py-sm"
          style="font-size: 0.9rem;"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    default: 'Loading...'
  },
  description: {
    type: String,
    default: ''
  },
  statusLabel: {
    type: String,
    default: ''
  },
  statusColor: {
    type: String,
    default: 'grey-6'
  }
});

defineEmits(['toggle-sidebar']);
</script>

<style scoped lang="scss">
.hero-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 2rem;
  box-shadow: 0 4px 20px rgba(102, 126, 234, 0.3);
}
</style>
