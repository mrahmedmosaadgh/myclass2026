<style scoped>
  .card-container {
    perspective: 800px;
  }

  .card {
    position: relative;
    width: 210px;
    height: 332px;
    background: #fffffd;
    border-radius: 1em;
    box-shadow: 0 0 6px -4px black;
    transition: 0.75s all;
    transform-style: preserve-3d;
    pointer-events: none;
    user-select: none;
  }

  .card::before {
    content: "";
    position: absolute;
    bottom: -24px;
    left: 0;
    width: 210px;
    height: 110px;
    transform: translate(0em, 60%) rotateX(90deg) scale(0.8);
    border-radius: 100%;
    background-color: #000a;
    z-index: -10;
    filter: blur(24px);
  }

  .card-container:hover .card {
    transform: rotateY(180deg);
  }

  .back {
    position: absolute;
    display: flex;
    justify-content: center;
    align-items: center;
    inset: 0.7em;
    border-radius: 1.25em;
    background: #191f1f;
    overflow: hidden;
  }

  .blue {
    position: absolute;
    background: #007bff; /* Blue color for the Skip card */
    inset: 0;
    border-radius: 100%;
    transform-origin: center;
    transform: scale(0.875) skewX(-22.5deg);
  }

  .back .text {
    position: absolute;
    font-size: 5em;
    font-weight: 800;
    color: #e4c713; /* Gold color for text */
    transform-origin: center;
    transform: rotate(-15deg) translateX(5px);
    text-shadow:
      2px 2px #f1e8ad,
      2px 1px #f1e8ad,
      2px 0px #f1e8ad,
      2px -1px #f1e8ad,
      2px -2px #f1e8ad,
      1px -2px #f1e8ad,
      0px -2px #f1e8ad,
      -1px -2px #f1e8ad,
      -2px -2px #f1e8ad,
      -2px -1px #f1e8ad,
      -2px 0px #f1e8ad,
      -2px 1px #f1e8ad,
      -2px 2px #f1e8ad,
      -12px 10px #191f1f,
      -10px 10px #191f1f,
      -9px 9.428571429px #191f1f,
      -8px 8.857142857px #191f1f,
      -7px 8.285714286px #191f1f,
      -6px 7.714285714px #191f1f,
      -5px 7.142857143px #191f1f,
      -4px 6.571428572px #191f1f,
      -3px 6px #191f1f,
      -2px 5.428571429px #191f1f,
      -1px 4.857142857px #191f1f,
      0px 4.285714286px #191f1f,
      1px 3.714285715px #191f1f,
      2px 3.142857143px #191f1f,
      3px 2.571428572px #191f1f,
      4px 2px #191f1f,
      4px -4px #191f1f,
      -4px -4px #191f1f,
      -5px -3px #191f1f,
      -6px -2px #191f1f,
      -7px -1px #191f1f,
      -8px 0px #191f1f,
      -9px 1px #191f1f,
      -10px 2px #191f1f,
      -11px 3px #191f1f,
      -12px 4px #191f1f,
      -14px 4px #fffffd,
      -13px 3px #fffffd,
      -12px 2px #fffffd,
      -11px 1px #fffffd,
      -10px 0px #fffffd,
      -9px -1px #fffffd,
      -8px -2px #fffffd,
      -7px -3px #fffffd,
      -6px -4px #fffffd,
      -5px -5px #fffffd,
      -4px -6px #fffffd,
      -3px -6px #fffffd,
      0px -6px #fffffd,
      3px -6px #fffffd,
      6px -6px #fffffd,
      -14px 12px #fffffd,
      -8px 12px #fffffd,
      -7px 11.5px #fffffd,
      -6px 11px #fffffd,
      -5px 10.5px #fffffd,
      -4px 10px #fffffd,
      -3px 9.5px #fffffd,
      -2px 9px #fffffd,
      -1px 8.5px #fffffd,
      0px 8px #fffffd,
      1px 7.5px #fffffd,
      2px 7px #fffffd,
      3px 6.5px #fffffd,
      4px 6px #fffffd,
      5px 5.5px #fffffd,
      6px 4px #fffffd;
  }

  .front {
    position: absolute;
    display: flex;
    justify-content: center;
    align-items: center;
    inset: 0.7em;
    border-radius: 1.25em;
    background: #007bff; /* Blue color for the Skip card */
    overflow: hidden;
    transform: rotateY(180deg);
    backface-visibility: hidden;
    -webkit-backface-visibility: hidden;
  }

  .front .blue {
    border: 0.7em solid #fffffd; /* White border for the front */
    transform: scale(0.92, 0.875) skewX(-22.5deg);
  }

  .text-center {
    position: absolute;
    width: 12em;
    color: white;
  }

  .text-top {
    position: absolute;
    top: -1em;
    left: -1.25em;
    width: 7em;
    color: white;
  }

  .text-bottom {
    position: absolute;
    bottom: -1em;
    right: -1.5em;
    width: 7em;
    color: white;
  }
</style>

<template>
  <div class="card-container">
    <div class="card">
      <div class="back">
        <div class="blue"></div>
        <div class="text">SKIP</div>
      </div>
      <div class="front">
        <div class="blue"></div>
        <div class="text-center">
          <svg viewBox="0 0 48 48">
            <path
              d="M 14,24 C 14,24 14,20 16,20 L 20,20 L 20,16 L 16,16 C 12,16 10,20 10,24 C 10,28 12,32 16,32 L 20,32 L 20,28 L 16,28 C 14,28 14,24 14,24 Z"
              fill="#191f1f"
            ></path>
            <path
              d="M 24,16 L 28,16 L 28,12 L 24,12 C 20,12 18,16 18,20 C 18,24 20,28 24,28 L 28,28 L 28,24 L 24,24 C 22,24 22,20 24,20 Z"
              fill="#fffffd"
            ></path>
          </svg>
        </div>
        <div class="text-top">
          <svg viewBox="0 0 48 48">
            <path
              d="M 14,24 C 14,24 14,20 16,20 L 20,20 L 20,16 L 16,16 C 12,16 10,20 10,24 C 10,28 12,32 16,32 L 20,32 L 20,28 L 16,28 C 14,28 14,24 14,24 Z"
              fill="#191f1f"
            ></path>
            <path
              d="M 24,16 L 28,16 L 28,12 L 24,12 C 20,12 18,16 18,20 C 18,24 20,28 24,28 L 28,28 L 28,24 L 24,24 C 22,24 22,20 24,20 Z"
              fill="#fffffd"
            ></path>
          </svg>
        </div>
        <div class="text-bottom">
          <svg viewBox="0 0 48 48">
            <path
              d="M 14,24 C 14,24 14,20 16,20 L 20,20 L 20,16 L 16,16 C 12,16 10,20 10,24 C 10,28 12,32 16,32 L 20,32 L 20,28 L 16,28 C 14,28 14,24 14,24 Z"
              fill="#191f1f"
            ></path>
            <path
              d="M 24,16 L 28,16 L 28,12 L 24,12 C 20,12 18,16 18,20 C 18,24 20,28 24,28 L 28,28 L 28,24 L 24,24 C 22,24 22,20 24,20 Z"
              fill="#fffffd"
            ></path>
          </svg>
        </div>
      </div>
    </div>
  </div>
</template>
