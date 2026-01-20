# AI Bulk Generator Pattern - Reusable Implementation Guide

**Date**: 2026-01-20  
**Pattern**: AI-Assisted Bulk Data Generation with 4-Step Wizard  
**First Implementation**: Behavior Management System

## Overview

This document describes a reusable pattern for implementing AI-assisted bulk data generation features. The pattern uses a 4-step wizard to guide users through configuring an AI prompt, copying it to an AI service, pasting the response, and previewing/inserting the generated data.

## When to Use This Pattern

Use this pattern when you need to:
- Generate multiple similar records at once (behaviors, questions, tasks, etc.)
- Leverage AI for content generation with user control
- Provide bilingual content generation
- Allow users to preview and selectively insert generated data
- Maintain data quality through validation

## Architecture

### Component Structure

```
ParentPage.vue (e.g., BehaviorManagement.vue)
├── Trigger Button ("AI Bulk Generator")
├── Success Handler (reloads data after insertion)
└── AIGeneratorDialog.vue
    ├── Step 1: Configure Prompt
    ├── Step 2: Copy Prompt to AI
    ├── Step 3: Paste AI Response
    └── Step 4: Preview & Insert
```

### Backend Structure

```
Controller (e.g., BehaviorController.php)
├── index() - List items
├── store() - Create single item
└── bulkStore() - Create multiple items (NEW)

Routes (api.php)
├── POST /api/{resource}/bulk - Bulk creation endpoint
└── Route::apiResource('{resource}', Controller::class)
```

## Implementation Steps

### 1. Create the AI Generator Dialog Component

**File**: `resources/js/Pages/{module}/{ResourceName}AIGeneratorDialog.vue`

#### Step 1: Configure Prompt
```vue
<q-step :name="1" title="Configure AI Prompt" icon="settings">
  <div class="q-gutter-md">
    <!-- Count Input -->
    <q-input
      v-model.number="config.count"
      type="number"
      label="Number of Items *"
      min="1"
      max="30"
      :rules="[val => val > 0 && val <= 30 || 'Must be between 1 and 30']"
    />

    <!-- Type/Category Selection -->
    <q-select
      v-model="config.type"
      :options="typeOptions"
      label="Type *"
      emit-value
      map-options
    />

    <!-- Optional Category/Theme -->
    <q-input
      v-model="config.category"
      label="Category/Theme (Optional)"
      outlined
    />

    <!-- Custom Instructions -->
    <q-input
      v-model="config.customInstructions"
      type="textarea"
      label="Additional Instructions / Context"
      outlined
      rows="3"
    />

    <!-- Smart Presets (Optional but Recommended) -->
    <q-expansion-item
      icon="lightbulb"
      label="Smart Presets"
      caption="Click for quick configuration templates"
    >
      <q-card flat bordered class="q-pa-md">
        <div class="text-subtitle2 q-mb-sm">Quick Presets:</div>
        <div class="q-gutter-sm">
          <q-btn
            size="sm"
            outline
            color="secondary"
            label="📚 Preset 1"
            @click="applyPreset('preset1')"
          />
          <!-- Add more presets -->
        </div>
        
        <q-separator class="q-my-md" />
        
        <div class="text-subtitle2 q-mb-sm">Suggested Instructions:</div>
        <q-list dense>
          <q-item clickable @click="addInstruction('Instruction text')">
            <q-item-section avatar>
              <q-icon name="add_circle" color="secondary" />
            </q-item-section>
            <q-item-section>Instruction text</q-item-section>
          </q-item>
        </q-list>
      </q-card>
    </q-expansion-item>

    <!-- Language Selection (if applicable) -->
    <q-select
      v-model="config.language"
      :options="['English', 'Arabic', 'Bilingual (Both)']"
      label="Language"
    />
  </div>

  <q-stepper-navigation>
    <q-btn @click="generatePrompt" color="secondary" label="Generate Prompt" icon="content_copy" />
  </q-stepper-navigation>
</q-step>
```

#### Step 2: Copy Prompt to AI
```vue
<q-step :name="2" title="Copy Prompt to AI" icon="content_copy">
  <q-card flat bordered>
    <q-card-section>
      <div class="text-subtitle2 q-mb-sm">Generated Prompt:</div>
      <q-input
        v-model="generatedPrompt"
        type="textarea"
        readonly
        outlined
        rows="15"
        class="q-mb-md"
      />
      <q-btn
        @click="copyPrompt"
        color="secondary"
        label="Copy to Clipboard"
        icon="content_copy"
        class="q-mr-sm"
      />
      <q-banner class="bg-info text-white q-mt-md">
        <template v-slot:avatar>
          <q-icon name="info" />
        </template>
        Copy this prompt and paste it into ChatGPT, Claude, or Gemini. Then copy the AI's JSON response.
      </q-banner>
    </q-card-section>
  </q-card>

  <q-stepper-navigation>
    <q-btn @click="step = 3" color="secondary" label="Next: Paste AI Response" />
    <q-btn flat @click="step = 1" label="Back" class="q-ml-sm" />
  </q-stepper-navigation>
</q-step>
```

#### Step 3: Paste AI Response
```vue
<q-step :name="3" title="Paste AI Response" icon="paste">
  <q-input
    v-model="aiResponse"
    type="textarea"
    label="Paste AI Response (JSON)"
    outlined
    rows="10"
    hint="Paste the JSON response from the AI"
    class="q-mb-md"
  />

  <!-- IMPORTANT: Add Paste from Clipboard button -->
  <q-btn
    @click="pasteFromClipboard"
    color="primary"
    label="Paste from Clipboard"
    icon="content_paste"
    outline
    class="q-mb-md"
  />

  <q-stepper-navigation>
    <q-btn @click="validateResponse" color="secondary" label="Validate & Preview" :loading="validating" />
    <q-btn flat @click="step = 2" label="Back" class="q-ml-sm" />
  </q-stepper-navigation>
</q-step>
```

#### Step 4: Preview & Insert
```vue
<q-step :name="4" title="Preview & Insert" icon="preview">
  <div v-if="validatedItems.length > 0">
    <!-- Selection Summary -->
    <q-banner class="bg-secondary text-white q-mb-md">
      <template v-slot:avatar>
        <q-icon name="check_circle" />
      </template>
      <div class="row items-center">
        <div class="col">
          <strong>{{ selectedCount }}</strong> of <strong>{{ validatedItems.length }}</strong> items selected
        </div>
        <div class="col-auto q-gutter-sm">
          <q-btn size="sm" outline color="white" label="Select All" @click="selectAll" icon="check_box" />
          <q-btn size="sm" outline color="white" label="Select None" @click="selectNone" icon="check_box_outline_blank" />
          <q-btn size="sm" outline color="white" label="Inverse" @click="selectInverse" icon="swap_vert" />
        </div>
      </div>
    </q-banner>

    <!-- Preview Table -->
    <q-table
      :rows="validatedItems"
      :columns="previewColumns"
      row-key="index"
      flat
      bordered
      selection="multiple"
      v-model:selected="selectedItems"
      class="q-mb-md"
    >
      <!-- Add custom cell templates as needed -->
    </q-table>

    <!-- Warning for invalid items -->
    <q-banner v-if="invalidCount > 0" class="bg-warning text-white q-mb-md">
      <template v-slot:avatar>
        <q-icon name="warning" />
      </template>
      {{ invalidCount }} invalid item(s) detected (cannot be selected)
    </q-banner>
  </div>

  <q-stepper-navigation>
    <q-btn
      @click="bulkInsert"
      color="secondary"
      :label="`Insert ${selectedCount} Selected Item${selectedCount !== 1 ? 's' : ''}`"
      icon="upload"
      :loading="inserting"
      :disable="selectedCount === 0"
    />
    <q-btn flat @click="step = 3" label="Back" class="q-ml-sm" />
  </q-stepper-navigation>
</q-step>
```

### 2. Key JavaScript Functions

```javascript
// State
const step = ref(1)
const validating = ref(false)
const inserting = ref(false)
const config = ref({
  count: 10,
  type: 'default',
  category: '',
  customInstructions: '',
  language: 'Bilingual (Both)'
})
const generatedPrompt = ref('')
const aiResponse = ref('')
const validatedItems = ref([])
const selectedItems = ref([])

// Generate AI Prompt
const generatePrompt = () => {
  const promptParts = [
    `Generate ${config.value.count} [RESOURCE_NAME] for [CONTEXT].`,
    '',
    `Type: ${config.value.type}`,
    config.value.category ? `Category/Theme: ${config.value.category}` : '',
    `Language: ${config.value.language}`,
    '',
    config.value.customInstructions ? `Additional Instructions:\n${config.value.customInstructions}` : '',
    '',
    'Return ONLY a valid JSON object in this exact format:',
    '```json',
    JSON.stringify({
      [resourceName]: [
        {
          field1: "value1",
          field2: "value2",
          // Define your schema
        }
      ]
    }, null, 2),
    '```',
    '',
    'Important rules:',
    '- List required fields',
    '- List validation rules',
    '- Add any specific instructions'
  ]

  generatedPrompt.value = promptParts.filter(Boolean).join('\n')
  step.value = 2
}

// Copy to Clipboard
const copyPrompt = async () => {
  try {
    await navigator.clipboard.writeText(generatedPrompt.value)
    $q.notify({
      type: 'positive',
      message: 'Prompt copied to clipboard!',
      icon: 'content_copy',
      position: 'top'
    })
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'Failed to copy to clipboard',
      position: 'top'
    })
  }
}

// Paste from Clipboard (IMPORTANT!)
const pasteFromClipboard = async () => {
  try {
    const text = await navigator.clipboard.readText()
    aiResponse.value = text
    $q.notify({
      type: 'positive',
      message: 'Pasted from clipboard!',
      icon: 'content_paste',
      position: 'top'
    })
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'Failed to read from clipboard',
      caption: 'Please paste manually using Ctrl+V or Cmd+V',
      position: 'top'
    })
  }
}

// Validate AI Response
const validateResponse = () => {
  validating.value = true
  
  try {
    let data
    const cleaned = aiResponse.value.trim()
    
    // Remove markdown code blocks if present
    const jsonMatch = cleaned.match(/```(?:json)?\s*([\s\S]*?)```/)
    if (jsonMatch) {
      data = JSON.parse(jsonMatch[1])
    } else {
      data = JSON.parse(cleaned)
    }

    if (!data[resourceName] || !Array.isArray(data[resourceName])) {
      throw new Error(`Invalid format: "${resourceName}" array not found`)
    }

    // Validate each item
    validatedItems.value = data[resourceName].map((item, index) => {
      const errors = []
      
      // Add validation logic
      if (!item.requiredField) errors.push('Missing requiredField')
      
      return {
        ...item,
        index,
        valid: errors.length === 0,
        errors: errors.join(', ')
      }
    })

    step.value = 4
    
    // Auto-select all valid items
    selectedItems.value = validatedItems.value.filter(item => item.valid)
    
    $q.notify({
      type: 'positive',
      message: `Validated ${validatedItems.value.length} items`,
      position: 'top'
    })
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'Invalid JSON format',
      caption: error.message,
      position: 'top'
    })
  } finally {
    validating.value = false
  }
}

// Selection Functions
const selectAll = () => {
  selectedItems.value = validatedItems.value.filter(item => item.valid)
}

const selectNone = () => {
  selectedItems.value = []
}

const selectInverse = () => {
  const currentSelected = new Set(selectedItems.value.map(item => item.index))
  selectedItems.value = validatedItems.value.filter(item => 
    item.valid && !currentSelected.has(item.index)
  )
}

// Bulk Insert
const bulkInsert = async () => {
  inserting.value = true
  
  const itemsToInsert = selectedItems.value.filter(item => item.valid)
  
  if (itemsToInsert.length === 0) {
    $q.notify({
      type: 'warning',
      message: 'No items selected',
      position: 'top'
    })
    inserting.value = false
    return
  }
  
  try {
    // Try bulk endpoint first, fall back to individual creation
    let response
    try {
      response = await axios.post('/api/[resource]/bulk', {
        [resourceName]: itemsToInsert,
        // Add context fields (school_id, etc.)
      })
    } catch (bulkError) {
      // Fallback: create individually
      const created = []
      for (const item of itemsToInsert) {
        const result = await axios.post('/api/[resource]', item)
        created.push(result.data)
      }
      response = { data: created }
    }

    $q.notify({
      type: 'positive',
      message: `Successfully created ${itemsToInsert.length} item${itemsToInsert.length !== 1 ? 's' : ''}!`,
      icon: 'check_circle',
      position: 'top'
    })
    
    // Close dialog and emit success
    showDialog.value = false
    emit('success')
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'Failed to create items',
      caption: error.response?.data?.message || error.message,
      position: 'top'
    })
  } finally {
    inserting.value = false
  }
}

// Smart Presets
const applyPreset = (type) => {
  const presets = {
    preset1: {
      count: 15,
      type: 'type1',
      category: 'Category 1',
      customInstructions: 'Instructions for preset 1'
    },
    // Add more presets
  }

  const preset = presets[type]
  if (preset) {
    Object.assign(config.value, preset)
    $q.notify({
      type: 'info',
      message: `Applied "${type}" preset`,
      position: 'top'
    })
  }
}

// Add Instruction Helper
const addInstruction = (instruction) => {
  if (config.value.customInstructions) {
    config.value.customInstructions += '\n- ' + instruction
  } else {
    config.value.customInstructions = '- ' + instruction
  }
  $q.notify({
    type: 'positive',
    message: 'Instruction added',
    position: 'top'
  })
}
```

### 3. Backend Controller - bulkStore Method

```php
public function bulkStore(Request $request)
{
    $validated = $request->validate([
        '[resourceName]' => 'required|array|min:1|max:50',
        '[resourceName].*.field1' => 'required|string|max:255',
        '[resourceName].*.field2' => 'nullable|string|max:255',
        // Add more validation rules
        'school_id' => 'required|integer|exists:schools,id',
    ]);

    // Get context (school, year, teacher, etc.)
    $school = \App\Models\School::find($validated['school_id']);
    $yearId = $school ? $school->academic_year_id : null;
    
    // Determine user context (role-based logic)
    $user = auth()->user();
    $contextId = null; // teacher_id, user_id, etc.
    
    if ($user && !method_exists($user, 'hasRole') || (!$user->hasRole('admin') && !$user->hasRole('super-admin'))) {
        $contextId = $this->getContextId(); // Your context method
    }

    $created = [];
    $skipped = [];
    $errors = [];

    foreach ($validated['[resourceName]'] as $index => $itemData) {
        try {
            // Check for duplicates
            $exists = [ResourceModel]::where('unique_field', $itemData['unique_field'])
                ->where('school_id', $validated['school_id'])
                ->where('year_id', $yearId)
                ->where('context_id', $contextId)
                ->first();

            if ($exists) {
                $skipped[] = [
                    'index' => $index,
                    'identifier' => $itemData['unique_field'],
                    'reason' => 'Already exists'
                ];
                continue;
            }

            $item = [ResourceModel]::create([
                'field1' => $itemData['field1'],
                'field2' => $itemData['field2'] ?? null,
                // Map all fields
                'school_id' => $validated['school_id'],
                'year_id' => $yearId,
                'context_id' => $contextId,
            ]);

            $created[] = $item;
        } catch (\Exception $e) {
            $errors[] = [
                'index' => $index,
                'identifier' => $itemData['unique_field'] ?? 'Unknown',
                'error' => $e->getMessage()
            ];
        }
    }

    return response()->json([
        'created' => $created,
        'skipped' => $skipped,
        'errors' => $errors,
        'summary' => [
            'total' => count($validated['[resourceName]']),
            'created' => count($created),
            'skipped' => count($skipped),
            'errors' => count($errors)
        ]
    ], 201);
}
```

### 4. Add Route

```php
// In routes/api.php
// IMPORTANT: Bulk route must come BEFORE apiResource

// Bulk creation (must come before apiResource)
Route::post('[resource]/bulk', [[ResourceController]::class, 'bulkStore']);

Route::apiResource('[resource]', [ResourceController]::class);
```

### 5. Integrate into Parent Page

```vue
<!-- In ParentPage.vue -->
<template>
  <div>
    <!-- Header with buttons -->
    <div class="flex gap-3">
      <q-btn
        color="secondary"
        icon="auto_awesome"
        label="AI Bulk Generator"
        @click="showAIGenerator = true"
        size="lg"
        outline
        class="shadow-lg"
      />
      <q-btn
        color="primary"
        icon="add"
        label="Add [Resource]"
        @click="showCreateDialog = true"
        size="lg"
        class="shadow-lg"
      />
    </div>

    <!-- Your existing content -->

    <!-- AI Generator Dialog -->
    <[Resource]AIGeneratorDialog
      v-model="showAIGenerator"
      :school-id="schoolId"
      @success="handleAIGeneratorSuccess"
    />
  </div>
</template>

<script setup>
import [Resource]AIGeneratorDialog from './[Resource]AIGeneratorDialog.vue'

const showAIGenerator = ref(false)

const handleAIGeneratorSuccess = () => {
  loadData() // Reload your data
  $q.notify({
    type: 'positive',
    message: '[Resources] created successfully via AI Generator!',
    icon: 'auto_awesome',
    position: 'top'
  })
}
</script>
```

## Best Practices

### 1. Clipboard Integration
- **Always** include "Paste from Clipboard" button in Step 3
- Provide fallback instructions if clipboard API fails
- Use clear icons: `content_copy` for copy, `content_paste` for paste

### 2. Validation
- Validate on both frontend and backend
- Show clear error messages for invalid items
- Allow partial success (some items created, some skipped)

### 3. User Experience
- Use stepper for clear progress indication
- Show item counts and selection status
- Provide smart presets for common use cases
- Include suggested instructions users can click to add

### 4. Error Handling
- Handle JSON parsing errors gracefully
- Support markdown code blocks (```json```)
- Provide detailed error messages with field names
- Return summary with created/skipped/error counts

### 5. Bilingual Support
- Include language selection in configuration
- Validate both language fields if applicable
- Show both languages in preview table

## Example Use Cases

This pattern has been successfully implemented for:
1. **Behaviors** (Positive/Negative school behaviors)

Future applications could include:
2. **Questions** (Quiz/exam questions with multiple choice)
3. **Tasks** (Project tasks with subtasks)
4. **Curriculum Topics** (Topics and lessons)
5. **Student Goals** (Learning objectives)
6. **Rubric Criteria** (Assessment criteria)
7. **Announcements** (School announcements)

## Files Reference

### First Implementation (Behaviors)
- Dialog: `resources/js/Pages/my_table_mnger/reward_sys/admin/BehaviorAIGeneratorDialog.vue`
- Parent: `resources/js/Pages/my_table_mnger/reward_sys/admin/BehaviorManagement.vue`
- Controller: `app/Http/Controllers/BehaviorController.php`
- Route: `routes/api.php`

## Checklist for New Implementation

- [ ] Create AIGeneratorDialog component with 4 steps
- [ ] Add "Paste from Clipboard" button in Step 3
- [ ] Implement generatePrompt() with proper JSON schema
- [ ] Implement validateResponse() with field validation
- [ ] Add selection functions (selectAll, selectNone, selectInverse)
- [ ] Implement bulkInsert() with fallback to individual creation
- [ ] Add bulkStore() method to controller
- [ ] Add validation rules for bulk data
- [ ] Implement duplicate detection logic
- [ ] Add bulk route BEFORE apiResource
- [ ] Add trigger button to parent page
- [ ] Add success handler to reload data
- [ ] Test end-to-end workflow
- [ ] Test error handling (invalid JSON, validation errors)
- [ ] Test duplicate detection
- [ ] Test partial success scenarios

## Notes

- The clipboard API requires HTTPS in production (works on localhost)
- Consider rate limiting for bulk endpoints
- Add appropriate authorization checks
- Log bulk operations for audit trail
- Consider adding export functionality for generated prompts
