I want you to generate a complete interactive lesson editor + player system using Vue 3 (Composition API) + Quasar Framework.
It should be offline-capable, modular, and use a timeline-based slide structure with videos, questions, scoring, and progress bars.

1️⃣ Slides & Video (Full Details)

Each slide can contain a video (local file upload or URL).

Videos can play from a specific start time to a specific end time.

Supports pause, resume, auto-play, or manual click to continue.

Teacher can choose:

Play full video or specific segment (start/end time).

Pause video at multiple points.

Insert questions or images at any point in the video.

Video playback must be synchronized with the timeline, so that events happen at the correct time or on click.

Should include video controls: play, pause, mute, volume, full-screen.

2️⃣ Timeline System (PowerPoint-style)

Timeline events support three trigger modes:

"time" → triggers automatically at a specific timestamp (e.g., 30s).

"click" → waits for the student to click “Next” to continue.

"auto" → triggers immediately after the previous event finishes.

Timeline events can be:

Show a question (any question type).

Pause or continue video at a certain timestamp.

Display an image or text message overlay.

Combine multiple events (video + question + image) in one timeline segment.

Teacher should be able to drag & reorder events in the timeline editor.

Events should be visualized as a timeline bar with markers for questions, images, and pauses.

3️⃣ Questions & Scoring

Supports modular question types:

Multiple Choice

True/False

Open-Ended / Fill-in-the-Blank

(Expandable for future types like Drag-and-Drop)

Each question stores:

Question text

Options (if applicable)

Correct answer(s)

Associated sounds (correct/wrong)

Points for correct/incorrect answers

Retry behavior if answered incorrectly

Scoring rules:

First correct attempt → +5 points

Each wrong retry → -1 point

Option to limit number of retries

Questions must trigger feedback sounds:

Correct answer → success sound

Wrong answer → error sound

After correct answer: question disappears automatically or triggers the next timeline event.

After wrong answer: student retries until correct (or limited retries), with point deductions.

4️⃣ Progress Tracking

Display progress for the current slide and total lesson progress.

Progress bar must update live as the video plays and questions are answered.

Show points earned per slide and total points for the lesson.

Include optional percentage complete indicator.

Should handle multiple slides seamlessly, carrying forward total points.

5️⃣ Local Persistence & Export/Import

Save lesson structure in JSON including:

Slide information

Video paths

Timeline events

Questions with options, answers, points, and sounds

Auto-save lesson in LocalStorage or IndexedDB while editing.

Auto-load last edited lesson on page refresh.

Export functionality:

Export lesson as .zip containing JSON + all assets (video, audio, images).

Import functionality:

Import lesson .zip and restore all data into the editor.

Hybrid mode: local editing + optional upload to backend (Laravel) when saving to platform.

Handle large assets efficiently; store paths in JSON and optionally embed small media as base64.

6️⃣ Component Structure (LessonEditor/)
LessonEditor/
├── LessonEditor.vue          # Main component managing slides, state, and lesson JSON
├── SlideEditor.vue           # Edit a single slide (video + timeline + questions)
├── LessonTimeline.vue        # Visual timeline editor with draggable events
├── LessonVideoPlayer.vue     # Handles video playback, pause/resume, timeline triggers
├── QuestionEditor.vue        # Create/edit questions and properties
├── QuestionDisplay.vue       # Shows questions to students during playback
├── QuestionTypes/
│   ├── MultipleChoice.vue
│   ├── TrueFalse.vue
│   └── OpenEnded.vue
├── LessonProgress.vue        # Slide + total lesson progress bars and points
├── LessonControls.vue        # Save, Load, Export, Import buttons
└── utils/
    ├── storage.js            # LocalStorage / IndexedDB helpers
    └── fileManager.js        # Zip/unzip + import/export helpers

7️⃣ Additional Requirements for AI Output

Use Vue 3 Composition API with reactive state and proper component communication.

Demonstrate timeline triggers (time, click, auto) working in a demo lesson.

Wire sample JSON lesson (1–2 slides, multiple questions, video segments, sounds).

Include progress bars updating live for slide + total lesson.

Include feedback sounds for correct/wrong answers.

Include comments explaining all logic, timeline handling, scoring, and JSON structure.

Provide clean, modular Quasar UI placeholders for video, timeline, questions, progress, and controls.

Goal: Generate a fully working starter project for an interactive lesson editor/player where teachers can:

Add and edit slides, video, and timeline events

Add multiple question types with scoring

Preview lesson live with points, progress, and feedback

Save, export, and import lessons as JSON + assets


1️⃣ Sample JSON Lesson (to use as demo)
{
  "lessonId": "lesson-1",
  "slides": [
    {
      "slideId": "slide-1",
      "title": "Introduction to Fractions",
      "video": {
        <!-- "src": "assets/videos/fractions_intro.mp4", -->
        "src":  "https://cdn.virtualnerd.com/videos/Gr6_10_01_0008.mp4" title="assets/videos/fractions_intro.mp4" ,
        "playFrom": 0,
        "playTo": 120
      },
      "timeline": [
        { "type": "videoSegment", "start": 0, "end": 30, "trigger": "auto" },
        { "type": "question", "questionId": "q1", "trigger": "time", "time": 30 },
        { "type": "pause", "trigger": "click" },
        { "type": "continueVideo", "start": 30, "end": 60, "trigger": "auto" },
        { "type": "question", "questionId": "q2", "trigger": "click" },
        { "type": "continueVideo", "start": 60, "end": 120, "trigger": "auto" }
      ],
      "questions": [
        {
          "id": "q1",
          "type": "MultipleChoice",
          "questionText": "Which fraction is equivalent to 1/2?",
          "options": ["1/3", "2/4", "3/5"],
          "correctAnswer": "2/4",
          "onCorrect": { "action": "hideQuestion", "scoreChange": 5, "sound": "assets/sounds/correct.mp3" },
          "onWrong": { "action": "retry", "scoreChange": -1, "sound": "assets/sounds/wrong.mp3" }
        },
        {
          "id": "q2",
          "type": "OpenEnded",
          "questionText": "Write 1/4 as a decimal:",
          "correctAnswer": "0.25",
          "onCorrect": { "action": "hideQuestion", "scoreChange": 5, "sound": "assets/sounds/correct.mp3" },
          "onWrong": { "action": "retry", "scoreChange": -1, "sound": "assets/sounds/wrong.mp3" }
        }
      ]
    }
  ]
}
