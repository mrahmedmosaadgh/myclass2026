<script setup>
import { computed, nextTick, onMounted, ref } from 'vue';
import QrCode from '@/Components/Common/QrCode.vue';
import html2canvas from 'html2canvas';
import jsPDF from 'jspdf';

const STORAGE_KEY = 'builder-v7-group-qr-print-data';

const groups = ref([]);
const printAreaRef = ref(null);

const printOptionIds = computed(() => ['A', 'B', 'C', 'D']);

function getQrPayload(groupId, optId) {
  // Keep consistent with scanner parsing (expects g1_a style)
  // If groupId already starts with 'g', keep it; else prefix with 'g'.
  const g = String(groupId);
  const groupToken = g.toLowerCase().startsWith('g') ? g : `g${g}`;
  return `${groupToken}_${String(optId).toLowerCase()}`;
}

function loadFromSession() {
  try {
    const raw = localStorage.getItem(STORAGE_KEY);
    if (!raw) return;
    const parsed = JSON.parse(raw);
    if (parsed && Array.isArray(parsed.groups)) {
      groups.value = parsed.groups;
    }
  } catch {
    // ignore
  }
}

async function printQrCodes() {
  await nextTick();
  if (!printAreaRef.value) return;

  document.body.classList.add('print-group-qr-mode');
  const cleanup = () => {
    document.body.classList.remove('print-group-qr-mode');
    window.removeEventListener('afterprint', cleanup);
  };
  window.addEventListener('afterprint', cleanup);
  window.print();
}

async function downloadQrCodesPdf() {
  await nextTick();
  const el = printAreaRef.value;
  if (!el) return;

  const canvas = await html2canvas(el, { backgroundColor: '#ffffff', scale: 2 });
  const imgData = canvas.toDataURL('image/png');

  const pdf = new jsPDF({ orientation: 'p', unit: 'mm', format: 'a4' });
  pdf.addImage(imgData, 'PNG', 0, 0, 210, 297);
  pdf.save('group-qr-codes-a4.pdf');
}

onMounted(() => {
  loadFromSession();
});
</script>

<template>
  <div class="page">
    <div class="topbar">
      <div class="title">
        <strong>Group QR Codes</strong>
        <span class="sub">A4 print page</span>
      </div>

      <div class="actions">
        <button class="btn" @click="printQrCodes">Print</button>
        <button class="btn btn-dark" @click="downloadQrCodesPdf">Download PDF</button>
      </div>
    </div>

    <div v-if="groups.length === 0" class="empty">
      <strong>No groups data found.</strong>
      <div class="hint">Open this page using the “Open in New Page” button from the Group Setup modal.</div>
    </div>

    <div class="stage" v-else>
      <div class="a4-page" ref="printAreaRef">
        <div class="a4-header">
          <div class="a4-title">Group QR Codes</div>
          <div class="a4-sub">Scan format: g1_a</div>
        </div>

        <div class="a4-content">
          <div v-for="g in groups" :key="'g-qr-' + g.id" class="qr-print-group">
            <div class="qr-print-group-title" :style="{ borderColor: g.color }">
              <span class="qr-dot" :style="{ backgroundColor: g.color }"></span>
              <strong>{{ g.name }}</strong>
            </div>

            <div class="qr-print-grid">
              <div v-for="optId in printOptionIds" :key="'qr-' + g.id + '-' + optId" class="qr-print-item">
                <QrCode :value="getQrPayload(g.id, optId)" :size="120" level="H" />
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
.page {
  min-height: 100vh;
  background: #f1f5f9;
}

.topbar {
  position: sticky;
  top: 0;
  z-index: 10;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  padding: 12px 16px;
  background: white;
  border-bottom: 1px solid #e2e8f0;
}

.title {
  display: flex;
  flex-direction: column;
  gap: 2px;
  color: #0f172a;
}

.sub {
  font-size: 12px;
  color: #64748b;
  font-weight: 700;
}

.actions {
  display: flex;
  gap: 8px;
  align-items: center;
}

.btn {
  background: #3b82f6;
  color: white;
  border: none;
  border-radius: 10px;
  padding: 10px 14px;
  cursor: pointer;
  font-weight: 800;
}

.btn-dark {
  background: #0f172a;
}

.stage {
  display: flex;
  justify-content: center;
  padding: 16px;
}

.a4-page {
  width: 210mm;
  min-height: 297mm;
  background: white;
  border: 1px solid #e2e8f0;
  box-shadow: 0 10px 25px rgba(0,0,0,0.12);
  padding: 12mm;
}

.a4-header {
  display: flex;
  justify-content: space-between;
  align-items: baseline;
  padding-bottom: 8mm;
  border-bottom: 1px dashed #cbd5e1;
  margin-bottom: 8mm;
}

.a4-title {
  font-weight: 900;
  font-size: 18px;
  color: #0f172a;
}

.a4-sub {
  font-size: 12px;
  color: #64748b;
  font-weight: 700;
}

.a4-content {
  display: flex;
  flex-direction: column;
  gap: 10mm;
}

.qr-print-group {
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  padding: 10px;
}

.qr-print-group-title {
  display: flex;
  align-items: center;
  gap: 8px;
  padding-bottom: 8px;
  border-bottom: 2px solid;
  margin-bottom: 10px;
  color: #0f172a;
}

.qr-dot {
  width: 10px;
  height: 10px;
  border-radius: 999px;
}

.qr-print-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 6mm;
  justify-items: center;
}

.qr-print-item {
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  padding: 3mm;
  background: #fff;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px;
  min-width: 0;
}

.qr-print-meta {
  width: 100%;
  text-align: center;
}

.qr-choice {
  font-weight: 900;
  color: #0f172a;
}

.qr-payload {
  font-size: 11px;
  color: #64748b;
  word-break: break-all;
}

.empty {
  max-width: 720px;
  margin: 30px auto;
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 20px;
  color: #0f172a;
}

.hint {
  margin-top: 6px;
  color: #64748b;
  font-weight: 700;
  font-size: 13px;
}

@media print {
  @page {
    size: A4;
    margin: 10mm;
  }

  :global(body.print-group-qr-mode) * {
    visibility: hidden !important;
  }

  :global(body.print-group-qr-mode) .a4-page,
  :global(body.print-group-qr-mode) .a4-page * {
    visibility: visible !important;
  }

  :global(body.print-group-qr-mode) .a4-page {
    position: fixed;
    left: 0;
    top: 0;
    width: 210mm;
    min-height: 297mm;
    box-shadow: none;
    border: none;
    padding: 0;
  }
}
</style>
