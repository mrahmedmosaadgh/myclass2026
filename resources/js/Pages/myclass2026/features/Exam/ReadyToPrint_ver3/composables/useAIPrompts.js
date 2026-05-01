import { ref } from 'vue'

export function useAIPrompts() {
  const generatedPrompt = ref('')

  /**
   * Generate a general AI prompt with all settings, validation, and instructions
   * @param {Object} pageOptions - Current page options/settings
   * @param {Array} sampleQuestions - Current questions
   * @param {Function} detectQuestionErrors - Function to detect question errors
   * @returns {string} The generated AI prompt
   */
  function generateGeneralAIPrompt(pageOptions, sampleQuestions, detectQuestionErrors) {
    // Validate questions first
    const errors = detectQuestionErrors ? detectQuestionErrors() : []

    let prompt = `Generate or improve exam questions based on the following requirements and current settings.\n\n`

    prompt += `=== CURRENT SETTINGS ===\n`
    prompt += `Exam Title: ${pageOptions.value?.examTitle?.text || 'Not set'}\n`
    prompt += `Show Marks Per Question: ${pageOptions.value?.showMarksPerQuestion ? 'Yes' : 'No'}\n`
    prompt += `Pagination Mode: ${pageOptions.value?.paginationMode || 'Not set'}\n`
    prompt += `Question Separator: ${pageOptions.value?.questionSeparator?.enabled ? 'Enabled' : 'Disabled'}\n`
    if (pageOptions.value?.questionSeparator?.enabled) {
      prompt += `  - Line Style: ${pageOptions.value.questionSeparator.lineStyle}\n`
      prompt += `  - Color: ${pageOptions.value.questionSeparator.color}\n`
      prompt += `  - Thickness: ${pageOptions.value.questionSeparator.thicknessPt}pt\n`
    }
    prompt += `MCQ Columns: ${pageOptions.value?.mcqOptions?.columns || 1}\n`
    prompt += `MCQ Option Gap: ${pageOptions.value?.mcqOptions?.optionGapPt || 6}pt\n`
    prompt += `MCQ Label Style: ${pageOptions.value?.mcqOptions?.labelStyle || 'letter'}\n`

    prompt += `\n=== CURRENT QUESTIONS ===\n`
    if (!sampleQuestions || sampleQuestions.value.length === 0) {
      prompt += `No questions yet. Generate new questions.\n`
    } else {
      prompt += `Current questions (${sampleQuestions.value.length}):\n`
      prompt += JSON.stringify(sampleQuestions.value, null, 2)
    }

    prompt += `\n=== DETECTED ISSUES ===\n`
    if (errors.length === 0) {
      prompt += `No issues detected in current questions.\n`
    } else {
      prompt += `Found ${errors.length} issue(s):\n`
      errors.forEach((error, index) => {
        prompt += `${index + 1}. Question ${error.questionId}: ${error.message}\n`
      })
    }

    prompt += `\n=== REQUIREMENTS ===\n`
    prompt += `- Use LaTeX notation for ALL math expressions: $x^2$ for inline, $$\\frac{a}{b}$$ for display\n`
    prompt += `- You can use basic HTML tags: <strong>, <em>, <u>, <sup>, <sub>\n`
    prompt += `- Each question must have "ver": 3 field\n`
    prompt += `- Multiple choice questions must have "correct_option_index" (0-based index)\n`
    prompt += `- Ensure only ONE correct answer per multiple choice question\n`
    prompt += `- Marks should be between 1-5 points based on difficulty\n`

    prompt += `\n=== VALIDATION CHECKLIST ===\n`
    prompt += `Before generating, please check:\n`
    prompt += `- Are there any duplicate correct answers in multiple choice questions?\n`
    prompt += `- Are all math expressions properly formatted with LaTeX?\n`
    prompt += `- Are all required fields present (id, type, ver, marks, content)?\n`
    prompt += `- Is the correct_option_index valid (within options array range)?\n`

    prompt += `\n=== INSTRUCTIONS ===\n`
    prompt += `1. If information is missing (e.g., topic, grade level, number of questions), ask me for it before generating.\n`
    prompt += `2. If you find issues in current questions, provide corrected versions.\n`
    prompt += `3. Return ONLY valid JSON format with the questions array.\n`
    prompt += `4. Do NOT include citations or source markers like [cite: 219] anywhere in the text.\n`

    prompt += `\n=== JSON FORMAT EXAMPLE ===\n`
    prompt += `\`\`\`json\n`
    prompt += `[\n`
    prompt += `  {\n`
    prompt += `    "id": 1,\n`
    prompt += `    "type": "multiple_choice",\n`
    prompt += `    "ver": 3,\n`
    prompt += `    "marks": 3,\n`
    prompt += `    "content": {\n`
    prompt += `      "prompt": "Solve for x: $x^2 + 2x - 8 = 0$",\n`
    prompt += `      "options": ["x = 2", "x = -4", "x = 2 or x = -4", "x = 4"],\n`
    prompt += `      "correct_option_index": 2\n`
    prompt += `    }\n`
    prompt += `  }\n`
    prompt += `]\n`
    prompt += `\`\`\``

    generatedPrompt.value = prompt
    return prompt
  }

  /**
   * Generate a prompt for creating questions based on configuration
   * @param {Object} config - AI configuration
   * @returns {string} The generated prompt
   */
  function generatePrompt(config) {
    const { topic, grade, questionCount, latexSupport, htmlSupport, instructions } = config

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

    generatedPrompt.value = prompt
    return prompt
  }

  /**
   * Copy the generated prompt to clipboard
   */
  async function copyPrompt() {
    try {
      await navigator.clipboard.writeText(generatedPrompt.value)
      return true
    } catch (err) {
      console.error('Failed to copy prompt:', err)
      return false
    }
  }

  return {
    generatedPrompt,
    generateGeneralAIPrompt,
    generatePrompt,
    copyPrompt
  }
}
