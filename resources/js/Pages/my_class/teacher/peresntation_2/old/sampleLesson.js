// Sample lesson JSON used by the LessonEditor demo
export default {
  lessonId: 'lesson-1',
  title: 'Demo: Introduction to Fractions',
  slides: [
    {
      slideId: 'slide-1',
      title: 'Introduction to Fractions',
      video: {
        // src: '/assets/videos/fractions_intro.mp4',
           src :  "https://cdn.virtualnerd.com/videos/Gr6_10_01_0008.mp4" ,
        playFrom: 0,
        playTo: 120
      },
      timeline: [
        { id: 't1', type: 'videoSegment', start: 0, end: 30, trigger: 'auto' },
        { id: 't2', type: 'question', questionId: 'q1', trigger: 'time', time: 30 },
        { id: 't3', type: 'pause', trigger: 'click' },
        { id: 't4', type: 'continueVideo', start: 30, end: 60, trigger: 'auto' },
        { id: 't5', type: 'question', questionId: 'q2', trigger: 'click' },
        { id: 't6', type: 'continueVideo', start: 60, end: 120, trigger: 'auto' }
      ],
      questions: [
        {
          id: 'q1',
          type: 'MultipleChoice',
          questionText: 'Which fraction is equivalent to 1/2?',
          options: ['1/3', '2/4', '3/5'],
          correctAnswer: '2/4',
          points: { correct: 5, wrong: -1 },
          sounds: { correct: '/assets/sounds/correct.mp3', wrong: '/assets/sounds/wrong.mp3' }
        },
        {
          id: 'q2',
          type: 'OpenEnded',
          questionText: 'Write 1/4 as a decimal:',
          correctAnswer: '0.25',
          points: { correct: 5, wrong: -1 },
          sounds: { correct: '/assets/sounds/correct.mp3', wrong: '/assets/sounds/wrong.mp3' }
        }
      ]
    }
  ]
}
