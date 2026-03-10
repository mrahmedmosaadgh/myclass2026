<template>
 
    <q-card flat bordered class="q-pa-md">
      <div class="text-h5 text-primary text-bold q-mb-md">
        LMS Project Roadmap 🌳
      </div>

      <q-tree
        :nodes="tree"
        node-key="id"
        default-expand-all
        accordion
        icon="folder"
        selected-color="primary"
        v-model:selected="selected"
        @update:selected="onNodeSelect"
      >
        <template v-slot:default-header="prop">
          <div class="row items-center">
            <q-icon
              :name="prop.node.icon || 'folder'"
              color="primary"
              class="q-mr-sm"
            />
            <div class="text-weight-medium cursor-pointer">
              {{ prop.node.label }}
            </div>
          </div>
        </template>
      </q-tree>
    </q-card>
 
</template>

<script>
import { router } from "@inertiajs/vue3";

export default {
  name: "LmsRoadmapTree",
  data() {
    return {
      selected: "",
      tree: [
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
                {
                  id: "subjects",
                  label: "Subjects & Lessons",
                  route: "/curriculum/subjects",
                },
              ],
            },
            {
              id: "quiz",
              label: "Quiz & Assessment System",
              icon: "quiz",
              children: [
                { id: "bank", label: "Question Bank", route: "/quiz/questions" },
                { id: "skills", label: "Skill Management", route: "/quiz/skills" },
                { id: "import", label: "Excel Import", route: "/quiz/import" },
              ],
            },
            {
              id: "classroom",
              label: "Classroom Management",
              icon: "people",
              children: [
                { id: "dashboard", label: "Teacher Dashboard", route: "/teacher/dashboard" },
                { id: "behavior", label: "Behavior Points", route: "/behavior" },
              ],
            },
            {
              id: "scoring",
              label: "Scoring & Gamification",
              icon: "military_tech",
              children: [
                { id: "groups", label: "Group Points", route: "/scoring/groups" },
                { id: "leaderboard", label: "Leaderboards", route: "/scoring/leaderboard" },
              ],
            },
            {
              id: "offline",
              label: "Offline & Sync System",
              icon: "sync",
              children: [
                { id: "dexie", label: "Dexie.js Integration", route: "/offline/dexie" },
                { id: "conflict", label: "Conflict Resolution", route: "/offline/conflict" },
              ],
            },
            {
              id: "timetable",
              label: "Timetable & Scheduling",
              icon: "calendar_month",
              children: [
                { id: "view", label: "Timetable View", route: "/timetable" },
                { id: "flex", label: "Flexible Scheduling", route: "/timetable/flex" },
              ],
            },
            {
              id: "future",
              label: "Future Pipeline 🚀",
              icon: "rocket_launch",
              children: [
                { id: "ai", label: "AI Assistant", route: "/future/ai" },
                { id: "analytics", label: "Analytics Dashboard", route: "/future/analytics" },
              ],
            },
          ],
        },
      ],
    };
  },
  methods: {
    onNodeSelect(nodeId) {
        console.log('NodeClick-----------------2');
      const node = this.findNode(this.tree, nodeId);
      if (node && node.route) {
        // router.visit(node.route);
        console.log(node?.label);
        console.log('ppppppppppp');
      }
    },
    findNode(nodes, id) {
      for (const node of nodes) {
        if (node.id === id) return node;
        if (node.children) {
          const found = this.findNode(node.children, id);
          if (found) return found;
        }
      }
      return null;
    },
  },
};
</script>
