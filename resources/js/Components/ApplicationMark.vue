<template>
  <img 
    v-if="schoolLogo" 
    :src="schoolLogo" 
    :alt="schoolName || 'School Logo'" 
    class="object-contain"
  />
  <svg 
    v-else 
    viewBox="0 0 48 48" 
    fill="none" 
    xmlns="http://www.w3.org/2000/svg"
  >
    <path d="M11.395 44.428C4.557 40.198 0 32.632 0 24 0 10.745 10.745 0 24 0a23.891 23.891 0 0113.997 4.502c-.2 17.907-11.097 33.245-26.602 39.926z" fill="#6875F5" />
    <path d="M14.134 45.885A23.914 23.914 0 0024 48c13.255 0 24-10.745 24-24 0-3.516-.756-6.856-2.115-9.866-4.659 15.143-16.608 27.092-31.75 31.751z" fill="#6875F5" />
  </svg>
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
