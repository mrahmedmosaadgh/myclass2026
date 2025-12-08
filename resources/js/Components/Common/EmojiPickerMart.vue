<!--
<EmojiPicker @select="onEmojiSelected" />
import EmojiPicker from "@/components/Common/EmojiPicker.vue";
-->
<template>
    <div class="emoji-picker">
      <Picker :data="emojiIndex" set="twitter" @select="selectEmoji" />
    </div>
  </template>

  <script>
  import data from "emoji-mart-vue-fast/data/all.json";
  import "emoji-mart-vue-fast/css/emoji-mart.css";

  import { Picker, EmojiIndex } from "emoji-mart-vue-fast";

  const emojiIndex = new EmojiIndex(data);

  export default {
    name: "EmojiPicker",
    components: { Picker },
    data() {
      return {
        emojiIndex,
      };
    },
    methods: {
      selectEmoji(emoji) {
        this.$emit("select", emoji.native);
      },
    },
  };
  </script>

  <style scoped>
  .emoji-picker {
    max-width: 320px;
  }
  </style>
