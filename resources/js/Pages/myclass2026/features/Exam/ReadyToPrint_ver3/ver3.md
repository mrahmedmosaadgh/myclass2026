# ReadyToPrint v3 — Final Architecture Specification

This document is the canonical, implementation-grade architecture for the ReadyToPrint v3 exam rendering system. It defines contracts, not features. Every contract here is treated as enforceable.

---

## 1. Overview

ReadyToPrint v3 is a deterministic exam rendering engine, not a print preview feature. It separates authoring, validation, and final output into three roles:

- **Vue** — authoring and preview (debug tool)
- **New tab route** — browser validation layer
- **Puppeteer + Blade** — final output and source of truth (PDF)

Design principles:

- structured data, not HTML, is the source of truth
- pagination is deterministic and computed server-side
- layout is shared as a contract (structure, classes, units), not as shared HTML
- versioning, metrics calibration, and caching are first-class concerns

---

## 2. Operating Model

| Layer | Role | Authority |
| --- | --- | --- |
| Vue `ExamPrintLayout.vue` | author preview, in-app rendering | none over final output |
| New tab `/print/exam?id=...` | browser-level validation, optional `window.print()` | validation only |
| Blade `exam/print.blade.php` | reference HTML for PDF | structural authority |
| Puppeteer service | renders Blade HTML to PDF | final output authority |

A pure Vue + new-tab solution is insufficient: browser differences, OS print-dialog margins, and font/loading variability cause layout drift. Puppeteer is the only deterministic output stage.

---

## 3. Data Contracts

### 3.1 Canonical Exam Schema

The single source of truth is a normalized exam document, not HTML.

```js
{
  meta: {
    title: "",
    duration: "",
    subject: ""
  },
  sections: [
    {
      title: "",
      questions: [
        {
          type: "mcq",
          content: "...",
          choices: [...]
        }
      ]
    }
  ]
}
```

This schema feeds every renderer (Vue, Blade, Puppeteer) identically.

### 3.2 Content Normalization Layer

Raw builder input must be normalized into the strict schema before any rendering or pagination.

```js
normalizeExam(rawInput) -> normalizedExam
```

Responsibilities:

- sanitize allowed HTML
- standardize question shape across legacy and new inputs
- normalize option arrays and correct-answer fields
- flatten edge cases before pagination

This prevents renderer-specific bug handling and keeps Vue, Blade, and Puppeteer fed with the same clean document model.

### 3.3 Layout Versioning

The shared layout contract is versioned explicitly.

```js
{ layoutVersion: "v1.0" }
```

Rules:

- Vue renderer and Blade renderer must implement the same `layoutVersion`
- breaking structural, class, or layout changes require a version bump
- paginated payloads must carry the version they were built against

### 3.4 Renderer Compliance Check

Version alignment is enforced at runtime, not assumed.

```js
assertLayoutVersionMatch(vueVersion, bladeVersion)
```

DOM-level visibility:

```html
<html data-layout-version="v1.0">
```

This prevents silent drift when one renderer evolves without the other.

### 3.5 Unit System Contract

All layout, metrics, pagination, and page-budget values use a formally defined unit system.

```js
units: {
  base: "mm",
  precision: 0.1
}
```

Rules:

- all layout metrics are stored in `mm`
- all height calculations are rounded to the same precision
- PHP, JavaScript, and Chromium-facing values share the same rounding rule

```js
round(value) => Math.round(value * 10) / 10
```

This eliminates float-handling drift across PHP, JS, and Chromium.

### 3.6 Layout Metrics Contract

Deterministic pagination requires an explicit metrics layer; DOM measurement is not a system dependency.

```js
{
  questionTypes: {
    mcq:   { baseHeight: 12, choiceHeight: 6, spacing: 4 },
    essay: { baseHeight: 10, lineHeight: 6, minLines: 5 }
  }
}
```

This contract is the backbone of pagination feasibility, predictable page fill, and reproducibility.

### 3.7 Metrics Calibration

Metrics are empirical, not theoretical. Raw values must be calibrated against the actual rendering environment (font, line-height, CSS spacing, Chromium).

Process:

- render canonical sample blocks in Puppeteer
- measure real heights
- persist calibrated values back into the metrics dataset

```js
measure("mcq_4_choices") -> 38.6

mcq: { baseHeight: 14.2, choiceHeight: 5.8 }
```

Calibration is repeatable and is rerun whenever fonts or layout CSS change.

### 3.8 Metrics Version Binding

Metrics are bound to a specific layout version.

```js
layoutMetricsBinding: {
  "v1.0": "metrics-v1.0"
}
```

Rules:

- a layout version must only use its bound metrics
- no cross-version reuse
- CSS, font, or layout changes require recalibration and a binding update

### 3.9 Metrics Integrity Hash

Metrics artifacts carry an integrity hash to detect tampering or drift.

```json
{
  "metricsVersion": "v1.0",
  "layoutVersion": "v1.0",
  "hash": "sha256-abc123..."
}
```

Rules:

- the hash is computed over: metrics values + font name + line-height
- the hash is verified at runtime before pagination runs
- a hash mismatch aborts pagination and flags the artifact as invalid

This prevents silent edits to `metrics.json` and mismatched deployments across environments.

### 3.10 Environment Fingerprint

Calibration output records the exact rendering environment it was produced in.

```json
{
  "environment": {
    "chromiumVersion": "120.x",
    "dpi": 96,
    "os": "linux"
  }
}
```

Rules:

- the PDF service must match this environment, or be within an allowed tolerance
- environment mismatch flags metrics as suspect and triggers recalibration

This catches subtle drift between macOS/Linux, different Chromium builds, or DPI differences.

---

## 4. Page and Pagination Contracts

### 4.1 Page Object Model

Pages are first-class document units, not arrays of questions.

```js
pages: [
  {
    number: 1,
    items: [
      { type: "header" },
      { type: "section", title: "..." },
      { type: "question", data: {...} }
    ]
  }
]
```

Reserved zones are formalized:

```js
page: {
  height: 297,
  marginTop: 12,
  marginBottom: 12,
  headerHeight: 20,
  footerHeight: 15,
  usableHeight: 238
}
```

Pagination always allocates against `usableHeight`, never the full page height.

### 4.2 Pagination Engine — Rules

Pagination is rule-driven.

Hard rules:

- no question splits across pages
- a section title cannot be the last item on a page
- the answer key starts on a new page
- the page footer area is reserved before content allocation

Soft rules:

- avoid a single orphan choice at page bottom
- avoid one-line overflow when a move-to-next-page produces cleaner output
- keep a question stem with at least part of its answer area when applicable

### 4.3 Lightweight Constraint Solver

Rules carry priorities and are resolved deterministically when they conflict.

```js
rules: [
  { id: "no-split-question", priority: 100 },
  { id: "section-not-last",  priority: 90  },
  { id: "avoid-orphan",      priority: 50  }
]
```

Higher-priority rules are enforced first. Lower-priority soft rules may be violated only when necessary, and any violation is recorded in the debug report.

### 4.4 Height Estimation Contract

Pagination uses `estimateHeight()` driven by the metrics contract, not live browser measurements.

```php
$currentPageHeight = 0;
$pageLimit = $page['usableHeight'];

foreach ($questions as $q) {
    $height = estimateHeight($q);

    if ($currentPageHeight + $height > $pageLimit) {
        newPage();
        $currentPageHeight = 0;
    }

    addToPage($q);
    $currentPageHeight += $height;
}
```

### 4.5 Overflow Policy

Overflow behavior is explicit, never ad hoc.

```js
overflowPolicy: {
  allowOverflow: false,
  fallback: "next-page",
  tolerance: 1
}
```

Default behavior:

- if an item does not fit on the current page, move it to the next page
- if an item exceeds `usableHeight` on an empty page, allow overflow for that single item
- every forced overflow is recorded in the pagination debug report

### 4.6 Deterministic Seed

Pagination carries a deterministic seed so identical input always produces identical output.

```js
paginationSeed: "examId-v1"
```

The seed governs any tie-breaking or fallback decisions to keep them reproducible.

### 4.7 Max Page Safety Limit

Pagination is bounded to protect against pathological inputs and rule bugs.

```js
limits: { maxPages: 200 }
```

Behavior on breach:

- abort pagination, or
- fall back to `flex` mode, or
- flag the exam as invalid

This prevents infinite loops from buggy rules and memory blowups in Puppeteer.

### 4.8 No Browser-Driven Pagination

Browser-driven pagination is rejected as final authority — it produces drift, inconsistent breaks, and unstable custom page numbers.

---

## 5. Rendering Contracts

### 5.1 Shared DOM Contract

Vue and Blade share structure, class names, and layout rules — never raw HTML.

```html
<div class="page">
  <div class="exam-header">...</div>

  <div class="section">
    <div class="question">...</div>
  </div>

  <div class="footer">Page X / Y</div>
</div>
```

### 5.2 Vue Renderer — `ExamPrintLayout.vue`

Presentational only. No measurement, no pagination, no sizing decisions.

```html
<div class="print-root">
  <div v-for="(page, i) in pages" class="page">
    <ExamHeader :meta="meta" />

    <QuestionBlock
      v-for="item in page.items"
      :key="item.id"
      :item="item"
    />

    <Footer :page="i + 1" :total="pages.length" />
  </div>
</div>
```

### 5.3 Blade Renderer — `exam/print.blade.php`

Same DOM contract and class system as Vue. Used by Puppeteer.

```php
$html = view('exam.print', $data)->render();
```

### 5.4 Font and Typography Lock

Font drift is the most likely real-world cause of pagination mismatch.

Required:

- one canonical font family for print layout
- identical font source for Vue, Blade, and Puppeteer
- fonts embedded or hosted consistently
- Puppeteer waits for font readiness before PDF generation

Locked typography rules:

```css
body {
  line-height: 1.4;
  letter-spacing: 0;
  word-break: normal;
}
```

Disallowed in final print layout:

- lazy-loaded images
- late-loading fonts
- dynamic resizing after layout-ready

### 5.5 Image and Media Contract

Media has explicit, deterministic constraints.

```js
media: {
  maxWidth: "100%",
  maxHeight: 120,
  behavior: "scale-down"
}
```

Estimation:

```js
if (q.containsImage) {
  height += imageEstimatedHeight;
}
```

Rules:

- media behavior is always deterministic
- oversized media is handled by the contract, not by browser default
- image-containing questions are classified at least as `medium` complexity

### 5.6 Content Complexity Classification

Questions carry an explicit complexity classification.

```js
question: { type: "mcq", complexity: "low" | "medium" | "high" }
```

Estimation may switch strategy based on complexity:

```js
estimateHeight(q) {
  if (q.complexity === "high") return fallbackToSafeBlockHeight();
}
```

This protects pagination from catastrophic underestimation for long LaTeX, images/diagrams, tables, and mixed RTL/LTR content.

### 5.7 Strict vs Flexible Rendering Modes

The engine supports two operating modes.

```js
mode: "strict" | "flex"
```

- **Strict** — final exams; enforces all hard rules; conservative spacing and breaks
- **Flex** — worksheets, drafts, previews; allows tighter packing and more soft-rule violations

---

## 6. Output Pipeline

### 6.1 Puppeteer PDF — Final Authority

Locked configuration:

```js
await page.evaluateHandle('document.fonts.ready');

await page.pdf({
  format: 'A4',
  printBackground: true,
  preferCSSPageSize: true,
  scale: 1,
});
```

Constraints:

- disable layout-shifting dependencies
- no asynchronous rendering after page-ready
- page size and margins fixed across environments

Timeout and failure policy:

```js
pdfGeneration: {
  timeoutMs: 30000,
  onFail: "retry"
}
```

Rules:

- PDF generation has a hard timeout
- failure behavior is explicit (`retry` or `fail-fast`), never a silent stall
- every failure is logged with environment fingerprint and exam id

### 6.2 New Tab Validation Route

```js
window.open('/print/exam?id=123', '_blank')
```

The route loads normalized + paginated data, renders the shared layout, and may optionally trigger `window.print()`. It is a validation surface, not a final output.

### 6.3 Page Numbers

CSS counters are not used for final page numbering. Page numbers are explicit in the DOM:

```html
<div class="footer">Page {{ current }} / {{ total }}</div>
```

This is required for stable, custom page numbering.

---

## 7. Operations

### 7.1 Caching

The pipeline is expensive; caching is deliberate.

```js
cacheKey = hash(normalizedExam + layoutVersion + metricsVersion)
```

Cache targets:

- normalized exam payload
- paginated output
- final PDF output

Without caching, Puppeteer becomes the scaling bottleneck.

### 7.2 Validation and Debug Mode

Visual debug overlay:

```css
.page     { outline: 1px dashed red; }
.question { outline: 1px solid  blue; }
```

Optional debug signals:

- computed estimated block heights
- current page fill percentage
- reserved header/footer zones
- page break reasons

### 7.3 Post-Deployment Drift Detector

A continuous safeguard that runs after deployment to catch silent rendering drift.

Process:

1. periodically render canonical test documents
2. compare expected page count (from pagination) against actual PDF page count
3. on mismatch: flag drift, invalidate metrics, require recalibration

This catches unnoticed font updates, OS or container changes, and Chromium upgrades that the unit/integrity contracts alone cannot detect.

### 7.4 Pagination Debug Report

Structured, machine-readable output for replay and tooling:

```js
{
  page: 2,
  usedHeight: 268,
  remaining: 5,
  decisions: [
    { type: "overflow", itemId: "q5",        action: "moved",                   fromPage: 1, toPage: 2 },
    { type: "grouping", itemId: "section-2", action: "kept-with-next-question", fromPage: 2, toPage: 2 }
  ]
}
```

This makes pagination issues explainable instead of guesswork.

---

## 8. Architecture Health

| Layer | Status |
| --- | --- |
| Schema | designed |
| Normalization | designed |
| Layout contract | designed |
| Versioning + compliance | designed |
| Unit system | designed |
| Metrics contract | designed; awaiting calibration artifact |
| Pagination engine | designed; awaiting implementation |
| Rendering constraints | designed |
| PDF pipeline | designed |
| Caching | designed |
| Failure handling | designed (timeout, retry, drift detector) |
| Production safety | designed (integrity hash, env fingerprint, max-pages) |

Architecture is closed. Implementation is unblocked.

---

## 9. Implementation Roadmap

This is the only authoritative ordering. Do not reorder.

### Phase 1 — Calibration (start here)

1. **Metrics Calibration Tool**
   - canonical Puppeteer measurement scripts for MCQ (2–6 choices), essay, long text, RTL/Arabic, image-containing blocks
   - viewport locked to A4 at 96dpi (`794 × 1123`)
   - `await page.evaluateHandle('document.fonts.ready')`
   - aggregate multiple samples; round per the unit contract
   - output: `metrics.v1.json` with `metricsVersion`, font, line-height, calibrated values

2. **Metrics Validation + Freeze**
   - render a representative test exam end-to-end
   - compare estimated vs actual Puppeteer page count
   - acceptable drift: ≤ 1mm per page; otherwise recalibrate
   - on success, freeze metrics as an immutable artifact bound to `layoutVersion`

### Phase 2 — Reference Output

3. **Blade Renderer** — implements the shared DOM contract; reference HTML for PDF
4. **Puppeteer Service** — locked config; consumes Blade output
5. **Pagination Engine** — rule-driven, priority-based; uses calibrated metrics; emits structured debug report

### Phase 3 — Vue and Operations

6. **Vue Preview (`ExamPrintLayout.vue`)** — mirrors the Blade contract; presentational only
7. **New Tab Validation Route** — browser-level validation surface
8. **Validation/Debug Layer** — overlays + structured debug report consumer
9. **Caching Layer** — normalization, pagination, and PDF outputs
10. **Editor Enhancements** — only after the rendering pipeline is stable

### Forbidden During Implementation

- tweaking print CSS without recalibrating metrics
- changing fonts without a metrics version bump
- per-renderer “quick fix” spacing that breaks the shared contract
- using DOM measurement for pagination decisions

---

## 10. Calibration Tool Spec (Phase 1 Deliverable)

Output artifact:

```json
{
  "metricsVersion": "v1.0",
  "layoutVersion": "v1.0",
  "units": "mm",
  "font": "ExamFont-Regular",
  "lineHeight": 1.4,
  "values": {
    "mcq":   { "baseHeight": 14.2, "choiceHeight": 5.8, "spacing": 3.6 },
    "essay": { "baseHeight": 10.5, "lineHeight": 6.0, "minLines": 5 }
  }
}
```

System components:

- **Calibration templates (HTML)** — controlled cases per question type and edge case
- **Measurement script (Puppeteer)** — `getBoundingClientRect().height`, converted via `pxToMm = px * 25.4 / 96`
- **Measurement protocol** — fixed viewport, fonts ready, no scaling, deterministic environment
- **Aggregation** — multi-sample mean, rounded per unit contract
- **CLI** — `node calibrate.js --layout=v1` → `metrics.v1.json` + debug report

Critical rules:

- font must be locked before calibration
- one metrics artifact per layout version (no cross-version reuse)
- never mix calibrated and estimated values in the same artifact

---

## 11. Summary Position

- Vue preview = debug and authoring tool
- New tab = browser validation
- Puppeteer PDF = final authority
- Pagination = deterministic, server-side, rule-driven, calibrated
- Output = reproducible across environments

This architecture turns ReadyToPrint v3 into a document rendering engine, not a print feature. Implementation begins with the Metrics Calibration Tool.
