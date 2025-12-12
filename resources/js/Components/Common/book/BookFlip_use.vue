<template>
  <div class="flex justify-center items-center min-h-screen bg-gray-100">
    <BookFlip :totalPages="totalPages">
      <template v-for="n in totalPages" :key="n">
        <template #[`front-${n-1}`]>
          <div class="w-full h-full bg-white flex items-center justify-center">
            <img
              :src="getImageForPage((n-1)*2)"
              class="max-w-full max-h-full object-contain"
              alt="page front"
            />
          </div>
        </template>
        <template #[`back-${n-1}`]>
          <div class="w-full h-full bg-white flex items-center justify-center">
            <img
              :src="getImageForPage((n-1)*2+1)"
              class="max-w-full max-h-full object-contain"
              alt="page back"
            />
          </div>
        </template>
      </template>
    </BookFlip>
  </div>
</template>

<script setup>
import BookFlip from '@/Components/Common/book/BookFlip.vue'
import { computed } from 'vue'

const imgs = [
  { name: '1 (1).png' },
  { name: '1 (2).png' },
  { name: '1 (3).png' },
  { name: '1 (4).png' },
  { name: '1 (5).png' },
  { name: '1 (6).png' }
]

const totalPages = computed(() => Math.ceil(imgs.length / 2))

const getImageForPage = (index) => {
  return imgs[index]?.name || ''
}
</script>

