<template>
  <AdminLayout>
    <div class="q-pa-md">
      <div class="row items-center q-mb-lg">
        <q-icon name="code" size="md" class="q-mr-sm text-primary" />
        <div class="text-h5 text-weight-bold">Developer Menu Manager</div>
      </div>

      <q-tabs
        v-model="activeTab"
        dense
        class="text-grey"
        active-color="primary"
        indicator-color="primary"
        align="left"
        narrow-indicator
      >
        <q-tab name="teacher" label="Teacher" />
        <q-tab name="student" label="Student" />
        <q-tab name="admin" label="Admin" />
        <q-tab name="parent" label="Parent" />
      </q-tabs>

      <q-separator />

      <q-tab-panels v-model="activeTab" animated>
        <q-tab-panel v-for="role in ['teacher', 'student', 'admin', 'parent']" :key="role" :name="role">
          <div class="row q-col-gutter-md">
            <!-- Left Column: Config File Viewer -->
            <div class="col-12 col-md-6">
              <q-card flat bordered class="h-full">
                <q-card-section class="row items-center justify-between">
                  <div class="text-subtitle1 text-weight-bold">
                    config/menus/{{ role }}.php
                  </div>
                  <q-btn flat round icon="content_copy" size="sm" @click="copyConfig(configs[role])">
                    <q-tooltip>Copy File Content</q-tooltip>
                  </q-btn>
                </q-card-section>
                <q-separator />
                <q-card-section class="q-pa-none">
                  <pre class="bg-grey-2 q-pa-md overflow-auto" style="max-height: 600px; font-size: 12px; border-radius: 0 0 4px 4px;"><code>{{ configs[role] }}</code></pre>
                </q-card-section>
              </q-card>
            </div>

            <!-- Right Column: Route Helper -->
            <div class="col-12 col-md-6">
              <q-card flat bordered class="h-full">
                <q-card-section>
                  <div class="text-subtitle1 text-weight-bold">Available {{ capitalize(role) }} Routes</div>
                  <div class="text-caption text-grey">Click copy to get a PHP snippet for the config file.</div>
                  <q-input v-model="search" dense outlined placeholder="Search routes..." class="q-mt-sm">
                    <template v-slot:prepend>
                      <q-icon name="search" />
                    </template>
                  </q-input>
                </q-card-section>
                <q-separator />
                <q-list separator style="max-height: 520px; overflow-y: auto">
                  <q-item v-for="route in filteredRoutes(role)" :key="route.name" class="q-py-sm">
                    <q-item-section>
                      <q-item-label class="text-weight-medium">{{ route.name }}</q-item-label>
                      <q-item-label caption class="text-xs">{{ route.uri }}</q-item-label>
                    </q-item-section>
                    <q-item-section side>
                      <q-btn flat round color="primary" icon="content_copy" size="sm" @click="copySnippet(route)">
                        <q-tooltip>Copy PHP Snippet</q-tooltip>
                      </q-btn>
                    </q-item-section>
                  </q-item>
                  <q-item v-if="filteredRoutes(role).length === 0">
                    <q-item-section class="text-center text-grey">
                      No routes found matching "{{ role }}" prefix or search.
                    </q-item-section>
                  </q-item>
                </q-list>
              </q-card>
            </div>
          </div>
        </q-tab-panel>
      </q-tab-panels>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useQuasar } from 'quasar';
import { copyToClipboard } from 'quasar';

const props = defineProps({
  configs: Object,
  allRoutes: Array,
});

const $q = useQuasar();
const activeTab = ref('teacher');
const search = ref('');

const capitalize = (s) => s.charAt(0).toUpperCase() + s.slice(1);

const filteredRoutes = (role) => {
  const term = search.value.toLowerCase();
  // Filter by role prefix (e.g., 'teacher.') AND search term
  // Also include generic 'schedules.' routes if role is student or teacher
  return props.allRoutes.filter(r => {
    const name = r.name.toLowerCase();
    const matchesRole = name.startsWith(role + '.') || 
                        (role === 'teacher' && name.includes('teacher')) ||
                        (role === 'student' && name.includes('student'));
    const matchesSearch = name.includes(term) || r.uri.toLowerCase().includes(term);
    return matchesRole && matchesSearch;
  });
};

const copyConfig = (content) => {
  copyToClipboard(content)
    .then(() => {
      $q.notify({ type: 'positive', message: 'Config content copied!' });
    })
    .catch(() => {
      $q.notify({ type: 'negative', message: 'Failed to copy.' });
    });
};

const copySnippet = (route) => {
  const id = route.name.split('.').pop().replace(/[^a-zA-Z0-9]/g, '_');
  const label = id.split('_').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
  
  const snippet = `    [
        'id' => '${id}',
        'label' => ['en' => '${label}', 'ar' => '${label}'],
        'route' => '${route.name}',
        'icon' => 'circle',
    ],`;

  copyToClipboard(snippet)
    .then(() => {
      $q.notify({ type: 'positive', message: 'PHP Snippet copied!' });
    })
    .catch(() => {
      $q.notify({ type: 'negative', message: 'Failed to copy.' });
    });
};
</script>
