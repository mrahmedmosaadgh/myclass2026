<template>
  <div class="content-page">
    <div class="page-header">
      <h1>Content Page</h1>
    </div>
    <div class="page-content">
      <div class="margin"></div>
      <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin mauris risus, lobortis a neque aliquet, ornare rutrum purus. Integer hendrerit ac est non cursus. Integer quis risus tincidunt nunc mattis ultricies. Proin sed enim tellus.</p>
      
      <p>Quisque ullamcorper ligula eget semper bibendum. Fusce imperdiet nisi sapien, eget facilisis magna viverra quis. Sed pharetra tellus massa, non congue justo lobortis a.</p>
      
      <div class="content-section">
        <h2>Section Title</h2>
        <p>Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Donec velit neque, commodo ut sodales ut, vestibulum eu quam. Nulla placerat augue vitae nibh aliquet, ac posuere lectus faucibus.</p>
      </div>
      
      <div class="action-buttons">
        <button class="btn-primary">Continue</button>
        <button class="btn-secondary">Cancel</button>
      </div>
    </div>
    <div class="page-footer">
      <p>© 2026 Content Page. All rights reserved.</p>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ContentPage'
}
</script>

<style scoped>
.content-page {
  min-height: 100vh;
  background: linear-gradient(135deg, #fdfcfb 0%, #e2d1c3 100%);
  display: flex;
  flex-direction: column;
  padding: 20px;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.page-header {
  text-align: center;
  padding: 20px 0;
}

.page-header h1 {
  color: #333;
  font-size: 2.2rem;
  margin: 0;
}

.page-content {
  max-width: 800px;
  margin: 0 auto;
  background: white;
  padding: 30px;
  border-radius: 12px;
  box-shadow: 0 5px 15px rgba(0,0,0,0.05);
  flex: 1;
}

.margin {
  height: 20px;
  background: linear-gradient(to right, #6a11cb, #2575fc);
  border-radius: 10px;
  margin: 15px 0;
}

p {
  color: #555;
  line-height: 1.7;
  margin-bottom: 16px;
  font-size: 1.05rem;
}

.content-section {
  margin: 30px 0;
  padding: 20px;
  background: #f9f9f9;
  border-radius: 8px;
}

.content-section h2 {
  color: #2575fc;
  margin-top: 0;
  font-size: 1.5rem;
}

.action-buttons {
  display: flex;
  gap: 15px;
  justify-content: flex-end;
  margin-top: 25px;
}

.btn-primary, .btn-secondary {
  padding: 12px 25px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s ease;
}

.btn-primary {
  background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
  color: white;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(37, 117, 252, 0.4);
}

.btn-secondary {
  background: #e9ecef;
  color: #6c757d;
}

.btn-secondary:hover {
  background: #dde0e3;
}

.page-footer {
  text-align: center;
  padding: 20px 0;
  color: #777;
  font-size: 0.9rem;
}
</style>