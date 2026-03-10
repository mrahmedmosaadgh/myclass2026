<template>
  <q-card flat bordered class="q-mb-xl">
    <q-card-section class="row items-center q-pb-none">
      <div class="text-h6 text-weight-bold">New Academic Year</div>
      <q-space />
      <q-chip size="sm" color="grey-3" text-color="grey-8" dense>SETTINGS</q-chip>
    </q-card-section>

    <q-card-section>
      <q-form @submit.prevent="submit" class="row q-col-gutter-md">
        <div class="col-12 col-md-4">
          <q-input
            v-model="form.start_date"
            type="date"
            label="Start Date"
            filled
            dense
            @update:model-value="suggestName"
            :rules="[val => !!val || 'Required']"
          />
        </div>

        <div class="col-12 col-md-4">
          <q-input
            v-model="form.end_date"
            type="date"
            label="End Date"
            filled
            dense
            :rules="[val => !!val || 'Required']"
          />
        </div>

        <div class="col-12 col-md-4">
          <div class="row q-col-gutter-sm">
            <div class="col">
              <q-input
                v-model="form.name"
                label="Year Name"
                placeholder="e.g. 2025-2026"
                filled
                dense
                class="text-mono"
                :rules="[val => !!val || 'Required']"
              />
            </div>
            <div class="col-auto">
              <q-btn
                type="submit"
                color="primary"
                unelevated
                :loading="form.processing"
                :disable="form.processing"
                icon="add"
                label="Create Year"
                class="full-height text-weight-bold"
              />
            </div>
          </div>
          <div v-if="form.errors.name" class="text-negative text-caption q-mt-xs">
            {{ form.errors.name }}
          </div>
        </div>
      </q-form>
    </q-card-section>
  </q-card>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';

const emit = defineEmits(['created']);

const today = new Date();
const currentYear = today.getFullYear();
// If we are currently after July, maybe suggest next year, otherwise current year
const startYear = today.getMonth() >= 6 ? currentYear : currentYear; 

const form = useForm({
  start_date: `${startYear}-07-01`,
  end_date: `${startYear + 1}-06-30`,
  name: `${startYear}-${startYear + 1}`,
});

const suggestName = () => {
  if (form.start_date) {
    const startDate = new Date(form.start_date);
    const selectedYear = startDate.getFullYear();
    
    // Always auto-update the name based on the new start year
    form.name = `${selectedYear}-${selectedYear + 1}`;
    
    // Auto-update end date (June 30th of following year)
    const end = new Date(form.start_date);
    end.setFullYear(end.getFullYear() + 1);
    end.setMonth(5); // June (0-indexed)
    end.setDate(30);
    form.end_date = end.toISOString().split('T')[0];
  }
};

const submit = () => {
  form.post(route('admin.academic_calendar.year.store'), {
    onSuccess: () => {
      form.reset();
      emit('created');
    },
  });
};
</script>
