<template>
  <div class="users-list">
    <!-- Loading State -->
    <div v-if="loading" class="text-center q-py-xl">
      <q-spinner-dots size="3rem" color="primary" />
      <div class="text-subtitle1 q-mt-md">Loading users...</div>
    </div>

    <!-- Empty State -->
    <div v-else-if="!users || users.length === 0" class="text-center q-py-xl">
      <q-icon name="people_outline" size="4rem" color="grey-5" />
      <div class="text-h6 text-grey-7 q-mt-md">No {{ type }}s found</div>
      <div class="text-body2 text-grey-6 q-mt-sm">
        Try adjusting your search criteria or filters
      </div>
    </div>

    <!-- Users List -->
    <div v-else class="q-pa-md">
      <div class="row q-gutter-md">
        <div
          v-for="user in users"
          :key="user.id"
          class="col-12 col-sm-6 col-lg-4"
        >
          <q-card class="user-card">
            <q-card-section class="user-header">
              <div class="row items-center">
                <div class="col-auto q-mr-md">
                  <q-avatar
                    :color="getUserTypeColor(type)"
                    text-color="white"
                    :icon="getUserTypeIcon(type)"
                    size="48px"
                  />
                </div>
                <div class="col">
                  <div class="text-h6 text-weight-bold">{{ user.name }}</div>
                  <div class="text-caption text-grey-7">{{ user.email }}</div>
                  <div class="text-caption text-primary">{{ user.school_name || 'No School' }}</div>
                </div>
                <div class="col-auto">
                  <q-btn
                    round
                    flat
                    icon="more_vert"
                    size="sm"
                  >
                    <q-menu>
                      <q-list style="min-width: 150px">
                        <q-item clickable v-close-popup @click="$emit('manage-roles', user)">
                          <q-item-section avatar>
                            <q-icon name="admin_panel_settings" />
                          </q-item-section>
                          <q-item-section>Manage Roles</q-item-section>
                        </q-item>
                        <q-item clickable v-close-popup @click="viewUserDetails(user)">
                          <q-item-section avatar>
                            <q-icon name="visibility" />
                          </q-item-section>
                          <q-item-section>View Details</q-item-section>
                        </q-item>
                      </q-list>
                    </q-menu>
                  </q-btn>
                </div>
              </div>
            </q-card-section>

            <q-card-section class="user-content">
              <!-- Teacher specific info -->
              <div v-if="type === 'teacher'" class="teacher-info">
                <div v-if="user.subjects && user.subjects.length" class="q-mb-sm">
                  <div class="text-caption text-weight-bold text-grey-8">Subjects:</div>
                  <div class="q-gutter-xs">
                    <q-chip
                      v-for="subject in user.subjects.slice(0, 3)"
                      :key="subject"
                      size="sm"
                      color="green"
                      text-color="white"
                    >
                      {{ subject }}
                    </q-chip>
                    <q-chip
                      v-if="user.subjects.length > 3"
                      size="sm"
                      color="grey"
                      text-color="white"
                    >
                      +{{ user.subjects.length - 3 }} more
                    </q-chip>
                  </div>
                </div>
                <div v-if="user.classrooms && user.classrooms.length" class="q-mb-sm">
                  <div class="text-caption text-weight-bold text-grey-8">Classrooms:</div>
                  <div class="q-gutter-xs">
                    <q-chip
                      v-for="classroom in user.classrooms.slice(0, 2)"
                      :key="classroom"
                      size="sm"
                      color="blue"
                      text-color="white"
                    >
                      {{ classroom }}
                    </q-chip>
                    <q-chip
                      v-if="user.classrooms.length > 2"
                      size="sm"
                      color="grey"
                      text-color="white"
                    >
                      +{{ user.classrooms.length - 2 }} more
                    </q-chip>
                  </div>
                </div>
              </div>

              <!-- Student specific info -->
              <div v-if="type === 'student'" class="student-info">
                <div class="row q-gutter-sm">
                  <div v-if="user.classroom_name" class="col-12">
                    <q-chip color="blue" text-color="white" icon="class">
                      {{ user.classroom_name }}
                    </q-chip>
                  </div>
                  <div v-if="user.grade_name" class="col-auto">
                    <q-chip color="purple" text-color="white" icon="grade" size="sm">
                      {{ user.grade_name }}
                    </q-chip>
                  </div>
                  <div v-if="user.stage_name" class="col-auto">
                    <q-chip color="orange" text-color="white" icon="school" size="sm">
                      {{ user.stage_name }}
                    </q-chip>
                  </div>
                </div>
              </div>

              <!-- Parent specific info -->
              <div v-if="type === 'parent'" class="parent-info">
                <div v-if="user.children_count" class="q-mb-sm">
                  <q-chip color="orange" text-color="white" icon="child_care">
                    {{ user.children_count }} {{ user.children_count === 1 ? 'Child' : 'Children' }}
                  </q-chip>
                </div>
                <div v-if="user.children_classrooms && user.children_classrooms.length" class="q-mb-sm">
                  <div class="text-caption text-weight-bold text-grey-8">Children's Classrooms:</div>
                  <div class="q-gutter-xs">
                    <q-chip
                      v-for="classroom in user.children_classrooms.slice(0, 2)"
                      :key="classroom"
                      size="sm"
                      color="blue"
                      text-color="white"
                    >
                      {{ classroom }}
                    </q-chip>
                    <q-chip
                      v-if="user.children_classrooms.length > 2"
                      size="sm"
                      color="grey"
                      text-color="white"
                    >
                      +{{ user.children_classrooms.length - 2 }} more
                    </q-chip>
                  </div>
                </div>
              </div>

              <!-- Roles -->
              <div class="roles-section q-mt-sm">
                <div class="text-caption text-weight-bold text-grey-8 q-mb-xs">Current Roles:</div>
                <div v-if="user.roles && user.roles.length" class="q-gutter-xs">
                  <q-chip
                    v-for="role in user.roles"
                    :key="role.id"
                    size="sm"
                    color="indigo"
                    text-color="white"
                    icon="security"
                  >
                    {{ role.name }}
                  </q-chip>
                </div>
                <div v-else class="text-caption text-grey-6">
                  No roles assigned
                </div>
              </div>
            </q-card-section>

            <q-card-actions class="user-actions">
              <q-btn
                color="primary"
                icon="admin_panel_settings"
                label="Manage Roles"
                @click="$emit('manage-roles', user)"
                size="sm"
                outline
              />
              <q-space />
              <q-btn
                color="grey-7"
                icon="visibility"
                @click="viewUserDetails(user)"
                size="sm"
                flat
                round
              />
            </q-card-actions>
          </q-card>
        </div>
      </div>
    </div>

    <!-- User Details Dialog -->
    <q-dialog v-model="showUserDetails" maximized>
      <UserDetailsDialog
        :user="selectedUser"
        :type="type"
        @close="closeUserDetails"
      />
    </q-dialog>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import UserDetailsDialog from './UserDetailsDialog.vue';

// Props
const props = defineProps({
  users: {
    type: Array,
    default: () => []
  },
  type: {
    type: String,
    required: true
  },
  loading: {
    type: Boolean,
    default: false
  }
});

// Emits
const emit = defineEmits(['manage-roles']);

// Reactive data
const showUserDetails = ref(false);
const selectedUser = ref(null);

// Methods
const getUserTypeIcon = (type) => {
  const icons = {
    teacher: 'school',
    student: 'person',
    parent: 'family_restroom',
    other: 'people_outline'
  };
  return icons[type] || 'person';
};

const getUserTypeColor = (type) => {
  const colors = {
    teacher: 'green',
    student: 'blue',
    parent: 'orange',
    other: 'grey'
  };
  return colors[type] || 'grey';
};

const viewUserDetails = (user) => {
  selectedUser.value = user;
  showUserDetails.value = true;
};

const closeUserDetails = () => {
  showUserDetails.value = false;
  selectedUser.value = null;
};
</script>

<style scoped>
.user-card {
  transition: all 0.3s ease;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  height: 100%;
  display: flex;
  flex-direction: column;
}

.user-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.user-header {
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  border-bottom: 1px solid #e0e0e0;
}

.user-content {
  flex: 1;
  padding: 16px;
}

.user-actions {
  padding: 8px 16px 16px;
  border-top: 1px solid #f0f0f0;
}

.teacher-info,
.student-info,
.parent-info {
  margin-bottom: 12px;
}

.roles-section {
  border-top: 1px solid #f0f0f0;
  padding-top: 12px;
}

/* Dark mode support */
.body--dark .user-card {
  background: #2d2d2d;
  color: #f5f5f5;
}

.body--dark .user-header {
  background: linear-gradient(135deg, #3a3a3a 0%, #2d2d2d 100%);
  border-bottom-color: #444;
}

.body--dark .user-actions {
  border-top-color: #444;
}

.body--dark .roles-section {
  border-top-color: #444;
}

/* Responsive */
@media (max-width: 600px) {
  .user-header .row {
    flex-direction: column;
    gap: 12px;
    text-align: center;
  }

  .user-actions {
    flex-direction: column;
    gap: 8px;
  }
}
</style>
