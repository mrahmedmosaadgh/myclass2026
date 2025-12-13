<template>
  <q-card class="column full-height hover-shadow transition-generic">
    <q-card-section class="col q-pb-none">
      <div class="row items-center q-gutter-xs q-mb-sm">
        <!-- Learn Section Badge -->
        <q-badge color="blue-1" text-color="primary">
          <q-icon name="menu_book" size="xs" class="q-mr-xs" />
          {{ lesson.slides_count || 0 }}
        </q-badge>
        
        <!-- Practice Section Badge -->
        <q-badge color="purple-1" text-color="purple">
          <q-icon name="edit" size="xs" class="q-mr-xs" />
          Practice
        </q-badge>

        <!-- Quiz Section Badge -->
        <q-badge v-if="lesson.quiz_id" color="green-1" text-color="green">
          <q-icon name="quiz" size="xs" class="q-mr-xs" />
          Quiz: {{ lesson.quiz_id }}
        </q-badge>
        
        <q-space />
        
        <span class="text-caption text-grey-5">
          {{ new Date(lesson.created_at).toLocaleDateString() }}
        </span>
      </div>
      <div class="text-h6 text-grey-9 ellipsis" :title="lesson.name">
        {{ lesson.name }}
      </div>
      <div class="text-body2 text-grey-6 ellipsis-2-lines q-mt-xs">
        {{ lesson.description || 'No description provided.' }}
      </div>
    </q-card-section>
    
    <q-card-actions class="bg-grey-1 border-top-grey-3 q-px-md q-py-sm">
      <div class="column full-width q-gutter-xs">
        <!-- First Row: Edit, Delete, Preview -->
        <div class="row justify-between items-center">
          <div class="row q-gutter-xs">
            <Link :href="route('lesson-presentation.edit', { id: lesson.id })">
              <q-btn
                flat
                dense
                size="sm"
                color="grey-7"
                icon="edit"
                label="Edit"
                no-caps
                class="hover-text-primary"
              />
            </Link>
            <q-btn
              flat
              dense
              size="sm"
              color="grey-7"
              icon="delete"
              label="Delete"
              no-caps
              class="hover-text-negative"
              @click="$emit('delete', lesson)"
            />
          </div>
          
          <div class="row q-gutter-xs">
             <a
              :href="route('lesson-presentation.student.view', { id: lesson.id })"
              target="_blank"
              class="text-decoration-none"
            >
              <q-btn
                flat
                dense
                size="sm"
                color="primary"
                icon="play_arrow"
                label="Preview"
                no-caps
              />
            </a>
            <q-btn
              flat
              round
              dense
              size="sm"
              color="grey-6"
              icon="link"
              @click="$emit('copy-link', lesson.id)"
            >
              <q-tooltip>Copy Student Link</q-tooltip>
            </q-btn>
            <a
              :href="route('lesson-presentation.print', { id: lesson.id })"
              target="_blank"
              class="text-decoration-none"
            >
              <q-btn
                flat
                round
                dense
                size="sm"
                color="grey-6"
                icon="print"
              >
                <q-tooltip>Print Lesson</q-tooltip>
              </q-btn>
            </a>
          </div>
        </div>

        <!-- Second Row: Progress Management -->
        <q-separator />
        <div class="row q-gutter-xs full-width">
          <q-btn
            unelevated
            dense
            size="sm"
            color="positive"
            icon="lock_open"
            label="Open to All Students"
            no-caps
            class="col"
            @click="$emit('open-to-all', lesson)"
          />
          <Link :href="route('lesson-presentation.teacher.progress', { lessonId: lesson.id })">
            <q-btn
              unelevated
              dense
              size="sm"
              color="primary"
              icon="assessment"
              label="View Progress"
              no-caps
            />
          </Link>
        </div>
      </div>
    </q-card-actions>
  </q-card>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
  lesson: {
    type: Object,
    required: true
  }
});

defineEmits(['delete', 'copy-link', 'open-to-all']);
</script>

<style scoped>
.hover-shadow:hover {
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
.transition-generic {
  transition: all 0.3s ease;
}
.hover-text-primary:hover {
  color: var(--q-primary) !important;
}
.hover-text-negative:hover {
  color: var(--q-negative) !important;
}
.text-decoration-none {
  text-decoration: none;
}
</style>
