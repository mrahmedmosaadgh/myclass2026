<template>
  <div class="p-6 space-y-6 bg-gradient-to-br from-blue-50 to-indigo-50 min-h-screen">



















    <!-- Header Card -->
    <q-card class="shadow-lg rounded-2xl overflow-hidden">
      <q-card-section class="bg-gradient-to-r from-blue-500 to-indigo-600 text-white">
        <h1 class="text-3xl font-bold">🏆 Reward System</h1>
        <p class="text-blue-100">Manage student behaviors and track achievements</p>
      </q-card-section>

      <!-- Control Panel -->
      <q-card-section class="p-6 space-y-4">
        <!-- Session Summary & Setup Button -->
        <div class="flex items-center justify-between bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
          <div class="flex items-center gap-4">
            <div class="bg-blue-100 p-3 rounded-full text-blue-600">
              <q-icon name="event_note" size="md" />
            </div>
            <div>
              <div class="text-sm text-gray-500 font-medium">Current Session</div>
              <div class="text-lg font-bold text-gray-800 flex items-center gap-2">
                <span v-if="selectedClassroomId">
                  {{ classrooms.find(c => c.classroom_id === selectedClassroomId)?.classroom_name || 'Unknown Class' }}
                </span>
                <span v-else class="text-gray-400 italic">No classroom selected</span>
                
                <span class="text-gray-300">|</span>
                
                <span class="text-blue-600 font-mono bg-blue-50 px-2 py-0.5 rounded text-base">
                  {{ periodCode }}
                </span>
              </div>
              <div class="text-xs text-gray-500 mt-1">
                {{ new Date(selectedDate).toLocaleDateString() }} • Period {{ selectedPeriodNumber }}
              </div>
            </div>
          </div>
          
          <q-btn
            color="primary"
            icon="settings"
            label="Setup Session"
            @click="showSetupDialog = true"
            size="lg"
            class="shadow-md"
          />
        </div>
      </q-card-section>
    </q-card>

    <!-- Session Setup Dialog -->
    <q-dialog v-model="showSetupDialog" full-width full-height>
      <q-card class="flex flex-col bg-gray-50">
        <q-toolbar class="bg-white border-b border-gray-200 p-4">
          <q-toolbar-title class="text-xl font-bold text-gray-800 flex items-center gap-2">
            <q-icon name="settings_suggest" color="primary" />
            Session Setup
          </q-toolbar-title>
          <q-btn flat round dense icon="close" v-close-popup />
        </q-toolbar>

        <q-card-section class="flex-1 overflow-auto p-6">
          <div class="max-w-7xl mx-auto space-y-8">
            <!-- Period Selection -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
              <h3 class="text-lg font-bold text-gray-700 mb-4 flex items-center gap-2">
                <q-icon name="schedule" class="text-blue-500" />
                Time & Period
              </h3>
              <PeriodSelectionRefactored
                :date="selectedDate"
                :semester="selectedSemester"
                :week="selectedWeek"
                :day="selectedDay"
                :period-number="selectedPeriodNumber"
                @update:date="selectedDate = $event"
                @update:semester="selectedSemester = $event"
                @update:week="selectedWeek = $event"
                @update:day="selectedDay = $event"
                @update:periodNumber="selectedPeriodNumber = $event"
                @change="handlePeriodChange"
                :persist="true"
                persistKey="reward-system-period-selection"
              />
              <div class="mt-4 p-3 bg-blue-50 rounded-lg border border-blue-100 flex justify-center">
                <p class="text-sm text-gray-700">
                  Active Period Code: <span class="font-bold text-blue-600 font-mono text-lg">{{ periodCode }}</span>
                </p>
              </div>
            </div>

            <!-- Classroom Selection -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
              <h3 class="text-lg font-bold text-gray-700 mb-4 flex items-center gap-2">
                <q-icon name="school" class="text-blue-500" />
                Classroom
              </h3>
              <ClassroomSelection
                v-model="selectedClassroomId"
                :classrooms="classrooms"
                :loading="loadingData"
                :init-status="initStatus"
                v-model:avatar-edit-enabled="avatarEditEnabled"
                @change="handleClassroomChange"
                @init="initClassroomSession"
              />
            </div>
          </div>
        </q-card-section>
      </q-card>
    </q-dialog>


































 







 

<!-- 

card2
   <card2
        v-for="student in students"
        :key="student.id"
        :student="student"
        @update-points="handleUpdatePoints"
      /> -->
 
      <!-- Selected Students List -->
      <!-- <div v-if="selectedIds.length" class="flex flex-col w-fit  gap-2 mb-4 p-3 bg-blue-50/50 rounded-xl border border-blue-100">
        <q-chip
          v-for="id in selectedIds"
          :key="id"
          removable
          @remove="toggleSelected(id)"
          color="white"
          text-color="primary"
          class="shadow-sm border border-blue-100 " 
        >
          <q-avatar icon="person" color="primary" text-color="white" font-size="16px" />
          <span class="  text-2xl pr-2 w-40">{{ students.find(s => s.id === id)?.firstName }}</span>
          <span class="  text-xl">{{ students.find(s => s.id === id)?.lastName }}</span>
        </q-chip>
      </div> -->





    <!-- Main Tabs -->

    <q-card class="shadow-lg rounded-2xl" v-if="students.length">
      <q-tabs
        v-model="activeTab"
        dense
        class="text-grey-7"
        active-color="primary"
        indicator-color="primary"
        align="left"
      >
        <q-tab name="attendance" icon="how_to_reg" label="Attendance" />
        <q-tab name="behavior_incidents" icon="report_problem" label="Behavior Incidents" />
        <q-tab name="positive" icon="add_circle" label="+ Points" />
        <!-- <q-tab name="negative" icon="remove_circle" label="- Points" /> -->
        <q-tab name="history" icon="cancel" label="Cancel" />
        <!-- <q-tab name="old_card" icon="grid_view" label="Old Card" /> -->
        <q-tab name="champions" icon="emoji_events" label="Champions" @click="showLeaderboard = true" />
        <q-tab name="behaviors" icon="settings" label="Behaviors" />
      </q-tabs>

      <q-separator />

      <q-tab-panels v-model="activeTab" animated>
        <!-- ATTENDANCE TAB -->
        <q-tab-panel name="attendance">
          <div class="space-y-4">
            <div class="flex justify-between items-center mb-4">
              <h3 class="text-xl font-bold">Manage Attendance</h3>
              <div class="flex gap-2">
                <q-btn
                  color="positive"
                  label="Mark All Present"
                  @click="markAllPresent"
                  :loading="bulkMarking"
                />
                <q-btn
                  color="warning"
                  label="Mark All Absent"
                  @click="markAllAbsent"
                  :loading="bulkMarking"
                />
              </div>
            </div>

            <div class="flex flex-wrap justify-center gap-6 p-4">
              <StudentCard
                v-for="student in students"
                :key="student.id"
                :student="student"
                :disable-behavior="!studentAttendance[student.id]"
                :allow-disabled-click="true"
                :avatar-edit-enabled="avatarEditEnabled"
                :student-summary="{
                  positive: studentBehaviors[student.id]?.points_plus || 0,
                  negative: studentBehaviors[student.id]?.points_minus || 0,
                  total: (studentBehaviors[student.id]?.points_plus || 0) - (studentBehaviors[student.id]?.points_minus || 0)
                }"
                @select="toggleAttendance(student.id)"
              />
            </div>
          </div>
        </q-tab-panel>

        <!-- BEHAVIOR INCIDENTS TAB -->
        <q-tab-panel name="behavior_incidents">
          <BehaviorIncidents
            :students="students"
            :classroom-id="selectedClassroomId"
            :date="selectedDate"
            :period-code="periodCode"
            @incident-recorded="handleIncidentRecorded"
          />
        </q-tab-panel>

        <!-- POSITIVE POINTS TAB -->
        <q-tab-panel name="positive">
          <div class=" ">
  
            <!-- Layout Selector Toolbar -->
            <q-card class="shadow-md rounded-xl mb-4">
              <q-card-section class="p-3 bg-gradient-to-r from-blue-50 to-indigo-50">
                <div class="flex items-center justify-between gap-4">
                  <div class="flex items-center gap-3 flex-1">
                    <q-icon name="view_module" size="sm" color="primary" />
                    <span class="font-bold text-gray-700">Layout:</span>
                    <q-select
                      v-model="selectedLayout"
                      :options="layoutOptions"
                      option-value="value"
                      option-label="label"
                      outlined
                      dense
                      emit-value
                      map-options
                      class="w-64"
                    >
                      <template v-slot:prepend>
                        <q-icon :name="selectedLayoutIcon" />
                      </template>
                    </q-select>
                  </div>
                  <q-btn
                    flat
                    color="primary"
                    icon="settings"
                    label="Manage Groups"
                    @click="showGroupEditor = true"
                  />
                </div>
              </q-card-section>
            </q-card>

            <!-- Behavior Selection -->
       


            <div class="flex flex-col md:flex-row gap-6 mt-6">
              
              <!-- Left Column: Selected Students (Sticky) -->
              <div class="w-full md:w-fit flex-shrink-0">
                <div class="sticky top-4">
                  <div class="bg-blue-50 rounded-xl border border-blue-100 p-4 shadow-sm">
                    <h4 class="font-bold text-blue-800 mb-3 flex items-center gap-2">
                      <q-icon name="checklist" />
                      Selected ({{ selectedIds.length }})
                    </h4>
                    
                    <div v-if="selectedIds.length === 0" class="text-center py-8 text-gray-400 italic text-sm">
                      Click students to select them
                    </div>

                    <div v-else class="flex flex-col gap-2 max-h-[calc(100vh-300px)] overflow-y-auto custom-scrollbar pr-1">
                      <q-chip
                        v-for="id in selectedIds"
                        :key="id"
                        removable
                        @remove="toggleSelected(id)"
                        color="white"
                        text-color="primary"
                        class="shadow-sm border border-blue-100 m-0 w-full  "
                      >
                        <q-avatar icon="person" color="primary" text-color="white" font-size="14px" />
                        <div class=" leading-tight overflow-hidden w-full ">
                          <span class="font-bold w-32   inline-block text-xl">{{ students.find(s => s.id === id)?.firstName }}</span>
                          <span class="text-xs opacity-80 truncate">{{ students.find(s => s.id === id)?.lastName }}</span>
                        </div>
                      </q-chip>
                    </div>
                    
                    <div class="mt-4 pt-3 border-t border-blue-200 space-y-2">
                      <!-- Selection Tools -->
                      <div class="grid grid-cols-3 gap-1">
                        <q-btn 
                          flat dense color="primary" label="All" size="sm" 
                          @click="selectAllPresent"
                          class="bg-blue-100/50"
                        />
                        <q-btn 
                          flat dense color="primary" label="Inv" size="sm" 
                          @click="inverseSelection"
                          class="bg-blue-100/50"
                        />
                        <q-btn 
                          flat dense color="negative" label="Clear" size="sm" 
                          @click="clearSelection"
                          class="bg-red-100/50"
                        />
                      </div>

                      <!-- Action Buttons -->
                      <div class="grid grid-cols-2 gap-2 pt-2">
                        <q-btn 
                          color="positive" icon="add" label="Points" 
                          @click="openBehaviorDialog('positive')"
                          :disable="selectedIds.length === 0"
                        />
                        <q-btn 
                          color="negative" icon="remove" label="Points" 
                          @click="openBehaviorDialog('negative')"
                          :disable="selectedIds.length === 0"
                        />
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Right Column: Student Grid -->
              <div class="flex-1">
                <div v-for="(group, index) in organizedStudents" :key="index" class="mb-6">
                  <!-- Group Header (only if groups exist) -->
                  <div v-if="group.name" class="mb-3 p-3 bg-gradient-to-r from-indigo-50 to-purple-50 rounded-lg border border-indigo-200">
                    <h4 class="font-bold text-indigo-900 flex items-center gap-2">
                      <q-icon name="groups" />
                      {{ group.name }}
                      <q-badge :label="group.students.length" color="indigo" />
                    </h4>
                  </div>
                  
                  <!-- Student Cards -->
                  <div class="flex flex-wrap justify-center gap-4">
                    <StudentCard
                      v-for="student in group.students"
                      :key="student.id"
                      :student="student"
                      :selected="selectedIds.includes(student.id)"
                      :selected-id="selectedIds.includes(student.id) ? student.id : null"
                      :disable-behavior="!studentAttendance[student.id]"
                      :allow-disabled-click="false"
                      :avatar-edit-enabled="avatarEditEnabled"
                      :student-summary="{
                        positive: studentBehaviors[student.id]?.points_plus || 0,
                        negative: studentBehaviors[student.id]?.points_minus || 0,
                        total: (studentBehaviors[student.id]?.points_plus || 0) - (studentBehaviors[student.id]?.points_minus || 0)
                      }"
                      @select="toggleSelected(student.id)"
                    />
                  </div>
                </div>
              </div>


            </div>

          </div>
        </q-tab-panel>

        <!-- NEGATIVE POINTS TAB -->
        <q-tab-panel name="negative">
          <div class="space-y-4">
            <div class="mb-4">
              <h3 class="text-xl font-bold mb-2">Deduct Points</h3>
              <p class="text-sm text-gray-600">Select students and choose a negative behavior</p>
            </div>

            <!-- Behavior Selection -->
            <div class="p-4 bg-red-50 rounded-lg border border-red-200">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <q-select
                  v-model="selectedNegativeBehaviorId"
                  :options="negativeBehaviors"
                  option-value="id"
                  option-label="name"
                  outlined
                  dense
                  placeholder="Select negative behavior"
                  emit-value
                  map-options
                >
                  <template v-slot:option="scope">
                    <q-item v-bind="scope.itemProps">
                      <q-item-section>
                        <q-item-label>{{ scope.opt.name }}</q-item-label>
                        <q-item-label caption>{{ scope.opt.value || scope.opt.points || 0 }} points</q-item-label>
                      </q-item-section>
                    </q-item>
                  </template>
                </q-select>
                <q-btn
                  color="negative"
                  icon="remove_circle"
                  label="Apply to Selected"
                  @click="applyNegativeBehavior"
                  :disable="!selectedIds.length || !selectedNegativeBehaviorId"
                  :loading="applyingBehavior"
                />
              </div>
              <div class="mt-2 text-sm">Selected: <strong>{{ selectedIds.length }}</strong> students</div>
            </div>

            <!-- Student Grid (New) -->
            <div class="flex flex-wrap justify-center gap-6 p-4">
              <StudentCard
                v-for="student in students"
                :key="student.id"
                :student="student"
                :selected="selectedIds.includes(student.id)"
                :selected-id="selectedIds.includes(student.id) ? student.id : null"
                :disable-behavior="!studentAttendance[student.id]"
                :allow-disabled-click="false"
                :avatar-edit-enabled="avatarEditEnabled"
                :student-summary="{
                  positive: studentBehaviors[student.id]?.points_plus || 0,
                  negative: studentBehaviors[student.id]?.points_minus || 0,
                  total: (studentBehaviors[student.id]?.points_plus || 0) - (studentBehaviors[student.id]?.points_minus || 0)
                }"
                @select="toggleSelected(student.id)"
              />
            </div>
          </div>
        </q-tab-panel>

        <!-- OLD CARD TAB -->
        <q-tab-panel name="old_card">
           <div class="space-y-4">
            <h3 class="text-xl font-bold mb-4">Old Card View</h3>
            <!-- Old Student Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              <div
                v-for="student in students"
                :key="student.id"
                class="p-4 border-2 rounded-lg transition"
                :class="[
                  !studentAttendance[student.id] 
                    ? 'bg-gray-100 border-gray-300 opacity-50 cursor-not-allowed'
                    : selectedIds.includes(student.id)
                    ? 'bg-green-100 border-green-500 cursor-pointer'
                    : 'bg-white border-gray-200 cursor-pointer'
                ]"
                @click="studentAttendance[student.id] && toggleSelected(student.id)"
              >
                <div class="flex items-start justify-between mb-3">
                  <div class="flex-1">
                    <p class="font-semibold text-lg">{{ student.name }}</p>
                    <p class="text-xs text-gray-600">ID: {{ student.id }}</p>
                    <p v-if="!studentAttendance[student.id]" class="text-xs text-red-600 mt-1">❌ Absent</p>
                  </div>
                  <q-checkbox
                    :model-value="selectedIds.includes(student.id)"
                    @update:model-value="toggleSelected(student.id)"
                    :disable="!studentAttendance[student.id]"
                    color="positive"
                    size="lg"
                  />
                </div>
                
                <!-- Points Display -->
                <div class="space-y-2">
                  <div class="p-2 bg-green-100 rounded flex justify-between items-center">
                    <span class="text-xs font-semibold text-green-800">Positive</span>
                    <span class="text-sm font-bold text-green-900">+{{ studentBehaviors[student.id]?.points_plus || 0 }} ⭐</span>
                  </div>
                  <div class="p-2 bg-red-100 rounded flex justify-between items-center">
                    <span class="text-xs font-semibold text-red-800">Negative</span>
                    <span class="text-sm font-bold text-red-900">-{{ studentBehaviors[student.id]?.points_minus || 0 }} ⚠️</span>
                  </div>
                  <div class="p-2 bg-blue-100 rounded flex justify-between items-center">
                    <span class="text-xs font-semibold text-blue-800">Total</span>
                    <span class="text-lg font-bold text-blue-600">{{ (studentBehaviors[student.id]?.points_plus || 0) - (studentBehaviors[student.id]?.points_minus || 0) }}</span>
                  </div>
                </div>
              </div>
            </div>
           </div>
        </q-tab-panel>

        <!-- HISTORY TAB -->
        <q-tab-panel name="history">
          <div class="space-y-4">
            <div class="flex justify-between items-center mb-4">
              <h3 class="text-xl font-bold">Recent Actions</h3>
              <q-btn
                color="primary"
                icon="refresh"
                label="Refresh"
                @click="loadHistory"
                :loading="loadingHistory"
              />
            </div>

            <div v-if="!recentActions.length" class="text-center py-8">
              <p class="text-gray-500 text-lg">No recent actions</p>
            </div>

            <div v-for="action in recentActions" :key="action.id" class="p-4 bg-white rounded-lg border-l-4"
              :class="[
                action.canceled ? 'border-gray-400 opacity-60' : 
                action.value > 0 ? 'border-green-500' : 'border-red-500'
              ]"
            >
              <div class="flex items-start justify-between">
                <div class="flex-1">
                  <div class="flex items-center gap-2 mb-1">
                    <span class="font-bold text-lg">{{ action.student_behavior?.student?.name || 'Unknown' }}</span>
                    <span class="text-2xl">{{ action.value > 0 ? '⭐' : '⚠️' }}</span>
                  </div>
                  <p class="text-sm text-gray-700">
                    <strong>{{ action.behavior?.name || 'Unknown Behavior' }}</strong>
                    <span :class="action.value > 0 ? 'text-green-600' : 'text-red-600'">
                      ({{ action.value > 0 ? '+' : '' }}{{ action.value }} points)
                    </span>
                  </p>
                  <p class="text-xs text-gray-500 mt-1">
                    {{ formatDateTime(action.created_at) }} by {{ action.created_by?.name || 'Unknown' }}
                  </p>
                  <p v-if="action.note" class="text-xs text-gray-600 mt-1 italic">Note: {{ action.note }}</p>
                  <p v-if="action.canceled" class="text-xs text-red-600 mt-1">
                    ❌ Canceled: {{ action.cancel_reason }} ({{ formatDateTime(action.canceled_at) }})
                  </p>
                </div>
                <q-btn
                  v-if="!action.canceled"
                  color="warning"
                  icon="undo"
                  label="Undo"
                  size="sm"
                  @click="undoAction(action.id)"
                  :loading="undoingAction === action.id"
                />
              </div>
            </div>
          </div>
        </q-tab-panel>

        <!-- BEHAVIORS MANAGEMENT TAB -->
        <q-tab-panel name="behaviors">
          <div class="space-y-6">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
              <h3 class="text-2xl font-bold text-gray-800">Behavior Management</h3>
              <q-btn
                color="primary"
                icon="add"
                label="Add New Behavior"
                @click="openBehaviorForm(null)"
                size="md"
                class="shadow-md"
              />
            </div>

            <!-- Positive Behaviors Section -->
            <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl p-6 border border-green-200">
              <div class="flex items-center gap-3 mb-4">
                <q-icon name="add_circle" size="md" color="positive" />
                <h4 class="text-xl font-bold text-green-800">Positive Behaviors / Rewards</h4>
                <q-badge :label="positiveBehaviors.length" color="positive" />
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div
                  v-for="behavior in positiveBehaviors"
                  :key="behavior.id"
                  class="bg-white rounded-lg p-4 shadow-sm border border-green-100 hover:shadow-md transition-all"
                >
                  <div class="flex items-start justify-between mb-2">
                    <div class="flex items-center gap-2">
                      <div class="text-3xl">{{ behavior.icon || '⭐' }}</div>
                      <div>
                        <h5 class="font-bold text-gray-800">{{ behavior.name }}</h5>
                        <p class="text-xs text-gray-500">ID: {{ behavior.id }}</p>
                      </div>
                    </div>
                    <div class="flex gap-1">
                      <q-btn
                        flat
                        round
                        dense
                        icon="edit"
                        color="primary"
                        size="sm"
                        @click="openBehaviorForm(behavior)"
                      >
                        <q-tooltip>Edit</q-tooltip>
                      </q-btn>
                      <q-btn
                        flat
                        round
                        dense
                        icon="delete"
                        color="negative"
                        size="sm"
                        @click="confirmDeleteBehavior(behavior)"
                      >
                        <q-tooltip>Delete</q-tooltip>
                      </q-btn>
                    </div>
                  </div>
                  
                  <div class="flex items-center justify-between mt-3 pt-3 border-t border-green-100">
                    <span class="text-sm text-gray-600">Points:</span>
                    <span class="text-lg font-bold text-green-600">+{{ behavior.value || behavior.points || 0 }}</span>
                  </div>
                </div>
              </div>

              <div v-if="positiveBehaviors.length === 0" class="text-center py-8 text-gray-400">
                <q-icon name="sentiment_neutral" size="3rem" class="mb-2" />
                <p>No positive behaviors yet. Click "Add New Behavior" to create one.</p>
              </div>
            </div>

            <!-- Negative Behaviors Section -->
            <div class="bg-gradient-to-r from-red-50 to-rose-50 rounded-xl p-6 border border-red-200">
              <div class="flex items-center gap-3 mb-4">
                <q-icon name="remove_circle" size="md" color="negative" />
                <h4 class="text-xl font-bold text-red-800">Negative Behaviors / Penalties</h4>
                <q-badge :label="negativeBehaviors.length" color="negative" />
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div
                  v-for="behavior in negativeBehaviors"
                  :key="behavior.id"
                  class="bg-white rounded-lg p-4 shadow-sm border border-red-100 hover:shadow-md transition-all"
                >
                  <div class="flex items-start justify-between mb-2">
                    <div class="flex items-center gap-2">
                      <div class="text-3xl">{{ behavior.icon || '⚠️' }}</div>
                      <div>
                        <h5 class="font-bold text-gray-800">{{ behavior.name }}</h5>
                        <p class="text-xs text-gray-500">ID: {{ behavior.id }}</p>
                      </div>
                    </div>
                    <div class="flex gap-1">
                      <q-btn
                        flat
                        round
                        dense
                        icon="edit"
                        color="primary"
                        size="sm"
                        @click="openBehaviorForm(behavior)"
                      >
                        <q-tooltip>Edit</q-tooltip>
                      </q-btn>
                      <q-btn
                        flat
                        round
                        dense
                        icon="delete"
                        color="negative"
                        size="sm"
                        @click="confirmDeleteBehavior(behavior)"
                      >
                        <q-tooltip>Delete</q-tooltip>
                      </q-btn>
                    </div>
                  </div>
                  
                  <div class="flex items-center justify-between mt-3 pt-3 border-t border-red-100">
                    <span class="text-sm text-gray-600">Points:</span>
                    <span class="text-lg font-bold text-red-600">{{ behavior.value || behavior.points || 0 }}</span>
                  </div>
                </div>
              </div>

              <div v-if="negativeBehaviors.length === 0" class="text-center py-8 text-gray-400">
                <q-icon name="sentiment_neutral" size="3rem" class="mb-2" />
                <p>No negative behaviors yet. Click "Add New Behavior" to create one.</p>
              </div>
            </div>
          </div>
        </q-tab-panel>
      </q-tab-panels>
    </q-card>

    <!-- Empty State -->
    <q-card v-if="!students.length" class="shadow-lg rounded-2xl">
      <q-card-section class="text-center py-12">
        <p class="text-2xl font-semibold text-gray-600">📚 Select a classroom and click "Init Session" to get started</p>
      </q-card-section>
    </q-card>

    <!-- Leaderboard Dialog -->
    <q-dialog v-model="showLeaderboard" maximized>
      <q-card>
              <q-card-actions align="right">
          <q-btn flat label="Close" color="primary" v-close-popup />
        </q-card-actions>
        <q-card-section class="p-0">
          <TopLeaderboard 
            :students="students" 
            :student-behaviors="studentBehaviors"
            :period-code="periodCode"
            :date="selectedDate"
          />
        </q-card-section>
        <q-card-actions align="right">
          <q-btn flat label="Close" color="primary" v-close-popup />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Behavior Selection Dialog -->
    <q-dialog v-model="showBehaviorDialog" full-width maxWidth="1000px">
      <q-card class="flex flex-col md:flex-row h-[80vh] md:h-[70vh]">
        
        <!-- Left Column: Selected Students -->
        <q-card-section class="w-full md:w-1/3 bg-blue-50 border-r border-blue-100 flex flex-col p-0">
          <div class="p-4 border-b border-blue-200 bg-blue-100/50">
            <h4 class="font-bold text-blue-900 flex items-center gap-2 text-lg">
              <q-icon name="checklist" />
              Selected ({{ selectedIds.length }})
            </h4>
          </div>

          <div class="flex-1 overflow-y-auto p-4 custom-scrollbar">
            <div v-if="selectedIds.length === 0" class="text-center py-8 text-gray-400 italic text-sm">
              Click students to select them
            </div>

            <div v-else class="flex flex-col gap-2">
              <q-chip
                v-for="id in selectedIds"
                :key="id"
                removable
                @remove="toggleSelected(id)"
                color="white"
                text-color="primary"
                class="shadow-sm border border-blue-100 m-0 w-full"
              >
                <q-avatar icon="person" color="primary" text-color="white" font-size="14px" />
                <div class="flex flex-col leading-tight overflow-hidden w-full">
                  <span class="font-bold text-2xl truncate">{{ students.find(s => s.id === id)?.firstName }}

                    <span class="text-sm opacity-80 truncate">{{ students.find(s => s.id === id)?.lastName }}</span>
                  </span>
                </div>
              </q-chip>
            </div>
          </div>

          <!-- Selection Tools Footer -->
          <div class="p-3 border-t border-blue-200 bg-white">
             <div class="grid grid-cols-3 gap-2">
              <q-btn 
                flat dense color="primary" label="All" size="sm" 
                @click="selectAllPresent"
                class="bg-blue-50"
              />
              <q-btn 
                flat dense color="primary" label="Inv" size="sm" 
                @click="inverseSelection"
                class="bg-blue-50"
              />
              <q-btn 
                flat dense color="negative" label="Clear" size="sm" 
                @click="clearSelection"
                class="bg-red-50"
              />
            </div>
          </div>
        </q-card-section>

        <!-- Right Column: Behavior Selection -->
        <div class="flex-1 flex flex-col bg-white">
          <q-card-section :class="behaviorDialogMode === 'positive' ? 'bg-green-50 border-b border-green-100' : 'bg-red-50 border-b border-red-100'">
            <div class="text-h6 font-bold flex items-center gap-2">
              <q-icon :name="behaviorDialogMode === 'positive' ? 'add_circle' : 'remove_circle'" 
                     :color="behaviorDialogMode === 'positive' ? 'positive' : 'negative'" />
              {{ behaviorDialogMode === 'positive' ? 'Add Positive Points' : 'Deduct Points' }}
            </div>
          </q-card-section>

          <q-card-section class="flex-1 overflow-y-auto p-4 custom-scrollbar">
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
              <div
                v-for="behavior in (behaviorDialogMode === 'positive' ? positiveBehaviors : negativeBehaviors)"
                :key="behavior.id"
                class="cursor-pointer transition-all duration-200 relative group"
                @click="selectedBehaviorIdForDialog = behavior.id"
              >
                <div 
                  class="h-full p-3 rounded-xl border-2 flex flex-col items-center text-center gap-2 hover:shadow-md transition-all"
                  :class="[
                    selectedBehaviorIdForDialog === behavior.id
                      ? (behaviorDialogMode === 'positive' ? 'border-green-500 bg-green-50 scale-105' : 'border-red-500 bg-red-50 scale-105')
                      : 'border-gray-100 bg-white hover:border-gray-300'
                  ]"
                >
                  <div 
                    class="text-3xl p-3 rounded-full mb-1"
                    :class="behaviorDialogMode === 'positive' ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600'"
                  >
                    {{ behavior.icon || (behaviorDialogMode === 'positive' ? '⭐' : '⚠️') }}
                  </div>
                  
                  <div class="font-bold text-xl leading-tight text-gray-800 line-clamp-2">
                    {{ behavior.name }}
                  </div>
                  
                  <div 
                    class="text-xl font-bold px-3 py-1 rounded-full mt-auto"
                    :class="behaviorDialogMode === 'positive' ? 'bg-green-200 text-green-800' : 'bg-red-200 text-red-800'"
                  >
                    {{ behaviorDialogMode === 'positive' ? '+' : '' }}{{ behavior.value || behavior.points || 0 }}
                  </div>

                  <!-- Selected Checkmark -->
                  <div 
                    v-if="selectedBehaviorIdForDialog === behavior.id"
                    class="absolute top-2 right-2 text-xl drop-shadow-sm"
                    :class="behaviorDialogMode === 'positive' ? 'text-green-600' : 'text-red-600'"
                  >
                    <q-icon name="check_circle" />
                  </div>
                </div>
              </div>
            </div>
          </q-card-section>

          <q-card-actions align="right" class="p-4 border-t border-gray-100 bg-gray-50 gap-3">
            <q-btn 
              flat 
              label="Cancel" 
              v-close-popup 
              color="grey-7" 
              size="lg"
              class="font-bold"
            />
            <q-btn 
              :color="behaviorDialogMode === 'positive' ? 'positive' : 'negative'"
              :label="behaviorDialogMode === 'positive' ? 'Apply Reward' : 'Apply Penalty'"
              :icon="behaviorDialogMode === 'positive' ? 'check_circle' : 'warning'"
              @click="applyBehaviorFromDialog"
              :disable="!selectedBehaviorIdForDialog"
              :loading="applyingBehavior"
              size="lg"
              class="px-8 font-bold shadow-md"
              push
            />
          </q-card-actions>
        </div>

      </q-card>
    </q-dialog>

    <!-- Behavior Form Dialog -->
    <q-dialog v-model="showBehaviorForm" persistent>
      <q-card class="min-w-[400px]">
        <q-card-section class="bg-primary text-white">
          <div class="text-h6">{{ editingBehavior ? 'Edit Behavior' : 'Add New Behavior' }}</div>
        </q-card-section>

        <q-card-section class="q-pt-md space-y-4">
          <q-input
            v-model="behaviorForm.name"
            label="Behavior Name *"
            outlined
            dense
            :rules="[val => !!val || 'Name is required']"
          />

          <q-select
            v-model="behaviorForm.type"
            :options="[
              { label: 'Positive / Reward', value: 'positive' },
              { label: 'Negative / Penalty', value: 'negative' }
            ]"
            label="Type *"
            outlined
            dense
            emit-value
            map-options
            :rules="[val => !!val || 'Type is required']"
          />

          <q-input
            v-model.number="behaviorForm.points"
            label="Points Value *"
            type="number"
            outlined
            dense
            :rules="[val => val !== null && val !== '' || 'Points are required']"
            :hint="behaviorForm.type === 'positive' ? 'Positive number (e.g., 5)' : 'Negative number (e.g., -3)'"
          />

          <q-input
            v-model="behaviorForm.icon"
            label="Icon (Emoji)"
            outlined
            dense
            hint="Optional emoji, e.g., ⭐ 🎉 ⚠️ 🚫"
          />
        </q-card-section>

        <q-card-actions align="right" class="q-px-md q-pb-md">
          <q-btn flat label="Cancel" v-close-popup />
          <q-btn
            color="primary"
            :label="editingBehavior ? 'Update' : 'Create'"
            @click="saveBehavior"
            :loading="savingBehavior"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Group Editor Dialog -->

    <q-dialog v-model="showGroupEditor" maximized>
      <StudentGrouping
        :students="students"
        :saved-layouts="savedLayouts"
        :editing-layout="editingLayout"
        @save="handleSaveLayout"
        @delete="handleDeleteLayout"
        @close="showGroupEditor = false"
      />
    </q-dialog>
  </div>
</template>

<script setup>
import { ref, watch, onMounted, computed } from 'vue'
import { useQuasar } from 'quasar'
import axios from 'axios'
import rewardPointService from './reward_sys_comp/reward_sys_point_action.js'
import PeriodSelectionRefactored from './reward_sys_comp/PeriodSelectionRefactored.vue'
import ClassroomSelection from './reward_sys_comp/ClassroomSelection.vue'
import TopLeaderboard from './reward_sys_comp/TopLeaderboard.vue'
import card2 from './final/card2.vue'; // Adjust path as needed
import card3 from './final/card3.vue'; // Adjust path as needed
import StudentCard from './reward_sys_comp/StudentCard.vue'
import StudentGrouping from './reward_sys_comp/StudentGrouping.vue'
import BehaviorIncidents from './reward_sys_comp/BehaviorIncidents.vue'
import noise from './final/noise.vue'; // Adjust path as needed

import pdf_main from './final/pdf_main.vue'
import PDFAnnotatorMain from './final/PDFAnnotatorMain.vue'
import video_player from './final/video_player.vue'
import video_player2 from './final/video_player2.vue'
import draw from './final/draw.vue'
import draw2 from './final/draw2.vue'
import draw3 from './final/draw3.vue'

const pdfUrl = ref('https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf')
// or local file: '/pdfs/sample.pdf'
// or base64: 'data:application/pdf;base64,JVBERi0x...'

const handleLoaded = () => {
  console.log('PDF loaded successfully!')
}

const handleError = (err ) => {
  console.error('Failed to load PDF:', err)
}

const $q = useQuasar()

// ============ REACTIVE STATE ============
const activeTab = ref('attendance')

// Watch for tab changes and clear selection
watch(activeTab, (newTab, oldTab) => {
  if (newTab !== oldTab) {
    selectedIds.value = []
  }
})
const classrooms = ref([])
const students = ref([])
const behaviors = ref([])
const selectedClassroomId = ref(null)
const selectedDate = ref(new Date().toISOString().split('T')[0])
const selectedSemester = ref(1)
const selectedWeek = ref(1)
const selectedDay = ref(1)
const selectedPeriodNumber = ref(1)
const selectedPositiveBehaviorId = ref(null)
const selectedNegativeBehaviorId = ref(null)
const selectedIds = ref([])
const studentBehaviors = ref({})
const studentAttendance = ref({})
const studentAttendanceSaving = ref({})
const recentActions = ref([])
const leaderboard = ref([])
const showLeaderboard = ref(false)
const loadingData = ref(false)
const applyingBehavior = ref(false)
const bulkMarking = ref(false)
const loadingHistory = ref(false)
const undoingAction = ref(null)
const studentBehaviorsMainId = ref(null)
const initStatus = ref({ message: '', created: 0, skipped: 0 })
const showSetupDialog = ref(true)
const avatarEditEnabled = ref(false)
const showBehaviorDialog = ref(false)
const behaviorDialogMode = ref('positive') // 'positive' or 'negative'
const selectedBehaviorIdForDialog = ref(null)

// Grouping state
const selectedLayout = ref('no_groups') // 'no_groups', 'name_asc', 'name_desc', or layout ID
const savedLayouts = ref([]) // Will be loaded from classroom_subject_teachers.data
const showGroupEditor = ref(false)
const editingLayout = ref(null)

// Behavior Management state
const showBehaviorForm = ref(false)
const editingBehavior = ref(null)
const savingBehavior = ref(false)
const behaviorForm = ref({
  name: '',
  type: 'positive',
  points: 0,
  icon: ''
})

// Computed period code generator
const periodCode = computed(() => {
  return `${selectedSemester.value}.${selectedWeek.value}.${selectedDay.value}.${selectedPeriodNumber.value}`
})

// Computed behavior lists
const positiveBehaviors = computed(() => {
  console.log('🔍 All behaviors:', behaviors.value)
  const positive = behaviors.value.filter(b => {
    // Check type field first, then value
    if (b.type) {
      return b.type === 'positive' || b.type === 'reward'
    }
    const value = b.value || b.points || 0
    return value > 0
  })
  console.log('✅ Positive behaviors:', positive)
  return positive
})

const negativeBehaviors = computed(() => {
  const negative = behaviors.value.filter(b => {
    // Check type field first, then value
    if (b.type) {
      return b.type === 'negative' || b.type === 'penalty'
    }
    const value = b.value || b.points || 0
    return value < 0
  })
  console.log('⚠️ Negative behaviors:', negative)
  return negative
})

// Layout options for dropdown
const layoutOptions = computed(() => {
  const baseOptions = [
    { label: 'No Groups', value: 'no_groups', icon: 'grid_view' },
    { label: 'Name (A→Z)', value: 'name_asc', icon: 'sort_by_alpha' },
    { label: 'Name (Z→A)', value: 'name_desc', icon: 'sort_by_alpha' }
  ]
  
  const customLayouts = savedLayouts.value.map(layout => ({
    label: layout.name,
    value: layout.id,
    icon: 'groups'
  }))
  
  return [...baseOptions, ...customLayouts]
})

// Icon for selected layout
const selectedLayoutIcon = computed(() => {
  const option = layoutOptions.value.find(opt => opt.value === selectedLayout.value)
  return option?.icon || 'grid_view'
})

// Organized students based on selected layout
const organizedStudents = computed(() => {
  if (selectedLayout.value === 'no_groups') {
    return [{ name: null, students: students.value }]
  }
  
  if (selectedLayout.value === 'name_asc') {
    const sorted = [...students.value].sort((a, b) => 
      (a.firstName || '').localeCompare(b.firstName || '')
    )
    return [{ name: null, students: sorted }]
  }
  
  if (selectedLayout.value === 'name_desc') {
    const sorted = [...students.value].sort((a, b) => 
      (b.firstName || '').localeCompare(a.firstName || '')
    )
    return [{ name: null, students: sorted }]
  }
  
  // Custom layout
  const layout = savedLayouts.value.find(l => l.id === selectedLayout.value)
  if (!layout) return [{ name: null, students: students.value }]
  
  const groups = layout.groups.map(group => ({
    name: group.name,
    students: students.value.filter(s => group.student_ids.includes(s.id))
  }))
  
  // Add unassigned students
  const assignedIds = new Set(layout.groups.flatMap(g => g.student_ids))
  const unassigned = students.value.filter(s => !assignedIds.has(s.id))
  if (unassigned.length > 0) {
    groups.push({ name: 'Unassigned', students: unassigned })
  }
  
  return groups
})

// Top 5 students by total points
// ============ METHODS ============

function selectAllPresent() {
  const presentStudents = students.value.filter(s => studentAttendance.value[s.id])
  selectedIds.value = presentStudents.map(s => s.id)
}

function inverseSelection() {
  const presentStudents = students.value.filter(s => studentAttendance.value[s.id])
  const currentSelectedSet = new Set(selectedIds.value)
  
  const newSelection = []
  presentStudents.forEach(s => {
    if (!currentSelectedSet.has(s.id)) {
      newSelection.push(s.id)
    }
  })
  selectedIds.value = newSelection
}

function openBehaviorDialog(mode) {
  if (selectedIds.value.length === 0) {
    $q.notify({
      message: 'Please select students first',
      color: 'warning',
      position: 'top'
    })
    return
  }
  behaviorDialogMode.value = mode
  selectedBehaviorIdForDialog.value = null
  showBehaviorDialog.value = true
}

async function applyBehaviorFromDialog() {
  if (!selectedBehaviorIdForDialog.value) return
  
  if (behaviorDialogMode.value === 'positive') {
    selectedPositiveBehaviorId.value = selectedBehaviorIdForDialog.value
    await applyPositiveBehavior()
  } else {
    selectedNegativeBehaviorId.value = selectedBehaviorIdForDialog.value
    await applyNegativeBehavior()
  }
  
  showBehaviorDialog.value = false
  selectedBehaviorIdForDialog.value = null
}

// Group Management Methods
async function loadSavedLayouts() {
  if (!selectedClassroomId.value) return
  
  try {
    const response = await axios.get('/api/classroom-layouts/load', {
      params: { classroom_id: selectedClassroomId.value }
    })
    
    if (response.data.success) {
      savedLayouts.value = response.data.data || []
    }
  } catch (error) {
    console.error('Error loading layouts:', error)
    // Silently fail - layouts are optional
  }
}

async function handleSaveLayout(layout) {
  // Check if updating existing or creating new
  const existingIndex = savedLayouts.value.findIndex(l => l.id === layout.id)
  
  if (existingIndex >= 0) {
    // Update existing
    savedLayouts.value[existingIndex] = layout
  } else {
    // Add new
    savedLayouts.value.push(layout)
  }
  
  // Persist to backend
  try {
    const response = await axios.post('/api/classroom-layouts/save', {
      classroom_id: selectedClassroomId.value,
      layouts: savedLayouts.value
    })
    
    if (response.data.success) {
      $q.notify({
        message: `Layout "${layout.name}" saved successfully!`,
        color: 'positive',
        position: 'top',
        icon: 'check_circle'
      })
      
      // Select the newly saved layout
      selectedLayout.value = layout.id
      showGroupEditor.value = false
      editingLayout.value = null
    } else {
      throw new Error(response.data.message || 'Failed to save layout')
    }
  } catch (error) {
    console.error('Error saving layout:', error)
    $q.notify({
      message: 'Failed to save layout: ' + (error.response?.data?.message || error.message),
      color: 'negative',
      position: 'top',
      icon: 'error'
    })
    
    // Revert the in-memory change
    if (existingIndex >= 0) {
      savedLayouts.value.splice(existingIndex, 1)
    } else {
      const newIndex = savedLayouts.value.findIndex(l => l.id === layout.id)
      if (newIndex >= 0) savedLayouts.value.splice(newIndex, 1)
    }
  }
}

async function handleDeleteLayout(layoutId) {
  // Find the layout to delete
  const layoutIndex = savedLayouts.value.findIndex(l => l.id === layoutId)
  if (layoutIndex < 0) return
  
  const deletedLayout = savedLayouts.value[layoutIndex]
  
  // Remove from local state
  savedLayouts.value.splice(layoutIndex, 1)
  
  // If the deleted layout was selected, reset to 'no_groups'
  if (selectedLayout.value === layoutId) {
    selectedLayout.value = 'no_groups'
  }
  
  // Persist to backend
  try {
    const response = await axios.post('/api/classroom-layouts/save', {
      classroom_id: selectedClassroomId.value,
      layouts: savedLayouts.value
    })
    
    if (response.data.success) {
      $q.notify({
        message: `Layout "${deletedLayout.name}" deleted successfully!`,
        color: 'positive',
        position: 'top',
        icon: 'delete'
      })
    } else {
      throw new Error(response.data.message || 'Failed to delete layout')
    }
  } catch (error) {
    console.error('Error deleting layout:', error)
    $q.notify({
      message: 'Failed to delete layout: ' + (error.response?.data?.message || error.message),
      color: 'negative',
      position: 'top',
      icon: 'error'
    })
    
    // Revert the deletion
    savedLayouts.value.splice(layoutIndex, 0, deletedLayout)
    if (selectedLayout.value === 'no_groups') {
      selectedLayout.value = layoutId
    }
  }
}

// Behavior Management Methods
function openBehaviorForm(behavior) {
  if (behavior) {
    // Editing existing behavior
    editingBehavior.value = behavior
    behaviorForm.value = {
      name: behavior.name,
      type: behavior.type || (behavior.value > 0 ? 'positive' : 'negative'),
      points: behavior.value || behavior.points || 0,
      icon: behavior.icon || ''
    }
  } else {
    // Creating new behavior
    editingBehavior.value = null
    behaviorForm.value = {
      name: '',
      type: 'positive',
      points: 0,
      icon: ''
    }
  }
  showBehaviorForm.value = true
}

async function saveBehavior() {
  // Validate
  if (!behaviorForm.value.name || !behaviorForm.value.type || behaviorForm.value.points === null) {
    $q.notify({
      message: 'Please fill in all required fields',
      color: 'warning',
      position: 'top'
    })
    return
  }

  savingBehavior.value = true

  try {
    const behaviorData = {
      name: behaviorForm.value.name,
      type: behaviorForm.value.type,
      value: behaviorForm.value.points,
      points: behaviorForm.value.points,
      icon: behaviorForm.value.icon || null,
      school_id: 1, // TODO: Get from authenticated user or classroom
      year_id: 2 // Academic year 2024-2025
    }

    if (editingBehavior.value) {
      // Update existing
      const response = await axios.put(`/api/behaviors/${editingBehavior.value.id}`, behaviorData)
      const index = behaviors.value.findIndex(b => b.id === editingBehavior.value.id)
      if (index >= 0) {
        behaviors.value[index] = response.data
      }
      $q.notify({
        message: 'Behavior updated successfully!',
        color: 'positive',
        icon: 'check_circle'
      })
    } else {
      // Create new
      const response = await axios.post('/api/behaviors', behaviorData)
      behaviors.value.push(response.data)
      $q.notify({
        message: 'Behavior created successfully!',
        color: 'positive',
        icon: 'check_circle'
      })
    }

    showBehaviorForm.value = false
    editingBehavior.value = null
  } catch (error) {
    console.error('Error saving behavior:', error)
    $q.notify({
      message: 'Failed to save behavior: ' + (error.response?.data?.message || error.message),
      color: 'negative',
      icon: 'error'
    })
  } finally {
    savingBehavior.value = false
  }
}

function confirmDeleteBehavior(behavior) {
  $q.dialog({
    title: 'Confirm Delete',
    message: `Are you sure you want to delete "${behavior.name}"? This action cannot be undone.`,
    cancel: true,
    persistent: true,
    color: 'negative'
  }).onOk(() => {
    deleteBehavior(behavior)
  })
}

async function deleteBehavior(behavior) {
  try {
    await axios.delete(`/api/behaviors/${behavior.id}`)
    const index = behaviors.value.findIndex(b => b.id === behavior.id)
    if (index >= 0) {
      behaviors.value.splice(index, 1)
    }
    $q.notify({
      message: 'Behavior deleted successfully!',
      color: 'positive',
      icon: 'check_circle'
    })
  } catch (error) {
    console.error('Error deleting behavior:', error)
    $q.notify({
      message: 'Failed to delete behavior: ' + (error.response?.data?.message || error.message),
      color: 'negative',
      icon: 'error'
    })
  }
}

function handlePeriodChange(data) {
  selectedDate.value = data.date
  selectedSemester.value = data.semester
  selectedWeek.value = data.week
  selectedDay.value = data.day
  selectedPeriodNumber.value = data.periodNumber
  console.log('📅 Period changed:', { periodCode: periodCode.value, ...data })
}

function toggleSelected(studentId) {
  // Don't allow selecting absent students
  if (!studentAttendance.value[studentId]) {
    $q.notify({
      message: 'Cannot select absent students',
      color: 'warning',
      position: 'top',
      timeout: 1000
    })
    return
  }
  
  const idx = selectedIds.value.indexOf(studentId)
  if (idx === -1) {
    selectedIds.value.push(studentId)
  } else {
    selectedIds.value.splice(idx, 1)
  }
}

function clearSelection() {
  selectedIds.value = []
}

async function handleClassroomChange(classroomId) {
  if (!classroomId) {
    students.value = []
    selectedIds.value = []
    savedLayouts.value = []
    return
  }

  try {
    loadingData.value = true
    students.value = []
    selectedIds.value = []
    studentBehaviorsMainId.value = null
    initStatus.value = { message: '', created: 0, skipped: 0 }
    
    // Load saved layouts for this classroom
    await loadSavedLayouts()
  } catch (error) {
    console.error('Failed to load classroom:', error)
    $q.notify({
      message: 'Failed to load classroom: ' + error.message,
      color: 'negative',
      position: 'top'
    })
  } finally {
    loadingData.value = false
  }
}

async function initClassroomSession() {
  if (!selectedClassroomId.value) return
  loadingData.value = true
  initStatus.value = { message: 'Initializing...', created: 0, skipped: 0 }

  try {
    const payload = {
      classroom_id: selectedClassroomId.value,
      date: selectedDate.value,
      period_code: periodCode.value,
    }

    const res = await axios.post('/api/student-behaviors/init-classroom', payload)
    if (res && res.data) {
      const d = res.data
      studentBehaviorsMainId.value = d.student_behaviors_mains_id
      initStatus.value = { 
        message: `Session initialized (created ${d.created}, skipped ${d.skipped})`, 
        created: d.created, 
        skipped: d.skipped 
      }
      
      const items = d.student_behaviors || []
      const mapped = items.map(b => ({
        id: b.student.id,
        name: b.student.name || `Student ${b.student_id}`,
        firstName: b.student.first_name,
        secondName: b.student.second_name,
        lastName: b.student.last_name,
        avatar: b.student.avatar,
        behaviorRecordId: b.id,
      }))

      students.value = mapped
      selectedIds.value = []
      
      const newStudentBehaviors = {}
      for (const b of items) {
        studentAttendance.value[b.student_id] = b.attend === undefined ? true : b.attend
        newStudentBehaviors[b.student_id] = {
          attend: b.attend === undefined ? true : b.attend,
          points_plus: b.points_plus || 0,
          points_minus: b.points_minus || 0,
        }
      }
      studentBehaviors.value = newStudentBehaviors

      $q.notify({ message: 'Session initialized', color: 'positive', position: 'top' })
      
      // Load history after init
      await loadHistory()
      showSetupDialog.value = false
    }
  } catch (err) {
    console.error('Failed to init classroom session:', err)
    initStatus.value = { message: 'Initialization failed', created: 0, skipped: 0 }
    $q.notify({ 
      message: 'Failed to init session: ' + (err.message || 'error'), 
      color: 'negative', 
      position: 'top' 
    })
  } finally {
    loadingData.value = false
  }
}

async function applyPositiveBehavior() {
  await applyBehaviorToStudents(selectedPositiveBehaviorId.value)
  selectedPositiveBehaviorId.value = null
}

async function applyNegativeBehavior() {
  await applyBehaviorToStudents(selectedNegativeBehaviorId.value)
  selectedNegativeBehaviorId.value = null
}

async function applyBehaviorToStudents(behaviorId) {
  if (!selectedIds.value.length || !behaviorId) return

  try {
    applyingBehavior.value = true

    const result = await rewardPointService.applyBehaviorToStudents(
      selectedIds.value,
      behaviorId,
      {
        date: selectedDate.value,
        periodCode: periodCode.value,
        classroomId: selectedClassroomId.value,
      }
    )

    if (result.success) {
      $q.notify({
        message: `Applied behavior to ${selectedIds.value.length} students`,
        color: 'positive',
        position: 'top'
      })
      await initClassroomSession()
      selectedIds.value = []
    } else {
      $q.notify({
        message: result.error || 'Failed to apply behavior',
        color: 'negative',
        position: 'top'
      })
    }
  } catch (error) {
    console.error('Error applying behavior:', error)
    $q.notify({
      message: error.message || 'Error applying behavior',
      color: 'negative',
      position: 'top'
    })
  } finally {
    applyingBehavior.value = false
  }
}

async function toggleAttendance(studentId, newValue) {
  const prev = studentAttendance.value[studentId] === undefined ? true : studentAttendance.value[studentId]
  const next = typeof newValue === 'boolean' ? newValue : !prev

  // If marking as absent, check if student has points for this session
  if (next === false) {
    const studentBehavior = studentBehaviors.value[studentId]
    const hasPoints = studentBehavior && (studentBehavior.points_plus > 0 || studentBehavior.points_minus > 0)
    
    if (hasPoints) {
      // Show warning dialog
      $q.dialog({
        title: 'Warning',
        message: `This student has ${studentBehavior.points_plus} positive and ${studentBehavior.points_minus} negative points for this session. Marking them absent will remove all their points for this session. Continue?`,
        cancel: true,
        persistent: true,
        ok: {
          label: 'Yes, Mark Absent',
          color: 'negative'
        },
        cancel: {
          label: 'Cancel',
          color: 'grey'
        }
      }).onOk(async () => {
        await performAttendanceUpdate(studentId, next, prev)
      })
      return
    }
  }

  await performAttendanceUpdate(studentId, next, prev)
}

async function performAttendanceUpdate(studentId, next, prev) {
  studentAttendance.value[studentId] = next
  studentAttendanceSaving.value[studentId] = true

  try {
    const res = await rewardPointService.updateAttendance(studentId, next, {
      date: selectedDate.value,
      periodCode: periodCode.value,
      classroomId: selectedClassroomId.value,
    })

    if (res.success) {
      $q.notify({ message: res.message || 'Attendance updated', color: 'positive', position: 'top' })
      
      // If marked absent and had points, refresh to show updated points
      if (next === false) {
        await initClassroomSession()
      }
    } else {
      throw new Error(res.error || 'Failed to update attendance')
    }
  } catch (err) {
    console.error('Failed to persist attendance for', studentId, err)
    studentAttendance.value[studentId] = prev
    $q.notify({ message: 'Failed to update attendance. Reverted.', color: 'negative', position: 'top' })
  } finally {
    studentAttendanceSaving.value[studentId] = false
  }
}

async function markAllPresent() {
  bulkMarking.value = true
  const attendancePayload = {}
  
  for (const student of students.value) {
    attendancePayload[student.id] = true
    studentAttendance.value[student.id] = true
  }

  try {
    const res = await rewardPointService.batchUpdateAttendance(attendancePayload, {
      date: selectedDate.value,
      periodCode: periodCode.value,
      classroomId: selectedClassroomId.value,
    })
    
    if (res.success) {
      $q.notify({ message: 'All students marked present', color: 'positive', position: 'top' })
    }
  } catch (err) {
    console.error('Failed to mark all present:', err)
    $q.notify({ message: 'Failed to mark all present', color: 'negative', position: 'top' })
  } finally {
    bulkMarking.value = false
  }
}

async function markAllAbsent() {
  bulkMarking.value = true
  const attendancePayload = {}
  
  for (const student of students.value) {
    attendancePayload[student.id] = false
    studentAttendance.value[student.id] = false
  }

  try {
    const res = await rewardPointService.batchUpdateAttendance(attendancePayload, {
      date: selectedDate.value,
      periodCode: periodCode.value,
      classroomId: selectedClassroomId.value,
    })
    
    if (res.success) {
      $q.notify({ message: 'All students marked absent', color: 'warning', position: 'top' })
    }
  } catch (err) {
    console.error('Failed to mark all absent:', err)
    $q.notify({ message: 'Failed to mark all absent', color: 'negative', position: 'top' })
  } finally {
    bulkMarking.value = false
  }
}

function getAttendanceClass(studentId) {
  const isPresent = studentAttendance.value[studentId]
  return isPresent 
    ? 'bg-green-50 border-green-300' 
    : 'bg-red-50 border-red-300 opacity-60'
}

async function loadHistory() {
  loadingHistory.value = true
  try {
    const result = await rewardPointService.getRecentActions({
      classroomId: selectedClassroomId.value,
      date: selectedDate.value,
      limit: 10
    })

    if (result.success) {
      recentActions.value = result.data
    } else {
      console.error('Failed to load history:', result.error)
    }
  } catch (error) {
    console.error('Error loading history:', error)
  } finally {
    loadingHistory.value = false
  }
}

async function undoAction(actionId) {
  undoingAction.value = actionId
  try {
    const result = await rewardPointService.undoAction(actionId, 'Undone by teacher')

    if (result.success) {
      $q.notify({
        message: 'Action undone successfully',
        color: 'positive',
        position: 'top'
      })
      await loadHistory()
      await initClassroomSession()
    } else {
      $q.notify({
        message: result.error || 'Failed to undo action',
        color: 'negative',
        position: 'top'
      })
    }
  } catch (error) {
    console.error('Error undoing action:', error)
    $q.notify({
      message: 'Error undoing action',
      color: 'negative',
      position: 'top'
    })
  } finally {
    undoingAction.value = null
  }
}

function formatDateTime(dateTime) {
  if (!dateTime) return ''
  const date = new Date(dateTime)
  return date.toLocaleString('en-US', {
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

function getMedalEmoji(index) {
  const medals = ['🥇', '🥈', '🥉']
  return medals[index] || `${index + 1}.`
}

function handleStudentClick(student) {
  console.log('Student clicked:', student)
  // You can add behavior manager or other actions here
  // For now, just log the click
}

async function handleIncidentRecorded(incident) {
  console.log('Incident recorded:', incident)
  // Refresh student behaviors to reflect the -1 point
  await initClassroomSession()
  $q.notify({
    message: 'Behavior incident recorded (-1 point)',
    color: 'warning',
    position: 'top'
  })
}

// ============ LIFECYCLE ============
onMounted(async () => {
  try {
    console.log('🚀 Initializing reward system...')

    // Load classrooms
    const classRes = await axios.get('/my_classes_with_students')
    classrooms.value = classRes.data
    console.log(`✅ Loaded ${classrooms.value.length} classrooms`)

    // Load behaviors
    const behaviorRes = await rewardPointService.fetchBehaviors()
    if (behaviorRes.success) {
      behaviors.value = behaviorRes.data
      console.log(`✅ Loaded ${behaviors.value.length} behaviors`)
      console.log('📋 Behaviors data:', behaviors.value)
      
      // Normalize behaviors to ensure they have a 'value' field
      behaviors.value = behaviors.value.map(b => ({
        ...b,
        value: b.value || b.points || 0
      }))
      
      console.log('📋 Normalized behaviors:', behaviors.value)
    } else {
      console.error('❌ Failed to load behaviors:', behaviorRes.error)
      $q.notify({
        message: 'Failed to load behaviors: ' + behaviorRes.error,
        color: 'negative',
        position: 'top'
      })
    }

    console.log('✅ Reward system initialized')
  } catch (error) {
    console.error('❌ Failed to initialize reward system:', error)
    $q.notify({
      message: 'Failed to initialize reward system: ' + error.message,
      color: 'negative',
      position: 'top'
    })
  }
})
</script>

<style scoped>
.space-y-6 > * + * {
  margin-top: 1.5rem;
}

.space-y-4 > * + * {
  margin-top: 1rem;
}

.gap-3 {
  gap: 0.75rem;
}

.gap-4 {
  gap: 1rem;
}
</style>
<style scoped>
.dojo-container {
  display: flex;
  flex-wrap: wrap;
  gap: 16px;
  padding: 24px;
  background: #f5f7fa;
  justify-content: center;
  font-family: 'Avenir', Helvetica, Arial, sans-serif;
}
</style>
