<template>
      <DynamicTreeEditor />
    <!-- <EChartComponent
      title="LMS System Setup - Steps Mindmap"
      type="tree"
      :labels="[]"
      :datasets="treeData"
      :customOptions="customTreeOptions"
    /> -->
  </template>

  <script setup>
  import EChartComponent from './EChartComponent_v2.vue'
  import DynamicTreeEditor  from './DynamicTreeEditor.vue'

  const treeData = [
  {
    name: '📚 LMS System',
    children: [
      {
        name: '⚙️ Step 1: Setup',
        children: [
          { name: '🏫 Add Schools' },
          { name: '👨‍🏫 User Management' },
          { name: '🎓 School Stages & Grades' },
          { name: '🏫 Classrooms' },
          { name: '📘 Subjects' },
          { name: '👩‍🏫 Add Teachers' },
          { name: '👶 Students' },
          { name: '👨‍👩‍👧 Parents' },
          { name: '🗓️ Academic Year' },
          { name: '🗓️ Semester' }
        ]
      },
      {
        name: '📚 Step 2: Academic Management',
        children: [
          { name: '📚 Curriculums' },
          { name: '📖 Curriculum Lessons' },
          { name: '📝 Lesson Plan' },
          { name: '📆 Schedule' },
          { name: '📅 Schedule Daily' },
          { name: '❓ Question Bank' },
          { name: '🧩 Quiz System' },
          { name: '🧍 Attendance' },
          { name: '📈 Student Report' },
          { name: '🏆 Marks & Certificates' }
        ]
      },
      {
        name: '💬 Step 3: Communication Tools',
        children: [
          { name: '💬 Chat System' },
          { name: '🔔 Notifications' },
          { name: '🔗 Sharing Icons Everywhere' },
          { name: '🚫 Teacher-Only Blocks' }
        ]
      }
    ]
  }
]


  const customTreeOptions = {
    series: [
      {
        type: 'tree',
        data: treeData,
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
          borderRadius: 5,
          padding: 5,
          position: 'left',
          verticalAlign: 'middle',
          align: 'right',
          fontSize: 14
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
        animationDuration: 550,
        animationDurationUpdate: 750
      }
    ]
  }
  </script>
