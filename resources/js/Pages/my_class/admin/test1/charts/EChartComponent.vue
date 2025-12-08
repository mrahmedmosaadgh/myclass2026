<template>
    <div class="w-full p-4 bg-white dark:bg-gray-800 rounded-2xl shadow-md">
      <h2 v-if="title" class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">
        {{ title }}
      </h2>
      <v-chart
        :option="chartOptions"
        autoresize
        style="height: 300px;"
      />
    </div>
  </template>

  <script setup>
  import { computed } from 'vue'
  import { use } from "echarts/core"
  import VChart from "vue-echarts"
  import {
    CanvasRenderer
  } from 'echarts/renderers'
  import {
    BarChart,
    LineChart,
    PieChart,
    ScatterChart
  } from 'echarts/charts'
  import {
    TitleComponent,
    TooltipComponent,
    LegendComponent,
    GridComponent
  } from 'echarts/components'

  // Register only needed pieces (very light!)
  use([
    CanvasRenderer,
    BarChart,
    LineChart,
    PieChart,
    ScatterChart,
    TitleComponent,
    TooltipComponent,
    LegendComponent,
    GridComponent
  ])

  const props = defineProps({
    title: { type: String, default: '' },
    type: { type: String, default: 'bar' }, // 'bar', 'line', 'pie', etc.
    labels: { type: Array, required: true },
    datasets: { type: Array, required: true }, // [{ name: 'Series1', data: [...] }]
    customOptions: { type: Object, default: () => ({}) }
  })

  const chartOptions = computed(() => {
    return {
      tooltip: {
        trigger: 'axis'
      },
      legend: {
        top: 'bottom'
      },
      grid: {
        left: '3%',
        right: '4%',
        bottom: '10%',
        containLabel: true
      },
      xAxis: {
        type: 'category',
        data: props.labels
      },
      yAxis: {
        type: 'value'
      },
      series: props.datasets.map(dataset => ({
        ...dataset,
        type: props.type
      })),
      ...props.customOptions
    }
  })
  </script>

  <style scoped>
  /* Adjust dark/light mode smooth */
  </style>
