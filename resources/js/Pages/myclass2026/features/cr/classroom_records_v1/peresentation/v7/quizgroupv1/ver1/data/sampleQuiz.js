export const sampleGroups = [
  { id: 'g1', name: 'Group 1', color: '#2563eb', score: 0 },
  { id: 'g2', name: 'Group 2', color: '#16a34a', score: 0 },
  { id: 'g3', name: 'Group 3', color: '#f97316', score: 0 }
]

export const sampleQuestions = [
  {
    id: 'sample-group-mcq-1',
    question: 'Which option is the correct answer for this sample group quiz?',
    options: [
      { id: 'A', text: 'Option A' },
      { id: 'B', text: 'Option B' },
      { id: 'C', text: 'Option C' },
      { id: 'D', text: 'Option D' }
    ],
    correctOptionId: 'B'
  },
  {
    id: 'sample-group-mcq-2',
    question: 'Which color is used by Group 2 in this sample session?',
    options: [
      { id: 'A', text: 'Blue' },
      { id: 'B', text: 'Green' },
      { id: 'C', text: 'Orange' },
      { id: 'D', text: 'Purple' }
    ],
    correctOptionId: 'B'
  },
  {
    id: 'sample-group-mcq-3',
    question: 'What should happen after a group answer is graded correctly?',
    options: [
      { id: 'A', text: 'The group score increases' },
      { id: 'B', text: 'The question is deleted' },
      { id: 'C', text: 'All groups reset' },
      { id: 'D', text: 'The version selector disappears' }
    ],
    correctOptionId: 'A'
  }
]
