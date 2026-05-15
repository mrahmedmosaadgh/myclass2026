<script setup>
import QuestionDisplay from './components/QuestionDisplay.vue'
import GroupSelector from './components/GroupSelector.vue'
import AnswerOptions from './components/AnswerOptions.vue'
import QuizControls from './components/QuizControls.vue'
import ScorePanel from './components/ScorePanel.vue'
import QuestionNavigator from './components/QuestionNavigator.vue'
import QuestionOverview from './components/QuestionOverview.vue'
import QuestionProgressBar from './components/QuestionProgressBar.vue'
import { useGroupQuizPlayerSession } from './composables/useGroupQuizPlayerSession'

const {
  groups,
  questions,
  selectedGroupId,
  currentQuestionIndex,
  currentQuestion,
  currentAnswersByGroup,
  isCurrentQuestionGraded,
  canGradeCurrentQuestion,
  answeredQuestionCount,
  gradedQuestionCount,
  selectGroup,
  selectQuestion,
  goToPreviousQuestion,
  goToNextQuestion,
  answerQuestion,
  gradeCurrentQuestion,
  getQuestionStatus,
  resetSession
} = useGroupQuizPlayerSession()
</script>

<template>
  <div class="player-grid">
    <div class="main-column">
      <QuestionProgressBar
        :total="questions.length"
        :answered="answeredQuestionCount"
        :graded="gradedQuestionCount"
      />

      <QuestionNavigator
        :current-index="currentQuestionIndex"
        :total="questions.length"
        @previous="goToPreviousQuestion"
        @next="goToNextQuestion"
      />

      <QuestionDisplay v-if="currentQuestion" :question="currentQuestion" />
      <GroupSelector :groups="groups" :selected-group-id="selectedGroupId" @select="selectGroup" />
      <AnswerOptions
        v-if="currentQuestion"
        :options="currentQuestion.options"
        :answers-by-group="currentAnswersByGroup"
        :selected-group-id="selectedGroupId"
        :is-graded="isCurrentQuestionGraded"
        :correct-option-id="currentQuestion.correctOptionId"
        @answer="answerQuestion"
      />
      <QuizControls
        :can-grade="canGradeCurrentQuestion"
        :is-graded="isCurrentQuestionGraded"
        @grade="gradeCurrentQuestion"
        @reset="resetSession"
      />
    </div>

    <div class="side-column">
      <ScorePanel :groups="groups" />
      <QuestionOverview
        :questions="questions"
        :current-index="currentQuestionIndex"
        :get-status="getQuestionStatus"
        @select="selectQuestion"
      />
    </div>
  </div>
</template>

<style scoped>
.player-grid {
  display: grid;
  grid-template-columns: minmax(0, 1fr) 280px;
  gap: 16px;
}

.main-column {
  display: grid;
  gap: 16px;
}

.side-column {
  display: grid;
  align-content: start;
  gap: 16px;
}

@media (max-width: 900px) {
  .player-grid {
    grid-template-columns: 1fr;
  }
}
</style>
