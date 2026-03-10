<template>
  <div >
     <h1>Finger Drawing Demo</h1>
   <FingerDrawingDemo @export-json="saveToDatabase" />

    <DrawingPad />
    -----
    <h1>Drawing Player</h1>
    <DrawingPlayer />

 <h1>Drawing Editor</h1>
    <DrawingEditor />
  </div>
</template>

<script setup>
 import DrawingPlayer from './DrawingPlayer.vue'
import FingerDrawingDemo from './FingerDrawingDemo.vue'
// import RoadmapEditor from './RoadmapTree/RoadmapEditor.vue'
//  resources\js\Pages\my_table_mnger\reward_sys\RoadmapTree\RoadmapTreeEditor.vue
import DrawingEditor from './DrawingEditor.vue'

import DrawingPad from './DrawingPad.vue'
</script>

 