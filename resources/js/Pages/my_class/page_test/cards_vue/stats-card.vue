<template>
  <div class="stats-container">
    <div class="stats-card">
      <div class="stat-item">
        <div class="stat-icon">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
            <circle cx="9" cy="7" r="4"></circle>
            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
          </svg>
        </div>
        <div class="stat-value">2.4K</div>
        <div class="stat-label">Followers</div>
      </div>
      
      <div class="stat-item">
        <div class="stat-icon">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
            <polyline points="22 4 12 14.01 9 11.01"></polyline>
          </svg>
        </div>
        <div class="stat-value">50%</div>
        <div class="stat-label">Growth</div>
      </div>
      
      <div class="stat-item">
        <div class="stat-icon">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
            <circle cx="9" cy="7" r="4"></circle>
            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
          </svg>
        </div>
        <div class="stat-value">1.2K</div>
        <div class="stat-label">Likes</div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'StatsCard'
}
</script>

<style scoped>
.stats-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  padding: 20px;
}

.stats-card {
  display: flex;
  background: white;
  border-radius: 20px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.1);
  padding: 30px;
  gap: 40px;
}

.stat-item {
  text-align: center;
  padding: 0 20px;
}

.stat-icon {
  width: 60px;
  height: 60px;
  margin: 0 auto 15px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
  border-radius: 50%;
  color: white;
}

.stat-icon svg {
  width: 28px;
  height: 28px;
}

.stat-value {
  font-size: 2rem;
  font-weight: 700;
  color: #2d3436;
  margin-bottom: 5px;
}

.stat-label {
  font-size: 0.9rem;
  color: #636e72;
  text-transform: uppercase;
  letter-spacing: 1px;
  font-weight: 600;
}
</style>