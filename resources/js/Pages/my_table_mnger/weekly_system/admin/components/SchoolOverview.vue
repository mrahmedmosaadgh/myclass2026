<template>
  <div class="row q-col-gutter-md">
    <!-- Statistics Cards -->
    <div class="col-12 col-md-4">
      <q-card flat bordered class="stat-card">
        <q-card-section class="q-pa-md">
          <div class="row items-center">
            <div class="col">
              <div class="text-h3 text-weight-bold text-primary">{{ stats.total_classrooms }}</div>
              <div class="text-subtitle2 text-grey-7">Classrooms</div>
            </div>
            <div class="col-auto">
              <q-icon name="class" size="48px" color="primary" />
            </div>
          </div>
        </q-card-section>
      </q-card>
    </div>

    <div class="col-12 col-md-4">
      <q-card flat bordered class="stat-card">
        <q-card-section class="q-pa-md">
          <div class="row items-center">
            <div class="col">
              <div class="text-h3 text-weight-bold text-secondary">{{ stats.total_subjects }}</div>
              <div class="text-subtitle2 text-grey-7">Subjects</div>
            </div>
            <div class="col-auto">
              <q-icon name="menu_book" size="48px" color="secondary" />
            </div>
          </div>
        </q-card-section>
      </q-card>
    </div>

    <div class="col-12 col-md-4">
      <q-card flat bordered class="stat-card">
        <q-card-section class="q-pa-md">
          <div class="row items-center">
            <div class="col">
              <div class="text-h3 text-weight-bold text-accent">{{ stats.total_teachers }}</div>
              <div class="text-subtitle2 text-grey-7">Teachers</div>
            </div>
            <div class="col-auto">
              <q-icon name="person" size="48px" color="accent" />
            </div>
          </div>
        </q-card-section>
      </q-card>
    </div>

    <!-- Additional Stats -->
    <div class="col-12 col-md-6">
      <q-card flat bordered>
        <q-card-section>
          <div class="text-h6 q-mb-md">Structure</div>
          <q-list dense>
            <q-item>
              <q-item-section avatar>
                <q-icon name="account_tree" color="primary" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Stages</q-item-label>
              </q-item-section>
              <q-item-section side>
                <q-badge color="primary" :label="stats.stages_count" />
              </q-item-section>
            </q-item>
            <q-item>
              <q-item-section avatar>
                <q-icon name="grade" color="secondary" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Grades</q-item-label>
              </q-item-section>
              <q-item-section side>
                <q-badge color="secondary" :label="stats.grades_count" />
              </q-item-section>
            </q-item>
            <q-item>
              <q-item-section avatar>
                <q-icon name="assignment" color="accent" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Total Assignments</q-item-label>
              </q-item-section>
              <q-item-section side>
                <q-badge color="accent" :label="stats.total_assignments" />
              </q-item-section>
            </q-item>
          </q-list>
        </q-card-section>
      </q-card>
    </div>

    <!-- Quick Info -->
    <div class="col-12 col-md-6">
      <q-card flat bordered>
        <q-card-section>
          <div class="text-h6 q-mb-md">Quick Info</div>
          <div class="q-gutter-sm">
            <div class="row items-center">
              <q-icon name="info" color="info" class="q-mr-sm" />
              <span class="text-body2">
                Average assignments per classroom: 
                <strong>{{ averageAssignments }}</strong>
              </span>
            </div>
            <div class="row items-center">
              <q-icon name="info" color="info" class="q-mr-sm" />
              <span class="text-body2">
                Average classrooms per teacher: 
                <strong>{{ averageClassrooms }}</strong>
              </span>
            </div>
          </div>
        </q-card-section>
      </q-card>
    </div>

    <!-- Subjects List -->
    <div class="col-12">
      <q-card flat bordered>
        <q-card-section>
          <div class="text-h6 q-mb-md">Subjects ({{ subjects.length }})</div>
          <div class="row q-col-gutter-sm">
            <div v-for="subject in subjects" :key="subject.id" class="col-auto">
              <q-chip
                :style="{
                  backgroundColor: subject.color_bg || '#e0e0e0',
                  color: subject.color_text || '#000000'
                }"
                icon="menu_book"
              >
                {{ subject.name }}
              </q-chip>
            </div>
          </div>
        </q-card-section>
      </q-card>
    </div>

    <!-- Teachers List -->
    <div class="col-12">
      <q-card flat bordered>
        <q-card-section>
          <div class="text-h6 q-mb-md">Teachers ({{ teachers.length }})</div>
          <q-list dense bordered separator>
            <q-item v-for="teacher in teachers.slice(0, 10)" :key="teacher.id">
              <q-item-section avatar>
                <q-avatar color="primary" text-color="white" icon="person" />
              </q-item-section>
              <q-item-section>
                <q-item-label>{{ teacher.name }}</q-item-label>
                <q-item-label caption>{{ teacher.email || 'No email' }}</q-item-label>
              </q-item-section>
              <q-item-section side v-if="teacher.phone_number">
                <q-icon name="phone" color="grey-6" />
                <q-item-label caption>{{ teacher.phone_number }}</q-item-label>
              </q-item-section>
            </q-item>
            <q-item v-if="teachers.length > 10">
              <q-item-section class="text-center text-grey-7">
                <q-item-label caption>... and {{ teachers.length - 10 }} more teachers</q-item-label>
              </q-item-section>
            </q-item>
          </q-list>
        </q-card-section>
      </q-card>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  stats: {
    type: Object,
    required: true
  },
  subjects: {
    type: Array,
    required: true
  },
  teachers: {
    type: Array,
    required: true
  }
});

const averageAssignments = computed(() => {
  if (props.stats.total_classrooms === 0) return 0;
  return (props.stats.total_assignments / props.stats.total_classrooms).toFixed(1);
});

const averageClassrooms = computed(() => {
  if (props.stats.total_teachers === 0) return 0;
  return (props.stats.total_classrooms / props.stats.total_teachers).toFixed(1);
});
</script>

<style scoped>
.stat-card {
  transition: transform 0.2s, box-shadow 0.2s;
}

.stat-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
}
</style>
