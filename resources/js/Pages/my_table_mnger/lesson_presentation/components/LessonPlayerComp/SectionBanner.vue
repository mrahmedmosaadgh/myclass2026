<template>
  <q-card
    v-if="section"
    flat
    class="section-banner"
    :style="{ 
      background: `linear-gradient(135deg, ${section.bg} 0%, ${section.bg} 100%)`,
      color: section.textColor
    }"
  >
    <q-card-section class="row items-center q-pa-lg">
      <q-avatar size="64px" color="white" :text-color="section.bg">
        <q-icon :name="section.qIcon || section.icon" size="32px" />
      </q-avatar>
      
      <div class="col q-ml-md">
        <div class="text-overline opacity-80">Current Section</div>
        <div class="text-h5 text-weight-bold">{{ section.title }}</div>
      </div>

      <q-linear-progress
        v-if="totalSlides > 0"
        :value="(currentSlide + 1) / totalSlides"
        color="white"
        track-color="rgba(255,255,255,0.3)"
        size="8px"
        rounded
        class="q-mt-md"
        style="width: 200px;"
      />
    </q-card-section>
  </q-card>
</template>

<script setup>
defineProps({
  section: {
    type: Object,
    default: null
  },
  currentSlide: {
    type: Number,
    default: 0
  },
  totalSlides: {
    type: Number,
    default: 0
  }
});
</script>

<style scoped lang="scss">
.section-banner {
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}
</style>
