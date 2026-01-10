<template>
  <q-dialog v-model="showDialog" persistent>
    <q-card style="min-width: 600px">
      <q-card-section>
        <div class="text-h6">{{ t('weeklyPlans.editPlan') }}</div>
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
                :label="t('weeklyPlans.periodOrder')"
                dense
                outlined
              />
            </div>
          </div>

          <!-- Classwork -->
          <div>
            <q-input
              v-model="planData.cw"
              :label="t('weeklyPlans.classwork')"
              type="textarea"
              :rows="4"
            />
            <div class="row items-center q-gutter-sm q-mt-xs">
              <q-btn dense flat icon="link" :label="t('weeklyPlans.insertLink')" @click="openLinkDialog('cw')" />
              <q-btn dense flat icon="content_copy" :label="t('weeklyPlans.copy')" @click="copyField('cw')" />
              <q-btn dense flat icon="content_paste" :label="t('weeklyPlans.paste')" @click="pasteField('cw')" />
              <q-btn dense flat icon="clear" :label="t('weeklyPlans.clear')" @click="clearField('cw')" />
            </div>
          </div>

          <!-- Homework -->
          <div>
            <q-input
              v-model="planData.hw"
              :label="t('weeklyPlans.homework')"
              type="textarea"
              :rows="4"
            />
            <div class="row items-center q-gutter-sm q-mt-xs">
              <q-btn dense flat icon="link" :label="t('weeklyPlans.insertLink')" @click="openLinkDialog('hw')" />
              <q-btn dense flat icon="content_copy" :label="t('weeklyPlans.copy')" @click="copyField('hw')" />
              <q-btn dense flat icon="content_paste" :label="t('weeklyPlans.paste')" @click="pasteField('hw')" />
              <q-btn dense flat icon="clear" :label="t('weeklyPlans.clear')" @click="clearField('hw')" />
            </div>
          </div>

          <!-- Notes -->
          <div>
            <q-input
              v-model="planData.notes"
              :label="t('weeklyPlans.notes')"
              type="textarea"
              :rows="4"
            />
            <div class="row items-center q-gutter-sm q-mt-xs">
              <q-btn dense flat icon="link" :label="t('weeklyPlans.insertLink')" @click="openLinkDialog('notes')" />
              <q-btn dense flat icon="content_copy" :label="t('weeklyPlans.copy')" @click="copyField('notes')" />
              <q-btn dense flat icon="content_paste" :label="t('weeklyPlans.paste')" @click="pasteField('notes')" />
              <q-btn dense flat icon="clear" :label="t('weeklyPlans.clear')" @click="clearField('notes')" />
            </div>
          </div>
        </div>
      </q-card-section>

      <!-- Live Preview (auto-links URLs, safe-escaped) -->
      <q-separator />
      <q-card-section>
        <div class="text-subtitle2 q-mb-sm">{{ t('weeklyPlans.preview') }}</div>
        <div class="q-gutter-md">
          <div>
            <div class="text-caption text-grey-7">CW</div>
            <div class="q-pa-sm preview-content" v-html="previewCw"></div>
          </div>
          <div>
            <div class="text-caption text-grey-7">{{ t('weeklyPlans.teacher.hwLabel') }}</div>
            <div class="q-pa-sm preview-content" v-html="previewHw"></div>
          </div>
          <div>
            <div class="text-caption text-grey-7">{{ t('weeklyPlans.teacher.notesLabel') }}</div>
            <div class="q-pa-sm preview-content" v-html="previewNotes"></div>
          </div>
        </div>
      </q-card-section>

      <q-card-section align="right">
    <q-btn flat :label="t('weeklyPlans.clear')" color="negative" @click="clearAll" class="q-mr-sm">
      <q-tooltip>{{ t('weeklyPlans.clearAllTooltip') }}</q-tooltip>
    </q-btn>
        <q-btn flat :label="t('weeklyPlans.cancel')" @click="$emit('close')" color="primary" v-close-popup />
        <q-btn flat :label="t('weeklyPlans.save')" @click="$emit('submit', planData)" color="primary" :loading="saving" />
      </q-card-section>

      <!-- Insert Link Dialog -->
      <q-dialog v-model="linkDialog">
        <q-card style="min-width: 420px">
          <q-card-section>
            <div class="text-h6">{{ t('weeklyPlans.linkDialog.title') }}</div>
          </q-card-section>
          <q-card-section class="q-gutter-md">
            <q-input v-model="linkUrl" :label="t('weeklyPlans.linkDialog.urlLabel')" type="url" />
            <q-input v-model="linkText" :label="t('weeklyPlans.linkDialog.textLabel')" />
          </q-card-section>
          <q-card-actions align="right">
            <q-btn flat :label="t('weeklyPlans.linkDialog.cancel')" v-close-popup />
            <q-btn flat :label="t('weeklyPlans.linkDialog.insert')" color="primary" @click="insertLink" />
          </q-card-actions>
        </q-card>
      </q-dialog>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import { useQuasar } from 'quasar';
import { useI18n } from 'vue-i18n';

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
const { t } = useI18n();

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
    $q.notify({ type: 'positive', message: t('weeklyPlans.notifications.copied') });
  } catch (e) {
    $q.notify({ type: 'negative', message: t('weeklyPlans.notifications.copyFailed') });
  }
};

const pasteField = async (field) => {
  try {
    const text = await navigator.clipboard.readText();
    planData.value[field] = text;
  } catch (e) {
    $q.notify({ type: 'negative', message: t('weeklyPlans.notifications.pasteFailed') });
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
    $q.notify({ type: 'warning', message: t('weeklyPlans.notifications.enterUrl') });
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