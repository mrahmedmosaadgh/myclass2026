<!-- components/RoadmapTree.vue -->
<template>
  <q-card flat bordered class="q-pa-md">
    <div class="text-h6 text-primary text-bold q-mb-md">
      {{ title }}
    </div>

    <q-tree
      :nodes="nodes"
      node-key="id"
      :default-expand-all="defaultExpand"
      selected-color="primary"
      v-model:selected="selected"
      @update:selected="handleNodeClick"
    >
      <template v-slot:default-header="prop">
        <div
          class="row items-center cursor-pointer"
          @click.stop="emitClick(prop.node)"
        >
          <q-icon
            :name="prop.node.icon || 'folder'"
            color="primary"
            class="q-mr-sm"
          />
          <div class="text-weight-medium">{{ prop.node.label }}</div>
        </div>
      </template>
    </q-tree>
  </q-card>
</template>

<script>
export default {
  name: "RoadmapTree",
  props: {
    title: { type: String, default: "Roadmap" },
    nodes: {
    type: [String, Array],
    default: () => []
  },
    defaultExpand: { type: Boolean, default: true },
  },
  data() {
    return {
      selected: "",
    };
  },
  methods: {
    emitClick(node) {
      this.$emit("node-click", node);
    },
    handleNodeClick(nodeId) {
      const findNode = (nodes, id) => {
        for (const node of nodes) {
          if (node.id === id) return node;
          if (node.children) {
            const found = findNode(node.children, id);
            if (found) return found;
          }
        }
        return null;
      };
      const node = findNode(this.nodes, nodeId);
      if (node) this.$emit("node-click", node);
    },
  },
};
</script>
