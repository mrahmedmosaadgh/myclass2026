# ReadyToPrint v3 — Task Status Check

Status file for `ver3.md` roadmap items (checked against current implementation work).

## Roadmap Status (from `ver3.md` Section 9)

1. **Metrics Calibration Tool** — ✅ **Done (artifact exists)**
   - Calibration CLI exists: `tools/print-calibration/calibrate.js`.
   - Artifact exists: `tools/print-calibration/metrics.v1.json`.

2. **Metrics Validation + Freeze** — ✅ **Done (current implementation scope)**
   - Validation CLI exists: `tools/print-calibration/validate.js` (1.0mm drift rule present).
   - Baseline validation was executed successfully (`node validate.js --layout=v1 --case=baseline`).
   - Freeze/binding artifact exists: `tools/print-calibration/layout-metrics-binding.json` with locked hash.

3. **Blade Renderer (reference output contract)** — 🟡 **Partial**
   - Server print HTML endpoint exists and is used in ver3/ver4 flows.
   - Still not fully aligned with the full strict architecture contract in `ver3.md`.

4. **Puppeteer Service (locked config)** — 🟡 **Partial (advanced)**
   - PDF generation pipeline exists with Puppeteer and robust fallback handling.
   - Runtime contract checks now enforce binding/frozen artifact, layout+metrics version alignment, and internal metrics hash validation before environment checks.
   - Remaining: broader operational hardening and full strict-mode rollout coverage.

5. **Pagination Engine (rule-driven, calibrated metrics)** — 🟡 **Partial (advanced)**
   - Deterministic seed, rule-priority reporting, and explicit page budget (margins/header/footer) are now implemented in `ExamPaginationService`.
   - Overflow policy/tolerance and richer page object reporting (`items`, `remainingHeightMm`, rule-tagged events) are now present.
   - Remaining: full strict rule coverage and end-to-end contract parity with final architecture spec.

6. **Vue Preview (`ExamPrintLayout.vue`) presentational-only mirror** — 🟡 **Partial**
   - Vue preview/print UI exists and was enhanced.
   - Not fully finalized as strict mirror of the architecture contract.

7. **New Tab Validation Route** — ✅ **Done (functional)**
   - New tab print HTML opening is implemented and used (popup-safe logic).

8. **Validation/Debug Layer** — 🟡 **Partial**
   - Debug print mode/reporting exists in current work.
   - Full architecture-level debug/validation tooling still incomplete.

9. **Caching Layer (normalization/pagination/PDF outputs)** — 🟡 **Partial (implemented core path)**
   - Print HTML + pagination report are now cached by exam-content fingerprint in `ExamFileController`.
   - Successful Puppeteer PDF output is now cached by the same render fingerprint and reused on subsequent requests.
   - Remaining: broader cache invalidation strategy and optional cross-layer cache observability.

10. **Editor Enhancements (after pipeline stability)** — 🟡 **Partial / Ongoing**
   - UI enhancements implemented (including footer/page-number controls and print mode UX).
   - Final phase should continue only after core pipeline completion.

---

## Recently Completed Work (Implemented)

- ✅ ver3 supports **old + new print** paths with fallback behavior.
- ✅ ver3 supports **new PDF download** flow with popup-safe handling and fallback behavior.
- ✅ ver4 switched to **new server print/PDF** pipeline with debug/UX improvements.
- ✅ Backend PDF generation hardened to avoid 500-only failure path and return fallback HTML where applicable.
- ✅ ver3 footer/page numbering enhanced with:
  - `Start numbering from question #`
  - `Start page number value`
  - runtime page counter reset marker during print HTML generation.
- ✅ ver4 question display fixed in live preview by loading KaTeX stylesheet in iframe HTML.
- ✅ Pagination service upgraded with deterministic seed + rule priorities + reserved-zone page budget + richer debug/event report.
- ✅ Pagination metrics loader now enforces runtime contracts: frozen artifact check, layout/metrics version alignment, and internal metrics hash validation.
- ✅ Puppeteer PDF service now enforces runtime metrics contracts via binding + frozen artifact + version alignment + internal hash validation.
- ✅ Core caching path added: print HTML/pagination payload cache + successful Puppeteer PDF cache keyed by render fingerprint.
- ✅ Cache observability added: response headers expose cache hit/miss, bypass flag, cache key, and render fingerprint.
- ✅ Cache bypass control added via `cache_bust=1` / `refresh=1` for print HTML and PDF endpoints.
- ✅ Pagination rule coverage improved: section `pageBreakBefore` is now enforced in deterministic engine with explicit rule event logging.
- ✅ Oversized question handling improved: explicit single-item forced overflow path is now tracked and exempted from false overflow-violation noise.
- ✅ Strict/Flex pagination mode contract is now wired end-to-end (settings → `ExamPaginationService`) with mode reported in pagination debug output.
- ✅ Ver3 settings UI now includes a visible `Pagination mode` selector (`Strict` / `Flex`) and persists it in `pageOptions` defaults/reset/save flows.
- ✅ Pagination mode observability completed: `print-html` debug payload includes active mode and response headers now expose `X-Pagination-Mode` for print/PDF/fallback responses.

---

## Overall

`ver3.md` roadmap is **not fully complete** yet.
Current state is **advanced but architecture-incomplete**.
Major remaining work is primarily deterministic pagination completion, stronger contract enforcement across runtime, and caching/ops finalization.
