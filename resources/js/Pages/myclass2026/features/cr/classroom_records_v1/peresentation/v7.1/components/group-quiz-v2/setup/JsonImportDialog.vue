<script setup>
import { ref } from 'vue';

const props = defineProps({
  modelValue: { type: Boolean, default: false }
});

const emit = defineEmits(['update:modelValue', 'import']);

const jsonText = ref('');
const errors = ref([]);
const success = ref('');

function close() {
  emit('update:modelValue', false);
  jsonText.value = '';
  errors.value = [];
  success.value = '';
}

function isValidHexColor(c) {
  if (typeof c !== 'string') return false;
  return /^#[0-9a-fA-F]{6}$/.test(c.trim());
}

function normalizeGroupId(id, takenIds) {
  const raw = String(id ?? '').trim();
  const base = raw ? (raw.toLowerCase().startsWith('g') ? raw : `g${raw}`) : `g${Date.now()}`;
  let next = base;
  let i = 1;
  while (takenIds.has(next)) {
    next = `${base}_${i}`;
    i += 1;
  }
  takenIds.add(next);
  return next;
}

function parseAndValidate(text) {
  const errs = [];
  let parsed;
  try {
    parsed = JSON.parse(String(text ?? ''));
  } catch {
    return { ok: false, errors: ['Invalid JSON. Please paste valid JSON.'], groups: [] };
  }

  const rawGroups = Array.isArray(parsed) ? parsed : parsed?.groups;
  if (!Array.isArray(rawGroups)) {
    return { ok: false, errors: ['JSON must be an array of groups, or an object like { "groups": [...] }.'], groups: [] };
  }
  if (rawGroups.length === 0) {
    return { ok: false, errors: ['Groups list is empty.'], groups: [] };
  }
  if (rawGroups.length > 50) {
    errs.push('Too many groups (max 50).');
  }

  const takenIds = new Set();
  const cleaned = [];

  rawGroups.forEach((g, idx) => {
    if (!g || typeof g !== 'object') {
      errs.push(`Group #${idx + 1} must be an object.`);
      return;
    }
    const name = String(g.name ?? '').trim();
    if (!name) errs.push(`Group #${idx + 1}: name is required.`);

    const color = g.color ?? '';
    if (!isValidHexColor(color)) errs.push(`Group #${idx + 1}: color must be a hex value like #3b82f6.`);

    const scoreNum = Number(g.score ?? 0);
    if (!Number.isFinite(scoreNum)) errs.push(`Group #${idx + 1}: score must be a number.`);

    const id = normalizeGroupId(g.id ?? `g${idx + 1}`, takenIds);
    cleaned.push({
      id,
      name: name || `Group ${idx + 1}`,
      score: Number.isFinite(scoreNum) ? scoreNum : 0,
      color: isValidHexColor(color) ? String(color).trim() : '#8b5cf6'
    });
  });

  if (errs.length > 0) {
    return { ok: false, errors: errs, groups: [] };
  }
  return { ok: true, errors: [], groups: cleaned };
}

function handleImport() {
  errors.value = [];
  success.value = '';

  const result = parseAndValidate(jsonText.value);
  if (!result.ok) {
    errors.value = result.errors;
    return;
  }

  emit('import', result.groups);
  success.value = `${result.groups.length} groups imported successfully.`;
  setTimeout(() => close(), 800);
}

function pasteFromClipboard() {
  navigator.clipboard.readText().then((text) => {
    if (text) jsonText.value = text;
  }).catch(() => {
    errors.value = ['Could not access clipboard.'];
  });
}
</script>

<template>
  <q-dialog :model-value="modelValue" @update:model-value="$emit('update:modelValue', $event)" persistent>
    <q-card style="min-width: 400px; max-width: 90vw">
      <q-card-section class="row items-center">
        <div class="text-h6">Import Groups</div>
        <q-space />
        <q-btn icon="close" flat round dense @click="close" />
      </q-card-section>

      <q-card-section>
        <q-input
          v-model="jsonText"
          type="textarea"
          rows="8"
          outlined
          placeholder='Paste JSON array: [{ "name": "Group A", "color": "#ef4444", "score": 0 }]'
          class="q-mb-sm"
        />

        <q-btn
          flat
          no-caps
          size="sm"
          color="primary"
          icon="content_paste"
          label="Paste from clipboard"
          @click="pasteFromClipboard"
        />

        <q-banner v-if="errors.length > 0" rounded class="bg-negative-1 q-mt-sm">
          <template #avatar>
            <q-icon name="error" color="negative" />
          </template>
          <ul class="q-ma-none">
            <li v-for="(err, i) in errors" :key="i">{{ err }}</li>
          </ul>
        </q-banner>

        <q-banner v-if="success" rounded class="bg-positive-1 q-mt-sm">
          <template #avatar>
            <q-icon name="check_circle" color="positive" />
          </template>
          {{ success }}
        </q-banner>
      </q-card-section>

      <q-card-actions align="right">
        <q-btn flat label="Cancel" color="grey-7" @click="close" />
        <q-btn label="Import" color="primary" @click="handleImport" :disable="!jsonText.trim()" />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<style scoped>
.bg-negative-1 {
  background: #fef2f2;
}
.bg-positive-1 {
  background: #f0fdf4;
}
</style>
