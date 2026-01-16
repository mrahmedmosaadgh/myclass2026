<template>
  <div class="slide-content">
    <!-- Text Slide -->
    <div v-if="slide.slide_type === 'text'" class="text-slide">
      <div class="prose" v-html="slide.slide_content?.text"></div>
    </div>

    <!-- PDF Slide -->
    <div v-else-if="slide.slide_type === 'pdf'" class="media-slide">
      <iframe
        v-if="slide.slide_content?.embedUrl || slide.slide_content?.pdfData"
        :src="slide.slide_content?.embedUrl || slide.slide_content?.pdfData"
        class="pdf-iframe"
        frameborder="0"
      ></iframe>
      <div v-else class="text-center q-pa-xl">
        <q-icon name="picture_as_pdf" size="80px" color="grey-4" class="q-mb-md" />
        <div class="text-grey-7">No PDF content available</div>
      </div>
    </div>

    <!-- Video Slide -->
    <div v-else-if="slide.slide_type === 'video'" class="media-slide">
      <!-- YouTube Embed -->
      <iframe
        v-if="slide.slide_content?.type === 'youtube' && slide.slide_content?.embedUrl"
        :src="slide.slide_content.embedUrl"
        class="video-iframe"
        frameborder="0"
        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
        allowfullscreen
      ></iframe>

      <!-- Google Drive Video Embed -->
      <iframe
        v-else-if="slide.slide_content?.type === 'gdrive' && slide.slide_content?.embedUrl"
        :src="slide.slide_content.embedUrl"
        class="video-iframe"
        frameborder="0"
        allow="autoplay"
        allowfullscreen
      ></iframe>

      <!-- Direct Video or Uploaded File -->
      <video
        v-else-if="slide.slide_content?.videoUrl"
        :src="slide.slide_content.videoUrl"
        controls
        controlsList="nodownload"
        preload="metadata"
        class="media-video"
      >
        Your browser does not support the video tag.
      </video>

      <div v-else class="text-center q-pa-xl">
        <q-icon name="videocam" size="80px" color="grey-4" class="q-mb-md" />
        <div class="text-grey-7">No video content available</div>
      </div>
    </div>

    <!-- Image Slide -->
    <div v-else-if="slide.slide_type === 'image'" class="media-slide">
      <img :src="slide.slide_content?.url" class="media-image" />
    </div>

    <!-- Audio Slide -->
    <div v-else-if="slide.slide_type === 'audio'" class="media-slide">
      <audio :src="slide.slide_content?.url" controls class="media-audio"></audio>
    </div>

    <!-- Drawing Slide -->
    <div v-else-if="slide.slide_type === 'drawing'">
      <FingerDrawingSlide
        :modelValue="slide.slide_content"
        @update:modelValue="$emit('update-drawing', $event)"
      />
    </div>

    <!-- Question Slide -->
    <div v-else-if="slide.slide_type === 'question'">
      <QuestionSlide
        :modelValue="slide.slide_content"
        mode="play"
        :quizConfig="{
          allowReviewMode: false,
          autoAdvance: false,
          showRationaleOnCorrect: true
        }"
        :attemptId="attemptId"
        :legacyMode="legacyMode"
        @answer-selected="$emit('answer-selected', $event)"
        @quiz-completed="$emit('quiz-completed', $event)"
      />
    </div>
  </div>
</template>

<script setup>
import QuestionSlide from '../slides/QuestionSlide.vue';
import FingerDrawingSlide from '../FingerDrawingSlide.vue';

defineProps({
  slide: {
    type: Object,
    required: true
  },
  attemptId: {
    type: String,
    default: ''
  },
  legacyMode: {
    type: String,
    default: ''
  }
});

defineEmits(['answer-selected', 'quiz-completed', 'update-drawing']);
</script>

<style scoped lang="scss">
.slide-content {
  min-height: 400px;
}

.prose {
  max-width: none;
  font-size: 1.1rem;
  line-height: 1.8;
}

.media-image,
.media-video {
  max-width: 100%;
  max-height: 500px;
  border-radius: 12px;
  display: block;
  margin: 0 auto;
}

.media-audio {
  width: 100%;
  max-width: 600px;
  display: block;
  margin: 0 auto;
}

.pdf-iframe,
.video-iframe {
  width: 100%;
  height: 600px;
  border: none;
  border-radius: 12px;
  display: block;
}
</style>
