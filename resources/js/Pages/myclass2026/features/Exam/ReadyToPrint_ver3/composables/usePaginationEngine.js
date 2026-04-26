import { computed } from 'vue'

// Height estimation constants (in mm)
const HEIGHTS = {
  sectionTitle: 8,
  sectionInstructions: 5,
  questionPrompt: 6,
  questionGap: 4,
  answerAreaTextLines: 6,
  answerAreaEssay: 40,
  answerAreaMCQOption: 5,
  answerAreaTrueFalse: 6,
  pageBreak: 2,
}

export function usePaginationEngine(exam) {
  const pageSetup = computed(() => exam.value.pageSetup)
  const layoutDefaults = computed(() => exam.value.layoutDefaults)

  // Available content height per page (excluding header/footer)
  const contentHeightPerPage = computed(() => {
    const totalHeight = pageSetup.value.paper === 'A4' ? 297 : 279
    const usedHeight =
      pageSetup.value.marginsMm.top +
      pageSetup.value.marginsMm.bottom +
      pageSetup.value.headerHeightMm +
      pageSetup.value.footerHeightMm
    return totalHeight - usedHeight
  })

  // Estimate height for a block (simplified)
  function estimateBlockHeight(block) {
    switch (block.type) {
      case 'sectionTitle':
        return HEIGHTS.sectionTitle
      case 'sectionInstructions':
        return Math.ceil(block.text.length / 80) * HEIGHTS.sectionInstructions
      case 'question':
        return estimateQuestionHeight(block.question)
      case 'spacer':
        return block.height || 4
      case 'pageBreak':
        return HEIGHTS.pageBreak
      default:
        return 10
    }
  }

  function estimateQuestionHeight(question) {
    let height = HEIGHTS.questionPrompt + HEIGHTS.questionGap
    
    // Prompt height (rough estimate)
    const promptLines = Math.ceil((question.content?.prompt || '').length / 70)
    height += promptLines * HEIGHTS.questionPrompt

    // Answer area height by type
    switch (question.type) {
      case 'text':
        height += 3 * HEIGHTS.answerAreaTextLines
        break
      case 'essay':
        height += HEIGHTS.answerAreaEssay
        break
      case 'mcq':
        const optionCount = question.content?.options?.length || 4
        height += optionCount * HEIGHTS.answerAreaMCQOption
        break
      case 'true_false':
        height += HEIGHTS.answerAreaTrueFalse
        break
      default:
        height += 10
    }

    return height
  }

  // Build render blocks from exam sections/questions
  function buildRenderBlocks() {
    const blocks = []

    for (const section of exam.value.sections) {
      // Section title
      blocks.push({
        type: 'sectionTitle',
        sectionId: section.id,
        text: section.title || 'Untitled Section',
      })

      // Section instructions (if any)
      if (section.instructions) {
        blocks.push({
          type: 'sectionInstructions',
          sectionId: section.id,
          text: section.instructions,
        })
      }

      // Questions
      for (const question of section.questions || []) {
        const forceEssay = !!section?.rules?.forceQuestionsToEssay
        const plannedQuestion = forceEssay ? { ...question, type: 'essay' } : question
        blocks.push({
          type: 'question',
          sectionId: section.id,
          questionId: question.id,
          question: plannedQuestion,
        })
      }
    }

    return blocks
  }

  // Pagination planning with overflow strategy
  function planPagination() {
    const blocks = buildRenderBlocks()
    const pages = []
    let currentPage = {
      blocks: [],
      usedHeight: 0,
    }

    for (const block of blocks) {
      const blockHeight = estimateBlockHeight(block)
      const remainingHeight = contentHeightPerPage.value - currentPage.usedHeight

      // Check if block fits
      if (blockHeight <= remainingHeight) {
        // Fits on current page
        currentPage.blocks.push(block)
        currentPage.usedHeight += blockHeight
      } else {
        // Handle overflow based on strategy
        const strategy = layoutDefaults.value.overflowStrategy

        if (strategy === 'reject_at_validation') {
          throw new Error(`Block too large for remaining page: ${block.type}`)
        }

        if (strategy === 'move_to_next_page' || strategy === 'scale_down') {
          // Start new page (unless it's the first page and block is too large)
          if (currentPage.blocks.length > 0) {
            pages.push(currentPage)
            currentPage = {
              blocks: [],
              usedHeight: 0,
            }
          }

          // Check if block fits on a fresh page
          if (blockHeight <= contentHeightPerPage.value) {
            currentPage.blocks.push(block)
            currentPage.usedHeight = blockHeight
          } else {
            if (strategy === 'scale_down') {
              // For scale_down, we'd need more sophisticated logic
              // For now, treat as move_to_next_page
              throw new Error(`Block too large even for fresh page: ${block.type}`)
            } else {
              throw new Error(`Block too large for any page: ${block.type}`)
            }
          }
        }

        if (strategy === 'split_allowed') {
          // Only allow splitting for certain block types
          if (block.type === 'sectionInstructions' || block.type === 'question') {
            // For now, move to next page (real splitting would be more complex)
            if (currentPage.blocks.length > 0) {
              pages.push(currentPage)
              currentPage = {
                blocks: [],
                usedHeight: 0,
              }
            }
            currentPage.blocks.push(block)
            currentPage.usedHeight = blockHeight
          } else {
            // Non-splittable blocks must move whole
            if (currentPage.blocks.length > 0) {
              pages.push(currentPage)
              currentPage = {
                blocks: [],
                usedHeight: 0,
              }
            }
            currentPage.blocks.push(block)
            currentPage.usedHeight = blockHeight
          }
        }
      }
    }

    // Add last page if it has content
    if (currentPage.blocks.length > 0) {
      pages.push(currentPage)
    }

    return pages
  }

  // Generate render snapshot (deterministic)
  function generateRenderSnapshot() {
    try {
      const pages = planPagination()
      const snapshot = {
        id: crypto.randomUUID(),
        createdAt: new Date().toISOString(),
        pageSetup: pageSetup.value,
        headerConfig: exam.value.headerConfig,
        footerConfig: exam.value.footerConfig,
        examMeta: exam.value.examMeta,
        pages,
        totalBlocks: pages.reduce((sum, page) => sum + page.blocks.length, 0),
      }
      return { success: true, snapshot }
    } catch (error) {
      return { success: false, error: error.message }
    }
  }

  return {
    contentHeightPerPage,
    estimateBlockHeight,
    planPagination,
    generateRenderSnapshot,
  }
}
