`<template>
  <q-btn-toggle
    v-model="localView"
    class="q-mx-sm"
    :options="[
      {
        label: 'Cards',
        value: 'card',
        icon: 'grid_view'
      },
      {
        label: 'Table',
        value: 'table',
        icon: 'table_view'
      }
    ]"
    color="primary"
    text-color="white"
    @update:model-value="$emit('update:view', $event)"
  />
</template>

<script>
export default {
  name: 'StudentViewToggle',
  props: {
    view: {
      type: String,
      required: true,
      validator: value => ['card', 'table'].includes(value)
    }
  },
  emits: ['update:view'],
  computed: {
    localView: {
      get() {
        return this.view
      },
      set(value) {
        this.$emit('update:view', value)
      }
    }
  }
}
</script>`