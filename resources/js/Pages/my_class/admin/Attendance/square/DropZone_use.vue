<template>
    <DropZone
      :acceptsDrop="validateDrop"
      :activeItem="activeBox"
      @drop="handleDrop"
      @rejected="handleRejected"
    >
      Drop answer here
    </DropZone>
  </template>

  <script setup>
  import DropZone from './DropZone.vue'
  import { ref } from 'vue'

  const activeBox = ref({
    isPicked: false,
    text: 'Box',
  })

  const validateDrop = (item) => {
    return item.text === 'Correct'
  }

  const handleDrop = ({ x, y, item }) => {
    console.log('Dropped:', item, 'at', x, y)
  }

  const handleRejected = (item) => {
    console.log('Rejected drop:', item)
  }
  </script>
