<template>
  <div class="calendar-header">
    <div class="header-left">
      <q-btn-group flat>
        <q-btn flat dense icon="chevron_left" @click="$emit('previous')" />
        <q-btn flat dense icon="today" @click="$emit('today')" label="Today" />
        <q-btn flat dense icon="chevron_right" @click="$emit('next')" />
      </q-btn-group>
      <h2 class="date-title">{{ formattedDate }}</h2>
    </div>
    
    <div class="header-right">
      <q-btn-group flat>
        <q-btn 
          flat 
          dense 
          :label="'Month'" 
          :color="currentView === 'month' ? 'primary' : ''" 
          @click="$emit('view-change', 'month')" 
        />
        <q-btn 
          flat 
          dense 
          :label="'Week'" 
          :color="currentView === 'week' ? 'primary' : ''" 
          @click="$emit('view-change', 'week')" 
        />
        <q-btn 
          flat 
          dense 
          :label="'Day'" 
          :color="currentView === 'day' ? 'primary' : ''" 
          @click="$emit('view-change', 'day')" 
        />
      </q-btn-group>
      
      <q-btn 
        color="primary" 
        icon="add" 
        label="Add Event" 
        @click="$emit('add-event')" 
      />
    </div>
  </div>
</template>

<script setup>
defineProps({
  formattedDate: {
    type: String,
    required: true
  },
  currentView: {
    type: String,
    required: true
  }
});

defineEmits(['previous', 'next', 'today', 'view-change', 'add-event']);
</script>

<style scoped>
.calendar-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px;
  border-bottom: 1px solid #e5e7eb;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 16px;
}

.date-title {
  font-size: 1.25rem;
  font-weight: 600;
  margin: 0;
}

.header-right {
  display: flex;
  align-items: center;
  gap: 16px;
}
</style>
