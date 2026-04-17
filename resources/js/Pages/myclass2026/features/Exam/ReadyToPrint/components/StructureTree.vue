<template>
  <div class="structure-tree">
    <div class="tree-header">
      <h3>{{ $t('exam.readyToPrint.structure.title') }}</h3>
      <q-btn
        flat
        round
        dense
        icon="add"
        :label="$t('exam.readyToPrint.structure.addSection')"
        @click="addSection"
        class="add-section-btn"
      />
    </div>
    <q-separator />
    <div class="tree-content">
      <div
        v-for="section in exam.sections"
        :key="section.id"
        class="section-node"
        :class="{ active: selectedSectionId === section.id }"
        @click="selectSection(section.id)"
      >
        <div class="section-header">
          <q-icon name="folder" class="section-icon" />
          <span class="section-title">{{ section.title || $t('exam.readyToPrint.structure.untitledSection') }}</span>
          <q-badge color="primary" class="section-badge">
            {{ section.questions?.length || 0 }}
          </q-badge>
        </div>
        <div class="section-meta">
          <span v-if="section.instructions" class="section-instructions">
            {{ section.instructions.slice(0, 60) }}{{ section.instructions.length > 60 ? '...' : '' }}
          </span>
        </div>
        <div class="section-actions">
          <q-btn
            flat
            round
            dense
            size="sm"
            icon="add"
            @click.stop="addQuestion(section.id)"
          />
          <q-btn
            flat
            round
            dense
            size="sm"
            icon="edit"
            @click.stop="editSection(section.id)"
          />
          <q-btn
            flat
            round
            dense
            size="sm"
            icon="delete"
            @click.stop="removeSection(section.id)"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { useExamReadyToPrintStore } from '@/Stores/examReadyToPrintStore'

const { t } = useI18n()
const store = useExamReadyToPrintStore()

const exam = computed(() => store.exam)
const selectedSectionId = computed(() => store.selection.sectionId)

function selectSection(sectionId) {
  store.selectSection(sectionId)
}

function addSection() {
  store.addSection({
    title: t('exam.readyToPrint.structure.newSection'),
  })
}

function editSection(sectionId) {
  selectSection(sectionId)
}

function removeSection(sectionId) {
  store.removeSection(sectionId)
}

function addQuestion(sectionId) {
  store.addQuestion(sectionId, {
    type: 'text',
    marks: 1,
    content: { prompt: '' },
  })
}
</script>

<style scoped>
.structure-tree {
  height: 100%;
  display: flex;
  flex-direction: column;
}

.tree-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px;
}

.tree-header h3 {
  margin: 0;
  font-size: 16px;
  font-weight: 600;
}

.add-section-btn {
  font-size: 12px;
}

.tree-content {
  flex: 1;
  overflow-y: auto;
  padding: 0 8px 8px;
}

.section-node {
  padding: 12px;
  margin-bottom: 4px;
  border-radius: 8px;
  cursor: pointer;
  transition: background-color 0.2s;
}

.section-node:hover {
  background: #f5f5f5;
}

.section-node.active {
  background: #e3f2fd;
  border: 1px solid #1976d2;
}

.section-header {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 4px;
}

.section-icon {
  color: #1976d2;
}

.section-title {
  font-weight: 500;
  flex: 1;
}

.section-badge {
  font-size: 10px;
}

.section-meta {
  font-size: 12px;
  color: #666;
  margin-bottom: 8px;
}

.section-instructions {
  font-style: italic;
}

.section-actions {
  display: flex;
  gap: 4px;
  opacity: 0;
  transition: opacity 0.2s;
}

.section-node:hover .section-actions {
  opacity: 1;
}
</style>
