/**
 * Table of Contents Prompt Templates
 * Generates prompts for AI to parse raw text into lesson lists
 */

export function generateToCPrompt(rawText, bookInfo) {
    const { name, grade, subject } = bookInfo;

    return `You are an expert data assistant. Your task is to extract a Table of Contents (ToC) from raw text provided by a user.
    
    BOOK CONTEXT:
    - Name: ${name}
    - Grade: ${grade}
    - Subject: ${subject}
    
    RAW TEXT TO PARSE:
    ---
    ${rawText}
    ---
    
    EXTRACT REQUIREMENTS:
    1. Identify all Lessons or Topics.
    2. For each lesson, extract:
       - Lesson Number (e.g., "1-1", "Lesson 1", "Unit 1")
       - Lesson Title (The descriptive name of the lesson)
       - Page Number (If available, otherwise use null)
       - Type (One of: "main", "revision", "quiz", "project", "extra")
    
    OUTPUT FORMAT:
    Return a JSON object with this EXACT structure (no markdown, no code blocks):
    
    {
      "lessons": [
        {
          "lesson_number": "1-1",
          "lesson_title": "Understanding Fractions",
          "page_number": 5,
          "type": "main"
        },
        ...
      ]
    }
    
    IMPORTANT RULES:
    1. Return ONLY valid JSON.
    2. NO markdown code blocks (\`\`\`json).
    3. NO extra text before or after the JSON.
    4. If the raw text is messy, use your best judgment to clean titles and identify numbers.
    5. Order the lessons in the order they appear in the text.
    
    Cleanly extract the lessons now.`;
}

export function validateToCResponse(data) {
    const errors = [];

    if (!data.lessons || !Array.isArray(data.lessons)) {
        errors.push('Response must have a "lessons" array');
        return { valid: false, errors };
    }

    data.lessons.forEach((lesson, idx) => {
        if (!lesson.lesson_number) {
            errors.push(`Lesson ${idx + 1}: Missing "lesson_number"`);
        }
        if (!lesson.lesson_title) {
            errors.push(`Lesson ${idx + 1}: Missing "lesson_title"`);
        }
    });

    return {
        valid: errors.length === 0,
        errors
    };
}

export function parseToCResponse(text) {
    try {
        let cleaned = text.trim();
        
        // Remove markdown code blocks
        if (cleaned.startsWith('```json')) {
            cleaned = cleaned.replace(/```json\n?/g, '').replace(/```\n?$/g, '');
        } else if (cleaned.startsWith('```')) {
            cleaned = cleaned.replace(/```\n?/g, '');
        }

        // Find JSON object
        const jsonMatch = cleaned.match(/\{[\s\S]*\}/);
        if (jsonMatch) {
            cleaned = jsonMatch[0];
        }

        const data = JSON.parse(cleaned);
        return { success: true, data };
    } catch (error) {
        return {
            success: false,
            error: `JSON Parse Error: ${error.message}`
        };
    }
}
