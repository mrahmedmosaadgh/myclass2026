<script setup>
import { ref, computed, nextTick } from 'vue';
import QrCode from '@/Components/Common/QrCode.vue';

const props = defineProps({
  groups: { type: Array, required: true },
  options: { type: Array, default: () => [] }
});

const printAreaRef = ref(null);
const a4PreviewScale = ref(1);

const printOptionIds = computed(() => {
  const ids = props.options.map((o) => String(o.id).toUpperCase());
  const unique = Array.from(new Set(ids));
  if (unique.length >= 4) return unique.slice(0, 4);
  return ['A', 'B', 'C', 'D'];
});

function getQrPayload(groupId, optId) {
  const g = String(groupId);
  const groupToken = g.toLowerCase().startsWith('g') ? g : `g${g}`;
  return `${groupToken}_${String(optId).toLowerCase()}`;
}

function updateA4PreviewScale() {
  const pageW = 210 * 3.7795275591;
  const pageH = 297 * 3.7795275591;
  const pad = 32;
  const availableW = Math.max(320, window.innerWidth - pad * 2);
  const availableH = Math.max(320, window.innerHeight - 120);
  const s = Math.min(1, availableW / pageW, availableH / pageH);
  a4PreviewScale.value = Number.isFinite(s) && s > 0 ? s : 1;
}

async function print() {
  await nextTick();
  if (!printAreaRef.value) return;
  document.body.classList.add('print-qr-mode');
  const cleanup = () => {
    document.body.classList.remove('print-qr-mode');
    window.removeEventListener('afterprint', cleanup);
  };
  window.addEventListener('afterprint', cleanup);
  window.print();
}

defineExpose({ print, updateA4PreviewScale, printAreaRef });
</script>

<template>
  <div class="qr-print-sheet">
    <div class="a4-stage">
      <div
        class="a4-page"
        ref="printAreaRef"
        :style="{ transform: `scale(${a4PreviewScale})`, transformOrigin: 'top center' }"
      >
        <div class="a4-header">
          <div class="a4-title">Group QR Codes</div>
          <div class="a4-sub">Scan format: g1_a</div>
        </div>

        <div class="a4-content">
          <div
            v-for="g in groups"
            :key="'print-g-' + g.id"
            class="qr-print-group"
          >
            <div class="qr-print-group-title" :style="{ borderColor: g.color }">
              <span class="qr-dot" :style="{ backgroundColor: g.color }" />
              <strong>{{ g.name }}</strong>
            </div>

            <div class="qr-print-grid">
              <div
                v-for="optId in printOptionIds"
                :key="'print-' + g.id + '-' + optId"
                class="qr-print-item"
              >
                <QrCode :value="getQrPayload(g.id, optId)" :size="110" level="H" />
                <div class="qr-print-meta">
                  <div class="qr-choice">{{ optId }}</div>
                  <div class="qr-payload">{{ getQrPayload(g.id, optId) }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.qr-print-sheet {
  overflow: auto;
  max-height: 70vh;
}
.a4-stage {
  display: flex;
  justify-content: center;
  padding: 16px;
}
.a4-page {
  width: 210mm;
  min-height: 297mm;
  background: #fff;
  box-shadow: 0 4px 20px rgba(0,0,0,0.1);
  padding: 20mm;
  box-sizing: border-box;
}
.a4-header {
  text-align: center;
  margin-bottom: 20px;
  padding-bottom: 12px;
  border-bottom: 2px solid #e2e8f0;
}
.a4-title {
  font-size: 1.5rem;
  font-weight: bold;
  color: #1e293b;
}
.a4-sub {
  color: #64748b;
  font-size: 0.875rem;
}
.qr-print-group {
  margin-bottom: 24px;
}
.qr-print-group-title {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 12px;
  border-left: 4px solid;
  background: #f8fafc;
  margin-bottom: 12px;
}
.qr-dot {
  width: 12px;
  height: 12px;
  border-radius: 50%;
}
.qr-print-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
}
.qr-print-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  padding: 12px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  background: #fff;
}
.qr-print-meta {
  text-align: center;
}
.qr-choice {
  font-weight: bold;
  font-size: 1.125rem;
  color: #334155;
}
.qr-payload {
  font-size: 0.75rem;
  color: #94a3b8;
  font-family: monospace;
}

@media print {
  .a4-page {
    transform: none !important;
    box-shadow: none;
    width: 210mm;
    height: 297mm;
  }
}
</style>
