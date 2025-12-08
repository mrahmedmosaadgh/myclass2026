<template>
  <div class="qr-code-container">
    <qrcode-vue
      :value="value"
      :size="size"
      :level="level"
      :background="background"
      :foreground="foreground"
      :class="className"
    />
  </div>
</template>

<script>
import QrcodeVue from 'qrcode.vue'

export default {
  name: 'QrCode',
  components: {
    QrcodeVue
  },
  props: {
    // The value to encode in the QR code
    value: {
      type: String,
      required: true
    },
    // Size of the QR code in pixels
    size: {
      type: Number,
      default: 200
    },
    // Error correction level (L, M, Q, H)
    level: {
      type: String,
      default: 'H',
      validator: (value) => ['L', 'M', 'Q', 'H'].includes(value)
    },
    // Background color
    background: {
      type: String,
      default: '#FFFFFF'
    },
    // Foreground color
    foreground: {
      type: String,
      default: '#000000'
    },
    // Additional CSS class
    className: {
      type: String,
      default: ''
    }
  }
}
</script>

<style scoped>
.qr-code-container {
  display: inline-block;
}
</style>
