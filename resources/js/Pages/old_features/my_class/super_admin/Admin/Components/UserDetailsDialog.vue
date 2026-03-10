<template>
  <q-card class="user-details-dialog">
    <q-card-section class="dialog-header">
      <div class="row items-center">
        <div class="col">
          <div class="text-h5 text-weight-bold">
            <q-icon :name="getUserTypeIcon(type)" class="q-mr-sm" :color="getUserTypeColor(type)" />
            {{ user?.name || 'User Details' }}
          </div>
          <div class="text-subtitle2 text-grey-7">{{ type.charAt(0).toUpperCase() + type.slice(1) }} Information</div>
        </div>
        <div class="col-auto">
          <q-btn
            flat
            round
            icon="close"
            @click="$emit('close')"
          />
        </div>
      </div>
    </q-card-section>

    <q-card-section class="dialog-content">
      <div v-if="user" class="row q-gutter-lg">
        <!-- Basic Information -->
        <div class="col-12 col-md-6">
          <q-card class="info-card">
            <q-card-section>
              <div class="text-h6 text-weight-bold q-mb-md">
                <q-icon name="person" class="q-mr-sm" />
                Basic Information
              </div>
              <q-list>
                <q-item>
                  <q-item-section>
                    <q-item-label caption>Name</q-item-label>
                    <q-item-label>{{ user.name }}</q-item-label>
                  </q-item-section>
                </q-item>
                <q-item>
                  <q-item-section>
                    <q-item-label caption>Email</q-item-label>
                    <q-item-label>{{ user.email }}</q-item-label>
                  </q-item-section>
                </q-item>
                <q-item v-if="user.school_name">
                  <q-item-section>
                    <q-item-label caption>School</q-item-label>
                    <q-item-label>{{ user.school_name }}</q-item-label>
                  </q-item-section>
                </q-item>
              </q-list>
            </q-card-section>
          </q-card>
        </div>

        <!-- Type-specific Information -->
        <div class="col-12 col-md-6">
          <q-card class="info-card">
            <q-card-section>
              <div class="text-h6 text-weight-bold q-mb-md">
                <q-icon :name="getUserTypeIcon(type)" class="q-mr-sm" />
                {{ type.charAt(0).toUpperCase() + type.slice(1) }} Details
              </div>

              <!-- Teacher Details -->
              <div v-if="type === 'teacher'">
                <q-list>
                  <q-item v-if="user.subjects && user.subjects.length">
                    <q-item-section>
                      <q-item-label caption>Subjects</q-item-label>
                      <q-item-label>
                        <div class="q-gutter-xs">
                          <q-chip
                            v-for="subject in user.subjects"
                            :key="subject"
                            size="sm"
                            color="green"
                            text-color="white"
                          >
                            {{ subject }}
                          </q-chip>
                        </div>
                      </q-item-label>
                    </q-item-section>
                  </q-item>
                  <q-item v-if="user.classrooms && user.classrooms.length">
                    <q-item-section>
                      <q-item-label caption>Classrooms</q-item-label>
                      <q-item-label>
                        <div class="q-gutter-xs">
                          <q-chip
                            v-for="classroom in user.classrooms"
                            :key="classroom"
                            size="sm"
                            color="blue"
                            text-color="white"
                          >
                            {{ classroom }}
                          </q-chip>
                        </div>
                      </q-item-label>
                    </q-item-section>
                  </q-item>
                </q-list>
              </div>

              <!-- Student Details -->
              <div v-if="type === 'student'">
                <q-list>
                  <q-item v-if="user.classroom_name">
                    <q-item-section>
                      <q-item-label caption>Classroom</q-item-label>
                      <q-item-label>{{ user.classroom_name }}</q-item-label>
                    </q-item-section>
                  </q-item>
                  <q-item v-if="user.grade_name">
                    <q-item-section>
                      <q-item-label caption>Grade</q-item-label>
                      <q-item-label>{{ user.grade_name }}</q-item-label>
                    </q-item-section>
                  </q-item>
                  <q-item v-if="user.stage_name">
                    <q-item-section>
                      <q-item-label caption>Stage</q-item-label>
                      <q-item-label>{{ user.stage_name }}</q-item-label>
                    </q-item-section>
                  </q-item>
                </q-list>
              </div>

              <!-- Parent Details -->
              <div v-if="type === 'parent'">
                <q-list>
                  <q-item v-if="user.children_count">
                    <q-item-section>
                      <q-item-label caption>Number of Children</q-item-label>
                      <q-item-label>{{ user.children_count }}</q-item-label>
                    </q-item-section>
                  </q-item>
                  <q-item v-if="user.children_classrooms && user.children_classrooms.length">
                    <q-item-section>
                      <q-item-label caption>Children's Classrooms</q-item-label>
                      <q-item-label>
                        <div class="q-gutter-xs">
                          <q-chip
                            v-for="classroom in user.children_classrooms"
                            :key="classroom"
                            size="sm"
                            color="blue"
                            text-color="white"
                          >
                            {{ classroom }}
                          </q-chip>
                        </div>
                      </q-item-label>
                    </q-item-section>
                  </q-item>
                </q-list>
              </div>
            </q-card-section>
          </q-card>
        </div>

        <!-- Roles Information -->
        <div class="col-12">
          <q-card class="info-card">
            <q-card-section>
              <div class="text-h6 text-weight-bold q-mb-md">
                <q-icon name="security" class="q-mr-sm" />
                Roles & Permissions
              </div>
              <div v-if="user.roles && user.roles.length" class="q-gutter-sm">
                <q-chip
                  v-for="role in user.roles"
                  :key="role.id"
                  color="indigo"
                  text-color="white"
                  icon="security"
                  size="md"
                >
                  {{ role.name }}
                </q-chip>
              </div>
              <div v-else class="text-grey-6">
                No roles assigned to this user
              </div>
            </q-card-section>
          </q-card>
        </div>
      </div>
    </q-card-section>

    <q-card-actions class="dialog-actions">
      <q-btn
        color="primary"
        icon="admin_panel_settings"
        label="Manage Roles"
        @click="manageRoles"
      />
      <q-space />
      <q-btn
        color="grey-7"
        label="Close"
        @click="$emit('close')"
        outline
      />
    </q-card-actions>
  </q-card>
</template>

<script setup>
// Props
const props = defineProps({
  user: {
    type: Object,
    default: () => null
  },
  type: {
    type: String,
    required: true
  }
});

// Emits
const emit = defineEmits(['close', 'manage-roles']);

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

const manageRoles = () => {
  emit('manage-roles', props.user);
  emit('close');
};
</script>

<style scoped>
.user-details-dialog {
  width: 100%;
  max-width: 900px;
  margin: 0 auto;
}

.dialog-header {
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  border-bottom: 1px solid #e0e0e0;
}

.dialog-content {
  max-height: 70vh;
  overflow-y: auto;
}

.info-card {
  border-radius: 8px;
  box-shadow: 0 1px 4px rgba(0,0,0,0.1);
}

.dialog-actions {
  border-top: 1px solid #e0e0e0;
  padding: 16px 24px;
}

/* Dark mode support */
.body--dark .user-details-dialog {
  background: #2d2d2d;
  color: #f5f5f5;
}

.body--dark .dialog-header {
  background: linear-gradient(135deg, #3a3a3a 0%, #2d2d2d 100%);
  border-bottom-color: #444;
}

.body--dark .info-card {
  background: #3a3a3a;
}

.body--dark .dialog-actions {
  border-top-color: #444;
}

/* Responsive */
@media (max-width: 600px) {
  .dialog-content .row {
    flex-direction: column;
  }
  
  .dialog-actions {
    flex-direction: column;
    gap: 8px;
  }
}
</style>
