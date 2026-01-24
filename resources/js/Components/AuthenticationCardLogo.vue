<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const page = usePage()

const schoolLogo = computed(() => {
  // Try user_context first (new structure)
  const userContext = page.props.user_context
  if (userContext?.user_school?.school && Array.isArray(userContext.user_school.school) && userContext.user_school.school.length > 0) {
    return userContext.user_school.school[0].logo_url
  }
  
  // Fallback to auth.user.school (backward compatibility)
  const authUser = page.props.auth?.user
  if (authUser?.school && Array.isArray(authUser.school) && authUser.school.length > 0) {
    return authUser.school[0].logo_url
  }
  
  return null
})

const schoolName = computed(() => {
  // Try user_context first (new structure)
  const userContext = page.props.user_context
  if (userContext?.user_school?.school && Array.isArray(userContext.user_school.school) && userContext.user_school.school.length > 0) {
    return userContext.user_school.school[0].name
  }
  
  // Fallback to auth.user.school (backward compatibility)
  const authUser = page.props.auth?.user
  if (authUser?.school && Array.isArray(authUser.school) && authUser.school.length > 0) {
    return authUser.school[0].name
  }
  
  return 'School'
})
</script>

<template>
  <Link :href="'/'">
    <img 
      v-if="schoolLogo" 
      :src="schoolLogo" 
      :alt="schoolName || 'School Logo'" 
      class="size-16 object-contain"
    />
    <img
      v-else
      class="size-16 object-contain"
      src="/icon.png"
      alt="Logo"
    />
  </Link>
</template>
