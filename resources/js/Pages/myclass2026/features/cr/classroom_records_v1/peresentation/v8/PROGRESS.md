# V8 Question Format Upgrade - Progress Tracker

## Current Status

- [x] **Current phase** — Phase 2 is mostly complete; Phase 3 is partially started.
- [x] **Completed now** — Domain layer, schema, normalizers, serializers, validators, factories, utilities, store migration, QuizElementV2, QuizGeneratorDialog, and GroupQuizGenerator.
- [ ] **Current blocker** — `QuestionsExportImportDialog.vue` was created, but `Toolbar.vue` was redesigned afterward and the dialog is not wired into the current toolbar UI.
- [ ] **Next step** — Reconnect question import/export UI into the current Quasar Toolbar without disturbing the new toolbar design.
- [ ] **Verification needed** — Run/build the app and test: legacy quiz render, AI quiz generation, group quiz generation, import/export, and migration on load.

## Phase 1: Foundation (Domain Layer)

### Core Infrastructure
- [x] **Create domain layer directory structure** (`domains/questions/`)
- [x] **Create schema definitions** (`schema.js`)
- [x] **Create main domain index** (`domains/questions/index.js`)

### Normalizers (Import → v8)
- [x] **fromQuizEngine.js** — Format A (enterprise quiz engine) → v8
- [x] **fromOldLesson.js** — Format B (old lesson presentation) → v8
- [x] **fromReadyToPrint.js** — Format C (exam builder) → v8
- [x] **fromAI.js** — AI minimal format → v8
- [x] **Main normalizer** (`normalizers/index.js`) — auto-detect + dispatch

### Serializers (v8 → Export)
- [x] **toQuizEngine.js** — v8 → Format A
- [x] **toOldLesson.js** — v8 → Format B
- [x] **toReadyToPrint.js** — v8 → Format C
- [x] **toExport.js** — v8 → clean export JSON
- [x] **Main serializer** (`serializers/index.js`)

### Validation
- [x] **schemaValidator.js** — JSON schema validation
- [x] **contentValidator.js** — Content-specific rules (MCQ, Boolean, etc.)
- [x] **Main validator** (`validators/index.js`) — three-level validation

### Factories
- [x] **createQuestion.js** — Question factory with defaults
- [x] **createOption.js** — Option factory with stable IDs
- [x] **createMedia.js** — Media object factory

### Utilities
- [x] **detectFormat.js** — Auto-detect incoming question format
- [x] **generateId.js** — UUID generation utilities
- [x] **enrichQuestion.js** — Enrich AI-minimal questions to full v8
- [x] **Main utils** (`utils/index.js`)

## Phase 2: Integration (Components & Stores)

### Store Updates
- [x] **Update presentationStore.js** — Use v8 schema for quiz elements
- [x] **Add auto-normalization on load** — Detect old format → auto-convert

### Component Updates
- [x] **Update QuizElementV2.vue** — Render v8 schema questions
- [x] **Update QuizGeneratorDialog.vue** — Generate v8 schema JSON
- [x] **Update GroupQuizGenerator.vue** — Use v8 format

### New Components
- [ ] **Create MediaRenderer.vue** — Render images/audio/video/links/embeds (planned but not created as a real component yet)

## Phase 3: Import/Export & Migration

### Import/Export UI
- [ ] **Update Toolbar.vue question import/export action** — Wire question import/export into the current Quasar toolbar
- [x] **Create JSON export/import dialog** — `QuestionsExportImportDialog.vue` created
- [x] **Add format selector** — Supports v8, v8-minimal, ReadyToPrint, QuizEngine
- [ ] **Connect imported questions to selected quiz / presentation flow** — Define where imported questions should be inserted

### Migration Utilities
- [x] **Auto-migration on load** — Detect old quiz/group-mcq questions → migrate to v8
- [ ] **Migration validation** — Report issues during migration in UI, not only console
- [ ] **Backup before migration** — Save old format before converting

## Phase 4: Testing & Documentation

### Tests
- [x] **Round-trip tests** — All formats → v8 → back (in `tests/roundtrip.test.js`)
- [x] **Unit tests for serializers** — Covered in `roundtrip.test.js`
- [x] **Unit tests for validators** — Covered in `roundtrip.test.js`
- [x] **Unit tests for factories** — Covered in `roundtrip.test.js`
- [ ] **Integration tests** — Import/export flows

### Documentation
- [x] **Update AI prompt** — QuizGeneratorDialog and GroupQuizGenerator now request AI-minimal v8-compatible schema
- [ ] **API documentation** — Document domain layer public API

## Success Criteria

- [ ] All existing questions load without data loss — implemented, needs manual verification
- [x] New questions created in v8 format
- [x] AI-generated questions normalized to v8
- [ ] Export produces valid v8 JSON — dialog created, toolbar connection pending
- [ ] Import supports all legacy formats — domain support exists, UI connection pending
- [ ] No `alert()`/`confirm()`/`prompt()` calls remain — needs final scan
