<!-- DojoStudentCard.vue -->
<template>
  <div 
    class="student-card" 
    :class="{ 
      'has-spots': hasSpots,
      'selected': isSelected,
      'disabled': isDisabled
    }" 
    @click="handleClick"
  >
    <!-- Selection Checkbox -->
    <div class="selection-checkbox" v-if="selectable">
      <div class="checkbox-inner" :class="{ 'checked': isSelected }">
        <svg v-if="isSelected" viewBox="0 0 24 24" class="check-icon">
          <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
        </svg>
      </div>
    </div>

    <div class="avatar-wrapper">
      <div :class="['avatar', colorClass]">
        <!-- Ears -->
        <div class="ear left"></div>
        <div class="ear right"></div>

        <!-- Eyes -->
        <div class="eye left-eye"></div>
        <div class="eye right-eye"></div>

        <!-- Mouth -->
        <div class="mouth"></div>

        <!-- Optional Spots (only for green monster) -->
        <div v-if="hasSpots" class="spots"></div>
      </div>

      <!-- Points Badge with Tooltip -->
      <div class="points-badge" :class="pointsBadgeClass" @click.stop>
        {{ totalPoints }}
        
        <!-- Tooltip on Hover -->
        <div class="points-tooltip">
          <div class="tooltip-row positive">
            <span class="tooltip-label">Positive:</span>
            <span class="tooltip-value">+{{ positivePoints }} ⭐</span>
          </div>
          <div class="tooltip-row negative">
            <span class="tooltip-label">Negative:</span>
            <span class="tooltip-value">-{{ negativePoints }} ⚠️</span>
          </div>
          <div class="tooltip-divider"></div>
          <div class="tooltip-row total">
            <span class="tooltip-label">Total:</span>
            <span class="tooltip-value">{{ totalPoints }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Student Name (First and Last) -->
    <div class="student-name">
      <div class="first-name">{{ firstName }}</div>
      <div class="last-name">{{ lastName }}</div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'DojoStudentCard',
  props: {
    name: {
      type: String,
      required: true
    },
    student: {
      type: Object,
      default: () => ({})
    },
    positivePoints: {
      type: Number,
      default: 0
    },
    negativePoints: {
      type: Number,
      default: 0
    },
    isSelected: {
      type: Boolean,
      default: false
    },
    selectable: {
      type: Boolean,
      default: true
    },
    isDisabled: {
      type: Boolean,
      default: false
    }
  },
  methods: {
    handleClick() {
      if (!this.isDisabled && this.selectable) {
        this.$emit('toggle-select', this.student);
      }
    }
  },
  computed: {
    colorClass() {
      // Assign color based on student ID for consistency
      const colors = ['yellow', 'pink', 'green', 'blue', 'purple', 'orange'];
      const studentId = this.student?.id || 0;
      return colors[studentId % colors.length];
    },
    hasSpots() {
      // Only green monsters have spots
      return this.colorClass === 'green';
    },
    firstName() {
      // Split name and get first part
      const parts = this.name.trim().split(' ');
      return parts[0] || this.name;
    },
    lastName() {
      // Split name and get last part (or empty if only one name)
      const parts = this.name.trim().split(' ');
      return parts.length > 1 ? parts.slice(1).join(' ') : '';
    },
    totalPoints() {
      // Calculate total from positive and negative
      return this.positivePoints - this.negativePoints;
    },
    pointsBadgeClass() {
      // Color badge based on total points
      if (this.totalPoints > 10) return 'badge-excellent';
      if (this.totalPoints > 0) return 'badge-good';
      if (this.totalPoints < 0) return 'badge-warning';
      return 'badge-neutral';
    }
  },
  emits: ['click', 'toggle-select']
};
</script>

<style scoped>
/* Base Card */
.student-card {
  position: relative;
  background: white;
  border-radius: 20px;
  width: 140px;
  text-align: center;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  overflow: visible;
  transition: all 0.2s ease;
  cursor: pointer;
  font-family: 'Avenir', 'Helvetica Neue', Arial, sans-serif;
  border: 3px solid transparent;
}

.student-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
}

/* Selected State */
.student-card.selected {
  border-color: #3498db;
  background: linear-gradient(135deg, #ebf8ff, #ffffff);
  box-shadow: 0 6px 20px rgba(52, 152, 219, 0.3);
}

/* Disabled State */
.student-card.disabled {
  opacity: 0.5;
  cursor: not-allowed;
  filter: grayscale(50%);
}

.student-card.disabled:hover {
  transform: none;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

/* Selection Checkbox */
.selection-checkbox {
  position: absolute;
  top: 8px;
  left: 8px;
  z-index: 10;
}

.checkbox-inner {
  width: 24px;
  height: 24px;
  border: 2px solid #bdc3c7;
  border-radius: 6px;
  background: white;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.checkbox-inner.checked {
  background: #3498db;
  border-color: #3498db;
}

.check-icon {
  width: 16px;
  height: 16px;
  fill: white;
}

/* Avatar Wrapper */
.avatar-wrapper {
  position: relative;
  padding: 20px 0 12px;
}

/* Avatar Base */
.avatar {
  position: relative;
  width: 90px;
  height: 90px;
  margin: 0 auto;
  border-radius: 50%;
  background: #ffd43b;
  box-shadow: inset 0 -4px 8px rgba(0, 0, 0, 0.1);
}

/* Color Variants with Gradients */
.avatar.yellow  { background: linear-gradient(135deg, #ffd43b, #f39c12); }
.avatar.pink    { background: linear-gradient(135deg, #ff9ff3, #f368e0); }
.avatar.green   { background: linear-gradient(135deg, #51cf66, #37b24d); }
.avatar.blue    { background: linear-gradient(135deg, #74c0fc, #339af0); }
.avatar.purple  { background: linear-gradient(135deg, #b197fc, #9775fa); }
.avatar.orange  { background: linear-gradient(135deg, #ffb84d, #ff922b); }

/* Ears */
.ear {
  position: absolute;
  top: -10px;
  width: 28px;
  height: 32px;
  background: inherit;
  border-radius: 50% 50% 0 0;
  box-shadow: inset 0 -3px 6px rgba(0, 0, 0, 0.15);
}
.ear.left  { left: 12px; transform: rotate(-20deg); }
.ear.right { right: 12px; transform: rotate(20deg); }

/* Eyes */
.eye {
  position: absolute;
  top: 28px;
  width: 18px;
  height: 22px;
  background: white;
  border-radius: 50%;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}
.left-eye  { left: 22px; }
.right-eye { right: 22px; }

/* Pupils */
.eye::after {
  content: '';
  position: absolute;
  top: 4px;
  left: 5px;
  width: 8px;
  height: 8px;
  background: #2d3436;
  border-radius: 50%;
}

/* Mouth */
.mouth {
  position: absolute;
  bottom: 22px;
  left: 50%;
  transform: translateX(-50%);
  width: 36px;
  height: 18px;
  background: white;
  border-radius: 0 0 20px 20px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

/* Spots (for green monster) */
.spots::before,
.spots::after {
  content: '';
  position: absolute;
  background: #2f9e44;
  border-radius: 50%;
  opacity: 0.6;
}
.spots::before {
  width: 12px;
  height: 12px;
  top: 18px;
  left: 20px;
}
.spots::after {
  width: 16px;
  height: 16px;
  bottom: 30px;
  right: 18px;
}

/* Points Badge */
.points-badge {
  position: absolute;
  top: 12px;
  right: 12px;
  width: 36px;
  height: 36px;
  background: #2ecc71;
  color: white;
  font-weight: bold;
  font-size: 15px;
  line-height: 36px;
  border-radius: 50%;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
  z-index: 2;
  cursor: help;
  transition: all 0.2s ease;
}

/* Badge Color Variants */
.points-badge.badge-excellent {
  background: linear-gradient(135deg, #2ecc71, #27ae60);
}

.points-badge.badge-good {
  background: linear-gradient(135deg, #3498db, #2980b9);
}

.points-badge.badge-neutral {
  background: linear-gradient(135deg, #95a5a6, #7f8c8d);
}

.points-badge.badge-warning {
  background: linear-gradient(135deg, #e74c3c, #c0392b);
}

/* Tooltip */
.points-tooltip {
  position: absolute;
  top: 45px;
  right: -10px;
  background: white;
  border-radius: 12px;
  padding: 12px;
  min-width: 160px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
  opacity: 0;
  visibility: hidden;
  transform: translateY(-10px);
  transition: all 0.3s ease;
  z-index: 10;
  pointer-events: none;
}

.points-badge:hover .points-tooltip {
  opacity: 1;
  visibility: visible;
  transform: translateY(0);
}

/* Tooltip Arrow */
.points-tooltip::before {
  content: '';
  position: absolute;
  top: -6px;
  right: 20px;
  width: 12px;
  height: 12px;
  background: white;
  transform: rotate(45deg);
  box-shadow: -2px -2px 4px rgba(0, 0, 0, 0.05);
}

/* Tooltip Rows */
.tooltip-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 6px 0;
  font-size: 13px;
}

.tooltip-row.positive .tooltip-value {
  color: #27ae60;
  font-weight: bold;
}

.tooltip-row.negative .tooltip-value {
  color: #e74c3c;
  font-weight: bold;
}

.tooltip-row.total {
  font-weight: bold;
  font-size: 14px;
}

.tooltip-row.total .tooltip-value {
  color: #2c3e50;
  font-size: 16px;
}

.tooltip-label {
  color: #7f8c8d;
  font-weight: 500;
}

.tooltip-divider {
  height: 1px;
  background: #ecf0f1;
  margin: 6px 0;
}

/* Student Name */
.student-name {
  padding: 8px 12px 14px;
  text-align: center;
}

.first-name {
  font-size: 16px;
  font-weight: 700;
  color: #2d3436;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  margin-bottom: 2px;
}

.last-name {
  font-size: 13px;
  font-weight: 500;
  color: #636e72;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
</style>