<template>
  <div class="student-card" @click="openBehavior">
    
    <!-- NEGATIVE BUBBLE -->
    <div v-if="behavior?.points_minus" class="neg-bubble">
      -{{ behavior.points_minus }}
    </div>

    <!-- AVATAR -->
    <div class="avatar-wrapper">
      <img
        :src="student.avatar ?? defaultAvatar"
        class="avatar"
      />
    </div>

    <div class="name">{{ student.name }}</div>

  </div>
</template>

<script setup>
const props = defineProps({
  student: Object,
  behavior: Object
});

const emit = defineEmits(["open-behavior", "open-avatar"]);

const defaultAvatar = "/images/default-monster.png";

const openBehavior = () => emit("open-behavior", props.student);
</script>

<style scoped>
.student-card {
  width: 150px;
  text-align: center;
  cursor: pointer;
  position: relative;
  transition: 0.2s;
}
.student-card:hover { transform: scale(1.05); }

.avatar-wrapper {
  width: 120px;
  height: 120px;
  margin: auto;
  border-radius: 50%;
  overflow: hidden;
  border: 4px solid #2ec4b6;
  box-shadow: 0 0 12px rgba(46,196,182,0.5);
}

.avatar {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.name {
  margin-top: 6px;
  font-weight: 600;
  font-size: 16px;
}

.neg-bubble {
  position: absolute;
  top: -6px;
  right: -6px;
  background: #e63946;
  color: white;
  font-weight: bold;
  padding: 4px 8px;
  border-radius: 14px;
  font-size: 13px;
}
</style>
