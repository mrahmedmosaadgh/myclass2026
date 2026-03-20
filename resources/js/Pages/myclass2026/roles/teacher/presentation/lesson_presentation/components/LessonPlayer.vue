<template>
  <q-layout view="hHh lpR fFf" class="student-lesson-view mt-8">
    <!-- Global Progress Bar -->
    <q-linear-progress 
      :value="globalSlideProgress" 
      color="primary" 
      track-color="transparent"
      class="fixed-top z-top" 
      style="height: 4px; z-index: 9999;"
    />

    <!-- Sidebar -->
    <PlayerSidebar
      v-model="showSectionsDrawer"
      :sections="sections"
      :current-section="currentSection"
      :slides="slides"
      :progress="progress"
      :can-access-section="canAccessSection"
      :is-section-completed="isSectionCompleted"
      @section-select="handleSectionChange"
    />

    <!-- Main Content -->
    <q-page-container>
      <q-page class="student-lesson-page">
        <!-- Compact Header -->
        <div class="compact-header">
          <div class="row items-center q-pa-sm q-px-md">
            <q-btn
              flat
              dense
              round
              icon="menu"
              color="primary"
              @click="showSectionsDrawer = !showSectionsDrawer"
              class="q-mr-sm"
            />
            
            <div class="col ellipsis">
              <div class="text-subtitle1 text-weight-bold ellipsis">{{ presentation.name }}</div>
            </div>

            <q-btn
              flat
              dense
              round
              :icon="viewMode === 'slide' ? 'view_agenda' : 'view_carousel'"
              color="primary"
              @click="() => { viewMode = viewMode === 'slide' ? 'scroll' : 'slide'; playClick(); }"
              class="q-mr-sm"
            >
              <q-tooltip>{{ viewMode === 'slide' ? 'Switch to Scroll View' : 'Switch to Slide View' }}</q-tooltip>
            </q-btn>

            <q-btn
              unelevated
              color="primary"
              icon="fullscreen"
              label="Present"
              @click="() => { openFullscreenDialog(); playClick(); }"
              class="q-px-md"
            >
              <q-tooltip>Enter Fullscreen Presentation Mode</q-tooltip>
            </q-btn>

            <q-btn
              flat
              dense
              round
              icon="event_note"
              color="warning"
              @click="goToRewardSystem"
              class="q-ml-sm"
            >
              <q-tooltip>Teacher Schedule</q-tooltip>
            </q-btn>
          </div>
        </div>

        <!-- Regular Content Area -->
        <div class="content-wrapper q-pa-md">
          <div class="content-container">
            <!-- Loading State -->
            <div v-if="loading" class="flex flex-center" style="min-height: 400px;">
              <div class="text-center">
                <q-spinner-dots color="primary" size="50px" />
                <div class="text-grey-7 q-mt-md">Loading lesson...</div>
              </div>
            </div>

            <!-- Locked State -->
            <div v-else-if="!isPreview && progress?.status === 'locked'" class="locked-state">
              <q-card flat bordered class="text-center q-pa-xl">
                <q-icon name="lock" size="80px" color="grey-4" class="q-mb-md" />
                <div class="text-h5 text-weight-bold q-mb-sm">Lesson Locked</div>
                <div class="text-grey-7">
                  This lesson has not been opened by your teacher yet.
                </div>
              </q-card>
            </div>

            <!-- Main Content -->
            <div v-else>
              <!-- Section Banner -->
              <SectionBanner
                :section="currentSection_data"
                :current-slide="currentSlideIndex"
                :total-slides="currentSectionSlides.length"
                class="q-mb-md"
              />

              <!-- Slide Content Card -->
              <div v-if="currentSectionSlides.length > 0 && !showSpecialContent">
                <!-- Slide Mode -->
                <q-card v-if="viewMode === 'slide'" flat bordered class="slide-card">
                  <q-card-section class="q-pa-lg">
                    <div class="row items-center justify-between q-mb-md">
                      <q-badge color="grey-4" text-color="grey-8" class="q-px-md q-py-sm">
                        <q-icon name="article" size="16px" class="q-mr-xs" />
                        Slide {{ currentSlideIndex + 1 }} of {{ currentSectionSlides.length }}
                      </q-badge>
                      
                      <q-chip
                        :icon="getSlideTypeIcon(currentSlide.slide_type)"
                        :color="getSlideTypeColor(currentSlide.slide_type)"
                        text-color="white"
                        class="text-weight-bold"
                      >
                        {{ currentSlide.slide_type?.toUpperCase() }}
                      </q-chip>
                    </div>

                    <SlideRenderer
                      :slide="currentSlide"
                      :attempt-id="generateAttemptId()"
                      :legacy-mode="currentSection"
                      @answer-selected="handleAnswerSelected"
                      @quiz-completed="handleQuizCompleted"
                      class="q-mt-lg"
                    />
                  </q-card-section>

                  <NavigationFooter
                    :current-slide="currentSlideIndex"
                    :total-slides="currentSectionSlides.length"
                    :can-proceed="isSlideCompleted"
                    :is-last-slide="currentSlideIndex >= currentSectionSlides.length - 1"
                    @prev="prevSlide"
                    @next="nextSlide"
                  />
                </q-card>

                <!-- Scroll Mode -->
                <div v-else class="scroll-mode-container">
                  <q-card v-for="(slide, index) in currentSectionSlides" :key="slide.id || index" flat bordered class="slide-card q-mb-lg">
                    <q-card-section class="q-pa-lg">
                      <div class="row items-center justify-between q-mb-md">
                        <q-badge color="grey-4" text-color="grey-8" class="q-px-md q-py-sm">
                          <q-icon name="article" size="16px" class="q-mr-xs" />
                          Slide {{ index + 1 }} of {{ currentSectionSlides.length }}
                        </q-badge>
                        
                        <q-chip
                          :icon="getSlideTypeIcon(slide.slide_type)"
                          :color="getSlideTypeColor(slide.slide_type)"
                          text-color="white"
                          class="text-weight-bold"
                        >
                          {{ slide.slide_type?.toUpperCase() }}
                        </q-chip>
                      </div>

                      <SlideRenderer
                        :slide="slide"
                        :attempt-id="`attempt_${presentation.id}_${slide.id || index}_${Date.now()}`"
                        :legacy-mode="currentSection"
                        @answer-selected="handleAnswerSelected"
                        @quiz-completed="(result) => handleScrollQuizCompleted(result, index)"
                        class="q-mt-lg"
                      />
                    </q-card-section>
                  </q-card>

                  <!-- Navigation Buttons (Scroll Mode) -->
                  <div class="row justify-center q-gutter-md q-pb-xl">
                      <q-btn
                        unelevated
                        outline
                        color="primary"
                        icon="arrow_back"
                        label="Previous Section"
                        :disable="sections.length > 0 && currentSection === sections[0].id"
                        @click="() => { prevSectionFromScroll(); playClick(); }"
                        size="lg"
                        class="q-px-xl"
                      />

                      <q-btn
                        unelevated
                        color="primary"
                        icon-right="arrow_forward"
                        label="Next Section"
                        :disable="sections.length > 0 && currentSection === sections[sections.length - 1].id"
                        @click="() => { nextSectionFromScroll(); playClick(); }"
                        size="lg"
                        class="q-px-xl"
                      />
                  </div>
                </div>
              </div>

              <!-- Special Content -->
              <div v-else-if="showSpecialContent || currentSectionSlides.length === 0">
                <q-card v-if="currentSection === 'practice'" flat bordered class="q-pa-lg">
                  <UniversalQuestionPlayer
                    v-if="progress || isPreview"
                    :question="{ type: 'upload_draw', text: 'Practice Submission', points: 0 }"
                    mode="practice"
                    @submitted="onPracticeSubmitted"
                  />
                </q-card>

                <q-card v-else-if="currentSection === 'quiz'" flat bordered class="text-center q-pa-xl">
                  <q-avatar size="100px" color="positive" text-color="white" class="q-mb-md">
                    <q-icon name="quiz" size="50px" />
                  </q-avatar>
                  
                  <div class="text-h4 text-weight-bold q-mb-sm">Quiz Time!</div>
                  <div class="text-subtitle1 text-grey-7 q-mb-lg">Ready to test your knowledge?</div>

                  <q-card v-if="quizInfo" flat bordered class="q-pa-lg q-mb-lg" style="max-width: 500px; margin: 0 auto;">
                    <div class="text-h6 text-weight-bold q-mb-sm">{{ quizInfo.name }}</div>
                    <div class="text-grey-7 q-mb-md">{{ quizInfo.description }}</div>
                    
                    <div class="row q-gutter-md justify-center">
                      <q-chip icon="help" color="primary" text-color="white">
                        {{ quizInfo.questions_count }} Questions
                      </q-chip>
                      <q-chip v-if="quizInfo.time_limit_minutes" icon="timer" color="orange" text-color="white">
                        {{ quizInfo.time_limit_minutes }} Minutes
                      </q-chip>
                    </div>
                  </q-card>

                  <q-btn
                    v-if="quizInfo"
                    unelevated
                    color="positive"
                    icon="play_arrow"
                    label="Start Quiz"
                    @click="startQuiz"
                    size="lg"
                    class="q-px-xl q-py-sm"
                  />
                </q-card>

                <q-card v-else flat bordered class="text-center q-pa-xl">
                  <q-icon name="folder_open" size="80px" color="grey-4" class="q-mb-md" />
                  <div class="text-h6 text-grey-7">No content available</div>
                </q-card>
              </div>
            </div>
          </div>
          
          <!-- Place QPageScroller at end of page -->
          <q-page-scroller position="bottom-right" :scroll-offset="150" :offset="[18, 18]">
            <q-btn fab icon="keyboard_arrow_up" color="accent">
              <q-tooltip>Scroll to top</q-tooltip>
            </q-btn>
          </q-page-scroller>
        </div>
      </q-page>
    </q-page-container>

    <!-- Fullscreen Presentation Dialog -->
    <q-dialog v-model="showFullscreenDialog" maximized>
      <div class="fullscreen-presentation">
        <!-- Loading State -->
        <div v-if="loading" class="flex flex-center" style="height: 100vh;">
          <div class="text-center">
            <q-spinner-dots color="white" size="50px" />
            <div class="text-white q-mt-md">Loading...</div>
          </div>
        </div>

        <!-- Slide Content -->
        <div v-else-if="currentSectionSlides.length > 0" class="slide-wrapper">
          <div class="slide-content-area" ref="slideContentAreaRef" @scroll="handleContentScroll">
            <SlideRenderer
              :slide="currentSlide"
              :attempt-id="generateAttemptId()"
              :legacy-mode="currentSection"
              @answer-selected="handleAnswerSelected"
              @quiz-completed="handleQuizCompleted"
            />
            
            <!-- Scroll to Top FAB -->
            <q-page-sticky position="bottom-right" :offset="[18, 18]" v-if="viewMode === 'scroll'">
              <transition name="scale">
                <q-btn
                  v-show="showScrollTopBtn"
                  fab
                  icon="keyboard_arrow_up"
                  color="secondary"
                  @click="() => { scrollToTop(); playClick(); }"
                >
                  <q-tooltip>Back to Top</q-tooltip>
                </q-btn>
              </transition>
            </q-page-sticky>
          </div>

          <!-- Floating Navigation Arrows -->
          <div class="floating-nav">
            <q-btn
              v-if="currentSlideIndex > 0"
              fab
              color="primary"
              icon="chevron_left"
              @click="() => { prevSlide(); playClick(); }"
              class="nav-arrow nav-arrow-left"
            >
              <q-tooltip>Previous</q-tooltip>
            </q-btn>

            <q-btn
              v-if="isSlideCompleted"
              fab
              :color="currentSlideIndex < currentSectionSlides.length - 1 ? 'primary' : 'positive'"
              :icon="currentSlideIndex < currentSectionSlides.length - 1 ? 'chevron_right' : 'check_circle'"
              @click="() => { nextSlide(); playClick(); }"
              class="nav-arrow nav-arrow-right"
            >
              <q-tooltip>{{ currentSlideIndex < currentSectionSlides.length - 1 ? 'Next' : 'Complete' }}</q-tooltip>
            </q-btn>
          </div>

          <!-- Floating Info Bar -->
          <div class="floating-info">
            <q-chip color="rgba(0,0,0,0.7)" text-color="white" class="q-px-md">
              <q-icon :name="getSlideTypeIcon(currentSlide.slide_type)" size="18px" class="q-mr-xs" />
              {{ currentSlideIndex + 1 }} / {{ currentSectionSlides.length }}
            </q-chip>
            
            <q-chip v-if="currentSection_data" :style="{ background: currentSection_data.bg, color: currentSection_data.textColor }" class="q-ml-sm q-px-md">
              <q-icon :name="currentSection_data.qIcon || currentSection_data.icon" size="18px" class="q-mr-xs" />
              {{ currentSection_data.title }}
            </q-chip>
          </div>

          <!-- Section Menu Button -->
          <q-btn
            flat
            dense
            round
            icon="list"
            color="white"
            @click="showSectionMenu = !showSectionMenu"
            class="section-menu-btn"
          >
            <q-tooltip>Sections</q-tooltip>
          </q-btn>

          <!-- Section Menu Overlay -->
          <transition name="slide-fade">
            <div v-if="showSectionMenu" class="section-menu-overlay">
              <div class="section-menu-header">
                <div class="text-h6 text-white">Sections</div>
                <q-btn flat dense round icon="close" color="white" @click="showSectionMenu = false" />
              </div>
              
              <q-scroll-area class="section-menu-list">
                <q-list dark>
                  <q-item
                    v-for="section in sections"
                    :key="section.id"
                    clickable
                    :active="currentSection === section.id"
                    :disable="!canAccessSection(section.id)"
                    @click="jumpToSection(section.id)"
                    class="section-menu-item"
                  >
                    <q-item-section avatar>
                      <q-avatar :style="{ background: section.bg, color: section.textColor }">
                        <q-icon :name="section.qIcon || section.icon" />
                      </q-avatar>
                    </q-item-section>
                    
                    <q-item-section>
                      <q-item-label class="text-weight-medium">{{ section.title }}</q-item-label>
                      <q-item-label caption class="text-grey-4">
                        {{ getSectionSlideCount(section.id) }} slides
                      </q-item-label>
                    </q-item-section>
                    
                    <q-item-section side>
                      <q-icon
                        v-if="isSectionCompleted(section.id)"
                        name="check_circle"
                        color="positive"
                        size="24px"
                      />
                      <q-icon
                        v-else-if="!canAccessSection(section.id)"
                        name="lock"
                        color="grey-5"
                        size="20px"
                      />
                    </q-item-section>
                  </q-item>
                </q-list>
              </q-scroll-area>
            </div>
          </transition>

          <!-- Exit Button -->
          <q-btn
            flat
            dense
            round
            icon="close"
            color="white"
            @click="() => { showFullscreenDialog = false; playClick(); }"
            class="exit-fullscreen-btn"
          >
            <q-tooltip>Exit Presentation</q-tooltip>
          </q-btn>

          <!-- Slide Annotation Toggle Button -->
          <q-btn
            flat
            dense
            round
            :icon="showSlideAnnotations ? 'edit_off' : 'edit_note'"
            :color="showSlideAnnotations ? 'deep-orange' : 'white'"
            @click="() => { toggleSlideAnnotations(); playClick(); }"
            class="slide-annotation-btn"
          >
            <q-tooltip>{{ showSlideAnnotations ? 'Hide Slide Annotations' : 'Show Slide Annotations' }}</q-tooltip>
          </q-btn>
        </div>

        <!-- No Content State -->
        <div v-else class="flex flex-center" style="height: 100vh;">
          <div class="text-center">
            <q-icon name="folder_open" size="80px" color="white" class="q-mb-md" />
            <div class="text-h6 text-white">No slides available in this section</div>
            <q-btn
              unelevated
              color="white"
              text-color="black"
              label="Close"
              @click="showFullscreenDialog = false"
              class="q-mt-lg"
            />
          </div>
        </div>
      </div>

      <!-- Drawing Overlay (outside main content, true overlay) -->
      <DrawingOverlay v-if="showFullscreenDialog" ref="drawingOverlayRef" @drawing-state-changed="handleDrawingStateChanged" />

      <SlideAnnotationOverlay 
        v-if="showFullscreenDialog && showSlideAnnotations" 
        :current-slide-id="getCurrentSlideId()"
        :current-section="currentSection"
        :current-slide-index="currentSlideIndex"
        @close="showSlideAnnotations = false"
      />
    </q-dialog>

    <div v-show="showRewardDialog" class="fixed-full column no-wrap" style="background: #f5f7fa; z-index: 6000;">
        <q-toolbar class="bg-white text-dark shadow-1 q-py-sm" style="z-index: 10;">
          <q-btn flat round dense icon="close" @click="showRewardDialog = false" color="grey-7" size="lg" />
          <q-toolbar-title class="text-weight-bold text-h6 text-grey-9 flex items-center">
            <q-icon name="event_note" color="warning" size="md" class="q-mr-sm" />
            Teacher Schedule
          </q-toolbar-title>
          <q-btn flat round dense icon="minimize" @click="minimizeSchedule" color="grey-7" size="lg">
             <q-tooltip>Keep Active (Minimize)</q-tooltip>
          </q-btn>
        </q-toolbar>

        <q-card-section class="col q-pa-none relative-position">
          <iframe 
            :src="rewardSystemUrl" 
            style="width: 100%; height: 100%; border: none;"
            title="Reward System"
          ></iframe>
        </q-card-section>
    </div>

    <!-- Classroom Selection Dialog -->
    <q-dialog v-model="showClassroomSelectDialog">
      <q-card style="min-width: 400px">
        <q-card-section>
          <div class="text-h6">Select Classroom</div>
          <div class="text-caption text-grey">Choose a classroom to link with the Reward System</div>
        </q-card-section>

        <q-card-section class="q-pt-none">
          <div v-if="isFetchingClassrooms" class="flex flex-center q-pa-md">
            <q-spinner color="primary" size="2em" />
          </div>
          
          <q-list v-else bordered separator class="rounded-borders">
            <q-item v-if="classrooms.length === 0" class="text-center q-pa-md">
               <q-item-section class="text-grey">No classrooms found.</q-item-section>
            </q-item>
            
            <q-item 
              v-for="cls in classrooms" 
              :key="cls.id" 
              clickable 
              v-ripple 
              @click="selectClassroom(cls)"
              class="hover:bg-gray-100"
            >
              <q-item-section avatar>
                <q-avatar color="primary" text-color="white" icon="school" />
              </q-item-section>
              <q-item-section>
                <q-item-label class="text-weight-bold">{{ cls.name || `Class ${cls.id}` }}</q-item-label>
                <q-item-label caption>{{ cls.grade_name || '' }} {{ cls.subject_name ? '• ' + cls.subject_name : '' }}</q-item-label>
              </q-item-section>
              <q-item-section side>
                <q-icon name="chevron_right" color="grey" />
              </q-item-section>
            </q-item>
          </q-list>
        </q-card-section>

        <q-card-actions align="right" class="bg-grey-1">
          <q-btn flat label="Cancel" color="grey" v-close-popup />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Minimized Schedule FAB -->
    <q-page-sticky position="bottom-left" :offset="[18, 50]" v-if="isScheduleMinimized" style="z-index: 5000;">
      <q-fab
        color="warning"
        icon="event_note"
        direction="up"
        padding="sm"
        @click="restoreSchedule"
      >
        <q-tooltip anchor="center right" self="center left" :offset="[10, 10]">
          Restore Teacher Schedule
        </q-tooltip>
      </q-fab>
    </q-page-sticky>
  </q-layout>
</template>

<script setup>
import { ref, computed, watch, onMounted, nextTick } from 'vue';
import { useQuasar } from 'quasar';
import axios from 'axios';
import UniversalQuestionPlayer from '@/Components/QuestionSystem/UniversalQuestionPlayer.vue';
import PlayerSidebar from './LessonPlayerComp/PlayerSidebar.vue';
import SectionBanner from './LessonPlayerComp/SectionBanner.vue';
import SlideRenderer from './LessonPlayerComp/SlideRenderer.vue';
import NavigationFooter from './LessonPlayerComp/NavigationFooter.vue';
import DrawingOverlay from './LessonPlayerComp/DrawingOverlay.vue';
import SlideAnnotationOverlay from './LessonPlayerComp/SlideAnnotationOverlay.vue';
import { playClick } from '@/Utils/audio';

const $q = useQuasar();

const props = defineProps({
  presentation: { type: Object, default: () => ({}) },
  sections: { type: Array, default: () => [] },
  slides: { type: Array, default: () => [] },
  progress: { type: Object, default: null },
  loading: { type: Boolean, default: false },
  isPreview: { type: Boolean, default: false }
});

const emit = defineEmits(['complete-learn', 'submit-practice']);

// State
const currentSection = ref('learn');
const currentSection_data = ref(null);
const currentSlideIndex = ref(0);
const showSectionsDrawer = ref(true);
const showSpecialContent = ref(false);
const questionSolved = ref({});
const quizInfo = ref(null);
const showFullscreenDialog = ref(false);
const showSectionMenu = ref(false);
const drawingOverlayRef = ref(null);
const showSlideAnnotations = ref(false);
const viewMode = ref('scroll'); // 'slide' or 'scroll'

// Computed
const currentSectionSlides = computed(() => {
  return props.slides.filter(s => s.section === currentSection.value);
});

const currentSlide = computed(() => currentSectionSlides.value[currentSlideIndex.value] || {});

const isSlideCompleted = computed(() => {
  if (props.isPreview) return true;
  if (currentSlide.value?.slide_type !== 'question') return true;
  const questions = currentSlide.value.slide_content?.questions || [];
  return questions.every(q => questionSolved.value[q.id]);
});

const overallProgress = computed(() => {
  if (!props.progress) return 0;
  let completed = 0;
  let total = props.sections.length;
  
  if (props.progress.learn_completed_at) completed++;
  if (props.progress.practice_submitted_at) completed++;
  if (props.progress.quiz_passed) completed++;
  
  return (completed / total) * 100;
});

const globalSlideProgress = computed(() => {
  if (!props.slides || props.slides.length === 0) return 0;
  
  if (showSpecialContent.value) {
     return 1.0; 
  }

  const currentS = currentSlide.value;
  if (!currentS || !currentS.id) return 0;
  
  const index = props.slides.findIndex(s => s.id === currentS.id);
  if (index === -1) return 0;
  
  return (index + 1) / props.slides.length;
});

const canAccessPractice = computed(() => {
  if (props.isPreview) return true;
  if (!props.progress) return false;
  return props.progress.learn_completed_at || props.progress.status !== 'opened';
});

const canAccessQuiz = computed(() => {
  if (props.isPreview) return true;
  if (!props.progress) return false;
  return props.progress.status === 'quiz_unlocked' || props.progress.status === 'completed' || props.progress.status === 'failed';
});

const canAccessSection = (sectionId) => {
  if (props.isPreview) return true;
  if (['learn', 'objectives', 'warmup', 'homework'].includes(sectionId)) return true;
  if (sectionId === 'practice') return canAccessPractice.value;
  if (sectionId === 'quiz') return canAccessQuiz.value;
  return true;
};

const isSectionCompleted = (sectionId) => {
  if (!props.progress) return false;
  if (sectionId === 'learn') return !!props.progress.learn_completed_at;
  if (sectionId === 'practice') return !!props.progress.practice_submitted_at;
  if (sectionId === 'quiz') return !!props.progress.quiz_passed;
  return false;
};

// Methods
const openFullscreenDialog = () => {
  showFullscreenDialog.value = true;
};

const getSlideTypeIcon = (type) => {
  const icons = {
    'text': 'description', 'image': 'image', 'video': 'videocam',
    'audio': 'audiotrack', 'pdf': 'picture_as_pdf', 'question': 'quiz', 'drawing': 'brush'
  };
  return icons[type] || 'article';
};

const getSlideTypeColor = (type) => {
  const colors = {
    'text': 'blue-6', 'image': 'purple-6', 'video': 'red-6',
    'audio': 'orange-6', 'pdf': 'red-8', 'question': 'green-6', 'drawing': 'pink-6'
  };
  return colors[type] || 'grey-6';
};

const getSectionSlideCount = (sectionId) => {
  return props.slides.filter(s => s.section === sectionId).length;
};

// Unified scroll to top function
// Unified scroll to top function
const showRewardDialog = ref(false);
const showClassroomSelectDialog = ref(false);
const isFetchingClassrooms = ref(false);
const classrooms = ref([]);
const rewardSystemUrl = ref('/schedules/my-schedule');
const isScheduleMinimized = ref(false);

const goToRewardSystem = () => {
  // Directly load the teacher schedule as requested
  rewardSystemUrl.value = '/schedules/my-schedule';
  showRewardDialog.value = true;
  isScheduleMinimized.value = false;
};

const minimizeSchedule = () => {
  showRewardDialog.value = false;
  isScheduleMinimized.value = true;
};

const restoreSchedule = () => {
  showRewardDialog.value = true;
  isScheduleMinimized.value = false;
};

// const goToRewardSystem_BACKUP = async () => { ... } // Removing backup to keep clean
/*
const selectClassroom = (classroom) => {
  localStorage.setItem('selected_classroom_id', classroom.id);
  showClassroomSelectDialog.value = false;
  rewardSystemUrl.value = `/schedules/my-schedule?classroom_id=${classroom.id}`;
  showRewardDialog.value = true;
};
*/
// Keeping selectClassroom helper but not used by default button action anymore. 
// Actually I'll just remove the complexity if it's not needed.

/*
// Previous classroom selection logic removed as per instruction to load "all teacher schedule" directly
const goToRewardSystem = async () => {
  const savedClassroomId = localStorage.getItem('selected_classroom_id');
  
  if (savedClassroomId) {
    rewardSystemUrl.value = `/schedules/my-schedule?classroom_id=${savedClassroomId}`;
    showRewardDialog.value = true;
  } else {
    // ...
  }
};
*/

/*
const selectClassroom = (classroom) => {
  localStorage.setItem('selected_classroom_id', classroom.id);
  showClassroomSelectDialog.value = false;
  rewardSystemUrl.value = `/schedules/my-schedule?classroom_id=${classroom.id}`;
  showRewardDialog.value = true;
};
*/

const scrollToTop = () => {
  nextTick(() => {
    // Priority 1: Window scroll (standard Quasar layout)
    window.scrollTo({ top: 0, behavior: 'smooth' });
    
    // Priority 2: Try specific containers just in case we are in a nested context
    const slideContentArea = document.querySelector('.slide-content-area');
    if (slideContentArea) {
      slideContentArea.scrollTo({ top: 0, behavior: 'smooth' });
    }

    const contentWrapper = document.querySelector('.content-wrapper');
    if (contentWrapper) {
      contentWrapper.scrollTo({ top: 0, behavior: 'smooth' });
    }
  });
};

const jumpToSection = (sectionId) => {
  if (canAccessSection(sectionId)) {
    currentSection.value = sectionId;
    currentSlideIndex.value = 0;
    const section = props.sections.find(s => s.id === sectionId);
    currentSection_data.value = section;
    showSectionMenu.value = false;
    
    $q.notify({
      type: 'positive',
      message: `Jumped to ${section.title}`,
      icon: 'check',
      position: 'top',
      timeout: 1500
    });
    
    // Scroll to top when jumping to section
    scrollToTop();
  } else {
    $q.notify({
      type: 'warning',
      message: 'This section is locked',
      position: 'top'
    });
  }
};

const handleSectionChange = (sectionId) => {
  if (canAccessSection(sectionId)) {
    currentSection.value = sectionId;
    const section = props.sections.find(s => s.id === sectionId);
    currentSection_data.value = section;
    
    // Scroll to top when changing sections
    scrollToTop();
  } else {
    let message = 'You cannot access this section yet.';
    if (sectionId === 'practice') message = 'Please complete the Learn section first.';
    else if (sectionId === 'quiz') message = 'Please complete the Practice section first.';
    
    $q.notify({ type: 'warning', message, position: 'top' });
  }
};

const nextSlide = () => {
  if (!isSlideCompleted.value) {
    $q.notify({ type: 'warning', message: 'Please complete all questions before proceeding.' });
    return;
  }
  
  if (currentSlideIndex.value < currentSectionSlides.value.length - 1) {
    // Move to next slide in current section
    currentSlideIndex.value++;
  } else {
    // At the end of current section, try to move to next section
    const currentSectionIdx = props.sections.findIndex(s => s.id === currentSection.value);
    const nextSection = props.sections[currentSectionIdx + 1];
    
    if (nextSection && canAccessSection(nextSection.id)) {
      // Move to next section
      currentSection.value = nextSection.id;
      currentSlideIndex.value = 0;
      currentSection_data.value = nextSection;
      
      $q.notify({
        type: 'positive',
        message: `Moving to ${nextSection.title}`,
        icon: 'arrow_forward',
        position: 'top',
        timeout: 1500
      });
      
      // Scroll to top when moving to next section
      scrollToTop();
    } else {
      // No more sections or section is locked
      handleSectionCompletion();
    }
  }
};

const prevSlide = () => {
  if (currentSlideIndex.value > 0) {
    // Move to previous slide in current section
    currentSlideIndex.value--;
  } else {
    // At the beginning of current section, try to move to previous section
    const currentSectionIdx = props.sections.findIndex(s => s.id === currentSection.value);
    const prevSection = props.sections[currentSectionIdx - 1];
    
    if (prevSection && canAccessSection(prevSection.id)) {
      // Move to previous section's last slide
      currentSection.value = prevSection.id;
      currentSection_data.value = prevSection;
      
      // Get slides for previous section and go to last one
      const prevSectionSlides = props.slides.filter(s => s.section === prevSection.id);
      currentSlideIndex.value = Math.max(0, prevSectionSlides.length - 1);
      
      $q.notify({
        type: 'info',
        message: `Back to ${prevSection.title}`,
        icon: 'arrow_back',
        position: 'top',
        timeout: 1500
      });
      
      // Scroll to top when moving to previous section
      scrollToTop();
    }
  }
};

const handleSectionCompletion = () => {
  if (currentSection.value === 'learn') {
    if (props.isPreview) {
      $q.notify({ type: 'positive', message: '[Preview] Learn section completed', icon: 'celebration' });
      currentSection.value = 'practice';
    } else {
      emit('complete-learn');
    }
  } else if (currentSection.value === 'practice') {
    showSpecialContent.value = true;
  } else if (currentSection.value === 'quiz') {
    showSpecialContent.value = true;
  } else {
    $q.notify({ type: 'positive', message: 'Section completed!', icon: 'celebration' });
  }
};


const slideContentAreaRef = ref(null);
const showScrollTopBtn = ref(false);

const handleContentScroll = (e) => {
  showScrollTopBtn.value = e.target.scrollTop > 300;
};

const prevSectionFromScroll = () => {
    const currentSectionIdx = props.sections.findIndex(s => s.id === currentSection.value);
    const prevSection = props.sections[currentSectionIdx - 1];
    
    if (prevSection && canAccessSection(prevSection.id)) {
      currentSection.value = prevSection.id;
      currentSection_data.value = prevSection;
      
      $q.notify({
        type: 'info',
        message: `Back to ${prevSection.title}`,
        icon: 'arrow_back',
        position: 'top',
        timeout: 1500
      });
      
      // Scroll to top of the content area
      scrollToTop();
    }
};

const nextSectionFromScroll = () => {
    // Check if we can proceed (e.g. check questions if strict mode, but for scroll view typically we allow free flow or check all)
    // For now, let's assume scroll view is meaningful for reading/reviewing.
    // If strict completion is required, we should check `isSlideCompleted` for all questions.
    


    const currentSectionIdx = props.sections.findIndex(s => s.id === currentSection.value);
    const nextSection = props.sections[currentSectionIdx + 1];
    
    if (nextSection && canAccessSection(nextSection.id)) {
      currentSection.value = nextSection.id;
      currentSlideIndex.value = 0;
      currentSection_data.value = nextSection;
      
      $q.notify({
        type: 'positive',
        message: `Moving to ${nextSection.title}`,
        icon: 'arrow_forward',
        position: 'top',
        timeout: 1500
      });
      
      // Scroll to top of the content area
      scrollToTop();
    } else {
      handleSectionCompletion();
    }
};

const handleScrollQuizCompleted = (result, index) => {
    // Just mark as solved, no auto-advance in scroll mode
    handleQuizCompleted(result); // Re-use logic to mark solved
    // But prevent the auto-next-slide timeout if in scroll mode?
    // Actually handleQuizCompleted calls nextSlide(); which changes currentSlideIndex.
    // In scroll mode, changing currentSlideIndex might not be what we want, or it might be fine (it doesn't affect view much).
    // Let's refactor handleQuizCompleted to check viewMode.
};



const generateAttemptId = () => {
  return `attempt_${props.presentation.id}_${currentSlide.value.id || currentSlideIndex.value}_${Date.now()}`;
};

const handleAnswerSelected = async (record) => {
  if (props.isPreview) return;
};

const handleQuizCompleted = async (result) => {
  if (props.isPreview) {
    $q.notify({
      type: 'positive',
      message: `[Preview] Quiz completed! Score: ${result.correct}/${result.total}`,
      position: 'top'
    });
  } else {
    $q.notify({
      type: 'positive',
      message: `Quiz completed! Score: ${result.correct}/${result.total}`,
      icon: 'celebration',
      position: 'top'
    });
  }

  // Mark all questions as solved so we can proceed
  const questions = currentSlide.value.slide_content?.questions || [];
  questions.forEach(q => {
    questionSolved.value[q.id] = true;
  });

  // Automatically proceed to next slide after a short delay ONLY in slide mode
  if (viewMode.value === 'slide') {
      setTimeout(() => {
        nextSlide();
      }, 1500);
  } else {
      $q.notify({
        type: 'positive',
        message: 'Question Completed!',
        position: 'top',
        timeout: 1000
      });
  }
};

const fetchQuizInfo = async () => {
  if (!props.presentation.quiz_id) return;
  try {
    const response = await axios.get(`/api/quizzes/${props.presentation.quiz_id}`);
    quizInfo.value = response.data;
  } catch (error) {
    console.error('Failed to fetch quiz info:', error);
  }
};

const startQuiz = () => {
  $q.notify({ type: 'info', message: 'Quiz engine integration coming soon!' });
};

const handleDrawingStateChanged = (isActive) => {
  // Can handle drawing state changes if needed
  console.log('Drawing mode:', isActive ? 'active' : 'inactive');
};

const toggleSlideAnnotations = () => {
  showSlideAnnotations.value = !showSlideAnnotations.value;
};

const getCurrentSlideId = () => {
  const slide = currentSlide.value;
  return slide?.id || `${currentSection.value}-${currentSlideIndex.value}`;
};

// Watchers
watch(currentSection, () => {
  currentSlideIndex.value = 0;
  showSpecialContent.value = false;
  const section = props.sections.find(s => s.id === currentSection.value);
  currentSection_data.value = section;
});

// Initialize
onMounted(() => {
  // Set initial section based on progress or default to first section
  if (props.progress) {
    if (canAccessQuiz.value) currentSection.value = 'quiz';
    else if (canAccessPractice.value && !props.progress.practice_submitted_at) currentSection.value = 'practice';
    else if (props.sections.length > 0) currentSection.value = props.sections[0].id;
  } else if (props.sections.length > 0) {
    // No progress, start with first section
    currentSection.value = props.sections[0].id;
  }
  
  // Set section data
  const section = props.sections.find(s => s.id === currentSection.value);
  currentSection_data.value = section;
  
  // Reset to first slide
  currentSlideIndex.value = 0;
  
  if (props.presentation.quiz_id) fetchQuizInfo();
});

watch(() => props.presentation.quiz_id, (newQuizId) => {
  if (newQuizId) fetchQuizInfo();
  else quizInfo.value = null;
});
</script>

<style scoped lang="scss">
.student-lesson-view {
  background: #f5f7fa;
}

.compact-header {
  background: white;
  border-bottom: 1px solid #e0e0e0;
}

.content-wrapper {
  max-width: 1200px;
  margin: 0 auto;
}

.slide-card {
  border-radius: 16px;
  box-shadow: 0 2px 12px rgba(0,0,0,0.08);
  min-height: 500px;
  max-height: calc(100vh - 200px);
  overflow-y: auto;
}

/* Custom scrollbar for slide cards */
.slide-card::-webkit-scrollbar {
  width: 8px;
}

.slide-card::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 4px;
}

.slide-card::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 4px;
}

.slide-card::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}

.locked-state {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 400px;
}

/* Fullscreen Dialog Styles */
.fullscreen-presentation {
  width: 100vw;
  height: 100vh;
  background: #000;
  position: relative;
}

.slide-wrapper {
  position: relative;
  height: 100%;
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.slide-content-area {
  width: 100%;
  max-width: 1400px;
  margin: 0 auto;
  padding: 60px;
  background: white;
  border-radius: 16px;
  box-shadow: 0 8px 32px rgba(0,0,0,0.3);
  overflow-y: auto;
  overflow-x: hidden;
  max-height: calc(100vh - 120px);
}

/* Custom scrollbar for slide content */
.slide-content-area::-webkit-scrollbar {
  width: 10px;
}

.slide-content-area::-webkit-scrollbar-track {
  background: rgba(0, 0, 0, 0.1);
  border-radius: 5px;
}

.slide-content-area::-webkit-scrollbar-thumb {
  background: rgba(0, 0, 0, 0.3);
  border-radius: 5px;
}

.slide-content-area::-webkit-scrollbar-thumb:hover {
  background: rgba(0, 0, 0, 0.5);
}


.floating-nav {
  position: absolute;
  top: 50%;
  left: 0;
  right: 0;
  transform: translateY(-50%);
  pointer-events: none;
  z-index: 100;
}

.nav-arrow {
  pointer-events: all;
  position: absolute;
  opacity: 0.7;
  transition: all 0.3s ease;
  
  &:hover {
    opacity: 1;
    transform: scale(1.15);
  }
  
  &.nav-arrow-left {
    left: 30px;
  }
  
  &.nav-arrow-right {
    right: 30px;
  }
}

.floating-info {
  position: absolute;
  bottom: 30px;
  left: 50%;
  transform: translateX(-50%);
  z-index: 100;
  display: flex;
  gap: 8px;
}

.exit-fullscreen-btn {
  position: absolute;
  top: 20px;
  right: 20px;
  z-index: 100;
  background: rgba(0,0,0,0.6);
  
  &:hover {
    background: rgba(0,0,0,0.8);
  }
}

.section-menu-btn {
  position: absolute;
  top: 20px;
  left: 20px;
  z-index: 100;
  background: rgba(0,0,0,0.6);
  
  &:hover {
    background: rgba(0,0,0,0.8);
  }
}

.section-menu-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 350px;
  height: 100%;
  background: rgba(0,0,0,0.95);
  z-index: 99;
  display: flex;
  flex-direction: column;
}

.section-menu-header {
  padding: 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid rgba(255,255,255,0.1);
}

.section-menu-list {
  flex: 1;
  height: calc(100vh - 80px);
}

.section-menu-item {
  border-bottom: 1px solid rgba(255,255,255,0.05);
  
  &.q-item--active {
    background: rgba(102, 126, 234, 0.3);
  }
  
  &:hover:not(.disabled) {
    background: rgba(255,255,255,0.1);
  }
}

/* Slide fade animation */
.slide-fade-enter-active {
  transition: all 0.3s ease;
}

.slide-fade-leave-active {
  transition: all 0.2s ease;
}

.slide-fade-enter-from {
  transform: translateX(-100%);
  opacity: 0;
}

.slide-fade-leave-to {
  transform: translateX(-100%);
  opacity: 0;
}

.slide-annotation-btn {
  position: absolute;
  top: 20px;
  right: 70px;
  z-index: 100;
  background: rgba(0,0,0,0.6);
  
  &:hover {
    background: rgba(0,0,0,0.8);
  }
}
</style>
