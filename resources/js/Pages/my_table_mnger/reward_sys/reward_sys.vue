<template>
  <Head title="Reward System" />
  <div class="  bg-gradient-to-br from-gray-800 to-gray-900  h-full">





<div class="p-1 scale-25">


</div>












    <!-- Compact Header for Dialog Mode -->
    <!-- <CompactSessionHeader
      v-if="isDialog && selectedClassroomId"
      :date="selectedDate"
      :week="selectedWeek"
      :period="selectedPeriodNumber"
      :classroom-name="classrooms.find(c => c.classroom_id === selectedClassroomId)?.classroom_name || ''"
      :stats="attendanceSummary"
      :avatar-edit-enabled="avatarEditEnabled"
      :init-status="initStatus.message ? `${initStatus.message} (Created: ${initStatus.created})` : ''"
      @update:date="selectedDate = $event"
      @update:week="selectedWeek = $event"
      @update:avatarEdit="avatarEditEnabled = $event"
    /> -->

    <!-- Header Card (Standalone Mode Only) -->
    <q-card v-if="!isDialog" class="shadow-lg rounded-2xl overflow-hidden">
      <q-card-section class="bg-gradient-to-r from-blue-500 to-indigo-600 text-white flex justify-between items-center">
        <div>
          <h1 class="text-3xl font-bold">🏆 {{ $t('rewardSys.behaviors.management') }}</h1>
          <p class="text-blue-100">{{ $t('rewardSys.behaviors.management') }}</p>

           
        </div>
        <!-- Language Toggle -->
        <q-btn-toggle
          v-model="locale"
          :options="[
            { label: '🇬🇧 English', value: 'en' },
            { label: '🇸🇦 العربية', value: 'ar' }
          ]"
          flat
          color="white"
          text-color="white"
          toggle-color="white"
          toggle-text-color="primary"
          unelevated
        />
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
              <div class="text-sm text-gray-500 font-medium">{{ $t('rewardSys.session.current') }}</div>
              <div class="text-lg font-bold text-gray-800 flex items-center gap-2">
                <span v-if="selectedClassroomId">
                  {{ classrooms.find(c => c.classroom_id === selectedClassroomId)?.classroom_name || $t('rewardSys.session.unknownClass') }}
                </span>
                <span v-else class="text-gray-400 italic">{{ $t('rewardSys.session.noClassroom') }}</span>
                
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
        </div>

        <!-- Classroom Summary - Compact Design -->
        <div v-if="selectedClassroomId" class="bg-gradient-to-r from-blue-50 to-indigo-50 p-3 rounded-lg border border-blue-200 shadow-sm">
          <div class="flex items-center justify-between flex-wrap gap-3">
            <!-- Title -->
            <div class="flex items-center gap-2">
              <q-icon name="groups" class="text-blue-600" size="sm" />
              <span class="text-sm font-bold text-gray-700">{{ $t('rewardSys.session.classroomSummary') }}</span>
            </div>
            
            <!-- Stats Row -->
            <div class="flex items-center gap-3 flex-wrap">
              <!-- Avatar Edit Toggle -->
              <q-toggle
                v-model="avatarEditEnabled"
                icon="edit"
                label="Edit Avatars"
                dense
                color="secondary"
                size="sm"
                class="bg-white px-2 py-1 rounded-lg border border-gray-200"
              />

              <!-- Init Status -->
              <div v-if="initStatus.message" class="text-xs text-gray-500 bg-white px-2 py-1 rounded border border-dashed border-gray-300">
                 {{ initStatus.message }} ({{ initStatus.created }})
              </div>

              <!-- Total Students -->
              <div class="flex items-center gap-2 bg-white px-3 py-1.5 rounded-lg border border-blue-200">
                <q-icon name="people" class="text-blue-600" size="sm" />
                <div class="flex flex-col">
                  <span class="text-xs text-gray-500">{{ $t('rewardSys.session.total') }}</span>
                  <span class="text-lg font-bold text-blue-600">{{ students.length }}</span>
                </div>
              </div>
              
              <!-- Present -->
              <div class="flex items-center gap-2 bg-white px-3 py-1.5 rounded-lg border border-green-200">
                <q-icon name="check_circle" class="text-green-600" size="sm" />
                <div class="flex flex-col">
                  <span class="text-xs text-gray-500">{{ $t('rewardSys.session.present') }}</span>
                  <span class="text-lg font-bold text-green-600">{{ attendanceSummary.present }}</span>
                </div>
              </div>
              
              <!-- Absent -->
              <div class="flex items-center gap-2 bg-white px-3 py-1.5 rounded-lg border border-red-200">
                <q-icon name="cancel" class="text-red-600" size="sm" />
                <div class="flex flex-col">
                  <span class="text-xs text-gray-500">{{ $t('rewardSys.session.absent') }}</span>
                  <span class="text-lg font-bold text-red-600">{{ attendanceSummary.absent }}</span>
                </div>
              </div>
              
              <!-- Copy Absent List Button -->
              <q-btn 
                dense 
                color="primary" 
                icon="content_copy" 
                :label="$t('rewardSys.session.copyList')"
                @click="copyToClipboard"
                :disable="attendanceSummary.absent === 0"
                size="sm"
                class="shadow-sm"
              >
                <q-tooltip>{{ $t('rewardSys.session.copyToClipboard') }}</q-tooltip>
              </q-btn>
            </div>
          </div>

          <!-- Absent Students List -->
          <div v-if="attendanceSummary.absent > 0" class="mt-3 pt-3 border-t border-blue-200">
            <div class="text-xs font-semibold text-gray-600 mb-2 flex items-center gap-1">
              <q-icon name="person_off" size="xs" color="red" />
              <span>{{ $t('rewardSys.session.absentStudentsList') }}</span>
              <q-badge color="red" :label="attendanceSummary.absent" class="ml-1" />
            </div>
            <div class="bg-white border border-red-200 rounded-lg p-2">
              <div class="flex flex-wrap gap-1.5">
                <q-chip
                  v-for="student in attendanceSummary.absentList"
                  :key="student.id"
                  color="red"
                  text-color="white"
                  size="sm"
                  dense
                  icon="person"
                >
                  {{ locale === 'ar' && student.name_ar ? student.name_ar : student.name }}
                </q-chip>
              </div>
            </div>
          </div>
        </div>
      </q-card-section>
    </q-card>




































 







 

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





    <!-- Audio Elements -->
    <audio ref="bgMusic" loop id="bg-music">
      <source src="/audio/background_music2.mp3" type="audio/mpeg">
    </audio>

    <!-- Main Tabs -->

    <q-card class="shadow-lg rounded-2xl" v-if="students.length">

      <!-- Main Dropdown Menu for Navigation (Replaces Tabs) -->
      <!-- Main Sticky Header for Navigation & Actions -->
      <div class="sticky top-0 z-50 bg-white shadow-md border-b border-gray-200">
        <div class="q-pa-sm flex justify-between items-center flex-nowrap gap-2">
          <!-- Left: Menu & Context -->
          <div class="flex items-center gap-2 flex-nowrap overflow-hidden">
            <q-btn-dropdown 
              color="primary" 
              icon="menu" 
              :label="$q.screen.gt.xs ? 'Menu' : ''"
              class="shadow-sm glossy"
              content-class="bg-white"
              dense
            >
              <div class="row no-wrap q-pa-md">
                <!-- Left Column: Navigation & Filters -->
                <div class="column" :style="$q.screen.lt.sm ? 'min-width: 250px' : 'min-width: 220px'">
                    <div class="text-subtitle1 text-weight-bold q-mb-sm text-grey-8">Navigation</div>
                    
                    <q-list dense>
                        <q-item clickable v-close-popup @click="activeTab = 'positive'" :active="activeTab === 'positive'" active-class="bg-blue-50 text-primary">
                           <q-item-section avatar><q-icon name="add_circle" /></q-item-section>
                           <q-item-section>{{ $t('rewardSys.tabs.positivePoints') }}</q-item-section>
                        </q-item>

                        <q-item clickable v-close-popup @click="activeTab = 'attendance'" :active="activeTab === 'attendance'" active-class="bg-blue-50 text-primary">
                          <q-item-section avatar><q-icon name="how_to_reg" /></q-item-section>
                          <q-item-section>{{ $t('rewardSys.tabs.attendance') }}</q-item-section>
                        </q-item>

                        <q-item clickable v-close-popup @click="activeTab = 'history'" :active="activeTab === 'history'" active-class="bg-blue-50 text-primary">
                           <q-item-section avatar><q-icon name="cancel" /></q-item-section>
                           <q-item-section>{{ $t('rewardSys.tabs.history') }}</q-item-section>
                        </q-item>

                        <q-item clickable v-close-popup @click="activeTab = 'classroom_helper'" :active="activeTab === 'classroom_helper'" active-class="bg-blue-50 text-primary">
                           <q-item-section avatar><q-icon name="assignment" /></q-item-section>
                           <q-item-section>Classroom Helper</q-item-section>
                        </q-item>

                        <q-item clickable v-close-popup @click="activeTab = 'timer_random'" :active="activeTab === 'timer_random'" active-class="bg-blue-50 text-primary">
                           <q-item-section avatar><q-icon name="timer" /></q-item-section>
                           <q-item-section>Timer & Random</q-item-section>
                        </q-item>
                        
                        <q-item clickable v-close-popup @click="showLeaderboard = true; activeTab = 'champions'" :active="activeTab === 'champions'" active-class="bg-blue-50 text-primary">
                           <q-item-section avatar><q-icon name="emoji_events" /></q-item-section>
                           <q-item-section>{{ $t('rewardSys.tabs.champions') }}</q-item-section>
                        </q-item>

                        <q-item clickable v-close-popup @click="activeTab = 'settings_reports'" :active="activeTab === 'settings_reports'" active-class="bg-blue-50 text-primary">
                           <q-item-section avatar><q-icon name="settings_applications" /></q-item-section>
                           <q-item-section>{{ $t('rewardSys.tabs.settingsReports') }}</q-item-section>
                        </q-item>
                    </q-list>

                    <q-separator class="q-my-md" />
                    
                    <div class="text-subtitle2 text-grey-7 q-mb-xs">Points Filter</div>
                    <q-list dense>
                        <q-item clickable v-close-popup @click="pointsDisplayMode = 'overall'" :active="pointsDisplayMode === 'overall'" active-class="text-orange-9 text-weight-bold">
                            <q-item-section avatar><q-icon name="functions" size="xs" /></q-item-section>
                            <q-item-section>Overall (All Time)</q-item-section>
                            <q-item-section side v-if="pointsDisplayMode === 'overall'"><q-icon name="check" size="xs" color="orange" /></q-item-section>
                        </q-item>

                        <q-item clickable v-close-popup @click="pointsDisplayMode = 'all_subjects'" :active="pointsDisplayMode === 'all_subjects'" active-class="text-orange-9 text-weight-bold">
                            <q-item-section avatar><q-icon name="public" size="xs" /></q-item-section>
                            <q-item-section>Overall (All Subjects)</q-item-section>
                            <q-item-section side v-if="pointsDisplayMode === 'all_subjects'"><q-icon name="check" size="xs" color="orange" /></q-item-section>
                        </q-item>
                        
                         <q-item clickable v-close-popup @click="pointsDisplayMode = 'session'" :active="pointsDisplayMode === 'session'" active-class="text-orange-9 text-weight-bold">
                            <q-item-section avatar><q-icon name="timer" size="xs" /></q-item-section>
                            <q-item-section>This Session</q-item-section>
                            <q-item-section side v-if="pointsDisplayMode === 'session'"><q-icon name="check" size="xs" color="orange" /></q-item-section>
                        </q-item>
                        
                        <q-item clickable v-close-popup @click="pointsDisplayMode = 'competition'" :active="pointsDisplayMode === 'competition'" active-class="text-orange-9 text-weight-bold">
                            <q-item-section avatar><q-icon name="date_range" size="xs" /></q-item-section>
                            <q-item-section>Competition (Week)</q-item-section>
                            <q-item-section side v-if="pointsDisplayMode === 'competition'"><q-icon name="check" size="xs" color="orange" /></q-item-section>
                        </q-item>

                        <q-item clickable v-close-popup @click="pointsDisplayMode = 'from_now'" :active="pointsDisplayMode === 'from_now'" active-class="text-orange-9 text-weight-bold">
                            <q-item-section avatar><q-icon name="restart_alt" size="xs" /></q-item-section>
                            <q-item-section>From Now (Reset View)</q-item-section>
                            <q-item-section side v-if="pointsDisplayMode === 'from_now'"><q-icon name="check" size="xs" color="orange" /></q-item-section>
                        </q-item>
                    </q-list>

                    <q-separator class="q-my-md" />

                    <div class="text-subtitle2 text-grey-7 q-mb-xs">Layout</div>
                    <q-list dense>
                        <q-item 
                            clickable 
                            v-close-popup 
                            v-for="layout in layoutOptions" 
                            :key="layout.value"
                            @click="selectedLayout = layout.value"
                            :active="selectedLayout === layout.value"
                            active-class="text-orange-9 text-weight-bold"
                        >
                            <q-item-section avatar><q-icon :name="layout.icon" size="xs" /></q-item-section>
                            <q-item-section>{{ layout.label }}</q-item-section>
                            <q-item-section side v-if="selectedLayout === layout.value"><q-icon name="check" size="xs" color="orange" /></q-item-section>
                        </q-item>
                        
                        <q-separator class="q-my-xs" />
                        
                        <q-item clickable v-close-popup @click="showGroupEditor = true">
                            <q-item-section avatar><q-icon name="settings" size="xs" class="text-grey-7" /></q-item-section>
                            <q-item-section class="text-grey-8">{{ $t('rewardSys.manageGroups') }}</q-item-section>
                        </q-item>
                    </q-list>
                    
                    <!-- Mobile: Embed Settings Here if screen is small -->
                    <div v-if="$q.screen.lt.sm">
                        <q-separator class="q-my-md" />
                        <div class="text-subtitle1 text-weight-bold q-mb-md text-grey-8">Settings</div>
                        <q-toggle v-model="isMusicEnabled" checked-icon="music_note" unchecked-icon="music_off" label="Music On" color="primary" dense />
                        <q-toggle v-model="avatarEditEnabled" icon="edit" label="Edit Avatars" color="secondary" dense />
                        <q-toggle v-model="isReadAloudEnabled" icon="volume_up" label="Read Aloud" color="purple" dense />
                    </div>
                </div>

                <q-separator vertical inset class="q-mx-lg" v-if="$q.screen.gt.xs" />

                <!-- Right Column: Settings & Info (Hidden on mobile, merged into left) -->
                <div class="column items-center" style="min-width: 180px" v-if="$q.screen.gt.xs">
                   <div class="text-subtitle1 text-weight-bold q-mb-md text-grey-8">Settings</div>
                   
                   <div class="column q-gutter-y-sm full-width">
                       <q-toggle
                           v-model="isMusicEnabled"
                           checked-icon="music_note"
                           unchecked-icon="music_off"
                           label="Music On"
                           color="primary"
                           dense
                         />
                         
                        <q-toggle
                           v-model="avatarEditEnabled"
                           icon="edit"
                           label="Edit Avatars"
                           color="secondary"
                           dense
                         />

                        <q-toggle
                           v-model="isReadAloudEnabled"
                           icon="volume_up"
                           label="Read Aloud"
                           color="purple"
                           dense
                         />
                   </div>
                   
                   <q-separator class="q-my-md full-width" />

                   <!-- Noise Meter -->
                   <div class="q-py-sm">
                      <noise class="scale-90 transform-origin-center" />
                   </div>
                </div>
              </div>
            </q-btn-dropdown>
            
            <!-- Persistent Attendance Summary -->
            <div class="flex items-center gap-1 mx-2">
                <!-- Total -->
                <q-btn
                    rounded
                    dense
                    no-caps
                    color="grey-3"
                    text-color="grey-9"
                    class="px-2 shadow-sm text-xs border border-gray-300"
                    @click="openAttendanceList('all')"
                >
                    <q-icon name="groups" size="xs" class="mr-1" />
                    <span class="font-bold">{{ students.length }}</span>
                </q-btn>

                <!-- Present -->
                <q-btn
                    rounded
                    dense
                    no-caps
                    color="positive"
                    class="px-2 shadow-sm text-xs"
                    @click="openAttendanceList('present')"
                >
                    <q-icon name="how_to_reg" class="mr-1" size="xs" />
                    <span class="font-bold">{{ presentCount }}</span>
                </q-btn>

                <!-- Absent -->
                <q-btn
                    rounded
                    dense
                    no-caps
                    color="negative"
                    class="px-2 shadow-sm text-xs"
                    @click="openAttendanceList('absent')"
                >
                    <q-icon name="person_off" class="mr-1" size="xs" />
                    <span class="font-bold">{{ absentCount }}</span>
                </q-btn>
            </div>
                        
            <!-- Context Badge showing current Tab (Compact on mobile) -->
            <div class="flex items-center gap-1 overflow-hidden" v-if="activeTab !== 'positive' || $q.screen.gt.xs">
                <q-badge color="blue-1" text-color="blue-9" class="q-py-xs q-px-sm text-subtitle2 whitespace-nowrap">
                    {{ activeTab === 'positive' ? $t('rewardSys.tabs.positivePoints') : 
                       activeTab === 'attendance' ? $t('rewardSys.tabs.attendance') :
                       activeTab === 'history' ? $t('rewardSys.tabs.history') :
                       activeTab === 'champions' ? $t('rewardSys.tabs.champions') : 
                       activeTab === 'classroom_helper' ? 'Classroom Helper' :
                       activeTab === 'timer_random' ? 'Timer & Random' :
                       $t('rewardSys.tabs.settingsReports') 
                    }}
                </q-badge>
            </div>
             <!-- Header Info / Hint Area -->
             <div class="flex items-center ml-4 transition-all duration-300">
                 <!-- Default Hint (Only if no preview AND no selection) -->
                 <div v-if="!previewData && selectedIds.length === 0" class="flex items-center text-gray-400 text-xs italic">
                     <q-icon name="touch_app" size="xs" class="mr-1" />
                     mouse on or active card
                 </div>
                 
                 <!-- Student Preview Or Selection Info -->
                 <div v-else class="flex items-center gap-3 bg-gray-100 px-3 py-1 rounded-full border border-gray-200 shadow-sm animate-fade-in overflow-hidden">
                    <template v-if="previewData || (selectedIds.length > 0 && students.find(s => s.id === selectedIds[selectedIds.length-1]))">
                        <!-- Use previewData if available, otherwise fallback to last selected student -->
                        <q-avatar size="24px">
                           <img :src="(previewData || students.find(s => s.id === selectedIds[selectedIds.length-1]))?.avatar || 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent('<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'24\' height=\'24\' viewBox=\'0 0 24 24\'><rect width=\'24\' height=\'24\' fill=\'#e2e8f0\'/><text x=\'50%\' y=\'50%\' dy=\'.3em\' text-anchor=\'middle\' font-size=\'12\' fill=\'#64748b\'>ST</text></svg>')" 
                                @error="$event.target.src = 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent('<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'24\' height=\'24\' viewBox=\'0 0 24 24\'><rect width=\'24\' height=\'24\' fill=\'#e2e8f0\'/><text x=\'50%\' y=\'50%\' dy=\'.3em\' text-anchor=\'middle\' font-size=\'12\' fill=\'#64748b\'>ST</text></svg>')"
                           />
                        </q-avatar>
                        
                        <div class="flex flex-col leading-none overflow-hidden">
                           <span class="text-sm font-bold text-gray-900 truncate max-w-[150px]">{{ (previewData || students.find(s => s.id === selectedIds[selectedIds.length-1]))?.name }}</span>
                           <span v-if="(previewData || students.find(s => s.id === selectedIds[selectedIds.length-1]))?.name_ar" class="text-xs text-blue-800 font-arabic font-bold mt-0.5 truncate max-w-[150px]">{{ (previewData || students.find(s => s.id === selectedIds[selectedIds.length-1]))?.name_ar }}</span>
                        </div>

                        <div class="h-4 w-px bg-gray-300 mx-1 flex-shrink-0"></div>

                        <div class="bg-white px-2 py-0.5 rounded text-xs font-mono font-bold border border-gray-200 flex-shrink-0"
                            :class="((studentBehaviors[(previewData || students.find(s => s.id === selectedIds[selectedIds.length-1]))?.id]?.points_plus || 0) - (studentBehaviors[(previewData || students.find(s => s.id === selectedIds[selectedIds.length-1]))?.id]?.points_minus || 0)) >= 0 ? 'text-green-700' : 'text-red-700'">
                           {{ (studentBehaviors[(previewData || students.find(s => s.id === selectedIds[selectedIds.length-1]))?.id]?.points_plus || 0) - (studentBehaviors[(previewData || students.find(s => s.id === selectedIds[selectedIds.length-1]))?.id]?.points_minus || 0) }} Pts
                        </div>
                    </template>
                 </div>
             </div>

          </div>

          <!-- Right: Feedback Button (Moved from body) -->
          <div class="flex items-center gap-2">
              <q-btn-dropdown 
              color="primary" 
              icon="stars" 
              :label="$q.screen.gt.xs ? 'Give Feedback' : ''" 
              :disable="selectedIds.length === 0"
              class="shadow-sm glossy"
              content-class="rounded-xl"
              dense
            >
              <div style="width: 420px; max-width: 90vw;">
                <!-- Selected Students Summary -->
                <div class="bg-blue-50 p-2 border-b border-blue-100" v-if="selectedIds.length > 0">
                    <div class="text-xs font-bold text-blue-900 mb-1 flex justify-between items-center">
                        <span>Giving to {{ selectedIds.length }} student{{ selectedIds.length > 1 ? 's' : '' }}:</span>
                         <span class="text-gray-500 cursor-pointer hover:text-blue-700" @click="clearSelection">Clear</span>
                    </div>
                    <div class="flex flex-wrap gap-1 max-h-[60px] overflow-y-auto custom-scrollbar">
                        <q-chip 
                            v-for="id in selectedIds" 
                            :key="id" 
                            dense 
                            removable 
                            @remove="toggleSelected(id)"
                            @mouseenter="setPreview(students.find(s => s.id === id))"
                            @mouseleave="clearPreview"
                            color="white" 
                            text-color="primary" 
                            class="shadow-sm border border-blue-100 text-xs m-0"
                        >
                            <q-avatar size="16px">
                                <img :src="getAvatarUrl(students.find(s => s.id === id))">
                            </q-avatar>
                            <span class="max-w-[80px] ellipsis">{{ students.find(s => s.id === id)?.firstName }}</span>
                        </q-chip>
                    </div>
                </div>
                <q-tabs 
                  v-model="feedbackTab" 
                  class="text-grey-7 bg-grey-1" 
                  active-color="primary" 
                  indicator-color="primary" 
                  align="justify" 
                  narrow-indicator 
                  dense
                >
                  <q-tab name="positive" icon="thumb_up" label="Positive" class="text-weight-bold" />
                  <q-tab name="needs_work" icon="warning" label="Needs Work" class="text-weight-bold" />
                </q-tabs>

                <q-separator />

                <q-tab-panels v-model="feedbackTab" animated class="bg-white" style="max-height: 400px; overflow-y: auto;">
                  <q-tab-panel name="positive" class="q-pa-md">
                    <div class="row q-col-gutter-sm">
                      <div class="col-3 cursor-pointer" v-for="behavior in positiveBehaviors" :key="behavior.id" @click="applyBehaviorToStudents(behavior.id)">
                         <div v-close-popup class="column items-center justify-center q-pa-xs bg-white rounded-lg border border-gray-200 shadow-sm hover:shadow-md hover:border-blue-300 transition-all text-center h-full relative-position group hover:bg-blue-50" style="min-height: 80px">
                            <q-icon :name="behavior.icon || 'star'" size="1.2em" class="q-mb-xs text-orange-400" />
                            <div class="text-[9px] font-bold text-gray-700 leading-tight flex items-center justify-center px-1 text-center h-[2.5em] overflow-hidden">{{ behavior.name }}</div>
                            <div class="mt-1">
                                <span class="bg-green-50 text-green-700 text-[9px] font-bold px-1.5 py-0.5 rounded border border-green-100">+{{ behavior.points || behavior.value }}</span>
                            </div>
                         </div>
                      </div>
                    </div>
                  </q-tab-panel>

                  <q-tab-panel name="needs_work" class="q-pa-md">
                   <div class="row q-col-gutter-sm">
                      <div class="col-4 cursor-pointer" v-for="behavior in negativeBehaviors" :key="behavior.id" @click="applyBehaviorToStudents(behavior.id)">
                         <div v-close-popup class="column items-center q-pa-sm bg-red-50 rounded-xl border border-red-100 shadow-sm hover:shadow-md hover:bg-red-100 transition-all text-center h-full relative-position">
                            <q-icon :name="behavior.icon || 'warning'" size="2em" class="q-mb-xs text-red" />
                            <div class="text-caption text-weight-bold text-grey-9 ellipsis-2-lines leading-tight min-h-[2.5em] flex items-center justify-center">{{ behavior.name }}</div>
                            <div class="absolute-top-right q-ma-xs">
                                <q-badge color="red-1" text-color="red-9" class="text-xs font-bold border border-red-200 shadow-sm rounded-full px-1.5">{{ behavior.points || behavior.value }}</q-badge>
                            </div>
                         </div>
                      </div>
                   </div>
                  </q-tab-panel>
                </q-tab-panels>
              </div>
            </q-btn-dropdown>
          </div>
        </div>
      </div>
      
      <!-- Filter Badge Row (Secondary Sticky if needed, or just scrolling) -->
      <div v-if="activeTab === 'positive' && pointsDisplayMode !== 'session'" class="bg-orange-50 p-1 text-center border-b border-orange-100 text-xs text-orange-800 font-bold">
        Viewing: {{ pointsDisplayMode === 'overall' ? 'Overall (All Time)' : pointsDisplayMode === 'all_subjects' ? 'Overall (All Subjects)' :  pointsDisplayMode === 'from_now' ? 'From Now (Temporary)' : 'Competition (Week)' }}
      </div>

      <q-separator />

      <q-tab-panels v-model="activeTab" animated>
        <!-- ATTENDANCE TAB -->
        <q-tab-panel name="attendance">
          <div class="space-y-4">
            <div class="flex justify-between items-center mb-4">
              <h3 class="text-xl font-bold">{{ $t('rewardSys.attendance.manage') }}</h3>
              <div class="flex gap-2">
                <q-btn
                  color="positive"
                  :label="$t('rewardSys.attendance.markAllPresent')"
                  @click="markAllPresent"
                  :loading="bulkMarking"
                />
                <q-btn
                  color="warning"
                  :label="$t('rewardSys.attendance.markAllAbsent')"
                  @click="markAllAbsent"
                  :loading="bulkMarking"
                />
              </div>
            </div>

            <div class="flex flex-wrap justify-center gap-6 p-4 bg-gray-400">
              <StudentCard
                v-for="student in students"
                :key="student.id"
                :student="student"
                :disable-behavior="!studentAttendance[student.id]"
                :allow-disabled-click="true"
                :avatar-edit-enabled="avatarEditEnabled"
                :student-summary="{

                  positive: (studentBehaviors[student.id]?.points_plus || 0) - (pointsDisplayMode === 'from_now' && pointsBaseline[student.id] ? pointsBaseline[student.id].plus : 0),
                  negative: (studentBehaviors[student.id]?.points_minus || 0) - (pointsDisplayMode === 'from_now' && pointsBaseline[student.id] ? pointsBaseline[student.id].minus : 0),
                  total: ((studentBehaviors[student.id]?.points_plus || 0) - (pointsDisplayMode === 'from_now' && pointsBaseline[student.id] ? pointsBaseline[student.id].plus : 0)) - 
                         ((studentBehaviors[student.id]?.points_minus || 0) - (pointsDisplayMode === 'from_now' && pointsBaseline[student.id] ? pointsBaseline[student.id].minus : 0))
                }"
                @select="toggleAttendance(student.id)"
                @preview="setPreview"
                @leave="clearPreview"
              />
            </div>
          </div>
        </q-tab-panel>

        <!-- BEHAVIOR INCIDENTS TAB -->


        <!-- CLASSROOM HELPER TAB -->
        <q-tab-panel name="classroom_helper">
           <ClassroomHelper 
              :students="students"
              :student-behaviors="studentBehaviors"
              :student-attendance="studentAttendance"
              :period-info="{
                 date: selectedDate,
                 periodCode: periodCode,
                 classroomId: selectedClassroomId
              }"
           />
        </q-tab-panel>

        <!-- TIMER & RANDOM TAB -->
        <q-tab-panel name="timer_random">
           <TimerRandomTools :students="students" />
        </q-tab-panel>

        <!-- POSITIVE POINTS TAB -->
        <q-tab-panel name="positive">
          <div class=" ">
  


            <!-- Behavior Selection -->
       


            <!-- Student Grid with Action Buttons -->
            <!-- Teleporting Selection Controls to Header -->
            <div class="mt-6">
              <!-- Teleporting Selection Controls to Header -->
              <Teleport to="#dialog-header-actions">
                  <div class="row items-center q-gutter-x-sm">
                    
                    <!-- Selection Menu -->
                      <q-btn-dropdown
                        split
                        :color="selectedIds.length > 0 ? 'blue-6' : 'blue-1'"
                        :text-color="selectedIds.length > 0 ? 'white' : 'primary'"
                        icon="checklist"
                        :label="selectedIds.length > 0 ? $t('rewardSys.selected') + ': ' + selectedIds.length : 'Select'"
                        dense
                        rounded
                        unelevated
                        class="mr-2 transition-all duration-300"
                        content-style="border-radius: 12px"
                        @click="selectedIds.length > 0 ? openAttendanceList('selected') : selectAllPresent()"
                      >
                        <q-list dense style="min-width: 180px">
                          <q-item clickable v-close-popup @click="openAttendanceList('selected')">
                             <q-item-section>
                                 <q-item-label header class="text-xs text-uppercase text-gray-500 font-bold bg-gray-50 border-b cursor-pointer hover:bg-gray-100 transition-colors">
                                   {{ $t('rewardSys.selected') }}: {{ selectedIds.length }}
                                 </q-item-label>
                             </q-item-section>
                          </q-item>

                          <q-item clickable v-close-popup @click="copyStudentNames" :disable="selectedIds.length === 0">
                            <q-item-section avatar>
                              <q-icon name="content_copy" color="grey-8" />
                            </q-item-section>
                            <q-item-section>
                              <q-item-label>Copy Names</q-item-label>
                            </q-item-section>
                          </q-item>

                          <q-separator />

                          <q-item clickable v-close-popup @click="selectAllPresent">
                            <q-item-section avatar>
                              <q-icon name="check_box" color="primary" />
                            </q-item-section>
                            <q-item-section>
                              <q-item-label>{{ $t('rewardSys.selection.all') }}</q-item-label>
                            </q-item-section>
                          </q-item>
                          
                          <q-item clickable v-close-popup @click="inverseSelection">
                            <q-item-section avatar>
                              <q-icon name="swap_vert" color="orange" />
                            </q-item-section>
                            <q-item-section>
                              <q-item-label>{{ $t('rewardSys.selection.inv') }}</q-item-label>
                            </q-item-section>
                          </q-item>
                          
                          <q-separator />
                          
                          <q-item clickable v-close-popup @click="clearSelection" :disable="selectedIds.length === 0">
                            <q-item-section avatar>
                              <q-icon name="clear" color="negative" />
                            </q-item-section>
                            <q-item-section>
                              <q-item-label>{{ $t('rewardSys.selection.clear') }}</q-item-label>
                            </q-item-section>
                          </q-item>
                        </q-list>
                      </q-btn-dropdown>
                        <!-- Mode Switch -->
                         <q-toggle
                            v-model="feedbackMode"
                            :label="feedbackMode === 'individual' ? 'Individual' : 'Selection'"
                            true-value="individual"
                            false-value="selection"
                            checked-icon="person"
                            unchecked-icon="checklist"
                            color="green-13"
                            icon-color="white"
                            size="sm"
                            dense
                            class="mr-2 text-white font-bold"
                          />


                      </div>
                    </Teleport>
                    
                    <q-separator vertical inset class="mx-2" />

                    <!-- Attendance List Dialog -->
                    <q-dialog v-model="showAttendanceListDialog">
                        <q-card style="min-width: 350px">
                            <q-card-section class="row items-center">
                                <div class="text-h6">
                                    {{ attendanceListFilter === 'all' ? 'All Students' : attendanceListFilter === 'present' ? 'Present Students' : attendanceListFilter === 'selected' ? 'Selected Students' : 'Absent Students' }}
                                    <q-badge :color="attendanceListFilter === 'all' ? 'grey-7' : attendanceListFilter === 'present' ? 'positive' : attendanceListFilter === 'selected' ? 'blue' : 'negative'" class="ml-2">
                                        {{ displayedAttendanceList.length }}
                                    </q-badge>
                                </div>
                                <q-space />
                                <q-btn icon="close" flat round dense v-close-popup />
                            </q-card-section>

                            <q-card-section class="q-pt-none" style="max-height: 50vh; overflow-y: auto;">
                                <div v-if="displayedAttendanceList.length === 0" class="text-gray-500 italic text-center py-4">
                                    No students found.
                                </div>
                                <q-list bordered separator class="rounded-lg" v-else>
                                    <q-item v-for="student in displayedAttendanceList" :key="student.id">
                                        <q-item-section avatar>
                                            <q-avatar size="sm">
                                                <img :src="getAvatarUrl(student)" />
                                            </q-avatar>
                                        </q-item-section>
                                        <q-item-section>{{ student.name }}</q-item-section>
                                    </q-item>
                                </q-list>
                            </q-card-section>

                            <q-card-actions align="right" class="bg-gray-50">
                                <q-btn flat label="Close" color="primary" v-close-popup />
                                <q-btn 
                                    label="Copy List" 
                                    color="primary" 
                                    icon="content_copy" 
                                    @click="copyStudentNames"
                                    :disable="displayedAttendanceList.length === 0"
                                />
                            </q-card-actions>
                        </q-card>
                    </q-dialog>
                    
                    <!-- Action Buttons -->
                    <!-- Give Feedback Button Moved to Sticky Header -->
                     <q-btn
                        v-if="undoStack.length > 0"
                        dense
                        rounded
                        color="grey-8"
                        icon="undo"
                        label="Undo"
                        @click="performUndo"
                        class="ml-2 shadow-sm"
                        size="sm"
                    >
                        <q-tooltip>Undo last action</q-tooltip>
                    </q-btn>


              <!-- Name Filter Tags (First 2 chars) -->
              <div v-if="nameTags.length > 0" class="mb-4 overflow-x-auto whitespace-nowrap p-2 bg-white rounded-lg shadow-sm border border-gray-100 flex gap-2 custom-scrollbar items-center">
                <!-- ALL Filter -->
                <q-btn
                   unelevated
                   dense
                   rounded
                   size="md"
                   icon="filter_alt_off"
                   label="ALL"
                   :color="selectedNameTag === null ? 'grey-3' : 'grey-2'"
                   :text-color="selectedNameTag === null ? 'grey-8' : 'grey-6'"
                   class="px-4 font-bold"
                   @click="selectedNameTag = null"
                   :class="selectedNameTag === null ? 'bg-gray-200' : ''"
                />
                
                <!-- Selected Only Filter -->
                <q-btn
                   unelevated
                   dense
                   rounded
                   size="md"
                   icon="check_circle"
                   :color="selectedNameTag === '__SELECTED__' ? 'primary' : 'grey-2'"
                   :text-color="selectedNameTag === '__SELECTED__' ? 'white' : 'grey-8'"
                   class="px-3 font-bold"
                   @click="selectedNameTag = selectedNameTag === '__SELECTED__' ? null : '__SELECTED__'"
                >
                   <q-tooltip>Selected Only</q-tooltip>
                </q-btn>

                <!-- Not Selected Filter -->
                <q-btn
                   unelevated
                   dense
                   rounded
                   size="md"
                   icon="radio_button_unchecked"
                   :color="selectedNameTag === '__UNSELECTED__' ? 'primary' : 'grey-2'"
                   :text-color="selectedNameTag === '__UNSELECTED__' ? 'white' : 'grey-8'"
                   class="px-3 font-bold mr-2"
                   @click="selectedNameTag = selectedNameTag === '__UNSELECTED__' ? null : '__UNSELECTED__'"
                >
                   <q-tooltip>Not Selected</q-tooltip>
                </q-btn>
                
                <!-- Individual Tags -->
                <q-btn
                   v-for="tag in nameTags"
                   :key="tag"
                   unelevated
                   dense
                   round
                   size="md"
                   :label="tag"
                   :color="selectedNameTag === tag ? 'primary' : 'grey-1'"
                   :text-color="selectedNameTag === tag ? 'white' : 'grey-7'"
                   @click="toggleNameTag(tag)"
                   class="font-bold shadow-sm"
                   :class="selectedNameTag === tag ? 'shadow-md scale-110' : 'hover:bg-gray-200'"
                />
              </div>



              <!-- Student Grid -->
              <div v-for="(group, index) in organizedStudents" :key="index" class="mb-6 bg-gray-400">
                <!-- Group Header (only if groups exist) -->
                <div v-if="group.name" class="mb-3 p-3 bg-gradient-to-r from-indigo-50 to-purple-50 rounded-lg border border-indigo-200">
                  <div class="flex items-center justify-between">
                    <h4 class="font-bold text-indigo-900 flex items-center gap-2">
                      <q-icon name="groups" />
                      {{ group.name }}
                      <q-badge :label="group.students.length" color="indigo" />
                    </h4>
                    <div class="flex gap-2">
                      <q-btn
                        dense
                        size="sm"
                        color="indigo"
                        icon="check_box"
                        :label="$t('rewardSys.selection.all')"
                        @click="selectGroupStudents(group.students, true)"
                        outline
                      />
                      <q-btn
                        dense
                        size="sm"
                        color="grey"
                        icon="check_box_outline_blank"
                        :label="$t('rewardSys.selection.clear')"
                        @click="selectGroupStudents(group.students, false)"
                        outline
                      />
                    </div>
                  </div>
                </div>
                
                <!-- Student Cards -->
                <div class="flex flex-wrap justify-center gap-6">
                  <StudentCard
                    v-for="student in group.students"
                    :key="student.id"
                    :student="student"
                    :selected="selectedIds.includes(student.id)"
                    :selected-id="selectedIds.includes(student.id) ? student.id : null"
                    :disable-behavior="!studentAttendance[student.id]"
                    :is-absent="!studentAttendance[student.id]"
                    :allow-disabled-click="false"
                    :avatar-edit-enabled="avatarEditEnabled"
                    :overall-points="(studentBehaviors[student.id]?.points_plus || 0) - (studentBehaviors[student.id]?.points_minus || 0)"
                    :student-summary="{

                      positive: (studentBehaviors[student.id]?.points_plus || 0) - (pointsDisplayMode === 'from_now' && pointsBaseline[student.id] ? pointsBaseline[student.id].plus : 0),
                      negative: (studentBehaviors[student.id]?.points_minus || 0) - (pointsDisplayMode === 'from_now' && pointsBaseline[student.id] ? pointsBaseline[student.id].minus : 0),
                      total: ((studentBehaviors[student.id]?.points_plus || 0) - (pointsDisplayMode === 'from_now' && pointsBaseline[student.id] ? pointsBaseline[student.id].plus : 0)) - 
                             ((studentBehaviors[student.id]?.points_minus || 0) - (pointsDisplayMode === 'from_now' && pointsBaseline[student.id] ? pointsBaseline[student.id].minus : 0))
                    }"
                    @select="onStudentCardClick(student.id)"
                    @preview="setPreview"
                    @leave="clearPreview"
                  />
                </div>
              </div>
            </div>

          </div>
        </q-tab-panel>

        <!-- NEGATIVE POINTS TAB -->
        <q-tab-panel name="negative">
          <div class="space-y-4">
            <div class="mb-4">
              <h3 class="text-xl font-bold mb-2">{{ $t('rewardSys.points.deduct') }}</h3>
              <p class="text-sm text-gray-600">{{ $t('rewardSys.points.selectStudentsNegative') }}</p>
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
                  :placeholder="$t('rewardSys.points.selectNegativeBehavior')"
                  emit-value
                  map-options
                >
                  <template v-slot:option="scope">
                    <q-item v-bind="scope.itemProps">
                      <q-item-section>
                        <q-item-label>{{ getBehaviorName(scope.opt) }}</q-item-label>
                        <q-item-label caption>{{ scope.opt.value || scope.opt.points || 0 }} points</q-item-label>
                      </q-item-section>
                    </q-item>
                  </template>
                </q-select>
                <q-btn
                  color="negative"
                  icon="remove_circle"
                  :label="$t('rewardSys.points.applyToSelected')"
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
              <h3 class="text-xl font-bold">{{ $t('rewardSys.history.title') }}</h3>
              <q-btn
                color="primary"
                icon="refresh"
                :label="$t('rewardSys.history.refresh')"
                @click="loadHistory"
                :loading="loadingHistory"
              />
            </div>

            <div v-if="!recentActions.length" class="text-center py-8">
              <p class="text-gray-500 text-lg">{{ $t('rewardSys.history.empty') }}</p>
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
                    <span class="font-bold text-lg">{{ action.student_behavior?.student?.name || $t('rewardSys.history.unknown') }}</span>
                    <span class="text-2xl">{{ action.value > 0 ? '⭐' : '⚠️' }}</span>
                  </div>
                  <p class="text-sm text-gray-700">
                    <strong>{{ getBehaviorName(action.behavior) || $t('rewardSys.history.unknownBehavior') }}</strong>
                    <span :class="action.value > 0 ? 'text-green-600' : 'text-red-600'">
                      ({{ action.value > 0 ? '+' : '' }}{{ action.value }} {{ $t('rewardSys.points.title') }})
                    </span>
                  </p>
                  <p class="text-xs text-gray-500 mt-1">
                    {{ formatDateTime(action.created_at) }} by {{ action.created_by?.name || $t('rewardSys.history.unknown') }}
                  </p>
                  <p v-if="action.note" class="text-xs text-gray-600 mt-1 italic">{{ $t('rewardSys.history.note') }} {{ action.note }}</p>
                  <p v-if="action.canceled" class="text-xs text-red-600 mt-1">
                    ❌ {{ $t('rewardSys.history.canceled') }} {{ action.cancel_reason }} ({{ formatDateTime(action.canceled_at) }})
                  </p>
                </div>
                <q-btn
                  v-if="!action.canceled"
                  color="warning"
                  icon="undo"
                  :label="$t('rewardSys.history.undo')"
                  size="sm"
                  @click="undoAction(action.id)"
                  :loading="undoingAction === action.id"
                />
              </div>
            </div>
          </div>
        </q-tab-panel>

        <!-- BEHAVIORS MANAGEMENT TAB -->
        <!-- SETTINGS AND REPORTS TAB -->
        <q-tab-panel name="settings_reports" class="p-4">
          <div class="flex flex-col h-full gap-4"> 
            
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <q-tabs 
                    v-model="settingsTab" 
                    align="left"
                    class="text-gray-600 bg-gray-50 border-b border-gray-200"
                    active-color="primary"
                    indicator-color="primary"
                    dense
                >
                    <q-tab name="behavior_incidents" icon="report_problem" :label="$t('rewardSys.tabs.behaviorIncidents')" />
                    <q-tab name="behaviors" icon="settings" :label="$t('rewardSys.tabs.behaviors')" />
                </q-tabs>

                <q-tab-panels v-model="settingsTab" animated class="bg-transparent">
                    <!-- BEHAVIOR INCIDENTS SUB-PANEL -->
                    <q-tab-panel name="behavior_incidents">
                      <BehaviorIncidents
                        :students="students"
                        :classroom-id="selectedClassroomId"
                        :date="selectedDate"
                        :period-code="periodCode"
                        @incident-recorded="handleIncidentRecorded"
                      />
                    </q-tab-panel>

                    <!-- BEHAVIORS SUB-PANEL -->
                    <q-tab-panel name="behaviors">
                      <div class="space-y-6">
                        <!-- Header -->
                        <div class="flex justify-between items-center mb-6">
                          <h3 class="text-2xl font-bold text-gray-800">{{ $t('rewardSys.behaviors.management') }}</h3>
                          <q-btn
                            color="primary"
                            icon="add"
                            :label="$t('rewardSys.behaviors.addNew')"
                            @click="openBehaviorForm(null)"
                            size="md"
                            class="shadow-md"
                          />
                        </div>

                        <!-- Positive Behaviors Section -->
                        <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl p-6 border border-green-200">
                          <div class="flex items-center gap-3 mb-4">
                            <q-icon name="add_circle" size="md" color="positive" />
                            <h4 class="text-xl font-bold text-green-800">{{ $t('rewardSys.behaviors.positiveSection') }}</h4>
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
                                    <h5 class="font-bold text-gray-800">{{ getBehaviorName(behavior) }}</h5>
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
                            <p>{{ $t('rewardSys.behaviors.noPositive') }}</p>
                          </div>
                        </div>

                        <!-- Negative Behaviors Section -->
                        <div class="bg-gradient-to-r from-red-50 to-rose-50 rounded-xl p-6 border border-red-200">
                          <div class="flex items-center gap-3 mb-4">
                            <q-icon name="remove_circle" size="md" color="negative" />
                            <h4 class="text-xl font-bold text-red-800">{{ $t('rewardSys.behaviors.negativeSection') }}</h4>
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
                                    <h5 class="font-bold text-gray-800">{{ getBehaviorName(behavior) }}</h5>
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
                            <p>{{ $t('rewardSys.behaviors.noNegative') }}</p>
                          </div>
                        </div>
                      </div>
                    </q-tab-panel>
                </q-tab-panels>
            </div>
          </div>
        </q-tab-panel>
      </q-tab-panels>
    </q-card>

    <!-- Empty State -->
    <q-card v-if="!students.length" class="shadow-lg rounded-2xl">
      <q-card-section class="text-center py-12">
        <p class="text-2xl font-semibold text-gray-600">📚 {{ $t('rewardSys.messages.selectClassroom') }}</p>
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
            :school-logo="schoolLogo"
          />
        </q-card-section>
        <q-card-actions align="right">
          <q-btn flat label="Close" color="primary" v-close-popup />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Behavior Selection Dialog -->
    <q-dialog v-model="showBehaviorDialog" full-width maxWidth="1200px">
      <q-card class="flex flex-col md:flex-row h-[90vh] md:h-[80vh] overflow-hidden">
        
        <!-- Left Column: Selected Students (Collapsible/Condensed on Mobile) -->
        <q-card-section class="w-full md:w-1/4 bg-blue-50 border-r border-blue-100 flex flex-col p-0 order-2 md:order-1 transition-all"
             :class="{'h-16 overflow-hidden md:h-auto': $q.screen.lt.md && !expandStudentList}">
          
          <!-- Header (Click to expand on mobile) -->
          <div class="p-3 border-b border-blue-200 bg-blue-100/50 flex justify-between items-center cursor-pointer md:cursor-default"
               @click="$q.screen.lt.md ? expandStudentList = !expandStudentList : null">
            <h4 class="font-bold text-blue-900 flex items-center gap-2 text-base md:text-lg">
              <q-icon name="checklist" />
              {{ $t('rewardSys.selected') }} ({{ selectedIds.length }})
            </h4>
            <div class="md:hidden">
                 <q-icon :name="expandStudentList ? 'expand_less' : 'expand_more'" color="primary" />
            </div>
          </div>

          <div class="flex-1 overflow-y-auto p-3 custom-scrollbar" v-show="!$q.screen.lt.md || expandStudentList">
            <div v-if="selectedIds.length === 0" class="text-center py-4 text-gray-400 italic text-sm">
              {{ $t('rewardSys.messages.clickToSelect') }}
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
                <q-avatar icon="person" color="primary" text-color="white" font-size="12px" />
                <div class="flex flex-col leading-tight overflow-hidden w-full">
                  <span class="font-bold text-sm md:text-base truncate">{{ students.find(s => s.id === id)?.firstName }}
                    <!-- <span class="text-xs opacity-80 truncate hidden md:inline">{{ students.find(s => s.id === id)?.lastName }}</span> -->
                  </span>
                </div>
              </q-chip>
            </div>
          </div>

          <!-- Selection Tools Footer (Hidden on mobile if collapsed) -->
          <div class="p-2 border-t border-blue-200 bg-white" v-show="!$q.screen.lt.md || expandStudentList">
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
        <div class="flex-1 flex flex-col bg-white order-1 md:order-2 h-full overflow-hidden">
          
          <!-- Student Name Header (Mobile/Desktop) - Only if 1 student -->
          <div v-if="selectedIds.length === 1" class="bg-white border-b border-gray-100 p-3 flex justify-center items-center">
             <div class="flex items-center gap-2">
                <!-- <q-avatar size="32px">
                   <img :src="getAvatarUrl(students.find(s => s.id === selectedIds[0]))" />
                </q-avatar> -->
                <div class="text-lg font-bold text-gray-800">
                   {{ students.find(s => s.id === selectedIds[0])?.name }}
                </div>
             </div>
          </div>

          <q-card-section :class="behaviorDialogMode === 'positive' ? 'bg-green-50 border-b border-green-100' : 'bg-red-50 border-b border-red-100'" 
             class="flex flex-wrap justify-between items-center py-2 px-3 gap-2 shrink-0">
            
            <div class="font-bold flex items-center gap-2 transition-colors duration-300" 
                 :class="behaviorDialogMode === 'positive' ? 'text-green-800' : 'text-red-800'">
                 <!-- Hide text on very small screens if needed, or make smaller -->
              <span class="text-base md:text-xl flex items-center gap-2">
                  <q-icon :name="behaviorDialogMode === 'positive' ? 'emoji_events' : 'warning'" size="sm" />
                  <span class="hidden xs:inline">{{ behaviorDialogMode === 'positive' ? $t('rewardSys.points.positiveTitle') : $t('rewardSys.points.negativeTitle') }}</span>
              </span>
            </div>

            <div class="bg-white/60 p-1 rounded-full flex gap-1 shadow-sm border border-white/60 backdrop-blur-sm">
               <q-btn
                  unelevated
                  rounded
                  dense
                  :color="behaviorDialogMode === 'positive' ? 'positive' : 'transparent'"
                  :text-color="behaviorDialogMode === 'positive' ? 'white' : 'grey-7'"
                  class="px-3 md:px-4 transition-all duration-300 text-xs md:text-sm"
                  @click="behaviorDialogMode = 'positive'"
               >
                  <q-icon name="thumb_up" size="xs" class="mr-1" />
                  <span class="hidden sm:inline">Pos</span>
               </q-btn>
               <q-btn
                  unelevated
                  rounded
                  dense
                  :color="behaviorDialogMode === 'negative' ? 'negative' : 'transparent'"
                  :text-color="behaviorDialogMode === 'negative' ? 'white' : 'grey-7'"
                  class="px-3 md:px-4 transition-all duration-300 text-xs md:text-sm"
                  @click="behaviorDialogMode = 'negative'"
               >
                  <q-icon name="thumb_down" size="xs" class="mr-1" />
                  <span class="hidden sm:inline">Neg</span>
               </q-btn>
            </div>
          </q-card-section>

          <q-card-section class="flex-1 overflow-y-auto p-2 md:p-4 custom-scrollbar bg-gray-50/50">
            <!-- Responsive Grid: 2 cols on mobile, 3 on sm, 4 on md/lg -->
            <div class="flex flex-wrap gap-2 justify-center md:gap-3 pb-20 md:pb-0">
              <div
                v-for="behavior in (behaviorDialogMode === 'positive' ? positiveBehaviors : negativeBehaviors)"
                :key="behavior.id"
                class="cursor-pointer transition-all duration-200 relative group touch-manipulation w-24"
                @click="selectedBehaviorIdForDialog = behavior.id"
              >
                <div 
                  class="h-full p-2 md:p-3 rounded-xl border flex flex-col items-center text-center gap-1 md:gap-2 active:scale-95 transition-transform bg-white shadow-sm"
                  :class="[
                    selectedBehaviorIdForDialog === behavior.id
                      ? (behaviorDialogMode === 'positive' ? 'border-green-500 bg-green-50 ring-2 ring-green-200' : 'border-red-500 bg-red-50 ring-2 ring-red-200')
                      : 'border-gray-200 hover:border-gray-300 hover:shadow-md'
                  ]"
                >
                  <div 
                    class="text-2xl md:text-3xl p-2 rounded-full mb-0 md:mb-1"
                    :class="behaviorDialogMode === 'positive' ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600'"
                  >
                    {{ behavior.icon || (behaviorDialogMode === 'positive' ? '⭐' : '⚠️') }}
                  </div>
                  
                  <div class="font-bold text-sm md:text-base leading-tight text-gray-800 line-clamp-2 md:line-clamp-3 w-full">
                    {{ getBehaviorName(behavior) }}
                  </div>
                  
                  <div 
                    class="text-sm md:text-lg font-bold px-2 md:px-3 py-0.5 md:py-1 rounded-full mt-auto"
                    :class="behaviorDialogMode === 'positive' ? 'bg-green-200 text-green-900' : 'bg-red-200 text-red-900'"
                  >
                    {{ behaviorDialogMode === 'positive' ? '+' : '' }}{{ behavior.value || behavior.points || 0 }}
                  </div>

                  <!-- Selected Checkmark -->
                  <div 
                    v-if="selectedBehaviorIdForDialog === behavior.id"
                    class="absolute top-1 right-1 md:top-2 md:right-2 text-lg md:text-xl drop-shadow-sm"
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
              :label="$t('rewardSys.tabs.cancel')" 
              v-close-popup 
              color="grey-7" 
              size="lg"
              class="font-bold"
            />
            <q-btn 
              :color="behaviorDialogMode === 'positive' ? 'positive' : 'negative'"
              :label="behaviorDialogMode === 'positive' ? $t('rewardSys.points.applyPositive') : $t('rewardSys.points.applyNegative')"
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
      <q-card class="min-w-[500px]">
        <q-card-section class="bg-primary text-white">
          <div class="flex justify-between items-center">
            <div class="text-h6">{{ editingBehavior ? $t('rewardSys.behaviors.edit') : $t('rewardSys.behaviors.create') }}</div>
            <q-badge v-if="editingBehavior && !editingBehavior.teacher_id" color="blue" :label="'🔒 ' + $t('rewardSys.behaviors.schoolDefault')" />
          </div>
        </q-card-section>

        <q-card-section class="q-pt-md space-y-4">
          <!-- Warning for School Defaults -->
          <q-banner v-if="editingBehavior && !editingBehavior.teacher_id" class="bg-blue-50 text-blue-900" rounded dense>
            <template v-slot:avatar>
              <q-icon name="info" color="blue" />
            </template>
            {{ $t('rewardSys.behaviors.readOnlyWarning') }}
          </q-banner>

          <!-- English Name -->
          <q-input
            v-model="behaviorForm.name"
            :label="'🇬🇧 ' + $t('rewardSys.behaviors.form.nameEn') + ' *'"
            outlined
            dense
            :rules="[val => !!val || 'Name is required']"
            :readonly="editingBehavior && !editingBehavior.teacher_id"
          >
            <template v-slot:prepend>
              <q-icon name="translate" />
            </template>
          </q-input>

          <!-- Arabic Name -->
          <q-input
            v-model="behaviorForm.name_ar"
            :label="'🇸🇦 ' + $t('rewardSys.behaviors.form.nameAr')"
            outlined
            dense
            dir="rtl"
            :readonly="editingBehavior && !editingBehavior.teacher_id"
          >
            <template v-slot:prepend>
              <q-icon name="translate" />
            </template>
          </q-input>

          <q-select
            v-model="behaviorForm.type"
            :options="[
              { label: $t('rewardSys.behaviors.form.types.positive'), value: 'positive' },
              { label: $t('rewardSys.behaviors.form.types.negative'), value: 'negative' }
            ]"
            :label="$t('rewardSys.behaviors.form.type') + ' *'"
            outlined
            dense
            emit-value
            map-options
            :rules="[val => !!val || 'Type is required']"
            :readonly="editingBehavior && !editingBehavior.teacher_id"
          />

          <q-input
            v-model.number="behaviorForm.points"
            :label="$t('rewardSys.behaviors.form.points') + ' *'"
            type="number"
            outlined
            dense
            :rules="[val => val !== null && val !== '' || 'Points are required']"
            :hint="behaviorForm.type === 'positive' ? 'Positive number (e.g., 5)' : 'Negative number (e.g., -3)'"
            :readonly="editingBehavior && !editingBehavior.teacher_id"
          />

          <q-input
            v-model="behaviorForm.icon"
            :label="$t('rewardSys.behaviors.form.icon')"
            outlined
            dense
            hint="Optional emoji, e.g., ⭐ 🎉 ⚠️ 🚫"
            :readonly="editingBehavior && !editingBehavior.teacher_id"
          />
        </q-card-section>

        <q-card-actions align="right" class="q-px-md q-pb-md">
          <q-btn flat :label="$t('rewardSys.tabs.cancel')" v-close-popup />
          <q-btn
            v-if="!editingBehavior || editingBehavior.teacher_id"
            color="primary"
            :label="editingBehavior ? $t('common.update') : $t('common.create')"
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

    <!-- Points Display Settings Dialog -->
    <q-dialog v-model="showPointsSettings">
      <PointsDisplaySettings
        :mode="pointsDisplayMode"
        :competition-start-time="competitionStartTime"
        :date-from="customDateFrom"
        :date-to="customDateTo"
        :leaderboard-mode="leaderboardMode"
        @update:mode="pointsDisplayMode = $event"
        @update:competitionStartTime="competitionStartTime = $event"
        @update:dateFrom="customDateFrom = $event"
        @update:dateTo="customDateTo = $event"
        @update:leaderboardMode="leaderboardMode = $event"
        @apply="applyPointsSettings"
        @reset="resetPointsSettings"
      />
    </q-dialog>

    <!-- Friendly Feedback Confirmation Dialog -->
    <q-dialog v-model="showFeedbackConfirmation" position="top">
       <q-card style="min-width: 350px; border-radius: 20px;" class="q-ma-md bg-deep-purple-8 text-white shadow-2xl">
          <q-card-section class="column items-center text-center q-pa-lg">
             <q-avatar size="80px" class="q-mb-md shadow-lg border-4 border-white">
                <img :src="getAvatarUrl(lastFeedbackDetails.student)" v-if="lastFeedbackDetails.student && lastFeedbackDetails.student.avatar" />
                <q-icon name="person" v-else />
             </q-avatar>
             
             <div class="text-h5 font-bold mb-1">
                 {{ lastFeedbackDetails.student?.name }}
             </div>
             
             <div class="text-h3 font-black my-2 flex items-center gap-2 animate-bounce">
                <span :class="lastFeedbackDetails.points > 0 ? 'text-green-300' : 'text-red-300'">
                    {{ lastFeedbackDetails.points > 0 ? '+' : '' }}{{ lastFeedbackDetails.points }}
                </span>
                <span class="text-4xl">{{ lastFeedbackDetails.behavior?.icon || '⭐' }}</span>
             </div>
             
             <div class="text-subtitle1 opacity-90 font-medium bg-white/20 px-4 py-1 rounded-full">
                {{ getBehaviorName(lastFeedbackDetails.behavior) }}
             </div>
          </q-card-section>
       </q-card>
    </q-dialog>
  </div>
</template>

<script setup>
import { ref, watch, onMounted, computed, defineProps, defineAsyncComponent } from 'vue' // update import

const props = defineProps({
  isDialog: {
    type: Boolean,
    default: false
  },
  classroomId: {
    type: Number,
    default: null
  },
  subjectId: {
    type: Number,
    default: null
  },
  period: {
    type: Number,
    default: null
  },
  date: {
    type: String,
    default: null
  },
  week: {
    type: Number,
    default: null
  },
  initialTab: {
    type: String,
    default: 'positive'
  }
})

import { useQuasar } from 'quasar'
import { useI18n } from 'vue-i18n'
import { usePage } from '@inertiajs/vue3'
import axios from 'axios'
import rewardPointService from './reward_sys_comp/reward_sys_point_action.js'
import CompactSessionHeader from './reward_sys_comp/CompactSessionHeader.vue'
import PointsDisplaySettings from './reward_sys_comp/PointsDisplaySettings.vue'
import TimerRandomTools from './reward_sys_comp/TimerRandomTools.vue'
import ClassroomHelper from './reward_sys_comp/ClassroomHelper.vue'
// Lazy-loaded heavy components (loaded on-demand)
const TopLeaderboard = defineAsyncComponent(() => import('./reward_sys_comp/TopLeaderboard.vue'))
const BehaviorIncidents = defineAsyncComponent(() => import('./reward_sys_comp/BehaviorIncidents.vue'))
const StudentGrouping = defineAsyncComponent(() => import('./reward_sys_comp/StudentGrouping.vue'))

// Static imports for lightweight, always-visible components
import card2 from './final/card2.vue'; // Adjust path as needed
import card3 from './final/card3.vue'; // Adjust path as needed
import StudentCard from './reward_sys_comp/StudentCard.vue'
import noise from './final/noise.vue'; // Adjust path as needed

// import pdf_main from './final/pdf_main.vue'
// import PDFAnnotatorMain from './final/PDFAnnotatorMain.vue'
// import video_player from './final/video_player.vue'
// import video_player2 from './final/video_player2.vue'
// import draw from './final/draw.vue'
// import draw2 from './final/draw2.vue'
// import draw3 from './final/draw3.vue'

// Import audio manager utility for lazy loading
import { playSound as playSoundUtil, preloadSoundsWhenIdle } from '@/utils/audioManager'

// Sound files
const soundFiles = {
  select: '/audio/click-234708.mp3',
  reward: '/audio/purchase-success-384963.mp3',
  penalty: '/audio/error-010-206498.mp3'
}

const bgMusic = ref(null)
const isMusicEnabled = ref(false)

// Initialize music settings
onMounted(() => {
  // Preload sound effects when browser is idle (lazy loading)
  preloadSoundsWhenIdle(soundFiles)

  const storedMusicSetting = localStorage.getItem('reward-system-bg-music')
  isMusicEnabled.value = storedMusicSetting === 'true'
  
  if (bgMusic.value) {
    bgMusic.value.volume = 0.1 // Set low volume
    if (isMusicEnabled.value) {
      bgMusic.value.play().catch(e => console.log('Autoplay prevented:', e))
    }
  }
})

watch(isMusicEnabled, (newValue) => {
  localStorage.setItem('reward-system-bg-music', newValue)
  if (bgMusic.value) {
    if (newValue) {
      bgMusic.value.play().catch(e => console.log('Playback error:', e))
    } else {
      bgMusic.value.pause()
    }
  }
})

// Use audioManager utility for sound effects
const playSound = (type) => {
  playSoundUtil(type, soundFiles)
}

const { t, locale } = useI18n()

const getBehaviorName = (behavior) => {
  if (!behavior) return ''
  return (locale.value === 'ar' && behavior.name_ar) ? behavior.name_ar : behavior.name
}

const page = usePage()
const schoolLogo = computed(() => {
   const schools = page.props.auth?.user?.school
   if (Array.isArray(schools) && schools.length > 0) {
     return schools[0].logo_url
   }
   return null
})

// Helper function to get localized subject name
const getSubjectName = (classroom) => {
  if (!classroom) return ''
  return locale.value === 'ar' && classroom.subject_name_ar ? classroom.subject_name_ar : classroom.subject_name
}

// Computed: Extract unique subjects from classrooms
const subjects = computed(() => {
  const subjectMap = new Map()
  classrooms.value.forEach(classroom => {
    if (classroom.subject_id && !subjectMap.has(classroom.subject_id)) {
      subjectMap.set(classroom.subject_id, {
        id: classroom.subject_id,
        name: classroom.subject_name,
        name_ar: classroom.subject_name_ar
      })
    }
  })
  return Array.from(subjectMap.values())
})

// Computed: Group classrooms by subject
const classroomsBySubject = computed(() => {
  if (!selectedSubjectId.value) return []
  return classrooms.value.filter(c => c.subject_id === selectedSubjectId.value)
})

// Attendance Summary Computed Property
const attendanceSummary = computed(() => {
  const total = students.value.length
  if (total === 0) return { present: 0, absent: 0, absentList: [] }
  
  const presentCount = students.value.filter(s => studentAttendance.value[s.id]).length
  const absentStudents = students.value.filter(s => !studentAttendance.value[s.id])
  
  return {
    present: presentCount,
    absent: total - presentCount,
    absentList: absentStudents
  }
})

// Function to copy absent students list to clipboard
const copyToClipboard = () => {
  const absentees = attendanceSummary.value.absentList
    .map(s => locale.value === 'ar' && s.name_ar ? s.name_ar : s.name)
    .join('\n')
  
  if (!absentees) return

  navigator.clipboard.writeText(absentees).then(() => {
    $q.notify({
      message: t('rewardSys.session.copied'),
      color: 'positive',
      icon: 'content_copy',
      position: 'top'
    })
  }).catch(err => {
    console.error('Failed to copy: ', err)
  })
}

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
const activeTab = ref(props.initialTab || 'positive')
const feedbackTab = ref('positive')

// Watch initialTab prop
watch(() => props.initialTab, (val) => {
    if (val) activeTab.value = val
})
const settingsTab = ref('behavior_incidents')

// Watch for tab changes and clear selection
watch(activeTab, (newTab, oldTab) => {
  localStorage.setItem('reward-system-active-tab', newTab)
  if (newTab !== oldTab) {
    selectedIds.value = []
  }
})
const classrooms = ref([])
const students = ref([])
const behaviors = ref([])
const selectedClassroomId = ref(null)
const selectedSubjectId = ref(null) // For subject tab selection
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
const isInitialized = ref(false) // Track if component has been initialized

const avatarEditEnabled = ref(false)
const showBehaviorDialog = ref(false)
const behaviorDialogMode = ref('positive') // 'positive' or 'negative'
const selectedBehaviorIdForDialog = ref(null)

// Grouping state
const selectedLayout = ref('no_groups') // 'no_groups', 'name_asc', 'name_desc', or layout ID
const savedLayouts = ref([]) // Will be loaded from classroom_subject_teachers.data
const showGroupEditor = ref(false)
const editingLayout = ref(null)

// Points Display Settings state
const showPointsSettings = ref(false)
const pointsDisplayMode = ref(localStorage.getItem('points-display-mode') || 'overall') // 'overall' | 'session' | 'competition' | 'custom' | 'from_now'
const pointsBaseline = ref({}) // Stores starting points for 'from_now' mode { student_id: { plus: 0, minus: 0 } }
const competitionStartTime = ref(localStorage.getItem('competition-start-time') || null)
const customDateFrom = ref(localStorage.getItem('custom-date-from') || null)
const customDateTo = ref(localStorage.getItem('custom-date-to') || null)
const leaderboardMode = ref(localStorage.getItem('leaderboard-mode') || 'top5') // 'top5' | 'top10' | 'groups'
const feedbackMode = ref('individual') // 'individual' | 'selection'

// Read Aloud Setting
const isReadAloudEnabled = ref(localStorage.getItem('reward-system-read-aloud') !== 'false') // Default true
watch(isReadAloudEnabled, (newValue) => {
  localStorage.setItem('reward-system-read-aloud', newValue)
})

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

// Visual Confirmation State
const showFeedbackConfirmation = ref(false)
const lastFeedbackDetails = ref({ student: null, behavior: null, points: 0 })
const expandStudentList = ref(false)

// Header Message State (Inline Notifications)
const headerMessage = ref(null)
const headerMessageTimer = ref(null)

function showHeaderMessage(text, duration = 3000) {
    headerMessage.value = text
    if (headerMessageTimer.value) clearTimeout(headerMessageTimer.value)
    
    headerMessageTimer.value = setTimeout(() => {
        headerMessage.value = null
    }, duration)
}


function onStudentCardClick(studentId) {
  if (activeTab.value === 'attendance') {
    toggleAttendance(studentId)
    return
  }

  // If in selection mode, just toggle selection
  if (feedbackMode.value === 'selection') {
    toggleSelected(studentId)
    return
  }

  // If in individual mode:
  // 1. Set selectedIds to just this student (so underlying logic works)
  selectedIds.value = [studentId]
  
  // 2. Open behavior dialog immediately
  openBehaviorDialog('positive') // Default to positive, or maybe ask? Positive is better default.
}

// Watch selectedSubjectId and save to localStorage
watch(selectedSubjectId, (newValue) => {
  if (newValue !== null) {
    localStorage.setItem('reward-system-selected-subject-id', newValue.toString())
    console.log(`💾 Saved subject to localStorage: ${newValue}`)
  }
})

// Watch selectedClassroomId and save to localStorage
watch(selectedClassroomId, (newValue) => {
  if (newValue !== null) {
    localStorage.setItem('reward-system-selected-classroom-id', newValue.toString())
    console.log(`💾 Saved classroom to localStorage: ${newValue}`)
  }
})

// Watch period code components and save to localStorage
watch(selectedSemester, (newValue) => {
  if (newValue !== null) {
    localStorage.setItem('reward-system-selected-semester', newValue.toString())
    console.log(`💾 Saved semester to localStorage: ${newValue}`)
  }
})

watch(selectedWeek, (newValue) => {
  if (newValue !== null) {
    localStorage.setItem('reward-system-selected-week', newValue.toString())
    console.log(`💾 Saved week to localStorage: ${newValue}`)
  }
})

watch(selectedDay, (newValue) => {
  if (newValue !== null) {
    localStorage.setItem('reward-system-selected-day', newValue.toString())
    console.log(`💾 Saved day to localStorage: ${newValue}`)
  }
})

watch(selectedPeriodNumber, (newValue) => {
  if (newValue !== null) {
    localStorage.setItem('reward-system-selected-period-number', newValue.toString())
    console.log(`💾 Saved period number to localStorage: ${newValue}`)
  }
})

// Watch for date changes to update day (moved here after declarations)
watch(selectedDate, (newDate) => {
  if (newDate) {
    const d = new Date(newDate)
    selectedDay.value = d.getDay() + 1
    console.log(`📅 Date changed to ${newDate}, Day updated to ${selectedDay.value}`)
  }
})

// Computed period code generator
const periodCode = computed(() => {
  return `${selectedSemester.value}.${selectedWeek.value}.${selectedDay.value}.${selectedPeriodNumber.value}`
})

const classroomName = computed(() => {
  const c = classrooms.value.find(cw => cw.classroom_id === selectedClassroomId.value)
  return c ? c.classroom_name : t('rewardSys.session.unknownClass')
})

const fullSubjectName = computed(() => {
  const s = subjects.value.find(sub => sub.subject_id === selectedSubjectId.value)
  return s ? (locale.value === 'ar' && s.subject_name_ar ? s.subject_name_ar : s.subject_name) : ''
})

// Attendance Summary Counts
const presentCount = computed(() => {
  return students.value.filter(s => studentAttendance.value[s.id]).length
})

const absentCount = computed(() => {
  return students.value.filter(s => !studentAttendance.value[s.id]).length
})

const showAttendanceListDialog = ref(false)
const attendanceListFilter = ref('present') // 'present', 'absent', or 'selected'

const displayedAttendanceList = computed(() => {
  if (attendanceListFilter.value === 'all') {
    return students.value
  }
  if (attendanceListFilter.value === 'selected') {
    return students.value.filter(s => selectedIds.value.includes(s.id))
  }
  return students.value.filter(s => 
    attendanceListFilter.value === 'present' 
      ? studentAttendance.value[s.id] 
      : !studentAttendance.value[s.id]
  )
})

function openAttendanceList(type) {
  attendanceListFilter.value = type
  showAttendanceListDialog.value = true
}

function copyStudentNames() {
  const names = displayedAttendanceList.value.map(s => s.name).join('\n')
  navigator.clipboard.writeText(names).then(() => {
  })
}

function getAvatarUrl(student) {
  if (!student || !student.avatar) return 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent('<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'24\' height=\'24\' viewBox=\'0 0 24 24\'><rect width=\'24\' height=\'24\' fill=\'#e2e8f0\'/><text x=\'50%\' y=\'50%\' dy=\'.3em\' text-anchor=\'middle\' font-size=\'12\' fill=\'#64748b\'>ST</text></svg>' )
  
  const avatar = student.avatar
  if (avatar.startsWith('http') || avatar.startsWith('data:')) return avatar
  return avatar.startsWith('/') ? avatar : `/${avatar}`
}

// Initializing from localStorage if available
const behaviorUsage = ref(JSON.parse(localStorage.getItem('behaviorUsage') || '{}'))

function trackBehaviorUsage(behaviorId) {
  if (!behaviorId) return
  
  const usage = behaviorUsage.value[behaviorId] || { count: 0, lastUsed: 0 }
  usage.count++
  usage.lastUsed = Date.now()
  behaviorUsage.value[behaviorId] = usage
  
  // Persist
  localStorage.setItem('behaviorUsage', JSON.stringify(behaviorUsage.value))
}

const topBehaviors = computed(() => {
   // Get IDs sorted by usage (count descending, then recency)
   const sortedIds = Object.keys(behaviorUsage.value).sort((a, b) => {
       const useA = behaviorUsage.value[a]
       const useB = behaviorUsage.value[b]
       if (useA.count !== useB.count) return useB.count - useA.count
       return useB.lastUsed - useA.lastUsed
   })
   
   // Map to actual behavior objects and filter valid ones
   return sortedIds.map(id => behaviors.value.find(b => b.id == id))
                   .filter(b => b && (b.type === 'positive' || b.type === 'reward' || (b.value || b.points || 0) > 0))
                   .slice(0, 5) // Top 5
})

// Helper for full name display
function getFullName(student) {
    if (!student) return ''
    if (locale.value === 'ar' && student.name_ar) return student.name_ar
    
    // Explicitly construct First + Second + Last if available
    const parts = [
        student.firstName || '', 
        student.secondName || '', 
        student.lastName || '' // Assuming lastName field exists, otherwise rely on name parsing
    ].filter(p => p).join(' ')
    
    return parts || student.name || 'Student'
}

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

// Name Filtering Logic
const selectedNameTag = ref(null)

const nameTags = computed(() => {
  const tags = new Set()
  students.value.forEach(s => {
    let name = s.firstName || s.name || ''
    if (name.length >= 2) {
      tags.add(name.substring(0, 2).toUpperCase())
    }
  })
  return Array.from(tags).sort()
})

function toggleNameTag(tag) {
  if (selectedNameTag.value === tag) {
    selectedNameTag.value = null
  } else {
    selectedNameTag.value = tag
  }
}

// Organized students based on selected layout AND filter
const organizedStudents = computed(() => {
  // First, filter master list if tag is selected
  let filteredStudents = students.value
  if (selectedNameTag.value) {
    if (selectedNameTag.value === '__SELECTED__') {
       filteredStudents = students.value.filter(s => selectedIds.value.includes(s.id))
    } else if (selectedNameTag.value === '__UNSELECTED__') {
       filteredStudents = students.value.filter(s => !selectedIds.value.includes(s.id))
    } else {
       filteredStudents = students.value.filter(s => {
          const name = s.firstName || s.name || ''
          return name.toUpperCase().startsWith(selectedNameTag.value)
       })
    }
  }

  if (selectedLayout.value === 'no_groups') {
    return [{ name: null, students: filteredStudents }]
  }
  
  if (selectedLayout.value === 'name_asc') {
    const sorted = [...filteredStudents].sort((a, b) => 
      (a.firstName || '').localeCompare(b.firstName || '')
    )
    return [{ name: null, students: sorted }]
  }
  
  if (selectedLayout.value === 'name_desc') {
    const sorted = [...filteredStudents].sort((a, b) => 
      (b.firstName || '').localeCompare(a.firstName || '')
    )
    return [{ name: null, students: sorted }]
  }
  
  // Custom layout
  const layout = savedLayouts.value.find(l => l.id === selectedLayout.value)
  if (!layout) return [{ name: null, students: filteredStudents }]
  
  const groups = layout.groups.map(group => ({
    name: group.name,
    students: filteredStudents.filter(s => group.student_ids.includes(s.id))
  })).filter(g => g.students.length > 0) // Hide empty groups if filtering
  
  // Add unassigned students
  const assignedIds = new Set(layout.groups.flatMap(g => g.student_ids))
  const unassigned = filteredStudents.filter(s => !assignedIds.has(s.id))
  if (unassigned.length > 0) {
    groups.push({ name: 'Unassigned', students: unassigned })
  }
  
  return groups
})

// Top 5 students by total points
// ============ METHODS ============

function speakText(text) {
  if (!window.speechSynthesis || !isReadAloudEnabled.value) return;
  
  // Cancel any ongoing speech
  window.speechSynthesis.cancel();
  
  const utterance = new SpeechSynthesisUtterance(text);
  utterance.rate = 1; // Normal speed
  utterance.pitch = 1; // Normal pitch
  utterance.volume = 1; // Max volume
  
  // Try to use a good voice if available (e.g., Google US English)
  // const voices = window.speechSynthesis.getVoices();
  // const preferredVoice = voices.find(v => v.name.includes('Google US English'));
  // if (preferredVoice) utterance.voice = preferredVoice;
  
  window.speechSynthesis.speak(utterance);
}

function getSpokenName(student) {
    // Use Arabic name if in Arabic locale
    const fullName = (locale.value === 'ar' && student.name_ar) ? student.name_ar : student.name;
    
    if (!fullName) return 'Student';

    // If explicit localized fields exist and match the locale logic (simple check)
    if (locale.value !== 'ar' && student.firstName) {
         return `${student.firstName} ${student.secondName || ''}`.trim();
    }

    // Parse from full name
    const parts = fullName.trim().split(/\s+/).filter(p => p.length > 0);
    if (parts.length > 0) {
        const first = parts[0];
        const second = parts.length > 1 ? parts[1] : '';
        return `${first} ${second}`.trim();
    }
    
    return fullName;
}

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
  
  // Capture details for confirmation BEFORE clearing selection
  const behavior = behaviors.value.find(b => b.id === selectedBehaviorIdForDialog.value)
  const isIndividual = feedbackMode.value === 'individual' && selectedIds.value.length === 1
  const student = isIndividual ? students.value.find(s => s.id === selectedIds.value[0]) : null

  if (behaviorDialogMode.value === 'positive') {
    selectedPositiveBehaviorId.value = selectedBehaviorIdForDialog.value
    await applyPositiveBehavior()
  } else {
    selectedNegativeBehaviorId.value = selectedBehaviorIdForDialog.value
    await applyNegativeBehavior()
  }
  
  // Show friendly confirmation if individual mode
  if (isIndividual && student && behavior) {
      lastFeedbackDetails.value = { 
          student, 
          behavior, 
          points: behavior.value || behavior.points || 0
      }
      
      // Speak Name and Points
      const points = behavior.value || behavior.points || 0;
      const pointsText = points > 0 ? `${points} points` : `${points} points`; // Keep simple, or "minus 2 points"
      // Wait a tiny bit so sound effect plays first
      setTimeout(() => {
        speakText(`${getSpokenName(student)}. ${pointsText}.`);
      }, 300);

      showFeedbackConfirmation.value = true
      
      // Auto close after 2.5s
      setTimeout(() => {
          showFeedbackConfirmation.value = false
      }, 2500)
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

// Preview Student Info Logic
const previewData = ref(null)
let previewTimeout = null

function setPreview(student) {
  if (previewTimeout) clearTimeout(previewTimeout)
  previewData.value = student
}

function clearPreview() {
  if (previewTimeout) clearTimeout(previewTimeout)
  // Keep the info for 3 seconds as requested before clearing
  previewTimeout = setTimeout(() => {
    previewData.value = null
  }, 3000)
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
      name_ar: behaviorForm.value.name_ar || null,
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
    // Play select sound when selecting
    playSound('select')
  } else {
    selectedIds.value.splice(idx, 1)
  }
}

function selectGroupStudents(groupStudents, select) {
  if (select) {
    // Select all present students in the group
    const presentStudentIds = groupStudents
      .filter(student => studentAttendance.value[student.id])
      .map(student => student.id)
    
    // Add to selectedIds (avoid duplicates)
    const currentSet = new Set(selectedIds.value)
    presentStudentIds.forEach(id => currentSet.add(id))
    selectedIds.value = Array.from(currentSet)
    
    // Play sound
    playSound('select')
  } else {
    // Deselect all students in the group
    const groupStudentIds = new Set(groupStudents.map(s => s.id))
    selectedIds.value = selectedIds.value.filter(id => !groupStudentIds.has(id))
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
      points_mode: pointsDisplayMode.value === 'from_now' ? 'overall' : pointsDisplayMode.value
    }

    const res = await axios.post('/api/student-behaviors/init-classroom', payload)
    if (res && res.data) {
      const d = res.data
      studentBehaviorsMainId.value = d.student_behaviors_mains_id
      initStatus.value = { 
        message: `Session initialized (created ${d.created}, skipped ${d.skipped}, mode: ${d.points_mode})`, 
        created: d.created, 
        skipped: d.skipped 
      }
      
      const items = d.student_behaviors || []
      const mapped = items.map(b => {
        if (!b.student) return null
        return {
          id: b.student.id,
          name: b.student.name || `Student ${b.student_id}`,
          name_ar: b.student.name_ar,
          firstName: b.student.first_name,
          secondName: b.student.second_name,
          lastName: b.student.last_name,
          avatar: b.student.avatar,
          behaviorRecordId: b.id,
        }
      }).filter(item => item !== null)

      students.value = mapped
      selectedIds.value = []
      
      const newStudentBehaviors = {}
      for (const b of items) {
        studentAttendance.value[b.student_id] = b.attend === undefined ? true : b.attend
        newStudentBehaviors[b.student_id] = {
          attend: b.attend === undefined ? true : b.attend,
          points_plus: b.points_plus || 0,
          points_minus: b.points_minus || 0,
          academic_tracker: b.academic_tracker || {},
          behavior_tracker: b.behavior_tracker || [],
          logistics_tracker: b.logistics_tracker || {}
        }
      }
      studentBehaviors.value = newStudentBehaviors

      $q.notify({ message: 'Session initialized', color: 'positive', position: 'top' })
      
      // Load history after init
      await loadHistory()
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

// Watch pointsDisplayMode to reload data
watch(pointsDisplayMode, (newVal) => {
    if (newVal === 'from_now') {
      // Capture current points as baseline
      const baseline = {}
      for (const [id, stats] of Object.entries(studentBehaviors.value)) {
        baseline[id] = {
          plus: stats.points_plus || 0,
          minus: stats.points_minus || 0
        }
      }
      pointsBaseline.value = baseline
      // Don't reload, just re-render with subtraction
    } else {
      initClassroomSession();
    }
});

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
        points_mode: pointsDisplayMode.value === 'from_now' ? 'overall' : pointsDisplayMode.value // Match init logic
      }
    )

    // Always process successful updates, even if some failed (partial success)
    if (result.results && result.results.length > 0) {
      result.results.forEach(res => {
        if (res.success && res.data) {
          const sId = res.studentId
          const updatedRecord = res.data
          
          // Ensure entry exists
          if (!studentBehaviors.value[sId]) {
             studentBehaviors.value[sId] = {
                points_plus: 0,
                points_minus: 0,
                attend: true // default assumption if missing
             }
          }

          if (studentBehaviors.value[sId]) {
            studentBehaviors.value[sId].points_plus = updatedRecord.points_plus
            studentBehaviors.value[sId].points_minus = updatedRecord.points_minus
            
            // Also update attendance if returned
            if (updatedRecord.attend !== undefined) {
              studentBehaviors.value[sId].attend = updatedRecord.attend
              studentAttendance.value[sId] = updatedRecord.attend
            }
          }
        }
      })
      
      // Play sound if at least one success
      const behavior = behaviors.value.find(b => b.id === behaviorId)
      if (behavior) {
        const value = behavior.value || behavior.points || 0
        if (value > 0) playSound('reward')
        if (value < 0) playSound('penalty')
      }

      // Show notification ONLY if NOT in individual mode (where we show the big popup)
      // or if applied to multiple students (where popup doesn't show or shows differently)
      const isIndividualConfirmation = feedbackMode.value === 'individual' && result.results.length === 1;
      
      if (!isIndividualConfirmation) {
          $q.notify({
            message: `Applied behavior to ${result.results.length} students` + (result.errors && result.errors.length ? ` (${result.errors.length} failed)` : ''),
            color: result.errors && result.errors.length ? 'warning' : 'positive',
            position: 'top'
          })
      }
      
      // Re-fetch only history, don't reload entire session
      await loadHistory()
      selectedIds.value = []
    } 
    
    // Handle errors separately
    if (result.errors && result.errors.length > 0) {
      console.error('Partial or full failure:', result.errors)
      if (result.results.length === 0) {
         // Full failure
         $q.notify({
          message: result.error || 'Failed to apply behavior to selected students',
          color: 'negative',
          position: 'top'
        })
      }
    } else {
        // Successful action -> Add to Undo History
        // Use recentActions to get the correctly ID'd records (PointAction IDs)
        // result.results might not have PointAction IDs (only StudentBehavior IDs).
        const successCount = result.results.length;
        if (successCount > 0 && recentActions.value.length > 0) {
            // Take the top N actions, assuming we just created them and they appear first.
            // This is a heuristic but safer than using wrong IDs.
            // Filter to ensure they match our current context if possible, but simplest is top N.
            const latest = recentActions.value.slice(0, successCount).map(a => ({
                actionId: a.id,
                studentId: a.student_id,
                offline: false 
            }));
            
            // Check for offline results that might not be in history yet?
            // If we are offline, loadHistory might fail or return old data.
            // If offline, result.results has `offline: true` and probably temp IDs. 
            // We should check result.results for offline flags.
            
            const offlineActions = result.results.filter(r => r.offline).map(r => ({
                actionId: r.data?.id || 'temp_' + Date.now(),
                studentId: r.studentId,
                offline: true
            }));
            
            const finalActions = offlineActions.length > 0 ? offlineActions : latest;

            if (finalActions.length > 0) {
                 addToUndoStack({
                    type: 'point_action',
                    actions: finalActions,
                    desc: successCount === 1 ? 'Last Action' : `Group Action (${successCount})` 
                })
            }
        }
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

// Undo Logic
const undoStack = ref([])

function addToUndoStack(action) {
    undoStack.value.push(action)
    // Limit stack size
    if (undoStack.value.length > 10) undoStack.value.shift()
}

async function performUndo() {
    if (undoStack.value.length === 0) return
    
    // Get last action
    const lastAction = undoStack.value.pop()
    
    $q.loading.show({ message: 'Undoing...' })
    
    try {
        let successCount = 0
        
        // It could be a batch of actions
        for (const act of lastAction.actions) {
             const result = await rewardPointService.undoAction(act.actionId)
             if (result.success) successCount++
        }
        
        if (successCount > 0) {
            $q.notify({
                message: `Undone successfully`,
                color: 'positive',
                icon: 'undo',
                position: 'top'
            })
            // Refresh history/stats
            loadHistory()
            // We might want to "optimistically" revert the local state too, but loadHistory/init does it.
            // For now, reload is safer.
            // Actually, we should call initClassroomSession() or at least update the local points locally to reflect undo immediately.
            // Simplified: Refresh.
            initClassroomSession() 
        } else {
             $q.notify({
                message: 'Failed to undo action',
                color: 'warning',
                position: 'top'
            })
        }
    } catch (e) {
        console.error('Undo failed', e)
         $q.notify({
                message: 'Undo failed',
                color: 'negative',
                position: 'top'
            })
    } finally {
        $q.loading.hide()
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

  // Play selection sound when toggling attendance locally (before API call)
  playSound('select')

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



// ============ POINTS DISPLAY SETTINGS ============
function applyPointsSettings() {
  // Save to localStorage
  localStorage.setItem('points-display-mode', pointsDisplayMode.value)
  if (competitionStartTime.value) {
    localStorage.setItem('competition-start-time', competitionStartTime.value)
  } else {
    localStorage.removeItem('competition-start-time')
  }
  if (customDateFrom.value) {
    localStorage.setItem('custom-date-from', customDateFrom.value)
  }
  if (customDateTo.value) {
    localStorage.setItem('custom-date-to', customDateTo.value)
  }
  localStorage.setItem('leaderboard-mode', leaderboardMode.value)
  
  // Close dialog
  showPointsSettings.value = false
  
  // Reload data with new filters
  if (selectedClassroomId.value) {
    initClassroomSession()
  }
  
  $q.notify({
    message: 'Points display settings applied',
    color: 'positive',
    position: 'top',
    icon: 'check_circle'
  })
}

function resetPointsSettings() {
  pointsDisplayMode.value = 'overall'
  competitionStartTime.value = null
  customDateFrom.value = null
  customDateTo.value = null
  leaderboardMode.value = 'top5'
  
  // Clear localStorage
  localStorage.removeItem('points-display-mode')
  localStorage.removeItem('competition-start-time')
  localStorage.removeItem('custom-date-from')
  localStorage.removeItem('custom-date-to')
  localStorage.removeItem('leaderboard-mode')
  
  // Close dialog
  showPointsSettings.value = false
  
  // Reload data
  if (selectedClassroomId.value) {
    initClassroomSession()
  }
  
  $q.notify({
    message: 'Reset to normal view',
    color: 'info',
    position: 'top'
  })
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
  // Skip initialization if already done (e.g., when restoring from minimize)
  if (isInitialized.value) {
    console.log('⏭️ Skipping re-initialization - component already initialized')
    return
  }

  try {
    console.log('🚀 Initializing reward system...')

    // Load classrooms
    const classRes = await axios.get('/my_classes_with_students')
    classrooms.value = classRes.data
    console.log(`✅ Loaded ${classrooms.value.length} classrooms`)
    
    let autoInit = false
    
    // Priority 1: Props (when used as dialog component)
    if (props.classroomId && props.subjectId) {
      console.log('🎯 Props detected (dialog mode) - auto-initializing...')
      selectedClassroomId.value = props.classroomId
      selectedSubjectId.value = props.subjectId
      
      // Fetch Active Semester
      try {
        // Try to get from inertia page props first if available
        const page = usePage()
        const schoolActiveSemester = page.props.auth?.school?.active_semester_id
        
        if (schoolActiveSemester) {
           selectedSemester.value = parseInt(schoolActiveSemester)
           console.log(`🏫 Active Semester from Inertia: ${selectedSemester.value}`)
        } else {
           // Fallback default
           selectedSemester.value = 1 
        }
      } catch (err) {
        console.warn('Could not fetch active semester, defaulting to 1', err)
        selectedSemester.value = 1
      }
      
      if (props.date) {
        selectedDate.value = props.date
      } else {
        selectedDate.value = new Date().toISOString().split('T')[0]
      }
      
      // Calculate Day from Date (Sunday=1, Monday=2...)
      const d = new Date(selectedDate.value)
      selectedDay.value = d.getDay() + 1
      

      if (props.period) {
        selectedPeriodNumber.value = props.period
      }
      
      if (props.week) {
        selectedWeek.value = props.week
        console.log(`📅 Props Week: ${props.week}`)
      }
      
      autoInit = true
      console.log(`📚 Props Subject: ${props.subjectId}`)
      console.log(`🏫 Props Classroom: ${props.classroomId}`)
      console.log(`📅 Props Date: ${selectedDate.value}`)
      console.log(`⚡ Calc Day: ${selectedDay.value}`)
      console.log(`🔢 Props Period: ${props.period}`)
    }
    // Priority 2: URL query parameters (from teacher schedule link)
    else {
      const urlParams = new URLSearchParams(window.location.search)
      const urlClassroomId = urlParams.get('classroom_id')
      const urlSubjectId = urlParams.get('subject_id')
      const urlPeriod = urlParams.get('period')
      const urlDate = urlParams.get('date')
      
      if (urlClassroomId && urlSubjectId) {
        console.log('🔗 URL parameters detected - auto-initializing...')
        selectedClassroomId.value = parseInt(urlClassroomId)
        selectedSubjectId.value = parseInt(urlSubjectId)
        
        // Set date (default to today if not provided)
        if (urlDate) {
          selectedDate.value = urlDate
        } else {
          selectedDate.value = new Date().toISOString().split('T')[0]
        }
        
        // Set period number if provided
        if (urlPeriod) {
          selectedPeriodNumber.value = parseInt(urlPeriod)
        }
        
        


        autoInit = true
        console.log(`📚 URL Subject: ${urlSubjectId}`)
        console.log(`🏫 URL Classroom: ${urlClassroomId}`)
        console.log(`📅 URL Date: ${selectedDate.value}`)
        console.log(`🔢 URL Period: ${urlPeriod}`)
      }
      // Priority 3: localStorage
      else {
        // Restore from localStorage or auto-select first subject
        const savedSubjectId = localStorage.getItem('reward-system-selected-subject-id')
        const savedClassroomId = localStorage.getItem('reward-system-selected-classroom-id')
        
        if (savedSubjectId && subjects.value.some(s => s.id === parseInt(savedSubjectId))) {
          selectedSubjectId.value = parseInt(savedSubjectId)
          console.log(`📚 Restored subject from localStorage: ${savedSubjectId}`)
        } else if (subjects.value.length > 0) {
          selectedSubjectId.value = subjects.value[0].id
          console.log(`📚 Auto-selected first subject: ${subjects.value[0].name}`)
        }
        
        if (savedClassroomId && classrooms.value.some(c => c.classroom_id === parseInt(savedClassroomId))) {
          selectedClassroomId.value = parseInt(savedClassroomId)
          console.log(`🏫 Restored classroom from localStorage: ${savedClassroomId}`)
        }

        // Restore active tab
        const savedActiveTab = localStorage.getItem('reward-system-active-tab')
        if (savedActiveTab) {
          activeTab.value = savedActiveTab
        }

        // Restore period code components from localStorage
        const savedSemester = localStorage.getItem('reward-system-selected-semester')
        const savedWeek = localStorage.getItem('reward-system-selected-week')
        const savedDay = localStorage.getItem('reward-system-selected-day')
        const savedPeriodNumber = localStorage.getItem('reward-system-selected-period-number')
        
        if (savedSemester) {
          selectedSemester.value = parseInt(savedSemester)
          console.log(`📅 Restored semester from localStorage: ${savedSemester}`)
        }
        
        if (savedWeek) {
          selectedWeek.value = parseInt(savedWeek)
          console.log(`📅 Restored week from localStorage: ${savedWeek}`)
        }
        
        if (savedDay) {
          selectedDay.value = parseInt(savedDay)
          console.log(`📅 Restored day from localStorage: ${savedDay}`)
        }
        
        if (savedPeriodNumber) {
          selectedPeriodNumber.value = parseInt(savedPeriodNumber)
          console.log(`📅 Restored period number from localStorage: ${savedPeriodNumber}`)
        }
      }
    }
    
    console.log(`🔢 Active Period Code: ${selectedSemester.value}.${selectedWeek.value}.${selectedDay.value}.${selectedPeriodNumber.value}`)

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
    
    // Mark as initialized to prevent re-initialization on restore
    isInitialized.value = true
    
    // Auto-initialize session if props or URL params were provided
    if (autoInit && selectedClassroomId.value) {
      console.log('🚀 Auto-initializing classroom session...')
      await handleClassroomChange(selectedClassroomId.value)
      await initClassroomSession()
      
      showHeaderMessage(props.isDialog ? 'Session loaded!' : 'Session loaded from schedule!', 3000)
    }
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
