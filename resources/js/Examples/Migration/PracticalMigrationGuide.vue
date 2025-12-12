<!--
  Practical Migration Guide
  
  This component provides step-by-step instructions for migrating existing
  components from the old auth.user pattern to the new user context system.
-->

<template>
  <div class="practical-migration-guide p-6 space-y-8">
    <h1 class="text-3xl font-bold mb-8">Practical Migration Guide</h1>

    <!-- Step 1: Identify Components -->
    <section class="bg-white rounded-lg shadow p-6">
      <h2 class="text-xl font-semibold mb-4">Step 1: Identify Components to Migrate</h2>
      
      <div class="space-y-4">
        <p class="text-gray-700">
          First, find all components that use the old <code>usePage().props.auth.user</code> pattern.
          Here are the components we found in your codebase:
        </p>
        
        <div class="bg-gray-50 p-4 rounded">
          <h3 class="font-medium mb-2">Components Using Old Pattern:</h3>
          <ul class="space-y-1 text-sm">
            <li class="flex justify-between">
              <span>📄 Dashboard/Components/TeacherDashboard.vue</span>
              <span class="text-blue-600">→ Use useTeacher()</span>
            </li>
            <li class="flex justify-between">
              <span>📄 Dashboard/Components/StudentDashboard.vue</span>
              <span class="text-blue-600">→ Use useStudent()</span>
            </li>
            <li class="flex justify-between">
              <span>📄 Dashboard/Components/AdminDashboard.vue</span>
              <span class="text-blue-600">→ Use useUser()</span>
            </li>
            <li class="flex justify-between">
              <span>📄 Dashboard/Components/ParentDashboard.vue</span>
              <span class="text-blue-600">→ Use useUser()</span>
            </li>
            <li class="flex justify-between">
              <span>📄 Dashboard.vue</span>
              <span class="text-blue-600">→ Use useUser()</span>
            </li>
            <li class="flex justify-between">
              <span>📄 Layouts/comp/SidebarMenu.vue</span>
              <span class="text-blue-600">→ Use useUser()</span>
            </li>
            <li class="flex justify-between">
              <span>📄 Chat/Index.vue</span>
              <span class="text-blue-600">→ Use useUser()</span>
            </li>
          </ul>
        </div>
        
        <div class="bg-blue-50 p-4 rounded">
          <h3 class="font-medium text-blue-800 mb-2">Search Command:</h3>
          <code class="text-sm">grep -r "usePage().props.auth.user" resources/js/</code>
        </div>
      </div>
    </section>

    <!-- Step 2: Choose Composable -->
    <section class="bg-white rounded-lg shadow p-6">
      <h2 class="text-xl font-semibold mb-4">Step 2: Choose the Right Composable</h2>
      
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="card bg-blue-50 border-blue-200">
          <h3 class="font-medium text-blue-800">useUser()</h3>
          <p class="text-sm text-blue-700 mt-1">Basic user info and role checks</p>
          <div class="mt-2 text-xs text-blue-600">
            <div>✓ Profile data</div>
            <div>✓ Role checking</div>
            <div>✓ Fast loading</div>
          </div>
        </div>
        
        <div class="card bg-green-50 border-green-200">
          <h3 class="font-medium text-green-800">useTeacher()</h3>
          <p class="text-sm text-green-700 mt-1">Teacher-specific features</p>
          <div class="mt-2 text-xs text-green-600">
            <div>✓ Classroom data</div>
            <div>✓ School info</div>
            <div>✓ Schedule</div>
          </div>
        </div>
        
        <div class="card bg-purple-50 border-purple-200">
          <h3 class="font-medium text-purple-800">useStudent()</h3>
          <p class="text-sm text-purple-700 mt-1">Student-specific features</p>
          <div class="mt-2 text-xs text-purple-600">
            <div>✓ Student classroom</div>
            <div>✓ School info</div>
            <div>✓ Schedule</div>
          </div>
        </div>
        
        <div class="card bg-orange-50 border-orange-200">
          <h3 class="font-medium text-orange-800">useUserContext()</h3>
          <p class="text-sm text-orange-700 mt-1">Full control and customization</p>
          <div class="mt-2 text-xs text-orange-600">
            <div>✓ All segments</div>
            <div>✓ Custom config</div>
            <div>✓ Advanced features</div>
          </div>
        </div>
      </div>
    </section>

    <!-- Step 3: Migration Examples -->
    <section class="bg-white rounded-lg shadow p-6">
      <h2 class="text-xl font-semibold mb-4">Step 3: Migration Examples</h2>
      
      <div class="space-y-6">
        <!-- Example 1: Simple Component -->
        <div class="border rounded-lg p-4">
          <h3 class="font-medium mb-3">Example 1: Simple User Display</h3>
          
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <div>
              <h4 class="text-sm font-medium text-red-600 mb-2">Before:</h4>
              <pre class="bg-gray-100 p-3 rounded text-xs overflow-auto">{{ simpleBeforeCode }}</pre>
            </div>
            <div>
              <h4 class="text-sm font-medium text-green-600 mb-2">After:</h4>
              <pre class="bg-gray-100 p-3 rounded text-xs overflow-auto">{{ simpleAfterCode }}</pre>
            </div>
          </div>
        </div>
        
        <!-- Example 2: Role-based Component -->
        <div class="border rounded-lg p-4">
          <h3 class="font-medium mb-3">Example 2: Role-based Features</h3>
          
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <div>
              <h4 class="text-sm font-medium text-red-600 mb-2">Before:</h4>
              <pre class="bg-gray-100 p-3 rounded text-xs overflow-auto">{{ roleBeforeCode }}</pre>
            </div>
            <div>
              <h4 class="text-sm font-medium text-green-600 mb-2">After:</h4>
              <pre class="bg-gray-100 p-3 rounded text-xs overflow-auto">{{ roleAfterCode }}</pre>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Step 4: Testing Checklist -->
    <section class="bg-white rounded-lg shadow p-6">
      <h2 class="text-xl font-semibold mb-4">Step 4: Testing Checklist</h2>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <h3 class="font-medium mb-3">Functional Testing</h3>
          <div class="space-y-2">
            <label class="flex items-center">
              <input type="checkbox" class="mr-2">
              <span class="text-sm">User data displays correctly</span>
            </label>
            <label class="flex items-center">
              <input type="checkbox" class="mr-2">
              <span class="text-sm">Role-based features work</span>
            </label>
            <label class="flex items-center">
              <input type="checkbox" class="mr-2">
              <span class="text-sm">Loading states appear</span>
            </label>
            <label class="flex items-center">
              <input type="checkbox" class="mr-2">
              <span class="text-sm">Error states handled</span>
            </label>
            <label class="flex items-center">
              <input type="checkbox" class="mr-2">
              <span class="text-sm">Navigation works correctly</span>
            </label>
          </div>
        </div>
        
        <div>
          <h3 class="font-medium mb-3">Performance Testing</h3>
          <div class="space-y-2">
            <label class="flex items-center">
              <input type="checkbox" class="mr-2">
              <span class="text-sm">Page loads faster</span>
            </label>
            <label class="flex items-center">
              <input type="checkbox" class="mr-2">
              <span class="text-sm">Fewer API calls made</span>
            </label>
            <label class="flex items-center">
              <input type="checkbox" class="mr-2">
              <span class="text-sm">Offline functionality works</span>
            </label>
            <label class="flex items-center">
              <input type="checkbox" class="mr-2">
              <span class="text-sm">Cache working properly</span>
            </label>
            <label class="flex items-center">
              <input type="checkbox" class="mr-2">
              <span class="text-sm">No console errors</span>
            </label>
          </div>
        </div>
      </div>
    </section>

    <!-- Step 5: Rollback Plan -->
    <section class="bg-white rounded-lg shadow p-6">
      <h2 class="text-xl font-semibold mb-4">Step 5: Rollback Plan</h2>
      
      <div class="bg-yellow-50 border border-yellow-200 rounded p-4">
        <h3 class="font-medium text-yellow-800 mb-2">If Something Goes Wrong:</h3>
        <ol class="text-yellow-700 text-sm space-y-1 list-decimal list-inside">
          <li>Revert to using <code>usePage().props.auth.user</code> temporarily</li>
          <li>Check browser console for errors</li>
          <li>Verify network connectivity for API calls</li>
          <li>Clear browser cache and localStorage</li>
          <li>Use backward compatibility mode with both systems</li>
        </ol>
        
        <div class="mt-3 p-2 bg-yellow-100 rounded">
          <div class="text-xs font-medium text-yellow-800">Emergency Rollback Code:</div>
          <pre class="text-xs mt-1">{{ rollbackCode }}</pre>
        </div>
      </div>
    </section>

    <!-- Progress Tracker -->
    <section class="bg-white rounded-lg shadow p-6">
      <h2 class="text-xl font-semibold mb-4">Migration Progress Tracker</h2>
      
      <div class="space-y-4">
        <div class="flex justify-between items-center">
          <span class="font-medium">Overall Progress</span>
          <span class="text-sm text-gray-600">{{ migrationProgress }}% Complete</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-3">
          <div class="bg-blue-600 h-3 rounded-full transition-all duration-300" 
               :style="`width: ${migrationProgress}%`"></div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
          <div>
            <h3 class="font-medium mb-2">Completed Components</h3>
            <ul class="text-sm space-y-1">
              <li class="text-green-600">✅ UserContextStore (Backend)</li>
              <li class="text-green-600">✅ Dexie Schema</li>
              <li class="text-green-600">✅ Pinia Store</li>
              <li class="text-green-600">✅ Vue Composables</li>
              <li class="text-green-600">✅ API Endpoints</li>
            </ul>
          </div>
          
          <div>
            <h3 class="font-medium mb-2">Pending Components</h3>
            <ul class="text-sm space-y-1">
              <li class="text-orange-600">🔄 TeacherDashboard.vue</li>
              <li class="text-orange-600">🔄 StudentDashboard.vue</li>
              <li class="text-orange-600">🔄 AdminDashboard.vue</li>
              <li class="text-orange-600">🔄 SidebarMenu.vue</li>
              <li class="text-orange-600">🔄 Dashboard.vue</li>
            </ul>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

// Migration progress (can be connected to actual tracking)
const completedComponents = ref(5);
const totalComponents = ref(12);

const migrationProgress = computed(() => 
  Math.round((completedComponents.value / totalComponents.value) * 100)
);

// Code examples
const simpleBeforeCode = `<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const user = computed(() => usePage().props.auth.user);
</script>

<template>
  <div>
    <h3>{{ user?.name }}</h3>
    <p>{{ user?.email }}</p>
  </div>
</template>`;

const simpleAfterCode = `<script setup>
import { useUser } from '@/composables/useUserContext.js';

const { user, isLoading } = useUser();
</script>

<template>
  <div>
    <div v-if="isLoading">Loading...</div>
    <div v-else-if="user">
      <h3>{{ user.name }}</h3>
      <p>{{ user.email }}</p>
    </div>
  </div>
</template>`;

const roleBeforeCode = `<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const user = computed(() => usePage().props.auth.user);
const isTeacher = computed(() => 
  user.value?.user_role === 'teacher' ||
  user.value?.roles?.includes('teacher')
);
</script>

<template>
  <div v-if="isTeacher">
    Teacher content
  </div>
</template>`;

const roleAfterCode = `<script setup>
import { useUser } from '@/composables/useUserContext.js';

const { isTeacher } = useUser();
</script>

<template>
  <div v-if="isTeacher">
    Teacher content
  </div>
</template>`;

const rollbackCode = `// Emergency rollback - use both systems
const legacyUser = computed(() => usePage().props.auth.user);
const { user: newUser } = useUser();
const user = computed(() => newUser.value || legacyUser.value);`;
</script>

<style scoped>
.practical-migration-guide {
  @apply max-w-6xl mx-auto;
}

.card {
  @apply p-4 border rounded-lg;
}

code {
  @apply bg-gray-200 px-1 rounded text-xs;
}
</style>
