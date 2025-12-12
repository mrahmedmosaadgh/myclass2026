<template>
  <q-table
    :title="title"
    :rows="rows"
    :columns="columns"
    row-key="id"
    flat
    bordered
    :pagination="{ rowsPerPage: 7 }"
  >
    <template v-slot:body-cell-status="props">
      <q-td :props="props">
        <q-badge :color="props.value === 'completed' ? 'green' : 'orange'">
          {{ props.value === 'completed' ? 'منجز' : 'معلق' }}
        </q-badge>
      </q-td>
    </template>
  </q-table>
</template>

<script setup>
defineProps({
  title: String,
  rows: Array,
  columns: Array
})
</script>
