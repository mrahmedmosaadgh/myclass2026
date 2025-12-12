<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { Head } from '@inertiajs/vue3';

// Get routes from props passed by the controller
const props = defineProps({
  routeData: Array
});

const routes = ref(props.routeData || []);
const searchQuery = ref('');
const selectedMethods = ref([]);
const savedTags = ref([]);
const newTag = ref('');

// Load saved tags from localStorage
onMounted(() => {
  const storedTags = localStorage.getItem('route-tags');
  if (storedTags) {
    savedTags.value = JSON.parse(storedTags);
  }
});

// Save tags to localStorage when they change
watch(savedTags, (newValue) => {
  localStorage.setItem('route-tags', JSON.stringify(newValue));
}, { deep: true });

// Get unique methods from all routes
const availableMethods = computed(() => {
  const methods = new Set();
  routes.value.forEach(route => {
    route.methods.forEach(method => methods.add(method));
  });
  return Array.from(methods).sort();
});

// Add a new tag
const addTag = () => {
  if (newTag.value && !savedTags.value.some(tag => tag.query === newTag.value)) {
    savedTags.value.push({
      query: newTag.value,
      label: newTag.value
    });
    newTag.value = '';
  }
};

// Apply a saved tag
const applyTag = (tag) => {
  searchQuery.value = tag.query;
};

// Remove a saved tag
const removeTag = (index) => {
  savedTags.value.splice(index, 1);
};

const filteredRoutes = computed(() => {
  let result = routes.value;
  
  // Filter by selected methods
  if (selectedMethods.value.length > 0) {
    result = result.filter(route => 
      route.methods.some(method => selectedMethods.value.includes(method))
    );
  }
  
  // Filter by search query
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    result = result.filter(route => 
      route.uri.toLowerCase().includes(query) || 
      route.name?.toLowerCase().includes(query) ||
      route.methods.some(method => method.toLowerCase().includes(query))
    );
  }
  
  return result;
});
</script>

<template>
  <div>
    <Head title="System Routes" />
    
    <div class="p-4">
      <h1 class="text-2xl font-bold mb-4">System Routes</h1>
      
      <!-- Search input -->
      <div class="mb-4">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Search routes..."
          class="w-full p-2 border rounded"
        />
      </div>
      
      <!-- Method filters -->
      <div class="mb-4">
        <h3 class="text-lg font-semibold mb-2">Filter by Method:</h3>
        <div class="flex flex-wrap gap-2">
          <label 
            v-for="method in availableMethods" 
            :key="method"
            class="inline-flex items-center cursor-pointer"
          >
            <input 
              type="checkbox" 
              :value="method" 
              v-model="selectedMethods"
              class="mr-1"
            />
            <span 
              :class="{
                'px-2 py-1 text-xs font-medium rounded': true,
                'bg-green-100 text-green-800': method === 'GET',
                'bg-blue-100 text-blue-800': method === 'POST',
                'bg-yellow-100 text-yellow-800': method === 'PUT' || method === 'PATCH',
                'bg-red-100 text-red-800': method === 'DELETE',
                'bg-gray-100 text-gray-800': !['GET', 'POST', 'PUT', 'PATCH', 'DELETE'].includes(method)
              }"
            >
              {{ method }}
            </span>
          </label>
        </div>
      </div>
      
      <!-- Saved tags -->
      <div class="mb-4">
        <h3 class="text-lg font-semibold mb-2">Saved Filters:</h3>
        <div class="flex flex-wrap gap-2 mb-2">
          <div 
            v-for="(tag, index) in savedTags" 
            :key="index"
            class="bg-blue-100 text-blue-800 px-2 py-1 rounded flex items-center"
          >
            <span 
              @click="applyTag(tag)" 
              class="cursor-pointer mr-1"
            >
              {{ tag.label }}
            </span>
            <button 
              @click="removeTag(index)" 
              class="text-blue-600 hover:text-blue-800"
            >
              &times;
            </button>
          </div>
        </div>
        <div class="flex">
          <input 
            v-model="newTag" 
            type="text" 
            placeholder="Save current search as tag..." 
            class="p-2 border rounded flex-grow"
            @keyup.enter="addTag"
          />
          <button 
            @click="addTag" 
            class="ml-2 bg-blue-500 text-white px-4 py-2 rounded"
          >
            Save
          </button>
        </div>
      </div>
      
      <div>
        <div class="bg-white rounded shadow overflow-hidden">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Methods</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">URI</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="(route, index) in filteredRoutes" :key="index" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex space-x-1">
                    <span 
                      v-for="method in route.methods" 
                      :key="method"
                      :class="{
                        'px-2 py-1 text-xs font-medium rounded': true,
                        'bg-green-100 text-green-800': method === 'GET',
                        'bg-blue-100 text-blue-800': method === 'POST',
                        'bg-yellow-100 text-yellow-800': method === 'PUT' || method === 'PATCH',
                        'bg-red-100 text-red-800': method === 'DELETE',
                        'bg-gray-100 text-gray-800': !['GET', 'POST', 'PUT', 'PATCH', 'DELETE'].includes(method)
                      }"
                    >
                      {{ method }}
                    </span>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <a 
                    v-if="route.methods.includes('GET') && !route.uri.startsWith('api/')" 
                    :href="'/' + route.uri" 
                    target="_blank"
                    class="text-blue-600 hover:underline"
                  >
                    {{ route.uri }}
                  </a>
                  <span v-else>{{ route.uri }}</span>
                </td>
                <td class="px-6 py-4">{{ route.name || '-' }}</td>
                <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">{{ route.action }}</td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <div class="mt-4 text-sm text-gray-500">
          Total routes: {{ filteredRoutes.length }} (of {{ routes.length }})
          <span v-if="selectedMethods.length > 0">
            | Filtered by methods: {{ selectedMethods.join(', ') }}
          </span>
        </div>
      </div>
    </div>
  </div>
</template>