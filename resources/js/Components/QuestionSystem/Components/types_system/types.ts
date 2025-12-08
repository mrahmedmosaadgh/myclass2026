/**
 * Core TypeScript Type Definitions for Question System
 * Supports versioned question types with extensible architecture
 */

// ============================================================================
// Base Types
// ============================================================================

export type QuestionType =
    | 'labelled-diagram'
    | 'image-quiz'
    | 'match-up'
    | 'group-sort'
    | 'missing-word'
    | 'sequence'
    | 'anagram'
    | 'speaking-cards'
    | 'quiz'
    | 'multiple-choice'
    | 'true-false';

export type QuestionVersion = string; // e.g., 'default', 'drag-drop', 'with-timer'

// ============================================================================
// Base Question Interface
// ============================================================================

export interface BaseQuestion {
    id: string;
    type: QuestionType;
    version?: QuestionVersion; // Optional - defaults to 'default' if not specified
    title: string;
    description?: string;
    points?: number;
    timeLimit?: number; // in seconds
    imageUrl?: string;
    audioUrl?: string;
    latex?: string;
}

// ============================================================================
// Labelled Diagram
// ============================================================================

export interface LabelPoint {
    id: string;
    x: number; // percentage 0-100
    y: number; // percentage 0-100
    label: string;
    correctAnswer: string;
}

export interface LabelledDiagramQuestion extends BaseQuestion {
    type: 'labelled-diagram';
    imageUrl: string;
    labels: LabelPoint[];
    instructions?: string;
}

// ============================================================================
// Image Quiz
// ============================================================================

export interface ImageQuizOption {
    id: string;
    imageUrl: string;
    label?: string;
    isCorrect: boolean;
}

export interface ImageQuizQuestion extends BaseQuestion {
    type: 'image-quiz';
    question: string;
    options: ImageQuizOption[];
    multiSelect?: boolean;
}

// ============================================================================
// Match Up
// ============================================================================

export interface MatchPair {
    id: string;
    left: string | { type: 'text' | 'image' | 'latex'; content: string };
    right: string | { type: 'text' | 'image' | 'latex'; content: string };
    audioUrl?: string; // For language learning with audio
}

export interface MatchUpQuestion extends BaseQuestion {
    type: 'match-up';
    pairs: MatchPair[];
    instructions?: string;
    shuffleRight?: boolean;
}

// ============================================================================
// Group Sort
// ============================================================================

export interface GroupCategory {
    id: string;
    name: string;
    color?: string;
}

export interface GroupItem {
    id: string;
    content: string;
    categoryId: string;
    imageUrl?: string;
}

export interface GroupSortQuestion extends BaseQuestion {
    type: 'group-sort';
    categories: GroupCategory[];
    items: GroupItem[];
    instructions?: string;
}

// ============================================================================
// Missing Word
// ============================================================================

export interface MissingWordBlank {
    id: string;
    correctAnswer: string;
    alternatives?: string[]; // Alternative correct answers
    hint?: string;
}

export interface MissingWordQuestion extends BaseQuestion {
    type: 'missing-word';
    sentence: string; // Use {{blank_id}} as placeholder
    blanks: MissingWordBlank[];
    wordBank?: string[]; // Optional word bank
}

// ============================================================================
// Sequence
// ============================================================================

export interface SequenceItem {
    id: string;
    content: string;
    order: number;
    imageUrl?: string;
}

export interface SequenceQuestion extends BaseQuestion {
    type: 'sequence';
    items: SequenceItem[];
    instructions?: string;
}

// ============================================================================
// Anagram
// ============================================================================

export interface AnagramQuestion extends BaseQuestion {
    type: 'anagram';
    scrambledWord: string;
    correctWord: string;
    hint?: string;
    imageUrl?: string;
}

// ============================================================================
// Speaking Cards
// ============================================================================

export interface SpeakingCard {
    id: string;
    text: string;
    imageUrl?: string;
    audioUrl?: string; // Reference pronunciation
    phonetic?: string;
}

export interface SpeakingCardsQuestion extends BaseQuestion {
    type: 'speaking-cards';
    cards: SpeakingCard[];
    instructions?: string;
    recordingRequired?: boolean;
    minRecordingDuration?: number; // in seconds
}

// ============================================================================
// Quiz
// ============================================================================

export interface QuizOption {
    id: string;
    text: string;
    isCorrect: boolean;
    latex?: string;
}

export interface QuizQuestion extends BaseQuestion {
    type: 'quiz';
    question: string;
    options: QuizOption[];
    explanation?: string;
    multiSelect?: boolean;
}

// ============================================================================
// Multiple Choice
// ============================================================================

export interface MultipleChoiceOption {
    id: string;
    text: string;
    isCorrect: boolean;
}

export interface MultipleChoiceQuestion extends BaseQuestion {
    type: 'multiple-choice';
    question: string;
    options: MultipleChoiceOption[];
    explanation?: string;
}

// ============================================================================
// True/False
// ============================================================================

export interface TrueFalseQuestion extends BaseQuestion {
    type: 'true-false';
    statement: string;
    correctAnswer: boolean;
    explanation?: string;
}

// ============================================================================
// Union Type for All Questions
// ============================================================================

export type Question =
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

// ============================================================================
// Answer Types
// ============================================================================

export interface BaseAnswer {
    questionId: string;
    timestamp: Date;
}

export interface LabelledDiagramAnswer extends BaseAnswer {
    labels: Record<string, string>; // labelId -> userAnswer
}

export interface ImageQuizAnswer extends BaseAnswer {
    selectedOptions: string[]; // option IDs
}

export interface MatchUpAnswer extends BaseAnswer {
    matches: Record<string, string>; // leftId -> rightId
}

export interface GroupSortAnswer extends BaseAnswer {
    groups: Record<string, string[]>; // categoryId -> itemIds
}

export interface MissingWordAnswer extends BaseAnswer {
    blanks: Record<string, string>; // blankId -> userAnswer
}

export interface SequenceAnswer extends BaseAnswer {
    sequence: string[]; // ordered item IDs
}

export interface AnagramAnswer extends BaseAnswer {
    answer: string;
}

export interface SpeakingCardsAnswer extends BaseAnswer {
    recordings: Record<string, Blob | string>; // cardId -> recording blob or URL
}

export interface QuizAnswer extends BaseAnswer {
    selectedOptions: string[]; // option IDs
}

export interface MultipleChoiceAnswer extends BaseAnswer {
    selectedOption: string; // option ID
}

export interface TrueFalseAnswer extends BaseAnswer {
    answer: boolean;
}

export type Answer =
    | LabelledDiagramAnswer
    | ImageQuizAnswer
    | MatchUpAnswer
    | GroupSortAnswer
    | MissingWordAnswer
    | SequenceAnswer
    | AnagramAnswer
    | SpeakingCardsAnswer
    | QuizAnswer
    | MultipleChoiceAnswer
    | TrueFalseAnswer;

// ============================================================================
// Version Registry
// ============================================================================

export interface VersionInfo {
    name: string;
    displayName: string;
    description: string;
    icon?: string;
}

export interface QuestionTypeConfig {
    type: QuestionType;
    displayName: string;
    icon: string;
    versions: Record<string, VersionInfo>;
}

// ============================================================================
// Preset Types
// ============================================================================

export interface QuestionPreset {
    id: string;
    name: string;
    subject: 'science' | 'math' | 'language-learning' | 'general';
    questions: Question[];
    metadata?: {
        author?: string;
        createdAt?: string;
        difficulty?: 'easy' | 'medium' | 'hard';
        tags?: string[];
    };
}
