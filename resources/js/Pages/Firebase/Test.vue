<template>
  <AppLayout title="Firebase Test">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        Firebase Connection Test
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
          <p class="mb-4 text-gray-600 dark:text-gray-400">
            This page allows you to test your Firebase connection and verify that read/write operations are working correctly.
          </p>
          
          <ConnectionTest />
          
          <div class="mt-8">
            <h3 class="text-lg font-medium mb-2">Firebase Configuration</h3>
            <div class="bg-gray-100 dark:bg-gray-700 p-4 rounded-lg">
              <p class="mb-2"><strong>Project ID:</strong> {{ firebaseConfig.projectId }}</p>
              <p class="mb-2"><strong>Database URL:</strong> {{ firebaseConfig.databaseURL }}</p>
              <p><strong>Auth Domain:</strong> {{ firebaseConfig.authDomain }}</p>
            </div>
          </div>
          
          <div class="mt-8">
            <h3 class="text-lg font-medium mb-2">Firebase Security Rules</h3>
            <p class="mb-4 text-gray-600 dark:text-gray-400">
              Make sure you've set up your Firebase security rules correctly. The test component requires read/write access to the <code>firebase_test</code> path.
            </p>
            <div class="bg-gray-100 dark:bg-gray-700 p-4 rounded-lg overflow-auto">
              <pre class="text-xs">
{
  "rules": {
    // Your existing rules...
    
    // Add this rule for testing
    "firebase_test": {
      ".read": true,
      ".write": true
    }
  }
}
              </pre>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import ConnectionTest from '@/Components/Firebase/ConnectionTest.vue';
import { database } from '@/firebase/init';

// Get Firebase config from the database reference
const firebaseConfig = computed(() => {
  try {
    return {
      projectId: database._repoInternal.app.options.projectId,
      databaseURL: database._repoInternal.databaseURL,
      authDomain: database._repoInternal.app.options.authDomain
    };
  } catch (error) {
    console.error('Error getting Firebase config:', error);
    return {
      projectId: 'Error retrieving config',
      databaseURL: 'Error retrieving config',
      authDomain: 'Error retrieving config'
    };
  }
});
</script>
