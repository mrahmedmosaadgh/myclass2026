<template>
  <div class="notification-container">
    <div class="notification-card">
      <div class="icon-container">
        <div class="bell-icon">
          <div class="bell-shape"></div>
          <div class="bell-clapper"></div>
        </div>
        <div class="notification-badge">3</div>
      </div>
      <div class="notification-content">
        <h3>New Notifications</h3>
        <ul class="notification-list">
          <li class="notification-item">
            <span class="dot unread"></span>
            <div class="notification-text">
              <p>New message received</p>
              <small>2 minutes ago</small>
            </div>
          </li>
          <li class="notification-item">
            <span class="dot unread"></span>
            <div class="notification-text">
              <p>Your order has shipped</p>
              <small>1 hour ago</small>
            </div>
          </li>
          <li class="notification-item">
            <span class="dot read"></span>
            <div class="notification-text">
              <p>Welcome to our platform!</p>
              <small>Yesterday</small>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'NotificationBadge'
}
</script>

<style scoped>
.notification-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background: linear-gradient(135deg, #1a2a6c, #b21f1f, #1a2a6c);
  padding: 20px;
}

.notification-card {
  width: 350px;
  background: white;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 10px 30px rgba(0,0,0,0.2);
}

.icon-container {
  position: relative;
  display: flex;
  justify-content: center;
  padding-top: 25px;
}

.bell-icon {
  position: relative;
  width: 60px;
  height: 60px;
}

.bell-shape {
  position: absolute;
  width: 40px;
  height: 45px;
  background: #f0f2f5;
  border-radius: 50% 50% 0 0;
  top: 0;
  left: 10px;
  border: 2px solid #6c757d;
  border-bottom: none;
}

.bell-clapper {
  position: absolute;
  width: 6px;
  height: 20px;
  background: #6c757d;
  top: 45px;
  left: 27px;
  border-radius: 0 0 3px 3px;
}

.notification-badge {
  position: absolute;
  top: 5px;
  right: 25px;
  background: #ff4757;
  color: white;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  display: flex;
  justify-content: center;
  align-items: center;
  font-size: 0.8rem;
  font-weight: bold;
  box-shadow: 0 2px 5px rgba(255, 71, 87, 0.5);
}

.notification-content {
  padding: 20px;
}

.notification-content h3 {
  margin-top: 0;
  margin-bottom: 20px;
  color: #2d3436;
  text-align: center;
}

.notification-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.notification-item {
  display: flex;
  align-items: flex-start;
  padding: 12px 0;
  border-bottom: 1px solid #eee;
}

.notification-item:last-child {
  border-bottom: none;
}

.dot {
  display: inline-block;
  width: 10px;
  height: 10px;
  border-radius: 50%;
  margin-right: 12px;
  margin-top: 6px;
}

.dot.unread {
  background: #2ed573;
  box-shadow: 0 0 8px #2ed573;
}

.dot.read {
  background: #747d8c;
}

.notification-text {
  flex: 1;
}

.notification-text p {
  margin: 0 0 4px 0;
  color: #2d3436;
}

.notification-text small {
  color: #a4b0be;
  font-size: 0.8rem;
}
</style>