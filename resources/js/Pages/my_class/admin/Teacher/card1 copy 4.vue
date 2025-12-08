<style scoped>
  .book {
    position: relative;
    border-radius: 10px;
    width: 220px;
    /* height: 300px; */
    background-color: whitesmoke;
    -webkit-box-shadow: 1px 1px 12px #000;
    box-shadow: 1px 1px 12px #000;
    -webkit-transform: preserve-3d;
    -ms-transform: preserve-3d;
    transform: preserve-3d;
    -webkit-perspective: 2000px;
    perspective: 2000px;
    display: -webkit-box;
    display: -ms-flexbox;
    display: flex;
    -webkit-box-align: center;
    -ms-flex-align: center;
    align-items: center;
    -webkit-box-pack: center;
    -ms-flex-pack: center;
    justify-content: center;
    color: #000;
  }

  .cover {
    top: 0;
    position: absolute;
    background-color: lightgray;
    width: 100%;
    height: 100%;
    border-radius: 10px;
    cursor: pointer;
    -webkit-transition: all 0.5s;
    transition: all 0.5s;
    -webkit-transform-origin: 0;
    -ms-transform-origin: 0;
    transform-origin: 0;
    -webkit-box-shadow: 1px 1px 12px #000;
    box-shadow: 1px 1px 12px #000;
    display: -webkit-box;
    display: -ms-flexbox;
    display: flex;
    -webkit-box-align: center;
    -ms-flex-align: center;
    align-items: center;
    -webkit-box-pack: center;
    -ms-flex-pack: center;
    justify-content: center;
  }

  .book:hover .cover {
    -webkit-transition: all 0.5s;
    transition: all 0.5s;
    -webkit-transform: rotatey(-100deg);
    -ms-transform: rotatey(-100deg);
    transform: rotatey(-100deg);
  }

  p {
    font-size: 20px;
    font-weight: bolder;
  }
</style>

<script setup>
defineProps({
  coverText: {
    type: String,
    default: 'Hover Me'
  }
});
</script>

<template>
  <div class="book">
    <!-- Default slot for main content -->
    <slot></slot>

    <!-- Named slot for additional content -->
    <slot name="extra"></slot>

    <div class="cover">
      <!-- Cover slot with fallback -->
      <slot name="cover">
        <p>{{ coverText }}</p>
      </slot>
    </div>
  </div>
</template>


