<template>
  <Head title="Basic Math - Parent Dashboard" />
  <div class="bm-parent-dashboard q-pa-md bg-grey-1" style="min-height: 100vh;">
    <!-- Parent Header -->
    <div class="row items-center justify-between q-mb-xl bg-white q-pa-lg rounded-borders shadow-1 border-left-primary">
      <div>
        <h3 class="text-h4 text-weight-bold text-dark q-my-none">Parent Portal</h3>
        <div class="text-subtitle1 text-grey-7">Monitoring: <span class="text-weight-bold text-primary">Alice Smith</span></div>
      </div>
      <q-avatar size="70px" color="primary" text-color="white" icon="face" class="shadow-2" />
    </div>

    <div class="row q-col-gutter-lg">
      <!-- Child Overview Stats -->
      <div class="col-12 col-md-4">
        <q-card class="shadow-2 rounded-borders text-center q-pa-xl bg-white" style="height: 100%">
          <q-icon name="trending_up" color="positive" size="60px" class="q-mb-md" />
          <div class="text-h3 text-weight-bold text-dark q-mb-sm">82%</div>
          <div class="text-h6 text-grey-8">Overall Math Proficiency</div>
          <q-chip color="positive" text-color="white" class="q-mt-lg text-weight-bold shadow-1">On Track</q-chip>
        </q-card>
      </div>

      <div class="col-12 col-md-4">
        <q-card class="shadow-2 rounded-borders text-center q-pa-xl bg-white" style="height: 100%">
          <q-icon name="local_fire_department" color="warning" size="60px" class="q-mb-md" />
          <div class="text-h3 text-weight-bold text-dark q-mb-sm">4 Days</div>
          <div class="text-h6 text-grey-8">Current Practice Streak</div>
          <q-chip color="warning" text-color="white" class="q-mt-lg text-weight-bold shadow-1">Keep it up!</q-chip>
        </q-card>
      </div>

      <div class="col-12 col-md-4">
        <q-card class="shadow-2 rounded-borders text-center q-pa-xl bg-white" style="height: 100%">
          <q-icon name="emoji_events" color="info" size="60px" class="q-mb-md" />
          <div class="text-h3 text-weight-bold text-dark q-mb-sm">12</div>
          <div class="text-h6 text-grey-8">Total Badges Earned</div>
          <q-btn flat color="info" label="View Badges" :to="route('bm.assessment.history')" class="q-mt-md" />
        </q-card>
      </div>
    </div>

    <!-- Recent Activity Timeline -->
    <div class="row q-mt-xl">
      <div class="col-12">
        <div class="text-h5 text-weight-bold text-dark q-mb-md">Recent Activity</div>
        <q-card class="shadow-2 rounded-borders q-pa-lg bg-white">
          <q-timeline color="primary">
            <q-timeline-entry
              title="Completed Fraction Speed Drill"
              subtitle="Today at 4:30 PM"
              icon="bolt"
              color="warning"
            >
              <div>Alice scored 90% and improved her fluency score by 15 points.</div>
            </q-timeline-entry>

            <q-timeline-entry
              title="Unlocked 'Addition Master' Badge"
              subtitle="Yesterday at 5:15 PM"
              icon="school"
              color="positive"
            >
              <div>Alice achieved 100% accuracy in the Addition domain.</div>
            </q-timeline-entry>

            <q-timeline-entry
              title="Took Initial Placement Test"
              subtitle="Last Monday"
              icon="analytics"
              color="primary"
            >
              <div>Placed at 'Proficient' level with a score of 78.</div>
            </q-timeline-entry>
          </q-timeline>
        </q-card>
      </div>
    </div>

  </div>
</template>

<script setup>
defineOptions({ layout: BMLayout });
import BMLayout from "@/Layouts/BMLayout.vue";
import { Head } from '@inertiajs/vue3';
</script>

<style scoped>
.rounded-borders {
  border-radius: 16px;
}
.border-left-primary {
  border-left: 8px solid var(--q-primary);
}
</style>
