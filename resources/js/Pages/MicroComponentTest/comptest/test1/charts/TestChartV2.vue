<template>
  <div class="p-6">
    <h1 class="text-2xl font-bold mb-6">Chart Test Page</h1>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <!-- Line Chart -->
      <div>
        <EChartComponentV2
          title="Attendance per Week"
          type="line"
          :labels="['Week 1', 'Week 2', 'Week 3', 'Week 4']"
          :datasets="[
            { name: 'Present', data: [95, 90, 93, 97] },
            { name: 'Absent', data: [5, 10, 7, 3] }
          ]"
        />
      </div>
      
      <!-- Bar Chart -->
      <div>
        <EChartComponentV2
          title="Student Performance"
          type="bar"
          :labels="['Math', 'Science', 'English', 'History']"
          :datasets="[
            { name: 'Class Average', data: [75, 82, 78, 85] },
            { name: 'Top Student', data: [95, 98, 92, 90] }
          ]"
        />
      </div>
      
      <!-- Pie Chart -->
      <div>
        <EChartComponentV2
          title="Student Distribution"
          type="pie"
          :labels="['Grade A', 'Grade B', 'Grade C', 'Grade D']"
          :datasets="[
            {
              name: 'Students',
              data: [
                { value: 35, name: 'Grade A' },
                { value: 45, name: 'Grade B' },
                { value: 15, name: 'Grade C' },
                { value: 5, name: 'Grade D' }
              ]
            }
          ]"
          :customOptions="{
            series: [
              {
                radius: '70%',
                center: ['50%', '50%'],
                itemStyle: {
                  borderRadius: 5,
                  borderColor: '#fff',
                  borderWidth: 2
                }
              }
            ]
          }"
        />
      </div>
      
      <!-- Tree Chart -->
      <div>
        <EChartComponentV2
          title="Course Structure"
          type="tree"
          :datasets="[treeData]"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import EChartComponentV2 from './EChartComponent_v2.vue';

// Sample tree data
const treeData = {
  name: 'Mathematics',
  children: [
    {
      name: 'Algebra',
      children: [
        { name: 'Linear Algebra', icon: '📘' },
        { name: 'Abstract Algebra', icon: '📗' }
      ]
    },
    {
      name: 'Calculus',
      children: [
        { name: 'Differential Calculus', icon: '📕' },
        { name: 'Integral Calculus', icon: '📙' },
        { name: 'Multivariable Calculus', icon: '📓' }
      ]
    },
    {
      name: 'Geometry',
      children: [
        { name: 'Euclidean Geometry', icon: '📐' },
        { name: 'Non-Euclidean Geometry', icon: '📏' }
      ]
    }
  ]
};
</script>
