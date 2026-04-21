# Plan — Exam Builder Page (Ready To Print)

## 0) Goal
Build a single page/workflow that lets a teacher create and manage an exam (sections + questions + layout rules) and produce a deterministic, validated, print-ready render.

The page is not just a "document editor"; it is a lifecycle-governed rendering pipeline:

Draft → Validated → Approved → Rendered (Snapshot) → Printed → Archived

## 1) Scope & Guarantees

### 1.1 In scope
- Exam metadata management (title/subject/term/time/grade/etc.)
- Sections (ordering, titles, instructions, marks aggregation)
- Questions (typed, extensible, reusable patterns)
- Layout settings (page size/margins/header/footer)
- Validation (schema + print/layout constraints)
- Pagination planning (deterministic)
- Print preview (WYSIWYG relative to exported output)
- Render snapshot creation (immutable artifact)

### 1.2 Out of scope (initial)
- Full collaborative real-time editing (can be added later)
- Full LMS export formats (future)
- Advanced auto-grading analytics (future)

### 1.3 Non-negotiable guarantees
- Rendering uses only **validated** data.
- Printing/export uses only an **immutable rendered snapshot**, never live draft.
- The engine must detect and report unrenderable content before attempting final render/export.

## 2) Exam Lifecycle Model (State Machine)

### 2.1 States
- **Draft**
  - Allows incomplete data.
  - Validation panel can show warnings/errors but does not block saving.

- **Validated**
  - Requires passing schema validation and layout preflight.
  - Any edit returns the exam to Draft or marks as "dirty".

- **Approved (Locked)**
  - Structural changes blocked (sections/questions/layout locked).
  - Only limited metadata edits allowed if you want (optional policy).

- **Rendered (Snapshot)**
  - Rendered output artifact created (HTML snapshot / render-tree JSON).
  - Immutable.

- **Printed / Archived**
  - Version stored with audit metadata.

### 2.2 Transitions and rules
- Draft → Validated: run validations; block transition on errors.
- Validated → Approved: require explicit user confirmation.
- Approved → Rendered: generate snapshot; block on unexpected render errors.
- Rendered → Printed/Archived: store version metadata and lock output.

## 3) Canonical Data Model (What the page edits)

### 3.1 Exam document
- `examMeta`
  - title, grade, subject, term, duration, date, etc.
- `headerConfig`
  - mode: `none | first_page_only | all_pages | custom_per_page`
  - dynamic fields support (school name, barcode, student id placeholder)
- `footerConfig`
  - page numbering format
  - continuation message rules
  - end message rules
- `pageSetup`
  - paper: A4/Letter
  - margins (mm)
  - header/footer reserved height (mm)
- `sections[]`
  - id, title, instructions, rules
  - questions[] (embedded or referenced)
- `layoutDefaults`
  - overflow strategy defaults
  - optional hybrid pagination controls

### 3.2 Question model (separation of concerns)
Each question is defined as:
- **Content**: what’s shown (prompt, options, media)
- **Response model**: how student answers (selected option id, text area, grid)
- **Evaluation**: how it’s graded (manual/auto later)

### 3.3 Block model (for layout)
Block categories:
- Structural: page/section/columns
- Content: text/instructions/media
- Question blocks: MCQ/essay/true-false/etc.
- Utility: spacer/divider/pagebreak

## 4) Validation Layer (must happen before rendering)

### 4.1 Schema validation
- required fields
- registered question types
- marks consistency
- allowed layout values
- rich content constraints (allowed HTML subset, media rules)

### 4.2 Layout preflight validation (print safety)
- page height budget in mm (fixed canvas)
- estimate block heights and enforce:
  - no overflow
  - avoid orphan section headers
  - images fit within width/height constraints (auto-scale or error)
  - detect questions that can’t fit on a fresh page (hard error)

### 4.3 Output
- structured validation report:
  - severity: error/warn/info
  - scope: exam/section/question/block
  - suggested fixes

## 5) Pagination + Overflow Strategy

### 5.1 Pagination modes
- Manual: teacher controls explicit page breaks
- Auto: engine decides all breaks
- Hybrid: teacher sets key breaks; engine fills the rest

### 6.2 Overflow strategies
- move-to-next-page (default)
- split-allowed (text only)
- scale-down (rare, controlled)
- reject-at-validation (strict)

  - badges (type, marks)

- **Center panel: Context Editor**
  - section editor OR question editor depending on selection
  - inline constraints hints

- **Right panel: Print & Layout Settings**
  - paper/margins/header/footer
  - overflow defaults
  - pagination mode controls

- **Bottom/Side: Validation + Lifecycle Actions**
  - validate
  - approve
  - render snapshot
  - print/export

### 6.2 Lifecycle UI requirements
- state badge (Draft/Validated/Approved/Rendered)
- “dirty” indicator if edits invalidate validation/snapshot
- confirmation dialogs for lock/approve and render

## 7) Rendering Pipeline

### 7.1 Inputs
- validated exam document
- selected print settings

### 7.2 Steps
1. Build render tree (blocks + resolved question content)
2. Run pagination planner using page budgets
3. Generate deterministic snapshot
   - snapshot should be versioned and referencable
4. Print preview uses snapshot only
5. Export/print uses snapshot only

### 7.3 Determinism requirements
- same input data produces the same pagination and output
- no hidden dependency on screen size

## 8) Extensibility (Plugin mindset)
- question type registry
- block type registry
- (future) grading strategy registry
- (future) export format registry

## 9) Versioning, Audit, and Safety
- each Approved/Rendered step creates a version entry
- record: author, timestamp, notes
- ability to open old rendered snapshots

## 10) Milestones (Implementation Roadmap)

### M1 — Contracts
- define exam JSON contract (schema)
- define lifecycle states and transition rules

### M2 — Validation Engine
- schema validation
- layout preflight validation
- validation report UI

### M3 — Pagination + Render Snapshot
- pagination planner
- snapshot generation
- deterministic preview

### M4 — Ready-To-Print Page UI
- structure tree
- editors
- print settings
- lifecycle actions

### M5 — Print/Export
- print CSS or PDF export flow
- archived versions
