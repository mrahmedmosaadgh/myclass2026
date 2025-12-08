// types.ts
// All TypeScript interfaces + BaseQuestion with optional "version" field

type QuestionType = 'labelled-diagram' | 'image-quiz' | 'match-up' | 'group-sort' | 'missing-word' | 'sequence' | 'anagram' | 'speaking-cards' | 'quiz' | 'multiple-choice' | 'true-false';

interface BaseQuestion {
  id: string;
  type: QuestionType;
  version?: string; // Optional version field (defaults to 'default' if not provided)
  title?: string;
  description?: string;
  imageUrl?: string;
  audioUrl?: string;
}

// Specific question type interfaces
interface LabelledDiagramQuestion extends BaseQuestion {
  type: 'labelled-diagram';
  diagramUrl: string;
  labels: Array<{ id: string; text: string; x: number; y: number }>;
  correctMatches?: Array<{ labelId: string; targetId: string }>;
}

interface ImageQuizQuestion extends BaseQuestion {
  type: 'image-quiz';
  quizImageUrls: string[];
  questions: Array<{ id: string; question: string; options: string[]; correct: number; timer_ms?: number }>;
}

interface MatchUpQuestion extends BaseQuestion {
  type: 'match-up';
  items: Array<{ id: string; left: string; right: string; audioUrl?: string; imageUrl?: string }>;
  instructions?: string;
}

interface GroupSortQuestion extends BaseQuestion {
  type: 'group-sort';
  items: Array<{ id: string; content: string; groupId?: string }>;
  groups: Array<{ id: string; name: string; color?: string }>;
}

interface MissingWordQuestion extends BaseQuestion {
  type: 'missing-word';
  text: string; // Text with {{placeholder}} for missing words
  words: string[]; // Correct words in order
  options?: string[]; // Multiple choice options if needed
}

interface SequenceQuestion extends BaseQuestion {
  type: 'sequence';
  items: Array<{ id: string; content: string; correctPosition: number }>;
}

interface AnagramQuestion extends BaseQuestion {
  type: 'anagram';
  scrambled: string;
  answer: string;
  hint?: string;
}

interface SpeakingCardsQuestion extends BaseQuestion {
  type: 'speaking-cards';
  cards: Array<{ id: string; text: string; hint?: string; audioUrl?: string }>;
  recordDuration_sec: number;
}

interface QuizQuestion extends BaseQuestion {
  type: 'quiz';
  questions: Array<{ id: string; question: string; options: string[]; correct: number; timer_ms?: number }>;
}

interface MultipleChoiceQuestion extends BaseQuestion {
  type: 'multiple-choice';
  question: string;
  options: Array<{ id: string; text: string; isCorrect: boolean }>;
  allowMultiple?: boolean;
}

interface TrueFalseQuestion extends BaseQuestion {
  type: 'true-false';
  statement: string;
  isTrue: boolean;
}

// Union type for all questions
type Question =
  | LabelledDiagramQuestion
  | ImageQuizQuestion
  | MatchUpQuestion
  | GroupSortQuestion
  | MissingWordQuestion
  | SequenceQuestion
  | AnagramQuestion
  | SpeakingCardsQuestion
  | QuizQuestion
  | MultipleChoiceQuestion
  | TrueFalseQuestion;

export type { QuestionType, BaseQuestion, Question };
export {
  LabelledDiagramQuestion,
  ImageQuizQuestion,
  MatchUpQuestion,
  GroupSortQuestion,
  MissingWordQuestion,
  SequenceQuestion,
  AnagramQuestion,
  SpeakingCardsQuestion,
  QuizQuestion,
  MultipleChoiceQuestion,
  TrueFalseQuestion,
};
