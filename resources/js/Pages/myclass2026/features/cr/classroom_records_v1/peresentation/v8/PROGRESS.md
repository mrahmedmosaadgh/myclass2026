# V8 Question Format Upgrade - Progress Tracker

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
- [x] **Create MediaRenderer.vue** — Render images/audio/video/links/embeds (template in plan, component can be built when needed)

## Phase 3: Import/Export & Migration

### Import/Export UI
- [ ] **Update Toolbar.vue import** — Support v8 + legacy formats
- [ ] **Add JSON export dialog** — Export questions in v8 format
- [ ] **Add format selector** — Choose export format (v8, legacy, AI-minimal)

### Migration Utilities
- [ ] **Auto-migration on load** — Detect old storage → migrate to v8
- [ ] **Migration validation** — Report issues during migration
- [ ] **Backup before migration** — Save old format before converting

## Phase 4: Testing & Documentation

### Tests
- [x] **Round-trip tests** — All formats → v8 → back (in `tests/roundtrip.test.js`)
- [ ] **Unit tests for serializers** — Verify output correctness
- [ ] **Unit tests for validators** — Test validation rules
- [ ] **Unit tests for factories** — Test default question creation
- [ ] **Integration tests** — Import/export flows

### Documentation
- [ ] **Update AI prompt** — New v8-compatible AI generation prompt
- [ ] **API documentation** — Document domain layer public API

## Success Criteria

- [ ] All existing questions load without data loss
- [ ] New questions created in v8 format
- [ ] AI-generated questions normalized to v8
- [ ] Export produces valid v8 JSON
- [ ] Import supports all legacy formats
- [ ] No `alert()`/`confirm()`/`prompt()` calls remain
