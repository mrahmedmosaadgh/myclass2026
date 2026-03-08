<style scoped>
  .avatar-stack {
    display: flex;
    align-items: center;
  }

  .avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    border: 3px solid white;
    background: linear-gradient(135deg, #4f46e5, #9333ea);
    position: relative;
    margin-left: -20px;
    transition:
      transform 0.3s,
      z-index 0.3s;
    cursor: pointer;
    display: flex;
    justify-content: center;
    align-items: center;
    color: white;
    font-weight: bold;
    font-size: 18px;
  }

  .avatar:first-child {
    margin-left: 0;
  }

  .avatar:hover {
    transform: scale(1.2);
    z-index: 10;
  }

  .avatar .tooltip {
    position: absolute;
    bottom: 110%;
    left: 50%;
    transform: translateX(-50%) scale(0.8);
    background: white;
    color: #111;
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 14px;
    white-space: nowrap;
    opacity: 0;
    pointer-events: none;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
    transition:
      opacity 0.3s ease,
      transform 0.3s ease;
  }

  .avatar:hover .tooltip {
    opacity: 1;
    transform: translateX(-50%) scale(1);
  }

  /* Avatar gradients */
  .avatar:nth-child(2) {
    background: linear-gradient(135deg, #10b981, #06b6d4);
  }
  .avatar:nth-child(3) {
    background: linear-gradient(135deg, #f59e0b, #ef4444);
  }
  .avatar:nth-child(4) {
    background: linear-gradient(135deg, #ec4899, #8b5cf6);
  }
</style>

<template>
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
      <div class="tooltip">Ananya Gupta</div>
    </div>
    <div class="avatar">
      <span>KM</span>
      <div class="tooltip">Kunal Mehta</div>
    </div>
  </div>
</template>
