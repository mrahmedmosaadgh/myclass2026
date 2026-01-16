/**
 * AI Prompt Templates and Utilities for Lesson Section Template Generation
 */

/**
 * Color palettes for different subjects
 */
export const subjectColorPalettes = {
    mathematics: {
        name: 'Mathematics',
        colors: ['#dbeafe', '#bfdbfe', '#93c5fd', '#60a5fa', '#3b82f6'],
        accent: '#2563eb'
    },
    science: {
        name: 'Science',
        colors: ['#dcfce7', '#bbf7d0', '#86efac', '#4ade80', '#22c55e'],
        accent: '#16a34a'
    },
    language: {
        name: 'Language Arts',
        colors: ['#fce7f3', '#fbcfe8', '#f9a8d4', '#f472b6', '#ec4899'],
        accent: '#db2777'
    },
    social_studies: {
        name: 'Social Studies',
        colors: ['#fed7aa', '#fdba74', '#fb923c', '#f97316', '#ea580c'],
        accent: '#c2410c'
    },
    arts: {
        name: 'Arts',
        colors: ['#e9d5ff', '#d8b4fe', '#c084fc', '#a855f7', '#9333ea'],
        accent: '#7c3aed'
    },
    default: {
        name: 'General',
        colors: ['#f3f4f6', '#e5e7eb', '#d1d5db', '#9ca3af', '#6b7280'],
        accent: '#4b5563'
    }
};

/**
 * Quick suggestion presets
 */
export const quickSuggestions = [
    {
        label: 'Elementary Science',
        subject: 'Science',
        grade: 'Elementary (K-5)',
        sections: 5,
        instructions: 'Focus on hands-on activities and exploration'
    },
    {
        label: 'High School Math',
        subject: 'Mathematics',
        grade: 'High School (9-12)',
        sections: 6,
        instructions: 'Include problem-solving and real-world applications'
    },
    {
        label: 'Middle School Language Arts',
        subject: 'Language Arts',
        grade: 'Middle School (6-8)',
        sections: 5,
        instructions: 'Balance reading, writing, and discussion activities'
    },
    {
        label: 'Elementary General',
        subject: 'General',
        grade: 'Elementary (K-5)',
        sections: 4,
        instructions: 'Keep it simple and engaging for young learners'
    }
];

/**
 * Generate AI prompt for lesson section template
 */
export function generateTemplatePrompt(config) {
    const { subject, grade, sectionCount, customInstructions } = config;

    return `You are an expert educational curriculum designer. Generate a lesson plan section template with the following specifications:

**Requirements:**
- Subject: ${subject}
- Grade Level: ${grade}
- Number of Sections: ${sectionCount}
${customInstructions ? `- Additional Instructions: ${customInstructions}` : ''}

**Instructions:**
1. Create pedagogically sound section names appropriate for the grade level
2. Use relevant emojis that match each section's educational purpose
3. Assign meaningful colors to each section (use hex color codes)
4. Include both emoji icons and Quasar icon names (from Material Design Icons)
5. Use age-appropriate language and terminology

**Required JSON Format:**
Return ONLY a valid JSON object (no markdown, no code blocks) with this exact structure:

{
  "name": "Descriptive template name (e.g., 'Grade 5 Math Problem-Solving Template')",
  "sections": [
    {
      "id": "unique_lowercase_id",
      "title": "Section Title",
      "icon": "📝",
      "qIcon": "article",
      "bg": "#f3f4f6",
      "bgActive": "#e5e7eb",
      "borderColor": "#9ca3af",
      "textColor": "#1f2937"
    }
  ]
}

**Section Guidelines:**
- Common sections might include: Objectives, Warm-up, Introduction, Main Activity, Practice, Assessment, Closure, Homework, etc.
- Each section should have a unique ID (lowercase, underscores for spaces)
- Use complementary colors that are visually distinct
- Background colors should be light/pastel for readability
- Active backgrounds should be slightly darker than regular backgrounds
- Text colors should provide good contrast with backgrounds

**Example Quasar Icons:** flag, lightbulb, school, psychology, edit, quiz, check_circle, home, star, etc.

Please generate the template now.`;
}

/**
 * Validate AI response structure
 */
export function validateTemplateResponse(response) {
    const errors = [];

    // Check if response is an object
    if (!response || typeof response !== 'object') {
        errors.push('Response must be a valid JSON object');
        return { valid: false, errors };
    }

    // Check required fields
    if (!response.name || typeof response.name !== 'string') {
        errors.push('Missing or invalid "name" field (must be a string)');
    }

    if (!response.sections || !Array.isArray(response.sections)) {
        errors.push('Missing or invalid "sections" field (must be an array)');
        return { valid: false, errors };
    }

    if (response.sections.length === 0) {
        errors.push('Sections array must contain at least one section');
    }

    // Validate each section
    response.sections.forEach((section, index) => {
        const sectionErrors = [];

        if (!section.id || typeof section.id !== 'string') {
            sectionErrors.push('Missing or invalid "id"');
        }

        if (!section.title || typeof section.title !== 'string') {
            sectionErrors.push('Missing or invalid "title"');
        }

        if (!section.icon || typeof section.icon !== 'string') {
            sectionErrors.push('Missing or invalid "icon"');
        }

        if (!section.qIcon || typeof section.qIcon !== 'string') {
            sectionErrors.push('Missing or invalid "qIcon"');
        }

        // Validate color fields
        const colorFields = ['bg', 'bgActive', 'borderColor', 'textColor'];
        colorFields.forEach(field => {
            if (!section[field] || typeof section[field] !== 'string') {
                sectionErrors.push(`Missing or invalid "${field}"`);
            } else if (!/^#[0-9A-Fa-f]{6}$/.test(section[field])) {
                sectionErrors.push(`Invalid hex color format for "${field}"`);
            }
        });

        if (sectionErrors.length > 0) {
            errors.push(`Section ${index + 1}: ${sectionErrors.join(', ')}`);
        }
    });

    return {
        valid: errors.length === 0,
        errors
    };
}

/**
 * Parse AI response (handles both plain JSON and markdown code blocks)
 */
export function parseAIResponse(responseText) {
    try {
        // First, try to extract JSON from markdown code blocks
        const codeBlockMatch = responseText.match(/```(?:json)?\s*\n?([\s\S]*?)\n?```/);
        const jsonText = codeBlockMatch ? codeBlockMatch[1] : responseText;

        // Parse the JSON
        const parsed = JSON.parse(jsonText.trim());
        return { success: true, data: parsed };
    } catch (error) {
        return {
            success: false,
            error: `Failed to parse JSON: ${error.message}. Please ensure the response is valid JSON.`
        };
    }
}

/**
 * Get subject-specific icon suggestions
 */
export function getSubjectIcons(subject) {
    const iconMap = {
        mathematics: ['calculate', 'functions', 'percent', 'straighten', 'timeline'],
        science: ['science', 'biotech', 'psychology', 'eco', 'local_fire_department'],
        language: ['book', 'edit', 'spellcheck', 'record_voice_over', 'article'],
        social_studies: ['public', 'history_edu', 'account_balance', 'map', 'groups'],
        arts: ['palette', 'music_note', 'theater_comedy', 'brush', 'color_lens'],
        default: ['school', 'menu_book', 'lightbulb', 'assignment', 'check_circle']
    };

    return iconMap[subject.toLowerCase()] || iconMap.default;
}
