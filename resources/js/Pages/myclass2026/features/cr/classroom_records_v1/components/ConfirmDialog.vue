<script setup>
import { useQuasar } from 'quasar'

const props = defineProps({
  title: {
    type: String,
    default: 'Confirm Action'
  },
  message: {
    type: String,
    default: 'Are you sure you want to continue?'
  },
  okLabel: {
    type: String,
    default: 'OK'
  },
  cancelLabel: {
    type: String,
    default: 'Cancel'
  },
  okColor: {
    type: String,
    default: 'primary'
  },
  cancelColor: {
    type: String,
    default: 'grey-7'
  },
  persistent: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['ok', 'cancel'])

const $q = useQuasar()

const show = () => {
  $q.dialog({
    title: props.title,
    message: props.message,
    html: true,
    ok: {
      label: props.okLabel,
      color: props.okColor
    },
    cancel: {
      label: props.cancelLabel,
      color: props.cancelColor
    },
    persistent: props.persistent,
    style: 'border-radius: 12px',
    'class': 'text-center'
  }).onOk(() => {
    emit('ok')
  }).onCancel(() => {
    emit('cancel')
  })
}

// Expose show method to parent
defineExpose({
  show
})
</script>

<template>
  <!-- This component doesn't render anything directly -->
  <!-- It only provides the show() method for programmatic use -->
</template>
