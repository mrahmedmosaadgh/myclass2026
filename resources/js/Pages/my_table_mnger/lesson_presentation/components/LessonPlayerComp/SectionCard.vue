<template>
  <q-card
    flat
    bordered
    class="section-card"
    :class="{ 
      'active-section': isActive,
      'locked-section': isLocked
    }"
    @click="$emit('select')"
  >
    <q-card-section class="row items-center q-pa-md">
      <q-avatar
        size="48px"
        :style="{ 
          background: section.bg || '#e0e0e0',
          color: section.textColor || '#000'
        }"
      >
        <q-icon :name="section.qIcon || section.icon || 'folder'" size="24px" />
      </q-avatar>
      
      <div class="col q-ml-md">
        <div class="text-weight-bold">{{ section.title }}</div>
        <div class="text-caption text-grey-7">
          {{ slideCount }} slides
        </div>
      </div>

      <div>
        <!-- Completion Icon -->
        <q-icon
          v-if="isCompleted"
          name="check_circle"
          color="positive"
          size="24px"
        />
        <!-- Lock Icon -->
        <q-icon
          v-else-if="isLocked"
          name="lock"
          color="grey-5"
          size="20px"
        />
      </div>
    </q-card-section>
  </q-card>
</template>

<script setup>
defineProps({
  section: {
    type: Object,
    required: true
  },
  slideCount: {
    type: Number,
    default: 0
  },
  isActive: {
    type: Boolean,
    default: false
  },
  isCompleted: {
    type: Boolean,
    default: false
  },
  isLocked: {
    type: Boolean,
    default: false
  }
});

defineEmits(['select']);
</script>

<style scoped lang="scss">
.section-card {
  cursor: pointer;
  transition: all 0.3s ease;
  border-radius: 12px;
  
  &:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  }
  
  &.active-section {
    border: 2px solid #667eea;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
  }
  
  &.locked-section {
    opacity: 0.6;
    cursor: not-allowed;
  }
}
</style>
