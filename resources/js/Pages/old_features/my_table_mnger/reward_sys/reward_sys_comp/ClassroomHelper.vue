<template>
  <div class="q-pa-md">
    <!-- Tracker Selection Dropdown -->
    <div class="row q-mb-md flex-center">
      <div class="col-12 col-md-6 flex justify-center">
        <q-btn-toggle
          v-model="activeTracker"
          push
          glossy
          toggle-color="primary"
          :options="[
            {label: 'Academic Tracker', value: 'academic', icon: 'school'},
            {label: 'Behavior Tracker', value: 'behavior', icon: 'psychology'},
            {label: 'Logistics Tracker', value: 'logistics', icon: 'swap_horiz'},
            {label: 'Attendance Helper', value: 'attendance', icon: 'assignment_ind'}
          ]"
        />
      </div>
    </div>

    <!-- MAIN TRACKER CARD -->
    <q-card class="shadow-sm border border-gray-200">
      <q-card-section class="q-pa-none">
        
        <!-- ================= ACADEMIC TRACKER ================= -->
        <!-- Academic Settings Button & Header -->
        <div v-if="activeTracker === 'academic'" class="flex justify-between items-center q-pa-sm bg-blue-50 border-b border-blue-100">
             <div class="text-subtitle1 text-blue-900 font-bold ml-2">Academic Goals</div>
             <q-btn flat icon="settings" label="Configure Columns" color="primary" size="sm" @click="showAcademicSettings = true" />
        </div>
        <q-table
          v-if="activeTracker === 'academic'"
          :rows="students"
          :columns="academicColumns"
          row-key="id"
          flat
          bordered
          :pagination="{ rowsPerPage: 0 }"
          hide-bottom
        >
          <!-- Student Name Column -->
          <template v-slot:body-cell-student="props">
            <q-td :props="props">
              <div class="flex items-center gap-3">
                <q-avatar size="md">
                  <img :src="getAvatarUrl(props.row)" />
                </q-avatar>
                <div class="font-bold">{{ props.row.name }}</div>
              </div>
            </q-td>
          </template>

          <!-- Attendance Column -->
          <template v-slot:body-cell-attendance="props">
            <q-td :props="props" class="text-center">
               <q-chip 
                  :color="isPresent(props.row.id) ? 'green-1' : 'red-1'" 
                  :text-color="isPresent(props.row.id) ? 'green-9' : 'red-9'"
                  dense
                  square
               >
                  {{ isPresent(props.row.id) ? 'Present' : 'Absent' }}
               </q-chip>
            </q-td>
          </template>

          <!-- Custom Header for Materials -->
          <template v-slot:header-cell-materials="props">
            <q-th :props="props">
              <div>{{ props.col.label }}</div>
              <div class="row justify-center q-gutter-xs">
                 <q-btn flat round dense icon="done_all" color="positive" size="sm" @click="bulkUpdateAcademic('materials', true)">
                    <q-tooltip>Check All Materials</q-tooltip>
                 </q-btn>
                 <q-btn flat round dense icon="remove_done" color="negative" size="sm" @click="bulkUpdateAcademic('materials', false)">
                    <q-tooltip>Uncheck All Materials</q-tooltip>
                 </q-btn>
              </div>
            </q-th>
          </template>

          <!-- Materials Checklist -->
          <template v-slot:body-cell-materials="props">
            <q-td :props="props">
              <div class="flex gap-2 justify-center">
                <q-checkbox 
                   v-for="item in academicConfig.materials"
                   :key="item"
                   v-model="getAcademicData(props.row.id).materials[item]" 
                   dense 
                   color="blue"
                   :label="item" 
                   @update:model-value="saveTrackerData('academic_tracker', props.row.id)"
                   :disable="!isPresent(props.row.id)"
                />
              </div>
            </q-td>
          </template>

          <!-- Custom Header for Tasks -->
          <template v-slot:header-cell-tasks="props">
            <q-th :props="props">
              <div>{{ props.col.label }}</div>
              <div class="row justify-center q-gutter-xs">
                 <q-btn flat round dense icon="done_all" color="positive" size="sm" @click="bulkUpdateAcademic('tasks', true)">
                    <q-tooltip>Check All Tasks</q-tooltip>
                 </q-btn>
                 <q-btn flat round dense icon="remove_done" color="negative" size="sm" @click="bulkUpdateAcademic('tasks', false)">
                    <q-tooltip>Uncheck All Tasks</q-tooltip>
                 </q-btn>
              </div>
            </q-th>
          </template>

          <!-- Tasks Checklist -->
          <template v-slot:body-cell-tasks="props">
            <q-td :props="props">
              <div class="flex gap-2 justify-center">
                <q-checkbox 
                   v-for="item in academicConfig.tasks"
                   :key="item"
                   v-model="getAcademicData(props.row.id).tasks[item]" 
                   dense 
                  
                   color="blue"
                   :label="item" 
                   @update:model-value="saveTrackerData('academic_tracker', props.row.id)"
                   :disable="!isPresent(props.row.id)"
                />
                
              </div>
            </q-td>
          </template>
        </q-table>



        <!-- ================= BEHAVIOR TRACKER ================= -->
        <q-table
          v-if="activeTracker === 'behavior'"
          :rows="students"
          :columns="behaviorColumns"
          row-key="id"
          flat
          bordered
          :pagination="{ rowsPerPage: 0 }"
          hide-bottom
        >
          <template v-slot:body-cell-student="props">
            <q-td :props="props">
              <div class="flex items-center gap-3">
                <q-avatar size="md">
                  <img :src="getAvatarUrl(props.row)" />
                </q-avatar>
                <div class="font-bold">{{ props.row.name }}</div>
              </div>
            </q-td>
          </template>

          <template v-slot:body-cell-pos="props">
             <q-td :props="props" class="text-center font-bold text-green-700">
                {{ props.value > 0 ? '+' + props.value : '-' }}
             </q-td>
          </template>

          <template v-slot:body-cell-pos_detail="props">
             <q-td :props="props">
                <div class="flex gap-1 flex-wrap">
                    <q-badge v-for="(note, i) in props.value" :key="i" color="green-1" text-color="green-9">
                        {{ note }}
                    </q-badge>
                </div>
             </q-td>
          </template>

          <template v-slot:body-cell-neg="props">
             <q-td :props="props" class="text-center font-bold text-red-700">
                {{ props.value > 0 ? '-' + props.value : '-' }}
             </q-td>
          </template>

          <template v-slot:body-cell-neg_detail="props">
             <q-td :props="props">
                <div class="flex gap-1 flex-wrap">
                    <q-badge v-for="(note, i) in props.value" :key="i" color="red-1" text-color="red-9">
                        {{ note }}
                    </q-badge>
                </div>
             </q-td>
          </template>

          <template v-slot:body-cell-total="props">
             <q-td :props="props" class="text-center font-bold" :class="props.value > 0 ? 'text-green-700' : (props.value < 0 ? 'text-red-700' : 'text-grey-7')">
                {{ props.value > 0 ? '+' + props.value : props.value }}
             </q-td>
          </template>

          <!-- Action Column -->
          <template v-slot:body-cell-action="props">
             <q-td :props="props" class="text-right">
                <div class="flex justify-end gap-2 items-center">
                    <!-- Mini Logs Preview -->
                    <div v-if="getBehaviorData(props.row.id).length > 0" class="flex gap-1 mr-2 opacity-50">
                        <div v-for="(log, i) in getBehaviorData(props.row.id).slice(0, 3)" :key="i" 
                            class="w-2 h-2 rounded-full"
                            :class="log.type === 'positive' ? 'bg-green-500' : 'bg-red-500'">
                        </div>
                    </div>
                    <q-btn 
                       icon="add_comment" 
                       label="Log" 
                       color="primary" 
                       size="sm" 
                       @click="openBehaviorLogDialog(props.row)"
                       :disable="!isPresent(props.row.id)"
                    />
                </div>
             </q-td>
          </template>
        </q-table>

        <!-- ================= LOGISTICS TRACKER ================= -->
        <div v-if="activeTracker === 'logistics'">
           <!-- Logistics Summary & Settings -->
           <div class="row q-col-gutter-md q-mb-md">
              <div class="col-12 col-md-8">
                 <div class="bg-orange-50 p-4 rounded-xl border border-orange-200 flex items-center justify-between">
                    <div class="flex items-center gap-4">
                       <div class="text-h6 text-orange-900">
                          <q-icon name="timelapse" /> Outside: {{ studentsOutside?.length || 0 }} / {{ maxStudentsOutside }}
                       </div>
                       <!-- Students Outside Chips -->
                       <div class="flex gap-2">
                          <q-chip 
                             v-for="student in studentsOutside" 
                             :key="student.id"
                             removable
                             @remove="checkInStudent(student.id)"
                             color="white"
                             text-color="orange-9"
                             class="border border-orange-300"
                          >
                             <q-avatar>
                                <img :src="getAvatarUrl(student)" />
                             </q-avatar>
                             {{ student.name }}
                             <q-badge color="orange" floating transparent class="text-xs font-mono">
                                {{ getElapsedTime(student.id) }}
                             </q-badge>
                          </q-chip>
                       </div>
                    </div>
                    
                    <!-- Settings & Reset -->
                    <div class="flex items-center gap-2">
                       <span class="text-orange-900 text-sm font-bold">Max Out:</span>
                       <q-select 
                          v-model="maxStudentsOutside" 
                          :options="[1, 2, 3, 4, 5]" 
                          dense 
                          outlined 
                          options-dense
                          bg-color="white"
                          class="w-20"
                       />
                       <span class="text-blue-900 text-sm font-bold ml-2">Max Wait:</span>
                         <q-select 
                          v-model="maxWaitingList" 
                          :options="[1, 2, 3, 4, 5, 6, 7, 8, 9, 10]" 
                          dense 
                          outlined 
                          options-dense
                          bg-color="white"
                          class="w-20"
                       />
                       <q-btn 
                          icon="restart_alt" 
                          color="negative" 
                          flat 
                          round 
                          dense 
                          @click="resetLogisticsSession" 
                       >
                          <q-tooltip>Reset Logistics Session</q-tooltip>
                       </q-btn>
                    </div>
                 </div>
              </div>
           </div>

           <!-- WAITING LIST SECTION -->
           <div v-if="studentsWaiting.length > 0" class="mb-6">
               <div class="text-h6 text-blue-900 mb-2 flex items-center gap-2">
                   <q-icon name="hourglass_empty" /> Waiting Queue ({{ studentsWaiting.length }} / {{ maxWaitingList }})
               </div>
               <q-list bordered separator class="rounded-borders bg-blue-50 border-blue-200">
                  <q-item v-for="(student, idx) in studentsWaiting" :key="student.id">
                     <q-item-section avatar>
                        <q-avatar>
                           <img :src="getAvatarUrl(student)" />
                        </q-avatar>
                     </q-item-section>
                     <q-item-section>
                        <q-item-label class="font-bold">{{ idx + 1 }}. {{ student.name }}</q-item-label>
                        <q-item-label caption>
                           Waiting since {{ new Date(getLogisticsStatus(student.id).current?.timestamp).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) }}
                        </q-item-label>
                     </q-item-section>
                     <q-item-section side>
                        <div class="flex gap-2">
                           <q-btn 
                              icon="arrow_forward" 
                              label="Let Out" 
                              color="primary" 
                              size="sm"
                              @click="promoteFromWaitlist(student)"
                              :disable="isLimitReached"
                           >
                              <q-tooltip v-if="isLimitReached">Class Limit Reached</q-tooltip>
                           </q-btn>
                           <q-btn 
                              icon="close" 
                              flat 
                              round 
                              color="grey" 
                              size="sm"
                              @click="removeFromWaitlist(student.id)"
                           />
                        </div>
                     </q-item-section>
                  </q-item>
               </q-list>
           </div>
           
           <!-- EMPTY STATE / INSTRUCTIONS -->
           <div v-if="studentsOutside.length === 0 && studentsWaiting.length === 0" class="text-center p-8 text-grey-5 border-2 border-dashed border-gray-200 rounded-xl mb-8">
               <q-icon name="timelapse" size="4em" class="mb-2" />
               <div class="text-lg">Classroom is full. No one is currently waiting or outside.</div>
               <q-btn 
                  label="Request Exit" 
                  color="primary" 
                  icon="add" 
                  class="mt-4"
                  @click="showStudentSelector = true"
               />
           </div>
           
           <!-- ACTION BAR (If not empty) -->
           <div v-else class="flex justify-center mb-8">
               <q-btn 
                  label="Request Another Exit" 
                  color="primary" 
                  icon="add" 
                  size="md"
                  @click="showStudentSelector = true"
               />
           </div>
        
        <!-- Previously Left Table -->
        <div v-if="studentsWithHistory.length > 0" class="mt-8">
            <div class="text-h6 text-grey-8 mb-2 flex items-center gap-2">
                <q-icon name="history" /> Previously Left
            </div>
             <q-table
             :rows="studentsWithHistory"
             :columns="logisticsHistoryColumns"
             row-key="id"
             flat
             hide-bottom
             class="bg-grey-50"
           >
               <template v-slot:body-cell-leave_order="props">
                 <q-td :props="props" class="text-center text-grey-7 font-bold">
                     #{{ props.value }}
                 </q-td>
               </template>

              <template v-slot:body-cell-student="props">
                <q-td :props="props">
                  <div class="flex items-center gap-3">
                    <q-avatar size="sm">
                      <img :src="getAvatarUrl(props.row)" />
                    </q-avatar>
                    <div class="font-medium">{{ props.row.name }}</div>
                  </div>
                </q-td>
              </template>
              
               <template v-slot:body-cell-times_left="props">
                 <q-td :props="props" class="text-center font-bold">
                     {{ getLogisticsHistoryCount(props.row.id) }}
                 </q-td>
               </template>

               <template v-slot:body-cell-last_out="props">
                 <q-td :props="props">
                     <div class="text-xs">
                         {{ getLastOutTime(props.row.id) }}
                     </div>
                 </q-td>
               </template>

               <template v-slot:body-cell-duration="props">
                 <q-td :props="props" class="text-center font-mono text-xs">
                     {{ getLastDuration(props.row.id) }}
                 </q-td>
               </template>

               <template v-slot:body-cell-action="props">
                 <q-td :props="props" class="text-center">
                     <q-btn 
                        label="Urgent Exit" 
                        color="negative" 
                        outline
                        size="xs"
                        icon="priority_high"
                        @click="openLogisticsDialog(props.row)"
                     />
                 </q-td>
               </template>
             </q-table>
        </div>

        </div>

        <!-- ================= ATTENDANCE HELPER ================= -->
        <div v-if="activeTracker === 'attendance'">
           <div class="row q-col-gutter-md">
              <div class="col-12 col-md-8">
                 <q-table
                  :rows="students"
                  :columns="attendanceHelperColumns"
                  row-key="id"
                  flat
                  bordered
                  :pagination="{ rowsPerPage: 0 }"
                  hide-bottom
                >
                  <template v-slot:body-cell-student="props">
                    <q-td :props="props">
                      <div class="flex items-center gap-3">
                        <q-avatar size="md">
                          <img :src="getAvatarUrl(props.row)" />
                        </q-avatar>
                        <div class="font-bold">{{ props.row.name }}</div>
                      </div>
                    </q-td>
                  </template>

                  <template v-slot:body-cell-mark="props">
                    <q-td :props="props" class="text-center">
                       <q-checkbox 
                          v-model="attendanceTemp[props.row.id]"
                          :color="attendanceTemp[props.row.id] ? 'positive' : 'negative'"
                          checked-icon="check_circle"
                          unchecked-icon="cancel"
                          size="lg"
                          keep-color
                          :disable="false"
                       >
                         <q-tooltip>Toggle Attendance</q-tooltip>
                       </q-checkbox>
                    </q-td>
                  </template>
                </q-table>
              </div>

              <!-- Absentees Summary Panel -->
              <div class="col-12 col-md-4">
                 <q-card class="bg-red-50 text-red-900 sticky-top" flat bordered>
                    <q-card-section>
                       <div class="text-h6 flex items-center gap-2">
                          <q-icon name="warning" color="negative" />
                          Absentees List
                       </div>
                       <div class="text-subtitle2 mt-1">
                          Total: {{ absentCount }} / {{ students.length }}
                       </div>
                    </q-card-section>

                    <q-separator color="red-200" />

                    <q-card-section class="q-pa-sm">
                       <div v-if="absentCount > 0" class="flex flex-col gap-2">
                          <div v-for="student in absentStudentsList" :key="student.id" class="flex items-center gap-2 bg-white p-2 rounded shadow-sm border border-red-100">
                              <q-avatar size="sm">
                                <img :src="getAvatarUrl(student)" />
                              </q-avatar>
                              <span class="font-medium">{{ student.name }}</span>
                          </div>
                       </div>
                       <div v-else class="text-center text-green-700 py-4 italic">
                          <q-icon name="thumb_up" /> All Present
                       </div>
                    </q-card-section>
                 </q-card>
              </div>
           </div>
        </div>

      </q-card-section>
    </q-card>

    <!-- Behavior Log Dialog -->
    <q-dialog v-model="showBehaviorDialog">
      <q-card style="min-width: 350px">
        <q-card-section>
          <div class="text-h6">Log Behavior Note</div>
          <div class="text-subtitle2 text-grey">{{ selectedStudent?.name }}</div>
        </q-card-section>

        <q-card-section>
          <div class="flex justify-center mb-4">
              <q-btn-toggle
                  v-model="behaviorType"
                  spread
                  no-caps
                  rounded
                  unelevated
                  toggle-color="primary"
                  color="white"
                  text-color="primary"
                  :options="[
                      {label: 'Negative Behavior', value: 'negative', slot: 'neg'},
                      {label: 'Positive Behavior', value: 'positive', slot: 'pos'}
                  ]"
              >
                  <template v-slot:neg>
                      <div class="row items-center no-wrap">
                          <q-icon name="remove_circle" color="negative" class="mr-2" />
                          <div class="text-center">Negative</div>
                      </div>
                  </template>
                  <template v-slot:pos>
                      <div class="row items-center no-wrap">
                          <q-icon name="add_circle" color="positive" class="mr-2" />
                          <div class="text-center">Positive</div>
                      </div>
                  </template>
              </q-btn-toggle>
          </div>
          
          <q-input v-model="behaviorNote" label="Note" outlined autofocus autogrow :color="behaviorType === 'positive' ? 'green' : 'red'" />
          
          <!-- Behavior Tags -->
          <div class="mt-3">
              <div class="text-xs text-grey-6 mb-1">Quick Tags:</div>
              <div class="flex gap-1 flex-wrap">
                  <q-chip 
                      v-for="tag in allBehaviorTags" 
                      :key="tag"
                      clickable
                      color="grey-2"
                      text-color="grey-9"
                      size="sm"
                      @click="addTagToNote(tag)"
                  >
                      {{ tag }}
                  </q-chip>
              </div>
          </div>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Cancel" color="primary" v-close-popup />
          <q-btn flat label="Save" color="primary" @click="saveBehaviorLog" />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Logistics Check Out Dialog -->
    <q-dialog v-model="showLogisticsDialog">
      <q-card style="min-width: 300px">
        <q-card-section>
          <div class="text-h6">Check Out Student</div>
          <div class="text-subtitle2 text-grey">{{ selectedStudent?.name }}</div>
          
          <div v-if="isLimitReached" class="bg-red-50 text-red-700 p-2 rounded mt-2 text-sm border border-red-200">
             <q-icon name="warning" /> Limit Reached ({{ maxStudentsOutside }} students already out).
          </div>
        </q-card-section>

        <q-card-section>
          <div class="d-flex flex-col gap-2">
             <!-- Regular Reasons -->
             <template v-if="!isLimitReached">
                 <q-btn 
                    v-for="reason in ['Bathroom', 'Office', 'Cafeteria', 'Doctor']" 
                    :key="reason"
                    :label="reason"
                    color="primary"
                    outline
                    class="full-width q-mb-sm"
                    @click="checkOutStudent(reason)"
                 />
             </template>
             
             <!-- Urgent Override -->
             <div v-else>
                 <q-btn 
                    label="Force Check Out (Urgent)"
                    color="negative"
                    icon="priority_high"
                    class="full-width q-mb-sm"
                    @click="checkOutStudent('Urgent Override')"
                 />
                 <q-btn flat label="Cancel" v-close-popup class="full-width" />
             </div>
          </div>
        </q-card-section>
      </q-card>
    </q-dialog>
    <!-- Student Selector Dialog -->
    <q-dialog v-model="showStudentSelector">
      <q-card style="min-width: 400px; max-height: 80vh">
        <q-card-section>
          <div class="text-h6">Request Exit</div>
          <q-input v-model="studentSearch" placeholder="Search student..." dense outlined class="mt-2" autofocus>
             <template v-slot:prepend><q-icon name="search" /></template>
          </q-input>
        </q-card-section>
        
        <q-separator />

        <q-card-section class="scroll" style="max-height: 60vh">
           <q-list separator>
              <q-item 
                 v-for="student in filteredSelectorStudents" 
                 :key="student.id" 
                 clickable 
                 v-ripple
                 :disable="!isPresent(student.id) || getLogisticsStatus(student.id).status !== 'in'"
                 @click="selectStudentForExit(student)"
              >
                 <q-item-section avatar>
                    <q-avatar size="md">
                       <img :src="getAvatarUrl(student)" />
                    </q-avatar>
                 </q-item-section>
                 
                 <q-item-section>
                    <q-item-label>{{ student.name }}</q-item-label>
                    <q-item-label caption class="text-amber-700" v-if="!isPresent(student.id)">Absent from Class</q-item-label>
                     <q-item-label caption class="text-blue-700" v-else-if="getLogisticsStatus(student.id).status === 'out'">Currently Out</q-item-label>
                    <q-item-label caption class="text-blue-700" v-else-if="getLogisticsStatus(student.id).status === 'waiting'">Already Waiting</q-item-label>
                 </q-item-section>

                 <q-item-section side v-if="isPresent(student.id) && getLogisticsStatus(student.id).status === 'in'">
                     <q-btn icon="navigate_next" color="primary" flat round />
                 </q-item-section>
              </q-item>
           </q-list>
        </q-card-section>
        
        <q-card-actions align="right">
          <q-btn flat label="Close" color="primary" v-close-popup />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Academic Settings Dialog -->
    <q-dialog v-model="showAcademicSettings">
      <q-card style="min-width: 400px">
        <q-card-section>
          <div class="text-h6">Configure Academic Tracker</div>
          <div class="text-caption text-grey">Manage checklist items for Materials and Tasks.</div>
        </q-card-section>

        <q-card-section class="q-pt-none">
           <div class="row q-col-gutter-md">
               <!-- Materials Config -->
               <div class="col-12 col-md-6">
                   <div class="font-bold mb-2 text-primary">Materials Items</div>
                   <q-input v-model="newMaterialItem" dense outlined placeholder="Add item (e.g. Laptop)" @keyup.enter="addAcademicItem('materials')">
                       <template v-slot:append>
                           <q-btn round dense flat icon="add" color="primary" @click="addAcademicItem('materials')" />
                       </template>
                   </q-input>
                   <q-list dense separator class="mt-2 text-sm border rounded">
                        <q-item v-for="(item, idx) in academicConfig.materials" :key="idx">
                            <q-item-section>
                                <q-input v-model="academicConfig.materials[idx]" dense borderless @change="saveAcademicConfig" />
                            </q-item-section>
                            <q-item-section side>
                                <q-btn flat round dense icon="delete" color="negative" size="xs" @click="removeAcademicItem('materials', idx)" />
                            </q-item-section>
                        </q-item>
                   </q-list>
               </div>

               <!-- Tasks Config -->
               <div class="col-12 col-md-6">
                   <div class="font-bold mb-2 text-primary">Tasks Items</div>
                   <q-input v-model="newTaskItem" dense outlined placeholder="Add item (e.g. Project)" @keyup.enter="addAcademicItem('tasks')">
                       <template v-slot:append>
                           <q-btn round dense flat icon="add" color="primary" @click="addAcademicItem('tasks')" />
                       </template>
                   </q-input>
                   <q-list dense separator class="mt-2 text-sm border rounded">
                        <q-item v-for="(item, idx) in academicConfig.tasks" :key="idx">
                            <q-item-section>
                                <q-input v-model="academicConfig.tasks[idx]" dense borderless @change="saveAcademicConfig" />
                            </q-item-section>
                            <q-item-section side>
                                <q-btn flat round dense icon="delete" color="negative" size="xs" @click="removeAcademicItem('tasks', idx)" />
                            </q-item-section>
                        </q-item>
                   </q-list>
               </div>
           </div>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Done" color="primary" v-close-popup />
        </q-card-actions>
      </q-card>
    </q-dialog>

  </div>
</template>

<script setup>
import { ref, defineProps, watch, computed, reactive, onMounted } from 'vue'
import axios from 'axios'
import { useQuasar } from 'quasar'

const props = defineProps({
  students: Array,
  studentBehaviors: Object, // Map of studentId -> behaviorRecord
  studentAttendance: Object, // Map of studentId -> boolean
  periodInfo: Object // { date, periodCode, classroomId }
})

const $q = useQuasar()
const activeTracker = ref('academic') // 'academic', 'behavior', 'logistics'

// === COLUMNS DEFINITION ===
const academicColumns = [
  { name: 'student', label: 'Student', align: 'left', field: 'name' },
  { name: 'attendance', label: 'Attendance', align: 'center' },
  { name: 'materials', label: 'Materials Readiness', align: 'center' },
  { name: 'tasks', label: 'Tasks Readiness', align: 'center' }
]

const behaviorColumns = [
  { 
      name: 'student', 
      label: 'Student', 
      align: 'left', 
      field: 'name',
      sortable: true
  },
  { 
      name: 'pos', 
      label: 'Pos (+)', 
      align: 'center',
      field: row => getBehaviorStats(row.id).pos,
      sortable: true
  },
  { 
      name: 'pos_detail', 
      label: 'Positive List', 
      align: 'left',
      field: row => getBehaviorStats(row.id).posList,
      headerStyle: 'width: 200px'
  },
  { 
      name: 'neg', 
      label: 'Neg (-)', 
      align: 'center',
      field: row => getBehaviorStats(row.id).neg,
      sortable: true 
  },
  { 
      name: 'neg_detail', 
      label: 'Negative List', 
      align: 'left',
      field: row => getBehaviorStats(row.id).negList, 
      headerStyle: 'width: 200px'
  },
  { 
      name: 'total', 
      label: 'Total', 
      align: 'center',
      field: row => getBehaviorStats(row.id).total,
      sortable: true
  },
  { name: 'action', label: 'Action', align: 'right' }
]

const logisticsColumns = [
  { name: 'student', label: 'Student', align: 'left', field: 'name' },
  { name: 'status', label: 'Current Status', align: 'center' },
  { name: 'times_left', label: 'Times Left', align: 'center' },
  { name: 'action', label: 'Action', align: 'center' }
]

const logisticsHistoryColumns = [
  { 
      name: 'leave_order', 
      label: 'Order', 
      align: 'center', 
      field: row => getLeaveOrder(row.id),
      sortable: true 
  },
  { 
      name: 'student', 
      label: 'Student', 
      align: 'left', 
      field: 'name',
      sortable: true
  },
  { 
      name: 'times_left', 
      label: 'Total Sorties', 
      align: 'center',
      field: row => getLogisticsHistoryCount(row.id),
      sortable: true
  },
  { 
      name: 'last_out', 
      label: 'Last Time Out', 
      align: 'left',
      field: row => getLastOutTime(row.id), // String sort might be imperfect, ideally timestamp
      sort: (a, b, rowA, rowB) => {
          const tA = getLastOutTimestamp(rowA.id)
          const tB = getLastOutTimestamp(rowB.id)
          return tA - tB
      },
      sortable: true
  },
  { 
      name: 'duration', 
      label: 'Time Stayed', 
      align: 'center',
      field: row => getLastDurationSeconds(row.id), // Sort by seconds
      format: (val, row) => getLastDuration(row.id), // Display string
      sortable: true
  },
  { name: 'action', label: 'Action', align: 'center' }
]

const attendanceHelperColumns = [
  { name: 'student', label: 'Student', align: 'left', field: 'name' },
  { name: 'mark', label: 'Attendance', align: 'center' }
]

// === DATA ACCESS HELPERS ===
const getAvatarUrl = (student) => {
  if (!student) return '';
  if (student.avatar) return student.avatar.startsWith('/') ? student.avatar : `/${student.avatar}`;
  return '/images/avatars/default-avatar.svg';
}

const isPresent = (studentId) => {
   return props.studentAttendance ? props.studentAttendance[studentId] : true
}

// === ATTENDANCE HELPER LOGIC ===
const attendanceTemp = reactive({}) 

// Initialize temp state from prop
watch(() => props.studentAttendance, (newVal) => {
    if (newVal) {
        props.students.forEach(s => {
             // If present (true) -> checkbox (true) [Green]
             // If absent (false) -> checkbox (false) [Red Icon]
             attendanceTemp[s.id] = newVal[s.id] !== false // Default true if undefined
        })
    }
}, { immediate: true, deep: true })

const absentStudentsList = computed(() => {
    return props.students.filter(s => !attendanceTemp[s.id])
})

const absentCount = computed(() => absentStudentsList.value.length)

// === ACADEMIC CONFIGURATION ===
const showAcademicSettings = ref(false)
const newMaterialItem = ref('')
const newTaskItem = ref('')
const academicConfig = reactive({
    materials: ['Pen', 'Book', 'Notebook'],
    tasks: ['Homework', 'Classwork']
})

const addAcademicItem = (type) => {
    const val = type === 'materials' ? newMaterialItem.value : newTaskItem.value
    if (!val.trim()) return
    
    if (type === 'materials') {
        if (!academicConfig.materials.includes(val)) academicConfig.materials.push(val)
        newMaterialItem.value = ''
    } else {
        if (!academicConfig.tasks.includes(val)) academicConfig.tasks.push(val)
        newTaskItem.value = ''
    }
    saveAcademicConfig()
}

const removeAcademicItem = (type, idx) => {
    if (type === 'materials') academicConfig.materials.splice(idx, 1)
    else academicConfig.tasks.splice(idx, 1)
    saveAcademicConfig()
}

const saveAcademicConfig = () => {
    localStorage.setItem('academicConfig', JSON.stringify(academicConfig))
    // Trigger reactivity for table? Vue reactive should handle it
}

// === ACADEMIC DATA ===
const getAcademicData = (studentId) => {
    const record = props.studentBehaviors[studentId]
    
    // Ensure structure exists
    if (!record || !record.academic_tracker) {
        if (record && !record.academic_tracker) {
             record.academic_tracker = {
                 materials: {},
                 tasks: {}
             }
        }
        return record ? record.academic_tracker : { materials: {}, tasks: {} }
    }
    
    // Fix: Ensure sub-objects exist even if record exists
    if (!record.academic_tracker.materials) record.academic_tracker.materials = {}
    if (!record.academic_tracker.tasks) record.academic_tracker.tasks = {}

    return record.academic_tracker
}

// === BEHAVIOR DATA ===
const getBehaviorData = (studentId) => {
    const record = props.studentBehaviors[studentId]
    if (record && !record.behavior_tracker) {
        record.behavior_tracker = []
    }
    return record ? record.behavior_tracker : []
}

// === LOGISTICS DATA & TIMER ===
const maxStudentsOutside = ref(1) // Default limit layout
const maxWaitingList = ref(5) // Default wait queue limit
const timeNow = ref(Date.now())
const showStudentSelector = ref(false)
const studentSearch = ref('')

const filteredSelectorStudents = computed(() => {
    if (!studentSearch.value) return props.students
    const search = studentSearch.value.toLowerCase()
    return props.students.filter(s => s.name.toLowerCase().includes(search))
})

const selectStudentForExit = (student) => {
    if (!isPresent(student.id)) return // Should be disabled anyway
    
    selectedStudent.value = student
    showLogisticsDialog.value = true
    showStudentSelector.value = false // Close selector, open action dialog
}

// Update timer every second
let timerInterval
onMounted(() => {
    timerInterval = setInterval(() => {
        timeNow.value = Date.now()
    }, 1000)
})

const getLogisticsStatus = (studentId) => {
    const record = props.studentBehaviors[studentId]
    if (record && !record.logistics_tracker) {
        // Init default
        record.logistics_tracker = { status: 'in', current: null, history: [] }
    } else if (record && record.logistics_tracker && !record.logistics_tracker.history) {
        // Upgrade legacy structure on the fly
        const old = record.logistics_tracker
        record.logistics_tracker = { 
            status: old.status || 'in',
            current: old.status === 'out' ? { reason: old.reason, timestamp: old.timestamp } : null,
            history: []
        }
    }
    return record ? record.logistics_tracker : { status: 'in', current: null, history: [] }
}

const getLogisticsHistoryCount = (studentId) => {
    const status = getLogisticsStatus(studentId)
    return status.history ? status.history.length : 0
}

const getLastOutTimestamp = (studentId) => {
     const status = getLogisticsStatus(studentId)
    if (!status.history || status.history.length === 0) return 0
    const last = status.history[0]
    const ts = last.out_time || last.timestamp
    return new Date(ts).getTime()
}

// Calculate order based on Last Out Time
const getLeaveOrder = (studentId) => {
    // Get all students with history
    const withHistory = studentsWithHistory.value.map(s => ({
        id: s.id,
        ts: getLastOutTimestamp(s.id)
    }))
    // Sort ascending (earliest first) or descending? "Order Leave" usually means chronological.
    // 1st person to leave, 2nd person to leave...
    // Only checking LAST trip? Or first trip? 
    // Usually history list is about "Recent Activity".
    // Let's assume daily chronological order of their *Latest* exit.
    withHistory.sort((a, b) => a.ts - b.ts)
    
    const idx = withHistory.findIndex(x => x.id === studentId)
    return idx + 1
}

const getLastOutTime = (studentId) => {
    const status = getLogisticsStatus(studentId)
    if (!status.history || status.history.length === 0) return '-'
    const last = status.history[0] // Assuming newest first or handle sort
    if (!last.timestamp && !last.out_time) return '-'
    const ts = last.out_time || last.timestamp
    return new Date(ts).toLocaleString([], {month:'short', day:'numeric', hour: '2-digit', minute:'2-digit'})
}

const getLastDuration = (studentId) => {
    const status = getLogisticsStatus(studentId)
    if (!status.history || status.history.length === 0) return '-'
    const last = status.history[0]
    
    // We need both in_time and out_time (or timestamp as alias for out_time)
    const outTime = last.out_time || last.timestamp
    const inTime = last.in_time
    
    if (!outTime || !inTime) return '-'
    
    const diff = Math.floor((new Date(inTime).getTime() - new Date(outTime).getTime()) / 1000)
    if (diff < 0) return '-'
    
    const m = Math.floor(diff / 60).toString().padStart(2, '0')
    const s = (diff % 60).toString().padStart(2, '0')
    return `${m}:${s}`
}

const getLastDurationSeconds = (studentId) => {
    const status = getLogisticsStatus(studentId)
    if (!status.history || status.history.length === 0) return 0
    const last = status.history[0]
    const outTime = last.out_time || last.timestamp
    const inTime = last.in_time
    if (!outTime || !inTime) return 0
    return Math.floor((new Date(inTime).getTime() - new Date(outTime).getTime()) / 1000)
}

const getBehaviorStats = (studentId) => {
    const logs = getBehaviorData(studentId)
    let pos = 0
    let neg = 0
    const posList = []
    const negList = []
    logs.forEach(log => {
        if (log.type === 'positive') {
            pos++
            posList.push(log.note)
        } else {
            neg++
            negList.push(log.note)
        }
    })
    return { pos, neg, total: pos - neg, posList, negList }
}

const studentsWithHistory = computed(() => {
    if (!props.students) return []
    return props.students.filter(s => {
        const status = getLogisticsStatus(s.id)
        // Show if has history AND currently IN (if currently OUT they are in main table active view, though requirements didn't specify strict separation, usually better to separate)
        // User said "if some one from this list need to get out... after all list be out i can empy the list"
        // Let's show anyone with history who is currently IN.
        return status.history && status.history.length > 0 && status.status === 'in'
    })
})

const studentsOutside = computed(() => {
    if (!props.students) return []
    return props.students.filter(s => {
        const status = getLogisticsStatus(s.id)
        return status && status.status === 'out'
    })
})

const studentsWaiting = computed(() => {
    if (!props.students) return []
    // Sort by timestamp if needed, currently assumes order of entry but filter order relies on array order
    // Let's rely on array order of students but we might need real sorting by queued_at if we want strict FIFO visual
    // For now, simpler filter:
    return props.students.filter(s => {
        const status = getLogisticsStatus(s.id)
        return status && status.status === 'waiting'
    }).sort((a, b) => {
        const tA = getLogisticsStatus(a.id).current?.timestamp
        const tB = getLogisticsStatus(b.id).current?.timestamp
        if (!tA) return 1
        if (!tB) return -1
        return new Date(tA) - new Date(tB)
    })
})

const isLimitReached = computed(() => {
    return studentsOutside.value.length >= maxStudentsOutside.value
})

const isWaitlistFull = computed(() => {
    return studentsWaiting.value.length >= maxWaitingList.value
})

const getElapsedTime = (studentId) => {
    const status = getLogisticsStatus(studentId)
    // Handle both new structure (current.timestamp) and old (timestamp)
    const ts = status.current?.timestamp || status.timestamp
    if (!status || status.status !== 'out' || !ts) return '00:00'
    
    const start = new Date(ts).getTime()
    const diff = Math.floor((timeNow.value - start) / 1000)
    
    if (diff < 0) return '00:00'
    
    const m = Math.floor(diff / 60).toString().padStart(2, '0')
    const s = (diff % 60).toString().padStart(2, '0')
    return `${m}:${s}`
}

// === SAVING LOGIC ===
const saveTrackerData = async (trackerType, studentId) => {
    const record = props.studentBehaviors[studentId]
    if (!record) return

    let data = null
    if (trackerType === 'academic_tracker') data = record.academic_tracker
    if (trackerType === 'behavior_tracker') data = record.behavior_tracker
    if (trackerType === 'logistics_tracker') data = record.logistics_tracker

    try {
        await axios.post('/api/student-behaviors/update-tracker', {
            student_id: studentId,
            tracker_type: trackerType,
            data: data,
            date: props.periodInfo.date,
            period_code: props.periodInfo.periodCode,
            classroom_id: props.periodInfo.classroomId
        })
        // Optional: Notify success small toast?
        console.log(`Saved ${trackerType} for student ${studentId}`)
    } catch (error) {
        console.error('Failed to save tracker:', error)
        $q.notify({
            message: 'Failed to save change',
            color: 'negative',
            icon: 'error'
        })
    }
}

// === BULK UPDATES ===
const bulkUpdateAcademic = async (category, value) => {
    // category: 'materials' or 'tasks'
    // value: true or false
    
    // 1. Identify items to toggle
    const items = category === 'materials' ? academicConfig.materials : academicConfig.tasks
    
    // 2. Loop through all present students
    const updates = []
    
    props.students.forEach(student => {
        if (!isPresent(student.id)) return // Skip absent
        
        const record = getAcademicData(student.id)
        const targetObj = category === 'materials' ? record.materials : record.tasks
        
        let changed = false
        items.forEach(item => {
            if (targetObj[item] !== value) {
                targetObj[item] = value
                changed = true
            }
        })
        
        if (changed) {
            // Push promise to array
            updates.push(saveTrackerData('academic_tracker', student.id))
        }
    })
    
    // 3. Execute all saves
    if (updates.length > 0) {
        $q.loading.show({ message: `Updating ${updates.length} students...` })
        try {
            await Promise.all(updates)
            $q.notify({ type: 'positive', message: 'Bulk update completed' })
        } catch (e) {
            console.error(e)
            $q.notify({ type: 'negative', message: 'Some updates failed' })
        } finally {
            $q.loading.hide()
        }
    } else {
         $q.notify({ type: 'info', message: 'No changes needed' })
    }
}

// === BEHAVIOR LOG LOGIC ===
const showBehaviorDialog = ref(false)
const selectedStudent = ref(null)
const behaviorNote = ref('')
const behaviorType = ref('negative') // 'positive' or 'negative'

const customBehaviorTags = ref([])
const defaultBehaviorTags = ['Disruptive', 'No Homework', 'Talking', 'Late', 'Sleeping', 'Disrespectful']

const allBehaviorTags = computed(() => {
    // Unique merge
    return [...new Set([...defaultBehaviorTags, ...customBehaviorTags.value])]
})

const openBehaviorLogDialog = (student) => {
    selectedStudent.value = student
    behaviorNote.value = ''
    behaviorType.value = 'negative' // Reset default
    showBehaviorDialog.value = true
}

const addTagToNote = (tag) => {
    if (behaviorNote.value) {
        behaviorNote.value += `, ${tag}`
    } else {
        behaviorNote.value = tag
    }
}

const saveBehaviorLog = () => {
    if (!selectedStudent.value || !behaviorNote.value) return

    const studentId = selectedStudent.value.id
    const record = props.studentBehaviors[studentId]
    
    // Init array if needed
    if (!record.behavior_tracker) record.behavior_tracker = []
    
    const newLog = {
        note: behaviorNote.value,
        type: behaviorType.value,
        timestamp: new Date().toISOString()
    }
    
    record.behavior_tracker.unshift(newLog)
    saveTrackerData('behavior_tracker', studentId)
    
    // Save to custom tags if not in default
    const potentialTags = behaviorNote.value.split(',').map(t => t.trim()).filter(t => t.length > 0)
    let updatedTags = false
    
    potentialTags.forEach(tag => {
         // simple heuristic: if it's short enough to be a tag and not in default
         if (tag.length < 20 && !defaultBehaviorTags.includes(tag) && !customBehaviorTags.value.includes(tag)) {
             customBehaviorTags.value.push(tag)
             updatedTags = true
         }
    })
    
    if (updatedTags) {
        localStorage.setItem('customBehaviorTags', JSON.stringify(customBehaviorTags.value))
    }

    showBehaviorDialog.value = false
    $q.notify({ type: 'positive', message: 'Behavior logged' })
}

onMounted(() => {
    const savedTags = localStorage.getItem('customBehaviorTags')
    if (savedTags) {
        try {
            customBehaviorTags.value = JSON.parse(savedTags)
        } catch (e) {
             console.error('Failed to parse behavior tags', e)
        }
    }
    
    const savedConfig = localStorage.getItem('academicConfig')
    if (savedConfig) {
        try {
            const parsed = JSON.parse(savedConfig)
            // merge to preserve reactivity
            academicConfig.materials = parsed.materials || []
            academicConfig.tasks = parsed.tasks || []
        } catch (e) {
            console.error('Failed to parse academic config', e)
        }
    }
})
// === LOGISTICS DIALOG LOGIC ===
const showLogisticsDialog = ref(false)

const openLogisticsDialog = (student) => {
    selectedStudent.value = student
    showLogisticsDialog.value = true
}

const resetLogisticsSession = () => {
    $q.dialog({
        title: 'Reset Session',
        message: 'Are you sure you want to clear all logistics history? This cannot be undone.',
        cancel: true,
        persistent: true
    }).onOk(() => {
        // Loop through all students with history or out status
        props.students.forEach(s => {
            const record = props.studentBehaviors[s.id]
            if (record && record.logistics_tracker) {
                // Check if needs reset
                if (record.logistics_tracker.status === 'out' || (record.logistics_tracker.history && record.logistics_tracker.history.length > 0)) {
                    record.logistics_tracker = { status: 'in', current: null, history: [] }
                    saveTrackerData('logistics_tracker', s.id)
                }
            }
        })
        $q.notify({ type: 'positive', message: 'Session Reset' })
    })
}

const checkOutStudent = (reason) => {
    if (!selectedStudent.value) return
    const studentId = selectedStudent.value.id
    
    // Urgent Override bypasses everything
    if (reason === 'Urgent Override') {
         // Proceed to check out immediately (fall through to existing logic below)
    } else {
        // Queue-First Workflow: ALL other reasons go to Waitlist first
        // Check if waitlist full
        if (isWaitlistFull.value) {
            $q.notify({
                message: 'Waitlist is full!',
                color: 'negative',
                icon: 'hourglass_disabled'
            })
            showLogisticsDialog.value = false
            return
        }
        
        // Add to Waitlist
        addToWaitlist(studentId, reason)
        return
    }
    
    // IMMEDIATE CHECKOUT LOGIC (Only for Urgent Override now, or if promoted)
    const record = props.studentBehaviors[studentId]
    
    // Get existing to preserve history
    let existing = getLogisticsStatus(studentId)
    
    record.logistics_tracker = {
        status: 'out',
        current: {
            reason: reason,
            timestamp: new Date().toISOString()
        },
        history: existing.history || []
    }
    
    saveTrackerData('logistics_tracker', studentId)
    showLogisticsDialog.value = false
}

const addToWaitlist = (studentId, reason) => {
    const record = props.studentBehaviors[studentId]
    let existing = getLogisticsStatus(studentId)
    
    record.logistics_tracker = {
        status: 'waiting',
        current: {
            reason: reason,
            timestamp: new Date().toISOString() // Queued Time
        },
        history: existing.history || []
    }
    saveTrackerData('logistics_tracker', studentId)
    showLogisticsDialog.value = false
    
    $q.notify({
        message: 'Added to Waiting List',
        color: 'orange',
        icon: 'hourglass_empty'
    })
}

const removeFromWaitlist = (studentId) => {
    const record = props.studentBehaviors[studentId]
    let existing = getLogisticsStatus(studentId)
    
    record.logistics_tracker = {
        status: 'in',
        current: null,
        history: existing.history || []
    }
    saveTrackerData('logistics_tracker', studentId)
}

const promoteFromWaitlist = (student) => {
    // Check limit again
    if (isLimitReached.value) {
         $q.notify({
            message: 'Classroom limit reached. Cannot let out.',
            color: 'negative'
        })
        return
    }
    
    const studentId = student.id
    const record = props.studentBehaviors[studentId]
    let existing = getLogisticsStatus(studentId)
    
    record.logistics_tracker = {
        status: 'out',
        current: {
            reason: existing.current?.reason || 'From Queue',
            timestamp: new Date().toISOString() // New "Out" time
        },
        history: existing.history || []
    }
    
    saveTrackerData('logistics_tracker', studentId)
    
     $q.notify({
        message: `${student.name} is now out.`,
        color: 'positive',
        icon: 'logout'
    })
}

const checkInStudent = (studentId) => {
    const record = props.studentBehaviors[studentId]
    let existing = getLogisticsStatus(studentId)
    
    // Archive current sortie to history
    let newHistory = [...(existing.history || [])]
    if (existing.status === 'out' && existing.current) {
        newHistory.unshift({
            ...existing.current,
            out_time: existing.current.timestamp,
            in_time: new Date().toISOString()
        })
    }
    // Backward comp fallback
    if (existing.status === 'out' && !existing.current && existing.timestamp) {
         newHistory.unshift({
            reason: existing.reason,
            out_time: existing.timestamp,
            in_time: new Date().toISOString()
        })
    }

    record.logistics_tracker = {
        status: 'in',
        current: null,
        history: newHistory
    }
    saveTrackerData('logistics_tracker', studentId)
}

</script>
