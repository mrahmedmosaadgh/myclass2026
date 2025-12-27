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
            @change="suggestName"
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

const form = useForm({
  start_date: '',
  end_date: '',
  name: '',
});

const suggestName = () => {
  if (form.start_date) {
    const year = new Date(form.start_date).getFullYear();
    if (!form.name || form.name === `${year-1}-${year}` || form.name === `${year}-${year+1}`) {
      form.name = `${year}-${year + 1}`;
    }
    
    // Suggest end date (1 year later)
    if (!form.end_date) {
      const end = new Date(form.start_date);
      end.setFullYear(end.getFullYear() + 1);
      form.end_date = end.toISOString().split('T')[0];
    }
  }
};

const submit = () => {
  form.post(route('admin.academic_calendar.year.store'), {
    onSuccess: () => {
      form.reset();
    },
  });
};
</script>
