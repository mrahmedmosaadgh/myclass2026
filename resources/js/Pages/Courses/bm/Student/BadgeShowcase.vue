<template>
  <div class="bm-badge-showcase q-pa-md">
    <div class="text-h5 text-weight-bold text-dark q-mb-md">Your Badges</div>
    
    <div v-if="badges.length === 0" class="text-grey-7 text-center q-pa-lg bg-grey-2 rounded-borders">
      <q-icon name="emoji_events" size="3rem" color="grey-5" class="q-mb-sm" />
      <div>No badges earned yet. Complete assessments to unlock them!</div>
    </div>

    <div class="row q-col-gutter-md">
      <div v-for="(badge, index) in badges" :key="index" class="col-6 col-md-4 col-lg-3">
        <q-card class="badge-card text-center q-pa-md shadow-2" :class="`bg-${badge.color}-1`">
          <q-avatar size="60px" :color="badge.color" text-color="white" :icon="badge.icon" class="q-mb-sm shadow-3" />
          <div class="text-weight-bold text-dark q-mt-sm">{{ badge.name || formatType(badge.badge_type) }}</div>
          <div class="text-caption text-grey-8">{{ formatDate(badge.earned_at) }}</div>
        </q-card>
      </div>
    </div>
  </div>
</template>

<script setup>
defineOptions({ layout: BMLayout });
import BMLayout from "@/Layouts/BMLayout.vue";
const props = defineProps({
  badges: {
    type: Array,
    default: () => []
  }
});

const formatType = (type) => {
  return type.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
};

const formatDate = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
};
</script>

<style scoped>
.badge-card {
  border-radius: 16px;
  transition: transform 0.2s;
}
.badge-card:hover {
  transform: translateY(-4px);
}
</style>
