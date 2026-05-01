<template>
  <q-dialog v-model="isOpen" maximized transition-show="slide-up" transition-hide="slide-down">
    <q-card class="settings-dialog">
      <q-card-section class="row items-center q-pb-none">
        <div class="text-h6">Settings</div>
        <q-space />
        <q-btn icon="close" flat round dense v-close-popup />
      </q-card-section>

      <q-separator />

      <q-tabs
        v-model="activeTab"
        dense
        class="text-grey-8"
        active-color="primary"
        indicator-color="primary"
        align="left"
        inline-label
      >
        <q-tab name="general" icon="tune" label="General" />
        <q-tab name="header" icon="view_headline" label="Header" />
        <q-tab name="footer" icon="horizontal_rule" label="Footer" />
        <q-tab name="numbering" icon="format_list_numbered" label="Numbering" />
        <q-tab name="mcq" icon="grid_view" label="MCQ" />
      </q-tabs>
      <q-separator />

      <q-card-section class="q-pt-none">
        <q-tab-panels v-model="activeTab" animated>
          <!-- General Tab -->
          <q-tab-panel name="general">
            <div class="options-grid">
              <q-toggle
                v-model="localPageOptions.examTitle.enabled"
                label="Show exam title"
                @update:model-value="emitUpdate"
              />
              <q-input
                v-if="localPageOptions.examTitle.enabled"
                dense
                outlined
                v-model="localPageOptions.examTitle.text"
                label="Exam title"
                @dblclick="editTitleInline"
                @blur="emitUpdate"
              />

              <q-toggle
                v-model="localPageOptions.showMarksPerQuestion"
                label="Show marks for every question"
                @update:model-value="emitUpdate"
              />

              <q-toggle
                v-model="localPageOptions.showExplanationUnderQuestion"
                label="Show explanation under each question"
                @update:model-value="emitUpdate"
              />

              <q-toggle
                v-model="localPageOptions.showCorrectAnswerUnderQuestion"
                label="Show correct answer under each question"
                @update:model-value="emitUpdate"
              />

              <q-select
                dense
                outlined
                :options="[
                  { label: 'Strict (recommended for final exams)', value: 'strict' },
                  { label: 'Flex (tighter packing for drafts/worksheets)', value: 'flex' }
                ]"
                emit-value
                map-options
                v-model="localPageOptions.paginationMode"
                label="Pagination mode"
                hint="Strict enforces conservative page-break rules; Flex allows tighter layout."
                @update:model-value="emitUpdate"
              />

              <q-toggle
                v-model="localPageOptions.questionSeparator.enabled"
                label="Line after each question"
                @update:model-value="emitUpdate"
              />

              <q-select
                v-if="localPageOptions.questionSeparator.enabled"
                dense
                outlined
                :options="[
                  { label: 'Solid', value: 'solid' },
                  { label: 'Dashed', value: 'dashed' },
                  { label: 'Dotted', value: 'dotted' }
                ]"
                emit-value
                map-options
                v-model="localPageOptions.questionSeparator.lineStyle"
                label="Line type"
                @update:model-value="emitUpdate"
              />

              <q-input
                v-if="localPageOptions.questionSeparator.enabled"
                dense
                outlined
                type="color"
                v-model="localPageOptions.questionSeparator.color"
                label="Line color"
                @blur="emitUpdate"
              />

              <q-input
                v-if="localPageOptions.questionSeparator.enabled"
                dense
                outlined
                type="number"
                v-model.number="localPageOptions.questionSeparator.thicknessPt"
                label="Line thickness (pt)"
                @blur="emitUpdate"
              />

              <q-input
                v-if="localPageOptions.questionSeparator.enabled"
                dense
                outlined
                type="number"
                v-model.number="localPageOptions.questionSeparator.spaceBeforePt"
                label="Space before (pt)"
                @blur="emitUpdate"
              />

              <q-input
                v-if="localPageOptions.questionSeparator.enabled"
                dense
                outlined
                type="number"
                v-model.number="localPageOptions.questionSeparator.spaceAfterPt"
                label="Space after (pt)"
                @blur="emitUpdate"
              />
            </div>
          </q-tab-panel>

          <!-- Header Tab -->
          <q-tab-panel name="header">
            <div class="options-grid">
              <q-toggle
                v-model="localPageOptions.printHeader.enabled"
                label="Repeat header on every printed page"
                @update:model-value="emitUpdate"
              />

              <q-toggle
                v-if="localPageOptions.printHeader.enabled"
                v-model="localPageOptions.printHeader.autoFit"
                label="Auto-fit header height (recommended)"
                @update:model-value="emitUpdate"
              />

              <q-select
                v-if="localPageOptions.printHeader.enabled"
                dense
                outlined
                :options="[
                  { label: 'HTML', value: 'html' },
                  { label: 'Image', value: 'image' }
                ]"
                emit-value
                map-options
                v-model="localPageOptions.printHeader.mode"
                label="Header type"
                @update:model-value="emitUpdate"
              />

              <q-input
                v-if="localPageOptions.printHeader.enabled"
                dense
                outlined
                type="number"
                v-model.number="localPageOptions.printHeader.heightPt"
                label="Header height (pt)"
                @blur="emitUpdate"
              />

              <q-input
                v-if="localPageOptions.printHeader.enabled"
                dense
                outlined
                type="number"
                min="0"
                v-model.number="localPageOptions.printHeader.pageMarginTopMm"
                label="Extra top margin (mm)"
                @blur="emitUpdate"
              />

              <q-input
                v-if="localPageOptions.printHeader.enabled && localPageOptions.printHeader.mode === 'image'"
                dense
                outlined
                v-model="localPageOptions.printHeader.imageUrl"
                label="Header image URL"
                @blur="emitUpdate"
              />

              <q-select
                v-if="localPageOptions.printHeader.enabled && localPageOptions.printHeader.mode === 'image'"
                dense
                outlined
                :options="[
                  { label: 'Contain', value: 'contain' },
                  { label: 'Cover', value: 'cover' }
                ]"
                emit-value
                map-options
                v-model="localPageOptions.printHeader.imageFit"
                label="Image fit"
                @update:model-value="emitUpdate"
              />

              <q-input
                v-if="localPageOptions.printHeader.enabled && localPageOptions.printHeader.mode !== 'image'"
                dense
                outlined
                type="textarea"
                v-model="localPageOptions.printHeader.html"
                label="Header HTML"
                rows="6"
                @blur="emitUpdate"
              />
            </div>
          </q-tab-panel>

          <!-- Footer Tab -->
          <q-tab-panel name="footer">
            <div class="options-grid">
              <q-toggle
                v-model="localPageOptions.printFooter.enabled"
                label="Repeat footer on every printed page"
                @update:model-value="emitUpdate"
              />

              <q-toggle
                v-if="localPageOptions.printFooter.enabled"
                v-model="localPageOptions.printFooter.reserveSpace"
                label="Reserve space for footer"
                @update:model-value="emitUpdate"
              />

              <q-toggle
                v-if="localPageOptions.printFooter.enabled"
                v-model="localPageOptions.printFooter.showPageNumbers"
                label="Show page numbers"
                @update:model-value="emitUpdate"
              />

              <q-select
                v-if="localPageOptions.printFooter.enabled && localPageOptions.printFooter.showPageNumbers"
                dense
                outlined
                :options="[
                  { label: 'bottom-center', value: 'bottom-center' },
                  { label: 'bottom-left', value: 'bottom-left' },
                  { label: 'bottom-right', value: 'bottom-right' }
                ]"
                emit-value
                map-options
                v-model="localPageOptions.printFooter.pageNumberPosition"
                label="Page number position"
                @update:model-value="emitUpdate"
              />

              <q-input
                v-if="localPageOptions.printFooter.enabled && localPageOptions.printFooter.showPageNumbers"
                dense
                outlined
                v-model="localPageOptions.printFooter.pageNumberFormat"
                label="Page number format"
                @blur="emitUpdate"
              />

              <q-input
                v-if="localPageOptions.printFooter.enabled && localPageOptions.printFooter.showPageNumbers"
                dense
                outlined
                type="number"
                v-model.number="localPageOptions.printFooter.pageNumberFontSize"
                label="Page number font size"
                @blur="emitUpdate"
              />

              <q-input
                v-if="localPageOptions.printFooter.enabled && localPageOptions.printFooter.showPageNumbers"
                dense
                outlined
                type="color"
                v-model="localPageOptions.printFooter.pageNumberColor"
                label="Page number color"
                @blur="emitUpdate"
              />

              <q-toggle
                v-if="localPageOptions.printFooter.enabled"
                v-model="localPageOptions.printFooter.singleLine"
                label="Single-line footer"
                @update:model-value="emitUpdate"
              />

              <q-toggle
                v-if="localPageOptions.printFooter.enabled"
                v-model="localPageOptions.printFooter.autoFit"
                label="Auto-fit footer height"
                @update:model-value="emitUpdate"
              />

              <q-input
                v-if="localPageOptions.printFooter.enabled"
                dense
                outlined
                type="number"
                v-model.number="localPageOptions.printFooter.heightPt"
                label="Footer height (pt)"
                @blur="emitUpdate"
              />

              <q-input
                v-if="localPageOptions.printFooter.enabled"
                dense
                outlined
                type="number"
                min="0"
                v-model.number="localPageOptions.printFooter.pageMarginBottomMm"
                label="Extra bottom margin (mm)"
                @blur="emitUpdate"
              />
            </div>
          </q-tab-panel>

          <!-- Numbering Tab -->
          <q-tab-panel name="numbering">
            <div class="options-grid">
              <q-select
                dense
                outlined
                :options="[
                  { label: 'Question', value: 'question' },
                  { label: 'Number', value: 'number' }
                ]"
                emit-value
                map-options
                v-model="localPageOptions.questionNumbering.style"
                label="Numbering style"
                @update:model-value="emitUpdate"
              />

              <q-input
                dense
                outlined
                type="number"
                v-model.number="localPageOptions.questionNumbering.startAt"
                label="Start numbering at"
                @blur="emitUpdate"
              />

              <q-toggle
                v-model="localPageOptions.questionNumbering.inlineWithText"
                label="Inline numbering with text"
                @update:model-value="emitUpdate"
              />

              <q-input
                v-if="localPageOptions.questionNumbering.inlineWithText"
                dense
                outlined
                type="number"
                v-model.number="localPageOptions.questionNumbering.inlineGap"
                label="Inline gap (pt)"
                @blur="emitUpdate"
              />
            </div>
          </q-tab-panel>

          <!-- MCQ Tab -->
          <q-tab-panel name="mcq">
            <div class="options-grid">
              <q-select
                dense
                outlined
                :options="[
                  { label: '1 column', value: 1 },
                  { label: '2 columns', value: 2 },
                  { label: '3 columns', value: 3 }
                ]"
                emit-value
                map-options
                v-model="localPageOptions.mcqOptions.columns"
                label="MCQ columns"
                @update:model-value="emitUpdate"
              />

              <q-input
                dense
                outlined
                type="number"
                v-model.number="localPageOptions.mcqOptions.optionGapPt"
                label="Option gap (pt)"
                @blur="emitUpdate"
              />

              <q-input
                dense
                outlined
                type="number"
                v-model.number="localPageOptions.mcqOptions.labelGapPt"
                label="Label gap (pt)"
                @blur="emitUpdate"
              />

              <q-select
                dense
                outlined
                :options="[
                  { label: 'Letter (A, B, C)', value: 'letter' },
                  { label: 'Number (1, 2, 3)', value: 'number' }
                ]"
                emit-value
                map-options
                v-model="localPageOptions.mcqOptions.labelStyle"
                label="Label style"
                @update:model-value="emitUpdate"
              />

              <q-select
                dense
                outlined
                :options="[
                  { label: 'Box', value: 'box' },
                  { label: 'Circle', value: 'circle' }
                ]"
                emit-value
                map-options
                v-model="localPageOptions.mcqOptions.checkboxStyle"
                label="Checkbox style"
                @update:model-value="emitUpdate"
              />

              <q-toggle
                v-model="localPageOptions.mcqOptions.labelBold"
                label="Bold labels"
                @update:model-value="emitUpdate"
              />

              <q-toggle
                v-model="localPageOptions.mcqOptions.optionBold"
                label="Bold options"
                @update:model-value="emitUpdate"
              />

              <q-select
                dense
                outlined
                :options="[
                  { label: 'Vertical', value: 'vertical' },
                  { label: 'Horizontal', value: 'horizontal' }
                ]"
                emit-value
                map-options
                v-model="localPageOptions.mcqOptions.layout"
                label="Layout"
                @update:model-value="emitUpdate"
              />
            </div>
          </q-tab-panel>
        </q-tab-panels>
      </q-card-section>

      <q-card-actions align="right" class="q-pa-md">
        <q-btn
          color="primary"
          icon="save"
          label="Save Settings"
          @click="saveAndClose"
        />
        <q-btn icon="close" flat round dense v-close-popup />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useQuasar } from 'quasar'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  pageOptions: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['update:modelValue', 'update:pageOptions', 'save'])

const $q = useQuasar()
const isOpen = ref(props.modelValue)
const activeTab = ref('general')
const localPageOptions = ref({})

// Initialize local copy of pageOptions
watch(() => props.pageOptions, (newVal) => {
  localPageOptions.value = JSON.parse(JSON.stringify(newVal))
}, { immediate: true, deep: true })

watch(() => props.modelValue, (newVal) => {
  isOpen.value = newVal
})

watch(isOpen, (newVal) => {
  emit('update:modelValue', newVal)
})

function emitUpdate() {
  emit('update:pageOptions', JSON.parse(JSON.stringify(localPageOptions.value)))
}

function saveAndClose() {
  emitUpdate()
  emit('save')
  isOpen.value = false
}

function editTitleInline() {
  $q.dialog({
    title: 'Edit Exam Title',
    message: 'Enter the exam title:',
    prompt: {
      model: localPageOptions.value.examTitle.text,
      type: 'text'
    },
    cancel: true,
    persistent: true
  }).onOk(data => {
    localPageOptions.value.examTitle.text = data
    emitUpdate()
  })
}
</script>

<style scoped>
.settings-dialog {
  max-width: 800px;
}

.options-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 12px;
}
</style>
