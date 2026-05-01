/**
 * Export utilities for different exam formats
 * Supports JSON, Word (.docx), and QTI (IMS Question & Test Interoperability)
 */

import { Document, Packer, Paragraph, TextRun, HeadingLevel, AlignmentType, Table, TableCell, TableRow, WidthType, BorderStyle } from 'docx'

/**
 * Convert LaTeX math to readable plain text for Word export
 * This converts LaTeX patterns to Unicode characters and readable formats
 */
function latexToPlainText(text) {
  if (!text) return ''

  let result = String(text)

  // Remove \text{} commands and keep the content
  result = result.replace(/\\text\{([^}]+)\}/g, '$1')

  // Convert fractions \frac{a}{b} to a/b
  result = result.replace(/\\frac\{([^}]+)\}\{([^}]+)\}/g, '($1)/($2)')

  // Convert common math symbols
  result = result.replace(/\\times/g, '×')
  result = result.replace(/\\div/g, '÷')
  result = result.replace(/\\pm/g, '±')
  result = result.replace(/\\neq/g, '≠')
  result = result.replace(/\\leq/g, '≤')
  result = result.replace(/\\geq/g, '≥')
  result = result.replace(/\\approx/g, '≈')
  result = result.replace(/\\infty/g, '∞')
  result = result.replace(/\\pi/g, 'π')
  result = result.replace(/\\theta/g, 'θ')
  result = result.replace(/\\alpha/g, 'α')
  result = result.replace(/\\beta/g, 'β')
  result = result.replace(/\\gamma/g, 'γ')
  result = result.replace(/\\delta/g, 'δ')
  result = result.replace(/\\sum/g, 'Σ')
  result = result.replace(/\\prod/g, '∏')
  result = result.replace(/\\sqrt\{([^}]+)\}/g, '√($1)')

  // Convert superscripts ^{...}
  result = result.replace(/\^\{([^}]+)\}/g, '^$1')
  result = result.replace(/\^([0-9a-zA-Z])/g, '^$1')

  // Convert subscripts _{...}
  result = result.replace(/_\{([^}]+)\}/g, '_$1')
  result = result.replace(/_([0-9a-zA-Z])/g, '_$1')

  // Remove remaining LaTeX commands (simple approach)
  result = result.replace(/\\[a-zA-Z]+/g, '')

  // Clean up remaining braces
  result = result.replace(/[{}]/g, '')

  return result
}

/**
 * Process text by extracting and converting LaTeX math
 */
function processLatexForWord(text) {
  if (!text) return ''

  let result = String(text)

  // Process inline math $...$
  result = result.replace(/\$([^$]+)\$/g, (match, math) => {
    return latexToPlainText(math)
  })

  // Process display math $$...$$
  result = result.replace(/\$\$([^$]+)\$\$/g, (match, math) => {
    return latexToPlainText(math)
  })

  return result
}

/**
 * Generate filename for export
 */
export function generateExportFileName(suffix, pageOptions = {}) {
  const examTitle = pageOptions?.examTitle?.enabled ? pageOptions.examTitle.text : ''
  const subject = pageOptions?.printHeader?.template1?.subject || ''
  const grade = pageOptions?.printHeader?.template1?.grade || ''
  const date = new Date().toISOString().split('T')[0]

  const parts = []
  if (examTitle) parts.push(examTitle)
  if (subject) parts.push(subject)
  if (grade) parts.push(grade)
  parts.push(date)
  parts.push(suffix)

  return parts
    .join(' - ')
    .replace(/[<>:"/\\|?*]/g, '')
    .substring(0, 200)
}

/**
 * Export to Word (.docx) format
 */
export async function exportToWord(data) {
  try {
    const { questions, settings, sections, questionSectionMap } = data

    const children = []

    // Add exam title
    if (settings?.examTitle?.enabled && settings.examTitle.text) {
      children.push(
        new Paragraph({
          text: settings.examTitle.text,
          heading: HeadingLevel.HEADING_1,
          alignment: AlignmentType.CENTER,
          spacing: { after: 200 }
        })
      )
    }

    // Add header information
    if (settings?.printHeader?.mode === 'template1') {
      const template1 = settings.printHeader.template1
      const headerLines = []
      if (template1.schoolName) headerLines.push(template1.schoolName)
      if (template1.subject) headerLines.push(template1.subject)
      if (template1.grade) headerLines.push(template1.grade)
      if (template1.date) headerLines.push(template1.date)

      headerLines.forEach(line => {
        children.push(
          new Paragraph({
            text: line,
            alignment: AlignmentType.CENTER,
            spacing: { after: 100 }
          })
        )
      })
    }

    // Group questions by section
    const sectionQuestions = {}
    const defaultSectionId = sections?.[0]?.id || 'default'

    // Initialize section groups
    sections?.forEach(section => {
      sectionQuestions[section.id] = {
        section,
        questions: []
      }
    })

    // Add default section if none exist
    if (!sections || sections.length === 0) {
      sectionQuestions[defaultSectionId] = {
        section: { title: 'Questions', description: '' },
        questions: []
      }
    }

    // Map questions to sections
    questions?.forEach(question => {
      const sectionId = questionSectionMap?.[question.id] || defaultSectionId
      if (!sectionQuestions[sectionId]) {
        sectionQuestions[sectionId] = {
          section: { title: 'Section', description: '' },
          questions: []
        }
      }
      sectionQuestions[sectionId].questions.push(question)
    })

    // Generate content for each section
    Object.values(sectionQuestions).forEach(({ section, questions: sectionQs }) => {
      if (section.title) {
        children.push(
          new Paragraph({
            text: section.title,
            heading: HeadingLevel.HEADING_2,
            spacing: { before: 300, after: 200 }
          })
        )
      }

      if (section.description) {
        children.push(
          new Paragraph({
            text: section.description,
            spacing: { after: 200 }
          })
        )
      }

      sectionQs.forEach((q, idx) => {
        // Process LaTeX in prompt
        const processedPrompt = processLatexForWord(q.content?.prompt || '')
        const questionText = `${idx + 1}. ${processedPrompt}`
        const marks = q.marks ? ` (${q.marks} marks)` : ''

        children.push(
          new Paragraph({
            children: [
              new TextRun({
                text: questionText,
                bold: true
              }),
              new TextRun(marks)
            ],
            spacing: { after: 100 }
          })
        )

        // Add options for MCQ
        if (q.type === 'multiple_choice' && q.content?.options) {
          const optionLabels = ['A', 'B', 'C', 'D', 'E', 'F']
          q.content.options.forEach((opt, optIdx) => {
            // Process LaTeX in options
            const processedOpt = processLatexForWord(opt)
            children.push(
              new Paragraph({
                text: `   ${optionLabels[optIdx] || optIdx + 1}) ${processedOpt}`,
                indent: { left: 720 },
                spacing: { after: 50 }
              })
            )
          })
        }

        // Add options for True/False
        if (q.type === 'true_false' && q.content?.options) {
          q.content.options.forEach((opt, optIdx) => {
            // Process LaTeX in options
            const processedOpt = processLatexForWord(opt)
            children.push(
              new Paragraph({
                text: `   ${optIdx === 0 ? 'True' : 'False'}) ${processedOpt}`,
                indent: { left: 720 },
                spacing: { after: 50 }
              })
            )
          })
        }

        children.push(new Paragraph({ text: '', spacing: { after: 200 } }))
      })
    })

    const doc = new Document({
      sections: [{
        children,
        properties: {
          page: {
            margin: {
              top: 1440,
              right: 1440,
              bottom: 1440,
              left: 1440
            }
          }
        }
      }]
    })

    const blob = await Packer.toBlob(doc)
    return blob
  } catch (error) {
    console.error('Word export error:', error)
    throw new Error('Failed to export to Word: ' + error.message)
  }
}

/**
 * Escape XML special characters for QTI
 */
function escapeXml(str) {
  return String(str ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&apos;')
}

/**
 * Export to QTI (IMS Question & Test Interoperability) format
 */
export function exportToQTI(data) {
  try {
    const { questions, settings, sections, questionSectionMap } = data

    const now = new Date().toISOString()
    const examTitle = settings?.examTitle?.enabled ? settings.examTitle.text : 'Exam'

    let xml = `<?xml version="1.0" encoding="UTF-8"?>
<questestinterop xmlns="http://www.imsglobal.org/xsd/ims_qtiasiv1p2" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.imsglobal.org/xsd/ims_qtiasiv1p2 http://www.imsglobal.org/xsd/ims_qtiasiv1p2p1.xsd">
  <assessment ident="exam_${Date.now()}" title="${escapeXml(examTitle)}">
    <qtimetadata>
      <qtimetadatafield>
        <fieldlabel>exported_at</fieldlabel>
        <fieldentry>${escapeXml(now)}</fieldentry>
      </qtimetadatafield>
    </qtimetadata>
    <section ident="root_section">
`

    // Group questions by section
    const sectionQuestions = {}
    const defaultSectionId = sections?.[0]?.id || 'default'

    sections?.forEach(section => {
      sectionQuestions[section.id] = {
        section,
        questions: []
      }
    })

    if (!sections || sections.length === 0) {
      sectionQuestions[defaultSectionId] = {
        section: { title: 'Questions' },
        questions: []
      }
    }

    questions?.forEach(question => {
      const sectionId = questionSectionMap?.[question.id] || defaultSectionId
      if (!sectionQuestions[sectionId]) {
        sectionQuestions[sectionId] = {
          section: { title: 'Section' },
          questions: []
        }
      }
      sectionQuestions[sectionId].questions.push(question)
    })

    Object.values(sectionQuestions).forEach(({ section, questions: sectionQs }) => {
      xml += `      <section ident="${escapeXml(section.id || 'section')}" title="${escapeXml(section.title || 'Section')}">
`
      sectionQs.forEach((q, idx) => {
        const questionId = `q_${q.id || idx}`
        const prompt = q.content?.prompt || ''

        xml += `        <item ident="${escapeXml(questionId)}" title="Question ${idx + 1}">
          <itemmetadata>
            <qtimetadata>
              <qtimetadatafield>
                <fieldlabel>type</fieldlabel>
                <fieldentry>${escapeXml(q.type || 'short_answer')}</fieldentry>
              </qtimetadatafield>
              <qtimetadatafield>
                <fieldlabel>marks</fieldlabel>
                <fieldentry>${escapeXml(String(q.marks || 1))}</fieldentry>
              </qtimetadatafield>
            </qtimetadata>
          </itemmetadata>
          <presentation>
            <material>
              <mattext texttype="text/plain">${escapeXml(prompt)}</mattext>
            </material>
`

        if (q.type === 'multiple_choice' && q.content?.options) {
          xml += `            <response_lid ident="response_${questionId}" rcardinality="Single">
              <render_choice>
`
          q.content.options.forEach((opt, optIdx) => {
            const isCorrect = optIdx === q.content.correct_option_index
            xml += `                <response_label ident="${escapeXml(`opt_${optIdx}`)}">
                  <material>
                    <mattext texttype="text/plain">${escapeXml(opt)}</mattext>
                  </material>
                </response_label>
`
          })
          xml += `              </render_choice>
              <resprocessing>
                <outcomes>
                  <decvar varname="SCORE" vartype="Decimal" defaultval="0"/>
                </outcomes>
                <respcondition>
                  <conditionvar>
`
          const correctOpt = q.content.correct_option_index
          xml += `                    <varequal respident="response_${questionId}">${escapeXml(`opt_${correctOpt}`)}</varequal>
`
          xml += `                  </conditionvar>
                  <setvar varname="SCORE" action="Set">${escapeXml(String(q.marks || 1))}</setvar>
                </respcondition>
              </resprocessing>
            </response_lid>
`
        } else if (q.type === 'true_false' && q.content?.options) {
          xml += `            <response_lid ident="response_${questionId}" rcardinality="Single">
              <render_choice>
                <response_label ident="true">
                  <material>
                    <mattext texttype="text/plain">True</mattext>
                  </material>
                </response_label>
                <response_label ident="false">
                  <material>
                    <mattext texttype="text/plain">False</mattext>
                  </material>
                </response_label>
              </render_choice>
              <resprocessing>
                <outcomes>
                  <decvar varname="SCORE" vartype="Decimal" defaultval="0"/>
                </outcomes>
                <respcondition>
                  <conditionvar>
                    <varequal respident="response_${questionId}">${escapeXml(q.content.correct_option_index === 0 ? 'true' : 'false')}</varequal>
                  </conditionvar>
                  <setvar varname="SCORE" action="Set">${escapeXml(String(q.marks || 1))}</setvar>
                </respcondition>
              </resprocessing>
            </response_lid>
`
        } else {
          // Short answer / essay
          xml += `            <response_str ident="response_${questionId}" rcardinality="Single">
              <render_fib fibtype="String" prompt="Dash">
                <response_label ident="answer_${questionId}"/>
              </render_fib>
            </response_str>
`
        }

        xml += `          </presentation>
        </item>
`
      })
      xml += `      </section>
`
    })

    xml += `    </section>
  </assessment>
</questestinterop>`

    return new Blob([xml], { type: 'application/xml' })
  } catch (error) {
    console.error('QTI export error:', error)
    throw new Error('Failed to export to QTI: ' + error.message)
  }
}

export default {
  exportToWord,
  exportToQTI,
  generateExportFileName
}
