<template>
  <img 
    v-if="schoolLogo" 
    :src="schoolLogo" 
    :alt="schoolName || 'School Logo'" 
    class="object-contain"
  />
  <img 
    v-else 
    src="/icon.png" 
    alt="Logo" 
    class="object-contain w-auto h-9"
  />
</template>

<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

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
