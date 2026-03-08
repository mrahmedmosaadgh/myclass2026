<!-- This file was previously named 12.vue - A card with dual layers -->
<style scoped>
  .cardm {
    position: absolute;
    display: flex;
    align-items: center;
    justify-content: center;
    top: 46.64%;
    left: 50%;
  }

  .card {
    position: absolute;
    width: 250px;
    height: 130px;
    border-radius: 25px;
    background: whitesmoke;
    color: black;
    z-index: 2;
    transition: .4s ease-in-out;
  }

  .weather {
    position: relative;
    margin: 1em;
  }

  .main {
    font-size: 2em;
    position: relative;
    top: -3em;
    left: 4.3em;
  }

  .mainsub {
    position: relative;
    top: -10.2em;
    left: 14em;
    font-size: 0.6em;
  }

  .card2 {
    position: absolute;
    display: flex;
    flex-direction: row;
    width: 240px;
    height: 130px;
    border-radius: 35px;
    background: white;
    z-index: -1;
    transition: .4s ease-in-out;
  }
</style>

<template>
  <div class="cardm">
    <div class="card2"></div>
    <div class="card">
      <div class="weather">
        <div class="main">25°C</div>
        <div class="mainsub">Sunny</div>
      </div>
    </div>
  </div>
</template>