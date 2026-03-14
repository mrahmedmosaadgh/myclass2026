/**
 * Quiz data for the Tables Diploma.
 * 24 random questions from 2x2 to 10x10.
 */

export function generateQuestions(total = 24) {
    const questions = [];
    for (let i = 0; i < total; i++) {
        // Generate random factors between 2 and 10
        const val1 = Math.floor(Math.random() * 9) + 2;
        const val2 = Math.floor(Math.random() * 9) + 2;
        const correct = val1 * val2;

        questions.push({
            id: `q${i}_${Date.now()}`,
            question: `${val1}x${val2}=`,
            correctAnswer: correct,
        });
    }
    return questions;
}

export default generateQuestions();
