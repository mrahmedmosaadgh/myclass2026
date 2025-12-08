<template>
    <div class="flex flex-col items-center gap-4">
      <input type="file" @change="handleFileChange" accept="image/*" />

      <canvas
        ref="canvas"
        :class="{ hidden: !imageLoaded }"
        @click="handleCanvasClick"
        class="border shadow rounded-xl"
      ></canvas>

      <button
        v-if="imageLoaded"
        @click="saveImage"
        class="px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700"
      >
        Save Image
      </button>
    </div>
  </template>

  <script setup>
  import { ref } from 'vue';

  const canvas = ref(null);
  const imageLoaded = ref(false);
  let ctx = null;
  let img = new Image();

  function handleFileChange(event) {
    const file = event.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = (e) => {
      img.onload = () => {
        const c = canvas.value;
        c.width = img.width;
        c.height = img.height;
        ctx = c.getContext('2d');
        ctx.drawImage(img, 0, 0);
        imageLoaded.value = true;
      };
      img.src = e.target.result;
    };
    reader.readAsDataURL(file);
  }

  function handleCanvasClick(event) {
    if (!ctx) return;

    const rect = canvas.value.getBoundingClientRect();
    const x = event.clientX - rect.left;
    const y = event.clientY - rect.top;

    const pixel = ctx.getImageData(x, y, 1, 1).data;
    const [r, g, b] = pixel;

    makeColorTransparent(r, g, b);
  }

  function makeColorTransparent(r, g, b) {
    const imageData = ctx.getImageData(0, 0, canvas.value.width, canvas.value.height);
    const data = imageData.data;

    for (let i = 0; i < data.length; i += 4) {
      if (isSameColor(data[i], data[i+1], data[i+2], r, g, b)) {
        data[i + 3] = 0; // Set alpha to 0 (transparent)
      }
    }

    ctx.putImageData(imageData, 0, 0);
  }

  function isSameColor(r1, g1, b1, r2, g2, b2) {
    const threshold = 30; // Allow slight differences
    return (
      Math.abs(r1 - r2) < threshold &&
      Math.abs(g1 - g2) < threshold &&
      Math.abs(b1 - b2) < threshold
    );
  }

  function saveImage() {
    const link = document.createElement('a');
    link.download = 'edited-image.png';
    link.href = canvas.value.toDataURL('image/png');
    link.click();
  }
  </script>

  <style scoped>
  canvas {
    max-width: 100%;
    cursor: crosshair;
  }
  </style>
