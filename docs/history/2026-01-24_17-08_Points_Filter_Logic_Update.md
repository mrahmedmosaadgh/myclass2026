# 2026-01-24 17:08 | Points Filter Logic Update

**Detailed description of changes:**

### 1. Backend Fixes (`StudentBehaviorsMainController.php`)
- **Overall (All Subjects)**: Implemented logic for `points_mode = 'all_subjects'`. This now aggregates points for the student across the entire school (Global), regardless of the subject.
- **Overall (Subject)**: Refined logic for `points_mode = 'overall'`. This now correctly aggregates points for the student *filtered by the current subject* (Subject Specific), fixing the issue where it was previously behaving like a global filter.

### 2. Impact
- **Filter Accuracy**: The "Overall" and "Overall Subjects" filters in the Reward System now function according to their definitions.
    - "Overall (All Time)" = Student's progress in *this specific class/subject*.
    - "Overall (All Subjects)" = Student's total points across *all classes*.

### 3. Remaining Tasks
- [ ] Monitor user feedback to ensure the distinction aligns with their workflow.
