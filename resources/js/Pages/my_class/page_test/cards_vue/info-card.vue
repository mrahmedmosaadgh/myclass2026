<template>
  <div class="info-card-container">
    <div class="info-card">
      <div class="card-header">
        <h3>Project Info</h3>
      </div>
      <div class="card-body">
        <p>This is an interactive info card component with smooth animations and transitions.</p>
        <div class="card-actions">
          <button class="btn-primary">View Details</button>
          <button class="btn-secondary">Share</button>
        </div>
      </div>
      <div class="card-footer">
        <div class="avatar-small">AH</div>
        <div class="date">Feb 10, 2026</div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'InfoCard'
}
</script>

<style scoped>
.info-card-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  padding: 20px;
}

.info-card {
  width: 350px;
  background: white;
  border-radius: 16px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.1);
  overflow: hidden;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.info-card:hover {
  transform: translateY(-10px);
  box-shadow: 0 15px 40px rgba(0,0,0,0.15);
}

.card-header {
  padding: 20px;
  background: linear-gradient(90deg, #6a11cb 0%, #2575fc 100%);
  color: white;
}

.card-header h3 {
  margin: 0;
  font-size: 1.4rem;
  font-weight: 600;
}

.card-body {
  padding: 25px;
}

.card-body p {
  color: #555;
  line-height: 1.6;
  margin-bottom: 20px;
}

.card-actions {
  display: flex;
  gap: 12px;
}

.btn-primary, .btn-secondary {
  padding: 10px 20px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s ease;
}

.btn-primary {
  background: #2575fc;
  color: white;
  flex: 1;
}

.btn-primary:hover {
  background: #1a68e8;
}

.btn-secondary {
  background: #f0f2f5;
  color: #555;
  flex: 1;
}

.btn-secondary:hover {
  background: #e4e6e9;
}

.card-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px 20px;
  border-top: 1px solid #eee;
}

.avatar-small {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: linear-gradient(135deg, #ff9a9e 0%, #fad0c4 100%);
  display: flex;
  justify-content: center;
  align-items: center;
  color: white;
  font-weight: bold;
  font-size: 14px;
}

.date {
  color: #888;
  font-size: 0.9rem;
}
</style>