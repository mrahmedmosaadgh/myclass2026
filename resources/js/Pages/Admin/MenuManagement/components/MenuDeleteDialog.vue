<template>
  <q-dialog :model-value="modelValue" @update:model-value="$emit('update:modelValue', $event)">
    <q-card style="min-width: 400px">
      <q-card-section class="row items-center q-pb-none">
        <q-icon name="warning" color="warning" size="md" class="q-mr-sm" />
        <div class="text-h6">Delete Menu</div>
        <q-space />
        <q-btn icon="close" flat round dense @click="$emit('cancel')" />
      </q-card-section>

      <q-card-section>
        <div class="text-body1 q-mb-md">
          Are you sure you want to delete <strong>{{ menu?.label }}</strong>?
        </div>

        <q-banner v-if="menu?.children && menu.children.length > 0" class="bg-warning text-white" rounded>
          <template v-slot:avatar>
            <q-icon name="info" />
          </template>
          <div class="text-subtitle2">Warning: Cascade Delete</div>
          <div class="text-body2">
            This menu has {{ menu.children.length }} child menu(s) that will also be deleted:
          </div>
          <ul class="q-mt-sm q-mb-none">
            <li v-for="child in menu.children" :key="child.id">
              {{ child.label }}
            </li>
          </ul>
        </q-banner>

        <div class="text-caption text-grey-7 q-mt-md">
          This action cannot be undone.
        </div>
      </q-card-section>

      <q-card-actions align="right">
        <q-btn flat label="Cancel" color="grey-7" @click="$emit('cancel')" />
        <q-btn
          unelevated
          label="Delete"
          color="negative"
          @click="$emit('confirm')"
        />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script setup>
defineProps({
  modelValue: Boolean,
  menu: {
    type: Object,
    default: null,
  },
});

defineEmits(['update:modelValue', 'confirm', 'cancel']);
</script>
