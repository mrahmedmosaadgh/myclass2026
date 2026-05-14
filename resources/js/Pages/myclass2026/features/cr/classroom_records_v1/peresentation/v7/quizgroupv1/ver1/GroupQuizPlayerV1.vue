<script setup>
import { computed, ref } from 'vue'
import QuestionDisplay from './components/QuestionDisplay.vue'
import GroupSelector from './components/GroupSelector.vue'
import AnswerOptions from './components/AnswerOptions.vue'
import QuizControls from './components/QuizControls.vue'
import ScorePanel from './components/ScorePanel.vue'
import { sampleGroups, sampleQuestion } from './data/sampleQuiz'

const groups = ref(sampleGroups.map((group) => ({ ...group })))
const question = ref({ ...sampleQuestion })
const selectedGroupId = ref(groups.value[0]?.id || null)
const answersByGroup = ref({})
const isGraded = ref(false)

const canGrade = computed(() => Object.keys(answersByGroup.value).length > 0)

function selectGroup(groupId) {
  selectedGroupId.value = groupId
}

function answerQuestion(optionId) {
  if (!selectedGroupId.value || isGraded.value) return
  answersByGroup.value = {
    ...answersByGroup.value,
    [selectedGroupId.value]: optionId
  }
}

function gradeAnswers() {
  if (!canGrade.value) return

  groups.value = groups.value.map((group) => ({
    ...group,
    score: answersByGroup.value[group.id] === question.value.correctOptionId ? group.score + 1 : group.score
  }))
  isGraded.value = true
}

function resetQuiz() {
  groups.value = sampleGroups.map((group) => ({ ...group }))
  answersByGroup.value = {}
  selectedGroupId.value = groups.value[0]?.id || null
  isGraded.value = false
}
</script>

<template>
  <div class="player-grid">
    <div class="main-column">
      <QuestionDisplay :question="question" />
      <GroupSelector :groups="groups" :selected-group-id="selectedGroupId" @select="selectGroup" />
      <AnswerOptions
        :options="question.options"
        :answers-by-group="answersByGroup"
        :selected-group-id="selectedGroupId"
        :is-graded="isGraded"
        :correct-option-id="question.correctOptionId"
        @answer="answerQuestion"
      />
      <QuizControls :can-grade="canGrade" :is-graded="isGraded" @grade="gradeAnswers" @reset="resetQuiz" />
    </div>

    <ScorePanel :groups="groups" />
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

@media (max-width: 900px) {
  .player-grid {
    grid-template-columns: 1fr;
  }
}
</style>
