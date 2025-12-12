import axios from 'axios';

export async function fetchQuestions(filters = {}) {
  // Placeholder: Replace with real API call
  return [
    {
      id: 1,
      title: 'Describe your education background',
      type: 'textarea',
      category: ['Education'],
      language: 'en',
      tags: ['core'],
      is_required: true,
      options: [],
      canEdit: true
    },
    {
      id: 2,
      title: 'Upload your portfolio',
      type: 'media',
      category: ['Experience'],
      language: 'en',
      tags: ['optional'],
      is_required: false,
      options: [],
      canEdit: true
    }
  ];
}

export async function submitQuestion(payload) {
  // Placeholder: Replace with real API call
  return { success: true };
}

export async function submitAnswer(questionId, payload) {
  // Placeholder: Replace with real API call
  return { success: true };
}

export async function uploadMedia(file) {
  // Placeholder: Replace with real API call
  return { url: URL.createObjectURL(file) };
}

export async function fetchAnswers(resumeId) {
  // Placeholder: Replace with real API call
  return [];
}
