<script setup>
const props = defineProps({
  groups: { type: Array, required: true } // top 3 ranked groups
});

function getMedalColor(rank) {
  if (rank === 1) return '#f59e0b';
  if (rank === 2) return '#94a3b8';
  if (rank === 3) return '#d97706';
  return 'transparent';
}

function getPodiumHeight(rank) {
  if (rank === 1) return '160px';
  if (rank === 2) return '120px';
  if (rank === 3) return '90px';
  return '0px';
}

function getMedalIcon(rank) {
  if (rank === 1) return 'emoji_events';
  if (rank === 2) return 'military_tech';
  if (rank === 3) return 'workspace_premium';
  return '';
}
</script>

<template>
  <div class="podium-display row justify-center items-end q-gutter-md q-mb-lg">
    <!-- 2nd Place (left) -->
    <div v-if="groups[1]" class="podium-spot text-center">
      <q-avatar :style="{ border: '3px solid ' + groups[1].color }" size="48px" class="q-mb-sm">
        <span class="text-weight-bold text-grey-8">{{ groups[1].name.charAt(0).toUpperCase() }}</span>
      </q-avatar>
      <div class="text-subtitle2 text-weight-bold" :style="{ color: groups[1].color }">{{ groups[1].name }}</div>
      <div class="text-caption text-grey-7">{{ groups[1].score }} pts</div>
      <q-icon :name="getMedalIcon(2)" :color="getMedalColor(2)" size="28px" class="q-mt-xs" />
      <div class="podium-block q-mt-sm" :style="{ height: getPodiumHeight(2), backgroundColor: getMedalColor(2) }">
        <span class="rank-num">2</span>
      </div>
    </div>

    <!-- 1st Place (center, tallest) -->
    <div v-if="groups[0]" class="podium-spot spot-1st text-center">
      <q-avatar :style="{ border: '3px solid ' + groups[0].color }" size="56px" class="q-mb-sm">
        <span class="text-weight-bold text-grey-8">{{ groups[0].name.charAt(0).toUpperCase() }}</span>
      </q-avatar>
      <div class="text-subtitle1 text-weight-bold" :style="{ color: groups[0].color }">{{ groups[0].name }}</div>
      <div class="text-caption text-grey-7">{{ groups[0].score }} pts</div>
      <q-icon name="emoji_events" color="amber" size="32px" class="q-mt-xs" />
      <div class="podium-block q-mt-sm" :style="{ height: getPodiumHeight(1), backgroundColor: getMedalColor(1) }">
        <span class="rank-num">1</span>
      </div>
    </div>

    <!-- 3rd Place (right) -->
    <div v-if="groups[2]" class="podium-spot text-center">
      <q-avatar :style="{ border: '3px solid ' + groups[2].color }" size="48px" class="q-mb-sm">
        <span class="text-weight-bold text-grey-8">{{ groups[2].name.charAt(0).toUpperCase() }}</span>
      </q-avatar>
      <div class="text-subtitle2 text-weight-bold" :style="{ color: groups[2].color }">{{ groups[2].name }}</div>
      <div class="text-caption text-grey-7">{{ groups[2].score }} pts</div>
      <q-icon :name="getMedalIcon(3)" :color="getMedalColor(3)" size="28px" class="q-mt-xs" />
      <div class="podium-block q-mt-sm" :style="{ height: getPodiumHeight(3), backgroundColor: getMedalColor(3) }">
        <span class="rank-num">3</span>
      </div>
    </div>
  </div>
</template>

<style scoped>
.podium-display {
  min-height: 280px;
  padding: 16px;
}
.podium-spot {
  display: flex;
  flex-direction: column;
  align-items: center;
  min-width: 100px;
}
.spot-1st {
  transform: scale(1.1);
}
.podium-block {
  width: 80px;
  border-radius: 8px 8px 0 0;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: bold;
  font-size: 1.5rem;
  box-shadow: 0 -4px 12px rgba(0,0,0,0.1);
}
</style>
