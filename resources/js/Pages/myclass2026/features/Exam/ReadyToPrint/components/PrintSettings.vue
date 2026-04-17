<template>
  <div class="print-settings">
    <h3>{{ $t('exam.readyToPrint.settings.title') }}</h3>
    
    <div class="settings-section">
      <h4>{{ $t('exam.readyToPrint.settings.pageSetup') }}</h4>
      <q-form class="settings-form">
        <q-select
          v-model="pageSetup.paper"
          :options="paperOptions"
          :label="$t('exam.readyToPrint.settings.paperSize')"
          outlined
          dense
          emit-value
          map-options
          @update:model-value="updatePageSetup"
        />
        
        <div class="margins-group">
          <div class="group-label">{{ $t('exam.readyToPrint.settings.margins') }} (mm)</div>
          <div class="margins-row">
            <q-input
              v-model.number="pageSetup.marginsMm.top"
              type="number"
              label="Top"
              outlined
              dense
              @update:model-value="updatePageSetup"
            />
            <q-input
              v-model.number="pageSetup.marginsMm.right"
              type="number"
              label="Right"
              outlined
              dense
              @update:model-value="updatePageSetup"
            />
            <q-input
              v-model.number="pageSetup.marginsMm.bottom"
              type="number"
              label="Bottom"
              outlined
              dense
              @update:model-value="updatePageSetup"
            />
            <q-input
              v-model.number="pageSetup.marginsMm.left"
              type="number"
              label="Left"
              outlined
              dense
              @update:model-value="updatePageSetup"
            />
          </div>
        </div>

        <q-input
          v-model.number="pageSetup.headerHeightMm"
          type="number"
          :label="$t('exam.readyToPrint.settings.headerHeight')"
          outlined
          dense
          suffix="mm"
          @update:model-value="updatePageSetup"
        />
        
        <q-input
          v-model.number="pageSetup.footerHeightMm"
          type="number"
          :label="$t('exam.readyToPrint.settings.footerHeight')"
          outlined
          dense
          suffix="mm"
          @update:model-value="updatePageSetup"
        />
      </q-form>
    </div>

    <q-separator />

    <div class="settings-section">
      <h4>{{ $t('exam.readyToPrint.settings.header') }}</h4>
      <q-form class="settings-form">
        <q-select
          v-model="headerConfig.mode"
          :options="headerModeOptions"
          :label="$t('exam.readyToPrint.settings.headerMode')"
          outlined
          dense
          emit-value
          map-options
          @update:model-value="updateHeaderConfig"
        />
      </q-form>
    </div>

    <q-separator />

    <div class="settings-section">
      <h4>{{ $t('exam.readyToPrint.settings.footer') }}</h4>
      <q-form class="settings-form">
        <q-select
          v-model="footerConfig.pageNumbering"
          :options="pageNumberingOptions"
          :label="$t('exam.readyToPrint.settings.pageNumbering')"
          outlined
          dense
          emit-value
          map-options
          @update:model-value="updateFooterConfig"
        />
      </q-form>
    </div>

    <q-separator />

    <div class="settings-section">
      <h4>{{ $t('exam.readyToPrint.settings.layout') }}</h4>
      <q-form class="settings-form">
        <q-select
          v-model="layoutDefaults.paginationMode"
          :options="paginationModeOptions"
          :label="$t('exam.readyToPrint.settings.paginationMode')"
          outlined
          dense
          emit-value
          map-options
          @update:model-value="updateLayoutDefaults"
        />
        
        <q-select
          v-model="layoutDefaults.overflowStrategy"
          :options="overflowStrategyOptions"
          :label="$t('exam.readyToPrint.settings.overflowStrategy')"
          outlined
          dense
          emit-value
          map-options
          @update:model-value="updateLayoutDefaults"
        />
      </q-form>
    </div>
  </div>
</template>

<script setup>
import { computed, reactive } from 'vue'
import { useI18n } from 'vue-i18n'
import { useExamReadyToPrintStore } from '@/Stores/examReadyToPrintStore'

const { t } = useI18n()
const store = useExamReadyToPrintStore()

const pageSetup = reactive({ ...store.exam.pageSetup })
const headerConfig = reactive({ ...store.exam.headerConfig })
const footerConfig = reactive({ ...store.exam.footerConfig })
const layoutDefaults = reactive({ ...store.exam.layoutDefaults })

const paperOptions = [
  { label: 'A4', value: 'A4' },
  { label: 'Letter', value: 'Letter' },
]

const headerModeOptions = [
  { label: t('exam.readyToPrint.headerModes.none'), value: 'none' },
  { label: t('exam.readyToPrint.headerModes.firstPageOnly'), value: 'first_page_only' },
  { label: t('exam.readyToPrint.headerModes.allPages'), value: 'all_pages' },
  { label: t('exam.readyToPrint.headerModes.customPerPage'), value: 'custom_per_page' },
]

const pageNumberingOptions = [
  { label: 'X of Y', value: 'x_of_y' },
  { label: 'X / Y', value: 'x_slash_y' },
  { label: 'Page X', value: 'page_x' },
]

const paginationModeOptions = [
  { label: t('exam.readyToPrint.paginationModes.manual'), value: 'manual' },
  { label: t('exam.readyToPrint.paginationModes.auto'), value: 'auto' },
  { label: t('exam.readyToPrint.paginationModes.hybrid'), value: 'hybrid' },
]

const overflowStrategyOptions = [
  { label: t('exam.readyToPrint.overflowStrategies.moveToNextPage'), value: 'move_to_next_page' },
  { label: t('exam.readyToPrint.overflowStrategies.splitAllowed'), value: 'split_allowed' },
  { label: t('exam.readyToPrint.overflowStrategies.scaleDown'), value: 'scale_down' },
  { label: t('exam.readyToPrint.overflowStrategies.rejectAtValidation'), value: 'reject_at_validation' },
]

function updatePageSetup() {
  store.exam.pageSetup = { ...pageSetup }
  store.markDirty()
}

function updateHeaderConfig() {
  store.exam.headerConfig = { ...headerConfig }
  store.markDirty()
}

function updateFooterConfig() {
  store.exam.footerConfig = { ...footerConfig }
  store.markDirty()
}

function updateLayoutDefaults() {
  store.exam.layoutDefaults = { ...layoutDefaults }
  store.markDirty()
}
</script>

<style scoped>
.print-settings {
  padding: 16px;
}

.print-settings h3 {
  margin-top: 0;
  margin-bottom: 20px;
  font-size: 16px;
  font-weight: 600;
}

.settings-section {
  margin-bottom: 24px;
}

.settings-section h4 {
  margin-top: 0;
  margin-bottom: 12px;
  font-size: 14px;
  font-weight: 500;
  color: #333;
}

.settings-form {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.margins-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.group-label {
  font-size: 12px;
  font-weight: 500;
  color: #666;
}

.margins-row {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr 1fr;
  gap: 8px;
}
</style>
