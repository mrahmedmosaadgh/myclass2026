<template>
  <div class="w-full">
    <div class="mb-4 flex items-center justify-between">
      <label class="text-lg font-bold text-gray-700">Select Classroom</label>

    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
      <q-card
        v-for="classroom in classrooms"
        :key="classroom.classroom_id"
        class="cursor-pointer transition-all duration-200 hover:shadow-lg hover:scale-[1.02] border-2 overflow-hidden"
        :class="[
          modelValue === classroom.classroom_id
            ? 'border-primary ring-2 ring-blue-200'
            : 'border-transparent hover:border-gray-200'
        ]"
        :style="{
          background: modelValue === classroom.classroom_id 
            ? 'yellow' 
            : (classroom.subject?.color_bg ? `${classroom.subject.color_bg}15` : '#ffffff')
        }"
        @click="$emit('update:modelValue', classroom.classroom_id); $emit('change', classroom.classroom_id)"
         @dblclick="$emit('init')"
      >
        <!-- Colored Header Bar -->
        <div 
          class="h-2 w-full bg-blue-600"
          ></div>
          <!-- :style="{ background: classroom.subject?.color_bg || 'blue' }" -->

        <q-card-section class="p-4">
          <div class="flex items-start justify-between">
            <div class="flex-1">
              <div 
                class="text-xs font-bold uppercase tracking-wider mb-1"
                :style="{ color: classroom.subject?.color_bg || '#2563eb' }"
              >
                {{ classroom.subject?.name || classroom.subject_name || 'Classroom' }}
              </div>
              <div class="text-2xl font-bold text-gray-800 leading-tight">
                {{ classroom.classroom_name }}
              </div>
            </div>
            <div 
              class="text-xs font-bold px-2 py-1 rounded-full flex items-center gap-1"
              :style="{ 
                backgroundColor: classroom.subject?.color_bg ? `${classroom.subject.color_bg}20` : '#dbeafe',
                color: classroom.subject?.color_bg || '#1e40af'
              }"
            >
              <q-icon name="group" size="xs" />
              {{ classroom.students ? classroom.students.length : 0 }}
            </div>
          </div>
          
          <div class="mt-4 flex items-center justify-between text-xs text-gray-500">
            <div class="flex items-center gap-1">
              <q-icon name="school" size="xs" />
              <span>ID: {{ classroom.classroom_id }}</span>
            </div>
            <div v-if="modelValue === classroom.classroom_id" class="font-bold flex items-center gap-1 text-primary">
              <q-icon name="check_circle" size="xs" />
              Selected
            </div>
          </div>
        </q-card-section>
      </q-card>
    </div>

    <div v-if="!classrooms.length" class="text-center py-8 text-gray-500 bg-gray-50 rounded-lg border border-dashed border-gray-300">
      No classrooms available
    </div>






          <div class="flex items-center gap-2">
        <q-toggle
          :model-value="avatarEditEnabled"
          @update:model-value="$emit('update:avatarEditEnabled', $event)"
          label="Enable Avatar Edit"
          color="purple"
          left-label
        />
        <div v-if="initStatus && initStatus.message" class="text-sm text-gray-600 bg-gray-100 px-3 py-1 rounded-full">
          {{ initStatus.message }}
        </div>
        <q-btn
          color="primary"
          label="Init Session"
          icon="play_arrow"
          :disable="!modelValue || loading"
          :loading="loading"
          @click="$emit('init')"
          class="shadow-md scale-150 m-12"
        />
      </div>
  </div>
</template>

<script setup>
defineProps({
  modelValue: {
    type: [String, Number],
    default: null
  },
  classrooms: {
    type: Array,
    default: () => []
  },
  loading: {
    type: Boolean,
    default: false
  },
  initStatus: {
    type: Object,
    default: () => ({ message: '' })
  },
  avatarEditEnabled: {
    type: Boolean,
    default: false
  }
})

defineEmits(['update:modelValue', 'change', 'init', 'update:avatarEditEnabled'])
</script>

<style scoped>
/* Custom scrollbar for grid if needed, though standard grid usually wraps */
</style>
