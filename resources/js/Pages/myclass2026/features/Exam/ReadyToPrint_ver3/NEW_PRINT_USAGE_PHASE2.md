# ReadyToPrint v3 — New Print Usage + Phase 2

This document explains how to run the current deterministic print foundation (Phase 1 completed) and how to start Phase 2 safely.

## 1) How to use the new print foundation

Phase 1 calibration artifacts live in:

- `tools/print-calibration/metrics.v1.json`
- `tools/print-calibration/layout-metrics-binding.json`

### Run calibration

```bash
node tools/print-calibration/calibrate.js --layout=v1
```

Expected result:

- updates `metrics.v1.json`
- includes environment fingerprint (`puppeteerVersion`, `chromiumVersion`, `dpi`, `os`)
- includes artifact hash

### Run baseline validation

```bash
node tools/print-calibration/validate.js --layout=v1
```

Expected result:

- all baseline template checks pass
- drift per case `<= 1mm`

### Run mixed real-world validation

```bash
node tools/print-calibration/validate.js --layout=v1 --case=mixed
```

Expected result:

- mixed case passes against calibrated `mixed_case` value
- drift `<= 1mm`

### Contract rules (must stay strict)

- Canonical artifact naming: `metrics.<major>.json` (example: `metrics.v1.json`)
- Canonical metrics identifier: `metricsVersion = metrics-v1`
- Binding must match exactly: `layoutVersion v1.0 -> metrics-v1`
- No duplicate/legacy artifacts in the same directory
- If print CSS/font changes, recalibrate and bump metrics version

## 2) Phase 2 scope (authoritative order)

Per `ver3.md`, Phase 2 is:

1. Blade Renderer
2. Puppeteer Service
3. Pagination Engine

Do not reorder.

## 3) Phase 2 implementation checklist

### 3.1 Blade Renderer (reference HTML authority)

Goal: implement the shared DOM contract in Blade so server rendering is the source for PDF output.

Deliverables:

- `resources/views/exam/print.blade.php`
- deterministic print CSS classes matching contract blocks
- stable data attributes/IDs for debug tracing

Acceptance:

- same normalized exam input always produces same HTML structure
- layout class names and block hierarchy are contract-locked

### 3.2 Puppeteer Service (locked PDF pipeline)

Goal: render Blade output to PDF with fixed engine settings.

Locked config:

- `format: 'A4'`
- `printBackground: true`
- `preferCSSPageSize: true`
- `scale: 1`

Deliverables:

- service endpoint/job that receives normalized exam + versions
- environment + timeout + max-page safeguards
- failure policy with clear error surface

Acceptance:

- same input + same versions => reproducible PDF page count
- metrics/environment mismatch fails fast (no silent fallback)

### 3.3 Pagination Engine (deterministic and rule-driven)

Goal: paginate server-side from metrics, without DOM measurement.

Inputs:

- normalized exam schema
- `layoutVersion`
- `metricsVersion` (resolved through binding)
- calibrated metric values (`metrics.v1.json`)

Outputs:

- paginated page objects
- structured debug report (`why block moved/split/new page`)

Acceptance:

- deterministic output across runs
- overflow and reserved header/footer constraints enforced
- drift checks remain within tolerance under validator scenarios

## 4) Phase 2 done-definition

Phase 2 is complete only when all are true:

- Blade renderer implemented and contract-consistent
- Puppeteer service renders with locked config and guardrails
- Pagination engine uses calibrated metrics only
- baseline + mixed validation pass
- binding and version checks enforced end-to-end

## 5) Current progress

- ✅ Phase 2 Step 1 (Blade Renderer) started and wired into `ExamFileController::generatePrintHtml()`
- ✅ New Blade view created: `resources/views/exam/print.blade.php`
- ✅ Phase 2 Step 2 (Puppeteer Service) integrated as primary PDF engine in `generatePdf()`
- ✅ Node renderer added: `tools/print-calibration/render-pdf.js` with locked Puppeteer PDF config
- ✅ Laravel service added: `app/Services/PuppeteerPdfService.php` with timeout, max-page guard, and environment fingerprint checks
- 🚧 Phase 2 Step 3 (Pagination Engine) started with deterministic server-side estimator
- ✅ Pagination service added: `app/Services/ExamPaginationService.php`
- ✅ `ExamFileController::generatePrintHtml()` now applies pagination-generated page breaks and logs debug report
- ✅ Page object model now emitted in pagination report (`pages[]` with per-page blocks + used height)
- ✅ Debug payload mode available via `GET /api/exam/ready-to-print/print-html/{examId}?debug=1`
- ✅ Pagination policy rules added: section `keep-with-next`, per-question `splitPolicy`, and oversize-block overflow events

## 6) Immediate next command sequence

```bash
node tools/print-calibration/calibrate.js --layout=v1
node tools/print-calibration/validate.js --layout=v1
node tools/print-calibration/validate.js --layout=v1 --case=mixed
```

If all pass, start implementation in this order:

1. Blade renderer
2. Puppeteer service
3. Pagination engine
