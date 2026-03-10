<template>
  <div class="student-card">
    <div class="card-header">
      <img
        :src="student.avatarUrl"
        :alt="`Avatar for ${student.name}`"
        class="student-avatar"
      />
      <div class="student-info">
        <h3 class="student-name">{{ student.name }}</h3>
        <p class="student-grade">{{ student.grade }}</p>
      </div>
    </div>

    <div class="card-body">
      <div class="points-display">
        <span class="points-icon">+</span>
        <span class="points-value">{{ student.points }}</span>
      </div>
      <p class="points-label">Positive Points</p>
    </div>

    <div class="card-footer">
      <button class="action-button give-points" @click="givePoint">
        <i class="icon-plus"></i> Give a Point
      </button>
      <button class="action-button view-details">
        <i class="icon-chart"></i> View Details
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

// --- PROPS ---
// Define the student data that the component expects
const props = defineProps({
  student: {
    type: Object,
    required: true,
    default: () => ({
      id: 1,
      name: 'Alex Johnson',
      grade: 'Grade 5',
      points: 25,
      avatarUrl: 'https://i.pravatar.cc/150?u=a042581f4e29026704d', // Default avatar
    }),
  },
});

// --- EMITS ---
// Define the events this component can emit to its parent
const emit = defineEmits(['update-points']);

// --- METHODS ---
// Method to handle giving a point
function givePoint() {
  // Emit an event to the parent with the student's new point total
  emit('update-points', props.student.id, props.student.points + 1);
}
</script>

<style scoped>
/* --- CARD CONTAINER --- */
.student-card {
  font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
  background-color: #ffffff;
  border-radius: 20px;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
  padding: 24px;
  text-align: center;
  width: 280px;
  transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
  cursor: pointer;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.student-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
}

/* --- CARD HEADER --- */
.card-header {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-bottom: 20px;
}

.student-avatar {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  border: 4px solid #f0f0f0;
  margin-bottom: 16px;
  object-fit: cover;
}

.student-name {
  font-size: 1.5rem; /* 24px */
  font-weight: 800;
  color: #2c3e50;
  margin: 0;
}

.student-grade {
  font-size: 1rem; /* 16px */
  color: #7f8c8d;
  margin: 4px 0 0;
}

/* --- CARD BODY --- */
.card-body {
  background-color: #f7f9fc;
  border-radius: 16px;
  padding: 20px;
  margin-bottom: 20px;
}

.points-display {
  display: flex;
  justify-content: center;
  align-items: center;
  font-weight: 700;
}

.points-icon {
  font-size: 2.5rem; /* 40px */
  color: #61dafb; /* A nice, positive blue */
  margin-right: 8px;
  line-height: 1;
}

.points-value {
  font-size: 3rem; /* 48px */
  color: #2c3e50;
  line-height: 1;
}

.points-label {
  font-size: 0.875rem; /* 14px */
  color: #95a5a6;
  margin-top: 8px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

/* --- CARD FOOTER --- */
.card-footer {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.action-button {
  background-color: #ffffff;
  border: 2px solid #e0e6ed;
  border-radius: 12px;
  padding: 12px 16px;
  font-size: 0.95rem;
  font-weight: 700;
  color: #5a6c7d;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.action-button:hover {
  background-color: #f7f9fc;
  border-color: #61dafb;
  color: #2c3e50;
}

.action-button.give-points {
  background-color: #61dafb;
  color: #ffffff;
  border-color: #61dafb;
}

.action-button.give-points:hover {
  background-color: #4fa8c5;
  border-color: #4fa8c5;
}

/* Simple icons using CSS for demonstration */
.icon-plus::before {
  content: '+';
  font-size: 1.2rem;
}
.icon-chart::before {
  content: '▤';
  font-size: 1.2rem;
}
</style>