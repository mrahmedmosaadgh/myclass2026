<template>

selectedType:{{ selectedType }}{{ tt }}
  <RadioButtonGroup
    v-model="selectedType"
    name="officeType"
    :options="options"
    />
    <button
    @click="tt=!tt"

  >tt</button>
</template>

<script setup>
import { computed, ref } from 'vue';
import RadioButtonGroup from './RadioButtonGroup.vue';
// import CozyIcon from './icons/CozyIcon.vue';
// import GreenIcon from './icons/GreenIcon.vue';
const tt = ref(true);

// const selectedType = computed(()=> tt?'Cozy':'Green')
    const selectedType1 = computed(()=>{
    if(tt.value){
return 'Cozy';
    }
    if(!tt.value){
return 'Cozy';
    }
})
const options = ref([
  { value: tt.value?'Green':'Cozy', label: 'Cozy'  },
//   { value: 'Green', label: 'Green', icon: GreenIcon },
]);

const selectedType  =ref(selectedType1.value)
</script>
<style  >
._boxRadioButton_19yuc_202 {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 2px;
    width: 92px;
    height: 80px;
    border: 1px solid #dc8250;
    border-radius: 8px;
    cursor: pointer;
    transition: background-color .25s ease, filter .25s ease, transform .25s ease;
}

/* When radio input is checked */
input:checked ~ ._boxRadioButton_19yuc_202 {
    background-color: #213433;
    color: #e1e51b;
    border-color: transparent;
}

/* When radio input is disabled */
input:disabled ~ ._boxRadioButton_19yuc_202 {
    background-color: #0000001a;
    color: #807e7a;
    border-color: transparent;
}

/* Hover state when not checked and not disabled */
input:not(:checked):not(:disabled) ~ ._boxRadioButton_19yuc_202:hover {
    filter: drop-shadow(0 4px 8px rgba(0,0,0,.2));
    transform: translateY(-4px);
    background: #f8e7cf;
}
</style>

