<template>
    <div class="p-0">
        <FlyingStars_usage></FlyingStars_usage>
        <card5_usage></card5_usage>
        <!-- resources/js/Pages/my_class/teacher/QuestionTypes/cards/card5_usage.vue -->
<button_v1></button_v1>
<my_tabs_v1
    v-model="selectedValue"
    :options="[
      { label: 'First', value: 1 },
      { label: 'Second', value: 2 },
      { label: 'Third', value: 3 },
      { label: 'Third', value: 4 }
    ]"
  />ppppppppppp
        <FireButton
        :start-color="'#ff0000'"
        :middle-color="'#ff8800'"
        :stop-color="'#ffff00'"
        :button-bg="'#222'"
        :text-color="'white'"
        :hover-text-color="'#ffd700'"
        :border-radius="15"
        :padding-x="2.5"
        :padding-y="1.2"
        :font-size="1.4"
        :animation-speed="12.5"
        :glow-intensity="2"
        :hoverLiftHeight="2"
        :press-depth="5"
        :enable-glow="true"
        :enable-3d="true"
        :enable-fire-effect="true"
    >
        Custom Fire Button
    </FireButton>
        <FireButton>Click Me</FireButton>
        <div class="p-0">
        <CelebrationButton @celebrate="handleWin">
        </CelebrationButton>
        <!-- <RippleButton @click="handleClick">
            Click Me
        </RippleButton> -->
    </div>

    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>





        <GameButton @click="    launchConfetti({
        colors: ['yellow','#FF0000','black', '#00FF00', '#0000FF'],
        particleCount:  350,
        spread: 800,
        origin: { y: 0.5 }
    })">
        Start Game! ⭐
    </GameButton>
    <GameButtonV2 @click="handleClick">
        Play Now! ✨
    </GameButtonV2>

        <card1></card1>
        <div class="p-4">
<div class="p-2 flex justify-center flex-row gap-4" >
 <div class="p-0  " v-for="item in 10 " :key="item">
    card2<card2></card2>

 </div>
</div>
        </div>
<card3></card3>
<p></p>
<p></p>
<p></p>
        <card1 cover-text="Course Details">
  <template #default>
    <h3>Main Content</h3>
    <p>Your content here</p>
  </template>

  <template #extra>
    <div>Additional information</div>
  </template>

  <template #cover>
    <div>Custom cover content</div>
  </template>
</card1>
<card1>
  <template #cover>
    <div class="flex flex-col items-center space-y-3">
      <h2 class="text-2xl font-bold text-white">Mathematics</h2>
      <div class="text-white/90 text-sm">Advanced Course</div>
    </div>
  </template>

  <div class="space-y-4">
    <h3 class="text-xl font-semibold text-indigo-900">Course Content</h3>
    <p class="text-gray-700">Explore advanced mathematical concepts...</p>
  </div>
</card1>
<p></p>
card1<card1>
  <template #cover>
    <div class="cover-content">
      <h2 class="cover-title">Mathematics</h2>
      <div class="cover-subtitle">Advanced Course</div>
    </div>
  </template>

  <div class="space-y-4">
    <h3 class="text-xl font-semibold text-indigo-900">Course Content</h3>
    <p class="text-gray-700">Explore advanced mathematical concepts...</p>
  </div>
</card1>
<p></p>
<card1>
  <template #cover>
    <div class="cover-content">
      <h2 class="cover-title">Mathematics</h2>
      <div class="cover-subtitle">Advanced Course</div>
    </div>
  </template>

  <div class="space-y-4">
    <h3 class="text-xl font-semibold text-indigo-900">Course Content</h3>
    <p class="text-gray-700">Explore advanced mathematical concepts...</p>
  </div>
</card1>
<p></p>
<p></p>
<card1
  title="Mathematics"
  subtitle="Advanced Course"
>
  <div class="space-y-4">
    <h3 class="text-xl font-semibold text-indigo-900">Course Content</h3>
    <p class="text-gray-700">Your content here...</p>
  </div>
</card1>
               <!-- <BookFlip_use></BookFlip_use> -->
               <card1
  title="Mathematics Advanced Course With Very Long Title"
  subtitle="This is a longer subtitle that will be properly truncated if it exceeds two lines"
>
  <div class="space-y-4">
    <h3 class="text-xl font-semibold text-indigo-900">Course Content</h3>
    <p class="text-gray-700">Your content here...</p>
  </div>
</card1>
card1<card1
  title="Mathematics dfsgdg"
  subtitle="Advanced Course"
>
  <div class="space-y-4">
    <h3 class="text-xl font-semibold text-indigo-900">Course Content</h3>
    <p class="text-gray-700">Your content here...</p>
  </div>
</card1>

<br>
<br>
<br>
<br>

<!-- Default colors (indigo gradient) -->
<card1
  title="Mathematics"
  subtitle="Advanced Course"
/>

<!-- Custom colors -->
<card1
  title="Biology"
  subtitle="Advanced Course"
  start-color="#22c55e"
  end-color="#16a34a"
/>

<!-- Using hex, rgb, or rgba values -->
<!-- Default colors (indigo gradient) -->
<card1
  title="Mathematics"
  subtitle="Advanced Course"
/>

<!-- Custom colors -->
<card1
  title="Biology"
  subtitle="Advanced Course"
  start-color="#22c55e"
  end-color="#16a34a"
/>

<!-- Using hex, rgb, or rgba values -->
<card1
  title="Chemistry"
  subtitle="Advanced Course"
  start-color="rgba(249, 115, 22, 0.9)"
  end-color="rgb(194, 65, 12)"
/>

 <br>
<br>




<card1
  title="Mathematics"
  subtitle="Advanced Course"
  start-color="#4f46e5"
  end-color="#6366f1"
>
  <div class="p-4 space-y-4">
    <div class="flex items-center space-x-3">
      <svg class="w-6 h-6 text-indigo-600"   ></svg>
      <h3 class="text-lg font-semibold">Course Details</h3>
    </div>
    <ul class="list-disc list-inside text-gray-600 space-y-2">
      <li>Advanced Topics</li>
      <li>Interactive Exercises</li>
      <li>Video Lectures</li>
    </ul>
    <div class="mt-4 flex justify-end">
      <button class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
        Start Learninggggg
      </button>
    </div>
  </div>
</card1>




    </div>
</template>
<script setup>
import { ref } from 'vue';
import card1 from './cards/card1.vue';
import card2 from './cards/card2.vue';
import card3 from './cards/card3.vue';
//  import card1 from '@/Pages/my_class/admin/Teacher/card1.vue';
import GameButton from './cards/GameButton.vue';
import GameButtonV2 from './cards/GameButtonV2.vue';
import RippleButton from './cards/RippleButton.vue';
import CelebrationButton from './cards/CelebrationButton.vue';
import FireButton from './cards/FireButton.vue';
import my_tabs_v1 from './cards/my_tabs_v1.vue';
import button_v1 from './cards/button_v1.vue';
import card5_usage from './cards/card5_usage.vue';
import FlyingStars_usage from './cards/FlyingStars_usage.vue';

// resources/js/Pages/my_class/teacher/QuestionTypes/cards/button_v1.vue
import { useConfetti } from '@/composables/useConfetti'

const { launchConfetti, patterns } = useConfetti()

const selectedValue = ref(1);
// import RippleButton from '@/Components/RippleButton.vue';
const handleClick = () => {
    // Your click handler logic
};

const handleWin = () => {
  console.log('Player won! 🎉')
  // Update score, show modal, etc.
}
</script>

