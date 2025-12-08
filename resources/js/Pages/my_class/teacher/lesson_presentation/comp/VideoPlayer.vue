<template>
    <video
      width="640"
      height="360"
      controls
      autoplay
      muted
      loop
      :poster="poster"
      preload="auto"
    >
      <source :src="videoUrl" type="video/mp4" />
      <source :src="videoUrl.replace(/\.mp4$/, '.webm')" type="video/webm" />
      <source :src="videoUrl.replace(/\.mp4$/, '.ogg')" type="video/ogg" />
      Your browser does not support the video tag.
    </video>
  </template>

  <script setup>
  defineProps({
    videoUrl: {
      type: String,
      required: true,
    },
    poster: {
      type: String,
      default: 'thumbnail.jpg',
    },
  });
  </script>
