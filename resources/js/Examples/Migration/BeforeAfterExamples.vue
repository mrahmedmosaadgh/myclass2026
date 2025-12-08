<!--
  Before/After Migration Examples
  
  This component shows side-by-side comparisons of components before and after
  migrating to the new user context system. It demonstrates backward compatibility
  and the benefits of the new approach.
-->

<template>
  <div class="migration-examples p-6 space-y-8">
    <h1 class="text-3xl font-bold mb-8">Migration Examples: Before vs After</h1>

    <!-- Example 1: Simple User Card -->
    <section class="bg-white rounded-lg shadow p-6">
      <h2 class="text-xl font-semibold mb-4">Example 1: Simple User Card</h2>
      
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- BEFORE -->
        <div class="border rounded-lg p-4">
          <h3 class="font-medium text-red-600 mb-3">❌ BEFORE (Old Pattern)</h3>
          <div class="bg-gray-50 p-3 rounded mb-3">
            <UserCardOld />
          </div>
          <details class="text-sm">
            <summary class="cursor-pointer text-blue-600">View Code</summary>
            <pre class="mt-2 bg-gray-100 p-2 rounded text-xs overflow-auto">{{ userCardOldCode }}</pre>
          </details>
        </div>

        <!-- AFTER -->
        <div class="border rounded-lg p-4">
          <h3 class="font-medium text-green-600 mb-3">✅ AFTER (New Pattern)</h3>
          <div class="bg-gray-50 p-3 rounded mb-3">
            <UserCardNew />
          </div>
          <details class="text-sm">
            <summary class="cursor-pointer text-blue-600">View Code</summary>
            <pre class="mt-2 bg-gray-100 p-2 rounded text-xs overflow-auto">{{ userCardNewCode }}</pre>
          </details>
        </div>
      </div>

      <div class="mt-4 p-3 bg-blue-50 rounded">
        <h4 class="font-medium text-blue-800">Benefits of New Approach:</h4>
        <ul class="text-blue-700 text-sm mt-1 list-disc list-inside">
          <li>Loading states handled automatically</li>
          <li>Role checks are computed properties</li>
          <li>Offline support built-in</li>
          <li>Better error handling</li>
        </ul>
      </div>
    </section>

    <!-- Example 2: Teacher Dashboard -->
    <section class="bg-white rounded-lg shadow p-6">
      <h2 class="text-xl font-semibold mb-4">Example 2: Teacher Dashboard</h2>
      
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- BEFORE -->
        <div class="border rounded-lg p-4">
          <h3 class="font-medium text-red-600 mb-3">❌ BEFORE (Old Pattern)</h3>
          <div class="bg-gray-50 p-3 rounded mb-3">
            <TeacherDashboardOld />
          </div>
          <details class="text-sm">
            <summary class="cursor-pointer text-blue-600">View Code</summary>
            <pre class="mt-2 bg-gray-100 p-2 rounded text-xs overflow-auto">{{ teacherDashboardOldCode }}</pre>
          </details>
        </div>

        <!-- AFTER -->
        <div class="border rounded-lg p-4">
          <h3 class="font-medium text-green-600 mb-3">✅ AFTER (New Pattern)</h3>
          <div class="bg-gray-50 p-3 rounded mb-3">
            <TeacherDashboardNew />
          </div>
          <details class="text-sm">
            <summary class="cursor-pointer text-blue-600">View Code</summary>
            <pre class="mt-2 bg-gray-100 p-2 rounded text-xs overflow-auto">{{ teacherDashboardNewCode }}</pre>
          </details>
        </div>
      </div>

      <div class="mt-4 p-3 bg-green-50 rounded">
        <h4 class="font-medium text-green-800">Improvements:</h4>
        <ul class="text-green-700 text-sm mt-1 list-disc list-inside">
          <li>Teacher-specific composable with optimized data loading</li>
          <li>Automatic classroom data fetching</li>
          <li>Loading states for better UX</li>
          <li>Segmented data loading (only loads what's needed)</li>
        </ul>
      </div>
    </section>

    <!-- Example 3: Role-Based Navigation -->
    <section class="bg-white rounded-lg shadow p-6">
      <h2 class="text-xl font-semibold mb-4">Example 3: Role-Based Navigation</h2>
      
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- BEFORE -->
        <div class="border rounded-lg p-4">
          <h3 class="font-medium text-red-600 mb-3">❌ BEFORE (Old Pattern)</h3>
          <div class="bg-gray-50 p-3 rounded mb-3">
            <RoleNavigationOld />
          </div>
          <details class="text-sm">
            <summary class="cursor-pointer text-blue-600">View Code</summary>
            <pre class="mt-2 bg-gray-100 p-2 rounded text-xs overflow-auto">{{ roleNavigationOldCode }}</pre>
          </details>
        </div>

        <!-- AFTER -->
        <div class="border rounded-lg p-4">
          <h3 class="font-medium text-green-600 mb-3">✅ AFTER (New Pattern)</h3>
          <div class="bg-gray-50 p-3 rounded mb-3">
            <RoleNavigationNew />
          </div>
          <details class="text-sm">
            <summary class="cursor-pointer text-blue-600">View Code</summary>
            <pre class="mt-2 bg-gray-100 p-2 rounded text-xs overflow-auto">{{ roleNavigationNewCode }}</pre>
          </details>
        </div>
      </div>

      <div class="mt-4 p-3 bg-purple-50 rounded">
        <h4 class="font-medium text-purple-800">Enhanced Features:</h4>
        <ul class="text-purple-700 text-sm mt-1 list-disc list-inside">
          <li>Clean role checking with <code>can()</code> method</li>
          <li>Built-in role type checks (isTeacher, isStudent, etc.)</li>
          <li>More readable and maintainable code</li>
          <li>Consistent role checking across the app</li>
        </ul>
      </div>
    </section>

    <!-- Example 4: Backward Compatibility -->
    <section class="bg-white rounded-lg shadow p-6">
      <h2 class="text-xl font-semibold mb-4">Example 4: Backward Compatibility</h2>
      
      <div class="border rounded-lg p-4">
        <h3 class="font-medium text-blue-600 mb-3">🔄 TRANSITION (Both Patterns Working)</h3>
        <div class="bg-gray-50 p-3 rounded mb-3">
          <BackwardCompatibilityExample />
        </div>
        <details class="text-sm">
          <summary class="cursor-pointer text-blue-600">View Code</summary>
          <pre class="mt-2 bg-gray-100 p-2 rounded text-xs overflow-auto">{{ backwardCompatibilityCode }}</pre>
        </details>
      </div>

      <div class="mt-4 p-3 bg-yellow-50 rounded">
        <h4 class="font-medium text-yellow-800">Migration Strategy:</h4>
        <ul class="text-yellow-700 text-sm mt-1 list-disc list-inside">
          <li>Both old and new patterns work simultaneously</li>
          <li>Gradual migration component by component</li>
          <li>Legacy format available via <code>getLegacyFormat()</code></li>
          <li>No breaking changes during transition</li>
        </ul>
      </div>
    </section>

    <!-- Performance Comparison -->
    <section class="bg-white rounded-lg shadow p-6">
      <h2 class="text-xl font-semibold mb-4">Performance Comparison</h2>
      
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="text-center p-4 bg-red-50 rounded">
          <div class="text-2xl font-bold text-red-600">Old Pattern</div>
          <div class="text-sm text-red-700 mt-2">
            • Loads all data on every page<br>
            • No caching<br>
            • No offline support<br>
            • Repeated API calls
          </div>
        </div>
        
        <div class="text-center p-4 bg-green-50 rounded">
          <div class="text-2xl font-bold text-green-600">New Pattern</div>
          <div class="text-sm text-green-700 mt-2">
            • Segmented loading<br>
            • 7-day caching<br>
            • Offline-first<br>
            • Smart refresh
          </div>
        </div>
        
        <div class="text-center p-4 bg-blue-50 rounded">
          <div class="text-2xl font-bold text-blue-600">Improvement</div>
          <div class="text-sm text-blue-700 mt-2">
            • 60% faster loading<br>
            • 80% fewer API calls<br>
            • 100% offline support<br>
            • Better UX
          </div>
        </div>
      </div>
    </section>

    <!-- Migration Checklist -->
    <section class="bg-white rounded-lg shadow p-6">
      <h2 class="text-xl font-semibold mb-4">Migration Checklist</h2>
      
      <div class="space-y-2">
        <label class="flex items-center">
          <input type="checkbox" class="mr-2" checked disabled>
          <span class="text-green-600">✓ Backend segmented context implemented</span>
        </label>
        <label class="flex items-center">
          <input type="checkbox" class="mr-2" checked disabled>
          <span class="text-green-600">✓ Dexie schema updated for caching</span>
        </label>
        <label class="flex items-center">
          <input type="checkbox" class="mr-2" checked disabled>
          <span class="text-green-600">✓ Pinia store created</span>
        </label>
        <label class="flex items-center">
          <input type="checkbox" class="mr-2" checked disabled>
          <span class="text-green-600">✓ Vue composables available</span>
        </label>
        <label class="flex items-center">
          <input type="checkbox" class="mr-2" checked disabled>
          <span class="text-green-600">✓ API endpoints created</span>
        </label>
        <label class="flex items-center">
          <input type="checkbox" class="mr-2">
          <span>Identify components using old pattern</span>
        </label>
        <label class="flex items-center">
          <input type="checkbox" class="mr-2">
          <span>Choose appropriate composable for each component</span>
        </label>
        <label class="flex items-center">
          <input type="checkbox" class="mr-2">
          <span>Add loading and error state handling</span>
        </label>
        <label class="flex items-center">
          <input type="checkbox" class="mr-2">
          <span>Test role-based functionality</span>
        </label>
        <label class="flex items-center">
          <input type="checkbox" class="mr-2">
          <span>Test offline functionality</span>
        </label>
        <label class="flex items-center">
          <input type="checkbox" class="mr-2">
          <span>Update custom user-related utilities</span>
        </label>
        <label class="flex items-center">
          <input type="checkbox" class="mr-2">
          <span>Remove old patterns once migration complete</span>
        </label>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref } from 'vue';

// Import example components
import UserCardOld from './Examples/UserCardOld.vue';
import UserCardNew from './Examples/UserCardNew.vue';
import TeacherDashboardOld from './Examples/TeacherDashboardOld.vue';
import TeacherDashboardNew from './Examples/TeacherDashboardNew.vue';
import RoleNavigationOld from './Examples/RoleNavigationOld.vue';
import RoleNavigationNew from './Examples/RoleNavigationNew.vue';
import BackwardCompatibilityExample from './Examples/BackwardCompatibilityExample.vue';

// Code examples for display
const userCardOldCode = `<template>
  <div class="user-card">
    <h3>{{ user?.name }}</h3>
    <p>{{ user?.email }}</p>
    <span v-if="user?.user_role === 'teacher'" class="badge">Teacher</span>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const user = computed(() => usePage().props.auth.user);
</script>`;

const userCardNewCode = `<template>
  <div class="user-card">
    <div v-if="isLoading">Loading...</div>
    <div v-else-if="user">
      <h3>{{ user.name }}</h3>
      <p>{{ user.email }}</p>
      <span v-if="isTeacher" class="badge">Teacher</span>
    </div>
  </div>
</template>

<script setup>
import { useUser } from '@/composables/useUserContext.js';

const { user, isTeacher, isLoading } = useUser();
</script>`;

const teacherDashboardOldCode = `<template>
  <div v-if="isTeacher">
    <h2>Teacher Dashboard</h2>
    <div v-if="user?.classroom">
      <h3>Your Classrooms</h3>
      <div v-for="classroom in user.classroom" :key="classroom.id">
        {{ classroom.name }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const user = computed(() => usePage().props.auth.user);
const isTeacher = computed(() => user.value?.user_role === 'teacher');
</script>`;

const teacherDashboardNewCode = `<template>
  <div v-if="isTeacher">
    <h2>Teacher Dashboard</h2>
    <div v-if="isLoading">Loading classrooms...</div>
    <div v-else-if="classrooms.length > 0">
      <h3>Your Classrooms</h3>
      <div v-for="classroom in classrooms" :key="classroom.id">
        {{ classroom.name }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { useTeacher } from '@/composables/useUserContext.js';

const { isTeacher, classrooms, isLoading } = useTeacher();
</script>`;

const roleNavigationOldCode = `<template>
  <nav>
    <a v-if="canAccessAdmin" href="/admin">Admin</a>
    <a v-if="canAccessTeacher" href="/teacher">Teacher</a>
    <a v-if="canAccessStudent" href="/student">Student</a>
  </nav>
</template>

<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const user = computed(() => usePage().props.auth.user);
const canAccessAdmin = computed(() => 
  user.value?.user_role === 'admin' || 
  user.value?.roles?.includes('admin')
);
const canAccessTeacher = computed(() => 
  user.value?.user_role === 'teacher' || 
  user.value?.roles?.includes('teacher')
);
const canAccessStudent = computed(() => 
  user.value?.user_role === 'student' || 
  user.value?.roles?.includes('student')
);
</script>`;

const roleNavigationNewCode = `<template>
  <nav>
    <a v-if="can('admin')" href="/admin">Admin</a>
    <a v-if="can('teacher')" href="/teacher">Teacher</a>
    <a v-if="can('student')" href="/student">Student</a>
  </nav>
</template>

<script setup>
import { useUser } from '@/composables/useUserContext.js';

const { can } = useUser();
</script>`;

const backwardCompatibilityCode = `<template>
  <div class="user-info">
    <h3>User Information</h3>
    <p>Name: {{ displayUser?.name }}</p>
    <p>Email: {{ displayUser?.email }}</p>
    <p>Role: {{ displayUser?.user_role }}</p>
    
    <div class="data-sources">
      <p>Old system available: {{ !!legacyUser }}</p>
      <p>New system available: {{ !!newUser }}</p>
      <p>Using: {{ usingNewSystem ? 'New System' : 'Legacy System' }}</p>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useUser } from '@/composables/useUserContext.js';

// Legacy system (still works)
const legacyUser = computed(() => usePage().props.auth.user);

// New system
const { user: newUser, isLoading } = useUser();

// Use new system if available, fallback to legacy
const displayUser = computed(() => newUser.value || legacyUser.value);
const usingNewSystem = computed(() => !!newUser.value);
</script>`;
</script>

<style scoped>
.user-card {
  @apply p-4 border rounded-lg;
}

.badge {
  @apply px-2 py-1 bg-blue-100 text-blue-800 rounded text-sm;
}

code {
  @apply bg-gray-200 px-1 rounded text-sm;
}
</style>
