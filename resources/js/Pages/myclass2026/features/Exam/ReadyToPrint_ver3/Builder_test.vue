<template>
  <Head :title="pageTitle" />
  <div class="exam-test-page">
    <!-- Quasar Toolbar -->
    <q-toolbar class="bg-primary text-white exam-toolbar">
      <!-- Title -->
      <q-icon name="quiz" size="md" class="q-mr-sm" />
      <q-toolbar-title shrink>
        <q-input
          v-if="editingTitle"
          dense
          outlined
          dark
          v-model="pageOptions.examTitle.text"
          label="Exam title"
          @blur="editingTitle = false; savePageState()"
          @keyup.enter="editingTitle = false; savePageState()"
          class="title-input"
        />
        <span v-else @dblclick="editingTitle = true" class="cursor-pointer">
          {{ pageOptions.examTitle?.text || 'Exam Builder' }}
        </span>
      </q-toolbar-title>

      <q-space />

      <q-btn
        flat
        dense
        icon="print"
        label="New Print"
        @click="printNewDirect"
        :loading="openingPrintHtml"
        class="q-mr-xs"
      />

      <q-btn
        flat
        dense
        icon="print"
        label="Old Print"
        @click="printOldDirect"
        class="q-mr-xs"
      />

      <q-btn
        flat
        dense
        icon="picture_as_pdf"
        label="PDF"
        @click="downloadServerPdf"
        :loading="pdfGenerating"
        class="q-mr-xs"
      />

      <q-btn
        flat
        dense
        icon="add_circle"
        label="New"
        @click="handleCreateNewExam"
        class="q-mr-xs"
      />

      <q-btn
        flat
        dense
        icon="content_copy"
        label="Duplicate"
        @click="duplicateCurrentExam"
        class="q-mr-xs"
      />

      <q-btn-dropdown flat dense icon="settings" label="Settings" class="q-mr-xs">
        <q-list dense style="min-width: 240px">
          <q-item>
            <q-item-section avatar><q-icon name="picture_as_pdf" /></q-item-section>
            <q-item-section>
              <q-toggle
                v-model="pdfPreviewMode"
                label="PDF View"
                dense
              />
            </q-item-section>
          </q-item>

          <q-item v-if="pdfPreviewMode">
            <q-item-section avatar><q-icon name="edit" /></q-item-section>
            <q-item-section>
              <q-toggle
                v-model="pdfInlineEditMode"
                label="Inline Edit (PDF)"
                dense
              />
            </q-item-section>
          </q-item>
          <q-separator />
          <q-item clickable v-close-popup @click="optionsOpen = true">
            <q-item-section avatar><q-icon name="tune" /></q-item-section>
            <q-item-section>
              <q-item-label>Page Settings</q-item-label>
            </q-item-section>
          </q-item>
          <q-item clickable v-close-popup @click="firstLastPageOpen = true">
            <q-item-section avatar><q-icon name="auto_stories" /></q-item-section>
            <q-item-section>
              <q-item-label>Cover Pages</q-item-label>
            </q-item-section>
          </q-item>
          <q-item clickable v-close-popup @click="answerKeyOpen = true">
            <q-item-section avatar><q-icon name="vpn_key" /></q-item-section>
            <q-item-section>
              <q-item-label>Answer Key</q-item-label>
            </q-item-section>
          </q-item>
          <q-separator />
          <q-item clickable v-close-popup @click="settingsPanelOpen = true">
            <q-item-section avatar><q-icon name="tune" /></q-item-section>
            <q-item-section>
              <q-item-label>Settings</q-item-label>
              <q-item-label caption>Reusable settings panel</q-item-label>
            </q-item-section>
          </q-item>
          <q-separator />
          <q-item clickable v-close-popup @click="generalAIPromptDialogOpen = true">
            <q-item-section avatar><q-icon name="auto_awesome" /></q-item-section>
            <q-item-section>
              <q-item-label>Generate AI Prompt</q-item-label>
              <q-item-label caption>General prompt with all settings & validation</q-item-label>
            </q-item-section>
          </q-item>
          <q-separator />
          <q-item clickable v-close-popup @click="isEditMode = !isEditMode">
            <q-item-section avatar><q-icon :name="isEditMode ? 'edit_off' : 'edit'" /></q-item-section>
            <q-item-section>
              <q-item-label>{{ isEditMode ? 'Exit Edit Mode' : 'Edit Mode' }}</q-item-label>
            </q-item-section>
          </q-item>
        </q-list>
      </q-btn-dropdown>

      <!-- Single dropdown for remaining actions -->
      <q-btn-dropdown flat dense icon="menu" label="Actions" class="q-mr-xs">
        <q-list dense style="min-width: 260px">
          <q-item clickable v-close-popup @click="handleSaveExam">
            <q-item-section avatar><q-icon name="save" /></q-item-section>
            <q-item-section>
              <q-item-label>Save</q-item-label>
            </q-item-section>
          </q-item>
          <q-item clickable v-close-popup @click="openSaveAsDialog">
            <q-item-section avatar><q-icon name="save_as" /></q-item-section>
            <q-item-section>
              <q-item-label>Save As</q-item-label>
            </q-item-section>
          </q-item>
          <q-item clickable v-close-popup @click="duplicateCurrentExam">
            <q-item-section avatar><q-icon name="content_copy" /></q-item-section>
            <q-item-section>
              <q-item-label>Duplicate (Create Copy)</q-item-label>
            </q-item-section>
          </q-item>

          <q-separator />

          <JsonImportExportActions
            v-model:sample-questions="sampleQuestions"
            v-model:page-options="pageOptions"
            v-model:sections="sections"
            v-model:question-section-map="questionSectionMap"
          />

          <q-separator />

          <q-item clickable v-close-popup @click="openFileManagerDialog">
            <q-item-section avatar><q-icon name="folder_open" /></q-item-section>
            <q-item-section>
              <q-item-label>Manage Files</q-item-label>
            </q-item-section>
          </q-item>
          <q-item clickable v-close-popup @click="openCopyFromDialog">
            <q-item-section avatar><q-icon name="content_copy" /></q-item-section>
            <q-item-section>
              <q-item-label>Copy From Saved</q-item-label>
            </q-item-section>
          </q-item>

          <q-separator />

          <q-item clickable v-close-popup @click="openSmartExamDialog">
            <q-item-section avatar><q-icon name="auto_awesome" /></q-item-section>
            <q-item-section>
              <q-item-label>Smart Exam Generator</q-item-label>
            </q-item-section>
          </q-item>
          <q-item clickable v-close-popup @click="openQuickImportDialog">
            <q-item-section avatar><q-icon name="bolt" /></q-item-section>
            <q-item-section>
              <q-item-label>Quick Import</q-item-label>
            </q-item-section>
          </q-item>
          <q-item clickable v-close-popup @click="openAIDialog">
            <q-item-section avatar><q-icon name="smart_toy" /></q-item-section>
            <q-item-section>
              <q-item-label>Import AI Questions</q-item-label>
            </q-item-section>
          </q-item>
          <q-item clickable v-close-popup @click="openFullExamDialog">
            <q-item-section avatar><q-icon name="psychology" /></q-item-section>
            <q-item-section>
              <q-item-label>Generate Full Exam</q-item-label>
            </q-item-section>
          </q-item>
          <q-item clickable v-close-popup @click="openAIChatDialog">
            <q-item-section avatar><q-icon name="chat" /></q-item-section>
            <q-item-section>
              <q-item-label>AI Chat</q-item-label>
            </q-item-section>
          </q-item>

          <q-separator />

          <q-item clickable v-close-popup @click="forceRegenerateHtml">
            <q-item-section avatar><q-icon name="refresh" /></q-item-section>
            <q-item-section>
              <q-item-label>Regenerate HTML</q-item-label>
            </q-item-section>
          </q-item>
          <q-item clickable v-close-popup @click="openValidationDialog">
            <q-item-section avatar><q-icon name="fact_check" /></q-item-section>
            <q-item-section>
              <q-item-label>Validate Questions</q-item-label>
            </q-item-section>
          </q-item>
          <q-item clickable v-close-popup @click="copyQuestionsToClipboard">
            <q-item-section avatar><q-icon name="content_copy" /></q-item-section>
            <q-item-section>
              <q-item-label>Copy Questions</q-item-label>
            </q-item-section>
          </q-item>
          <q-item clickable v-close-popup @click="updateQuestionsFromClipboard">
            <q-item-section avatar><q-icon name="content_paste" /></q-item-section>
            <q-item-section>
              <q-item-label>Paste Questions</q-item-label>
            </q-item-section>
          </q-item>
        </q-list>
      </q-btn-dropdown>
    </q-toolbar>

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

    <!-- Settings Panel -->
    <SettingsPanel
      v-model="settingsPanelOpen"
      :page-options="pageOptions"
      @update:page-options="pageOptions = $event"
      @save="savePageState"
    />

    <!-- General AI Prompt Dialog -->
    <AIPromptGenerator
      v-model="generalAIPromptDialogOpen"
      :page-options="pageOptions"
      :sample-questions="sampleQuestions"
    />

    <!-- Hidden ExamFileManager for dialog functionality -->
    <ExamFileManager
      ref="fileManagerRef"
      :show-save-button="false"
      :show-manage-button="false"
      :has-unsaved-changes="hasUnsavedChanges"
      :auto-save-enabled="autoSaveEnabled"
      @save="handleSaveExam"
      @saveAs="handleSaveAs"
      @load="handleLoadExam"
      @delete="handleDeleteExam"
      @refresh="handleRefreshFiles"
      @createNew="handleCreateNewExam"
      @toggle-auto-save="toggleAutoSave"
    />

    <div v-if="pdfPreviewMode && !pdfInlineEditMode" class="pdf-preview-container">
      <iframe
        v-if="pdfPreviewHtml"
        :srcdoc="pdfPreviewHtml"
        class="pdf-preview-iframe"
      />
    </div>

    <template v-else>
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
      <div :class="['questions-container', (pdfPreviewMode ? 'questions-container--pdf' : '')]">
        <div
          v-for="(question, index) in printSequence"
          :key="question.id"
          :class="['question-row', isEditMode ? 'question-row--edit' : '']"
        >
        <!-- Edit mode toolbar: section selector + 3-dot menu -->
        <div class="question-edit-bar" v-if="isEditMode">
          <div class="question-edit-bar__left">
            <span class="question-edit-bar__num text-grey-7">Q{{ index + 1 }}</span>
            <q-select
              dense
              borderless
              emit-value
              map-options
              :options="sectionOptions"
              :model-value="getQuestionSectionId(question)"
              @update:model-value="(val) => setQuestionSection(question, val)"
              style="min-width: 160px"
              class="question-edit-bar__section"
            />
          </div>
          <div class="question-edit-bar__right">
            <q-btn-dropdown
              dense
              flat
              no-icon-animation
              dropdown-icon="more_vert"
              color="grey-7"
              size="sm"
              unelevated
            >
              <q-list dense style="min-width: 200px">
                <q-item clickable v-close-popup @click="editQuestionContent(question)">
                  <q-item-section avatar><q-icon name="edit" size="xs" color="primary" /></q-item-section>
                  <q-item-section>Edit</q-item-section>
                </q-item>
                <q-item clickable v-close-popup @click="duplicateQuestion(question)">
                  <q-item-section avatar><q-icon name="content_copy" size="xs" color="teal" /></q-item-section>
                  <q-item-section>Copy / Duplicate</q-item-section>
                </q-item>
                <q-item clickable v-close-popup @click="openImageDialog(question)">
                  <q-item-section avatar><q-icon name="image" size="xs" color="indigo" /></q-item-section>
                  <q-item-section>{{ question?.content?.image ? 'Edit Image' : 'Add Image' }}</q-item-section>
                </q-item>
                <q-separator />
                <q-item clickable v-close-popup @click="togglePageBreakBefore(question)">
                  <q-item-section avatar><q-icon name="vertical_align_top" size="xs" color="orange-8" /></q-item-section>
                  <q-item-section>{{ isPageBreakBefore(question) ? 'Remove' : 'Add' }} Page Break Before</q-item-section>
                </q-item>
                <q-item clickable v-close-popup @click="togglePageBreakAfter(question)">
                  <q-item-section avatar><q-icon name="vertical_align_bottom" size="xs" color="orange-8" /></q-item-section>
                  <q-item-section>{{ isPageBreakAfter(question) ? 'Remove' : 'Add' }} Page Break After</q-item-section>
                </q-item>
                <q-separator />
                <q-item clickable v-close-popup @click="confirmDeleteQuestion(question.id)" class="text-negative">
                  <q-item-section avatar><q-icon name="delete" size="xs" color="negative" /></q-item-section>
                  <q-item-section>Delete</q-item-section>
                </q-item>
              </q-list>
            </q-btn-dropdown>
          </div>
        </div>

        <div v-if="isPageBreakBefore(question)" class="page-break-marker">
          <div class="page-break-marker-line"></div>
          <div class="page-break-marker-label">PAGE BREAK</div>
          <div class="page-break-marker-line"></div>

        </div>

        <QuestionDisplay
          :question="question"
          :question-number="index + 1"
          :total-questions="printSequence.length"
          :force-essay="isSectionForcingEssay(getQuestionSectionId(question))"
          :show-marks="pageOptions.showMarksPerQuestion"
          :mcq-options="pageOptions.mcqOptions"
          :answer-lines="getPrintAnswerLines(question)"
          :answer-line-style="pageOptions.answerLines"
          :math-render="renderMathContent"
        />

        <div v-if="isPageBreakAfter(question)" class="page-break-marker">
          <div class="page-break-marker-line"></div>
          <div class="page-break-marker-label">PAGE BREAK</div>
          <div class="page-break-marker-line"></div>
        </div>
      </div>
    </div>
  </template>

    <!-- Settings Dialog -->
    <q-dialog v-model="optionsOpen">
      <q-card style="min-width: 820px; max-width: 95vw;">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">Settings</div>
          <q-space />
          <q-select
            v-model="selectedSettingsPreset"
            :options="settingsPresets"
            label="Preset"
            dense
            outlined
            style="min-width: 150px;"
            emit-value
            map-options
            @update:model-value="applySettingsPreset"
          >
            <template v-slot:append>
              <q-btn
                flat
                dense
                icon="add"
                @click.stop="saveSettingsAsPreset"
              />
              <q-btn
                v-if="selectedSettingsPreset !== 'default'"
                flat
                dense
                icon="delete"
                @click.stop="deleteSettingsPreset"
              />
            </template>
          </q-select>
          <q-btn
            color="primary"
            icon="save"
            label="Save Settings"
            @click="savePageState"
          />
          <q-btn
            color="primary"
            icon="print"
            label="Print"
            @click="openFullscreenPrint"
          />
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
          <q-tab name="answerKey" icon="vpn_key" label="Answer Key" />
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
                @dblclick="editExamTitleInline"
                @blur="savePageState"
              />

              <q-toggle
                v-model="pageOptions.showMarksPerQuestion"
                label="Show marks for every question"
                @update:model-value="savePageState"
              />

              <q-toggle
                v-model="pageOptions.showExplanationUnderQuestion"
                label="Show explanation under each question"
                @update:model-value="savePageState"
              />

              <q-toggle
                v-model="pageOptions.showCorrectAnswerUnderQuestion"
                label="Show correct answer under each question"
                @update:model-value="savePageState"
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
                v-model="pageOptions.paginationMode"
                label="Pagination mode"
                hint="Strict enforces conservative page-break rules; Flex allows tighter layout."
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

              <div v-if="pageOptions.printFooter.enabled">
                <q-tabs
                  v-model="footerSettingsTab"
                  dense
                  class="text-grey-8"
                  active-color="primary"
                  indicator-color="primary"
                  align="left"
                  inline-label
                >
                  <q-tab name="layout" icon="view_day" label="Layout" />
                  <q-tab name="content" icon="edit_note" label="Content" />
                  <q-tab name="pageNumbers" icon="format_list_numbered" label="Page Numbers" />
                </q-tabs>

                <q-separator class="q-mt-sm" />

                <q-tab-panels v-model="footerSettingsTab" animated>
                  <q-tab-panel name="layout">
                    <div class="options-grid">
                      <div class="row items-center q-col-gutter-sm">
                        <div class="col-auto">
                          <q-btn
                            color="primary"
                            icon="auto_fix_high"
                            label="Recommended: 1-line + Top align"
                            @click="applyRecommendedFooterOneLineTopAlign"
                          />
                        </div>
                      </div>

                      <q-toggle
                        v-model="pageOptions.printFooter.reserveSpace"
                        label="Reserve space for footer (recommended)"
                        @update:model-value="savePageState"
                      />

                      <q-toggle
                        v-model="pageOptions.printFooter.singleLine"
                        label="Single-line footer (content + page number)"
                        @update:model-value="savePageState"
                      />

                      <q-toggle
                        v-model="pageOptions.printFooter.singleLineTopAlign"
                        label="Top align (single-line)"
                        @update:model-value="savePageState"
                      />

                      <q-toggle
                        v-model="pageOptions.printFooter.showTopBorder"
                        label="Show a line above footer"
                        @update:model-value="savePageState"
                      />

                      <q-toggle
                        v-model="pageOptions.printFooter.autoFit"
                        label="Auto-fit footer height (recommended)"
                        @update:model-value="savePageState"
                      />

                      <q-input
                        dense
                        outlined
                        type="number"
                        v-model.number="pageOptions.printFooter.heightPt"
                        label="Footer height (pt) — used when Auto-fit is off"
                        @blur="savePageState"
                      />

                      <q-input
                        dense
                        outlined
                        type="number"
                        min="0"
                        v-model.number="pageOptions.printFooter.pageMarginBottomMm"
                        label="Extra bottom margin (mm) — space between content and footer"
                        hint="0 = automatic. Increase to push content further up."
                        @blur="savePageState"
                      />

                      <q-input
                        dense
                        outlined
                        type="number"
                        v-model.number="pageOptions.printFooter.bottomOffsetMm"
                        label="Footer bottom offset (mm) - move footer position"
                        hint="0 = bottom edge. Positive values move footer down, negative values move it up."
                        @blur="savePageState"
                      />

                      <q-select
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
                    </div>
                  </q-tab-panel>

                  <q-tab-panel name="content">
                    <div class="options-grid">
                      <div v-if="pageOptions.printFooter.mode === 'image'" class="row items-center q-col-gutter-sm">
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
                        v-if="pageOptions.printFooter.mode === 'image'"
                        dense
                        outlined
                        v-model="pageOptions.printFooter.imageUrl"
                        label="Footer image URL / Data URL"
                        @blur="savePageState"
                      />

                      <q-select
                        v-if="pageOptions.printFooter.mode === 'image'"
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
                        v-if="pageOptions.printFooter.mode !== 'image'"
                        outlined
                        type="textarea"
                        v-model="pageOptions.printFooter.html"
                        label="Footer HTML (paste here)"
                        rows="8"
                        @blur="savePageState"
                      />

                      <q-separator class="q-my-md" />

                      <div class="text-subtitle2 q-mb-sm">Last Page Footer</div>
                      
                      <q-toggle
                        v-model="pageOptions.printFooter.useLastPageText"
                        label="Use different footer text on last page"
                        @update:model-value="savePageState"
                      />

                      <q-input
                        v-if="pageOptions.printFooter.useLastPageText"
                        outlined
                        type="textarea"
                        v-model="pageOptions.printFooter.lastPageText"
                        label="Last page footer text (replaces regular footer text)"
                        hint="This text will appear only on the last page instead of the regular footer text"
                        rows="4"
                        @blur="savePageState"
                      />

                      <div v-if="pageOptions.printFooter.mode !== 'image'" class="row items-center q-col-gutter-sm q-mt-md">
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

                      <div v-if="pageOptions.printFooter.mode !== 'image'" class="row items-center q-col-gutter-sm">
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

                  <q-tab-panel name="pageNumbers">
                    <div class="options-grid">
                      <div class="row items-center q-col-gutter-sm">
                        <div class="col-auto">
                          <q-btn
                            color="primary"
                            icon="visibility"
                            label="Make page numbers inline + visible"
                            @click="applyVisibleInlinePageNumberPreset"
                          />
                        </div>
                      </div>

                      <q-toggle
                        v-model="pageOptions.printFooter.showPageNumbers"
                        label="Show page numbers in footer"
                        @update:model-value="savePageState"
                      />

                      <div v-if="pageOptions.printFooter.showPageNumbers" class="row items-center q-col-gutter-sm">
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
                          <q-toggle
                            dense
                            v-model="pageOptions.printFooter.applyOffsetToPageNumbers"
                            label="Apply footer offset to page numbers"
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
                            @update:model-value="(val) => { console.log('[PAGE NUMBER DEBUG - UI] Format changed to:', val); savePageState(); }"
                          />
                          <div style="font-size: 10px; color: #666; margin-top: 4px;">
                            Current: {{ pageOptions.printFooter.pageNumberFormat }}
                          </div>
                        </div>
                      </div>

                      <div v-if="pageOptions.printFooter.showPageNumbers" class="row items-center q-col-gutter-sm">
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
                        <div class="col-12 col-md-6">
                          <q-input
                            dense
                            outlined
                            type="number"
                            min="1"
                            v-model.number="pageOptions.printFooter.pageNumberStartAtQuestion"
                            label="Start numbering from question #"
                            hint="Page containing this question becomes the numbering start page"
                            @blur="savePageState"
                          />
                        </div>
                        <div class="col-12 col-md-6">
                          <q-input
                            dense
                            outlined
                            type="number"
                            min="1"
                            v-model.number="pageOptions.printFooter.pageNumberStartValue"
                            label="Start page number value"
                            hint="Example: 1 means selected question page shows page 1"
                            @blur="savePageState"
                          />
                        </div>
                      </div>
                    </div>
                  </q-tab-panel>
                </q-tab-panels>
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
                <q-toggle
                  dense
                  v-model="s.forceQuestionsToEssay"
                  label="Essay"
                  :title="'Force all questions in this section to be essay (hide options, add workspace)'"
                  class="q-mr-sm"
                  @update:model-value="savePageState"
                />
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
                  :icon="s.pageBreakBefore ? 'page_break' : 'format_pagebreak'"
                  :color="s.pageBreakBefore ? 'primary' : 'grey-7'"
                  :title="s.pageBreakBefore ? 'Section starts on new page' : 'Force section to start on new page'"
                  @click="s.pageBreakBefore = !s.pageBreakBefore; savePageState()"
                />
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
              
              <!-- Section formatting options -->
              <div class="section-formatting q-mt-md">
                <div class="text-subtitle2 q-mb-sm">Section Formatting</div>
                <div class="section-row" v-for="s in sections" :key="s.id">
                  <q-toggle
                    dense
                    v-model="s.lineBefore"
                    label="Line before"
                    @update:model-value="savePageState"
                  />
                  <q-toggle
                    dense
                    v-model="s.lineAfter"
                    label="Line after"
                    @update:model-value="savePageState"
                  />
                  <q-toggle
                    dense
                    v-model="s.pageBreakBefore"
                    label="Page break before"
                    @update:model-value="savePageState"
                  />
                </div>
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

          <q-tab-panel name="answerKey">
            <div class="options-grid">

              <!-- ── Enable / master toggle ── -->
              <q-toggle
                v-model="pageOptions.answerKey.enabled"
                label="Enable Answer Key"
                color="primary"
                @update:model-value="savePageState"
              />

              <!-- ── Live coverage summary ── -->
              <q-banner
                v-if="pageOptions.answerKey.enabled"
                rounded
                :class="answerKeyCoverage.missing === 0 ? 'bg-green-1 text-green-9' : 'bg-orange-1 text-orange-9'"
              >
                <template #avatar>
                  <q-icon :name="answerKeyCoverage.missing === 0 ? 'check_circle' : 'warning'" />
                </template>
                <span class="text-caption">
                  <strong>{{ answerKeyCoverage.covered }}</strong> of
                  <strong>{{ answerKeyCoverage.total }}</strong> questions have a correct answer set.
                  <span v-if="answerKeyCoverage.missing > 0">
                    — <strong>{{ answerKeyCoverage.missing }}</strong> missing.
                  </span>
                </span>
              </q-banner>

              <template v-if="pageOptions.answerKey.enabled">
                <q-separator class="full-width" />

                <!-- ── Template ── -->
                <div class="text-caption text-grey-7 full-width q-mt-xs">Answer Key Table</div>

                <q-select
                  dense
                  outlined
                  :options="[
                    { label: 'Full  (# · Question · Marks · Answer)', value: 'full' },
                    { label: 'Compact  (# · Correct Choice only)', value: 'compact_choice' },
                    { label: 'Compact  (# · Letter · Correct Option Text)', value: 'compact_letter_text' }
                  ]"
                  emit-value
                  map-options
                  v-model="pageOptions.answerKey.template"
                  label="Table template"
                  @update:model-value="savePageState"
                />

                <q-toggle
                  v-model="pageOptions.answerKey.mcqShowOptionText"
                  label="MCQ: show correct option text (instead of A/B/C/D)"
                  @update:model-value="savePageState"
                />

                <!-- ── Title ── -->
                <q-input
                  dense
                  outlined
                  v-model="pageOptions.answerKey.title"
                  label='Answer key heading (default: "Answer Key")'
                  clearable
                  @blur="savePageState"
                />

                <!-- ── Placement ── -->
                <q-separator class="full-width" />
                <div class="text-caption text-grey-7 full-width q-mt-xs">Placement</div>

                <q-toggle
                  v-model="pageOptions.answerKey.showAtEnd"
                  label="Append answer key at end of exam"
                  @update:model-value="savePageState"
                />

                <q-toggle
                  v-model="pageOptions.answerKey.pageBreakBefore"
                  label="Start answer key on a new page"
                  @update:model-value="savePageState"
                />

                <!-- ── Per-question inline display ── -->
                <q-separator class="full-width" />
                <div class="text-caption text-grey-7 full-width q-mt-xs">Inline (under each question)</div>

                <q-toggle
                  v-model="pageOptions.showCorrectAnswerUnderQuestion"
                  label="Show correct answer under each question"
                  @update:model-value="savePageState"
                />

                <q-toggle
                  v-model="pageOptions.showExplanationUnderQuestion"
                  label="Show explanation under each question"
                  @update:model-value="savePageState"
                />

                <!-- ── Footer note ── -->
                <q-separator class="full-width" />
                <div class="text-caption text-grey-7 full-width q-mt-xs">Footer Note</div>

                <q-toggle
                  v-model="pageOptions.answerKey.showNotes"
                  label='Show "separate before distribution" note'
                  @update:model-value="savePageState"
                />

                <q-input
                  v-if="pageOptions.answerKey.showNotes"
                  dense
                  outlined
                  type="textarea"
                  rows="2"
                  v-model="pageOptions.answerKey.notesText"
                  label="Custom note text (leave blank for default)"
                  @blur="savePageState"
                />
              </template>
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
          
          <!-- Step 0: Quick Generate Mode (AI Auto-generates everything) -->
          <q-step
            v-if="quickMode"
            :name="0"
            title="Quick Generate - AI Auto-Configuration"
            icon="bolt"
            :done="step > 0"
          >
            <div class="step-content">
              <!-- Phase Indicator -->
              <q-card flat bordered class="q-mb-md bg-blue-1">
                <q-card-section>
                  <div class="row items-center">
                    <q-icon 
                      :name="allFieldsFilled ? 'check_circle' : 'help_outline'" 
                      :color="allFieldsFilled ? 'green' : 'orange'" 
                      size="md" 
                      class="q-mr-sm"
                    />
                    <div class="col">
                      <div class="text-subtitle2">
                        {{ allFieldsFilled ? '✓ Ready to Generate' : '⚠️ Information Needed' }}
                      </div>
                      <div class="text-caption">
                        {{ allFieldsFilled 
                          ? 'All information provided. AI will generate your exam.' 
                          : 'AI will suggest options for missing information first.' 
                        }}
                      </div>
                    </div>
                  </div>
                </q-card-section>
              </q-card>

              <!-- What AI Will Do -->
              <q-card flat bordered class="q-pa-md q-mb-md">
                <div class="text-h6 q-mb-md">
                  {{ allFieldsFilled ? '🤖 AI Will Generate:' : '🤖 AI Will Suggest:' }}
                </div>
                
                <q-list v-if="!allFieldsFilled" bordered separator class="rounded-borders">
                  <q-item>
                    <q-item-section avatar>
                      <q-icon color="orange" name="lightbulb" />
                    </q-item-section>
                    <q-item-section>
                      <q-item-label>Suggestions for Missing Information</q-item-label>
                      <q-item-label caption>
                        AI will provide 3-4 options for: {{ missingFieldsList }}
                      </q-item-label>
                    </q-item-section>
                  </q-item>

                  <q-item>
                    <q-item-section avatar>
                      <q-icon color="orange" name="question_answer" />
                    </q-item-section>
                    <q-item-section>
                      <q-item-label>Confirmation Request</q-item-label>
                      <q-item-label caption>
                        AI will ask you to confirm before generating
                      </q-item-label>
                    </q-item-section>
                  </q-item>

                  <q-item>
                    <q-item-section avatar>
                      <q-icon color="blue" name="info" />
                    </q-item-section>
                    <q-item-section>
                      <q-item-label>Next Steps</q-item-label>
                      <q-item-label caption>
                        Review suggestions, fill in the fields above, then generate again
                      </q-item-label>
                    </q-item-section>
                  </q-item>
                </q-list>

                <q-list v-else bordered separator class="rounded-borders">
                  <q-item>
                    <q-item-section avatar>
                      <q-icon color="green" name="check_circle" />
                    </q-item-section>
                    <q-item-section>
                      <q-item-label>Complete Exam Structure</q-item-label>
                      <q-item-label caption>3-5 sections with clear organization</q-item-label>
                    </q-item-section>
                  </q-item>

                  <q-item>
                    <q-item-section avatar>
                      <q-icon color="green" name="check_circle" />
                    </q-item-section>
                    <q-item-section>
                      <q-item-label>{{ quickModeContext.totalQuestions }} Questions</q-item-label>
                      <q-item-label caption>Mixed types with varied difficulty</q-item-label>
                    </q-item-section>
                  </q-item>

                  <q-item>
                    <q-item-section avatar>
                      <q-icon color="green" name="check_circle" />
                    </q-item-section>
                    <q-item-section>
                      <q-item-label>Answer Keys & Explanations</q-item-label>
                      <q-item-label caption>Complete with LaTeX math expressions</q-item-label>
                    </q-item-section>
                  </q-item>
                </q-list>

                <!-- Information Banner -->
                <q-banner 
                  :class="allFieldsFilled ? 'bg-green-1' : 'bg-amber-1'" 
                  class="q-mt-md q-mb-md" 
                  rounded
                >
                  <template v-slot:avatar>
                    <q-icon 
                      :name="allFieldsFilled ? 'check_circle' : 'info'" 
                      :color="allFieldsFilled ? 'green-8' : 'amber-8'" 
                    />
                  </template>
                  <div class="text-body2">
                    <strong v-if="!allFieldsFilled">Optional:</strong>
                    <strong v-else>Ready:</strong>
                    {{ allFieldsFilled 
                      ? 'All information provided. Click below to generate your exam.' 
                      : 'Provide context to help AI, or leave empty for AI to suggest everything.' 
                    }}
                  </div>
                </q-banner>

                <!-- Input Fields -->
                <q-input
                  v-model="quickModeContext.subject"
                  label="Subject"
                  :placeholder="allFieldsFilled ? quickModeContext.subject : 'e.g., Mathematics, Science, English'"
                  outlined
                  class="q-mb-md"
                  :hint="allFieldsFilled ? '✓ Provided' : 'Leave empty for AI suggestions'"
                  :filled="!!quickModeContext.subject"
                >
                  <template v-slot:prepend>
                    <q-icon :name="quickModeContext.subject ? 'check_circle' : 'help_outline'" 
                            :color="quickModeContext.subject ? 'green' : 'grey'" />
                  </template>
                </q-input>

                <q-input
                  v-model="quickModeContext.grade"
                  label="Grade Level"
                  :placeholder="allFieldsFilled ? quickModeContext.grade : 'e.g., Grade 7, Grade 10'"
                  outlined
                  class="q-mb-md"
                  :hint="allFieldsFilled ? '✓ Provided' : 'Leave empty for AI suggestions'"
                  :filled="!!quickModeContext.grade"
                >
                  <template v-slot:prepend>
                    <q-icon :name="quickModeContext.grade ? 'check_circle' : 'help_outline'" 
                            :color="quickModeContext.grade ? 'green' : 'grey'" />
                  </template>
                </q-input>

                <q-input
                  v-model="quickModeContext.examType"
                  label="Exam Type"
                  :placeholder="allFieldsFilled ? quickModeContext.examType : 'e.g., Final Exam, Mid-term, Quiz'"
                  outlined
                  class="q-mb-md"
                  :hint="allFieldsFilled ? '✓ Provided' : 'Leave empty for AI suggestions'"
                  :filled="!!quickModeContext.examType"
                >
                  <template v-slot:prepend>
                    <q-icon :name="quickModeContext.examType ? 'check_circle' : 'help_outline'" 
                            :color="quickModeContext.examType ? 'green' : 'grey'" />
                  </template>
                </q-input>

                <q-input
                  v-model.number="quickModeContext.totalQuestions"
                  type="number"
                  label="Total Questions"
                  :placeholder="allFieldsFilled ? String(quickModeContext.totalQuestions) : 'e.g., 20'"
                  outlined
                  min="5"
                  max="50"
                  class="q-mb-md"
                  :hint="allFieldsFilled ? '✓ Provided' : 'Leave empty for AI suggestions (typically 15-25)'"
                  :filled="!!quickModeContext.totalQuestions"
                >
                  <template v-slot:prepend>
                    <q-icon :name="quickModeContext.totalQuestions ? 'check_circle' : 'help_outline'" 
                            :color="quickModeContext.totalQuestions ? 'green' : 'grey'" />
                  </template>
                </q-input>
              </q-card>

              <q-stepper-navigation class="q-mt-md">
                <q-btn 
                  @click="generateQuickModePrompt" 
                  :color="allFieldsFilled ? 'positive' : 'primary'"
                  :label="allFieldsFilled ? 'Generate Exam Now' : 'Get AI Suggestions'"
                  :icon="allFieldsFilled ? 'auto_awesome' : 'lightbulb'"
                  unelevated
                  size="lg"
                  class="full-width q-mb-sm"
                />
                <q-btn 
                  flat 
                  @click="quickMode = false; step = 1" 
                  color="grey-7" 
                  label="Switch to Manual Mode" 
                  class="full-width"
                  size="sm"
                />
              </q-stepper-navigation>

              <!-- Quick Mode Generated Prompt -->
              <div v-if="generatedPrompt && quickMode" class="q-mt-md">
                <q-card bordered>
                  <q-card-section :class="allFieldsFilled ? 'bg-green text-white' : 'bg-orange text-white'">
                    <div class="text-subtitle1">
                      {{ allFieldsFilled ? '✨ Exam Generation Prompt' : '💡 Suggestion Request Prompt' }}
                    </div>
                    <div class="text-caption">
                      {{ allFieldsFilled 
                        ? 'Copy this and paste into your AI assistant to generate the complete exam' 
                        : 'Copy this and paste into your AI assistant to get suggestions' 
                      }}
                    </div>
                  </q-card-section>
                  
                  <q-card-section>
                    <q-markdown :source="generatedPrompt" class="prompt-markdown" />
                  </q-card-section>

                  <q-card-actions>
                    <q-btn 
                      flat 
                      @click="copyPrompt" 
                      color="primary" 
                      icon="content_copy"
                      label="Copy to Clipboard"
                    />
                    <q-space />
                    <q-btn 
                      v-if="allFieldsFilled"
                      @click="step = 2" 
                      color="secondary" 
                      icon="arrow_forward"
                      label="Next: Paste AI Response"
                      unelevated
                    />
                    <q-chip v-else color="orange" text-color="white" icon="info">
                      After getting suggestions, fill fields above and generate again
                    </q-chip>
                  </q-card-actions>
                </q-card>
              </div>
            </div>
          </q-step>

          <!-- Step 1: Configure AI Prompt (Manual Mode) -->
          <q-step
            v-if="!quickMode"
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
                  hint="What topic should the questions cover?"
                />
                
                <q-input
                  v-model="aiConfig.grade"
                  label="Grade Level"
                  placeholder="e.g., Grade 7, Grade 8"
                  outlined
                  hint="Target grade level for question difficulty"
                />
                
                <q-input
                  v-model.number="aiConfig.questionCount"
                  type="number"
                  label="Number of Questions"
                  min="1"
                  max="20"
                  outlined
                  hint="How many questions to generate (1-20)"
                />

                <q-select
                  v-model="aiConfig.questionTypes"
                  :options="['short_answer', 'multiple_choice', 'true_false', 'mixed']"
                  label="Question Types"
                  outlined
                  multiple
                  hint="Select one or more question types"
                  emit-value
                  map-options
                >
                  <template v-slot:option="scope">
                    <q-item v-bind="scope.itemProps">
                      <q-item-section>
                        <q-item-label>{{ formatQuestionType(scope.opt) }}</q-item-label>
                      </q-item-section>
                    </q-item>
                  </template>
                  <template v-slot:selected-item="scope">
                    <q-chip
                      removable
                      dense
                      @remove="scope.removeAtIndex(scope.index)"
                      color="primary"
                      text-color="white"
                    >
                      {{ formatQuestionType(scope.opt) }}
                    </q-chip>
                  </template>
                </q-select>

                <q-select
                  v-model="aiConfig.difficulty"
                  :options="['easy', 'medium', 'hard', 'mixed']"
                  label="Difficulty Level"
                  outlined
                  emit-value
                  map-options
                  hint="Overall difficulty of questions"
                />
                
                <q-toggle
                  v-model="aiConfig.latexSupport"
                  label="Enable LaTeX/Math Expressions"
                  hint="Use mathematical notation like fractions, equations"
                />
                
                <q-toggle
                  v-model="aiConfig.htmlSupport"
                  label="Enable HTML Formatting"
                  hint="Allow bold, italic, underline formatting"
                />

                <q-toggle
                  v-model="aiConfig.includeSolutions"
                  label="Include Answer Key"
                  hint="Generate correct answers for each question"
                />

                <q-toggle
                  v-model="aiConfig.includeExplanations"
                  label="Include Explanations"
                  hint="Add step-by-step solutions"
                />
                
                <q-input
                  v-model="aiConfig.instructions"
                  type="textarea"
                  label="Additional Instructions (Optional)"
                  placeholder="e.g., Include step-by-step solutions, focus on real-world applications, avoid complex vocabulary"
                  outlined
                  rows="3"
                  hint="Any specific requirements or preferences"
                />
              </div>
              
              <q-stepper-navigation>
                <q-btn 
                  @click="showGenerationPreview" 
                  color="primary" 
                  label="Preview & Generate Prompt" 
                  icon="preview"
                  :disable="!aiConfig.topic || !aiConfig.grade || !aiConfig.questionCount"
                />
                <q-btn flat @click="step = 2" color="secondary" label="Skip to Paste" class="q-ml-sm" />
              </q-stepper-navigation>
              
              <!-- Generation Preview Dialog -->
              <q-dialog v-model="showPreviewDialog" persistent>
                <q-card style="min-width: 600px">
                  <q-card-section class="row items-center q-pb-none">
                    <div class="text-h6">📋 Generation Preview</div>
                    <q-space />
                    <q-btn icon="close" flat round dense v-close-popup />
                  </q-card-section>

                  <q-card-section>
                    <div class="text-subtitle2 q-mb-md">AI will generate the following:</div>
                    
                    <q-list bordered separator class="rounded-borders">
                      <q-item>
                        <q-item-section avatar>
                          <q-icon color="primary" name="topic" />
                        </q-item-section>
                        <q-item-section>
                          <q-item-label>Topic</q-item-label>
                          <q-item-label caption>{{ aiConfig.topic }}</q-item-label>
                        </q-item-section>
                      </q-item>

                      <q-item>
                        <q-item-section avatar>
                          <q-icon color="primary" name="school" />
                        </q-item-section>
                        <q-item-section>
                          <q-item-label>Grade Level</q-item-label>
                          <q-item-label caption>{{ aiConfig.grade }}</q-item-label>
                        </q-item-section>
                      </q-item>

                      <q-item>
                        <q-item-section avatar>
                          <q-icon color="primary" name="format_list_numbered" />
                        </q-item-section>
                        <q-item-section>
                          <q-item-label>Number of Questions</q-item-label>
                          <q-item-label caption>{{ aiConfig.questionCount }} questions</q-item-label>
                        </q-item-section>
                      </q-item>

                      <q-item v-if="aiConfig.questionTypes && aiConfig.questionTypes.length">
                        <q-item-section avatar>
                          <q-icon color="primary" name="quiz" />
                        </q-item-section>
                        <q-item-section>
                          <q-item-label>Question Types</q-item-label>
                          <q-item-label caption>{{ aiConfig.questionTypes.map(t => formatQuestionType(t)).join(', ') }}</q-item-label>
                        </q-item-section>
                      </q-item>

                      <q-item v-if="aiConfig.difficulty">
                        <q-item-section avatar>
                          <q-icon color="primary" name="speed" />
                        </q-item-section>
                        <q-item-section>
                          <q-item-label>Difficulty</q-item-label>
                          <q-item-label caption>{{ aiConfig.difficulty.charAt(0).toUpperCase() + aiConfig.difficulty.slice(1) }}</q-item-label>
                        </q-item-section>
                      </q-item>

                      <q-item v-if="aiConfig.latexSupport">
                        <q-item-section avatar>
                          <q-icon color="green" name="functions" />
                        </q-item-section>
                        <q-item-section>
                          <q-item-label>Math Expressions</q-item-label>
                          <q-item-label caption>LaTeX notation enabled</q-item-label>
                        </q-item-section>
                      </q-item>

                      <q-item v-if="aiConfig.includeSolutions">
                        <q-item-section avatar>
                          <q-icon color="green" name="check_circle" />
                        </q-item-section>
                        <q-item-section>
                          <q-item-label>Answer Key</q-item-label>
                          <q-item-label caption>Correct answers included</q-item-label>
                        </q-item-section>
                      </q-item>

                      <q-item v-if="aiConfig.includeExplanations">
                        <q-item-section avatar>
                          <q-icon color="green" name="lightbulb" />
                        </q-item-section>
                        <q-item-section>
                          <q-item-label>Explanations</q-item-label>
                          <q-item-label caption>Step-by-step solutions included</q-item-label>
                        </q-item-section>
                      </q-item>
                    </q-list>

                    <q-banner v-if="aiConfig.instructions" class="bg-blue-1 q-mt-md" rounded>
                      <template v-slot:avatar>
                        <q-icon name="info" color="primary" />
                      </template>
                      <div class="text-subtitle2">Additional Instructions:</div>
                      <div class="text-caption">{{ aiConfig.instructions }}</div>
                    </q-banner>
                  </q-card-section>

                  <q-card-actions align="right">
                    <q-btn flat label="Cancel" color="grey" v-close-popup />
                    <q-btn 
                      label="Generate Prompt" 
                      color="primary" 
                      icon="auto_awesome"
                      @click="confirmAndGeneratePrompt"
                      unelevated
                    />
                  </q-card-actions>
                </q-card>
              </q-dialog>
              
              <div v-if="generatedPrompt" class="prompt-preview q-mt-md">
                <q-card bordered>
                  <q-card-section class="bg-primary text-white">
                    <div class="text-subtitle1">✨ Generated Prompt</div>
                    <div class="text-caption">Copy this prompt and paste it into your AI assistant (ChatGPT, Claude, etc.)</div>
                  </q-card-section>
                  
                  <q-card-section>
                    <q-markdown :source="generatedPrompt" class="prompt-markdown" />
                  </q-card-section>

                  <q-card-actions>
                    <q-btn 
                      flat 
                      @click="copyPrompt" 
                      color="primary" 
                      icon="content_copy"
                      label="Copy to Clipboard"
                    />
                    <q-space />
                    <q-btn 
                      flat 
                      @click="step = 2" 
                      color="secondary" 
                      icon="arrow_forward"
                      label="Next: Paste Response"
                    />
                  </q-card-actions>
                </q-card>
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

  <!-- Full Exam AI Generation Dialog -->
  <q-dialog v-model="fullExamDialogOpen" maximized transition-show="slide-up" transition-hide="slide-down">
    <q-card class="q-pa-md">
      <q-card-section class="row items-center q-pb-none">
        <div class="text-h6">Generate Full Exam with AI</div>
        <q-space />
        <q-btn flat round dense icon="close" v-close-popup />
      </q-card-section>

      <q-card-section>
        <q-stepper v-model="fullExamStep" vertical color="primary" animated>
          <!-- Step 1: Exam Configuration -->
          <q-step
            :name="1"
            title="Configure Exam"
            icon="settings"
            :done="fullExamStep > 1"
          >
            <div class="form-section">
              <div class="text-subtitle1 q-mb-md">Exam Details</div>
              
              <q-input
                v-model="examConfig.examTitle"
                label="Exam Title"
                placeholder="e.g., Mathematics Final Exam"
                outlined
                class="q-mb-md"
              />
              
              <q-input
                v-model="examConfig.examSubject"
                label="Subject"
                placeholder="e.g., Mathematics"
                outlined
                class="q-mb-md"
              />
              
              <q-input
                v-model="examConfig.examGrade"
                label="Grade Level"
                placeholder="e.g., Grade 10"
                outlined
                class="q-mb-md"
              />
              
              <q-input
                v-model="examConfig.examDuration"
                label="Duration"
                placeholder="e.g., 2 hours"
                outlined
                class="q-mb-md"
              />
              
              <q-input
                v-model.number="examConfig.totalMarks"
                type="number"
                label="Total Marks"
                placeholder="e.g., 100"
                outlined
                class="q-mb-md"
              />

              <div class="text-subtitle1 q-mb-md q-mt-lg">Sections</div>
              
              <div v-for="(section, index) in examConfig.sections" :key="index" class="section-card q-mb-md q-pa-md bordered">
                <div class="row items-center q-mb-md">
                  <div class="text-subtitle2">Section {{ index + 1 }}</div>
                  <q-space />
                  <q-btn flat round dense icon="delete" color="negative" @click="removeExamSection(index)" />
                </div>
                
                <q-input
                  v-model="section.title"
                  label="Section Title"
                  placeholder="e.g., Algebra, Geometry"
                  outlined
                  class="q-mb-sm"
                />
                
                <q-input
                  v-model="section.description"
                  label="Description (optional)"
                  placeholder="Brief description of this section"
                  outlined
                  class="q-mb-sm"
                />
                
                <q-select
                  v-model="section.questionTypes"
                  label="Question Types"
                  :options="['short_answer', 'multiple_choice', 'true_false', 'essay', 'fill_in_blank']"
                  multiple
                  outlined
                  class="q-mb-sm"
                />
                
                <q-input
                  v-model.number="section.questionCount"
                  type="number"
                  label="Number of Questions"
                  min="1"
                  outlined
                  class="q-mb-sm"
                />
                
                <q-input
                  v-model.number="section.marksPerQuestion"
                  type="number"
                  label="Marks per Question"
                  min="1"
                  outlined
                />
              </div>

              <q-btn @click="addExamSection" color="secondary" icon="add" label="Add Section" class="q-mt-md" />
            </div>

            <q-stepper-navigation>
              <q-btn @click="generateFullExamPrompt" color="primary" label="Generate Prompt" />
              <q-btn flat @click="fullExamDialogOpen = false" color="secondary" label="Cancel" class="q-ml-sm" />
            </q-stepper-navigation>
          </q-step>

          <!-- Step 2: AI Prompt -->
          <q-step
            :name="2"
            title="AI Prompt"
            icon="chat"
            :done="fullExamStep > 2"
          >
            <div class="step-content">
              <div v-if="fullExamPrompt" class="prompt-preview">
                <div class="text-subtitle2 q-mb-sm">Generated Prompt:</div>
                <q-markdown :source="fullExamPrompt" />
              </div>

              <q-stepper-navigation>
                <q-btn v-if="fullExamPrompt" flat @click="copyFullExamPrompt" color="secondary" label="Copy to Clipboard" />
                <q-btn @click="fullExamStep = 3" color="primary" label="Next" class="q-ml-sm" />
                <q-btn flat @click="fullExamStep = 1" color="secondary" label="Back" class="q-ml-sm" />
              </q-stepper-navigation>
            </div>
          </q-step>

          <!-- Step 3: AI Response -->
          <q-step
            :name="3"
            title="AI Response"
            icon="check_circle"
            :done="fullExamStep > 3"
          >
            <div class="step-content">
              <div class="text-subtitle2 q-mb-sm">Paste AI Response (JSON):</div>
              <q-input
                v-model="fullExamResponse"
                type="textarea"
                label="AI Response (JSON)"
                placeholder="Paste the JSON response from AI here..."
                outlined
                rows="15"
                class="q-mb-md"
              />

              <q-btn @click="processFullExamResponse" color="primary" label="Generate Exam" />
              
              <q-stepper-navigation class="q-mt-md">
                <q-btn flat @click="fullExamStep = 2" color="secondary" label="Back" />
              </q-stepper-navigation>
            </div>
          </q-step>
        </q-stepper>
      </q-card-section>
    </q-card>
  </q-dialog>

  <!-- Copy From Dialog -->
  <q-dialog v-model="copyFromDialogOpen">
    <q-card style="min-width: 500px; max-width: 95vw;">
      <q-card-section class="row items-center q-pb-none">
        <div class="text-h6">Copy From Saved Exam</div>
        <q-space />
        <q-btn flat round dense icon="close" v-close-popup />
      </q-card-section>

      <q-card-section>
        <q-select
          v-model="copyFromSelectedFile"
          :options="copyFromFiles"
          label="Select Exam"
          outlined
          option-label="name"
          option-value="id"
          emit-value
          map-options
          :loading="copyFromLoading"
          :disable="copyFromLoading"
        >
          <template v-slot:prepend>
            <q-icon name="folder_open" />
          </template>
        </q-select>

        <q-separator class="q-my-md" />

        <div class="text-subtitle2 q-mb-md">Copy Options</div>

        <q-list>
          <q-item tag="label" v-ripple>
            <q-item-section avatar>
              <q-checkbox v-model="copyFromOptions.copyQuestions" color="primary" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Copy Questions</q-item-label>
              <q-item-label caption>Include all questions from selected exam</q-item-label>
            </q-item-section>
          </q-item>

          <q-item tag="label" v-ripple>
            <q-item-section avatar>
              <q-checkbox v-model="copyFromOptions.copySettings" color="primary" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Copy Settings</q-item-label>
              <q-item-label caption>Include page options, header, footer settings</q-item-label>
            </q-item-section>
          </q-item>

          <q-item tag="label" v-ripple>
            <q-item-section avatar>
              <q-checkbox v-model="copyFromOptions.copySections" color="primary" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Copy Sections</q-item-label>
              <q-item-label caption>Include section structure and organization</q-item-label>
            </q-item-section>
          </q-item>

          <q-item tag="label" v-ripple>
            <q-item-section avatar>
              <q-checkbox v-model="copyFromOptions.copyFullExamAsJson" color="accent" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Copy Full Exam as JSON</q-item-label>
              <q-item-label caption>Replace entire current exam with the selected exam data</q-item-label>
            </q-item-section>
          </q-item>

          <q-item tag="label" v-ripple>
            <q-item-section avatar>
              <q-checkbox v-model="copyFromOptions.removeCurrentQuestions" color="negative" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Remove Current Questions</q-item-label>
              <q-item-label caption>Replace all current questions with copied ones</q-item-label>
            </q-item-section>
          </q-item>
        </q-list>

        <q-separator class="q-my-md" />

        <div class="text-subtitle2 q-mb-md">JSON Format Guide</div>
        <q-expansion-item
          icon="code"
          label="View JSON Structure"
          header-class="bg-grey-1"
        >
          <q-card>
            <q-card-section class="bg-grey-2">
              <div class="row items-center justify-between q-mb-sm">
                <div class="text-caption text-grey-7">Question JSON Format:</div>
                <q-btn
                  flat
                  dense
                  size="sm"
                  icon="content_copy"
                  label="Copy"
                  @click="copyQuestionJsonExample"
                />
              </div>
              <pre class="code-block">{
  "id": "unique_id",
  "type": "multiple_choice" | "short_answer" | "true_false" | "essay" | "fill_in_blank",
  "ver": 3,
  "marks": 1,
  "content": {
    "prompt": "Question text here",
    "options": ["Option A", "Option B", "Option C", "Option D"],
    "correct_option_index": 0,
    "explanation": "Optional explanation text"
  }
}</pre>

              <div class="row items-center justify-between q-mt-md q-mb-sm">
                <div class="text-caption text-grey-7">Settings JSON Format:</div>
                <q-btn
                  flat
                  dense
                  size="sm"
                  icon="content_copy"
                  label="Copy"
                  @click="copySettingsJsonExample"
                />
              </div>
              <pre class="code-block">{
  "examTitle": { "enabled": true, "text": "Exam Name" },
  "showMarksPerQuestion": true,
  "showExplanationUnderQuestion": false,
  "showCorrectAnswerUnderQuestion": false,
  "printHeader": { "enabled": false, ... },
  "printFooter": { "enabled": true, ... },
  "answerKey": { "enabled": false, ... },
  "mcqOptions": { "labelStyle": "letter", ... }
}</pre>

              <div class="text-caption text-grey-7 q-mt-md">
                <strong>Note:</strong> For MCQ questions, <code>correct_option_index</code> should be the 0-based index of the correct option (0 = A, 1 = B, etc.)
              </div>

              <q-separator class="q-my-md" />

              <div class="row justify-center q-mt-md">
                <q-btn
                  color="primary"
                  icon="content_copy"
                  label="Copy Current Exam JSON (Both)"
                  @click="copyCurrentExamJson"
                />
              </div>
            </q-card-section>
          </q-card>
        </q-expansion-item>
      </q-card-section>

      <q-card-actions align="right">
        <q-btn flat label="Cancel" v-close-popup />
        <q-btn
          flat
          label="Copy"
          color="primary"
          @click="handleCopyFrom"
          :disable="!copyFromSelectedFile || copyFromLoading"
          :loading="copyFromLoading"
        />
      </q-card-actions>
    </q-card>
  </q-dialog>

  <!-- Smart Exam Generator Dialog -->
  <q-dialog v-model="smartExamDialogOpen" maximized transition-show="slide-up" transition-hide="slide-down">
    <q-card class="q-pa-md">
      <q-card-section class="row items-center q-pb-none">
        <div class="text-h6">Smart Exam Generator</div>
        <q-space />
        <q-btn flat round dense icon="close" v-close-popup />
      </q-card-section>

      <q-card-section>
        <q-stepper v-model="smartExamStep" vertical color="primary" animated>
          <!-- Step 1: Information Gathering -->
          <q-step
            :name="1"
            title="Exam Information"
            icon="info"
            :done="smartExamStep > 1"
          >
            <div class="q-pa-md">
              <p class="text-body1 q-mb-md">
                AI will ask you for any missing information to create the perfect exam.
              </p>

              <q-input
                v-model="smartExamConfig.subject"
                label="Subject"
                outlined
                class="q-mb-md"
                hint="e.g., Mathematics, Science, English"
              />

              <q-input
                v-model="smartExamConfig.grade"
                label="Grade Level"
                outlined
                class="q-mb-md"
                hint="e.g., Grade 7, Grade 10"
              />

              <q-input
                v-model="smartExamConfig.examType"
                label="Exam Type"
                outlined
                class="q-mb-md"
                hint="e.g., Final Exam, Mid-term, Quiz"
              />

              <q-input
                v-model="smartExamConfig.duration"
                label="Duration"
                outlined
                class="q-mb-md"
                hint="e.g., 60 minutes, 2 hours"
              />

              <q-input
                v-model.number="smartExamConfig.totalQuestions"
                type="number"
                label="Total Questions"
                outlined
                class="q-mb-md"
                min="5"
                max="50"
                hint="Number of questions to generate"
              />

              <q-select
                v-model="smartExamConfig.questionTypes"
                label="Question Types"
                :options="[
                  { label: 'Multiple Choice (MCQ)', value: 'mcq' },
                  { label: 'Short Answer', value: 'short_answer' },
                  { label: 'True/False', value: 'true_false' },
                  { label: 'Fill in the Blanks', value: 'fill_blank' },
                  { label: 'Essay', value: 'essay' }
                ]"
                multiple
                outlined
                class="q-mb-md"
                hint="Select question types to include"
              />

              <q-select
                v-model="smartExamConfig.difficulty"
                label="Difficulty Level"
                :options="['easy', 'medium', 'hard', 'mixed']"
                outlined
                class="q-mb-md"
                hint="Overall difficulty of questions"
              />
            </div>

            <q-stepper-navigation>
              <q-btn @click="generateSmartExamPrompt" color="primary" label="Continue" unelevated />
            </q-stepper-navigation>
          </q-step>

          <!-- Step 2: AI Information Request -->
          <q-step
            :name="2"
            title="AI Information Request"
            icon="chat"
            :done="smartExamStep > 2"
          >
            <div class="q-pa-md">
              <p class="text-body1 q-mb-md">
                AI will ask for any missing information or suggest improvements.
              </p>

              <q-card v-if="smartExamPrompt" bordered class="q-mb-md">
                <q-card-section class="bg-blue-1">
                  <div class="text-subtitle2">AI Request Prompt</div>
                </q-card-section>
                <q-card-section>
                  <q-markdown :source="smartExamPrompt" />
                </q-card-section>
                <q-card-actions>
                  <q-btn flat @click="copySmartPrompt" color="primary" icon="content_copy" label="Copy" />
                  <q-space />
                  <q-btn flat color="secondary" label="Paste Response" @click="pasteSmartResponse" />
                </q-card-actions>
              </q-card>

              <q-input
                v-model="smartExamResponse"
                label="AI Response"
                type="textarea"
                outlined
                rows="10"
                hint="Paste the AI response here"
              />
            </div>

            <q-stepper-navigation>
              <q-btn @click="parseSmartResponse" color="primary" label="Continue" unelevated />
              <q-btn flat @click="smartExamStep = 1" color="grey" label="Back" class="q-ml-sm" />
            </q-stepper-navigation>
          </q-step>

          <!-- Step 3: Summary & Confirmation -->
          <q-step
            :name="3"
            title="Exam Summary"
            icon="summarize"
            :done="smartExamStep > 3"
          >
            <div class="q-pa-md">
              <p class="text-body1 q-mb-md">
                Review the exam summary before generation.
              </p>

              <q-card v-if="smartExamSummary" bordered class="q-mb-md">
                <q-card-section class="bg-green-1">
                  <div class="text-subtitle2">Exam Summary</div>
                </q-card-section>
                <q-card-section>
                  <q-list>
                    <q-item v-for="(value, key) in smartExamSummary" :key="key">
                      <q-item-section avatar>
                        <q-icon name="check_circle" color="green" />
                      </q-item-section>
                      <q-item-section>
                        <q-item-label>{{ key }}</q-item-label>
                        <q-item-label caption>{{ value }}</q-item-label>
                      </q-item-section>
                    </q-item>
                  </q-list>
                </q-card-section>
              </q-card>

              <q-banner class="bg-orange-1 q-mb-md" rounded>
                <template v-slot:avatar>
                  <q-icon name="warning" color="orange" />
                </template>
                Review the summary carefully. Once confirmed, AI will generate the complete exam JSON.
              </q-banner>
            </div>

            <q-stepper-navigation>
              <q-btn @click="confirmSmartExam" color="positive" label="Confirm & Generate" unelevated />
              <q-btn flat @click="smartExamStep = 2" color="grey" label="Back" class="q-ml-sm" />
            </q-stepper-navigation>
          </q-step>

          <!-- Step 4: Generated Questions -->
          <q-step
            :name="4"
            title="Generated Questions"
            icon="quiz"
            :done="smartExamStep > 4"
          >
            <div class="q-pa-md">
              <p class="text-body1 q-mb-md">
                AI has generated the exam questions. Review them below.
              </p>

              <q-card v-if="smartExamQuestions.length > 0" bordered class="q-mb-md">
                <q-card-section class="bg-blue-1">
                  <div class="text-subtitle2">Questions Summary</div>
                </q-card-section>
                <q-card-section>
                  <q-table
                    :rows="smartExamQuestions"
                    :columns="[
                      { name: 'id', label: '#', field: 'id', align: 'left' },
                      { name: 'type', label: 'Type', field: 'type', align: 'left' },
                      { name: 'marks', label: 'Marks', field: 'marks', align: 'left' },
                      { name: 'content', label: 'Content', field: 'content', align: 'left' }
                    ]"
                    row-key="id"
                    flat
                    dense
                  >
                    <template v-slot:body-cell-content="props">
                      <q-td :props="props">
                        <div class="text-caption">{{ props.row.content.substring(0, 50) }}...</div>
                      </q-td>
                    </template>
                  </q-table>
                </q-card-section>
              </q-card>
            </div>

            <q-stepper-navigation>
              <q-btn @click="validateSmartExam" color="primary" label="Validate & Check Issues" unelevated />
              <q-btn flat @click="smartExamStep = 3" color="grey" label="Back" class="q-ml-sm" />
            </q-stepper-navigation>
          </q-step>

          <!-- Step 5: Issues & Recommendations -->
          <q-step
            :name="5"
            title="Issues & Recommendations"
            icon="fact_check"
            :done="smartExamStep > 5"
          >
            <div class="q-pa-md">
              <p class="text-body1 q-mb-md">
                AI has checked the exam for issues and provided recommendations.
              </p>

              <q-card v-if="smartExamIssues.length > 0" bordered class="q-mb-md bg-red-1">
                <q-card-section>
                  <div class="text-subtitle2 text-negative">Issues Found</div>
                </q-card-section>
                <q-card-section>
                  <q-list>
                    <q-item v-for="(issue, index) in smartExamIssues" :key="index">
                      <q-item-section avatar>
                        <q-icon name="error" color="negative" />
                      </q-item-section>
                      <q-item-section>
                        <q-item-label>{{ issue }}</q-item-label>
                      </q-item-section>
                    </q-item>
                  </q-list>
                </q-card-section>
              </q-card>

              <q-card v-if="smartExamRecommendations.length > 0" bordered class="q-mb-md bg-green-1">
                <q-card-section>
                  <div class="text-subtitle2 text-positive">Recommendations</div>
                </q-card-section>
                <q-card-section>
                  <q-table
                    :rows="smartExamRecommendations"
                    :columns="[
                      { name: 'category', label: 'Category', field: 'category', align: 'left' },
                      { name: 'issue', label: 'Issue', field: 'issue', align: 'left' },
                      { name: 'recommendation', label: 'Recommendation', field: 'recommendation', align: 'left' },
                      { name: 'priority', label: 'Priority', field: 'priority', align: 'left' }
                    ]"
                    row-key="category"
                    flat
                    dense
                  >
                    <template v-slot:body-cell-priority="props">
                      <q-td :props="props">
                        <q-chip :color="props.row.priority === 'high' ? 'red' : props.row.priority === 'medium' ? 'orange' : 'green'" text-color="white" size="sm">
                          {{ props.row.priority }}
                        </q-chip>
                      </q-td>
                    </template>
                  </q-table>
                </q-card-section>
              </q-card>

              <q-banner v-if="smartExamIssues.length === 0 && smartExamRecommendations.length === 0" class="bg-green-1" rounded>
                <template v-slot:avatar>
                  <q-icon name="check_circle" color="green" />
                </template>
                No issues found! The exam is ready to import.
              </q-banner>
            </div>

            <q-stepper-navigation>
              <q-btn @click="importSmartExam" color="positive" label="Import Exam" unelevated />
              <q-btn flat @click="smartExamStep = 4" color="grey" label="Back" class="q-ml-sm" />
            </q-stepper-navigation>
          </q-step>
        </q-stepper>
      </q-card-section>
    </q-card>
  </q-dialog>

  <!-- AI Chat Dialog -->
  <q-dialog v-model="aiChatDialogOpen" maximized transition-show="slide-up" transition-hide="slide-down">
    <q-card class="q-pa-md">
      <q-card-section class="row items-center q-pb-none">
        <div class="text-h6">AI Chat Exam Generation</div>
        <q-space />
        <q-btn flat round dense icon="close" v-close-popup />
      </q-card-section>

      <q-card-section>
        <!-- API Key Configuration -->
        <q-input
          v-model="aiApiKey"
          label="OpenAI API Key"
          type="password"
          outlined
          hint="Your API key is stored locally in your browser"
          class="q-mb-md"
        />

        <q-select
          v-model="aiModel"
          label="AI Model"
          :options="['gpt-4', 'gpt-3.5-turbo']"
          outlined
          class="q-mb-md"
        />

        <!-- Chat Messages -->
        <div class="chat-container q-mb-md" style="height: 400px; overflow-y: auto;">
          <div
            v-for="(message, index) in aiChatMessages"
            :key="index"
            :class="['chat-message', message.role === 'user' ? 'user-message' : 'ai-message']"
            class="q-pa-md q-mb-sm"
          >
            <div class="message-sender q-mb-xs">
              <q-icon :name="message.role === 'user' ? 'person' : 'smart_toy'" class="q-mr-xs" />
              {{ message.role === 'user' ? 'You' : 'AI Assistant' }}
            </div>
            <div class="message-content q-markdown-body">
              <q-markdown :source="message.content" />
            </div>
          </div>
          <div v-if="aiLoading" class="q-pa-md">
            <q-spinner color="primary" size="2em" />
            <span class="q-ml-sm">AI is thinking...</span>
          </div>
        </div>

        <!-- Chat Input -->
        <div class="row q-col-gutter-sm">
          <div class="col">
            <q-input
              v-model="aiChatInput"
              label="Type your message..."
              outlined
              @keyup.enter="sendAIMessage"
              :disable="aiLoading"
            />
          </div>
          <div class="col-auto">
            <q-btn
              @click="sendAIMessage"
              color="primary"
              icon="send"
              :disable="aiLoading || !aiChatInput.trim()"
              round
            />
          </div>
        </div>

        <!-- Action Buttons when exam is generated -->
        <div v-if="conversationMode === 'review' && generatedExamData" class="q-mt-md">
          <q-btn @click="acceptGeneratedExam" color="positive" icon="check" label="Accept Exam" class="q-mr-sm" />
          <q-btn @click="regenerateExam" color="secondary" icon="refresh" label="Regenerate" />
        </div>
      </q-card-section>
    </q-card>
  </q-dialog>

  <!-- Question Validation Dialog -->
  <q-dialog v-model="validationDialogOpen" maximized transition-show="slide-up" transition-hide="slide-down">
    <q-card class="q-pa-md">
      <q-card-section class="row items-center q-pb-none">
        <div class="text-h6">Validate & Fix Questions</div>
        <q-space />
        <q-btn flat round dense icon="close" v-close-popup />
      </q-card-section>

      <q-card-section>
        <!-- Errors Table -->
        <div class="text-subtitle1 q-mb-md">Detected Errors ({{ questionErrors.length }})</div>
        
        <q-table
          v-if="questionErrors.length > 0"
          :rows="questionErrors"
          :columns="[
            { name: 'questionNumber', label: '#', field: 'questionNumber', align: 'center', style: 'width: 50px' },
            { name: 'type', label: 'Type', field: 'type', align: 'left', style: 'width: 150px' },
            { name: 'severity', label: 'Severity', field: 'severity', align: 'left', style: 'width: 100px' },
            { name: 'message', label: 'Message', field: 'message', align: 'left' },
            { name: 'question', label: 'Question', field: 'question', align: 'left' }
          ]"
          row-key="questionId"
          flat
          bordered
          dense
          :rows-per-page-options="[10, 20, 50]"
        >
          <template v-slot:body-cell-severity="props">
            <q-td :props="props">
              <q-badge :color="props.row.severity === 'error' ? 'negative' : 'warning'">
                {{ props.row.severity }}
              </q-badge>
            </q-td>
          </template>
        </q-table>
        
        <div v-else class="q-pa-md text-center text-positive">
          <q-icon name="check_circle" size="48px" color="positive" />
          <div class="text-h6 q-mt-md">No errors detected!</div>
          <div class="text-caption">All questions appear to be valid.</div>
        </div>

        <!-- Copy Questions for AI Check -->
        <q-separator class="q-my-md" />
        
        <div class="text-subtitle1 q-mb-md">Copy Questions for AI Check</div>
        <div class="text-caption q-mb-sm">Copy current questions JSON to send to AI for error checking:</div>
        
        <div class="row q-col-gutter-sm q-mb-md">
          <q-btn @click="copyQuestionsOnly" color="primary" icon="content_copy" label="Copy Questions Only" />
        </div>

        <!-- AI Error Check Feedback -->
        <q-separator class="q-my-md" />
        
        <div class="text-subtitle1 q-mb-md">AI Error Check Feedback</div>
        <div class="text-caption q-mb-sm">Paste AI error feedback here (JSON or table format):</div>
        
        <q-input
          v-model="aiErrorFeedback"
          type="textarea"
          outlined
          rows="6"
          placeholder="Paste AI feedback here..."
          class="q-mb-md"
          @update:model-value="parseAiFeedback"
        />
        
        <div class="row q-col-gutter-sm q-mb-md">
          <q-btn @click="parseAiFeedback" color="secondary" icon="analytics" label="Parse Feedback" />
        </div>

        <!-- AI Feedback Table -->
        <div v-if="parsedAiFeedback.length > 0" class="q-mb-md">
          <div class="text-subtitle1 q-mb-sm">AI Feedback ({{ parsedAiFeedback.length }} items)</div>
          
          <q-table
            :rows="parsedAiFeedback"
            :columns="[
              { name: 'questionId', label: 'Question #', field: 'questionId', align: 'center', style: 'width: 80px' },
              { name: 'type', label: 'Type', field: 'type', align: 'left', style: 'width: 150px' },
              { name: 'severity', label: 'Severity', field: 'severity', align: 'left', style: 'width: 100px' },
              { name: 'message', label: 'Message', field: 'message', align: 'left' }
            ]"
            row-key="questionId"
            flat
            bordered
            dense
            :rows-per-page-options="[10, 20, 50]"
          >
            <template v-slot:body-cell-severity="props">
              <q-td :props="props">
                <q-badge :color="props.row.severity === 'error' ? 'negative' : props.row.severity === 'warning' ? 'warning' : 'info'">
                  {{ props.row.severity }}
                </q-badge>
              </q-td>
            </template>
          </q-table>
        </div>

        <!-- AI Prompt Section -->
        <q-separator class="q-my-md" />
        
        <div class="text-subtitle1 q-mb-md">AI Validation Prompt</div>
        <div class="text-caption q-mb-sm">Copy this prompt and send to AI to get corrected questions:</div>
        
        <q-input
          v-model="questionsForValidation"
          type="textarea"
          outlined
          rows="8"
          readonly
          class="q-mb-md"
        />
        
        <div class="row q-col-gutter-sm q-mb-md">
          <q-btn @click="copyValidationPrompt" color="primary" icon="content_copy" label="Copy Prompt" />
        </div>

        <!-- Paste Revised Questions Section -->
        <q-separator class="q-my-md" />
        
        <div class="text-subtitle1 q-mb-md">Paste Revised Questions</div>
        <div class="text-caption q-mb-sm">Paste the corrected JSON from AI here:</div>
        
        <q-input
          v-model="revisedQuestions"
          type="textarea"
          outlined
          rows="8"
          placeholder="Paste corrected questions JSON here..."
          class="q-mb-md"
        />
        
        <div class="row q-col-gutter-sm">
          <q-btn @click="applyRevisedQuestions" color="positive" icon="check" label="Apply Revised Questions" :disable="!revisedQuestions.trim()" />
        </div>
      </q-card-section>

      <q-card-actions align="right">
        <q-btn flat color="primary" @click="validationDialogOpen = false">Close</q-btn>
      </q-card-actions>
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

  <!-- Print Preview Full Screen Dialog -->
  <q-dialog v-model="printPreviewOpen" maximized>
    <q-card class="full-screen-print-preview">
      <q-bar>
        <q-icon name="print" />
        <div>Print Preview</div>
        <q-space />
        <q-btn flat dense icon="print" label="Print" @click="printPreview" />
        <q-btn flat dense icon="close" v-close-popup />
      </q-bar>

      <q-card-section class="q-pa-none" style="height: calc(100vh - 38px); overflow: auto;">
        <iframe
          v-if="printPreviewHtml"
          :srcdoc="printPreviewHtml"
          style="width: 100%; height: 100%; border: none;"
          @load="onPreviewLoaded"
        />
      </q-card-section>
    </q-card>
  </q-dialog>

  <!-- Delete Confirmation Dialog -->
  <q-dialog v-model="deleteConfirmOpen">
    <q-card>
      <q-card-section class="row items-center">
        <q-avatar icon="delete" color="negative" text-color="white" />
        <span class="q-ml-sm">Delete Question</span>
        <q-space />
        <q-btn icon="close" flat round dense v-close-popup />
      </q-card-section>

      <q-card-section class="q-pt-none">
        Are you sure you want to delete this question? This action cannot be undone.
      </q-card-section>

      <q-card-actions align="right">
        <q-btn flat label="Cancel" color="grey" v-close-popup />
        <q-btn flat label="Delete" color="negative" @click="executeDeleteQuestion" />
      </q-card-actions>
    </q-card>
  </q-dialog>

  <!-- Edit Question Dialog -->
  <EditMCQDialog
    v-if="questionToEdit && questionToEdit.type === 'multiple_choice'"
    v-model="editQuestionOpen"
    :question="questionToEdit"
    @save="saveEditedMCQ"
  />

  <q-dialog v-else v-model="editQuestionOpen">
    <q-card style="min-width: 500px;">
      <q-card-section class="row items-center q-pb-none">
        <div class="text-h6">Edit Question</div>
        <q-space />
        <q-btn icon="close" flat round dense v-close-popup />
      </q-card-section>

      <q-separator />

      <q-card-section>
        <q-input
          v-model="editQuestionPrompt"
          label="Question Prompt"
          type="textarea"
          filled
          autogrow
          hint="Enter the question text"
        />
      </q-card-section>

      <q-card-actions align="right">
        <q-btn flat label="Cancel" color="grey" v-close-popup />
        <q-btn flat label="Save" color="primary" @click="saveEditedQuestion" />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { ref, computed, onMounted, watch, nextTick } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import { useQuasar } from 'quasar'
import QuestionDisplay from './components/QuestionDisplay.vue'
import SectionTotalMark from './components/SectionTotalMark.vue'
import PrintFooter from './components/PrintFooter.vue'
import EditMCQDialog from './components/EditMCQDialog.vue'
import FirstPageSettings from './components/FirstPageSettings.vue'
import LastPageSettings from './components/LastPageSettings.vue'
import ExamFileManager from './components/ExamFileManager.vue'
import AnswerKey from './components/AnswerKey.vue'
import JsonImportExportActions from './components/JsonImportExportActions.vue'
import SettingsPanel from './components/SettingsPanel.vue'
import AIPromptGenerator from './components/AIPromptGenerator.vue'
import TextRenderer from './components/TextRenderer.vue'
import { useAIPrompts } from './composables/useAIPrompts'
import { useQuestionValidation } from './composables/useQuestionValidation'
import { renderSectionTotalHTML } from './utils/sectionTotalTemplates'
import { formatQuestionLabel } from './utils/questionNumbering'
import { renderMathContent } from './utils/mathRenderer'
 

const PAGE_STATE_KEY = 'exam_ready_to_print_test_builder_state_v1'

const page = usePage()
const $q = useQuasar()

// Flag to prevent auto-save during initial load
const isLoadingState = ref(false)
const isEditMode = ref(false)

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
const generalAIPromptDialogOpen = ref(false)
const aiResponse = ref('')
const parsedQuestions = ref([])
const selectedQuestions = ref([])
const pasteError = ref('')
const firstLastPageOpen = ref(false)

// Use AI composables
const { detectQuestionErrors } = useQuestionValidation()

// Quick Mode State
const quickMode = ref(false)
const quickModeLoading = ref(false)
const quickModeContext = ref({
  subject: '',
  grade: '',
  examType: '',
  totalQuestions: null
})

// Component version for backward compatibility
const COMPONENT_VERSION = 'v3.0'

// Unsaved changes tracking
const hasUnsavedChanges = ref(false)
const autoSaveEnabled = ref(false)
const autoSaveDebounceTimer = ref(null)
const lastSavedState = ref(null)

// Tools dropdown tab
const toolsTab = ref('file')

// Smart Exam Generator State
const smartExamDialogOpen = ref(false)
const smartExamStep = ref(1)
const smartExamConfig = ref({
  subject: '',
  grade: '',
  examType: '',
  duration: '',
  totalQuestions: 10,
  questionTypes: ['mcq', 'short_answer'],
  difficulty: 'mixed'
})
const smartExamPrompt = ref('')
const smartExamResponse = ref('')
const smartExamSummary = ref(null)
const smartExamQuestions = ref([])
const smartExamIssues = ref([])
const smartExamRecommendations = ref([])
const smartExamLoading = ref(false)

// Copy From Dialog State
const copyFromDialogOpen = ref(false)
const copyFromFiles = ref([])
const copyFromSelectedFile = ref(null)
const copyFromOptions = ref({
  copyQuestions: true,
  copySettings: true,
  copySections: true,
  removeCurrentQuestions: false,
  copyFullExamAsJson: false
})
const copyFromLoading = ref(false)

// Full Exam AI Generation State
const fullExamDialogOpen = ref(false)
const fullExamStep = ref(1)
const fullExamPrompt = ref('')
const fullExamResponse = ref('')
const examConfig = ref({
  examTitle: '',
  examSubject: '',
  examGrade: '',
  examDuration: '',
  totalMarks: '',
  sections: []
})
const currentSectionIndex = ref(0)
const missingInfoPrompts = ref([])

// AI Chat Integration State
const aiChatDialogOpen = ref(false)
const aiChatMessages = ref([])
const aiChatInput = ref('')
const aiApiKey = ref('')
const aiModel = ref('gpt-4')
const aiLoading = ref(false)
const conversationMode = ref('exam_generation') // 'exam_generation' or 'question_asking'
const generatedExamData = ref(null)

// Question Validation and Revision State

// Computed properties for Quick Mode
const allFieldsFilled = computed(() => {
  return !!(
    quickModeContext.value.subject &&
    quickModeContext.value.grade &&
    quickModeContext.value.examType &&
    quickModeContext.value.totalQuestions
  )
})

const missingFieldsList = computed(() => {
  const missing = []
  if (!quickModeContext.value.subject) missing.push('Subject')
  if (!quickModeContext.value.grade) missing.push('Grade Level')
  if (!quickModeContext.value.examType) missing.push('Exam Type')
  if (!quickModeContext.value.totalQuestions) missing.push('Question Count')
  return missing.join(', ')
})

const quickModePhase = computed(() => {
  return allFieldsFilled.value ? 'confirmed' : 'gathering'
})
const validationDialogOpen = ref(false)
const questionErrors = ref([])
const questionsForValidation = ref('')
const revisedQuestions = ref('')
const validationPrompt = ref('')
const aiErrorFeedback = ref('')
const parsedAiFeedback = ref([])
const optionsOpen = ref(false)
const settingsTab = ref('general')
const footerSettingsTab = ref('layout')
const selectedSettingsPreset = ref('default')
const settingsPresets = ref([
  { label: 'Default', value: 'default' }
])
const settingsPanelOpen = ref(false)
const editingTitle = ref(false)

// File manager reference
const fileManagerRef = ref(null)
const lastSavedExamId = ref(null)
const openingPrintHtml = ref(false)
const pdfGenerating = ref(false)

const LAST_EXAM_ID_STORAGE_KEY = 'rtp_v3_lastSavedExamId'

// Print preview dialog
const printPreviewOpen = ref(false)
const printPreviewHtml = ref('')

// Delete confirmation dialog
const deleteConfirmOpen = ref(false)
const questionToDelete = ref(null)

// Edit question dialog
const editQuestionOpen = ref(false)
const questionToEdit = ref(null)
const editQuestionPrompt = ref('')
// Computed page title for Head component
const pageTitle = computed(() => {
  return pageOptions.value.examTitle?.enabled && pageOptions.value.examTitle?.text
    ? pageOptions.value.examTitle.text
    : 'Exam Builder - Ready to Print'
})

function applyRecommendedFooterOneLineTopAlign() {
  if (!pageOptions.value?.printFooter) return
  pageOptions.value.printFooter.enabled = true
  pageOptions.value.printFooter.singleLine = true
  pageOptions.value.printFooter.singleLineTopAlign = true
  pageOptions.value.printFooter.showPageNumbers = true
  savePageState()
}

function applyVisibleInlinePageNumberPreset() {
  if (!pageOptions.value?.printFooter) return

  pageOptions.value.printFooter.enabled = true
  pageOptions.value.printFooter.reserveSpace = true
  pageOptions.value.printFooter.singleLine = true
  pageOptions.value.printFooter.singleLineTopAlign = true
  pageOptions.value.printFooter.showPageNumbers = true
  pageOptions.value.printFooter.pageNumberPosition = 'bottom-right'
  pageOptions.value.printFooter.applyOffsetToPageNumbers = false
  pageOptions.value.printFooter.pageNumberColor = '#000000'

  const currentFontSize = Number(pageOptions.value.printFooter.pageNumberFontSize)
  pageOptions.value.printFooter.pageNumberFontSize =
    Number.isFinite(currentFontSize) && currentFontSize >= 10 ? currentFontSize : 10

  savePageState()
}

const pageOptions = ref({
  examTitle: {
    enabled: true,
    text: 'New Exam'
  },
  showMarksPerQuestion: true,
  showExplanationUnderQuestion: false,
  showCorrectAnswerUnderQuestion: false,
  paginationMode: 'strict',
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
    bottomOffsetMm: 0,
    mode: 'html',
    html: '',
    imageUrl: '',
    imageFit: 'contain',
    textFontSizePt: 12,
    textColor: '#000000',
    reserveSpace: true,
    singleLine: true,
    singleLineTopAlign: false,
    showTopBorder: false,
    showPageNumbers: true,
    pageNumberPosition: 'bottom-center',
    pageNumberFormat: 'page',
    pageNumberFontSize: 10,
    pageNumberColor: '#000000',
    pageNumberStartAtQuestion: 1,
    pageNumberStartValue: 1,
    applyOffsetToPageNumbers: false,
    lastPageText: '',
    useLastPageText: false
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
  answerKey: {
    enabled: false,
    showAtEnd: true,
    showNotes: true,
    notesText: '',
    title: '',
    template: 'full',
    mcqShowOptionText: false,
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

/** How many questions have a correct answer set (for the settings banner). */
const answerKeyCoverage = computed(() => {
  const qs = sampleQuestions.value || []
  const total = qs.length
  let covered = 0
  qs.forEach(q => {
    const v = q.content?.correct_option_index ?? q.content?.correct_answer ?? q.correct_answer
    if (v !== undefined && v !== null && v !== '') covered++
  })
  return { total, covered, missing: total - covered }
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
    ' .exam-header { width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; border: 3px double #00AEEF; margin-top: 0; }' +
    ' .exam-header td { border: 1px solid #00AEEF; padding: 4px 8px; font-weight: bold; color: #000; font-size: 13px; }' +
    ' .center-text { text-align: center; font-size: 14px; }' +
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

function openSaveAsDialog() {
  const currentTitle = pageOptions.value.examTitle?.enabled ? pageOptions.value.examTitle.text : ''
  $q.dialog({
    title: 'Save As',
    message: 'Enter a name for the copy:',
    prompt: {
      model: currentTitle ? `${currentTitle} (Copy)` : 'Untitled Exam (Copy)',
      type: 'text'
    },
    cancel: true,
    persistent: true
  }).onOk(async (fileName) => {
    if (!fileName || !fileName.trim()) {
      $q.notify({ type: 'warning', message: 'Please enter a valid name.', position: 'top' })
      return
    }
    await handleSaveAs(fileName.trim())
  })
}

function editExamTitleInline() {
  $q.dialog({
    title: 'Edit Exam Title',
    message: 'Enter the exam title:',
    prompt: {
      model: pageOptions.value.examTitle.text,
      type: 'text'
    },
    cancel: true,
    persistent: true
  }).onOk(data => {
    pageOptions.value.examTitle.text = data
    savePageState()
  })
}

function duplicateCurrentExam() {
  openSaveAsDialog()
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

function isPageBreakAfter(question) {
  const qid = String(question?.id)
  if (!qid) return false
  const idx = printSequence.value.findIndex(q => String(q?.id) === qid)
  const nextQuestion = idx >= 0 ? printSequence.value[idx + 1] : null
  if (!nextQuestion) return false
  return isPageBreakBefore(nextQuestion)
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
  { 
    id: 'sec_default', 
    title: 'Choose the correct answer :-', 
    instructions: '',
    forceQuestionsToEssay: false,
    lineBefore: false,
    lineAfter: false,
    pageBreakBefore: false,
    lineStyle: 'solid',
    lineThickness: 1,
    lineColor: '#000000'
  }
])

const questionSectionMap = ref({})

// Inline (PDF-like) preview mode
const pdfPreviewMode = ref(true)
const pdfInlineEditMode = ref(false)
const pdfPreviewHtml = ref('')

function refreshPdfPreviewHtml() {
  try {
    pdfPreviewHtml.value = generatePrintHTML()
  } catch (e) {
    console.error('Failed to build PDF preview HTML', e)
    pdfPreviewHtml.value = ''
  }
}

watch(pdfPreviewMode, (v) => {
  if (!v) pdfInlineEditMode.value = false
  if (v) refreshPdfPreviewHtml()
})

watch(pdfInlineEditMode, (v) => {
  if (v) {
    isEditMode.value = true
  }
})

watch([sampleQuestions, pageOptions, sections, questionSectionMap], () => {
  if (pdfPreviewMode.value) refreshPdfPreviewHtml()
}, { deep: true })

const sectionOptions = computed(() => sections.value.map(s => ({ label: s.title, value: s.id })))

// AI Configuration
const aiConfig = ref({
  topic: '',
  grade: '',
  questionCount: 5,
  questionTypes: [],
  difficulty: 'mixed',
  latexSupport: true,
  htmlSupport: false,
  includeSolutions: false,
  includeExplanations: false,
  instructions: ''
})

const showPreviewDialog = ref(false)

// Preview table columns
const previewColumns = [
  { name: 'id', label: 'ID', field: 'id', align: 'left' },
  { name: 'type', label: 'Type', field: 'type', align: 'left' },
  { name: 'marks', label: 'Marks', field: 'marks', align: 'left' },
  { name: 'preview', label: 'Preview', field: 'preview', align: 'left' },
  { name: 'status', label: 'Status', field: 'status', align: 'left' },
  { name: 'errors', label: 'Issues', field: 'errors', align: 'left' }
]

// Computed properties
const validQuestions = computed(() => parsedQuestions.value.filter(q => q.valid))

// AI Import Functions
function openAIDialog() {
  aiDialogOpen.value = true
  step.value = quickMode.value ? 0 : 1
  resetAIState()
}

function openQuickImportDialog() {
  quickMode.value = true
  aiDialogOpen.value = true
  step.value = 0
  resetAIState()
}

function openSmartExamDialog() {
  smartExamDialogOpen.value = true
  smartExamStep.value = 1
  smartExamConfig.value = {
    subject: '',
    grade: '',
    examType: '',
    duration: '',
    totalQuestions: 10,
    questionTypes: ['mcq', 'short_answer'],
    difficulty: 'mixed'
  }
  smartExamPrompt.value = ''
  smartExamResponse.value = ''
  smartExamSummary.value = null
  smartExamQuestions.value = []
  smartExamIssues.value = []
  smartExamRecommendations.value = []
}

function generateSmartExamPrompt() {
  const config = smartExamConfig.value
  let prompt = `# Smart Exam Generation - Information Gathering\n\n`
  prompt += `I want to generate a complete exam. Here's what I have:\n\n`

  // Show what we have
  prompt += `## Current Information:\n`
  if (config.subject) prompt += `- Subject: ${config.subject}\n`
  if (config.grade) prompt += `- Grade Level: ${config.grade}\n`
  if (config.examType) prompt += `- Exam Type: ${config.examType}\n`
  if (config.duration) prompt += `- Duration: ${config.duration}\n`
  if (config.totalQuestions) prompt += `- Total Questions: ${config.totalQuestions}\n`
  if (config.questionTypes.length) prompt += `- Question Types: ${config.questionTypes.join(', ')}\n`
  if (config.difficulty) prompt += `- Difficulty: ${config.difficulty}\n`

  // Check what's missing
  const missing = []
  if (!config.subject) missing.push('subject')
  if (!config.grade) missing.push('grade level')
  if (!config.examType) missing.push('exam type')
  if (!config.duration) missing.push('duration')
  if (!config.totalQuestions) missing.push('question count')

  if (missing.length > 0) {
    prompt += `\n## Missing Information:\n`
    missing.forEach(m => prompt += `- ${m}\n`)
    prompt += `\nPlease provide 3-4 suggestions for each missing field and ask me to confirm.\n`
  } else {
    prompt += `\nAll information provided. Please provide a summary of what you will generate and ask for confirmation.\n`
  }

  prompt += `\nAfter confirmation, you will generate the complete exam JSON with proper validation.\n`
  smartExamPrompt.value = prompt
  smartExamStep.value = 2
}

function copySmartPrompt() {
  navigator.clipboard.writeText(smartExamPrompt.value)
  $q.notify({ type: 'positive', message: 'Prompt copied to clipboard', position: 'top' })
}

function pasteSmartResponse() {
  navigator.clipboard.readText().then(text => {
    smartExamResponse.value = text
  }).catch(() => {
    $q.notify({ type: 'warning', message: 'Could not read from clipboard', position: 'top' })
  })
}

function parseSmartResponse() {
  try {
    const response = smartExamResponse.value
    // Try to parse as JSON first
    try {
      const parsed = JSON.parse(response)
      if (parsed.summary) {
        smartExamSummary.value = parsed.summary
      }
      if (parsed.questions) {
        smartExamQuestions.value = parsed.questions
      }
    } catch {
      // If not JSON, extract summary from text
      const summaryMatch = response.match(/## Summary\s*\n([\s\S]*?)(?=\n##|$)/)
      if (summaryMatch) {
        const summaryText = summaryMatch[1]
        smartExamSummary.value = {
          'Subject': smartExamConfig.value.subject || 'To be determined',
          'Grade': smartExamConfig.value.grade || 'To be determined',
          'Exam Type': smartExamConfig.value.examType || 'To be determined',
          'Duration': smartExamConfig.value.duration || 'To be determined',
          'Total Questions': smartExamConfig.value.totalQuestions,
          'Question Types': smartExamConfig.value.questionTypes.join(', '),
          'Difficulty': smartExamConfig.value.difficulty
        }
      }
    }
    smartExamStep.value = 3
  } catch (e) {
    $q.notify({ type: 'negative', message: 'Failed to parse response: ' + e.message, position: 'top' })
  }
}

function confirmSmartExam() {
  // Generate the full prompt for exam creation
  const config = smartExamConfig.value
  let prompt = `# Generate Complete Exam JSON\n\n`
  prompt += `Generate a complete exam with the following specifications:\n\n`
  prompt += `## Exam Specifications:\n`
  prompt += `- Subject: ${config.subject}\n`
  prompt += `- Grade Level: ${config.grade}\n`
  prompt += `- Exam Type: ${config.examType}\n`
  prompt += `- Duration: ${config.duration}\n`
  prompt += `- Total Questions: ${config.totalQuestions}\n`
  prompt += `- Question Types: ${config.questionTypes.join(', ')}\n`
  prompt += `- Difficulty: ${config.difficulty}\n\n`

  prompt += `## Requirements:\n`
  prompt += `1. Generate valid JSON for the exam questions\n`
  prompt += `2. Each question must have: id, type, ver, marks, content, options (for MCQ), correct_option_index\n`
  prompt += `3. Ensure all questions are appropriate for the grade level\n`
  prompt += `4. Mix difficulty levels as specified\n`
  prompt += `5. Include clear and unambiguous questions\n\n`

  prompt += `## JSON Output Format:\n\n`
  prompt += `Return ONLY valid JSON in this format:\n\n`
  prompt += `\`\`\`json\n`
  prompt += `[\n`
  prompt += `  {\n`
  prompt += `    "id": 1,\n`
  prompt += `    "type": "multiple_choice",\n`
  prompt += `    "ver": 3,\n`
  prompt += `    "marks": 2,\n`
  prompt += `    "content": {\n`
  prompt += `      "prompt": "What is $2 + 2$?",\n`
  prompt += `      "options": ["A", "B", "C", "D"],\n`
  prompt += `      "correct_option_index": 0\n`
  prompt += `    }\n`
  prompt += `  }\n`
  prompt += `]\n`
  prompt += `\`\`\`\n`

  smartExamPrompt.value = prompt
  smartExamStep.value = 4
}

function validateSmartExam() {
  // Generate validation prompt
  let prompt = `# Validate Exam Questions\n\n`
  prompt += `Review the following exam questions for issues:\n\n`
  prompt += JSON.stringify(smartExamQuestions.value, null, 2)
  prompt += `\n\n## Check for:\n`
  prompt += `1. Invalid question types\n`
  prompt += `2. Missing required fields\n`
  prompt += `3. Ambiguous or unclear questions\n`
  prompt += `4. Inappropriate difficulty for grade level\n`
  prompt += `5. Duplicate questions\n`
  prompt += `6. Formatting issues\n\n`

  prompt += `## Output Format:\n`
  prompt += `Return JSON with:\n`
  prompt += `{\n`
  prompt += `  "issues": ["list of issues found"],\n`
  prompt += `  "recommendations": [\n`
  prompt += `    {\n`
  prompt += `      "category": "category name",\n`
  prompt += `      "issue": "description of issue",\n`
  prompt += `      "recommendation": "how to fix",\n`
  prompt += `      "priority": "high|medium|low"\n`
  prompt += `    }\n`
  prompt += `  ]\n`
  prompt += `}\n`

  smartExamPrompt.value = prompt
  smartExamStep.value = 5

  // Simulate validation (in real implementation, this would call AI)
  setTimeout(() => {
    smartExamIssues.value = []
    smartExamRecommendations.value = [
      {
        category: 'Question Clarity',
        issue: 'Some questions may be ambiguous',
        recommendation: 'Review questions for clarity and specificity',
        priority: 'medium'
      },
      {
        category: 'Difficulty Balance',
        issue: 'Ensure difficulty matches grade level',
        recommendation: 'Adjust question complexity accordingly',
        priority: 'high'
      }
    ]
  }, 500)
}

function importSmartExam() {
  // Import the generated questions
  sampleQuestions.value = [...smartExamQuestions.value]
  hasUnsavedChanges.value = true
  smartExamDialogOpen.value = false
  $q.notify({ type: 'positive', message: 'Exam imported successfully!', position: 'top' })
}

function openCopyFromDialog() {
  copyFromDialogOpen.value = true
  copyFromSelectedFile.value = null
  copyFromOptions.value = {
    copyQuestions: true,
    copySettings: true,
    copySections: true,
    removeCurrentQuestions: false,
    copyFullExamAsJson: false
  }
  loadCopyFromFiles()
}

async function loadCopyFromFiles() {
  copyFromLoading.value = true
  try {
    const response = await fetch('/api/exam/ready-to-print/list-saved-exams')
    const data = await response.json()
    if (data.files) {
      copyFromFiles.value = data.files
    } else {
      $q.notify({ type: 'negative', message: 'Failed to load saved exams', position: 'top' })
    }
  } catch (error) {
    $q.notify({ type: 'negative', message: 'Error loading saved exams: ' + error.message, position: 'top' })
  } finally {
    copyFromLoading.value = false
  }
}

async function handleCopyFrom() {
  if (!copyFromSelectedFile.value) return

  copyFromLoading.value = true
  try {
    const response = await fetch(`/api/exam/ready-to-print/load-saved-exam/${copyFromSelectedFile.value}`)
    const data = await response.json()

    if (data.success) {
      const examData = data.data

      // Remove current questions if option is selected
      if (copyFromOptions.value.removeCurrentQuestions) {
        sampleQuestions.value = []
      }

      // Copy questions
      if (copyFromOptions.value.copyQuestions && examData.questions) {
        const newQuestions = examData.questions.map((q, index) => ({
          ...q,
          id: sampleQuestions.value.length + index + 1
        }))
        sampleQuestions.value = [...sampleQuestions.value, ...newQuestions]
      }

      // Copy settings
      if (copyFromOptions.value.copySettings && examData.pageOptions) {
        pageOptions.value = JSON.parse(JSON.stringify(examData.pageOptions))
      }

      // Copy sections
      if (copyFromOptions.value.copySections) {
        if (examData.sections) {
          sections.value = JSON.parse(JSON.stringify(examData.sections))
        }
        if (examData.questionSectionMap) {
          questionSectionMap.value = JSON.parse(JSON.stringify(examData.questionSectionMap))
        }
      }

      hasUnsavedChanges.value = true
      copyFromDialogOpen.value = false
      $q.notify({ type: 'positive', message: 'Copied successfully!', position: 'top' })
    } else {
      $q.notify({ type: 'negative', message: 'Failed to load exam data', position: 'top' })
    }
  } catch (error) {
    $q.notify({ type: 'negative', message: 'Error copying exam: ' + error.message, position: 'top' })
  } finally {
    copyFromLoading.value = false
  }
}

function copyQuestionJsonExample() {
  const example = {
    id: "unique_id",
    type: "multiple_choice",
    ver: 3,
    marks: 1,
    content: {
      prompt: "Question text here",
      options: ["Option A", "Option B", "Option C", "Option D"],
      correct_option_index: 0,
      explanation: "Optional explanation text"
    }
  }
  navigator.clipboard.writeText(JSON.stringify(example, null, 2))
    .then(() => {
      $q.notify({ type: 'positive', message: 'Question JSON copied to clipboard', position: 'top' })
    })
    .catch((err) => {
      $q.notify({ type: 'negative', message: 'Failed to copy: ' + err.message, position: 'top' })
    })
}

function copySettingsJsonExample() {
  const example = {
    examTitle: { enabled: true, text: "Exam Name" },
    showMarksPerQuestion: true,
    showExplanationUnderQuestion: false,
    showCorrectAnswerUnderQuestion: false,
    printHeader: { enabled: false },
    printFooter: { enabled: true },
    answerKey: { enabled: false },
    mcqOptions: { labelStyle: "letter" }
  }
  navigator.clipboard.writeText(JSON.stringify(example, null, 2))
    .then(() => {
      $q.notify({ type: 'positive', message: 'Settings JSON copied to clipboard', position: 'top' })
    })
    .catch((err) => {
      $q.notify({ type: 'negative', message: 'Failed to copy: ' + err.message, position: 'top' })
    })
}

function copyCurrentExamJson() {
  const examData = {
    questions: sampleQuestions.value,
    settings: {
      examTitle: pageOptions.value.examTitle,
      showMarksPerQuestion: pageOptions.value.showMarksPerQuestion,
      showExplanationUnderQuestion: pageOptions.value.showExplanationUnderQuestion,
      showCorrectAnswerUnderQuestion: pageOptions.value.showCorrectAnswerUnderQuestion,
      printHeader: pageOptions.value.printHeader,
      printFooter: pageOptions.value.printFooter,
      firstPage: pageOptions.value.firstPage,
      lastPage: pageOptions.value.lastPage,
      answerKey: pageOptions.value.answerKey,
      questionSeparator: pageOptions.value.questionSeparator,
      mcqOptions: pageOptions.value.mcqOptions,
      sectionTotal: pageOptions.value.sectionTotal,
      questionNumbering: pageOptions.value.questionNumbering,
      pageLayout: pageOptions.value.pageLayout
    },
    sections: sections.value,
    questionSectionMap: questionSectionMap.value
  }
  navigator.clipboard.writeText(JSON.stringify(examData, null, 2))
    .then(() => {
      $q.notify({ type: 'positive', message: 'Current exam JSON copied to clipboard', position: 'top' })
    })
    .catch((err) => {
      $q.notify({ type: 'negative', message: 'Failed to copy: ' + err.message, position: 'top' })
    })
}

function resetAIState() {
  generatedPrompt.value = ''
  aiResponse.value = ''
  parsedQuestions.value = []
  selectedQuestions.value = []
  pasteError.value = ''
  quickMode.value = false
  quickModeContext.value = {
    subject: '',
    grade: '',
    examType: '',
    totalQuestions: null
  }
}

// Quick Mode Functions
function enableQuickMode() {
  quickMode.value = true
  step.value = 0
}

function generateQuickModePrompt() {
  const { subject, grade, examType, totalQuestions } = quickModeContext.value
  
  // Check what information is missing
  const missingInfo = []
  if (!subject) missingInfo.push('subject')
  if (!grade) missingInfo.push('grade level')
  if (!examType) missingInfo.push('exam type')
  if (!totalQuestions) missingInfo.push('number of questions')
  
  let prompt = ''
  
  // PHASE 1: Information gathering (if anything is missing)
  if (missingInfo.length > 0) {
    prompt = generateInformationRequestPrompt(subject, grade, examType, totalQuestions, missingInfo)
  } 
  // PHASE 2: Exam generation (all info provided)
  else {
    prompt = generateExamCreationPrompt(subject, grade, examType, totalQuestions)
  }
  
  generatedPrompt.value = prompt
}

function generateInformationRequestPrompt(subject, grade, examType, totalQuestions, missingInfo) {
  let prompt = `# Exam Generation - Information Gathering\n\n`
  prompt += `I want to generate a complete exam, but I need some information first.\n\n`
  
  // Show what we have
  prompt += `## Information I Have:\n`
  if (subject) prompt += `- Subject: ${subject}\n`
  if (grade) prompt += `- Grade Level: ${grade}\n`
  if (examType) prompt += `- Exam Type: ${examType}\n`
  if (totalQuestions) prompt += `- Total Questions: ${totalQuestions}\n`
  if (missingInfo.length === 4) prompt += `- None (I want you to suggest everything)\n`
  
  // Show what's missing
  prompt += `\n## Missing Information:\n`
  missingInfo.forEach(info => {
    prompt += `- ${info.charAt(0).toUpperCase() + info.slice(1)}\n`
  })
  
  // Provide options for missing fields
  prompt += `\n## Your Task:\n\n`
  prompt += `Please provide 3-4 suggestions for the missing information and ask me to confirm.\n\n`
  
  if (!subject) {
    prompt += `**Subject Options:**\n1. Mathematics\n2. Science\n3. English Language Arts\n4. Social Studies\n\n`
  }
  
  if (!grade) {
    prompt += `**Grade Level Options:**\n1. Elementary (Grades 3-5)\n2. Middle School (Grades 6-8)\n3. High School (Grades 9-12)\n\n`
  }
  
  if (!examType) {
    prompt += `**Exam Type Options:**\n1. Quiz (10-15 questions, 20-30 minutes)\n2. Mid-term Exam (20-30 questions, 60-90 minutes)\n3. Final Exam (30-50 questions, 90-120 minutes)\n4. Practice Test (15-25 questions, 45-60 minutes)\n\n`
  }
  
  if (!totalQuestions) {
    prompt += `**Suggested Question Count:**\nBased on the exam type, I recommend:\n- Quiz: 10-15 questions\n- Mid-term: 20-30 questions\n- Final: 30-50 questions\n- Practice: 15-25 questions\n\n`
  }
  
  prompt += `**Please respond with:**\n`
  prompt += `1. Your recommendations for the missing information\n`
  prompt += `2. A brief explanation of why these choices work well together\n`
  prompt += `3. Ask me to confirm before generating the exam\n\n`
  
  prompt += `**Example response format:**\n`
  prompt += `"Based on your requirements, I suggest:\n`
  prompt += `- Subject: Mathematics\n`
  prompt += `- Grade Level: Grade 8\n`
  prompt += `- Exam Type: Mid-term Exam\n`
  prompt += `- Total Questions: 25 questions\n\n`
  prompt += `This combination will create a comprehensive assessment covering algebra, geometry, and problem-solving skills appropriate for 8th graders.\n\n`
  prompt += `Shall I proceed with generating the exam with these specifications? Please confirm or let me know if you'd like to adjust anything."\n\n`
  
  prompt += `**Important:** Do NOT generate the exam yet. Just provide suggestions and wait for my confirmation.\n`
  
  return prompt
}

function generateExamCreationPrompt(subject, grade, examType, totalQuestions) {
  let prompt = `# Generate Complete Exam - CONFIRMED\n\n`
  prompt += `Generate a complete, well-structured exam with the following specifications:\n\n`
  
  prompt += `## Exam Specifications:\n`
  prompt += `- Subject: ${subject}\n`
  prompt += `- Grade Level: ${grade}\n`
  prompt += `- Exam Type: ${examType}\n`
  prompt += `- Total Questions: ${totalQuestions} questions\n\n`
  
  prompt += `## Structure Requirements:\n\n`
  prompt += `### 1. Create Logical Sections\n`
  prompt += `- Divide questions into 3-5 sections\n`
  prompt += `- Each section should focus on a specific skill or topic\n`
  prompt += `- Examples: "Section 1: Multiple Choice", "Section 2: Short Answer", "Section 3: Problem Solving"\n`
  prompt += `- Include brief instructions for each section\n\n`
  
  prompt += `### 2. Question Distribution\n`
  prompt += `- Mix question types across sections:\n`
  prompt += `  - multiple_choice: 40-50% of questions\n`
  prompt += `  - short_answer: 30-40% of questions\n`
  prompt += `  - true_false: 10-20% of questions\n`
  prompt += `- Vary difficulty: 30% easy, 50% medium, 20% hard\n`
  prompt += `- Assign appropriate marks: easy (1-2), medium (2-3), hard (3-5)\n\n`
  
  prompt += `### 3. Content Requirements\n`
  prompt += `- Use LaTeX for mathematical expressions: $\\frac{3}{4}$, $x^2$, $\\sqrt{16}$\n`
  prompt += `- Multiple choice must have 4 options (A, B, C, D)\n`
  prompt += `- Include correct_option_index (0-based) for each MCQ question\n`
  prompt += `- Add explanation for complex questions\n`
  prompt += `- Make content age-appropriate for ${grade}\n\n`
  
  prompt += `## JSON Output Format:\n\n`
  prompt += `Return ONLY a valid JSON array of questions (no additional text):\n\n`
  prompt += `\`\`\`json\n`
  prompt += `[\n`
  prompt += `  {\n`
  prompt += `    "id": 1,\n`
  prompt += `    "type": "multiple_choice",\n`
  prompt += `    "ver": 3,\n`
  prompt += `    "marks": 2,\n`
  prompt += `    "section": "Section 1: Multiple Choice",\n`
  prompt += `    "content": {\n`
  prompt += `      "prompt": "What is $2 + 2$?",\n`
  prompt += `      "options": ["A", "B", "C", "D"],\n`
  prompt += `      "correct_option_index": 1,\n`
  prompt += `      "explanation": "Basic addition: 2 + 2 = 4"\n`
  prompt += `    }\n`
  prompt += `  },\n`
  prompt += `  {\n`
  prompt += `    "id": 2,\n`
  prompt += `    "type": "short_answer",\n`
  prompt += `    "ver": 3,\n`
  prompt += `    "marks": 3,\n`
  prompt += `    "section": "Section 2: Short Answer",\n`
  prompt += `    "content": {\n`
  prompt += `      "prompt": "Solve for x: $x + 5 = 12$",\n`
  prompt += `      "correct_option_index": "x = 7",\n`
  prompt += `      "explanation": "Subtract 5 from both sides: x = 12 - 5 = 7"\n`
  prompt += `    }\n`
  prompt += `  },\n`
  prompt += `  {\n`
  prompt += `    "id": 3,\n`
  prompt += `    "type": "true_false",\n`
  prompt += `    "ver": 3,\n`
  prompt += `    "marks": 1,\n`
  prompt += `    "section": "Section 3: True or False",\n`
  prompt += `    "content": {\n`
  prompt += `      "prompt": "The sum of angles in a triangle is 180 degrees.",\n`
  prompt += `      "correct_option_index": "True",\n`
  prompt += `      "explanation": "This is a fundamental property of triangles."\n`
  prompt += `    }\n`
  prompt += `  }\n`
  prompt += `]\n`
  prompt += `\`\`\`\n\n`
  
  prompt += `## Critical Requirements:\n`
  prompt += `- Return ONLY the JSON array (no text before or after)\n`
  prompt += `- Do NOT include citations like [cite: 219]\n`
  prompt += `- Ensure all LaTeX syntax is correct\n`
  prompt += `- All questions must have sequential IDs (1, 2, 3, ...)\n`
  prompt += `- Include "section" field for each question\n`
  prompt += `- Validate JSON before returning\n`
  prompt += `- Generate exactly ${totalQuestions} questions\n`
  
  return prompt
}

async function loadPageState() {
  isLoadingState.value = true
  try {
    const response = await fetch('/exam/ready-to-print/api/load-data-v3')
    const data = await response.json()
    
    // Restore sections first (needed for question-section mapping)
    if (data?.sections && Array.isArray(data.sections) && data.sections.length > 0) {
      sections.value = data.sections
    }
    
    // Ensure sections always has at least a default section
    if (sections.value.length === 0) {
      sections.value = [
        { 
          id: 'sec_default', 
          title: 'Choose the correct answer :-', 
          instructions: '',
          forceQuestionsToEssay: false,
          lineBefore: false,
          lineAfter: false,
          pageBreakBefore: false,
          lineStyle: 'solid',
          lineThickness: 1,
          lineColor: '#000000'
        }
      ]
    }
    
    // Restore question-section mapping with validation
    const validSectionIds = new Set(sections.value.map(s => s.id))
    if (data?.questionSectionMap && typeof data.questionSectionMap === 'object') {
      const normalizedMap = {}
      Object.keys(data.questionSectionMap).forEach(key => {
        const sectionId = data.questionSectionMap[key]
        // Only keep mappings to valid sections
        if (validSectionIds.has(sectionId)) {
          normalizedMap[key] = sectionId
        }
      })
      questionSectionMap.value = normalizedMap
    }
    
    // Restore questions
    if (data?.questions) sampleQuestions.value = data.questions
    
    if (data?.settings) {
      // Deep merge settings to preserve all nested properties
      pageOptions.value = deepMerge(pageOptions.value, data.settings)
      // Force enable footer and page numbers regardless of cached settings
      if (pageOptions.value.printFooter) {
        pageOptions.value.printFooter.enabled = true
        pageOptions.value.printFooter.showPageNumbers = true
      }
      // Restore page breaks if they exist
      if (data?.pageBreaks && typeof data.pageBreaks === 'object') {
        if (!pageOptions.value.questionNumbering) pageOptions.value.questionNumbering = {}
        pageOptions.value.questionNumbering.pageBreaksBefore = data.pageBreaks
      }
    }
    
    // Ensure every question has a valid section mapping (do this after all data is loaded)
    const defaultSectionId = sections.value[0]?.id
    sampleQuestions.value.forEach(q => {
      const qid = String(q?.id)
      // Try multiple ID formats for the map lookup (handles both "1" and "q1" formats)
      const mapKeys = [qid, 'q' + qid]
      let mappedSection = null
      for (const key of mapKeys) {
        if (questionSectionMap.value[key]) {
          mappedSection = questionSectionMap.value[key]
          break
        }
      }
      // Use the question's section property if available and valid
      if (!mappedSection && q.section && validSectionIds.has(q.section)) {
        mappedSection = q.section
      }
      // Fall back to default section
      if (!mappedSection && defaultSectionId) {
        mappedSection = defaultSectionId
      }
      if (mappedSection) {
        questionSectionMap.value[qid] = mappedSection
      }
    })
  } catch (e) {
    console.error('Failed to load page state', e)
  } finally {
    isLoadingState.value = false
  }
}

// Deep merge utility function
function deepMerge(target, source) {
  const output = { ...target }
  if (isObject(target) && isObject(source)) {
    Object.keys(source).forEach(key => {
      if (isObject(source[key])) {
        if (!(key in target)) {
          Object.assign(output, { [key]: source[key] })
        } else {
          output[key] = deepMerge(target[key], source[key])
        }
      } else {
        Object.assign(output, { [key]: source[key] })
      }
    })
  }
  return output
}

function isObject(item) {
  return item && typeof item === 'object' && !Array.isArray(item)
}

// Settings Presets Management
const SETTINGS_PRESETS_KEY = 'exam-settings-presets'

function loadSettingsPresets() {
  try {
    const saved = localStorage.getItem(SETTINGS_PRESETS_KEY)
    if (saved) {
      const parsed = JSON.parse(saved)
      settingsPresets.value = [
        { label: 'Default', value: 'default' },
        ...parsed
      ]
    }
  } catch (e) {
    console.error('Failed to load settings presets', e)
  }
}

function saveSettingsPresets() {
  try {
    const customPresets = settingsPresets.value.filter(p => p.value !== 'default')
    localStorage.setItem(SETTINGS_PRESETS_KEY, JSON.stringify(customPresets))
  } catch (e) {
    console.error('Failed to save settings presets', e)
  }
}

function saveSettingsAsPreset() {
  $q.dialog({
    title: 'Save Settings as Preset',
    message: 'Enter a name for this preset:',
    prompt: {
      model: '',
      type: 'text',
      attrs: { maxlength: 50 }
    },
    cancel: true,
    persistent: true
  }).onOk((name) => {
    if (!name || !name.trim()) {
      $q.notify({ type: 'warning', message: 'Please enter a name', position: 'top' })
      return
    }
    const presetValue = 'preset_' + Date.now()
    const preset = {
      label: name.trim(),
      value: presetValue,
      settings: JSON.parse(JSON.stringify(pageOptions.value))
    }
    settingsPresets.value.push(preset)
    selectedSettingsPreset.value = presetValue
    saveSettingsPresets()
    $q.notify({ type: 'positive', message: 'Preset saved', position: 'top' })
  })
}

function applySettingsPreset(presetValue) {
  if (presetValue === 'default') {
    // Reset to default settings
    pageOptions.value = {
      examTitle: { enabled: true, text: 'New Exam' },
      showMarksPerQuestion: true,
      showExplanationUnderQuestion: false,
      showCorrectAnswerUnderQuestion: false,
      paginationMode: 'strict',
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
        bottomOffsetMm: 0,
        mode: 'html',
        html: '',
        imageUrl: '',
        imageFit: 'contain',
        textFontSizePt: 12,
        textColor: '#000000',
        reserveSpace: true,
        singleLine: true,
        singleLineTopAlign: false,
        showTopBorder: false,
        showPageNumbers: true,
        pageNumberPosition: 'bottom-center',
        pageNumberFormat: 'page',
        pageNumberFontSize: 10,
        pageNumberColor: '#000000',
        pageNumberStartAtQuestion: 1,
        pageNumberStartValue: 1,
        applyOffsetToPageNumbers: false,
        lastPageText: '',
        useLastPageText: false
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
      answerKey: {
        enabled: false,
        showAtEnd: true,
        showNotes: true,
        notesText: '',
        title: '',
        template: 'full',
        mcqShowOptionText: false,
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
    }
    $q.notify({ type: 'info', message: 'Reset to default settings', position: 'top' })
  } else {
    const preset = settingsPresets.value.find(p => p.value === presetValue)
    if (preset && preset.settings) {
      pageOptions.value = deepMerge(pageOptions.value, preset.settings)
      $q.notify({ type: 'positive', message: 'Preset applied', position: 'top' })
    }
  }
  savePageState()
}

function deleteSettingsPreset() {
  if (selectedSettingsPreset.value === 'default') return
  
  $q.dialog({
    title: 'Delete Preset',
    message: 'Are you sure you want to delete this preset?',
    cancel: true,
    persistent: true
  }).onOk(() => {
    settingsPresets.value = settingsPresets.value.filter(p => p.value !== selectedSettingsPreset.value)
    selectedSettingsPreset.value = 'default'
    saveSettingsPresets()
    $q.notify({ type: 'positive', message: 'Preset deleted', position: 'top' })
  })
}

async function pasteQuestionImageUrlFromClipboard() {
  try {
    const text = await navigator.clipboard.readText()
    if (!text || !String(text).trim()) {
      $q.notify({ type: 'warning', message: 'Clipboard is empty.', position: 'top' })
      return
    }
    imageEdit.value.url = String(text).trim()
  } catch (e) {
    console.error('Paste URL failed', e)
    $q.notify({ type: 'warning', message: 'Clipboard paste blocked. Please allow clipboard permission or paste manually.', position: 'top' })
  }
}

async function pasteQuestionImageFromClipboard() {
  try {
    if (!navigator.clipboard?.read) {
      $q.notify({ type: 'warning', message: 'Clipboard image paste is not supported in this browser. Use Choose Image instead.', position: 'top' })
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

    $q.notify({ type: 'warning', message: 'No image found in clipboard.', position: 'top' })
  } catch (e) {
    console.error('Paste image failed', e)
    $q.notify({ type: 'warning', message: 'Clipboard image paste blocked. Please allow clipboard permission or use Choose Image.', position: 'top' })
  }
}

async function pasteHeaderImageUrlFromClipboard() {
  try {
    const text = await navigator.clipboard.readText()
    if (!text || !String(text).trim()) {
      $q.notify({ type: 'warning', message: 'Clipboard is empty.', position: 'top' })
      return
    }
    pageOptions.value.printHeader.imageUrl = String(text).trim()
    saveSettingsPresets()
    $q.notify({ type: 'positive', message: 'Header image URL pasted from clipboard', position: 'top' })
  } catch (e) {
    console.error('Paste header URL failed', e)
    $q.notify({ type: 'warning', message: 'Clipboard paste blocked. Please allow clipboard permission or paste manually.', position: 'top' })
  }
}

async function pasteHeaderImageFromClipboard() {
  try {
    if (!navigator.clipboard?.read) {
      $q.notify({ type: 'warning', message: 'Clipboard image paste is not supported in this browser. Use Choose Image instead.', position: 'top' })
      return
    }

    const items = await navigator.clipboard.read()
    for (const item of items) {
      const imgType = item.types.find(t => t.startsWith('image/'))
      if (!imgType) continue

      const blob = await item.getType(imgType)
      const reader = new FileReader()
      reader.onload = () => {
        pageOptions.value.printHeader.imageUrl = String(reader.result || '')
        saveSettingsPresets()
        $q.notify({ type: 'positive', message: 'Header image pasted from clipboard', position: 'top' })
      }
      reader.readAsDataURL(blob)
      return
    }

    $q.notify({ type: 'warning', message: 'No image found in clipboard.', position: 'top' })
  } catch (e) {
    console.error('Paste header image failed', e)
    $q.notify({ type: 'warning', message: 'Clipboard image paste blocked. Please allow clipboard permission or use Choose Image.', position: 'top' })
  }
}

async function pasteHeaderHtmlFromClipboard() {
  try {
    const text = await navigator.clipboard.readText()
    if (!text || !String(text).trim()) {
      $q.notify({ type: 'warning', message: 'Clipboard is empty.', position: 'top' })
      return
    }
    pageOptions.value.printHeader.html = String(text).trim()
    saveSettingsPresets()
    $q.notify({ type: 'positive', message: 'Header HTML pasted from clipboard', position: 'top' })
  } catch (e) {
    console.error('Paste header HTML failed', e)
    $q.notify({ type: 'warning', message: 'Clipboard paste blocked. Please allow clipboard permission or paste manually.', position: 'top' })
  }
}

async function savePageState() {
  try {
    const data = {
      questions: sampleQuestions.value,
      // Save entire pageOptions to ensure no settings are lost
      settings: JSON.parse(JSON.stringify(pageOptions.value)),
      // Also save page breaks separately for easy access
      pageBreaks: pageOptions.value.questionNumbering?.pageBreaksBefore || {},
      // Save sections and question-section mapping
      sections: sections.value,
      questionSectionMap: questionSectionMap.value
    }

    const response = await fetch('/exam/ready-to-print/api/save-data-v3', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': page.props.csrf_token || '',
        'Accept': 'application/json'
      },
      body: JSON.stringify(data)
    })

    const result = await response.json()
    if (result.success) {
      $q.notify({
        type: 'positive',
        message: 'Settings saved successfully',
        position: 'top',
        timeout: 2000
      })
    } else {
      throw new Error('Save failed')
    }
  } catch (e) {
    console.error('Failed to save page state', e)
    $q.notify({
      type: 'negative',
      message: 'Failed to save settings',
      position: 'top',
      timeout: 3000
    })
  }
}

async function handleSaveAs(fileName) {
  try {
    if (typeof fileName !== 'string' || fileName.trim() === '') {
      throw new Error('Missing file name')
    }
    const data = {
      name: fileName,
      questions: sampleQuestions.value,
      component_version: COMPONENT_VERSION,
      settings: {
        examTitle: pageOptions.value.examTitle,
        showMarksPerQuestion: pageOptions.value.showMarksPerQuestion,
        showExplanationUnderQuestion: pageOptions.value.showExplanationUnderQuestion,
        showCorrectAnswerUnderQuestion: pageOptions.value.showCorrectAnswerUnderQuestion,
        printHeader: pageOptions.value.printHeader,
        printFooter: pageOptions.value.printFooter,
        firstPage: pageOptions.value.firstPage,
        lastPage: pageOptions.value.lastPage,
        answerKey: pageOptions.value.answerKey,
        questionNumbering: pageOptions.value.questionNumbering,
        sectionTotal: pageOptions.value.sectionTotal,
        questionSeparator: pageOptions.value.questionSeparator,
        mcqOptions: pageOptions.value.mcqOptions,
        pageLayout: pageOptions.value.pageLayout
      },
      sections: sections.value || [],
      questionSectionMap: questionSectionMap.value || {},
      pageBreaks: pageOptions.value.questionNumbering?.pageBreaksBefore || {}
    }

    console.log('Saving exam as:', fileName, 'with data:', {
      questions_count: data.questions.length,
      sections_count: data.sections.length,
      questionSectionMap_count: Object.keys(data.questionSectionMap).length,
    })

    const response = await fetch('/api/exam/ready-to-print/save-exam', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': page.props.csrf_token || '',
        'Accept': 'application/json'
      },
      body: JSON.stringify(data)
    })

    const result = await response.json()
    console.log('Save As response:', result)

    if (response.ok) {
      lastSavedExamId.value = result.exam_id
      $q.notify({
        type: 'positive',
        message: `Exam saved as "${fileName}" successfully!`,
        position: 'top'
      })
    } else {
      $q.notify({
        type: 'negative',
        message: 'Failed to save exam: ' + (result.message || 'Unknown error'),
        position: 'top'
      })
    }
  } catch (e) {
    console.error('Failed to save exam', e)
    $q.notify({
      type: 'negative',
      message: 'Failed to save exam: ' + e.message,
      position: 'top'
    })
  }
}

async function handleSaveExam() {
  try {
    const examTitle = pageOptions.value.examTitle?.enabled ? pageOptions.value.examTitle.text : 'Untitled Exam'
    const data = {
      name: examTitle,
      questions: sampleQuestions.value,
      component_version: COMPONENT_VERSION,
      // Save entire pageOptions to ensure no settings are lost
      settings: JSON.parse(JSON.stringify(pageOptions.value)),
      // Optional - AI can ask about these before creating JSON
      sections: sections.value || [],
      questionSectionMap: questionSectionMap.value || {},
      pageBreaks: pageOptions.value.questionNumbering?.pageBreaksBefore || {}
    }

    // Include exam_id if updating existing exam
    if (lastSavedExamId.value) {
      data.exam_id = lastSavedExamId.value
    }

    console.log('Saving exam with data:', {
      questions_count: data.questions.length,
      sections_count: data.sections.length,
      questionSectionMap_count: Object.keys(data.questionSectionMap).length,
      questions_sample: data.questions.slice(0, 2),
      exam_id: data.exam_id
    })

    const response = await fetch('/api/exam/ready-to-print/save-exam', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': page.props.csrf_token || '',
        'Accept': 'application/json'
      },
      body: JSON.stringify(data)
    })

    const result = await response.json()
    console.log('Save response:', result)

    if (response.ok) {
      lastSavedExamId.value = result.exam_id
      hasUnsavedChanges.value = false
      lastSavedState.value = getCurrentState()
      $q.notify({
        type: 'positive',
        message: data.exam_id ? 'Exam updated successfully!' : 'New exam created successfully!',
        position: 'top'
      })
    } else {
      $q.notify({
        type: 'negative',
        message: 'Failed to save exam: ' + (result.message || 'Unknown error'),
        position: 'top'
      })
    }
  } catch (e) {
    console.error('Failed to save exam', e)
    $q.notify({
      type: 'negative',
      message: 'Failed to save exam: ' + e.message,
      position: 'top'
    })
  }
}

function handleCreateNewExam() {
  $q.dialog({
    title: 'Create New Exam',
    message: 'Create a new blank exam? All current unsaved changes will be lost.',
    cancel: true,
    persistent: true
  }).onOk(() => {
    // Reset all exam data to default state
    sampleQuestions.value = []
    sections.value = []
    questionSectionMap.value = {}
    lastSavedExamId.value = null
    try { localStorage.removeItem(LAST_EXAM_ID_STORAGE_KEY) } catch (e) {}
    hasUnsavedChanges.value = false
    lastSavedState.value = null

    // Reset page options to defaults
    pageOptions.value = {
      examTitle: { enabled: true, text: 'New Exam' },
      showMarksPerQuestion: true,
      showExplanationUnderQuestion: false,
      showCorrectAnswerUnderQuestion: false,
      paginationMode: 'strict',
      printHeader: {
        enabled: true,
        template1: { subject: '', grade: '', date: '', duration: '' },
        pageMarginTopMm: 20
      },
      printFooter: { enabled: false, pageNumberStartAtQuestion: 1, pageNumberStartValue: 1 },
      firstPage: { enabled: false },
      lastPage: { enabled: false },
      questionNumbering: { startFrom: 1, pageBreaksBefore: {} },
      sectionTotal: { enabled: false },
      questionSeparator: { enabled: true },
      mcqOptions: { layout: 'vertical' },
      pageLayout: { margin: 'normal' }
    }

    $q.notify({
      type: 'info',
      message: 'New blank exam created',
      position: 'top'
    })
  })
}

function getCurrentState() {
  return JSON.stringify({
    questions: sampleQuestions.value,
    sections: sections.value,
    questionSectionMap: questionSectionMap.value,
    pageOptions: pageOptions.value
  })
}

function markAsChanged() {
  if (!hasUnsavedChanges.value) {
    hasUnsavedChanges.value = true
  }
  triggerAutoSave()
}

function triggerAutoSave() {
  if (!autoSaveEnabled.value || !lastSavedExamId.value) return

  // Clear existing timer
  if (autoSaveDebounceTimer.value) {
    clearTimeout(autoSaveDebounceTimer.value)
  }

  // Set new timer for auto-save (debounce 2 seconds)
  autoSaveDebounceTimer.value = setTimeout(() => {
    handleSaveExam()
  }, 2000)
}

function toggleAutoSave() {
  autoSaveEnabled.value = !autoSaveEnabled.value
  if (autoSaveEnabled.value && hasUnsavedChanges.value && lastSavedExamId.value) {
    // Trigger immediate save when enabling auto-save
    handleSaveExam()
  }
}

// Watch for changes in exam data
watch([sampleQuestions, sections, questionSectionMap, pageOptions], () => {
  const currentState = getCurrentState()
  if (lastSavedState.value && currentState !== lastSavedState.value) {
    markAsChanged()
  }
}, { deep: true })

async function openPrintPreview() {
  await printNewDirect()
}

function ensureSavedExamIdOrNotify() {
  if (lastSavedExamId.value) {
    return String(lastSavedExamId.value)
  }

  $q.notify({
    type: 'warning',
    message: 'Please save the exam first to use server print/PDF.',
    position: 'top'
  })
  return ''
}

async function openServerPrintHtml() {
  if (openingPrintHtml.value) return
  const examId = ensureSavedExamIdOrNotify()
  if (!examId) return false

  const w = window.open('', '_blank')
  if (!w) {
    $q.notify({
      type: 'negative',
      message: 'Popup blocked. Please allow popups to open print preview.',
      position: 'top'
    })
    return false
  }
  w.document.write('<!doctype html><html><head><meta charset="utf-8" /><title>Loading...</title></head><body style="font-family: Arial, sans-serif; padding: 16px;">Loading print preview...</body></html>')
  w.document.close()

  openingPrintHtml.value = true
  try {
    const response = await fetch(`/api/exam/ready-to-print/print-html/${encodeURIComponent(examId)}`, {
      method: 'GET',
      headers: {
        Accept: 'text/html, application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    })

    const contentType = (response.headers.get('content-type') || '').toLowerCase()
    if (!response.ok) {
      if (contentType.includes('application/json')) {
        const data = await response.json().catch(() => ({}))
        throw new Error(data?.message || 'Failed to generate print HTML')
      }
      const text = await response.text()
      throw new Error(`Failed to generate print HTML (${response.status}): ` + (text.substring(0, 200) || 'Unknown server error'))
    }

    const html = await response.text()
    w.document.write(html)
    w.document.close()
    return true
  } catch (e) {
    try { w.close() } catch {}
    $q.notify({
      type: 'negative',
      message: 'Server print failed: ' + (e?.message || e),
      position: 'top'
    })
    return false
  } finally {
    openingPrintHtml.value = false
  }
}

async function downloadServerPdf() {
  if (pdfGenerating.value) return
  const examId = ensureSavedExamIdOrNotify()
  if (!examId) return

  const fallbackWindow = window.open('', '_blank')
  if (fallbackWindow) {
    fallbackWindow.document.write('<!doctype html><html><head><meta charset="utf-8" /><title>Loading...</title></head><body style="font-family: Arial, sans-serif; padding: 16px;">Preparing PDF...</body></html>')
    fallbackWindow.document.close()
  }

  pdfGenerating.value = true
  try {
    const response = await fetch(`/api/exam/ready-to-print/generate-pdf/${encodeURIComponent(examId)}`, {
      method: 'GET',
      headers: {
        Accept: 'application/pdf, text/html, application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    })

    const contentType = (response.headers.get('content-type') || '').toLowerCase()
    if (!response.ok) {
      if (contentType.includes('application/json')) {
        const data = await response.json().catch(() => ({}))
        throw new Error(data?.message || 'Failed to generate PDF')
      }
      const text = await response.text()
      throw new Error(text.substring(0, 200) || 'Failed to generate PDF')
    }

    if (contentType.includes('text/html')) {
      const html = await response.text()
      if (!fallbackWindow) {
        throw new Error('Popup blocked. Please allow popups to use HTML print fallback.')
      }
      fallbackWindow.document.write(html)
      fallbackWindow.document.close()
      fallbackWindow.onload = () => fallbackWindow.print()
      return
    }

    if (fallbackWindow) {
      try { fallbackWindow.close() } catch {}
    }

    const blob = await response.blob()
    const url = window.URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = 'Exam.pdf'
    document.body.appendChild(a)
    a.click()
    document.body.removeChild(a)
    window.URL.revokeObjectURL(url)
  } catch (e) {
    if (fallbackWindow) {
      try { fallbackWindow.close() } catch {}
    }
    $q.notify({
      type: 'negative',
      message: 'PDF generation failed: ' + (e?.message || e),
      position: 'top'
    })
  } finally {
    pdfGenerating.value = false
  }
}

async function printNewDirect() {
  try {
    const ok = await openServerPrintHtml()
    if (ok) return
  } catch (e) {
    console.error('New print failed', e)
    $q.notify({
      type: 'warning',
      message: 'New print failed, using old print fallback.',
      position: 'top'
    })
  }

  await printOldDirect()
}

async function printOldDirect() {
  try {
    const livePdfIframe = document.querySelector('.pdf-preview-iframe')
    if (pdfPreviewMode.value && livePdfIframe && livePdfIframe.contentWindow) {
      livePdfIframe.contentWindow.focus()
      livePdfIframe.contentWindow.print()
      return
    }

    const html = generatePrintHTML()
    const iframe = document.createElement('iframe')
    iframe.style.position = 'fixed'
    iframe.style.right = '0'
    iframe.style.bottom = '0'
    iframe.style.width = '0'
    iframe.style.height = '0'
    iframe.style.border = '0'
    iframe.style.opacity = '0'
    iframe.style.pointerEvents = 'none'
    iframe.srcdoc = html
    document.body.appendChild(iframe)

    iframe.onload = () => {
      const win = iframe.contentWindow
      const start = Date.now()
      const maxWaitMs = 4000

      const tryPrint = () => {
        try {
          if (!win) return
          const ready = !!win.__printReady
          const timedOut = (Date.now() - start) > maxWaitMs
          if (!ready && !timedOut) {
            setTimeout(tryPrint, 60)
            return
          }
          win.focus()
          win.print()
        } finally {
          setTimeout(() => {
            try { iframe.remove() } catch (e) {}
          }, 1000)
        }
      }

      // Give the iframe a moment to run its internal load+measure script, then poll.
      setTimeout(tryPrint, 60)
    }
  } catch (e) {
    console.error('Old print failed', e)
    $q.notify({
      type: 'negative',
      message: 'Print failed: ' + e.message,
      position: 'top'
    })
  }
}

function printPreview() {
  const iframe = document.querySelector('.full-screen-print-preview iframe')
  if (iframe && iframe.contentWindow) {
    iframe.contentWindow.print()
  }
}

function onPreviewLoaded() {
  console.log('Print preview loaded')
}

async function forceRegenerateHtml() {
  if (!lastSavedExamId.value) {
    $q.notify({
      type: 'warning',
      message: 'Please save the exam first to regenerate HTML',
      position: 'top'
    })
    return
  }

  try {
    $q.notify({
      type: 'info',
      message: 'Regenerating cached HTML...',
      position: 'top',
      timeout: 1000
    })

    const url = `/api/exam/ready-to-print/print-html/${lastSavedExamId.value}`
    const response = await fetch(url, {
      method: 'GET',
      headers: {
        'Accept': 'text/html'
      }
    })

    if (response.ok) {
      $q.notify({
        type: 'positive',
        message: 'HTML regenerated successfully!',
        position: 'top'
      })
      console.log('HTML regenerated successfully')
    } else {
      $q.notify({
        type: 'negative',
        message: 'Failed to regenerate HTML',
        position: 'top'
      })
    }
  } catch (e) {
    console.error('Failed to regenerate HTML', e)
    $q.notify({
      type: 'negative',
      message: 'Failed to regenerate HTML: ' + e.message,
      position: 'top'
    })
  }
}

function testPageNumbers() {
  console.log('Generating test HTML with 4 empty pages')
  
  const testHtml = `<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Page Number Test</title>
    <style>
        @page {
            size: A4;
            margin: 12mm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 12pt;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }
        .print-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background: white;
            padding: 10mm;
            border-bottom: 1px solid #ccc;
        }
        .print-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background: white;
            padding: 10mm;
            border-top: 1px solid #ccc;
            text-align: center;
        }
        .page-break {
            page-break-before: always;
            height: 0;
        }
        .page-content {
            min-height: 250mm;
            padding: 20mm;
        }
        .page-label {
            font-size: 24pt;
            font-weight: bold;
            color: #1f3a5a;
            text-align: center;
            margin-top: 50mm;
        }
        @media print {
            .print-header {
                position: fixed;
                top: 0;
            }
            .print-footer {
                position: fixed;
                bottom: 0;
            }
            .page-counter::after {
                content: " " counter(page);
            }
        }
    </style>
</head>
<body>
    <div class="print-header">
        <h2>Page Number Test Document</h2>
    </div>

    <div class="page-content">
        <div class="page-label">Page 1 Content</div>
        <p>This is the first page. It should show "Page 1" in the footer when printed.</p>
    </div>

    <div class="page-break"></div>

    <div class="page-content">
        <div class="page-label">Page 2 Content</div>
        <p>This is the second page. It should show "Page 2" in the footer when printed.</p>
    </div>

    <div class="page-break"></div>

    <div class="page-content">
        <div class="page-label">Page 3 Content</div>
        <p>This is the third page. It should show "Page 3" in the footer when printed.</p>
    </div>

    <div class="page-break"></div>

    <div class="page-content">
        <div class="page-label">Page 4 Content</div>
        <p>This is the fourth page. It should show "Page 4" in the footer when printed.</p>
    </div>

    <div class="print-footer">
        <div class="page-number">Page <span class="page-counter"></span></div>
    </div>
</body>
</html>`

  printPreviewHtml.value = testHtml
  printPreviewOpen.value = true
  
  console.log('Test HTML generated, length:', testHtml.length)
  console.log('Test HTML preview (first 300 chars):', testHtml.substring(0, 300))
  
  $q.notify({
    type: 'info',
    message: 'Test HTML with 4 pages loaded. Print to see page numbers.',
    position: 'top'
  })
}

async function handleLoadExam(data) {
  if (data?.id) {
    lastSavedExamId.value = data.id
    updateUrlWithExamId(data.id)
  }
  if (data.questions) sampleQuestions.value = data.questions
  if (data.settings) {
    pageOptions.value = { ...pageOptions.value, ...data.settings }
  }
  if (data.sections && Array.isArray(data.sections)) sections.value = data.sections
  if (data.questionSectionMap) questionSectionMap.value = data.questionSectionMap
  if (data.pageBreaks) {
    if (!pageOptions.value.questionNumbering) pageOptions.value.questionNumbering = {}
    pageOptions.value.questionNumbering.pageBreaksBefore = data.pageBreaks
  }

  // Ensure every question has a section
  const defaultSectionId = sections.value[0]?.id
  sampleQuestions.value.forEach(q => {
    const qid = String(q?.id)
    if (defaultSectionId && !questionSectionMap.value[qid]) questionSectionMap.value[qid] = defaultSectionId
  })

  hasUnsavedChanges.value = false
  lastSavedState.value = getCurrentState()

  await savePageState()
}

function updateUrlWithExamId(examId) {
  const url = new URL(window.location.href)
  url.searchParams.set('exam_id', examId)
  window.history.replaceState({}, '', url)
}

async function loadExamFromUrl() {
  const urlParams = new URLSearchParams(window.location.search)
  const examId = urlParams.get('exam_id')

  if (examId) {
    try {
      const response = await fetch(`/api/exam/ready-to-print/load-saved-exam/${examId}`, {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'X-CSRF-TOKEN': page.props.csrf_token || ''
        }
      })

      if (response.ok) {
        const result = await response.json()
        if (result.success && result.data) {
          await handleLoadExam(result.data)
          $q.notify({
            type: 'positive',
            message: 'Exam loaded successfully',
            position: 'top'
          })
        } else {
          $q.notify({
            type: 'negative',
            message: result.message || 'Failed to load exam',
            position: 'top'
          })
        }
      } else {
        $q.notify({
          type: 'negative',
          message: 'Failed to load exam',
          position: 'top'
        })
      }
    } catch (error) {
      console.error('Error loading exam:', error)
      $q.notify({
        type: 'negative',
        message: 'Error loading exam',
        position: 'top'
      })
    }
  }
}

function handleDeleteExam(fileId) {
  // File already deleted by component, just handle any additional cleanup if needed
  console.log('File deleted:', fileId)
}

function handleRefreshFiles(files) {
  // Handle refresh if needed
  console.log('Files refreshed:', files)
}

function openFileManagerDialog() {
  if (fileManagerRef.value) {
    fileManagerRef.value.openDialog()
  }
}

async function addSection() {
  const id = 'sec_' + Date.now()
  sections.value.push({ 
    id, 
    title: 'New Section', 
    instructions: '',
    forceQuestionsToEssay: false,
    lineBefore: false,
    lineAfter: false,
    pageBreakBefore: false,
    lineStyle: 'solid',
    lineThickness: 1,
    lineColor: '#000000'
  })
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

function isSectionForcingEssay(sectionId) {
  const s = sections.value.find(x => x.id === sectionId)
  return !!s?.forceQuestionsToEssay
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

async function copyQuestionsToClipboard() {
  try {
    const normalizedQuestions = (sampleQuestions.value || []).map((q) => {
      const qq = JSON.parse(JSON.stringify(q))
      if (qq?.type === 'multiple_choice') {
        const opts = qq?.content?.options
        const correctText = qq?.content?.correct_option_index
        if ((qq?.correct_option_index === null || qq?.correct_option_index === undefined) && Array.isArray(opts) && typeof correctText === 'string') {
          const idx = opts.findIndex(o => String(o).trim() === String(correctText).trim())
          if (idx >= 0) qq.correct_option_index = idx
        }
      }
      return qq
    })

    const payload = {
      version: 1,
      exportedAt: new Date().toISOString(),
      questions: normalizedQuestions,
      sections: sections.value,
      questionSectionMap: questionSectionMap.value
    }
    const textToCopy = JSON.stringify(payload, null, 2)
    
    if (navigator.clipboard && navigator.clipboard.writeText) {
      await navigator.clipboard.writeText(textToCopy)
      $q.notify({ type: 'positive', message: 'Questions copied to clipboard!', position: 'top' })
    } else {
      const textArea = document.createElement("textarea")
      textArea.value = textToCopy
      textArea.style.top = "0"
      textArea.style.left = "0"
      textArea.style.position = "fixed"
      document.body.appendChild(textArea)
      textArea.focus()
      textArea.select()
      const successful = document.execCommand('copy')
      document.body.removeChild(textArea)
      if (successful) {
        $q.notify({ type: 'positive', message: 'Questions copied to clipboard!', position: 'top' })
      } else {
        throw new Error('Clipboard write failed')
      }
    }
  } catch (e) {
    console.error('Copy to clipboard failed', e)
    $q.notify({ type: 'negative', message: 'Failed to copy to clipboard: ' + e.message, position: 'top' })
  }
}

async function updateQuestionsFromClipboard() {
  try {
    if (!navigator.clipboard || !navigator.clipboard.readText) {
      $q.notify({ type: 'negative', message: 'Clipboard access is not supported in this browser.', position: 'top' })
      return
    }

    const text = await navigator.clipboard.readText()
    if (!text) {
      $q.notify({ type: 'warning', message: 'Clipboard is empty', position: 'top' })
      return
    }
    
    let parsed
    try {
      parsed = JSON.parse(text)
    } catch (e) {
      $q.notify({ type: 'negative', message: 'Clipboard content is not valid JSON', position: 'top' })
      return
    }
    
    if (!parsed || (!parsed.questions && !Array.isArray(parsed))) {
      $q.notify({ type: 'negative', message: 'No questions found in clipboard JSON', position: 'top' })
      return
    }
    
    const questionsArray = Array.isArray(parsed) ? parsed : parsed.questions
    
    $q.dialog({
      title: 'Update Questions',
      message: `Found ${questionsArray.length} questions in clipboard. What would you like to do?`,
      options: {
        type: 'radio',
        model: 'replace',
        items: [
          { label: 'Replace all current questions', value: 'replace' },
          { label: 'Append to current questions', value: 'append' }
        ]
      },
      cancel: true,
      persistent: true
    }).onOk(async (action) => {
      if (action === 'replace') {
        sampleQuestions.value = questionsArray
        if (parsed.sections && Array.isArray(parsed.sections)) {
          sections.value = parsed.sections
        }
        
        // Handle questionSectionMap with validation
        if (parsed.questionSectionMap && typeof parsed.questionSectionMap === 'object') {
          // Normalize question IDs in the map to match the actual question IDs
          const normalizedMap = {}
          const validSectionIds = new Set(sections.value.map(s => s.id))
          
          Object.keys(parsed.questionSectionMap).forEach(key => {
            const sectionId = parsed.questionSectionMap[key]
            // Only keep mappings to valid sections
            if (validSectionIds.has(sectionId)) {
              normalizedMap[key] = sectionId
            }
          })
          questionSectionMap.value = normalizedMap
        }
        
        // Ensure every question has a valid section mapping
        const defaultSectionId = sections.value[0]?.id
        if (defaultSectionId) {
          questionsArray.forEach(q => {
            const qid = String(q?.id)
            // Try multiple ID formats for the map lookup
            const mapKeys = [qid, 'q' + qid]
            let mappedSection = null
            for (const key of mapKeys) {
              if (questionSectionMap.value[key]) {
                mappedSection = questionSectionMap.value[key]
                break
              }
            }
            // Use the question's section property if available and valid
            if (!mappedSection && q.section && validSectionIds.has(q.section)) {
              mappedSection = q.section
            }
            // Fall back to default section
            if (!mappedSection) {
              mappedSection = defaultSectionId
            }
            questionSectionMap.value[qid] = mappedSection
          })
        }

        $q.notify({ type: 'positive', message: 'Questions replaced successfully', position: 'top' })
      } else {
        let maxId = 0
        sampleQuestions.value.forEach(q => {
          if (Number(q.id) > maxId) maxId = Number(q.id)
        })
        
        const defaultSectionId = sections.value[0]?.id
        const newQuestions = questionsArray.map(q => {
          const newQ = { ...q, id: String(++maxId) }
          if (defaultSectionId) {
            questionSectionMap.value[newQ.id] = defaultSectionId
          }
          return newQ
        })
        
        sampleQuestions.value = [...sampleQuestions.value, ...newQuestions]
        $q.notify({ type: 'positive', message: 'Questions appended successfully', position: 'top' })
      }
      
      await savePageState()
    })
  } catch (e) {
    console.error('Update from clipboard failed', e)
    $q.notify({ type: 'negative', message: 'Failed to read from clipboard. You may need to grant clipboard permissions.', position: 'top' })
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
  prompt += `\n- Each question should have: id (number), type (string), marks (number), content with prompt and options (if applicable)`
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
  prompt += `\n    "ver": 3,`
  prompt += `\n    "marks": 2,`
  prompt += `\n    "content": {`
  prompt += `\n      "prompt": "What is the sum of $2 \\frac{1}{5}$ and $1 \\frac{2}{5}$?"`
  prompt += `\n    }`
  prompt += `\n  },`
  prompt += `\n  {`
  prompt += `\n    "id": 2,`
  prompt += `\n    "type": "multiple_choice",`
  prompt += `\n    "ver": 3,`
  prompt += `\n    "marks": 3,`
  prompt += `\n    "content": {`
  prompt += `\n      "prompt": "Solve for x: $x^2 + 2x - 8 = 0$",`
  prompt += `\n      "options": ["x = 2", "x = -4", "x = 2 or x = -4", "x = 4"],`
  prompt += `\n      "correct_option_index": 2`
  prompt += `\n    }`
  prompt += `\n  }`
  prompt += `\n]`
  prompt += `\n\`\`\``
}

function formatQuestionType(type) {
  const typeMap = {
    'short_answer': 'Short Answer',
    'multiple_choice': 'Multiple Choice',
    'true_false': 'True/False',
    'mixed': 'Mixed Types'
  }
  return typeMap[type] || type
}

function showGenerationPreview() {
  showPreviewDialog.value = true
}

function confirmAndGeneratePrompt() {
  showPreviewDialog.value = false
  generatePrompt()
}

// Full Exam AI Generation Functions
function openFullExamDialog() {
  fullExamDialogOpen.value = true
  fullExamStep.value = 1
  fullExamPrompt.value = ''
  fullExamResponse.value = ''
  examConfig.value = {
    examTitle: '',
    examSubject: '',
    examGrade: '',
    examDuration: '',
    totalMarks: '',
    sections: []
  }
  currentSectionIndex.value = 0
  missingInfoPrompts.value = []
}

function addExamSection() {
  examConfig.value.sections.push({
    title: '',
    description: '',
    questionTypes: [],
    questionCount: 0,
    marksPerQuestion: 1,
    totalMarks: 0
  })
}

function removeExamSection(index) {
  examConfig.value.sections.splice(index, 1)
}

function generateFullExamPrompt() {
  const { examTitle, examSubject, examGrade, examDuration, totalMarks, sections } = examConfig.value

  // Detect missing information
  const missingInfo = []
  if (!examTitle) missingInfo.push('exam title')
  if (!examSubject) missingInfo.push('exam subject')
  if (!examGrade) missingInfo.push('grade level')
  if (!examDuration) missingInfo.push('exam duration')
  if (!totalMarks) missingInfo.push('total marks')
  
  sections.forEach((section, index) => {
    if (!section.title) missingInfo.push(`section ${index + 1} title`)
    if (!section.questionTypes || section.questionTypes.length === 0) missingInfo.push(`section ${index + 1} question types`)
    if (!section.questionCount) missingInfo.push(`section ${index + 1} question count`)
  })

  if (missingInfo.length > 0) {
    missingInfoPrompts.value = missingInfo
    $q.notify({
      type: 'warning',
      message: `Please provide the following missing information:\n${missingInfo.join('\n')}`,
      position: 'top',
      timeout: 5000
    })
    return
  }

  let prompt = `Generate a complete exam with the following specifications:`
  prompt += `\n\nExam Details:`
  prompt += `\n- Title: "${examTitle}"`
  prompt += `\n- Subject: "${examSubject}"`
  prompt += `\n- Grade Level: "${examGrade}"`
  prompt += `\n- Duration: "${examDuration}"`
  prompt += `\n- Total Marks: ${totalMarks}`

  prompt += `\n\nSections:`
  sections.forEach((section, index) => {
    prompt += `\n\nSection ${index + 1}:`
    prompt += `\n- Title: "${section.title}"`
    prompt += `\n- Description: "${section.description || 'N/A'}"`
    prompt += `\n- Question Types: ${section.questionTypes.join(', ')}`
    prompt += `\n- Number of Questions: ${section.questionCount}`
    prompt += `\n- Marks per Question: ${section.marksPerQuestion}`
  })

  prompt += `\n\nRequirements:`
  prompt += `\n- Return ONLY a JSON object with the complete exam structure`
  prompt += `\n- Include all sections with their questions`
  prompt += `\n- Each question must have: id, type, ver, marks, content with prompt and options (if applicable)`
  prompt += `\n- Question types: "short_answer", "multiple_choice", "true_false", "essay", "fill_in_blank"`
  prompt += `\n- For multiple_choice questions, include options array and correct_option_index (0-based integer)`
  prompt += `\n- Use LaTeX notation for math: $x^2$ for inline, $$\\frac{a}{b}$$ for display`
  prompt += `\n- Do NOT include citations or source markers like [cite: 219] anywhere in the text`

  prompt += `\n\nJSON format:`
  prompt += `\n\`\`\`json`
  prompt += `\n{`
  prompt += `\n  "examTitle": "${examTitle}",`
  prompt += `\n  "examSubject": "${examSubject}",`
  prompt += `\n  "examGrade": "${examGrade}",`
  prompt += `\n  "examDuration": "${examDuration}",`
  prompt += `\n  "totalMarks": ${totalMarks},`
  prompt += `\n  "sections": [`
  prompt += `\n    {`
  prompt += `\n      "id": 1,`
  prompt += `\n      "title": "${sections[0]?.title || 'Section 1'}",`
  prompt += `\n      "description": "${sections[0]?.description || ''}",`
  prompt += `\n      "totalMarks": ${sections[0]?.totalMarks || 0},`
  prompt += `\n      "questions": [`
  prompt += `\n        {`
  prompt += `\n          "id": 1,`
  prompt += `\n          "type": "short_answer",`
  prompt += `\n          "ver": 3,`
  prompt += `\n          "marks": 2,`
  prompt += `\n          "content": {`
  prompt += `\n            "prompt": "Question text here..."`
  prompt += `\n          }`
  prompt += `\n        },`
  prompt += `\n        {`
  prompt += `\n          "id": 2,`
  prompt += `\n          "type": "multiple_choice",`
  prompt += `\n          "ver": 3,`
  prompt += `\n          "marks": 3,`
  prompt += `\n          "content": {`
  prompt += `\n            "prompt": "Question text here...",`
  prompt += `\n            "options": ["Option A", "Option B", "Option C", "Option D"],`
  prompt += `\n            "correct_option_index": 0`
  prompt += `\n          }`
  prompt += `\n        }`
  prompt += `\n      ]`
  prompt += `\n    }`
  prompt += `\n  ]`
  prompt += `\n}`
  prompt += `\n\`\`\``

  fullExamPrompt.value = prompt
  fullExamStep.value = 2
}

function copyFullExamPrompt() {
  navigator.clipboard.writeText(fullExamPrompt.value).then(() => {
    console.log('Full exam prompt copied to clipboard')
  }).catch(err => {
    console.error('Failed to copy prompt', err)
  })
}

function processFullExamResponse() {
  try {
    let cleanedResponse = fullExamResponse.value
      .replace(/```json\n?|\n?```/g, '')
      .trim()

    const examData = JSON.parse(cleanedResponse)
    
    // Update exam title
    if (examData.examTitle) {
      pageOptions.value.examTitle.text = examData.examTitle
      pageOptions.value.examTitle.enabled = true
    }

    // Process sections and questions
    if (examData.sections && Array.isArray(examData.sections)) {
      const newSections = []
      const newQuestions = []
      const newQuestionSectionMap = {}
      let questionIdCounter = 1

      examData.sections.forEach((section, sectionIndex) => {
        const sectionId = `section_${Date.now()}_${sectionIndex}`
        newSections.push({
          id: sectionId,
          title: section.title || `Section ${sectionIndex + 1}`,
          description: section.description || '',
          totalMarks: section.totalMarks || 0
        })

        if (section.questions && Array.isArray(section.questions)) {
          section.questions.forEach((question) => {
            const questionId = questionIdCounter++
            newQuestions.push({
              id: questionId,
              type: question.type || 'short_answer',
              ver: 3,
              marks: question.marks || 1,
              content: question.content || { prompt: '' }
            })
            newQuestionSectionMap[questionId] = sectionId
          })
        }
      })

      sections.value = newSections
      sampleQuestions.value = newQuestions
      questionSectionMap.value = newQuestionSectionMap
    }

    // Save the generated data
    savePageState()
    
    // Close dialog
    fullExamDialogOpen.value = false
    
    // Show success message
    $q.notify({ type: 'positive', message: 'Exam generated successfully!', position: 'top' })
  } catch (error) {
    console.error('Failed to process exam response:', error)
    $q.notify({ type: 'negative', message: 'Failed to process exam response. Please check the JSON format.', position: 'top' })
  }
}

// AI Chat Integration Functions
function openAIChatDialog() {
  aiChatDialogOpen.value = true
  aiChatMessages.value = [
    {
      role: 'assistant',
      content: 'Hello! I can help you generate a complete exam. Please provide me with the exam details (title, subject, grade level, duration, total marks) and sections you want to include. I\'ll ask you for any missing information if needed.'
    }
  ]
  aiChatInput.value = ''
  conversationMode.value = 'exam_generation'
  generatedExamData.value = null
}

async function sendAIMessage() {
  const message = aiChatInput.value.trim()
  if (!message || aiLoading.value) return

  // Add user message to chat
  aiChatMessages.value.push({
    role: 'user',
    content: message
  })
  aiChatInput.value = ''
  aiLoading.value = true

  try {
    // Check if API key is provided
    if (!aiApiKey.value) {
      aiChatMessages.value.push({
        role: 'assistant',
        content: 'Please provide your OpenAI API key in the settings to use AI generation.'
      })
      aiLoading.value = false
      return
    }

    // Prepare conversation history for API
    const messages = [
      {
        role: 'system',
        content: `You are an expert exam generator. Your task is to help users create complete exams with sections and questions.
        
When the user provides exam information:
1. Analyze what information is provided
2. If critical information is missing (exam title, subject, grade level, duration, total marks, or section details), ask the user for it specifically
3. If all information is complete, generate a complete exam in JSON format
4. The JSON should include: examTitle, examSubject, examGrade, examDuration, totalMarks, and sections array with questions
5. Each question should have: id, type, marks, content with prompt and options (if applicable)
6. Question types: "short_answer", "multiple_choice", "true_false", "essay", "fill_in_blank"
7. Use LaTeX notation for math: $x^2$ for inline, $$\\frac{a}{b}$$ for display
8. Do NOT include citations or source markers like [cite: 219] anywhere in the text

If you need to ask questions, be specific and ask one question at a time.
When you have all the information, return the complete JSON with a message saying "Here is your complete exam:" followed by the JSON.`
      },
      ...aiChatMessages.value
    ]

    // Call OpenAI API
    const response = await fetch('https://api.openai.com/v1/chat/completions', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${aiApiKey.value}`
      },
      body: JSON.stringify({
        model: aiModel.value,
        messages: messages,
        temperature: 0.7,
        max_tokens: 4000
      })
    })

    const data = await response.json()

    if (data.error) {
      throw new Error(data.error.message)
    }

    const aiMessage = data.choices[0].message.content
    aiChatMessages.value.push({
      role: 'assistant',
      content: aiMessage
    })

    // Check if AI provided a complete exam JSON
    if (aiMessage.includes('Here is your complete exam:') || aiMessage.includes('```json')) {
      try {
        // Extract JSON from message
        const jsonMatch = aiMessage.match(/```json\n?([\s\S]*?)\n?```/) || aiMessage.match(/\{[\s\S]*\}/)
        if (jsonMatch) {
          const jsonStr = jsonMatch[1] || jsonMatch[0]
          const examData = JSON.parse(jsonStr)
          generatedExamData.value = examData
          conversationMode.value = 'review'
        }
      } catch (e) {
        console.error('Failed to parse exam JSON:', e)
      }
    }

  } catch (error) {
    console.error('AI API error:', error)
    aiChatMessages.value.push({
      role: 'assistant',
      content: `Error: ${error.message}. Please check your API key and try again.`
    })
  } finally {
    aiLoading.value = false
  }
}

function acceptGeneratedExam() {
  if (!generatedExamData.value) {
    $q.notify({ type: 'warning', message: 'No exam data to accept.', position: 'top' })
    return
  }

  try {
    const examData = generatedExamData.value

    // Update exam title
    if (examData.examTitle) {
      pageOptions.value.examTitle.text = examData.examTitle
      pageOptions.value.examTitle.enabled = true
    }

    // Process sections and questions
    if (examData.sections && Array.isArray(examData.sections)) {
      const newSections = []
      const newQuestions = []
      const newQuestionSectionMap = {}
      let questionIdCounter = 1

      examData.sections.forEach((section, sectionIndex) => {
        const sectionId = `section_${Date.now()}_${sectionIndex}`
        newSections.push({
          id: sectionId,
          title: section.title || `Section ${sectionIndex + 1}`,
          description: section.description || '',
          totalMarks: section.totalMarks || 0
        })

        if (section.questions && Array.isArray(section.questions)) {
          section.questions.forEach((question) => {
            const questionId = questionIdCounter++
            newQuestions.push({
              id: questionId,
              type: question.type || 'short_answer',
              ver: 3,
              marks: question.marks || 1,
              content: question.content || { prompt: '' }
            })
            newQuestionSectionMap[questionId] = sectionId
          })
        }
      })

      sections.value = newSections
      sampleQuestions.value = newQuestions
      questionSectionMap.value = newQuestionSectionMap
    }

    // Save the generated data
    savePageState()
    
    // Close dialog
    aiChatDialogOpen.value = false
    
    // Show success message
    $q.notify({ type: 'positive', message: 'Exam generated successfully!', position: 'top' })
  } catch (error) {
    console.error('Failed to process exam data:', error)
    $q.notify({ type: 'negative', message: 'Failed to process exam data. Please try again.', position: 'top' })
  }
}

function regenerateExam() {
  conversationMode.value = 'exam_generation'
  generatedExamData.value = null
  aiChatMessages.value.push({
    role: 'user',
    content: 'Please regenerate the exam with different questions.'
  })
  sendAIMessage()
}

// Question Validation and Revision Functions
function openValidationDialog() {
  // Detect errors in questions using composable
  questionErrors.value = detectQuestionErrors(sampleQuestions.value)

  // Generate AI prompt for validation
  const questionsJson = JSON.stringify(sampleQuestions.value, null, 2)
  validationPrompt.value = `Please review these questions for errors and provide a corrected version in JSON format:

Errors to check for:
- Missing correct_option_index for multiple_choice questions
- Invalid correct_option_index (out of range)
- Duplicate questions
- Empty prompts
- Invalid question types
- Missing required fields

Questions:
${questionsJson}

Return ONLY the corrected JSON array.`

  validationDialogOpen.value = true
}

function copyValidationPrompt() {
  navigator.clipboard.writeText(questionsForValidation.value)
  $q.notify({ type: 'positive', message: 'Validation prompt copied to clipboard!', position: 'top' })
}

function copyQuestionsOnly() {
  const questionsJson = JSON.stringify(sampleQuestions.value, null, 2)
  navigator.clipboard.writeText(questionsJson)
  $q.notify({ type: 'positive', message: 'Questions copied to clipboard!', position: 'top' })
}

function parseAiFeedback() {
  try {
    const feedback = aiErrorFeedback.value.trim()
    if (!feedback) {
      parsedAiFeedback.value = []
      return
    }

    // Try to parse as JSON array
    const parsed = JSON.parse(feedback)
    if (Array.isArray(parsed)) {
      parsedAiFeedback.value = parsed
      return
    }

    // Try to parse as JSON object
    if (typeof parsed === 'object' && parsed.errors && Array.isArray(parsed.errors)) {
      parsedAiFeedback.value = parsed.errors
      return
    }

    // If not JSON, try to parse markdown table or plain text
    const lines = feedback.split('\n').filter(line => line.trim())
    const tableData = []
    
    lines.forEach((line, index) => {
      // Skip header lines (lines with | separators)
      if (line.includes('|') && line.split('|').length > 2) {
        const cells = line.split('|').map(cell => cell.trim()).filter(cell => cell)
        if (cells.length >= 3 && !line.includes('---')) {
          tableData.push({
            questionId: cells[0] || index,
            type: cells[1] || 'Unknown',
            severity: cells[2] || 'info',
            message: cells[3] || line
          })
        }
      }
    })

    if (tableData.length > 0) {
      parsedAiFeedback.value = tableData
    } else {
      // If no table found, treat as plain text feedback
      parsedAiFeedback.value = [{
        questionId: 'N/A',
        type: 'General Feedback',
        severity: 'info',
        message: feedback
      }]
    }
  } catch (error) {
    console.error('Failed to parse AI feedback:', error)
    parsedAiFeedback.value = [{
      questionId: 'Error',
      type: 'Parse Error',
      severity: 'error',
      message: 'Could not parse AI feedback. Please ensure it\'s in valid JSON or table format.'
    }]
  }
}

function applyRevisedQuestions() {
  try {
    let cleanedResponse = revisedQuestions.value
      .replace(/```json\n?|\n?```/g, '')
      .trim()
    
    const revisedQuestionsData = JSON.parse(cleanedResponse)
    
    if (!Array.isArray(revisedQuestionsData)) {
      throw new Error('Response must be a JSON array of questions')
    }
    
    // Update questions with revised versions
    revisedQuestionsData.forEach((revisedQ) => {
      const existingIndex = sampleQuestions.value.findIndex(q => q.id === revisedQ.id)
      if (existingIndex !== -1) {
        sampleQuestions.value[existingIndex] = { ...sampleQuestions.value[existingIndex], ...revisedQ }
      }
    })
    
    savePageState()
    $q.notify({ type: 'positive', message: 'Revised questions applied successfully!', position: 'top' })
    validationDialogOpen.value = false
  } catch (error) {
    console.error('Failed to apply revised questions:', error)
    $q.notify({ type: 'negative', message: 'Failed to apply revised questions. Please check the JSON format.', position: 'top' })
  }
}

function validateAndPreview() {
  try {
    // Remove markdown code blocks and clean up the response
    let cleanedResponse = aiResponse.value
      .replace(/```json\n?|\n?```/g, '') // Remove code blocks
      .replace(/[\u2018\u2019\u201C\u201D\u2026\u2027\u00A0\u00A8\u00A9\u00AA\u00AB\u00AC\u00B0\u00B4\u00B8\u00C0-\u00C1\u00C8\u00C9\u00CC\u00CD\u00CE\u00CF\u00D0-\u00D1\u00D8\u00DA\u00DB\u00DC\u00DD\u00DE\u00DF\u00E0-\u00E1\u00E8\u00EA\u00EB\u00EC\u00ED\u00EE\u00EF\u00F0-\u00F1\u00F2\u00F3\u00F4\u00F5\u00F6\u00F7\u00F8\u00F9\u00FA\u00FB\u00FC\u00FD\u00FE\u00FF]/g, '') // Remove weird characters
      .trim()
    
    if (!cleanedResponse) {
      $q.notify({ type: 'warning', message: 'Please paste some JSON content first.', position: 'top' })
      return
    }
    
    let parsedData = null
    try {
      parsedData = JSON.parse(cleanedResponse)
    } catch (parseError) {
      $q.notify({ type: 'negative', message: 'Invalid JSON format: ' + parseError.message, position: 'top' })
      return
    }

    // Handle different JSON structures
    let questionsArray = null
    
    // Case 1: Direct array [{...}, {...}]
    if (Array.isArray(parsedData)) {
      questionsArray = parsedData
    }
    // Case 2: Object with questions property {questions: [...]}
    else if (parsedData?.questions && Array.isArray(parsedData.questions)) {
      questionsArray = parsedData.questions
    }
    // Case 3: Array of sections [{section: "...", questions: [...]}, ...]
    else if (Array.isArray(parsedData) && parsedData[0]?.questions) {
      questionsArray = []
      parsedData.forEach(sectionData => {
        if (sectionData.questions && Array.isArray(sectionData.questions)) {
          // Add section info to each question
          const sectionName = sectionData.section || 'Unnamed Section'
          sectionData.questions.forEach(q => {
            questionsArray.push({
              ...q,
              section: q.section || sectionName
            })
          })
        }
      })
    }
    // Case 4: Single section object {section: "...", questions: [...]}
    else if (parsedData?.section && parsedData?.questions && Array.isArray(parsedData.questions)) {
      const sectionName = parsedData.section
      questionsArray = parsedData.questions.map(q => ({
        ...q,
        section: q.section || sectionName
      }))
    }
    // Case 5: Object with sections array {exam_info: {...}, sections: [{questions: [...]}, ...]}
    else if (parsedData?.sections && Array.isArray(parsedData.sections)) {
      questionsArray = []
      parsedData.sections.forEach(section => {
        if (section.questions && Array.isArray(section.questions)) {
          const sectionName = section.title || section.section || 'Unnamed Section'
          section.questions.forEach(q => {
            questionsArray.push({
              ...q,
              section: q.section || sectionName
            })
          })
        }
      })
    }
    
    if (!questionsArray || questionsArray.length === 0) {
      $q.notify({ 
        type: 'negative', 
        message: 'Invalid JSON format: could not find questions array. Expected format: [{id, type, marks, content}] or {questions: [...]} or {section: "...", questions: [...]}', 
        position: 'top',
        timeout: 5000
      })
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
      $q.notify({ 
        type: 'positive', 
        message: `Successfully parsed ${selectedQuestions.value.length} valid questions`, 
        position: 'top' 
      })
    } else {
      $q.notify({ 
        type: 'warning', 
        message: `Parsed ${parsedQuestions.value.length} questions but none are valid. Check the preview for errors.`, 
        position: 'top',
        timeout: 5000
      })
    }
  } catch (error) {
    console.error('Error:', error)
    $q.notify({ type: 'negative', message: 'Error processing AI response: ' + error.message, position: 'top' })
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
    
    if (!question.content.correct_option_index && question.content.correct_option_index !== 0) {
      errors.push('Multiple choice questions need correct_option_index')
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

async function duplicateQuestion(question) {
  const newId = Date.now() + Math.floor(Math.random() * 1000)
  const newQuestion = JSON.parse(JSON.stringify(question))
  newQuestion.id = newId

  const idx = sampleQuestions.value.findIndex(q => q.id === question.id)
  if (idx !== -1) {
    sampleQuestions.value.splice(idx + 1, 0, newQuestion)
    const sectionId = getQuestionSectionId(question)
    if (sectionId) {
      await setQuestionSection(newQuestion, sectionId)
    } else {
      savePageState()
    }
  }
}

function confirmDeleteQuestion(id) {
  questionToDelete.value = id
  deleteConfirmOpen.value = true
}

function executeDeleteQuestion() {
  if (questionToDelete.value) {
    deleteQuestion(questionToDelete.value)
    questionToDelete.value = null
    deleteConfirmOpen.value = false
  }
}

function editQuestionContent(question) {
  questionToEdit.value = question
  editQuestionPrompt.value = question?.content?.prompt || ''
  editQuestionOpen.value = true
}

function saveEditedMCQ(payload) {
  if (!questionToEdit.value) return
  if (!questionToEdit.value.content) questionToEdit.value.content = {}

  questionToEdit.value.content.prompt = payload?.prompt ?? ''
  if (Array.isArray(payload?.options)) {
    questionToEdit.value.content.options = payload.options
  }
  if (payload?.correct_option_index !== undefined) {
    questionToEdit.value.content.correct_option_index = payload.correct_option_index
  }
  if (payload?.explanation !== undefined) {
    questionToEdit.value.content.explanation = payload.explanation
  }

  savePageState()
  questionToEdit.value = null
  editQuestionPrompt.value = ''
  editQuestionOpen.value = false
}

function saveEditedQuestion() {
  if (questionToEdit.value) {
    if (!questionToEdit.value.content) questionToEdit.value.content = {}
    questionToEdit.value.content.prompt = editQuestionPrompt.value
    savePageState()
    questionToEdit.value = null
    editQuestionPrompt.value = ''
    editQuestionOpen.value = false
  }
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
    $q.notify({ type: 'negative', message: 'Failed to load image: ' + e.message, position: 'top' })
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

function generateFooterComponentHTML() {
  const footerText = pageOptions.value?.printFooter?.html || ''
  const showPageNumbers = !!pageOptions.value?.printFooter?.showPageNumbers
  const pageNumberFormat = String(pageOptions.value?.printFooter?.pageNumberFormat || 'page')
  const pageNumberFontSize = Number(pageOptions.value?.printFooter?.pageNumberFontSize) || 10
  const pageNumberColor = String(pageOptions.value?.printFooter?.pageNumberColor || '#000000')
  const singleLine = pageOptions.value?.printFooter?.singleLine === true
  const singleLineTopAlign = pageOptions.value?.printFooter?.singleLineTopAlign === true
  const initialPreviewText = ''
  
  let html = '<div class="footer-content"'
  
  if (singleLine) {
    html += ' style="display: flex; align-items: ' + (singleLineTopAlign ? 'flex-start' : 'center') + '; justify-content: space-between; gap: 12px; padding: 6mm 12mm;"'
  } else {
    html += ' style="padding: 6mm 12mm;"'
  }
  
  html += '>'
  
  // Left side: Footer text
  html += '<div class="footer-left"'
  if (singleLine) {
    html += ' style="flex: 1; min-width: 0;"'
  } else {
    html += ' style="text-align: left;"'
  }
  html += '>'
  
  if (footerText) {
    html += '<div class="footer-text">' + footerText + '</div>'
  }
  
  html += '</div>'
  
  // Right side: Page numbers
  if (showPageNumbers) {
    html += '<div class="footer-right"'
    if (singleLine) {
      html += ' style="flex: 0 0 auto; white-space: nowrap; text-align: right;"'
    } else {
      html += ' style="text-align: right;"'
    }
    html += '>'
    
    html += '<div class="page-number" data-format="' + pageNumberFormat + '" style="font-size:' + pageNumberFontSize + 'pt; color:' + pageNumberColor + '; --page-number-color:' + pageNumberColor + ';">'
    html += '<span class="page-number-content" data-format="' + pageNumberFormat + '"></span>'
    html += '</div>'
    
    html += '</div>'
  }
  
  html += '</div>'
  return html
}

function generatePrintHTML() {
  const examTitleEnabled = !!pageOptions.value?.examTitle?.enabled
  const examTitleTextRaw = String(pageOptions.value?.examTitle?.text || 'New Exam')
  const baseTitle = (examTitleEnabled ? examTitleTextRaw : 'Exam Print')
  const now = new Date()
  const dd = String(now.getDate()).padStart(2, '0')
  const mm = String(now.getMonth() + 1).padStart(2, '0')
  const yyyy = String(now.getFullYear())
  const HH = String(now.getHours()).padStart(2, '0')
  const MM = String(now.getMinutes()).padStart(2, '0')
  const timestamp = dd + '-' + mm + '-' + yyyy + '_' + HH + '-' + MM
  const safeTitle = String(baseTitle).replace(/[<>:"/\\|?*]/g, '').trim() || 'Exam'
  const docTitle = safeTitle + '_' + timestamp
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
  const pageNumberStartAtQuestionRaw = Number(pageOptions.value?.printFooter?.pageNumberStartAtQuestion)
  const pageNumberStartAtQuestion = Number.isFinite(pageNumberStartAtQuestionRaw) && pageNumberStartAtQuestionRaw > 0
    ? Math.floor(pageNumberStartAtQuestionRaw)
    : 1
  const pageNumberStartValueRaw = Number(pageOptions.value?.printFooter?.pageNumberStartValue)
  const pageNumberStartValue = Number.isFinite(pageNumberStartValueRaw) && pageNumberStartValueRaw > 0
    ? Math.floor(pageNumberStartValueRaw)
    : 1
  const pageCounterResetValue = Math.max(0, pageNumberStartValue - 1)
  const extraFooterMarginMm = (() => {
    const v = Number(pageOptions.value?.printFooter?.pageMarginBottomMm)
    return Number.isFinite(v) && v > 0 ? v : 0
  })()
  const bottomOffsetMm = (() => {
    const v = Number(pageOptions.value?.printFooter?.bottomOffsetMm)
    return Number.isFinite(v) ? v : 0
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
  html += '@page { size: A4; margin: ' + ((headerEnabled && hasHeaderContent) ? '6mm 12mm 12mm 12mm' : '12mm') + '; }'
  html += ' body { font-family: Arial, sans-serif; font-size: 12pt; line-height: 1.5; margin: 0; padding: ' + bodyPadPx + 'px; counter-reset: page 0 pages 0; }'
  html += ' @media print { .page-number-content[data-format="page"]::after { content: counter(page); } }'
  html += ' @media print { .page-number-content[data-format="page-of"]::after { content: counter(page) " of " counter(pages); } }'
  html += ' @media print { .page-number-content[data-format="page-slash"]::after { content: "Page " counter(page) " / " counter(pages); } }'
  html += ' @media print { .page-number-content[data-format="fraction"]::after { content: counter(page) "/" counter(pages); } }'
  html += ' @media screen { .page-number-content[data-format]::after { content: ""; } }'
  html += ' .page-counter-reset-marker { display: block; height: 0; margin: 0; padding: 0; line-height: 0; }'
  html += ' @media print { .page-counter-reset-marker { counter-reset: page ' + pageCounterResetValue + '; } }'
  html += ' h1 { margin: 0 0 14pt; font-size: 22pt; }'
  html += ' h2 { margin: 14pt 0 6pt; font-size: 15pt; text-decoration: underline; }'
  // Header: touches the top of the physical page printable area.
  html += ' .print-header { position: fixed; top: 0; left: 0; right: 0; z-index: 999; background: white; overflow: hidden; box-sizing: border-box; padding: 0 0; }'
  html += ' .print-footer { position: fixed; bottom: ' + bottomOffsetMm + 'mm; left: 0; right: 0; z-index: 999; background: transparent; overflow: hidden; box-sizing: border-box; padding: 0 0; }'
  html += ' @media print { .print-footer { position: fixed; bottom: ' + bottomOffsetMm + 'mm; left: 0; right: 0; z-index: 999; background: transparent; overflow: hidden; box-sizing: border-box; } }'
  html += ' @media print { .footer-content { display: flex; align-items: center; justify-content: space-between; gap: 12px; width: 100%; padding: 6mm 12mm; } }'
  html += ' @media print { .footer-left { flex: 1; min-width: 0; } }'
  html += ' @media print { .footer-right { flex: 0 0 auto; white-space: nowrap; text-align: right; } }'
  html += ' @media print { .page-number { font-size: ' + pageNumberFontSize + 'pt; color: ' + pageNumberColor + '; font-weight: 600; } }'
  
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
  html += ' .question-explanation { margin-top: 8pt; padding: 8pt; border-left: 3pt solid #1976d2; background: #f7fbff; font-size: 11pt; }'
  html += ' .question-explanation-title { font-weight: 700; margin-bottom: 4pt; }'
  html += ' .question-correct-answer { margin-top: 8pt; padding: 8pt; border-left: 3pt solid #43a047; background: #f1f8e9; font-size: 11pt; }'
  html += ' .question-correct-answer-title { font-weight: 700; margin-bottom: 4pt; }'
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
  html += ' @media screen { body { background: #e9ecef; padding: 36px; } .print-layout { width: 210mm; margin: 18px auto; background: #fff; box-shadow: 0 8px 24px rgba(0,0,0,0.12); } .page-break { display: block; height: 18px; margin: 24px 0; border-top: 2px dashed #c0c0c0; } }'
  html += ' .page-number { display: block; z-index: 1000; white-space: nowrap; line-height: 1.2; font-variant-numeric: tabular-nums; font-feature-settings: "tnum" 1; }'
  html += '</style>'
  html += '</head><body>'

  // Runtime measurement script:
  // - Waits for all images to fully load
  // - Measures the exact height of the header
  // - Applies that height + extraMargin to the #headerSpacer inside the <thead>
  // - This safely pushes content down on EVERY printed page.
  if ((headerEnabled && hasHeaderContent) || (footerEnabled && hasFooterContent)) {
    var scriptContent = "(function(){";
scriptContent += "window.__printReady = false;";
scriptContent += "function mmToPx(mm){";
scriptContent += "var d = document.createElement('div');";
scriptContent += "d.style.cssText = \"position:absolute;left:-9999px;top:0;height:\" + mm + \"mm;width:1px;\";";
scriptContent += "document.body.appendChild(d);";
scriptContent += "var px = d.getBoundingClientRect().height || d.offsetHeight || 0;";
scriptContent += "d.remove();";
scriptContent += "return Math.max(1, px);";
scriptContent += "}";
scriptContent += "function doMeasure(){";
scriptContent += "try {";
scriptContent += "var h = document.getElementById('printHeaderRoot');";
scriptContent += "var hs = document.getElementById('headerSpacer');";
scriptContent += "if (h && hs) {";
scriptContent += "var hPx = h.offsetHeight;";
scriptContent += "var hExtraPx = Math.ceil(" + extraMarginMm + " * 96 / 25.4);";
scriptContent += "hs.style.height = (hPx + hExtraPx) + 'px';";
scriptContent += "}";
scriptContent += "if (" + (footerEnabled && hasFooterContent && footerReserveSpace ? 'true' : 'false') + ") {";
scriptContent += "var f = document.getElementById('printFooterRoot');";
scriptContent += "var fs = document.getElementById('footerSpacer');";
scriptContent += "if (f && fs) {";
scriptContent += "var fPx = f.offsetHeight;";
scriptContent += "var fExtraPx = Math.ceil(" + extraFooterMarginMm + " * 96 / 25.4);";
scriptContent += "fs.style.height = (fPx + fExtraPx) + 'px';";
scriptContent += "}";
scriptContent += "}";
scriptContent += "setTimeout(function(){}, 100);";
scriptContent += "setTimeout(function(){}, 500);";
scriptContent += "try {";
scriptContent += "if (typeof useLastPageText !== 'undefined' && useLastPageText && lastPageText) {";
scriptContent += "var footerTexts = document.querySelectorAll('.footer-text');";
scriptContent += "if (footerTexts && footerTexts.length) {";
scriptContent += "footerTexts.forEach(function(el, index) {";
scriptContent += "if (index === footerTexts.length - 1) {";
scriptContent += "el.innerHTML = lastPageText;";
scriptContent += "}";
scriptContent += "});";
scriptContent += "}";
scriptContent += "}";
scriptContent += "} catch(e) {}";
scriptContent += "window.__printReady = true;";
scriptContent += "} catch(e) { console.error('[PAGE NUMBER DEBUG] Error in doMeasure:', e); }";
scriptContent += "}";
scriptContent += "try { window.addEventListener('beforeprint', function(){}); } catch(e) {}";
scriptContent += "try { window.addEventListener('afterprint', function(){ console.log('Print completed'); }); } catch(e) {}";
scriptContent += "try { if (window.matchMedia) { var mediaQueryList = window.matchMedia('print'); mediaQueryList.addListener(function(mql) { if (mql.matches) {} }); } } catch(e) {}";
scriptContent += "setTimeout(function(){}, 100);";
scriptContent += "window.addEventListener('load', function(){";
scriptContent += "var imgs = document.querySelectorAll('img');";
scriptContent += "if (!imgs.length) { doMeasure(); return; }";
scriptContent += "var total = imgs.length, done = 0;";
scriptContent += "function onDone(){ if (++done >= total) doMeasure(); }";
scriptContent += "[].forEach.call(imgs, function(img){ if(img.complete){ onDone(); } else { img.onload = img.onerror = onDone; } });";
scriptContent += "});";
scriptContent += "})();";
    html += '<script>' + scriptContent + '<\/script>';
  }


  if (headerEnabled && hasHeaderContent) {
    const headerInner = headerMode === 'image'
      ? (
        headerAutoFit
          ? ('<img src="' + headerImageUrl + '" style="width:100%; height:auto; object-fit:contain; display:block;">')
          : ('<img src="' + headerImageUrl + '" style="width:100%; height:100%; object-fit:' + headerImageFit + '; display:block;">')
      )
      : headerHtml
    html += '<div id="printHeaderRoot" class="print-header" style="' + (headerAutoFit ? '' : ('height:' + headerHeightPt + 'pt;')) + '">' + headerInner + '</div>'
  }

  if (footerEnabled && hasFooterContent) {
    const useLastPageText = !!pageOptions.value?.printFooter?.useLastPageText
    const lastPageText = String(pageOptions.value?.printFooter?.lastPageText || '')
    
    const footerContentHtml = footerMode === 'image'
      ? (
        footerAutoFit
          ? ('<img src="' + footerImageUrl + '" style="width:100%; height:auto; object-fit:contain; display:block;">')
          : ('<img src="' + footerImageUrl + '" style="width:100%; height:100%; object-fit:' + footerImageFit + '; display:block;">')
      )
      : ('<div class="footer-text" style="font-size:' + footerTextFontSizePt + 'pt; color:' + footerTextColor + ';">' + footerHtml + '</div>')

    const footerBorderStyle = footerTopBorder ? 'border-top:1px solid rgba(0,0,0,0.25);' : ''

    let footerInner = footerContentHtml
    
    // Add last page text support
    if (useLastPageText && lastPageText && footerMode !== 'image') {
      scriptContent += "var useLastPageText = " + useLastPageText + ";"
      scriptContent += "var lastPageText = '" + lastPageText.replace(/'/g, "\\'") + "';"
      scriptContent += "var regularFooterText = '" + footerHtml.replace(/'/g, "\\'") + "';"
    }

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
      
      // Apply footer offset to page numbers if enabled
      const applyOffsetToPageNumbers = !!pageOptions.value?.printFooter?.applyOffsetToPageNumbers
      const pageNumberOffsetStyle = (applyOffsetToPageNumbers && bottomOffsetMm !== 0) ? 
        (position + ':' + bottomOffsetMm + 'mm;') : ''

      if (footerSingleLine) {
        footerInner =
          '<div class="footer-line" style="display:flex; align-items:center; justify-content:space-between; gap:12px; padding:6mm 12mm;">' +
            '<div class="footer-line-left" style="flex:1; min-width:0;">' + footerContentHtml + '</div>' +
            '<div class="footer-line-right" style="flex:0 0 auto; white-space:nowrap; font-size:' + pageNumberFontSize + 'pt; color:' + pageNumberColor + '; ' + pageNumberOffsetStyle + '">' +
              '<span class="page-number-content" data-format="' + pageNumberFormat + '">1</span>' +
            '</div>' +
          '</div>'
      } else {
        footerInner +=
          '<div class="page-number" style="position:absolute; ' + position + ':0; ' + style + ' font-size:' + pageNumberFontSize + 'pt; color:' + pageNumberColor + '; padding:8mm 12mm; ' + pageNumberOffsetStyle + '">' +
            '<span class="page-number-content" data-format="' + pageNumberFormat + '">1</span>' +
          '</div>'
      }
    } else if (footerSingleLine) {
      footerInner = '<div class="footer-line" style="padding:6mm 12mm;">' + footerContentHtml + '</div>'
    }

    // Generate footer using reusable component structure
    const footerOffsetStyle = bottomOffsetMm !== 0 ? ('bottom:' + bottomOffsetMm + 'mm;') : ''
    const footerComponentHtml = generateFooterComponentHTML()
    html += '<div id="printFooterRoot" class="print-footer" style="' + footerBorderStyle + footerOffsetStyle + (footerAutoFit ? '' : ('height:' + footerHeightPt + 'pt;')) + '">' + footerComponentHtml + '</div>'
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

    // Add page break before section if enabled
    if (section.pageBreakBefore) {
      html += '<div class="page-break"></div>'
    }

    // Add line before section if enabled
    if (section.lineBefore) {
      const lineStyle = section.lineStyle || 'solid'
      const lineThickness = section.lineThickness || 1
      const lineColor = section.lineColor || '#000000'
      html += '<div class="section-line-before" style="border-top: ' + lineThickness + 'px ' + lineStyle + ' ' + lineColor + '; margin-bottom: 10pt;"></div>'
    }

    html += '<div class="section-header">'
    html += '<h2>' + renderMathContent(section.title) + '</h2>'
    if (section.instructions) {
      html += '<div class="section-instructions">' + renderMathContent(section.instructions) + '</div>'
    }
    html += renderSectionTotalHTML(sectionTotalMarks(section.id), pageOptions.value.sectionTotal)
    html += '</div>'

    // Add line after section if enabled
    if (section.lineAfter) {
      const lineStyle = section.lineStyle || 'solid'
      const lineThickness = section.lineThickness || 1
      const lineColor = section.lineColor || '#000000'
      html += '<div class="section-line-after" style="border-bottom: ' + lineThickness + 'px ' + lineStyle + ' ' + lineColor + '; margin-top: 10pt;"></div>'
    }

    const forceEssay = !!section.forceQuestionsToEssay

    sectionQuestions.forEach((question) => {
      globalIndex += 1
      const label = renderMathContent(formatQuestionLabel(globalIndex, pageOptions.value.questionNumbering))
      const breaksMap = pageOptions.value?.questionNumbering?.pageBreaksBefore || {}
      if (breaksMap[String(question?.id)]) {
        html += '<div class="page-break"></div>'
      }
      if (showPageNumbers && globalIndex === pageNumberStartAtQuestion) {
        html += '<div class="page-counter-reset-marker" data-start-question="' + globalIndex + '"></div>'
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
        html += '<span class="question-inline-text">' + renderMathContent(question.content?.prompt || '') + '</span>'
        html += '</div>'
        html += showMarks ? ('<span>' + question.marks + ' marks</span>') : '<span></span>'
        html += '</div>'
      } else {
        html += '<div class="question-header">'
        html += '<span>' + label + '</span>'
        html += showMarks ? ('<span>' + question.marks + ' marks</span>') : '<span></span>'
        html += '</div>'
        html += '<div class="question-content">' + renderMathContent(question.content?.prompt || '') + '</div>'
      }

      if (!forceEssay && Array.isArray(question.content?.options) && question.content.options.length > 0) {
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

      if (pageOptions.value?.showExplanationUnderQuestion) {
        const explanationText = question?.explanation || question?.content?.explanation
        if (explanationText) {
          html += '<div class="question-explanation">'
          html += '<div class="question-explanation-title">Explanation:</div>'
          html += '<div class="question-explanation-body">' + renderMathContent(explanationText) + '</div>'
          html += '</div>'
        }
      }

      if (pageOptions.value?.showCorrectAnswerUnderQuestion) {
        const answerText = getAnswerKeyText(question)
        if (answerText && answerText !== '-') {
          html += '<div class="question-correct-answer">'
          html += '<div class="question-correct-answer-title">Correct Answer:</div>'
          html += '<div class="question-correct-answer-body">' + answerText + '</div>'
          html += '</div>'
        }
      }

      const linesCount = getPrintAnswerLines(forceEssay ? ({ ...question, type: 'essay' }) : question)
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

  // Add Answer Key at the end if enabled
  const answerKeyEnabled = !!pageOptions.value?.answerKey?.enabled
  const answerKeyShowAtEnd = pageOptions.value?.answerKey?.showAtEnd !== false
  const answerKeyShowNotes = pageOptions.value?.answerKey?.showNotes !== false
  const answerKeyPageBreakBefore = pageOptions.value?.answerKey?.pageBreakBefore !== false
  const answerKeyTemplate = pageOptions.value?.answerKey?.template || 'full'

  if (answerKeyEnabled && answerKeyShowAtEnd) {
    if (answerKeyPageBreakBefore) {
      html += '<div class="page-break"></div>'
    }

    html += '<div class="answer-key-section" style="margin: 20px 0; page-break-inside: avoid;">'
    const akTitle = pageOptions.value?.answerKey?.title?.trim() || 'Answer Key'
    html += '<h3 class="text-center" style="border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 15px;">' + akTitle + '</h3>'

    if (answerKeyTemplate === 'compact_choice') {
      html += '<table style="width: 100%; border-collapse: collapse; font-size: 12px; margin: 20px 0;">'
      html += '<thead style="background-color: #f5f5f5;">'
      html += '<tr>'
      html += '<th style="padding: 8px; border: 1px solid #ddd; font-weight: 600; color: #333; text-align: center; width: 60px;">#</th>'
      html += '<th style="padding: 8px; border: 1px solid #ddd; font-weight: 600; color: #333; text-align: center; width: 90px;">Letter</th>'
      html += '<th style="padding: 8px; border: 1px solid #ddd; font-weight: 600; color: #333;">Correct Option</th>'
      html += '<th style="padding: 8px; border: 1px solid #ddd; font-weight: 600; color: #333; text-align: center; width: 60px;">✓</th>'
      html += '</tr>'
      html += '</thead>'
      html += '<tbody>'

      let answerIndex = 0
      sampleQuestions.value.forEach((question) => {
        answerIndex += 1
        const choiceSymbol = getAnswerKeyChoiceSymbol(question)
        const choiceText = getAnswerKeyChoiceText(question)

        html += '<tr style="' + (answerIndex % 2 === 0 ? 'background-color: #fafafa;' : '') + '">'
        html += '<td style="padding: 8px; border: 1px solid #ddd; text-align: center;">' + answerIndex + '</td>'
        html += '<td style="padding: 8px; border: 1px solid #ddd; text-align: center;"><strong style="color: #1976d2;">' + choiceSymbol + '</strong></td>'
        html += '<td style="padding: 8px; border: 1px solid #ddd; vertical-align: top; line-height: 1.35;">' + choiceText + '</td>'
        html += '<td style="padding: 8px; border: 1px solid #ddd; text-align: center; font-weight: 800; color: #2e7d32;">' + (choiceSymbol !== '-' ? '✔' : '') + '</td>'
        html += '</tr>'
      })

      html += '</tbody>'
      html += '</table>'
    } else if (answerKeyTemplate === 'compact_letter_text') {
      html += '<table style="width: 100%; border-collapse: collapse; font-size: 12px; margin: 20px 0;">'
      html += '<thead style="background-color: #f5f5f5;">'
      html += '<tr>'
      html += '<th style="padding: 8px; border: 1px solid #ddd; font-weight: 600; color: #333; text-align: center; width: 60px;">#</th>'
      html += '<th style="padding: 8px; border: 1px solid #ddd; font-weight: 600; color: #333; text-align: center; width: 90px;">Letter</th>'
      html += '<th style="padding: 8px; border: 1px solid #ddd; font-weight: 600; color: #333;">Correct Option</th>'
      html += '</tr>'
      html += '</thead>'
      html += '<tbody>'

      let answerIndex = 0
      sampleQuestions.value.forEach((question) => {
        answerIndex += 1
        const choiceSymbol = getAnswerKeyChoiceSymbol(question)
        const choiceText = getAnswerKeyChoiceText(question)

        html += '<tr style="' + (answerIndex % 2 === 0 ? 'background-color: #fafafa;' : '') + '">'
        html += '<td style="padding: 8px; border: 1px solid #ddd; text-align: center;">' + answerIndex + '</td>'
        html += '<td style="padding: 8px; border: 1px solid #ddd; text-align: center;"><strong style="color: #1976d2;">' + choiceSymbol + '</strong></td>'
        html += '<td style="padding: 8px; border: 1px solid #ddd; vertical-align: top; line-height: 1.35;">' + choiceText + '</td>'
        html += '</tr>'
      })

      html += '</tbody>'
      html += '</table>'
    } else {
      html += '<table style="width: 100%; border-collapse: collapse; font-size: 12px; margin: 20px 0;">'
      html += '<thead style="background-color: #f5f5f5;">'
      html += '<tr>'
      html += '<th style="padding: 8px; border: 1px solid #ddd; font-weight: 600; color: #333; text-align: center; width: 60px;">#</th>'
      html += '<th style="padding: 8px; border: 1px solid #ddd; font-weight: 600; color: #333;">Question</th>'
      html += '<th style="padding: 8px; border: 1px solid #ddd; font-weight: 600; color: #333; text-align: center; width: 80px;">Marks</th>'
      html += '<th style="padding: 8px; border: 1px solid #ddd; font-weight: 600; color: #333; text-align: center; width: 100px;">Answer</th>'
      html += '</tr>'
      html += '</thead>'
      html += '<tbody>'

      let answerIndex = 0
      sampleQuestions.value.forEach((question) => {
        answerIndex += 1
        const prompt = question.content?.prompt || ''
        // Render math content for question text, then truncate if needed
        let questionText = renderMathContent(prompt)
        if (!questionText) {
          questionText = 'N/A'
        }
        const answerText = getAnswerKeyText(question)
        // Also render math in answer text for LaTeX support
        const renderedAnswer = renderMathContent(answerText)

        html += '<tr style="' + (answerIndex % 2 === 0 ? 'background-color: #fafafa;' : '') + '">'
        html += '<td style="padding: 8px; border: 1px solid #ddd; text-align: center;">' + answerIndex + '</td>'
        html += '<td style="padding: 8px; border: 1px solid #ddd; vertical-align: top; max-width: 300px; word-wrap: break-word; line-height: 1.4;">' + questionText + '</td>'
        html += '<td style="padding: 8px; border: 1px solid #ddd; text-align: center;">' + (question.marks || 0) + '</td>'
        html += '<td style="padding: 8px; border: 1px solid #ddd; text-align: center;"><strong style="color: #1976d2;">' + renderedAnswer + '</strong></td>'
        html += '</tr>'
      })

      const totalMarks = sampleQuestions.value.reduce((sum, q) => sum + (q.marks || 0), 0)
      html += '<tr style="background-color: #e8f5e9; font-weight: 600;">'
      html += '<td colspan="2" style="padding: 8px; border: 1px solid #ddd; text-align: right; border-top: 2px solid #4caf50;"><strong>Total:</strong></td>'
      html += '<td style="padding: 8px; border: 1px solid #ddd; text-align: center; border-top: 2px solid #4caf50;"><strong>' + totalMarks + '</strong></td>'
      html += '<td style="padding: 8px; border: 1px solid #ddd; border-top: 2px solid #4caf50;"></td>'
      html += '</tr>'
      html += '</tbody>'
      html += '</table>'
    }

    if (answerKeyShowNotes) {
      const defaultNote = 'Note: This answer key should be separated from the exam paper before distribution to students.'
      const noteText = pageOptions.value?.answerKey?.notesText?.trim() || defaultNote
      html += '<p style="margin-top: 15px; font-size: 10px; color: #666; border-top: 1px dashed #ccc; padding-top: 10px;">'
      html += '<em>' + noteText + '</em>'
      html += '</p>'
    }

    html += '</div>'
  }

  html += '</body></html>'
  return html
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

  // For other types, use the configured answer lines
  const lines = Number(pageOptions.value?.answerLines?.defaultLines || 3)
  return lines
}

function getAnswerKeyText(question) {
  // Check if question is in a section that forces essay mode
  const sectionId = getQuestionSectionId(question)
  const section = sections.value.find(s => s.id === sectionId)
  const forceEssay = !!section?.forceQuestionsToEssay
  const showOptText = !!pageOptions.value?.answerKey?.mcqShowOptionText

  if (question.type === 'multiple_choice') {
    // correct_option_index is the canonical field (ver 3); fall back to correct_answer for legacy data
    const v = question.content?.correct_option_index ?? question.content?.correct_answer ?? question.correct_answer
    const labelStyle = pageOptions.value?.mcqOptions?.labelStyle || 'letter'
    const customTpl = pageOptions.value?.mcqOptions?.customLabelTemplate || '{letter})'

    // If section forces essay, return the actual option text instead of letter
    if (forceEssay || showOptText) {
      const idx = (typeof v === 'number' && Number.isFinite(v))
        ? v
        : ((typeof v === 'string' && v.trim() !== '' && !Number.isNaN(Number(v))) ? Number(v) : null)

      if (idx !== null && Array.isArray(question.content?.options)) {
        const optionText = question.content.options[idx]?.text || question.content.options[idx]
        if (optionText) return String(optionText)
      }
    }

    const idx = (typeof v === 'number' && Number.isFinite(v))
      ? v
      : ((typeof v === 'string' && v.trim() !== '' && !Number.isNaN(Number(v))) ? Number(v) : null)

    if (idx !== null) {
      const n = idx + 1
      const letter = String.fromCharCode('A'.charCodeAt(0) + idx)
      if (labelStyle === 'number') return String(n)
      if (labelStyle === 'custom') {
        return String(customTpl)
          .replaceAll('{i}', String(idx))
          .replaceAll('{n}', String(n))
          .replaceAll('{letter}', String(letter))
      }
      return letter
    }

    if (typeof v === 'string' && v.trim() !== '') return v
  } else if (question.type === 'true_false') {
    const v = question.content?.correct_option_index ?? question.content?.correct_answer ?? question.correct_answer
    if (v !== undefined) return v ? 'True' : 'False'
  } else {
    const v = question.content?.correct_option_index ?? question.content?.correct_answer ?? question.correct_answer
    if (v) return v
  }
  return '-'
}

function getAnswerKeyChoiceLabel(question) {
  if (question?.type !== 'multiple_choice') return '-'
  const sectionId = getQuestionSectionId(question)
  const section = sections.value.find(s => s.id === sectionId)
  const forceEssay = !!section?.forceQuestionsToEssay
  const showOptText = !!pageOptions.value?.answerKey?.mcqShowOptionText

  const labelStyle = pageOptions.value?.mcqOptions?.labelStyle || 'letter'
  const customTpl = pageOptions.value?.mcqOptions?.customLabelTemplate || '{letter})'
  // correct_option_index is canonical (ver 3); fall back to correct_answer for legacy data
  const v = question?.content?.correct_option_index ?? question?.content?.correct_answer ?? question?.correct_answer

  const idx = (typeof v === 'number' && Number.isFinite(v))
    ? v
    : ((typeof v === 'string' && v.trim() !== '' && !Number.isNaN(Number(v))) ? Number(v) : null)
  if (idx === null) return '-'

  if (forceEssay || showOptText) {
    if (Array.isArray(question.content?.options)) {
      const optionText = question.content.options[idx]?.text || question.content.options[idx]
      if (optionText) return renderMathContent(String(optionText))
    }
  }

  const n = idx + 1
  const letter = String.fromCharCode('A'.charCodeAt(0) + idx)
  if (labelStyle === 'number') return String(n)
  if (labelStyle === 'custom') {
    const res = String(customTpl)
      .replaceAll('{i}', String(idx))
      .replaceAll('{n}', String(n))
      .replaceAll('{letter}', String(letter))
    // For the compact template we want just the choice symbol, not trailing punctuation/spaces.
    return res.replace(/[)\.\s]+$/g, '')
  }
  return letter
}

function getAnswerKeyChoiceText(question) {
  if (!question) return '-'

  if (question?.type === 'multiple_choice') {
    const v = question?.content?.correct_option_index ?? question?.content?.correct_answer ?? question?.correct_answer
    const idx = (typeof v === 'number' && Number.isFinite(v))
      ? v
      : ((typeof v === 'string' && v.trim() !== '' && !Number.isNaN(Number(v))) ? Number(v) : null)
    if (idx === null) return '-'

    if (Array.isArray(question.content?.options)) {
      const optionText = question.content.options[idx]?.text || question.content.options[idx]
      if (optionText) return renderMathContent(String(optionText))
    }
    return '-'
  }

  // Fallback for non-MCQ: keep it readable in the compact table.
  const raw = getAnswerKeyText(question)
  return renderMathContent(String(raw || '-'))
}

function getAnswerKeyChoiceSymbol(question) {
  if (question?.type !== 'multiple_choice') return '-'

  const labelStyle = pageOptions.value?.mcqOptions?.labelStyle || 'letter'
  const customTpl = pageOptions.value?.mcqOptions?.customLabelTemplate || '{letter})'
  // correct_option_index is canonical (ver 3); fall back to correct_answer for legacy data
  const v = question?.content?.correct_option_index ?? question?.content?.correct_answer ?? question?.correct_answer

  const idx = (typeof v === 'number' && Number.isFinite(v))
    ? v
    : ((typeof v === 'string' && v.trim() !== '' && !Number.isNaN(Number(v))) ? Number(v) : null)
  if (idx === null) return '-'

  const n = idx + 1
  const letter = String.fromCharCode('A'.charCodeAt(0) + idx)
  if (labelStyle === 'number') return String(n)
  if (labelStyle === 'custom') {
    const res = String(customTpl)
      .replaceAll('{i}', String(idx))
      .replaceAll('{n}', String(n))
      .replaceAll('{letter}', String(letter))
    return res.replace(/[)\.\s]+$/g, '')
  }
  return letter
}

// Watch for changes and auto-save
watch(
  [sampleQuestions, pageOptions],
  async () => {
    // Skip auto-save during initial load to prevent overwriting loaded data
    if (isLoadingState.value) return
    await nextTick()
    await savePageState()
  },
  { deep: true }
)

onMounted(async () => {
  loadSettingsPresets()
  await loadPageState()

  // Load exam from URL if exam_id parameter is present
  await loadExamFromUrl()

  try {
    const savedExamId = localStorage.getItem(LAST_EXAM_ID_STORAGE_KEY)
    if (savedExamId && !lastSavedExamId.value) {
      lastSavedExamId.value = savedExamId
    }
  } catch (e) {}
})

watch(lastSavedExamId, (v) => {
  try {
    if (v) localStorage.setItem(LAST_EXAM_ID_STORAGE_KEY, String(v))
    else localStorage.removeItem(LAST_EXAM_ID_STORAGE_KEY)
  } catch (e) {}
})
</script>

<style scoped>
.exam-test-page {
  min-height: 100vh;
  background: #f5f5f5;
  padding: 20px;
  padding-top: 80px;
}

.cursor-pointer {
  cursor: pointer;
}

.title-input {
  min-width: 200px;
}

.prompt-dialog {
  max-width: 900px;
}

.prompt-textarea {
  font-family: 'Courier New', monospace;
  font-size: 13px;
}

.exam-toolbar {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 1000;
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

.pdf-preview-container {
  height: calc(100vh - 120px);
  border-radius: 10px;
  overflow: hidden;
  border: 1px solid #e0e0e0;
  background: #e9ecef;
}

.pdf-preview-iframe {
  width: 100%;
  height: 100%;
  border: none;
  background: transparent;
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

/* AI Chat Styles */
.chat-container {
  background: #f5f5f5;
  border-radius: 8px;
  padding: 16px;
}

.chat-message {
  border-radius: 8px;
  max-width: 80%;
}

.user-message {
  background: #e3f2fd;
  margin-left: auto;
  border: 1px solid #bbdefb;
}

.ai-message {
  background: white;
  margin-right: auto;
  border: 1px solid #e0e0e0;
}

.message-sender {
  font-weight: 600;
  font-size: 0.9em;
  color: #666;
  display: flex;
  align-items: center;
}

.message-content {
  word-wrap: break-word;
  line-height: 1.6;
}

.questions-container {
  background: white;
  border-radius: 8px;
  padding: 24px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.questions-container--pdf {
  background: #e9ecef;
  box-shadow: none;
  padding: 36px;
}

.questions-container--pdf .question-row {
  background: #fff;
  border-radius: 0;
  box-shadow: 0 8px 24px rgba(0,0,0,0.12);
  width: 210mm;
  margin: 0 auto 18px;
  padding: 24px;
}

.question-row {
  position: relative;
}

.question-row--edit {
  border: 1px solid transparent;
  border-radius: 6px;
  transition: border-color 0.15s;
  margin-bottom: 16px;
}

.question-row--edit:hover {
  border-color: #e0e0e0;
}

/* Edit mode top bar */
.question-edit-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: #f5f7fa;
  border: 1px solid #e3e8ef;
  border-radius: 6px 6px 0 0;
  padding: 4px 8px 4px 10px;
  margin-bottom: 0;
  gap: 8px;
}

.question-row--edit:hover .question-edit-bar {
  border-color: #b0bec5;
  background: #eef2f7;
}

.question-edit-bar__left {
  display: flex;
  align-items: center;
  gap: 8px;
  flex: 1;
  min-width: 0;
}

.question-edit-bar__num {
  font-size: 11px;
  font-weight: 600;
  letter-spacing: 0.04em;
  white-space: nowrap;
  color: #888;
}

.question-edit-bar__section {
  font-size: 12px;
}

.question-edit-bar__right {
  display: flex;
  align-items: center;
  gap: 2px;
  flex-shrink: 0;
}

/* When edit bar is present, give the question content a subtle left border */
.question-row--edit :deep(.question-display) {
  border-left: 3px solid #e3e8ef;
  padding-left: 10px;
  margin-top: 0;
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

.code-block {
  background: #1e1e1e;
  color: #d4d4d4;
  padding: 12px;
  border-radius: 4px;
  font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
  font-size: 12px;
  overflow-x: auto;
  white-space: pre-wrap;
  word-wrap: break-word;
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
