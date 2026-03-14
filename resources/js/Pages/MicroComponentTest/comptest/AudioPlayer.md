# AudioPlayer Component

A versatile, premium-styled audio player component for Vue 3 applications. It supports both minimal "sound effect" triggering and full media player controls.

## Features

- **Two Interaction Modes**:
  - **Instant Replay**: Ideal for sound effects (e.g., clicks, hits). Can be triggered repeatedly.
  - **Wait-to-Finish**: Ideal for notifications or speech. Blocks replay until current track ends.
- **Full Player UI**:
  - Play/Pause toggle
  - Progress bar with seeking
  - Time display (Current / Total)
  - Loop toggle
- **Responsive Design**: Adapts to container width.
- **Methods**: Exposes play/pause control via refs.

## Usage

### 1. Simple Sound Effect (Instant Replay)

Use for button clicks, interactions, or game sounds where rapid triggering is desired.

```vue
<script setup>
import AudioPlayer from '@/Pages/MicroComponentTest/comptest/AudioPlayer.vue';
</script>

<template>
  <AudioPlayer 
    src="/audio/click.mp3" 
    :allow-replay-when-playing="true"
    label="Click Me"
  />
</template>
```

### 2. Notification / Speech (Wait to Finish)

Use for success messages, error alerts, or narrations where you want to prevent overlapping playback.

```vue
<AudioPlayer 
  src="/audio/success.mp3" 
  :allow-replay-when-playing="false"
  label="Purchase Success"
/>
```

### 3. Full Music Player

Use for background music, podcasts, or longer audio tracks.

```vue
<AudioPlayer 
  src="/audio/background.mp3" 
  :show-controls="true"
  :loop="true"
  title="Ambient Music"
/>
```

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `src` | `String` | **Required** | Path to the audio file (e.g., `/audio/file.mp3`). |
| `showControls` | `Boolean` | `false` | If `true`, renders the full player UI. If `false`, renders a trigger button. |
| `allowReplayWhenPlaying` | `Boolean` | `true` | **Only for Button Mode**. If `true`, clicking restarts audio immediately. If `false`, clicking is ignored while playing. |
| `loop` | `Boolean` | `false` | Initial loop state. Can be toggled by user in Full UI mode. |
| `label` | `String` | `''` | Label text for the trigger button. |
| `title` | `String` | `''` | Title text displayed in the Full Player UI. |

## Events

Currently, the component does not emit custom events but relies on internal state logic. You can easily extend it to emit `started`, `paused`, or `ended` events if needed.

## Styling

The component uses **Tailwind CSS** for styling.
- **Light Mode**: Clean white/gray aesthetic with blue accents.
- **Dark Mode**: fully supported with `dark:` classes (zinc/slate palette).
