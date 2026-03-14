/**
 * Quiz data for the 2× multiplication table.
 * Each question has 4 options: 1 correct + 3 nearby distractors.
 * Passed as a prop to MultipleChoiceQuiz.vue.
 */

function makeOptions(correct) {
    const distractors = new Set();
    const offsets = [-4, -2, 2, 4, -6, 6];
    for (const offset of offsets) {
        const val = correct + offset;
        if (val > 0 && val !== correct) {
            distractors.add(val);
            if (distractors.size === 3) break;
        }
    }
    const options = [correct, ...distractors];
    // Fisher-Yates shuffle
    for (let i = options.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [options[i], options[j]] = [options[j], options[i]];
    }
    return options;
}

const TABLE = 2;
const TOTAL = 15;

const quizData = Array.from({ length: TOTAL }, (_, i) => {
    const multiplier = i + 1;
    const correct = TABLE * multiplier;
    return {
        id: `q${multiplier}`,
        question: `${TABLE} × ${multiplier}`,
        correctAnswer: correct,
        options: makeOptions(correct),
    };
});

export default quizData;
