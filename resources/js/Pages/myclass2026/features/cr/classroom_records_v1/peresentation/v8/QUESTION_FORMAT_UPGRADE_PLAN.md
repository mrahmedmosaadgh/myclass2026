# Question Format Upgrade Plan — V8 Presentation Builder

## Executive Summary

Standardize all question formats across v8 to a single canonical schema that:
- Preserves semantic richness (option IDs, rationale, metadata)
- Supports future features (partial credit, analytics, localization)
- Provides bidirectional normalization (import/export)
- Avoids fragile index-based answers
- Includes explicit schema versioning

---

## Current State Analysis

### Three Formats in Use

| Format | Location | Pros | Cons |
|--------|----------|------|------|
| **A: QuizEngine** (`@/types/quiz.ts`) | `QuizEngine.vue` | Rich metadata, stable IDs, rationale | Complex nested structure |
| **B: Old Lesson** | `old_features/lesson_presentation` | Simple, direct | Mixed naming, `correct_answer` as ID string |
| **C: ReadyToPrint v3** | `ReadyToPrint_ver3` | Clean architecture, `content` wrapper | String-only options, index-based answers |

### Problems to Solve

1. **Fragmentation**: 3 different shapes for the same concept
2. **Data loss**: String options lose IDs, rationale, metadata
3. **Fragility**: Index-based answers break on shuffle/reorder
4. **No versioning**: Can't evolve schema safely
5. **No serialization**: Only normalization exists (one-way)

---

## Canonical Schema (v8 Standard)

### Full Schema

```json
{
  "schema_version": 1,
  "id": "uuid-or-string",
  "type": "multiple_choice",
  "marks": 1,
  
  "content": {
    "prompt": "What is \\( 2\\frac{1}{4} + 3\\frac{2}{4} \\)?",
    
    "options": [
      {
        "id": "a",
        "text": "\\( 5\\frac{1}{2} \\)",
        "is_correct": false,
        "rationale": "Remember to add fractions with common denominators"
      },
      {
        "id": "b",
        "text": "\\( 5\\frac{3}{4} \\)",
        "is_correct": true,
        "rationale": "Correct! 2 + 3 = 5, and ¼ + ½ = ¾"
      },
      {
        "id": "c",
        "text": "\\( 6\\frac{1}{4} \\)",
        "is_correct": false,
        "rationale": "Check your whole number addition"
      },
      {
        "id": "d",
        "text": "\\( 5\\frac{3}{8} \\)",
        "is_correct": false,
        "rationale": "Ensure fractions have common denominators"
      }
    ],
    
    "explanation": "Add whole numbers: 2 + 3 = 5. Add fractions: ¼ + ½ = ¾. Result: 5¾",
    "hints": ["Add the whole numbers first", "Then add the fractions with common denominators"]
  },
  
  "meta": {
    "difficulty": 2,
    "bloom_level": 2,
    "estimated_time_sec": 60,
    "source": "ai",
    "tags": ["fractions", "addition", "mixed_numbers"]
  },
  
  "response": {},
  "evaluation": {
    "mode": "auto"
  },
  
  "layout": {}
}
```

### Rich Media Support

Questions and options can include images, audio, video, and links using a `media` array:

```json
{
  "schema_version": 1,
  "id": "q_123",
  "type": "multiple_choice",
  "marks": 1,
  
  "content": {
    "prompt": "Listen to the audio clip and identify the instrument:",
    
    "media": [
      {
        "id": "media_1",
        "type": "audio",
        "url": "/storage/audio/trumpet.mp3",
        "alt": "Audio clip of a musical instrument",
        "placement": "prompt",
        "autoplay": false,
        "controls": true
      },
      {
        "id": "media_2",
        "type": "image",
        "url": "/storage/images/orchestra.jpg",
        "alt": "Orchestra performing",
        "placement": "prompt",
        "width": 400,
        "caption": "A full orchestra"
      }
    ],
    
    "options": [
      {
        "id": "a",
        "text": "Trumpet",
        "is_correct": true,
        "media": [
          {
            "id": "opt_media_1",
            "type": "image",
            "url": "/storage/images/trumpet.jpg",
            "alt": "Trumpet instrument",
            "width": 100
          }
        ]
      },
      {
        "id": "b",
        "text": "Violin",
        "is_correct": false,
        "media": [
          {
            "id": "opt_media_2",
            "type": "image",
            "url": "/storage/images/violin.jpg",
            "alt": "Violin instrument",
            "width": 100
          }
        ]
      }
    ],
    
    "explanation": "The brass sound and timbre are characteristic of a trumpet.",
    "hints": ["Listen for brass instrument characteristics"]
  },
  
  "meta": {
    "difficulty": 2,
    "bloom_level": 3,
    "estimated_time_sec": 45,
    "source": "teacher"
  }
}
```

#### Media Object Schema

```typescript
interface Media {
  id: string                    // Unique media ID
  type: 'image' | 'audio' | 'video' | 'link' | 'embed'
  url: string                   // URL or path to media
  alt?: string                  // Alt text for accessibility
  placement?: 'prompt' | 'option' | 'explanation' | 'hint'
  
  // Image-specific
  width?: number
  height?: number
  caption?: string
  thumbnail?: string
  
  // Audio/Video-specific
  autoplay?: boolean
  controls?: boolean
  loop?: boolean
  muted?: boolean
  duration?: number             // Duration in seconds
  transcript?: string           // For accessibility
  
  // Link-specific
  title?: string
  target?: '_blank' | '_self'
  
  // Embed-specific (YouTube, etc.)
  embed_code?: string
  provider?: 'youtube' | 'vimeo' | 'custom'
  
  // Common
  mime_type?: string
  file_size?: number            // In bytes
  metadata?: Record<string, any>
}
```

#### Media Placement Examples

**1. Image in Prompt**
```json
{
  "prompt": "What shape is shown in the image?",
  "media": [
    {
      "id": "img_1",
      "type": "image",
      "url": "/storage/shapes/triangle.png",
      "alt": "A geometric shape",
      "placement": "prompt",
      "width": 300
    }
  ]
}
```

**2. Video Question**
```json
{
  "prompt": "Watch the video and answer: What is the main theme?",
  "media": [
    {
      "id": "vid_1",
      "type": "video",
      "url": "/storage/videos/lesson1.mp4",
      "alt": "Educational video about photosynthesis",
      "placement": "prompt",
      "controls": true,
      "transcript": "In this video, we explore how plants..."
    }
  ]
}
```

**3. Audio with Multiple Options**
```json
{
  "prompt": "Listen and select the correct pronunciation:",
  "media": [
    {
      "id": "audio_prompt",
      "type": "audio",
      "url": "/storage/audio/word_correct.mp3",
      "placement": "prompt",
      "controls": true
    }
  ],
  "options": [
    {
      "id": "a",
      "text": "Pronunciation A",
      "media": [
        {
          "id": "audio_a",
          "type": "audio",
          "url": "/storage/audio/option_a.mp3",
          "controls": true
        }
      ]
    },
    {
      "id": "b",
      "text": "Pronunciation B",
      "media": [
        {
          "id": "audio_b",
          "type": "audio",
          "url": "/storage/audio/option_b.mp3",
          "controls": true
        }
      ]
    }
  ]
}
```

**4. Link/Reference in Explanation**
```json
{
  "explanation": "For more information, see the reference material.",
  "media": [
    {
      "id": "link_1",
      "type": "link",
      "url": "https://example.com/reference",
      "title": "Additional Reading",
      "placement": "explanation",
      "target": "_blank"
    }
  ]
}
```

**5. YouTube Embed**
```json
{
  "prompt": "Watch the tutorial and answer:",
  "media": [
    {
      "id": "yt_1",
      "type": "embed",
      "provider": "youtube",
      "url": "https://www.youtube.com/watch?v=VIDEO_ID",
      "embed_code": "<iframe src='...'></iframe>",
      "placement": "prompt"
    }
  ]
}
```

#### Media Rendering Strategy

**Component Structure:**
```vue
<template>
  <div class="question-content">
    <!-- Prompt with media -->
    <div class="prompt-section">
      <div v-html="renderMath(question.content.prompt)" />
      <MediaRenderer 
        v-for="media in promptMedia" 
        :key="media.id"
        :media="media"
      />
    </div>
    
    <!-- Options with media -->
    <div class="options-section">
      <div v-for="option in question.content.options" :key="option.id">
        <div v-html="renderMath(option.text)" />
        <MediaRenderer 
          v-for="media in option.media" 
          :key="media.id"
          :media="media"
        />
      </div>
    </div>
    
    <!-- Explanation with media -->
    <div v-if="showExplanation" class="explanation-section">
      <div v-html="renderMath(question.content.explanation)" />
      <MediaRenderer 
        v-for="media in explanationMedia" 
        :key="media.id"
        :media="media"
      />
    </div>
  </div>
</template>
```

**MediaRenderer Component:**
```vue
<!-- components/MediaRenderer.vue -->
<template>
  <div class="media-container" :class="`media-${media.type}`">
    <!-- Image -->
    <img 
      v-if="media.type === 'image'"
      :src="media.url"
      :alt="media.alt"
      :width="media.width"
      :height="media.height"
      :loading="lazy ? 'lazy' : 'eager'"
    />
    <p v-if="media.caption" class="media-caption">{{ media.caption }}</p>
    
    <!-- Audio -->
    <audio 
      v-else-if="media.type === 'audio'"
      :src="media.url"
      :controls="media.controls !== false"
      :autoplay="media.autoplay"
      :loop="media.loop"
      :muted="media.muted"
    >
      <track v-if="media.transcript" kind="captions" :src="media.transcript" />
    </audio>
    
    <!-- Video -->
    <video 
      v-else-if="media.type === 'video'"
      :src="media.url"
      :controls="media.controls !== false"
      :autoplay="media.autoplay"
      :loop="media.loop"
      :muted="media.muted"
      :width="media.width"
      :height="media.height"
    >
      <track v-if="media.transcript" kind="captions" :src="media.transcript" />
    </video>
    
    <!-- Link -->
    <a 
      v-else-if="media.type === 'link'"
      :href="media.url"
      :target="media.target || '_blank'"
      :title="media.title"
      class="media-link"
    >
      {{ media.title || media.url }}
    </a>
    
    <!-- Embed (YouTube, etc.) -->
    <div 
      v-else-if="media.type === 'embed'"
      class="media-embed"
      v-html="sanitizeEmbed(media.embed_code)"
    />
  </div>
</template>
```

#### Media Storage Strategy

**Option 1: Base64 Inline (Small Files)**
```json
{
  "type": "image",
  "url": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAA...",
  "inline": true
}
```

**Option 2: External URL**
```json
{
  "type": "image",
  "url": "https://cdn.example.com/images/diagram.png"
}
```

**Option 3: Local Storage Path**
```json
{
  "type": "image",
  "url": "/storage/presentations/pres_123/media/image_1.jpg",
  "local": true
}
```

**Option 4: Attachment Reference**
```json
{
  "type": "image",
  "attachment_id": "att_456",
  "url": "/api/attachments/att_456"
}
```

#### Import/Export with Media

**Export Options:**
1. **Inline Media** — Base64 encode small files into JSON
2. **Media Package** — ZIP with JSON + media folder
3. **External URLs** — Keep URLs as-is (requires internet)
4. **Attachment Manifest** — JSON + separate attachment list

**Import Validation:**
- Check media URLs are accessible
- Validate file types (MIME)
- Check file sizes
- Scan for security issues
- Generate thumbnails for images/videos

### Minimal Schema (AI Import)

For AI-generated questions, accept a simplified format:

```json
{
  "type": "multiple_choice",
  "prompt": "What is 2 + 2?",
  "options": [
    { "text": "3", "correct": false, "rationale": "Too low" },
    { "text": "4", "correct": true, "rationale": "Correct!" }
  ],
  "explanation": "Basic addition",
  "hints": ["Think simple"]
}
```

System enriches with: `id`, `schema_version`, `marks`, `meta`, `evaluation`, `layout`

---

## Standardized Question Types

### Type Naming Convention

| Old Names | **V8 Standard** | Description |
|-----------|-----------------|-------------|
| `mcq`, `single_choice` | `multiple_choice` | Single correct answer |
| `multiple_choice` (multi-select) | `multiple_select` | Multiple correct answers |
| `true_false`, `boolean` | `boolean` | True/False only |
| `text`, `short_text` | `short_answer` | Text input |
| `essay` | `long_answer` | Essay/paragraph |
| `fill_blank` | `fill_in_blank` | Fill in the blank |
| `matching` | `matching` | Match pairs |
| `ordering` | `ordering` | Sequence items |

### Modern Assessment Question Types (Advanced)

To support the shift from "knowing" to "disciplinary thinking", v8 includes advanced question types:

| **V8 Type** | **Purpose** | **Discipline Focus** |
|-------------|-------------|---------------------|
| `stimulus_mcq` | MCQ with rich context (graph, document, scenario) | All subjects |
| `evidence_based` | Interpret and evaluate evidence | Science, History |
| `claim_evaluation` | Assess validity of claims | Science, Social Studies |
| `data_interpretation` | Analyze graphs, tables, datasets | Science, Geography, Economics |
| `source_analysis` | Critique primary sources for bias/perspective | History, Humanities |
| `scenario_application` | Apply knowledge to real-world contexts | All subjects |
| `code_tracing` | Trace algorithm execution | ICT, Computer Science |
| `debugging` | Identify and fix errors | ICT, Computer Science |
| `multi_step_reasoning` | Complex problem chains | Math, Science, ICT |
| `justification_required` | Answer + explain reasoning | All subjects |

### Stimulus-Based Question Schema

```json
{
  "schema_version": 1,
  "id": "q_456",
  "type": "stimulus_mcq",
  "marks": 2,
  
  "content": {
    "stimulus": {
      "type": "data_table",
      "title": "Population Growth Data (2010-2020)",
      "content": "| Year | City A | City B |\n|------|--------|--------|\n| 2010 | 50,000 | 75,000 |\n| 2015 | 65,000 | 80,000 |\n| 2020 | 85,000 | 82,000 |",
      "media": [
        {
          "id": "graph_1",
          "type": "image",
          "url": "/storage/graphs/population_trend.png",
          "alt": "Line graph showing population trends",
          "placement": "stimulus"
        }
      ],
      "source": "National Census Bureau, 2021",
      "context": "Two cities experienced different growth patterns over the decade."
    },
    
    "prompt": "Based on the data, which conclusion is BEST supported?",
    
    "options": [
      {
        "id": "a",
        "text": "City A had consistent growth while City B plateaued",
        "is_correct": true,
        "rationale": "Data shows City A grew 70% while City B grew only 9.3%",
        "evidence_support": "strong"
      },
      {
        "id": "b",
        "text": "Both cities had similar growth rates",
        "is_correct": false,
        "rationale": "Common misconception: ignoring percentage vs absolute numbers",
        "targets_misconception": "absolute_vs_relative_comparison"
      },
      {
        "id": "c",
        "text": "City B had faster growth than City A",
        "is_correct": false,
        "rationale": "Misreading the trend direction",
        "targets_misconception": "data_misinterpretation"
      },
      {
        "id": "d",
        "text": "Population data is insufficient to draw conclusions",
        "is_correct": false,
        "rationale": "Overly cautious; data clearly shows trends",
        "targets_misconception": "over_skepticism"
      }
    ],
    
    "explanation": "City A grew from 50k to 85k (70% increase), while City B grew from 75k to 82k (9.3% increase). This demonstrates different growth trajectories.",
    
    "hints": [
      "Calculate percentage change for each city",
      "Compare growth rates, not just final numbers"
    ]
  },
  
  "meta": {
    "difficulty": 3,
    "bloom_level": 4,
    "cognitive_demand": "analysis",
    "discipline_thinking": {
      "type": "data_literacy",
      "skills": ["interpret_trends", "calculate_percentages", "compare_rates"]
    },
    "authenticity": {
      "level": "high",
      "real_world_context": "Urban planning and demographic analysis"
    },
    "transfer_requirement": true,
    "assessment_mode": "stimulus_based",
    "estimated_time_sec": 90,
    "source": "teacher",
    "tags": ["data_analysis", "population", "geography", "math_application"]
  }
}
```

### Evidence-Based Question Schema

```json
{
  "type": "evidence_based",
  "content": {
    "stimulus": {
      "type": "scientific_experiment",
      "title": "Plant Growth Experiment",
      "content": "Students tested whether fertilizer affects plant height...",
      "media": [
        {
          "type": "image",
          "url": "/storage/experiments/plant_data.jpg",
          "alt": "Bar graph of plant heights"
        }
      ]
    },
    "prompt": "Which claim is BEST supported by the experimental evidence?",
    "options": [
      {
        "id": "a",
        "text": "Fertilizer increases plant growth",
        "is_correct": true,
        "evidence_support": "strong",
        "reasoning": "Control group averaged 12cm, fertilized group averaged 18cm"
      },
      {
        "id": "b",
        "text": "All plants need fertilizer to grow",
        "is_correct": false,
        "evidence_support": "none",
        "targets_misconception": "overgeneralization",
        "reasoning": "Control plants grew without fertilizer"
      }
    ]
  },
  "meta": {
    "cognitive_demand": "evaluation",
    "discipline_thinking": {
      "type": "scientific_inquiry",
      "skills": ["evaluate_evidence", "distinguish_correlation_causation", "assess_claims"]
    }
  }
}
```

### Code Tracing Question (ICT)

```json
{
  "type": "code_tracing",
  "content": {
    "stimulus": {
      "type": "code_snippet",
      "language": "python",
      "content": "x = 5\ny = 10\nx = x + y\ny = x - y\nx = x - y\nprint(x, y)"
    },
    "prompt": "What output will this code produce?",
    "options": [
      {
        "id": "a",
        "text": "10 5",
        "is_correct": true,
        "rationale": "Classic swap algorithm without temp variable"
      },
      {
        "id": "b",
        "text": "5 10",
        "is_correct": false,
        "targets_misconception": "ignoring_reassignment"
      }
    ]
  },
  "meta": {
    "discipline_thinking": {
      "type": "computational_thinking",
      "skills": ["trace_execution", "track_state", "understand_assignment"]
    }
  }
}
```

### Source Analysis Question (History)

```json
{
  "type": "source_analysis",
  "content": {
    "stimulus": {
      "type": "primary_source",
      "title": "Political Cartoon, 1920",
      "content": "[Description of political cartoon]",
      "media": [
        {
          "type": "image",
          "url": "/storage/sources/cartoon_1920.jpg",
          "alt": "Political cartoon depicting..."
        }
      ],
      "source": "The Daily Tribune, March 15, 1920",
      "context": "Published during the debate over women's suffrage"
    },
    "prompt": "What perspective does the cartoonist most likely hold?",
    "options": [
      {
        "id": "a",
        "text": "Supports women's voting rights",
        "is_correct": true,
        "evidence_support": "strong",
        "reasoning": "Positive portrayal of suffragettes, mocking opposition"
      },
      {
        "id": "b",
        "text": "Opposes women's voting rights",
        "is_correct": false,
        "targets_misconception": "missing_satirical_intent"
      }
    ]
  },
  "meta": {
    "discipline_thinking": {
      "type": "historical_analysis",
      "skills": ["detect_bias", "interpret_perspective", "analyze_sources", "understand_context"]
    }
  }
}
```

### Meta Fields for Modern Assessment

All question types support these enhanced meta fields:

```typescript
interface QuestionMeta {
  // Existing fields
  difficulty: 1 | 2 | 3 | 4 | 5
  bloom_level: 1 | 2 | 3 | 4 | 5 | 6
  estimated_time_sec: number
  source: 'ai' | 'teacher' | 'textbook' | 'standardized'
  tags: string[]
  
  // Modern assessment fields
  cognitive_demand: 'recall' | 'application' | 'analysis' | 'evaluation' | 'synthesis' | 'creation'
  assessment_mode: 'traditional' | 'stimulus_based' | 'critical_thinking' | 'performance_task'
  
  discipline_thinking?: {
    type: 'scientific_inquiry' | 'historical_analysis' | 'computational_thinking' | 
          'data_literacy' | 'artistic_critique' | 'ethical_reasoning'
    skills: string[]  // e.g., ['interpret_evidence', 'evaluate_claims']
  }
  
  authenticity?: {
    level: 'high' | 'medium' | 'low'
    real_world_context?: string
  }
  
  transfer_requirement?: boolean  // Does question require applying learning to new context?
  
  distractor_design?: {
    targets_misconception: boolean
    misconceptions?: string[]  // Common errors targeted by wrong answers
  }
}
```

### Cognitive Demand Levels

| Level | Description | Example |
|-------|-------------|---------|
| **Recall** | Remember facts, definitions | "What is photosynthesis?" |
| **Application** | Use knowledge in familiar context | "Calculate the area of this rectangle" |
| **Analysis** | Break down, interpret, infer | "What pattern does this data show?" |
| **Evaluation** | Judge, critique, assess validity | "Which claim is best supported?" |
| **Synthesis** | Combine ideas, create solutions | "Design an experiment to test..." |
| **Creation** | Generate new ideas, products | "Propose a solution to..." |

### Discipline-Specific Thinking Skills

**Science:**
- `interpret_evidence`
- `design_experiments`
- `evaluate_claims`
- `identify_variables`
- `analyze_data`

**History/Social Studies:**
- `detect_bias`
- `analyze_causation`
- `interpret_sources`
- `evaluate_perspectives`
- `understand_context`

**ICT/Computer Science:**
- `trace_execution`
- `debug_code`
- `optimize_algorithms`
- `abstract_problems`
- `decompose_systems`

**Mathematics:**
- `apply_formulas`
- `recognize_patterns`
- `justify_reasoning`
- `model_situations`
- `verify_solutions`

**Arts:**
- `analyze_composition`
- `critique_technique`
- `interpret_meaning`
- `evaluate_aesthetics`

---

## Architecture: Domain Layer

### New Directory Structure

```
/v8/
  domains/
    questions/
      index.js                    # Public API
      schema.js                   # Schema definitions
      normalizers/
        index.js                  # Main normalizer
        fromQuizEngine.js         # Format A → v8
        fromOldLesson.js          # Format B → v8
        fromReadyToPrint.js       # Format C → v8
        fromAI.js                 # AI minimal → v8
      serializers/
        index.js                  # Main serializer
        toQuizEngine.js           # v8 → Format A
        toOldLesson.js            # v8 → Format B
        toReadyToPrint.js         # v8 → Format C
        toExport.js               # v8 → Export JSON
      validators/
        index.js                  # Validation rules
        schemaValidator.js        # JSON schema validation
        contentValidator.js       # Content-specific rules
      factories/
        createQuestion.js         # Question factory
        createOption.js           # Option factory
      utils/
        detectFormat.js           # Auto-detect source format
        generateId.js             # ID generation
        enrichQuestion.js         # Add defaults/metadata
```

---

## Implementation Plan

### Phase 1: Foundation (Week 1)

#### 1.1 Create Domain Layer
- [ ] Create `/domains/questions/` directory structure
- [ ] Define v8 canonical schema in `schema.js`
- [ ] Create TypeScript/JSDoc types
- [ ] Add schema versioning constant

#### 1.2 Build Normalizers
- [ ] `normalizeQuestion(raw, sourceFormat = 'auto')` — main entry point
- [ ] `detectFormat(raw)` — auto-detect source format
- [ ] `fromQuizEngine(raw)` — Format A → v8
- [ ] `fromOldLesson(raw)` — Format B → v8
- [ ] `fromReadyToPrint(raw)` — Format C → v8
- [ ] `fromAI(raw)` — AI minimal → v8
- [ ] `enrichQuestion(partial)` — add IDs, defaults, metadata

#### 1.3 Build Serializers
- [ ] `serializeQuestion(question, targetFormat)` — main entry point
- [ ] `toQuizEngine(question)` — v8 → Format A (for QuizEngine compatibility)
- [ ] `toReadyToPrint(question)` — v8 → Format C (for exam builder)
- [ ] `toExport(question)` — v8 → clean export JSON

#### 1.4 Build Validators
- [ ] JSON schema validator (using Ajv or similar)
- [ ] Content validators (required fields, option count, etc.)
- [ ] Type-specific validators (MCQ needs options, etc.)

#### 1.5 Build Factories
- [ ] `createQuestion(partial)` — smart factory with defaults
- [ ] `createOption(partial)` — option factory with auto-ID

---

### Phase 2: Integration (Week 2)

#### 2.1 Update Quiz Generator Dialog
- [ ] Update AI prompt to request minimal schema
- [ ] Use `normalizeQuestion(aiResponse, 'ai')` on import
- [ ] Use `enrichQuestion()` to add metadata
- [ ] Update preview to render v8 format
- [ ] Update JSON import to accept multiple formats

#### 2.2 Update Group Quiz Generator
- [ ] Replace current `normalizeQuestion()` with domain layer
- [ ] Use `fromAI()` normalizer
- [ ] Update preview rendering

#### 2.3 Update Quiz Element V2
- [ ] Create computed property: `uiQuestion = toQuizUIModel(question)`
- [ ] Render from UI model, not raw storage
- [ ] Support `is_correct` flag instead of index

#### 2.4 Update Interactive Group MCQ
- [ ] Use v8 format internally
- [ ] Update option rendering to use `option.id` and `option.is_correct`

---

### Phase 3: Import/Export (Week 3)

#### 3.1 JSON Import/Export Component
- [ ] Create `JsonImportExportDialog.vue` component
- [ ] Support multiple input formats (auto-detect)
- [ ] Validate on import
- [ ] Show format conversion preview
- [ ] Export options: v8, QuizEngine, ReadyToPrint, Clean

#### 3.2 Bulk Operations
- [ ] Import array of questions
- [ ] Export selected questions
- [ ] Batch normalize
- [ ] Validation report for bulk imports

#### 3.3 Migration Utilities
- [ ] CLI script to migrate existing presentations
- [ ] Backup before migration
- [ ] Validation report after migration

---

### Phase 4: Testing & Documentation (Week 4)

#### 4.1 Unit Tests
- [ ] Normalizer tests (all formats → v8)
- [ ] Serializer tests (v8 → all formats)
- [ ] Validator tests
- [ ] Factory tests
- [ ] Round-trip tests (A → v8 → A should be lossless)

#### 4.2 Integration Tests
- [ ] AI import flow
- [ ] JSON import flow
- [ ] Quiz rendering
- [ ] Group quiz rendering

#### 4.3 Documentation
- [ ] Schema documentation (with examples)
- [ ] Migration guide
- [ ] API reference for domain layer
- [ ] Examples for each question type

---

## Import/Export Strategy

### Import Flow

```
Raw JSON Input
    ↓
detectFormat(raw)
    ↓
normalizeQuestion(raw, format)
    ↓
validateQuestion(normalized)
    ↓
enrichQuestion(normalized)  [add IDs, defaults, metadata]
    ↓
V8 Canonical Question
    ↓
Store in presentation
```

### Export Flow

```
V8 Canonical Question
    ↓
serializeQuestion(question, targetFormat)
    ↓
validateSerialized(output)
    ↓
Export JSON
```

### Supported Export Formats

1. **v8** — Full canonical schema (default)
2. **v8-minimal** — Remove empty fields, IDs, metadata
3. **quiz-engine** — Format A for QuizEngine compatibility
4. **ready-to-print** — Format C for exam builder
5. **ai-friendly** — Minimal schema for AI re-import

---

## JSON Import/Export UI

### Import Dialog Features

- **Paste JSON** — textarea for JSON input
- **Upload File** — .json file upload
- **Format Detection** — auto-detect or manual select
- **Validation** — real-time validation with error messages
- **Preview** — show normalized result before import
- **Batch Import** — import array of questions

### Export Dialog Features

- **Select Questions** — checkbox list of questions
- **Format Selector** — dropdown for target format
- **Options**:
  - Include metadata
  - Include IDs
  - Include empty fields
  - Pretty print (indented)
- **Preview** — show export JSON before download
- **Download** — save as .json file
- **Copy to Clipboard** — copy JSON

---

## Migration Path for Existing Data

### Automatic Migration

When loading old presentations:

```js
// In presentationStore.js
function loadPresentation(data) {
  const slides = data.slides.map(slide => ({
    ...slide,
    elements: slide.elements.map(element => {
      if (element.type === 'quiz-v2' || element.type === 'group-quiz') {
        // Detect and normalize questions
        element.questions = element.questions.map(q => 
          normalizeQuestion(q, 'auto')
        )
      }
      return element
    })
  }))
  
  return { ...data, slides, schema_version: 1 }
}
```

### Manual Migration Tool

Create a migration utility:

```js
// domains/questions/migrate.js
export function migratePresentation(presentation) {
  const backup = JSON.parse(JSON.stringify(presentation))
  
  const migrated = {
    ...presentation,
    schema_version: 1,
    slides: presentation.slides.map(slide => ({
      ...slide,
      elements: slide.elements.map(element => {
        if (hasQuestions(element)) {
          return {
            ...element,
            questions: element.questions.map(q => 
              normalizeQuestion(q, 'auto')
            )
          }
        }
        return element
      })
    }))
  }
  
  return { backup, migrated, report: generateMigrationReport(backup, migrated) }
}
```

---

## Validation Strategy

### Three-Level Validation

1. **Schema Validation** — JSON structure matches schema
2. **Content Validation** — Required fields present, types correct
3. **Semantic Validation** — Business rules (e.g., at least 1 correct option)

### Validation Rules

#### Multiple Choice
- ✓ At least 2 options
- ✓ At least 1 correct option
- ✓ All option IDs unique
- ✓ Prompt not empty
- ⚠ Recommended: 3-5 options
- ⚠ Recommended: Only 1 correct option (unless multiple_select)

#### Boolean
- ✓ Exactly 2 options
- ✓ Exactly 1 correct option
- ✓ Option texts are "True" and "False"

#### Short Answer
- ✓ Prompt not empty
- ✓ At least 1 acceptable answer
- ⚠ Recommended: Case-insensitive matching

---

## Backward Compatibility

### Reading Old Formats

All old formats automatically normalized on load via `detectFormat()` + `normalizeQuestion()`

### Writing Old Formats

Use serializers when needed:

```js
// Export for QuizEngine
const quizEngineFormat = serializeQuestion(question, 'quiz-engine')

// Export for ReadyToPrint
const examFormat = serializeQuestion(question, 'ready-to-print')
```

### Presentation Storage

Store in v8 format, serialize on export only if needed.

---

## AI Prompt Updates

### Current Prompt Issues

- Requests full complex schema
- AI hallucinates structure
- Double-escaping confusion

### New AI Prompt Strategy

Request **minimal schema** only:

```
Generate a JSON array of multiple choice questions.

Use this EXACT format for each question:
{
  "type": "multiple_choice",
  "prompt": "Question text here (use \\( and \\) for LaTeX math)",
  "options": [
    {
      "text": "Option A text",
      "correct": false,
      "rationale": "Why this is wrong"
    },
    {
      "text": "Option B text",
      "correct": true,
      "rationale": "Why this is correct"
    }
  ],
  "explanation": "Overall explanation after answering",
  "hints": ["Hint 1", "Hint 2"]
}

IMPORTANT for LaTeX:
- Use single backslash: \( not \\(
- Use \frac{1}{2} not \\frac{1}{2}
- JSON will escape automatically

Return ONLY the JSON array, no other text.
```

Then system enriches with IDs, metadata, etc.

---

## Benefits of This Approach

### Immediate Benefits

1. **Single source of truth** — one canonical format
2. **Future-proof** — schema versioning allows evolution
3. **Stable IDs** — no index-based fragility
4. **Rich metadata** — rationale, hints, analytics ready
5. **Bidirectional** — import AND export any format

### Long-Term Benefits

1. **Extensibility** — easy to add new question types
2. **Analytics** — option-level data for insights
3. **Localization** — stable IDs for translations
4. **Partial credit** — option-level scoring ready
5. **Accessibility** — metadata for screen readers
6. **AI training** — rationale for model fine-tuning

---

## File Checklist

### New Files to Create

```
/v8/domains/questions/
  ├── index.js
  ├── schema.js
  ├── types.d.ts (or JSDoc)
  ├── normalizers/
  │   ├── index.js
  │   ├── fromQuizEngine.js
  │   ├── fromOldLesson.js
  │   ├── fromReadyToPrint.js
  │   └── fromAI.js
  ├── serializers/
  │   ├── index.js
  │   ├── toQuizEngine.js
  │   ├── toReadyToPrint.js
  │   └── toExport.js
  ├── validators/
  │   ├── index.js
  │   ├── schemaValidator.js
  │   └── contentValidator.js
  ├── factories/
  │   ├── createQuestion.js
  │   └── createOption.js
  └── utils/
      ├── detectFormat.js
      ├── generateId.js
      └── enrichQuestion.js

/v8/components/
  └── JsonImportExportDialog.vue

/v8/composables/
  └── useQuestionDomain.js  (wrapper for domain layer)
```

### Files to Update

```
/v8/components/quiz-v2/
  ├── QuizGeneratorDialog.vue     [use normalizers]
  └── QuizElementV2.vue           [render from UI model]

/v8/components/group-quiz/
  ├── GroupQuizGenerator.vue      [use normalizers]
  └── InteractiveGroupMCQ.vue     [render from UI model]

/v8/stores/
  └── presentationStore.js        [auto-migrate on load]

/v8/
  └── DONE.md                     [document migration]
```

---

## Testing Strategy

### Unit Tests

```js
// normalizers/fromAI.test.js
describe('fromAI normalizer', () => {
  it('should convert minimal AI format to v8', () => {
    const ai = {
      type: 'multiple_choice',
      prompt: 'What is 2+2?',
      options: [
        { text: '3', correct: false },
        { text: '4', correct: true }
      ]
    }
    
    const v8 = fromAI(ai)
    
    expect(v8.schema_version).toBe(1)
    expect(v8.content.options[0].id).toBeDefined()
    expect(v8.content.options[1].is_correct).toBe(true)
  })
})
```

### Integration Tests

```js
// Import → Render flow
it('should import AI JSON and render correctly', () => {
  const aiJson = '[{"type":"multiple_choice",...}]'
  const questions = importQuestions(aiJson)
  const rendered = renderQuiz(questions)
  
  expect(rendered).toMatchSnapshot()
})
```

### Round-Trip Tests

```js
// Ensure no data loss
it('should round-trip without loss', () => {
  const original = createQuestion({ type: 'multiple_choice', ... })
  const exported = serializeQuestion(original, 'quiz-engine')
  const reimported = normalizeQuestion(exported, 'quiz-engine')
  
  expect(reimported).toEqual(original)
})
```

---

## Success Criteria

### Phase 1 Complete When:
- [ ] All normalizers working
- [ ] All serializers working
- [ ] Validators working
- [ ] Unit tests passing (>90% coverage)

### Phase 2 Complete When:
- [ ] Quiz generators use domain layer
- [ ] Quiz renderers use UI models
- [ ] No direct format manipulation in components

### Phase 3 Complete When:
- [ ] Import/export dialog working
- [ ] Multiple formats supported
- [ ] Validation on import
- [ ] Migration utility tested

### Phase 4 Complete When:
- [ ] All tests passing
- [ ] Documentation complete
- [ ] Migration guide published
- [ ] Existing presentations migrated

---

## Risk Mitigation

### Risks

1. **Breaking existing presentations** — auto-migration fails
2. **Data loss** — normalization loses information
3. **Performance** — validation/normalization too slow
4. **Complexity** — domain layer too complex

### Mitigations

1. **Always backup** before migration
2. **Round-trip tests** ensure no data loss
3. **Lazy validation** — validate on save, not on every render
4. **Simple API** — hide complexity behind clean interface

---

## Timeline Estimate

| Phase | Duration | Deliverables |
|-------|----------|--------------|
| Phase 1: Foundation | 1 week | Domain layer, normalizers, serializers, validators |
| Phase 2: Integration | 1 week | Updated components, stores |
| Phase 3: Import/Export | 1 week | UI, bulk operations, migration |
| Phase 4: Testing & Docs | 1 week | Tests, documentation, migration guide |
| **Total** | **4 weeks** | Production-ready question domain |

---

## Next Steps

1. **Review this plan** — confirm approach
2. **Create domain directory** — set up structure
3. **Define schema** — finalize v8 canonical schema
4. **Build normalizers** — start with `fromAI()`
5. **Update Quiz Generator** — first integration point
6. **Iterate** — test, refine, expand

---

## Questions for Confirmation

1. ✅ Approve v8 canonical schema?
2. ✅ Approve domain layer architecture?
3. ✅ Approve import/export strategy?
4. ✅ Approve migration approach?
5. ✅ Ready to start Phase 1?

---

**Status**: ⏸️ Awaiting confirmation before implementation
