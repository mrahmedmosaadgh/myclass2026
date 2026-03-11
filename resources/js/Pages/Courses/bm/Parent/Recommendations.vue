<template>
  <Head title="Basic Math - Recommendations" />
  <div class="bm-parent-recommendations q-pa-md bg-grey-1" style="min-height: 100vh;">
    <!-- Parent Header -->
    <div class="row items-center justify-between q-mb-xl bg-white q-pa-lg rounded-borders shadow-1 border-left-info">
      <div>
        <h3 class="text-h4 text-weight-bold text-dark q-my-none">Actionable Recommendations</h3>
        <div class="text-subtitle1 text-grey-7">How to help <span class="text-weight-bold text-info">Alice Smith</span> at home</div>
      </div>
      <q-btn flat icon="arrow_back" label="Back to Dashboard" :to="route('bm.parent.dashboard')" />
    </div>

    <div class="row q-col-gutter-lg">
      <div class="col-12 col-md-8">
        <div class="text-h5 text-weight-bold q-mb-md">AI Gap Analysis</div>
        
        <q-card class="shadow-2 rounded-borders q-mb-lg bg-white">
          <q-card-section class="bg-primary text-white row items-center">
            <q-icon name="insights" size="md" class="q-mr-sm" />
            <div class="text-h6 text-weight-bold">Current Learning Profile</div>
          </q-card-section>
          <q-card-section class="q-pa-lg text-body1">
            <p>
              Alice is doing great with basic addition and subtraction! However, our adaptive engine noticed she is taking an average of <strong>12 seconds</strong> per Division question, which is pulling her fluency score down. 
            </p>
            <p>
              She also struggled slightly with Fractions during her last assessment. Don't worry, this is entirely normal at this stage!
            </p>
          </q-card-section>
        </q-card>

        <div class="text-h5 text-weight-bold q-mb-md">Suggested Home Activities</div>

        <q-list separator class="bg-white shadow-2 rounded-borders">
          <!-- Activity 1 -->
          <q-item class="q-pa-lg">
            <q-item-section avatar>
              <q-avatar color="warning" text-color="dark" icon="kitchen" size="50px" />
            </q-item-section>
            <q-item-section>
              <q-item-label class="text-h6 text-weight-bold">Baking Fractions</q-item-label>
              <q-item-label class="text-body1 text-grey-8 q-mt-xs">
                Next time you're in the kitchen, ask Alice to help measure out 1/2 or 1/4 cups of ingredients. Visualizing physical fractions is the best way to understand denominators!
              </q-item-label>
            </q-item-section>
            <q-item-section side top>
              <q-chip color="primary" text-color="white" icon="schedule" size="sm">15 mins</q-chip>
            </q-item-section>
          </q-item>

          <!-- Activity 2 -->
          <q-item class="q-pa-lg">
            <q-item-section avatar>
              <q-avatar color="info" text-color="white" icon="directions_car" size="50px" />
            </q-item-section>
            <q-item-section>
              <q-item-label class="text-h6 text-weight-bold">Traffic Light Division</q-item-label>
              <q-item-label class="text-body1 text-grey-8 q-mt-xs">
                While driving, play a quick mental game. Give Alice a total number (like 20) and ask her how to divide it by 4 before the light turns green. This builds speed without pressure.
              </q-item-label>
            </q-item-section>
            <q-item-section side top>
              <q-chip color="primary" text-color="white" icon="schedule" size="sm">5 mins</q-chip>
            </q-item-section>
          </q-item>
        </q-list>
      </div>

      <!-- Motivation Sidebar -->
      <div class="col-12 col-md-4">
        <q-card class="shadow-2 rounded-borders bg-blue-1 border-all-info">
          <q-card-section class="text-center q-pa-xl">
            <q-icon name="favorite" color="negative" size="80px" class="q-mb-md" />
            <div class="text-h5 text-weight-bold text-dark q-mb-sm">Praise Effort, Not Smartness</div>
            <div class="text-body1 text-grey-8">
              "You worked really hard on those division problems today!" is statistically proven to be better for a child's mathematical growth mindset than saying "You're so smart!"
            </div>
          </q-card-section>
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
.border-left-info {
  border-left: 8px solid var(--q-info);
}
.border-all-info {
  border: 2px solid var(--q-info);
}
</style>
