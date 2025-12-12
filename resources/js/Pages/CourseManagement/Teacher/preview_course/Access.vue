<template>
  <q-layout view="hHh lpR fFf">
    <q-page-container>
      <div class="q-pa-md bg-grey-2" style="min-height: 100vh">
        <h1 class="text-h3 q-mb-md">Course Management Interface</h1>
        
        <q-card class="q-mb-md">
          <q-card-section>
            <div class="text-h6">Access Information</div>
            <p class="q-mt-sm">
              The Course Management Interface has been successfully created. You can access it using the following URL:
            </p>
            
            <q-banner class="bg-primary text-white q-mt-md">
              <template #avatar>
                <q-icon name="link" />
              </template>
              <div class="text-subtitle1">
                <strong>Direct URL:</strong>
                <a :href="previewUrl" class="text-white underline" target="_blank">
                  {{ previewUrl }}
                </a>
              </div>
            </q-banner>
            
            <q-list bordered class="q-mt-md">
              <q-item clickable @click="openPreview">
                <q-item-section avatar>
                  <q-icon name="preview" color="primary" />
                </q-item-section>
                <q-item-section>
                  <q-item-label>Open Preview</q-item-label>
                  <q-item-label caption>View the Course Management Interface in action</q-item-label>
                </q-item-section>
                <q-item-section side>
                  <q-icon name="chevron_right" />
                </q-item-section>
              </q-item>
              
              <q-item clickable @click="openDocumentation">
                <q-item-section avatar>
                  <q-icon name="description" color="secondary" />
                </q-item-section>
                <q-item-section>
                  <q-item-label>View Documentation</q-item-label>
                  <q-item-label caption>Read the complete usage guide</q-item-label>
                </q-item-section>
                <q-item-section side>
                  <q-icon name="chevron_right" />
                </q-item-section>
              </q-item>
            </q-list>
          </q-card-section>
        </q-card>
        
        <q-card>
          <q-card-section>
            <div class="text-h6">Features Overview</div>
            <div class="row q-col-gutter-md q-mt-sm">
              <div class="col-12 col-md-6">
                <q-list dense>
                  <q-item>
                    <q-item-section avatar>
                      <q-icon name="view_sidebar" color="primary" />
                    </q-item-section>
                    <q-item-section>Two-panel layout with resizable splitter</q-item-section>
                  </q-item>
                  <q-item>
                    <q-item-section avatar>
                      <q-icon name="account_tree" color="secondary" />
                    </q-item-section>
                    <q-item-section>Tree navigation for courses, levels, and sections</q-item-section>
                  </q-item>
                  <q-item>
                    <q-item-section avatar>
                      <q-icon name="school" color="accent" />
                    </q-item-section>
                    <q-item-section>Interactive lesson management</q-item-section>
                  </q-item>
                </q-list>
              </div>
              <div class="col-12 col-md-6">
                <q-list dense>
                  <q-item>
                    <q-item-section avatar>
                      <q-icon name="devices" color="positive" />
                    </q-item-section>
                    <q-item-section>Responsive design for all devices</q-item-section>
                  </q-item>
                  <q-item>
                    <q-item-section avatar>
                      <q-icon name="palette" color="warning" />
                    </q-item-section>
                    <q-item-section>Quasar styling with Material Icons</q-item-section>
                  </q-item>
                  <q-item>
                    <q-item-section avatar>
                      <q-icon name="code" color="info" />
                    </q-item-section>
                    <q-item-section>Vue 3 Composition API with TypeScript</q-item-section>
                  </q-item>
                </q-list>
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>
    </q-page-container>
  </q-layout>
</template>

<script setup>
import { computed } from 'vue'

const previewUrl = computed(() => {
  return window.location.origin + '/course-management/teachers/preview-course'
})

const openPreview = () => {
  window.open(previewUrl.value, '_blank')
}

const openDocumentation = () => {
  window.open('/docs/CourseManagement/todo/2.md', '_blank')
}
</script>

<style scoped>
.underline {
  text-decoration: underline;
}
</style>