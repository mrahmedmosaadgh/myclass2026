<template>
  <div class="card-container">
    <div class="card">
      <div class="card__body">
        <div class="card__icon">
          <svg
            height="32"
            width="32"
            stroke="currentColor"
            stroke-width="1.5"
            viewBox="0 0 24 24"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path
              d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0V12a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 12V5.25"
              stroke-linejoin="round"
              stroke-linecap="round"
            ></path>
          </svg>
        </div>

        <p class="card__title">Abstract Design</p>
        <p class="card__paragraph">
          Lorem ipsum dolor sit amet consectetur, adipisicing elit. Ducimus
          perferendis eaque dolore repellat numquam. Dolores.
        </p>
      </div>

      <div class="card__ribbon">
        <label class="card__ribbon-label">01</label>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'RibbonCard'
}
</script>

<style scoped>
.card-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background: linear-gradient(135deg, #f0f2f5 0%, #e1e8ed 100%);
  padding: 20px;
}

.card {
  position: relative;
  width: 350px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.1);
  overflow: hidden;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card:hover {
  transform: translateY(-10px);
  box-shadow: 0 15px 40px rgba(0,0,0,0.15);
}

.card__body {
  padding: 30px;
}

.card__icon {
  width: 60px;
  height: 60px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
  border-radius: 12px;
  margin-bottom: 20px;
  color: white;
}

.card__title {
  font-size: 1.4rem;
  font-weight: 600;
  color: #2d3436;
  margin: 0 0 15px 0;
}

.card__paragraph {
  color: #636e72;
  line-height: 1.6;
  margin: 0;
}

.card__ribbon {
  position: absolute;
  top: 20px;
  right: -30px;
  width: 120px;
  height: 30px;
  background: linear-gradient(135deg, #ff9a9e 0%, #fad0c4 100%);
  transform: rotate(45deg);
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

.card__ribbon-label {
  color: #2d3436;
  font-size: 0.9rem;
  font-weight: 600;
  transform: rotate(-45deg);
  position: relative;
  top: -1px;
}
</style>