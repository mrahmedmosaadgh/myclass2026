Let me look at the existing `PrintPreview.vue` (already in components) and the exact structure first.

Viewed PrintPreview.vue:1-347
Viewed PrintActions.vue:1-301

Now I have the full picture. I'll create a new `LivePrintPreview.vue` component — a dark, polished dialog that shows the A4 page at exact size with the header fixed inside it (exactly like Chrome's print preview), page break bars, and a live margin adjustment slider on the side.

Created LivePrintPreview.vue

Now add the **"Live Preview"** button to `PrintActions.vue` and wire it all up:

Edited PrintActions.vue

Now wire `extraMarginMm` in the parent `Builder_tetst.vue` so the preview and settings stay in sync:

Viewed Builder_tetst.vue:34-38
Edited Builder_tetst.vue

Done. Here's the complete picture of what was built:

---

## ✅ New: Live Print Preview

### How it works

Click the **"Live Preview"** button (teal) next to Print. A full-screen dark dialog opens showing:

```
┌─────────────────────────────────────────────────────────────────┐
│  📄 Live Print Preview   [3 pages] [A4·210×297mm]   [🖨 Print] [✕]│
├──────────────┬──────────────────────────────────────────────────┤
│ Sidebar      │                                                  │
│              │   ┌────────────────────────────────────────┐    │
│ Page top     │   │ HEADER IMAGE (fixed, always visible)   │    │
│ margin: 10mm │   ├────────────────────────────────────────┤    │
│ ─────────────│   │ Q1. ...                                │    │
│ Slider 0-60mm│   │ Q2. ...             ↕ scroll inside    │    │
│              │   │ Q3. ...                                │    │
│ Presets:     │   │ ░░░░░░░ page 2 boundary ░░░░░░░░░░░   │    │
│ 0 5 10 15 20 │   │ Q4. ...                                │    │
│              │   └────────────────────────────────────────┘    │
│ Legend       │        794px × 1123px (one A4 page, scrolls)    │
└──────────────┴──────────────────────────────────────────────────┘
```

### Key behaviours

| Feature | How it's achieved |
|---|---|
| **Header fixed on every page** | `position: fixed` inside a 1123px-tall iframe = stays at top as user scrolls inside = perfectly simulates print |
| **Explicit page breaks** | `.page-break` elements shown as dashed blue lines with label |
| **Natural page boundary bars** | Injected every 1123px (A4 height) with page number labels |
| **Adjust margin & see result** | Slider + number input → `Apply` → refreshes iframe immediately |
| **Changes sync to Settings** | `@update:extraMarginMm` propagates to `pageOptions.printHeader.pageMarginTopMm` and saves |
| **Print from preview** | "Print" button in the topbar triggers the actual print |