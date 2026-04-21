<template>
  <div class="exam-test-page">
    <!-- Modern sticky toolbar -->
    <div class="modern-toolbar">
      <div class="toolbar-left">
        <div class="toolbar-title">
          <q-icon name="quiz" size="24px" color="primary" />
          <span v-if="pageOptions.examTitle?.enabled" class="title-text">{{ pageOptions.examTitle?.text }}</span>
          <span v-else class="title-text">Exam Builder</span>
        </div>
      </div>
      
      <div class="toolbar-right">
        <!-- Print Actions -->
        <div class="toolbar-group">
          <PrintActions
            :generate-print-html="generatePrintHTML"
            :extra-margin-mm="pageOptions.printHeader.pageMarginTopMm ?? 0"
            @update:extra-margin-mm="(v) => { pageOptions.printHeader.pageMarginTopMm = v; savePageState() }"
          />
        </div>

        <!-- Import/Export Group -->
        <div class="toolbar-group">
          <q-btn-dropdown
            flat
            color="white"
            icon="more_vert"
            dropdown-icon=""
          >
            <q-list style="min-width: 220px">
              <q-item clickable v-close-popup @click="openAIDialog">
                <q-item-section avatar>
                  <q-icon name="smart_toy" />
                </q-item-section>
                <q-item-section>Import AI Questions</q-item-section>
              </q-item>

              <q-item clickable v-close-popup @click="triggerImportFile">
                <q-item-section avatar>
                  <q-icon name="upload_file" />
                </q-item-section>
                <q-item-section>Import JSON</q-item-section>
              </q-item>

              <q-item clickable v-close-popup @click="exportToJson">
                <q-item-section avatar>
                  <q-icon name="download" />
                </q-item-section>
                <q-item-section>Export JSON</q-item-section>
              </q-item>

              <q-separator />

              <q-item clickable v-close-popup @click="firstLastPageOpen = true">
                <q-item-section avatar>
                  <q-icon name="auto_stories" />
                </q-item-section>
                <q-item-section>First / Last Page</q-item-section>
              </q-item>

              <q-item clickable v-close-popup @click="optionsOpen = true">
                <q-item-section avatar>
                  <q-icon name="tune" />
                </q-item-section>
                <q-item-section>Settings</q-item-section>
              </q-item>
            </q-list>
          </q-btn-dropdown>
        </div>
      </div>
    </div>

    <input
      ref="importFileInput"
      type="file"
      accept="application/json,.json"
      style="display: none"
      @change="handleImportFile"
    />

    <input
      ref="headerImageFileInput"
      type="file"
      accept="image/*"
      style="display: none"
      @change="handleHeaderImageFile"
    />

    <input
      ref="footerImageFileInput"
      type="file"
      accept="image/*"
      style="display: none"
      @change="handleFooterImageFile"
    />

    <div class="sections-summary" v-if="sections.length">
      <div class="section-chip" v-for="s in sections" :key="s.id">
        <span class="section-chip-title">{{ s.title }}</span>
        <SectionTotalMark
          :total="sectionTotalMarks(s.id)"
          :options="pageOptions.sectionTotal"
        />
      </div>
    </div>

    <!-- Questions display -->
    <div class="questions-container">
      <div
        v-for="(question, index) in printSequence"
        :key="question.id"
        class="question-row"
      >
        <div v-if="isPageBreakBefore(question)" class="page-break-marker">
          <div class="page-break-marker-line"></div>
          <div class="page-break-marker-label">PAGE BREAK</div>
          <div class="page-break-marker-line"></div>
        </div>

        <div class="question-row-actions">
          <q-select
            dense
            outlined
            emit-value
            map-options
            :options="sectionOptions"
            :model-value="getQuestionSectionId(question)"
            @update:model-value="(val) => setQuestionSection(question, val)"
            style="min-width: 180px"
            class="q-mr-sm"
          />

          <q-btn
            dense
            flat
            round
            icon="vertical_align_bottom"
            color="grey-8"
            :title="isPageBreakBefore(question) ? 'Remove page break before' : 'Add page break before'"
            @click="togglePageBreakBefore(question)"
          />

          <q-btn
            dense
            flat
            round
            icon="vertical_align_top"
            color="grey-8"
            :title="'Add page break after'"
            @click="togglePageBreakAfter(question)"
          />

          <q-btn
            dense
            flat
            round
            icon="image"
            color="grey-8"
            :title="question?.content?.image ? 'Edit question image' : 'Add question image'"
            @click="openImageDialog(question)"
          />

          <q-btn
            dense
            flat
            round
            icon="delete"
            color="negative"
            @click="deleteQuestion(question.id)"
          />
        </div>
        <QuestionDisplay
          :question="question"
          :question-number="index + 1"
          :numbering-options="pageOptions.questionNumbering"
          :mcq-options="pageOptions.mcqOptions"
          :show-answer-area="true"
          :answer-lines-override="3"
          :show-marks="pageOptions.showMarksPerQuestion"
        />
        <div
          v-if="pageOptions.questionSeparator?.enabled"
          class="question-separator"
          :style="questionSeparatorStyle"
        ></div>
      </div>
    </div>

    <!-- Settings Dialog -->
    <q-dialog v-model="optionsOpen">
      <q-card style="min-width: 820px; max-width: 95vw;">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">Settings</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>
        <q-separator />

        <q-tabs
          v-model="settingsTab"
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
          <q-tab name="sections" icon="category" label="Sections" />
          <q-tab name="sectionTotal" icon="summarize" label="Section Total" />
          <q-tab name="mcq" icon="grid_view" label="MCQ" />
        </q-tabs>
        <q-separator />

        <q-tab-panels v-model="settingsTab" animated>
          <q-tab-panel name="general">
            <div class="options-grid">
              <q-toggle
                v-model="pageOptions.examTitle.enabled"
                label="Show exam title"
                @update:model-value="savePageState"
              />

              <q-input
                v-if="pageOptions.examTitle.enabled"
                dense
                outlined
                v-model="pageOptions.examTitle.text"
                label="Exam title"
                @blur="savePageState"
              />

              <q-toggle
                v-model="pageOptions.showMarksPerQuestion"
                label="Show marks for every question"
                @update:model-value="savePageState"
              />

              <q-toggle
                v-model="pageOptions.questionSeparator.enabled"
                label="Line after each question"
                @update:model-value="savePageState"
              />

              <q-select
                v-if="pageOptions.questionSeparator.enabled"
                dense
                outlined
                :options="[
                  { label: 'Solid', value: 'solid' },
                  { label: 'Dashed', value: 'dashed' },
                  { label: 'Dotted', value: 'dotted' }
                ]"
                emit-value
                map-options
                v-model="pageOptions.questionSeparator.lineStyle"
                label="Line type"
                @update:model-value="savePageState"
              />

              <q-input
                v-if="pageOptions.questionSeparator.enabled"
                dense
                outlined
                type="color"
                v-model="pageOptions.questionSeparator.color"
                label="Line color"
                @blur="savePageState"
              />

              <q-input
                v-if="pageOptions.questionSeparator.enabled"
                dense
                outlined
                type="number"
                v-model.number="pageOptions.questionSeparator.thicknessPt"
                label="Line thickness (pt)"
                @blur="savePageState"
              />

              <q-input
                v-if="pageOptions.questionSeparator.enabled"
                dense
                outlined
                type="number"
                v-model.number="pageOptions.questionSeparator.spaceBeforePt"
                label="Space before (pt)"
                @blur="savePageState"
              />

              <q-input
                v-if="pageOptions.questionSeparator.enabled"
                dense
                outlined
                type="number"
                v-model.number="pageOptions.questionSeparator.spaceAfterPt"
                label="Space after (pt)"
                @blur="savePageState"
              />
            </div>
          </q-tab-panel>

          <q-tab-panel name="header">
            <div class="options-grid">
              <q-toggle
                v-model="pageOptions.printHeader.enabled"
                label="Repeat header on every printed page"
                @update:model-value="savePageState"
              />

              <q-toggle
                v-if="pageOptions.printHeader.enabled"
                v-model="pageOptions.printHeader.autoFit"
                label="Auto-fit header height (recommended)"
                @update:model-value="savePageState"
              />

              <q-select
                v-if="pageOptions.printHeader.enabled"
                dense
                outlined
                :options="[
                  { label: 'HTML', value: 'html' },
                  { label: 'Image', value: 'image' }
                ]"
                emit-value
                map-options
                v-model="pageOptions.printHeader.mode"
                label="Header type"
                @update:model-value="savePageState"
              />

              <q-select
                v-if="pageOptions.printHeader.enabled && pageOptions.printHeader.mode !== 'image'"
                dense
                outlined
                :options="headerTemplateOptions"
                emit-value
                map-options
                v-model="pageOptions.printHeader.templateId"
                label="Header template"
                @update:model-value="applyHeaderTemplate"
              />

              <q-input
                v-if="pageOptions.printHeader.enabled"
                dense
                outlined
                type="number"
                v-model.number="pageOptions.printHeader.heightPt"
                label="Header height (pt) — used when Auto-fit is off"
                @blur="savePageState"
              />

              <q-input
                v-if="pageOptions.printHeader.enabled"
                dense
                outlined
                type="number"
                min="0"
                v-model.number="pageOptions.printHeader.pageMarginTopMm"
                label="Extra top margin (mm) — space between header and content"
                hint="0 = automatic. Increase to push content further down."
                @blur="savePageState"
              />

              <div v-if="pageOptions.printHeader.enabled && pageOptions.printHeader.mode === 'image'" class="row items-center q-col-gutter-sm">
                <div class="col-auto">
                  <q-btn
                    color="primary"
                    icon="image"
                    label="Choose Image"
                    @click="triggerHeaderImageFile"
                  />
                </div>
                <div class="col-auto">
                  <q-btn
                    flat
                    color="secondary"
                    icon="content_paste"
                    label="Paste Image"
                    @click="pasteHeaderImageFromClipboard"
                  />
                </div>
                <div class="col-auto">
                  <q-btn
                    flat
                    color="secondary"
                    icon="content_paste"
                    label="Paste URL"
                    @click="pasteHeaderImageUrlFromClipboard"
                  />
                </div>
                <div class="col-auto">
                  <q-btn
                    v-if="pageOptions.printHeader.imageUrl"
                    flat
                    color="negative"
                    icon="delete"
                    label="Remove"
                    @click="removeHeaderImage"
                  />
                </div>
              </div>

              <q-input
                v-if="pageOptions.printHeader.enabled && pageOptions.printHeader.mode === 'image'"
                dense
                outlined
                v-model="pageOptions.printHeader.imageUrl"
                label="Header image URL / Data URL"
                @blur="savePageState"
              />

              <q-select
                v-if="pageOptions.printHeader.enabled && pageOptions.printHeader.mode === 'image'"
                dense
                outlined
                :options="[
                  { label: 'Contain (fit inside)', value: 'contain' },
                  { label: 'Cover (fill)', value: 'cover' }
                ]"
                emit-value
                map-options
                v-model="pageOptions.printHeader.imageFit"
                label="Image fit"
                @update:model-value="savePageState"
              />

              <q-input
                v-if="pageOptions.printHeader.enabled && pageOptions.printHeader.mode !== 'image'"
                outlined
                type="textarea"
                v-model="pageOptions.printHeader.html"
                label="Header HTML (paste here)"
                rows="10"
                @blur="savePageState"
              />

              <div v-if="pageOptions.printHeader.enabled && pageOptions.printHeader.mode !== 'image'" class="row items-center q-col-gutter-sm">
                <div class="col-auto">
                  <q-btn
                    flat
                    color="secondary"
                    icon="content_paste"
                    label="Paste HTML"
                    @click="pasteHeaderHtmlFromClipboard"
                  />
                </div>
              </div>

              <q-card
                v-if="pageOptions.printHeader.enabled && pageOptions.printHeader.mode === 'html' && pageOptions.printHeader.templateId === 'exam_header_v1'"
                flat
                bordered
                class="q-mt-md"
                style="width: 100%;"
              >
                <q-card-section class="text-subtitle2">Exam Header V1 Editor</q-card-section>
                <q-separator />
                <q-card-section>
                  <div class="options-grid">
                    <q-input
                      dense
                      outlined
                      v-model="pageOptions.printHeader.template1.schoolName"
                      label="School Name"
                      @blur="saveHeaderTemplate1"
                    />

                    <q-input
                      dense
                      outlined
                      v-model="pageOptions.printHeader.template1.period"
                      label="Academic Period"
                      @blur="saveHeaderTemplate1"
                    />

                    <q-input
                      dense
                      outlined
                      v-model="pageOptions.printHeader.template1.grade"
                      label="Grade"
                      @blur="saveHeaderTemplate1"
                    />

                    <q-input
                      dense
                      outlined
                      v-model="pageOptions.printHeader.template1.subject"
                      label="Subject"
                      @blur="saveHeaderTemplate1"
                    />

                    <q-input
                      dense
                      outlined
                      v-model="pageOptions.printHeader.template1.examType"
                      label="Exam Type"
                      @blur="saveHeaderTemplate1"
                    />

                    <q-select
                      dense
                      outlined
                      :options="[
                        { label: 'Boys', value: 'Boys' },
                        { label: 'Girls', value: 'Girls' },
                        { label: 'Both (unchecked)', value: 'Both' }
                      ]"
                      emit-value
                      map-options
                      v-model="pageOptions.printHeader.template1.gender"
                      label="Target Gender"
                      @update:model-value="saveHeaderTemplate1"
                    />
                  </div>

                  <div class="row justify-end q-mt-sm">
                    <q-btn
                      color="primary"
                      icon="refresh"
                      label="Regenerate Header"
                      @click="applyHeaderTemplate"
                    />
                  </div>
                </q-card-section>
              </q-card>
            </div>
          </q-tab-panel>

          <q-tab-panel name="footer">
            <div class="options-grid">
              <q-toggle
                v-model="pageOptions.printFooter.enabled"
                label="Repeat footer on every printed page"
                @update:model-value="savePageState"
              />

              <q-toggle
                v-if="pageOptions.printFooter.enabled"
                v-model="pageOptions.printFooter.reserveSpace"
                label="Reserve space for footer (recommended)"
                @update:model-value="savePageState"
              />

              <q-toggle
                v-if="pageOptions.printFooter.enabled"
                v-model="pageOptions.printFooter.singleLine"
                label="Single-line footer (content + page number)"
                @update:model-value="savePageState"
              />

              <q-toggle
                v-if="pageOptions.printFooter.enabled"
                v-model="pageOptions.printFooter.showTopBorder"
                label="Show a line above footer"
                @update:model-value="savePageState"
              />

              <q-toggle
                v-if="pageOptions.printFooter.enabled"
                v-model="pageOptions.printFooter.autoFit"
                label="Auto-fit footer height (recommended)"
                @update:model-value="savePageState"
              />

              <q-select
                v-if="pageOptions.printFooter.enabled"
                dense
                outlined
                :options="[
                  { label: 'HTML', value: 'html' },
                  { label: 'Image', value: 'image' }
                ]"
                emit-value
                map-options
                v-model="pageOptions.printFooter.mode"
                label="Footer type"
                @update:model-value="savePageState"
              />

              <q-input
                v-if="pageOptions.printFooter.enabled"
                dense
                outlined
                type="number"
                v-model.number="pageOptions.printFooter.heightPt"
                label="Footer height (pt) — used when Auto-fit is off"
                @blur="savePageState"
              />

              <q-input
                v-if="pageOptions.printFooter.enabled"
                dense
                outlined
                type="number"
                min="0"
                v-model.number="pageOptions.printFooter.pageMarginBottomMm"
                label="Extra bottom margin (mm) — space between content and footer"
                hint="0 = automatic. Increase to push content further up."
                @blur="savePageState"
              />

              <!-- Page Number Options -->
              <q-separator v-if="pageOptions.printFooter.enabled" class="q-my-md" />
              
              <q-toggle
                v-if="pageOptions.printFooter.enabled"
                v-model="pageOptions.printFooter.showPageNumbers"
                label="Show page numbers in footer"
                @update:model-value="savePageState"
              />

              <div v-if="pageOptions.printFooter.enabled && pageOptions.printFooter.showPageNumbers" class="row items-center q-col-gutter-sm">
                <div class="col-12 col-md-6">
                  <q-select
                    dense
                    outlined
                    :options="[
                      { label: 'Bottom Left', value: 'bottom-left' },
                      { label: 'Bottom Center', value: 'bottom-center' },
                      { label: 'Bottom Right', value: 'bottom-right' },
                      { label: 'Top Left', value: 'top-left' },
                      { label: 'Top Center', value: 'top-center' },
                      { label: 'Top Right', value: 'top-right' }
                    ]"
                    emit-value
                    map-options
                    v-model="pageOptions.printFooter.pageNumberPosition"
                    label="Page number position"
                    @update:model-value="savePageState"
                  />
                </div>
                <div class="col-12 col-md-6">
                  <q-select
                    dense
                    outlined
                    :options="[
                      { label: 'Page 1', value: 'page' },
                      { label: '1 of 5', value: 'page-of' },
                      { label: 'Page 1 / 5', value: 'page-slash' },
                      { label: '1/5', value: 'fraction' }
                    ]"
                    emit-value
                    map-options
                    v-model="pageOptions.printFooter.pageNumberFormat"
                    label="Page number format"
                    @update:model-value="savePageState"
                  />
                </div>
              </div>

              <div v-if="pageOptions.printFooter.enabled && pageOptions.printFooter.showPageNumbers" class="row items-center q-col-gutter-sm">
                <div class="col-12 col-md-6">
                  <q-input
                    dense
                    outlined
                    type="number"
                    v-model.number="pageOptions.printFooter.pageNumberFontSize"
                    label="Font size (pt)"
                    @blur="savePageState"
                  />
                </div>
                <div class="col-12 col-md-6">
                  <q-select
                    dense
                    outlined
                    :options="[
                      { label: 'Black', value: '#000000' },
                      { label: 'Dark Gray', value: '#333333' },
                      { label: 'Gray', value: '#666666' },
                      { label: 'Light Gray', value: '#999999' }
                    ]"
                    emit-value
                    map-options
                    v-model="pageOptions.printFooter.pageNumberColor"
                    label="Color"
                    @update:model-value="savePageState"
                  />
                </div>
              </div>

              <div v-if="pageOptions.printFooter.enabled && pageOptions.printFooter.mode === 'image'" class="row items-center q-col-gutter-sm">
                <div class="col-auto">
                  <q-btn
                    color="primary"
                    icon="image"
                    label="Choose Image"
                    @click="triggerFooterImageFile"
                  />
                </div>
                <div class="col-auto">
                  <q-btn
                    flat
                    color="primary"
                    icon="content_paste"
                    label="Paste Image"
                    @click="pasteFooterImage"
                  />
                </div>
                <div class="col-auto">
                  <q-btn
                    flat
                    color="primary"
                    icon="link"
                    label="Paste URL"
                    @click="pasteFooterImageUrl"
                  />
                </div>
                <div class="col-auto">
                  <q-btn
                    v-if="pageOptions.printFooter.imageUrl"
                    flat
                    color="negative"
                    icon="delete"
                    label="Remove"
                    @click="removeFooterImage"
                  />
                </div>
              </div>

              <q-input
                v-if="pageOptions.printFooter.enabled && pageOptions.printFooter.mode === 'image'"
                dense
                outlined
                v-model="pageOptions.printFooter.imageUrl"
                label="Footer image URL / Data URL"
                @blur="savePageState"
              />

              <q-select
                v-if="pageOptions.printFooter.enabled && pageOptions.printFooter.mode === 'image'"
                dense
                outlined
                :options="[
                  { label: 'Contain', value: 'contain' },
                  { label: 'Cover', value: 'cover' },
                  { label: 'Fill', value: 'fill' }
                ]"
                emit-value
                map-options
                v-model="pageOptions.printFooter.imageFit"
                label="Image fit"
                @update:model-value="savePageState"
              />

              <q-input
                v-if="pageOptions.printFooter.enabled && pageOptions.printFooter.mode !== 'image'"
                outlined
                type="textarea"
                v-model="pageOptions.printFooter.html"
                label="Footer HTML (paste here)"
                rows="8"
                @blur="savePageState"
              />

              <div v-if="pageOptions.printFooter.enabled && pageOptions.printFooter.mode !== 'image'" class="row items-center q-col-gutter-sm">
                <div class="col-12 col-md-6">
                  <q-input
                    dense
                    outlined
                    type="number"
                    v-model.number="pageOptions.printFooter.textFontSizePt"
                    label="Footer text font size (pt)"
                    @blur="savePageState"
                  />
                </div>
                <div class="col-12 col-md-6">
                  <q-select
                    dense
                    outlined
                    :options="[
                      { label: 'Black', value: '#000000' },
                      { label: 'Dark Gray', value: '#333333' },
                      { label: 'Gray', value: '#666666' },
                      { label: 'Light Gray', value: '#999999' }
                    ]"
                    emit-value
                    map-options
                    v-model="pageOptions.printFooter.textColor"
                    label="Footer text color"
                    @update:model-value="savePageState"
                  />
                </div>
              </div>

              <div v-if="pageOptions.printFooter.enabled && pageOptions.printFooter.mode !== 'image'" class="row items-center q-col-gutter-sm">
                <div class="col-auto">
                  <q-btn
                    flat
                    color="primary"
                    icon="content_paste"
                    label="Paste HTML"
                    @click="pasteFooterHtml"
                  />
                </div>
              </div>
            </div>
          </q-tab-panel>

          <q-tab-panel name="numbering">
            <div class="options-grid">
              <q-select
                dense
                outlined
                :options="questionNumberingStyles"
                emit-value
                map-options
                v-model="pageOptions.questionNumbering.style"
                label="Question number style"
                @update:model-value="savePageState"
              />

              <q-input
                dense
                outlined
                type="number"
                v-model.number="pageOptions.questionNumbering.startAt"
                label="Start at"
                @blur="savePageState"
              />

              <q-input
                dense
                outlined
                v-model="pageOptions.questionNumbering.prefix"
                label="Prefix (optional)"
                @blur="savePageState"
              />

              <q-input
                dense
                outlined
                v-model="pageOptions.questionNumbering.suffix"
                label="Suffix (optional)"
                @blur="savePageState"
              />

              <q-input
                v-if="pageOptions.questionNumbering.style === 'custom'"
                dense
                outlined
                v-model="pageOptions.questionNumbering.customTemplate"
                label="Custom template (use {n} and {letter})"
                @blur="savePageState"
              />

              <q-toggle
                v-model="pageOptions.questionNumbering.inlineWithText"
                label="Number inline with question text"
                @update:model-value="savePageState"
              />

              <q-input
                v-if="pageOptions.questionNumbering.inlineWithText"
                dense
                outlined
                type="number"
                v-model.number="pageOptions.questionNumbering.inlineGap"
                label="Inline gap (pt)"
                @blur="savePageState"
              />
            </div>
          </q-tab-panel>

          <q-tab-panel name="sections">
            <div class="text-subtitle1 q-mb-sm">Sections / Categories</div>
            <div class="text-caption q-mb-sm">Each section will show its total marks in print.</div>

            <div class="sections-editor">
              <div class="section-row" v-for="s in sections" :key="s.id">
                <q-input
                  dense
                  outlined
                  v-model="s.title"
                  label="Section title"
                  class="q-mr-sm"
                  @blur="savePageState"
                />
                <q-input
                  dense
                  outlined
                  v-model="s.instructions"
                  label="Section instructions (optional)"
                  class="q-mr-sm"
                  @blur="savePageState"
                />
                <div class="section-marks">{{ sectionTotalMarks(s.id) }} marks</div>
                <q-btn
                  dense
                  flat
                  round
                  icon="delete"
                  color="negative"
                  :disable="sections.length <= 1"
                  @click="removeSection(s.id)"
                />
              </div>
            </div>

            <q-btn
              color="primary"
              icon="add"
              label="Add Section"
              class="q-mt-sm"
              @click="addSection"
            />
          </q-tab-panel>

          <q-tab-panel name="sectionTotal">
            <div class="options-grid">
              <q-select
                dense
                outlined
                :options="sectionTotalTemplates"
                emit-value
                map-options
                v-model="pageOptions.sectionTotal.template"
                label="Section total template"
                @update:model-value="savePageState"
              />

              <q-select
                dense
                outlined
                :options="sectionTotalPlacements"
                emit-value
                map-options
                v-model="pageOptions.sectionTotal.placement"
                label="Section total placement"
                @update:model-value="savePageState"
              />

              <q-input
                v-if="pageOptions.sectionTotal.placement === 'fly_top_right'"
                dense
                outlined
                type="number"
                v-model.number="pageOptions.sectionTotal.offsetXPt"
                label="Total offset right (pt)"
                @blur="savePageState"
              />

              <q-input
                v-if="pageOptions.sectionTotal.placement === 'fly_top_right'"
                dense
                outlined
                type="number"
                v-model.number="pageOptions.sectionTotal.offsetYPt"
                label="Total offset top (pt)"
                @blur="savePageState"
              />

              <q-input
                v-if="pageOptions.sectionTotal.template === 'box'"
                dense
                outlined
                type="number"
                v-model.number="pageOptions.sectionTotal.boxTopHeightPt"
                label="Box top height (pt)"
                @blur="savePageState"
              />
            </div>
          </q-tab-panel>

          <q-tab-panel name="mcq">
            <div class="options-grid">
              <q-select
                dense
                outlined
                :options="mcqLabelStyles"
                emit-value
                map-options
                v-model="pageOptions.mcqOptions.labelStyle"
                label="MCQ option label style"
                @update:model-value="savePageState"
              />

              <q-toggle
                v-if="pageOptions.mcqOptions.labelStyle === 'checkbox'"
                v-model="pageOptions.mcqOptions.checkboxShowLabel"
                label="Show letter/number next to checkbox"
                @update:model-value="savePageState"
              />

              <q-select
                v-if="pageOptions.mcqOptions.labelStyle === 'checkbox' && pageOptions.mcqOptions.checkboxShowLabel"
                dense
                outlined
                :options="[
                  { label: 'Letters (A)', value: 'letter' },
                  { label: 'Numbers (1)', value: 'number' },
                  { label: 'Custom', value: 'custom' }
                ]"
                emit-value
                map-options
                v-model="pageOptions.mcqOptions.checkboxLabelType"
                label="Checkbox label type"
                @update:model-value="savePageState"
              />

              <q-input
                v-if="pageOptions.mcqOptions.labelStyle === 'custom' || (pageOptions.mcqOptions.labelStyle === 'checkbox' && pageOptions.mcqOptions.checkboxShowLabel && pageOptions.mcqOptions.checkboxLabelType === 'custom')"
                dense
                outlined
                v-model="pageOptions.mcqOptions.customLabelTemplate"
                label="MCQ label template (use {i}, {n}, {letter})"
                @blur="savePageState"
              />

              <q-input
                dense
                outlined
                type="number"
                v-model.number="pageOptions.mcqOptions.labelFontSizePt"
                label="Label font size (pt) (0 = default)"
                @blur="savePageState"
              />

              <q-toggle
                v-model="pageOptions.mcqOptions.labelBold"
                label="Label bold"
                @update:model-value="savePageState"
              />

              <q-input
                dense
                outlined
                type="number"
                v-model.number="pageOptions.mcqOptions.optionFontSizePt"
                label="Choice font size (pt) (0 = default)"
                @blur="savePageState"
              />

              <q-toggle
                v-model="pageOptions.mcqOptions.optionBold"
                label="Choice bold"
                @update:model-value="savePageState"
              />

              <q-input
                dense
                outlined
                type="number"
                v-model.number="pageOptions.mcqOptions.columns"
                label="MCQ choices per line (1 = each choice on its own line)"
                min="1"
                @blur="savePageState"
              />

              <q-select
                dense
                outlined
                :options="[
                  { label: '1 per line', value: 1 },
                  { label: '2 per line', value: 2 },
                  { label: '3 per line', value: 3 },
                  { label: '4 per line', value: 4 }
                ]"
                emit-value
                map-options
                :model-value="(Number(pageOptions.mcqOptions.columns) || 1)"
                label="Quick layout"
                @update:model-value="(v) => { pageOptions.mcqOptions.columns = v; savePageState() }"
              />

              <q-input
                dense
                outlined
                type="number"
                v-model.number="pageOptions.mcqOptions.optionGapPt"
                label="MCQ option gap (pt)"
                @blur="savePageState"
              />

              <q-input
                dense
                outlined
                type="number"
                v-model.number="pageOptions.mcqOptions.labelGapPt"
                label="MCQ label gap (pt)"
                @blur="savePageState"
              />
            </div>
          </q-tab-panel>
        </q-tab-panels>

        <q-separator />
        <q-card-actions align="right">
          <q-btn flat label="Close" color="primary" v-close-popup />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- AI Import Dialog -->
    <q-dialog v-model="aiDialogOpen" maximized>
      <q-card class="ai-dialog">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">Import Questions from AI</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>
        <q-separator />
        
        <q-stepper v-model="step" vertical color="primary" animated header-nav>
          <!-- Step 1: Configure AI Prompt -->
          <q-step
            :name="1"
            title="Configure AI Prompt"
            icon="settings"
            :done="step > 1"
          >
            <div class="step-content">
              <div class="form-section">
                <q-input
                  v-model="aiConfig.topic"
                  label="Topic"
                  placeholder="e.g., Fractions, Algebra, Geometry"
                  outlined
                />
                
                <q-input
                  v-model="aiConfig.grade"
                  label="Grade Level"
                  placeholder="e.g., Grade 7, Grade 8"
                  outlined
                />
                
                <q-input
                  v-model.number="aiConfig.questionCount"
                  type="number"
                  label="Number of Questions"
                  min="1"
                  max="20"
                  outlined
                />
                
                <q-toggle
                  v-model="aiConfig.latexSupport"
                  label="Enable LaTeX/Math Expressions"
                />
                
                <q-toggle
                  v-model="aiConfig.htmlSupport"
                  label="Enable HTML Formatting"
                />
                
                <q-input
                  v-model="aiConfig.instructions"
                  type="textarea"
                  label="Additional Instructions (Optional)"
                  placeholder="e.g., Include step-by-step solutions, focus on real-world applications"
                  outlined
                  rows="3"
                />
              </div>
              
              <q-stepper-navigation>
                <q-btn @click="generatePrompt" color="primary" label="Generate Prompt" />
                <q-btn v-if="generatedPrompt" flat @click="copyPrompt" color="secondary" label="Copy to Clipboard" class="q-ml-sm" />
                <q-btn flat @click="step = 2" color="secondary" label="Next" class="q-ml-sm" />
              </q-stepper-navigation>
              
              <div v-if="generatedPrompt" class="prompt-preview">
                <div class="text-subtitle2 q-mt-md q-mb-sm">Generated Prompt:</div>
                <q-markdown :source="generatedPrompt" />
              </div>
            </div>
          </q-step>

          <!-- Step 2: Paste AI Response -->
          <q-step
            :name="2"
            title="Paste AI Response"
            icon="content_paste"
            >
            <!-- :done="step > 2" -->
            <div class="step-content">
              <div class="text-body2 q-mb-md">
                Copy the AI response and paste it below. The response should be in JSON format.
              </div>
              
              <div class="paste-actions">
                <q-btn
                  color="secondary"
                  icon="content_paste"
                  label="Paste from Clipboard"
                  @click="pasteFromClipboard"
                />
                <div v-if="pasteError" class="paste-error text-negative">
                  {{ pasteError }}
                </div>
              </div>
              

              <q-input
                v-model="aiResponse"
                type="textarea"
                label="AI Response (JSON)"
                placeholder="Paste the JSON response from AI here..."
                outlined
                rows="15"
                class="q-mb-md"
              />
              
              <q-stepper-navigation>
                <q-btn @click="validateAndPreview" color="primary" label="Validate & Preview" />
                <q-btn flat @click="step = 1" color="secondary" label="Back" class="q-ml-sm" />
              </q-stepper-navigation>
            </div>
          </q-step>

          <!-- Step 3: Preview & Import -->
          <q-step
            :name="3"
            title="Preview & Import"
            icon="preview"
          >
            <div class="step-content">
              <div class="preview-header">
                <div class="text-h6">Question Preview</div>
                <div class="text-caption">
                  {{ validQuestions.length }} of {{ parsedQuestions.length }} questions are valid
                </div>
              </div>
              
              <q-table
                :rows="parsedQuestions"
                :columns="previewColumns"
                row-key="id"
                selection="multiple"
                v-model:selected="selectedQuestions"
                flat
                bordered
                class="preview-table"
              >
                <template #body-cell-preview="props">
                  <q-td :props="props">
                    <div class="question-preview-text">
                      {{ props.row.content?.prompt?.substring(0, 100) }}...
                    </div>
                  </q-td>
                </template>
                
                <template #body-cell-status="props">
                  <q-td :props="props">
                    <q-badge
                      :color="props.row.valid ? 'positive' : 'negative'"
                      :label="props.row.valid ? 'Valid' : 'Invalid'"
                    />
                  </q-td>
                </template>
              </q-table>
              
              <q-stepper-navigation>
                <q-btn
                  @click="importQuestions"
                  color="primary"
                  :label="`Import ${selectedQuestions.length} Questions`"
                  :disabled="selectedQuestions.length === 0"
                />
                <q-btn flat @click="step = 2" color="secondary" label="Back" class="q-ml-sm" />
              </q-stepper-navigation>
            </div>
          </q-step>
        </q-stepper>
      </q-card>
    </q-dialog>
  </div>

  <input
    ref="questionImageInput"
    type="file"
    accept="image/*"
    style="display: none"
    @change="handleQuestionImageFile"
  />

  <q-dialog v-model="imageDialogOpen">
    <q-card style="min-width: 520px; max-width: 95vw;">
      <q-card-section class="row items-center q-pb-none">
        <div class="text-h6">Question Image</div>
        <q-space />
        <q-btn icon="close" flat round dense v-close-popup />
      </q-card-section>
      <q-separator />

      <q-card-section v-if="imageEditQuestion">
        <div v-if="imagePreviewUrl" class="image-preview">
          <img :src="imagePreviewUrl" alt="" />
        </div>

        <div class="row q-col-gutter-md">
          <div class="col-12">
            <q-btn
              color="primary"
              icon="upload"
              label="Choose Image"
              @click="triggerQuestionImageFile"
            />
            <q-btn
              flat
              color="secondary"
              icon="content_paste"
              label="Paste Image"
              class="q-ml-sm"
              @click="pasteQuestionImageFromClipboard"
            />
            <q-btn
              flat
              color="secondary"
              icon="content_paste"
              label="Paste URL"
              class="q-ml-sm"
              @click="pasteQuestionImageUrlFromClipboard"
            />
            <q-btn
              v-if="imagePreviewUrl"
              flat
              color="negative"
              icon="delete"
              label="Remove"
              class="q-ml-sm"
              @click="removeQuestionImage"
            />
          </div>

          <div class="col-12">
            <q-input
              dense
              outlined
              v-model="imageEdit.url"
              label="Image URL / Data URL"
            />
          </div>

          <div class="col-12 col-sm-6">
            <q-input
              dense
              outlined
              type="number"
              v-model.number="imageEdit.widthPt"
              label="Width (pt)"
            />
          </div>

          <div class="col-12 col-sm-6">
            <q-input
              dense
              outlined
              type="number"
              v-model.number="imageEdit.opacity"
              label="Opacity (0-1)"
            />
          </div>

          <div class="col-12 col-sm-6">
            <q-input
              dense
              outlined
              type="number"
              v-model.number="imageEdit.topPt"
              label="Top offset (pt)"
            />
          </div>

          <div class="col-12 col-sm-6">
            <q-input
              dense
              outlined
              type="number"
              v-model.number="imageEdit.rightPt"
              label="Right offset (pt)"
            />
          </div>
        </div>
      </q-card-section>

      <q-separator />
      <q-card-actions align="right">
        <q-btn flat label="Cancel" color="grey-8" v-close-popup />
        <q-btn
          color="primary"
          label="Save"
          :disable="!imageEditQuestion"
          @click="saveQuestionImage"
        />
      </q-card-actions>
    </q-card>
  </q-dialog>

  <!-- First/Last Page Fullscreen Dialog -->
  <q-dialog
    v-model="firstLastPageOpen"
    maximized
    transition-show="slide-up"
    transition-hide="slide-down"
  >
    <q-card class="first-last-page-dialog">
      <q-card-section class="row items-center q-pb-none">
        <div class="text-h6">
          <q-icon name="auto_stories" class="q-mr-sm" />
          First & Last Page Management
        </div>
        <q-space />
        <q-btn icon="close" flat round dense v-close-popup />
      </q-card-section>

      <q-separator />

      <q-card-section class="scroll">
        <div class="row q-col-gutter-lg">
          <!-- First Page Section -->
          <div class="col-12 col-md-6">
            <div class="page-section">
              <div class="section-header">
                <q-icon name="first_page" size="24px" color="primary" />
                <span class="section-title">First Page</span>
              </div>
              
              <FirstPageSettings
                :model-value="pageOptions.firstPage"
                @update:model-value="(value) => { pageOptions.firstPage = value; savePageState() }"
              />
            </div>
          </div>

          <!-- Last Page Section -->
          <div class="col-12 col-md-6">
            <div class="page-section">
              <div class="section-header">
                <q-icon name="last_page" size="24px" color="secondary" />
                <span class="section-title">Last Page</span>
              </div>
              
              <LastPageSettings
                :model-value="pageOptions.lastPage"
                @update:model-value="(value) => { pageOptions.lastPage = value; savePageState() }"
              />
            </div>
          </div>
        </div>

        <!-- Preview Section -->
        <div class="row q-mt-lg">
          <div class="col-12">
            <div class="preview-section">
              <div class="section-header">
                <q-icon name="preview" size="24px" color="accent" />
                <span class="section-title">Preview</span>
              </div>
              
              <div class="preview-container">
                <div class="preview-page first-page-preview" v-if="pageOptions.firstPage.enabled">
                  <div class="preview-label">First Page</div>
                  <div class="preview-content">
                    <div v-if="pageOptions.firstPage.type === 'title'" class="title-preview">
                      <h1>{{ pageOptions.firstPage.title || 'Title' }}</h1>
                      <h2 v-if="pageOptions.firstPage.subtitle">{{ pageOptions.firstPage.subtitle }}</h2>
                    </div>
                    <div v-else-if="pageOptions.firstPage.type === 'cover'" class="cover-preview">
                      <h1>{{ pageOptions.firstPage.coverTitle || 'Cover Title' }}</h1>
                      <p>{{ pageOptions.firstPage.coverDescription || 'Description' }}</p>
                      <img v-if="pageOptions.firstPage.coverImage" :src="pageOptions.firstPage.coverImage" class="cover-image" />
                    </div>
                    <div v-else class="custom-preview" v-html="pageOptions.firstPage.customContent || 'Custom content'"></div>
                  </div>
                </div>
                
                <div class="preview-page last-page-preview" v-if="pageOptions.lastPage.enabled">
                  <div class="preview-label">Last Page</div>
                  <div class="preview-content">
                    <h2>{{ pageOptions.lastPage.title || 'End of Exam' }}</h2>
                    <p>{{ pageOptions.lastPage.message || 'Thank you for completing the exam.' }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </q-card-section>

      <q-separator />

      <q-card-actions align="right">
        <q-btn flat color="primary" @click="firstLastPageOpen = false">Done</q-btn>
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { ref, computed, onMounted, watch, nextTick } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { renderToString } from 'katex'
import QuestionDisplay from './components/QuestionDisplay.vue'
import SectionTotalMark from './components/SectionTotalMark.vue'
import PrintActions from './components/PrintActions.vue'
import FirstPageSettings from './components/FirstPageSettings.vue'
import LastPageSettings from './components/LastPageSettings.vue'
import { renderSectionTotalHTML } from './utils/sectionTotalTemplates'
import { formatQuestionLabel } from './utils/questionNumbering'
 

const PAGE_STATE_KEY = 'exam_ready_to_print_test_builder_state_v1'

const page = usePage()

const importFileInput = ref(null)
const questionImageInput = ref(null)

const imageDialogOpen = ref(false)
const imageEditQuestion = ref(null)
const imageEdit = ref({
  url: '',
  widthPt: 90,
  topPt: 0,
  rightPt: 0,
  opacity: 1
})

const imagePreviewUrl = computed(() => imageEdit.value?.url || '')

// Sample questions with math expressions
const sampleQuestions = ref([
  {
    id: 1,
    type: 'short_answer',
    marks: 2,
    content: {
      prompt: 'What is the sum of $2 \\frac{1}{5}$ and $1 \\frac{2}{5}$?'
    }
  },
  {
    id: 2,
    type: 'short_answer', 
    marks: 3,
    content: {
      prompt: 'Calculate: $\\frac{3}{4} + \\frac{2}{3} = ?$'
    }
  },
  {
    id: 3,
    type: 'short_answer',
    marks: 1,
    content: {
      prompt: 'Simplify: $\\sqrt{16} + \\sqrt{9}$'
    }
  }
])

// AI Import State
const aiDialogOpen = ref(false)
const step = ref(1)
const generatedPrompt = ref('')
const aiResponse = ref('')
const parsedQuestions = ref([])
const selectedQuestions = ref([])
const pasteError = ref('')
const firstLastPageOpen = ref(false)
const optionsOpen = ref(false)
const settingsTab = ref('general')
const pageOptions = ref({
  examTitle: {
    enabled: true,
    text: 'Math Questions Test'
  },
  showMarksPerQuestion: true,
  printHeader: {
    enabled: false,
    autoFit: true,
    heightPt: 120,
    pageMarginTopMm: 0,
    mode: 'html',
    templateId: 'custom',
    html: '',
    imageUrl: '',
    imageFit: 'contain',
    template1: {
      schoolName: 'AL-MUTAQADIMAH SCHOOLS (Al-Tadamon International School)',
      period: 'first Academic period 2025 - 2026',
      grade: '4',
      subject: 'Math',
      examType: 'V1',
      gender: 'Boys'
    }
  },
  printFooter: {
    enabled: true,
    autoFit: true,
    heightPt: 90,
    pageMarginBottomMm: 0,
    mode: 'html',
    html: '',
    imageUrl: '',
    imageFit: 'contain',
    textFontSizePt: 12,
    textColor: '#000000',
    reserveSpace: true,
    singleLine: false,
    showTopBorder: false,
    showPageNumbers: true,
    pageNumberPosition: 'bottom-center',
    pageNumberFormat: 'page',
    pageNumberFontSize: 10,
    pageNumberColor: '#000000'
  },
  firstPage: {
    enabled: false,
    type: 'title',
    title: '',
    subtitle: '',
    titleAlignment: 'center',
    coverTitle: '',
    coverDescription: '',
    coverImage: '',
    customContent: '',
    skipPageNumber: true,
    pageBreakAfter: true
  },
  lastPage: {
    enabled: false,
    type: 'message',
    title: 'End of Exam',
    message: 'Thank you for completing the exam.',
    alignment: 'center',
    showTotalMarks: false,
    showCompletionTime: false,
    customContent: '',
    skipPageNumber: false,
    pageBreakBefore: true
  },
  questionSeparator: {
    enabled: false,
    lineStyle: 'solid',
    color: '#1f3a5a',
    thicknessPt: 1,
    spaceBeforePt: 8,
    spaceAfterPt: 12
  },
  mcqOptions: {
    columns: 1,
    optionGapPt: 6,
    labelGapPt: 8,
    labelStyle: 'letter',
    customLabelTemplate: '{letter})',
    checkboxStyle: 'box',
    checkboxShowLabel: false,
    checkboxLabelType: 'letter',
    labelFontSizePt: 0,
    optionFontSizePt: 0,
    labelBold: false,
    optionBold: false
  },
  sectionTotal: {
    template: 'text',
    prefix: 'Total:',
    suffix: 'marks',
    placement: 'normal',
    offsetXPt: 0,
    offsetYPt: 0,
    boxTopHeightPt: 22
  },
  questionNumbering: {
    style: 'question',
    startAt: 1,
    prefix: '',
    suffix: '',
    customTemplate: '{n}',
    inlineWithText: false,
    inlineGap: 8,
    pageBreaksBefore: {}
  }
})

const printSequence = computed(() => {
  const result = []
  sections.value.forEach((section) => {
    sampleQuestions.value
      .filter(q => getQuestionSectionId(q) === section.id)
      .forEach(q => result.push(q))
  })
  return result
})

const HEADER_TEMPLATES = [
  {
    id: 'custom',
    label: 'Custom',
    heightPt: 120,
    html: ''
  },
  {
    id: 'exam_header_v1',
    label: 'Exam Header V1',
    heightPt: 150,
    html: ''
  }
]

const headerTemplateOptions = computed(() => {
  return HEADER_TEMPLATES.map(t => ({ label: t.label, value: t.id }))
})

function applyHeaderTemplate() {
  const id = String(pageOptions.value?.printHeader?.templateId || 'custom')
  const tpl = HEADER_TEMPLATES.find(t => t.id === id)
  if (!tpl) {
    savePageState()
    return
  }

  if (tpl.id !== 'custom') {
    if (tpl.id === 'exam_header_v1') {
      pageOptions.value.printHeader.mode = 'html'
      pageOptions.value.printHeader.html = buildTemplate1HTML(pageOptions.value.printHeader.template1)
    } else {
      pageOptions.value.printHeader.html = tpl.html
    }
    pageOptions.value.printHeader.heightPt = tpl.heightPt
  }
  savePageState()
}

function escapeHtml(value) {
  return String(value ?? '')
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#39;')
}

function buildTemplate1HTML(cfg) {
  const schoolName = escapeHtml(cfg?.schoolName)
  const period = escapeHtml(cfg?.period)
  const grade = escapeHtml(cfg?.grade)
  const subject = escapeHtml(cfg?.subject)
  const examType = escapeHtml(cfg?.examType)
  const gender = String(cfg?.gender || 'Both')
  const boysFilled = gender === 'Boys' ? ' box-filled' : ''
  const girlsFilled = gender === 'Girls' ? ' box-filled' : ''

  return (
    '<style>' +
    ' .exam-header { width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; border: 4px double #00AEEF; margin-top: 0; }' +
    ' .exam-header td { border: 1px solid #00AEEF; padding: 8px 12px; font-weight: bold; color: #000; font-size: 14px; }' +
    ' .center-text { text-align: center; font-size: 16px; }' +
    ' .checkbox-group { display: inline-flex; align-items: center; gap: 10px; }' +
    ' .box { display: inline-block; width: 15px; height: 15px; border: 1px solid black; vertical-align: middle; margin-right: 5px; }' +
    ' .box-filled { background-color: black; }' +
    '</style>' +
    '<table class="exam-header">' +
    ' <tr>' +
    '   <td colspan="4" class="center-text">' +
    '     Final Second- Term Exam for the ' + period + ' &nbsp;&nbsp; ---- &nbsp;&nbsp; ' +
    '     ' + schoolName +
    '   </td>' +
    ' </tr>' +
    ' <tr>' +
    '   <td style="width: 15%;">Grade: ' + grade + '</td>' +
    '   <td style="width: 25%;">Subject: ' + subject + '</td>' +
    '   <td style="width: 35%;">' +
    '     <span class="checkbox-group">' +
    '       <span><span class="box' + boysFilled + '"></span> Boys</span>' +
    '       <span><span class="box' + girlsFilled + '"></span> Girls</span>' +
    '     </span>' +
    '   </td>' +
    '   <td style="width: 25%; text-align: right;">Exam Type: ' + examType + '</td>' +
    ' </tr>' +
    ' <tr>' +
    '   <td colspan="2">Student Name: ....................................................................................</td>' +
    '   <td colspan="2">ID No. S.................................................................</td>' +
    ' </tr>' +
    '</table>'
  )
}

watch(
  () => pageOptions.value?.printHeader?.template1,
  () => {
    if (pageOptions.value?.printHeader?.templateId !== 'exam_header_v1') return
    if (pageOptions.value?.printHeader?.mode !== 'html') return
    pageOptions.value.printHeader.html = buildTemplate1HTML(pageOptions.value.printHeader.template1)
    savePageState()
  },
  { deep: true }
)

async function saveHeaderTemplate1() {
  pageOptions.value.printHeader.template1 = {
    schoolName: pageOptions.value.printHeader.template1.schoolName,
    period: pageOptions.value.printHeader.template1.period,
    grade: pageOptions.value.printHeader.template1.grade,
    subject: pageOptions.value.printHeader.template1.subject,
    examType: pageOptions.value.printHeader.template1.examType,
    gender: pageOptions.value.printHeader.template1.gender
  }

  if (pageOptions.value?.printHeader?.templateId === 'exam_header_v1' && pageOptions.value?.printHeader?.mode === 'html') {
    pageOptions.value.printHeader.html = buildTemplate1HTML(pageOptions.value.printHeader.template1)
  }
  await savePageState()
}

const headerImageFileInput = ref(null)

const footerImageFileInput = ref(null)

function triggerHeaderImageFile() {
  if (headerImageFileInput.value) {
    headerImageFileInput.value.value = ''
    headerImageFileInput.value.click()
  }
}

function triggerFooterImageFile() {
  if (footerImageFileInput.value) {
    footerImageFileInput.value.value = ''
    footerImageFileInput.value.click()
  }
}

async function handleFooterImageFile(event) {
  try {
    const file = event?.target?.files?.[0]
    if (!file) return

    const reader = new FileReader()
    reader.onload = async () => {
      pageOptions.value.printFooter.imageUrl = String(reader.result || '')
      await savePageState()
    }
    reader.readAsDataURL(file)
  } catch (e) {
    console.error('Footer image load failed', e)
  }
}

async function pasteFooterImage() {
  try {
    const items = await navigator.clipboard.read()
    for (const item of items) {
      const type = item.types.find(t => t.startsWith('image/'))
      if (!type) continue
      const blob = await item.getType(type)
      const dataUrl = await new Promise((resolve, reject) => {
        const r = new FileReader()
        r.onload = () => resolve(String(r.result || ''))
        r.onerror = reject
        r.readAsDataURL(blob)
      })
      pageOptions.value.printFooter.imageUrl = dataUrl
      await savePageState()
      return
    }
  } catch (e) {
    console.error('Paste footer image failed', e)
  }
}

async function pasteFooterImageUrl() {
  try {
    const text = await navigator.clipboard.readText()
    if (!text) return
    pageOptions.value.printFooter.imageUrl = String(text)
    await savePageState()
  } catch (e) {
    console.error('Paste footer image url failed', e)
  }
}

async function removeFooterImage() {
  pageOptions.value.printFooter.imageUrl = ''
  await savePageState()
}

async function pasteFooterHtml() {
  try {
    const text = await navigator.clipboard.readText()
    if (!text) return
    pageOptions.value.printFooter.html = String(text)
    await savePageState()
  } catch (e) {
    console.error('Paste footer html failed', e)
  }
}

async function handleHeaderImageFile(event) {
  try {
    const file = event?.target?.files?.[0]
    if (!file) return

    const reader = new FileReader()
    reader.onload = async () => {
      pageOptions.value.printHeader.imageUrl = String(reader.result || '')
      await savePageState()
    }
    reader.readAsDataURL(file)
  } catch (e) {
    console.error('Header image load failed', e)
    alert('Failed to load image: ' + e.message)
  }
}

async function removeHeaderImage() {
  pageOptions.value.printHeader.imageUrl = ''
  await savePageState()
}

async function pasteHeaderImageUrlFromClipboard() {
  try {
    const text = await navigator.clipboard.readText()
    if (!text || !String(text).trim()) {
      alert('Clipboard is empty.')
      return
    }
    pageOptions.value.printHeader.imageUrl = String(text).trim()
    await savePageState()
  } catch (e) {
    console.error('Paste header URL failed', e)
    alert('Clipboard paste blocked. Please allow clipboard permission or paste manually.')
  }
}

async function pasteHeaderImageFromClipboard() {
  try {
    if (!navigator.clipboard?.read) {
      alert('Clipboard image paste is not supported in this browser. Use Choose Image instead.')
      return
    }

    const items = await navigator.clipboard.read()
    for (const item of items) {
      const imgType = item.types.find(t => t.startsWith('image/'))
      if (!imgType) continue

      const blob = await item.getType(imgType)
      const reader = new FileReader()
      reader.onload = async () => {
        pageOptions.value.printHeader.imageUrl = String(reader.result || '')
        await savePageState()
      }
      reader.readAsDataURL(blob)
      return
    }

    alert('No image found in clipboard.')
  } catch (e) {
    console.error('Paste header image failed', e)
    alert('Clipboard image paste blocked. Please allow clipboard permission or use Choose Image.')
  }
}

async function pasteHeaderHtmlFromClipboard() {
  try {
    const text = await navigator.clipboard.readText()
    if (!text || !String(text).trim()) {
      alert('Clipboard is empty.')
      return
    }
    pageOptions.value.printHeader.html = String(text)
    await savePageState()
  } catch (e) {
    console.error('Paste header HTML failed', e)
    alert('Clipboard paste blocked. Please allow clipboard permission or paste manually.')
  }
}

const questionSeparatorStyle = computed(() => {
  const sep = pageOptions.value?.questionSeparator || {}
  const style = String(sep.lineStyle || 'solid')
  const color = String(sep.color || '#1f3a5a')
  const thicknessRaw = Number(sep.thicknessPt)
  const thicknessPt = Number.isFinite(thicknessRaw) ? thicknessRaw : 1
  const beforeRaw = Number(sep.spaceBeforePt)
  const beforePt = Number.isFinite(beforeRaw) ? beforeRaw : 8
  const afterRaw = Number(sep.spaceAfterPt)
  const afterPt = Number.isFinite(afterRaw) ? afterRaw : 12

  return {
    marginTop: beforePt + 'pt',
    marginBottom: afterPt + 'pt',
    borderBottom: thicknessPt + 'pt ' + style + ' ' + color
  }
})

function isPageBreakBefore(question) {
  const map = pageOptions.value?.questionNumbering?.pageBreaksBefore || {}
  const qid = String(question?.id)
  return !!map[qid]
}

async function togglePageBreakBefore(question) {
  const qid = String(question?.id)
  if (!qid) return
  const current = pageOptions.value.questionNumbering.pageBreaksBefore || {}
  const next = { ...current }
  if (next[qid]) {
    delete next[qid]
  } else {
    next[qid] = true
  }
  pageOptions.value.questionNumbering.pageBreaksBefore = next
  await savePageState()
}

function togglePageBreakAfter(question) {
  const qid = String(question?.id)
  if (!qid) return

  const idx = printSequence.value.findIndex(q => String(q?.id) === qid)
  const nextQuestion = idx >= 0 ? printSequence.value[idx + 1] : null
  if (!nextQuestion) return
  togglePageBreakBefore(nextQuestion)
}

const questionNumberingStyles = [
  { label: 'Question 1', value: 'question' },
  { label: '1', value: 'number' },
  { label: 'A', value: 'letter' },
  { label: 'Custom', value: 'custom' }
]

const sectionTotalTemplates = [
  { label: 'Text (Total: 33 marks)', value: 'text' },
  { label: 'Box (33)', value: 'box' }
]

const sectionTotalPlacements = [
  { label: 'Normal (takes space)', value: 'normal' },
  { label: 'Fly top-right (no space)', value: 'fly_top_right' }
]

const mcqLabelStyles = [
  { label: 'Letters (A)', value: 'letter' },
  { label: 'Numbers (1)', value: 'number' },
  { label: 'Checkbox', value: 'checkbox' },
  { label: 'Custom', value: 'custom' }
]

const sections = ref([
  { id: 'sec_default', title: 'Choose the correct answer :-', instructions: '' }
])

const questionSectionMap = ref({})

const sectionOptions = computed(() => sections.value.map(s => ({ label: s.title, value: s.id })))

// AI Configuration
const aiConfig = ref({
  topic: '',
  grade: '',
  questionCount: 5,
  latexSupport: true,
  htmlSupport: false,
  instructions: ''
})

// Preview table columns
const previewColumns = [
  { name: 'id', label: 'ID', field: 'id', align: 'left' },
  { name: 'type', label: 'Type', field: 'type', align: 'left' },
  { name: 'marks', label: 'Marks', field: 'marks', align: 'left' },
  { name: 'preview', label: 'Preview', field: 'preview', align: 'left' },
  { name: 'status', label: 'Status', field: 'status', align: 'left' }
]

// Computed properties
const validQuestions = computed(() => parsedQuestions.value.filter(q => q.valid))

// AI Import Functions
function openAIDialog() {
  aiDialogOpen.value = true
  step.value = 1
  resetAIState()
}

function resetAIState() {
  generatedPrompt.value = ''
  aiResponse.value = ''
  parsedQuestions.value = []
  selectedQuestions.value = []
  pasteError.value = ''
}

async function loadPageState() {
  try {
    const response = await fetch('/exam/ready-to-print/api/load-data')
    const data = await response.json()
    
    if (data?.questions) sampleQuestions.value = data.questions
    if (data?.settings) {
      pageOptions.value = { ...pageOptions.value, ...data.settings }
      // Force enable footer and page numbers regardless of cached settings
      if (pageOptions.value.printFooter) {
        pageOptions.value.printFooter.enabled = true
        pageOptions.value.printFooter.showPageNumbers = true
      }
    }
    
    // Ensure every question has a section
    const defaultSectionId = sections.value[0]?.id
    sampleQuestions.value.forEach(q => {
      const qid = String(q?.id)
      if (defaultSectionId && !questionSectionMap.value[qid]) questionSectionMap.value[qid] = defaultSectionId
    })
  } catch (e) {
    console.error('Failed to load page state', e)
  }
}

async function pasteQuestionImageUrlFromClipboard() {
  try {
    const text = await navigator.clipboard.readText()
    if (!text || !String(text).trim()) {
      alert('Clipboard is empty.')
      return
    }
    imageEdit.value = { ...imageEdit.value, url: String(text).trim() }
  } catch (e) {
    console.error('Paste URL failed', e)
    alert('Clipboard paste blocked. Please allow clipboard permission or paste manually.')
  }
}

async function pasteQuestionImageFromClipboard() {
  try {
    if (!navigator.clipboard?.read) {
      alert('Clipboard image paste is not supported in this browser. Use Choose Image instead.')
      return
    }

    const items = await navigator.clipboard.read()
    for (const item of items) {
      const imgType = item.types.find(t => t.startsWith('image/'))
      if (!imgType) continue

      const blob = await item.getType(imgType)
      const reader = new FileReader()
      reader.onload = () => {
        imageEdit.value = { ...imageEdit.value, url: String(reader.result || '') }
      }
      reader.readAsDataURL(blob)
      return
    }

    alert('No image found in clipboard.')
  } catch (e) {
    console.error('Paste image failed', e)
    alert('Clipboard image paste blocked. Please allow clipboard permission or use Choose Image.')
  }
}

async function savePageState() {
  try {
    const data = {
      questions: sampleQuestions.value,
      settings: {
        examTitle: pageOptions.value.examTitle,
        showMarksPerQuestion: pageOptions.value.showMarksPerQuestion,
        printHeader: pageOptions.value.printHeader,
        printFooter: pageOptions.value.printFooter,
        firstPage: pageOptions.value.firstPage,
        lastPage: pageOptions.value.lastPage,
        // Include other important settings as needed
        questionNumbering: pageOptions.value.questionNumbering,
        sectionTotal: pageOptions.value.sectionTotal,
        questionSeparator: pageOptions.value.questionSeparator,
        mcqOptions: pageOptions.value.mcqOptions,
        pageLayout: pageOptions.value.pageLayout
      }
    }
    
    await fetch('/exam/ready-to-print/api/save-data', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': page.props.csrf_token || '',
        'Accept': 'application/json'
      },
      body: JSON.stringify(data)
    })
  } catch (e) {
    console.error('Failed to save page state', e)
  }
}

async function addSection() {
  const id = 'sec_' + Date.now()
  sections.value.push({ id, title: 'New Section', instructions: '' })
  await savePageState()
}

async function removeSection(sectionId) {
  if (sections.value.length <= 1) return
  const fallback = sections.value.find(s => s.id !== sectionId)?.id
  sections.value = sections.value.filter(s => s.id !== sectionId)
  Object.keys(questionSectionMap.value).forEach(qid => {
    if (questionSectionMap.value[qid] === sectionId) questionSectionMap.value[qid] = fallback
  })
  await savePageState()
}

function getQuestionSectionId(question) {
  const qid = String(question?.id)
  return questionSectionMap.value[qid] || sections.value[0]?.id
}

async function setQuestionSection(question, sectionId) {
  const qid = String(question?.id)
  questionSectionMap.value[qid] = sectionId
  await savePageState()
}

function sectionTotalMarks(sectionId) {
  return sampleQuestions.value
    .filter(q => getQuestionSectionId(q) === sectionId)
    .reduce((sum, q) => sum + (Number(q?.marks) || 0), 0)
}

function getExportPayload() {
  return {
    version: 1,
    exportedAt: new Date().toISOString(),
    sampleQuestions: sampleQuestions.value,
    pageOptions: pageOptions.value,
    sections: sections.value,
    questionSectionMap: questionSectionMap.value
  }
}

function exportToJson() {
  try {
    const payload = getExportPayload()
    const blob = new Blob([JSON.stringify(payload, null, 2)], { type: 'application/json' })
    const url = URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = 'exam_builder_test_export.json'
    document.body.appendChild(a)
    a.click()
    a.remove()
    URL.revokeObjectURL(url)
  } catch (e) {
    console.error('Export failed', e)
    alert('Export failed: ' + e.message)
  }
}

function triggerImportFile() {
  if (importFileInput.value) {
    importFileInput.value.value = ''
    importFileInput.value.click()
  }
}

async function handleImportFile(event) {
  try {
    const file = event?.target?.files?.[0]
    if (!file) return

    const text = await file.text()
    const parsed = JSON.parse(text)

    if (Array.isArray(parsed)) {
      // Allow importing a plain questions array
      sampleQuestions.value = parsed
      const defaultSectionId = sections.value[0]?.id
      parsed.forEach(q => {
        const qid = String(q?.id)
        if (defaultSectionId && !questionSectionMap.value[qid]) questionSectionMap.value[qid] = defaultSectionId
      })
      savePageState()
      return
    }

    if (!parsed || typeof parsed !== 'object') {
      alert('Invalid JSON file format.')
      return
    }

    if (Array.isArray(parsed.sampleQuestions)) sampleQuestions.value = parsed.sampleQuestions
    if (parsed.pageOptions) pageOptions.value = { ...pageOptions.value, ...parsed.pageOptions }
    if (Array.isArray(parsed.sections) && parsed.sections.length > 0) sections.value = parsed.sections
    if (parsed.questionSectionMap && typeof parsed.questionSectionMap === 'object') questionSectionMap.value = parsed.questionSectionMap

    // Ensure every question has a section
    const defaultSectionId = sections.value[0]?.id
    sampleQuestions.value.forEach(q => {
      const qid = String(q?.id)
      if (defaultSectionId && !questionSectionMap.value[qid]) questionSectionMap.value[qid] = defaultSectionId
    })

    savePageState()
  } catch (e) {
    console.error('Import failed', e)
    alert('Import failed: ' + e.message)
  }
}

async function pasteFromClipboard() {
  pasteError.value = ''
  try {
    const text = await navigator.clipboard.readText()
    if (!text || !String(text).trim()) {
      pasteError.value = 'Clipboard is empty.'
      return
    }
    aiResponse.value = text
  } catch (err) {
    pasteError.value = 'Clipboard paste blocked. Please allow clipboard permission or paste manually.'
    console.error('pasteFromClipboard failed', err)
  }
}

function generatePrompt() {
  const { topic, grade, questionCount, latexSupport, htmlSupport, instructions } = aiConfig.value
  
  let prompt = `Generate ${questionCount} math questions for ${grade} students on the topic of "${topic}".`

  prompt += `\n\nRequirements:`
  prompt += `\n- Return ONLY a JSON array of question objects`
  prompt += `\n- Do NOT include citations or source markers like [cite: 219] anywhere in the text`
  prompt += `\n- Each question should have: id (number), type (string), marks (number), content with prompt (string)`
  prompt += `\n- Question types: "short_answer", "multiple_choice", or "true_false"`
  prompt += `\n- Marks should be between 1-5 points based on difficulty`

  if (latexSupport) {
    prompt += `\n- Use LaTeX notation for math expressions: $x^2$ for inline, $$\\frac{a}{b}$$ for display`
    prompt += `\n- Include examples like: $\\frac{3}{4}$, $x^2 + 2x - 8 = 0$, $\\sqrt{16}$`
  }

  if (htmlSupport) {
    prompt += `\n- You can use basic HTML tags: <strong>, <em>, <u>, <sup>, <sub>`
  }

  if (instructions) {
    prompt += `\n\nAdditional instructions: ${instructions}`
  }

  prompt += `\n\nJSON format example:`
  prompt += `\n\`\`\`json`
  prompt += `\n[`
  prompt += `\n  {`
  prompt += `\n    "id": 1,`
  prompt += `\n    "type": "short_answer",`
  prompt += `\n    "marks": 2,`
  prompt += `\n    "content": {`
  prompt += `\n      "prompt": "What is the sum of $2 \\frac{1}{5}$ and $1 \\frac{2}{5}$?"`
  prompt += `\n    }`
  prompt += `\n  },`
  prompt += `\n  {`
  prompt += `\n    "id": 2,`
  prompt += `\n    "type": "multiple_choice",`
  prompt += `\n    "marks": 3,`
  prompt += `\n    "content": {`
  prompt += `\n      "prompt": "Solve for x: $x^2 + 2x - 8 = 0$",`
  prompt += `\n      "options": ["x = 2", "x = -4", "x = 2 or x = -4", "x = 4"],`
  prompt += `\n      "correct_answer": "x = 2 or x = -4"`
  prompt += `\n    }`
  prompt += `\n  }`
  prompt += `\n]`
  prompt += `\n\`\`\``

  generatedPrompt.value = prompt
}

function copyPrompt() {
  navigator.clipboard.writeText(generatedPrompt.value).then(() => {
    // Show success notification (you can add Quasar notify here)
    console.log('Prompt copied to clipboard')
  }).catch(err => {
    console.error('Failed to copy prompt:', err)
  })
}

function validateAndPreview() {
  try {
    // Remove markdown code blocks and clean up the response
    let cleanedResponse = aiResponse.value
      .replace(/```json\n?|\n?```/g, '') // Remove code blocks
      .replace(/[\u2018\u2019\u201C\u201D\u2026\u2027\u00A0\u00A8\u00A9\u00AA\u00AB\u00AC\u00B0\u00B4\u00B8\u00C0-\u00C1\u00C8\u00C9\u00CC\u00CD\u00CE\u00CF\u00D0-\u00D1\u00D8\u00DA\u00DB\u00DC\u00DD\u00DE\u00DF\u00E0-\u00E1\u00E8\u00EA\u00EB\u00EC\u00ED\u00EE\u00EF\u00F0-\u00F1\u00F2\u00F3\u00F4\u00F5\u00F6\u00F7\u00F8\u00F9\u00FA\u00FB\u00FC\u00FD\u00FE\u00FF]/g, '') // Remove weird characters
      .trim()
    
    if (!cleanedResponse) {
      alert('Please paste some JSON content first.')
      return
    }
    
    // Try to parse JSON
    let questions
    try {
      questions = JSON.parse(cleanedResponse)
    } catch (parseError) {
      alert('Invalid JSON format: ' + parseError.message)
      return
    }

    const questionsArray = Array.isArray(questions)
      ? questions
      : (Array.isArray(questions?.questions) ? questions.questions : null)

    if (!questionsArray) {
      alert('Invalid JSON format: expected an array of questions.')
      return
    }
    
    // Validate and add status
    parsedQuestions.value = questionsArray.map((q, index) => {
      const validation = validateQuestion(q, index + 1)
      return {
        ...q,
        valid: validation.isValid,
        errors: validation.errors
      }
    })
    
    // Auto-select valid questions
    selectedQuestions.value = parsedQuestions.value.filter(q => q.valid)
    
    step.value = 3
    
    // Show success message
    if (selectedQuestions.value.length > 0) {
      console.log(`Successfully parsed ${selectedQuestions.value.length} valid questions`)
    }
  } catch (error) {
    console.error('Error:', error)
    alert('Error processing AI response: ' + error.message)
  }
}

function validateQuestion(question, index) {
  const errors = []
  
  if (!question.id || typeof question.id !== 'number') {
    errors.push('Invalid or missing id')
  }
  
  if (!question.type || !['short_answer', 'multiple_choice', 'true_false'].includes(question.type)) {
    errors.push('Invalid or missing type')
  }
  
  if (!question.marks || typeof question.marks !== 'number' || question.marks < 1 || question.marks > 5) {
    errors.push('Invalid marks (must be 1-5)')
  }
  
  if (!question.content || !question.content.prompt) {
    errors.push('Missing content.prompt')
  }
  
  if (question.type === 'multiple_choice') {
    if (!question.content.options || !Array.isArray(question.content.options) || question.content.options.length < 2) {
      errors.push('Multiple choice questions need at least 2 options')
    }
    
    if (!question.content.correct_answer) {
      errors.push('Multiple choice questions need correct_answer')
    }
  }
  
  return {
    isValid: errors.length === 0,
    errors
  }
}

function importQuestions() {
  // Add selected questions to sampleQuestions
  const newQuestions = selectedQuestions.value.map(q => ({
    ...q,
    id: Date.now() + Math.random() // Generate unique ID
  }))
  
  sampleQuestions.value = [...sampleQuestions.value, ...newQuestions]

  const defaultSectionId = sections.value[0]?.id
  newQuestions.forEach(q => {
    const qid = String(q?.id)
    if (defaultSectionId && !questionSectionMap.value[qid]) questionSectionMap.value[qid] = defaultSectionId
  })
  savePageState()
  
  // Close dialog and reset
  aiDialogOpen.value = false
  resetAIState()
  
  // Show success notification
  console.log(`Successfully imported ${newQuestions.length} questions`)
}

function deleteQuestion(id) {
  sampleQuestions.value = sampleQuestions.value.filter(q => q.id !== id)
  delete questionSectionMap.value[String(id)]
  savePageState()
}

function openImageDialog(question) {
  imageEditQuestion.value = question
  const existing = question?.content?.image

  if (existing && typeof existing === 'object') {
    imageEdit.value = {
      url: existing.url || '',
      widthPt: Number.isFinite(Number(existing.widthPt)) ? Number(existing.widthPt) : 90,
      topPt: Number.isFinite(Number(existing.topPt)) ? Number(existing.topPt) : 0,
      rightPt: Number.isFinite(Number(existing.rightPt)) ? Number(existing.rightPt) : 0,
      opacity: Number.isFinite(Number(existing.opacity)) ? Number(existing.opacity) : 1
    }
  } else if (typeof existing === 'string') {
    imageEdit.value = {
      url: existing,
      widthPt: 90,
      topPt: 0,
      rightPt: 0,
      opacity: 1
    }
  } else {
    imageEdit.value = {
      url: '',
      widthPt: 90,
      topPt: 0,
      rightPt: 0,
      opacity: 1
    }
  }

  imageDialogOpen.value = true
}

function triggerQuestionImageFile() {
  if (!questionImageInput.value) return
  questionImageInput.value.value = ''
  questionImageInput.value.click()
}

async function handleQuestionImageFile(event) {
  try {
    const file = event?.target?.files?.[0]
    if (!file) return

    const reader = new FileReader()
    reader.onload = () => {
      imageEdit.value = { ...imageEdit.value, url: String(reader.result || '') }
    }
    reader.readAsDataURL(file)
  } catch (e) {
    console.error('Image load failed', e)
    alert('Failed to load image: ' + e.message)
  }
}

function removeQuestionImage() {
  imageEdit.value = { ...imageEdit.value, url: '' }
}

function saveQuestionImage() {
  const q = imageEditQuestion.value
  if (!q) return

  if (!q.content) q.content = {}

  const url = String(imageEdit.value.url || '')
  if (!url) {
    delete q.content.image
  } else {
    q.content.image = {
      url,
      widthPt: Number(imageEdit.value.widthPt) || 90,
      topPt: Number(imageEdit.value.topPt) || 0,
      rightPt: Number(imageEdit.value.rightPt) || 0,
      opacity: Number.isFinite(Number(imageEdit.value.opacity)) ? Number(imageEdit.value.opacity) : 1
    }
  }

  imageDialogOpen.value = false
  savePageState()
}

function generatePrintHTML() {
  const examTitleEnabled = !!pageOptions.value?.examTitle?.enabled
  const examTitleTextRaw = String(pageOptions.value?.examTitle?.text || 'Math Questions Test')
  const docTitle = examTitleEnabled ? examTitleTextRaw : 'Math Questions Print'
  let html = '<!DOCTYPE html><html><head><title>' + escapeHtml(docTitle) + '</title>'
  html += '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/katex.min.css">'
  const inlineGap = Number(pageOptions.value?.questionNumbering?.inlineGap)
  const inlineGapPt = Number.isFinite(inlineGap) ? inlineGap : 8
  const mcqColsRaw = Number(pageOptions.value?.mcqOptions?.columns)
  const mcqCols = Number.isFinite(mcqColsRaw) && mcqColsRaw > 0 ? Math.floor(mcqColsRaw) : 1
  const mcqGapRaw = Number(pageOptions.value?.mcqOptions?.optionGapPt)
  const mcqGapPt = Number.isFinite(mcqGapRaw) ? mcqGapRaw : 6
  const mcqLabelGapRaw = Number(pageOptions.value?.mcqOptions?.labelGapPt)
  const mcqLabelGapPt = Number.isFinite(mcqLabelGapRaw) ? mcqLabelGapRaw : 8
  const labelFontRaw = Number(pageOptions.value?.mcqOptions?.labelFontSizePt)
  const labelFontPt = Number.isFinite(labelFontRaw) && labelFontRaw > 0 ? labelFontRaw : 0
  const optionFontRaw = Number(pageOptions.value?.mcqOptions?.optionFontSizePt)
  const optionFontPt = Number.isFinite(optionFontRaw) && optionFontRaw > 0 ? optionFontRaw : 0
  const labelBold = !!pageOptions.value?.mcqOptions?.labelBold
  const optionBold = !!pageOptions.value?.mcqOptions?.optionBold
  const checkboxShowLabel = !!pageOptions.value?.mcqOptions?.checkboxShowLabel
  const checkboxLabelType = pageOptions.value?.mcqOptions?.checkboxLabelType || 'letter'
  const headerEnabled = !!pageOptions.value?.printHeader?.enabled
  const headerMode = String(pageOptions.value?.printHeader?.mode || 'html')
  const headerHtml = String(pageOptions.value?.printHeader?.html || '')
  const headerImageUrl = String(pageOptions.value?.printHeader?.imageUrl || '')
  const headerImageFit = String(pageOptions.value?.printHeader?.imageFit || 'contain')
  const headerAutoFit = pageOptions.value?.printHeader?.autoFit !== false
  const headerHeightRaw = Number(pageOptions.value?.printHeader?.heightPt)
  const headerHeightPt = Number.isFinite(headerHeightRaw) && headerHeightRaw > 0 ? headerHeightRaw : 120
  const hasHeaderContent = headerMode === 'image' ? !!headerImageUrl.trim() : !!headerHtml.trim()
  const bodyPadPx = headerEnabled && hasHeaderContent ? 0 : 20
  // Extra margin the user can dial in from Settings → Header → Extra top margin
  const extraMarginMm = (() => {
    const v = Number(pageOptions.value?.printHeader?.pageMarginTopMm)
    return Number.isFinite(v) && v > 0 ? v : 0
  })()
  // Initial height for the spacer (gets overridden by accurate pixel measurement at runtime)
  const initialSpacerPt = headerAutoFit ? 100 : headerHeightPt

  const footerEnabled = !!pageOptions.value?.printFooter?.enabled
  const footerMode = String(pageOptions.value?.printFooter?.mode || 'html')
  const footerHtml = String(pageOptions.value?.printFooter?.html || '')
  const footerTextFontSizePt = Number(pageOptions.value?.printFooter?.textFontSizePt) || 12
  const footerTextColor = String(pageOptions.value?.printFooter?.textColor || '#000000')
  const footerImageUrl = String(pageOptions.value?.printFooter?.imageUrl || '')
  const footerImageFit = String(pageOptions.value?.printFooter?.imageFit || 'contain')
  const footerAutoFit = pageOptions.value?.printFooter?.autoFit !== false
  const footerHeightRaw = Number(pageOptions.value?.printFooter?.heightPt)
  const footerHeightPt = Number.isFinite(footerHeightRaw) && footerHeightRaw > 0 ? footerHeightRaw : 90
  const showPageNumbers = !!pageOptions.value?.printFooter?.showPageNumbers
  const footerReserveSpace = pageOptions.value?.printFooter?.reserveSpace !== false
  const footerSingleLine = pageOptions.value?.printFooter?.singleLine === true
  const footerTopBorder = pageOptions.value?.printFooter?.showTopBorder === true
  const hasFooterContent = footerMode === 'image'
    ? !!footerImageUrl.trim()
    : (!!footerHtml.trim() || showPageNumbers)
  const pageNumberPosition = String(pageOptions.value?.printFooter?.pageNumberPosition || 'bottom-center')
  const pageNumberFormat = String(pageOptions.value?.printFooter?.pageNumberFormat || 'page')
  const pageNumberFontSize = Number(pageOptions.value?.printFooter?.pageNumberFontSize) || 10
  const pageNumberColor = String(pageOptions.value?.printFooter?.pageNumberColor || '#000000')
  const extraFooterMarginMm = (() => {
    const v = Number(pageOptions.value?.printFooter?.pageMarginBottomMm)
    return Number.isFinite(v) && v > 0 ? v : 0
  })()
  const initialFooterSpacerPt = footerAutoFit ? 80 : footerHeightPt
  
  const sep = pageOptions.value?.questionSeparator || {}
  const sepEnabled = !!sep.enabled
  const sepStyle = String(sep.lineStyle || 'solid')
  const sepColor = String(sep.color || '#1f3a5a')
  const sepThicknessRaw = Number(sep.thicknessPt)
  const sepThicknessPt = Number.isFinite(sepThicknessRaw) ? sepThicknessRaw : 1
  const sepBeforeRaw = Number(sep.spaceBeforePt)
  const sepBeforePt = Number.isFinite(sepBeforeRaw) ? sepBeforeRaw : 8
  const sepAfterRaw = Number(sep.spaceAfterPt)
  const sepAfterPt = Number.isFinite(sepAfterRaw) ? sepAfterRaw : 12
  const sepInlineStyle =
    'margin-top:' + sepBeforePt + 'pt; margin-bottom:' + sepAfterPt + 'pt; border-bottom:' + sepThicknessPt + 'pt ' + sepStyle + ' ' + sepColor + ';'

  html += '<style>'
  html += '@page { size: A4; margin: 12mm; }'
  html += ' body { font-family: Arial, sans-serif; font-size: 12pt; line-height: 1.5; margin: 0; padding: ' + bodyPadPx + 'px; }'
  html += ' h1 { margin: 0 0 14pt; font-size: 22pt; }'
  html += ' h2 { margin: 14pt 0 6pt; font-size: 15pt; text-decoration: underline; }'
  // Header: touches the top of the physical page printable area.
  html += ' .print-header { position: fixed; top: 0; left: 0; right: 0; z-index: 999; background: white; overflow: hidden; box-sizing: border-box; padding: 0 0; }'
  html += ' .print-footer { position: fixed; bottom: 0; left: 0; right: 0; z-index: 999; background: white; overflow: hidden; box-sizing: border-box; padding: 0 0; }'
  // Basic reset for the layout table
  html += ' table.print-layout { width: 100%; border: none; border-spacing: 0; border-collapse: collapse; }'
  html += ' table.print-layout td { padding: 0; border: none; vertical-align: top; }'

  html += ' .section-header { position: relative; }'
  html += ' .section-instructions { margin: 0 0 6pt; font-weight: 600; }'
  html += ' .section-total-text { margin: 0 0 10pt; color: #444; font-size: 11pt; }'
  html += ' .section-total-fly { position: absolute; }'
  html += ' .section-total-box { width: 54px; border: 2px solid #1f3a5a; display: flex; flex-direction: column; margin: 0 0 10pt; }'
  html += ' .section-total-box-top { height: 22px; border-bottom: 2px solid rgba(31, 58, 90, 0.4); }'
  html += ' .section-total-box-value { height: 36px; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 16pt; }'
  html += ' .question { margin-bottom: 18pt; position: relative; }'
  html += ' .question-fly-image { position: absolute; z-index: 1; pointer-events: none; }'
  html += ' .question-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 6pt; font-weight: bold; }'
  html += ' .question-content { margin-bottom: 8pt; }'
  html += ' .question-options { margin: 6pt 0 8pt; }'
  html += ' .question-options-grid { display: grid; grid-template-columns: repeat(' + mcqCols + ', minmax(0, 1fr)); gap: ' + mcqGapPt + 'pt; }'
  html += ' .option { display: flex; gap: ' + mcqLabelGapPt + 'pt; margin-bottom: 4pt; }'
  html += ' .option-label { min-width: 24px; font-weight: 600; }'
  html += ' .option-label { ' + (labelFontPt ? ('font-size:' + labelFontPt + 'pt;') : '') + (labelBold ? 'font-weight:700;' : '') + ' }'
  html += ' .option-text { ' + (optionFontPt ? ('font-size:' + optionFontPt + 'pt;') : '') + (optionBold ? 'font-weight:700;' : '') + ' }'
  html += ' .option-checkbox { width: 14px; height: 14px; border: 2px solid #333; box-sizing: border-box; margin-top: 2px; display: inline-block; }'
  html += ' .checkbox-label-text { margin-left: 6pt; }'
  html += ' .question-header-inline { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 6pt; font-weight: bold; }'
  html += ' .question-inline { display: inline-flex; align-items: baseline; }'
  html += ' .question-inline-number { margin-right: ' + inlineGapPt + 'pt; }'
  html += ' .answer-area { border-top: 1px solid #ccc; margin-top: 10pt; padding-top: 8pt; }'
  html += ' .answer-line { border-bottom: 1px solid #ccc; height: 18pt; margin-bottom: 6pt; }'
  html += ' .page-break { page-break-before: always; height: 0; }'
  html += ' .page-number { position: absolute; z-index: 1000; }'
  html += ' .page-number-content::after { content: counter(page); }'
  html += ' .page-number-content[data-format="page"]::after { content: counter(page); }'
  html += ' .page-number-content[data-format="page-of"]::after { content: "Page " counter(page) " of " counter(pages); }'
  html += ' .page-number-content[data-format="page-slash"]::after { content: counter(page) " / " counter(pages); }'
  html += ' .page-number-content[data-format="fraction"]::after { content: counter(page) " / " counter(pages); }'
  html += ' @media screen { .page-number-content::after, .page-number-content[data-format]::after { content: attr(data-preview); } }'
  html += ' @media print { .page-number-content::after, .page-number-content[data-format]::after { content: ""; } }'
  html += ' .abs-page-number { position: absolute; z-index: 5000; font-family: Arial, sans-serif; pointer-events: none; }'
  html += '</style>'
  html += '</head><body>'

  // Runtime measurement script:
  // - Waits for all images to fully load
  // - Measures the exact height of the header
  // - Applies that height + extraMargin to the #headerSpacer inside the <thead>
  // - This safely pushes content down on EVERY printed page.
  if ((headerEnabled && hasHeaderContent) || (footerEnabled && hasFooterContent)) {
    html += '<script>(function(){'
    html += '  window.__printReady = false;'
    html += '  function injectAbsPageNumbers(){'
    html += '    try {'
    html += '      var ENABLE = ' + (showPageNumbers ? 'true' : 'false') + ';'
    html += '      if (!ENABLE) return;'
    html += '      function mmToPx(mm){'
    html += '        var d = document.createElement("div");'
    html += '        d.style.cssText = "position:absolute;left:-9999px;top:0;height:" + mm + "mm;width:1px;";'
    html += '        document.body.appendChild(d);'
    html += '        var px = d.getBoundingClientRect().height || d.offsetHeight || 0;'
    html += '        d.remove();'
    html += '        return Math.max(1, px);'
    html += '      }'
    html += '      var PAGE_MARGIN_MM = 12;'
    html += '      var PAGE_H = mmToPx(297 - (PAGE_MARGIN_MM * 2));'
    html += '      var MARGIN = 0;'
    html += '      var docH = Math.max(document.body.scrollHeight, document.documentElement.scrollHeight);'
    html += '      var np = Math.max(1, Math.ceil(docH / PAGE_H));'
    html += '      // Printing pagination can create a final page even if scrollHeight is slightly smaller than an exact multiple.'
    html += '      // Add a small buffer to avoid missing the last page label.'
    html += '      if ((docH % PAGE_H) > (PAGE_H * 0.05)) np = np + 1;'
    html += '      try { document.body.style.minHeight = (np * PAGE_H) + "px"; } catch(e) {}'
    html += '      var fmt = "' + pageNumberFormat + '";'
    html += '      var pos = "' + pageNumberPosition + '";'
    html += '      var fontPt = ' + pageNumberFontSize + ';'
    html += '      var color = "' + pageNumberColor + '";'
    html += '      var footerH = 0;'
    html += '      try { var f = document.getElementById("printFooterRoot"); if (f) footerH = f.getBoundingClientRect().height || f.offsetHeight || 0; } catch(e) {}'
    html += '      var existing = document.querySelectorAll(".abs-page-number");'
    html += '      for (var k = 0; k < existing.length; k++) existing[k].remove();'
    html += '      for (var i = 1; i <= np; i++) {'
    html += '        var t = "";'
    html += '        if (fmt === "page") t = String(i);'
    html += '        else if (fmt === "page-of") t = "Page " + i + " of " + np;'
    html += '        else if (fmt === "page-slash") t = "Page " + i + " / " + np;'
    html += '        else t = i + " / " + np;'
    html += '        var el = document.createElement("div");'
    html += '        el.className = "abs-page-number";'
    html += '        el.textContent = t;'
    html += '        el.style.fontSize = fontPt + "pt";'
    html += '        el.style.color = color;'
    html += '        var pageTop = (i - 1) * PAGE_H;'
    html += '        var pageBottom = i * PAGE_H;'
    html += '        var top = (pageBottom - MARGIN) - 18;'
    html += '        if (footerH > 0 && pos.indexOf("bottom") === 0) { top = (pageBottom - MARGIN) - Math.min(footerH, 80) + 10; }'
    html += '        if (pos.indexOf("top") === 0) top = pageTop + MARGIN + 10;'
    html += '        el.style.top = top + "px";'
    html += '        if (pos.indexOf("left") !== -1) { el.style.left = "0"; el.style.right = "auto"; el.style.textAlign = "left"; }'
    html += '        else if (pos.indexOf("right") !== -1) { el.style.right = "0"; el.style.left = "auto"; el.style.textAlign = "right"; }'
    html += '        else { el.style.left = "0"; el.style.right = "0"; el.style.textAlign = "center"; }'
    html += '        document.body.appendChild(el);'
    html += '      }'
    html += '    } catch(e) {}'
    html += '  }'
    html += '  function doMeasure(){'
    html += '    try {'
    html += '      var h = document.getElementById("printHeaderRoot");'
    html += '      var hs = document.getElementById("headerSpacer");'
    html += '      if (h && hs) {'
    html += '        var hPx = h.offsetHeight;'
    html += '        var hExtraPx = Math.ceil(' + extraMarginMm + ' * 96 / 25.4);'
    html += '        hs.style.height = (hPx + hExtraPx) + "px";'
    html += '      }'
    html += '      if (' + (footerEnabled && hasFooterContent && footerReserveSpace ? 'true' : 'false') + ') {'
    html += '        var f = document.getElementById("printFooterRoot");'
    html += '        var fs = document.getElementById("footerSpacer");'
    html += '        if (f && fs) {'
    html += '          var fPx = f.offsetHeight;'
    html += '          var fExtraPx = Math.ceil(' + extraFooterMarginMm + ' * 96 / 25.4);'
    html += '          fs.style.height = (fPx + fExtraPx) + "px";'
    html += '        }'
    html += '      }'

    html += '      try {'
    html += '        var els = document.querySelectorAll(".page-number-content");'
    html += '        if (els && els.length) {'
    html += '          var total = Math.max(1, Math.ceil(document.documentElement.scrollHeight / window.innerHeight));'
    html += '          var fmt = "' + pageNumberFormat + '";'
    html += '          var pageNum = 1;'
    html += '          var preview = "";'
    html += '          if (fmt === "page") preview = String(pageNum);'
    html += '          else if (fmt === "page-of") preview = "Page " + pageNum + " of " + total;'
    html += '          else if (fmt === "page-slash") preview = "Page " + pageNum + " / " + total;'
    html += '          else preview = pageNum + " / " + total;'
    html += '          els.forEach(function(el){ el.setAttribute("data-preview", preview); });'
    html += '        }'
    html += '      } catch(e) {}'

    html += '    } catch(e) {}'
    html += '    window.__printReady = true;'
    html += '  }'
    html += '  try { window.addEventListener("beforeprint", injectAbsPageNumbers); } catch(e) {}'
    html += '  window.addEventListener("load", function(){'
    html += '    var imgs = document.querySelectorAll("img");'
    html += '    if (!imgs.length) { doMeasure(); return; }'
    html += '    var total = imgs.length, done = 0;'
    html += '    function onDone(){ if (++done >= total) doMeasure(); }'
    html += '    [].forEach.call(imgs, function(img){ if(img.complete){ onDone(); } else { img.onload = img.onerror = onDone; } });'
    html += '  });'
    html += '})();<' + '/script>\n'
  }


  if (headerEnabled && hasHeaderContent) {
    const headerInner = headerMode === 'image'
      ? (
        headerAutoFit
          ? ('<img src="' + headerImageUrl + '" style="width:100%; height:auto; object-fit:contain; display:block;" />')
          : ('<img src="' + headerImageUrl + '" style="width:100%; height:100%; object-fit:' + headerImageFit + '; display:block;" />')
      )
      : headerHtml
    html += '<div id="printHeaderRoot" class="print-header" style="' + (headerAutoFit ? '' : ('height:' + headerHeightPt + 'pt;')) + '">' + headerInner + '</div>'
  }

  if (footerEnabled && hasFooterContent) {
    const footerContentHtml = footerMode === 'image'
      ? (
        footerAutoFit
          ? ('<img src="' + footerImageUrl + '" style="width:100%; height:auto; object-fit:contain; display:block;" />')
          : ('<img src="' + footerImageUrl + '" style="width:100%; height:100%; object-fit:' + footerImageFit + '; display:block;" />')
      )
      : ('<div class="footer-text" style="font-size:' + footerTextFontSizePt + 'pt; color:' + footerTextColor + ';">' + footerHtml + '</div>')

    const footerBorderStyle = footerTopBorder ? 'border-top:1px solid rgba(0,0,0,0.25);' : ''

    let footerInner = footerContentHtml

    if (showPageNumbers) {
      const positionStyles = {
        'bottom-left': 'text-align:left; left:12mm; right:auto;',
        'bottom-center': 'text-align:center; left:0; right:0;',
        'bottom-right': 'text-align:right; right:12mm; left:auto;',
        'top-left': 'text-align:left; left:12mm; right:auto; top:0;',
        'top-center': 'text-align:center; left:0; right:0; top:0;',
        'top-right': 'text-align:right; right:12mm; left:auto; top:0;'
      }

      const position = pageNumberPosition.includes('top') ? 'top' : 'bottom'
      const style = positionStyles[pageNumberPosition] || positionStyles['bottom-center']

      if (footerSingleLine) {
        footerInner =
          '<div class="footer-line" style="display:flex; align-items:center; justify-content:space-between; gap:12px; padding:6mm 12mm;">' +
            '<div class="footer-line-left" style="flex:1; min-width:0;">' + footerContentHtml + '</div>' +
            '<div class="footer-line-right" style="flex:0 0 auto; white-space:nowrap; font-size:' + pageNumberFontSize + 'pt; color:' + pageNumberColor + ';">' +
              '<span class="page-number-content" data-format="' + pageNumberFormat + '"></span>' +
            '</div>' +
          '</div>'
      } else {
        footerInner +=
          '<div class="page-number" style="position:absolute; ' + position + ':0; ' + style + ' font-size:' + pageNumberFontSize + 'pt; color:' + pageNumberColor + '; padding:8mm 12mm;">' +
            '<span class="page-number-content" data-format="' + pageNumberFormat + '"></span>' +
          '</div>'
      }
    } else if (footerSingleLine) {
      footerInner = '<div class="footer-line" style="padding:6mm 12mm;">' + footerContentHtml + '</div>'
    }

    html += '<div id="printFooterRoot" class="print-footer" style="' + footerBorderStyle + (footerAutoFit ? '' : ('height:' + footerHeightPt + 'pt;')) + '">' + footerInner + '</div>'
  }

  // The master layout table forces the header spacer to repeat on every printed page
  html += '<table class="print-layout">'
  
  if (headerEnabled && hasHeaderContent) {
    html += '<thead><tr><td>'
    const extraPt = Math.ceil(extraMarginMm * 72 / 25.4)
    html += '<div id="headerSpacer" style="height: ' + (initialSpacerPt + extraPt) + 'pt;"></div>'
    html += '</td></tr></thead>'
  }
  
  html += '<tbody><tr><td>'

  if (examTitleEnabled) {
    html += '<h1>' + renderMathContent(examTitleTextRaw) + '</h1>'
  }

  const showMarks = !!pageOptions.value.showMarksPerQuestion
  let globalIndex = 0

  sections.value.forEach((section) => {
    const sectionQuestions = sampleQuestions.value.filter(q => getQuestionSectionId(q) === section.id)
    if (sectionQuestions.length === 0) return

    html += '<div class="section-header">'
    html += '<h2>' + renderMathContent(section.title) + '</h2>'
    if (section.instructions) {
      html += '<div class="section-instructions">' + renderMathContent(section.instructions) + '</div>'
    }
    html += renderSectionTotalHTML(sectionTotalMarks(section.id), pageOptions.value.sectionTotal)
    html += '</div>'

    sectionQuestions.forEach((question) => {
      globalIndex += 1
      const label = renderMathContent(formatQuestionLabel(globalIndex, pageOptions.value.questionNumbering))
      const breaksMap = pageOptions.value?.questionNumbering?.pageBreaksBefore || {}
      if (breaksMap[String(question?.id)]) {
        html += '<div class="page-break"></div>'
      }
      html += '<div class="question">'
      const img = question?.content?.image
      if (img) {
        const cfg = (typeof img === 'object') ? img : { url: String(img) }
        const url = cfg.url ? String(cfg.url) : ''
        if (url) {
          const w = Number(cfg.widthPt)
          const t = Number(cfg.topPt)
          const r = Number(cfg.rightPt)
          const o = Number(cfg.opacity)
          const wPt = Number.isFinite(w) ? w : 90
          const tPt = Number.isFinite(t) ? t : 0
          const rPt = Number.isFinite(r) ? r : 0
          const op = Number.isFinite(o) ? o : 1
          html += '<img class="question-fly-image" src="' + url + '" style="width:' + wPt + 'pt; top:' + tPt + 'pt; right:' + rPt + 'pt; opacity:' + op + ';" />'
        }
      }
      if (pageOptions.value.questionNumbering?.inlineWithText) {
        html += '<div class="question-header-inline">'
        html += '<div class="question-inline">'
        html += '<span class="question-inline-number">' + label + '</span>'
        html += '<span class="question-inline-text">' + renderMathContent(question.content.prompt) + '</span>'
        html += '</div>'
        html += showMarks ? ('<span>' + question.marks + ' marks</span>') : '<span></span>'
        html += '</div>'
      } else {
        html += '<div class="question-header">'
        html += '<span>' + label + '</span>'
        html += showMarks ? ('<span>' + question.marks + ' marks</span>') : '<span></span>'
        html += '</div>'
        html += '<div class="question-content">' + renderMathContent(question.content.prompt) + '</div>'
      }

      if (Array.isArray(question.content?.options) && question.content.options.length > 0) {
        const style = pageOptions.value?.mcqOptions?.labelStyle || 'letter'
        const gridClass = question.type === 'multiple_choice' ? 'question-options question-options-grid' : 'question-options'
        html += '<div class="' + gridClass + '">'
        question.content.options.forEach((opt, idx) => {
          html += '<div class="option">'
          if (question.type === 'multiple_choice' && style === 'checkbox') {
            html += '<span class="option-label"><span class="option-checkbox"></span>'
            if (checkboxShowLabel) {
              const lbl = checkboxLabelType === 'number'
                ? (idx + 1 + ')')
                : (checkboxLabelType === 'custom'
                  ? String(pageOptions.value?.mcqOptions?.customLabelTemplate || '{letter})')
                    .replaceAll('{i}', String(idx))
                    .replaceAll('{n}', String(idx + 1))
                    .replaceAll('{letter}', String.fromCharCode('A'.charCodeAt(0) + idx))
                  : (String.fromCharCode('A'.charCodeAt(0) + idx) + ')'))
              html += '<span class="checkbox-label-text">' + lbl + '</span>'
            }
            html += '</span>'
          } else {
            const lbl = question.type === 'multiple_choice' ? mcqOptionLabel(idx) : optionLabel(idx)
            html += '<span class="option-label">' + lbl + '</span>'
          }
          html += '<span class="option-text">' + renderMathContent(opt) + '</span>'
          html += '</div>'
        })
        html += '</div>'
      }

      const linesCount = getPrintAnswerLines(question)
      if (linesCount > 0) {
        html += '<div class="answer-area">'
        for (let i = 0; i < linesCount; i++) {
          html += '<div class="answer-line"></div>'
        }
        html += '</div>'
      }

      if (sepEnabled) {
        html += '<div class="question-separator" style="' + sepInlineStyle + '"></div>'
      }
      html += '</div>'
    })
  })
  
  html += '</td></tr></tbody>'

  if (footerEnabled && hasFooterContent && footerReserveSpace) {
    html += '<tfoot><tr><td>'
    const extraPt = Math.ceil(extraFooterMarginMm * 72 / 25.4)
    html += '<div id="footerSpacer" style="height: ' + (initialFooterSpacerPt + extraPt) + 'pt;"></div>'
    html += '</td></tr></tfoot>'
  }

  html += '</table>'
  html += '</body></html>'
  return html
}

function renderMathContent(content) {
  if (!content) return ''

  let text = String(content)
    .replace(/\[\s*cite\s*:\s*\d+\s*\]/gi, '')
    .replace(/\s{2,}/g, ' ')
    .trim()

  text = text.replace(/\$\$([^$]+)\$\$/g, (match, math) => {
    try {
      return renderToString(math, { throwOnError: false, displayMode: true })
    } catch (e) {
      return match
    }
  })

  text = text.replace(/\$([^$]+)\$/g, (match, math) => {
    try {
      return renderToString(math, { throwOnError: false })
    } catch (e) {
      return match
    }
  })

  return text
}

function optionLabel(idx) {
  const charCode = 'A'.charCodeAt(0) + idx
  return String.fromCharCode(charCode) + ') '
}

function mcqOptionLabel(idx) {
  const style = pageOptions.value?.mcqOptions?.labelStyle || 'letter'
  const n = idx + 1
  const letter = String.fromCharCode('A'.charCodeAt(0) + idx)

  if (style === 'number') return n + ') '
  if (style === 'custom') {
    const tpl = pageOptions.value?.mcqOptions?.customLabelTemplate || '{letter})'
    return String(tpl)
      .replaceAll('{i}', String(idx))
      .replaceAll('{n}', String(n))
      .replaceAll('{letter}', String(letter)) + ' '
  }

  // checkbox handled separately
  return letter + ') '
}

function getPrintAnswerLines(question) {
  const type = question?.type

  // For MCQ / True-False, usually no answer lines are needed
  if (type === 'multiple_choice' || type === 'true_false') return 0

  const marks = Number(question?.marks || 1)
  if (!Number.isFinite(marks)) return 3

  // Short answers: keep it compact
  if (marks <= 1) return 2
  if (marks <= 3) return 3
  return 4
}

// Watch for changes and auto-save
watch(
  [sampleQuestions, pageOptions],
  async () => {
    await nextTick()
    await savePageState()
  },
  { deep: true }
)

onMounted(async () => {
  await loadPageState()
})
</script>

<style scoped>
.exam-test-page {
  min-height: 100vh;
  background: #fafafa;
  padding: 20px;
}

.test-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  background: white;
  padding: 16px 24px;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.test-header h1 {
  margin: 0;
  font-size: 24px;
  font-weight: 600;
  color: #333;
}

.header-actions {
  display: flex;
  gap: 12px;
}

.sections-summary {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin: 0 0 16px;
}

.section-chip {
  background: white;
  border: 1px solid #e6e6e6;
  border-radius: 8px;
  padding: 8px 12px;
  display: flex;
  gap: 10px;
  align-items: center;
}

.section-chip-title {
  font-weight: 600;
  color: #333;
}

.section-chip-marks {
  color: #666;
  font-size: 0.9em;
}

.questions-container {
  background: white;
  border-radius: 8px;
  padding: 24px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.question-row {
  position: relative;
}

.page-break-marker {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 0;
  margin: 6px 0 14px;
  color: #777;
  font-size: 12px;
  user-select: none;
}

.page-break-marker-line {
  flex: 1;
  height: 1px;
  background: repeating-linear-gradient(
    90deg,
    #d6d6d6,
    #d6d6d6 6px,
    transparent 6px,
    transparent 12px
  );
}

.page-break-marker-label {
  background: #f7f7f7;
  border: 1px solid #e1e1e1;
  border-radius: 999px;
  padding: 4px 10px;
  letter-spacing: 0.08em;
}

.question-row-actions {
  position: absolute;
  top: 0;
  right: 0;
  z-index: 2;
  display: flex;
  align-items: center;
  gap: 2px;
}

.image-preview {
  border: 1px solid #e6e6e6;
  border-radius: 8px;
  padding: 10px;
  background: #fafafa;
  margin-bottom: 12px;
  max-height: 240px;
  overflow: auto;
}

.image-preview img {
  max-width: 100%;
  height: auto;
  display: block;
}

.question-separator {
  width: 100%;
}

.options-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 16px;
}

.sections-editor {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.section-row {
  display: flex;
  align-items: center;
  gap: 10px;
}

.section-marks {
  min-width: 110px;
  color: #666;
}

/* AI Dialog Styles */
.ai-dialog {
  max-width: 900px;
  max-height: 90vh;
}

.step-content {
  padding: 20px;
}

.form-section {
  display: flex;
  flex-direction: column;
  gap: 16px;
  margin-bottom: 20px;
}

.prompt-preview {
  background: #f5f5f5;
  border: 1px solid #ddd;
  border-radius: 4px;
  padding: 16px;
  margin-top: 16px;
  max-height: 300px;
  overflow-y: auto;
}

.paste-actions {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 12px;
}

.paste-error {
  font-size: 12px;
}

.preview-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.preview-table {
  max-height: 400px;
}

.question-preview-text {
  max-width: 300px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* Modern Toolbar Styles */
.modern-toolbar {
  position: sticky;
  top: 0;
  z-index: 1000;
  background: linear-gradient(135deg, #1976d2 0%, #1565c0 100%);
  color: white;
  padding: 12px 20px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  display: flex;
  justify-content: space-between;
  align-items: center;
  backdrop-filter: blur(10px);
  transition: all 0.3s ease;
}

.toolbar-left {
  display: flex;
  align-items: center;
}

.toolbar-right {
  display: flex;
  align-items: center;
  gap: 10px;
}

.toolbar-title {
  display: flex;
  align-items: center;
  gap: 12px;
}

.title-text {
  font-size: 18px;
  font-weight: 600;
  color: white;
  margin: 0;
}

.toolbar-group {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 0 4px;
  border-right: none;
}

.toolbar-group:last-child {
  border-right: none;
}

/* Enhanced button styles */
.modern-toolbar .q-btn {
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  transition: all 0.3s ease;
}

.modern-toolbar .q-btn:hover {
  background: rgba(255, 255, 255, 0.2);
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.modern-toolbar .q-btn--round {
  width: 48px;
  height: 48px;
}

.modern-toolbar .q-btn-dropdown {
  background: rgba(255, 255, 255, 0.12);
}

.modern-toolbar .q-menu {
  color: #1f2937;
}

/* Responsive Design */
@media (max-width: 768px) {
  .modern-toolbar {
    padding: 8px 12px;
    flex-wrap: wrap;
    gap: 8px;
  }

  .toolbar-left {
    width: 100%;
    justify-content: center;
    margin-bottom: 8px;
  }

  .toolbar-right {
    width: 100%;
    justify-content: center;
    flex-wrap: wrap;
  }

  .toolbar-group {
    flex: 1;
    min-width: 0;
    justify-content: center;
    border-right: none;
    border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    padding-bottom: 8px;
    margin-bottom: 8px;
  }

  .toolbar-group:last-child {
    border-bottom: none;
    margin-bottom: 0;
  }

  .modern-toolbar .q-btn--round {
    width: 44px;
    height: 44px;
  }

  .title-text {
    font-size: 16px;
  }

  .test-header {
    flex-direction: column;
    gap: 16px;
    align-items: stretch;
  }

  .header-actions {
    justify-content: center;
  }

  .ai-dialog {
    max-width: 100vw;
    max-height: 100vh;
  }
}

/* First/Last Page Dialog Styles */
.first-last-page-dialog {
  height: 100vh;
  display: flex;
  flex-direction: column;
}

.page-section {
  background: white;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  border: 1px solid #e0e0e0;
}

.section-header {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 20px;
  padding-bottom: 12px;
  border-bottom: 2px solid #f0f0f0;
}

.section-title {
  font-size: 18px;
  font-weight: 600;
  color: #333;
}

.preview-section {
  background: white;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  border: 1px solid #e0e0e0;
}

.preview-container {
  display: flex;
  gap: 20px;
  flex-wrap: wrap;
  justify-content: center;
}

.preview-page {
  flex: 1;
  min-width: 300px;
  max-width: 400px;
  background: #f8f9fa;
  border: 2px solid #dee2e6;
  border-radius: 8px;
  padding: 20px;
  position: relative;
  min-height: 400px;
}

.preview-label {
  position: absolute;
  top: -12px;
  left: 20px;
  background: #1976d2;
  color: white;
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
}

.first-page-preview .preview-label {
  background: #1976d2;
}

.last-page-preview .preview-label {
  background: #9c27b0;
}

.preview-content {
  margin-top: 20px;
  text-align: center;
}

.title-preview h1 {
  font-size: 24px;
  margin-bottom: 16px;
  color: #333;
}

.title-preview h2 {
  font-size: 18px;
  color: #666;
  margin: 0;
}

.cover-preview h1 {
  font-size: 28px;
  margin-bottom: 16px;
  color: #333;
}

.cover-preview p {
  font-size: 16px;
  color: #666;
  margin-bottom: 20px;
}

.cover-image {
  max-width: 100%;
  height: auto;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.custom-preview {
  font-size: 16px;
  color: #333;
  line-height: 1.6;
}

.last-page-preview h2 {
  font-size: 22px;
  margin-bottom: 16px;
  color: #333;
}

.last-page-preview p {
  font-size: 16px;
  color: #666;
  margin: 0;
}
</style>
