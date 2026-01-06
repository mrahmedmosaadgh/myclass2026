<template>
  <q-dialog v-model="showDialog" persistent>
    <q-card style="min-width: 600px">
      <q-card-section>
        <div class="text-h6">Edit Weekly Plan</div>
      </q-card-section>

      <q-card-section>
        <div class="q-gutter-md">
          <!-- Period Order (Schedule) -->
          <div v-if="planData?.schedule?.id" class="row q-col-gutter-md">
            <div class="col-12 col-sm-6">
              <q-input
                v-model.number="planData.schedule.period_order"
                type="number"
                min="1"
                label="Period Order"
                dense
                outlined
              />
            </div>
          </div>

          <!-- Classwork -->
          <div>
            <q-input
              v-model="planData.cw"
              label="Classwork (CW)"
              type="textarea"
              :rows="4"
            />
            <div class="row items-center q-gutter-sm q-mt-xs">
              <q-btn dense flat icon="link" label="Insert Link" @click="openLinkDialog('cw')" />
              <q-btn dense flat icon="content_copy" label="Copy" @click="copyField('cw')" />
              <q-btn dense flat icon="content_paste" label="Paste" @click="pasteField('cw')" />
              <q-btn dense flat icon="clear" label="Clear" @click="clearField('cw')" />
            </div>
          </div>

          <!-- Homework -->
          <div>
            <q-input
              v-model="planData.hw"
              label="Homework (HW)"
              type="textarea"
              :rows="4"
            />
            <div class="row items-center q-gutter-sm q-mt-xs">
              <q-btn dense flat icon="link" label="Insert Link" @click="openLinkDialog('hw')" />
              <q-btn dense flat icon="content_copy" label="Copy" @click="copyField('hw')" />
              <q-btn dense flat icon="content_paste" label="Paste" @click="pasteField('hw')" />
              <q-btn dense flat icon="clear" label="Clear" @click="clearField('hw')" />
            </div>
          </div>

          <!-- Notes -->
          <div>
            <q-input
              v-model="planData.notes"
              label="Notes"
              type="textarea"
              :rows="4"
            />
            <div class="row items-center q-gutter-sm q-mt-xs">
              <q-btn dense flat icon="link" label="Insert Link" @click="openLinkDialog('notes')" />
              <q-btn dense flat icon="content_copy" label="Copy" @click="copyField('notes')" />
              <q-btn dense flat icon="content_paste" label="Paste" @click="pasteField('notes')" />
              <q-btn dense flat icon="clear" label="Clear" @click="clearField('notes')" />
            </div>
          </div>
        </div>
      </q-card-section>

      <!-- Live Preview (auto-links URLs, safe-escaped) -->
      <q-separator />
      <q-card-section>
        <div class="text-subtitle2 q-mb-sm">Preview</div>
        <div class="q-gutter-md">
          <div>
            <div class="text-caption text-grey-7">CW</div>
            <div class="q-pa-sm preview-content" v-html="previewCw"></div>
          </div>
          <div>
            <div class="text-caption text-grey-7">HW</div>
            <div class="q-pa-sm preview-content" v-html="previewHw"></div>
          </div>
          <div>
            <div class="text-caption text-grey-7">Notes</div>
            <div class="q-pa-sm preview-content" v-html="previewNotes"></div>
          </div>
        </div>
      </q-card-section>

      <q-card-section align="right">
    <q-btn flat label="Clear" color="negative" @click="clearAll" class="q-mr-sm">
      <q-tooltip>Clear CW, HW and Notes</q-tooltip>
    </q-btn>
        <q-btn flat label="Cancel" @click="$emit('close')" color="primary" v-close-popup />
        <q-btn flat label="Save" @click="$emit('submit', planData)" color="primary" :loading="saving" />
      </q-card-section>

      <!-- Insert Link Dialog -->
      <q-dialog v-model="linkDialog">
        <q-card style="min-width: 420px">
          <q-card-section>
            <div class="text-h6">Insert Link</div>
          </q-card-section>
          <q-card-section class="q-gutter-md">
            <q-input v-model="linkUrl" label="URL (https://...)" type="url" />
            <q-input v-model="linkText" label="Display text (optional)" />
          </q-card-section>
          <q-card-actions align="right">
            <q-btn flat label="Cancel" v-close-popup />
            <q-btn flat label="Insert" color="primary" @click="insertLink" />
          </q-card-actions>
        </q-card>
      </q-dialog>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import { useQuasar } from 'quasar';

const props = defineProps({
  modelValue: {
    type: Boolean,
    required: true
  },
  plan: {
    type: Object,
    required: true
  },
  saving: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['update:modelValue', 'submit', 'close']);
const $q = useQuasar();

// Reactive reference for dialog visibility
const showDialog = ref(props.modelValue);

// Reactive reference for plan data
const planData = ref({ ...props.plan });

// Keep local planData in sync when a new plan is passed in
watch(
  () => props.plan,
  (newVal) => {
    planData.value = { ...newVal };
  },
  { immediate: true }
);

// Watch for changes in the modelValue prop to update dialog visibility
watch(
  () => props.modelValue,
  (newVal) => {
    showDialog.value = newVal;
  }
);

// Watch for changes in dialog visibility to emit update event
watch(
  () => showDialog.value,
  (newVal) => {
    emit('update:modelValue', newVal);
  }
);

// Clear helper: wipe CW/HW/Notes
const clearAll = () => {
  if (!planData.value) return;
  planData.value.cw = '';
  planData.value.hw = '';
  planData.value.notes = '';
};

// Per-field actions
const clearField = (field) => {
  if (!planData.value) return;
  planData.value[field] = '';
};

const copyField = async (field) => {
  try {
    await navigator.clipboard.writeText(planData.value?.[field] ?? '');
    $q.notify({ type: 'positive', message: 'Copied to clipboard' });
  } catch (e) {
    $q.notify({ type: 'negative', message: 'Copy failed. Check browser permissions.' });
  }
};

const pasteField = async (field) => {
  try {
    const text = await navigator.clipboard.readText();
    planData.value[field] = text;
  } catch (e) {
    $q.notify({ type: 'negative', message: 'Paste failed. Check browser permissions.' });
  }
};

// Insert Link support
const linkDialog = ref(false);
const linkTargetField = ref('cw');
const linkUrl = ref('');
const linkText = ref('');

const openLinkDialog = (field) => {
  linkTargetField.value = field;
  linkUrl.value = '';
  linkText.value = '';
  linkDialog.value = true;
};

const insertLink = () => {
  const url = (linkUrl.value || '').trim();
  const txt = (linkText.value || '').trim();
  if (!url) {
    $q.notify({ type: 'warning', message: 'Please enter a URL' });
    return;
  }
  // Use Markdown link when display text provided, otherwise raw URL
  const snippet = txt ? `[${txt}](${url})` : url;
  const field = linkTargetField.value;
  const cur = planData.value[field] || '';
  planData.value[field] = cur ? `${cur}\n${snippet}` : snippet;
  linkDialog.value = false;
};

// Safe rich-text preview: escape HTML, support a small Markdown subset, auto-link URLs
const escapeHtml = (s) => (s || '').replace(/&/g, '&amp;')
  .replace(/</g, '&lt;')
  .replace(/>/g, '&gt;')
  .replace(/\"/g, '&quot;')
  .replace(/'/g, '&#039;');

const renderRich = (s) => {
  const src = String(s || '');
  // 1) escape raw HTML
  let t = escapeHtml(src);
  // 2) Markdown links [text](url) → protect with placeholders to avoid autolink breaking href
  const placeholders = [];
  t = t.replace(/\[([^\]]+)\]\((https?:\/\/[^\s)]+)\)/g, (m, text, url) => {
    const token = `__LINK_${placeholders.length}__`;
    placeholders.push(`<a href="${url}" target="_blank" rel="noopener">${text}</a>`);
    return token;
  });
  // 3) Auto-link plain URLs
  t = t.replace(/(https?:\/\/[\w\-._~:/?#\[\]@!$&'()*+,;=%]+)/g, '<a href="$1" target="_blank" rel="noopener">$1</a>');
  // 4) Restore markdown link placeholders
  placeholders.forEach((html, i) => {
    t = t.replace(`__LINK_${i}__`, html);
  });
  // 5) Inline styles: **bold**, *italic*, `code`
  t = t.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>')
       .replace(/\*([^*]+)\*/g, '<em>$1</em>')
       .replace(/`([^`]+)`/g, '<code>$1</code>');
  // 6) Headings #, ##, ###; lists; paragraphs
  const lines = t.split(/\n/);
  let html = '';
  let inUl = false, inOl = false;
  const closeLists = () => {
    if (inUl) { html += '</ul>'; inUl = false; }
    if (inOl) { html += '</ol>'; inOl = false; }
  };
  for (let i = 0; i < lines.length; i++) {
    const line = lines[i];
    if (!line.trim()) { closeLists(); continue; }
    let m;
    if ((m = line.match(/^\s*###\s+(.+)/))) { closeLists(); html += `<h3>${m[1]}</h3>`; continue; }
    if ((m = line.match(/^\s*##\s+(.+)/)))  { closeLists(); html += `<h2>${m[1]}</h2>`; continue; }
    if ((m = line.match(/^\s*#\s+(.+)/)))   { closeLists(); html += `<h1>${m[1]}</h1>`; continue; }
    if ((m = line.match(/^\s*[-*]\s+(.+)/))) {
      if (!inUl) { closeLists(); html += '<ul>'; inUl = true; }
      html += `<li>${m[1]}</li>`; continue;
    }
    if ((m = line.match(/^\s*\d+\.\s+(.+)/))) {
      if (!inOl) { closeLists(); html += '<ol>'; inOl = true; }
      html += `<li>${m[1]}</li>`; continue;
    }
    closeLists();
    html += `<p>${line}</p>`;
  }
  closeLists();
  return html || '';
};

const previewCw = computed(() => renderRich(planData.value?.cw));
const previewHw = computed(() => renderRich(planData.value?.hw));
const previewNotes = computed(() => renderRich(planData.value?.notes));
</script>

<style  >
.preview-content {
  /* "Real" body text look */
  color: var(--q-dark, #1d1d1d);
  font-size: 14px;
  line-height: 1.6;
  background: transparent;
}
.preview-content a {
  color: var(--q-primary, #027be3);
  text-decoration: underline;
}
.preview-content h1,
.preview-content h2,
.preview-content h3 {
  color: var(--q-dark, #1d1d1d);
  margin: 0.25rem 0;
}
.preview-content p {
  margin: 0.25rem 0;
}
.preview-content ul,
.preview-content ol {
  margin: 0.25rem 1rem;
}
.preview-content code {
  background: rgba(0,0,0,0.04);
  padding: 0 4px;
  border-radius: 3px;
}
</style>