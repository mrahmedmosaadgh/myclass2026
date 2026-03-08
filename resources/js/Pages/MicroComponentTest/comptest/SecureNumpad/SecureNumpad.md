# SecureNumpad Component

A touch-friendly, secure input component that forces user input via a custom on-screen numeric keypad. It prevents usage of the physical keyboard, making it ideal for POS systems, kiosks, or pin pads. It also integrates audio feedback for every keypress.

## Features

- **Virtual Keypad**: 0-9 numeric input + Backspace + Clear.
- **Audio Feedback**: Uses `AudioPlayer` component to play a click sound on every press.
- **Visual Feedback**: Active states, animations, and focus indicators.
- **Secure Input**: `readonly` input field prevents physical keyboard typing.
- **Responsive**: Pop-over keypad design.

## Usage

```vue
<script setup>
import { ref } from 'vue';
import SecureNumpad from '@/Pages/MicroComponentTest/comptest/SecureNumpad/SecureNumpad.vue';

const pinCode = ref('');
const amount = ref('');
</script>

<template>
  <!-- Basic Usage -->
  <SecureNumpad v-model="pinCode" placeholder="Enter PIN" :max-length="4" />

  <!-- For Currency/Numbers -->
  <SecureNumpad v-model="amount" placeholder="0.00" />
</template>
```

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `modelValue` | `String` / `Number` | `''` | The bound value of the input. |
| `maxLength` | `Number` | `10` | Maximum number of digits allowed. |
| `placeholder` | `String` | `'0.00'` | Placeholder text when empty. |

## Dependencies

- Requires `AudioPlayer.vue` in the same directory (or update import path).
- Uses `public/audio/click/mixkit-mouse-click-close-1113.wav` for sound.
