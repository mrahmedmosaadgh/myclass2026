<template>
  <div class="timeline-container">
    <div class="timeline">
      <div class="timeline-item">
        <div class="timeline-marker"></div>
        <div class="timeline-content">
          <h3>Event Title 1</h3>
          <p>February 2026</p>
          <p>Description of the event goes here.</p>
        </div>
      </div>
      <div class="timeline-item">
        <div class="timeline-marker"></div>
        <div class="timeline-content">
          <h3>Event Title 2</h3>
          <p>March 2026</p>
          <p>Another description of the event goes here.</p>
        </div>
      </div>
      <div class="timeline-item">
        <div class="timeline-marker"></div>
        <div class="timeline-content">
          <h3>Event Title 3</h3>
          <p>April 2026</p>
          <p>Final description of the event goes here.</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Timeline'
}
</script>

<style scoped>
.timeline-container {
  display: flex;
  justify-content: center;
  align-items: flex-start;
  min-height: 100vh;
  background: #f8f9fa;
  padding: 40px 20px;
}

.timeline {
  max-width: 800px;
  position: relative;
  padding-left: 30px;
  border-left: 2px solid #dee2e6;
}

.timeline-item {
  position: relative;
  margin-bottom: 40px;
  padding-left: 30px;
}

.timeline-item:last-child {
  margin-bottom: 0;
}

.timeline-marker {
  position: absolute;
  left: -30px;
  top: 5px;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
  border: 3px solid white;
  box-shadow: 0 0 0 2px #dee2e6;
}

.timeline-content {
  background: white;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}

.timeline-content h3 {
  margin-top: 0;
  color: #2575fc;
  font-size: 1.3rem;
}

.timeline-content p {
  margin-bottom: 10px;
  color: #6c757d;
}
</style>