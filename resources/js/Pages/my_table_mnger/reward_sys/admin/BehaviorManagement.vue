<template>
  <Head title="Behavior Management" />
  <div class="p-6 space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">🎯 Behavior Management</h1>
        <p class="text-gray-600 mt-1">Manage school-wide default behaviors</p>
      </div>
      <div class="flex gap-3">
        <q-btn
          color="secondary"
          icon="auto_awesome"
          label="AI Bulk Generator"
          @click="showAIGenerator = true"
          size="lg"
          outline
          class="shadow-lg"
        />
        <q-btn
          color="primary"
          icon="add"
          label="Add Behavior"
          @click="showCreateDialog = true"
          size="lg"
          class="shadow-lg"
        />
      </div>
    </div>

    <!-- Filters -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
      <!-- Language Toggle -->
      <q-btn-toggle
        v-model="displayLang"
        :options="[
          { label: '🇬🇧 English', value: 'en' },
          { label: '🇸🇦 Arabic', value: 'ar' }
        ]"
        color="primary"
        toggle-color="primary"
        unelevated
      />
      
      <!-- Behavior Type Filter Tabs -->
      <q-btn-toggle
        v-model="behaviorTypeFilter"
        :options="[
          { label: 'All', value: 'all', icon: 'apps' },
          { label: '✅ Positive', value: 'positive', icon: 'thumb_up' },
          { label: '❌ Negative', value: 'negative', icon: 'thumb_down' }
        ]"
        color="secondary"
        toggle-color="secondary"
        unelevated
      />
    </div>

    <!-- Behaviors Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <q-card
        v-for="behavior in filteredBehaviors"
        :key="behavior.id"
        class="shadow-lg hover:shadow-xl transition-all duration-300 cursor-pointer"
        :class="{
          'border-l-4 border-green-500': behavior.type === 'positive',
          'border-l-4 border-red-500': behavior.type === 'negative'
        }"
      >
        <q-card-section>
          <!-- Header with badges -->
          <div class="flex justify-between items-start mb-3">
            <div class="flex gap-2">
              <!-- Type Badge -->
              <q-badge
                :color="behavior.type === 'positive' ? 'green' : 'red'"
                :label="behavior.type === 'positive' ? '✅ Positive' : '❌ Negative'"
              />
              <!-- School Default Badge -->
              <q-badge
                v-if="!behavior.teacher_id"
                color="blue"
                label="🔒 School Default"
              />
              <!-- Teacher Custom Badge -->
              <q-badge
                v-else
                color="purple"
                label="✏️ Teacher Custom"
              />
            </div>
          </div>

          <!-- Behavior Name -->
          <h3 class="text-xl font-bold text-gray-900 mb-2">
            {{ displayLang === 'ar' && behavior.name_ar ? behavior.name_ar : behavior.name }}
          </h3>

          <!-- Points Display -->
          <div class="flex items-center gap-2 mb-3">
            <q-chip
              :color="behavior.points > 0 ? 'green' : 'red'"
              text-color="white"
              size="lg"
              class="font-bold"
            >
              {{ behavior.points > 0 ? '+' : '' }}{{ behavior.points }} points
            </q-chip>
          </div>

          <!-- Category/Description -->
          <div v-if="behavior.description" class="text-sm text-gray-600 mb-3">
            📂 {{ behavior.description }}
          </div>

          <!-- Actions -->
          <div class="flex gap-2 mt-4">
            <q-btn
              icon="edit"
              label="Edit"
              color="primary"
              outline
              size="sm"
              @click="editBehavior(behavior)"
            />
            <q-btn
              icon="delete"
              label="Delete"
              color="negative"
              outline
              size="sm"
              @click="confirmDelete(behavior)"
            />
          </div>
        </q-card-section>
      </q-card>
    </div>

    <!-- Empty State -->
    <div v-if="!filteredBehaviors.length" class="text-center py-12">
      <q-icon name="emoji_events" size="4rem" color="grey-5" />
      <p class="text-xl text-gray-600 mt-4">No behaviors yet</p>
      <p class="text-gray-500">Click "Add Behavior" to create your first one</p>
    </div>

    <!-- Create/Edit Dialog -->
    <q-dialog v-model="showCreateDialog" persistent>
      <q-card style="min-width: 600px">
        <q-card-section class="bg-primary text-white">
          <div class="text-h6">{{ editingBehavior ? '✏️ Edit Behavior' : '➕ Add New Behavior' }}</div>
        </q-card-section>

        <q-card-section class="q-pt-md">
          <div class="space-y-4">
            <!-- English Name -->
            <q-input
              v-model="form.name"
              label="🇬🇧 Behavior Name (English)"
              outlined
              :rules="[val => !!val || 'English name is required']"
            >
              <template v-slot:prepend>
                <q-icon name="translate" />
              </template>
            </q-input>

            <!-- Arabic Name -->
            <q-input
              v-model="form.name_ar"
              label="🇸🇦 Behavior Name (Arabic)"
              outlined
              dir="rtl"
            >
              <template v-slot:prepend>
                <q-icon name="translate" />
              </template>
            </q-input>

            <!-- Type Selection -->
            <q-select
              v-model="form.type"
              :options="[
                { label: '✅ Positive', value: 'positive' },
                { label: '❌ Negative', value: 'negative' }
              ]"
              label="Behavior Type"
              outlined
              emit-value
              map-options
              :rules="[val => !!val || 'Type is required']"
            >
              <template v-slot:prepend>
                <q-icon name="category" />
              </template>
            </q-select>

            <!-- Points -->
            <q-input
              v-model.number="form.points"
              type="number"
              label="Points"
              outlined
              :rules="[val => val !== null && val !== '' || 'Points are required']"
              hint="Use positive numbers for rewards, negative for deductions"
            >
              <template v-slot:prepend>
                <q-icon name="stars" />
              </template>
            </q-input>

            <!-- Description/Category -->
            <q-input
              v-model="form.description"
              label="Category/Description (Optional)"
              outlined
            >
              <template v-slot:prepend>
                <q-icon name="folder" />
              </template>
            </q-input>
          </div>
        </q-card-section>

        <q-card-actions align="right" class="q-px-md q-pb-md">
          <q-btn flat label="Cancel" color="grey" @click="closeDialog" />
          <q-btn
            :label="editingBehavior ? 'Update' : 'Create'"
            color="primary"
            @click="saveBehavior"
            :loading="saving"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Delete Confirmation Dialog -->
    <q-dialog v-model="showDeleteDialog" persistent>
      <q-card>
        <q-card-section class="row items-center">
          <q-avatar icon="delete" color="negative" text-color="white" />
          <span class="q-ml-sm">Are you sure you want to delete this behavior?</span>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Cancel" color="grey" v-close-popup />
          <q-btn flat label="Delete" color="negative" @click="deleteBehavior" :loading="deleting" />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- AI Bulk Generator Dialog -->
    <BehaviorAIGeneratorDialog
      v-model="showAIGenerator"
      :school-id="1"
      @success="handleAIGeneratorSuccess"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useQuasar } from 'quasar'
import axios from 'axios'
import BehaviorAIGeneratorDialog from './BehaviorAIGeneratorDialog.vue'

const $q = useQuasar()

// State
const behaviors = ref([])
const displayLang = ref('en')
const behaviorTypeFilter = ref('all') // 'all', 'positive', 'negative'
const showCreateDialog = ref(false)
const showDeleteDialog = ref(false)
const showAIGenerator = ref(false)
const editingBehavior = ref(null)
const behaviorToDelete = ref(null)
const saving = ref(false)
const deleting = ref(false)

// Form
const form = ref({
  name: '',
  name_ar: '',
  type: 'positive',
  points: 1,
  description: '',
  school_id: 1 // TODO: Get from context
})

// Computed: Filtered behaviors based on type filter
const filteredBehaviors = computed(() => {
  if (behaviorTypeFilter.value === 'all') {
    return behaviors.value
  }
  return behaviors.value.filter(b => b.type === behaviorTypeFilter.value)
})

// Methods
const loadBehaviors = async () => {
  try {
    const response = await axios.get('/api/behaviors', {
      params: { school_id: 1 } // TODO: Get from context
    })
    behaviors.value = response.data
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'Failed to load behaviors',
      position: 'top'
    })
  }
}

const editBehavior = (behavior) => {
  editingBehavior.value = behavior
  form.value = {
    name: behavior.name,
    name_ar: behavior.name_ar || '',
    type: behavior.type,
    points: behavior.points,
    description: behavior.description || '',
    school_id: behavior.school_id
  }
  showCreateDialog.value = true
}

const saveBehavior = async () => {
  saving.value = true
  try {
    if (editingBehavior.value) {
      // Update
      await axios.put(`/api/behaviors/${editingBehavior.value.id}`, form.value)
      $q.notify({
        type: 'positive',
        message: 'Behavior updated successfully',
        position: 'top'
      })
    } else {
      // Create
      await axios.post('/api/behaviors', form.value)
      $q.notify({
        type: 'positive',
        message: 'Behavior created successfully',
        position: 'top'
      })
    }
    closeDialog()
    loadBehaviors()
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: error.response?.data?.message || 'Failed to save behavior',
      position: 'top'
    })
  } finally {
    saving.value = false
  }
}

const confirmDelete = (behavior) => {
  behaviorToDelete.value = behavior
  showDeleteDialog.value = true
}

const deleteBehavior = async () => {
  deleting.value = true
  try {
    await axios.delete(`/api/behaviors/${behaviorToDelete.value.id}`)
    $q.notify({
      type: 'positive',
      message: 'Behavior deleted successfully',
      position: 'top'
    })
    showDeleteDialog.value = false
    loadBehaviors()
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: error.response?.data?.message || 'Failed to delete behavior',
      position: 'top'
    })
  } finally {
    deleting.value = false
  }
}

const handleAIGeneratorSuccess = () => {
  loadBehaviors()
  $q.notify({
    type: 'positive',
    message: 'Behaviors created successfully via AI Generator!',
    icon: 'auto_awesome',
    position: 'top'
  })
}

const closeDialog = () => {
  showCreateDialog.value = false
  editingBehavior.value = null
  form.value = {
    name: '',
    name_ar: '',
    type: 'positive',
    points: 1,
    description: '',
    school_id: 1
  }
}

// Lifecycle
onMounted(() => {
  loadBehaviors()
})
</script>

<style scoped>
.q-card {
  border-radius: 12px;
}
</style>
