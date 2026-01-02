# 🤖 Agent Protocol: Task Completion

**ATTENTION AGENT**: You must strictly follow this protocol when completing any coding task, feature implementation, or bug fix.

## 1. Documentation ( MANDATORY )
Before marking a task as complete, you **MUST** create a history documentation file.

- **Location**: `docs/history/`
- **Filename Format**: `YYYY-MM-DD_HH-MM_feature_name_or_fix.md`
- **Content Requirements**:
  - Title with Timestamp: `# YYYY-MM-DD HH:MM | Title`
  - **Overview**: Brief summary of what was done.
  - **Key Changes**: Bullet points of specific changes (API, Frontend, Database, etc.).
  - **Technical Details**: Explanation of the implementation logic.

## 2. Git Operations ( MANDATORY )
After creating the documentation, you **MUST** push your changes.

1.  **Stage Changes**:
    ```bash
    git add .
    ```

2.  **Commit**:
    - Use the standard detailed format:
    ```bash
    git commit -m "Brief Subject | YYYY-MM-DD HH:MM:SS | <DEVICE_INFO> | Detailed description of changes"
    ```
    - *Note*: Ensure the timestamp is current and device info is accurate (e.g., 'Mac').

3.  **Push**:
    - Push to the current working branch:
    ```bash
    git push origin <branch_name>
    ```

## 3. Final Verification
- Confirm the push was successful.
- Only *then* notify the user that the task is complete.
