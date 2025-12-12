<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
  href: {
    type: String,
    required: true
  },
  method: {
    type: String,
    default: 'get'
  },
  as: {
    type: String,
    default: 'a'
  },
  data: {
    type: Object,
    default: () => ({})
  },
  preserveScroll: {
    type: Boolean,
    default: false
  },
  preserveState: {
    type: [Boolean, String],
    default: false
  },
  replace: {
    type: Boolean,
    default: false
  },
  only: {
    type: Array,
    default: () => []
  },
  headers: {
    type: Object,
    default: () => ({})
  },
  queryStringArrayFormat: {
    type: String,
    default: 'brackets'
  }
});
</script>

<template>
  <Link
    :href="href"
    :method="method"
    :as="as"
    :data="data"
    :preserve-scroll="preserveScroll"
    :preserve-state="preserveState"
    :replace="replace"
    :only="only"
    :headers="headers"
    :query-string-array-format="queryStringArrayFormat"
  >
    <slot />
  </Link>
</template>

<style scoped>
/* Make sure the link doesn't interfere with the styling of its children */
a {
  text-decoration: none;
  color: inherit;
  display: block;
}
</style>
