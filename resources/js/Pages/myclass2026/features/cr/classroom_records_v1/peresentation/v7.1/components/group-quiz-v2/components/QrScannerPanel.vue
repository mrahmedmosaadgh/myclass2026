<script setup>
const props = defineProps({
  scannerState: { type: String, default: 'idle' }, // 'idle' | 'waiting' | 'received'
  lastRaw: { type: String, default: '' },
  lastResult: { type: String, default: '' }
});
</script>

<template>
  <q-card flat bordered class="scanner-panel">
    <q-card-section class="q-py-sm row items-center">
      <q-icon
        :name="scannerState === 'waiting' ? 'qr_code_scanner' : (scannerState === 'received' ? 'check_circle' : 'qr_code')"
        :color="scannerState === 'waiting' ? 'positive' : (scannerState === 'received' ? 'info' : 'grey-5')"
        size="20px"
        class="q-mr-sm"
      />

      <div class="col">
        <div class="text-caption text-weight-bold text-grey-8">
          Scanner:
          <span v-if="scannerState === 'waiting'" class="text-positive">Ready</span>
          <span v-else-if="scannerState === 'received'" class="text-info">Received</span>
          <span v-else class="text-grey">Idle</span>
        </div>
        <div v-if="lastRaw" class="text-caption text-grey-7">
          Last: <strong>{{ lastRaw }}</strong>
        </div>
        <div v-if="lastResult" class="text-caption text-positive">
          {{ lastResult }}
        </div>
        <div v-if="!lastRaw && scannerState === 'waiting'" class="text-caption text-grey-5">
          Scan QR or type code, then press Enter
        </div>
      </div>
    </q-card-section>
  </q-card>
</template>

<style scoped>
.scanner-panel {
  background: #f8fafc;
}
</style>
