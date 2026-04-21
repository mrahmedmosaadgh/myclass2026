import { defineStore } from 'pinia'

const createEmptyExam = () => ({
    schemaVersion: 1,
    examMeta: {
        title: '',
        subject: '',
        grade: '',
        term: '',
        durationMinutes: null,
        date: null,
    },
    pageSetup: {
        paper: 'A4',
        marginsMm: {
            top: 12,
            right: 12,
            bottom: 12,
            left: 12,
        },
        headerHeightMm: 18,
        footerHeightMm: 12,
    },
    headerConfig: {
        mode: 'first_page_only',
        fields: [],
    },
    footerConfig: {
        pageNumbering: 'x_of_y',
        continuationMessage: 'Continue',
        endMessage: 'End',
    },
    printFooter: {
        enabled: true,
        reserveSpace: true,
        showPageNumbers: true,
        bottomOffsetMm: 0,
        applyOffsetToPageNumbers: false,
        pageNumberPosition: 'bottom-center',
    },
    layoutDefaults: {
        paginationMode: 'hybrid',
        overflowStrategy: 'move_to_next_page',
    },
    sections: [],
})

const createSection = (partial = {}) => ({
    id: partial.id ?? crypto.randomUUID(),
    title: partial.title ?? '',
    instructions: partial.instructions ?? '',
    rules: partial.rules ?? {},
    questions: partial.questions ?? [],
})

const createQuestion = (partial = {}) => ({
    id: partial.id ?? crypto.randomUUID(),
    type: partial.type ?? 'text',
    marks: partial.marks ?? null,
    content: partial.content ?? {},
    response: partial.response ?? {},
    evaluation: partial.evaluation ?? { mode: 'manual' },
    layout: partial.layout ?? {},
})

export const useExamReadyToPrintStore = defineStore('examReadyToPrint', {
    state: () => ({
        lifecycle: {
            status: 'draft',
            locked: false,
            dirty: false,
        },
        exam: createEmptyExam(),
        selection: {
            sectionId: null,
            questionId: null,
        },
        validation: {
            lastRunAt: null,
            summary: {
                errors: 0,
                warnings: 0,
                infos: 0,
            },
            items: [],
        },
        renderSnapshot: {
            id: null,
            createdAt: null,
        },
    }),

    getters: {
        selectedSection(state) {
            if (!state.selection.sectionId) return null
            return state.exam.sections.find(s => s.id === state.selection.sectionId) ?? null
        },
        selectedQuestion(state) {
            if (!state.selection.questionId) return null
            for (const section of state.exam.sections) {
                const q = (section.questions ?? []).find(x => x.id === state.selection.questionId)
                if (q) return q
            }
            return null
        },
        canApprove(state) {
            return state.lifecycle.status === 'validated' && !state.lifecycle.dirty
        },
        canRender(state) {
            return state.lifecycle.status === 'approved' && !state.lifecycle.dirty
        },
    },

    actions: {
        reset() {
            this.lifecycle = { status: 'draft', locked: false, dirty: false }
            this.exam = createEmptyExam()
            this.selection = { sectionId: null, questionId: null }
            this.validation = {
                lastRunAt: null,
                summary: { errors: 0, warnings: 0, infos: 0 },
                items: [],
            }
            this.renderSnapshot = { id: null, createdAt: null }
        },

        markDirty() {
            this.lifecycle.dirty = true
            if (this.lifecycle.status !== 'draft') this.lifecycle.status = 'draft'
            this.renderSnapshot = { id: null, createdAt: null }
        },

        setLifecycleStatus(status) {
            this.lifecycle.status = status
            this.lifecycle.locked = status === 'approved' || status === 'rendered'
            if (status === 'validated' || status === 'approved' || status === 'rendered') {
                this.lifecycle.dirty = false
            }
        },

        selectSection(sectionId) {
            this.selection.sectionId = sectionId
            this.selection.questionId = null
        },

        selectQuestion(sectionId, questionId) {
            this.selection.sectionId = sectionId
            this.selection.questionId = questionId
        },

        addSection(partial) {
            console.log('Store: addSection called with:', partial)
            console.log('Store: current lifecycle:', this.lifecycle)
            console.log('Store: is locked:', this.lifecycle.locked)
            // Allow adding sections even when locked, but reset to draft
            if (this.lifecycle.locked) {
                console.log('Store: was locked, resetting to draft')
                this.setLifecycleStatus('draft')
            }
            const newSection = createSection(partial)
            console.log('Store: created section:', newSection)
            this.exam.sections.push(newSection)
            console.log('Store: sections after add:', this.exam.sections)
            this.markDirty()
            console.log('Store: marked dirty, lifecycle now:', this.lifecycle)
        },

        updateSection(sectionId, patch) {
            // Allow updating sections even when locked, but reset to draft
            if (this.lifecycle.locked) {
                this.setLifecycleStatus('draft')
            }
            const idx = this.exam.sections.findIndex(s => s.id === sectionId)
            if (idx === -1) return
            this.exam.sections[idx] = { ...this.exam.sections[idx], ...patch }
            this.markDirty()
        },

        removeSection(sectionId) {
            // Allow removing sections even when locked, but reset to draft
            if (this.lifecycle.locked) {
                this.setLifecycleStatus('draft')
            }
            this.exam.sections = this.exam.sections.filter(s => s.id !== sectionId)
            if (this.selection.sectionId === sectionId) this.selection = { sectionId: null, questionId: null }
            this.markDirty()
        },

        addQuestion(sectionId, partial) {
            // Allow adding questions even when locked, but reset to draft
            if (this.lifecycle.locked) {
                this.setLifecycleStatus('draft')
            }
            const section = this.exam.sections.find(s => s.id === sectionId)
            if (!section) return
            section.questions.push(createQuestion(partial))
            this.markDirty()
        },

        updateQuestion(sectionId, questionId, patch) {
            // Allow updating questions even when locked, but reset to draft
            if (this.lifecycle.locked) {
                this.setLifecycleStatus('draft')
            }
            const section = this.exam.sections.find(s => s.id === sectionId)
            if (!section) return
            const idx = (section.questions ?? []).findIndex(q => q.id === questionId)
            if (idx === -1) return
            section.questions[idx] = { ...section.questions[idx], ...patch }
            this.markDirty()
        },

        removeQuestion(sectionId, questionId) {
            // Allow removing questions even when locked, but reset to draft
            if (this.lifecycle.locked) {
                this.setLifecycleStatus('draft')
            }
            const section = this.exam.sections.find(s => s.id === sectionId)
            if (!section) return
            section.questions = (section.questions ?? []).filter(q => q.id !== questionId)
            if (this.selection.questionId === questionId) this.selection.questionId = null
            this.markDirty()
        },

        setValidationReport({ items = [], summary = null, ranAt = null } = {}) {
            this.validation.items = items
            this.validation.summary = summary ?? {
                errors: items.filter(x => x.severity === 'error').length,
                warnings: items.filter(x => x.severity === 'warn').length,
                infos: items.filter(x => x.severity === 'info').length,
            }
            this.validation.lastRunAt = ranAt ?? new Date().toISOString()
        },

        markValidated() {
            this.setLifecycleStatus('validated')
        },

        approve() {
            if (!this.canApprove) return
            this.setLifecycleStatus('approved')
        },

        setRenderedSnapshot(snapshot) {
            this.renderSnapshot = {
                id: snapshot?.id ?? crypto.randomUUID(),
                createdAt: snapshot?.createdAt ?? new Date().toISOString(),
            }
            this.setLifecycleStatus('rendered')
        },

        loadUserData(questions, settings) {
            // Load user-specific data
            this.exam = { ...settings }
            this.exam.sections = questions || []
            this.lifecycle = { status: 'draft', locked: false, dirty: false }
            this.selection = { sectionId: null, questionId: null }
            this.validation = {
                lastRunAt: null,
                summary: { errors: 0, warnings: 0, infos: 0 },
                items: [],
            }
            this.renderSnapshot = { id: null, createdAt: null }
        },

        markClean() {
            // Mark data as clean (no unsaved changes)
            this.lifecycle.dirty = false
        },
    },

    persist: {
        key: 'examReadyToPrint',
        paths: ['lifecycle', 'exam', 'selection', 'validation', 'renderSnapshot'],
    },
})
