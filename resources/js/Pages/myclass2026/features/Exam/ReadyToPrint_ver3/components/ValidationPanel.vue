<template>
  <div class="validation-panel">
    <div class="panel-header">
      <h4>{{ $t('exam.readyToPrint.validation.title') }}</h4>
      <div class="summary-badges">
        <q-badge v-if="summary.errors" color="negative" :label="`${summary.errors} ${$t('exam.readyToPrint.validation.errors')}`" />
        <q-badge v-if="summary.warnings" color="warning" :label="`${summary.warnings} ${$t('exam.readyToPrint.validation.warnings')}`" />
        <q-badge v-if="summary.infos" color="info" :label="`${summary.infos} ${$t('exam.readyToPrint.validation.infos')}`" />
      </div>
    </div>
    <q-separator />
    <div class="validation-items">
      <div
        v-for="(item, index) in validation.items"
        :key="index"
        class="validation-item"
        :class="`severity-${item.severity}`"
      >
        <q-icon
          :name="severityIcon(item.severity)"
          :color="severityColor(item.severity)"
          class="item-icon"
        />
        <div class="item-content">
          <div class="item-message">{{ item.message }}</div>
          <div class="item-scope">{{ $t(`exam.readyToPrint.validation.scopes.${item.scope}`) }}</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { useExamReadyToPrintStore } from '@/Stores/examReadyToPrintStore'

const { t } = useI18n()
const store = useExamReadyToPrintStore()

const validation = computed(() => store.validation)
const summary = computed(() => validation.value.summary)

function severityIcon(severity) {
  switch (severity) {
    case 'error': return 'error'
    case 'warn': return 'warning'
    case 'info': return 'info'
    default: return 'info'
  }
}

function severityColor(severity) {
  switch (severity) {
    case 'error': return 'negative'
    case 'warn': return 'warning'
    case 'info': return 'info'
    default: return 'info'
  }
}
</script>

<style scoped>
.validation-panel {
  height: 100%;
  display: flex;
  flex-direction: column;
}

.panel-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 16px;
}

.panel-header h4 {
  margin: 0;
  font-size: 14px;
  font-weight: 600;
}

.summary-badges {
  display: flex;
  gap: 8px;
}

.validation-items {
  flex: 1;
  overflow-y: auto;
  padding: 8px;
}

.validation-item {
  display: flex;
  align-items: flex-start;
  gap: 8px;
  padding: 8px;
  border-radius: 6px;
  margin-bottom: 4px;
}

.validation-item.severity-error {
  background: #fef2f2;
  border-left: 3px solid #ef4444;
}

.validation-item.severity-warn {
  background: #fffbeb;
  border-left: 3px solid #f59e0b;
}

.validation-item.severity-info {
  background: #f0f9ff;
  border-left: 3px solid #3b82f6;
}

.item-icon {
  margin-top: 2px;
}

.item-content {
  flex: 1;
}

.item-message {
  font-size: 13px;
  font-weight: 500;
  margin-bottom: 2px;
}

.item-scope {
  font-size: 11px;
  color: #666;
  text-transform: uppercase;
}
</style>
