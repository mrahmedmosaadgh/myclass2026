You are an expert educational software designer, frontend developer, and curriculum planner. 
Your task is to generate a **full interactive lesson editor and player system** for Quasar + Vue 3, with a **timeline view**, ready for production use. 
The output must be **highly structured, visually clear, fully ordered, and interactive**, with all data verified before generating.

Requirements:

1. **Lesson Structure**:
   - Each lesson has: `title`, `objectives`, `gradeLevel`, `subject`, `curriculumLink`, `duration`.
   - Each lesson contains multiple **activities**, each with:
     - `type` (lecture, quiz, game, discussion, exercise)
     - `duration`
     - `teacherInstructions`
     - `studentInstructions`
     - `materials` (links or resources)
     - `learningOutcome`
     - `dependencies` (optional, previous activity IDs)

2. **Timeline View**:
   - Activities displayed in chronological order.
   - Show `startTime`, `duration`, `endTime`.
   - Highlight dependencies visually (arrows, connectors).
   - Color-code activities by type.

3. **Teacher/Student Interactions**:
   - Teachers: start/pause activity, give hints, provide feedback.
   - Students: submit answers, request help, mark completion.
   - Real-time notifications if students complete early or lag.

4. **Verification / Data Integrity**:
   - Check that all `curriculumLink`, `materials`, `dependencies` exist and are valid.
   - Ensure `duration` matches start/end times, no overlaps.
   - Confirm learning objectives align with activities.

5. **Frontend Requirements (Quasar + Vue)**:
   - Generate **Vue components**:
     - `<LessonTimeline>`: interactive timeline of activities.
     - `<ActivityCard>`: details for each activity.
     - `<TeacherPanel>`: start/pause, feedback, hints.
     - `<StudentPanel>`: submit answers, request help.
   - Include **props and events** for real-time interactivity.
   - Include **sample dummy data** to test components immediately.
   - Make it **responsive**, **color-coded**, **visually clear**.

6. **Output**:
   - Provide **full Vue component code** with Quasar UI.
   - Provide **structured dummy data** for testing.
   - Provide a **visual timeline layout example**.
   - Include comments for developers to integrate easily.

7. **Final Requirements**:
   - **Never skip steps**.
   - **Never produce unordered activities**.
   - **Validate all data integrity** before generating.
   - Output should be **copy-paste ready**, fully working in a Quasar + Vue 3 project.
