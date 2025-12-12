<template>
  <q-card class="prompt-builder-card">
    <q-card-section>
      <div class="text-h6">Quick Prompt Builder</div>
      <div class="text-caption text-grey">Generate AI prompts with a few clicks</div>
    </q-card-section>

    <q-card-section class="q-pt-none">
      <q-tabs v-model="activeTemplate" dense class="text-grey" active-color="primary" indicator-color="primary">
        <q-tab name="pro-quiz" label="Pro Quiz" icon="print" />
        <q-tab name="quiz" label="Quick Quiz" icon="quiz" />
        <q-tab name="lesson" label="Lesson" icon="school" />
        <q-tab name="worksheet" label="Worksheet" icon="assignment" />
        <q-tab name="custom" label="Custom" icon="edit" />
      </q-tabs>

      <q-separator class="q-my-md" />

      <q-tab-panels v-model="activeTemplate" animated>
        <!-- Professional Quiz Template -->
        <q-tab-panel name="pro-quiz">
          <div class="q-gutter-md">
            <q-input v-model="proQuiz.grade" filled label="Grade Level" placeholder="e.g. Grade 6" />
            <q-input v-model.number="proQuiz.numQuestions" filled type="number" label="Total Questions" min="10" max="50" />
            <q-input v-model="proQuiz.lessons" filled type="textarea" rows="3" label="Lessons/Topics (one per line)" 
              placeholder="e.g.&#10;3-1 Understand Exponents (P.123)&#10;3-2 Find GCF and LCM (P.129)" />
            <q-toggle v-model="proQuiz.withSections" label="Divide into sections by lesson" />
            <q-toggle v-model="proQuiz.groupByType" label="Group questions by type (Multiple Choice, True/False, etc.)" />
            <q-toggle v-model="proQuiz.withLatex" label="Render math with LaTeX/KaTeX" />
            <q-toggle v-model="proQuiz.withAnswerKey" label="Include answer key with explanations" />
            <q-toggle v-model="proQuiz.printReady" label="Print-ready format (HTML/clean)" />
          </div>
        </q-tab-panel>

        <!-- Quiz Template -->
        <q-tab-panel name="quiz">
          <div class="q-gutter-md">
            <q-input v-model="quiz.topic" filled label="Topic / Lesson" placeholder="e.g. Photosynthesis, Fractions" />
            <q-input v-model="quiz.grade" filled label="Grade / Level" placeholder="e.g. Grade 7, High School" />
            <q-input v-model.number="quiz.numQuestions" filled type="number" label="Number of questions" min="5" max="50" />
            <q-select v-model="quiz.types" filled multiple label="Question Types" :options="questionTypes" emit-value map-options />
            <q-toggle v-model="quiz.withAnswerKey" label="Include answer key with explanations" />
          </div>
        </q-tab-panel>

        <!-- Lesson Template -->
        <q-tab-panel name="lesson">
          <div class="q-gutter-md">
            <q-input v-model="lesson.topic" filled label="Lesson Topic" placeholder="e.g. Introduction to Algebra" />
            <q-input v-model="lesson.grade" filled label="Grade Level" placeholder="e.g. Grade 8" />
            <q-input v-model.number="lesson.duration" filled type="number" label="Duration (minutes)" min="15" max="120" />
            <q-select v-model="lesson.sections" filled multiple label="Include Sections" :options="lessonSections" emit-value map-options />
          </div>
        </q-tab-panel>

        <!-- Worksheet Template -->
        <q-tab-panel name="worksheet">
          <div class="q-gutter-md">
            <q-input v-model="worksheet.topic" filled label="Topic" placeholder="e.g. Multiplication Tables" />
            <q-input v-model="worksheet.grade" filled label="Grade Level" placeholder="e.g. Grade 3" />
            <q-input v-model.number="worksheet.numProblems" filled type="number" label="Number of problems" min="5" max="50" />
            <q-select v-model="worksheet.difficulty" filled label="Difficulty" :options="difficultyLevels" emit-value map-options />
            <q-toggle v-model="worksheet.withAnswers" label="Include answer key" />
          </div>
        </q-tab-panel>

        <!-- Custom Template -->
        <q-tab-panel name="custom">
          <q-input v-model="customPrompt" filled type="textarea" rows="5" label="Custom Prompt" placeholder="Type your custom prompt here..." />
        </q-tab-panel>
      </q-tab-panels>
    </q-card-section>

    <q-card-actions align="right">
      <q-btn flat label="Cancel" color="grey" @click="$emit('close')" />
      <q-btn unelevated label="Generate Prompt" color="primary" icon="auto_awesome" @click="generatePrompt" />
      <q-btn v-if="generatedPrompt" unelevated label="Use in AI" color="accent" icon="psychology" @click="useInAI" />
    </q-card-actions>

    <!-- Generated Prompt Preview -->
    <q-card-section v-if="generatedPrompt" class="bg-grey-1">
      <div class="text-subtitle2 q-mb-sm">Generated Prompt:</div>
      <q-input v-model="generatedPrompt" filled readonly type="textarea" rows="4" class="font-mono text-sm" @click="$event.target.select()" />
      <q-btn flat dense size="sm" color="primary" icon="content_copy" label="Copy" @click="copyPrompt" class="q-mt-sm" />
    </q-card-section>
  </q-card>
</template>

<script setup>
import { ref } from 'vue';
import { useQuasar } from 'quasar';

const emit = defineEmits(['close', 'use-prompt']);

const $q = useQuasar();

const activeTemplate = ref('pro-quiz');
const generatedPrompt = ref('');

// Professional Quiz template data
const proQuiz = ref({
  grade: '',
  numQuestions: 20,
  lessons: '',
  withSections: true,
  groupByType: true,
  withLatex: true,
  withAnswerKey: true,
  printReady: true,
});

// Quiz template data
const quiz = ref({
  topic: '',
  grade: '',
  numQuestions: 10,
  types: ['multiple-choice', 'true-false'],
  withAnswerKey: true,
});

const questionTypes = [
  { label: 'Multiple Choice', value: 'multiple-choice' },
  { label: 'True/False', value: 'true-false' },
  { label: 'Fill in the Blank', value: 'fill-blank' },
  { label: 'Short Answer', value: 'short-answer' },
];

// Lesson template data
const lesson = ref({
  topic: '',
  grade: '',
  duration: 45,
  sections: ['objectives', 'content', 'activities'],
});

const lessonSections = [
  { label: 'Learning Objectives', value: 'objectives' },
  { label: 'Main Content', value: 'content' },
  { label: 'Activities', value: 'activities' },
  { label: 'Assessment', value: 'assessment' },
  { label: 'Homework', value: 'homework' },
];

// Worksheet template data
const worksheet = ref({
  topic: '',
  grade: '',
  numProblems: 20,
  difficulty: 'medium',
  withAnswers: true,
});

const difficultyLevels = [
  { label: 'Easy', value: 'easy' },
  { label: 'Medium', value: 'medium' },
  { label: 'Hard', value: 'hard' },
  { label: 'Mixed', value: 'mixed' },
];

// Custom prompt
const customPrompt = ref('');

const generatePrompt = () => {
  let prompt = '';

  if (activeTemplate.value === 'pro-quiz') {
    if (!proQuiz.value.grade || !proQuiz.value.lessons) {
      $q.notify({ type: 'warning', message: 'Please fill in Grade and Lessons!' });
      return;
    }
    const lessonsFormatted = proQuiz.value.lessons.split('\n').filter(l => l.trim()).map(l => `• ${l.trim()}`).join('\n');
    const sectionsText = proQuiz.value.withSections ? `- Divide into ${proQuiz.value.lessons.split('\n').filter(l => l.trim()).length} clear sections with headings for each lesson\n` : '';
    const groupingText = proQuiz.value.groupByType ? '- Group questions by type with clear headings:\n  * Part A: Multiple Choice Questions\n  * Part B: True/False Questions\n  * Part C: Fill in the Blank Questions\n  * Part D: Short Answer Questions\n' : '- Mix these question types evenly: Multiple Choice (4 options, only 1 correct), True/False, Fill in the Blank, Short Answer (1–2 sentences or show work)\n';
    const latexText = proQuiz.value.withLatex ? '- Render every math expression in proper LaTeX using <span class="katex">…</span> so it displays beautifully\n' : '';
    const answerKeyText = proQuiz.value.withAnswerKey ? '- End with a separate "Answer Key with Explanations" section, grouped by section, showing correct answer + 1–2 sentence explanation with steps where needed\n' : '';
    const formatText = proQuiz.value.printReady ? 'Output the entire quiz in clean HTML/Markdown so it looks exactly like a real test when copied into Word or Google Docs.' : '';

    prompt = `You are an expert ${proQuiz.value.grade} teacher. Create a polished, printable ${proQuiz.value.numQuestions}-question quiz covering exactly these lessons:

${lessonsFormatted}

Grade level: ${proQuiz.value.grade} students

Requirements:
- Exactly ${proQuiz.value.numQuestions} questions total
${sectionsText}${groupingText}- Use realistic distractors (common student mistakes)
${latexText}- Start with a bold quiz title and clear student instructions (how to answer each type)
${answerKeyText}- Make it 100% error-free, printable, and classroom-ready

${formatText}`;
  } else if (activeTemplate.value === 'quiz') {
    if (!quiz.value.topic || !quiz.value.grade) {
      $q.notify({ type: 'warning', message: 'Please fill in Topic and Grade!' });
      return;
    }
    const typesText = quiz.value.types.map(t => questionTypes.find(qt => qt.value === t)?.label).join(', ');
    const keyText = quiz.value.withAnswerKey ? 'Include answer key with explanations. ' : '';
    prompt = `Create a ${quiz.value.numQuestions}-question quiz about "${quiz.value.topic}" for ${quiz.value.grade} students. Question types: ${typesText}. ${keyText}Format: numbered questions with clear instructions.`;
  } else if (activeTemplate.value === 'lesson') {
    if (!lesson.value.topic || !lesson.value.grade) {
      $q.notify({ type: 'warning', message: 'Please fill in Topic and Grade!' });
      return;
    }
    const sectionsText = lesson.value.sections.map(s => lessonSections.find(ls => ls.value === s)?.label).join(', ');
    prompt = `Create a ${lesson.value.duration}-minute lesson plan about "${lesson.value.topic}" for ${lesson.value.grade} students. Include: ${sectionsText}. Make it engaging and age-appropriate.`;
  } else if (activeTemplate.value === 'worksheet') {
    if (!worksheet.value.topic || !worksheet.value.grade) {
      $q.notify({ type: 'warning', message: 'Please fill in Topic and Grade!' });
      return;
    }
    const answerText = worksheet.value.withAnswers ? 'Include answer key. ' : '';
    prompt = `Create a ${worksheet.value.difficulty} difficulty worksheet about "${worksheet.value.topic}" for ${worksheet.value.grade} students with ${worksheet.value.numProblems} problems. ${answerText}Clean, printable format.`;
  } else {
    prompt = customPrompt.value;
  }

  generatedPrompt.value = prompt;
};

const copyPrompt = () => {
  navigator.clipboard.writeText(generatedPrompt.value).then(() => {
    $q.notify({ type: 'positive', message: 'Copied to clipboard!', position: 'top', timeout: 1500 });
  });
};

const useInAI = () => {
  emit('use-prompt', generatedPrompt.value);
};
</script>

<style scoped>
.prompt-builder-card {
  min-width: 500px;
  max-width: 90vw;
}

.font-mono {
  font-family: monospace;
}
</style>
