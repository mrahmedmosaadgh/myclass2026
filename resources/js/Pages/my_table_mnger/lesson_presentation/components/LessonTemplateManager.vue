<template>
  <q-dialog v-model="isOpen">
    <q-card style="min-width: 600px">
      <q-card-section>
        <div class="text-h6">Lesson Templates</div>
        <div class="text-caption text-grey-7">Manage templates per subject</div>
      </q-card-section>
      <q-card-section>
        <div v-if="subjects.length === 0" class="text-grey-6">
          No subjects available.
        </div>
        <div v-else class="q-gutter-md">
          <div v-for="s in subjects" :key="s.id" class="q-pa-sm rounded-borders border">
            <div class="row items-center justify-between">
              <div class="text-weight-medium">{{ s.name }}</div>
              <div class="q-gutter-sm">
                <q-btn dense flat icon="add" label="Add Template" />
                <q-btn dense flat icon="view_list" label="View Templates" />
              </div>
            </div>
          </div>
        </div>
      </q-card-section>
      <q-card-actions align="right">
        <q-btn flat label="Close" v-close-popup />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  subjects: { type: Array, default: () => [] }
});
const emit = defineEmits(['update:modelValue']);

const isOpen = ref(props.modelValue);
watch(() => props.modelValue, v => (isOpen.value = v));
watch(isOpen, v => emit('update:modelValue', v));
</script>

<style scoped>
</style>
