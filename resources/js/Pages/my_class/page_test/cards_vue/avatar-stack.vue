<template>
  <div class="avatar-container">
    <div class="avatar-stack">
      <div class="avatar">
        <span>PS</span>
        <div class="tooltip">Priya Sharma</div>
      </div>
      <div class="avatar">
        <span>RS</span>
        <div class="tooltip">Rohit Sen</div>
      </div>
      <div class="avatar">
        <span>AG</span>
        <div class="tooltip">Alex Glover</div>
      </div>
      <div class="avatar">
        <span>JT</span>
        <div class="tooltip">Jamie Taylor</div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AvatarStack'
}
</script>

<style scoped>
.avatar-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background: #f0f2f5;
  padding: 20px;
}

.avatar-stack {
  display: flex;
  gap: -10px;
}

.avatar {
  position: relative;
  width: 40px;
  height: 40px;
  display: flex;
  justify-content: center;
  align-items: center;
  background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
  color: white;
  border-radius: 50%;
  font-size: 14px;
  font-weight: bold;
  cursor: pointer;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  z-index: 1;
  margin-right: -10px;
  border: 2px solid #fff;
}

.avatar:first-child {
  margin-left: 0;
}

.avatar:hover {
  transform: translateY(-5px) scale(1.1);
  z-index: 5;
  box-shadow: 0 10px 20px rgba(0,0,0,0.2);
}

.tooltip {
  position: absolute;
  bottom: 100%;
  left: 50%;
  transform: translateX(-50%) translateY(5px);
  background: #333;
  color: white;
  padding: 6px 12px;
  border-radius: 4px;
  font-size: 13px;
  white-space: nowrap;
  opacity: 0;
  visibility: hidden;
  transition: all 0.3s ease;
  z-index: 10;
}

.avatar:hover .tooltip {
  opacity: 1;
  visibility: visible;
  transform: translateX(-50%) translateY(-5px);
}

.tooltip::after {
  content: '';
  position: absolute;
  top: 100%;
  left: 50%;
  transform: translateX(-50%);
  border-width: 6px;
  border-style: solid;
  border-color: #333 transparent transparent transparent;
}
</style>