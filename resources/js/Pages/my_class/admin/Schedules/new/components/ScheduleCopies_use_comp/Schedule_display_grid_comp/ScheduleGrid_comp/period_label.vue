<template>
    <div  class="flex   flex-nowrap  www  ">
        <div class="p-0 w-0.5 bg-gray-400">  </div>        <!-- Lock/Unlock Icons -->


        <div
          v-for="(period, periodIndex) in [1,2,3,4,5,6,7,8]"
          :key="periodIndex"
          class="period-cell  relative pl-2 p-0.5"
          :class="hover_period.period==periodIndex&&dayIndex==hover_period.day?'  text-xl text-blue-800 bg-gray-400    ':' '"
          @mouseenter="$emit('period',period)"
          >
          <div
          :class="selected_period.period==periodIndex&&dayIndex==selected_period.day?'bg-red-500  text-xl text-white scale-125 rounded z-20':' '"
class="p-0"
>{{period}}</div>

        </div>
</div>

</template>
<script setup>


const props = defineProps({
    hover_period: {
    type: Object,
    required: false,
    default:
    {day:null,period:null,teacher:null}
  },
  selected_period: {
    type: Object,
    required: false,
    default:
    {day:null,period:null,teacher:null}
  },

  dayIndex: { required: false}



})
</script>
<style scoped>




.period-cell {
  width: 40px;
  height: 50px;

  aspect-ratio: 1;
  box-sizing: border-box;
  border: 0.5px solid #e2e8f0;
  cursor: pointer;

  
  transition: all 0.2s;
  display: flex;
  flex-direction: column;
  justify-content: center;
  background: white;
  overflow: hidden;
  font-size: 0.7rem;
  text-align: center;
}
  .period-cell:hover {
    background: #f5f5f5;
    cursor: pointer;
    color:red
  }
  </style>
