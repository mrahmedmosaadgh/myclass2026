<template>
    <div class="w-full p-4 bg-white dark:bg-gray-800 rounded-2xl shadow-md">
      <div class="flex justify-between items-center mb-4">
        <h2 v-if="title" class="text-lg font-semibold text-gray-900 dark:text-white">
          {{ title }}
        </h2>
        <button
          @click="downloadChart"
          class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-md shadow"
        >
          Download as Image
        </button>
      </div>
      <v-chart
        ref="chartRef"
        :option="chartOptions"
        autoresize
        style="height: 600px;"
      />
    </div>
  </template>

  <script setup>
  import { ref, computed } from 'vue'
  import { use } from 'echarts/core'
  import VChart from "vue-echarts"
  import {
    CanvasRenderer
  } from 'echarts/renderers'
  import {
    TreeChart,
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

  // Register all chart types to ensure compatibility
  use([
    CanvasRenderer,
    TreeChart,
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
    type: { type: String, default: 'line' }, // 'bar', 'line', 'pie', 'tree', etc.
    labels: { type: Array, default: () => [] },
    datasets: { type: Array, required: true },
    customOptions: { type: Object, default: () => ({}) }
  })

  const chartRef = ref(null)

  const chartOptions = computed(() => {
    // Handle tree chart type differently
    if (props.type === 'tree') {
      return {
        tooltip: {
          trigger: 'item',
          triggerOn: 'mousemove'
        },
        series: [
          {
            type: 'tree',
            data: props.datasets,
            top: '5%',
            left: '10%',
            bottom: '5%',
            right: '20%',
            symbol: 'circle',
            symbolSize: 10,
            edgeShape: 'polyline',
            edgeForkPosition: '63%',
            initialTreeDepth: -1,
            lineStyle: {
              width: 2,
              color: '#4f46e5'
            },
            label: {
              backgroundColor: '#fff',
              borderColor: '#ddd',
              borderWidth: 1,
              borderRadius: 6,
              padding: 6,
              position: 'left',
              verticalAlign: 'middle',
              align: 'right',
              fontSize: 14,
              formatter: function(params) {
                return `{icon|${params.data.icon || '📄'}} ${params.name}`
              },
              rich: {
                icon: {
                  fontSize: 16,
                  lineHeight: 20
                }
              }
            },
            leaves: {
              label: {
                position: 'right',
                verticalAlign: 'middle',
                align: 'left'
              }
            },
            emphasis: {
              focus: 'descendant'
            },
            expandAndCollapse: true,
            animationDuration: 800,
            animationDurationUpdate: 800,
            animationEasing: 'bounceOut'
          }
        ],
        ...props.customOptions
      }
    } else {
      // Standard chart types (bar, line, pie, etc.)
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
    }
  })

  const downloadChart = () => {
    if (chartRef.value) {
      // In vue-echarts v7, we need to access the chart instance through the exposed API
      try {
        // Get the echarts instance
        const instance = chartRef.value.chart

        if (instance && typeof instance.getDataURL === 'function') {
          const img = instance.getDataURL({
            pixelRatio: 2,
            backgroundColor: '#fff'
          })

          const link = document.createElement('a')
          link.href = img
          link.download = 'chart-export.png'
          link.click()
        } else {
          console.error('Chart instance does not have getDataURL method')
        }
      } catch (error) {
        console.error('Error downloading chart:', error)
      }
    }
  }
  </script>

  <style scoped>
  /* Smooth dark/light background */
  </style>
