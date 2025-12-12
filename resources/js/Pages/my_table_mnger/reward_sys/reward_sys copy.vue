<template>
  <div class="p-6 space-y-6">
         
 

    <q-card class="main-container-card shadow-lg rounded-2xl overflow-hidden">
      <q-card-section class="bg-white">
        <div class="control-panel">
          <div class="flex flex-col gap-4">
            <div class="flex flex-row justify-center gap-4">
              <q-btn color="amber" icon="emoji_events" label="🏆 Show Leaderboard 🏆"
                @click="showLeaderboard = true; loadLeaderboard()" class="shadow-sm rounded-lg leaderboard-btn"
                size="md" :disable="!fetchData.my_sel_classroom_students?.length">
                <q-tooltip
                  :class="!fetchData.my_sel_classroom_students?.length ? 'bg-grey-8 text-white' : 'bg-amber text-white'"
                  anchor="top middle" self="bottom middle">
                  <q-icon :name="!fetchData.my_sel_classroom_students?.length ? 'warning' : 'emoji_events'" size="xs"
                    class="mr-1" />
                  {{ !fetchData.my_sel_classroom_students?.length ? 'Select a classroom first to view leaderboard' :
                    'View top 5 students with highest star points!' }}
                </q-tooltip>
              </q-btn>
            </div>

            <!-- Classroom select -->
            <div v-if="fetchData.my_classes_with_students?.length" class="p-2 flex items-center justify-center">
              <q-select
                dense
                outlined
                v-model="link"
                :options="fetchData.my_classes_with_students"
                option-value="id"
                option-label="classroom_name"
                label="Classroom"
                emit-value
                map-options
                @update:model-value="(id) => { const cls = (fetchData.my_classes_with_students || []).find(c => c.id == id); handleClassroomSelection(id, cls?.students || []) }"
                class="w-64"
              />
            </div>

            <!-- Period / Date / Subject selection -->
            <div class="p-4 mb-4 flex gap-4 items-center justify-center">
              <PeriodSelection
                v-model:date="selDate"
                v-model:subject="selSubject"
                v-model:semester="selSemester"
                v-model:week="selWeek"
                v-model:day="selDay"
                v-model:periodNumber="selPeriodNumber"
                :persist="true"
                :persistKey="STORAGE_KEY_LAST_SELECTION"
                @change="saveLastSelection"
              />


         













<!-- select classroom -->
              <!-- Classroom dropdown (alternate to the card list) -->
              <q-select v-if="fetchData.my_classes_with_students"
                @update:model-value="val => { const cls = (fetchData.my_classes_with_students || []).find(c => c.id === val); if (cls) handleClassroomSelection(cls.id, cls.students) }"
                filled v-model="link"
                :options="(fetchData.my_classes_with_students || []).map(c => ({ label: `${c.classroom_name} (${c.students?.length || 0})`, value: c.id }))"
                option-value="value" 
                option-label="label" 
                emit-value map-options 
                label="Classroom" 
                class="rounded-lg min-w-40" />

              <q-btn v-if="fetchData.my_sel_classroom_students?.length" color="info" icon="assessment"
                label="Load  Summaries" @click="loadAllStudentSummaries" class="shadow-sm rounded-lg" size="md"
                :loading="loadingSummaries">
                <q-tooltip class="bg-info text-white" anchor="top middle" self="bottom middle">
                  <q-icon name="assessment" size="xs" class="mr-1" />
                  Load behavior summaries for all students in this classroom
                </q-tooltip>
              </q-btn>


   </div>


            <div class="p-2 flex flex-col gap-4 items-stretch m-auto">
              <div class="flex items-center justify-between bg-white rounded-lg p-3 border border-blue-100 shadow-sm">
                <div class="flex items-center gap-3">
                  <q-badge color="primary" outline>Selected: {{ selectedIds.length }}</q-badge>
                  <q-select v-model="selectedBehaviorId" :options="(behaviors || []).map(b => ({ label: b.name, value: b.id }))" option-value="value" option-label="label" emit-value map-options dense outlined placeholder="Select behavior" class="min-w-48" />
                </div>
                <div class="flex items-center gap-2">
                  <q-btn color="positive" label="Present" @click="markSelectedPresent" :disable="!selectedIds.length" />
                  <q-btn color="warning" label="Absent" @click="markSelectedAbsent" :disable="!selectedIds.length" />
                  <q-btn color="primary" label="Apply Behavior" @click="applyBehaviorToSelected" :loading="applyingBehavior" :disable="!selectedIds.length || !selectedBehaviorId" />
                  <q-btn flat color="grey" label="Clear" @click="clearSelection" :disable="!selectedIds.length" />
                </div>
              </div>
              <div class="flex flex-wrap justify-center gap-2w-full">
                <StudentCard
                  v-for="student in fetchData.my_sel_classroom_students"
                  :key="student.id"
                  :student="student"
                  :card-class="getStudentCardClass(student.id)"
                  :student-summary="studentSummaries[student.id]"
                  :selected="selectedIds.includes(student.id)"
                  :avatar-edit-enabled="avatarEditingEnabled_2"
                  :show-avatar-buttons="shouldShowAvatarButtons"
                  :disable-behavior="shouldDisableBehaviorButtons(student.id)"
                  @select="toggleSelected"
                  @open-camera="openCamera"
                  @open-behavior="openBehaviorDialog"
                />
              </div>
            </div>
          </div>
        </div>
      </q-card-section>

      <q-separator />

      <q-card-section>
        <div v-if="!jsonData.length" class="text-gray-500 text-center">
          {{ t('common.noResults') || 'No JSON data provided' }}
        </div>

        <div v-else>
          <div class="mb-4 font-semibold">{{ t('common.preview') || 'Table Preview' }}</div>

          <q-separator class="my-4" />

          <div class="font-semibold mb-2">{{ t('common.edit') || 'Edit Configuration' }}</div>
          <q-table flat :rows="tableConfig" :columns="configColumns" row-key="key" dense>
            <template v-slot:body-cell-label="props">
              <q-input v-model="props.row.label" outlined dense />
            </template>

            <template v-slot:body-cell-type="props">
              <q-select v-model="props.row.type" :options="['text', 'email', 'number', 'button', 'list']" outlined
                dense />
            </template>

            <template v-slot:body-cell-visible="props">
              <q-toggle v-model="props.row.visible" color="primary" />
            </template>
          </q-table>
        </div>
      </q-card-section>
    </q-card>

    <!-- Paste JSON Dialog -->
    <q-dialog v-model="showPasteDialog">
      <q-card style="min-width: 500px">
        <q-card-section>
          <div class="text-lg font-semibold mb-2">Paste JSON Data</div>
          <q-input v-model="jsonInput" type="textarea" autogrow />
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Cancel" color="grey" v-close-popup />
          <q-btn flat label="Load" color="primary" @click="loadJsonData" />
          <q-btn flat label="Load Text" color="primary" @click="loadtextData" />

        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Behavior Recording Dialog for Student Feedback -->
    <q-dialog v-model="showBehaviorDialog">
      <q-card class="q-pa-md" style="min-width: 500px; max-width: 800px;">
        <q-card-section class="row items-center justify-between">
          <div class="text-h6">
            🧑‍🎓 {{ sel_student.name }}
          </div>
          <q-btn flat round icon="close" dense v-close-popup />
        </q-card-section>



        <q-separator />

        <q-card-section>
          <!-- Your reusable component -->
          <BehaviorManager 
          :student-id="sel_student.id" 
          :student-name="sel_student.f_l_name" 
          :period-code="period_code"
            :behaviors="behaviors" 
            :read="settings.readingAloudEnabled" 
            :summary="studentSummaries[sel_student.id] || { positive: 0, negative: 0, total: 0 }"
            @recorded="onBehaviorRecorded" 
            @close="showBehaviorDialog = false"
            
            />
        </q-card-section>
      </q-card>
    </q-dialog>

          <!-- File input for avatar uploads (hidden from view) -->
          <input ref="fileInputRef" type="file" accept="image/*" style="display:none" @change="handleFileSelected" />




    <!-- Camera dialog -->
    <q-dialog v-model="showCameraDialog">
      <q-card style="min-width: 500px">
        <CameraCapture @captured="handleCameraCapture" @cancel="handleCameraCancel" />
      </q-card>
    </q-dialog>

    <!-- 🏆 Leaderboard Dialog -->
    <q-dialog v-model="showLeaderboard" maximized>
      <q-card class="leaderboard-dialog">
        <!-- Header -->
        <q-card-section class="leaderboard-header">
          <div class="header-content">
            <div class="trophy-animation">🏆</div>
            <h1 class="leaderboard-title">Super Star Leaderboard!</h1>
            <p class="leaderboard-subtitle">Our Amazing Top 5 Students This Week! ⭐</p>
            <q-btn flat round icon="close" color="white" size="lg" @click="showLeaderboard = false" class="close-btn" />
          </div>
        </q-card-section>

        <!-- Period Selector -->
        <q-card-section class="period-selector">
          <div class="selector-content">
            <q-btn-toggle v-model="leaderboardPeriod" toggle-color="primary" :options="[
              { label: '📅 This Week', value: 'week' },
              { label: '📆 This Month', value: 'month' },
              { label: '🗓️ All Time', value: 'all' }
            ]" @update:model-value="loadLeaderboard" class="period-toggle" />
            <q-btn color="primary" icon="refresh" label="Refresh" @click="loadLeaderboard" class="refresh-btn" />
          </div>
        </q-card-section>

        <!-- Leaderboard Content -->
        <q-card-section class="leaderboard-content">
          <div v-if="loadingLeaderboard" class="loading-state">
            <q-spinner-hourglass size="4rem" color="primary" />
            <p class="loading-text">Loading our amazing students...</p>
          </div>

          <div v-else-if="leaderboardData.length === 0" class="empty-state">
            <div class="empty-icon">📚</div>
            <h3>No stars earned yet!</h3>
            <p>Let's start earning some points and see who becomes our first super star!</p>
          </div>

          <div v-else class="leaderboard-list">
            <!-- Top 3 Podium -->
            <div class="podium-section" v-if="leaderboardData.length >= 3">
              <div class="podium">
                <!-- 2nd Place -->
                <div class="podium-position second-place" v-if="leaderboardData[1]">
                  <div class="podium-student">
                    <div class="student-avatar-large">
                      <q-img :src="leaderboardData[1].avatar || placeholderAvatar(leaderboardData[1].name)"
                        class="avatar-img" />
                      <div class="position-badge silver">2</div>
                    </div>
                    <h3 class="student-name">{{ leaderboardData[1].first_name }}</h3>
                    <div class="student-points silver-points">{{ leaderboardData[1].total }} ⭐</div>
                    <div class="medal">🥈</div>
                  </div>
                  <div class="podium-base second-base">2nd</div>
                </div>

                <!-- 1st Place -->
                <div class="podium-position first-place" v-if="leaderboardData[0]">
                  <div class="podium-student">
                    <div class="student-avatar-large">
                      <q-img :src="leaderboardData[0].avatar || placeholderAvatar(leaderboardData[0].name)"
                        class="avatar-img" />
                      <div class="position-badge gold">1</div>
                      <div class="crown">👑</div>
                    </div>
                    <h3 class="student-name champion">{{ leaderboardData[0].first_name }}</h3>
                    <div class="student-points gold-points">{{ leaderboardData[0].total }} ⭐</div>
                    <div class="medal">🥇</div>
                  </div>
                  <div class="podium-base first-base">1st</div>
                </div>

                <!-- 3rd Place -->
                <div class="podium-position third-place" v-if="leaderboardData[2]">
                  <div class="podium-student">
                    <div class="student-avatar-large">
                      <q-img :src="leaderboardData[2].avatar || placeholderAvatar(leaderboardData[2].name)"
                        class="avatar-img" />
                      <div class="position-badge bronze">3</div>
                    </div>
                    <h3 class="student-name">{{ leaderboardData[2].first_name }}</h3>
                    <div class="student-points bronze-points">{{ leaderboardData[2].total }} ⭐</div>
                    <div class="medal">🥉</div>
                  </div>
                  <div class="podium-base third-base">3rd</div>
                </div>
              </div>
            </div>

            <!-- Remaining Students (4th and 5th) -->
            <div class="remaining-students" v-if="leaderboardData.length > 3">
              <div v-for="(student, index) in leaderboardData.slice(3, 5)" :key="student.id" class="student-row">
                <div class="row-position">{{ index + 4 }}</div>
                <div class="row-avatar">
                  <q-img :src="student.avatar || placeholderAvatar(student.name)" class="row-avatar-img" />
                </div>
                <div class="row-info">
                  <h4 class="row-name">{{ student.first_name }} {{ student.last_name }}
                  </h4>
                  <div class="row-points">{{ student.total }} ⭐ points</div>
                </div>
                <div class="row-badge">
                  {{ index === 0 ? '🌟' : '⭐' }}
                </div>
              </div>
            </div>

            <!-- Special Recognition for Top 3 -->
            <div class="recognition-section" v-if="leaderboardData.length > 0">
              <h3 class="recognition-title">🎉 Special Recognition! 🎉</h3>
              <div class="recognition-cards">
                <div class="recognition-card" v-if="leaderboardData[0]">
                  <div class="card-icon">👑</div>
                  <div class="card-title">Super Star Champion!</div>
                  <div class="card-name">{{ leaderboardData[0].first_name }}</div>
                </div>
                <div class="recognition-card" v-if="leaderboardData[1]">
                  <div class="card-icon">🌟</div>
                  <div class="card-title">Amazing Achiever!</div>
                  <div class="card-name">{{ leaderboardData[1].first_name }}</div>
                </div>
                <div class="recognition-card" v-if="leaderboardData[2]">
                  <div class="card-icon">⭐</div>
                  <div class="card-title">Fantastic Friend!</div>
                  <div class="card-name">{{ leaderboardData[2].first_name }}</div>
                </div>
              </div>
            </div>
          </div>
        </q-card-section>

        <!-- Footer with Encouragement -->
        <q-card-section class="leaderboard-footer">
          <div class="footer-content">
            <p class="encouragement-message">
              🌟 Every student is a star! Keep being awesome and earning those points! 🌟
            </p>
            <q-btn color="primary" label="🎯 Back to Classroom" @click="showLeaderboard = false" class="back-btn"
              size="lg" />
          </div>
        </q-card-section>
      </q-card>
    </q-dialog>

    <!-- Settings Dialog -->
    <q-dialog v-model="showSettingsDialog">
      <q-card style="min-width: 600px; max-width: 800px;">
        <q-card-section>
          <div class="text-h6 flex items-center gap-2">
            <q-icon name="settings" color="primary" />
            Classroom Settings
          </div>
        </q-card-section>

        <q-separator />
        <!-- Reading Aloud Toggle -->
        <q-card-section>
          <div class="text-subtitle1 mb-3 font-semibold flex items-center gap-2">
            <q-icon name="record_voice_over" color="primary" />
            Reading Aloud
          </div>
          <div class="settings-toggle-container enhanced-toggle-container">
            <q-toggle v-model="settings.readingAloudEnabled" label="Enable Reading Aloud" color="primary" size="lg"
              class="mb-2 settings-toggle enhanced-settings-toggle"
              :class="{ 'toggle-enabled': settings.readingAloudEnabled, 'toggle-disabled': !settings.readingAloudEnabled }">
              <q-tooltip class="bg-grey-8 text-white tooltip-enhanced" anchor="top middle" self="bottom middle"
                :class="{ 'tooltip-success': settings.readingAloudEnabled, 'tooltip-warning': !settings.readingAloudEnabled }">
                <q-icon :name="settings.readingAloudEnabled ? 'volume_up' : 'volume_off'" size="xs" class="mr-1" />
                {{ settings.readingAloudEnabled ? 'Reading aloud is enabled - the app will play text aloud where supported' : 'Reading aloud is disabled - no text-to-speech will play' }}
              </q-tooltip>
            </q-toggle>
            <div class="toggle-status-indicator enhanced-status-indicator">
              <q-icon :name="settings.readingAloudEnabled ? 'check_circle' : 'cancel'"
                :color="settings.readingAloudEnabled ? 'positive' : 'negative'" size="sm" class="status-icon"
                :class="{ 'icon-pulse': settings.readingAloudEnabled }">
                <q-tooltip :class="settings.readingAloudEnabled ? 'bg-positive text-white' : 'bg-negative text-white'"
                  anchor="top middle" self="bottom middle">
                  <q-icon :name="settings.readingAloudEnabled ? 'thumb_up' : 'thumb_down'" size="xs" class="mr-1" />
                  Reading aloud is {{ settings.readingAloudEnabled ? 'active' : 'inactive' }}
                </q-tooltip>
              </q-icon>
              <span class="text-sm font-medium transition-all duration-300"
                :class="settings.readingAloudEnabled ? 'text-positive' : 'text-negative'">
                {{ settings.readingAloudEnabled ? 'Enabled' : 'Disabled' }}
              </span>
              <!-- Visual state indicator -->
              <div class="ml-2 w-2 h-2 rounded-full transition-all duration-300"
                :class="settings.readingAloudEnabled ? 'bg-positive shadow-green-glow animate-pulse' : 'bg-negative shadow-red-glow'">
              </div>
            </div>
          </div>
          <div class="text-caption text-grey-6 ml-8 mt-2">
            <q-icon name="info" size="xs" class="mr-1" />
            When enabled, the app will play text aloud where supported (text-to-speech)
          </div>
        </q-card-section>
        <!-- Avatar Editing Toggle -->
        <q-card-section>
          <div class="text-subtitle1 mb-3 font-semibold flex items-center gap-2">
            <q-icon name="photo_camera" color="primary" />
            Avatar Management
          </div>
          <div class="settings-toggle-container enhanced-toggle-container">
            <q-toggle v-model="avatarEditingEnabled_2" label="Enable Avatar Editing" color="primary" size="lg"
              class="mb-2 settings-toggle enhanced-settings-toggle"
              :class="{ 'toggle-enabled': avatarEditingEnabled_2, 'toggle-disabled': !avatarEditingEnabled_2 }">
              <q-tooltip class="bg-grey-8 text-white tooltip-enhanced" anchor="top middle" self="bottom middle"
                :class="{ 'tooltip-success': avatarEditingEnabled_2, 'tooltip-warning': !avatarEditingEnabled_2 }">
                <q-icon :name="avatarEditingEnabled_2 ? 'photo_camera' : 'photo_camera_off'" size="xs"
                  class="mr-1" />
                {{ avatarEditingEnabled_2 ? 'Avatar editing is currently enabled - students can upload/capture photos' : 'Avatar editing is currently disabled - students cannot change photos' }}
              </q-tooltip>
            </q-toggle>
            <div class="toggle-status-indicator enhanced-status-indicator">
              <q-icon :name="avatarEditingEnabled_2 ? 'check_circle' : 'cancel'"
                :color="avatarEditingEnabled_2 ? 'positive' : 'negative'" size="sm" class="status-icon"
                :class="{ 'icon-pulse': avatarEditingEnabled_2 }">
                <q-tooltip :class="avatarEditingEnabled_2 ? 'bg-positive text-white' : 'bg-negative text-white'"
                  anchor="top middle" self="bottom middle">
                  <q-icon :name="avatarEditingEnabled_2 ? 'thumb_up' : 'thumb_down'" size="xs" class="mr-1" />
                  Avatar editing is {{ avatarEditingEnabled_2 ? 'active' : 'inactive' }}
                </q-tooltip>
              </q-icon>
              <span class="text-sm font-medium transition-all duration-300"
                :class="avatarEditingEnabled_2 ? 'text-positive' : 'text-negative'">
                {{ avatarEditingEnabled_2 ? 'Enabled' : 'Disabled' }}
              </span>
              <!-- Visual state indicator -->
              <div class="ml-2 w-2 h-2 rounded-full transition-all duration-300"
                :class="avatarEditingEnabled_2 ? 'bg-positive shadow-green-glow animate-pulse' : 'bg-negative shadow-red-glow'">
              </div>
            </div>
          </div>
          <div class="text-caption text-grey-6 ml-8 mt-2">
            <q-icon name="info" size="xs" class="mr-1" />
            When enabled, students can upload or capture new profile pictures
          </div>
        </q-card-section>

        <q-separator />

        <!-- Attendance Section -->
        <q-card-section v-if="fetchData.my_sel_classroom_students?.length">
          <div class="text-subtitle1 mb-3 font-semibold">Student Attendance</div>
          <div class="text-caption text-grey-6 mb-4">
            Mark students as present or absent. Absent students will appear grayed out.
          </div>

          <div class="attendance-list max-h-80 overflow-y-auto">
            <div v-for="student in fetchData.my_sel_classroom_students" :key="student.id"
              class="attendance-item flex items-center justify-between p-3 rounded-lg mb-2 transition-all duration-300 hover:shadow-md"
              :class="[
                isStudentPresent(student.id)
                  ? 'bg-green-50 hover:bg-green-100 border-l-4 border-green-400 attendance-present'
                  : 'bg-red-50 hover:bg-red-100 border-l-4 border-red-400 attendance-absent'
              ]">
              <div class="flex items-center gap-3">
                <div class="relative attendance-avatar-container">
                  <q-avatar size="40px" class="attendance-avatar">
                    <q-img :src="student.avatar || placeholderAvatar(student.name)"
                      class="rounded-full transition-all duration-300"
                      :class="{ 'grayscale opacity-60 blur-sm': !isStudentPresent(student.id) }" />
                  </q-avatar>
                  <!-- Enhanced attendance status icon overlay -->
                  <div class="absolute -top-1 -right-1 attendance-status-badge">
                    <q-icon :name="isStudentPresent(student.id) ? 'check_circle' : 'cancel'"
                      :color="isStudentPresent(student.id) ? 'positive' : 'negative'" size="sm"
                      class="bg-white rounded-full shadow-sm attendance-status-icon"
                      :class="{ 'pulse-animation': !isStudentPresent(student.id) }">
                      <q-tooltip
                        :class="isStudentPresent(student.id) ? 'bg-positive text-white' : 'bg-negative text-white'"
                        anchor="top middle" self="bottom middle">
                        <q-icon :name="isStudentPresent(student.id) ? 'person' : 'person_off'" size="xs" class="mr-1" />
                        {{ student.first_name }} is {{ isStudentPresent(student.id) ? 'present' :
                          'absent' }}
                      </q-tooltip>
                    </q-icon>
                  </div>
                  <!-- Presence indicator ring -->
                  <div class="absolute inset-0 rounded-full border-2 transition-all duration-300"
                    :class="isStudentPresent(student.id) ? 'border-green-400 shadow-green-glow' : 'border-red-400 shadow-red-glow'">
                  </div>
                </div>
                <div class="student-info">
                  <div class="font-medium transition-all duration-300"
                    :class="isStudentPresent(student.id) ? 'text-grey-8' : 'text-grey-5 line-through'">
                    {{ student.first_name }} {{ student.last_name }}
                    <q-icon v-if="!isStudentPresent(student.id)" name="visibility_off" size="xs"
                      class="ml-1 text-grey-4" />
                  </div>
                  <div class="text-sm transition-all duration-300"
                    :class="isStudentPresent(student.id) ? 'text-grey-6' : 'text-grey-4'"
                    v-if="student.second_name">
                    {{ student.second_name }}
                  </div>
                </div>
              </div>

              <div class="flex items-center gap-3">
                <q-chip :color="isStudentPresent(student.id) ? 'positive' : 'negative'" :text-color="'white'" size="sm"
                  class="font-medium attendance-chip transition-all duration-300"
                  :class="{ 'chip-pulse': !isStudentPresent(student.id) }"
                  :icon="isStudentPresent(student.id) ? 'person' : 'person_off'">
                  {{ isStudentPresent(student.id) ? 'Present' : 'Absent' }}
                  <q-tooltip :class="isStudentPresent(student.id) ? 'bg-positive text-white' : 'bg-negative text-white'"
                    anchor="top middle" self="bottom middle">
                    <q-icon :name="isStudentPresent(student.id) ? 'check' : 'close'" size="xs" class="mr-1" />
                    {{ student.first_name }} is currently marked as {{ isStudentPresent(student.id) ?
                      'present' : 'absent' }}
                  </q-tooltip>
                </q-chip>
                <q-toggle :model-value="isStudentPresent(student.id)"
                  @update:model-value="toggleStudentAttendance(student.id)"
                  :color="isStudentPresent(student.id) ? 'positive' : 'negative'" size="md"
                  class="attendance-toggle enhanced-toggle" :class="{ 'toggle-absent': !isStudentPresent(student.id) }">
                  <q-tooltip class="bg-grey-8 text-white tooltip-enhanced" anchor="top middle" self="bottom middle">
                    <q-icon :name="isStudentPresent(student.id) ? 'toggle_off' : 'toggle_on'" size="xs" class="mr-1" />
                    Click to mark {{ student.first_name }} as {{ isStudentPresent(student.id) ? 'absent'
                      :
                      'present' }}
                  </q-tooltip>
                </q-toggle>
              </div>
            </div>
          </div>

          <!-- Enhanced Attendance Actions -->
          <div class="flex gap-2 mt-4 pt-4 border-t border-grey-3">
            <q-btn color="positive" icon="check_circle" label="Mark All Present" @click="markAllPresent" size="sm"
              class="flex-1 attendance-action-btn enhanced-action-btn"
              :class="{ 'btn-success-glow': fetchData.my_sel_classroom_students?.every(s => isStudentPresent(s.id)) }">
              <q-tooltip class="bg-positive text-white tooltip-enhanced">
                <q-icon name="group" size="xs" class="mr-1" />
                Mark all {{ fetchData.my_sel_classroom_students?.length || 0 }} students in this classroom as present
              </q-tooltip>
            </q-btn>
            <q-btn color="negative" icon="cancel" label="Mark All Absent" @click="markAllAbsent" size="sm"
              class="flex-1 attendance-action-btn enhanced-action-btn"
              :class="{ 'btn-warning-glow': fetchData.my_sel_classroom_students?.every(s => !isStudentPresent(s.id)) }">
              <q-tooltip class="bg-negative text-white tooltip-enhanced">
                <q-icon name="group_off" size="xs" class="mr-1" />
                Mark all {{ fetchData.my_sel_classroom_students?.length || 0 }} students in this classroom as absent
              </q-tooltip>
            </q-btn>
          </div>

          <!-- Attendance Summary -->
          <div class="mt-3 p-3 bg-grey-1 rounded-lg attendance-summary">
            <div class="flex items-center justify-between text-sm">
              <div class="flex items-center gap-2">
                <q-icon name="group" color="primary" size="sm" />
                <span class="font-medium">Attendance Summary:</span>
              </div>
              <div class="flex items-center gap-4">
                <div class="flex items-center gap-1">
                  <q-icon name="check_circle" color="positive" size="xs" />
                  <span class="text-positive font-medium">
                    {{fetchData.my_sel_classroom_students?.filter(s => isStudentPresent(s.id)).length || 0}} Present
                  </span>
                </div>
                <div class="flex items-center gap-1">
                  <q-icon name="cancel" color="negative" size="xs" />
                  <span class="text-negative font-medium">
                    {{fetchData.my_sel_classroom_students?.filter(s => !isStudentPresent(s.id)).length || 0}} Absent
                  </span>
                </div>
              </div>
            </div>
            <!-- Progress bar -->
            <div class="mt-2">
              <q-linear-progress
                :value="fetchData.my_sel_classroom_students?.length ? (fetchData.my_sel_classroom_students.filter(s => isStudentPresent(s.id)).length / fetchData.my_sel_classroom_students.length) : 0"
                color="positive" track-color="negative" size="8px" rounded class="attendance-progress">
                <q-tooltip class="bg-grey-8 text-white">
                  {{ Math.round((fetchData.my_sel_classroom_students?.length ? (fetchData.my_sel_classroom_students.filter(s => isStudentPresent(s.id)).length / fetchData.my_sel_classroom_students.length) : 0) * 100) }}%
                  attendance
                  rate
                </q-tooltip>
              </q-linear-progress>
            </div>
          </div>
        </q-card-section>

        <!-- No Students Message -->
        <q-card-section v-else>
          <div class="text-center text-grey-6 py-8">
            <q-icon name="school" size="4rem" class="mb-4" />
            <div class="text-h6 mb-2">No Classroom Selected</div>
            <div class="text-body2">
              Please select a classroom to manage student attendance
            </div>
          </div>
        </q-card-section>

        <q-separator />

        <q-card-actions align="right" class="q-pa-md">
          <q-btn flat label="Test Classroom Switching" color="info" @click="testClassroomSwitching" size="sm"
            v-if="fetchData.my_classes_with_students?.length > 1" class="mr-2">
            <q-tooltip class="bg-info text-white">
              <q-icon name="swap_horiz" size="xs" class="mr-1" />
              Test settings behavior by switching between all available classrooms
            </q-tooltip>
          </q-btn>
          <q-btn flat label="Close" color="grey" @click="showSettingsDialog = false" size="md" />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </div>
</template>

<script setup>
import CameraCapture from './reward_sys_comp/CameraCapture.vue'
import NeonText from './reward_sys_comp/neontext.vue'
import PeriodSelection from './reward_sys_comp/PeriodSelectionRefactored.vue'
import StudentCard from './reward_sys_comp/StudentCard.vue'
 
const handleCameraCancel = () => {
  showCameraDialog.value = false
}

const handleCameraCapture = async ({ dataUrl }) => {
  // Convert dataUrl to Blob
  function dataURLtoBlob(dataurl) {
    const arr = dataurl.split(','), mime = arr[0].match(/:(.*?);/)[1], bstr = atob(arr[1]), n = bstr.length, u8arr = new Uint8Array(n);
    for (let i = 0; i < n; i++) u8arr[i] = bstr.charCodeAt(i);
    return new Blob([u8arr], { type: mime });
  }
  if (!selectedForAvatar.value || !dataUrl) return
  const blob = dataURLtoBlob(dataUrl)
  await uploadAvatar(selectedForAvatar.value.id, blob, 'capture.png')
  showCameraDialog.value = false
}
import { ref, onMounted, onUnmounted, watch, computed, nextTick } from 'vue'

import axios from 'axios'
import { useQuasar } from 'quasar'
import roadmap from './roadmap.vue'
import BehaviorManager from './BehaviorManager.vue'

import RoadmapEditor from './RoadmapTree/RoadmapEditor.vue'
import LmsRoadmapTree from './RoadmapTree/LmsRoadmapTree.vue'
import { createSelectionManager } from './reward_sys_comp/reward_sys_selection.js'
import rewardPointService from './reward_sys_comp/reward_sys_point_action.js'
// import { useSafeI18n } from '@/composables/useSafeI18n'
import { useI18n } from 'vue-i18n'
// const { t } = useSafeI18n()
const { notsafe_t } = useI18n()

// ✅ Safe translation function
const t = (key) => {
  if (!key || typeof key !== 'string') return key || ''
  try {
    return notsafe_t(key)
  } catch {
    return key
  }
}
const $q = useQuasar()
const link = ref('')
const period_code = ref('')
const ani_class = ref('neon-text')
const avatarEditingEnabled_2 = ref(false)

// Helper function to get today's date in YYYY-MM-DD format
const getTodayDate = () => {
  const today = new Date()
  return today.toISOString().split('T')[0]
}

// Selection persistence (date/subject/semester/week/day/period/classroom/period_code)
const selDate = ref(getTodayDate())
const selSubject = ref('Math')
const selSemester = ref(null)
const selWeek = ref(null)
const selDay = ref(null)
const selPeriodNumber = ref(null)
const selClassroomName = ref('')

const jsonData = ref([])

const STORAGE_KEY_LAST_SELECTION = 'reward-system-last-selection'

const semesterOptions = Array.from({ length: 4 }, (_, i) => ({ label: `Semester ${i + 1}`, value: i + 1 }))
const weekOptions = Array.from({ length: 16 }, (_, i) => ({ label: `Week ${i + 1}`, value: i + 1 }))
const dayOptions = Array.from({ length: 7 }, (_, i) => ({ label: `Day ${i + 1}`, value: i + 1 }))
const periodOptions = Array.from({ length: 8 }, (_, i) => ({ label: `Period ${i + 1}`, value: i + 1 }))
const periodCodeOptions = ref(['P-01', 'P-02', 'P-03', 'P-04'])

function saveLastSelection() {
  try {
    const payload = {
      date: selDate.value,
      subject: selSubject.value,
      semester: selSemester.value,
      week: selWeek.value,
      day: selDay.value,
      periodNumber: selPeriodNumber.value,
      classroomId: link.value || settings.value.currentClassroomId || null,
      classroomName: selClassroomName.value || null,
      period_code: period_code.value || null
    }
    localStorage.setItem(STORAGE_KEY_LAST_SELECTION, JSON.stringify(payload))
    // console.log('Saved last selection', payload)
    return true
  } catch (e) {
    console.warn('saveLastSelection failed', e)
    return false
  }
}

function loadLastSelection() {
  try {
    const raw = localStorage.getItem(STORAGE_KEY_LAST_SELECTION)
    if (!raw) {
      // No saved selection: reset date/subject to defaults and clear others
      selDate.value = getTodayDate()
      selSubject.value = 'Math'
      selSemester.value = null
      selWeek.value = null
      selDay.value = null
      selPeriodNumber.value = null
      link.value = ''
      selClassroomName.value = ''
      period_code.value = ''
      return false
    }
    const p = JSON.parse(raw)
    selDate.value = p.date ?? getTodayDate()
    selSubject.value = p.subject ?? 'Math'
    selSemester.value = p.semester ?? null
    selWeek.value = p.week ?? null
    selDay.value = p.day ?? null
    selPeriodNumber.value = p.periodNumber ?? null
    // link.value = p.classroomId ?? ''
    selClassroomName.value = p.classroomName ?? ''
    period_code.value = p.period_code ?? ''
    // console.log('Loaded last selection', p)
    return true
  } catch (e) {
    // On error, reset to defaults
    selDate.value = getTodayDate()
    selSubject.value = 'Math'
    selSemester.value = null
    selWeek.value = null
    selDay.value = null
    selPeriodNumber.value = null
    link.value = ''
    selClassroomName.value = ''
    period_code.value = ''
    console.warn('loadLastSelection failed', e)
    return false
  }
}

// save on unmount to ensure last state is persisted
onUnmounted(() => {
  saveLastSelection()
})
const sel = ref({
  classes: [],
  subjects: [],
  my_classes: [],
  my_subjects: [],
  teacher: null,
  teacher_classes: []
})
const tableConfig = ref([])

const configColumns = ref([
  { name: 'key', label: 'Field', field: 'key', align: 'left', sortable: true },
  { name: 'label', label: 'Label', field: 'label', align: 'left', sortable: true },
  { name: 'type', label: 'Type', field: 'type', align: 'left', sortable: true },
  { name: 'visible', label: 'Visible', field: 'visible', align: 'center' }
]);

function saveToDatabase(json) {
  console.log('Drawing JSON:', json)
  // send to API here
}


const jsonInput = ref('')
const showPasteDialog = ref(false)

const loadJsonData = () => {
  try {
    const parsed = JSON.parse(jsonInput.value);
    jsonData.value = Array.isArray(parsed) ? parsed : [parsed];
    if (jsonData.value.length > 0) {
      const firstRow = jsonData.value[0];
      tableConfig.value = Object.keys(firstRow).map(key => ({
        key,
        label: key,
        type: 'text',
        visible: true,
      }));
    }
    showPasteDialog.value = false;
    $q.notify({ type: 'positive', message: 'JSON Loaded' });
  } catch (e) {
    console.error("JSON parsing error:", e);
    $q.notify({ type: 'negative', message: 'Invalid JSON format' });
  }
};

const loadtextData = () => {
  try {
    const lines = jsonInput.value.split('\n').filter(l => l.trim() !== '');
    if (lines.length > 0) {
      jsonData.value = lines.map(line => ({ value: line }));
      tableConfig.value = [{ key: 'value', label: 'Value', type: 'text', visible: true }];
    }
    showPasteDialog.value = false;
    $q.notify({ type: 'positive', message: 'Text Loaded' });
  } catch (e) {
    console.error("Text loading error:", e);
    $q.notify({ type: 'negative', message: 'Failed to load text data' });
  }
};

const roadmapNodes = ref([
  {
    label: 'LMS System',
    children: [
      { label: 'Authentication' },
      { label: 'Class Management' },
      { label: 'Quiz System' },
      { label: 'Behavior Points' },
      { label: 'Weekly Plan' },
      { label: 'Lesson Planner' },
    ]
  }
])


const showBehaviorDialog = ref(false)
const sel_student = ref({ name: null, id: null, f_l_name: '' })
const studentSummaries = ref({}) // keyed by student id

// Leaderboard functionality
const showLeaderboard = ref(false)
const leaderboardData = ref([])
const loadingLeaderboard = ref(false)
const leaderboardPeriod = ref('week')

// Settings dialog
const showSettingsDialog = ref(false)

// avatar / camera
const fileInputRef = ref(null)
const selectedForAvatar = ref(null)
const showCameraDialog = ref(false)
const videoRef = ref(null)
const canvasRef = ref(null)
const cameraStream = ref(null)
const uploadingAvatar = ref(false)
const loadingSummaries = ref(false)

const placeholderAvatar = (name) => {
  try {
    // Try to generate an SVG with initials if name is provided
    if (name) {
      const initials = name.split(' ').map(s => s[0] || '').slice(0, 2).join('').toUpperCase()
      const bgColor = '#e2e8f0' // Tailwind gray-200
      const textColor = '#475569' // Tailwind gray-600
      const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="96" height="96" viewBox="0 0 96 96">
        <rect width="96" height="96" fill="${bgColor}" rx="48" ry="48"/>
        <text x="48" y="48" dy="8" text-anchor="middle" font-family="Arial" font-size="32" font-weight="bold" fill="${textColor}">${initials}</text>
      </svg>`
      return `data:image/svg+xml,${encodeURIComponent(svg)}`
    }
  } catch (e) {
    console.warn('Failed to generate avatar SVG:', e)
  }
  // Fallback to static SVG file
  return '/images/avatars/default-avatar.svg'
}

const openBehaviorDialog = (student_id, student_name) => {
  sel_student.value['id'] = student_id
  sel_student.value['name'] = student_name
  sel_student.value['f_l_name'] = f_l_name(student_name)

  showBehaviorDialog.value = true
  loadStudentSummary(student_id)
}

// open file picker for avatar upload
const openAvatarPicker = (student) => {
  // Check if avatar editing is enabled
  if (!settings.value.avatarEditingEnabled) {
    $q.notify({
      message: 'Avatar editing is currently disabled',
      color: 'warning',
      position: 'top'
    })
    return
  }

  selectedForAvatar.value = student
  // trigger hidden file input
  fileInputRef.value && fileInputRef.value.click()
}

const handleFileSelected = async (ev) => {
  const files = ev.target.files || ev.dataTransfer?.files
  if (!files || !files.length) return
  const file = files[0]
  if (!selectedForAvatar.value) return
  await uploadAvatar(selectedForAvatar.value.id, file)
  // clear selection
  ev.target.value = ''
}

// camera flow
const openCamera = async (student) => {
  // Check if avatar editing is enabled
  if (!settings.value.avatarEditingEnabled) {
    $q.notify({
      message: 'Avatar editing is currently disabled',
      color: 'warning',
      position: 'top'
    })
    return
  }

  selectedForAvatar.value = student
  showCameraDialog.value = true
  try {
    const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' }, audio: false })
    cameraStream.value = stream
    if (videoRef.value) {
      videoRef.value.srcObject = stream
      await videoRef.value.play()
    }
  } catch (e) {
    console.warn('Camera open failed', e)
    $q.notify({ message: 'Camera not available', color: 'negative' })
    showCameraDialog.value = false
  }
}

const closeCamera = () => {
  showCameraDialog.value = false
  if (cameraStream.value) {
    cameraStream.value.getTracks().forEach(t => t.stop())
    cameraStream.value = null
  }
}

const captureImage = async () => {
  if (!videoRef.value) return
  const video = videoRef.value
  const canvas = canvasRef.value || document.createElement('canvas')
  canvas.width = video.videoWidth || 640
  canvas.height = video.videoHeight || 480
  const ctx = canvas.getContext('2d')
  ctx.drawImage(video, 0, 0, canvas.width, canvas.height)
  canvas.toBlob(async (blob) => {
    if (!blob) return
    await uploadAvatar(selectedForAvatar.value.id, blob, 'capture.png')
    closeCamera()
  }, 'image/png')
}

const uploadAvatar = async (studentId, fileOrBlob, filename = null) => {
  try {
    uploadingAvatar.value = true
    const fd = new FormData()
    const file = fileOrBlob instanceof Blob ? new File([fileOrBlob], filename || 'avatar.png', { type: fileOrBlob.type || 'image/png' }) : fileOrBlob
    fd.append('avatar', file)
    // append student id just in case server expects it in body
    fd.append('student_id', studentId)
    const res = await axios.post(`/api/students/${studentId}/avatar`, fd, { headers: { 'Content-Type': 'multipart/form-data' } })
    $q.notify({ message: 'Avatar uploaded', color: 'positive' })
    // update local student object if present
    const list = fetchData.value.my_sel_classroom_students || []
    const s = list.find(ss => ss.id === studentId)
    if (s) {
      // assume server returns avatar URL in res.data.avatar or res.data.url
      s.avatar = res.data.avatar || res.data.url || s.avatar || null
    }
  } catch (e) {
    console.error('avatar upload failed', e)
    $q.notify({ message: e.response?.data?.message || 'Avatar upload failed', color: 'negative' })
  } finally {
    uploadingAvatar.value = false
  }
}

const loadStudentSummary = async (studentId) => {
  if (!studentId) return
  try {
    const res = await axios.get(`/api/student-behaviors/${studentId}`)
    studentSummaries.value[studentId] = res.data
  } catch (e) {
    console.warn('Failed to load student summary', e)
    studentSummaries.value[studentId] = { positive: 0, negative: 0, total: 0 }
  }
}

const loadAllStudentSummaries = async () => {
  if (!fetchData.value.my_sel_classroom_students?.length) {
    $q.notify({
      message: 'No students found in selected classroom!',
      color: 'warning',
      position: 'top'
    })
    return
  }

  loadingSummaries.value = true

  try {
    const students = fetchData.value.my_sel_classroom_students
    const promises = students.map(student => loadStudentSummary(student.id))

    await Promise.all(promises)

    $q.notify({
      message: `Successfully loaded summaries for ${students.length} students!`,
      color: 'positive',
      position: 'top',
      icon: 'assessment'
    })
  } catch (error) {
    console.error('Failed to load all student summaries:', error)
    $q.notify({
      message: 'Failed to load some student summaries',
      color: 'negative',
      position: 'top'
    })
  } finally {
    loadingSummaries.value = false
  }
}

// refresh summary when a behavior is recorded in the child
const onBehaviorRecorded = async () => {
  if (sel_student.value?.id) {
    await loadStudentSummary(sel_student.value.id)

    // If leaderboard is open, refresh it too
    if (showLeaderboard.value) {
      await loadLeaderboard()
    }
  }
}

// Leaderboard functions
const loadLeaderboard = async () => {
  if (!fetchData.value.my_sel_classroom_students?.length) {
    $q.notify({
      message: 'Please select a classroom first!',
      color: 'warning',
      position: 'top'
    })
    return
  }

  loadingLeaderboard.value = true

  try {
    // Get current classroom ID
    const classroomId = link.value

    // Calculate date range based on period
    const now = new Date()
    let startDate = new Date()

    switch (leaderboardPeriod.value) {
      case 'week':
        // Start of current week (Monday)
        const dayOfWeek = now.getDay()
        const daysToMonday = dayOfWeek === 0 ? 6 : dayOfWeek - 1
        startDate.setDate(now.getDate() - daysToMonday)
        startDate.setHours(0, 0, 0, 0)
        break
      case 'month':
        // Start of current month
        startDate = new Date(now.getFullYear(), now.getMonth(), 1)
        break
      case 'all':
        // Beginning of school year (September 1st of current year, or previous year if before September)
        const currentYear = now.getFullYear()
        const schoolYearStart = now.getMonth() >= 8 ? currentYear : currentYear - 1
        startDate = new Date(schoolYearStart, 8, 1) // September 1st
        break
    }

    // Fetch leaderboard data
    const response = await axios.get('/api/leaderboard', {
      params: {
        classroom_id: classroomId,
        start_date: startDate.toISOString().split('T')[0],
        end_date: now.toISOString().split('T')[0],
        limit: 5
      }
    })

    // Process the data to include student info
    const leaderboardWithStudents = response.data.map(entry => {
      const student = fetchData.value.my_sel_classroom_students.find(s => s.id === entry.student_id)
      return {
        ...entry,
        name: student?.name || 'Unknown Student',
        avatar: student?.avatar || null,
        first_name: student?.first_name || '',
        last_name: student?.last_name || ''
      }
    }).filter(entry => entry.total > 0)

    leaderboardData.value = leaderboardWithStudents

    // Play celebration sound if there are winners
    if (leaderboardWithStudents.length > 0) {
      playLeaderboardSound()
    }

  } catch (error) {
    console.error('Failed to load leaderboard:', error)

    // Fallback: create leaderboard from current student summaries
    const fallbackLeaderboard = Object.entries(studentSummaries.value)
      .map(([studentId, summary]) => {
        const student = fetchData.value.my_sel_classroom_students?.find(s => s.id == studentId)
        return {
          student_id: parseInt(studentId),
          name: student?.name || 'Unknown Student',
          avatar: student?.avatar || null,
          first_name: student?.first_name || '',
          last_name: student?.last_name || '',
          total: summary.total || 0,
          positive: summary.positive || 0,
          negative: summary.negative || 0
        }
      })
      .filter(entry => entry.total > 0)
      .sort((a, b) => b.total - a.total)
      .slice(0, 5)

    leaderboardData.value = fallbackLeaderboard

    if (fallbackLeaderboard.length === 0) {
      $q.notify({
        message: 'No student data available yet. Start recording some behaviors!',
        color: 'info',
        position: 'top'
      })
    }
  } finally {
    loadingLeaderboard.value = false
  }
}

// Play celebration sound for leaderboard
const playLeaderboardSound = () => {
  try {
    const audioContext = new (window.AudioContext || window.webkitAudioContext)()

    // Play a triumphant fanfare
    const playNote = (frequency, startTime, duration) => {
      const oscillator = audioContext.createOscillator()
      const gainNode = audioContext.createGain()

      oscillator.connect(gainNode)
      gainNode.connect(audioContext.destination)

      oscillator.frequency.setValueAtTime(frequency, startTime)
      oscillator.type = 'triangle'

      gainNode.gain.setValueAtTime(0, startTime)
      gainNode.gain.linearRampToValueAtTime(0.3, startTime + 0.01)
      gainNode.gain.exponentialRampToValueAtTime(0.01, startTime + duration)

      oscillator.start(startTime)
      oscillator.stop(startTime + duration)
    }

    // Fanfare melody
    const now = audioContext.currentTime
    playNote(523.25, now, 0.3)      // C5
    playNote(659.25, now + 0.15, 0.3) // E5
    playNote(783.99, now + 0.3, 0.3)  // G5
    playNote(1046.50, now + 0.45, 0.5) // C6

  } catch (error) {
    console.log('Audio not available')
  }
}


const fetchData = ref({
  my_sel_classroom_students: [],
  my_classes_with_students: [],
  classes: [],
  subjects: []
})

// Current view (card | table) with persistence
const currentView = ref(localStorage.getItem('studentViewPreference') || 'card')
watch(currentView, (nv) => {
  try { localStorage.setItem('studentViewPreference', nv) } catch (e) { /* ignore */ }
})

// Table helpers
const pagination = ref({ sortBy: 'name', descending: false, page: 1, rowsPerPage: 10 })
const studentColumns = ref([
  { name: 'name', label: 'Name', field: 'name', sortable: true },
  { name: 'id', label: 'ID', field: 'id', sortable: true },
  { name: 'classroom', label: 'Class', field: 'class', sortable: true },
  { name: 'points', label: 'Points', field: 'points', sortable: true },
  { name: 'status', label: 'Status', field: 'status', sortable: true },
  { name: 'attend', label: 'Attendance', field: 'attend', sortable: true, align: 'center' }
])
const filter = ref('')
const loading = ref(false)

// Selection manager: keep lightweight refs here and lazily create manager when needed
const selectedBehaviorId = ref(null)
let selectedIds = ref([])
let applyingBehavior = ref(false)
let _selection = null

function ensureSelection() {
  if (_selection) return _selection
  // createSelectionManager will accept external refs so we can declare them earlier
  _selection = createSelectionManager({
    settingsRef: settings,
    selDateRef: selDate,
    periodCodeRef: period_code,
    loadStudentSummary: loadStudentSummary,
    notify: (opts) => $q.notify(opts),
    applyBehaviorApi: async (studentIds, behaviorId, opts) => {
      return rewardPointService.applyBehaviorToStudents(studentIds, behaviorId, {
        date: opts.date,
        periodCode: opts.periodCode,
        notes: opts.notes,
        classroomId: link.value || null,
        subjectId: selSubject.value || null,
      })
    },
    selectedIdsRef: selectedIds,
    applyingBehaviorRef: applyingBehavior,
  })

  return _selection
}

function toggleSelected(id) {
  ensureSelection()
  return _selection.toggleSelected(id)
}

function clearSelection() {
  ensureSelection()
  return _selection.clearSelection()
}

function markSelectedPresent() {
  ensureSelection()
  return _selection.markSelectedPresent()
}

function markSelectedAbsent() {
  ensureSelection()
  return _selection.markSelectedAbsent()
}

async function applyBehaviorToSelected() {
  if (!selectedIds.value.length || !selectedBehaviorId.value) return
  ensureSelection()
  return _selection.applyBehaviorToSelected(selectedBehaviorId.value)
}

async function fetchDataGet(route, myvar, par = null) {
  try {
    const res = await axios.get(route, { params: par })
    // ensure object exists
    if (!fetchData.value) fetchData.value = {}
    fetchData.value[myvar] = res.data
    return res.data
  } catch (e) {
    console.error('fetchDataGet failed', e)
    return null
  }
}

function normalizeStudents(students) {
  return (students || []).map(s => {
    const parts = (s.name || '').trim().split(/\s+/).filter(p => p.length > 0)
    const firstName = parts[0] || ''
    const lastName = parts.length > 1 ? parts[parts.length - 1] : ''
    const secondName = parts.length > 2 ? parts.slice(1, -1).join(' ') : ''
    return { ...s, firstName, secondName, lastName }
  })
}
// Simple action handlers (stubs) to avoid runtime errors
function handleStudentAction(student) {
  // TODO: open edit dialog or navigate to student page
  console.log('handleStudentAction', student)
}

function awardPoints(student) {
  // TODO: implement award points flow (dialog/form/api)
  console.log('awardPoints', student)
}



const f_l_name = (fullName) => {
  if (!fullName || typeof fullName !== 'string') {
    return { firstName: null, lastName: null };
  }

  // Trim whitespace and split the name into words
  const nameParts = fullName.trim().split(/\s+/);

  if (nameParts.length === 0) {
    return { firstName: null, lastName: null };
  }

  const firstName = nameParts[0];
  let lastName = null;

  // If there's more than one word, the last word is the last name
  if (nameParts.length > 1) {
    lastName = nameParts[nameParts.length - 1];
  } else {
    // If only one word exists, use it as the first name
    // and leave lastName as null (or set it to firstName depending on desired behavior)
  }

  return firstName + " " + lastName;
  // return { firstName, lastName };
};



fetchDataGet('my_classes_with_students', 'my_classes_with_students')


function parseName(fullName) {
  if (!fullName || typeof fullName !== 'string') {
    return { firstName: '', secondName: '', lastName: '' };
  }
  const parts = fullName.trim().split(/\s+/).filter(part => part.length > 0);

  const firstName = parts[0] || '';

  const lastName = parts.length > 1 ? parts[parts.length - 1] : '';

  const secondName = parts.length > 2 ? parts.slice(1, -1).join(' ') : '';

  return {
    firstName,
    secondName,
    lastName,
  };
}
/*
  function useNameParser(fullNameRefOrString) {
    
    // Ensure input is reactive: If a plain string is passed, wrap it in a computed Ref.
    const nameRef = isRef(fullNameRefOrString) 
        ? fullNameRefOrString 
        : computed(() => fullNameRefOrString || '');

    // Split the full name into an array of words
    const nameParts = computed(() => {
        if (!nameRef.value) return [];
        // Split by whitespace and filter out any empty strings
        return nameRef.value.trim().split(/\s+/).filter(part => part.length > 0);
    });

    // 1. First Name: The very first word.
    const firstName = computed(() => {
        return nameParts.value[0] || '';
    });

    // 2. Last Name (Surname): The very last word (if more than one word exists).
    const lastName = computed(() => {
        const parts = nameParts.value;
        if (parts.length > 1) {
            return parts[parts.length - 1];
        }
        return '';
    });

    // 3. Second Name (Middle Name/Initial): This is the FIX.
    // It returns the word at index [1] only. If that index doesn't exist, it returns an empty string.
    const secondName = computed(() => {
        return nameParts.value[1] || '';
    });

    // Return the full object with all reactive name parts.
    return {
        firstName,
        secondName,
        lastName, // This is the surname
        fullName: nameRef,
    };
}
*/

/*
  function useNameParserold(fullNameRefOrString) {
  // Ensure we are working with a reactive Ref, even if a plain string is passed
  const nameRef = isRef(fullNameRefOrString) ? fullNameRefOrString : computed(() => fullNameRefOrString);

  // Split the full name into an array of words
  const nameParts = computed(() => {
    if (!nameRef.value) return [];
    // Split by whitespace and filter out empty strings
    return nameRef.value.trim().split(/\s+/).filter(part => part.length > 0);
  });

  // 1. First Name
  const firstName = computed(() => {
    return nameParts.value[0] || '';
  });

  // 2. Last Name (Surname)
  const lastName = computed(() => {
    const parts = nameParts.value;
    // If there's more than one word, the last word is the last name/surname.
    if (parts.length > 1) {
      return parts[parts.length - 1];
    }
    return '';
  });

  // 3. Second Name (Middle Name/Initials)
  const secondName = computed(() => {
    const parts = nameParts.value;
    // Middle parts are everything between the first and the last name.
    if (parts.length > 2) {
      // Slice from index 1 up to (but not including) the last element.
      return parts.slice(1, parts.length - 1).join(' ');
    }
    return '';
  });

//     return  
//   [   firstName,
//     secondName,
//     lastName, // This is the surname
//       nameRef ];


  // Return the full object with all name parts and the original name
  return {
    firstName,
    secondName,
    lastName, // This is the surname
    fullName: nameRef,
  };
}
*/


const behaviors = ref([])

// Settings state management
const settings = ref({
  avatarEditingEnabled: false,
  readingAloudEnabled: true, // Controls reading aloud (TTS)
  attendance: {},
  currentClassroomId: null
})

// Settings persistence functions with enhanced error handling
const saveSettings = () => {
  try {
    console.log('Starting settings save operation...')

    // Check if localStorage is available
    if (typeof Storage === 'undefined' || !window.localStorage) {
      console.warn('localStorage is not available')
      $q.notify({
        message: 'Settings cannot be saved - browser storage unavailable',
        color: 'warning',
        position: 'top',
        timeout: 3000,
        icon: 'warning'
      })
      return useSessionStorageFallback()
    }

    // Validate settings before saving with detailed error reporting
    let validatedSettings
    try {
      validatedSettings = validateSettingsData(settings.value)
      if (!validatedSettings) {
        throw new Error('Settings validation returned null/undefined')
      }
    } catch (validationError) {
      console.error('Settings validation failed:', validationError)
      throw new Error('Settings validation failed: ' + validationError.message)
    }

    const settingsToSave = {
      ...validatedSettings,
      // Include metadata for debugging and migration
      lastUpdated: new Date().toISOString(),
      version: '1.1',
      userAgent: navigator.userAgent.substring(0, 100), // Truncated for size
      classroomId: validatedSettings.currentClassroomId,
      attendanceCount: Object.keys(validatedSettings.attendance || {}).length
    }

    // Test localStorage availability by attempting to write
    try {
      const testKey = 'reward-system-settings-test-' + Date.now()
      localStorage.setItem(testKey, 'test')
      localStorage.removeItem(testKey)
    } catch (testError) {
      if (testError.name === 'QuotaExceededError') {
        throw new Error('localStorage quota exceeded during test write')
      } else {
        throw new Error('localStorage write test failed: ' + testError.message)
      }
    }

    // Serialize settings with error handling
    let serializedSettings
    try {
      serializedSettings = JSON.stringify(settingsToSave)
    } catch (serializationError) {
      console.error('Settings serialization failed:', serializationError)
      throw new Error('Failed to serialize settings: ' + serializationError.message)
    }

    // Check if serialized data is too large (most browsers have ~5-10MB limit)
    const sizeLimit = 5000000 // 5MB limit
    if (serializedSettings.length > sizeLimit) {
      console.warn(`Settings data too large: ${serializedSettings.length} bytes (limit: ${sizeLimit})`)

      // Try compression first
      try {
        return compressSettingsData()
      } catch (compressionError) {
        throw new Error(`Settings data too large for localStorage: ${serializedSettings.length} bytes`)
      }
    }

    // Create backup before saving
    let existingSettings = null
    try {
      existingSettings = localStorage.getItem('reward-system-settings')
    } catch (backupError) {
      console.warn('Could not create backup of existing settings:', backupError)
    }

    // Save the actual settings
    try {
      localStorage.setItem('reward-system-settings', serializedSettings)
    } catch (saveError) {
      // Restore backup if save failed
      if (existingSettings) {
        try {
          localStorage.setItem('reward-system-settings', existingSettings)
        } catch (restoreError) {
          console.error('Failed to restore backup settings:', restoreError)
        }
      }
      throw saveError
    }

    console.log('Settings saved successfully:', {
      size: serializedSettings.length,
      attendanceEntries: Object.keys(validatedSettings.attendance || {}).length,
      avatarEditingEnabled: validatedSettings.avatarEditingEnabled,
      currentClassroomId: validatedSettings.currentClassroomId
    })

    return true

  } catch (error) {
    console.error('Failed to save settings to localStorage:', error)

    // Provide specific error messages and recovery actions based on error type
    let errorMessage = 'Failed to save settings'
    let recoveryActions = []

    if (error.name === 'QuotaExceededError' || error.message.includes('quota exceeded')) {
      errorMessage = 'Storage quota exceeded - cannot save settings'
      recoveryActions = [
        {
          label: 'Clear Old Data',
          color: 'white',
          handler: () => handleStorageQuotaExceeded()
        },
        {
          label: 'Compress Data',
          color: 'white',
          handler: () => compressSettingsData()
        }
      ]
    } else if (error.message.includes('localStorage write test failed') || error.message.includes('disabled')) {
      errorMessage = 'Browser storage is disabled or unavailable'
      recoveryActions = [
        {
          label: 'Use Session Storage',
          color: 'white',
          handler: () => useSessionStorageFallback()
        },
        {
          label: 'Use Memory Storage',
          color: 'white',
          handler: () => useMemoryFallback()
        }
      ]
    } else if (error.message.includes('too large')) {
      errorMessage = 'Settings data is too large to save'
      recoveryActions = [
        {
          label: 'Compress Data',
          color: 'white',
          handler: () => compressSettingsData()
        },
        {
          label: 'Sanitize Data',
          color: 'white',
          handler: () => sanitizeAndRetrySettings()
        }
      ]
    } else if (error.message.includes('validation failed') || error.message.includes('serialize')) {
      errorMessage = 'Settings data is corrupted'
      recoveryActions = [
        {
          label: 'Sanitize & Retry',
          color: 'white',
          handler: () => sanitizeAndRetrySettings()
        },
        {
          label: 'Reset to Defaults',
          color: 'white',
          handler: () => {
            initializeDefaultSettings()
            saveSettings()
          }
        }
      ]
    }

    // Always include retry option
    recoveryActions.unshift({
      label: 'Retry',
      color: 'white',
      handler: () => {
        setTimeout(() => saveSettings(), 1000)
      }
    })

    $q.notify({
      message: errorMessage,
      caption: error.message,
      color: 'negative',
      position: 'top',
      timeout: 6000,
      icon: 'error',
      actions: recoveryActions
    })

    // Report error for debugging
    handleSettingsDialogError(error, 'save')

    return false
  }
}

// Fallback functions for localStorage errors
const useSessionStorageFallback = () => {
  try {
    if (typeof sessionStorage !== 'undefined' && sessionStorage) {
      const validatedSettings = validateSettingsData(settings.value)
      const settingsToSave = {
        ...validatedSettings,
        lastUpdated: new Date().toISOString(),
        version: '1.0',
        fallbackMode: 'sessionStorage'
      }

      sessionStorage.setItem('reward-system-settings-fallback', JSON.stringify(settingsToSave))

      $q.notify({
        message: 'Settings saved to session storage (temporary)',
        color: 'warning',
        position: 'top',
        timeout: 3000,
        icon: 'warning'
      })

      console.log('Settings saved to sessionStorage as fallback')
      return true
    } else {
      throw new Error('sessionStorage not available')
    }
  } catch (error) {
    console.error('sessionStorage fallback failed:', error)
    useMemoryFallback()
    return false
  }
}

const useMemoryFallback = () => {
  try {
    // Store settings in memory (will be lost on page refresh)
    window.rewardSystemSettingsMemory = {
      ...validateSettingsData(settings.value),
      lastUpdated: new Date().toISOString(),
      version: '1.0',
      fallbackMode: 'memory'
    }

    $q.notify({
      message: 'Settings saved in memory only (will be lost on refresh)',
      color: 'warning',
      position: 'top',
      timeout: 4000,
      icon: 'warning'
    })

    console.log('Settings saved to memory as fallback')
    return true
  } catch (error) {
    console.error('Memory fallback failed:', error)
    $q.notify({
      message: 'Unable to save settings - all storage methods failed',
      color: 'negative',
      position: 'top',
      timeout: 5000,
      icon: 'error'
    })
    return false
  }
}

const compressSettingsData = () => {
  try {
    // Create a compressed version by removing non-essential data
    const compressedSettings = {
      avatarEditingEnabled: settings.value.avatarEditingEnabled,
      attendance: settings.value.attendance,
      currentClassroomId: settings.value.currentClassroomId
    }

    // Additional compression: remove false attendance entries (default is true)
    const compressedAttendance = {}
    Object.entries(compressedSettings.attendance || {}).forEach(([studentId, isPresent]) => {
      if (isPresent === false) {
        compressedAttendance[studentId] = false
      }
      // Skip true values to save space (default is present)
    })
    compressedSettings.attendance = compressedAttendance

    const serialized = JSON.stringify(compressedSettings)

    if (serialized.length > 5000000) {
      throw new Error('Even compressed data is too large')
    }

    // Test write before committing
    try {
      localStorage.setItem('reward-system-settings-test', 'test')
      localStorage.removeItem('reward-system-settings-test')
    } catch (testError) {
      throw new Error('localStorage write test failed after compression: ' + testError.message)
    }

    localStorage.setItem('reward-system-settings', serialized)

    $q.notify({
      message: 'Settings saved in compressed format',
      color: 'info',
      position: 'top',
      timeout: 3000,
      icon: 'compress'
    })

    console.log('Settings saved in compressed format:', {
      originalSize: JSON.stringify(settings.value).length,
      compressedSize: serialized.length,
      compressionRatio: Math.round((1 - serialized.length / JSON.stringify(settings.value).length) * 100)
    })
    return true
  } catch (error) {
    console.error('Compression fallback failed:', error)

    $q.notify({
      message: 'Compression failed, trying alternative storage',
      color: 'warning',
      position: 'top',
      timeout: 3000,
      icon: 'warning'
    })

    return useSessionStorageFallback()
  }
}

const sanitizeAndRetrySettings = () => {
  try {
    console.log('Starting settings sanitization process...')

    // Create backup before sanitization
    const backupSettings = JSON.parse(JSON.stringify(settings.value))

    // Create a sanitized version of settings with only safe values
    const sanitizedSettings = {
      avatarEditingEnabled: Boolean(settings.value.avatarEditingEnabled),
      attendance: {},
      currentClassroomId: settings.value.currentClassroomId ? String(settings.value.currentClassroomId) : null
    }

    // Sanitize attendance data with validation
    let sanitizedCount = 0
    let removedCount = 0

    if (settings.value.attendance && typeof settings.value.attendance === 'object') {
      Object.entries(settings.value.attendance).forEach(([key, value]) => {
        // Validate key (should be numeric or numeric string)
        const numericKey = parseInt(key)
        if (!isNaN(numericKey) && numericKey > 0) {
          // Validate value (should be boolean)
          if (typeof value === 'boolean') {
            sanitizedSettings.attendance[String(key)] = value
            sanitizedCount++
          } else if (value === 'true' || value === 'false') {
            // Convert string booleans
            sanitizedSettings.attendance[String(key)] = value === 'true'
            sanitizedCount++
          } else if (value === 1 || value === 0) {
            // Convert numeric booleans
            sanitizedSettings.attendance[String(key)] = Boolean(value)
            sanitizedCount++
          } else {
            console.warn(`Removed invalid attendance value for student ${key}:`, value)
            removedCount++
          }
        } else {
          console.warn(`Removed invalid student ID from attendance:`, key)
          removedCount++
        }
      })
    }

    // Validate against current classroom roster if available
    if (fetchData.value.my_sel_classroom_students?.length) {
      const validStudentIds = new Set(
        fetchData.value.my_sel_classroom_students
          .filter(s => s && s.id)
          .map(s => s.id.toString())
      )

      const attendanceKeys = Object.keys(sanitizedSettings.attendance)
      attendanceKeys.forEach(studentId => {
        if (!validStudentIds.has(studentId)) {
          delete sanitizedSettings.attendance[studentId]
          removedCount++
          console.warn(`Removed attendance for student not in current classroom: ${studentId}`)
        }
      })
    }

    // Test serialization
    const serialized = JSON.stringify(sanitizedSettings)

    // Check size limits
    if (serialized.length > 5000000) {
      throw new Error('Sanitized data still too large for localStorage')
    }

    // Test localStorage write
    try {
      localStorage.setItem('reward-system-settings-test', 'test')
      localStorage.removeItem('reward-system-settings-test')
    } catch (testError) {
      throw new Error('localStorage write test failed after sanitization: ' + testError.message)
    }

    // Save sanitized settings
    localStorage.setItem('reward-system-settings', serialized)

    // Update the current settings with sanitized version
    Object.assign(settings.value, sanitizedSettings)

    const message = `Settings sanitized: ${sanitizedCount} entries kept, ${removedCount} invalid entries removed`

    $q.notify({
      message: message,
      color: 'positive',
      position: 'top',
      timeout: 4000,
      icon: 'cleaning_services'
    })

    console.log('Settings sanitization completed:', {
      sanitizedCount,
      removedCount,
      sanitizedSettings,
      originalSize: JSON.stringify(backupSettings).length,
      sanitizedSize: serialized.length
    })

    return true

  } catch (error) {
    console.error('Sanitization failed:', error)

    $q.notify({
      message: `Sanitization failed: ${error.message}`,
      color: 'negative',
      position: 'top',
      timeout: 4000,
      icon: 'error'
    })

    // Try session storage as fallback
    return useSessionStorageFallback()
  }
}

const loadSettings = () => {
  try {
    // Check if localStorage is available
    if (typeof Storage === 'undefined' || !window.localStorage) {
      console.warn('localStorage is not available, using default settings')
      initializeDefaultSettings()
      return false
    }

    const saved = localStorage.getItem('reward-system-settings')
    if (!saved) {
      console.log('No saved settings found, using defaults')
      initializeDefaultSettings()
      return true
    }

    // Parse and validate the saved settings
    let parsedSettings
    try {
      parsedSettings = JSON.parse(saved)
    } catch (parseError) {
      throw new Error('Invalid JSON in saved settings: ' + parseError.message)
    }

    // Validate the structure of parsed settings
    if (!parsedSettings || typeof parsedSettings !== 'object') {
      throw new Error('Invalid settings data structure')
    }

    // Remove metadata before applying settings
    const { lastUpdated, version, ...settingsData } = parsedSettings

    // Validate and sanitize the settings data
    const validatedSettings = validateSettingsData(settingsData)

    // Apply validated settings
    Object.assign(settings.value, validatedSettings)
    console.log('Settings loaded successfully:', validatedSettings)

    // Log version info for debugging
    if (version) {
      console.log('Loaded settings version:', version)
    }

    return true

  } catch (error) {
    console.error('Failed to load settings from localStorage:', error)

    // Provide specific error messages
    let errorMessage = 'Settings reset to defaults due to loading error'
    if (error.message.includes('Invalid JSON')) {
      errorMessage = 'Settings file corrupted - reset to defaults'
    } else if (error.message.includes('Invalid settings data')) {
      errorMessage = 'Invalid settings format - reset to defaults'
    }

    // Try fallback loading methods before resetting to defaults
    const fallbackLoaded = tryFallbackLoading()

    if (!fallbackLoaded) {
      // Reset to default settings only if all fallbacks fail
      initializeDefaultSettings()

      $q.notify({
        message: errorMessage,
        color: 'info',
        position: 'top',
        timeout: 3000,
        icon: 'refresh',
        actions: [
          {
            label: 'OK',
            color: 'white',
            handler: () => { }
          }
        ]
      })
    }

    return fallbackLoaded
  }
}

// Fallback loading function to try alternative storage methods
const tryFallbackLoading = () => {
  try {
    // Try sessionStorage fallback first
    if (typeof sessionStorage !== 'undefined' && sessionStorage) {
      const sessionData = sessionStorage.getItem('reward-system-settings-fallback')
      if (sessionData) {
        try {
          const parsedData = JSON.parse(sessionData)
          const { lastUpdated, version, fallbackMode, ...settingsData } = parsedData
          const validatedSettings = validateSettingsData(settingsData)
          Object.assign(settings.value, validatedSettings)

          $q.notify({
            message: 'Settings loaded from session storage backup',
            color: 'warning',
            position: 'top',
            timeout: 3000,
            icon: 'backup'
          })

          console.log('Settings loaded from sessionStorage fallback')
          return true
        } catch (sessionError) {
          console.warn('sessionStorage fallback data corrupted:', sessionError)
        }
      }
    }

    // Try memory fallback
    if (window.rewardSystemSettingsMemory) {
      try {
        const { lastUpdated, version, fallbackMode, ...settingsData } = window.rewardSystemSettingsMemory
        const validatedSettings = validateSettingsData(settingsData)
        Object.assign(settings.value, validatedSettings)

        $q.notify({
          message: 'Settings loaded from memory backup',
          color: 'warning',
          position: 'top',
          timeout: 3000,
          icon: 'memory'
        })

        console.log('Settings loaded from memory fallback')
        return true
      } catch (memoryError) {
        console.warn('Memory fallback data corrupted:', memoryError)
      }
    }

    return false
  } catch (error) {
    console.error('All fallback loading methods failed:', error)
    return false
  }
}

// Helper function to validate settings data structure and values
const validateSettingsData = (data) => {
  const defaultSettings = {
    avatarEditingEnabled: false,
    attendance: {},
    currentClassroomId: null
  }

  if (!data || typeof data !== 'object') {
    console.warn('Invalid settings data, using defaults')
    return defaultSettings
  }

  const validated = { ...defaultSettings }

  // Validate avatarEditingEnabled
  if (typeof data.avatarEditingEnabled === 'boolean') {
    validated.avatarEditingEnabled = data.avatarEditingEnabled
  } else {
    console.warn('Invalid avatarEditingEnabled value, using default:', data.avatarEditingEnabled)
  }

  // Validate attendance object
  if (data.attendance && typeof data.attendance === 'object' && !Array.isArray(data.attendance)) {
    // Validate each attendance entry
    const validatedAttendance = {}
    Object.entries(data.attendance).forEach(([studentId, isPresent]) => {
      // Validate student ID (should be numeric or numeric string)
      const numericId = parseInt(studentId)
      if (!isNaN(numericId) && numericId > 0) {
        // Validate presence value (should be boolean)
        if (typeof isPresent === 'boolean') {
          validatedAttendance[studentId] = isPresent
        } else {
          console.warn(`Invalid attendance value for student ${studentId}:`, isPresent)
          validatedAttendance[studentId] = true // Default to present
        }
      } else {
        console.warn('Invalid student ID in attendance:', studentId)
      }
    })
    validated.attendance = validatedAttendance
  } else {
    console.warn('Invalid attendance data, using empty object:', data.attendance)
  }

  // Validate currentClassroomId
  if (data.currentClassroomId === null ||
    (typeof data.currentClassroomId === 'string' && data.currentClassroomId.length > 0) ||
    (typeof data.currentClassroomId === 'number' && data.currentClassroomId > 0)) {
    validated.currentClassroomId = data.currentClassroomId
  } else {
    console.warn('Invalid currentClassroomId, using null:', data.currentClassroomId)
  }

  return validated
}

// Helper function to initialize default settings
const initializeDefaultSettings = () => {
  settings.value = {
    avatarEditingEnabled: false,
    attendance: {},
    currentClassroomId: null
  }
  console.log('Initialized default settings')
}

// Function to validate student IDs against current classroom roster
const validateStudentIds = () => {
  try {
    if (!fetchData.value.my_sel_classroom_students?.length) {
      console.log('No students in current classroom to validate against')
      return true
    }

    // Validate that all students have valid IDs
    const studentsWithInvalidIds = fetchData.value.my_sel_classroom_students.filter(
      student => !student.id || (typeof student.id !== 'string' && typeof student.id !== 'number')
    )

    if (studentsWithInvalidIds.length > 0) {
      console.error('Found students with invalid IDs:', studentsWithInvalidIds)
      $q.notify({
        message: `Warning: ${studentsWithInvalidIds.length} students have invalid IDs`,
        color: 'warning',
        position: 'top',
        timeout: 4000,
        icon: 'warning'
      })
    }

    // Create set of valid current student IDs
    const currentStudentIds = new Set()
    fetchData.value.my_sel_classroom_students.forEach(student => {
      if (student.id) {
        currentStudentIds.add(student.id.toString())
      }
    })

    if (currentStudentIds.size === 0) {
      console.warn('No valid student IDs found in current classroom')
      return true
    }

    // Validate attendance data structure
    if (!settings.value.attendance || typeof settings.value.attendance !== 'object') {
      console.warn('Invalid attendance data structure, resetting')
      settings.value.attendance = {}
      return true
    }

    const attendanceStudentIds = Object.keys(settings.value.attendance)
    const invalidIds = attendanceStudentIds.filter(id => {
      // Check if ID exists in current classroom
      if (!currentStudentIds.has(id)) {
        return true
      }

      // Check if attendance value is valid
      const attendanceValue = settings.value.attendance[id]
      if (typeof attendanceValue !== 'boolean') {
        console.warn(`Invalid attendance value for student ${id}:`, attendanceValue)
        return true
      }

      return false
    })

    if (invalidIds.length > 0) {
      console.warn('Found invalid student IDs or values in attendance:', invalidIds)

      // Create backup before cleaning
      const backupAttendance = { ...settings.value.attendance }

      try {
        // Remove invalid student IDs from attendance
        const cleanedAttendance = { ...settings.value.attendance }
        let removedCount = 0
        let fixedCount = 0

        invalidIds.forEach(id => {
          const attendanceValue = settings.value.attendance[id]

          if (!currentStudentIds.has(id)) {
            // Remove completely invalid IDs
            delete cleanedAttendance[id]
            removedCount++
          } else if (typeof attendanceValue !== 'boolean') {
            // Fix invalid values by defaulting to present
            cleanedAttendance[id] = true
            fixedCount++
          }
        })

        settings.value.attendance = cleanedAttendance

        let message = ''
        if (removedCount > 0 && fixedCount > 0) {
          message = `Removed ${removedCount} invalid student records and fixed ${fixedCount} invalid values`
        } else if (removedCount > 0) {
          message = `Removed ${removedCount} invalid student records from attendance`
        } else if (fixedCount > 0) {
          message = `Fixed ${fixedCount} invalid attendance values`
        }

        $q.notify({
          message: message,
          color: 'info',
          position: 'top',
          timeout: 3000,
          icon: 'cleaning_services'
        })

        console.log('Attendance validation completed:', {
          removed: removedCount,
          fixed: fixedCount,
          cleanedAttendance
        })

        return false
      } catch (cleanupError) {
        console.error('Error during attendance cleanup:', cleanupError)

        // Restore backup on error
        settings.value.attendance = backupAttendance

        $q.notify({
          message: 'Failed to clean invalid attendance data',
          color: 'negative',
          position: 'top',
          timeout: 3000,
          icon: 'error'
        })

        return false
      }
    }

    console.log('All student IDs in attendance are valid')
    return true

  } catch (error) {
    console.error('Error during student ID validation:', error)

    $q.notify({
      message: 'Error validating student data - some features may not work correctly',
      color: 'negative',
      position: 'top',
      timeout: 4000,
      icon: 'error'
    })

    return false
  }
}

// Function to handle localStorage quota exceeded errors
const handleStorageQuotaExceeded = () => {
  try {
    // Try to clear old or unnecessary data
    const keysToCheck = []
    for (let i = 0; i < localStorage.length; i++) {
      const key = localStorage.key(i)
      if (key && key.startsWith('reward-system-')) {
        keysToCheck.push(key)
      }
    }

    // Remove old backup settings if they exist
    keysToCheck.forEach(key => {
      if (key.includes('backup') || key.includes('old')) {
        localStorage.removeItem(key)
        console.log('Removed old settings:', key)
      }
    })

    // Try saving again
    return saveSettings()

  } catch (error) {
    console.error('Failed to handle storage quota exceeded:', error)
    $q.notify({
      message: 'Storage full - settings cannot be saved. Please clear browser data.',
      color: 'negative',
      position: 'top',
      timeout: 5000,
      icon: 'storage'
    })
    return false
  }
}

// Enhanced classroom selection handler
const handleClassroomSelection = (classroomId, students) => {
  console.log(`Handling classroom selection: ${classroomId}`)

  // Update the link (this will trigger the watcher)
  link.value = classroomId

  // Update selected students
  fetchData.value.my_sel_classroom_students = normalizeStudents(students || [])

  // The watchers will handle the rest of the logic
}

// Test method for settings behavior with multiple classroom selections
const testClassroomSwitching = () => {
  console.log('Testing classroom switching behavior...')

  if (!fetchData.value.my_classes_with_students?.length) {
    console.warn('No classrooms available for testing')
    return
  }

  const classrooms = fetchData.value.my_classes_with_students
  let currentIndex = 0

  const switchClassroom = () => {
    if (currentIndex < classrooms.length) {
      const classroom = classrooms[currentIndex]
      console.log(`Switching to classroom ${currentIndex + 1}: ${classroom.classroom_name}`)

      // Log current settings state
      console.log('Current settings before switch:', JSON.parse(JSON.stringify(settings.value)))

      // Switch classroom
      handleClassroomSelection(classroom.id, classroom.students)

      // Log settings state after switch
      setTimeout(() => {
        console.log('Settings after switch:', JSON.parse(JSON.stringify(settings.value)))
        console.log('LocalStorage content:', localStorage.getItem('reward-system-settings'))

        currentIndex++
        if (currentIndex < classrooms.length) {
          setTimeout(switchClassroom, 2000) // Wait 2 seconds before next switch
        } else {
          console.log('Classroom switching test completed')
        }
      }, 100)
    }
  }

  switchClassroom()
}

// Comprehensive error handling test function
const testErrorHandling = () => {
  console.log('Starting comprehensive error handling tests...')

  const tests = [
    {
      name: 'localStorage Unavailable Test',
      test: () => {
        // Temporarily disable localStorage
        const originalLocalStorage = window.localStorage
        Object.defineProperty(window, 'localStorage', {
          value: null,
          writable: true
        })

        const result = saveSettings()

        // Restore localStorage
        Object.defineProperty(window, 'localStorage', {
          value: originalLocalStorage,
          writable: true
        })

        return !result // Should return false when localStorage unavailable
      }
    },
    {
      name: 'Invalid Settings Data Test',
      test: () => {
        const originalSettings = { ...settings.value }

        // Corrupt settings
        settings.value = {
          avatarEditingEnabled: 'invalid',
          attendance: 'not an object',
          currentClassroomId: []
        }

        const validatedSettings = validateSettingsData(settings.value)

        // Restore settings
        settings.value = originalSettings

        return validatedSettings &&
          typeof validatedSettings.avatarEditingEnabled === 'boolean' &&
          typeof validatedSettings.attendance === 'object' &&
          !Array.isArray(validatedSettings.attendance)
      }
    },
    {
      name: 'Student ID Validation Test',
      test: () => {
        const originalAttendance = { ...settings.value.attendance }

        // Add invalid student IDs
        settings.value.attendance = {
          'valid_id_1': true,
          'invalid_id_abc': false,
          '': true,
          'null': null,
          '999999': 'not_boolean'
        }

        const result = validateStudentIds()

        // Restore attendance
        settings.value.attendance = originalAttendance

        return typeof result === 'boolean'
      }
    },
    {
      name: 'Settings Dialog Error Boundary Test',
      test: () => {
        try {
          // Test with corrupted fetchData
          const originalFetchData = fetchData.value
          fetchData.value = null

          safeOpenSettingsDialog()

          // Restore fetchData
          fetchData.value = originalFetchData

          return true // Should not throw error
        } catch (error) {
          return false
        }
      }
    },
    {
      name: 'Compression Fallback Test',
      test: () => {
        // Create large settings data
        const originalAttendance = { ...settings.value.attendance }
        const largeAttendance = {}

        // Create attendance data that's too large
        for (let i = 0; i < 10000; i++) {
          largeAttendance[`student_${i}`] = Math.random() > 0.5
        }

        settings.value.attendance = largeAttendance

        const result = compressSettingsData()

        // Restore attendance
        settings.value.attendance = originalAttendance

        return typeof result === 'boolean'
      }
    },
    {
      name: 'Sanitization Test',
      test: () => {
        const originalSettings = { ...settings.value }

        // Add corrupted data
        settings.value.attendance = {
          '123': true,
          'invalid': 'not_boolean',
          '456': false,
          '': true,
          'null': null
        }

        const result = sanitizeAndRetrySettings()

        // Restore settings
        settings.value = originalSettings

        return typeof result === 'boolean'
      }
    }
  ]

  const results = []

  tests.forEach(({ name, test }) => {
    try {
      const startTime = Date.now()
      const result = test()
      const duration = Date.now() - startTime

      results.push({
        name,
        passed: result,
        duration,
        error: null
      })

      console.log(`✅ ${name}: ${result ? 'PASSED' : 'FAILED'} (${duration}ms)`)
    } catch (error) {
      results.push({
        name,
        passed: false,
        duration: 0,
        error: error.message
      })

      console.error(`❌ ${name}: ERROR - ${error.message}`)
    }
  })

  const passedTests = results.filter(r => r.passed).length
  const totalTests = results.length

  console.log(`\n📊 Error Handling Test Results: ${passedTests}/${totalTests} tests passed`)

  if (passedTests === totalTests) {
    $q.notify({
      message: `All ${totalTests} error handling tests passed!`,
      color: 'positive',
      position: 'top',
      timeout: 3000,
      icon: 'check_circle'
    })
  } else {
    $q.notify({
      message: `${passedTests}/${totalTests} error handling tests passed`,
      color: 'warning',
      position: 'top',
      timeout: 4000,
      icon: 'warning'
    })
  }

  return results
}

// Make test methods available globally for debugging
if (typeof window !== 'undefined') {
  window.testClassroomSwitching = testClassroomSwitching
  window.testErrorHandling = testErrorHandling
  window.testSettingsValidation = () => validateStudentIds()
  window.testSettingsCompression = () => compressSettingsData()
  window.testSettingsSanitization = () => sanitizeAndRetrySettings()
}

// Watch for settings changes and save to localStorage with error handling
watch(settings, () => {
  try {
    // Debounce the save operation to avoid excessive writes
    if (saveSettingsTimeout) {
      clearTimeout(saveSettingsTimeout)
    }

    saveSettingsTimeout = setTimeout(() => {
      saveSettings()
    }, 500) // Wait 500ms after last change before saving

  } catch (error) {
    console.error('Error in settings watcher:', error)
    $q.notify({
      message: 'Error monitoring settings changes',
      color: 'warning',
      position: 'top',
      timeout: 2000,
      icon: 'warning'
    })
  }
}, { deep: true })

// Watch for classroom changes to reset attendance with error handling
// watch(link, (newClassroomId, oldClassroomId) => {
//   try {
//     // Only process if classroom actually changed
//     if (newClassroomId !== oldClassroomId && newClassroomId !== settings.value.currentClassroomId) {
//       console.log(`Classroom changed from ${oldClassroomId} to ${newClassroomId}`)

//       // Validate the new classroom ID
//       if (newClassroomId !== null &&
//         typeof newClassroomId !== 'string' &&
//         typeof newClassroomId !== 'number') {
//         throw new Error(`Invalid classroom ID: ${newClassroomId}`)
//       }

//       // Update current classroom ID in settings
//       settings.value.currentClassroomId = newClassroomId

//       // Reset attendance to all present for new classroom
//       settings.value.attendance = {}

//       // If we have students in the new classroom, initialize their attendance as present
//       if (fetchData.value.my_sel_classroom_students?.length) {
//         const newAttendance = {}
//         let validStudentCount = 0

//         fetchData.value.my_sel_classroom_students.forEach(student => {
//           if (student && student.id) {
//             newAttendance[student.id] = true // Default to present
//             validStudentCount++
//           } else {
//             console.warn('Invalid student data found:', student)
//           }
//         })

//         settings.value.attendance = newAttendance
//         console.log(`Initialized attendance for ${validStudentCount} students`)
//       }

//       // Notify user about attendance reset
//       if (newClassroomId && oldClassroomId) {
//         $q.notify({
//           message: 'Attendance reset for new classroom - all students marked present',
//           color: 'info',
//           position: 'top',
//           timeout: 3000,
//           icon: 'people'
//         })
//       }
//     }

//   } catch (error) {
//     console.error('Error handling classroom change:', error)
//     $q.notify({
//       message: `Error switching classroom: ${error.message}`,
//       color: 'negative',
//       position: 'top',
//       timeout: 3000,
//       icon: 'error'
//     })

//     // Try to recover by initializing default settings
//     try {
//       initializeDefaultSettings()
//       settings.value.currentClassroomId = newClassroomId
//     } catch (recoveryError) {
//       console.error('Failed to recover from classroom change error:', recoveryError)
//     }
//   }
// })

// Watch for changes in selected classroom students to update attendance state with error handling
watch(() => fetchData.value.my_sel_classroom_students, (newStudents, oldStudents) => {
  try {
    if (!newStudents || !Array.isArray(newStudents)) {
      console.log('No valid students array provided')
      return
    }

    if (newStudents.length > 0) {
      // Initialize attendance for any new students that don't have attendance set
      const currentAttendance = { ...settings.value.attendance }
      let hasChanges = false
      let addedCount = 0
      let removedCount = 0

      // Add new students
      newStudents.forEach(student => {
        if (!student || !student.id) {
          console.warn('Invalid student data:', student)
          return
        }

        if (!(student.id in currentAttendance)) {
          currentAttendance[student.id] = true // Default new students to present
          hasChanges = true
          addedCount++
        }
      })

      // Clean up attendance for students no longer in classroom
      if (oldStudents && Array.isArray(oldStudents) && oldStudents.length > 0) {
        const newStudentIds = new Set(
          newStudents
            .filter(s => s && s.id)
            .map(s => s.id.toString())
        )

        Object.keys(currentAttendance).forEach(studentId => {
          if (!newStudentIds.has(studentId.toString())) {
            delete currentAttendance[studentId]
            hasChanges = true
            removedCount++
          }
        })
      }

      if (hasChanges) {
        settings.value.attendance = currentAttendance
        console.log(`Updated attendance: +${addedCount} students, -${removedCount} students`)

        if (addedCount > 0 || removedCount > 0) {
          $q.notify({
            message: `Attendance updated: ${addedCount} added, ${removedCount} removed`,
            color: 'info',
            position: 'top',
            timeout: 2000,
            icon: 'update'
          })
        }
      }
    } else {
      // No students in classroom, clear attendance
      if (Object.keys(settings.value.attendance).length > 0) {
        settings.value.attendance = {}
        console.log('Cleared attendance - no students in classroom')
      }
    }

  } catch (error) {
    console.error('Error updating attendance for student changes:', error)
    $q.notify({
      message: `Error updating attendance: ${error.message}`,
      color: 'warning',
      position: 'top',
      timeout: 3000,
      icon: 'warning'
    })

    // Try to recover by validating current attendance
    try {
      validateStudentIds()
    } catch (validationError) {
      console.error('Failed to validate student IDs during recovery:', validationError)
    }
  }
}, { deep: true })

// Add timeout variable for debouncing saves
let saveSettingsTimeout = null

// Computed properties for settings
const isStudentPresent = (studentId) => {
  return settings.value.attendance[studentId] !== false
}

// Computed property for student presence checking (reactive)
const studentPresenceMap = computed(() => {
  const presenceMap = {}
  if (fetchData.value.my_sel_classroom_students) {
    fetchData.value.my_sel_classroom_students.forEach(student => {
      presenceMap[student.id] = settings.value.attendance[student.id] !== false
    })
  }
  return presenceMap
})

// Computed property to check if avatar editing is enabled
const shouldShowAvatarButtons = computed(() => {
  return settings.value.avatarEditingEnabled
})

// Computed property for dynamic student card classes
const getStudentCardClass = computed(() => (studentId) => {
  const baseClass = 'student-card cursor-pointer transition-all duration-300'
  const presentClass = 'hover:shadow-xl hover:-translate-y-1'
  const absentClass = 'student-absent opacity-50 grayscale cursor-not-allowed'

  return isStudentPresent(studentId)
    ? `${baseClass} ${presentClass}`
    : `${baseClass} ${absentClass}`
})

// Computed property to check if behavior buttons should be disabled for absent students
const shouldDisableBehaviorButtons = computed(() => (studentId) => {
  return !isStudentPresent(studentId)
})

// Attendance management functions with error handling
const toggleStudentAttendance = (studentId) => {
  try {
    // Validate student ID
    if (!studentId || (typeof studentId !== 'string' && typeof studentId !== 'number')) {
      throw new Error('Invalid student ID provided')
    }

    // Check if student exists in current classroom
    const studentExists = fetchData.value.my_sel_classroom_students?.some(
      student => student.id.toString() === studentId.toString()
    )

    if (!studentExists) {
      throw new Error(`Student ID ${studentId} not found in current classroom`)
    }

    // Toggle attendance
    const currentStatus = isStudentPresent(studentId)
    settings.value.attendance[studentId] = !currentStatus

    // Provide user feedback
    const student = fetchData.value.my_sel_classroom_students.find(
      s => s.id.toString() === studentId.toString()
    )
    const studentName = student?.first_name ? student.first_name : `Student ${studentId}`

    $q.notify({
      message: `${studentName} marked as ${!currentStatus ? 'present' : 'absent'}`,
      color: !currentStatus ? 'positive' : 'warning',
      position: 'top',
      timeout: 2000,
      icon: !currentStatus ? 'person' : 'person_off'
    })

    console.log(`Toggled attendance for student ${studentId}: ${!currentStatus ? 'present' : 'absent'}`)

  } catch (error) {
    console.error('Error toggling student attendance:', error)
    $q.notify({
      message: `Failed to update attendance: ${error.message}`,
      color: 'negative',
      position: 'top',
      timeout: 3000,
      icon: 'error'
    })
  }
}

const markAllPresent = () => {
  try {
    if (!fetchData.value.my_sel_classroom_students?.length) {
      throw new Error('No students found in current classroom')
    }

    let successCount = 0
    const errors = []

    fetchData.value.my_sel_classroom_students.forEach(student => {
      try {
        if (!student.id) {
          throw new Error(`Invalid student data: missing ID`)
        }
        settings.value.attendance[student.id] = true
        successCount++
      } catch (studentError) {
        errors.push(`Student ${student.name || 'Unknown'}: ${studentError.message}`)
      }
    })

    if (errors.length > 0) {
      console.warn('Errors marking students present:', errors)
      $q.notify({
        message: `Marked ${successCount} students present, ${errors.length} errors occurred`,
        color: 'warning',
        position: 'top',
        timeout: 4000,
        icon: 'warning'
      })
    } else {
      $q.notify({
        message: `All ${successCount} students marked present`,
        color: 'positive',
        position: 'top',
        timeout: 2000,
        icon: 'people'
      })
    }

    console.log(`Marked all students present: ${successCount} successful, ${errors.length} errors`)

  } catch (error) {
    console.error('Error marking all students present:', error)
    $q.notify({
      message: `Failed to mark all present: ${error.message}`,
      color: 'negative',
      position: 'top',
      timeout: 3000,
      icon: 'error'
    })
  }
}

const markAllAbsent = () => {
  try {
    if (!fetchData.value.my_sel_classroom_students?.length) {
      throw new Error('No students found in current classroom')
    }

    let successCount = 0
    const errors = []

    fetchData.value.my_sel_classroom_students.forEach(student => {
      try {
        if (!student.id) {
          throw new Error(`Invalid student data: missing ID`)
        }
        settings.value.attendance[student.id] = false
        successCount++
      } catch (studentError) {
        errors.push(`Student ${student.name || 'Unknown'}: ${studentError.message}`)
      }
    })

    if (errors.length > 0) {
      console.warn('Errors marking students absent:', errors)
      $q.notify({
        message: `Marked ${successCount} students absent, ${errors.length} errors occurred`,
        color: 'warning',
        position: 'top',
        timeout: 4000,
        icon: 'warning'
      })
    } else {
      $q.notify({
        message: `All ${successCount} students marked absent`,
        color: 'warning',
        position: 'top',
        timeout: 2000,
        icon: 'person_off'
      })
    }

    console.log(`Marked all students absent: ${successCount} successful, ${errors.length} errors`)

  } catch (error) {
    console.error('Error marking all students absent:', error)
    $q.notify({
      message: `Failed to mark all absent: ${error.message}`,
      color: 'negative',
      position: 'top',
      timeout: 3000,
      icon: 'error'
    })
  }
}

// Settings dialog error boundary functions
const handleSettingsDialogError = (error, operation) => {
  console.error(`Settings dialog error during ${operation}:`, error)

  const errorMessages = {
    'open': 'Failed to open settings dialog',
    'close': 'Failed to close settings dialog',
    'save': 'Failed to save settings',
    'load': 'Failed to load settings',
    'reset': 'Failed to reset settings',
    'validate': 'Settings validation failed',
    'initialize': 'Settings initialization failed'
  }

  // Create error report for debugging
  const errorReport = {
    operation,
    error: error.message,
    stack: error.stack,
    timestamp: new Date().toISOString(),
    settingsState: settings.value ? JSON.stringify(settings.value) : 'null',
    classroomId: link.value,
    studentCount: fetchData.value.my_sel_classroom_students?.length || 0
  }

  console.error('Settings error report:', errorReport)

  // Determine recovery actions based on error type and operation
  const actions = []

  if (operation === 'open') {
    actions.push({
      label: 'Retry',
      color: 'white',
      handler: () => safeOpenSettingsDialog()
    })
    actions.push({
      label: 'Reset & Open',
      color: 'white',
      handler: () => {
        initializeDefaultSettings()
        safeOpenSettingsDialog()
      }
    })
  } else if (operation === 'save') {
    actions.push({
      label: 'Retry',
      color: 'white',
      handler: () => saveSettings()
    })
    actions.push({
      label: 'Compress & Save',
      color: 'white',
      handler: () => compressSettingsData()
    })
    actions.push({
      label: 'Sanitize & Save',
      color: 'white',
      handler: () => sanitizeAndRetrySettings()
    })
  } else if (operation === 'load') {
    actions.push({
      label: 'Retry',
      color: 'white',
      handler: () => loadSettings()
    })
    actions.push({
      label: 'Use Defaults',
      color: 'white',
      handler: () => initializeDefaultSettings()
    })
  } else if (operation === 'validate') {
    actions.push({
      label: 'Fix Data',
      color: 'white',
      handler: () => sanitizeAndRetrySettings()
    })
    actions.push({
      label: 'Reset',
      color: 'white',
      handler: () => initializeDefaultSettings()
    })
  } else {
    actions.push({
      label: 'Retry',
      color: 'white',
      handler: () => {
        if (operation === 'reset') {
          resetSettingsToDefaults()
        }
      }
    })
  }

  $q.notify({
    message: errorMessages[operation] || 'Settings dialog error occurred',
    caption: error.message,
    color: 'negative',
    position: 'top',
    timeout: 5000,
    icon: 'error',
    actions: actions
  })
}

const safeOpenSettingsDialog = () => {
  try {
    console.log('Attempting to open settings dialog...')

    // Pre-flight checks before opening dialog
    if (!fetchData.value) {
      throw new Error('Application data not initialized')
    }

    // Check browser compatibility
    if (typeof Storage === 'undefined') {
      console.warn('Browser storage not available, settings will not persist')
    }

    // Validate current state before opening dialog
    if (!fetchData.value.my_sel_classroom_students) {
      console.warn('No classroom selected, initializing empty student list')
      fetchData.value.my_sel_classroom_students = []
    }

    // Validate settings structure with detailed checks
    if (!settings.value || typeof settings.value !== 'object') {
      console.warn('Settings object corrupted, reinitializing')
      initializeDefaultSettings()
    } else {
      // Check individual setting properties
      if (typeof settings.value.avatarEditingEnabled !== 'boolean') {
        console.warn('avatarEditingEnabled corrupted, resetting to default')
        settings.value.avatarEditingEnabled = true
      }

      if (!settings.value.attendance || typeof settings.value.attendance !== 'object' || Array.isArray(settings.value.attendance)) {
        console.warn('Attendance data corrupted, reinitializing')
        settings.value.attendance = {}
      }

      if (settings.value.currentClassroomId !== null &&
        typeof settings.value.currentClassroomId !== 'string' &&
        typeof settings.value.currentClassroomId !== 'number') {
        console.warn('currentClassroomId corrupted, resetting to null')
        settings.value.currentClassroomId = null
      }
    }

    // Validate student IDs against current roster with error recovery
    try {
      const validationResult = validateStudentIds()
      if (!validationResult) {
        console.warn('Student ID validation returned false, but continuing with dialog open')
      }
    } catch (validationError) {
      console.warn('Student ID validation failed, attempting recovery:', validationError)

      try {
        // Attempt to recover by sanitizing settings
        sanitizeAndRetrySettings()
      } catch (recoveryError) {
        console.error('Recovery failed, using default settings:', recoveryError)
        initializeDefaultSettings()
      }
    }

    // Check if dialog is already open to prevent multiple instances
    if (showSettingsDialog.value) {
      console.log('Settings dialog already open')
      return
    }

    // Final validation before opening
    if (!$q) {
      throw new Error('Quasar instance not available')
    }

    // Open the dialog
    showSettingsDialog.value = true

    console.log('Settings dialog opened successfully')

    // Log current state for debugging
    console.log('Settings dialog state:', {
      settingsValid: !!settings.value,
      studentsCount: fetchData.value.my_sel_classroom_students?.length || 0,
      attendanceEntries: Object.keys(settings.value.attendance || {}).length,
      currentClassroom: settings.value.currentClassroomId
    })

  } catch (error) {
    console.error('Critical error opening settings dialog:', error)
    handleSettingsDialogError(error, 'open')
  }
}

const safeCloseSettingsDialog = () => {
  try {
    // Check if dialog is actually open
    if (!showSettingsDialog.value) {
      console.log('Settings dialog already closed')
      return
    }

    // Validate settings before attempting to save
    try {
      const validatedSettings = validateSettingsData(settings.value)
      if (!validatedSettings) {
        throw new Error('Settings validation failed')
      }
    } catch (validationError) {
      console.error('Settings validation failed during close:', validationError)

      $q.dialog({
        title: 'Invalid Settings',
        message: 'Current settings contain invalid data. Reset to defaults before closing?',
        cancel: true,
        persistent: false,
        ok: {
          label: 'Reset & Close',
          color: 'negative'
        },
        cancel: {
          label: 'Fix Settings',
          color: 'primary'
        }
      }).onOk(() => {
        try {
          initializeDefaultSettings()
          showSettingsDialog.value = false
          console.log('Settings reset and dialog closed')
        } catch (resetError) {
          console.error('Failed to reset settings:', resetError)
          // Force close even if reset fails
          showSettingsDialog.value = false
        }
      }).onCancel(() => {
        console.log('User chose to fix settings instead of closing')
      })
      return
    }

    // Attempt to save settings before closing
    let saveSuccess = false
    try {
      saveSuccess = saveSettings()
    } catch (saveError) {
      console.error('Error saving settings during close:', saveError)
      saveSuccess = false
    }

    if (saveSuccess) {
      showSettingsDialog.value = false
      console.log('Settings dialog closed successfully')
    } else {
      // Ask user if they want to close without saving
      $q.dialog({
        title: 'Settings Not Saved',
        message: 'Settings could not be saved. Close anyway?',
        cancel: true,
        persistent: false,
        ok: {
          label: 'Close Without Saving',
          color: 'negative'
        },
        cancel: {
          label: 'Keep Open',
          color: 'primary'
        }
      }).onOk(() => {
        showSettingsDialog.value = false
        console.log('Settings dialog closed without saving')
      }).onCancel(() => {
        console.log('User chose to keep settings dialog open')
      })
    }

  } catch (error) {
    console.error('Critical error closing settings dialog:', error)

    // Force close dialog on critical errors to prevent UI lock
    try {
      showSettingsDialog.value = false
      console.log('Settings dialog force closed due to critical error')
    } catch (forceCloseError) {
      console.error('Failed to force close settings dialog:', forceCloseError)
    }

    handleSettingsDialogError(error, 'close')
  }
}

const resetSettingsToDefaults = () => {
  try {
    $q.dialog({
      title: 'Reset Settings',
      message: 'Are you sure you want to reset all settings to defaults? This will clear all attendance data.',
      cancel: true,
      persistent: false,
      color: 'negative'
    }).onOk(() => {
      try {
        // Reset to defaults
        initializeDefaultSettings()

        // Try to save the reset settings
        saveSettings()

        $q.notify({
          message: 'Settings reset to defaults',
          color: 'info',
          position: 'top',
          timeout: 2000,
          icon: 'refresh'
        })

        console.log('Settings reset to defaults successfully')

      } catch (resetError) {
        handleSettingsDialogError(resetError, 'reset')
      }
    })

  } catch (error) {
    handleSettingsDialogError(error, 'reset')
  }
}

// Placeholder avatar function is already defined above

onMounted(async () => {
  try {
    console.log('Initializing reward system component...')

    // Initialize settings from localStorage with error handling
    try {
      const loadResult = loadSettings()
      if (!loadResult) {
        console.warn('Settings loading failed, using defaults')
        initializeDefaultSettings()
      }
    } catch (settingsError) {
      console.error('Critical error loading settings:', settingsError)
      handleSettingsDialogError(settingsError, 'load')
      initializeDefaultSettings()
    }

    // Load behaviors with error handling
    try {
      const res = await axios.get('/api/behaviors')
      if (res.data && Array.isArray(res.data)) {
        behaviors.value = res.data
        console.log(`Loaded ${res.data.length} behaviors`)
      } else {
        console.warn('Invalid behaviors data received:', res.data)
        behaviors.value = []
      }
    } catch (behaviorError) {
      console.error('Failed to load behaviors:', behaviorError)
      behaviors.value = []

      $q.notify({
        message: 'Failed to load behavior options',
        color: 'warning',
        position: 'top',
        timeout: 3000,
        icon: 'warning'
      })
    }

    // Initialize settings state if there's already a selected classroom
    nextTick(() => {
      try {
        if (link.value && fetchData.value.my_sel_classroom_students?.length) {
          console.log('Initializing settings for existing classroom selection...')

          // Validate classroom ID
          if (typeof link.value !== 'string' && typeof link.value !== 'number') {
            console.warn('Invalid classroom ID detected:', link.value)
            return
          }

          // Ensure current classroom ID is set
          if (settings.value.currentClassroomId !== link.value) {
            settings.value.currentClassroomId = link.value
            console.log('Updated current classroom ID:', link.value)
          }

          // Validate students data
          const validStudents = fetchData.value.my_sel_classroom_students.filter(student =>
            student && student.id && typeof student.name === 'string'
          )

          if (validStudents.length !== fetchData.value.my_sel_classroom_students.length) {
            console.warn(`Found ${fetchData.value.my_sel_classroom_students.length - validStudents.length} invalid student records`)
          }

          // Initialize attendance for current students if not already set
          const currentAttendance = { ...settings.value.attendance }
          let hasChanges = false
          let initializedCount = 0

          validStudents.forEach(student => {
            if (!(student.id in currentAttendance)) {
              currentAttendance[student.id] = true // Default to present
              hasChanges = true
              initializedCount++
            }
          })

          if (hasChanges) {
            settings.value.attendance = currentAttendance
            console.log(`Initialized attendance for ${initializedCount} students in existing classroom`)

            // Save the updated settings
            try {
              saveSettings()
            } catch (saveError) {
              console.warn('Failed to save initial attendance settings:', saveError)
            }
          }

          // Validate the final state
          try {
            validateStudentIds()
          } catch (validationError) {
            console.warn('Post-initialization validation failed:', validationError)
          }
        } else {
          console.log('No classroom selected during initialization')
        }
      } catch (initError) {
        console.error('Error during settings initialization:', initError)
        handleSettingsDialogError(initError, 'initialize')
      }
    })

    // Load last selection (semester/week/day/period/classroom/code) so UI reflects previous choices
    try {
      loadLastSelection()
    } catch (e) {
      console.warn('Failed to load last selection on init', e)
    }

    console.log('Reward system component initialization completed')

  } catch (mountError) {
    console.error('Critical error during component mounting:', mountError)

    $q.notify({
      message: 'Application initialization failed',
      caption: mountError.message,
      color: 'negative',
      position: 'top',
      timeout: 5000,
      icon: 'error',
      actions: [
        {
          label: 'Reload Page',
          color: 'white',
          handler: () => window.location.reload()
        }
      ]
    })
  }
})

// Persist last selection when selection values change (debounced)
let lastSelSaveTimeout = null
watch([selSemester, selWeek, selDay, selPeriodNumber, link, period_code], () => {
  try {
    // Auto-generate period_code when semester/week/day/period are available
    try {
      const s = selSemester.value
      const w = selWeek.value
      const d = selDay.value
      const p = selPeriodNumber.value
      if (s != null && w != null && d != null && p != null) {
        // Ensure numeric or string values are converted to simple dot format
        period_code.value = `${s}.${w}.${d}.${p}`
      }
    } catch (genErr) {
      console.warn('Failed to generate period_code', genErr)
    }

    if (lastSelSaveTimeout) clearTimeout(lastSelSaveTimeout)
    lastSelSaveTimeout = setTimeout(() => {
      saveLastSelection()
    }, 300)
  } catch (e) {
    console.warn('Failed to auto-save last selection', e)
  }
})

// Keep classroom name in sync with selected classroom id
watch(link, (newId) => {
  try {
    const cls = (fetchData.value.my_classes_with_students || []).find(c => c.id == newId)
    selClassroomName.value = cls?.classroom_name || ''
  } catch (e) {
    console.warn('Failed to sync classroom name for id', newId, e)
  }
}, { immediate: true })
</script>

<style scoped>
.q-table {
  border-radius: 12px;
  overflow: hidden;
}

.student-card {
  border-radius: 16px;
  overflow: hidden;
  background: white;
  border: 2px solid transparent;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.student-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.selected-card {
  border-color: #1976d2;
  box-shadow: 0 0 0 3px rgba(25, 118, 210, 0.1);
}

.student-avatar {
  height: 50px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.student-avatar img {
  object-fit: cover;
  width: 100%;
  height: 100%;
}

/* Responsive adjustments */
@media (max-width: 640px) {
  .student-card {
    margin: 0.5rem;
  }

  .student-avatar {
    height: 150px;
  }
}

/* Animation for badges */
.q-badge {
  animation: pulse 2s infinite;
}

@keyframes pulse {

  0%,
  100% {
    opacity: 1;
  }

  50% {
    opacity: 0.8;
  }
}

/* Improved hover effects */
.student-card .q-btn {
  transition: all 0.2s ease;
}

.student-card .q-btn:hover {
  transform: scale(1.05);
}

/* Main container styling */
.main-container-card {
  background: white;
  border: none;
}

.control-panel {
  padding: 1rem 0;
}

/* Classroom card styling */
.classroom-card {
  border-radius: 12px;
  border: 2px solid #e5e7eb;
  transition: all 0.2s ease;
}

.classroom-card:hover {
  border-color: #3b82f6;
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
  transform: translateY(-1px);
}

.selected-classroom {
  border-color: #1976d2;
  background-color: #f0f7ff;
  box-shadow: 0 0 0 3px rgba(25, 118, 210, 0.1);
}

/* Teacher card styling */
.teacher-card {
  border-radius: 8px;
  border: 1px solid #e5e7eb;
  transition: all 0.2s ease;
}

.teacher-card:hover {
  border-color: #3b82f6;
  box-shadow: 0 2px 8px rgba(59, 130, 246, 0.1);
}

.selected-teacher {
  border-color: #1976d2;
  background-color: #f8faff;
}

/* Kid-friendly enhancements */
.star-btn {
  background: linear-gradient(135deg, #FFD700, #FFA500);
  animation: twinkle 2s infinite;
}

.star-btn:hover {
  transform: scale(1.2);
  animation: twinkle 0.5s infinite;
}

@keyframes twinkle {

  0%,
  100% {
    opacity: 1;
  }

  50% {
    opacity: 0.8;
  }
}

.student-card {
  position: relative;
  overflow: visible;
}

.student-card::before {
  content: '';
  position: absolute;
  top: -2px;
  left: -2px;
  right: -2px;
  bottom: -2px;
  background: linear-gradient(45deg, #ff6b6b, #4ecdc4, #45b7d1, #96ceb4, #ffeaa7, #dda0dd);
  background-size: 400% 400%;
  border-radius: 18px;
  z-index: -1;
  animation: rainbow 3s ease infinite;
  opacity: 0;
  transition: opacity 0.3s ease;
}

.student-card:hover::before {
  opacity: 1;
}

@keyframes rainbow {
  0% {
    background-position: 0% 50%;
  }

  50% {
    background-position: 100% 50%;
  }

  100% {
    background-position: 0% 50%;
  }
}

/* Leaderboard Button */
.leaderboard-btn {
  background: linear-gradient(135deg, #FFD700, #FFA500, #FF8C00);
  color: white;
  font-weight: bold;
  animation: glow 2s ease-in-out infinite alternate;
}

.leaderboard-btn:hover {
  transform: scale(1.05);
  box-shadow: 0 8px 25px rgba(255, 215, 0, 0.4);
}

@keyframes glow {
  from {
    box-shadow: 0 0 10px rgba(255, 215, 0, 0.5);
  }

  to {
    box-shadow: 0 0 20px rgba(255, 215, 0, 0.8);
  }
}

/* Leaderboard Dialog */
.leaderboard-dialog {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  font-family: 'Comic Sans MS', cursive, sans-serif;
}

.leaderboard-header {
  background: linear-gradient(135deg, #FFD700, #FFA500);
  color: white;
  text-align: center;
  padding: 2rem;
  position: relative;
}

.header-content {
  max-width: 800px;
  margin: 0 auto;
}

.trophy-animation {
  font-size: 4rem;
  animation: bounce 2s infinite;
  margin-bottom: 1rem;
}

@keyframes bounce {

  0%,
  20%,
  50%,
  80%,
  100% {
    transform: translateY(0);
  }

  40% {
    transform: translateY(-20px);
  }

  60% {
    transform: translateY(-10px);
  }
}

.leaderboard-title {
  font-size: 3rem;
  font-weight: bold;
  margin: 0;
  text-shadow: 3px 3px 6px rgba(0, 0, 0, 0.3);
  animation: sparkle-text 3s ease-in-out infinite;
}

@keyframes sparkle-text {

  0%,
  100% {
    text-shadow: 3px 3px 6px rgba(0, 0, 0, 0.3);
  }

  50% {
    text-shadow: 3px 3px 6px rgba(0, 0, 0, 0.3), 0 0 20px rgba(255, 255, 255, 0.5);
  }
}

.leaderboard-subtitle {
  font-size: 1.5rem;
  margin: 1rem 0 0 0;
  opacity: 0.9;
}

.close-btn {
  position: absolute;
  top: 1rem;
  right: 1rem;
  background: rgba(255, 255, 255, 0.2);
  backdrop-filter: blur(10px);
}

/* Period Selector */
.period-selector {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  padding: 1.5rem;
}

.selector-content {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 2rem;
  flex-wrap: wrap;
}

.period-toggle {
  background: white;
  border-radius: 25px;
  padding: 0.5rem;
}

.refresh-btn {
  background: rgba(255, 255, 255, 0.2);
  backdrop-filter: blur(10px);
  border-radius: 25px;
}

/* Leaderboard Content */
.leaderboard-content {
  padding: 2rem;
  min-height: 60vh;
}

.loading-state,
.empty-state {
  text-align: center;
  padding: 4rem 2rem;
}

.loading-text {
  font-size: 1.5rem;
  margin-top: 1rem;
  opacity: 0.8;
}

.empty-state .empty-icon {
  font-size: 6rem;
  margin-bottom: 2rem;
}

.empty-state h3 {
  font-size: 2rem;
  margin-bottom: 1rem;
}

.empty-state p {
  font-size: 1.3rem;
  opacity: 0.8;
}

/* Podium Section */
.podium-section {
  margin-bottom: 3rem;
}

.podium {
  display: flex;
  justify-content: center;
  align-items: end;
  gap: 2rem;
  margin: 2rem 0;
  flex-wrap: wrap;
}

.podium-position {
  display: flex;
  flex-direction: column;
  align-items: center;
  animation: slideUp 1s ease-out;
}

.podium-student {
  background: rgba(255, 255, 255, 0.15);
  backdrop-filter: blur(10px);
  border-radius: 20px;
  padding: 2rem;
  text-align: center;
  margin-bottom: 1rem;
  position: relative;
  transition: all 0.3s ease;
}

.podium-student:hover {
  transform: scale(1.05);
  background: rgba(255, 255, 255, 0.25);
}

.student-avatar-large {
  position: relative;
  width: 120px;
  height: 120px;
  margin: 0 auto 1rem auto;
}

.avatar-img {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  border: 4px solid white;
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
}

.position-badge {
  position: absolute;
  top: -10px;
  right: -10px;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 1.2rem;
  color: white;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
}

.gold {
  background: linear-gradient(135deg, #FFD700, #FFA500);
}

.silver {
  background: linear-gradient(135deg, #C0C0C0, #A8A8A8);
}

.bronze {
  background: linear-gradient(135deg, #CD7F32, #B87333);
}

.crown {
  position: absolute;
  top: -30px;
  left: 50%;
  transform: translateX(-50%);
  font-size: 2rem;
  animation: float 3s ease-in-out infinite;
}

@keyframes float {

  0%,
  100% {
    transform: translateX(-50%) translateY(0px);
  }

  50% {
    transform: translateX(-50%) translateY(-10px);
  }
}

.student-name {
  font-size: 1.5rem;
  font-weight: bold;
  margin: 0 0 0.5rem 0;
  text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
}

.champion {
  font-size: 1.8rem;
  color: #FFD700;
  animation: champion-glow 2s ease-in-out infinite alternate;
}

@keyframes champion-glow {
  from {
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
  }

  to {
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3), 0 0 15px rgba(255, 215, 0, 0.8);
  }
}

.student-points {
  font-size: 1.3rem;
  font-weight: bold;
  margin-bottom: 1rem;
}

.gold-points {
  color: #FFD700;
}

.silver-points {
  color: #C0C0C0;
}

.bronze-points {
  color: #CD7F32;
}

.medal {
  font-size: 2.5rem;
  animation: spin 4s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }

  to {
    transform: rotate(360deg);
  }
}

.podium-base {
  background: rgba(255, 255, 255, 0.2);
  backdrop-filter: blur(10px);
  padding: 1rem 2rem;
  border-radius: 10px 10px 0 0;
  font-weight: bold;
  font-size: 1.2rem;
  text-align: center;
  min-width: 100px;
}

.first-place {
  order: 2;
}

.second-place {
  order: 1;
}

.third-place {
  order: 3;
}

.first-base {
  height: 80px;
  background: linear-gradient(135deg, #FFD700, #FFA500);
  color: white;
}

.second-base {
  height: 60px;
  background: linear-gradient(135deg, #C0C0C0, #A8A8A8);
  color: white;
}

.third-base {
  height: 40px;
  background: linear-gradient(135deg, #CD7F32, #B87333);
  color: white;
}

@keyframes slideUp {
  from {
    transform: translateY(50px);
    opacity: 0;
  }

  to {
    transform: translateY(0);
    opacity: 1;
  }
}

/* Remaining Students */
.remaining-students {
  margin: 2rem 0;
}

.student-row {
  display: flex;
  align-items: center;
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  border-radius: 15px;
  padding: 1.5rem;
  margin-bottom: 1rem;
  transition: all 0.3s ease;
  animation: fadeIn 1s ease-out;
}

.student-row:hover {
  background: rgba(255, 255, 255, 0.2);
  transform: translateX(10px);
}

.row-position {
  font-size: 2rem;
  font-weight: bold;
  width: 60px;
  text-align: center;
  color: #FFD700;
}

.row-avatar {
  margin: 0 1.5rem;
}

.row-avatar-img {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  border: 3px solid white;
}

.row-info {
  flex: 1;
}

.row-name {
  font-size: 1.3rem;
  font-weight: bold;
  margin: 0 0 0.5rem 0;
}

.row-points {
  font-size: 1.1rem;
  opacity: 0.9;
}

.row-badge {
  font-size: 2rem;
  margin-left: 1rem;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Recognition Section */
.recognition-section {
  margin-top: 3rem;
  text-align: center;
}

.recognition-title {
  font-size: 2rem;
  margin-bottom: 2rem;
  animation: pulse-glow 2s ease-in-out infinite;
}

@keyframes pulse-glow {

  0%,
  100% {
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
  }

  50% {
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3), 0 0 20px rgba(255, 255, 255, 0.6);
  }
}

.recognition-cards {
  display: flex;
  justify-content: center;
  gap: 2rem;
  flex-wrap: wrap;
}

.recognition-card {
  background: rgba(255, 255, 255, 0.15);
  backdrop-filter: blur(10px);
  border-radius: 20px;
  padding: 2rem;
  text-align: center;
  min-width: 200px;
  transition: all 0.3s ease;
  animation: cardFloat 6s ease-in-out infinite;
}

.recognition-card:nth-child(2) {
  animation-delay: 2s;
}

.recognition-card:nth-child(3) {
  animation-delay: 4s;
}

@keyframes cardFloat {

  0%,
  100% {
    transform: translateY(0px);
  }

  33% {
    transform: translateY(-10px);
  }

  66% {
    transform: translateY(5px);
  }
}

.recognition-card:hover {
  transform: scale(1.05) translateY(-5px);
  background: rgba(255, 255, 255, 0.25);
}

.card-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.card-title {
  font-size: 1.2rem;
  font-weight: bold;
  margin-bottom: 0.5rem;
}

.card-name {
  font-size: 1.1rem;
  color: #FFD700;
  font-weight: bold;
}

/* Footer */
.leaderboard-footer {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  text-align: center;
  padding: 2rem;
}

.footer-content {
  max-width: 600px;
  margin: 0 auto;
}

.encouragement-message {
  font-size: 1.3rem;
  margin-bottom: 2rem;
  line-height: 1.6;
}

.back-btn {
  background: linear-gradient(135deg, #4CAF50, #8BC34A);
  border-radius: 25px;
  padding: 1rem 2rem;
  font-weight: bold;
}

/* Absent Student Styling */
.student-absent {
  opacity: 0.5;
  filter: grayscale(100%);
  pointer-events: none;
}

.student-absent:hover {
  transform: none !important;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important;
}

.student-absent .star-btn {
  background: #e0e0e0 !important;
  color: #9e9e9e !important;
  cursor: not-allowed;
}

.student-absent .star-btn:hover {
  transform: none !important;
  animation: none !important;
}

.student-absent .q-badge {
  opacity: 0.6;
  animation: none;
}

.student-absent .q-chip {
  opacity: 0.6;
}

/* Disabled state for absent students */
.student-absent .q-card-actions {
  background-color: #f5f5f5 !important;
}

/* Override rainbow border for absent students */
.student-absent::before {
  display: none !important;
}

/* Settings Visual Feedback Styles */
.settings-toggle-container {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.5rem;
  border-radius: 8px;
  transition: all 0.3s ease;
}

.settings-toggle {
  transition: all 0.3s ease;
}

.toggle-enabled {
  background-color: rgba(76, 175, 80, 0.1);
  border-radius: 8px;
  padding: 0.5rem;
}

.toggle-disabled {
  background-color: rgba(244, 67, 54, 0.1);
  border-radius: 8px;
  padding: 0.5rem;
}

.toggle-status-indicator {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  background-color: rgba(255, 255, 255, 0.8);
  border: 1px solid rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
}

.toggle-status-indicator:hover {
  transform: scale(1.05);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

/* Attendance Visual Feedback */
.attendance-item {
  position: relative;
  overflow: hidden;
}

.attendance-item::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 4px;
  transition: all 0.3s ease;
}

.attendance-chip {
  transition: all 0.3s ease;
  position: relative;
}

.attendance-chip:hover {
  transform: scale(1.05);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
}

.attendance-toggle {
  transition: all 0.3s ease;
}

.attendance-toggle:hover {
  transform: scale(1.1);
}

.attendance-action-btn {
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
}

.attendance-action-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.attendance-action-btn::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
  transition: left 0.5s;
}

.attendance-action-btn:hover::before {
  left: 100%;
}

/* Avatar Button Visual Feedback */
.avatar-overlay-enabled {
  background: linear-gradient(135deg, rgba(76, 175, 80, 0.1), rgba(33, 150, 243, 0.1));
}

.avatar-overlay-disabled {
  background: linear-gradient(135deg, rgba(158, 158, 158, 0.2), rgba(97, 97, 97, 0.2));
}

.avatar-btn-enabled {
  transition: all 0.3s ease;
  position: relative;
}

.avatar-btn-enabled:hover {
  transform: scale(1.15) rotate(5deg);
  box-shadow: 0 4px 15px rgba(33, 150, 243, 0.4);
}

.avatar-btn-enabled::before {
  content: '';
  position: absolute;
  inset: -2px;
  border-radius: 50%;
  background: linear-gradient(45deg, #4CAF50, #2196F3, #FF9800);
  background-size: 200% 200%;
  animation: avatar-btn-glow 2s ease-in-out infinite;
  z-index: -1;
  opacity: 0;
  transition: opacity 0.3s ease;
}

.avatar-btn-enabled:hover::before {
  opacity: 1;
}

@keyframes avatar-btn-glow {

  0%,
  100% {
    background-position: 0% 50%;
  }

  50% {
    background-position: 100% 50%;
  }
}

.avatar-btn-disabled {
  transition: all 0.3s ease;
  position: relative;
}

.avatar-btn-disabled:hover {
  transform: scale(1.05);
  box-shadow: 0 2px 8px rgba(158, 158, 158, 0.3);
}

.avatar-btn-disabled::after {
  content: '';
  position: absolute;
  inset: 0;
  border-radius: 50%;
  background: repeating-linear-gradient(45deg,
      transparent,
      transparent 2px,
      rgba(244, 67, 54, 0.3) 2px,
      rgba(244, 67, 54, 0.3) 4px);
  pointer-events: none;
}

/* Behavior Button Visual Feedback */
.behavior-btn {
  transition: all 0.3s ease;
  position: relative;
}

.star-btn-enabled {
  background: linear-gradient(135deg, #FFD700, #FFA500);
  animation: star-pulse 2s ease-in-out infinite;
}

.star-btn-enabled:hover {
  transform: scale(1.2) rotate(10deg);
  animation: star-sparkle 0.6s ease-in-out infinite;
  box-shadow: 0 0 20px rgba(255, 215, 0, 0.6);
}

@keyframes star-pulse {

  0%,
  100% {
    box-shadow: 0 0 10px rgba(255, 215, 0, 0.3);
  }

  50% {
    box-shadow: 0 0 20px rgba(255, 215, 0, 0.6);
  }
}

@keyframes star-sparkle {

  0%,
  100% {
    transform: scale(1.2) rotate(10deg);
    filter: brightness(1);
  }

  50% {
    transform: scale(1.25) rotate(-5deg);
    filter: brightness(1.2);
  }
}

.star-btn-disabled {
  background: #e0e0e0 !important;
  color: #9e9e9e !important;
  cursor: not-allowed;
  animation: none !important;
}

.star-btn-disabled:hover {
  transform: none !important;
  animation: disabled-shake 0.5s ease-in-out;
}

@keyframes disabled-shake {

  0%,
  100% {
    transform: translateX(0);
  }

  25% {
    transform: translateX(-2px);
  }

  75% {
    transform: translateX(2px);
  }
}

/* Enhanced Hover States for Interactive Elements */
.q-btn:not(:disabled):hover {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.q-toggle:hover {
  transform: scale(1.05);
  transition: transform 0.2s ease;
}

.q-chip:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
  transition: all 0.3s ease;
}

/* Enhanced Tooltip Styles */
.tooltip-enhanced {
  font-size: 0.875rem;
  padding: 0.5rem 0.75rem;
  border-radius: 6px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  backdrop-filter: blur(4px);
}

.tooltip-success {
  background: linear-gradient(135deg, #4CAF50, #66BB6A) !important;
}

.tooltip-warning {
  background: linear-gradient(135deg, #FF9800, #FFB74D) !important;
}

.tooltip-info {
  background: linear-gradient(135deg, #2196F3, #64B5F6) !important;
}

/* Enhanced Settings Toggle Container */
.enhanced-toggle-container {
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.8), rgba(248, 250, 252, 0.9));
  border: 1px solid rgba(0, 0, 0, 0.08);
  border-radius: 12px;
  padding: 1rem;
  transition: all 0.3s ease;
}

.enhanced-toggle-container:hover {
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(248, 250, 252, 1));
  border-color: rgba(0, 0, 0, 0.12);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.enhanced-settings-toggle {
  position: relative;
}

.enhanced-settings-toggle::before {
  content: '';
  position: absolute;
  inset: -4px;
  border-radius: 8px;
  background: linear-gradient(45deg, transparent, rgba(33, 150, 243, 0.1), transparent);
  opacity: 0;
  transition: opacity 0.3s ease;
}

.enhanced-settings-toggle:hover::before {
  opacity: 1;
}

.enhanced-status-indicator {
  background: rgba(255, 255, 255, 0.9);
  border: 1px solid rgba(0, 0, 0, 0.08);
  border-radius: 20px;
  padding: 0.5rem 0.75rem;
  transition: all 0.3s ease;
}

.enhanced-status-indicator:hover {
  transform: scale(1.05);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  border-color: rgba(0, 0, 0, 0.15);
}

.status-icon {
  transition: all 0.3s ease;
}

.icon-pulse {
  animation: icon-pulse 2s ease-in-out infinite;
}

@keyframes icon-pulse {

  0%,
  100% {
    transform: scale(1);
    opacity: 1;
  }

  50% {
    transform: scale(1.1);
    opacity: 0.8;
  }
}

/* Enhanced Attendance Visual Feedback */
.attendance-present {
  background: linear-gradient(135deg, rgba(76, 175, 80, 0.08), rgba(129, 199, 132, 0.05));
  border-left: 4px solid #4CAF50;
  position: relative;
}

.attendance-present::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 4px;
  background: linear-gradient(180deg, #4CAF50, #66BB6A);
  box-shadow: 0 0 8px rgba(76, 175, 80, 0.3);
}

.attendance-absent {
  background: linear-gradient(135deg, rgba(244, 67, 54, 0.08), rgba(239, 154, 154, 0.05));
  border-left: 4px solid #F44336;
  position: relative;
}

.attendance-absent::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 4px;
  background: linear-gradient(180deg, #F44336, #EF5350);
  box-shadow: 0 0 8px rgba(244, 67, 54, 0.3);
}

.attendance-avatar-container {
  transition: all 0.3s ease;
}

.attendance-avatar-container:hover {
  transform: scale(1.05);
}

.attendance-status-badge {
  transition: all 0.3s ease;
}

.attendance-status-icon {
  transition: all 0.3s ease;
}

.pulse-animation {
  animation: status-pulse 2s ease-in-out infinite;
}

@keyframes status-pulse {

  0%,
  100% {
    transform: scale(1);
    box-shadow: 0 0 0 0 rgba(244, 67, 54, 0.4);
  }

  50% {
    transform: scale(1.1);
    box-shadow: 0 0 0 4px rgba(244, 67, 54, 0.1);
  }
}

.shadow-green-glow {
  box-shadow: 0 0 8px rgba(76, 175, 80, 0.3);
}

.shadow-red-glow {
  box-shadow: 0 0 8px rgba(244, 67, 54, 0.3);
}

.chip-pulse {
  animation: chip-pulse 2s ease-in-out infinite;
}

@keyframes chip-pulse {

  0%,
  100% {
    opacity: 1;
    transform: scale(1);
  }

  50% {
    opacity: 0.8;
    transform: scale(1.02);
  }
}

.enhanced-toggle {
  transition: all 0.3s ease;
  position: relative;
}

.enhanced-toggle::before {
  content: '';
  position: absolute;
  inset: -2px;
  border-radius: 50px;
  background: linear-gradient(45deg, transparent, rgba(76, 175, 80, 0.2), transparent);
  opacity: 0;
  transition: opacity 0.3s ease;
}

.enhanced-toggle:hover::before {
  opacity: 1;
}

.toggle-absent::before {
  background: linear-gradient(45deg, transparent, rgba(244, 67, 54, 0.2), transparent);
}

/* Enhanced Action Buttons */
.enhanced-action-btn {
  position: relative;
  overflow: hidden;
  transition: all 0.3s ease;
}

.enhanced-action-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
}

.enhanced-action-btn::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
  transition: left 0.5s;
}

.enhanced-action-btn:hover::before {
  left: 100%;
}

.btn-success-glow {
  box-shadow: 0 0 20px rgba(76, 175, 80, 0.4);
  animation: success-glow 2s ease-in-out infinite;
}

.btn-warning-glow {
  box-shadow: 0 0 20px rgba(244, 67, 54, 0.4);
  animation: warning-glow 2s ease-in-out infinite;
}

@keyframes success-glow {

  0%,
  100% {
    box-shadow: 0 0 20px rgba(76, 175, 80, 0.4);
  }

  50% {
    box-shadow: 0 0 30px rgba(76, 175, 80, 0.6);
  }
}

@keyframes warning-glow {

  0%,
  100% {
    box-shadow: 0 0 20px rgba(244, 67, 54, 0.4);
  }

  50% {
    box-shadow: 0 0 30px rgba(244, 67, 54, 0.6);
  }
}

/* Attendance Summary */
.attendance-summary {
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.9), rgba(248, 250, 252, 0.8));
  border: 1px solid rgba(0, 0, 0, 0.08);
  transition: all 0.3s ease;
}

.attendance-summary:hover {
  background: linear-gradient(135deg, rgba(255, 255, 255, 1), rgba(248, 250, 252, 0.95));
  border-color: rgba(0, 0, 0, 0.12);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.attendance-progress {
  transition: all 0.3s ease;
}

.attendance-progress:hover {
  transform: scaleY(1.2);
}

/* Settings Main Button */
.settings-main-btn {
  transition: all 0.3s ease;
  position: relative;
}

.settings-main-btn:hover {
  /*transform: translateY(-2px) rotate(90deg);*/
  box-shadow: 0 4px 15px rgba(97, 97, 97, 0.3);
}

.settings-main-btn::before {
  content: '';
  position: absolute;
  inset: -2px;
  border-radius: 8px;
  background: linear-gradient(45deg, #616161, #424242, #757575);
  background-size: 200% 200%;
  animation: settings-glow 3s ease-in-out infinite;
  z-index: -1;
  opacity: 0;
  transition: opacity 0.3s ease;
}

.settings-main-btn:hover::before {
  opacity: 0.3;
}

@keyframes settings-glow {

  0%,
  100% {
    background-position: 0% 50%;
  }

  50% {
    background-position: 100% 50%;
  }
}

/* Settings Dialog Enhanced Styling */
.q-dialog .q-card {
  backdrop-filter: blur(10px);
  background: rgba(255, 255, 255, 0.95);
  border: 1px solid rgba(255, 255, 255, 0.2);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
}

.q-dialog .q-card-section {
  position: relative;
}

.q-dialog .q-card-section::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 1px;
  background: linear-gradient(90deg, transparent, rgba(0, 0, 0, 0.1), transparent);
}

/* Responsive Design */
@media (max-width: 768px) {
  .leaderboard-title {
    font-size: 2rem;
  }

  .leaderboard-subtitle {
    font-size: 1.2rem;
  }

  .podium {
    flex-direction: column;
    gap: 1rem;
  }

  .podium-position {
    order: unset !important;
  }

  .student-avatar-large {
    width: 100px;
    height: 100px;
  }

  .avatar-img {
    width: 100px;
    height: 100px;
  }

  .recognition-cards {
    flex-direction: column;
    align-items: center;
  }

  .student-row {
    flex-direction: column;
    text-align: center;
    gap: 1rem;
  }

  .row-position {
    width: auto;
  }

  .settings-toggle-container {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
  }

  .attendance-item {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
    text-align: left;
  }

  .attendance-item .flex:last-child {
    align-self: flex-end;
  }
}
</style>
