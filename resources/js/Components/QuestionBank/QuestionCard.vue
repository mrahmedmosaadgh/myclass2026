<template>
  <q-card class="question-card" flat bordered>
    <q-card-section class="q-pa-md">
      <div class="row items-start q-gutter-md">
        <!-- Question Type Icon -->
        <div class="question-type-icon" :style="{ background: typeGradient }">
          <q-icon :name="typeIcon" size="28px" color="white" />
        </div>
        
        <!-- Question Content -->
        <div class="col">
          <!-- Header with Status -->
          <div class="row items-center justify-between q-mb-sm">
            <div class="row items-center q-gutter-sm">
              <span class="text-caption text-grey-7">{{ questionType }}</span>
              <q-badge 
                :color="statusColor" 
                :label="statusLabel" 
                clickable
                @click="showStatusMenu = true"
              >
                <q-menu v-model="showStatusMenu">
                  <q-list style="min-width: 150px">
                    <q-item 
                      v-for="status in statusOptions" 
                      :key="status.value"
                      clickable 
                      v-close-popup
                      @click="changeStatus(status.value)"
                      :active="question.status === status.value"
                    >
                      <q-item-section>
                        <q-item-label>{{ status.label }}</q-item-label>
                      </q-item-section>
                      <q-item-section side>
                        <q-icon 
                          v-if="question.status === status.value" 
                          name="check" 
                          color="primary" 
                        />
                      </q-item-section>
                    </q-item>
                  </q-list>
                </q-menu>
                <q-tooltip>Click to change status</q-tooltip>
              </q-badge>
              <q-badge v-if="difficulty" :color="difficultyColor" :label="difficulty" outline />
            </div>
            
            <!-- Actions -->
            <div class="row q-gutter-xs">
              <q-btn
                flat
                round
                dense
                icon="edit"
                size="sm"
                color="primary"
                @click="$emit('edit', question)"
              >
                <q-tooltip>Edit Question</q-tooltip>
              </q-btn>
              
              <q-btn
                flat
                round
                dense
                icon="content_copy"
                size="sm"
                color="secondary"
                @click="$emit('duplicate', question)"
              >
                <q-tooltip>Duplicate Question</q-tooltip>
              </q-btn>
              
              <q-btn
                flat
                round
                dense
                icon="delete"
                size="sm"
                color="negative"
                @click="$emit('delete', question)"
              >
                <q-tooltip>Delete Question</q-tooltip>
              </q-btn>
            </div>
          </div>
          
          <!-- Question Text -->
          <div class="question-text text-body1 q-mb-sm" v-html="truncatedText" />
          
          <!-- Metadata -->
          <div class="row items-center q-gutter-md text-caption text-grey-7">
            <span v-if="subject">
              <q-icon name="subject" size="14px" class="q-mr-xs" />
              {{ subject }}
            </span>
            <span v-if="grade">
              <q-icon name="school" size="14px" class="q-mr-xs" />
              {{ grade }}
            </span>
            <span v-if="topic">
              <q-icon name="label" size="14px" class="q-mr-xs" />
              {{ topic }}
            </span>
            <span v-if="bloomLevel">
              <q-icon name="psychology" size="14px" class="q-mr-xs" />
              Bloom: {{ bloomLevel }}
            </span>
          </div>
          
          <!-- Analytics (if available) -->
          <div v-if="showAnalytics" class="row items-center q-gutter-md q-mt-sm text-caption">
            <span v-if="usageCount !== null" class="text-grey-7">
              <q-icon name="quiz" size="14px" class="q-mr-xs" />
              Used: {{ usageCount }} times
            </span>
            <span v-if="successRate !== null" class="text-positive">
              <q-icon name="check_circle" size="14px" class="q-mr-xs" />
              Success: {{ successRate }}%
            </span>
            <span v-if="discriminationIndex !== null" class="text-info">
              <q-icon name="analytics" size="14px" class="q-mr-xs" />
              DI: {{ discriminationIndex }}
            </span>
          </div>
        </div>
      </div>
    </q-card-section>
  </q-card>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  question: {
    type: Object,
    required: true
  },
  showAnalytics: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['edit', 'duplicate', 'delete', 'statusChange']);

// State
const showStatusMenu = ref(false);

const statusOptions = [
  { label: 'Draft', value: 'draft' },
  { label: 'Active', value: 'active' },
  { label: 'Archived', value: 'archived' },
  { label: 'Review', value: 'review' }
];

// Methods
const changeStatus = (newStatus) => {
  if (newStatus !== props.question.status) {
    emit('statusChange', props.question, newStatus);
  }
};

// Question Type
const questionType = computed(() => {
  return props.question.question_type?.name || props.question.questionType?.name || 'Unknown';
});

const typeIcon = computed(() => {
  const slug = props.question.question_type?.slug || props.question.questionType?.slug || '';
  const icons = {
    'multiple_choice': 'radio_button_checked',
    'multi_select': 'check_box',
    'true_false': 'check_circle',
    'fill_blank': 'edit',
    'short_answer': 'short_text',
    'essay': 'description',
    'matching': 'compare_arrows'
  };
  return icons[slug] || 'help_outline';
});

const typeGradient = computed(() => {
  const slug = props.question.question_type?.slug || props.question.questionType?.slug || '';
  const gradients = {
    'multiple_choice': 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
    'multi_select': 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
    'true_false': 'linear-gradient(135deg, #11998e 0%, #38ef7d 100%)',
    'fill_blank': 'linear-gradient(135deg, #fa709a 0%, #fee140 100%)',
    'short_answer': 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
    'essay': 'linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)',
    'matching': 'linear-gradient(135deg, #fa709a 0%, #fee140 100%)'
  };
  return gradients[slug] || 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
});

// Status
const statusLabel = computed(() => {
  const status = props.question.status || 'draft';
  return status.charAt(0).toUpperCase() + status.slice(1);
});

const statusColor = computed(() => {
  const colors = {
    'draft': 'grey',
    'active': 'positive',
    'archived': 'warning',
    'review': 'info'
  };
  return colors[props.question.status] || 'grey';
});

// Difficulty
const difficulty = computed(() => {
  const level = props.question.difficulty_level;
  if (!level) return null;
  const labels = {
    1: 'Very Easy',
    2: 'Easy',
    3: 'Medium',
    4: 'Hard',
    5: 'Very Hard'
  };
  return labels[level] || `Level ${level}`;
});

const difficultyColor = computed(() => {
  const level = props.question.difficulty_level;
  if (level <= 2) return 'positive';
  if (level === 3) return 'warning';
  return 'negative';
});

// Metadata
const subject = computed(() => props.question.subject?.name);
const grade = computed(() => props.question.grade?.name);
const topic = computed(() => props.question.topic?.name);
const bloomLevel = computed(() => props.question.bloom_level);

// Question Text
const truncatedText = computed(() => {
  const text = props.question.question_text || '';
  const maxLength = 200;
  
  // Strip HTML tags for length calculation
  const plainText = text.replace(/<[^>]*>/g, '');
  
  if (plainText.length <= maxLength) {
    return text;
  }
  
  // Truncate and add ellipsis
  const truncated = plainText.substring(0, maxLength) + '...';
  return truncated;
});

// Analytics
const usageCount = computed(() => props.question.usage_count);
const successRate = computed(() => {
  const rate = props.question.avg_success_rate;
  return rate !== null && rate !== undefined ? Number(rate).toFixed(1) : null;
});
const discriminationIndex = computed(() => {
  const di = props.question.discrimination_index;
  return di !== null && di !== undefined ? Number(di).toFixed(2) : null;
});
</script>

<style scoped lang="scss">
.question-card {
  transition: all 0.2s ease;
  
  &:hover {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  }
}

.question-type-icon {
  width: 56px;
  height: 56px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.question-text {
  line-height: 1.5;
  color: #1a202c;
  
  // Handle HTML content
  :deep(p) {
    margin: 0;
  }
  
  :deep(img) {
    max-width: 100%;
    height: auto;
  }
}
</style>
