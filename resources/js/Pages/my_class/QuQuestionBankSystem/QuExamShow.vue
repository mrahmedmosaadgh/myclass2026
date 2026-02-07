<template>
  <Head :title="exam.title" />
  <div class="q-pa-md">
    <q-card>
      <q-card-section>
        <div class="row items-center no-wrap">
          <div class="col">
            <div class="text-h6">{{ exam.title }}</div>
            <div class="text-subtitle2 text-grey-7">{{ exam.subject?.name }}</div>
          </div>
          <div class="col-auto">
            <q-btn flat round icon="edit" color="primary" @click="editExam">
              <q-tooltip>Edit Exam</q-tooltip>
            </q-btn>
            <q-btn flat round icon="arrow_back" color="grey" @click="goBack">
              <q-tooltip>Back to List</q-tooltip>
            </q-btn>
          </div>
        </div>
      </q-card-section>

      <q-separator />

      <q-card-section>
        <div class="row q-col-gutter-md">
          <div class="col-12 col-md-8">
            <div class="text-h6 q-mb-md">Description</div>
             <div class="text-body1 text-grey-8" v-if="exam.description">
              {{ exam.description }}
            </div>
            <div class="text-body2 text-grey-5" v-else>
              No description provided.
            </div>

            <div class="q-mt-lg">
              <div class="text-h6 q-mb-md">Exam Details</div>
              <q-list bordered separator>
                <q-item>
                  <q-item-section>
                    <q-item-label caption>Type</q-item-label>
                    <q-item-label class="text-capitalize">{{ exam.exam_type }}</q-item-label>
                  </q-item-section>
                </q-item>
                
                <q-item>
                  <q-item-section>
                    <q-item-label caption>Duration</q-item-label>
                    <q-item-label>{{ exam.duration_minutes }} minutes</q-item-label>
                  </q-item-section>
                </q-item>

                <q-item>
                  <q-item-section>
                    <q-item-label caption>Total Marks</q-item-label>
                    <q-item-label>{{ exam.total_marks }}</q-item-label>
                  </q-item-section>
                </q-item>

                 <q-item v-if="exam.passing_score">
                  <q-item-section>
                    <q-item-label caption>Passing Score</q-item-label>
                    <q-item-label>{{ Math.round(exam.passing_score) }}</q-item-label>
                  </q-item-section>
                </q-item>
                
                 <q-item>
                  <q-item-section>
                    <q-item-label caption>Status</q-item-label>
                    <q-item-label>
                        <q-chip :color="exam.is_published ? 'green-2' : 'grey-3'" :text-color="exam.is_published ? 'green-9' : 'grey-8'" dense>
                            {{ exam.is_published ? 'Published' : 'Draft' }}
                        </q-chip>
                    </q-item-label>
                  </q-item-section>
                </q-item>

                <q-item v-if="exam.start_date || exam.end_date">
                  <q-item-section>
                    <q-item-label caption>Availability</q-item-label>
                     <q-item-label v-if="exam.start_date">
                        Starts: {{ formatDate(exam.start_date) }}
                     </q-item-label>
                     <q-item-label v-if="exam.end_date">
                        Ends: {{ formatDate(exam.end_date) }}
                     </q-item-label>
                  </q-item-section>
                </q-item>

              </q-list>
            </div>
          </div>

          <div class="col-12 col-md-4">
             <div class="text-h6 q-mb-md">Questions Overview</div>
             <q-card bordered flat>
                <q-card-section>
                    <div class="text-subtitle1">Total Questions: {{ exam.questions ? exam.questions.length : 0 }}</div>
                </q-card-section>
             </q-card>
             
             <!-- Could add Bloom distribution chart here later -->
          </div>
        </div>
      </q-card-section>
      
      <q-separator />
      
      <q-card-section>
         <div class="text-h6 q-mb-md">Questions</div>
         <div v-if="exam.questions && exam.questions.length > 0">
            <div class="q-gutter-y-md">
                <q-card 
                    v-for="(question, index) in exam.questions" 
                    :key="question.id" 
                    bordered 
                    flat
                >
                    <q-card-section class="bg-grey-1 row items-center justify-between">
                        <div class="text-subtitle1">
                            <strong>Q{{ index + 1 }}</strong> 
                            <span class="text-caption text-grey-7 q-ml-sm">
                                ({{ question.question_type }})
                            </span>
                        </div>
                        <div class="row q-gutter-sm">
                            <q-chip v-if="question.difficulty" :color="difficultyColor(question.difficulty)" text-color="white" size="sm">
                                {{ question.difficulty }}
                            </q-chip>
                            <q-chip v-if="question.bloom_level" color="purple-3" :icon="getBloomIcon(question.bloom_level)" text-color="white" size="sm">
                                {{ capitalizeFirst(question.bloom_level) }}
                            </q-chip>
                            <q-chip color="primary" text-color="white" size="sm">
                                {{ question.marks }} {{ question.marks === 1 ? 'Mark' : 'Marks' }}
                            </q-chip>
                        </div>
                    </q-card-section>
                    <q-separator />
                    <q-card-section>
                        <QuQuestionDisplay :question="question" :readonly="true" :show-correct-answer="true" :hide-header="true" />
                    </q-card-section>
                </q-card>
            </div>
         </div>
         <div v-else class="text-grey-7 text-center q-pa-lg">
            No questions added to this exam yet.
         </div>
      </q-card-section>

    </q-card>
  </div>
</template>

<script setup>
import { router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import QuQuestionDisplay from './QuComponents/QuQuestionDisplay.vue';

const props = defineProps({
  exam: Object,
});

const goBack = () => {
  router.visit(route('qu-exams.index'));
};

const editExam = () => {
    // Navigate to edit page or open edit dialog (depending on implementation in List)
    // Since List uses dialog, we might want to go back to list and open dialog, 
    // or implement a separate edit page. For now, let's just go back to list
    // where they can edit. 
    // Ideally we would support routing to edit, but check QuExamList implementation...
    // It seems QuExamList opens a dialog. 
    // Let's check if there is an edit route.
    // QuExamController has edit() method returning QuExamForm. 
    // So we can visit qu-exams.edit
    
    // NOTE: The previous context showed list usage of dialog. 
    // But controller has edit method rendering a page.
    // Let's try visiting the edit route.
    router.visit(route('qu-exams.edit', props.exam.id));
};

const formatDate = (dateStr) => {
    if (!dateStr) return '';
    return new Date(dateStr).toLocaleString();
};

const truncateText = (text, length) => {
  if (!text) return '';
  return text.length > length ? text.substring(0, length) + '...' : text;
};

const capitalizeFirst = (str) => {
  return str ? str.charAt(0).toUpperCase() + str.slice(1) : '';
};

const difficultyColor = (difficulty) => {
  const colors = {
    easy: 'green',
    medium: 'orange',
    hard: 'red'
  };
  return colors[difficulty] || 'grey';
};

const getBloomIcon = (level) => {
  const icons = {
    remember: 'psychology',
    understand: 'lightbulb',
    apply: 'build',
    analyze: 'analytics',
    evaluate: 'fact_check',
    create: 'auto_awesome'
  };
  return icons[level] || 'help';
};
</script>
