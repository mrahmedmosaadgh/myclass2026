<template>
  <q-card 
    flat 
    bordered 
    class="rounded-xl overflow-hidden shadow-1"
    style="border: 2px dashed #FFA726;"
  >
    <!-- Header -->
    <q-card-section class="bg-orange-1 row items-center justify-between q-py-sm">
      <div class="row items-center q-gutter-sm">
        <q-avatar size="24px" color="orange-5" text-color="white" icon="calendar_today" />
        <div class="column">
          <span class="text-weight-bold text-orange-9">Unscheduled Period</span>
          <span class="text-caption text-orange-7 text-weight-medium">No semester assigned</span>
        </div>
      </div>
      <q-chip dense color="orange-2" text-color="orange-9" class="text-caption text-weight-bold">
        {{ gap.days }} days
      </q-chip>
    </q-card-section>

    <q-separator color="orange-2" />

    <!-- Date Range -->
    <q-card-section class="q-pa-md column justify-center items-center" style="min-height: 130px;">
      <div class="row items-center justify-center q-gutter-md q-mb-md">
        <div class="column items-center">
          <span class="text-caption text-weight-bold text-grey-5 uppercase tracking-wider">From</span>
          <span class="text-weight-bold text-grey-8 text-body2">{{ gap.startDate }}</span>
        </div>
        <q-icon name="east" color="orange-3" size="sm" />
        <div class="column items-center">
          <span class="text-caption text-weight-bold text-grey-5 uppercase tracking-wider">To</span>
          <span class="text-weight-bold text-grey-8 text-body2">{{ gap.endDate }}</span>
        </div>
      </div>

      <div class="bg-orange-1 rounded-lg q-pa-xs q-px-md text-center full-width">
        <span class="text-caption text-orange-8">
          This period has no semester. Set semester dates to cover it.
        </span>
      </div>
    </q-card-section>
  </q-card>
</template>

<script setup>
defineProps({
  gap: {
    type: Object,
    required: true,
    // Expected: { startDate: 'YYYY-MM-DD', endDate: 'YYYY-MM-DD', days: Number }
  }
});
</script>

<style scoped>
.rounded-xl { border-radius: 12px; }
</style>
