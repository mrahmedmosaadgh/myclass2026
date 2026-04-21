<template>
  <q-dialog v-model="open" maximized transition-show="slide-up" transition-hide="slide-down">
    <div class="lpp-root">

      <!-- ─── Top bar ─────────────────────────────────────────────── -->
      <div class="lpp-topbar">
        <div class="lpp-topbar-left">
          <q-icon name="article" size="18px" class="q-mr-xs" />
          <span class="lpp-title">Live Print Preview</span>
          <q-badge
            v-if="pageCount > 0"
            color="blue-grey-7"
            class="q-ml-sm"
            style="font-size:11px"
          >
            {{ pageCount }} page{{ pageCount !== 1 ? 's' : '' }}
          </q-badge>
          <q-badge color="blue-grey-8" class="q-ml-xs" style="font-size:11px">
            A4 · 210 × 297 mm
          </q-badge>
        </div>

        <div class="lpp-topbar-right">
          <q-btn flat dense color="grey-4" icon="refresh" label="Refresh" @click="refresh" :loading="loading" />
          <q-separator dark vertical inset class="q-mx-sm" />
          <q-btn color="primary" icon="print" label="Print" dense @click="onPrint" />
          <q-btn flat round dense color="grey-5" icon="close" class="q-ml-sm" @click="open = false" />
        </div>
      </div>

      <!-- ─── Body: sidebar + canvas ──────────────────────────────── -->
      <div class="lpp-body">

        <!-- Sidebar controls -->
        <div class="lpp-sidebar">
          <div class="lpp-sidebar-section">
            <div class="lpp-sidebar-label">
              <q-icon name="expand" size="14px" class="q-mr-xs" />
              Page top margin
            </div>
            <div class="lpp-sidebar-hint">Space from header bottom to first line of content</div>

            <div class="row items-center q-gutter-xs q-mt-sm">
              <q-input
                v-model.number="localMarginMm"
                dense
                outlined
                dark
                type="number"
                min="0"
                max="80"
                suffix="mm"
                style="width: 100px"
                @blur="applyMargin"
                @keyup.enter.prevent="applyMargin"
              />
              <q-btn
                flat
                dense
                color="primary"
                icon="check"
                label="Apply"
                @click="applyMargin"
                size="sm"
              />
            </div>

            <q-slider
              v-model="localMarginMm"
              :min="0"
              :max="60"
              :step="1"
              color="primary"
              dark
              class="q-mt-md q-px-xs"
              @change="applyMargin"
            />

            <div class="lpp-margin-presets">
              <div class="lpp-sidebar-label q-mb-xs">Quick presets</div>
              <div class="row q-gutter-xs">
                <q-btn
                  v-for="p in [0, 5, 10, 15, 20, 25, 30]"
                  :key="p"
                  :label="p + 'mm'"
                  dense
                  size="xs"
                  :flat="localMarginMm !== p"
                  :color="localMarginMm === p ? 'primary' : 'grey-7'"
                  :outline="localMarginMm !== p"
                  @click="localMarginMm = p; applyMargin()"
                />
              </div>
            </div>
          </div>

          <q-separator dark class="q-my-md" />

          <div class="lpp-sidebar-section">
            <div class="lpp-sidebar-label">Legend</div>
            <div class="lpp-legend-row">
              <div class="lpp-legend-dot" style="background: rgba(66,133,244,0.5)" />
              <span>Explicit page break</span>
            </div>
            <div class="lpp-legend-row">
              <div class="lpp-legend-dot" style="background: rgba(120,160,220,0.4)" />
              <span>Natural page boundary</span>
            </div>
            <div class="lpp-legend-row">
              <div class="lpp-legend-dot" style="background: white; border: 1px solid #666" />
              <span>Header (fixed on every page)</span>
            </div>
          </div>

          <q-separator dark class="q-my-md" />

          <div class="lpp-sidebar-section">
            <div class="lpp-sidebar-hint q-mb-sm">
              Scroll inside the preview to see subsequent pages. The header stays fixed at the top — exactly as it will print.
            </div>
            <q-btn
              flat
              dense
              color="blue-3"
              icon="open_in_new"
              label="Open in new tab"
              size="sm"
              @click="openInTab"
            />
          </div>
        </div>

        <!-- A4 canvas area -->
        <div class="lpp-canvas" ref="canvasRef">
          <!-- Loading overlay -->
          <transition name="fade">
            <div v-if="loading" class="lpp-loader">
              <q-spinner-dots size="48px" color="primary" />
              <div class="lpp-loader-text">Rendering preview…</div>
            </div>
          </transition>

          <!-- A4 page shadow wrapper -->
          <div class="lpp-page-shadow" :style="{ opacity: loading ? 0.3 : 1 }">
            <!-- The iframe = one A4 page visible, scrolls internally.
                 position:fixed header in the iframe stays at top = simulates header on every page. -->
            <iframe
              ref="iframeRef"
              class="lpp-iframe"
              :srcdoc="iframeHtml"
              scrolling="yes"
              @load="onIframeLoad"
            />
          </div>
        </div>

      </div><!-- /lpp-body -->
    </div>
  </q-dialog>
</template>

<script setup>
import { ref, nextTick } from 'vue'

const props = defineProps({
  generatePrintHtml: { type: Function, required: true },
  extraMarginMm:     { type: Number,   default: 0 }
})

const emit = defineEmits(['update:extraMarginMm', 'print'])

// ─── State ───────────────────────────────────────────────────────────
const open          = ref(false)
const loading       = ref(false)
const iframeHtml    = ref('')
const iframeRef     = ref(null)
const canvasRef     = ref(null)
const pageCount     = ref(0)
const localMarginMm = ref(props.extraMarginMm)

// A4 at 96 dpi
const A4_W_PX = 794
const A4_H_PX = 1123

// ─── Public API ──────────────────────────────────────────────────────
function show(currentExtraMarginMm) {
  localMarginMm.value = currentExtraMarginMm ?? props.extraMarginMm
  open.value = true
  nextTick(refresh)
}

defineExpose({ show })

// ─── Actions ─────────────────────────────────────────────────────────
function applyMargin() {
  emit('update:extraMarginMm', Number(localMarginMm.value) || 0)
  refresh()
}

function refresh() {
  loading.value = true
  pageCount.value = 0
  const base = props.generatePrintHtml()
  iframeHtml.value = buildPreviewHtml(base, Number(localMarginMm.value) || 0)
}

function onPrint() {
  emit('print')
}

function openInTab() {
  const w = window.open('', '_blank', 'width=850,height=1100,scrollbars=yes,resizable=yes')
  if (!w) return
  const base = props.generatePrintHtml()
  w.document.open()
  w.document.write(base)
  w.document.close()
  w.focus()
}

function onIframeLoad() {
  loading.value = false

  // Receive page count from the injected script
  const handler = (e) => {
    if (e.data && e.data.__lpp_pages !== undefined) {
      pageCount.value = e.data.__lpp_pages
      window.removeEventListener('message', handler)
    }
  }
  window.addEventListener('message', handler)
}

// ─── HTML builder ────────────────────────────────────────────────────
function buildPreviewHtml(base, extraMm) {
  // Screen-only CSS: page-break elements become visible dashed lines.
  // Body gets 12mm side padding (matching @page side margins) so layout = print layout.
  const screenCss = [
    '<style id="lpp-screen">',
    '@media screen {',
    '  html { background: #fff !important; }',
    '  body {',
    '    padding-left: 45px !important;',   // 12mm side margins
    '    padding-right: 45px !important;',
    '    padding-bottom: 45px !important;',
    '    box-sizing: border-box !important;',
    '  }',
    '  .print-header {',
    '    left: 45px !important;',           // simulate the @page margin constraints for fixed elements
    '    right: 45px !important;',
    '  }',
    // Explicit page-break divs → visible dashed blue line
    '  .page-break {',
    '    display: block !important;',
    '    height: 0 !important; margin: 0 !important;',
    '    position: relative !important; z-index: 6000 !important;',
    '    border-top: 2px dashed rgba(66,133,244,0.55) !important;',
    '  }',
    '  .page-break::after {',
    '    content: "— explicit page break —";',
    '    position: absolute; top: -9px; left: 50%;',
    '    transform: translateX(-50%);',
    '    background: rgba(66,133,244,0.08); color: rgba(66,133,244,0.75);',
    '    font: 500 10px/1 Arial, sans-serif;',
    '    padding: 2px 12px; border-radius: 20px; white-space: nowrap;',
    '  }',
    '}',
    '</style>'
  ].join('\n')

  // Preview script injected into the iframe:
  //  1. Draw page-boundary bars every A4_H_PX pixels
  //  2. Post page count back to parent via postMessage
  const previewScript = [
    '<script id="lpp-init">(function(){',
    '  var EXTRA_MM   = ' + extraMm + ';',
    '  var PAGE_H     = 1123;',
    '  var MARGIN_B   = Math.round(12 * 96 / 25.4);', // 12mm bottom margin in px
    '',
    '  window.__printReady = false;',
    '',
    '  function run(headerPx) {',
    '    var extraPx  = Math.ceil(EXTRA_MM * 96 / 25.4);',
    '    var topPx    = headerPx + extraPx;',
    '',
    '    // Sync header spacer for screen preview',
    '    var spacer = document.getElementById("headerSpacer");',
    '    if (spacer) spacer.style.height = topPx + "px";',
    '',
    '    var docH   = Math.max(document.body.scrollHeight, 2000);',
    '    var npages = Math.ceil(docH / PAGE_H) + 1;',
    '',
    '    for (var i = 1; i <= npages; i++) {',
    '      // Natural page boundary band',
    '      var band = document.createElement("div");',
    '      band.style.cssText =',
    '        "position:absolute;" +',
    '        "top:"  + (i * PAGE_H - MARGIN_B) + "px;" +',
    '        "left:0;right:0;" +',
    '        "height:" + (MARGIN_B + topPx) + "px;" +',
    '        "background:linear-gradient(to bottom," +',
    '          "rgba(200,216,240,0) 0%," +',
    '          "rgba(200,216,240,0.75) 35%," +',
    '          "rgba(200,216,240,0.75) 65%," +',
    '          "rgba(200,216,240,0) 100%);" +',
    '        "z-index:5000;pointer-events:none;";',
    '      document.body.appendChild(band);',
    '',
    '      // Page number label inside band',
    '      var lbl = document.createElement("div");',
    '      lbl.style.cssText =',
    '        "position:absolute;" +',
    '        "top:"  + (i * PAGE_H - Math.round(MARGIN_B / 2) - 8) + "px;" +',
    '        "right:8px;" +',
    '        "font:600 10px/1 Arial,sans-serif;" +',
    '        "color:rgba(60,110,190,0.65);" +',
    '        "letter-spacing:0.5px;" +',
    '        "z-index:5001;pointer-events:none;";',
    '      lbl.textContent = "Page " + (i + 1);',
    '      document.body.appendChild(lbl);',
    '    }',
    '',
    '    try { window.parent.postMessage({ __lpp_pages: npages - 1 }, "*"); } catch(e) {}',
    '    window.__printReady = true;',
    '  }',
    '',
    '  window.addEventListener("load", function() {',
    '    var h = document.getElementById("printHeaderRoot");',
    '    if (!h) { run(0); return; }',
    '    var imgs = document.querySelectorAll("img");',
    '    if (!imgs.length) { run(h.offsetHeight); return; }',
    '    var n = imgs.length, done = 0;',
    '    function tick() { if (++done >= n) run(h.offsetHeight); }',
    '    [].forEach.call(imgs, function(img) {',
    '      if (img.complete) tick(); else { img.onload = img.onerror = tick; }',
    '    });',
    '  });',
    '})();<' + '/script>'
  ].join('\n')

  return base
    .replace('</head>', screenCss + '\n</head>')
    .replace(/<body([^>]*)>/i, (_m, attrs) => '<body' + attrs + '>\n' + previewScript + '\n')
}
</script>

<style scoped>
/* ─── Shell ─────────────────────────────────────────────────────── */
.lpp-root {
  display: flex;
  flex-direction: column;
  height: 100vh;
  background: #1c2030;
  overflow: hidden;
}

/* ─── Top bar ───────────────────────────────────────────────────── */
.lpp-topbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 8px 16px;
  background: #131720;
  border-bottom: 1px solid rgba(255,255,255,0.07);
  flex-shrink: 0;
  gap: 12px;
}
.lpp-topbar-left  { display: flex; align-items: center; gap: 6px; color: #c5d0e8; }
.lpp-topbar-right { display: flex; align-items: center; }
.lpp-title {
  font-size: 14px;
  font-weight: 600;
  color: #d8e2f4;
  letter-spacing: 0.2px;
}

/* ─── Body ──────────────────────────────────────────────────────── */
.lpp-body {
  display: flex;
  flex: 1;
  overflow: hidden;
}

/* ─── Sidebar ───────────────────────────────────────────────────── */
.lpp-sidebar {
  width: 230px;
  flex-shrink: 0;
  background: #151923;
  border-right: 1px solid rgba(255,255,255,0.06);
  padding: 16px 14px;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
}
.lpp-sidebar-section { }
.lpp-sidebar-label {
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.7px;
  color: #7a93b8;
  margin-bottom: 4px;
  display: flex;
  align-items: center;
}
.lpp-sidebar-hint {
  font-size: 11px;
  color: #4e6080;
  line-height: 1.4;
}
.lpp-margin-presets { margin-top: 14px; }
.lpp-legend-row {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 11px;
  color: #6a7f9a;
  margin-top: 6px;
}
.lpp-legend-dot {
  width: 12px;
  height: 12px;
  border-radius: 2px;
  flex-shrink: 0;
}

/* ─── Canvas ────────────────────────────────────────────────────── */
.lpp-canvas {
  flex: 1;
  overflow: auto;
  display: flex;
  align-items: flex-start;
  justify-content: center;
  padding: 28px 20px 40px;
  background: #272d3c;
  position: relative;
}

/* Loading overlay */
.lpp-loader {
  position: absolute;
  inset: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  z-index: 10;
  background: rgba(27,33,48,0.6);
  backdrop-filter: blur(4px);
}
.lpp-loader-text {
  color: #8aaccc;
  font-size: 13px;
  margin-top: 14px;
  letter-spacing: 0.3px;
}

/* Fade transition for loader */
.fade-enter-active, .fade-leave-active { transition: opacity 0.25s; }
.fade-enter-from, .fade-leave-to       { opacity: 0; }

/* A4 page box shadow */
.lpp-page-shadow {
  transition: opacity 0.3s;
  border-radius: 2px;
  box-shadow:
    0 0 0 1px rgba(255,255,255,0.06),
    0 8px 40px rgba(0,0,0,0.55),
    0 2px 8px  rgba(0,0,0,0.4);
}

/* The iframe: exactly one A4 page tall, scrolls internally.
   The position:fixed header inside = fixed to iframe viewport
   = stays at top as user scrolls = simulates header on every page. */
.lpp-iframe {
  display: block;
  width: 794px;   /* A4 = 210mm at 96dpi */
  height: 1123px; /* A4 = 297mm at 96dpi — exactly one page, internal scroll shows next pages */
  border: none;
  background: #fff;
  overflow: auto;
}
</style>
