/**
 * Lesson Plan Prompt Templates
 * Generates prompts for AI to create complete lesson plans with slides
 */

export function generateLessonPlanPrompt(config) {
    const { lessonTitle, subject, grade, sections, customInstructions } = config;

    const sectionsList = sections.map(s => `- ${s.title} (${s.id})`).join('\n');

    // Determine common question types based on subject
    const getSubjectQuestionGuidance = (subject) => {
        const subjectLower = subject.toLowerCase();

        if (subjectLower.includes('math') || subjectLower.includes('رياضيات')) {
            return `For Mathematics:
- Use single_choice for concept understanding
- Use true_false for properties and theorems
- Include step-by-step problem solving in text slides
- Add practice problems with multiple choice answers`;
        } else if (subjectLower.includes('science') || subjectLower.includes('علوم')) {
            return `For Science:
- Use single_choice for facts and concepts
- Use true_false for scientific statements
- Include diagrams descriptions in text slides
- Add experiment-based questions`;
        } else if (subjectLower.includes('language') || subjectLower.includes('لغة')) {
            return `For Language Arts:
- Use single_choice for grammar and vocabulary
- Use true_false for comprehension checks
- Include reading passages in text slides
- Add questions about text analysis`;
        } else {
            return `General guidance:
- Use single_choice for concept testing
- Use true_false for fact checking
- Mix question types for engagement`;
        }
    };

    return `You are an expert educational content designer specializing in ${subject} for ${grade} students.

Create a comprehensive lesson plan for: "${lessonTitle}"

LESSON STRUCTURE:
The lesson has the following sections:
${sectionsList}

REQUIREMENTS:
1. Generate 3-5 slides for EACH section
2. Each slide should have educational content appropriate for ${grade} level
3. Content should be engaging, clear, and pedagogically sound
4. Include variety: text slides, and question slides
5. For practice/assessment sections, include MORE question slides

${getSubjectQuestionGuidance(subject)}

${customInstructions ? `CUSTOM INSTRUCTIONS:\n${customInstructions}\n` : ''}

OUTPUT FORMAT:
Return a JSON object with this EXACT structure (no markdown, no code blocks):

{
  "sections": [
    {
      "sectionId": "objectives",
      "slides": [
        {
          "slide_type": "text",
          "slide_content": {
            "text": "<h2>Learning Objectives</h2><ul><li>Objective 1</li><li>Objective 2</li></ul>"
          }
        }
      ]
    },
    {
      "sectionId": "practice",
      "slides": [
        {
          "slide_type": "question",
          "slide_content": {
            "questions": [
              {
                "id": "q_abc123",
                "type": "single_choice",
                "text": "What is 2 + 2?",
                "options": [
                  {"id": "opt_1", "text": "3"},
                  {"id": "opt_2", "text": "4"},
                  {"id": "opt_3", "text": "5"}
                ],
                "correct_answer": "opt_2",
                "explanation": "2 + 2 equals 4",
                "timer": 30
              }
            ]
          }
        }
      ]
    }
  ]
}

SLIDE TYPES:
1. TEXT SLIDES ("slide_type": "text"):
   - Use for explanations, examples, and content delivery
   - Content should be HTML formatted (use <h2>, <h3>, <p>, <ul>, <li>, <strong>, <em>)
   - Keep content concise but informative

2. QUESTION SLIDES ("slide_type": "question"):
   - Use for practice, assessment, and engagement
   - Each question slide can have 1-3 questions in the "questions" array
   
   QUESTION TYPES:
   
   a) SINGLE CHOICE (most common):
   {
     "id": "q_unique_id",
     "type": "single_choice",
     "text": "Question text here",
     "options": [
       {"id": "opt_1", "text": "Option A"},
       {"id": "opt_2", "text": "Option B"},
       {"id": "opt_3", "text": "Option C"}
     ],
     "correct_answer": "opt_2",
     "explanation": "Why this is correct",
     "timer": 30
   }
   
   b) TRUE/FALSE:
   {
     "id": "q_unique_id",
     "type": "true_false",
     "text": "Statement to evaluate",
     "correct_answer": true,
     "explanation": "Explanation here",
     "timer": 20
   }
   
   c) MULTIPLE CHOICE (select all that apply):
   {
     "id": "q_unique_id",
     "type": "multiple_choice",
     "text": "Select all correct answers",
     "options": [
       {"id": "opt_1", "text": "Option A"},
       {"id": "opt_2", "text": "Option B"},
       {"id": "opt_3", "text": "Option C"}
     ],
     "correct_answer": ["opt_1", "opt_3"],
     "explanation": "Why these are correct",
     "timer": 45
   }

SECTION GUIDELINES:
- objectives: Use text slides to list learning goals
- learn/content: Mix text slides (70%) and question slides (30%) for engagement
- practice: Use MORE question slides (70%) and some text slides (30%)
- assessment/review: Primarily question slides (80-90%)

IMPORTANT RULES:
1. Return ONLY valid JSON
2. NO markdown code blocks (\`\`\`json)
3. NO extra text before or after the JSON
4. Each section MUST have at least 3 slides
5. HTML content must be properly escaped in JSON strings
6. Use double quotes for JSON properties and strings
7. Each question MUST have a unique "id" (use format: q_abc123, q_xyz789, etc.)
8. Each option MUST have a unique "id" within its question (opt_1, opt_2, etc.)
9. For single_choice: correct_answer is a STRING (the option id)
10. For multiple_choice: correct_answer is an ARRAY of strings (option ids)
11. For true_false: correct_answer is a BOOLEAN (true or false)
12. Timer is in seconds (20-60 seconds recommended)

Generate the complete lesson plan now with a good mix of text and question slides.`;
}

export function validateLessonPlanResponse(data) {
    const errors = [];

    if (!data.sections || !Array.isArray(data.sections)) {
        errors.push('Response must have a "sections" array');
        return { valid: false, errors };
    }

    if (data.sections.length === 0) {
        errors.push('Must have at least one section');
        return { valid: false, errors };
    }

    data.sections.forEach((section, idx) => {
        if (!section.sectionId || typeof section.sectionId !== 'string') {
            errors.push(`Section ${idx + 1}: Missing or invalid "sectionId"`);
        }

        if (!section.slides || !Array.isArray(section.slides)) {
            errors.push(`Section ${idx + 1}: Missing or invalid "slides" array`);
        } else if (section.slides.length < 3) {
            errors.push(`Section ${idx + 1}: Must have at least 3 slides (has ${section.slides.length})`);
        } else {
            section.slides.forEach((slide, slideIdx) => {
                if (!slide.slide_type) {
                    errors.push(`Section ${idx + 1}, Slide ${slideIdx + 1}: Missing "slide_type"`);
                }

                if (!slide.slide_content) {
                    errors.push(`Section ${idx + 1}, Slide ${slideIdx + 1}: Missing "slide_content"`);
                } else if (slide.slide_type === 'text' && !slide.slide_content.text) {
                    errors.push(`Section ${idx + 1}, Slide ${slideIdx + 1}: Text slide must have "text" content`);
                } else if (slide.slide_type === 'question') {
                    // Validate question slides
                    if (!slide.slide_content.questions || !Array.isArray(slide.slide_content.questions)) {
                        errors.push(`Section ${idx + 1}, Slide ${slideIdx + 1}: Question slide must have "questions" array`);
                    } else if (slide.slide_content.questions.length === 0) {
                        errors.push(`Section ${idx + 1}, Slide ${slideIdx + 1}: Question slide must have at least 1 question`);
                    }
                }
            });
        }
    });

    return {
        valid: errors.length === 0,
        errors
    };
}

export function parseAIResponse(text) {
    try {
        // Remove markdown code blocks if present
        let cleaned = text.trim();

        // Remove ```json and``` markers
        if (cleaned.startsWith('```json')) {
            cleaned = cleaned.replace(/```json\n?/g, '').replace(/```\n?$/g, '');
        } else if (cleaned.startsWith('```')) {
            cleaned = cleaned.replace(/```\n?/g, '');
        }

        // Try to find JSON object if there's extra text
        const jsonMatch = cleaned.match(/\{[\s\S]*\}/);
        if (jsonMatch) {
            cleaned = jsonMatch[0];
        }

        const data = JSON.parse(cleaned);
        return { success: true, data };
    } catch (error) {
        return {
            success: false,
            error: `JSON Parse Error: ${error.message}. Please ensure the AI returned valid JSON.`
        };
    }
}

export const lessonPlanQuickSuggestions = [
    {
        label: 'Interactive Lesson',
        instructions: 'Include interactive elements, questions for students, and hands-on activities in each section.'
    },
    {
        label: 'Visual Learning',
        instructions: 'Focus on visual descriptions, diagrams explanations, and visual metaphors to explain concepts.'
    },
    {
        label: 'Step-by-Step',
        instructions: 'Break down complex topics into simple, sequential steps. Use numbered lists and clear progression.'
    },
    {
        label: 'Real-World Examples',
        instructions: 'Include real-world applications and practical examples that students can relate to.'
    },
    {
        label: 'Differentiated',
        instructions: 'Provide content at multiple difficulty levels to accommodate different learning abilities.'
    }
];
