# 2026-02-08: AI Question Generator Prompt Update

## Summary
Updated the AI Question Generator prompt to explicitly include the `custom_group` field in the JSON structure instructions. This ensures that the AI model understands the schema requirements and populates the `custom_group` field (defaulting to the Exam Title) directly in the generated response, improving the reliability of the bulk import process.

## Completed Tasks

### Frontend
-   **AI Prompt Enhancement**: Modified `generatePrompt` in `QuQuestionAIGeneratorDialog.vue` to:
    -   Include `custom_group` in the example JSON object.
    -   Add an explicit rule instruction: `custom_group is optional: "${config.value.customTopic || ''}"`.

## Next Steps
-   Monitor the AI's adherence to the new schema rule.
-   Gather user feedback on the "Group" functionality in exams.
