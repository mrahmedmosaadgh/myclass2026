<template>
  <div class="_controlGroup_19yuc_175 relative overflow-visible   " :class="my_class">
   <div class="  h-6"></div>
    <div class="  mb-2 absolute flex gap-2 scale-75  " >

           <button
        v-for="subject in uniqueSubjects"
        :key="subject.id"
        @click="filterBySubject(subject)"
        class="px-2 py-1 rounded-full text-xs"
        :class="[
          selectedSubject?.id === subject.id
            ? 'ring-2 ring-offset-1'
            : '',
        ]"
        :style="`background-color: ${subject.color_bg}; color: ${subject.color_text}`"
      >
        {{ subject.name }}
      </button>
      <button
      v-if="selectedSubject"
      @click="clearSubjectFilter"
      class="px-2 py-1 rounded-full text-xs bg-gray-200 hover:bg-gray-300"
      >
      Clear
    </button>
</div>
<div class="  flex flex-wrap justify-center w-full ">



    <label v-for="(option, index) in filteredOptions" :key="index"

    @click="$emit('set_data', option)"
    v-show="!option?.day||!option?.period_number"
    class="relative "
    >
    <span class="period-badge bg-black px-2 rounded-full text-white   scale-50 absolute ">
              {{ option?.period_order }}
          </span>
    <!-- add here list of unique scubjects as badjets at clik filter by subject -->
    <!--  -->
      <input class="_hiddenRadioInput_u1tst_155 sf-hidden "
        type="radio"
        v-model="model"
        :name="name"
        :value="option.value"
        >
        <!-- :checked="model === option.value"
        @change="model = option.value" -->
      <!-- :disabled="option[disabled]" -->
      <div
      class="_boxRadioButton_19yuc_202 w-18 h-18 !border-4  border-solid   "
      :style="`background-color: ${option?.cst?.subject?.color_bg};color:${option?.cst?.subject?.color_text};${model?'border-color:green':'border-color:white'};`"
      >
        <!-- <component
          :is="option.icon"
          class="_icon_1l3zf_1"
          style="height:22px;width:32px"
        /> -->
        <!-- <div class="scale-75  ">   {{ option?.cst?.subject?.color_bg }}   </div>
        <div class="scale-75  ">   {{ option?.cst?.subject?.color_text }}   </div> -->
        <span class="p-0 ">   {{ option?.cst?.classroom?.name }}   </span>
        <!-- <span class="p-0 ">   {{ option?.cst?.classroom?.name }}   </span> -->
        <!-- <div class="_text_hkjt4_94">   {{ option?.cst?.teacher?.name }}   </div> -->
        <div class=" bg-blue-800 px-2 rounded-full scale-75 text-white">   {{ option?.cst?.subject?.name }}   </div>
        <NameAbbreviator class="scale-75 "
        :full-name="option?.cst?.teacher?.name"
        separator=" "
        :letters_count="2"
        />
    <!-- <NameAbbreviator class="bg-blue-800 px-2 rounded-full scale-75 text-white"
        :full-name="option?.cst?.teacher?.name"
        separator=" "
        :letters_count="2"
    /> -->

<!-- <details>

    <pre >
        {{ option?.cst?.subject?.name  }}
    </pre>
</details> -->
      </div>
    </label>
</div>
  </div>
</template>

<script setup>
import NameAbbreviator from './NameAbbreviator2.vue';
import { computed, ref } from 'vue';

const model = defineModel();
const selectedSubject = ref(null);

const props = defineProps({
  name: {
    type: String,
    required: true
  },
  my_class: {
    type: String,
    default: ''
  },
  options: {
    type: Array,
    required: true,
  }
});

const emit = defineEmits(['filter-change', 'set_data' ]);

// Get unique subjects from options
const uniqueSubjects = computed(() => {
  const subjects = props.options
    .map(option => option.cst?.subject)
    .filter(subject => subject); // Remove null/undefined

  return [...new Map(subjects.map(item => [item.id, item])).values()];
});
// Filter out null/undefined options
const filteredOptions = computed(() => {
  if (!selectedSubject.value) {
    return props.options.filter(option => option != null);
  }
  return props.options.filter(option =>
    option != null &&
    option.cst?.subject?.id === selectedSubject.value.id
  );
});
const filterBySubject = (subject) => {
  selectedSubject.value = subject;
  emit('filter-change', { subjectId: subject.id });
};

const clearSubjectFilter = () => {
  selectedSubject.value = null;
  emit('filter-change', { subjectId: null });
};
</script>




