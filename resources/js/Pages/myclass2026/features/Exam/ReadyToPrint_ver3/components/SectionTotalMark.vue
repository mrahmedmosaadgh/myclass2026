<template>
  <div class="section-total-mark">
    <template v-if="template === 'box'">
      <div class="section-total-box">
        <div class="section-total-box-top" :style="boxTopStyle"></div>
        <div class="section-total-box-value">{{ safeTotal }}</div>
      </div>
    </template>

    <template v-else>
      <div class="section-total-text">
        {{ prefix }} {{ safeTotal }} {{ suffix }}
      </div>
    </template>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  total: {
    type: Number,
    default: 0
  },
  options: {
    type: Object,
    default: () => ({})
  }
})

const template = computed(() => props.options?.template || 'text')
const prefix = computed(() => props.options?.prefix ?? 'Total:')
const suffix = computed(() => props.options?.suffix ?? 'marks')

const safeTotal = computed(() => {
  const value = Number(props.total)
  return Number.isFinite(value) ? value : 0
})

const boxTopStyle = computed(() => {
  const n = Number(props.options?.boxTopHeightPt)
  const heightPt = Number.isFinite(n) ? n : 22
  return {
    height: heightPt + 'pt'
  }
})
</script>

<style scoped>
.section-total-mark {
  margin: 0 0 10pt;
}

.section-total-text {
  color: #444;
  font-size: 11pt;
}

.section-total-box {
  width: 54px;
  border: 2px solid #1f3a5a;
  display: flex;
  flex-direction: column;
}

.section-total-box-top {
  height: 22px;
  border-bottom: 2px solid rgba(31, 58, 90, 0.4);
}

.section-total-box-value {
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 16pt;
}
</style>
