<!-- pages/LmsRoadmapTree.vue -->
<template>
  <div class="q-pa-md bg-grey-2">
    <roadmap-tree
      title="LMS Project Roadmap 🌳"
      :nodes="lmsTree"
      :defaultExpand="true"
      @node-click="handleNodeClick"
    />
  </div>
</template>

<script>
import { router } from "@inertiajs/vue3";
import RoadmapTree from "./RoadmapTree.vue";

export default {
  name: "LmsRoadmapTree",
  components: { RoadmapTree },
  data() {
    return {
      lmsTree: [
        {
          id: "root",
          label: "Learning Management System",
          icon: "school",
          children: [
            {
              id: "foundation",
              label: "Foundation Layer",
              icon: "settings",
              children: [
                {
                  id: "tech",
                  label: "Tech Stack: Laravel + Inertia + Vue",
                  route: "/system/tech-stack",
                },
                {
                  id: "auth",
                  label: "Jetstream Auth & Roles",
                  route: "/system/auth",
                },
                {
                  id: "realtime",
                  label: "Realtime via Firebase",
                  route: "/system/realtime",
                },
              ],
            },
            {
              id: "curriculum",
              label: "Curriculum & Lesson Planning",
              icon: "book",
              children: [
                {
                  id: "structure",
                  label: "Curriculum Map",
                  route: "/curriculum/map",
                },
                {
                  id: "plans",
                  label: "Weekly Plans",
                  route: "/curriculum/weekly-plans",
                },
              ],
            },
            {
              id: "quiz",
              label: "Quiz & Assessment System",
              icon: "quiz",
              children: [
                {
                  id: "bank",
                  label: "Question Bank",
                  route: "/quiz/questions",
                },
                {
                  id: "skills",
                  label: "Skill Management",
                  route: "/quiz/skills",
                },
              ],
            },
            {
              id: "behavior",
              label: "Behavior Management",
              icon: "emoji_events",
              route: "/behavior",
            },
            {
              id: "future",
              label: "Future Pipeline 🚀",
              icon: "rocket_launch",
              children: [
                {
                  id: "ai",
                  label: "AI Assistant",
                  route: "/future/ai",
                },
              ],
            },
          ],
        },
      ],
    };
  },
  methods: {
    handleNodeClick(node) {
        console.log('NodeClick-----------------1');

      if (node.route) {
        // router.visit(node.route);
        console.log('node-----------------11111111');
        console.log(node);

      } else {
        console.log("Node clicked:", node.label);
      }
    },
  },
};
</script>
