<template>
  <div class="_controlGroup_19yuc_175">
    <label v-for="option in options" :key="option.value">
      <input 
        class="_hiddenRadioInput_u1tst_155 sf-hidden" 
        type="radio" 
        :name="name"
        :value="option.value"
        :checked="modelValue === option.value"
        @change="$emit('update:modelValue', option.value)"
      >
      <div class="_boxRadioButton_19yuc_202">
        <component 
          :is="option.icon" 
          class="_icon_1l3zf_1" 
          style="height:32px;width:32px"
        />
        <div class="_text_hkjt4_94">{{ option.label }}</div>
      </div>
    </label>
  </div>
</template>

<script setup>
defineProps({
  modelValue: {
    type: [String, Number],
    required: true
  },
  name: {
    type: String,
    required: true
  },
  options: {
    type: Array,
    required: true,
    // Each option should have: { value, label, icon }
  }
});

defineEmits(['update:modelValue']);
</script>

<style scoped>
._hiddenRadioInput_u1tst_155 {
  display: none;
}

._boxRadioButton_19yuc_202 {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px;
  border: 1px solid #ccc;
  border-radius: 4px;
  cursor: pointer;
}

._icon_1l3zf_1 {
  flex-shrink: 0;
}

._text_hkjt4_94 {
  font-size: 14px;
}

._controlGroup_19yuc_175 {
  display: flex;
  gap: 8px;
}
</style>