<template>
  <div class="flip-card-container">
    <div class="flip-card">
      <div class="card-inner">
        <div class="card-front">
          <h3>Front Side</h3>
          <p>Hover to flip the card</p>
        </div>
        <div class="card-back">
          <h3>Back Side</h3>
          <p>More information here</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'FlipCard'
}
</script>

<style scoped>
.flip-card-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background: #2c3e50;
  padding: 20px;
}

.flip-card {
  width: 280px;
  height: 350px;
  perspective: 1000px;
}

.card-inner {
  position: relative;
  width: 100%;
  height: 100%;
  text-align: center;
  transition: transform 0.8s;
  transform-style: preserve-3d;
}

.flip-card:hover .card-inner {
  transform: rotateY(180deg);
}

.card-front, .card-back {
  position: absolute;
  width: 100%;
  height: 100%;
  backface-visibility: hidden;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  padding: 30px;
  border-radius: 16px;
  box-shadow: 0 15px 35px rgba(0,0,0,0.25);
}

.card-front {
  background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
  color: white;
}

.card-back {
  background: linear-gradient(135deg, #ff9a9e 0%, #fad0c4 100%);
  color: #333;
  transform: rotateY(180deg);
}

.card-front h3, .card-back h3 {
  font-size: 1.8rem;
  margin-bottom: 15px;
}

.card-front p, .card-back p {
  font-size: 1.1rem;
  opacity: 0.9;
}
</style>