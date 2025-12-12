<!--
  BACKWARD COMPATIBILITY: Both patterns working together
  
  This demonstrates how both old and new patterns can coexist during migration.
  This allows for gradual migration without breaking existing functionality.
-->

<template>
  <div class="backward-compatibility">
    <h3 class="font-medium mb-4">Backward Compatibility Example</h3>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <!-- Data Sources -->
      <div class="card">
        <h4 class="font-medium mb-2">Data Sources</h4>
        <div class="space-y-2 text-sm">
          <div class="flex justify-between">
            <span>Legacy System:</span>
            <span :class="legacyUser ? 'text-green-600' : 'text-red-600'">
              {{ legacyUser ? '✅ Available' : '❌ Not Available' }}
            </span>
          </div>
          <div class="flex justify-between">
            <span>New System:</span>
            <span :class="newUser ? 'text-green-600' : 'text-red-600'">
              {{ newUser ? '✅ Available' : '❌ Not Available' }}
            </span>
          </div>
          <div class="flex justify-between">
            <span>Currently Using:</span>
            <span class="font-medium text-blue-600">
              {{ usingNewSystem ? 'New System' : 'Legacy System' }}
            </span>
          </div>
        </div>
      </div>
      
      <!-- User Information -->
      <div class="card">
        <h4 class="font-medium mb-2">User Information</h4>
        <div v-if="displayUser" class="space-y-1 text-sm">
          <p><strong>Name:</strong> {{ displayUser.name }}</p>
          <p><strong>Email:</strong> {{ displayUser.email }}</p>
          <p><strong>Role:</strong> {{ displayUser.user_role }}</p>
          <p><strong>Roles:</strong> {{ displayUser.roles?.join(', ') || 'None' }}</p>
        </div>
        <div v-else class="text-gray-500 text-sm">
          No user data available
        </div>
      </div>
    </div>
    
    <!-- System Comparison -->
    <div class="mt-4">
      <h4 class="font-medium mb-2">System Comparison</h4>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Legacy System -->
        <div class="card bg-red-50 border-red-200">
          <h5 class="font-medium text-red-800 mb-2">Legacy System</h5>
          <div class="text-sm space-y-1">
            <div>Data Source: Inertia Props</div>
            <div>Loading State: {{ legacyUser ? 'Loaded' : 'Not Loaded' }}</div>
            <div>Caching: None</div>
            <div>Offline Support: None</div>
            <div>Error Handling: Manual</div>
          </div>
          
          <!-- Legacy role checking example -->
          <div class="mt-3 p-2 bg-red-100 rounded">
            <div class="text-xs text-red-700">Legacy Role Check:</div>
            <code class="text-xs">
              user?.user_role === 'teacher' || user?.roles?.includes('teacher')
            </code>
          </div>
        </div>
        
        <!-- New System -->
        <div class="card bg-green-50 border-green-200">
          <h5 class="font-medium text-green-800 mb-2">New System</h5>
          <div class="text-sm space-y-1">
            <div>Data Source: Composable + API</div>
            <div>Loading State: {{ isLoading ? 'Loading...' : 'Ready' }}</div>
            <div>Caching: 7-day offline cache</div>
            <div>Offline Support: Full</div>
            <div>Error Handling: Built-in</div>
          </div>
          
          <!-- New role checking example -->
          <div class="mt-3 p-2 bg-green-100 rounded">
            <div class="text-xs text-green-700">New Role Check:</div>
            <code class="text-xs">
              can('teacher') or isTeacher
            </code>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Migration Strategy -->
    <div class="mt-4 card bg-blue-50 border-blue-200">
      <h4 class="font-medium text-blue-800 mb-2">Migration Strategy</h4>
      <div class="text-sm text-blue-700 space-y-2">
        <p>
          <strong>Phase 1:</strong> Both systems run in parallel (current state)
        </p>
        <p>
          <strong>Phase 2:</strong> Gradually migrate components to use new system
        </p>
        <p>
          <strong>Phase 3:</strong> Remove legacy system once all components migrated
        </p>
        
        <div class="mt-3 p-2 bg-blue-100 rounded">
          <div class="text-xs font-medium">Migration Progress:</div>
          <div class="w-full bg-blue-200 rounded-full h-2 mt-1">
            <div class="bg-blue-600 h-2 rounded-full" style="width: 25%"></div>
          </div>
          <div class="text-xs mt-1">25% of components migrated</div>
        </div>
      </div>
    </div>
    
    <!-- Live Demo -->
    <div class="mt-4 card">
      <h4 class="font-medium mb-2">Live Demo: Role Checking</h4>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
        <!-- Legacy way -->
        <div>
          <div class="font-medium text-red-600 mb-1">Legacy Way:</div>
          <div class="space-y-1">
            <div>Is Teacher (legacy): {{ legacyIsTeacher ? '✅' : '❌' }}</div>
            <div>Is Student (legacy): {{ legacyIsStudent ? '✅' : '❌' }}</div>
            <div>Is Admin (legacy): {{ legacyIsAdmin ? '✅' : '❌' }}</div>
          </div>
        </div>
        
        <!-- New way -->
        <div>
          <div class="font-medium text-green-600 mb-1">New Way:</div>
          <div class="space-y-1">
            <div>Is Teacher (new): {{ isTeacher ? '✅' : '❌' }}</div>
            <div>Is Student (new): {{ isStudent ? '✅' : '❌' }}</div>
            <div>Is Admin (new): {{ isAdmin ? '✅' : '❌' }}</div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Actions -->
    <div class="mt-4 flex space-x-2">
      <button v-if="newUser" @click="refresh" :disabled="isLoading"
              class="px-3 py-1 bg-green-600 text-white rounded text-sm hover:bg-green-700 disabled:opacity-50">
        {{ isLoading ? 'Refreshing...' : 'Refresh New System' }}
      </button>
      <button @click="toggleSystem" 
              class="px-3 py-1 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">
        Prefer {{ usingNewSystem ? 'Legacy' : 'New' }} System
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useUser } from '@/composables/useUserContext.js';

// Legacy system (still works)
const legacyUser = computed(() => usePage().props.auth.user);

// New system
const { 
  user: newUser, 
  isTeacher, 
  isStudent, 
  isAdmin, 
  isLoading,
  refresh
} = useUser();

// Legacy role checking (verbose)
const legacyIsTeacher = computed(() => 
  legacyUser.value?.user_role === 'teacher' || 
  legacyUser.value?.roles?.includes('teacher')
);

const legacyIsStudent = computed(() => 
  legacyUser.value?.user_role === 'student' || 
  legacyUser.value?.roles?.includes('student')
);

const legacyIsAdmin = computed(() => 
  legacyUser.value?.user_role === 'admin' || 
  legacyUser.value?.roles?.includes('admin')
);

// System preference
const preferNewSystem = ref(true);

// Use new system if available and preferred, fallback to legacy
const displayUser = computed(() => {
  if (preferNewSystem.value && newUser.value) {
    return newUser.value;
  }
  return legacyUser.value;
});

const usingNewSystem = computed(() => 
  preferNewSystem.value && !!newUser.value
);

const toggleSystem = () => {
  preferNewSystem.value = !preferNewSystem.value;
};
</script>

<style scoped>
.backward-compatibility {
  @apply p-4;
}

.card {
  @apply p-4 border rounded-lg bg-white;
}

code {
  @apply bg-gray-200 px-1 rounded text-xs;
}
</style>
