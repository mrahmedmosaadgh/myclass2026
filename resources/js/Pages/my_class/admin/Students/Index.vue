<template>
  <div class="q-pa-md">
    <Head title="Student Management"  />
    <!-- Header Section -->
    <div class="row items-center q-mb-md">
      <div class="col">
        <div class="text-h4 text-weight-bold">
          <q-icon name="school" class="q-mr-sm" />
          Student Management
        </div>
        <div class="text-subtitle2 text-grey-7">
          Manage students, classrooms, and promotions
        </div>
      </div>
      <div class="col-auto">
        <q-btn-group push>
          <q-btn
            color="primary"
            icon="add"
            label="Add Student"
            @click="openStudentDialog()"
            :disable="!canAddStudent"
            unelevated
          >
            <q-tooltip v-if="!canAddStudent">
              Please select School, Grade, and Classroom first
            </q-tooltip>
          </q-btn>
          <q-btn
            color="secondary"
            icon="upgrade"
            label="Promote Students"
            @click="showPromotionDialog = true"
            unelevated
          />
          <q-btn
            color="info"
            icon="upload_file"
            label="Import"
            @click="triggerImport"
            :disable="!canAddStudent"
            unelevated
          >
            <q-tooltip v-if="!canAddStudent">
              Please select School, Grade, and Classroom first
            </q-tooltip>
          </q-btn>
          <q-btn
            color="purple"
            icon="cloud_upload"
            label="School-Wide Import"
            @click="triggerSchoolWideImport"
            :disable="!filters.school_id"
            unelevated
          >
            <q-tooltip v-if="!filters.school_id">
              Please select a School first
            </q-tooltip>
          </q-btn>
          <q-btn
            color="positive"
            icon="download"
            label="Export"
            @click="handleExport"
            unelevated
          />
        </q-btn-group>
      </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row q-col-gutter-md q-mb-md">
      <div class="col-12 col-md-3">
        <q-card flat bordered>
          <q-card-section class="bg-primary text-white">
            <div class="text-h6">{{ totalStudents }}</div>
            <div class="text-caption">Total Students</div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-12 col-md-3">
        <q-card flat bordered>
          <q-card-section class="bg-secondary text-white">
            <div class="text-h6">{{ selectedSchoolStudents }}</div>
            <div class="text-caption">In Selected School</div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-12 col-md-3">
        <q-card flat bordered>
          <q-card-section class="bg-positive text-white">
            <div class="text-h6">{{ filteredCount }}</div>
            <div class="text-caption">Filtered Results</div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-12 col-md-3">
        <q-card flat bordered>
          <q-card-section class="bg-info text-white">
            <div class="text-h6">{{ selectedCount }}</div>
            <div class="text-caption">Selected</div>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <!-- Filters Section -->
    <q-card flat bordered class="q-mb-md">
      <q-card-section>
        <div class="row items-center q-mb-sm">
          <div class="col">
            <div class="text-h6">
              <q-icon name="filter_list" class="q-mr-sm" />
              Filters
            </div>
          </div>
          <div class="col-auto">
            <q-btn
              flat
              dense
              icon="clear"
              label="Clear All"
              @click="clearFilters"
              color="negative"
            />
          </div>
        </div>

        <div class="row q-col-gutter-md">
          <div class="col-12 col-md-3">
            <q-select
              v-model="filters.school_id"
              :options="schools"
              option-value="id"
              option-label="name"
              label="School"
              outlined
              dense
              clearable
              emit-value
              map-options
              @update:model-value="onSchoolChange"
            >
              <template v-slot:prepend>
                <q-icon name="business" />
              </template>
            </q-select>
          </div>

          <div class="col-12 col-md-3">
            <q-select
              v-model="filters.stage_id"
              :options="stages"
              option-value="id"
              option-label="name"
              label="Stage"
              outlined
              dense
              clearable
              emit-value
              map-options
              :disable="!filters.school_id"
              @update:model-value="onStageChange"
            >
              <template v-slot:prepend>
                <q-icon name="layers" />
              </template>
            </q-select>
          </div>

          <div class="col-12 col-md-3">
            <q-select
              v-model="filters.grade_id"
              :options="grades"
              option-value="id"
              option-label="name"
              label="Grade"
              outlined
              dense
              clearable
              emit-value
              map-options
              :disable="!filters.school_id"
              @update:model-value="onGradeChange"
            >
              <template v-slot:prepend>
                <q-icon name="school" />
              </template>
            </q-select>
          </div>

          <div class="col-12 col-md-3">
            <q-select
              v-model="filters.classroom_id"
              :options="classrooms"
              option-value="id"
              option-label="name"
              label="Classroom"
              outlined
              dense
              clearable
              emit-value
              map-options
              :disable="!filters.school_id"
              @update:model-value="applyFilters"
              @popup-show="onClassroomDropdownShow"
            >
              <template v-slot:prepend>
                <q-icon name="meeting_room" />
              </template>
              <template v-slot:no-option>
                <q-item>
                  <q-item-section class="text-grey">
                    {{ classrooms.length === 0 ? 'Click to load classrooms...' : 'No classrooms available' }}
                  </q-item-section>
                </q-item>
              </template>
            </q-select>
          </div>

          <div class="col-12 col-md-6">
            <q-input
              v-model="filters.search"
              label="Search by name or ID"
              outlined
              dense
              clearable
              @update:model-value="debouncedSearch"
            >
              <template v-slot:prepend>
                <q-icon name="search" />
              </template>
            </q-input>
          </div>
        </div>
      </q-card-section>
    </q-card>

    <!-- Data Table -->
    <q-card flat bordered>
      <q-table
        :rows="students"
        :columns="columns"
        row-key="id"
        :loading="loading"
        :pagination="pagination"
        @request="onRequest"
        selection="multiple"
        v-model:selected="selected"
        flat
        :rows-per-page-options="[10, 25, 50, 100]"
      >
        <template v-slot:top>
          <div class="col-12">
            <div class="text-h6">Students List</div>
            <div v-if="selected.length > 0" class="text-caption text-grey-7">
              {{ selected.length }} student(s) selected
            </div>
          </div>
        </template>

        <template v-slot:body-cell-index="props">
          <q-td :props="props">
            {{ props.rowIndex + 1 }}
          </q-td>
        </template>

        <template v-slot:body-cell-avatar="props">
          <q-td :props="props">
            <q-avatar color="primary" text-color="white" size="md">
              {{ getInitials(props.row.name) }}
            </q-avatar>
          </q-td>
        </template>

        <template v-slot:body-cell-name="props">
          <q-td :props="props">
            <div class="text-weight-bold">{{ props.row.name }}</div>
            <div class="text-caption text-grey-7">{{ props.row.name_ar }}</div>
          </q-td>
        </template>

        <template v-slot:body-cell-s_id="props">
          <q-td :props="props">
            <q-badge color="grey-7" outline>
              {{ props.row.s_id }}
            </q-badge>
          </q-td>
        </template>

        <template v-slot:body-cell-school="props">
          <q-td :props="props">
            <div>{{ props.row.school?.name }}</div>
          </q-td>
        </template>

        <template v-slot:body-cell-classroom="props">
          <q-td :props="props">
            <q-badge color="secondary">
              {{ props.row.classroom?.name }}
            </q-badge>
          </q-td>
        </template>

        <template v-slot:body-cell-grade="props">
          <q-td :props="props">
            <div>{{ props.row.grade?.name }}</div>
          </q-td>
        </template>

        <template v-slot:body-cell-actions="props">
          <q-td :props="props">
            <q-btn-group flat>
              <q-btn
                flat
                dense
                round
                icon="edit"
                color="primary"
                @click="openStudentDialog(props.row)"
              >
                <q-tooltip>Edit</q-tooltip>
              </q-btn>
              <q-btn
                flat
                dense
                round
                icon="history"
                color="info"
                @click="viewHistory(props.row)"
              >
                <q-tooltip>View History</q-tooltip>
              </q-btn>
              <q-btn
                flat
                dense
                round
                icon="delete"
                color="negative"
                @click="deleteStudent(props.row)"
              >
                <q-tooltip>Delete</q-tooltip>
              </q-btn>
            </q-btn-group>
          </q-td>
        </template>

        <template v-slot:no-data>
          <div class="full-width row flex-center q-gutter-sm q-pa-lg">
            <q-icon size="2em" name="sentiment_dissatisfied" />
            <span>No students found. Try adjusting your filters.</span>
          </div>
        </template>
      </q-table>
    </q-card>

    <!-- Bulk Actions Toolbar (appears when students selected) -->
    <div v-if="selected.length > 0" style="position: fixed; bottom: 18px; left: 0; right: 0; z-index: 2000; padding: 0 16px;">
      <q-toolbar class="bg-primary text-white shadow-up-2" style="border-radius: 4px;">
        <q-toolbar-title>
          {{ selected.length }} student(s) selected
        </q-toolbar-title>
        <q-btn
          flat
          label="Change Classroom"
          icon="meeting_room"
          @click="bulkChangeClassroom"
        />
        <q-btn
          flat
          label="Export Selected"
          icon="download"
          @click="exportSelected"
        />
        <q-btn
          flat
          label="Delete Selected"
          icon="delete"
          @click="bulkDelete"
        />
        <q-btn
          flat
          round
          dense
          icon="close"
          @click="selected = []"
        />
      </q-toolbar>
    </div>

    <!-- Student Form Dialog -->
    <q-dialog v-model="showStudentDialog" persistent>
      <q-card style="min-width: 600px">
        <q-card-section class="bg-primary text-white">
          <div class="text-h6">
            {{ editingStudent ? 'Edit Student' : 'Add New Student' }}
          </div>
          <div v-if="!editingStudent" class="text-caption">
            Adding to: {{ getSelectedContext() }}
          </div>
        </q-card-section>

        <q-card-section>
          <!-- Context Info Banner (for new students) -->
          <q-banner v-if="!editingStudent" class="bg-info text-white q-mb-md" rounded>
            <template v-slot:avatar>
              <q-icon name="info" />
            </template>
            <div class="text-weight-bold">Selected Context:</div>
            <div>{{ getSelectedSchoolName() }} → {{ getSelectedGradeName() }} → {{ getSelectedClassroomName() }}</div>
          </q-banner>

          <div class="row q-col-gutter-md">
            <div class="col-12 col-md-6">
              <q-input
                v-model="studentForm.name"
                label="Name *"
                outlined
                :rules="[val => !!val || 'Name is required']"
              />
            </div>
            <div class="col-12 col-md-6">
              <q-input
                v-model="studentForm.name_ar"
                label="Arabic Name"
                outlined
              />
            </div>
            <div class="col-12 col-md-6">
              <q-input
                v-model="studentForm.name_cute"
                label="Nickname"
                outlined
              />
            </div>
            
            <!-- Show these fields only when editing -->
            <template v-if="editingStudent">
              <!-- Current Student Info Banner -->
              <div class="col-12">
                <q-banner class="bg-grey-2 q-mb-md" rounded>
                  <template v-slot:avatar>
                    <q-icon name="info" color="primary" />
                  </template>
                  <div class="text-weight-bold">Current Assignment:</div>
                  <div>
                    <q-chip dense color="secondary" text-color="white" icon="business">
                      {{ editingStudent.school?.name || 'N/A' }}
                    </q-chip>
                    <q-chip dense color="info" text-color="white" icon="layers">
                      {{ editingStudent.stage?.name || 'N/A' }}
                    </q-chip>
                    <q-chip dense color="primary" text-color="white" icon="school">
                      {{ editingStudent.grade?.name || 'N/A' }}
                    </q-chip>
                    <q-chip dense color="accent" text-color="white" icon="meeting_room">
                      {{ editingStudent.classroom?.name || 'N/A' }}
                    </q-chip>
                  </div>
                </q-banner>
              </div>

              <div class="col-12 col-md-6">
                <q-select
                  v-model="studentForm.school_id"
                  :options="schools"
                  option-value="id"
                  option-label="name"
                  label="School *"
                  outlined
                  emit-value
                  map-options
                  :rules="[val => !!val || 'School is required']"
                  @update:model-value="onFormSchoolChange"
                />
              </div>
              <div class="col-12 col-md-6">
                <q-select
                  v-model="studentForm.stage_id"
                  :options="formStages"
                  option-value="id"
                  option-label="name"
                  label="Stage *"
                  outlined
                  emit-value
                  map-options
                  :disable="!studentForm.school_id"
                  :rules="[val => !!val || 'Stage is required']"
                  @update:model-value="onFormStageChange"
                />
              </div>
              <div class="col-12 col-md-6">
                <q-select
                  v-model="studentForm.grade_id"
                  :options="formGrades"
                  option-value="id"
                  option-label="name"
                  label="Grade *"
                  outlined
                  emit-value
                  map-options
                  :disable="!studentForm.stage_id"
                  :rules="[val => !!val || 'Grade is required']"
                  @update:model-value="onFormGradeChange"
                />
              </div>
              <div class="col-12">
                <q-select
                  v-model="studentForm.classroom_id"
                  :options="formClassrooms"
                  option-value="id"
                  option-label="name"
                  label="Classroom *"
                  outlined
                  emit-value
                  map-options
                  :disable="!studentForm.grade_id"
                  :rules="[val => !!val || 'Classroom is required']"
                  @update:model-value="onClassroomChange"
                >
                  <template v-slot:prepend>
                    <q-icon name="meeting_room" />
                  </template>
                </q-select>
                
                <!-- Warning when classroom is changed -->
                <q-banner v-if="classroomChanged" class="bg-warning text-white q-mt-sm" dense rounded>
                  <template v-slot:avatar>
                    <q-icon name="warning" />
                  </template>
                  <strong>Warning:</strong> Changing the classroom will update the student's assignment and may affect their schedule and records.
                </q-banner>
              </div>
            </template>
            <div class="col-12">
              <q-input
                v-model="studentForm.notes"
                label="Notes"
                outlined
                type="textarea"
                rows="3"
              />
            </div>
          </div>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Cancel" color="negative" v-close-popup />
          <q-btn
            unelevated
            label="Save"
            color="primary"
            @click="saveStudent"
            :loading="saving"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Student Promotion Dialog -->
    <StudentPromotionDialog
      v-model="showPromotionDialog"
      :grades="allGrades"
      :academic-years="academicYears"
      @promoted="onPromotionComplete"
    />

    <!-- Import Dialog (Filtered - to selected classroom) -->
    <q-dialog v-model="showImportDialog" persistent>
      <q-card style="min-width: 700px; max-width: 90vw">
        <q-card-section class="bg-info text-white">
          <div class="text-h6">Import Students</div>
          <div class="text-caption">
            Importing to: {{ getSelectedContext() }}
          </div>
        </q-card-section>

        <q-card-section v-if="!importPreviewData.length">
          <q-file
            v-model="importFile"
            label="Select Excel file"
            accept=".xlsx,.xls"
            outlined
            @update:model-value="handleFileUpload"
          >
            <template v-slot:prepend>
              <q-icon name="attach_file" />
            </template>
          </q-file>
          
          <q-banner class="bg-grey-2 q-mt-md" rounded>
            <template v-slot:avatar>
              <q-icon name="info" color="info" />
            </template>
            <div class="text-caption">
              <strong>Required columns:</strong> name<br>
              <strong>Optional columns:</strong> name_ar, name_cute, notes
            </div>
            <template v-slot:action>
              <q-btn
                flat
                dense
                label="Download Template"
                icon="download"
                color="primary"
                @click="downloadTemplate"
              />
            </template>
          </q-banner>
        </q-card-section>

        <q-card-section v-else class="q-pt-none" style="max-height: 400px; overflow-y: auto">
          <div class="text-subtitle2 q-mb-sm">Preview ({{ importPreviewData.length }} students)</div>
          <q-table
            :rows="importPreviewData"
            :columns="importPreviewColumns"
            row-key="index"
            flat
            dense
            :pagination="{ rowsPerPage: 10 }"
          />
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Cancel" color="negative" @click="closeImportDialog" />
          <q-btn
            v-if="importPreviewData.length"
            unelevated
            label="Import All"
            color="primary"
            @click="executeImport"
            :loading="importing"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- School-Wide Import Dialog -->
    <q-dialog v-model="showSchoolWideImportDialog" persistent>
      <q-card style="min-width: 700px; max-width: 90vw">
        <q-card-section class="bg-purple text-white">
          <div class="text-h6">School-Wide Import</div>
          <div class="text-caption">
            Importing to: {{ getSelectedSchoolName() }}
          </div>
        </q-card-section>

        <q-card-section v-if="!importWithClassroomPreview.length">
          <!-- Import Mode Selector -->
          <div class="q-mb-md">
            <div class="text-subtitle2 q-mb-sm">Import Mode</div>
            <q-option-group
              v-model="importMode"
              :options="importModeOptions"
              color="purple"
              inline
            />
            <q-banner class="bg-blue-1 q-mt-sm" dense rounded>
              <template v-slot:avatar>
                <q-icon :name="importMode === 'skip' ? 'info' : 'update'" color="primary" />
              </template>
              <div class="text-caption">
                <span v-if="importMode === 'skip'">
                  <strong>Skip Mode:</strong> Existing students will be left unchanged. Only new students will be created.
                </span>
                <span v-else>
                  <strong>Update Mode:</strong> Existing students will be updated with new data from Excel (non-empty fields only).
                </span>
              </div>
            </q-banner>
          </div>

          <q-file
            v-model="importWithClassroomFile"
            label="Select Excel file with classroom column"
            accept=".xlsx,.xls"
            outlined
            @update:model-value="handleSchoolWideFileUpload"
          >
            <template v-slot:prepend>
              <q-icon name="attach_file" />
            </template>
          </q-file>
          
          <q-banner class="bg-grey-2 q-mt-md" rounded>
            <template v-slot:avatar>
              <q-icon name="info" color="info" />
            </template>
            <div class="text-caption">
              <strong>Required columns:</strong> name, classroom<br>
              <strong>Optional columns:</strong> name_ar, name_cute, notes<br>
              <strong>Classroom formats:</strong> "4A", "Grade 4 - A", "4-A"
            </div>
            <template v-slot:action>
              <q-btn
                flat
                dense
                label="Download Template"
                icon="download"
                color="primary"
                @click="downloadTemplateWithClassroom"
              />
            </template>
          </q-banner>
        </q-card-section>

        <q-card-section v-else class="q-pt-none">
          <div class="text-subtitle2 q-mb-sm">Preview ({{ filteredPreviewRecords.length }} of {{ importWithClassroomPreview.length }} students)</div>
          
          <!-- Filter Buttons (only show after validation) -->
          <div v-if="validationResults.length" class="q-mb-md">
            <q-btn-group outline>
              <q-btn 
                :outline="statusFilter !== 'all'"
                :unelevated="statusFilter === 'all'"
                label="All"
                color="grey-7"
                size="sm"
                @click="statusFilter = 'all'"
              />
              <q-btn 
                v-if="validationSummary.errors > 0"
                :outline="statusFilter !== 'error'"
                :unelevated="statusFilter === 'error'"
                :label="`❌ Errors (${validationSummary.errors})`"
                color="negative"
                size="sm"
                @click="statusFilter = 'error'"
              />
              <q-btn 
                v-if="validationSummary.will_create > 0"
                :outline="statusFilter !== 'will_create'"
                :unelevated="statusFilter === 'will_create'"
                :label="`✅ Create (${validationSummary.will_create})`"
                color="positive"
                size="sm"
                @click="statusFilter = 'will_create'"
              />
              <q-btn 
                v-if="validationSummary.will_update > 0"
                :outline="statusFilter !== 'will_update'"
                :unelevated="statusFilter === 'will_update'"
                :label="`📝 Update (${validationSummary.will_update})`"
                color="primary"
                size="sm"
                @click="statusFilter = 'will_update'"
              />
              <q-btn 
                v-if="validationSummary.will_restore > 0"
                :outline="statusFilter !== 'will_restore'"
                :unelevated="statusFilter === 'will_restore'"
                :label="`🔄 Restore (${validationSummary.will_restore})`"
                color="info"
                size="sm"
                @click="statusFilter = 'will_restore'"
              />
              <q-btn 
                v-if="validationSummary.will_skip > 0"
                :outline="statusFilter !== 'will_skip'"
                :unelevated="statusFilter === 'will_skip'"
                :label="`⏭️ Skip (${validationSummary.will_skip})`"
                color="warning"
                size="sm"
                @click="statusFilter = 'will_skip'"
              />
            </q-btn-group>
          </div>

          <q-table
            :rows="filteredPreviewRecords"
            :columns="importWithClassroomColumns"
            row-key="index"
            flat
            bordered
            dense
            :pagination="{ rowsPerPage: 20 }"
            style="max-height: 400px"
            virtual-scroll
          >
            <template v-slot:body-cell-status="props">
              <q-td :props="props">
                <q-badge 
                  v-if="props.row.validationStatus"
                  :color="props.row.validationColor || 'grey'"
                  :label="props.row.validationIcon + ' ' + props.row.validationMessage"
                />
                <q-badge 
                  v-else
                  color="grey" 
                  label="⏳ Pending validation"
                />
              </q-td>
            </template>
          </q-table>

          <!-- Validation Summary (below table) -->
          <div v-if="validationSummary" class="q-mt-md">
            <q-separator class="q-mb-md" />
            <div class="text-subtitle2 q-mb-sm">📊 Validation Summary</div>
            <div class="row q-gutter-sm">
              <q-chip color="grey-3" text-color="grey-9" dense>
                <strong>Total:</strong> {{ validationSummary.total }}
              </q-chip>
              <q-chip v-if="validationSummary.will_create > 0" color="positive" text-color="white" dense>
                ✅ Will Create: {{ validationSummary.will_create }}
              </q-chip>
              <q-chip v-if="validationSummary.will_update > 0" color="primary" text-color="white" dense>
                📝 Will Update: {{ validationSummary.will_update }}
              </q-chip>
              <q-chip v-if="validationSummary.will_restore > 0" color="info" text-color="white" dense>
                🔄 Will Restore: {{ validationSummary.will_restore }}
              </q-chip>
              <q-chip v-if="validationSummary.will_skip > 0" color="warning" text-color="white" dense>
                ⏭️ Will Skip: {{ validationSummary.will_skip }}
              </q-chip>
              <q-chip v-if="validationSummary.errors > 0" color="negative" text-color="white" dense>
                ❌ Errors: {{ validationSummary.errors }}
              </q-chip>
            </div>
            <q-banner v-if="validationSummary.errors > 0" class="bg-red-1 text-negative q-mt-sm" dense rounded>
              <template v-slot:avatar>
                <q-icon name="warning" color="negative" />
              </template>
              <strong>Cannot import:</strong> Please fix {{ validationSummary.errors }} error(s) before importing.
              Click the "❌ Errors" filter above to see only problematic records.
            </q-banner>
          </div>
        </q-card-section>

        <!-- Import Results (shown after completion) -->
        <q-card-section v-if="importCompleted" class="bg-green-1 q-pa-md">
          <div class="text-h6 text-positive q-mb-md">
            <q-icon name="check_circle" size="sm" class="q-mr-sm" />
            Import Complete!
          </div>
          <div class="row q-gutter-sm q-mb-sm">
            <q-chip color="grey-7" text-color="white" size="md">
              <strong>Total:</strong> {{ importResults.created + importResults.updated + importResults.restored + importResults.duplicates + importResults.failed }}
            </q-chip>
          </div>
          <div class="row q-gutter-sm">
            <q-chip v-if="importResults.created > 0" color="positive" text-color="white" size="md">
              ✅ Created: {{ importResults.created }}
            </q-chip>
            <q-chip v-if="importResults.updated > 0" color="primary" text-color="white" size="md">
              📝 Updated: {{ importResults.updated }}
            </q-chip>
            <q-chip v-if="importResults.restored > 0" color="info" text-color="white" size="md">
              🔄 Restored: {{ importResults.restored }}
            </q-chip>
            <q-chip v-if="importResults.duplicates > 0" color="warning" text-color="white" size="md">
              ⏭️ Skipped: {{ importResults.duplicates }}
            </q-chip>
            <q-chip v-if="importResults.failed > 0" color="negative" text-color="white" size="md">
              ❌ Failed: {{ importResults.failed }}
            </q-chip>
          </div>
          <q-banner v-if="importResults.failed > 0" class="bg-red-1 text-negative q-mt-md" dense rounded>
            <template v-slot:avatar>
              <q-icon name="error" color="negative" />
            </template>
            <strong>{{ importResults.failed }} record(s) failed.</strong> Check browser console for details.
          </q-banner>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Cancel" color="negative" @click="closeSchoolWideImportDialog" />
          <q-btn
            v-if="importWithClassroomPreview.length && !validationResults.length"
            unelevated
            label="Validate All"
            color="info"
            icon="fact_check"
            @click="validateAllRecords"
            :loading="validating"
          />
          <q-btn
            v-if="validationResults.length"
            unelevated
            label="Import All"
            color="purple"
            icon="upload"
            @click="executeSchoolWideImport"
            :loading="importing"
            :disable="validationSummary && validationSummary.errors > 0"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useQuasar } from 'quasar'
import axios from 'axios'
// import * as XLSX from 'xlsx' // Dynamic import used instead
import StudentPromotionDialog from './components/StudentPromotionDialog.vue'

const props = defineProps({
  records: Object,
  schools: Array,
  grades: Array,
  academicYears: Array,
  userRoles: Array,
  permissions: Object
})

const $q = useQuasar()

// State
const loading = ref(false)
const students = ref([])
const selected = ref([])
const showStudentDialog = ref(false)
const showPromotionDialog = ref(false)
const showImportDialog = ref(false)
const editingStudent = ref(null)
const saving = ref(false)

// Import state
const importFile = ref(null)
const importPreviewData = ref([])
const importing = ref(false)
const classroomChanged = ref(false)
const originalClassroomId = ref(null)

// School-wide import state
const showSchoolWideImportDialog = ref(false)
const importWithClassroomFile = ref(null)
const importWithClassroomPreview = ref([])

const importPreviewColumns = [
  { name: 'name', label: 'Name', field: 'name', align: 'left' },
  { name: 'name_ar', label: 'Arabic Name', field: 'name_ar', align: 'left' },
  { name: 'name_cute', label: 'Nickname', field: 'name_cute', align: 'left' },
  { name: 'notes', label: 'Notes', field: 'notes', align: 'left' }
]

// Import mode state
const importMode = ref('skip') // 'skip' or 'update'
const importModeOptions = [
  { label: 'Skip Duplicates (Safe)', value: 'skip', description: 'Only create new students' },
  { label: 'Update Existing', value: 'update', description: 'Update duplicate students with new data' }
]

// Validation state
const validating = ref(false)
const validationResults = ref([])
const validationSummary = ref(null)
const statusFilter = ref('all') // 'all', 'error', 'will_create', 'will_update', 'will_skip', 'will_restore'

// Import completion state
const importCompleted = ref(false)
const importResults = ref(null)

const importWithClassroomColumns = [
  { 
    name: 'status', 
    label: 'Status', 
    field: 'status', 
    align: 'center',
    format: (val) => val || 'pending'
  },
  { name: 'name', label: 'Name', field: 'name', align: 'left' },
  { name: 'name_ar', label: 'Arabic Name', field: 'name_ar', align: 'left' },
  { name: 'name_cute', label: 'Nickname', field: 'name_cute', align: 'left' },
  { name: 'classroom', label: 'Classroom', field: 'classroom', align: 'left' },
  { name: 'notes', label: 'Notes', field: 'notes', align: 'left' }
]

// Filters
const filters = ref({
  school_id: null,
  stage_id: null,
  grade_id: null,
  classroom_id: null,
  search: ''
})

// Cascading data
const stages = ref([])
const grades = ref([])
const classrooms = ref([])
const allGrades = ref(props.grades || [])
const academicYears = ref(props.academicYears || [])

// Form data
const formStages = ref([])
const formGrades = ref([])
const formClassrooms = ref([])

const studentForm = ref({
  name: '',
  name_ar: '',
  name_cute: '',
  school_id: null,
  stage_id: null,
  grade_id: null,
  classroom_id: null,
  notes: ''
})

// Pagination
const pagination = ref({
  sortBy: 'name',
  descending: false,
  page: 1,
  rowsPerPage: 25,
  rowsNumber: 0
})

// Table columns
const columns = [
  {
    name: 'index',
    label: '#',
    field: 'index',
    align: 'left',
    sortable: false,
    style: 'width: 50px'
  },
  {
    name: 'avatar',
    label: '',
    field: 'name', // Use name field for avatar
    align: 'center',
    style: 'width: 60px',
    sortable: false
  },
  {
    name: 's_id',
    label: 'ID',
    field: 's_id',
    align: 'left',
    sortable: true
  },
  {
    name: 'name',
    label: 'Name',
    field: 'name',
    align: 'left',
    sortable: true
  },
  {
    name: 'school',
    label: 'School',
    field: row => row.school?.name || '',
    align: 'left',
    sortable: true
  },
  {
    name: 'grade',
    label: 'Grade',
    field: row => row.grade?.name || '',
    align: 'left',
    sortable: true
  },
  {
    name: 'classroom',
    label: 'Classroom',
    field: row => row.classroom?.name || '',
    align: 'left',
    sortable: true
  },
  {
    name: 'actions',
    label: 'Actions',
    field: 'id', // Use id field for actions
    align: 'center',
    sortable: false
  }
]

// Computed
const totalStudents = computed(() => pagination.value.rowsNumber || 0)
const selectedSchoolStudents = computed(() => {
  if (!filters.value.school_id) return 0
  return students.value.filter(s => s.school_id === filters.value.school_id).length
})
const filteredCount = computed(() => students.value.length)
const selectedCount = computed(() => selected.value.length)

// Check if user can add student (must have school, grade, and classroom selected)
const canAddStudent = computed(() => {
  return filters.value.school_id && filters.value.grade_id && filters.value.classroom_id
})

// Helper methods to get selected names
const getSelectedSchoolName = () => {
  const school = props.schools.find(s => s.id === filters.value.school_id)
  return school?.name || 'N/A'
}

const getSelectedGradeName = () => {
  const grade = grades.value.find(g => g.id === filters.value.grade_id)
  return grade?.name || 'N/A'
}

const getSelectedClassroomName = () => {
  const classroom = classrooms.value.find(c => c.id === filters.value.classroom_id)
  return classroom?.name || 'N/A'
}

const getSelectedContext = () => {
  return `${getSelectedSchoolName()} → ${getSelectedGradeName()} → ${getSelectedClassroomName()}`
}

// Computed property for filtered preview records
const filteredPreviewRecords = computed(() => {
  if (statusFilter.value === 'all') {
    return importWithClassroomPreview.value
  }
  
  return importWithClassroomPreview.value.filter(row => {
    return row.validationStatus === statusFilter.value
  })
})

// Methods
const getInitials = (name) => {
  if (!name) return '?'
  const parts = name.split(' ')
  if (parts.length >= 2) {
    return (parts[0][0] + parts[1][0]).toUpperCase()
  }
  return name.substring(0, 2).toUpperCase()
}

const onSchoolChange = async () => {
  filters.value.stage_id = null
  filters.value.grade_id = null
  filters.value.classroom_id = null
  stages.value = []
  grades.value = []
  classrooms.value = []

  if (filters.value.school_id) {
    // Load stages but also load classrooms for the school directly
    loadStages(filters.value.school_id)
    await loadClassrooms(filters.value.school_id, 'school')
  }
  applyFilters()
}

const onStageChange = async () => {
  filters.value.grade_id = null
  filters.value.classroom_id = null
  grades.value = []
  // Don't clear classrooms, we might want to show stage-specific ones

  if (filters.value.stage_id) {
    await loadGrades(filters.value.stage_id)
    await loadClassrooms(filters.value.stage_id, 'stage')
  } else {
    // Revert to school classrooms if stage is cleared
    if (filters.value.school_id) {
      await loadClassrooms(filters.value.school_id, 'school')
    } else {
      classrooms.value = []
    }
  }
  applyFilters()
}

const onGradeChange = async () => {
  filters.value.classroom_id = null
  // Don't clear classrooms immediately

  if (filters.value.grade_id) {
    await loadClassrooms(filters.value.grade_id, 'grade')
  } else {
    // Revert to stage or school classrooms
    if (filters.value.stage_id) {
      await loadClassrooms(filters.value.stage_id, 'stage')
    } else if (filters.value.school_id) {
      await loadClassrooms(filters.value.school_id, 'school')
    } else {
      classrooms.value = []
    }
  }
  applyFilters()
}

const onClassroomDropdownShow = async () => {
  // Load classrooms when dropdown is opened (if not already loaded)
  if (classrooms.value.length === 0 && filters.value.school_id) {
    if (filters.value.grade_id) {
      await loadClassrooms(filters.value.grade_id, 'grade')
    } else if (filters.value.stage_id) {
      await loadClassrooms(filters.value.stage_id, 'stage')
    } else {
      await loadClassrooms(filters.value.school_id, 'school')
    }
  }
}

const loadStages = async (schoolId) => {
  try {
    const response = await axios.get(`/admin/stages/by-school/${schoolId}`)
    stages.value = response.data
  } catch (error) {
    console.error('Error loading stages:', error)
  }
}

const loadGrades = async (stageId) => {
  try {
    const response = await axios.get(`/admin/grades/by-stage/${stageId}`)
    grades.value = response.data
  } catch (error) {
    console.error('Error loading grades:', error)
  }
}

const loadClassrooms = async (id, type) => {
  try {
    let url = ''
    if (type === 'school') {
      url = `/admin/classrooms/by-school/${id}`
    } else if (type === 'stage') {
      url = `/admin/classrooms/by-stage/${id}`
    } else {
      // Default to grade for backward compatibility or explicit 'grade' type
      url = `/admin/classrooms/by-grade/${id}`
    }
    
    const response = await axios.get(url)
    classrooms.value = response.data
  } catch (error) {
    console.error('Error loading classrooms:', error)
    classrooms.value = []
  }
}

const applyFilters = async () => {
  loading.value = true
  try {
    const params = {
      school_id: filters.value.school_id,
      stage_id: filters.value.stage_id,
      grade_id: filters.value.grade_id,
      classroom_id: filters.value.classroom_id,
      search: filters.value.search
    }

    const response = await axios.get('/admin/students/filtered', { params })
    students.value = response.data.records.data || []
    pagination.value.rowsNumber = response.data.records.total || 0
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'Failed to load students'
    })
  } finally {
    loading.value = false
  }
}

const clearFilters = () => {
  filters.value = {
    school_id: null,
    stage_id: null,
    grade_id: null,
    classroom_id: null,
    search: ''
  }
  stages.value = []
  grades.value = []
  classrooms.value = []
  applyFilters()
}

let searchTimeout
const debouncedSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    applyFilters()
  }, 500)
}

const onRequest = (props) => {
  // Handle pagination, sorting, etc.
  applyFilters()
}

const openStudentDialog = (student = null) => {
  classroomChanged.value = false
  originalClassroomId.value = null
  
  if (student) {
    editingStudent.value = student
    originalClassroomId.value = student.classroom_id
    studentForm.value = {
      name: student.name,
      name_ar: student.name_ar,
      name_cute: student.name_cute,
      school_id: student.school_id,
      stage_id: student.stage_id,
      grade_id: student.grade_id,
      classroom_id: student.classroom_id,
      notes: student.notes
    }
    // Load cascading data for form
    if (student.school_id) onFormSchoolChange()
    if (student.stage_id) onFormStageChange()
    if (student.grade_id) onFormGradeChange()
  } else {
    // New student - pre-fill with selected filters
    editingStudent.value = null
    studentForm.value = {
      name: '',
      name_ar: '',
      name_cute: '',
      school_id: filters.value.school_id,
      stage_id: filters.value.stage_id,
      grade_id: filters.value.grade_id,
      classroom_id: filters.value.classroom_id,
      notes: ''
    }
  }
  showStudentDialog.value = true
}

const onClassroomChange = () => {
  // Check if classroom has been changed from original
  if (editingStudent.value && originalClassroomId.value !== null) {
    classroomChanged.value = studentForm.value.classroom_id !== originalClassroomId.value
  }
}

const onFormSchoolChange = async () => {
  studentForm.value.stage_id = null
  studentForm.value.grade_id = null
  studentForm.value.classroom_id = null
  
  if (studentForm.value.school_id) {
    const response = await axios.get(`/admin/stages/by-school/${studentForm.value.school_id}`)
    formStages.value = response.data
  }
}

const onFormStageChange = async () => {
  studentForm.value.grade_id = null
  studentForm.value.classroom_id = null
  
  if (studentForm.value.stage_id) {
    const response = await axios.get(`/admin/grades/by-stage/${studentForm.value.stage_id}`)
    formGrades.value = response.data
  }
}

const onFormGradeChange = async () => {
  studentForm.value.classroom_id = null
  
  if (studentForm.value.grade_id) {
    const response = await axios.get(`/admin/classrooms/by-grade/${studentForm.value.grade_id}`)
    formClassrooms.value = response.data
  }
}

const saveStudent = async () => {
  saving.value = true
  try {
    const url = editingStudent.value
      ? `/admin/students/${editingStudent.value.id}`
      : '/admin/students'

    const data = {
      ...studentForm.value,
      ...(editingStudent.value && { _method: 'PUT' })
    }

    await axios.post(url, data)

    $q.notify({
      type: 'positive',
      message: editingStudent.value ? 'Student updated successfully' : 'Student created successfully'
    })

    showStudentDialog.value = false
    applyFilters()
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: error.response?.data?.message || 'Failed to save student'
    })
  } finally {
    saving.value = false
  }
}

const deleteStudent = async (student) => {
  $q.dialog({
    title: 'Confirm Delete',
    message: `Are you sure you want to delete ${student.name}?`,
    cancel: true,
    persistent: true
  }).onOk(async () => {
    try {
      await axios.delete(`/admin/students/${student.id}`)
      $q.notify({
        type: 'positive',
        message: 'Student deleted successfully'
      })
      applyFilters()
    } catch (error) {
      $q.notify({
        type: 'negative',
        message: 'Failed to delete student'
      })
    }
  })
}

const viewHistory = async (student) => {
  try {
    const response = await axios.get(`/admin/students/${student.id}/classroom-history`)
    $q.dialog({
      title: `Classroom History - ${student.name}`,
      message: JSON.stringify(response.data.history, null, 2)
    })
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'Failed to load history'
    })
  }
}

const handleExport = async () => {
  if (students.value.length === 0) {
    $q.notify({
      type: 'warning',
      message: 'No students to export'
    })
    return
  }

  try {
    const XLSX = await import('xlsx');
    // Prepare data for export
    const exportData = students.value.map(student => ({
      'ID': student.s_id || '',
      'Name': student.name || '',
      'Arabic Name': student.name_ar || '',
      'Nickname': student.name_cute || '',
      'School': student.school?.name || '',
      'Stage': student.stage?.name || '',
      'Grade': student.grade?.name || '',
      'Classroom': student.classroom?.name || '',
      'Notes': student.notes || ''
    }))

    // Create worksheet
    const ws = XLSX.utils.json_to_sheet(exportData)
    
    // Create workbook
    const wb = XLSX.utils.book_new()
    XLSX.utils.book_append_sheet(wb, ws, 'Students')

    // Generate filename with timestamp
    const timestamp = new Date().toISOString().split('T')[0]
    const filename = `students_export_${timestamp}.xlsx`

    // Download file
    XLSX.writeFile(wb, filename)

    $q.notify({
      type: 'positive',
      message: `Successfully exported ${students.value.length} students`
    })
  } catch (error) {
    console.error('Export error:', error)
    $q.notify({
      type: 'negative',
      message: 'Failed to export students'
    })
  }
}

const bulkChangeClassroom = () => {
  $q.notify({
    type: 'info',
    message: 'Bulk classroom change coming in Phase 4!'
  })
}

const exportSelected = async () => {
  if (selected.value.length === 0) {
    $q.notify({
      type: 'warning',
      message: 'No students selected'
    })
    return
  }

  try {
    const XLSX = await import('xlsx');
    // Prepare data for export
    const exportData = selected.value.map(student => ({
      'ID': student.s_id || '',
      'Name': student.name || '',
      'Arabic Name': student.name_ar || '',
      'Nickname': student.name_cute || '',
      'School': student.school?.name || '',
      'Stage': student.stage?.name || '',
      'Grade': student.grade?.name || '',
      'Classroom': student.classroom?.name || '',
      'Notes': student.notes || ''
    }))

    // Create worksheet
    const ws = XLSX.utils.json_to_sheet(exportData)
    
    // Create workbook
    const wb = XLSX.utils.book_new()
    XLSX.utils.book_append_sheet(wb, ws, 'Students')

    // Generate filename with timestamp
    const timestamp = new Date().toISOString().split('T')[0]
    const filename = `students_export_${timestamp}.xlsx`

    // Download file
    XLSX.writeFile(wb, filename)

    $q.notify({
      type: 'positive',
      message: `Successfully exported ${selected.value.length} students`
    })
  } catch (error) {
    console.error('Export error:', error)
    $q.notify({
      type: 'negative',
      message: 'Failed to export students'
    })
  }
}

const bulkDelete = () => {
  $q.dialog({
    title: 'Confirm Bulk Delete',
    message: `Are you sure you want to delete ${selected.value.length} students?`,
    cancel: true,
    persistent: true
  }).onOk(() => {
    $q.notify({
      type: 'info',
      message: 'Bulk delete coming soon!'
    })
  })
}

const onPromotionComplete = (result) => {
  $q.notify({
    type: 'positive',
    message: `Successfully promoted ${result.promoted_count} students!`
  })
  applyFilters()
}

const triggerImport = () => {
  if (!canAddStudent.value) {
    $q.notify({
      type: 'negative',
      message: 'Please select School, Grade, and Classroom before importing'
    })
    return
  }
  showImportDialog.value = true
}

const handleFileUpload = async (file) => {
  if (!file) return

  try {
    const data = await readExcelFile(file)
    importPreviewData.value = data.map((row, index) => ({
      index,
      name: row.name || row.Name || '',
      name_ar: row.name_ar || row['Arabic Name'] || '',
      name_cute: row.name_cute || row.Nickname || '',
      notes: row.notes || row.Notes || ''
    }))
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'Error reading Excel file: ' + error.message
    })
  }
}

const readExcelFile = (file) => {
  return new Promise((resolve, reject) => {
    const reader = new FileReader()
    
    reader.onload = async (e) => {
      try {
        const XLSX = await import('xlsx');
        const data = new Uint8Array(e.target.result)
        const workbook = XLSX.read(data, { type: 'array' })
        const firstSheet = workbook.Sheets[workbook.SheetNames[0]]
        const jsonData = XLSX.utils.sheet_to_json(firstSheet)
        resolve(jsonData)
      } catch (error) {
        reject(error)
      }
    }
    
    reader.onerror = reject
    reader.readAsArrayBuffer(file)
  })
}

const executeImport = async () => {
  importing.value = true
  let successCount = 0
  let errorCount = 0

  try {
    for (const row of importPreviewData.value) {
      try {
        const studentData = {
          name: row.name,
          name_ar: row.name_ar || '',
          name_cute: row.name_cute || '',
          school_id: filters.value.school_id,
          stage_id: filters.value.stage_id,
          grade_id: filters.value.grade_id,
          classroom_id: filters.value.classroom_id,
          notes: row.notes || ''
        }

        await axios.post('/admin/students', studentData)
        successCount++
      } catch (error) {
        errorCount++
        console.error('Import error:', error)
      }
    }

    $q.notify({
      type: successCount > 0 ? 'positive' : 'negative',
      message: `Import complete: ${successCount} students added${errorCount > 0 ? `, ${errorCount} errors` : ''}`,
      timeout: 3000
    })

    closeImportDialog()
    await applyFilters()
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'Import failed: ' + (error.message || 'Unknown error')
    })
  } finally {
    importing.value = false
  }
}

const downloadTemplate = () => {
  window.location.href = '/admin/students/download-template'
}

const downloadTemplateWithClassroom = () => {
  window.location.href = '/admin/students/download-template-with-classroom'
}

const handleSchoolWideFileUpload = async (file) => {
  if (!file) return

  try {
    const data = await readExcelFile(file)
    
    // Helper to find value case-insensitive
    const findValue = (row, keys) => {
      const rowKeys = Object.keys(row)
      for (const key of keys) {
        // Exact match
        if (row[key] !== undefined) return row[key]
        
        // Case-insensitive match
        const foundKey = rowKeys.find(k => k.toLowerCase().trim() === key.toLowerCase())
        if (foundKey && row[foundKey] !== undefined) return row[foundKey]
      }
      return ''
    }

    // Check if classroom column exists
    const hasClassroom = data.length > 0 && (
      'classroom' in data[0] || 
      'Classroom' in data[0] || 
      Object.keys(data[0]).some(k => k.toLowerCase().trim() === 'classroom')
    )

    if (data.length > 0 && !hasClassroom) {
      $q.notify({
        type: 'negative',
        message: 'Invalid file: Missing "classroom" column. Please download the correct template.'
      })
      importWithClassroomFile.value = null
      return
    }

    const validRows = []
    const invalidRows = []

    data.forEach((row, index) => {
      const name = findValue(row, ['name', 'Name', 'Student Name'])
      const classroom = findValue(row, ['classroom', 'Classroom'])
      const nameAr = findValue(row, ['name_ar', 'Arabic Name', 'arabic name', 'Name Ar'])
      const nameCute = findValue(row, ['name_cute', 'Nickname', 'nickname', 'Name Cute'])
      const notes = findValue(row, ['notes', 'Notes'])

      // Check if required fields are empty
      if (!name.toString().trim() || !classroom.toString().trim()) {
        invalidRows.push({
          row: index + 2, // Excel row number (1-indexed + header)
          reason: !name.toString().trim() ? 'Missing name' : 'Missing classroom'
        })
      } else {
        validRows.push({
          index,
          name: name.toString().trim(),
          name_ar: nameAr.toString().trim(),
          name_cute: nameCute.toString().trim(),
          classroom: classroom.toString().trim(),
          notes: notes.toString().trim()
        })
      }
    })

    if (invalidRows.length > 0) {
      const errorMessages = invalidRows.map(r => `Row ${r.row}: ${r.reason}`).join('\n')
      $q.dialog({
        title: 'Invalid Records Found',
        message: `Found ${invalidRows.length} invalid record(s):\n\n${errorMessages}\n\nThese rows will be skipped.`,
        html: true
      })
    }

    importWithClassroomPreview.value = validRows
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'Error reading Excel file: ' + error.message
    })
  }
}

const executeSchoolWideImport = async () => {
  if (!filters.value.school_id) {
    $q.notify({
      type: 'negative',
      message: 'Please select a school'
    })
    return
  }

  const totalRecords = importWithClassroomPreview.value.length
  let processed = 0
  let created = 0
  let updated = 0
  let duplicates = 0
  let restored = 0
  let failed = 0
  const errors = []

  // Show progress dialog
  const progressDialog = $q.dialog({
    title: 'Importing Students',
    message: `Processing 0 of ${totalRecords} students...`,
    progress: {
      spinner: false,
      value: 0,
      color: 'primary'
    },
    persistent: true,
    ok: false
  })

  importing.value = true

  try {
    // Process each record one by one
    for (const [index, row] of importWithClassroomPreview.value.entries()) {
      try {
        const response = await axios.post('/admin/students/import-with-classroom', {
          school_id: filters.value.school_id,
          name: row.name,
          name_ar: row.name_ar,
          name_cute: row.name_cute,
          classroom: row.classroom,
          notes: row.notes,
          import_mode: importMode.value // Pass the selected import mode
        })

        processed++

        // Track status based on response
        const status = response.data.status
        if (status === 'created') {
          created++
        } else if (status === 'updated') {
          updated++
        } else if (status === 'duplicate') {
          duplicates++
        } else if (status === 'restored') {
          restored++
        }

        // Update progress
        const statusText = importMode.value === 'update' 
          ? `Created: ${created} | Updated: ${updated} | Restored: ${restored} | Skipped: ${duplicates} | Failed: ${failed}`
          : `Created: ${created} | Restored: ${restored} | Duplicates: ${duplicates} | Failed: ${failed}`

        progressDialog.update({
          message: `Processing ${processed} of ${totalRecords} students...\\n${statusText}`,
          progress: {
            value: processed / totalRecords
          }
        })

      } catch (error) {
        processed++
        failed++
        errors.push({
          row: index + 2,
          name: row.name,
          error: error.response?.data?.message || error.message
        })

        // Update progress
        const statusText = importMode.value === 'update' 
          ? `Created: ${created} | Updated: ${updated} | Restored: ${restored} | Skipped: ${duplicates} | Failed: ${failed}`
          : `Created: ${created} | Restored: ${restored} | Duplicates: ${duplicates} | Failed: ${failed}`

        progressDialog.update({
          message: `Processing ${processed} of ${totalRecords} students...\\n${statusText}`,
          progress: {
            value: processed / totalRecords
          }
        })
      }
    }

    // Close progress dialog
    progressDialog.hide()

    // Store import results for display
    importResults.value = {
      created,
      updated,
      restored,
      duplicates,
      failed,
      errors
    }

    // Show results in dialog instead of closing
    importCompleted.value = true
    importing.value = false

    // Refresh the student list
    await applyFilters()

  } catch (error) {
    progressDialog.hide()
    $q.notify({
      type: 'negative',
      message: 'Import failed: ' + (error.message || 'Unknown error')
    })
    importing.value = false
  }
}

const triggerSchoolWideImport = () => {
  if (!filters.value.school_id) {
    $q.notify({
      type: 'negative',
      message: 'Please select a School first'
    })
    return
  }
  showSchoolWideImportDialog.value = true
}

const validateAllRecords = async () => {
  validating.value = true
  
  try {
    const response = await axios.post('/admin/students/validate-import-batch', {
      school_id: filters.value.school_id,
      students: importWithClassroomPreview.value,
      import_mode: importMode.value
    })

    validationResults.value = response.data.validations
    validationSummary.value = response.data.summary

    // Update preview table with validation results
    importWithClassroomPreview.value = importWithClassroomPreview.value.map((row, index) => {
      const validation = validationResults.value[index]
      return {
        ...row,
        validationStatus: validation.status,
        validationMessage: validation.message,
        validationIcon: validation.icon,
        validationColor: validation.color
      }
    })

    $q.notify({
      type: 'positive',
      message: `Validation complete! ${validationSummary.value.total} records checked.`,
      timeout: 2000
    })

  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'Validation failed: ' + (error.message || 'Unknown error')
    })
  } finally {
    validating.value = false
  }
}

const closeImportDialog = () => {
  showImportDialog.value = false
  importFile.value = null
  importPreviewData.value = []
}

const closeSchoolWideImportDialog = () => {
  showSchoolWideImportDialog.value = false
  importWithClassroomFile.value = null
  importWithClassroomPreview.value = []
  validationResults.value = []
  validationSummary.value = null
  statusFilter.value = 'all'
  importCompleted.value = false
  importResults.value = null
}

// Initialize
onMounted(async () => {
  if (props.records?.data) {
    students.value = props.records.data
    pagination.value.rowsNumber = props.records.total || 0
  }
  
  // Auto-select first school if available
  if (props.schools && props.schools.length > 0) {
    filters.value.school_id = props.schools[0].id
    await onSchoolChange()
  }
})
</script>

<style scoped>
.shadow-up-2 {
  box-shadow: 0 -2px 4px rgba(0, 0, 0, 0.1);
}
</style>
