<template>
  <q-dialog v-model="internalModel" full-width full-height>
    <q-card class="column full-height">
      <!-- Header -->
      <q-card-section class="bg-primary text-white row items-center q-py-sm">
        <div class="text-h6">
          <q-icon name="compare_arrows" class="q-mr-sm" />
          Compare Draft: {{ draftName }}
        </div>
        <q-space />
        <q-btn icon="close" flat round dense v-close-popup />
      </q-card-section>

      <!-- Content -->
      <q-card-section class="col q-pa-none scroll relative-position">
        <div v-if="loading" class="absolute-center text-center">
          <q-spinner color="primary" size="3em" />
          <div class="text-grey q-mt-sm">Comparing Schedules...</div>
        </div>

        <div v-else class="column full-height">
          <!-- Stats Summary -->
          <div class="row q-pa-md q-col-gutter-md bg-grey-1 border-bottom">
            <div class="col-12 col-md-3">
              <q-card flat bordered class="text-center q-pa-sm">
                <div class="text-h4 text-positive">{{ comparison?.additions || 0 }}</div>
                <div class="text-caption text-grey">New Assignments</div>
              </q-card>
            </div>
            <div class="col-12 col-md-3">
              <q-card flat bordered class="text-center q-pa-sm">
                <div class="text-h4 text-negative">{{ comparison?.deletions || 0 }}</div>
                <div class="text-caption text-grey">Removed Assignments</div>
              </q-card>
            </div>
            <div class="col-12 col-md-3">
              <q-card flat bordered class="text-center q-pa-sm">
                <div class="text-h4 text-primary">{{ comparison?.unchanged || 0 }}</div>
                <div class="text-caption text-grey">Unchanged</div>
              </q-card>
            </div>
            <div class="col-12 col-md-3">
              <q-card flat bordered class="text-center bg-blue-1 q-pa-sm cursor-pointer" @click="$emit('publish')">
                <div class="text-subtitle1 text-primary text-weight-bold q-mt-sm">Ready to Publish?</div>
                <div class="text-caption text-primary">Click to apply changes</div>
              </q-card>
            </div>
          </div>

          <!-- Comparison Grid -->
          <!-- Note: For simplicity in MVP, we show a summary list. 
               Full visual grid comparison would require substantial code duplication from TimetableGrid 
               or extracting a Grid component first. -->
          <div class="col q-pa-md scroll">
            <div v-if="comparison?.details && comparison.details.length > 0">
               <!-- Details list implementation would go here -->
               <div class="text-center text-grey q-mt-xl">
                 Detailed visual comparison coming soon.
                 Please rely on summary statistics above.
               </div>
            </div>
            <div v-else class="text-center text-grey q-mt-xl">
               <q-icon name="check_circle" size="4em" color="positive" class="q-mb-md" />
               <div class="text-h6">Analysis Complete</div>
               <p>Use the summary above to understand impact.</p>
            </div>
          </div>
        </div>
      </q-card-section>

      <!-- Footer -->
      <q-card-actions align="right" class="bg-grey-1 q-pa-md">
        <q-btn flat label="Cancel" color="grey" v-close-popup />
        <q-btn 
          unelevated 
          color="primary" 
          icon="publish" 
          label="Publish Draft (Replace Live)"
          :loading="publishing"
          @click="$emit('publish')"
        />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  modelValue: Boolean,
  draftName: String,
  comparison: Object,
  loading: Boolean,
  publishing: Boolean
});

const emit = defineEmits(['update:modelValue', 'publish']);

const internalModel = computed({
  get: () => props.modelValue,
  set: (val) => emit('update:modelValue', val)
});
</script>

<style scoped>
.border-bottom {
  border-bottom: 1px solid #ddd;
}
</style>
